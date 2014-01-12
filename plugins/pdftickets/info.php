<?php namespace freeseat;

function pdftickets_info() {
    return array
	('english_name' => 'PDF Tickets',
	 'version' => '1.0',
	 'required_fs_version' => '1.4.0',
	 'category' => 'tickets',
	 'summary' => 'Render tickets in PDF format.',
	 'details' => 'This plugin renders tickets the user booked in PDF format, so that the user may download a ticket file, after booking tickets. Also works when tickets are displayed by the remail or adminprint plugins. ');
}

