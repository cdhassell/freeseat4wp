<?php namespace freeseat;

/** Français (FRENCH) Language file.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info.

$Id: francais.php 400 2013-01-06 17:54:04Z tendays $
*/

require_once ( FS_PATH . "languages/default.php" );

$lang["_encoding"] = "ISO-8859-1";


$lang["access_denied"] = 'ACCÈS INTERDIT - Votre session a dû expirer';
$lang["acknowledge"] = 'confirmer'; // used with check_st_update
$lang["address"] = 'Adresse';
$lang["admin"] = 'Fonctions Administratives';
$lang["admin_buy"] = 'Réserver et %1$sacheter%2$s des billets (et invitations)';
$lang["alert"] = 'ALERTE';
$lang["are-you-ready"] = 'Veuillez vérifier que les données sont correctes puis cliquez Continuer.';

$lang["backto"] = 'Retour à %1$s';
$lang["book"] = 'Réserver';
$lang["bookagain"] = 'Faire une %1$sNouvelle réservation%2$s';
$lang["bookid"] = 'Code';
$lang["book_adminonly"] = 'Réservations fermées au public';
$lang["book_submit"] = 'Placer la réservation';
$lang["booking_st"] = 'Réservations dans l\'état %1$s';
$lang["bookinglist"] = '%1$sConsulter/Modifier les réservations%2$s (Entre autres pour accuser réception d\'un paiement)';
$lang["bookingmap"] = 'Plan des réservations';
$lang["buy"] = 'Réserver et %1$sacheter%2$s des billets';

$lang["cancel"] = 'Annuler';
$lang["cancellations"] = 'Annulations';
$lang["cat"] = "Tarif";
$lang["cat_free"] = "Invitation";
$lang["cat_normal"] = "Normal";
$lang["cat_reduced"] = "Réduit";
$lang["ccard_failed"] = '%1$s LORS DE LA GESTION D\'UNE NOTIFICATION CARTE DE CREDIT\n\n\n';
$lang["ccard_partner"] = 'Le paiement est sécurisé par&nbsp;%1$s';
$lang["change_date"] = "Corriger la date";
$lang["change_pay"] = 'Corriger les %1$sInformations personnelles et de paiement%2$s';
$lang["change_seats"] = 'Corriger la %1$sSélection de places%2$s';
$lang["check_st_update"] = 'Vérifiez que la liste suivante de réservations à %1$s est correcte puis cliquez sur confirmation en bas de cette page';
$lang["choose_show"] = 'Sélectionnez un spectacle';
$lang["city"] = "Ville";
$lang["confirmation"] = 'Confirmation';
$lang["continue"] = "Continuer";
$lang["country"] = "Pays";
$lang["class"] = "Catégorie";
$lang["closed"] = "Fermé";
$lang["col"] = "Siège";
$lang["create_show"] = 'Nouveau spectacle';

$lang["date"] = "Date";
$lang['datesandtimes'] = 'Représentations';
$lang["day"] = 'j'; // abbreviated
$lang["days"] = 'jours';
$lang["DELETE"] = "SUPPRIMER"; // used in check_st_update
$lang["description"] = 'Description';
$lang["diffprice"] = 'À chaque catégorie correspond une couleur comme indiqué ci-dessous';
$lang["disabled"] = "désactivé"; // for shows or payment methods
$lang["dump_csv"] = 'Contenu de la base de données au format csv : %1$sbookings.csv%2$s';

