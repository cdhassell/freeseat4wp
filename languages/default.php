<?php namespace freeseat;

$lang["_encoding"] = "ISO-8859-1";


$lang["access_denied"] = 'ACCESS DENIED - Your session must have expired';
$lang["acknowledge"] = 'Confirm Payment'; // used with check_st_update
$lang["address"] = 'Address';
$lang["admin"] = 'Administrative Functions';
$lang["admin_buy"] = 'Select and %1$sBuy%2$s tickets';
$lang["alert"] = 'ALERT';
$lang["are-you-ready"] = 'Please review your entries to make sure they are correct, then click Continue.';

$lang["backto"] = 'Back to %1$s';
$lang["book"] = 'Select';
$lang["bookagain"] = 'Make %1$sAnother reservation%2$s';
$lang["bookid"] = 'ID';
$lang["book_adminonly"] = 'No seats available';
$lang["book_submit"] = 'Make reservation';
$lang["booking_st"] = 'Reservations in state %1$s';
$lang["bookinglist"] = 'Browse/Modify %1$sreservations%2$s (e.g. for acknowledging a payment)';
$lang["bookingmap"] = 'Reservation Map';
$lang["buy"] = 'Select and %1$sBuy%2$s tickets';

$lang["cancel"] = "Cancel";
$lang["cancellations"] = "Cancellations";
$lang["cat"] = 'Rate';
$lang["cat_free"] = 'Complimentary';
$lang["cat_normal"] = 'Adults';
$lang["cat_reduced"] = 'Children';
$lang["ccard_failed"] = '%1$s WHILE PROCESSING A CREDIT CARD NOTIFICATION\n\n\n';
$lang["ccard_partner"] = 'Credit card payment made secure by&nbsp;%1$s';
$lang["change_date"] = 'Change date';
$lang["change_pay"] = 'Change %1$sPersonal and payment information%2$s';
$lang["change_seats"] = 'Change %1$sSeat selection%2$s';
$lang["check_st_update"] = 'To %1$s these entries, click Confirmation';
$lang["choose_reduced"] = 'Please select any reduced price tickets below.';
$lang["choose_show"] = 'Please choose a show';
$lang["city"] = 'City';
$lang["comment"] = 'Comment';
$lang["confirmation"] = 'Confirmation';
$lang["continue"] = 'Continue';
$lang["country"] = 'Country';
$lang["class"] = 'Class';
$lang["closed"] = 'Closed';
$lang["col"] = 'Seat';
$lang["create_show"] = 'Create a new show';

