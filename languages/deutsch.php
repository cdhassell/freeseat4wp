<?php

/** Deutscher (GERMAN) Sprach-file.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info. �bersetzung: Stefan Schuck

*/

//$FS_PATH = plugin_dir_path( __FILE__ ) . '../';
require_once ($FS_PATH . "languages/default.php");

$lang["_encoding"] = "ISO-8859-1";


$lang["access_denied"] = 'ZUGANG VERWEIGERT - Ihre Sitzung scheint abgelaufen zu sein';
$lang["acknowledge"] = 'best�tigt'; // used with check_st_update
$lang["address"] = 'Addresse';
$lang["admin"] = 'Administrative Funktionen';
$lang["admin_buy"] = 'Eintrittskarten   %1$sbuchen und kaufen%2$s';
$lang["alert"] = 'ALARM';
$lang["are-you-ready"] = '�berpr�fen Sie bitte Ihre Eingaben und klicken dann auf WEITER.';

$lang["backto"] = 'Zur�ck zur %1$s';
$lang["book"] = 'Buchen';
$lang["bookagain"] = 'Eine weitere %1$sBuchung%2$s durchf�hren';
$lang["bookid"] = 'Nummer';
$lang["book_adminonly"] = 'Keine online-Buchung mehr m�glich';
$lang["book_submit"] = 'Buchen';
$lang["booking_st"] = 'Reservierungsstatus: %1$s';
$lang["bookinglist"] = '%1$sBuchungen%2$s ansehen/�ndern (z.B. um einen Zahlungseingang zu best�tigen)';
$lang["bookingmap"] = 'Buchungs-Liste';
$lang["buy"] = 'Eintrittskarten %1$sbuchen und kaufen%2$s';

$lang["cancel"] = "Abbrechen";
$lang["cancellations"] = "Stornierungen";
$lang["cat"] = 'Kategorie';
$lang["cat_free"] = 'Ehrenkarte';
$lang["cat_normal"] = 'Normal ';
$lang["cat_reduced"] = 'Rabatt ';
$lang["ccard_failed"] = '%1$s NACHRICHT W�HREND DER KREDITKARTENABWICKLUNG\n\n\n';
$lang["ccard_partner"] = 'Die Bezahlung mit Kreditkarte ist abgesichert durch&nbsp;%1$s';
$lang["change_date"] = 'Datum �ndern';
$lang["change_pay"] = ' %1$sAdress- und Bezahlinformationen%2$s �ndern';
$lang["change_seats"] = '%1$sPlatzauswahl%2$s �ndern';
$lang["check_st_update"] = '�berpr�fen Sie, ob die folgende Liste von Buchungen %1$s werden sollen und klicken Sie dann auf "Best�tigung" unten auf der Seite';
$lang["choose_show"] = 'Bitte w�hlen Sie eine Veranstaltung';
$lang["city"] = 'Ort';
$lang["comment"] = 'Anmerkung';
$lang["confirmation"] = 'Best�tigung';
$lang["continue"] = 'Weiter';
$lang["country"] = 'Staat';
$lang["class"] = 'Kategorie';
$lang["closed"] = 'Gesperrt';
$lang["col"] = 'Platz';
$lang["create_show"] = 'Neue Veranstaltung anlegen';

$lang["date"] = 'Datum';
$lang['datesandtimes'] = 'Daten anzeigen';
$lang["date_title"] = 'Datum<br>(jjjj-mm-tt)';
$lang["day"] = 'd'; // abbreviated
$lang["days"] = 'Tage';
$lang["DELETE"] = 'GEL�SCHT'; // used in check_st_update
$lang["description"] = 'Beschreibung';
$lang["diffprice"] = 'Preiskategorien sind farblich gekennzeichnet wie unten angegeben';
$lang["disabled"] = "deaktiviert"; // for shows or payment methods
$lang["dump_csv"] = 'Datenbank im csv-Format speichern: %1$sbookings.csv%2$s';

