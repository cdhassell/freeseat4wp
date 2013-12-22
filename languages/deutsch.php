<?php

/** Deutscher (GERMAN) Sprach-file.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info. Übersetzung: Stefan Schuck

*/

//$FS_PATH = plugin_dir_path( __FILE__ ) . '../';
require_once ($FS_PATH . "languages/default.php");

$lang["_encoding"] = "ISO-8859-1";


$lang["access_denied"] = 'ZUGANG VERWEIGERT - Ihre Sitzung scheint abgelaufen zu sein';
$lang["acknowledge"] = 'bestätigt'; // used with check_st_update
$lang["address"] = 'Addresse';
$lang["admin"] = 'Administrative Funktionen';
$lang["admin_buy"] = 'Eintrittskarten   %1$sbuchen und kaufen%2$s';
$lang["alert"] = 'ALARM';
$lang["are-you-ready"] = 'Überprüfen Sie bitte Ihre Eingaben und klicken dann auf WEITER.';

$lang["backto"] = 'Zurück zur %1$s';
$lang["book"] = 'Buchen';
$lang["bookagain"] = 'Eine weitere %1$sBuchung%2$s durchführen';
$lang["bookid"] = 'Nummer';
$lang["book_adminonly"] = 'Keine online-Buchung mehr möglich';
$lang["book_submit"] = 'Buchen';
$lang["booking_st"] = 'Reservierungsstatus: %1$s';
$lang["bookinglist"] = '%1$sBuchungen%2$s ansehen/ändern (z.B. um einen Zahlungseingang zu bestätigen)';
$lang["bookingmap"] = 'Buchungs-Liste';
$lang["buy"] = 'Eintrittskarten %1$sbuchen und kaufen%2$s';

$lang["cancel"] = "Abbrechen";
$lang["cancellations"] = "Stornierungen";
$lang["cat"] = 'Kategorie';
$lang["cat_free"] = 'Ehrenkarte';
$lang["cat_normal"] = 'Normal ';
$lang["cat_reduced"] = 'Rabatt ';
$lang["ccard_failed"] = '%1$s NACHRICHT WÄHREND DER KREDITKARTENABWICKLUNG\n\n\n';
$lang["ccard_partner"] = 'Die Bezahlung mit Kreditkarte ist abgesichert durch&nbsp;%1$s';
$lang["change_date"] = 'Datum ändern';
$lang["change_pay"] = ' %1$sAdress- und Bezahlinformationen%2$s ändern';
$lang["change_seats"] = '%1$sPlatzauswahl%2$s ändern';
$lang["check_st_update"] = 'Überprüfen Sie, ob die folgende Liste von Buchungen %1$s werden sollen und klicken Sie dann auf "Bestätigung" unten auf der Seite';
$lang["choose_show"] = 'Bitte wählen Sie eine Veranstaltung';
$lang["city"] = 'Ort';
$lang["comment"] = 'Anmerkung';
$lang["confirmation"] = 'Bestätigung';
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
$lang["DELETE"] = 'GELÖSCHT'; // used in check_st_update
$lang["description"] = 'Beschreibung';
$lang["diffprice"] = 'Preiskategorien sind farblich gekennzeichnet wie unten angegeben';
$lang["disabled"] = "deaktiviert"; // for shows or payment methods
$lang["dump_csv"] = 'Datenbank im csv-Format speichern: %1$sbookings.csv%2$s';

