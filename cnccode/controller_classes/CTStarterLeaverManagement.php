<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/01/2019
 * Time: 11:28
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEStarterLeaverQuestion.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTStarterLeaverManagement extends CTCNC
{
    var $dsStandardText = '';
    var $buStandardText = '';

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $this->dsStandardText = new DSForm($this);
        $this->dsStandardText->copyColumnsFrom(new DBEStarterLeaverQuestion($this));
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case 'addQuestion':
                try {

                    $this->addQuestion();
                } catch (\Exception $exception) {

                }
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
        $this->setPageTitle('Starter Leaver Management');
        $this->setTemplateFiles(
            [
                'StarterLeaverManagement'      => 'StarterLeaverManagement',
                'StarterLeaverQuestionSection' => 'StarterLeaverQuestionSection'
            ]
        );


        $this->template->setVar(
            [
                "thingy" => "test"
            ]
        );

        $this->template->parse(
            'starterLeaverQuestionCreationSection',
            "StarterLeaverQuestionSection",
            true
        );


        $this->template->parse(
            'CONTENTS',
            'StarterLeaverManagement',
            true
        );
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
                $this->buStandardText->getStandardTextByID(
                    $_REQUEST['stt_standardtextno'],
                    $dsStandardText
                );
                $stt_standardtextno = $_REQUEST['stt_standardtextno'];
            } else {                                                                    // creating new
                $dsStandardText->initialise();
                $dsStandardText->setValue(
                    'stt_standardtextno',
                    '0'
                );
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
                        'action'             => CTSTANDARDTEXT_ACT_DELETE,
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
                    'action'             => CTSTANDARDTEXT_ACT_UPDATE,
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
                'stt_standardtextno'    => $stt_standardtextno,
                'stt_sort_order'        => Controller::htmlInputText($dsStandardText->getValue('stt_sort_order')),
                'stt_sort_orderMessage' => Controller::htmlDisplayText($dsStandardText->getMessage('stt_sort_order')),
                'stt_desc'              => Controller::htmlInputText($dsStandardText->getValue('stt_desc')),
                'stt_descMessage'       => Controller::htmlDisplayText($dsStandardText->getMessage('stt_desc')),
                'stt_text'              => Controller::htmlInputText($dsStandardText->getValue('stt_text')),
                'stt_textMessage'       => Controller::htmlDisplayText($dsStandardText->getMessage('stt_text')),
                'urlUpdate'             => $urlUpdate,
                'urlDelete'             => $urlDelete,
                'txtDelete'             => $txtDelete,
                'urlDisplayList'        => $urlDisplayList
            )
        );

        /* type selector */
        // activity status selector
        $this->template->set_block(
            'StandardTextEdit',
            'typeBlock',
            'types'
        ); // ss avoids naming confict!
        if ($this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $statusArray = &$this->statusArrayCustomer;
        } else {
            $statusArray = &$this->statusArray;
        }

        $dbeStandardTextType = new DBEStandardTextType($this);

        $dbeStandardTextType->getRows('description');

        while ($dbeStandardTextType->fetchNext()) {
            $selected = ($dsStandardText->getValue('stt_standardtexttypeno') == $dbeStandardTextType->getPKValue(
                )) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'typeSelected'           => $selected,
                    'stt_standardtexttypeno' => $dbeStandardTextType->getValue('standardTextTypeID'),
                    'typeDescription'        => $dbeStandardTextType->getValue('description'),
                    'variables'              => $dbeStandardTextType->getValue(DBEStandardTextType::variables)
                )
            );
            $this->template->parse(
                'types',
                'typeBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'StandardTextEdit',
            true
        );
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
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'stt_standardtextno' => $this->dsStandardText->getValue('stt_standardtextno'),
                    'action'             => CTCNC_ACT_VIEW
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

    private function addQuestion()
    {
        if (!isset($_REQUEST['question'])) {
            throw new Exception('Question array is not set');
        }

        if (!isset($_REQUEST['question']['customerID'])) {
            throw new Exception('Customer is not set');
        }

        $questionData = $_REQUEST['question'];


        $dbeStarterLeaverQuestion = new DBEStarterLeaverQuestion($this);

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::customerID,
            $questionData[DBEStarterLeaverQuestion::customerID]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::formType,
            $questionData[DBEStarterLeaverQuestion::formType]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::name,
            $questionData[DBEStarterLeaverQuestion::name]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::sortOrder,
            $dbeStarterLeaverQuestion->getNextSortOrder()
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::required,
            isset($questionData[DBEStarterLeaverQuestion::required])
        );




    }

}