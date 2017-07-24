<?php
/**
 * Base class for all classes
 *
 * @author Karim Ahmed
 * @access private
 */
define('BO_CLASSNAME_CONTROLLER', 'Controller');
define('BO_CLASSNAME_BASEOBJECT', 'BaseObject');

class BaseObject
{
    var $methodName = "";
    var $owner = "";                    // The owner object of this object

    function __construct(&$owner)
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $this->owner = &$owner;
    }

    function BaseObjectNoOwner()
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $this->owner = '';
    }
//	function setClassname($class_name){
// This is here from a time when PHP did not store the class name. ToDo: Try removing it!
//	}
    function getClassname()
    {
        return strtolower(get_class($this));
    }

    /**
     * Set current class method name
     * @access public
     * @param string $methodName Method name
     * @return void
     */
    function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * Return current class method name
     * @access public
     * @return  string Method name
     */
    function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Raise error on owner object
     * Recursively raises errors on owner objects until the owner is a controller then displays a formated error message
     *
     * @access public
     * @param  string $message Error message
     * @return  void
     */
    function raiseError($message)
    {
        if (is_subclass_of($this, BO_CLASSNAME_CONTROLLER)) {                                // This object is a controller so display formatted error document
            $this->displayFatalError($message);
        } else {
            $errorString = $message . ' in ' . $this->getClassName() . "." . $this->getMethodName();
            if (($this->owner == '') || (!is_subclass_of($this->owner, BO_CLASSNAME_BASEOBJECT))) {    // Not owned one of our system's objects
                die($errorString);                                                                                                                                // So simply die with a message
            } else {
                if (is_subclass_of($this->owner, BO_CLASSNAME_CONTROLLER)) {                // Owner is a controller so display formatted error document
                    $this->owner->displayFatalError($errorString);
                } else {
                    $this->owner->raiseError($errorString . ' << ');                                    // Promote error handling to owner class handler
                }
            }
        }
    }
}

?>
