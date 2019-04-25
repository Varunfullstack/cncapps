<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Syonix\ChangelogViewer\Factory\ViewerFactory;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTAbout extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->displayList();
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('About');
        $this->setTemplateFiles(
            array('ChangeLog' => 'About.inc')
        );

        $changelog = ViewerFactory::createMarkdownHtmlViewer(__DIR__ . '/../../CHANGELOG.md')
            ->frame(false)
            ->styles(false)
            ->downloadLinks(false)
//            ->modal(true)
            ->scripts(false)
            ->build();

        $this->template->set_var('changeLog', $changelog);

        $this->template->parse('CONTENTS', 'ChangeLog', true);
        $this->parsePage();
    }
}// end of class
