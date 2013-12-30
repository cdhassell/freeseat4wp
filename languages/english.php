<?php

/** English Language file.

Copyright (C) 2010 Maxime Gamboni. See COPYING for copying/warranty
info. 

$Id: english.php 297 2010-11-12 21:49:59Z tendays $
*/

/** Instructions for creating a new language file:

1. Create a file with the name of the language and the .php extention.

2. Put the necessary headers, i.e. the php prefix, and the default.php
   include (see francais.php for an example).

3. Translate all strings found in default.php.

4. Translate all functions found below.

5. If you want to distribute your work, put a comment with your name,
   your email (or any way to contact you) and send it to me. Make sure
   you set the encoding properly with $lang["_encoding"] (again, see
   francais.php for an example). A file not specifying the encoding is
   *worthless* !

6. Send the resulting language file to me

When translating strings, %1$s or %2$s or similar four character
blocks must be left intact though they can be reordered or moved
around in the string. These four character blocks are replaced with
some information whenever they are used. Don't forget to escape quotes
when needed (see francais.php for examples).

*/


require_once ( FS_PATH . "languages/default.php");

/** add "at the" before the given noun. This is used with theatre
names (There are languages where it is not as trivial as English) **/
function lang_at_the($w) {
  return "at the auditorium";
}


