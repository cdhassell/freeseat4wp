<?php namespace freeseat;


  /** Some functions plugins may use for integration with the config
   plugin */

  /** If $string is of the form 'something' or "something", remove the
   quotes. Removes escape sequences as well. If instead $string starts
   with the word "array", construct an array with the content,
   splitting at commas. */
function unquote_string($string) {
  $string = trim($string);
  //  echo "<!-- unquoting $string -->\n";
  if (preg_match("/^'(.*)'\$/", $string, $matches)) {
    //    echo "<!-- single quoted -->\n";
    /* 'Single quotes'. */
    return $matches[1];
  } else if (preg_match('/^"(.*)"$/', $string, $matches)) {
    //    echo "<!-- double quoted -->\n";
    /* "Double quotes". */
    return stripcslashes($matches[1]);
  } else if (preg_match('/^array *\((.*)\)$/', $string, $matches)) {
    //    echo "<!-- array! -->";
    $r = array(); // result
    $string = $matches[1];

    while (count($string)) {
      $comma = strpos($string, ',');
      if ($comma === false) {
	$r[] = unquote_string($string);
	break;
      }
      
      $r[] = unquote_string(substr($string, 0, $comma));
      $string = substr($string, $comma+1);
    }
    return $r;
  } else {
    //    echo "<!-- not quoted -->\n";
    /* Not quoted. */
    return $string;
  }
}

/** Construct the php-code corresponding to the given string */
function quote_string($string) {
  return "\"".addcslashes($string,"\n\r\t\"")."\"";
}

/** Construct the php-code corresponding to the given array of
 strings. Note that $arr's elements are trimmed. */
function quote_array($arr) {
  $r = "array("; // result
  $comma = ""; // will be an actual comma after first element.
  foreach ($arr as $elt) {
    $r .= $comma. quote_string(trim($elt));
    $comma = ', ';
  }
  return $r . ")";
}

/** Returns the list of available languages. */
function language_list() {
  $langs = array();
  if ($dh = opendir(FS_PATH . 'languages')) {
    while (($file = readdir($dh)) !== false) {
      if (preg_match('/^(.*)\.php$/',$file,$matches)) {
	if ($matches[1] != 'default') {
	  $langs[] = $matches[1];
	}
      }
    }
    closedir($dh);
  }
  return $langs;
}

/** Construct a configuration form for the given config-dist file if
 $form is 0, print corresponding config.php code if $form is 2.

 I was originally planning to use $form==1 to store POST data into the
 session but storing the entire POST into the session works well
 enough (it should anyway not contain more data than configuration
 parameters)

 Conveniently, the semantics of $form is identical to that of the
 config_form hook's parameter.

 $configfile: the name of the configuration file, relative to freeseat
 root (i.e. DON'T add FS_PATH, this function will do it). */
