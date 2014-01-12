<?php namespace freeseat;

/** Nederlands (DUTCH) Language file.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info. Vertaling : Jan De Vilder (LAO)

$Id: nederlands.php 231 2007-01-13 13:29:41Z tendays $
*/

require_once ( FS_PATH . "languages/default.php" );

$lang["_encoding"] = "ISO-8859-1";


$lang["access_denied"] = 'GEEN TOEGANG - Uw sessie is verlopen';
$lang["acknowledge"] = 'BEVESTIGEN'; // used with check_st_update
$lang["address"] = 'Adres';
$lang["admin"] = 'Administratieve functies';
$lang["admin_buy"] = '%1$sReserveer%2$s tickets en kies zelf uw plaatsen';
$lang["alert"] = 'OPGEPAST';
$lang["are-you-ready"] = 'Controleer uw gegevens en klik op Doorgaan.';

$lang["backto"] = 'Terug naar %1$s';
$lang["book"] = 'Reserveer';
$lang["bookagain"] = 'Maak een %1$sNieuwe Reservatie%2$s';
$lang["bookid"] = 'Code';
$lang["book_adminonly"] = 'Reservaties afgesloten voor het publiek';
$lang["book_submit"] = 'VALIDEER';
$lang["booking_st"] = 'Reservatie in staat %1$s';
$lang["bookinglist"] = '%1$sRaadplegen/Wijzigen van de reservaties%2$s (o.a. om betalingen aan te duiden)';
$lang["bookingmap"] = 'Zetelplan';
$lang["buy"] = '%1$sReserveer%2$s uw tickets en kies zelf uw plaatsen';

$lang["cancel"] = 'Annuleren';
$lang["cancellations"] = 'Annulaties';
$lang["cat"] = "Tarief";
$lang["cat_free"] = "Vrijkaart";
$lang["cat_normal"] = "Normaal";
$lang["cat_reduced"] = "Student";
$lang["cat_alumni"] = "Alumni/Personeel KUL";
$lang["cat_steunend"] = "Steunend Lid";
$lang["ccard_failed"] = '%1$s LORS DE LA GESTION D\'UNE NOTIFICATION CARTE DE CREDIT\n\n\n';
$lang["ccard_partner"] = 'De betaling wordt verzekerd door&nbsp;%1$s';
$lang["change_date"] = "Kies andere concertdatum";
$lang["change_pay"] = 'Wijzig uw %1$spersoonlijke gegevens en/of tarieven%2$s';
$lang["change_seats"] = 'Wijzig de %1$sgeselecteerde plaatsen%2$s';
$lang["check_st_update"] = 'Verifieer of de reservatie van %1$s correct is. Klik daarna op bevestigen onderaan deze pagina';
$lang["choose_show"] = 'Kies een concert';
$lang["city"] = "Gemeente";
$lang["comment"] = 'Opmerking';
$lang["confirmation"] = 'Bevestiging';
$lang["continue"] = "Doorgaan";
$lang["country"] = "Land";
$lang["class"] = "Cat.";
$lang["closed"] = "Gesloten";
$lang["col"] = "Plaats";
$lang["create_show"] = 'Nieuw concert';

$lang["date"] = "Datum";
$lang['datesandtimes'] = 'Voorstellingen';
$lang["date_title"] = 'Datum<br>(jjjj-mm-dd)';
$lang["day"] = 'd'; // abbreviated
$lang["days"] = 'dagen';
$lang["DELETE"] = "VERWIJDEREN"; // used in check_st_update
$lang["description"] = 'Omschrijving';
$lang["diffprice"] = 'Bij elke categorie hoort een kleur zoals hieronder aangegeven';
$lang["disabled"] = "gedesactiveerd"; // for shows or payment methods
$lang["dump_csv"] = 'Om de inhoud van de database in csv formaat om te zetten : %1$sbookings.csv%2$s';

