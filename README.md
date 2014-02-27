freeseat4wp
===========

This is a port of the Freeseat ticketing application to Wordpress as a native WP plugin.  Freeseat is an open source web application for managing ticket booking and sales, (C) Maxime Gamboni 2010, maxime@gamboni.org.  See http://freeseat.sf.net for details.  

I want to express my thanks to Maxime for creating Freeseat as it has been a very useful program.  All hail Maxime!  :-)

This is a development release for public comment and testing purposes.  It is not fully tested and should *not* be used in production.


<h3>INSTALLATION</h3>

<p>The following assumes that you have installed Wordpress and it is working correctly.  A current version of Wordpress is recommended.  Versions prior to 3.8 have not been tested.</p>

<ol><li>Just do the usual Wordpress thing - download the tarball and save it somewhere.  Then go to Plugins - Add New - Upload and upload the file, then install it.  There is also an optional link to install sample data on the plugins screen.

<li>Once installed, you will have a new Freeseat menu on the administrative screens.  Point your mouse at the menu item, and you should see a submenu of items like "Current Shows", "Settings", etc.  If there is no data in the tables,  most of these won't do much.

<li>Go to the Settings page to begin to customize your application by entering the name of your theatre, etc.  Be sure to use the Save Settings button at the bottom of the page when you are done.

<li>To use PayPal, activate the PayPal plugin on the Settings page, and save it.  Then edit the Settings page again, to enter your account email and PayPal authorization code in the boxes provided. 

<li>To set up your theatre, go the Seatmaps page.  Follow the instructions linked there to create a seatmap in a spreadsheet and then upload it to Freeseat.  An example.csv file is provided to get you started.

<li>Once you have your seatmap installed, the next step is to create a show at the Show Setup page.  Fill in the information in that form, including a name, description, image, show dates and prices.  Show dates are added by pressing the "+" button under the date list.

<li>Prices are entered by class of seat, which are set in the seatmap.  For example, if your seatmap contains seats organized into classes 1, 2, and 3, you will need to enter prices for those 3 classes on this page.  A column is provided for reduced price tickets, for example, a special price for children.

<li>If you are uploading an image for the show, be careful of the size.  Files larger than 1MB will be refused.  The image will be resized to 300 pixels wide. 

<li>Save the show setup, and confirm it on the next screen if everything is accurate.  Now go to the Current Shows page, and your show should be visible.  

<li>In order to display your shows on the Wordpress front end, create a page or a post and add the shortcode [freeseat-shows].  This will display information about all of the currently available shows.  Once a show is over, it will disappear from the list.  To jump right to the page for a particular show date, use the shortcode [freeseat-direct showid=xx] where xx is the number for a particular show date.  To find the number for a show date, navigate to the page for it and look in the URL where "showid=" should be visible.

<li>Finally, you will need to set up a daily call to cron.php on the command line, in order to send email and update statuses.  Consult your hosting service for how to do that.</li></ol>


<h3>NOTES ON THIS VERSION</h3>

<ul><li>A "namespace freeseat" has been added to all files to prevent name collisions within WP.  As a result, PHP version 5.3 or newer is required, both in apache and on the command line.

<li>A new options page that is managed by WP options functionality has been added for entry of most configuration items, replacing params.php. Config options are stored in the wp_options table. Freeseat plugins can add new configuration parameters using a hook. Activation of freeseat plugins also occurs on this options page. This process also replaces the separate confip-default.php files for each plugin.

<li>Database table names have "freeseat_" prepended. Database queries are managed by WP database functionality. All direct calls to mysql_*() functions have been replaced.

<li>All standalone pages have been converted to functions and linked to WP action hooks.  Administrator pages are linked to menu items on the WP administrator backend.

<li>Entry points from WP pages or posts are now from WP shortcodes. The shortcode "[freeseat-shows]" displays the front page list of all available shows. The shortcode "[freeseat-direct showid=xx]" enters the seatmap display formerly at seats.php.

<li>A new function freeseat_switch() is called on every page of the ticket purchase process. A parameter $fsp determines whether to display repr, seats, pay, confirm or finish pages.

<li>The showedit and showlist plugins have been moved into core.

<li>A new plugin has been added to allow administrator upload of seatmap CSV files. A help screen explains how to format a spreadsheet and save it as a CSV file for upload.

<li>A new plugin has been added that requires the user to be logged in to WP, and stores and retrieves the user name and address details in the WP user account.

<li>A new plugin has been added to record shows as events and users as event participants in a civicrm database.

<li>A new plugin has been added to allow the admin user to print theatre tickets on a Boca FGL21 ticket printer.  This incorporates a java applet from the excellent QZ-print project (https://code.google.com/p/jzebra) to permit output to a local printer from the browser.  This requires some additional setup as described in the plugin README file.  Users may also want to try their hand at supporting different brands of ticket printers by copying and modifying this code.  If you do this, please share the result here. 

<li>A new installation routine creates the database tables and loads example data and configuration options.  An uninstall file drops all freeseat tables and deletes the options.

<li>The pdftickets copy of dompdf has been upgraded to version 0.6. The old version of dompdf was not compatible with WP.
</li></ul>

<h3>TODO</h3>

<ul><li>Plugins not yet converted: seasontickets, remail, post_pay, klikandpay, and htmltickets. Currently the only payment plugin is paypal.

<li>The setup.sql files in groupdiscount, civicrm, bookingnotes, and castpw are not handled yet.  For the moment, the default install includes all of the extra fields required by those plugins. 

<li>Testing, comments, documentation, consistency, and more testing.
</li></ul>