$lang['editshows'] = 'Hinzufügen oder ändern von  %1$sVeranstaltungs%2$s-informationen';
$lang["email"] = 'Email';
$lang["err_bademail"] = 'Die e-mail Addresse, die Sie angaben, scheint ungültig';
$lang["err_badip"] = 'Keine Zugangsberechtigung zu dieser Datei';
$lang["err_badkey"] = 'Der Zugangsschlüssel war nicht richtig. Sie können es erneut versuchen. (Senden Sie bitte eine email an %1$s wenn Sie weiterhin keinen Erfolg haben)';
$lang["err_bookings"] = 'Fehler beim Aufrufen der Buchungen';
$lang["err_ccard_cfg"] = 'Die Bezahlung mittels Kreditkarte muß zuerst in config.php konfiguriert werden, bevor sie genutzt werden kann.';
$lang["err_ccard_insuff"] = 'Nicht genug Geld um Platz %1$d zu bezahlen. Er kostet %4$s %2$d und Sie haben nur %4$s %3$d !';
$lang["err_ccard_mysql"] = '(Mysql) Fehler beim Protokollieren der Kreditkarten-Übertragung';
$lang["err_ccard_nomatch"] = 'push (%1$s) and pull (%2$s) do not match (using pull amount)';
$lang["err_ccard_pay"] = 'Bezahlung des Platzes %1$d konnte nicht verzeichnet werden! (Überprüfen Sie die logs -vielleicht wurde der Platz bereits bezahlt)';
$lang["err_ccard_repay"] = 'Kreditkartenzahlung für Platz %1$d erhalten, der bereits bezahlt ist!';
$lang["err_ccard_toomuch"] = 'Zu viel Geld eingegangen! %3$s %1$d von %3$s %2$d ungenutzt.';
$lang["err_ccard_user"] = 'Ein Problem mit der Zahlung trat auf - Versuchen Sie es erneut oder schreiben Sie eine Mail an %1$s';
$lang["err_checkseats"] = 'Plätze auswählen';
$lang["err_closed"] = 'Leider wurde das online-Buchungssystem für diese Veranstaltung gerade geschlossen.';
$lang["err_config"] = 'Überprüfen Sie die Server-Konfiguration auf: ';
$lang["err_connect"] = 'Verbindungsfehler : ';
$lang["err_cronusage"] = "Obligatorische Eingabe erwartet (System-Passwort für die Buchungsdatenbank)\n";
$lang["err_email"] = 'Ausgewählte Buchungen haben nicht alle die gleiche email (ich nehme die erste). ';
$lang["err_filetype"] = 'Falscher Datentyp, erwartet: ';
$lang["err_ic_firstname"] =    'Ausgewählte Buchungen haben nicht alle den gleichen Vornamen (ich nehme den ersten)';
$lang["err_ic_lastname"] =    'Ausgewählte Buchungen haben nicht alle den gleichen Vornamen (ich nehme den ersten)';
$lang["err_ic_payment"] = 'Ausgewählte Buchungen haben nicht alle die gleiche Zahlungsweise (ich nehme die erste)';
$lang["err_ic_phone"] =   'Ausgewählte Buchungen haben nicht alle die gleiche Telefonnummer (ich nehme die erste)';
$lang["err_ic_showid"] =  'Ausgewählte Buchungen sind nicht alle für die gleiche Veranstaltung...';
$lang["err_noaddress"] = 'Für die Bezahlung it Kreditkarte müssen Sie mindestens eine gültige email-Adresse und die komplette Postanschrift angeben.';
$lang["err_nodates"] = 'Für diese Veranstaltung sind keine Daten eingegeben.';
$lang["err_noname"] = 'Bitte geben Sie mindestens Ihren Namen an.';
$lang["err_noprices"] = 'Bitte geben Sie für mindestens eine Kategorie Preise ein.';
$lang["err_noseats"] = 'Kein Sitzplan';
$lang["err_nospec"] = 'Sie müssen für diese Veranstaltung einen Namen eingeben.';
$lang["err_notheatre"] = 'Bitte wählen Sie einen Sitzplan aus.';
$lang["err_occupied"] = 'Leider ist einer der Plätze, die Sie ausgewählt haben, gerade gebucht worden.';
$lang["err_paymentclosed"] = 'Online Bezahlung %1$s für diese Veranstaltung wurde gerade beendet.';
$lang["err_payreminddelay"] = 'Frist der Zahlungsverzögerung muß länger als die Erinnerungsfrist sein.';
$lang["err_postaltax"] = 'Preis zu hoch für Posteinzug';
$lang["err_price"] = 'Fehler beim Abrufen des Platz-Preises';
$lang["err_pw"] = 'Unbekannter Benutzer oder falsches Passwort. Bitte erneut versuchen.';
$lang["err_scriptauth"] = 'Anfrage an Skript %1$s verweigert.';
$lang["err_scriptconnect"] = 'Verbindung zu %1$s Skript schlug fehl.';
$lang["err_seat"] = 'Konnte nicht auf den Platz zugreifen.';
$lang["err_seatcount"] = 'Sie können nicht so viele Plätze gleichzeitig buchen.';
$lang["err_seatlocks"] = 'Fehler beim Sperren des Platzes';
$lang["err_session"] = 'Sie haben keine aktive Buchungssitzung (mehr). (Haben Sie in Ihrem Browser Cookies aktiviert?)';
$lang["err_setbookstatus"] = 'Fehler beim Ändern des Platz-Status.';
$lang["err_shellonly"] = 'ZUGANG VERWEIGERT - Der Zugang zu dieser Seite setzt shell access voraus.';
$lang["err_show_entry"] = 'Diese Veranstaltung kann nicht gespeichert werden bis Sie die fehlenden Angaben ergänzt haben.';
$lang["err_showid"] = 'Ungültige Veranstaltungsnummer';
$lang["err_smtp"] = 'Achtung: Senen fehlgeschlagen: %1$s - Server antwortete: %2$s';
$lang["err_spectacle"] = 'Fehler beim Zugriff auf Veranstaltungsdaten';
$lang["err_spectacleid"] = 'Ungültige Veranstaltungs-Nummer';
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
 <h2>Danke für Ihre Bestellung.</h2>

