<?php


/* 
 *  Performs cleanups on address, name and phone number, using US standards
 *  Address validation requires an account with USPS address validation system.
 *  Assumes all data can be found in $_SESSION.  Requires global variables
 *  for configuration in config-dist.php to be set.
 */

function freeseat_plugin_init_UScleanup() {
    global $freeseat_plugin_hooks;

    $freeseat_plugin_hooks['config_form']['UScleanup'] = 'UScleanup_config_form';

    $freeseat_plugin_hooks['pay_process']['UScleanup'] = 'UScleanup_scrub';
}

function UScleanup_config_form($form) {
  return config_form('plugins/UScleanup/config-dist.php', $form);
}

function UScleanup_scrub() {
  // do some quality control on the entered values
  foreach (array("firstname","lastname") as $n => $a) {
    $_SESSION[$a] = ucwords(strtolower($_SESSION[$a]));
  }

  // clean up the entered phone number using US standard format
  if ($_SESSION['phone']) 
    $_SESSION['phone'] = formatPhone($_SESSION['phone']);

  // validate the entered street address against the US postal service database
  $USPS_result = USPS_validate();
  // echo $USPS_result;
}

function USPS_validate() {
  // sends address information to the USPS API for validation
  // updates data in $_SESSION if present
  // expects to find a valid USPS user ID in $USPS_user
  // returns 0 on success or an error message on failure
  // returns address fields in all caps on success
  global $USPS_user;

  if ((!isset($USPS_user)) || !strlen($USPS_user)) {
    return "No USPS user ID available";
  }
  foreach (array("address","city","us_state") as $n => $a) {
    if ((!isset($_SESSION[$a])) || (!strlen($_SESSION[$a]))) {
      return "Address elements missing";
    }
  }
  foreach (array("address","postalcode","city","us_state") as $n => $a) {
    $$a = urlencode($_SESSION[$a]);  
  }
  $host = "production.shippingapis.com";
  $path = "/ShippingAPI.dll";  // http://production.shippingapis.com/ShippingAPI.dll
  
  $request = "API=Verify&XML=<AddressValidateRequest USERID='$USPS_user'>";
  $request .= "<Address ID='0'><Address1></Address1><Address2>$address</Address2>";
  $request .= "<City>$city</City><State>$us_state</State><Zip5>$postalcode</Zip5><Zip4></Zip4>";
  $request .= "</Address></AddressValidateRequest>";  
  
  $data = "POST $path HTTP/1.1\r\n";
  $data .= "Host: $host\r\n";
  $data .= "Content-Type: text/xml; charset=ISO-8859-1\r\n";
  $data .= "Content-length: ".strlen($request)."\r\n";
  $data .= "Connection: close\r\n\r\n";
  $data .= $request . "\r\n\r\n";

  $fp=@fsockopen($host,80,$errnum,$errstr,30);
  $response = "";
  if(!$fp) { 
    return "Error $errnum: $errstr at $host Data: ".htmlentities($data);
  } else {
    fputs($fp, $data);
    while(!feof($fp)) 
      $instr = @fgets($fp, 1024); 
    if ($instr[0] == '<')     // throw away first line w/ non-XML stuff
      $response .= $instr;
    fclose($fp);
  } 
  $data = XML_unserialize($response);  // use Keith Devens awesome XML library to convert to an array
  
/* Expected responses:

Success:
<?xml version="1.0"?>
<AddressValidateResponse>
  <Address ID="0">
    <Address2>6406 IVY LN</Address2>
    <City>GREENBELT</City>
    <State>MD</State>
    <Zip5>20770</Zip5>
    <Zip4>1440</Zip4>
  </Address>
</AddressValidateResponse>

Error:
<?xml version="1.0"?>
<Error>
  <Number>-2147217951</Number>
  <Source>EMI_Respond :EMI:clsEMI.ValidateParameters:
         clsEMI.ProcessRequest;SOLServerIntl.EMI_Respond</Source>
  <Description>Missing value for To Phone number.</Description>
  <HelpFile></HelpFile>
  <HelpContext>1000440</HelpContext>
</Error>
*/
  
  if (isset($data['Error'])) {
    return "USPS XML data error: ". $data['Error']['Description'];
  } 
  if (isset($data['AddressValidateResponse']['Address'])) {
    $address = $data['AddressValidateResponse']['Address'];
    if (isset($address['Address2']) && strlen($address['Address2']))
      $_SESSION['address'] = $address['Address2'];
    if (isset($address['City']) && strlen($address['City']))
      $_SESSION['city'] = $address['City'];
    if (isset($address['State']) && strlen($address['State']))
      $_SESSION['us_state'] = $address['State'];
    if (isset($address['Zip5']) && strlen($address['Zip5']))
      $_SESSION['postalcode'] = (string) $address['Zip5'];
  }  
	return 'Ok';
}

/* 
 * Formats a phone number to be US standard for readability and consistency
 */
function formatPhone($phone = '') {
  global $default_area_code;
  // If we have not entered a phone number just return empty
  if (empty($phone)) {
    return '';
  }
  $dac = '';
  if (!empty($default_area_code)) {
    if (strlen($default_area_code) != 3) {
      $default_area_code = preg_replace("/[^0-9]/", "", $default_area_code);
    }
    $dac = "($default_area_code) ";
  }
  // Strip out any extra characters that we do not need only keep letters and numbers
  $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);
  // Perform phone number formatting here
  if (strlen($phone) == 7) {
    return $dac . preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
  } elseif (strlen($phone) == 10) {
    return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
  } elseif (strlen($phone) == 11) {
    return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1($2) $3-$4", $phone);
  }
  // Return original phone if not 7, 10 or 11 digits long
  return $phone;
}
