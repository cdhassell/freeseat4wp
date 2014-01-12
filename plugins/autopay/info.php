<?php namespace freeseat;

function autopay_info() {
    return array
	('english_name' => 'Autopay',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'operator',
	 'summary' => 'Lets the admin mark tickets as paid at time of booking',
	 'details' => 'On the confirm page, *if the admin selected the "other" payment method*, adds a checkbox that permits marking immediately tickets as paid');
}