<p class="main">Die Plätze sind nun auf Ihren Namen gebucht.</p>
EOD;

$lang["intro_confirm"] = 'Bitte überprüfen Sie Ihre Eingaben, bevor Sie endgültig reservieren';
$lang["intro_finish"] = 'Diese Seite ist Ihre Eintrittskarte. Bitte bringen Sie sie zur Veranstaltung mit.';
$lang["intro_params"] = <<<EOD
<h2>Zahlungsmöglichkeiten</h2>

<p class="main">
<ul><li><p>
Geben Sie hier die Zeitspannen an, in welchen die verschiedenen Zahlungsmöglichkeiten zur Verfügung stehen (in Abhängigkeit vom Veranstaltungsdatum).
</p>
<li>
<p>Anzahl der <em>Minuten</em> vor Veranstaltungsbeginn.</p>
<li>
<p>Öffnungs-/Schließungszeiten an der Kasse meinen hier die Zeitspanne, in welcher man die Barzahlung des Kunden erwartet (nicht die Kassen-Öffnungszeiten)</p>

<li>
<p>
Verzögerungen im Posttransfer werden in Werktagen angezeigt. Die Spanne verlängert sich automatisch um Sonn- und Feiertage, wenn diese hineinfallen.</p>
</ul>
</p>

%1\$s

<h2>Erinnerungen und Stornierungen</h2>

<p class="main">Wie viele <em>Tage</em> nach der Buchung muß ich eine Erinnerung oder Stornierung senden, je nach gewählter Zahlungsmethode?</p>

%2\$s

<h2>Andere Parameter</h2>

EOD;
//'

$lang["intro_remail"] = <<<EOD

<h2>Reservierung aufrufen</h2>

<p class='main'>Bitte geben Sie in das folgende Feld die mail-Adresse, die Sie zur Buchung benutzt haben, ein.<br>
Sie werden eine mail mit den Buchungsdetails zur Bestätigung erhalten.</p>

