<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/04/2018
 * Time: 9:19
 */

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEPassword.inc.php');

class CTNotAllowed extends CTCNC
{
    function defaultAction()
    {
        $this->setPageTitle('Not Allowed');
        $this->setTemplateFiles(
            array('NotAllowed' => 'NotAllowed')
        );

        $this->template->parse('CONTENTS', 'NotAllowed', true);
        $this->parsePage();
    }
}