$lang['editshows'] = '%1$sAjouter et Modifier%2$s les spectacles';
$lang["email"] = "E-mail";
$lang["err_bademail"] = 'L\'adresse e-mail que vous avez donnée ne semble pas valide';
$lang["err_badip"] = 'Vous n\'êtes pas autorisé à voir ce document';
$lang["err_badkey"] = 'La clé n\'était pas correcte. Vous pouvez réessayer (envoyez un mail à %1$s si vous n\'y parvenez pas)';
$lang["err_bookings"] = 'Erreur d\'accès aux réservations';
$lang["err_ccard_insuff"] = 'Pas assez d\'argent pour payer le siège %1$d coûtant %2$d CHF avec seulement %3$d CHF !';
$lang["err_ccard_mysql"] = 'Erreur (mysql) pendant l\'enregistrement d\'une transaction par carte de crédit';
$lang["err_ccard_nomatch"] = 'push (%1$s) and pull (%2$s) ne correspondent pas (la valeur pull sera utilisée)';
$lang["err_ccard_pay"] = 'Impossible d\'enregistrer le payement du siège %1$d par carte de crédit (vérifiez les journaux système - peut-être qu\'il a déjà été payé)!';
$lang["err_ccard_repay"] = 'Double demande de payment (par carte de crédit) pour le siège %1$d par carte de crédit!';
$lang["err_ccard_toomuch"] = 'Nous avons reçu trop d\'argent! %1$d sur %2$d CHF non utilisés';
$lang["err_ccard_user"] = 'Le paiement n\'a pas pu être effectué - vous pouvez essayer à nouveau, ou envoyer un e-mail à %1$s';
$lang["err_checkseats"] = 'Veuillez placer des croix sur les sièges que vous souhaitez réserver';
$lang["err_closed"] = 'Désolé, la réservation en ligne vient d\'être fermée pour cette représentation';
$lang["err_connect"] = 'Erreur de connexion : ';
$lang["err_cronusage"] = 'Argument obligatoire manquant (mot de passe administrateur pour la base de données)\n';
$lang["err_email"] = 'Les réservations sélectionnées ne sont pas toutes à la même adresse email (je garde la première)';
$lang["err_filetype"] = 'Le fichier n\'est pas de type ';
$lang["err_ic_firstname"] = 'Les réservations sélectionnées n\'ont pas toutes le même prénom (je garde le premier)';
$lang["err_ic_lastname"] = 'Les réservations sélectionnées n\'ont pas toutes au même nom (je garde le premier)';
$lang["err_ic_payment"] = 'Les réservations sélectionnées n\'ont pas toutes le même mode de paiement (je garde le premier)';
$lang["err_ic_phone"] = 'Les réservations sélectionnées n\'ont pas toutes le même numéro de téléphone (je garde le premier)';
$lang["err_ic_showid"] = 'Les réservations sélectionnées ne sont pas toutes de la même représentation...';
$lang["err_noaddress"] = 'Pour payer par carte de crédit il vous faut fournir une adresse e-mail ainsi qu\'une adresse postale complète.';
$lang["err_ccard_cfg"] = 'Le paiement par carte de crédit doit être configuré dans config.php avant qu\'il ne puisse être activé';
$lang["err_noavailspec"] = 'La billetterie de tous les spectacles est fermée.';
$lang["err_nodates"] = 'Il faut créer au moins une représentation pour ce spectacle.';
$lang["err_noname"] = 'Veuillez indiquer au moins un nom';
$lang["err_noprices"] = 'Veuillez indiquer les prix des places pour au moins une catégorie.';
$lang["err_nospec"] = 'Veuillez indiquer le nom du spectacle.';
$lang["err_notheatre"] = 'Veuillez sélectionner un théâtre.';
$lang["err_occupied"] = "Désolé, un des sièges que vous avez choisis vient d'être réservé";
$lang["err_paymentclosed"] = 'Le paiement %1$s vient d\'être fermé pour cette représentation';
$lang["err_payreminddelay"] = 'Le délai de paiement doit être plus long que le délai de rappel';
$lang["err_postaltax"] = 'Prix trop élevé pour paiement par bulletin de versement';
$lang["err_price"] = 'Erreur à l\'obtention du prix';
$lang["err_pw"] = 'Utilisateur inconnu ou mot de passe incorrect. Veuillez réessayer';
$lang["err_scriptauth"] = 'La requête au script %1$s a été refusée';
$lang["err_scriptconnect"] = 'La connexion au script %1$s a échoué';
$lang["err_seat"] = 'Erreur d\'accès au siège';
$lang["err_seatcount"] = 'Vous ne pouvez pas réserver autant de sièges en une fois';
$lang["err_seatlocks"] = 'Erreur de verrouillage de siège';
$lang["err_session"] = 'Vous n\'avez pas (ou plus) de réservation en cours (les "cookies" sont-ils activés sur votre navigateur?)';
$lang["err_setbookstatus"] = 'Impossible de changer l\'état du siège';
$lang["err_shellonly"] = 'ACCÈS INTERDIT - ce script est réservé à un accès shell';
$lang["err_show_entry"] = 'Vous ne pouvez pas enregistrer ce spectacle avant d\'avoir fourni les éléments manquants.';
$lang["err_showid"] = 'Mauvais numéro de représentation';
$lang["err_smtp"] = 'Attention: échec d\'envoi de message: %1$s - Réponse du serveur: %2$s';
$lang["err_spectacle"] = 'Erreur d\'accès aux données du spectacle';
$lang["err_spectacleid"] = 'Mauvais numéro de spectacle';
$lang["err_upload"] = 'Le téléchargement a échoué';
$lang["expiration"] = 'Expiration';
$lang["expired"] = 'déjà expiré';

