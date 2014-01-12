<?php namespace freeseat;

/** Fran�ais (FRENCH) Language file.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info.

$Id: francais.php 400 2013-01-06 17:54:04Z tendays $
*/

require_once ( FS_PATH . "languages/default.php" );

$lang["_encoding"] = "ISO-8859-1";


$lang["access_denied"] = 'ACC�S INTERDIT - Votre session a d� expirer';
$lang["acknowledge"] = 'confirmer'; // used with check_st_update
$lang["address"] = 'Adresse';
$lang["admin"] = 'Fonctions Administratives';
$lang["admin_buy"] = 'R�server et %1$sacheter%2$s des billets (et invitations)';
$lang["alert"] = 'ALERTE';
$lang["are-you-ready"] = 'Veuillez v�rifier que les donn�es sont correctes puis cliquez Continuer.';

$lang["backto"] = 'Retour � %1$s';
$lang["book"] = 'R�server';
$lang["bookagain"] = 'Faire une %1$sNouvelle r�servation%2$s';
$lang["bookid"] = 'Code';
$lang["book_adminonly"] = 'R�servations ferm�es au public';
$lang["book_submit"] = 'Placer la r�servation';
$lang["booking_st"] = 'R�servations dans l\'�tat %1$s';
$lang["bookinglist"] = '%1$sConsulter/Modifier les r�servations%2$s (Entre autres pour accuser r�ception d\'un paiement)';
$lang["bookingmap"] = 'Plan des r�servations';
$lang["buy"] = 'R�server et %1$sacheter%2$s des billets';

$lang["cancel"] = 'Annuler';
$lang["cancellations"] = 'Annulations';
$lang["cat"] = "Tarif";
$lang["cat_free"] = "Invitation";
$lang["cat_normal"] = "Normal";
$lang["cat_reduced"] = "R�duit";
$lang["ccard_failed"] = '%1$s LORS DE LA GESTION D\'UNE NOTIFICATION CARTE DE CREDIT\n\n\n';
$lang["ccard_partner"] = 'Le paiement est s�curis� par&nbsp;%1$s';
$lang["change_date"] = "Corriger la date";
$lang["change_pay"] = 'Corriger les %1$sInformations personnelles et de paiement%2$s';
$lang["change_seats"] = 'Corriger la %1$sS�lection de places%2$s';
$lang["check_st_update"] = 'V�rifiez que la liste suivante de r�servations � %1$s est correcte puis cliquez sur confirmation en bas de cette page';
$lang["choose_show"] = 'S�lectionnez un spectacle';
$lang["city"] = "Ville";
$lang["confirmation"] = 'Confirmation';
$lang["continue"] = "Continuer";
$lang["country"] = "Pays";
$lang["class"] = "Cat�gorie";
$lang["closed"] = "Ferm�";
$lang["col"] = "Si�ge";
$lang["create_show"] = 'Nouveau spectacle';

$lang["date"] = "Date";
$lang['datesandtimes'] = 'Repr�sentations';
$lang["day"] = 'j'; // abbreviated
$lang["days"] = 'jours';
$lang["DELETE"] = "SUPPRIMER"; // used in check_st_update
$lang["description"] = 'Description';
$lang["diffprice"] = '� chaque cat�gorie correspond une couleur comme indiqu� ci-dessous';
$lang["disabled"] = "d�sactiv�"; // for shows or payment methods
$lang["dump_csv"] = 'Contenu de la base de donn�es au format csv : %1$sbookings.csv%2$s';