<p class='main'>Email Addresse: %1\$s</p>

<p class='Hauptseite'>(Wenn Sie keine Mail-Adresse angegeben haben oder keinen Zugriff mehr darauf haben, rufen Sie uns bitte direkt an.)</p>

EOD;

$lang["intro_remail2"] = <<<EOD

<h2>Reservierung aufrufen</h2>

<p class='main'>Falls die e-mail, die Sie erhalten haben, einen Zugangs-Code zu Ihren Tickets erhalten hat, können sie ihn nun in das folgende Feld kopieren, um Ihre Tickets auszudrucken:</p>

<p class='main'>(Achtung, das ist nicht der Reservierungs-Code!)</p>

<p class='main'>Reservierungs-Code für Ihre Tickets: %1\$s</p>

EOD;

$lang["intro_seats"] = 'Klicken Sie auf "Weiter" am Ende der Seite, wenn Sie Ihre Wahl getroffen haben.';
$lang["is_default"] = 'Dies ist die aktive Veranstaltung.';
$lang["is_not_default"] = 'Dies ist nicht die aktive Veranstaltung.';

$lang["lastname"] = 'Nachname';
$lang["legend"] = 'Legende: ';
$lang["link_bookinglist"] = 'Buchungsliste';
$lang["link_edit"] = 'Veranstaltungen editieren';
$lang["link_index"] = 'Startseite';
$lang["link_pay"] = 'Persönliche Informationen';
$lang["link_repr"] = 'Veranstaltungsliste';
$lang["link_seats"] = 'Platzauswahl';
$lang["login"] = 'System Administration (nur für authorisierte Personen):';
$lang["logout"] = 'Logout';

$lang["mail-anon"] = <<<EOD
Hallo,

Diese Informationen betreffen jemanden, der keine email-Adresse angegeben hat.

Falls Sie ihn (bei Bedarf) dennoch kontaktieren müssen, sind hier die Informationen, die er hinterlassen hat:

EOD;

/* NOTE - Assumes spectacle must be preceded by a (masculine)
 definite article. In the future we will need to integrate the article
 in the spectacle name and alter/extended it when needed (e.g. French
 de+le = du, German von+dem = vom, etc) */
$lang["mail-booked"] = <<<EOD
Danke für Ihre Reservierung für %1\$s

Hier sind noch einmal die Details Ihrer Reservierung. Bitte legen Sie diese Bestätigung nach Aufforderung bei der Einlasskontrolle der Veranstaltung vor.

EOD;

$lang["mail-cancel-however"] = <<<EOD
Wir müssen Sie allerdings darüber informieren, dass die Buchung für folgenden Platz annuliert wurde:
EOD;
$lang["mail-cancel-however-p"] = <<<EOD
Wir müssen Sie allerdings darüber informieren, dass die Buchung für die folgenden Plätze annuliert wurde:
EOD;
$lang["mail-cancel"] = <<<EOD
Hiermit informieren wir Sie, dass die Buchung für folgenden Platz annuliert wurde:
EOD;
$lang["mail-cancel-p"] = <<<EOD
Hiermit informieren wie Sie, dass die Buchung für die folgenden Plätze annuliert wurde:
EOD;

$lang["mail-gotmoney"] = 'Wir erhielten Ihre Zahlung für folgenden Platz:';
$lang["mail-gotmoney-p"] = 'Wir erhielten Ihre Zahlung für folgende Plätze';

$lang["mail-heywakeup"] = <<<EOD

Wir haben bislang noch keine Zahlung für den folgenden Platz erhalten:

%1\$s
Falls sich Ihre Zahlung mit dieser Nachricht überschnitten hat, ignorieren Sie bitte dieses Schreiben.

Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail. Sollten wir nichts von Ihnen hören, wird Ihre Buchung in den nächsten Tagen durch uns storniert.

EOD;

$lang["mail-heywakeup-p"] = <<<EOD