$lang['editshows'] = 'Hinzuf�gen oder �ndern von  %1$sVeranstaltungs%2$s-informationen';
$lang["email"] = 'Email';
$lang["err_bademail"] = 'Die e-mail Addresse, die Sie angaben, scheint ung�ltig';
$lang["err_badip"] = 'Keine Zugangsberechtigung zu dieser Datei';
$lang["err_badkey"] = 'Der Zugangsschl�ssel war nicht richtig. Sie k�nnen es erneut versuchen. (Senden Sie bitte eine email an %1$s wenn Sie weiterhin keinen Erfolg haben)';
$lang["err_bookings"] = 'Fehler beim Aufrufen der Buchungen';
$lang["err_ccard_cfg"] = 'Die Bezahlung mittels Kreditkarte mu� zuerst in config.php konfiguriert werden, bevor sie genutzt werden kann.';
$lang["err_ccard_insuff"] = 'Nicht genug Geld um Platz %1$d zu bezahlen. Er kostet %4$s %2$d und Sie haben nur %4$s %3$d !';
$lang["err_ccard_mysql"] = '(Mysql) Fehler beim Protokollieren der Kreditkarten-�bertragung';
$lang["err_ccard_nomatch"] = 'push (%1$s) and pull (%2$s) do not match (using pull amount)';
$lang["err_ccard_pay"] = 'Bezahlung des Platzes %1$d konnte nicht verzeichnet werden! (�berpr�fen Sie die logs -vielleicht wurde der Platz bereits bezahlt)';
$lang["err_ccard_repay"] = 'Kreditkartenzahlung f�r Platz %1$d erhalten, der bereits bezahlt ist!';
$lang["err_ccard_toomuch"] = 'Zu viel Geld eingegangen! %3$s %1$d von %3$s %2$d ungenutzt.';
$lang["err_ccard_user"] = 'Ein Problem mit der Zahlung trat auf - Versuchen Sie es erneut oder schreiben Sie eine Mail an %1$s';
$lang["err_checkseats"] = 'Pl�tze ausw�hlen';
$lang["err_closed"] = 'Leider wurde das online-Buchungssystem f�r diese Veranstaltung gerade geschlossen.';
$lang["err_config"] = '�berpr�fen Sie die Server-Konfiguration auf: ';
$lang["err_connect"] = 'Verbindungsfehler : ';
$lang["err_cronusage"] = "Obligatorische Eingabe erwartet (System-Passwort f�r die Buchungsdatenbank)\n";
$lang["err_email"] = 'Ausgew�hlte Buchungen haben nicht alle die gleiche email (ich nehme die erste). ';
$lang["err_filetype"] = 'Falscher Datentyp, erwartet: ';
$lang["err_ic_firstname"] =    'Ausgew�hlte Buchungen haben nicht alle den gleichen Vornamen (ich nehme den ersten)';
$lang["err_ic_lastname"] =    'Ausgew�hlte Buchungen haben nicht alle den gleichen Vornamen (ich nehme den ersten)';
$lang["err_ic_payment"] = 'Ausgew�hlte Buchungen haben nicht alle die gleiche Zahlungsweise (ich nehme die erste)';
$lang["err_ic_phone"] =   'Ausgew�hlte Buchungen haben nicht alle die gleiche Telefonnummer (ich nehme die erste)';
$lang["err_ic_showid"] =  'Ausgew�hlte Buchungen sind nicht alle f�r die gleiche Veranstaltung...';
$lang["err_noaddress"] = 'F�r die Bezahlung it Kreditkarte m�ssen Sie mindestens eine g�ltige email-Adresse und die komplette Postanschrift angeben.';
$lang["err_nodates"] = 'F�r diese Veranstaltung sind keine Daten eingegeben.';
$lang["err_noname"] = 'Bitte geben Sie mindestens Ihren Namen an.';
$lang["err_noprices"] = 'Bitte geben Sie f�r mindestens eine Kategorie Preise ein.';
$lang["err_noseats"] = 'Kein Sitzplan';
$lang["err_nospec"] = 'Sie m�ssen f�r diese Veranstaltung einen Namen eingeben.';
$lang["err_notheatre"] = 'Bitte w�hlen Sie einen Sitzplan aus.';
$lang["err_occupied"] = 'Leider ist einer der Pl�tze, die Sie ausgew�hlt haben, gerade gebucht worden.';
$lang["err_paymentclosed"] = 'Online Bezahlung %1$s f�r diese Veranstaltung wurde gerade beendet.';
$lang["err_payreminddelay"] = 'Frist der Zahlungsverz�gerung mu� l�nger als die Erinnerungsfrist sein.';
$lang["err_postaltax"] = 'Preis zu hoch f�r Posteinzug';
$lang["err_price"] = 'Fehler beim Abrufen des Platz-Preises';
$lang["err_pw"] = 'Unbekannter Benutzer oder falsches Passwort. Bitte erneut versuchen.';
$lang["err_scriptauth"] = 'Anfrage an Skript %1$s verweigert.';
$lang["err_scriptconnect"] = 'Verbindung zu %1$s Skript schlug fehl.';
$lang["err_seat"] = 'Konnte nicht auf den Platz zugreifen.';
$lang["err_seatcount"] = 'Sie k�nnen nicht so viele Pl�tze gleichzeitig buchen.';
$lang["err_seatlocks"] = 'Fehler beim Sperren des Platzes';
$lang["err_session"] = 'Sie haben keine aktive Buchungssitzung (mehr). (Haben Sie in Ihrem Browser Cookies aktiviert?)';
$lang["err_setbookstatus"] = 'Fehler beim �ndern des Platz-Status.';
$lang["err_shellonly"] = 'ZUGANG VERWEIGERT - Der Zugang zu dieser Seite setzt shell access voraus.';
$lang["err_show_entry"] = 'Diese Veranstaltung kann nicht gespeichert werden bis Sie die fehlenden Angaben erg�nzt haben.';
$lang["err_showid"] = 'Ung�ltige Veranstaltungsnummer';
$lang["err_smtp"] = 'Achtung: Senen fehlgeschlagen: %1$s - Server antwortete: %2$s';
$lang["err_spectacle"] = 'Fehler beim Zugriff auf Veranstaltungsdaten';
$lang["err_spectacleid"] = 'Ung�ltige Veranstaltungs-Nummer';
$lang["expiration"] = 'Frist:';
$lang["expired"] = 'bereits abgelaufen.';