$lang["failure"] = 'CATASTROPHE';
$lang["file"] = 'Fichier: '; 
$lang["filter"] = 'Montrer&nbsp;:'; // filter form header in bookinglist
$lang["firstname"] = 'Prénom';
$lang["from"] = 'De'; // in a temporal sense : from a to b

$lang["hello"] = 'Bonjour %1$s,';
$lang["hideold"] = '%1$sCacher%2$s les anciens spectacles.';
/* (note : this is only used for at least two seats) */
$lang["howmanyare"] = 'Sur ces %1$d places, combien sont-elles';

$lang["id"] = 'Code';
$lang['imagesrc'] = 'Emplacement de l\'image';
$lang["immediately"] = 'tout de suite';
$lang["import"] = 'Envoyer ce fichier';
$lang["in"] = 'dans %1$s'; // as in "in <ten days>"
$lang["index_head"] = 'Réservations pour le spectacle';
$lang["intro_ccard"] = <<<EOD
 <h2>Merci pour votre réservation</h2>

<p class="main">Les sièges sont à présent réservés à votre nom.</p>
EOD;

$lang["intro_confirm"] = 'Merci de vérifier et le cas échéant corriger les informations suivantes avant de valider votre réservation.';
$lang["intro_finish"] = 'Cette page constitue votre billet d\'entrée. Imprimez-la et emportez-la avec vous le soir du spectacle.';
$lang["intro_params"] = <<<EOD
<h2>Disponibilité des moyens de paiement</h2>

