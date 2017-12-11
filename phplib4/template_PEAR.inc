<?php
require_once('Template_PHPLIB.php');                // PEAR PHPLib template
class Template extends Template_PHPLIB
    /**
     * This extended class is a wrapper for the new PEAR Template_PHPLib class so that code written
     * against the original PHPLib Template class doesn't have to be changed for the new method naming
     * conventions.
     *
     * To use it, simply change your includes to point at this file instead of the original
     * PHPLib template.inc file. You will need the HTML/Template/PHPLIB.php PEAR package
     *    installed too.
     *
     * NOTE: I found a diffence of behaviour between the new setBlock() and old set_block() that
     * broke my code when using nested blocks:
     *
     * I found that using set_block(<template>,<blockname>, ''); to clear the contents of the inner
     * block no longer works in the new setBlock() method.
     *
     * to get around this, I have copied the setBlock() code from Template_PHPLib
     * into this class' set_block() method then changed it to work as it used to in PHPLib.
     *
     * @author: Karim Ahmed
     * @version 1.0
     */
{
    /**
     * Constructor
     *
     * @access public
     * @param  string template root directory
     * @param  string how to handle unknown variables
     * @param  array fallback paths
     */
    function __construct($root = ".", $unknowns = "remove")
    {
        parent::__construct($root, $unknowns);
    }

    /**
     * Sets the template directory
     *
     * @access public
     * @param  string new template directory
     * @return bool
     */
    function set_root($root)
    {
        return ($this->setRoot($root));
    }

    /**
     * What to do with unknown variables
     *
     * three possible values:
     *
     * - "remove" will remove unknown variables
     *   (don't use this if you define CSS in your page)
     * - "comment" will replace undefined variables with comments
     * - "keep" will keep undefined variables as-is
     *
     * @access public
     * @param  string unknowns
     */
    function set_unknowns($unknowns = "remove")
    {
        return ($this->setUnknowns($unknowns));
    }

    /**
     * Set appropriate template files
     *
     * With this method you set the template files you want to use.
     * Either you supply an associative array with key/value pairs
     * where the key is the handle for the filname and the value
     * is the filename itself, or you define $handle as the file name
     * handle and $filename as the filename if you want to define only
     * one template.
     *
     * @access public
     * @param  mixed handle for a filename or array with handle/name value pairs
     * @param  string name of template file
     * @return bool
     */
    function set_file($varname, $filename = "")
    {
        return ($this->setFile($varname, $filename));
    }

    /**
     * IMPORTANT: the new PEAR method does NOT clear down nested blocks in the same way as the old one
     * so I have overriden it here to make the behaviour identical to old class.
     *
     * Set a block in the appropriate template handle
     *
     * By setting a block like that:
     *
     * &lt;!-- BEGIN blockname --&gt;
     * html code
     * &lt;!-- END blockname --&gt;
     *
     * you can easily do repeating HTML code, i.e. output
     * database data nice formatted into a HTML table where
     * each DB row is placed into a HTML table row which is
     * defined in this block.
     * It extracts the template $handle from $parent and places
     * variable {$name} instead.
     *
     * @access public
     * @param  string parent handle
     * @param  string block name handle
     * @param  string variable substitution name
     * @return bool
     */
    function set_block($parent, $handle, $name = "")
    {
        if (!$this->_loadFile($parent)) {
            $this->halt("setBlock: unable to load $parent.");
            return false;
        }
        if ($name == "") {
            $name = $handle;
        }

        $str = $this->getVar($parent);
        $reg = "/[ \t]*<!--\s+BEGIN $handle\s+-->\s*?\n?(\s*.*?\n?)\s*<!--\s+END $handle\s+-->\s*?\n?/sm";
        preg_match_all($reg, $str, $m);
        $str = preg_replace($reg, "{" . "$name}", $str);

        /*
        This is what Template_PHPLIB::setBlock() does but it will not clear down nested inner blocks
        in the same way that the original PHPLib code does and so your code would have broken.
        I have replaced it with code that does the same as the orginal code
                if (isset($m[1][0])) $this->setVar($handle, $m[1][0]);
        */
        $this->setVar($handle, $m[1][0]);
        $this->setVar($parent, $str);
        return true;
    }

    /**
     * Set corresponding substitutions for placeholders
     *
     * @access public
     * @param  string name of a variable that is to be defined or an array of variables with value substitution as key/value pairs
     * @param  string value of that variable
     * @param  boolean if true, the value is appended to the variable's existing value
     */
    function set_var($varname, $value = "", $append = false)
    {
        return ($this->setVar($varname, $value, $append));
    }

    /******************************************************************************
     * NOTE: This method NOT defined in new PEAR template class so try doing nothing (for now)
     * cause I dont think I call it anyhow!
     *
     * This functions clears the value of a variable.
     *
     * It may be called with either a varname as a string or an array with the
     * values being the varnames to be cleared.
     *
     * The function sets the value of the variable in the $varkeys and $varvals
     * hashes to "". It is not necessary for a variable to exist in these hashes
     * before calling this function.
     *
     *
     * usage: clear_var(string $varname)
     * or
     * usage: clear_var(array $varname = (string $varname))
     *
     * @param     $varname      either a string containing a varname or an array of varnames.
     * @access    public
     * @return    void
     */
    function clear_var($varname)
    {
        /* original PHPLib code
                  if (!is_array($varname)) {
                  if (!empty($varname)) {
                    if ($this->debug & 1) {
                      printf("<b>clear_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
                    }
                    $this->set_var($varname, "");
                  }
                } else {
                  reset($varname);
                  while(list($k, $v) = each($varname)) {
                    if (!empty($v)) {
                      if ($this->debug & 1) {
                        printf("<b>clear_var:</b> (with array) <b>%s</b><br>\n", $v);
                      }
                      $this->set_var($v, "");
                    }
                  }
                }
        */
    }

    /******************************************************************************
     * NOTE: This method NOT defined in new PEAR template class so try doing nothing (for now)
     * cause I dont think I call it anyhow!
     * This functions unsets a variable completely.
     *
     * It may be called with either a varname as a string or an array with the
     * values being the varnames to be cleared.
     *
     * The function removes the variable from the $varkeys and $varvals hashes.
     * It is not necessary for a variable to exist in these hashes before calling
     * this function.
     *
     *
     * usage: unset_var(string $varname)
     * or
     * usage: unset_var(array $varname = (string $varname))
     *
     * @param     $varname      either a string containing a varname or an array of varnames.
     * @access    public
     * @return    void
     */
    function unset_var($varname)
    {
        /* original PHPLib code
                  if (!is_array($varname)) {
                  if (!empty($varname)) {
                    if ($this->debug & 1) {
                      printf("<b>unset_var:</b> (with scalar) <b>%s</b><br>\n", $varname);
                    }
                    unset($this->varkeys[$varname]);
                    unset($this->varvals[$varname]);
                  }
                } else {
                  reset($varname);
                  while(list($k, $v) = each($varname)) {
                    if (!empty($v)) {
                      if ($this->debug & 1) {
                        printf("<b>unset_var:</b> (with array) <b>%s</b><br>\n", $v);
                      }
                      unset($this->varkeys[$v]);
                      unset($this->varvals[$v]);
                    }
                  }
                }
            */
    }

    /**
     * Return all defined variables and their values
     *
     * @access public
     * @return array with all defined variables and their values
     */
    function get_vars()
    {
        return ($this->getVars());
    }

    /**
     * Return one or more specific variable(s) with their values.
     *
     * @access public
     * @param  mixed array with variable names or one variable name as a string
     * @return mixed array of variable names with their values or value of one specific variable
     */
    function get_var($varname)
    {
        return ($this->getVar($varname));
    }

    /**
     * Get undefined values of a handle
     *
     * @access public
     * @param  string handle name
     * @return mixed  false if an error occured or the undefined values
     */
    function get_undefined($varname)
    {
        return ($this->getUndefined($varname));
    }

    /**
     * Complete filename
     *
     * Complete filename, i.e. testing it for slashes
     *
     * @access private
     * @param  string filename to be completed
     * @return string completed filename
     */
    function filename($filename)
    {
        return ($this->_filename($filename));
    }

    /**
     * Protect a replacement variable
     *
     * @access private
     * @param  string name of replacement variable
     * @return string replaced variable
     */
    function varname($varname)
    {
        return ($this->_varname($filename));
    }

    /**
     * load file defined by handle if it is not loaded yet
     *
     * @access private
     * @param  string handle
     * @return bool   FALSE if error, true if all is ok
     */
    function loadfile($varname)
    {
        return ($this->_loadFile($varname));
    }
}

?>