$lang["failure"] = 'KATASTROPHE';
$lang["file"] = 'Datei: '; 
$lang["filter"] = 'Veranstaltung:'; // filter form header in bookinglist
$lang["firstname"] = 'Vorname';
$lang["from"] = 'von'; // in a temporal sense : from a to b

$lang["hello"] = 'Hallo %1$s,';
$lang["hour"] = 'Std.'; // abbreviated
/* (note : this is only used for at least two seats) */
$lang["howmanyare"] = 'Wieviele von diesen %1$d Sitzen sind';

$lang["id"] = 'Nummer';
$lang['imagesrc'] = 'Speicherplatz des Bildes:';
$lang["immediately"] = 'sofort';
$lang["import"] = 'Dieses File hochladen';
$lang["in"] = 'in %1$s'; // as in "in <ten days>"
$lang["index_head"] = 'On-line Kartenbestellsystem';
$lang["intro_ccard"] = <<<EOD
 <h2>Danke f�r Ihre Bestellung.</h2>

<p class="main">Die Pl�tze sind nun auf Ihren Namen gebucht.</p>
EOD;

$lang["intro_confirm"] = 'Bitte �berpr�fen Sie Ihre Eingaben, bevor Sie endg�ltig reservieren';
$lang["intro_finish"] = 'Diese Seite ist Ihre Eintrittskarte. Bitte bringen Sie sie zur Veranstaltung mit.';
$lang["intro_params"] = <<<EOD
<h2>Zahlungsm�glichkeiten</h2>

<p class="main">
<ul><li><p>
Geben Sie hier die Zeitspannen an, in welchen die verschiedenen Zahlungsm�glichkeiten zur Verf�gung stehen (in Abh�ngigkeit vom Veranstaltungsdatum).
</p>
<li>
<p>Anzahl der <em>Minuten</em> vor Veranstaltungsbeginn.</p>
<li>
<p>�ffnungs-/Schlie�ungszeiten an der Kasse meinen hier die Zeitspanne, in welcher man die Barzahlung des Kunden erwartet (nicht die Kassen-�ffnungszeiten)</p>

<li>
<p>
Verz�gerungen im Posttransfer werden in Werktagen angezeigt. Die Spanne verl�ngert sich automatisch um Sonn- und Feiertage, wenn diese hineinfallen.</p>
</ul>
</p>

%1\$s

<h2>Erinnerungen und Stornierungen</h2>