function config_form($configfile, $form) {

  $configdist = fopen( FS_PATH . $configfile, "r");
  if ($configdist) {
    $printing = false; // gets set to true as soon as the first header is found.
    $incomment = false; // true if we're between the <p> and </p> of a comment
    $type = "string"; // the type of the next field, as set by @type
    //  $isArray = false; // whether the next field is an array (set by @type
    // xyz array)
    
    while ($line = fgets($configdist)) {
      if (preg_match('/\/\*\*\* ([^*]*) \*\*\*\//',$line, $matches)) {
	/* header */
	if ($form == 0) {
	  if ($incomment) echo '</p>';
	  echo '<h2>'.htmlspecialchars($matches[1]).'</h2>';
	} else if ($form == 2) {
	  echo "\n/*** ".$matches[1]." ***/\n";
	}
	$printing = true;

      } else if (preg_match("/ *\/\/ *@type ([^ \n]*)/", $line, $matches)) {
	/* @type directive */
	$type = $matches[1];
	//	$isArray = $matches[2] == 'array';

      } else if (preg_match("/ *\/\/ *@echo.*/", $line, $matches)) {
	$printing = true;
      } else if (preg_match("/ *\/\/ *@no-echo.*/", $line, $matches)) {
	$printing = false;
      } else if ($printing && preg_match('/^ *(\/\/)? *\$([a-zA-Z0-9_]*)(\["([a-zA-Z0-9_]*)"\])? *= *(.*);([^;]*)/', $line, $matches)) {
	/* Configuration field */
	$basename = $matches[2];
	$arrkey = $matches[4];
	// see if we have a "// example" at the end:
	$example = preg_match('/.*\/\/ *example */', $matches[6]);

	$varname = '$'.$basename; // the php variable name as a string
	$fkey = $basename; // the form id and POST key.
	if ($arrkey) {
	  $varname .= '["'.$arrkey.'"]';
	  $fkey .= '-'.$arrkey;
	}
	if ($form == 0) {
	  // We first take the value in config-dist.
	  $value = unquote_string($matches[5]);

	  if ($example) {
	    // That value is just an example, so we WON'T put it into
	    // the field, but in plain text before the field.
	    if (!$incomment) {
	      echo '<p>';
	      $incomment = true;
	    }
	    echo ' <i>Example:&nbsp;' . htmlspecialchars(config_format_for_user($type, $value)) . '</i>';
	    $value = null;
	  }

	  // now we see if that variable is to be overriden through config.php
	  if (isset($GLOBALS[$basename])
	      && ( (!$arrkey) || isset($GLOBALS[$basename][$arrkey]) )) {
	    $value = $arrkey? $GLOBALS[$basename][$arrkey] : $GLOBALS[$basename];
	  }

	  if ($incomment) echo '</p>';
	  echo '<p><span class="configitem">'. htmlspecialchars($fkey). ':</span>';

	  // $fvalue will be the value going into the html field if any.
	  $fvalue = config_format_for_user($type, $value);
	} elseif ($form == 2) {
	  if (isset($_SESSION["config_post"][$fkey])) {
	    $value = nogpc($_SESSION["config_post"][$fkey]);
	  } else {
	    $value = null;
	  }
	}

	/* With $form=0, print a form field. With $form=2,
	 populate $value with what the form field put into $_POST. */
	switch ($type) {
	case 'array':
	  if ($form == 0) {
	    echo ' Enter a comma-separated list: ';
	    echo ' <input size=50 name="'.htmlspecialchars($fkey)
	      .'" value="'. htmlspecialchars($fvalue).'">';
	  } elseif ($form == 2) {
	    /* explode includes all spaces around the commas, but then
	     quote_array removes them. */
	    if ($value !== null) $value = quote_array(explode(',',$value));
	  }
	  break;
	case 'boolean':
	  if ($form == 0) {
	    echo ' <input type="checkbox" name="'.htmlspecialchars($fkey)
	      .'"'.htmlspecialchars($fvalue).'>';
	  } elseif ($form == 2) {
	    $value = ($value !== null)? "true" : "false";
	  }
	  break;
	case 'language':
	  /* Note, this case is no longer used, but some plugin might want it anyway, so leaving it in. */
	  if ($form == 0) {
	    echo '<select name="'.htmlspecialchars($fkey).'">';
	    foreach (language_list() as $lang) {
	      if ($lang == $value) {
		echo '<option selected>';
	      } else {
		echo '<option>';
	      }
	      echo $lang;
	      echo '</option>';
	    }
	    echo '</select>';
	  } elseif ($form == 2) $value = quote_string($value);
	  break;
	case 'multline':
	  if ($form == 0) {
	    echo ' <textarea cols=50 rows=5 name="'.htmlspecialchars($fkey)
	      .'">';
	    echo htmlspecialchars($fvalue);
	    echo '</textarea>';
	  } elseif ($form == 2) $value = quote_string($value);
	  break;
	case 'multarray':
	  if ($form == 0) {
	    echo ' Enter one item per line: ';
	    echo ' <textarea cols=50 rows=5 name="'.htmlspecialchars($fkey)
	      .'">';
	    echo htmlspecialchars($fvalue);
	    echo '</textarea>';
	  } elseif ($form == 2) {
	    if ($value !== null) $value = quote_array(explode("\n", $value));
	  }
	  break;
	case 'password':
	  if ($form == 0) {
	    echo ' <input type="password" size=50 name="'.htmlspecialchars($fkey)
	      .'" value="'. htmlspecialchars($fvalue).'">';
	  } elseif ($form == 2) {
	    $value = quote_string($value);
	  }
	  break;
	case 'plugin':
	  /*	echo '</p>';
	   foreach (plugin_list() as $plugin) {
	   echo '<p><input type="checkbox">';
	   echo ' <b>'. $plugin['english_name']. '</b> '.$plugin['summary'];
	   echo '</p>';
	   }
	   echo '<p>'; */
	  break;
	case 'num': case 'string':
	  if ($form == 0) {
	    echo ' <input size=50 name="'.htmlspecialchars($fkey)
	      .'" value="'. htmlspecialchars($fvalue).'">';
	  } elseif ($form == 2) {
	    if ($type=='num') {
	      $value = (int)$value;
	    } else {
	      $value = quote_string($value);
	    }
	  }
	  break;
	default:
	  echo "unknown configuration type ($type).";
	}
	if ($form == 0) {
	  echo '</p>';
	} elseif ($form == 2) {
	  echo "$varname = $value;\n";
	}

      } else if ($printing && preg_match('/^[ \/*]*(.*)$/', $line, $matches)) {
	if (strstr($line, '?>')) break;
	/* Comment */
	if ($form == 0) {
	  if (!$incomment) {
	    $incomment = true;
	    echo '<p>';
	  }
	  $line = $matches[1];
	  // remove any terminating "*/"
	  if (preg_match('/(.*[^*])\*\**\/ */', $line, $matches))
	    $line = $matches[1];
	  echo ' '.$line;
	}
      }
    }
    if ($incomment) echo '</p>';
    fclose($configdist);
  } else {
    echo "Could not find $configfile! Please check your install and try again.";
  }

}