$lang["date"] = 'Date';
$lang['datesandtimes'] = 'Show Dates';
$lang["date_title"] = 'Date<br>(yyyy-mm-dd)';
$lang["day"] = 'd'; // abbreviated
$lang["days"] = 'days';
$lang["DELETE"] = 'Delete'; // used in check_st_update
$lang["description"] = 'Description';
$lang["diffprice"] = 'Tickets for each class of seat are color-coded as shown below';
$lang["disabled"] = "disabled"; // for shows or payment methods
$lang["dump_csv"] = 'Database dump in csv format: %1$sAll details%2$s';
$lang['editshows'] = 'Add or edit %1$sshow%2$s information';
$lang["email"] = 'Email';
$lang["err_bademail"] = 'The e-mail address you gave does not seem to be valid';
$lang["err_badip"] = 'You are not authorised to access this file';
$lang["err_badkey"] = 'The access key was not correct. Please try again. If unsuccessful, send an email to %1$s for assistance.';
$lang["err_bookings"] = 'Error accessing reservations';
$lang["err_ccard_cfg"] = 'Credit card payment must be setup in config.php before it can be enabled'; // § NEW in 1.2.1
$lang["err_ccard_insuff"] = 'Can\'t pay seat %1$d costing %4$s %2$d with only %4$s %3$d available!';
$lang["err_ccard_mysql"] = '(Mysql) error while logging a credit card transaction';
$lang["err_ccard_nomatch"] = 'push (%1$s) and pull (%2$s) do not match (using pull amount)';
$lang["err_ccard_pay"] = 'Can\'t record credit card payment for seat %1$d ! (check logs - maybe it has already been paid)';
$lang["err_ccard_repay"] = 'Received a (credit card) payment acknowledgement for seat %1$d that has already been paid !';
$lang["err_ccard_toomuch"] = 'We got too much money! %3$s %1$d out of %3$s %2$d unused.';
$lang["err_ccard_user"] = 'There was a problem with your payment. You may try again, or send an email to %1$s for assistance.';
$lang["err_checkseats"] = 'Please Select Your Seats';
$lang["err_closed"] = 'Sorry, online reservations for this show are now closed';
$lang["err_config"] = 'Check server configuration on: '; // § NEW
$lang["err_connect"] = 'Connection error : ';
$lang["err_cronusage"] = "One argument expected (database reservation admin password)\n";
$lang["err_email"] = 'Selected reservations don\'t all have the same email address. We will keep the first one.';
$lang["err_filetype"] = 'Wrong file type, was expecting: ';
$lang["err_ic_firstname"] =    'Selected reservations don\'t all have the same first name (keeping the first one)';
$lang["err_ic_lastname"] =    'Selected reservations don\'t all have the same last name (keeping the first one)';
$lang["err_ic_payment"] = 'Selected reservations don\'t all have the same payment method (keeping the first one)';
$lang["err_ic_phone"] =   'Selected reservations don\'t all have the same phone number (keeping the first one)';
$lang["err_ic_showid"] =  'Selected reservations are not all for the same show...';
$lang["err_noaddress"] = 'For credit card payment you must complete all of the fields.';
$lang["err_noavailspec"] = 'Sorry, but there are no shows available for booking at the moment'; // § NEW IN 1.2.2b
$lang["err_nodates"] = 'No show dates are set for this show.';
$lang["err_noname"] = 'Please fill in the requested data fields';
$lang["err_noprices"] = 'No prices are set for this show.';
$lang["err_noseats"] = 'No seats to display'; // § NEW
$lang["err_nospec"] = 'You must provide a name for this show.';
$lang["err_notheatre"] = 'Please select a theatre seatmap.';
$lang["err_occupied"] = 'Sorry, one of the seats you selected has just been reserved';
$lang["err_paymentclosed"] = 'The %1$s payment method has been closed for this show';
$lang["err_payreminddelay"] = 'Payment delay must be longer than remind delay';
$lang["err_postaltax"] = 'Price is too high for postal payment';
$lang["err_price"] = 'Can\'t get seat price';
$lang["err_pw"] = 'Unknown user or bad password. Please try again';
$lang["err_scriptauth"] = 'Request to script %1$s rejected';
$lang["err_scriptconnect"] = 'Connecting to the %1$s script failed';
$lang["err_seat"] = 'Error accessing seat';
$lang["err_seatcount"] = 'You cannot reserve so many seats at a time';
$lang["err_seatlocks"] = 'Error locking seat';
$lang["err_session"] = 'Your reservation session has expired. (Are "cookies" activated in your browser?)';
$lang["err_setbookstatus"] = 'Error while changing seat status';
$lang["err_shellonly"] = 'ACCESS DENIED - Accessing this page requires shell access';
$lang["err_show_entry"] = 'This show cannot be saved until you supply the missing items.<br>Please click the Edit link to go back to the edit screen.';
$lang["err_showid"] = 'Bad show number';
$lang["err_smtp"] = 'Warning: sending message failed: %1$s - Server replied: %2$s';
$lang["err_spectacle"] = 'Error accessing show data';
$lang["err_spectacleid"] = 'Bad spectacle number'; // § NEW
$lang["err_upload"] = 'Problem uploading file';
$lang["expiration"] = 'Expiration';
$lang["expired"] = 'already expired';

$lang["failure"] = 'PANIC';
$lang["file"] = 'File: ';
$lang["filter"] = 'Show:'; // filter form header in bookinglist
$lang["firstname"] = 'First name';
$lang["from"] = 'From'; // in a temporal sense : from a to b

$lang["hello"] = 'Hello %1$s,';
$lang["hideold"] = '';  // '%1$sHide%2$s old spectacles.'; §NEW IN 1.2.2b that's "%1$s hide %2$s" without the spaces
$lang["hour"] = 'hr'; // abbreviated
/* (note : this is only used for at least two seats) */
$lang["howmanyare"] = 'Among these %1$d seats, how many are';

$lang["id"] = 'ID';
$lang['imagesrc'] = 'Image location (Must be JPEG)';
$lang["immediately"] = 'immediately';
$lang["import"] = 'Upload this file';
$lang["in"] = 'in %1$s'; // as in "in <ten days>"
$lang["index_head"] = '';
$lang["intro_ccard"] = <<<EOD
 <h2>Thank you for your reservation</h2>