<p class="main">Wie viele <em>Tage</em> nach der Buchung mu� ich eine Erinnerung oder Stornierung senden, je nach gew�hlter Zahlungsmethode?</p>

%2\$s

<h2>Andere Parameter</h2>

EOD;
//'

$lang["intro_remail"] = <<<EOD

<h2>Reservierung aufrufen</h2>

<p class='main'>Bitte geben Sie in das folgende Feld die mail-Adresse, die Sie zur Buchung benutzt haben, ein.<br>
Sie werden eine mail mit den Buchungsdetails zur Best�tigung erhalten.</p>

<p class='main'>Email Addresse: %1\$s</p>

<p class='Hauptseite'>(Wenn Sie keine Mail-Adresse angegeben haben oder keinen Zugriff mehr darauf haben, rufen Sie uns bitte direkt an.)</p>

EOD;

$lang["intro_remail2"] = <<<EOD

<h2>Reservierung aufrufen</h2>

<p class='main'>Falls die e-mail, die Sie erhalten haben, einen Zugangs-Code zu Ihren Tickets erhalten hat, k�nnen sie ihn nun in das folgende Feld kopieren, um Ihre Tickets auszudrucken:</p>

<p class='main'>(Achtung, das ist nicht der Reservierungs-Code!)</p>

<p class='main'>Reservierungs-Code f�r Ihre Tickets: %1\$s</p>

EOD;

$lang["intro_seats"] = 'Klicken Sie auf "Weiter" am Ende der Seite, wenn Sie Ihre Wahl getroffen haben.';
$lang["is_default"] = 'Dies ist die aktive Veranstaltung.';
$lang["is_not_default"] = 'Dies ist nicht die aktive Veranstaltung.';

$lang["lastname"] = 'Nachname';
$lang["legend"] = 'Legende: ';
$lang["link_bookinglist"] = 'Buchungsliste';
$lang["link_edit"] = 'Veranstaltungen editieren';
$lang["link_index"] = 'Startseite';
$lang["link_pay"] = 'Pers�nliche Informationen';
$lang["link_repr"] = 'Veranstaltungsliste';
$lang["link_seats"] = 'Platzauswahl';
$lang["login"] = 'System Administration (nur f�r authorisierte Personen):';
$lang["logout"] = 'Logout';

$lang["mail-anon"] = <<<EOD
Hallo,

Diese Informationen betreffen jemanden, der keine email-Adresse angegeben hat.

Falls Sie ihn (bei Bedarf) dennoch kontaktieren m�ssen, sind hier die Informationen, die er hinterlassen hat:

EOD;

/* NOTE - Assumes spectacle must be preceded by a (masculine)
 definite article. In the future we will need to integrate the article
 in the spectacle name and alter/extended it when needed (e.g. French
 de+le = du, German von+dem = vom, etc) */
$lang["mail-booked"] = <<<EOD
Danke f�r Ihre Reservierung f�r %1\$s

Hier sind noch einmal die Details Ihrer Reservierung. Bitte legen Sie diese Best�tigung nach Aufforderung bei der Einlasskontrolle der Veranstaltung vor.

EOD;

$lang["mail-cancel-however"] = <<<EOD
Wir m�ssen Sie allerdings dar�ber informieren, dass die Buchung f�r folgenden Platz annuliert wurde:
EOD;
$lang["mail-cancel-however-p"] = <<<EOD
Wir m�ssen Sie allerdings dar�ber informieren, dass die Buchung f�r die folgenden Pl�tze annuliert wurde:
EOD;
$lang["mail-cancel"] = <<<EOD
Hiermit informieren wir Sie, dass die Buchung f�r folgenden Platz annuliert wurde:
EOD;
$lang["mail-cancel-p"] = <<<EOD
Hiermit informieren wie Sie, dass die Buchung f�r die folgenden Pl�tze annuliert wurde:
EOD;

$lang["mail-gotmoney"] = 'Wir erhielten Ihre Zahlung f�r folgenden Platz:';
$lang["mail-gotmoney-p"] = 'Wir erhielten Ihre Zahlung f�r folgende Pl�tze';

$lang["mail-heywakeup"] = <<<EOD

Wir haben bislang noch keine Zahlung f�r den folgenden Platz erhalten:

