<?php namespace freeseat;

function login_info() {
    return array
	('english_name' => 'User Login',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'operator',
	 'summary' => 'Makes use of WordPress user login credentials.',
	 'details' => 'This plugin requires the user to log in to the WordPress site, and stores his name, address and phone number in the database.  When the user logs in again, it will retrieve his user details and pre-fill the purchase screen.  The user may then edit the data before submitting it.  Once submitted, the database is updated with the latest information. This simplifies the purchase process for the user.');
}