$lang['editshows'] = '%1$sToevoegen en Wijzigen%2$s van concerten';
$lang["email"] = "E-mail";
$lang["err_bademail"] = 'Het e-mailadres dat u hebt ingegeven is niet correct.';
$lang["err_badip"] = 'U hebt geen toegang om dit document te bekijken.';
$lang["err_badkey"] = 'De sleutel was niet correct. U kan opnieuw proberen.';
$lang["err_bookings"] = 'Error bij toegang tot reservaties';
$lang["err_ccard_cfg"] = 'Betalingen met kredietkaart moeten worden geconfigureerd in config.php voordat dit kan worden gebruikt';
$lang["err_ccard_insuff"] = 'Er is niet genoeg geld betaald voor het ticket %1$d die kost %2$d EUR. U hebt enkel %3$d EUR betaald!';
$lang["err_ccard_mysql"] = 'Error (mysql) tijdens de verwerking van een transactie met credit card.';
$lang["err_ccard_nomatch"] = 'push (%1$s) and pull (%2$s) komen niet overeen (de waarde pull zal gebruikt worden)';
$lang["err_ccard_pay"] = 'Onmogelijk om de betaling uit te voeren van plaats %1$d met credit card (misschien is de betaling reeds gebeurd?)!';
$lang["err_ccard_repay"] = 'Dubbele aanvraag tot betaling (met credit card) voor plaats %1$d met credit card!';
$lang["err_ccard_toomuch"] = 'We hebben te veel geld ontvangen! %1$d op %2$d EUR is niet gebruikt';
$lang["err_ccard_user"] = 'De betaling is niet uitgevoerd - u kan opnieuw proberen of ons een e-mail sturen';
$lang["err_checkseats"] = 'Gelieve de plaatsen te selecteren die u wenst te reserveren';
$lang["err_closed"] = 'Spijtig, de online ticketverkoop is afgesloten voor deze voorstelling.';
$lang["err_config"] = 'Verifieer de configuratie van de server in verband met: ';
$lang["err_connect"] = 'Error bij connectie : ';
$lang["err_cronusage"] = 'Verplichte informatie ontbreekt (paswoord van de admin voor de database\n';
$lang["err_email"] = 'De reservaties die u hebt gedaan zijn niet geregistreerd via hetzelfde e-mailadres (we bewaren het eerste)';
$lang["err_filetype"] = 'Het bestand is niet van het type ';
$lang["err_ic_firstname"] = 'De reservaties die u hebt gedaan zijn niet geregistreerd op dezelfde voornaam (we bewaren de eerste)';
$lang["err_ic_lastname"] = 'De reservaties die u hebt gedaan zijn niet geregistreerd op dezelfde naam (we bewaren de eerste)';
$lang["err_ic_payment"] = 'De reservaties die u hebt gedaan hebben niet dezelfde betalingswijze (we bewaren de eerste)';
$lang["err_ic_phone"] = 'De reservaties die u hebt gedaan zijn niet gebeurd met hetzelfde telefoonnummer (we bewaren het eerste)';
$lang["err_ic_showid"] = 'De reservaties die u hebt gedaan zijn niet allemaal voor dezelfde voorstelling...';
$lang["err_noaddress"] = 'Voor een betaling via credit card, dient u zowel uw e-mailadres, als uw volledige adresgegevens in te vullen.';
$lang["err_nodates"] = 'U moet minstens 1 voorstelling aanmaken voor dit concert.';
$lang["err_noname"] = 'Gelieve minstens een naam in te geven';
$lang["err_noprices"] = 'Gelieve de prijzen in te geven (minstens voor 1 categorie).';
$lang["err_noseats"] = 'Geen zetels beschikbaar';
$lang["err_nospec"] = 'Gelieve de naam te geven van het concert.';
$lang["err_notheatre"] = 'Gelieve een concert te selecteren.';
$lang["err_occupied"] = "Spijtig, 1 van de plaatsen die u hebt gekozen is reeds gereserveerd";
$lang["err_paymentclosed"] = 'De betaling %1$s is afgesloten voor deze voorstelling';
$lang["err_payreminddelay"] = 'Le délai de paiement doit être plus long que le délai de rappel';
$lang["err_postaltax"] = 'Prijs te hoog voor een betaling via overschrijving';
$lang["err_price"] = 'Error bij het verkrijgen van de prijs';
$lang["err_pw"] = 'Gebruiker onbekend of paswoord niet correct. Gelieve opnieuw te proberen.';
$lang["err_scriptauth"] = 'De vraag naar het script %1$s is gewijgerd';
$lang["err_scriptconnect"] = 'De connectie naar script %1$s is mislukt';
$lang["err_seat"] = 'Error bij de toegang van de plaats';
$lang["err_seatcount"] = 'U kan niet zoveel plaatsen in 1 keer reserveren';
$lang["err_seatlocks"] = 'Error bij het vergrendelen van de plaats';
$lang["err_session"] = 'U hebt geen reservatie lopen op dit moment (zijn de "cookies" geactiveerd in uw webbrowser?)';
$lang["err_setbookstatus"] = 'Niet mogelijk om de status van deze plaats te wijzigen';
$lang["err_shellonly"] = 'TOEGANG VERBODEN - dit script is gereserveerd voor een shell toegang';
$lang["err_show_entry"] = 'U kan dit concert niet bewaren alvorens de ontbrekende informatie in te geven.';
$lang["err_showid"] = 'Verkeerd nummer van de voorstelling';
$lang["err_smtp"] = 'Opgelet ! Bericht verzenden mislukt: %1$s - Antwoord van de server: %2$s';
$lang["err_spectacle"] = 'Error : Geen toegang tot de gegevens van dit concert';
$lang["err_spectacleid"] = 'Verkeerd voorstellingsnummer';
$lang["expiration"] = 'Vervaldag';
$lang["expired"] = 'Reeds vervallen';