<p class="main">Please wait ... </p>
EOD;


$lang["intro_confirm"] = 'Please check and make any required changes before confirming your reservation';
$lang["intro_finish"] = 'ENTRY TICKET<br> Please print this page and bring it to the show.';
$lang["intro_params"] = <<<EOD
<h2>Availability of payment methods</h2>

<p class="main">
<ul><li><p>
Enter the periods during which the various payment methods are
available, relative to show date.
</p>
<li>
<p>The numbers to be provided are numbers of <em>minutes</em> before
the beginning of the show.</p>
<li>
<p>Opening/Closing payment at the door means the time during
which customers may request to pay at the door (and not the opening
hours of the theatre office)</p>

<li>
<p>
Delays about postal transfer are seen in working days only. If a delay
falls on a working day then the specified interval is implicitly
increased by 24 hours times the number of holidays.
</p>
</ul>
</p>

%1\$s

<h2>Reminders and cancellation</h2>

<p class="main">Depending on the payment method requested by the client,
how many <em>days</em> after the reservation should a reminder be sent,
and the reservation cancelled?</p>

%2\$s

<h2>Other parameters</h2>

EOD;
$lang["intro_remail"] = <<<EOD

<h2>Reservation retrieval</h2>

<p class='main'>Please type in the following field the email address
you used when making your reservation, then click Submit.<br>
You will then receive an email containing all details about your
reservations</p>

<p class='main'>Email address: %1\$s</p>

<p class='main'>(If you have not provided an email address, or if you no
longer have access to it, please contact us directly.)</p>

EOD;

$lang["intro_remail2"] = <<<EOD
<h2>Reservation retrieval</h2>

<p class='main'>Please check your email now, while keeping this
page open.  If you received an email containing an access code, 
please copy and paste the code here.  If the email did not
arrive, check your spam or junk mail folders to see if it is there.
</p>

<p class='main'>Access code: %1\$s</p>

<p class='main'>Thank you!</p>

EOD;
$lang["intro_remail_paid"] = <<<EOD

<p class="main">Please note that only paid tickets can be printed.  If your tickets have not been paid for, please use the "Make a Payment" link on the front page, or contact the office.</p>

EOD;
$lang["intro_seats"] = '<b>Instructions</b><ul>
<li><p>You may select reserved seats by clicking on one or more locations in the Reserved Seat Tickets map below. 
<li><p>Click on "Continue" at the bottom of this page once you have made your choices. 
<li><p>Tickets may be purchased for one show date at a time.  If you wish to purchase tickets for a second show date, please click the Make Another Reservation link after your first purchase is complete.
</ul>';

$lang["is_default"] = 'This is the active show.';
$lang["is_not_default"] = 'This is not the active show.';

$lang["lastname"] = 'Last name';
$lang["legend"] = 'Legend: ';
$lang["link_bookinglist"] = 'List';
$lang["link_edit"] = 'Go back to edit';
$lang["link_index"] = 'Welcome page';
$lang["link_pay"] = 'Personal information';
$lang["link_repr"] = 'Show list';
$lang["link_seats"] = 'Seat selection';
$lang["login"] = 'System Administration (For authorised persons only):';
$lang["logout"] = 'Log Out';

$lang["mail-anon"] = <<<EOD
Hello,

This is information concerning a ticket buyer who did not provide an
e-mail address.

Here is the information they provided during the reservation, in case
we need to contact them:

EOD;

/* NOTE - Assumes spectacle must be preceded by a (masculine)
 definite article. In the future we will need to integrate the article
 in the spectacle name and alter/extended it when needed (e.g. French
 de+le = du, German von+dem = vom, etc) */
$lang["mail-booked"] = <<<EOD
Thank you for your reservation for the %1\$s

Here are the details we received from your reservation, which you should
bring with you to the show.

EOD;

$lang["mail-cancel-however"] = <<<EOD
However we regret to inform you that your reservation for the following 
seat has been cancelled:
EOD;
$lang["mail-cancel-however-p"] = <<<EOD
However we regret to inform you that your reservations for the following 
seats have been cancelled:
EOD;
$lang["mail-cancel"] = <<<EOD
This is to inform you that your reservation for the following seat has
been cancelled:
EOD;
$lang["mail-cancel-p"] = <<<EOD
This is to inform you that your reservation for the following seats has
been cancelled:
EOD;

