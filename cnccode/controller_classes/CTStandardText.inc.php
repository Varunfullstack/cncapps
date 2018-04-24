<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUStandardText.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardTextType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTSTANDARDTEXT_ACT_DISPLAY_LIST', 'standardTextList');
define('CTSTANDARDTEXT_ACT_CREATE', 'createStandardText');
define('CTSTANDARDTEXT_ACT_EDIT', 'editStandardText');
define('CTSTANDARDTEXT_ACT_DELETE', 'deleteStandardText');
define('CTSTANDARDTEXT_ACT_UPDATE', 'updateStandardText');

class CTSTANDARDTEXT extends CTCNC
{
    var $dsStandardText = '';
    var $buStandardText = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "maintenance",
        ];
        if (!self::canAccess($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buStandardText = new BUStandardText($this);
        $this->dsStandardText = new DSForm($this);
        $this->dsStandardText->copyColumnsFrom($this->buStandardText->dbeStandardText);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTSTANDARDTEXT_ACT_EDIT:
            case CTSTANDARDTEXT_ACT_CREATE:
                $this->edit();
                break;
            case CTSTANDARDTEXT_ACT_DELETE:
                $this->delete();
                break;
            case CTSTANDARDTEXT_ACT_UPDATE:
                $this->update();
                break;
            case CTSTANDARDTEXT_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Standard Text');
        $this->setTemplateFiles(
            array('StandardTextList' => 'StandardTextList.inc')
        );

        $this->buStandardText->getAllTypes($dsStandardText);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSTANDARDTEXT_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        $dbeStandardTextType = new DBEStandardTextType($this);

        if ($dsStandardText->rowCount() > 0) {
            $this->template->set_block('StandardTextList', 'standardTextBlock', 'standardTexts');
            while ($dsStandardText->fetchNext()) {

                $dbeStandardTextType->getRow($dsStandardText->getValue('stt_standardtexttypeno'));

                $stt_standardtextno = $dsStandardText->getValue('stt_standardtextno');

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSTANDARDTEXT_ACT_EDIT,
                            'stt_standardtextno' => $stt_standardtextno
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSTANDARDTEXT_ACT_DELETE,
                            'stt_standardtextno' => $stt_standardtextno
                        )
                    );
                $txtDelete = '[delete]';

                $this->template->set_var(
                    array(
                        'stt_standardtextno' => $stt_standardtextno,
                        'stt_desc' => Controller::htmlDisplayText($dsStandardText->getValue('stt_desc')),
                        'type' => Controller::htmlDisplayText($dbeStandardTextType->getValue('description')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );
                $this->template->parse('standardTexts', 'standardTextBlock', true);
            }//while $dsStandardText->fetchNext()
        }
        $this->template->parse('CONTENTS', 'StandardTextList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsStandardText = &$this->dsStandardText; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTSTANDARDTEXT_ACT_EDIT) {
                $this->buStandardText->getStandardTextByID($_REQUEST['stt_standardtextno'], $dsStandardText);
                $stt_standardtextno = $_REQUEST['stt_standardtextno'];
            } else {                                                                    // creating new
                $dsStandardText->initialise();
                $dsStandardText->setValue('stt_standardtextno', '0');
                $stt_standardtextno = '0';
            }
        } else {                                                                        // form validation error
            $dsStandardText->initialise();
            $dsStandardText->fetchNext();
            $stt_standardtextno = $dsStandardText->getValue('stt_standardtextno');
        }
        if ($_REQUEST['action'] == CTSTANDARDTEXT_ACT_EDIT) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSTANDARDTEXT_ACT_DELETE,
                        'stt_standardtextno' => $stt_standardtextno
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSTANDARDTEXT_ACT_UPDATE,
                    'stt_standardtextno' => $stt_standardtextno
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSTANDARDTEXT_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Standard Text');
        $this->setTemplateFiles(
            array('StandardTextEdit' => 'StandardTextEdit.inc')
        );
        $this->template->set_var(
            array(
                'stt_standardtextno' => $stt_standardtextno,
                'stt_sort_order' => Controller::htmlInputText($dsStandardText->getValue('stt_sort_order')),
                'stt_sort_orderMessage' => Controller::htmlDisplayText($dsStandardText->getMessage('stt_sort_order')),
                'stt_desc' => Controller::htmlInputText($dsStandardText->getValue('stt_desc')),
                'stt_descMessage' => Controller::htmlDisplayText($dsStandardText->getMessage('stt_desc')),
                'stt_text' => Controller::htmlInputText($dsStandardText->getValue('stt_text')),
                'stt_textMessage' => Controller::htmlDisplayText($dsStandardText->getMessage('stt_text')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );

        /* type selector */
        // activity status selector
        $this->template->set_block('StandardTextEdit', 'typeBlock', 'types'); // ss avoids naming confict!
        if ($this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $statusArray = &$this->statusArrayCustomer;
        } else {
            $statusArray = &$this->statusArray;
        }

        $dbeStandardTextType = new DBEStandardTextType($this);

        $dbeStandardTextType->getRows('description');

        while ($dbeStandardTextType->fetchNext()) {
            $selected = ($dsStandardText->getValue('stt_standardtexttypeno') == $dbeStandardTextType->getPKValue()) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'typeSelected' => $selected,
                    'stt_standardtexttypeno' => $dbeStandardTextType->getValue('standardTextTypeID'),
                    'typeDescription' => $dbeStandardTextType->getValue('description')
                )
            );
            $this->template->parse('types', 'typeBlock', true);
        }


        $this->template->parse('CONTENTS', 'StandardTextEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsStandardText = &$this->dsStandardText;

        $this->formError = (!$this->dsStandardText->populateFromArray($_REQUEST['standardText']));

        if ($this->formError) {
            if ($this->dsStandardText->getValue('stt_standardtextno') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTSTANDARDTEXT_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTSTANDARDTEXT_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buStandardText->updateStandardText($this->dsStandardText);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'stt_standardtextno' => $this->dsStandardText->getValue('stt_standardtextno'),
                                 'action' => CTCNC_ACT_VIEW
                             )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buStandardText->deleteStandardText($_REQUEST['stt_standardtextno'])) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSTANDARDTEXT_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>