$lang['editshows'] = '%1$sAjouter et Modifier%2$s les spectacles';
$lang["email"] = "E-mail";
$lang["err_bademail"] = 'L\'adresse e-mail que vous avez donn�e ne semble pas valide';
$lang["err_badip"] = 'Vous n\'�tes pas autoris� � voir ce document';
$lang["err_badkey"] = 'La cl� n\'�tait pas correcte. Vous pouvez r�essayer (envoyez un mail � %1$s si vous n\'y parvenez pas)';
$lang["err_bookings"] = 'Erreur d\'acc�s aux r�servations';
$lang["err_ccard_insuff"] = 'Pas assez d\'argent pour payer le si�ge %1$d co�tant %2$d CHF avec seulement %3$d CHF !';
$lang["err_ccard_mysql"] = 'Erreur (mysql) pendant l\'enregistrement d\'une transaction par carte de cr�dit';
$lang["err_ccard_nomatch"] = 'push (%1$s) and pull (%2$s) ne correspondent pas (la valeur pull sera utilis�e)';
$lang["err_ccard_pay"] = 'Impossible d\'enregistrer le payement du si�ge %1$d par carte de cr�dit (v�rifiez les journaux syst�me - peut-�tre qu\'il a d�j� �t� pay�)!';
$lang["err_ccard_repay"] = 'Double demande de payment (par carte de cr�dit) pour le si�ge %1$d par carte de cr�dit!';
$lang["err_ccard_toomuch"] = 'Nous avons re�u trop d\'argent! %1$d sur %2$d CHF non utilis�s';
$lang["err_ccard_user"] = 'Le paiement n\'a pas pu �tre effectu� - vous pouvez essayer � nouveau, ou envoyer un e-mail � %1$s';
$lang["err_checkseats"] = 'Veuillez placer des croix sur les si�ges que vous souhaitez r�server';
$lang["err_closed"] = 'D�sol�, la r�servation en ligne vient d\'�tre ferm�e pour cette repr�sentation';
$lang["err_connect"] = 'Erreur de connexion : ';
$lang["err_cronusage"] = 'Argument obligatoire manquant (mot de passe administrateur pour la base de donn�es)\n';
$lang["err_email"] = 'Les r�servations s�lectionn�es ne sont pas toutes � la m�me adresse email (je garde la premi�re)';
$lang["err_filetype"] = 'Le fichier n\'est pas de type ';
$lang["err_ic_firstname"] = 'Les r�servations s�lectionn�es n\'ont pas toutes le m�me pr�nom (je garde le premier)';
$lang["err_ic_lastname"] = 'Les r�servations s�lectionn�es n\'ont pas toutes au m�me nom (je garde le premier)';
$lang["err_ic_payment"] = 'Les r�servations s�lectionn�es n\'ont pas toutes le m�me mode de paiement (je garde le premier)';
$lang["err_ic_phone"] = 'Les r�servations s�lectionn�es n\'ont pas toutes le m�me num�ro de t�l�phone (je garde le premier)';
$lang["err_ic_showid"] = 'Les r�servations s�lectionn�es ne sont pas toutes de la m�me repr�sentation...';
$lang["err_noaddress"] = 'Pour payer par carte de cr�dit il vous faut fournir une adresse e-mail ainsi qu\'une adresse postale compl�te.';
$lang["err_ccard_cfg"] = 'Le paiement par carte de cr�dit doit �tre configur� dans config.php avant qu\'il ne puisse �tre activ�';
$lang["err_noavailspec"] = 'La billetterie de tous les spectacles est ferm�e.';
$lang["err_nodates"] = 'Il faut cr�er au moins une repr�sentation pour ce spectacle.';
$lang["err_noname"] = 'Veuillez indiquer au moins un nom';
$lang["err_noprices"] = 'Veuillez indiquer les prix des places pour au moins une cat�gorie.';
$lang["err_nospec"] = 'Veuillez indiquer le nom du spectacle.';
$lang["err_notheatre"] = 'Veuillez s�lectionner un th��tre.';
$lang["err_occupied"] = "D�sol�, un des si�ges que vous avez choisis vient d'�tre r�serv�";
$lang["err_paymentclosed"] = 'Le paiement %1$s vient d\'�tre ferm� pour cette repr�sentation';
$lang["err_payreminddelay"] = 'Le d�lai de paiement doit �tre plus long que le d�lai de rappel';
$lang["err_postaltax"] = 'Prix trop �lev� pour paiement par bulletin de versement';
$lang["err_price"] = 'Erreur � l\'obtention du prix';
$lang["err_pw"] = 'Utilisateur inconnu ou mot de passe incorrect. Veuillez r�essayer';
$lang["err_scriptauth"] = 'La requ�te au script %1$s a �t� refus�e';
$lang["err_scriptconnect"] = 'La connexion au script %1$s a �chou�';
$lang["err_seat"] = 'Erreur d\'acc�s au si�ge';
$lang["err_seatcount"] = 'Vous ne pouvez pas r�server autant de si�ges en une fois';
$lang["err_seatlocks"] = 'Erreur de verrouillage de si�ge';
$lang["err_session"] = 'Vous n\'avez pas (ou plus) de r�servation en cours (les "cookies" sont-ils activ�s sur votre navigateur?)';
$lang["err_setbookstatus"] = 'Impossible de changer l\'�tat du si�ge';
$lang["err_shellonly"] = 'ACC�S INTERDIT - ce script est r�serv� � un acc�s shell';
$lang["err_show_entry"] = 'Vous ne pouvez pas enregistrer ce spectacle avant d\'avoir fourni les �l�ments manquants.';
$lang["err_showid"] = 'Mauvais num�ro de repr�sentation';
$lang["err_smtp"] = 'Attention: �chec d\'envoi de message: %1$s - R�ponse du serveur: %2$s';
$lang["err_spectacle"] = 'Erreur d\'acc�s aux donn�es du spectacle';
$lang["err_spectacleid"] = 'Mauvais num�ro de spectacle';
$lang["err_upload"] = 'Le t�l�chargement a �chou�';
$lang["expiration"] = 'Expiration';
$lang["expired"] = 'd�j� expir�';