Wir haben bislang noch keine Zahlung für die folgenden Plätze erhalten:

%1\$s
Falls sich Ihre Zahlung mit dieser Nachricht überschnitten hat, ignorieren Sie bitte dieses Schreiben.

Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail. Sollten wir nichts von Ihnen hören, wird Ihre Buchung in den nächsten Tagen durch uns storniert.
EOD;

$lang["mail-notconfirmed"] = <<<EOD
Ihre Buchung wurde noch nicht bestätigt. Sie ist erst bei Zahlungseingang gültig. 
EOD;

// for one seat
$lang["mail-notdeleted"] = 'Wir haben folgende Platzreservierung erhalten:';
// for more than one seat
$lang["mail-notdeleted-p"] = 'Wir haben folgende Platzreservierung erhalten';
$lang["mail-notpaid"] = 'Folgender Platz wurde gebucht, aber wir haben bislang noch keine Zahlung erhalten:';
$lang["mail-notpaid-p"] = 'Folgende Plätze wurden gebucht, aber wir haben bislang noch keine Zahlung erhalten:';
$lang["mail-remail"] = <<<EOD
Gemäß Ihrer Anfrage auf der %1\$s website erhalten Sie hier eine Zusammenfassung der Buchungen für diese email-Adresse.


Zugangs-Schlüssel für Ihre Tickets : %2\$s

EOD;

$lang["mail-reminder-p"] = <<<EOD
Darüber hinaus möchten wir Sie daran erinnern, dass folgende Plätze noch bezahlt werden müssen:

%1\$s
Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail.

EOD;

$lang["mail-reminder"] = <<<EOD
Darüber hinaus möchten wir Sie daran erinnern, dass folgender Platz noch bezahlt werden muß:

%1\$s
Falls Sie jedoch Ihre Reservierung stornieren wollen, antworten Sie bitte auf unsere Mail.

EOD;

$lang["mail-secondmail"] = <<<EOD
Sie werden eine weitere Mail erhalten, wenn Ihre Zahlung bei uns eingegangen ist.
EOD;

$lang["mail-spammer"] = <<<EOD
Hallo,

Jemand (vielleicht Sie?) forderte eine Buchungsübersicht an für diese Mail-Adresse (%3\$s) für %1\$s
(%2\$s).

Wir haben jedoch unter dieser Mail-Adresse keine Buchungen vorliegen. Das kann dreierlei bedeuten:
* Sie benutzten für Ihre Buchung eine andere email-Adresse.
* Sie haben einen Platz gebucht, aber die Buchung wurde durch uns storniert. Sie sollten eine Mail über die Stornierung erhalten haben.
* Ein Witzbold versucht, Ihr email-Postfach zu füllen und glaubt, dabei unbemerkt bleiben zu können.

Falls Sie Fragen haben, antworten Sie bitte auf diese Mail.

EOD;
// following always plural
$lang["mail-summary-p"] = 'Folgende Platz-Buchungen sind derzeit bestätigt:';

$lang["mail-thankee"] = <<<EOD
Herzlichen Dank für Ihre Buchung. Wir hoffen, dass Ihnen unsere Veranstaltung gefallen wird.

EOD;

$lang["mail-oops"] = <<<EOD
Falls Sie der Meinung sind, dass ein Fehler vorliegt, antworten Sie bitte baldmöglichst auf diese Mail, damit wir Ihre Buchung reaktivieren können.
EOD;
    //'

