<?php namespace freeseat;


function castpw_info() {
    return array
	('english_name' => 'Cast Password Access',
	 'version' => '1.0', 
	 'required_fs_version' => '1.4.0', 
	 'category' => 'access',
	 'summary' => 'Allow user access to disabled shows with a password.',
	 'details' => 'This plugin allows access to disabled shows with a password. The use case is that the theatre wants to open sales of tickets to some favored group (cast members and their families) before opening sales to the general public. The admin user can create the spectacle and disable all shows in repr.php. He can then add a password and give it to those who should have access. Later, the password can be removed to allow sales to the general public.');
}