$lang["mail-gotmoney"] = 'We have received your payment for the following seat:';
$lang["mail-gotmoney-p"] = 'We have received your payment for the following seats:';

$lang["mail-heywakeup"] = <<<EOD

According to our records, we still have not received the payment for
your reservation for the following seat:

%1\$s

If you have already made your payment, please disregard this message.

You may pay for this reservation and receive your ticket by visiting
our web site at http://tickets.hbg-cpac.org and clicking the
"Make a Payment" button.

If on the other hand you would like to cancel this reservation, please
let us know that by replying to this email.  Unless we hear from you soon,
your reservation will expire.

EOD;

$lang["mail-heywakeup-p"] = <<<EOD

According to our records, we still have not received the payment for
your reservation for the following seats:

%1\$s

If you have already made your payment, please disregard this message.

You may pay for this reservation and receive your tickets by visiting
our web site at http://tickets.hbg-cpac.org and clicking the
"Make a Payment" button.

If on the other hand you would like to cancel this reservation, please
let us know by replying to this email. Unless we hear from you soon,
your reservation will expire.

EOD;

$lang["mail-notconfirmed"] = <<<EOD
Your reservation has not yet been confirmed ; Tickets grant access to
the seats only after the payment has been received.
EOD;

// for one seat
$lang["mail-notdeleted"] = 'The following seat reservation is maintained:';
// for more than one seat
$lang["mail-notdeleted-p"] = 'The following seat reservations are maintained:';
$lang["mail-notpaid"] = 'The following seat is reserved but we have not yet received the payment:';
$lang["mail-notpaid-p"] = 'The following seats are reserved but we have not yet received the payment:';
$lang["mail-remail"] = <<<EOD
In response to your request on the %1\$s
website, the access code below will allow you to access your tickets.
Please copy and paste this code into the space provided on our web 
page.

%2\$s


Below is a summary of all reservations you have made so far using this
email address.

EOD;

$lang["mail-reminder-p"] = <<<EOD
We would also like to remind you that the following seats have not been 
paid:

%1\$s

You may pay for this reservation and receive your tickets by visiting
our web site at http://tickets.hbg-cpac.org and clicking the
"Make a Payment" button.

If you would like to cancel them, please let us know by replying to this 
email.

EOD;

$lang["mail-reminder"] = <<<EOD
We would also like to remind you that the following seat has not been
paid:

%1\$s

You may pay for this reservation and receive your ticket by visiting
our web site at http://tickets.hbg-cpac.org and clicking the
"Make a Payment" button.

If you would like to cancel it, please let us know by replying to this 
email.

EOD;

$lang["mail-secondmail"] = <<<EOD
You will receive a second email once we receive your payment.

EOD;

$lang["mail-spammer"] = <<<EOD
Hello,

We received a request to have a summary of ticket reservations 
made at this address (%3\$s) for the %1\$s
(%2\$s) sent to you.

However we do not have any reservation made from this address. 
This could mean that --

* You did make a reservation, but using another e-mail address. You 
may try again using the correct email address.
* Or, you had a seat reserved but it was cancelled. You should have 
received another e-mail at the time, including the reason for the 
cancellation.

If you have any questions please let us know by replying to this e-mail.

EOD;
// following always plural
$lang["mail-summary-p"] = 'Seats that are presently confirmed are the following:';

$lang["mail-thankee"] = <<<EOD
Thank you for your reservation!  We hope you will enjoy the show.

EOD;
$lang["mail-oops"] = <<<EOD
If you believe this is an error, please reply to this mail as quickly
as possible, so that we may reactivate your reservation.
EOD;
$lang["mail-sent"] = 'An e-mail has just been sent to you, and contains the same information as this page';
$lang["mail-sub-booked"] = 'Your reservation';
$lang["mail-sub-cancel"] = 'Reservation cancellation';
$lang["mail-sub-gotmoney"] = 'Payment acknowledgement';
$lang["mail-sub-heywakeup"] = 'Reminder';
$lang["mail-sub-remail"] = 'Reservation summary';
$lang["make_default"] = 'Make this the active show.  Only one show can be active at a time.';
$lang['make_payment'] = 'Make Your Payment';
$lang["max_seats"] = 'Maximum number of seats that can be reserved in one session';
$lang["minute"] = 'min'; // abbreviated
$lang["minutes"] = 'minutes';
$lang["months"] = array(1=>"January","February","March","April","May","June","July","August","September","October","November","December");

