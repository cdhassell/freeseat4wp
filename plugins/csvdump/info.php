<?php

function csvdump_info() {
    return array
	('english_name' => 'CSV Dump',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'export',
	 'summary' => 'Allows the admin to export bookings in csv format.',
	 'details' => 'Displays a page in the admin menu allowing the user to download a file with the contents of the booking table.  The file will be in CSV (comma separated values) format so it can be loaded in a spreadsheet.');
}