$lang["failure"] = 'RAMP';
$lang["file"] = 'Bestand: '; 
$lang["filter"] = 'Toon&nbsp;:'; // filter form header in bookinglist
$lang["firstname"] = 'Voornaam';
$lang["from"] = 'De'; // in a temporal sense : from a to b

$lang["hello"] = 'Geachte %1$s,';
$lang["hour"] = 'u'; // abbreviated
/* (note : this is only used for at least two seats) */
$lang["howmanyare"] = 'U hebt %1$d plaatsen aangeduid. Vul hieronder in hoeveel plaatsen u AAN WELK TARIEF wenst te reserveren.';

$lang["id"] = 'Code';
$lang['imagesrc'] = 'Beeld invoegen';
$lang["immediately"] = 'onmiddellijk';
$lang["import"] = 'Verstuur dit bestand';
$lang["in"] = 'binnen %1$s'; // as in "in <ten days>"
$lang["index_head"] = 'Welkom op ons online ticket-systeem';
$lang["intro_ccard"] = <<<EOD
 <h2>Bedankt voor uw reservatie voor</h2>

<p class="main">Deze plaatsen zijn gereserveerd op uw naam.</p>
EOD;

$lang["intro_confirm"] = 'Gelieve onderstaande informatie te verifi&euml;ren alvorens te valideren.';
$lang["intro_finish"] = 'Deze pagina bevat uw DEFINITIEVE tickets. DRUK deze pagina AF en NEEM deze afgedrukte tickets MEE naar het concert!';
$lang["intro_params"] = <<<EOD
<h2>Beschikbare betalingswijzen</h2>

<p class="main">
<ul><li><p>
Vul hier in welke periode de verschillende reservaties mogelijk zijn,
gerelateerd aan de datum van de voorstelling.
</p>
<li>
<p>Het aantal geeft het aantal <em>minuten</em> weer voor het begin van de voorstelling..</p>
<li>
<p>Opening/Sluiting van de betaling aan de kassa geeft het interval weer waarop er aan de kassa kan betaald worden (en niet de openingstijd van de kassa zelf).</p>

<li>
<p>
De vertragingen van de betalingsoverschrijvingen worden geteld in werkdagen. Indien een vertraging tijdens een feestdag voorkomt, zal het interval 24u worden opgeschoven.
</p>
</ul>
</p>

%1\$s

<h2>Herinneringen en Annulaties</h2>

<p class="main">Afhankelijk van de betalingswijze; hoeveel <em>dagen</em> na de reservatie moet er een herinnering gestuurd worden, 
en wanneer worden de tickets dan geannuleerd&nbsp;</p>

