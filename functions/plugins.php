<?php namespace freeseat;


/** Copyright (C) 2010 Maxime Gamboni. See COPYING for
copying/warranty info.

Large parts of this code was taken from SquirrelMail.

$Id: index.php 273 2010-09-29 12:59:40Z tendays $
*/

global $freeseat_plugin_hooks;
$freeseat_plugin_hooks = array();

function use_plugin ($name) {
    if (file_exists( FS_PATH . "plugins/$name/setup.php" )) {
        include_once( FS_PATH . "plugins/$name/setup.php" );
        $function = __NAMESPACE__ . "\\freeseat_plugin_init_$name";
        if (function_exists($function)) {
            $function();
        }
    }
}

/** Run all code registered for the given hook. No return value. */
function do_hook ($name) {
    global $freeseat_plugin_hooks;

    //    echo "<!-- hook $name -->";
    if (isset($freeseat_plugin_hooks[$name])
          && is_array($freeseat_plugin_hooks[$name])) {
      //       echo "<!-- is_array -->";
        foreach ($freeseat_plugin_hooks[$name] as $function) {
	  //    echo "<!-- function $function -->";
	  $NSfunction = __NAMESPACE__ . '\\' . $function;
	  if (function_exists($NSfunction)) {
	    //       echo "<!-- exists -->";
	      $NSfunction();
            }
        }
    }
}

/** Same as do_hook, but returns whether at least one plugin returned
 true. */
function do_hook_exists($name, $param=null) {
    global $freeseat_plugin_hooks;
    $ret = false;

    if (isset($freeseat_plugin_hooks[$name])
          && is_array($freeseat_plugin_hooks[$name])) {
        foreach ($freeseat_plugin_hooks[$name] as $function) {
        	$NSfunction = __NAMESPACE__ . '\\' . $function;
            if (function_exists($NSfunction)) {
                $ret |= $NSfunction($param);
            }
        }
    }
    return $ret;
}

/** Run all code registered for the given hook with the given
 parameter.  Returns whatever the last of them returned. 
 For WP changed to call by reference to facilitate input 
 validation in options.php  */
function do_hook_function($name, &$parm=null) {
    global $freeseat_plugin_hooks;
    $ret = NULL;

    //   echo "<!-- hook $name -->";
    if (isset($freeseat_plugin_hooks[$name])
          && is_array($freeseat_plugin_hooks[$name])) {
      //    echo "<!-- is_array -->";
        foreach ($freeseat_plugin_hooks[$name] as $function) {
	  //    echo "<!-- function $function -->";
	  	  $NSfunction = __NAMESPACE__ . '\\' . $function;
            if (function_exists($NSfunction)) {
	    //      echo "<!-- exists -->";
                $r = $NSfunction($parm);
		if ($r !== NULL) $ret = $r;
            }
        }
    }
    return $ret;
}

/** Run all code registered for the given hook with the given
 parameter.  Returns the total of returned values. Functions 
 should return integers */
function do_hook_sum($name, $parm=null) {
  global $freeseat_plugin_hooks;
  $ret = 0;

  if (isset($freeseat_plugin_hooks[$name])
      && is_array($freeseat_plugin_hooks[$name])) {
    foreach ($freeseat_plugin_hooks[$name] as $function) {
    	$NSfunction = __NAMESPACE__ . '\\' . $function;
      	if (function_exists($NSfunction)) {
        	$ret += $NSfunction($parm);
      }
    }
  }
  return $ret;
}

/** Same as do_hook_function, but returns the (string) concatenation
 of all return values. */
function do_hook_concat($name, $parm=null) {
    global $freeseat_plugin_hooks;
    $ret = '';
    
    if (isset($freeseat_plugin_hooks[$name])
          && is_array($freeseat_plugin_hooks[$name])) {
        foreach ($freeseat_plugin_hooks[$name] as $function) {
        	$NSfunction = __NAMESPACE__ . '\\' . $function;
            if (function_exists($NSfunction)) {
                $ret .= $NSfunction($parm);
            }
        }
    }
    return $ret;
}

/** Same as do_hook_function but returns an array containing all
 non-null values returned by the hooks. Note that all other
 do_hook_xyz functions could be implemented by calling this one. */
function do_hook_array($name, $parm=null) {
  global $freeseat_plugin_hooks;
  $ret = array();

    if (isset($freeseat_plugin_hooks[$name])
          && is_array($freeseat_plugin_hooks[$name])) {
        foreach ($freeseat_plugin_hooks[$name] as $function) {
        		  $NSfunction = __NAMESPACE__ . '\\' . $function;
            if (function_exists($NSfunction)) {
                $r = $NSfunction($parm);
		if ($r !== null) {
		  $ret[] = $r;
		}
            }
        }
    }
    return $ret;
}

/** Call this function on top of pages that absolutely require the
 given plugin to be active. */
function ensure_plugin ($name) {
  global $plugins;

  foreach ($plugins as $plugin) {
    if ($plugin == $name)
      return;
  }
  /* none of the plugins are equal to $name */
  fatal_error("Plugin $name is not activated.");
}

/** Call this function from your plugin_init function if your plugin
 has a "languages" subdirectory with localised strings. The .php file
 selected in config.php will be included. The languages directory must
 at least contain english.php (which will be included if the
 user-selected language is not available).

 $name: your plugin name. */
function init_language($name) {
  global $language, $lang;
  if (file_exists( FS_PATH . "plugins/$name/languages/$language.php" ) ) {
    include_once( FS_PATH . "plugins/$name/languages/$language.php" );
  } else {
    require_once( FS_PATH . "plugins/$name/languages/english.php" );
  }
}

/*************************************/
/*** MAIN PLUGIN LOADING CODE HERE ***/
/*************************************/

/* On startup, register all plugins configured for use. */
if (isset($plugins) && is_array($plugins)) {
    foreach ($plugins as $name) {
		// echo "<!-- using plugin $name -->";
		use_plugin($name);
    }
}
