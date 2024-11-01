# WP Iletimerkezi SMS

WP Iletimerkezi SMS is a plugin to add SMS functionality to your website using the Iletimerkezi API.

[Iletimerkezi](https://www.iletimerkezi.com) is one of the best sms provider.


How does it work?
-

The plugin primarily allows a WordPress developer to extend functionality and integrate it into any type of site.

The plugin also includes functionality to directly send a text message to any number from the plugin settings page.
You can use it to SMS any of your users or just for testing purposes

Here's a list of what the plugin provides out of the box:

* Custom function to easily send SMS messages to any number (including international ones)
* Functionality to directly send a text message to any number from the plugin settings page
* Hooks to add additional tabs on the plugin settings page to allow managing all SMS related settings from the same page
* Basic logging capability to keep track of up to 100 entries
* Mobile Phone User Field added to each profile (optional)
* Shorten URLs using Google URL Shortener API (optional)

<h3>ilt_send_sms( $args )</h3>
<p>Sends a standard text message from your Iletimerkezi Sender Id when arguments are passed in an array format. Description of each array key is given below.</p>
Array Key | Type | Description
------------- | ------------- | ----
number_to | string | The mobile number that will be texted. Must be formatted as country code + 10-digit number (i.e. +905909009090).
message | string | The message that will be sent to the recipient.
sender | string | Override the Sender from settings. Must be approved at iletimerkezi.com
public_key | string | Override the Iletimerkezi public key from settings.
private_key | string | Override the Iletimerkezi private key from settings.
logging *(optional)* | integer (1 or 0) | Override the logging option set from the settings page. Requires the digit '1' to enable.
url_shorten *(optional)* | integer (1 or 0) | Override the URL shortening option set from the settings page. Requires the digit '1' to enable.

Returns an message id, if error accurs, a *WP_Error* object on failure.

<h5>Example</h5>

```php
$args = array(
    'number_to' => '+905909009090',
    'message'   => 'Hello World!',
);
ilt_send_sms( $args );
```