%2\$s

<h2>Andere parameters</h2>

EOD;
//'

$lang["intro_remail"] = <<<EOD

<h2>Opvragen van uw reservatie</h2>

<p class='main'>Vul hier het e-mailadres in dat u hebt gebruikt voor de reservatie van uw tickets, en druk daarna op "Query verzenden".<br>
U krijgt vervolgens een e-mail met alle details van uw eerder geplaatste reservatie.</p>

<p class='main'>E-mailadres&nbsp;: %1\$s</p>

<p class='main'>(Indien u niet meer het juiste e-mailadres kent, kan u ons best een e-mail sturen.
Wij zullen u dan verder helpen.)</p>

EOD;

$lang["intro_remail2"] = <<<EOD

<h2>Opvragen van uw reservatie</h2>

<p class='main'>Indien de e-mail die u ontvangt een "sleutelcode" bevat, kan u die hieronder invullen en vervolgens klikken op "Query verzenden".
U zal dan uw tickets op het scherm krijgen en ze opnieuw kunnen afdrukken&nbsp;:</p>

<p class='main'>(Opgelet! Het gaat hier over een unieke sleutelcode die slecht 1 keer kan gebruikt worden.)</p>

<p class='main'>Sleutelcode voor uw tickets&nbsp;: %1\$s</p>

EOD;

$lang["submit_query"] = 'Query verzenden';
$lang["intro_seats"] = 'Klik op "Doorgaan" onderaan deze pagina om uw keuze te bevestigen. Gelieve even te wachten indien het plan hieronder niet onmiddellijk verschijnt...';
$lang["is_default"] = 'Dit is de standaard voorstelling.';
$lang["is_not_default"] = 'Dit is niet de standaard voorstelling.';

$lang["lastname"] = 'Naam';
$lang["legend"] = "Reservatie&nbsp;:";
$lang["link_bookinglist"] = 'Lijst van reservaties';
$lang["link_edit"] = 'Wijzigen van voorstellingen';
$lang["link_index"] = 'Welkompagina';
$lang["link_pay"] = 'Persoonlijke gegevens';
$lang["link_repr"] = 'Lijst van de voorstellingen';
$lang["link_seats"] = 'Reserveer uw tickets';
$lang["login"] = 'System administration (Only authorised persons)&nbsp;:';
$lang["logout"] = "Logout";

$lang["mail-anon"] = <<<EOD
Geachte,

Hier vindt u informatie die bestemd is voor een persoon die zijn e-mailadres niet heeft achtergelaten.

Indien u die persoon toch kan bereiken, kan u onderstaande gegevens gebruiken :

EOD;
//'

/* NOTE - Assumes spectacle must be preceeded by a (masculine)
 definite article. In the future we will need to integrate the article
 in the spectacle name and alter/extended it when needed (e.g. French
 de+le = du, German von+dem = vom, etc) */
$lang["mail-booked"] = <<<EOD
Geachte,

Bedankt voor uw reservatie voor %1\$s. Hierbij vindt u een overzicht van de plaatsen die u hebt gereserveerd.


EOD;
//'

$lang["mail-cancel-however"] = <<<EOD
Intussen wensen wij u te informeren van het feit dat de reservatie van het onderstaande ticket is geannuleerd :

EOD;
$lang["mail-cancel-however-p"] = <<<EOD
Intussen wensen wij u te informeren van het feit dat de reservatie van het onderstaande ticket is geannuleerd :
EOD;
$lang["mail-cancel"] = <<<EOD
U krijgt dit bericht omdat u uw ticketreservatie hebt geannuleerd.
EOD;
$lang["mail-cancel-p"] = <<<EOD
U krijgt dit bericht omdat u uw ticketreservatie hebt geannuleerd.
EOD;

$lang["mail-gotmoney"] = "We hebben uw betaling ontvangen voor de volgende tickets :";
$lang["mail-gotmoney-p"] = "We hebben uw betaling ontvangen voor de volgende tickets :";

$lang["mail-heywakeup"] = <<<EOD

We hebben nog steeds geen betaling ontvangen voor de onderstaande tickets :

