<?php namespace freeseat;

function UScleanup_info() {
    return array
	('english_name' => 'UScleanup',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
   'category' => 'operator',
	 'summary' => 'Formats user-entered data to follow US standards.',
	 'details' => 'This plugin attempts to clean up the user-entered name, address and telephone number entered for a booking.  For the name, it will capitalize the first letter of each word.  For the phone number, it will attempt to use the format "(xxx) xxx-xxxx".  If a default area code is specified by the administrator, then it will add that area code to any phone numbers that lack an area code.  For the address, it will attempt to verify the address against the US Postal Service address database.  This requires a user ID for the USPS address verification system.  See  https://www.usps.com/business/web-tools-apis/technical-documentation.htm and read carefully the procedures for registration and use of the USPS API.  If the address check fails, the address will remain unchanged.  If it succeeds, the address will be returned in all caps with the correct zip code included.');
}

