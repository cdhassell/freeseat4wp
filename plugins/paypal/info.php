<?php namespace freeseat;

function paypal_info() {
    return array
	('english_name' => 'Paypal',
	 'version' => '2.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'payment',
	 'summary' => 'Enables credit card payment with paypal.',
	 'details' => 'Enables credit card payment with paypal. Uses the PayPal API class released by Andrew Angell on www.angelleye.com.  Enter your PayPal credentials on the FreeSeat settings page.');
}