$lang["mail-sent"] = 'Eine email mit dem Inhalt dieser Seite wurde gerade an Sie versendet.';
$lang["mail-sub-booked"] = 'Ihre Buchung';
$lang["mail-sub-cancel"] = 'Buchungsstornierungen';
$lang["mail-sub-gotmoney"] = 'Zahlungsbestätigungen';
$lang["mail-sub-heywakeup"] = 'Erinnerung';
$lang["mail-sub-remail"] = 'Buchungsübersicht';
$lang["make_default"] = 'Diese Veranstaltung zur aktiven machen. Es kann immer nur eine Veranstaltung als aktiv gekennzeichnet werden.';
$lang['make_payment'] = 'bezahlen';
$lang["max_seats"] = 'Maximale Anzahl der Plätze, die in einer Sitzung gebucht werden können';
$lang["minute"] = 'min'; // abbreviated
$lang["minutes"] = 'Minuten';
$lang["months"] = array(1=>"Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember");

$lang["name"] = 'Name';
$lang["new_spectacle"] = 'Eine neue Veranstaltung anlegen';
$lang["ninvite"] = 'Ehrenkarten';
// following written on tickets for non-numbered seats.
$lang["nnseat"] = 'Unnummerierte Plätze';
$lang["nnseat-avail"] = 'Ein %1sunnummerierter Platz ist noch verfügbar. <br>Geben Sie hier 1 (eins) ein, wenn Sie ihn buchen wollen: ';
$lang["nnseat-header"] = 'Unnummerierte Tickets';
$lang["nnseats-avail"] = 'Es sind noch %1$s %2$sPlätze verfügbar. <br>Geben Sie hier die Anzahl der Plätze, die Sie buchen wollen, ein: ';
$lang["nocancellations"] = 'keine automatische Stornierung';
$lang["noimage"] = 'Keine Bild-Datei';
$lang["none"] = 'keine';
$lang["noreminders"] = 'keine Erinnerungen';
$lang["notes"] = 'Notizen';
$lang["notes-changed"] = 'Notizen geändert für 1 Reservierung';
$lang["notes-changed-p"] = 'Notizen geändert für %1$d Reservierungen';
$lang["nreduced"] = 'Zum ermäßigten Preis';

$lang["orderby"] = 'Sortieren nach %1$s';

$lang["panic"] = <<<EOD
<h2>BUCHUNG NICHT ERFOLGREICH</h2>
<p class='main'>Der Systemadministrator wurde informiert. Er wird das Problem baldmöglichst beheben.</p>

<p class='main'>Bitte versuchen Sie es in ein paar Stunden noch einmal.</p>

<p class='main'>Wir entschuldigen uns für dieses Problem und bedanken uns für Ihre Geduld.</p>
EOD;

$lang["params"] = '%1$sSystemparameter%2$s ändern';
$lang["pay_cash"] = 'Barzahlung';
$lang["pay_ccard"] = 'mit Kreditkarte';
$lang["pay_other"] = 'andere';
$lang["pay_postal"] = 'Überweisung';
$lang["payinfo_cash"] = <<<EOD
Tickets müssen bis 30 min. vor Veranstaltungsbeginn bezahlt werden, anderenfalls gehen sie wieder in den freien Verkauf.

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
$lang['paypal_lastchance'] = "Wir sind nun bereit, die Zahlung abzuschließen.  Nachdem Sie auf den unten stehenden Button geklickt haben, werden Sie zusammen mit Ihren Informationen zum Ticketkauf auf die Paypal-Seite weitergeleitet. Ist die Zahlung abgeschlossen, werden Sie wieder auf diese Seite zurück geleitet und Ihre Zahlung registriert.  Ihre Kreditkarteninformationen werden durch das Paypal Sicherheitssystem verschlüsselt.";
$land["paypal_purchase"] = 'Ticket-Kauf mit PayPal';
$lang["phone"] = 'Telefon';
$lang['please_wait'] = 'Ihre Daten werden übertragen . . .  bitte warten';
$lang["postal tax"] = 'Einzahlungsgebühr';
$lang["postalcode"] = 'Postleitzahl';
$lang["poweredby"] = 'Powered by %1$s';
$lang["price"] = 'Preis';
$lang["price_discount"] = 'ermäßigter Preis ';
$lang['prices']  = 'Kartenpreis';
$lang["print_entries"] = '%1$sDrucken%2$s ausgewählter Einträge';

