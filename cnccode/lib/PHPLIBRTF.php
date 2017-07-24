<?php

require_once('HTML/Template/PHPLIB.php');

/**
 * Extended PHPLIB Template class that uses square brackets instead of
 * curly so that it can be used with RTF files. RTF puts a backslash in front
 * of { messing up the placeholders.
 *
 * @author  Karim Ahmed<karim@sweetcode.co.uk>
 * @version 1.0
 */
class Template_PHPLIBRTF extends Template_PHPLIB
{
    /**
     * Constructor
     *
     * @access public
     * @param  string template root directory
     * @param  string how to handle unknown variables
     * @param  array fallback paths
     */
    function __construct($root = ".", $unknowns = "remove", $fallback="")
    {
				parent::Template_PHPLIB( $root, $unknowns, $fallback);
    }
		
    /**
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
     */
    function setBlock($parent, $handle, $name = "")
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
        $str = preg_replace($reg, "[" . "$name]", $str);

        if (isset($m[1][0])) $this->setVar($handle, $m[1][0]);
        $this->setVar($parent, $str);
    }

    /**
     * Protect a replacement variable
     *
     * @access private
     * @param  string name of replacement variable
     * @return string replaced variable
     */
    function _varname($varname)
    {
        return "[".$varname."]";
    }
		
}
?>
