<?php

/*
Wordpress mailer function
*/


function send_message( $from, $to, $subject, $body, $attachment = NULL ) {
	global $smtp_server, $sender_name, $unsecure_login, $lang;

	if ($unsecure_login) { // on dev system, don't send real emails ...
		echo "<div class='dontprint'><pre>From: $from\n";
		echo "To: $to\n";
		echo "Subject: $subject\n\n";
		echo $body;
		echo "EOD</pre></div>";
		return true;
	}
	$headers[] = "From: $sender_name <$from>";
	$ok = wp_mail( $to, $subject, $body, $headers, $attachment );
	sys_log("Mail: from=$from sender=$sender_name to=$to");
	if( !$ok )  {
		kaboom(sprintf($lang["err_smtp"],$to, $ok ));
		return false;
	}
	return true;
}