%1\$s
Indien dit bericht uw betaling gekruist heeft, gelieve hier dan geen rekening mee te houden.

Indien u echter uw tickets wenst te behouden, gelieve ons dan een e-mail te sturen, zoniet zullen uw tickets geannuleerd worden.
EOD;
//'
$lang["mail-heywakeup-p"] = <<<EOD

We hebben nog steeds geen betaling ontvangen voor de onderstaande tickets : 

%1\$s
Indien dit bericht uw betaling gekruist heeft, gelieve hier dan geen rekening mee te houden.

Indien u echter uw tickets wenst te behouden, gelieve ons dan een e-mail te sturen, zoniet zullen uw tickets geannuleerd worden.
EOD;
//'

$lang["mail-notconfirmed"] = <<<EOD
Uw reservatie is nog niet bevestigd. De tickets zijn maar geldig vanaf het moment dat we de betaling hebben ontvangen.
EOD;
//'
$lang["mail-notdeleted"] = "De volgende reservaties zijn behouden gebleven :";
$lang["mail-notdeleted-p"] = "De volgende reservaties zijn behouden gebleven :";
$lang["mail-notpaid"] = 'De volgende tickets zijn gereserveerd, maar we hebben de betaling nog niet ontvangen :';
$lang["mail-notpaid-p"] = 'De volgende plaatsen zijn gereserveerd, maar we hebben de betaling nog niet ontvangen :';
$lang["mail-remail"] = <<<EOD
Zoals gevraagd via het "%1\$s", vindt u hieronder een overzicht van de reservatie die u hebt gedaan
via uw e-mailadres, alsook uw "sleutelcode" om de tickets opnieuw op te vragen en af te drukken. 

Sleutelcode voor uw tickets : %2\$s


EOD;
//'
$lang["mail-reminder-p"] = <<<EOD
We herinneren u eraan dat de volgende tickets nog moeten betaald worden :

%1\$s
Indien u deze tickets wenst te annuleren, stuur ons dan een e-mail.

EOD;

$lang["mail-reminder"] = <<<EOD
We herinneren u eraan dat de volgende tickets nog moeten betaald worden :

%1\$s
Indien u deze tickets wenst te annuleren, stuur ons dan een e-mail.

EOD;

$lang["mail-secondmail"] = <<<EOD
U krijgt nog een e-mail met de bevestiging van uw betaling wanneer we die hebben ontvangen.

EOD;

$lang["mail-spammer"] = <<<EOD
Geachte,

U hebt een overzicht gevraagd van de reservatie voor "%1\$s" die u hebt gedaan via het e-mailadres %3\$s.

We hebben geen reservatie gevonden via dit e-mailadres. Dit kan 3 oorzaken hebben:

* U hebt gereserveerd via een ander e-mailadres.
* Uw reservatie is geannuleerd. Normaal gezien hebt u voor deze annulatie ook
een bevestiging gekregen via e-mail.
* Iemand anders heeft uw e-mailadres onterecht gebruikt.

Indien u nog verdere vragen hebt, kan u ons altijd een e-mail sturen.

EOD;
// following always plural
$lang["mail-summary-p"] = 'Hierna vindt u een overzicht van de tickets die tot op heden zijn betaald :';

$lang["mail-thankee"] = <<<EOD

Wij danken u voor uw reservatie en wensen u veel plezier tijdens ons concert.

EOD;

$lang["mail-oops"] = <<<EOD

Indien u de reservatie toch niet wou annuleren, stuur ons dan opnieuw een e-mail. Wij kunnen uw reservatie terug actief maken.

EOD;
    //'

$lang["mail-sent"] = 'U krijgt eveneens een e-mail met dezelfde informatie als op deze pagina staat.';
$lang["mail-sub-booked"] = 'Uw reservatie';
$lang["mail-sub-cancel"] = 'Annulatie van uw reservatie';
$lang["mail-sub-gotmoney"] = 'Bevestiging van uw betaling';
$lang["mail-sub-heywakeup"] = 'Herinnering reservatie tickets';
$lang["mail-sub-remail"] = 'Opvragen van uw reservatie';
$lang["make_default"] = 'Maak van deze voorstelling de standaard (Er zal altijd 1 voorstelling standaard zijn).';
$lang['make_payment'] = 'De betaling doen';
$lang["max_seats"] = 'Het max. aantal plaatsen dat u in 1 keer kan selecteren.';
$lang["minute"] = 'm'; // abbreviated
$lang["minutes"] = 'minuten';
$lang["months"] = array(1=>"januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december");

