<?php
/**
 * Controller base class
 * Provides generic functionality to process HTML requests.
 * Supplies some generic actions triggered by the value of a HTTP_GET_VARS
 * variable named "action". The user is expected to override the generic
 * methods and may also add user-defined actions and handlers by overriding
 * the default method.
 *
 * e.g. http://www.mysite.com/task.php?action=displayAddForm&processid=2
 * If session classnames are supplied then it will use the PHPLib sessions and
 * security classes to provide comprehensive session, authentication and
 * security support.
 *
 * @author Karim Ahmed.
 * @access virtual
 */
define(
    "CT_LEVEL_NONE",
    0
);
define(
    "CT_LEVEL_SESS",
    1
);
define(
    "CT_LEVEL_PERM",
    2
);
// Action constants
define(
    "CT_ACTION_INSERT",
    "insert"
);
define(
    "CT_ACTION_DELETE",
    "delete"
);
define(
    "CT_ACTION_UPDATE",
    "update"
);
define(
    "CT_ACTION_DISPLAY_EDIT",
    "displayEditForm"
);
define(
    "CT_ACTION_DISPLAY_ADD",
    "displayAddForm"
);
define(
    "CT_ACTION_DISPLAY_DELETE",
    "displayDeleteForm"
);
// HTTP request method constants
define(
    "CT_METHOD_POST",
    "POST"
);
define(
    "CT_METHOD_GET",
    "GET"
);
// Type of document to to client (extend this as necessary)
define(
    'CT_DOC_TYPE_HTML',
    'html'
);
define(
    'CT_DOC_TYPE_XML',
    'xml'
);
// HTML display format
define(
    "CT_HTML_FMT_SCREEN",
    'screen'
);                // HTML Document to be formatted for screen output
define(
    "CT_HTML_FMT_POPUP",
    'popup'
);                // HTML Document to be formatted for popup window
define(
    "CT_HTML_FMT_PRINTER",
    'printer'
);            // HTML Document to be formatted for printer output
define(
    "CT_HTML_FMT_PDF",
    'pdf'
);        // HTML Document to be formatted for PDF file output
define(
    'CT_HTML_READONLY',
    'readonly'
); // HTML for setting text fields readonly
// Other
define(
    "CT_FORM_ERROR_MESSAGE",
    "Please complete the fields highlighted in red"
);
define(
    "CT_SELECTED",
    "selected"
);
define(
    "CT_CHECKED",
    "checked"
);
define(
    "POUND_CHAR",
    chr(163)
);
global $cfg;
require_once($cfg["path_gc"] . "/BaseObject.inc.php");

class Controller extends BaseObject
{
// instance vars
    var    $requestMethod = "";        // this->requestMethod from html request
    public $postVars      = [];                    // HTTP_POST_VARS from html request
    var    $startTime;
    var    $getVars       = "";                    // HTTP_GET_VARS from html request
    var    $cookieVars    = "";                // HTTP_COOKIE_VARS from html request
    /**
     * @var Template $template
     */
    public $template;                    // PHPLib template object
    var    $cfg;                            // Configuration variables
    /** @var dbSweetcode $db */
    public    $db;                                // PHPLib DB object
    var       $formError        = FALSE;
    var       $docType          = CT_DOC_TYPE_HTML;
    var       $htmlFmt          = CT_HTML_FMT_SCREEN;
    var       $formErrorMessage = "";        // HTML formatting
    var       $action           = "";
    protected $cachedVersion;
    private   $pageTitle        = "";
    private   $pageHeader       = "";

    function __construct($requestMethod,
                         &$postVars,
                         &$getVars,
                         &$cookieVars,
                         &$cfg
    )
    {
        $this->cfg =& $cfg;
        $this->createTemplate();
        $this->BaseObjectNoOwner();
        $this->pageOpen();
        $this->postVars      =& $postVars;
        $this->getVars       =& $getVars;
        $this->cookieVars    =& $cookieVars;
        $this->requestMethod = $requestMethod;
        $this->setFormErrorOff();

    }