$lang["rebook"] = 'Neue Buchung mit den gewählten Einträgen als Vorlage: %1$sBuchung starten%2$s';
$lang["rebook-info"] = 'Um gelöschte Buchungen zu reaktivieren, bitte zuerst den Filter "Gelöscht" oben links auf der Seite aktivieren';
$lang["remail"] = 'Haben Sie Ihre Eintrittskarte verloren? Mit dem folgenden Link bekommen Sie sie zurück %1$sBuchung aufrufen%2$s';
$lang["reminders"] = 'Erinnerungen';
$lang["reqd_info"] = <<<EOD
Sie müssen auf jeden Fall einen Namen angeben.
Wenn Sie mit Kreditkarte bezahlen, müssen Sie darüber hinaus eine gültige email-Adresse angeben.
EOD;
$lang["reserved-header"] = 'Reservierte Platzkarten';
$lang["row"] = 'Reihe';

$lang["sameprice"] = 'Preise für alle Kategorien gleich';
$lang["save"] = 'Speichern';
$lang["seat_free"] = 'Freier<br>Platz:';
$lang["seat_occupied"] = 'Besetzter<br>Platz:';
$lang["seats"] = 'Plätze';
$lang["seats_booked"] = 'Gebuchte Plätze';
$lang["seeasalist"] = '%1$sListenansicht%2$s';
$lang["seeasamap"] = 'Der folgende link zeigt die Buchungen dieser Veranstaltung als&nbsp;: %1$sSitzplan%2$s';
$lang["select"] = 'Auswählen';
$lang["select_payment"] = 'Bitte wählen Sie eine Zahlweise:';
$lang["selected_1"] = '1 Platz ausgewählt';
$lang["selected_n"] = '%1$d Plätze ausgewählt';
$lang["sentto"] = 'Nachricht gesendet an: %1$s';
$lang["set_status_to"] = 'Ausgewählte Einträge werden: ';
$lang["show_any"] = 'Alle Veranstaltungen';
$lang["show_info"] = '%1$s um %2$s, %3$s'; // date, time, location
$lang["show_name"] = 'Name der Veranstaltung';
$lang["show_not_stored"] = 'Ihre Änderungen konnten nicht gespeichert werden, bitte kontaktieren Sie Ihren Systemadministrator.';
$lang["show_stored"] = 'Ihre Änderungen wurden gespeichert.';
$lang["showlist"] = 'Aufführungen von %1$s';
$lang["spectacle_name"] = 'Wählen Sie eine Veranstaltung aus';
$lang["state"] = 'Status'; // in the sense of status, not in the sense
			  // of a country's part
$lang["st_any"] = 'Jeder Status';
$lang["st_booked"] = 'Gebucht';
$lang["st_deleted"] = 'Gelöscht';
$lang["st_disabled"] = 'Deaktiviert';
$lang["st_free"] = 'Frei';
$lang["st_locked"] = 'In Bearbeitung';
$lang["st_notdeleted"] = 'Nicht gelöscht';
$lang["st_paid"] = 'Bezahlt';
$lang["st_shaken"] = 'Erinnerung gesendet';
$lang["st_tobepaid"] = 'Zu bezahlen';
$lang["stage"] = 'Bühne';
$lang["summary"] = 'Zusammenfassung';

$lang["theatre_name"] = 'Name des Sitzplans';
$lang["time"] = 'Zeit';
$lang["time_title"] = 'Zeit<br>(hh:mm)';
$lang["timestamp"] = 'Gebucht am';
$lang["title_mconfirm"] = 'Bestätige Veranstaltungs-Details';
$lang["title_maint"] = 'Aufführungen hinzufügen oder ändern';
$lang["to"] = 'bis'; // in a temporal sense : from a to b
$lang["total"] = 'Gesamt';

