<?php namespace freeseat;

function bookingmap_info() {
    return array
	('english_name' => 'Booking map',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'operator',
	 'summary' => 'Display booking status on a map.',
	 'details' => 'This plugin lets the user see the state (free, booked, paid, etc) of each seat on a map.');
}