$lang["failure"] = 'CATASTROPHE';
$lang["file"] = 'Fichier: '; 
$lang["filter"] = 'Montrer&nbsp;:'; // filter form header in bookinglist
$lang["firstname"] = 'Pr�nom';
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
$lang["index_head"] = 'R�servations pour le spectacle';
$lang["intro_ccard"] = <<<EOD
 <h2>Merci pour votre r�servation</h2>

<p class="main">Les si�ges sont � pr�sent r�serv�s � votre nom.</p>
EOD;

$lang["intro_confirm"] = 'Merci de v�rifier et le cas �ch�ant corriger les informations suivantes avant de valider votre r�servation.';
$lang["intro_finish"] = 'Cette page constitue votre billet d\'entr�e. Imprimez-la et emportez-la avec vous le soir du spectacle.';
$lang["intro_params"] = <<<EOD
<h2>Disponibilit� des moyens de paiement</h2>

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
concernant uniquement en jours ouvrables. Si un d�lai est dans un
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

<p class='main'>Si l'e-mail que vous avez re�u contient une cl� d'acc�s pour vos billets, 
vous pouvez le copier dans le champ suivant afin de pouvoir les imprimer � nouveau&nbsp;:</p>

<p class='main'>(Attention il ne s'agit pas du code de r�servation)</p>

<p class='main'>Cl� d'acc�s pour vos billets&nbsp;: %1\$s</p>

EOD;

$lang["intro_seats"] = 'Cliquez sur "Continuer" en bas de cette page une fois votre choix effectu�';
$lang["is_default"] = 'Ceci est le spectacle par d�faut.';
$lang["is_not_default"] = 'Ceci n\'est pas le spectacle par d�faut.';

$lang["lastname"] = 'Nom';
$lang["legend"] = "L�gende&nbsp;:";
$lang["link_bookinglist"] = 'Liste de r�servations';
$lang["link_edit"] = 'la modification des spectacles';
$lang["link_index"] = 'l\'accueil';
$lang["link_pay"] = 'Informations Personnelles';
$lang["link_repr"] = 'Liste des repr�sentations';
$lang["link_seats"] = 'S�lection de places';
$lang["login"] = 'Administration Syst�me (Acc�s restreint aux personnes autoris�es)&nbsp;:';
$lang["logout"] = "D�connexion";

$lang["mail-anon"] = <<<EOD
Bonjour,
Voici des informations destin�es � une personne n'ayant pas fourni
d'adresse e-mail.

Afin que, si n�cessaire et si possible vous puissiez les contacter, voici
les informations qui m'ont �t� fournies lors de la r�servation :

EOD;
//'

/* NOTE - Assumes spectacle must be preceeded by a (masculine)
 definite article. In the future we will need to integrate the article
 in the spectacle name and alter/extended it when needed (e.g. French
 de+le = du, German von+dem = vom, etc) */
$lang["mail-booked"] = <<<EOD
Merci pour votre r�servation pour le %1\$s

Voici encore une fois les d�tails de votre r�servation, que vous
devrez pr�senter � l'entr�e du spectacle.

EOD;
//'

$lang["mail-cancel-however"] = <<<EOD
Cependant nous vous informons �galement que votre r�servation du si�ge
suivant a �t� annul�e :
EOD;
$lang["mail-cancel-however-p"] = <<<EOD
Cependant nous vous informons �galement que votre r�servation des si�ges
suivants a �t� annul�e :
EOD;
$lang["mail-cancel"] = <<<EOD
Ce message est pour vous informer que votre r�servation du si�ge suivant a
�t� annul�e :
EOD;
$lang["mail-cancel-p"] = <<<EOD
Ce message est pour vous informer que votre r�servation des si�ges suivants a
�t� annul�e :
EOD;

$lang["mail-gotmoney"] = "Nous avons re�u votre paiement pour le si�ge suivant :";
$lang["mail-gotmoney-p"] = "Nous avons re�u votre paiement pour les si�ges suivants :";

$lang["mail-heywakeup"] = <<<EOD

Nous n'avons toujours pas re�u votre paiement pour la r�servation du si�ge
suivant :

%1\$s
Si votre paiement a crois� ce message, merci de ne pas en tenir compte.

Si au contraire vous souhaitez renoncer � cette place nous vous serions
reconnaissants de nous le dire par retour de courrier. Sans r�ponse de votre
part nous annulerons la r�servation de ce si�ge.
EOD;
//'
$lang["mail-heywakeup-p"] = <<<EOD

Nous n'avons toujours pas re�u votre paiement pour la r�servation des si�ges
suivants :

%1\$s
Si votre paiement a crois� ce message, merci de ne pas en tenir compte.

Si au contraire vous souhaitez renoncer � ces places nous vous serions
reconnaissants de nous le dire par retour de courrier. Sans r�ponse de votre
part nous annulerons la r�servation de ces si�ges..
EOD;
//'

$lang["mail-notconfirmed"] = <<<EOD
Votre r�servation n'est pas encore confirm�e ; les billets ne
donneront droit � la place qu'une fois le paiement re�u.
EOD;
//'
$lang["mail-notdeleted"] = "La r�servation suivante est maintenue :";
$lang["mail-notdeleted-p"] = "Les r�servations suivantes sont maintenues :";
$lang["mail-notpaid"] = 'Le si�ge suivant est r�serv� mais nous n\'avons pas encore re�u le paiement :';
$lang["mail-notpaid-p"] = 'Les si�ges suivants sont r�serv�s mais nous n\'avons pas encore re�u le paiement :';
$lang["mail-remail"] = <<<EOD
En r�ponse � votre requ�te sur %1\$s, voici un
r�capitulatif des r�servations que vous avez faites � ce jour, � cette adresse
email.


Cl� d'acc�s pour vos billets : %2\$s


EOD;
//'
$lang["mail-reminder-p"] = <<<EOD
Nous vous rappelons en outre que les si�ges suivants restent � payer :

%1\$s
Si vous souhaitez renoncer � ces places nous vous serions reconnaissants de
nous le dire par retour de courrier.

EOD;

$lang["mail-reminder"] = <<<EOD
Nous vous rappelons en outre que le si�ge suivant reste � payer :

%1\$s
Si vous souhaitez renoncer � cette place nous vous serions reconnaissants de
nous le dire par retour de courrier.

EOD;

$lang["mail-secondmail"] = <<<EOD
Vous recevrez un second
courrier lorsque nous aurons re�u votre paiement.

EOD;

$lang["mail-spammer"] = <<<EOD
Bonjour,

Quelqu'un (peut-�tre vous) a demand� � ce qu'un r�capitulatif des
r�servations faites pour %1\$s
(%2\$s) � cette adresse (%3\$s)
vous soit envoy�.

Nous n'avons cependant aucune r�servation � cette adresse. Cela peut
signifier trois choses.

* Vous avez bien fait une r�servation mais � une autre adresse.
* Vous aviez une r�servation mais elle a �t� annul�e. En principe vous
devriez avoir re�u un autre courrier � l'�poque o� �a a �t� fait.
* Un farceur cherche � remplir votre bo�te aux lettres en pensant rester
anonyme.

Si vous avez des questions, nous vous serions reconnaissants de nous le
faire savoir par retour de courrier.

EOD;
// following always plural
$lang["mail-summary-p"] = 'Les si�ges qui sont � pr�sent confirm�s (spectacles pass�s exclus) sont donc les suivants :';

$lang["mail-thankee"] = <<<EOD
Nous vous remercions pour votre r�servation et vous souhaitons beaucoup
de plaisir.

EOD;

$lang["mail-oops"] = <<<EOD
Si vous pensez qu'il s'agit d'une erreur, merci de r�pondre aussi rapidement
que possible � ce courrier afin que nous puissions r�activer votre
r�servation.
EOD;
    //'

$lang["mail-sent"] = 'Un e-mail vient �galement de vous �tre envoy� et contient les m�mes informations que cette page';
$lang["mail-sub-booked"] = 'Votre r�servation';
$lang["mail-sub-cancel"] = 'Annulation de r�servation';
$lang["mail-sub-gotmoney"] = 'Re�u de paiement';
$lang["mail-sub-heywakeup"] = 'Rappel';
$lang["mail-sub-remail"] = 'R�capitulatif de r�servations';
$lang["make_default"] = 'Faire de ceci le spectacle par d�faut (il y a � tout instant exactement un spectacle par d�faut).';
$lang['make_payment'] = 'Effectuer le paiement';
$lang["max_seats"] = 'Nombre maximum de si&egrave;ges pouvant &ecirc;tre r&eacute;serv&eacute;s en une fois';
$lang["minute"] = 'm'; // abbreviated
$lang["minutes"] = 'minutes';
$lang["months"] = array(1=>"janvier","f�vrier","mars","avril","mai","juin","juillet","ao�t","septembre","octobre","novembre","d�cembre");

$lang["name"] = 'Nom';
$lang["new_spectacle"] = 'Cr�ation d\'un nouveau spectacle';
$lang["ninvite"] = 'Des invitations';
// following written on tickets for non-numbered seats.
$lang["nnseat"] = 'Place non num�rot�e';
$lang["nnseat-avail"] = 'Une place non-num�rot�e en cat�gorie %1$s est encore libre. Indiquez 1 (un) dans le champ suivant si vous souhaitez la r�server&nbsp;: ';
$lang["nnseat-header"] = 'Places non num�rot�es';
$lang["nnseats-avail"] = '%1$s places non num�rot�es en cat�gorie %2$s sont encore libres. Indiquez dans le champ suivant combien vous souhaitez en r�server&nbsp;: ';
$lang["nocancellations"] = 'Pas d\'annulation automatique';
$lang["noimage"] = 'Pas d\'image';
$lang["none"] = 'aucun';
$lang["noreminders"] = 'Pas de rappels envoy�s';
$lang["notes"] = 'Notes';
$lang["notes-changed"] = 'Notes chang�es pour 1 r�servation';
$lang["notes-changed-p"] = 'Notes chang�es pour %1$d r�servations';
$lang["nreduced"] = '� prix r�duit';

$lang["orderby"] = 'Trier par %1$s';

$lang["panic"] = <<<EOD
<h2>VOTRE R�SERVATION N'A PAS PU �TRE TRAIT�E</h2>
<p class='main'>L'administrateur du syst�me a �t�
 averti et nous allons �tudier et t�cher de corriger le
 probl�me</p>

<p class='main'>Merci de revenir dans quelques heures et tenter � nouveau
de r�server vos places</p>

<p class='main'>Nous nous excusons pour ce probl�me</p>
EOD;

$lang["params"] = 'Modifier les %1$sparam�tres du syst�me%2$s';
$lang["pay_cash"] = '&agrave; la caisse';
$lang["pay_ccard"] = 'par carte de cr�dit';
$lang["pay_other"] = "autre";
$lang["pay_postal"] = "par bulletin de versement";
$lang["payinfo_cash"] = <<<EOD
Les billets sont � payer au plus tard 30 minutes avant le d�but du spectacle,
sans quoi ils pourront �tre remis en vente.

EOD;
$lang["payinfo_ccard"] = <<<EOD
Le paiement ne nous est pas encore parvenu. Si d'ici � %1\$d jours ils
ne nous parvient pas, les billets pourront �tre remis en vente.

EOD;
//'
$lang["payinfo_postal"] = <<<EOD
Le total est � payer sur le %1\$s
d'ici � %2\$d jours ouvrables sans quoi ils pourront �tre
remis en vente.

EOD;
//'

$lang["paybutton"] = 'Merci d\'utiliser le bouton suivant pour proc�der au paiement&nbsp;:&nbsp;%1$sContinuer%2$s';
$lang["payment"] = "Paiement&nbsp;:";
$lang['payment_received'] = 'Nous avons re�u votre paiement. Merci!';
$lang['paypal_id'] = 'Identifiant de transaction PayPal : ';
$lang['paypal_lastchance'] = "Nous sommes pr�t � accepter votre paiement. Une fois avoir cliqu� sur le bouton ci-dessous, vous serez transf�r� au site PayPal. Une fois que vous aurez effectu� le paiement, vous serez redirig� � nouveau sur ce site. Les informations relative � vos cartes de cr�dit ou d�bit seront uniquement stock�es chez PayPal.";
$land["paypal_purchase"] = 'Achat de billet par PayPal';
$lang["phone"] = 'T�l�phone';
$lang['please_wait'] = 'Traitement de transaction en cours . . .  Veuillez patienter';
$lang["postal tax"] = 'Taxe bulletin de versement';
$lang["postalcode"] = 'Code postal';
$lang["poweredby"] = 'G�r� par le syst�me %1$s';
$lang["price"] = 'Prix';
$lang["price_discount"] = 'Prix r�duit ';
$lang['prices']  = 'Prix des billets';
$lang["print_entries"] = 'Pour imprimer les billets correspondant aux entr�es s�lectionn�es : %1$sImpression%2$s';

$lang["rebook"] = 'Refaire une r�servation en prenant les entr�es s�lectionn�es comme mod�le&nbsp;: %1$sFaire la r�servation%2$s';
$lang["rebook-info"] = 'Pour r�activer des r�servations supprim�es, d\'abord choisir le filtre "Supprim�" en haut � gauche de cette page';
$lang["reduction_or_charges"] = 'Reductions/frais';
$lang["remail"] = 'Vous avez perdu votre billet? Le lien suivant vous permet de le r�cup�rer&nbsp;: %1$sR�cup�ration de r�servation%2$s';
$lang["reminders"] = 'Rappels';
$lang["reqd_info"] = <<<EOD
Il faut dans tous les cas fournir au moins un nom.
De plus si vous payez par carte de cr�dit, une adresse email ainsi
que l'adresse compl�te sont requises.
EOD;
//'
$lang["reserved-header"] = 'Places num�rot�es';
$lang["row"] = "Rang";

$lang["sameprice"] = 'Les prix sont les m�mes pour toutes les cat�gories';
$lang["save"] = 'Enregistrer';
$lang["seat_free"] = 'Si�ge encore libre&nbsp;:';
$lang["seat_occupied"] = 'Si�ge occup�&nbsp;:';
$lang["seats"] = 'Si�ges';
$lang["seats_booked"] = 'Billets R�serv�s';
$lang["seeasalist"] = 'Voir sous forme de %1$sListe%2$s';
$lang["seeasamap"] = 'Le lien suivant vous permet de visualiser les r�servations d�j� effectu�es pour cette repr�sentation&nbsp;: %1$sPlan des r�servations%2$s';
$lang["select"] = 'Selectionner';
$lang["select_payment"] = 'S�lectionnez un moyen de paiement&nbsp;:';
$lang["selected_1"] = '1 si�ge s�lectionn�';
$lang["selected_n"] = '%1$d si�ges s�lectionn�s';
$lang["sentto"] = 'E-Mail envoy� � %1$s';
$lang["set_status_to"] = 'Les entr�es s�lectionn�es sont �&nbsp;: ';
$lang["show_any"] = 'de toutes les repr�sentations';
$lang["show_info"] = 'Le %1$s � %2$s, %3$s'; // date, time, location
$lang["show_name"] = 'Nom du spectacle';
$lang["show_not_stored"] = 'Impossible d\'enregistrer vos modifications. Merci de contacter votre administrateur syst�me.';
$lang["show_stored"] = 'Modifications enregistr�es.';
// we WILL run into problems (at least in French) when we have to choose between "du" or "de la" or "des"  or even "d'"...
// (%1$s is here the name of the spectacle, not a particular show)
$lang["showallspec"] = 'Montrer %1$stous les spectacles%2$s.';
$lang["showlist"] = 'Repr�sentations du %1$s';
$lang["spectacle_name"] = 'S�lectionnez un spectacle';
$lang["state"] = "�tat";
$lang["st_any"] = "N'importe quel �tat";
$lang["st_booked"] = "R�serv�";
$lang["st_deleted"] = "Annul�";
$lang["st_disabled"] = "D�sactiv�";
$lang["st_free"] = "Libre";
$lang["st_locked"] = "Transitoire";
$lang["st_notdeleted"] = "Pas supprim�";
$lang["st_paid"] = "Pay�";
$lang["st_shaken"] = "Rappel envoy�";
$lang["st_tobepaid"] = "� payer";
$lang["stage"] = "Sc�ne";
$lang["summary"] = "R�sum�";

$lang["theatre_name"] = 'Th��tre';
$lang["time"] = 'Heure';
$lang["timestamp"] = "R�servation le";
$lang["title_mconfirm"] = 'Confirmez les informations sur le spectacle';
$lang["title_maint"] = 'Ajouter ou Modifier les spectacles';
$lang["to"] = '�'; // in a temporal sense : from a to b
$lang["total"] = 'Total';

$lang["update"] = 'Actualiser';
$lang['us_state'] = '�tat (USA uniquement)';

$lang["warn_badlogin"] = 'Connexion depuis une adresse non autoris�e';
$lang["warn_bookings"] = 'Attention: Vous �tes sur le point de changer la date, l\'heure ou le prix d\'un spectacle pour lequel des billets ont d�j� �t� vendus. Pensez � en informer les personnes ayant d�j� achet� des billets. Si vous changez le prix des billets, diff�rentes personnes pourront avoir pay� des sommes diff�rentes, ce qui peut provoquer une confusion. Merci d\'agir avec prudence.';
$lang["warn_close_in_1"] = 'Attention, la r�servation en ligne pour ce spectacle va �tre ferm�e dans 1 minute';
$lang["warn_close_in_n"] = 'Attention, la r�servation en ligne pour ce spectacle va �tre ferm�e dans %1$d minutes';
$lang["warn-nocontact"] = 'Attention, vous n\'avez pas fourni de moyen de contact ; Nous serons donc dans l\'incapacit� de vous contacter en cas de probl�me relatif � votre r�servation';
$lang["warn-nomail"] = 'Attention, vous n\'avez pas fourni d\'adresse e-mail ; Vous ne serez donc pas inform� du status de votre r�servation';
$lang["warn-nomatch"] = 'Pas de r�servations correspondant � ces crit�res';
$lang["warn-nonsensicalcat"] = 'Attention vous avez demand� plus de places r�duites que vous n\'avez s�lectionn� de places';
$lang["warn-nonsensicalcat-admin"] = "Attention votre nombre d'invitations plus votre nombre de places r�duites est plus grand que le nombre total de places que vous avez s�lectionn�es";
$lang['warn_paypal_confirm'] = 'Nous n\'avons pas pu confirmer votre paiement PayPal. Merci de contacter la billetterie.';
$lang['warn_process_payment'] = 'Il y a eu un probl�me au cours de la finalisation de votre paiement.';
$lang["warn_show_confirm"] = 'Veuillez v�rifier que les informations ci-dessus sont exactes. Pour les modifier � nouveau, utilisez le bouton Modification des spectacles. Une fois termin�, utilisez le bouton Enregistrer.';
$lang["warn_spectacle"] = 'Veuillez noter que vous ne pouvez pas changer le th��tre apr�s la cr�ation du spectacle.';
$lang["we_accept"] = "Nous Acceptons"; // credit card logos come after that
$lang["weekdays"] = array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi");
$lang["workingdays"] = 'jours ouvrables';

$lang["youare"] = "Vous �tes";

$lang["zoneextra"] = ""; // intentionally left blank

/** add "at the" before the given noun. This is used with theatre
names (We have a problem in case we need to know if $w is masculine or
feminine or whatever - so far everything has been masculine so won't
extend the function until need appears :-) **/
function lang_at_the($w) {
  if (strstr("aeiouyAEIOUY",$w{0}))
    return "� l'$w";
  else
    return "au $w";
}