    /**
     * Create template object
     *
     * @access public
     * @return boolean Success
     */
    function createTemplate()
    {
        $this->template = new Template(
            $this->cfg["path_templates"], "remove"
        );
        return TRUE;
    }

    /**
     * All initialisation upon execution of request
     * @access private
     * @return boolean Success
     */
    function pageOpen()
    {
        $timeOfDay = gettimeofday();
        if (SHOW_TIMINGS) {
            $this->startTime = $timeOfDay["sec"] + ($timeOfDay["usec"] / 1000000);
        }
//		ob_start("ob_gz_handler"); // start output buffering
        return TRUE;
    }

    function setFormErrorOff()
    {
        $this->formError = FALSE;
    }

    public static function dateToISO($getValue)
    {
        $date = new \DateTime($getValue);
        return $date->format("Y-m-d\TH:i:s");

    }

    /**
     * Build a link
     *
     * @access private
     * @returns string $urlString
     * @throws Exception
     */
    public static function buildLink($args)
    {
        $numargs = func_num_args();
        if ($numargs < 2) {
            throw new Exception("Too few arguments passed");
        }
        // get args
        $url        = func_get_arg(0);
        $parameters = func_get_arg(1);
        // This bit added so that extensions such as gif for image files may be
        // passed.
        if ($numargs > 2) {
            $fileExtension = func_get_arg(2);
        } else {
            $fileExtension = "";
        }
        if ($url == "") {
            throw new Exception('Invalid URL');
        }
        $urlString = $url;
        $first     = TRUE;
        reset($parameters);
        while (list($p, $v) = each($parameters)) {
            $v = urlencode($v);
            if ($first == TRUE) {
                $urlString = $urlString . "?" . $p . "=" . $v;
                $first     = FALSE;
            } else {
                $urlString = $urlString . "&" . $p . "=" . $v;
            };
        };
        // This idiot guard prevents the URL page rom being cached by the browser
        if ((defined('CONFIG_IDIOT_GUARD_ON')) && CONFIG_IDIOT_GUARD_ON) {
            $urlString = self::addParametersToLink(
                $urlString,
                array("ig" => time())
            );
        }
        return $urlString;
    }

    /**
     * Add additional parameters to an existing link
     *
     * @access private
     * @param $url
     * @param $parameters
     * @return string
     * @throws Exception
     */
    public static function addParametersToLink($url,
                                               $parameters
    )
    {
        if ($url == "") {
            throw new Exception('Invalid URL');
        }
        $urlString = $url;
        // Do we have at least one parameter already?
        if (stristr(
                $urlString,
                "?"
            ) == FALSE) {
            $first = TRUE;
        } else {
            $first = FALSE;
        }
        reset($parameters);
        while (list($p, $v) = each($parameters)) {
            if ($first == TRUE) {
                $urlString = $urlString . "?" . $p . "=" . $v;
                $first     = FALSE;
            } else {
                $urlString = $urlString . "&" . $p . "=" . $v;
            }
        };
        return $urlString;
    }

    public static function htmlChecked($flag)
    {
        if ($flag == 'N' or $flag == '') {
            $ret = '';
        } else {
            $ret = CT_CHECKED;
        }
        return $ret;
    }

    /**
     * Prepare string for display on HTML page
     */
    public static function htmlDisplayText($text,
                                           $format = 0
    )
    {
        $text = stripslashes($text);
        $text = htmlspecialchars(
            $text,
            ENT_QUOTES
        );
        $text = str_replace(
            "\r\n",
            "\n",
            $text
        );
        switch ($format):
            case 1: //
                // convert \r\n to <br>
                $text = Controller::formatForHTML($text);
                break;
            case 2:
                // change case - use on all uppercase strings
                $text = ucwords(strtolower($text));
                break;
        endswitch;
        return trim($text);
    }

