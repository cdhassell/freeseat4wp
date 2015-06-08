<?php namespace freeseat;

require_once (FS_PATH . "plugins/stripe/languages/default.php");

$lang["stripe_failure_page"] = <<<EOD
<h3>Le paiement n'a malheureusement pas pu être effectué.</h3>
<p class='main'>Vous pouvez réessayer, ou contacter le bureau des réservations pour assistance.</p>
<p class='main'>Merci pour votre patience.</p><br>
<a href='%1\$s'>Cliquez ici pour réessayer</a>.
EOD;