$lang["name"] = 'Name';
$lang["new_spectacle"] = 'Creating a New Show Definition';
$lang["ninvite"] = 'Complimentary?';
// following written on tickets for non-numbered seats.
$lang["nnseat"] = 'General Admission';
$lang["nnseat-avail"] = 'There is one %1$s seat available. <br>Type 1 (one) here if you want to reserve it: ';
$lang["nnseat-header"] = 'General Admission Tickets';
$lang["nnseats-avail"] = 'There are %1$s %2$s seats available. <br>Enter here the number you want to reserve: ';
$lang["nocancellations"] = 'No automatic cancellation';
$lang["noimage"] = 'No image file';
$lang["none"] = 'none'; // § NEW in 1.2.2
$lang["noreminders"] = 'No reminders sent';
$lang["notes"] = 'Notes';
$lang["notes-changed"] = 'Notes changed for 1 reservation';
$lang["notes-changed-p"] = 'Notes changed for %1$d reservations';
$lang["no_tickets"] = <<<EOD
No %1\$s tickets have been found for that email address.  
Did you use the email address you used when the purchase was made?  
If not, you may try again.  If you believe you received this message in error, 
please call the office.
EOD;
$lang["nreduced"] = 'For children?';

$lang["orderby"] = 'Order by %1$s';

$lang["panic"] = <<<EOD
<h2>WE HAVE BEEN UNABLE TO PROCESS YOUR RESERVATION</h2>
<p class='main'>The system administrator has been notified and it will 
be fixed as quickly as possible.</p>

<p class='main'>Please come back in a few hours and try again</p>

<p class='main'>We apologise for this problem, and appreciate your patience.</p>
EOD;

$lang["params"] = 'Modify %1$ssystem parameters%2$s';
$lang["pay_cash"] = 'Will Call';
$lang["pay_ccard"] = 'Online payment';
$lang["pay_other"] = 'Office sale';
$lang["pay_postal"] = 'By postal transfer';
$lang["payinfo_cash"] = <<<EOD
Please pay for your tickets at least 15 minutes before the show begins.

EOD;
$lang["payinfo_ccard"] = <<<EOD
We have not yet received the payment confirmation. If we don't
receive it within %1\$d days, the tickets may be made available for sale
to another patron.

EOD;
//'
$lang["payinfo_postal"] = <<<EOD
Please pay on the %1\$s
before %2\$d working days, otherwise the tickets may be made available
for sale to another patron.

EOD;
//'

$lang["paybutton"] = 'Click here to proceed with payment:&nbsp;%1$sContinue%2$s';
$lang["payment"] = 'Payment';
$lang['payment_received'] = 'Your payment has been received.  Thank you!';
$lang['paypal_id'] = 'PayPal Transaction ID: ';
$lang['paypal_lastchance'] = "We are ready to accept your payment.  After clicking on the button below, you will be transferred to the PayPal web site along with information about your ticket purchase.  After completing the payment, you will be transferred back to this site, your payment will be recorded, and your tickets will be printed.  Your credit or debit card information will be limited to the PayPal secure payment system.  All sales are final -- no refunds or exchanges.";
$land["paypal_purchase"] = 'PayPal Ticket Purchase';
$lang["phone"] = 'Phone';
$lang['please_wait'] = 'Processing Transaction . . .  Please Wait';
$lang["postal tax"] = 'Postal tax';
$lang["postalcode"] = 'Zip Code';
$lang["poweredby"] = 'Powered by %1$s';
$lang["price"] = 'Price';
$lang["price_discount"] = 'Discount Price ';
$lang['prices']  = 'Ticket Prices';
$lang["print_entries"] = '%1$sPrint%2$s selected entries';

$lang["rebook"] = 'Make a new reservation using the selected entries as a template: %1$sStart reservation%2$s';
$lang["rebook-info"] = 'To reactivate deleted reservations, first select the "Deleted" filter on the top-left of this page';
$lang["regular_ticket"] = ' Std ';	// as in standard
$lang["remail"] = 'Have you lost your ticket? Click here for %1$sReservation retrieval%2$s';
$lang["reminders"] = 'Reminders';
$lang["reqd_info"] = <<<EOD
Please provide all of the following information.  Double check your email address.
If you enter an incorrect email address, we will not be able to send you a receipt.
EOD;
$lang["reserved-header"] = 'Reserved Seat Tickets';
$lang["row"] = 'Row';