%1\$s
Falls sich Ihre Zahlung mit dieser Nachricht �berschnitten hat, ignorieren Sie bitte dieses Schreiben.

Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail. Sollten wir nichts von Ihnen h�ren, wird Ihre Buchung in den n�chsten Tagen durch uns storniert.

EOD;

$lang["mail-heywakeup-p"] = <<<EOD


Wir haben bislang noch keine Zahlung f�r die folgenden Pl�tze erhalten:

%1\$s
Falls sich Ihre Zahlung mit dieser Nachricht �berschnitten hat, ignorieren Sie bitte dieses Schreiben.

Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail. Sollten wir nichts von Ihnen h�ren, wird Ihre Buchung in den n�chsten Tagen durch uns storniert.
EOD;

$lang["mail-notconfirmed"] = <<<EOD
Ihre Buchung wurde noch nicht best�tigt. Sie ist erst bei Zahlungseingang g�ltig. 
EOD;

// for one seat
$lang["mail-notdeleted"] = 'Wir haben folgende Platzreservierung erhalten:';
// for more than one seat
$lang["mail-notdeleted-p"] = 'Wir haben folgende Platzreservierung erhalten';
$lang["mail-notpaid"] = 'Folgender Platz wurde gebucht, aber wir haben bislang noch keine Zahlung erhalten:';
$lang["mail-notpaid-p"] = 'Folgende Pl�tze wurden gebucht, aber wir haben bislang noch keine Zahlung erhalten:';
$lang["mail-remail"] = <<<EOD
Gem�� Ihrer Anfrage auf der %1\$s website erhalten Sie hier eine Zusammenfassung der Buchungen f�r diese email-Adresse.


Zugangs-Schl�ssel f�r Ihre Tickets : %2\$s

EOD;

$lang["mail-reminder-p"] = <<<EOD
Dar�ber hinaus m�chten wir Sie daran erinnern, dass folgende Pl�tze noch bezahlt werden m�ssen:

%1\$s
Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail.

EOD;

$lang["mail-reminder"] = <<<EOD
Dar�ber hinaus m�chten wir Sie daran erinnern, dass folgender Platz noch bezahlt werden mu�:

%1\$s
Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail.

EOD;

$lang["mail-secondmail"] = <<<EOD
Sie werden eine weitere Mail erhalten, wenn Ihre Zahlung bei uns eingegangen ist.
EOD;

$lang["mail-spammer"] = <<<EOD
Hallo,

Jemand (vielleicht Sie?) forderte eine Buchungs�bersicht an f�r diese Mail-Adresse (%3\$s) f�r %1\$s
(%2\$s).

Wir haben jedoch unter dieser Mail-Adresse keine Buchungen vorliegen. Das kann dreierlei bedeuten:
* Sie benutzten f�r Ihre Buchung eine andere email-Adresse.
* Sie haben einen Platz gebucht, aber die Buchung wurde durch uns storniert. Sie sollten eine Mail �ber die Stornierung erhalten haben.
* Ein Witzbold versucht, Ihr email-Postfach zu f�llen und glaubt, dabei unbemerkt bleiben zu k�nnen.

Falls Sie Fragen haben, antworten Sie bitte auf diese Mail.

EOD;
// following always plural
$lang["mail-summary-p"] = 'Folgende Platz-Buchungen sind derzeit best�tigt:';

$lang["mail-thankee"] = <<<EOD
Herzlichen Dank f�r Ihre Buchung. Wir hoffen, dass Ihnen unsere Veranstaltung gefallen wird.

EOD;

$lang["mail-oops"] = <<<EOD
Falls Sie der Meinung sind, dass ein Fehler vorliegt, antworten Sie bitte baldm�glichst auf diese Mail, damit wir Ihre Buchung reaktivieren k�nnen.
EOD;
    //'

$lang["mail-sent"] = 'Eine email mit dem Inhalt dieser Seite wurde gerade an Sie versendet.';
$lang["mail-sub-booked"] = 'Ihre Buchung';
$lang["mail-sub-cancel"] = 'Buchungsstornierungen';
$lang["mail-sub-gotmoney"] = 'Zahlungsbest�tigungen';
$lang["mail-sub-heywakeup"] = 'Erinnerung';
$lang["mail-sub-remail"] = 'Buchungs�bersicht';
$lang["make_default"] = 'Diese Veranstaltung zur aktiven machen. Es kann immer nur eine Veranstaltung als aktiv gekennzeichnet werden.';
$lang['make_payment'] = 'bezahlen';
$lang["max_seats"] = 'Maximale Anzahl der Pl�tze, die in einer Sitzung gebucht werden k�nnen';
$lang["minute"] = 'min'; // abbreviated
$lang["minutes"] = 'Minuten';
$lang["months"] = array(1=>"Januar","Februar","M�rz","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");

