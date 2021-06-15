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
define(
    'CTSTANDARDTEXT_ACT_DISPLAY_LIST',
    'standardTextList'
);
define(
    'CTSTANDARDTEXT_ACT_CREATE',
    'createStandardText'
);
define(
    'CTSTANDARDTEXT_ACT_EDIT',
    'editStandardText'
);
define(
    'CTSTANDARDTEXT_ACT_DELETE',
    'deleteStandardText'
);
define(
    'CTSTANDARDTEXT_ACT_UPDATE',
    'updateStandardText'
);

class CTStandardText extends CTCNC
{
    const GET_SALES_REQUEST_OPTIONS  = "getSalesRequestOptions";
    const GET_CHANGE_REQUEST_OPTIONS = "getChangeRequestOptions";
    const GET_BY_TYPE                = "getByType";
    /** @var DSForm */
    public $dsStandardText;
    /** @var BUStandardText */
    public $buStandardText;

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
        $this->setMenuId(806);
        $this->buStandardText = new BUStandardText($this);
        $this->dsStandardText = new DSForm($this);
        $this->dsStandardText->copyColumnsFrom($this->buStandardText->dbeStandardText);

    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case CTSTANDARDTEXT_ACT_EDIT:
            case CTSTANDARDTEXT_ACT_CREATE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->edit();
                break;
            case CTSTANDARDTEXT_ACT_DELETE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->delete();
                break;
            case CTSTANDARDTEXT_ACT_UPDATE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->update();
                break;
            case self::GET_SALES_REQUEST_OPTIONS:
                try {
                    $data = $this->getStandardTextOptionsForType('Sales Request');
                } catch (Exception $exception) {
                    $data = [
                        "error" => $exception->getMessage()
                    ];
                }
                echo json_encode(
                    $data,
                    JSON_NUMERIC_CHECK
                );
                break;
            case self::GET_CHANGE_REQUEST_OPTIONS :
                try {
                    $data = $this->getStandardTextOptionsForType("Change Request");
                } catch (Exception $exception) {
                    $data = [
                        "error" => $exception->getMessage()
                    ];
                }
                echo json_encode(
                    $data,
                    JSON_NUMERIC_CHECK
                );
                break;
            case self::GET_BY_TYPE :
                //UnableToOfferFirstTimeFixReasonOptions
                try {
                    $data = $this->getStandardTextOptionsForType($_REQUEST["type"]);
                } catch (Exception $exception) {
                    $data = [
                        "error" => $exception->getMessage()
                    ];
                }
                echo json_encode(
                    $data,
                    JSON_NUMERIC_CHECK
                );
                break;
            case "getList":
                echo json_encode($this->getList());
                exit;
            case CTSTANDARDTEXT_ACT_DISPLAY_LIST:
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->displayList();
                break;
        }
    }

    /**
     * Edit/Add Further Action
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsStandardText = &$this->dsStandardText; // ref to class var
        if (!$this->getFormError()) {
            if ($this->getAction() == CTSTANDARDTEXT_ACT_EDIT) {
                $this->buStandardText->getStandardTextByID(
                    $this->getParam('stt_standardtextno'),
                    $dsStandardText
                );
                $stt_standardtextno = $this->getParam('stt_standardtextno');
            } else {                                                                    // creating new
                $dsStandardText->initialise();
                $dsStandardText->setValue(
                    DBEStandardText::stt_standardtextno,
                    null
                );
                $stt_standardtextno = null;
            }
        } else {                                                                        // form validation error
            $dsStandardText->initialise();
            $dsStandardText->fetchNext();
            $stt_standardtextno = $dsStandardText->getValue(DBEStandardText::stt_standardtextno);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTSTANDARDTEXT_ACT_EDIT) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'             => CTSTANDARDTEXT_ACT_DELETE,
                    'stt_standardtextno' => $stt_standardtextno
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate      = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'             => CTSTANDARDTEXT_ACT_UPDATE,
                'stt_standardtextno' => $stt_standardtextno
            )
        );
        $urlDisplayList = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTSTANDARDTEXT_ACT_DISPLAY_LIST
            )
        );
        $this->setPageTitle('Edit Standard Text');
        $this->setTemplateFiles(
            array('StandardTextEdit' => 'StandardTextEdit.inc')
        );
        /* type selector */
        // activity status selector
        $this->template->set_block(
            'StandardTextEdit',
            'typeBlock',
            'types'
        ); // ss avoids naming conflict!
        $dbeStandardTextType = new DBEStandardTextType($this);
        $dbeStandardTextType->getRows('description');
        while ($dbeStandardTextType->fetchNext()) {
            $selected = ($dsStandardText->getValue(
                    DBEStandardText::stt_standardtexttypeno
                ) == $dbeStandardTextType->getPKValue()) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'typeSelected'           => $selected,
                    'stt_standardtexttypeno' => $dbeStandardTextType->getValue(DBEStandardTextType::standardTextTypeID),
                    'typeDescription'        => $dbeStandardTextType->getValue(DBEStandardTextType::description),
                    'variables'              => $dbeStandardTextType->getValue(DBEStandardTextType::variables)
                )
            );
            $this->template->parse(
                'types',
                'typeBlock',
                true
            );
        }
        $this->template->set_var(
            array(
                'stt_standardtextno'                        => $stt_standardtextno,
                'stt_desc'                                  => Controller::htmlInputText(
                    $dsStandardText->getValue(DBEStandardText::stt_desc)
                ),
                'stt_descMessage'                           => Controller::htmlDisplayText(
                    $dsStandardText->getMessage(DBEStandardText::stt_desc)
                ),
                'stt_text'                                  => Controller::htmlInputText(
                    $dsStandardText->getValue(DBEStandardText::stt_text)
                ),
                'stt_textMessage'                           => Controller::htmlDisplayText(
                    $dsStandardText->getMessage(DBEStandardText::stt_text)
                ),
                'urlUpdate'                                 => $urlUpdate,
                'urlDelete'                                 => $urlDelete,
                'txtDelete'                                 => $txtDelete,
                'urlDisplayList'                            => $urlDisplayList,
                'salesRequestEmail'                         => $dsStandardText->getValue(
                    DBEStandardText::salesRequestEmail
                ),
                'salesRequestUnassignFlagChecked'           => $this->getChecked(
                    $dsStandardText->getValue(DBEStandardText::salesRequestUnassignFlag)
                ),
                'salesRequestDoNotNotifySalesOptionChecked' => $this->dsStandardText->getValue(
                    DBEStandardText::salesRequestDoNotNotifySalesOption
                ) ? 'checked' : null
            )
        );
        $this->template->parse(
            'CONTENTS',
            'StandardTextEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buStandardText->deleteStandardText($this->getParam('stt_standardtextno'))) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSTANDARDTEXT_ACT_DISPLAY_LIST
                )
            );
            header('Location: ' . $urlNext);
            exit;
        }
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $standardTextArray                = $this->getParam('standardText');
        $standardTextArray[1]['stt_text'] = preg_replace(
            '/(<[^>]+) style=".*?"/i',
            '$1',
            $standardTextArray[1]['stt_text']
        );
        $this->formError                  = (!$this->dsStandardText->populateFromArray($standardTextArray));
        if ($this->formError) {
            if (!$this->dsStandardText->getValue(
                DBEStandardText::stt_standardtextno
            )) {                    // attempt to insert
                $this->setAction(CTSTANDARDTEXT_ACT_EDIT);
            } else {
                $this->setAction(CTSTANDARDTEXT_ACT_CREATE);
            }
            $this->edit();
            exit;
        }
        $this->buStandardText->updateStandardText($this->dsStandardText);
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'stt_standardtextno' => $this->dsStandardText->getValue(DBEStandardText::stt_standardtextno),
                'action'             => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * @param string $standardTextType
     * @return array
     */
    private function getStandardTextOptionsForType($standardTextType)
    {
        $DBEStandardTextType = new DBEStandardTextType($this);
        $DBEStandardTextType->setValue(
            DBEStandardTextType::description,
            $standardTextType
        );
        $DBEStandardTextType->getRowsByColumn(DBEStandardTextType::description);
        $DBEStandardTextType->fetchNext();
        $dsOptions = new DataSet($this);
        $this->buStandardText->getStandardTextByTypeID(
            $DBEStandardTextType->getValue(DBEStandardTextType::standardTextTypeID),
            $dsOptions
        );
        $options = [];
        while ($dsOptions->fetchNext()) {
            $options[] = [
                "id"       => $dsOptions->getValue(DBEStandardText::stt_standardtextno),
                "template" => $dsOptions->getValue(DBEStandardText::stt_text),
                'name'     => $dsOptions->getValue(DBEStandardText::stt_desc)
            ];
        }
        return $options;
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Standard Text');
        $this->setTemplateFiles(
            array('StandardTextList' => 'StandardTextList.inc')
        );
        $dsStandardText = new DataSet($this);
        $this->buStandardText->getAllTypes($dsStandardText);
        $urlCreate = Controller::buildLink(
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
            $this->template->set_block(
                'StandardTextList',
                'standardTextBlock',
                'standardTexts'
            );
            while ($dsStandardText->fetchNext()) {

                $dbeStandardTextType->getRow($dsStandardText->getValue(DBEStandardText::stt_standardtexttypeno));
                $stt_standardtextno = $dsStandardText->getValue(DBEStandardText::stt_standardtextno);
                $urlEdit            = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'             => CTSTANDARDTEXT_ACT_EDIT,
                        'stt_standardtextno' => $stt_standardtextno
                    )
                );
                $txtEdit            = '[edit]';
                $urlDelete          = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'             => CTSTANDARDTEXT_ACT_DELETE,
                        'stt_standardtextno' => $stt_standardtextno
                    )
                );
                $txtDelete          = '[delete]';
                $this->template->set_var(
                    array(
                        'stt_standardtextno' => $stt_standardtextno,
                        'stt_desc'           => Controller::htmlDisplayText(
                            $dsStandardText->getValue(DBEStandardText::stt_desc)
                        ),
                        'type'               => Controller::htmlDisplayText(
                            $dbeStandardTextType->getValue(DBEStandardTextType::description)
                        ),
                        'urlEdit'            => $urlEdit,
                        'urlDelete'          => $urlDelete,
                        'txtEdit'            => $txtEdit,
                        'txtDelete'          => $txtDelete,
                    )
                );
                $this->template->parse(
                    'standardTexts',
                    'standardTextBlock',
                    true
                );
            }//while $dsStandardText->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'StandardTextList',
            true
        );
        $this->parsePage();
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function getList()
    {
        $this->setMethodName('getList');
        $dsStandardText = new DataSet($this);
        $this->buStandardText->getAllTypes($dsStandardText);
        $list = array();
        if ($dsStandardText->rowCount() > 0) {
            while ($dsStandardText->fetchNext()) {
                $stt_standardtextno = $dsStandardText->getValue(DBEStandardText::stt_standardtextno);
                array_push(
                    $list,
                    array(
                        'id'      => $stt_standardtextno,
                        'title'   => $dsStandardText->getValue(DBEStandardText::stt_desc),
                        'content' => $dsStandardText->getValue(DBEStandardText::stt_text),
                        'typeId'  => $dsStandardText->getValue(DBEStandardText::stt_standardtexttypeno)
                    )
                );

            }
        }
        return $list;
    }
}