$lang["name"] = 'Naam';
$lang["new_spectacle"] = 'Aanmaak van een nieuwe voorstelling';
$lang["ninvite"] = 'Vrijkaarten';
// following written on tickets for non-numbered seats.
$lang["nnseat"] = 'Niet-genummerde plaats';
$lang["nnseat-avail"] = 'Een niet-genummerde plaats in catogerie %1$s is nog vrij. Selecteer het volgende veld indien u deze plaats wilt reserveren;: ';
$lang["nnseat-header"] = 'Niet-genummerde plaatsen';
$lang["nnseats-avail"] = '%1$s niet-genummerde plaatsen in categorie %2$s zijn nog vrij. Selecteerd het volgende veld indien u deze plaatsen wil reserveren&nbsp;: ';
$lang["nocancellations"] = 'Geen automatische annulatie';
$lang["noimage"] = 'Geen beeld';
$lang["none"] = 'geed';
$lang["noreminders"] = 'Geen herinneringen verstuurd';
$lang["notes"] = 'Opmerking';
$lang["notes-changed"] = 'Er zijn veranderingen aangebracht voor 1 reservatie';
$lang["notes-changed-p"] = 'Er zijn veranderingen aangebracht voor %1$d reservaties';
$lang["nreduced"] = 'Studenten';
$lang["nalumni"] = 'Alumni/Personeel KUL';
$lang["nsteunend"] = 'Steunende leden';
$lang["nnormal"] = 'Normaal';
$lang["orderby"] = 'Sorteer per %1$s';

$lang["panic"] = <<<EOD
<h2>UW RESERVATIE IS NIET VERWERKT</h2>
<p class='main'>Het Leuvens Alumni Orkest is op de hoogte van het probleem en zal dit asap in orde brengen</p>

<p class='main'>Bedankt om binnen enkele uren opnieuw te proberen en dan de tickets opnieuw te reserveren</p>

<p class='main'>Wij verontschuldingen ons voor dit probleem</p>
EOD;

$lang["params"] = 'Wijzigen van de %1$sparameters van het systeem%2$s';
$lang["pay_cash"] = 'aan de kassa';
$lang["pay_ccard"] = 'met credit card';
$lang["pay_other"] = "andere";
$lang["pay_postal"] = "via overschrijving";
$lang["payinfo_cash"] = <<<EOD
De tickets moeten minstens 30 minuten voor aanvang van het concert worden betaald, zoniet zullen ze opnieuw te koop worden aangeboden.

EOD;
$lang["payinfo_ccard"] = <<<EOD
Wij hebben uw betaling nog niet ontvangen. Als wij niets hebben ontvangen binnen %1\$d dagen, zullen de tickets terug te koop worden aangeboden.

EOD;
//'
$lang["payinfo_postal"] = <<<EOD
De totaalprijs moet binnen de %2\$d werkdagen betaald worden op reknr. %1\$s. Zoniet zullen de tickets opnieuw te koop worden aangeboden.
BELANGRIJK : Vermeld als mededeling bij uw overschrijving de codes die u hierboven in de kolom "Code" terugvindt%3\$s. 

EOD;
//'