    public static function formatForHTML($string,
                                         $html_encode = true
    )
    {
        /*
                $string = str_replace("\011", ' &nbsp;&nbsp;&nbsp;', str_replace('  ', ' &nbsp;', $string));
                $string = ereg_replace("((\015\012)|(\015)|(\012))", '<br />', $string);
        */
        if ($html_encode) {
            $string = htmlentities($string);
        }
        $string = preg_replace(
            "/((\015\012)|(\015)|(\012))/",
            '<br />',
            $string
        );
        return $string;
    }

    /**
     * format number for display in HTML cell
     * Default 2dps
     */
    public static function formatNumber($unformattedNumber,
                                        $dps = 2,
                                        $thousandsSep = ',',
                                        $blankZeros = true
    )
    {

        if ($unformattedNumber == 0 && $blankZeros) {
            return '&nbsp;';
        } else {

            return number_format(
                $unformattedNumber,
                $dps,
                '.',
                $thousandsSep
            );
        }
    }

    /**
     * format GBP currency value for display
     * Default 2dps
     */
    public static function formatNumberCur($unformattedNumber,
                                           $dps = 2,
                                           $thousandsSep = ',',
                                           $blankZeros = true
    )
    {

        if ($unformattedNumber == 0 && $blankZeros) {
            return '';
        } else {
            return POUND_CHAR . number_format(
                    $unformattedNumber,
                    $dps,
                    '.',
                    $thousandsSep
                );
        }
    }

    /**
     *    convert a database-formatted date Y-M-D to a dd/mm/yyyy date
     * NOTE1: If empty string then return same
     * NOTE2: If invalid input date format then return same
     * @param $dateYMD
     * @param string $separator
     * @return string
     */
    public static function dateYMDtoDMY($dateYMD,
                                        $separator = '/'
    )
    {
        if (($dateYMD == '') or ($dateYMD == '0000-00-00')) {
            return null;
        } else {
            if (preg_match_all(
                "/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/",
                $dateYMD,
                $regs
            )) {
                $day   = $regs[3][0];
                $month = $regs[2][0];
                $year  = $regs[1][0];
                return $day . $separator . $month . $separator . $year;
            } else {
                return $dateYMD; // it isn't a valid date format so just return it as-is for display
            }
        }
    }

    function getJSONData()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    public function getParam($paramName)
    {
        if (!$paramName) {
            return null;
        }
        if (!isset($_REQUEST[$paramName])) {
            return null;
        }
        return $_REQUEST[$paramName];
    }

    /**
     * Get document output type
     * @access public
     * @return string DocumentType
     */
    function getDocType()
    {
        return $this->docType;
    }

    /**
     * Set document output type
     * @param string $documentType The type of document to return
     * @access public
     * @return boolean
     */
    function setDocType($docType)
    {
        $this->setMethodName('setDocumentType');
        if (($docType == CT_DOC_TYPE_HTML) | ($docType == CT_DOC_TYPE_XML)) {
            $this->docType = $docType;
        } else {
            $this->displayFatalError('Document Type must be ' . CT_DOC_TYPE_HTML . ' or ' . CT_DOC_TYPE_XML);
        }
    }

    /**
     * Display fatal error page with passed message
     * @access private
     */
    function displayFatalError($errorMessage)
    {
        $this->setPageTitle('A Problem Has Occurred');
        $this->setTemplateFiles(array("FatalError" => "FatalError.inc"));
        $this->template->set_var(
            array(
                "errorMessage" => $errorMessage,
                "className"    => $this->getClassName(),
                "methodName"   => $this->getMethodName(),
                "trace"        => $this->generateCallTrace(),
                "url"          => $_SERVER['PHP_SELF'],
                "arguments"    => isset($_SERVER['argv']) ? $_SERVER['argv'] : null
            )
        );
        $this->template->parse(
            "CONTENTS",
            "FatalError",
            true
        );
        $this->parsePage();
        exit;
    }