<p class="main">
<ul><li><p>
Indiquez ici les p&eacute;riodes durant lesquelles les
diff&eacute;rents moyens de r&eacute;servation sont ouverts,
relativement &agrave; la date du spectacle.
</p>
<li>
<p>Les nombres &agrave; donner indiquent
le nombre de <em>minutes</em> avant le d&eacute;but de la repr&eacute;sentation.</p>
<li>
<p>Ouverture/Fermeture du paiement &agrave; la caisse
d&eacute;signe l'intervalle de temps pendant lequel le public peut
demander &agrave; payer &agrave; la caisse (et non le temps
d'ouverture de la caisse elle-m&ecirc;me.)</p>

<li>
<p>
Les d&eacute;lais concernant le paiement par bulletin de versement sont consid&eacute;r&eacute;s
concernant uniquement en jours ouvrables. Si un délai est dans un
jour f&eacute;ri&eacute; alors l'intervalle sp&eacute;cifi&eacute; ici
est augment&eacute; de 24h fois le nombre de jours f&eacute;ri&eacute;s.
</p>
</ul>
</p>

%1\$s

<h2>Rappels et annulation</h2>

<p class="main">En fonction du mode de paiement demand&eacute; par le
client, combien de <em>jours</em> apr&egrave;s la r&eacute;servation
faut-il envoyer un rappel, et puis annuler la
r&eacute;servation&nbsp;?</p>

%2\$s

<h2>Autres param&egrave;tres</h2>

EOD;
//'

$lang["intro_remail"] = <<<EOD

<h2>R&eacute;cup&eacute;ration de r&eacute;servation</h2>

<p class='main'>Entrez dans le champ suivant l'adresse e-mail que vous
aviez utilis&eacute;e lors de votre r&eacute;servation, puis validez.<br>
Vous recevrez alors un e-mail contenant les d&eacute;tails
de toutes les r&eacute;servations que vous avez plac&eacute;es.</p>

<p class='main'>Adresse E-Mail&nbsp;: %1\$s</p>

<p class='main'>(Si vous n'aviez pas donn&eacute; d'adresse ou que vous n'y
avez plus acc&egrave;s il vous faut t&eacute;l&eacute;phoner au bureau de
r&eacute;servations.)</p>

EOD;

$lang["intro_remail2"] = <<<EOD

<h2>R&eacute;cup&eacute;ration de r&eacute;servation</h2>

<p class='main'>Si l'e-mail que vous avez reçu contient une clé d'accès pour vos billets, 
vous pouvez le copier dans le champ suivant afin de pouvoir les imprimer à nouveau&nbsp;:</p>

<p class='main'>(Attention il ne s'agit pas du code de réservation)</p>

<p class='main'>Clé d'accès pour vos billets&nbsp;: %1\$s</p>

EOD;

$lang["intro_seats"] = 'Cliquez sur "Continuer" en bas de cette page une fois votre choix effectué';
$lang["is_default"] = 'Ceci est le spectacle par défaut.';
$lang["is_not_default"] = 'Ceci n\'est pas le spectacle par défaut.';

$lang["lastname"] = 'Nom';
$lang["legend"] = "Légende&nbsp;:";
$lang["link_bookinglist"] = 'Liste de réservations';
$lang["link_edit"] = 'la modification des spectacles';
$lang["link_index"] = 'l\'accueil';
$lang["link_pay"] = 'Informations Personnelles';
$lang["link_repr"] = 'Liste des représentations';
$lang["link_seats"] = 'Sélection de places';
$lang["login"] = 'Administration Système (Accès restreint aux personnes autorisées)&nbsp;:';
$lang["logout"] = "Déconnexion";

$lang["mail-anon"] = <<<EOD
Bonjour,
Voici des informations destinées à une personne n'ayant pas fourni
d'adresse e-mail.

Afin que, si nécessaire et si possible vous puissiez les contacter, voici
les informations qui m'ont été fournies lors de la réservation :

EOD;
//'

/* NOTE - Assumes spectacle must be preceeded by a (masculine)
 definite article. In the future we will need to integrate the article
 in the spectacle name and alter/extended it when needed (e.g. French
 de+le = du, German von+dem = vom, etc) */
$lang["mail-booked"] = <<<EOD
Merci pour votre réservation pour le %1\$s

Voici encore une fois les détails de votre réservation, que vous
devrez présenter à l'entrée du spectacle.

EOD;
//'

$lang["mail-cancel-however"] = <<<EOD
Cependant nous vous informons également que votre réservation du siège
suivant a été annulée :
EOD;
$lang["mail-cancel-however-p"] = <<<EOD
Cependant nous vous informons également que votre réservation des sièges
suivants a été annulée :
EOD;
$lang["mail-cancel"] = <<<EOD
Ce message est pour vous informer que votre réservation du siège suivant a
été annulée :
EOD;
$lang["mail-cancel-p"] = <<<EOD
Ce message est pour vous informer que votre réservation des sièges suivants a
été annulée :
EOD;

$lang["mail-gotmoney"] = "Nous avons reçu votre paiement pour le siège suivant :";
$lang["mail-gotmoney-p"] = "Nous avons reçu votre paiement pour les sièges suivants :";

$lang["mail-heywakeup"] = <<<EOD

Nous n'avons toujours pas reçu votre paiement pour la réservation du siège
suivant :

%1\$s
Si votre paiement a croisé ce message, merci de ne pas en tenir compte.

Si au contraire vous souhaitez renoncer à cette place nous vous serions
reconnaissants de nous le dire par retour de courrier. Sans réponse de votre
part nous annulerons la réservation de ce siège.
EOD;
//'
$lang["mail-heywakeup-p"] = <<<EOD

Nous n'avons toujours pas reçu votre paiement pour la réservation des sièges
suivants :

%1\$s
Si votre paiement a croisé ce message, merci de ne pas en tenir compte.

Si au contraire vous souhaitez renoncer à ces places nous vous serions
reconnaissants de nous le dire par retour de courrier. Sans réponse de votre
part nous annulerons la réservation de ces sièges..
EOD;
//'

$lang["mail-notconfirmed"] = <<<EOD
Votre réservation n'est pas encore confirmée ; les billets ne
donneront droit à la place qu'une fois le paiement reçu.
EOD;
//'
$lang["mail-notdeleted"] = "La réservation suivante est maintenue :";
$lang["mail-notdeleted-p"] = "Les réservations suivantes sont maintenues :";
$lang["mail-notpaid"] = 'Le siège suivant est réservé mais nous n\'avons pas encore reçu le paiement :';
$lang["mail-notpaid-p"] = 'Les sièges suivants sont réservés mais nous n\'avons pas encore reçu le paiement :';
$lang["mail-remail"] = <<<EOD
En réponse à votre requête sur %1\$s, voici un
récapitulatif des réservations que vous avez faites à ce jour, à cette adresse
email.


Clé d'accès pour vos billets : %2\$s


EOD;
//'
$lang["mail-reminder-p"] = <<<EOD
Nous vous rappelons en outre que les sièges suivants restent à payer :

%1\$s
Si vous souhaitez renoncer à ces places nous vous serions reconnaissants de
nous le dire par retour de courrier.

EOD;

$lang["mail-reminder"] = <<<EOD
Nous vous rappelons en outre que le siège suivant reste à payer :

%1\$s
Si vous souhaitez renoncer à cette place nous vous serions reconnaissants de
nous le dire par retour de courrier.

EOD;

$lang["mail-secondmail"] = <<<EOD
Vous recevrez un second
courrier lorsque nous aurons reçu votre paiement.

EOD;

$lang["mail-spammer"] = <<<EOD
Bonjour,

Quelqu'un (peut-être vous) a demandé à ce qu'un récapitulatif des
réservations faites pour %1\$s
(%2\$s) à cette adresse (%3\$s)
vous soit envoyé.

Nous n'avons cependant aucune réservation à cette adresse. Cela peut
signifier trois choses.

* Vous avez bien fait une réservation mais à une autre adresse.
* Vous aviez une réservation mais elle a été annulée. En principe vous
devriez avoir reçu un autre courrier à l'époque où ça a été fait.
* Un farceur cherche à remplir votre boîte aux lettres en pensant rester
anonyme.

Si vous avez des questions, nous vous serions reconnaissants de nous le
faire savoir par retour de courrier.

EOD;
// following always plural
$lang["mail-summary-p"] = 'Les sièges qui sont à présent confirmés (spectacles passés exclus) sont donc les suivants :';

$lang["mail-thankee"] = <<<EOD
Nous vous remercions pour votre réservation et vous souhaitons beaucoup
de plaisir.

EOD;

$lang["mail-oops"] = <<<EOD
Si vous pensez qu'il s'agit d'une erreur, merci de répondre aussi rapidement
que possible à ce courrier afin que nous puissions réactiver votre
réservation.
EOD;
    //'

$lang["mail-sent"] = 'Un e-mail vient également de vous être envoyé et contient les mêmes informations que cette page';
$lang["mail-sub-booked"] = 'Votre réservation';
$lang["mail-sub-cancel"] = 'Annulation de réservation';
$lang["mail-sub-gotmoney"] = 'Reçu de paiement';
$lang["mail-sub-heywakeup"] = 'Rappel';
$lang["mail-sub-remail"] = 'Récapitulatif de réservations';
$lang["make_default"] = 'Faire de ceci le spectacle par défaut (il y a à tout instant exactement un spectacle par défaut).';
$lang['make_payment'] = 'Effectuer le paiement';
$lang["max_seats"] = 'Nombre maximum de si&egrave;ges pouvant &ecirc;tre r&eacute;serv&eacute;s en une fois';
$lang["minute"] = 'm'; // abbreviated
$lang["minutes"] = 'minutes';
$lang["months"] = array(1=>"janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");

$lang["name"] = 'Nom';
$lang["new_spectacle"] = 'Création d\'un nouveau spectacle';
$lang["ninvite"] = 'Des invitations';
// following written on tickets for non-numbered seats.
$lang["nnseat"] = 'Place non numérotée';
$lang["nnseat-avail"] = 'Une place non-numérotée en catégorie %1$s est encore libre. Indiquez 1 (un) dans le champ suivant si vous souhaitez la réserver&nbsp;: ';
$lang["nnseat-header"] = 'Places non numérotées';
$lang["nnseats-avail"] = '%1$s places non numérotées en catégorie %2$s sont encore libres. Indiquez dans le champ suivant combien vous souhaitez en réserver&nbsp;: ';
$lang["nocancellations"] = 'Pas d\'annulation automatique';
$lang["noimage"] = 'Pas d\'image';
$lang["none"] = 'aucun';
$lang["noreminders"] = 'Pas de rappels envoyés';
$lang["notes"] = 'Notes';
$lang["notes-changed"] = 'Notes changées pour 1 réservation';
$lang["notes-changed-p"] = 'Notes changées pour %1$d réservations';
$lang["nreduced"] = 'À prix réduit';

$lang["orderby"] = 'Trier par %1$s';

$lang["panic"] = <<<EOD
<h2>VOTRE RÉSERVATION N'A PAS PU ÊTRE TRAITÉE</h2>
<p class='main'>L'administrateur du système a été
 averti et nous allons étudier et tâcher de corriger le
 problème</p>

<p class='main'>Merci de revenir dans quelques heures et tenter à nouveau
de réserver vos places</p>

<p class='main'>Nous nous excusons pour ce problème</p>
EOD;

$lang["params"] = 'Modifier les %1$sparamètres du système%2$s';
$lang["pay_cash"] = '&agrave; la caisse';
$lang["pay_ccard"] = 'par carte de crédit';
$lang["pay_other"] = "autre";
$lang["pay_postal"] = "par bulletin de versement";
$lang["payinfo_cash"] = <<<EOD
Les billets sont à payer au plus tard 30 minutes avant le début du spectacle,
sans quoi ils pourront être remis en vente.

EOD;
$lang["payinfo_ccard"] = <<<EOD
Le paiement ne nous est pas encore parvenu. Si d'ici à %1\$d jours ils
ne nous parvient pas, les billets pourront être remis en vente.

EOD;
//'
$lang["payinfo_postal"] = <<<EOD
Le total est à payer sur le %1\$s
d'ici à %2\$d jours ouvrables sans quoi ils pourront être
remis en vente.

EOD;
//'

$lang["paybutton"] = 'Merci d\'utiliser le bouton suivant pour procéder au paiement&nbsp;:&nbsp;%1$sContinuer%2$s';
$lang["payment"] = "Paiement&nbsp;:";
$lang['payment_received'] = 'Nous avons reçu votre paiement. Merci!';
$lang['paypal_id'] = 'Identifiant de transaction PayPal : ';
$lang['paypal_lastchance'] = "Nous sommes prêt à accepter votre paiement. Une fois avoir cliqué sur le bouton ci-dessous, vous serez transféré au site PayPal. Une fois que vous aurez effectué le paiement, vous serez redirigé à nouveau sur ce site. Les informations relative à vos cartes de crédit ou débit seront uniquement stockées chez PayPal.";
$land["paypal_purchase"] = 'Achat de billet par PayPal';
$lang["phone"] = 'Téléphone';
$lang['please_wait'] = 'Traitement de transaction en cours . . .  Veuillez patienter';
$lang["postal tax"] = 'Taxe bulletin de versement';
$lang["postalcode"] = 'Code postal';
$lang["poweredby"] = 'Géré par le système %1$s';
$lang["price"] = 'Prix';
$lang["price_discount"] = 'Prix réduit ';
$lang['prices']  = 'Prix des billets';
$lang["print_entries"] = 'Pour imprimer les billets correspondant aux entrées sélectionnées : %1$sImpression%2$s';

$lang["rebook"] = 'Refaire une réservation en prenant les entrées sélectionnées comme modèle&nbsp;: %1$sFaire la réservation%2$s';
$lang["rebook-info"] = 'Pour réactiver des réservations supprimées, d\'abord choisir le filtre "Supprimé" en haut à gauche de cette page';
$lang["reduction_or_charges"] = 'Reductions/frais';
$lang["remail"] = 'Vous avez perdu votre billet? Le lien suivant vous permet de le récupérer&nbsp;: %1$sRécupération de réservation%2$s';
$lang["reminders"] = 'Rappels';
$lang["reqd_info"] = <<<EOD
Il faut dans tous les cas fournir au moins un nom.
De plus si vous payez par carte de crédit, une adresse email ainsi
que l'adresse complète sont requises.
EOD;
//'
$lang["reserved-header"] = 'Places numérotées';
$lang["row"] = "Rang";

$lang["sameprice"] = 'Les prix sont les mêmes pour toutes les catégories';
$lang["save"] = 'Enregistrer';
$lang["seat_free"] = 'Siège encore libre&nbsp;:';
$lang["seat_occupied"] = 'Siège occupé&nbsp;:';
$lang["seats"] = 'Sièges';
$lang["seats_booked"] = 'Billets Réservés';
$lang["seeasalist"] = 'Voir sous forme de %1$sListe%2$s';
$lang["seeasamap"] = 'Le lien suivant vous permet de visualiser les réservations déjà effectuées pour cette représentation&nbsp;: %1$sPlan des réservations%2$s';
$lang["select"] = 'Selectionner';
$lang["select_payment"] = 'Sélectionnez un moyen de paiement&nbsp;:';
$lang["selected_1"] = '1 siège sélectionné';
$lang["selected_n"] = '%1$d sièges sélectionnés';
$lang["sentto"] = 'E-Mail envoyé à %1$s';
$lang["set_status_to"] = 'Les entrées sélectionnées sont à&nbsp;: ';
$lang["show_any"] = 'de toutes les représentations';
$lang["show_info"] = 'Le %1$s à %2$s, %3$s'; // date, time, location
$lang["show_name"] = 'Nom du spectacle';
$lang["show_not_stored"] = 'Impossible d\'enregistrer vos modifications. Merci de contacter votre administrateur système.';
$lang["show_stored"] = 'Modifications enregistrées.';
// we WILL run into problems (at least in French) when we have to choose between "du" or "de la" or "des"  or even "d'"...
// (%1$s is here the name of the spectacle, not a particular show)
$lang["showallspec"] = 'Montrer %1$stous les spectacles%2$s.';
$lang["showlist"] = 'Représentations du %1$s';
$lang["spectacle_name"] = 'Sélectionnez un spectacle';
$lang["state"] = "État";
$lang["st_any"] = "N'importe quel état";
$lang["st_booked"] = "Réservé";
$lang["st_deleted"] = "Annulé";
$lang["st_disabled"] = "Désactivé";
$lang["st_free"] = "Libre";
$lang["st_locked"] = "Transitoire";
$lang["st_notdeleted"] = "Pas supprimé";
$lang["st_paid"] = "Payé";
$lang["st_shaken"] = "Rappel envoyé";
$lang["st_tobepaid"] = "À payer";
$lang["stage"] = "Scène";
$lang["summary"] = "Résumé";

$lang["theatre_name"] = 'Théâtre';
$lang["time"] = 'Heure';
$lang["timestamp"] = "Réservation le";
$lang["title_mconfirm"] = 'Confirmez les informations sur le spectacle';
$lang["title_maint"] = 'Ajouter ou Modifier les spectacles';
$lang["to"] = 'À'; // in a temporal sense : from a to b
$lang["total"] = 'Total';

$lang["update"] = 'Actualiser';
$lang['us_state'] = 'État (USA uniquement)';

$lang["warn_badlogin"] = 'Connexion depuis une adresse non autorisée';
$lang["warn_bookings"] = 'Attention: Vous êtes sur le point de changer la date, l\'heure ou le prix d\'un spectacle pour lequel des billets ont déjà été vendus. Pensez à en informer les personnes ayant déjà acheté des billets. Si vous changez le prix des billets, différentes personnes pourront avoir payé des sommes différentes, ce qui peut provoquer une confusion. Merci d\'agir avec prudence.';
$lang["warn_close_in_1"] = 'Attention, la réservation en ligne pour ce spectacle va être fermée dans 1 minute';
$lang["warn_close_in_n"] = 'Attention, la réservation en ligne pour ce spectacle va être fermée dans %1$d minutes';
$lang["warn-nocontact"] = 'Attention, vous n\'avez pas fourni de moyen de contact ; Nous serons donc dans l\'incapacité de vous contacter en cas de problème relatif à votre réservation';
$lang["warn-nomail"] = 'Attention, vous n\'avez pas fourni d\'adresse e-mail ; Vous ne serez donc pas informé du status de votre réservation';
$lang["warn-nomatch"] = 'Pas de réservations correspondant à ces critères';
$lang["warn-nonsensicalcat"] = 'Attention vous avez demandé plus de places réduites que vous n\'avez sélectionné de places';
$lang["warn-nonsensicalcat-admin"] = "Attention votre nombre d'invitations plus votre nombre de places réduites est plus grand que le nombre total de places que vous avez sélectionnées";
$lang['warn_paypal_confirm'] = 'Nous n\'avons pas pu confirmer votre paiement PayPal. Merci de contacter la billetterie.';
$lang['warn_process_payment'] = 'Il y a eu un problème au cours de la finalisation de votre paiement.';
$lang["warn_show_confirm"] = 'Veuillez vérifier que les informations ci-dessus sont exactes. Pour les modifier à nouveau, utilisez le bouton Modification des spectacles. Une fois terminé, utilisez le bouton Enregistrer.';
$lang["warn_spectacle"] = 'Veuillez noter que vous ne pouvez pas changer le théâtre après la création du spectacle.';
$lang["we_accept"] = "Nous Acceptons"; // credit card logos come after that
$lang["weekdays"] = array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi");
$lang["workingdays"] = 'jours ouvrables';

$lang["youare"] = "Vous êtes";

$lang["zoneextra"] = ""; // intentionally left blank

/** add "at the" before the given noun. This is used with theatre
names (We have a problem in case we need to know if $w is masculine or
feminine or whatever - so far everything has been masculine so won't
extend the function until need appears :-) **/
function lang_at_the($w) {
  if (strstr("aeiouyAEIOUY",$w{0}))
    return "à l'$w";
  else
    return "au $w";
}

