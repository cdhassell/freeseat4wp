<?php namespace freeseat;

function civicrm_info() {
    return array
	('english_name' => 'CiviCRM Integration',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
     'category' => 'export',
	 'summary' => 'Data exchange with a civicrm installation.',
	 'details' => 'This plugin searches for the current user in a civicrm database. If found, it captures the civicrm contact_id and stores it with the booking record. It also records an activity for the civicrm contact.');
}