$lang["name"] = 'Name';
$lang["new_spectacle"] = 'Eine neue Veranstaltung anlegen';
$lang["ninvite"] = 'Ehrenkarten';
// following written on tickets for non-numbered seats.
$lang["nnseat"] = 'Unnummerierte Pl�tze';
$lang["nnseat-avail"] = 'Ein %1sunnummerierter Platz ist noch verf�gbar. <br>Geben Sie hier 1 (eins) ein, wenn Sie ihn buchen wollen: ';
$lang["nnseat-header"] = 'Unnummerierte Tickets';
$lang["nnseats-avail"] = 'Es sind noch %1$s %2$sPl�tze verf�gbar. <br>Geben Sie hier die Anzahl der Pl�tze, die Sie buchen wollen, ein: ';
$lang["nocancellations"] = 'keine automatische Stornierung';
$lang["noimage"] = 'Keine Bild-Datei';
$lang["none"] = 'keine';
$lang["noreminders"] = 'keine Erinnerungen';
$lang["notes"] = 'Notizen';
$lang["notes-changed"] = 'Notizen ge�ndert f�r 1 Reservierung';
$lang["notes-changed-p"] = 'Notizen ge�ndert f�r %1$d Reservierungen';
$lang["nreduced"] = 'Zum erm��igten Preis';

$lang["orderby"] = 'Sortieren nach %1$s';

$lang["panic"] = <<<EOD
<h2>BUCHUNG NICHT ERFOLGREICH</h2>
<p class='main'>Der Systemadministrator wurde informiert. Er wird das Problem baldm�glichst beheben.</p>

<p class='main'>Bitte versuchen Sie es in ein paar Stunden noch einmal.</p>

<p class='main'>Wir entschuldigen uns f�r dieses Problem und bedanken uns f�r Ihre Geduld.</p>
EOD;

$lang["params"] = '%1$sSystemparameter%2$s �ndern';
$lang["pay_cash"] = 'Barzahlung';
$lang["pay_ccard"] = 'mit Kreditkarte';
$lang["pay_other"] = 'andere';
$lang["pay_postal"] = '�berweisung';
$lang["payinfo_cash"] = <<<EOD
Tickets m�ssen bis 30 min. vor Veranstaltungsbeginn bezahlt werden, anderenfalls gehen sie wieder in den freien Verkauf.

EOD;
$lang["payinfo_ccard"] = <<<EOD
Wir haben bislang noch keinen Zahlungseingang von Ihnen erhalten. Sollte die Zahlung nicht %1\$d Tage vor Beginn der 
Veranstaltung eingegangen sein, gehen die Karten wieder in den freien Verkauf.

EOD;
//'
$lang["payinfo_postal"] = <<<EOD
Die Gesamtsumme muss innerhalb von %2\$d Werktagen auf unserem Konto %1\$s eingegangen sein, anderenfalls gehen die Karten wieder in den freien Verkauf.

EOD;
//'

$lang["paybutton"] = 'Bitte klicken Sie auf "Weiter", um mit der Bezahlung fortzufahren:&nbsp;%1$sWeiter%2$s';
$lang["payment"] = 'Bezahlung:';
$lang['payment_received'] = 'Wir haben Ihre Zahlung erhalten.  Danke sehr!';
$lang['paypal_id'] = 'PayPal Transaktions-ID: ';
$lang['paypal_lastchance'] = "Wir sind nun bereit, die Zahlung abzuschlie�en.  Nachdem Sie auf den unten stehenden Button geklickt haben, werden Sie zusammen mit Ihren Informationen zum Ticketkauf auf die Paypal-Seite weitergeleitet. Ist die Zahlung abgeschlossen, werden Sie wieder auf diese Seite zur�ck geleitet und Ihre Zahlung registriert.  Ihre Kreditkarteninformationen werden durch das Paypal Sicherheitssystem verschl�sselt.";
$land["paypal_purchase"] = 'Ticket-Kauf mit PayPal';
$lang["phone"] = 'Telefon';
$lang['please_wait'] = 'Ihre Daten werden �bertragen . . .  bitte warten';
$lang["postal tax"] = 'Einzahlungsgeb�hr';
$lang["postalcode"] = 'Postleitzahl';
$lang["poweredby"] = 'Powered by %1$s';
$lang["price"] = 'Preis';
$lang["price_discount"] = 'erm��igter Preis ';
$lang['prices']  = 'Kartenpreis';
$lang["print_entries"] = '%1$sDrucken%2$s ausgew�hlter Eintr�ge';