function config_format_for_user($type, $value) {
  switch ($type) {
  case 'array':
    return implode(', ', $value);
  case 'boolean':
    return ($value? " checked":"");
  case 'multarray':
    return implode("\n", $value);
  default:
    return $value;
  }
}

/** This function aborts the script with a "please login" message if
 the user should be denied access to the plugin: if db users are
 available but user is not logged in. (if database users have not yet
 been setup, logging as admin would be impossible). */
function config_authorise() {
  if ((config_checkconfig() && config_checkdbusers()) &&
      !config_checkadmin()) {
    flush_messages_html();
?>
  Please login first.
</body></html>
<?php 
    exit;
  }
}

function config_checkconfig() {
  return file_exists(FS_PATH . "config.php");
}

/** Return whether the first (user) freeseat user is available. When
 false is returned you can use mysql_error() to find out why. */
function config_checkdbusers() {
  global $dbserv, $dbuser, $dbpass, $dbdb, $systemuser, $systempass,
    $lang;
  $dblink = @mysql_connect($dbserv, $dbuser, $dbpass);
  if (!$dblink) {
    return false;
  }
  mysql_close($dblink);

  /* If we got so far, everything is in order. */
  return true;
}

/** Attempt to connect to the database with the system account (using
 either the config-provided or user-provided password, whichever is
 available) using the user-provided admin password, and returns
 whether that worked.

 It's rather similar to the usual freeseat db_connect() but doesn't
 throw a fatal_error if the password is wrong, doesn't select the
 database, doesn't complain if a tables.sql is in the main directory
 (that requirement will soon be removed anyway), and fails instead of
 going in normal mode when admin_mode returns false. */
function config_checkadmin() {
  global $dbserv, $adminuser, $systemuser, $systempass, $lang;

  /** If the user specified a system password in the configuration
   file or through a form field, let's use that one. */
  if (isset($systempass) && !empty($systempass)) {
    $_SESSION["systempass"] = $systempass;
  } else if (isset($_POST["login_systempass"])) {
    $_SESSION["systempass"] = nogpc($_POST["login_systempass"]);
  } else if (!isset($_SESSION["systempass"])){
    return false;
  }

  $dblink = @mysql_connect($dbserv, $systemuser, $_SESSION["systempass"]);
  if (!$dblink) {
    return false;
  }
  mysql_close($dblink);

  /* WARN: We are not checking here that the admin password is not
   empty, and sent through a secure connection. This is to avoid
   locking out the user in case of misconfiguration. This only
   provides access to the config plugin, not the rest of FreeSeat
   which uses the more restrictive admin_mode() function. */

  if (isset($_POST["adminpass"]))
    $_SESSION["adminpass"] = nogpc($_POST["adminpass"]);

  return @mysql_connect($dbserv, $adminuser, $_SESSION["adminpass"]);
}

