<?php
/**
 *    SC_Object class
 *
 * Sweet Code Base class for all others
 *
 * Use for global class stuff such as error handling
 *
 * @package sc
 * @author Karim Ahmed
 * @version 1.0
 * @copyright Sweetcode Ltd 2005
 * @public
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'html.php');

class SC_Object
{
    /**
     * @access public
     */
    function raiseError($message)
    {
        die(SC_HTML::preformat($message));                        // do something more elegant !!!!
    }

    function staticRaiseError($message)
    {
        SC_Object::raiseError($message);
    }
} //end class SC_Object
?>