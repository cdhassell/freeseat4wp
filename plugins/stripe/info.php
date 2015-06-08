<?php namespace freeseat;

function stripe_info() {
    return array
	('english_name' => 'Stripe',
	 'version' => '2.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'payment',
	 'summary' => 'Enables credit card payment with Stripe.',
	 'details' => 'Enables credit card payment with Stripe.  Enter your Stripe credentials on the FreeSeat settings page.');
}