/** Include the given sql file, removing comments and replacing
 $db, $adminuser, $systemuser and $dbuser by the values of the
 corresponding php variables. As with config_form() above, $sqlfile
 must be relative to Freeseat root. */
function config_include_sql($sqlfile) {
  global $adminuser, $systemuser, $dbuser;
  global $config_missingdb_cache;

  $sql = fopen( FS_PATH . $sqlfile, "r");
  if ($sql) {
    while ($line = fgets($sql)) {
      if (!preg_match('/^ *--/', $line)) {
	/* $line is not commented */
	echo config_apply_subst($line);
      }
    }
    fclose($sql);
  } else {
    echo "Could not find $sqlfile! Please check your install and try again.";
  }
}

function config_apply_subst($line) {
  global $dbdb, $adminuser, $systemuser, $dbuser;
  return str_replace
    (array('$adminuser', '$systemuser', '$dbuser', '$db'),
     array("$adminuser@localhost",
	   "$systemuser@localhost",
	   "$dbuser@localhost",
	   "$dbdb"),
     $line);
}

/** Check if the given sql commands seem to have already been
 executed.

 For now it only understands three kinds of commands:

 "create table <tablename> (...)" (for which it only checks the table
 exists, not that it has the correct schema)

 "alter table <tablename> add column <columnname>" (for which it only
 checks the given column exists in the given table).

 If you omit the "column" keyword it won't work. If you put more than
 one alter specification in the statement it won't work (so, make as
 many alter table statements as you want to add columns even if
 they're in the same table). If your table or column names contain
 non-alphanumeric characters or are quoted or fully-qualified, it
 won't work.

 "grant <privileges> on $db.<tablename> to $<fsusername>" where
 privileges is a comma-separated list of keywords like select or
 update ("all privileges" should also work but why would you want
 that?). $db must be the actual string dollar-d-b. tablename can't be
 a wildcard. $<fsusername> must be one of $dbuser, $systemuser and
 $adminuser.

 Other statements are ignored.

 Only statements at the beginning of a line (modulo white space) are
 taken into account.

 parameter $user: the name of the user we're currently connected as,
 with quotes and host (one of "'$dbuser'@'localhost'",
 "'$systemuser'@localhost'" and "'$adminuser'@'localhost'"). As users
 aren't permitted to check each other's grants you must run this
 function three times with all three values of $user in order to make
 sure all statements are checked. The table and column statements are
 only checked when $user is the admin user to avoid doing the same checks
 three times.

 Return value: false if everything in the file has a match in the
 database, for use with the config_db hook.
 */
