<?php
function pdt_info() {
    return array
	('english_name' => 'Paypal PDT',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
   'category' => 'payment',
	 'summary' => 'Enables Paypal Payment Data Transfer authentication.',
	 'details' => 'This script will check the Payment Data Transfer (PDT) returned by PayPal for a valid transaction and decide whether to print tickets based on the response.  A notice of failure is displayed if not successful, and the user has the option to try again. Failures are logged but no other action is taken. PayPal requires that Auto return must be enabled in the PayPal account, so there will not be a button to click for returning to our site after payment.  Requires that Payment Data Return and Automatic Return are set to ON in your PayPal account, and that you set $PDT_auth_token in config.php.  Depends on having the paypal plugin enabled (obviously).');
}
