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

<li>Ticket prices are entered by class of seat, which are set in the seatmap.  For example, if your seatmap contains seats organized into classes 1, 2, and 3, you will need to enter prices for those 3 classes on this page.  A column is provided for reduced price tickets, for example, a special price for children.

<li>If you are uploading an image for the show, be careful of the size.  Files larger than 1MB will be refused.  The image will be resized to 300 pixels wide. 

<li>Save the show setup, and confirm it on the next screen if everything is accurate.  Now go to the Current Shows page, and your show should be visible.  

<li>In order to display your shows on the Wordpress front end, create a page or a post and add the shortcode [freeseat-shows].  This will display information about all of the currently available shows.  Once a show is over, it will disappear from the list.  To jump right to the page for a particular show date, use the shortcode [freeseat-direct showid=xx] where xx is the number for a particular show date.  To find the number for a show date, navigate to the page for it and look in the URL where "showid=" should be visible.

<li>It is *highly* recommended to use a secure site for ticket purchases, especially when using credit card purchases.  See for example the Wordpress HTTPS plugin (http://wordpress.org/plugins/wordpress-https/) for one way to do that.


<h3>TODO</h3>

<ul><li>Plugins not yet converted: klikandpay and seasontickets. Currently the only payment plugin is paypal.  There are no plans to convert seasontickets at the moment.  Htmltickets is unnecesary and will be dropped.

<li>The setup.sql files in groupdiscount, civicrm, bookingnotes, and castpw are not handled yet.  For the moment, the default install includes all of the extra fields required by those plugins. 

<li>Testing, comments, documentation, consistency, and more testing.
</li></ul>