function config_checksql_for($sqlfile, $user) {
  global $dbdb, $dbuser, $systemuser, $adminuser;
  global $config_missingdb_cache;

  /* Whether to check create table and alter table statements. */
  $checkcreates = ($user == "'$adminuser'@'localhost'");

  if (@mysql_select_db($dbdb)) {
    $db_available = true;
  } else {
    /* Either because the user doesn't have sufficient permissions or
     because the database doesn't exist. We go through the sql file anyway
     but will only look for GRANT statements. */
    $db_available = false;
    $config_missingdb_cache[$sqlfile] = true;
    kaboom("User $user can't open database $dbdb.");
    
    $checkcreates = false;
  }

  $actualgrants = @m_eval_all("show grants for $user");
  if (!$actualgrants) {
    myboom("Error selecting grants for $user");
    $user = ''; // prevent all grant checking.
  // } else {
  //   echo '<!-- ';
  // 	foreach ($actualgrants as $actual) {
  // 	  echo $actual;
  // 	  echo "\n";
  // 	}
  // 	echo '-->';
  }

  $skipnext = false; // set to true by @meta skip
  $sql = fopen( FS_PATH . $sqlfile, "r");
  $allokay = $db_available;
  if ($sql) {
    while ($line = fgets($sql)) {
      if (preg_match('/^ *-- *@meta  *skip *$/', $line)) {
	$skipnext = true;
      } else if (preg_match('/^ *create  *table (if not exists|)  *`?([a-zA-Z_][a-zA-Z1-9_]*)/i', $line, $matches)) {
	if ($skipnext) {
	  $skipnext = false;
	} else if ($checkcreates) {
	  $table_name = $matches[2];
	  if (!mysql_query("describe $table_name")) {
	    $allokay = myboom("Table $table_name missing");
	  }
	}
      } else if (preg_match('/^ *alter  *table  *([a-zA-Z_][a-zA-Z1-9_]*)  *add  *column  *([a-zA-Z_][a-zA-Z1-9_]*)/i', $line, $matches)) {
	if ($skipnext) {
	  $skipnext = false;
	} else if ($checkcreates) {
	  $table_name = $matches[1];
	  $column_name = $matches[2];
	  /** Try to get one element from the given column. Note that
	   if the user doesn't have select grants for the table it
	   won't work! (That could be the case for e.g. logging tables
	   that are write only) */
	  if (!mysql_query("select $column_name from $table_name limit 1")) {
	    $allokay = myboom("Column $column_name missing in table $table_name");
	  }
	}
      } else if (preg_match('/^ *grant  *([a-z, ]*) on  *\$db\.([a-z_]*) to  *\$([a-z]*) *;/i', $line, $matches)) {
	$grants = explode(',', $matches[1]);
	$table = $matches[2];
	$grantuser = $matches[3];
	/* Note, mysql show grants puts `-quotes around table names
	 and '-quotes around user names */
	switch ($grantuser) {
	case "dbuser":
	  $grantuser = "'$dbuser'@'localhost'";
	  break;
	case "systemuser":
	  $grantuser = "'$systemuser'@'localhost'";
	  break;
	case "adminuser":
	  $grantuser = "'$adminuser'@'localhost'";
	  break;
	default:
	  if ($checkcreates) {
	    // (the if is to put only one error message, not three)
	    kaboom("Warning, skipping grant statement for unknown user $grantuser");
	  }
	  continue 2; // go back to while ($line=fgets, etc).
	}

	if ($grantuser != $user) continue; // (no "2" here, we're out
					   // of the switch.)

	$found = false; // will be set to true if a statement for user
			// $user on table $table is found

	// echo "<!-- looking for: grant ([a-z, ]*) on `".$dbdb.'`.`'.$table.'` to '.$user." -->\n"; 

	foreach ($actualgrants as $actual) {
	  if (preg_match('/^grant ([a-z, ]*) on `'.$dbdb.'`.`'.$table.'` to '.$user.'/i', $actual, $matches)) {
	    $found = true;
	    // echo "<!-- found -->\n";
	    /* there is a grant statement for user $user on table
	     $table. Let's see if it includes all $grants. */
	    $actualgrantitems = array_fliplowtrim(explode(',', $matches[1]));
	    $missing = ""; // for the error message
	    foreach ($grants as $g) {
	      if (!isset($actualgrantitems[strtolower(trim($g))])) {
		$missing .= " " . strtolower(trim($g));
	      }
	    }
	    if ($missing != "") {
	      $allokay = kaboom("missing grants $missing on $table for $user");
	    }
	  }
	}
	if (!$found) {
	    // echo "<!-- not found -->\n";
	  $allokay = kaboom("missing grants on $table for $user");
	}
      } // if preg_match(.. grant ..)
    }  // while ($line =...)
    fclose($sql);

    if (!$allokay) $config_missingdb_cache[$sqlfile] = true;

    return !$allokay;
  } else {
    return kaboom("Could not find $sqlfile! Please check your install and try again.");
    return true;
  }
}

/** Check if tables for the base install are available (does not check
 tables only required by plugins). You must be connected to the
 database before calling this. Call config_checkalltables instead of
 you want to know whether *all* tables are available (i.e. including
 tables required by selected plugins)
 
 Returns true if they are available, false otherwise.
 
 Side effect: this selects the $dbdb database as well. */