    function setContainerTemplate(){
        switch ($this->getHTMLFmt()) {
            case CT_HTML_FMT_PRINTER:
                $file = array("page" => "printer.inc." . $this->getDocType());
                break;
            case CT_HTML_FMT_POPUP:
                $file = array("page" => "PopupPage.inc." . $this->getDocType());
                break;
            case CT_HTML_FMT_PDF:
                $file = array("page" => "pdf_layout.inc." . $this->getDocType());
                break;
            default:
                $file = array("page" => "screen.inc." . $this->getDocType());
                break;
        }
        $this->template->setFile($file);
    }

    /**
     * Set template files, automatically includes the page template then calls
     * the PHPLib template set_file method
     *
     * @access private
     */
    function setTemplateFiles($handle,
                              $fileName = ""
    )
    {

        $this->setContainerTemplate();
        if (!is_array($handle)) {
// FOR DOS $file[$handle] = $this->template->fileName($fileName);
            $file[$handle] = $fileName . '.' . $this->getDocType();
        } else {
            reset($handle);
            while (list($h, $f) = each($handle)) {
//FOR DOS        $file[$h] = $this->template->fileName($f);
                $file[$h] = $f . '.' . $this->getDocType();
            }
        }
        $this->template->set_file($file);
    }

    /**
     * Get html display format
     * @access public
     * @return string format
     */
    function getHTMLFmt()
    {
        return $this->htmlFmt;
    }

    /**
     * Set html display format
     * @param string $format The html format
     * @access public
     * @return boolean
     */
    function setHTMLFmt($format)
    {
        $this->setMethodName('setHTMLFmt');
        if (($format == CT_HTML_FMT_SCREEN) | ($format == CT_HTML_FMT_PRINTER) | ($format == CT_HTML_FMT_POPUP | ($format == CT_HTML_FMT_PDF))) {
            $this->htmlFmt = $format;
        } else {
            $this->displayFatalError(
                'Format must be ' . CT_HTML_FMT_SCREEN . ' or ' . CT_HTML_FMT_PRINTER . ' or ' . CT_HTML_FMT_POPUP
            );
        }
    }

