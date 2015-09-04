# PHP TracSlack
Sends Trac's ticket email notifications to Slack

## Installation

1. Download and save `tracslack.php` to your server's `$HOME` directory
2. Make `tracslack.php` executable by doing a `chmod +x tracslack.php`
3. Create an email forwarder that pipes to `|/path/to/tracslack.php`
4. Edit `trac.ini` and set `smtp_always_cc` to whatever email forwarder you created

## Configuration

1. Create an Incoming Webhook integration in Slack
2. Edit `tracslack.php` and
  * set `INCOMING_WEBHOOK_URL` to the Incoming Webhook URL provided by Slack
  * set `TRAC_URL` to your Trac Project's URL

## Meta

Maintained by Mike Lopez [@easterncoder](https://github.com/easterncoder)

_Inspired by https://github.com/grexi/snippets/tree/master/tracslack_