$lang["paybutton"] = 'Gelieve de volgende knop te gebruiken om uw betaling voort te zetten&nbsp;:&nbsp;%1$sDoorgaan%2$s';
$lang["payment"] = "Tarieven & Betaling&nbsp;:";
$lang['payment_received'] = 'We hebben uw betaling goed ontvangen. Bedankt!';
$lang['paypal_id'] = 'Identificatie van de PayPal transactie ';
$lang['paypal_lastchance'] = "We zijn klaar om uw betaling te aanvaarden. Wanneer u onderstaande knop drukt, komt u op de site van PayPal terecht. Wanneer u uw betaling hebt doorgevoerd, zal u terug op de huidige site komen. Uw credit card gegevens zullen enkel bewaard worden bij PayPal. Dit om corruptie te voorkomen.";
$land["paypal_purchase"] = 'Aankoop van tickets via PayPal';
$lang["phone"] = 'Telefoon';
$lang['please_wait'] = 'De transactie wordt verwerkt . . .  Even geduld';
$lang["postal tax"] = 'Taxe bulletin de versement';
$lang["postalcode"] = 'Postcode';
$lang["poweredby"] = 'Powered by %1$s';
$lang["price"] = 'Prijs';
$lang["price_discount"] = 'Studenten ';
$lang["price_alumni"] = 'Alumni ';
$lang["price_steunend"] = 'Steunende Leden ';
$lang['prices']  = 'Prijs van de tickets';
$lang["print_entries"] = 'Om de geselecteerde tickets af te drukken, klik hier : %1$sAFDRUKKEN%2$s';

$lang["rebook"] = 'De geselecteerde reservaties opnieuw actief maken&nbsp;: %1$sOpnieuw activeren van de reservatie%2$s';
$lang["rebook-info"] = 'Kies de filter "Geannuleerd" links boven deze pagina, om de verwijderde reservaties terug actief te maken';
$lang["remail"] = 'Tickets verloren? Via deze link kan u ze terug opvragen bij ons &nbsp;: %1$sTerug opvragen van de tickets%2$s';
$lang["reminders"] = 'Herinneringen';
$lang["reqd_info"] = <<<EOD
* Naam en e-mailadres zijn VERPLICHT.
EOD;
//'
$lang["reserved-header"] = 'Genummerde plaatsen';
$lang["row"] = "Rij";

$lang["sameprice"] = ' Gelieve hieronder de plaatsen die u wenst te reserveren aan te vinken.';
$lang["save"] = 'Bewaren';
$lang["seat_free"] = 'Plaats nog vrij&nbsp;:';
$lang["seat_occupied"] = 'Plaats bezet&nbsp;:';
$lang["seats"] = 'Plaatsen';
$lang["seats_booked"] = 'Gereserveerde tickets';
$lang["seeasalist"] = 'Terug naar de %1$sLijstweergave%2$s';
$lang["seeasamap"] = 'De volgende link geeft de grafische voorstelling weer van het zetelplan&nbsp;: %1$sZetelplan%2$s';
$lang["select"] = 'Selecteren';
$lang["select_payment"] = 'Kies uw betalingswijze&nbsp;:';
$lang["selected_1"] = 'U hebt 1 plaats aangeduid.';
$lang["selected_n"] = 'U hebt %1$d plaatsen aangeduid.';
$lang["sentto"] = 'E-mail verstuurd naar %1$s';
$lang["set_status_to"] = 'De selectie hierboven&nbsp;: ';
$lang["show_any"] = 'van alle voorstellingen';
$lang["show_info"] = '%1$s om %2$s, %3$s'; // date, time, location
$lang["show_name"] = 'Naam van het concert';
$lang["show_not_stored"] = 'Het is onmogelijk uw wijzigingen te bewaren.';
$lang["show_stored"] = 'Wijzigingen bewaard.';
// we WILL run into problems (at least in French) when we have to choose between "du" or "de la" or "des"  or even "d'"...
// (%1$s is here the name of the spectacle, not a particular show)
$lang["showlist"] = 'Reserveer tickets voor %1$s';
$lang["spectacle_name"] = 'Selecteer een voorstelling';
$lang["state"] = "Status";
$lang["st_any"] = "Alle statussen";
$lang["st_booked"] = "Gereserveerd";
$lang["st_deleted"] = "Geannuleerd";
$lang["st_disabled"] = "Inactief";
$lang["st_free"] = "Vrij";
$lang["st_locked"] = "In overgang";
$lang["st_notdeleted"] = "Niet verwijderd";
$lang["st_paid"] = "Betaald";
$lang["st_shaken"] = "Herinnering verstuurd";
$lang["st_tobepaid"] = "Te betalen";
$lang["stage"] = "PODIUM";
$lang["summary"] = "Overzicht van de geselecteerde plaatsen";