    function generateCallTrace()
    {
        $e     = new Exception();
        $trace = explode(
            "\n",
            $e->getTraceAsString()
        );
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        $length = count($trace);
        $result = array();
        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1) . ')' . substr(
                    $trace[$i],
                    strpos(
                        $trace[$i],
                        ' '
                    )
                ); // replace '#someNum' with '$i)', set the right ordering
        }
        return "\t" . implode(
                "\n\t",
                $result
            );
    }

    /**
     * Parse templates into page
     *
     * @access private
     */
    function parsePage()
    {
        $this->template->set_var(
            "STYLESHEET",
            isset($this->cfg["stylesheet"]) ? $this->cfg["stylesheet"] : null
        );
        $this->template->set_var(
            [
                "pageTitle"  => $this->getPageTitle(),
                "pageHeader" => $this->pageHeader
            ]
        );
        $this->template->set_var(
            'environmentClass',
            "environment-" . $GLOBALS['server_type']
        );
        if ($this->getFormError()) {
            if ($this->getFormErrorMessage() != '') {
                $this->template->set_var(
                    "formErrorMessage",
                    $this->getFormErrorMessage()
                );
            } else {
                $this->template->set_var(
                    "formErrorMessage",
                    CT_FORM_ERROR_MESSAGE
                );
            }
        }
        $this->template->parse(
            "CONTENTS",
            "page"
        );
        if (SHOW_TIMINGS) {
            $timeOfDay   = gettimeofday();
            $endTime     = $timeOfDay["sec"] + ($timeOfDay["usec"] / 1000000);
            $executeTime = $endTime - $this->startTime;
            $timeOfDay   = gettimeofday();
            $startTime   = $timeOfDay["sec"] + ($timeOfDay["usec"] / 1000000);
        }
        $this->template->p("CONTENTS");
        if (SHOW_TIMINGS) {
            $timeOfDay = gettimeofday();
            $endTime   = $timeOfDay["sec"] + ($timeOfDay["usec"] / 1000000);
            $pageTime  = $endTime - $startTime;
            echo "Time to excecute script: " . $executeTime . " seconds.<BR/>";
            echo "Time to return page: " . $pageTime . " seconds.<BR/>";
            echo "Total: " . ($executeTime + $pageTime) . " seconds.<BR/>";
        }
    }

    function getPageTitle()
    {
        return $this->pageTitle;
    }

    function setPageTitle($pageTitle, string $pageHeader = null)
    {
        $this->pageTitle = $pageTitle;
        if ($pageHeader) {
            $this->pageHeader = $pageHeader;
        }
    }

    function getFormError()
    {
        return $this->formError;
    }

    function getFormErrorMessage()
    {
        return $this->formErrorMessage;
    }

    function setFormErrorMessage($message)
    {
        if (func_get_arg(0) != "") $this->setFormErrorOn();
        $this->formErrorMessage = $message;
    }

    function setFormErrorOn()
    {
        $this->formError = TRUE;
    }

    /**
     * Return the HTMLOutputText
     * NOTE: Used, at present, during test mode
     * @access public
     * @return string HTML text
     */
    function getHTMLOutputText()
    {
        return $this->htmlOutputText;
    }

    /**
     * Execute the current request
     * @access public
     * @return boolean Success
     */
    function execute()
    {
        $this->setMethodName("execute");
        // This function to be defined in descendent class for anything to
        // be done before all requests are processed.
        $this->initialProcesses();
        // This actually processes the request
        $this->handleRequest();
        return TRUE;
    }

    /**
     * Anything you want to do before execution. e.g. setting variables
     * Override in decendent
     * @access private
     * @return void
     */
    function initialProcesses()
    {
    }

    /**
     * Action to perform on display add form request
     * As explained above, you will have to, at least, override the defaultAction
     * method in your decendent class to make anything happen in "execute()"
     * The generic action handlers provide assumed security levels but you
     * can quite easily preceed your own overriden methods with alternate
     * levels of security.
     * Override in decendent
     * @access public
     * @return void
     */
    function handleRequest()
    {
        $this->setMethodName("handleRequest");
        if (isset($_REQUEST['action'])) {
            $this->setAction($_REQUEST['action']);
        }
        switch ($this->getAction()) {
            case CT_ACTION_INSERT:
                $this->insert();
                break;
            case CT_ACTION_DISPLAY_ADD:
                $this->displayAddForm();
                break;
            case CT_ACTION_DISPLAY_DELETE:
                $this->displayDeleteForm();
                break;
            case CT_ACTION_DELETE:
                $this->delete();
                break;
            case CT_ACTION_DISPLAY_EDIT:
                $this->displayEditForm();
                break;
            case CT_ACTION_UPDATE:
                $this->update();
                break;
            default:
                try {
                    $this->defaultAction();
                } catch (\CNCLTD\Exceptions\JsonHttpException $exception) {
                    echo $exception->getMessage();
                    http_response_code($exception->getResponseCode());
                    exit;
                }
                break;
        }
    }

    /**
     * Get the HTML action
     * @access public
     * @return string Action CT_METHOD_POST or CT_METHOD_GET
     */
    function getAction()
    {
        return $this->action;
    }

    /**
     * Set the HTML action
     * @access public
     * @param string $action CT_METHOD_POST or CT_METHOD_GET
     * @return boolean Success
     */
    function setAction($action)
    {
        $this->action = $action;
        return TRUE;
    }

    /**
     * Action to perform on insert
     * Override in decendent
     * @access private
     * @return void
     */
    function insert()
    {
    }

    /**
     * Action to perform on display add form request
     * Override in decendent
     * @access private
     * @return void
     */
    function displayAddForm()
    {
    }

    /**
     * Action to perform on display delete form request
     * Override in decendent
     * @access private
     * @return void
     */
    function displayDeleteForm()
    {
    }

    /**
     * Action to perform on delete
     * Override in decendent
     * @access private
     * @return void
     */
    function delete()
    {
    }

    /**
     * Action to perform on display edit form request
     * Override in decendent
     * @access private
     * @return void
     */
    function displayEditForm()
    {
    }

    /**
     * Action to perform on update request
     * Override in decendent
     * @access private
     * @return void
     */
    function update()
    {
    }

    /**
     * Override this one with the action to take when none of the generic
     * action parameters have been passed
     *
     * e.g. You may simply want some code to display a page.
     *
     * HINT:
     *
     *    You can add your own list of actions like this:
     *
     *    In your defaultAction function, add your own switch statement with the new
     *    actions:
     *
     *    function defaultAction(){
     *        switch ($this->getAction()){
     *            case "new_action":
     *                <------ CODE TO DO NEW ACTION HERE ------>
     *                break;
     *            default:                    // This becomes the new defaultAction
     *                <------ CODE TO DISPLAY PAGE HERE ----->
     *                break;
     *        }
     *    }
     *
     * SECURITY WARNING: The default action only provides user-level permissions
     *        checking(if authentication is on) as it stands so you may want to preceed
     *        your code with $this->permCheck(<level>)
     * @access private
     * @return void
     */
    function defaultAction()
    {

    }

    function getSessionParam($paramName)
    {
        if (!$paramName) {
            return null;
        }
        if (!isset($_SESSION[$paramName])) {
            return null;
        }
        return $_SESSION[$paramName];
    }

    function unsetSessionParam($paramName)
    {
        unset($_SESSION[$paramName]);
    }

    function setSessionParam($paramName, $value)
    {
        $_SESSION[$paramName] = $value;
    }

    /**
     * Reset the HTML action
     * @access private
     * @return boolean Success
     */
    function resetAction()
    {
        $this->action = "";
        return TRUE;
    }

    /**
     * All clean-up on completion of client request
     * @access private
     * @return boolean Success
     */
    function pageClose()
    {
        $this->setMethodName("pageClose");
//		ob_end_flush();				// flush output buffer (to client)
    }

    /**
     * Add a string to the end of the generated page title
     * @access private
     * @returns void
     */
    function addStringToPageTitle($newString)
    {
        // Truncate if it will become longer than max allowed
        $totalNewLength = strlen($this->pageTitle) + strlen($newString) + 2;
        if ($totalNewLength > MAX_PAGE_TITLE) {
            $this->pageTitle = substr(
                $this->pageTitle,
                0,
                (strlen($this->pageTitle) - ($totalNewLength - MAX_PAGE_TITLE) - 3)
            );
            $this->pageTitle = $this->pageTitle . "...";
        }
        if ($this->pageTitle != "") {
            $this->pageTitle = $this->pageTitle . "> ";
        }
        $this->pageTitle = $this->pageTitle . $newString;
    }

    /**
     * Check Email Format is valid
     * @access private
     */
    function checkEmailFormat($emailAddress)
    {
        return !!filter_var(
            $emailAddress,
            FILTER_VALIDATE_EMAIL
        );
    }

    /**
     *  This loops around all the get vars and post vars giving prioity to post vars
     * @access private
     * @param String $variableName The variable to retrieve/set
     */
    function retrieveHTMLVars()
    {
        while (list ($key, $val) = each($this->getVars)) {
            if ($key != "ig") {                                            // Don't try to find $this->SetIg(), [I]diot [G]uard is simply to avoid unwanted caching
                $this->getHTMLGetVar($key);
            }
        }
        if (isset($this->postVars["form"])) {
            while (list ($key, $val) = each($this->postVars["form"])) {
                $this->getHTMLPostVar($key);
            }
        }
        if (isset($this->getVars)) {

            while (list ($key, $val) = each($this->getVars)) {
                $this->getHTMLGetVar($key);
            }
        }
    }

    /**
     * This builds a call at run time to methord 'getHTMLGetVar'.
     * the varibale name is collected then reused with 'ucwords' to make it uppercase, needs to have slashes removed
     * @access private
     * @param String $variableName The variable to retrieve/set
     */
    function getHTMLGetVar($variableName)
    {
        $this->setMethodName("getHTMLGetVar");
        $methodName = "set" . ucwords($variableName);
        if (!method_exists(
            $this,
            $methodName
        )) {
//			$this->displayFatalError("Method ".$methodName."() does not exist");
        } else {
            $command = "if(isset(\$this->getVars[\"" . $variableName . "\"]))" . "\$this->" . $methodName . "(stripslashes(\$this->getVars[\"" . $variableName . "\"]));";
            eval($command);
        }
    }

    /**
     * This builds a call at run time to methord 'getHTMLPostVar'. the post must be from an HTML array named 'form'
     * the varibale name is collected then reused with 'ucwords' to make it uppercase, needs to have slashes removed
     * @access private
     * @param String $variableName The variable to retrieve/set
     */
    function getHTMLPostVar($variableName)
    {
        $this->setMethodName("getHTMLPostVar");
        $methodName = "set" . ucwords($variableName);
        if (!method_exists(
            $this,
            $methodName
        )) {
            //$this->displayFatalError("Method ".$methodName."() does not exist");
        } else {
            if (isset($this->postVars['form'][$variableName])) {
                call_user_func([$this, $methodName], stripslashes_deep($this->postVars['form'][$variableName]));
            }
        }
    }

    /**
     * This builds a call at run time to methord 'getHTMLGetVar'.
     * the varibale name is collected then reused with 'ucwords' to make it uppercase, needs to have slashes removed
     * @access private
     * @param String $variableName The variable to retrieve/set
     */
    function setNumericVar($variableName,
                           $value
    )
    {
        if (!is_numeric($value) & ($value != '')) {
            $this->displayFatalError('Non-numeric value passed to numeric variable ' . $variableName);
        } else {
            eval('$this->' . $variableName . '=\'' . $value . '\';');
        }
    }

    /**
     * Prepare string for DB
     */
    function myText($string)
    {
        $string = mysql_real_escape_string($string);
        return trim($string);
    }

    /**
     * Prepare string for display in HTML text input field
     */
    function htmlInputText($text)
    {
//		$text = addslashes($text);// replaced because it resulted in e.g. Karim O\'Ahmed
//		$text = str_replace('"','\"', $text);	// this one only escapes "
        $text = htmlspecialchars(
            $text,
            ENT_QUOTES
        );
        return trim($text);
    }

    /**
     * Prepare string for display in HTML text area input field
     */
    function htmlTextArea($text)
    {
        return trim($text);
    }

    protected function setParam(string $string, $value)
    {
        $_REQUEST[$string] = $value;
    }

    public function loadReactCSS(string $string)
    {
        $version = $this->getVersion();
        if (!$this->template) {
            throw new Exception('Please define a template first');
        }
        $this->template->setVar(
            'javaScript',
            "<link rel='stylesheet'  href='components/dist/$string?$version'>",
            true
        );
    }

    protected function getVersion()
    {
        if (!$this->cachedVersion) {
            $this->cachedVersion = \CNCLTD\Utils::getCurrentChangelogVersion();
        }
        return $this->cachedVersion;
    }

    public function loadReactScript(string $string)
    {
        $version = $this->getVersion();
        if (!$this->template) {
            throw new Exception('Please define a template first');
        }
        $this->template->setVar(
            'javaScript',
            "<script src='components/dist/$string?$version'></script>",
            true
        );

    }
}// End of class
?>