$lang["rebook"] = 'Neue Buchung mit den gew�hlten Eintr�gen als Vorlage: %1$sBuchung starten%2$s';
$lang["rebook-info"] = 'Um gel�schte Buchungen zu reaktivieren, bitte zuerst den Filter "Gel�scht" oben links auf der Seite aktivieren';
$lang["remail"] = 'Haben Sie Ihre Eintrittskarte verloren? Mit dem folgenden Link bekommen Sie sie zur�ck %1$sBuchung aufrufen%2$s';
$lang["reminders"] = 'Erinnerungen';
$lang["reqd_info"] = <<<EOD
Sie m�ssen auf jeden Fall einen Namen angeben.
Wenn Sie mit Kreditkarte bezahlen, m�ssen Sie dar�ber hinaus eine g�ltige email-Adresse angeben.
EOD;
$lang["reserved-header"] = 'Reservierte Platzkarten';
$lang["row"] = 'Reihe';

$lang["sameprice"] = 'Preise f�r alle Kategorien gleich';
$lang["save"] = 'Speichern';
$lang["seat_free"] = 'Freier<br>Platz:';
$lang["seat_occupied"] = 'Besetzter<br>Platz:';
$lang["seats"] = 'Pl�tze';
$lang["seats_booked"] = 'Gebuchte Pl�tze';
$lang["seeasalist"] = '%1$sListenansicht%2$s';
$lang["seeasamap"] = 'Der folgende link zeigt die Buchungen dieser Veranstaltung als&nbsp;: %1$sSitzplan%2$s';
$lang["select"] = 'Ausw�hlen';
$lang["select_payment"] = 'Bitte w�hlen Sie eine Zahlweise:';
$lang["selected_1"] = '1 Platz ausgew�hlt';
$lang["selected_n"] = '%1$d Pl�tze ausgew�hlt';
$lang["sentto"] = 'Nachricht gesendet an: %1$s';
$lang["set_status_to"] = 'Ausgew�hlte Eintr�ge werden: ';
$lang["show_any"] = 'Alle Veranstaltungen';
$lang["show_info"] = '%1$s um %2$s, %3$s'; // date, time, location
$lang["show_name"] = 'Name der Veranstaltung';
$lang["show_not_stored"] = 'Ihre �nderungen konnten nicht gespeichert werden, bitte kontaktieren Sie Ihren Systemadministrator.';
$lang["show_stored"] = 'Ihre �nderungen wurden gespeichert.';
$lang["showlist"] = 'Auff�hrungen von %1$s';
$lang["spectacle_name"] = 'W�hlen Sie eine Veranstaltung aus';
$lang["state"] = 'Status'; // in the sense of status, not in the sense
			  // of a country's part
$lang["st_any"] = 'Jeder Status';
$lang["st_booked"] = 'Gebucht';
$lang["st_deleted"] = 'Gel�scht';
$lang["st_disabled"] = 'Deaktiviert';
$lang["st_free"] = 'Frei';
$lang["st_locked"] = 'In Bearbeitung';
$lang["st_notdeleted"] = 'Nicht gel�scht';
$lang["st_paid"] = 'Bezahlt';
$lang["st_shaken"] = 'Erinnerung gesendet';
$lang["st_tobepaid"] = 'Zu bezahlen';
$lang["stage"] = 'B�hne';
$lang["summary"] = 'Zusammenfassung';

