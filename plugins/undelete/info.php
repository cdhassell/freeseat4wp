<?php

function undelete_info() {
    return array
	('english_name' => 'Undelete',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'operator',
	 'summary' => 'Allows the admin to restore deleted bookings.',
	 'details' => 'This plugin lets the admin use deleted bookings as a "template" for new bookings, essentially acting as an "undelete". If the seats have been booked in between, the admin will have to follow the usual seat selection process. ');
}

