#!/usr/bin/php
<?php
/**
 * PHP TracSlack
 * Sends Trac's ticket email notifications to Slack
 * 
 * Author: Mike Lopez <e@mikelopez.com>
 *
 * ----
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * ----
 */

// start of config

define('INCOMING_WEBHOOK_URL', 'put-slack-incoming-webook-url-here');
define('TRAC_URL', 'put-your-trac-project-url-here');

// end of config

$trac_url = TRAC_URL;
if(substr($trac_url, '-1') != '/') $trac_url .= '/';

// read email
$email=file("php://stdin", FILE_IGNORE_NEW_LINES);

// find message subject and body
$subject = '';
while($x = trim(array_shift($email))) {
	if(preg_match('/^subject:.*?\[(.+?)\].*$/i', $x, $match)) {
		$subject = trim($match[1]);
	}
}

// remove headers, empty lines and other unnecessary parts
$message = array();
$in_header = false;
$in_footer = false;

foreach($email AS $line) {
	$line = trim($line);

	if(strpos($line, '----+----')) {
		$in_header = empty($in_header) ? true : false;
		continue;
	}

	if($in_header || $line == '') continue;

	if($line == '--') {
		$in_footer = true;
		continue;
	}
	if($in_footer) {
		// grab the link to the ticket
		preg_match('/http[s]{0,1}:\/\/[^>]+/', $line, $link);
		$link = trim($link[0]);
		break;
	}

	if(count($message)) {
		// replace #0000 with link to ticket
		$line = preg_replace('/#(\d+)/', '<' . $trac_url . 'ticket/$1/|#$1>', $line);
		// replace r0000 with link to revision on default repository
		$line = preg_replace('/\br(\d+)\b/', '<' . $trac_url . 'changeset/$1/|r$1>', $line);

		$line = '>' . $line;
	}

	$message[] = $line;
}

if(!$message) {
	// no message
	exit (1);
}

// some formatting for the first two lines of our message
$message[0] = sprintf("*%s*\n<%s|Ticket %s>", $subject, $link, $message[0]);

// prepare payload
$message = implode("\n", $message);
$payload = array(
	'text' => $message,
);
$data = array(
	'payload' => json_encode($payload),
);


// send to slack
$ch = curl_init(INCOMING_WEBHOOK_URL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);

exit (0);