$lang["theatre_name"] = 'Name des Sitzplans';
$lang["time"] = 'Zeit';
$lang["time_title"] = 'Zeit<br>(hh:mm)';
$lang["timestamp"] = 'Gebucht am';
$lang["title_mconfirm"] = 'Best�tige Veranstaltungs-Details';
$lang["title_maint"] = 'Auff�hrungen hinzuf�gen oder �ndern';
$lang["to"] = 'bis'; // in a temporal sense : from a to b
$lang["total"] = 'Gesamt';

$lang["update"] = 'aktualisieren';
$lang['us_state'] = 'Bundesstaat (nur USA)';

$lang["warn_badlogin"] = 'Verbindungsversuch durch illegalen Client';
$lang["warn_bookings"] = 'Bitte beachten Sie: Sie sind dabei, das Datum, die Zeit oder die Preise f�r eine Veranstaltung zu �ndern, f�r die schon Karten verkauft sind. Sie sollten die K�ufer dieser Karten �ber Ihre �nderungen informieren.  Falls Sie den Preis �ndern, k�nnte es sein, dass bereits Tickets zu anderen Preisen verkauft worden sind, was zu Verwirrung f�hren kann.  Bitte fahren Sie mit Bedacht fort.';
$lang["warn_close_in_1"] = 'Achtung, on-line Buchungssystem f�r diese Veranstaltung schlie�t in einer Minute';
$lang["warn_close_in_n"] = 'Achtung, on-line Buchungssystem f�r diese Veranstaltung schlie�t in %1$d Minuten';
$lang["warn-nocontact"] = 'Achtung! Sie haben keine Kontaktdaten eingegeben. Wir werden daher diese Buchung nicht abschlie�end best�tigen k�nnen!';
$lang["warn-nomail"] = 'Achtung! Sie haben keine email-Adresse eingegeben. Wir werden Sie daher nicht �ber den Stand der Buchung informieren k�nnen.';
$lang["warn-nomatch"] = 'Nichts gefunden'; // no matching bookings
$lang["warn-nonsensicalcat"] = 'Achtung, Sie haben mehr Pl�tze zum reduizierten Preis eingegeben als Sie Pl�tze ausgew�hlt haben!';
$lang["warn-nonsensicalcat-admin"] = 'Achtung, die Anzahl der Ehrenkarten plus die Anzahl der Karten zum erm��igten Tarif ist gr��er als die Anzahl der ausgew�hlten Pl�tze';
$lang['warn_paypal_confirm'] = 'Wir konnten Ihre Zahlung durch Paypal nicht best�tigen.  Bitte kontaktieren Sie das Veranstaltungsb�ro, um die Zahlung best�tigen zu lassen.';
$lang['warn_process_payment'] = 'Es gab ein Problem bei der abschlie�enden �bermittlung Ihrer Zahlung.  Bitte kontaktieren Sie das Veranstaltungsb�ro, um die Zahlung best�tigen zu lassen';
$lang["warn_show_confirm"] = 'Bitte �berpr�fen Sie Ihre Eingaben. Zum �ndern klicken Sie auf "�ndern", zum Speichern auf "Speichern"';
$lang["warn_spectacle"] = 'Bitte beachten Sie, dass Sie den Bestuhlungsplan eines Veranstaltungsortes nicht mehr �ndern k�nnen, nachdem eine Auff�hrung angelegt wurde.';
$lang["we_accept"] = "Wir akzeptieren"; // credit card logos come after that
$lang["weekdays"] = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Sonnabend");
$lang["workingdays"] = 'Werktage';

$lang["youare"] = 'Ihre Angaben:';

$lang["zoneextra"] = ''; // intentionally left blank

$lang["err_notickets"] = 'Bitte w�hlen Sie aus, wie Sie das Ticket zugestellt bekommen wollen.';
  $lang["intro_finish_notickets"] = 'Danke f�r Ihre Buchung. Anschlie�end finden Sie eine �bersicht �ber Ihre Buchung';
  $lang["mark_as_sent"] = 'als versendet markieren';
  $lang["pay_snail"] = 'per Brief';
  $lang["reqd_info_all"] = 'Bitte alle Felder ausf�llen';
  $lang["st_sent"] = 'gesendet';
  $lang["tickets"] = 'Ticket Versand: ';


/** add "at the" before the given noun. This is used with theatre
names (We have a problem in case we need to know if $w is masculine or
feminine or whatever - so far everything has been masculine so won't
extend the function until need appears :-) **/
function lang_at_the($w) {
  return "in $w";
}
