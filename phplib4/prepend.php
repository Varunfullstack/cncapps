<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: prepend.php,v 1.2 2002/10/04 14:14:46 joestewart Exp $
 *
 */ 

$_PHPLIB = array();

# Can't control your include path?
# Point this to your PHPLIB base directory. Use a trailing "/"!
$_PHPLIB["libdir"]  = "";

require($_PHPLIB["libdir"] . "db_mysql.inc");  /* Change this to match your database. */
//require($_PHPLIB["libdir"] . "ct_sql.inc");    /* Change this to match your data storage container */
require($_PHPLIB["libdir"] . "ct_file.inc");    /* Change this to match your data storage container */

/* Additional require statements go below this line */
require($_PHPLIB["libdir"] . "session.inc");   // Required for using PHPLIB storage container for sessions.
require($_PHPLIB["libdir"] . "auth.inc");      // Disable this, if you are not using authentication.
require($_PHPLIB["libdir"] . "perm.inc");      // Disable this, if you are not using permission checks.
require($_PHPLIB["libdir"] . "user.inc");      // Disable this, if you are not using per-user variables.
require_once($_PHPLIB["libdir"] . "template_PEAR.inc");      /* Disable this, if you are not using per-user variables. */
require($_PHPLIB["libdir"] . "page.inc");      /* Required, contains the page management functions. */

?>