$lang["theatre_name"] = 'Concert';
$lang["time"] = 'Uur';
$lang["time_title"] = 'Tijd<br>(uu:mm)';
$lang["timestamp"] = "Reservatie";
$lang["title_mconfirm"] = 'Bevestig de informatie van deze voorstelling';
$lang["title_maint"] = 'Toevoegen of wijzigen van voorstellingen';
$lang["to"] = 'tot'; // in a temporal sense : from a to b
$lang["total"] = 'Totaal';

$lang["update"] = 'Update';
$lang['us_state'] = 'Staat (enkel USA)';

$lang["warn_badlogin"] = 'Connectie vanaf een niet-geautoriseerd adres';
$lang["warn_bookings"] = 'Opgelet ! U gaat de datum, het uur of de prijs wijzigen van een voorstelling waarvoor reeds tickets zijn verkocht. Vergeet de personen niet te verwittigen die reeds tickets hebben aangekocht. Indien u de prijzen van de tickets wijzigt, betekent dit dat er verschillende sommen zullen betaald worden. Dit kan voor verwarring zorgen. Gelieve hier voorzichtig mee om te gaan.';
$lang["warn_close_in_1"] = 'Opgelet ! HEt online-ticketsysteem zal afgesloten worden binnen 1 minuut';
$lang["warn_close_in_n"] = 'Opgelet ! Het online-ticketsysteem zal afgesloten worden binnen %1$d minuten';
$lang["warn-nocontact"] = 'Opgelet ! U hebt geen contactgegevens ingevuld. We zullen u dus niet kunnen contacteren indien er een probleem zou zijn met uw reservatie';
$lang["warn-nomail"] = 'Opgelet ! U hebt geen e-mailadres ingevuld. U zal dus niet geinformeerd worden over de status van uw reservatie';
$lang["warn-nomatch"] = 'Er zijn geen reservaties die overeen komen met deze criteria.';
$lang["warn-nonsensicalcat"] = 'Opgelet! U hebt bij de tarieven meer plaatsen ingevuld dan dat u geselecteerd had op het plan.';
$lang["warn-nonsensicalcat-admin"] = "Opgelet! Uw aantal uitnodigingen + het aantal plaatsen aan gereduceerd tarief is groter dan het aantal geselecteerde plaatsen.";
$lang["warn-nonsensicalcat2"] = 'Opgelet! U hebt bij de tarieven minder plaatsen ingevuld dan dat u geselecteerd had op het plan.';
$lang["warn-nonsensicalcat2-admin"] = "Opgelet! Uw aantal uitnodigingen + het aantal plaatsen aan gereduceerd tarief is kleiner dan het aantal geselecteerde plaatsen.";
$lang['warn_paypal_confirm'] = 'We kunnen uw PayPal betaling niet bevestigen. Gelieve ons te contacteren.';
$lang['warn_process_payment'] = 'Er is een probleem opgetreden met de verwerking van uw betaling.';
$lang["warn_show_confirm"] = 'Gelieve na te kijken of onderstaande gegevens correct zijn. Om ze opnieuw te wijzigen, gelieve de knop "Wijzigen van voorstellingen" aan te klikken. Gebruik daarna de knop Bewaren.';
$lang["warn_spectacle"] = 'U kan het concert niet wijzigen na de aanmaak van een voorstelling.';
$lang["we_accept"] = "Wij aanvaarden"; // credit card logos come after that
$lang["weekdays"] = array("Zondag","Maandag","Dinsdag","Woensdag","Donderdag","Vrijdag","Zaterdag");
$lang["workingdays"] = 'werkdagen';

$lang["youare"] = "Persoonlijke Gegevens";

$lang["zoneextra"] = ""; // intentionally left blank

/** add "at the" before the given noun. This is used with theatre
names (We have a problem in case we need to know if $w is masculine or
feminine or whatever - so far everything has been masculine so won't
extend the function until need appears :-) **/
function lang_at_the($w) {
  return "in de $w";
}