$lang["update"] = 'aktualisieren';
$lang['us_state'] = 'Bundesstaat (nur USA)';

$lang["warn_badlogin"] = 'Verbindungsversuch durch illegalen Client';
$lang["warn_bookings"] = 'Bitte beachten Sie: Sie sind dabei, das Datum, die Zeit oder die Preise für eine Veranstaltung zu ändern, für die schon Karten verkauft sind. Sie sollten die Käufer dieser Karten über Ihre Änderungen informieren.  Falls Sie den Preis ändern, könnte es sein, dass bereits Tickets zu anderen Preisen verkauft worden sind, was zu Verwirrung führen kann.  Bitte fahren Sie mit Bedacht fort.';
$lang["warn_close_in_1"] = 'Achtung, on-line Buchungssystem für diese Veranstaltung schließt in einer Minute';
$lang["warn_close_in_n"] = 'Achtung, on-line Buchungssystem für diese Veranstaltung schließt in %1$d Minuten';
$lang["warn-nocontact"] = 'Achtung! Sie haben keine Kontaktdaten eingegeben. Wir werden daher diese Buchung nicht abschließend bestätigen können!';
$lang["warn-nomail"] = 'Achtung! Sie haben keine email-Adresse eingegeben. Wir werden Sie daher nicht über den Stand der Buchung informieren können.';
$lang["warn-nomatch"] = 'Nichts gefunden'; // no matching bookings
$lang["warn-nonsensicalcat"] = 'Achtung, Sie haben mehr Plätze zum reduizierten Preis eingegeben als Sie Plätze ausgewählt haben!';
$lang["warn-nonsensicalcat-admin"] = 'Achtung, die Anzahl der Ehrenkarten plus die Anzahl der Karten zum ermäßigten Tarif ist größer als die Anzahl der ausgewählten Plätze';
$lang['warn_paypal_confirm'] = 'Wir konnten Ihre Zahlung durch Paypal nicht bestätigen.  Bitte kontaktieren Sie das Veranstaltungsbüro, um die Zahlung bestätigen zu lassen.';
$lang['warn_process_payment'] = 'Es gab ein Problem bei der abschließenden Übermittlung Ihrer Zahlung.  Bitte kontaktieren Sie das Veranstaltungsbüro, um die Zahlung bestätigen zu lassen';
$lang["warn_show_confirm"] = 'Bitte überprüfen Sie Ihre Eingaben. Zum Ändern klicken Sie auf "Ändern", zum Speichern auf "Speichern"';
$lang["warn_spectacle"] = 'Bitte beachten Sie, dass Sie den Bestuhlungsplan eines Veranstaltungsortes nicht mehr ändern können, nachdem eine Aufführung angelegt wurde.';
$lang["we_accept"] = "Wir akzeptieren"; // credit card logos come after that
$lang["weekdays"] = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Sonnabend");
$lang["workingdays"] = 'Werktage';

$lang["youare"] = 'Ihre Angaben:';

$lang["zoneextra"] = ''; // intentionally left blank

$lang["err_notickets"] = 'Bitte wählen Sie aus, wie Sie das Ticket zugestellt bekommen wollen.';
  $lang["intro_finish_notickets"] = 'Danke für Ihre Buchung. Anschließend finden Sie eine Übersicht über Ihre Buchung';
  $lang["mark_as_sent"] = 'als versendet markieren';
  $lang["pay_snail"] = 'per Brief';
  $lang["reqd_info_all"] = 'Bitte alle Felder ausfüllen';
  $lang["st_sent"] = 'gesendet';
  $lang["tickets"] = 'Ticket Versand: ';


/** add "at the" before the given noun. This is used with theatre
names (We have a problem in case we need to know if $w is masculine or
feminine or whatever - so far everything has been masculine so won't
extend the function until need appears :-) **/
function lang_at_the($w) {
  return "in $w";
}