function config_checkbasetables_as($user) {
  return config_checksql_for('tables.sql', $user);
}

function config_checkalltables_as($user) {
  global $dbdb;
  $success = true;
  if (config_checkbasetables_as($user)) {
    $success = false;
  }
  if (do_hook_exists('config_db', $user)) {
    $success = false;
  }
  return $success;
}

/** Returns whether database tables required by all selected plugins
 * are available. You must be connected to the database before running
 * this. As the check for grant statements requires logging in
 * respective users, you will be connected as admin when this
   function returns. */
function config_checkalltables() {
  global $dbserv, $dbuser, $dbpass, $systemuser, $systempass, $adminuser;
  global $config_missingdb_cache;

  $success = true;

  /* $config_missingdb_cache[$sqlfile] gets set (to true) by
   config_checksql_for if a mismatch was found for that file. */
  $config_missingdb_cache = array();

  /* we're connected as admin, but have to close it because we're
   testing other users. I guess simultaneous connections could work
   but don't want to depend on it. Putting an '@' because we get
   a warning if admin connection failed. */
  @mysql_close();
  /* first try regular user. */
  $connected = @mysql_connect($dbserv, $dbuser, $dbpass);
  $success &= config_checkalltables_as("'$dbuser'@'localhost'");
  if ($connected)
    mysql_close();
  else
    $success = false;

  /* then system user: */
  $connected = @mysql_connect($dbserv, $systemuser, $_SESSION["systempass"]);
  $success &= config_checkalltables_as("'$systemuser'@'localhost'");
  if ($connected)
    mysql_close();
  else
    $success = false;

  /* Finally, admin user. */
  $connected = @mysql_connect($dbserv, $adminuser, $_SESSION["adminpass"]);
  $success &= config_checkalltables_as("'$adminuser'@'localhost'");
  if (!$connected)
    $success = false;
  
  /* leave the connection open for the rest of the script. */
  return $success;
}

function config_checkcron() {
  global $lang;
  $lastcron = m_eval('select lastcron from config');
  if ($lastcron != null) {
    kaboom($lang['config_cron_latest'].' '. $lastcron);
    return true;
  } else {
    kaboom($lang['config_cron_never']);
    return false;
  }
}

function config_checkseats() {
  global $lang;
  $cnt = m_eval("select count(*) from theatres");
  if ($cnt == 0) {
    return kaboom($lang['config_seats_none']);
  } else if ($cnt == 1) {
    kaboom($lang['config_seats_one']);
  } else {
    kaboom(sprintf($lang['config_seats_n'], $cnt));
  }
  return true;
}

function config_checkshows() {
  global $lang;
  $cnt = m_eval("select count(*) from spectacles");
  if ($cnt == 0) {
    return kaboom($lang['config_spectacles_none']);
  } else if ($cnt == 1) {
    kaboom($lang['config_spectacles_one']);
  } else {
    kaboom(sprintf($lang['config_spectacles_n'], $cnt));
  }
  return true;
}

/** Check if the given file exists, and if not give an error message to the user.
 $resource: a human readable of what the file is for
 $url: a url where the file (or an archive containing the file) can be downloaded. */
function config_checkdependency($filename, $resource, $url) {
  global $lang, $config_missingdeps;
  if (!file_exists(FS_PATH . $filename)) {
    $config_missingdeps[$resource] = $url;
    return kaboom(sprintf($lang['config_missing_file'], $filename));
  } else {
    return true;
  }
}


function config_checkdependencies() {
  global $lang, $config_missingdeps;
  $all_found = true; // so far
  $config_missingdeps = array();
  /* Not using $all_found &= to prevent short-circuit and make sure
   all missing files are reported, not just the first one. */
  if (!config_checkdependency("class.smtp.php", "PHPMailer", "http://phpmailer.worxware.com/")) $all_found = false;
  if (!config_checkdependency("class.phpmailer.php", "PHPMailer", "http://phpmailer.worxware.com/")) $all_found = false;
  if (do_hook_exists('config_checkdependencies'))
    $all_found = false;

  return $all_found;
}

?>
