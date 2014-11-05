<?php namespace freeseat;

function paypalpro_info() {
    return array
	('english_name' => 'Paypal Pro',
	 'version' => '2.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'payment',
	 'summary' => 'Enables credit card payment with PayPal Pro.',
	 'details' => 'Enables credit card payment with PayPal Pro.  This allows either Direct payments without leaving the website, or Express payments.  Enter your PayPal credentials on the FreeSeat settings page.');
}