$lang["sameprice"] = 'Tickets for each class of seat are color-coded as shown below';
$lang["save"] = 'Save';
$lang["seat_free"] = 'Free<br>Seat:';
$lang["seat_occupied"] = 'Occupied<br>Seat:';
$lang["seats"] = 'Seats';
$lang["seats_booked"] = 'Reservations';
$lang["seeasalist"] = 'See as a %1$sList%2$s';
$lang["seeasamap"] = 'See reservations for this show as a %1$sReservation Map%2$s';
$lang["select"] = 'Select';
$lang["select_payment"] = 'Please select a payment method:';
$lang["selected_1"] = '1 seat selected';
$lang["selected_n"] = '%1$d seats selected';
$lang["sentto"] = 'Message sent to %1$s';
$lang["set_status_to"] = 'Do this with the selected entries: ';
$lang["show_any"] = 'All shows';
$lang["show_info"] = '%1$s at %2$s, %3$s'; // date, time, location
$lang["show_name"] = 'Show Name';
$lang["show_not_stored"] = 'Your changes could not be saved.  Please check with your system administrator.';
$lang["show_stored"] = 'Your changes have been saved.';
$lang["showallspec"] = '';  // 'Show %1$sall spectacles%2$s.';  §NEW IN 1.2.2b (that's "%1$s show all %2$s" without the spaces)
$lang["showlist"] = 'Shows of %1$s';
$lang["spectacle_name"] = 'Select a Show';
$lang["state"] = 'State'; // in the sense of status, not in the sense
			  // of a country's part
$lang["st_any"] = 'Any state';
$lang["st_booked"] = 'Reserved';
$lang["st_deleted"] = 'Deleted';
$lang["st_disabled"] = 'Disabled';
$lang["st_free"] = 'Free';
$lang["st_locked"] = 'In process';
$lang["st_notdeleted"] = 'Not deleted';
$lang["st_paid"] = 'Paid';
$lang["st_shaken"] = 'Reminder sent';
$lang["st_tobepaid"] = 'Unpaid';
$lang["stage"] = 'Stage';
$lang["summary"] = 'Summary';

$lang["theatre_name"] = 'Theatre Seatmap Name';
$lang["time"] = 'Time';
$lang["time_title"] = 'Time<br>(hh:mm)';
$lang["timestamp"] = 'Reserved on';
$lang["title_mconfirm"] = 'Confirm Show Details';
$lang["title_maint"] = 'Add or Edit a Show';
$lang["to"] = 'To'; // in a temporal sense : from a to b
$lang["total"] = 'Total';

$lang["update"] = 'Update';
$lang['us_state'] = 'State';

$lang["warn_badlogin"] = 'Illegal client connection attempt';
$lang["warn_bookings"] = 'Please Note:  If you change a show date or time you should contact those who have already purchased tickets to inform them of the change.  If you change the ticket prices, tickets may have been sold for amounts different than the new prices, which will cause confusion.  Please proceed with caution.';
$lang["warn_close_in_1"] = 'Warning, online reservations for this show will close in one minute';
$lang["warn_close_in_n"] = 'Warning, online reservations for this show will close in %1$d minutes';
$lang["warn-nocontact"] = 'Please provide the required contact information.';
$lang["warn-nomail"] = 'Please provide the required contact information.';
$lang["warn-nomatch"] = 'None found'; // no matching reservations
$lang["warn-nonsensicalcat"] = 'Warning, you have requested more children\'s seats than you have selected total seats';
$lang["warn-nonsensicalcat-admin"] = 'Warning, the number of requested invitations plus the number of requested children\'s tickets is more than the number of selected total seats';
$lang['warn_paypal_confirm'] = 'We could not confirm your PayPal payment.  Please contact the office to confirm your payment.';
$lang['warn_process_payment'] = 'There was a problem with final processing of your payment.  Please contact the office to confirm your payment';
$lang["warn_show_confirm"] = 'Please confirm that the information above is accurate.  To make further changes, click on the Edit link.  When you are ready, click Save.';
$lang["warn_spectacle"] = 'Please note that you cannot change the theatre seatmap after a show has been created.';
$lang["we_accept"] = "We Accept"; // credit card logos come after that
$lang["weekdays"] = array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
$lang["workingdays"] = 'working days';

$lang["youare"] = 'You Are:';

$lang["zoneextra"] = ''; // intentionally left blank

