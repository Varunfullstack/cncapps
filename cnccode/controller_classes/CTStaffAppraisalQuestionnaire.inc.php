<?php
/**
 * Questionnaire controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUStaffAppraisalQuestionnaire.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions

class CTStaffAppraisalQuestionnaire extends CTCNC
{
    /** @var DataSet */
    private $dsQuestionnaire;
    /** @var  BUStaffAppraisalQuestionnaire */
    private $buQuestionnaire;
    /** @var DataSet */
    private $dsQuestion;

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
        $roles = [
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buQuestionnaire = new BUStaffAppraisalQuestionnaire($this);
        $this->dsQuestionnaire = new DSForm($this);
        $this->dsQuestionnaire->copyColumnsFrom($this->buQuestionnaire->dbeQuestionnaire);
        $this->dsQuestion = new DSForm($this);
        $this->dsQuestion->copyColumnsFrom($this->buQuestionnaire->dbeQuestion);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case 'createQuestion':
            case 'editQuestion':
                $this->editQuestion();
                break;
            case 'deleteQuestion':
                $this->deleteQuestion();
                break;
            case 'updateQuestion':
                $this->updateQuestion();
                break;
            case 'displayQuestionList':
                $this->displayQuestions();
                break;
            case 'create':
            case 'edit':
                $this->edit();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'update':
                $this->update();
                break;
            case 'displayList':
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of questionnaires
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Questionnaires');
        $this->setTemplateFiles(
            array('QuestionnaireList' => 'QuestionnaireList.inc')
        );

        $this->buQuestionnaire->getAll($dsQuestionnaire);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'create'
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsQuestionnaire->rowCount() > 0) {

            $this->template->set_block(
                'QuestionnaireList',
                'QuestionnaireBlock',
                'rows'
            );

            while ($dsQuestionnaire->fetchNext()) {

                $questionnaireID = $dsQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::id);

                $urlDisplayQuestionList =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'          => 'displayQuestionList',
                            'questionnaireID' => $questionnaireID
                        )
                    );
                $urlView =
                    $this->buildLink(
                        'http://cnc-ltd.co.uk/questionnaire/index.php',
                        array(
                            'questionnaireno' => $questionnaireID
                        )
                    );

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'          => 'edit',
                            'questionnaireID' => $questionnaireID
                        )
                    );
                $txtEdit = '[edit]';

                if ($this->buQuestionnaire->canDelete($questionnaireID)) {
                    $urlDelete =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'          => 'delete',
                                'questionnaireID' => $questionnaireID
                            )
                        );
                    $txtDelete = '[delete]';
                } else {
                    $urlDelete = '';
                    $txtDelete = '';
                }

                $this->template->set_var(
                    array(
                        'questionnaireID'        => $questionnaireID,
                        'description'            => Controller::htmlDisplayText(
                            $dsQuestionnaire->getValue('description')
                        ),
                        'urlEdit'                => $urlEdit,
                        'urlDisplayQuestionList' => $urlDisplayQuestionList,
                        'urlDelete'              => $urlDelete,
                        'txtEdit'                => $txtEdit,
                        'txtDelete'              => $txtDelete,
                        'urlView'                => $urlView
                    )
                );

                $this->template->parse(
                    'rows',
                    'QuestionnaireBlock',
                    true
                );

            }//while $dsQuestionnaire->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'QuestionnaireList',
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
        $dsQuestionnaire = &$this->dsQuestionnaire; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buQuestionnaire->getQuestionnaireByID(
                    $_REQUEST['questionnaireID'],
                    $dsQuestionnaire
                );
                $questionnaireID = $_REQUEST['questionnaireID'];
            } else {                                                                    // creating new
                $dsQuestionnaire->initialise();
                $dsQuestionnaire->setValue(
                    DBEStaffAppraisalQuestionnaire::id,
                    '0'
                );
                $questionnaireID = '0';
            }
        } else {                                                                        // form validation error
            $dsQuestionnaire->initialise();
            $dsQuestionnaire->fetchNext();
            $questionnaireID = $dsQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::id);
        }
        if ($_REQUEST['action'] == 'edit' && $this->buQuestionnaire->canDelete($_REQUEST['questionnaireID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'          => 'delete',
                        'questionnaireID' => $questionnaireID
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
                    'action'          => 'update',
                    'questionnaireID' => $questionnaireID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'displayList'
                )
            );
        $this->setPageTitle('Edit Questionnaire');
        $this->setTemplateFiles(
            array('QuestionnaireEdit' => 'StaffAppraisalQuestionnaireEdit.inc')
        );
        $this->template->set_var(
            array(
                'id'                 => $questionnaireID,
                'description'        => Controller::htmlInputText($dsQuestionnaire->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsQuestionnaire->getMessage('description')),
                //                'intro'               => Controller::htmlInputText($dsQuestionnaire->getValue('intro')),
                //                'introMessage'        => Controller::htmlDisplayText($dsQuestionnaire->getMessage('intro')),
                //                'thankYou'            => Controller::htmlInputText($dsQuestionnaire->getValue('thankYou')),
                //                'thankYouMessage'     => Controller::htmlDisplayText($dsQuestionnaire->getMessage('thankYou')),
                //                'rating1Desc'         => Controller::htmlInputText($dsQuestionnaire->getValue('rating1Desc')),
                //                'rating1DescMessage'  => Controller::htmlDisplayText($dsQuestionnaire->getMessage('rating1Desc')),
                //                'rating5Desc'         => Controller::htmlInputText($dsQuestionnaire->getValue('rating5Desc')),
                //                'rating5DescMessage'  => Controller::htmlDisplayText($dsQuestionnaire->getMessage('rating5Desc')),
                //                'nameRequiredChecked' => Controller::htmlChecked($dsQuestionnaire->getValue('nameRequired')),
                //                'nameRequiredMessage' => Controller::htmlDisplayText($dsQuestionnaire->getMessage('nameRequired')),
                'urlUpdate'          => $urlUpdate,
                'urlDelete'          => $urlDelete,
                'txtDelete'          => $txtDelete,
                'urlDisplayList'     => $urlDisplayList
            )
        );
        $this->template->parse(
            'CONTENTS',
            'QuestionnaireEdit',
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
        $dsQuestionnaire = &$this->dsQuestionnaire;
//        var_dump($_REQUEST);
        $this->formError = (!$this->dsQuestionnaire->populateFromArray($_REQUEST['questionnaire']));
        if ($this->formError) {
            var_dump($this->formError);
            var_dump($this->formErrorMessage);
            if ($this->dsQuestionnaire->getValue(
                    DBEStaffAppraisalQuestionnaire::id
                ) == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $this->buQuestionnaire->updateQuestionnaire($this->dsQuestionnaire);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'questionnaireID' => $this->dsQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::id),
                    'action'          => 'view'
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Questionnaire
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buQuestionnaire->deleteQuestionnaire($_REQUEST['questionnaireID'])) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'displayList'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * Display list of questions
     * @access private
     */
    function displayQuestions()
    {
        $this->setMethodName('displayQuestions');
        $this->setTemplateFiles(
            array('QuestionList' => 'StaffAppraisalQuestionList.inc')
        );

        $this->buQuestionnaire->getAllQuestions(
            $_REQUEST['questionnaireID'],
            $dsQuestion
        );

        $this->buQuestionnaire->getQuestionnaireByID(
            $dsQuestion->getValue('questionnaireID'),
            $dsQuestionnaire
        );

        $this->setPageTitle($dsQuestionnaire->getValue('description'));

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => 'createQuestion',
                    'questionnaireID' => $_REQUEST['questionnaireID']

                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsQuestion->rowCount() > 0) {

            $this->template->set_block(
                'QuestionList',
                'QuestionBlock',
                'rows'
            );

            while ($dsQuestion->fetchNext()) {

                $questionID = $dsQuestion->getValue(DBEStaffAppraisalQuestion::id);

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'editQuestion',
                            'questionID' => $questionID
                        )
                    );
                $txtEdit = '[edit]';

                if ($this->buQuestionnaire->canDelete($questionID)) {
                    $urlDelete =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => 'delete',
                                'questionID' => $questionID
                            )
                        );
                    $txtDelete = '[delete]';
                } else {
                    $urlDelete = '';
                    $txtDelete = '';
                }


                $this->template->set_var(
                    array(
                        'questionID'               => $questionID,
                        'description'              => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEStaffAppraisalQuestion::description)
                        ),
                        'answerType'               => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEJStaffAppraisalQuestion::answerTypeName)
                        ),
                        'orderSequence'            => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEStaffAppraisalQuestion::orderSequence)
                        ),
                        'activeFlag'               => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEStaffAppraisalQuestion::activeFlag)
                        ),
                        'questionnaireDescription' => Controller::htmlDisplayText(
                            $dsQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::description)
                        ),
                        'urlEdit'                  => $urlEdit,
                        'urlDelete'                => $urlDelete,
                        'txtEdit'                  => $txtEdit,
                        'txtDelete'                => $txtDelete
                    )
                );

                $this->template->parse(
                    'rows',
                    'QuestionBlock',
                    true
                );

            }//while $dsQuestion->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'QuestionList',
            true
        );
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function editQuestion()
    {
        $this->setMethodName('editQuestion');
        $dsQuestion = &$this->dsQuestion; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'editQuestion') {
                $this->buQuestionnaire->getQuestionByID(
                    $_REQUEST['questionID'],
                    $dsQuestion
                );
                $questionID = $_REQUEST['questionID'];
            } else {                                  // creating new
                $dsQuestion->initialise();
                $dsQuestion->setValue(
                    DBEStaffAppraisalQuestion::id,
                    '0'
                );
                $dsQuestion->setValue(
                    'questionnaireID',
                    $_REQUEST['questionnaireID']
                );
                $questionID = '0';
            }
        } else {                                    // form validation error
            $dsQuestion->initialise();
            $dsQuestion->fetchNext();
            $questionID = $dsQuestion->getValue(DBEStaffAppraisalQuestion::id);
        }
        if (
            $_REQUEST['action'] == 'editQuestion' &&
            $this->buQuestionnaire->canDelete($_REQUEST['questionID'])
        ) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'deleteQuestion',
                        'questionID' => $questionID
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
                    'action'     => 'updateQuestion',
                    'questionID' => $questionID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => 'displayQuestionList',
                    'questionnaireID' => $dsQuestion->getValue('questionnaireID')
                )
            );
        $this->setPageTitle('Edit Question');
        $this->setTemplateFiles(
            array('QuestionEdit' => 'StaffAppraisalQuestionEdit.inc')
        );
        $this->template->set_var(
            array(
                'questionID'           => $questionID,
                'questionnaireID'      => $dsQuestion->getValue(DBEStaffAppraisalQuestion::questionnaireID),
                'orderSequence'        => Controller::htmlInputText(
                    $dsQuestion->getValue(DBEStaffAppraisalQuestion::orderSequence)
                ),
                'orderSequenceMessage' => Controller::htmlDisplayText(
                    $dsQuestion->getMessage(DBEStaffAppraisalQuestion::orderSequence)
                ),
                'description'          => Controller::htmlInputText(
                    $dsQuestion->getValue(DBEStaffAppraisalQuestion::description)
                ),
                'descriptionMessage'   => Controller::htmlDisplayText(
                    $dsQuestion->getMessage(DBEStaffAppraisalQuestion::description)
                ),
                'activeFlagChecked'    => $dsQuestion->getValue(
                    DBEStaffAppraisalQuestion::activeFlag
                ) == 'Y' ? 'CHECKED' : '',
                'activeFlagMessage'    => Controller::htmlDisplayText(
                    $dsQuestion->getMessage(DBEStaffAppraisalQuestion::activeFlag)
                ),
                'requiredFlagChecked'  => $dsQuestion->getValue(
                    DBEStaffAppraisalQuestion::requiredFlag
                ) == 'Y' ? 'CHECKED' : '',
                'requiredFlagMessage'  => Controller::htmlDisplayText(
                    $dsQuestion->getMessage(DBEStaffAppraisalQuestion::requiredFlag)
                ),
                'urlUpdate'            => $urlUpdate,
                'urlDelete'            => $urlDelete,
                'txtDelete'            => $txtDelete,
                'urlDisplayList'       => $urlDisplayList
            )
        );
        /*
        Answer types
        */
        $this->answerTypeDropdown($dsQuestion->getValue(DBEStaffAppraisalQuestion::answerTypeID));

        $this->template->parse(
            'CONTENTS',
            'QuestionEdit',
            true
        );
        $this->parsePage();
    }// end function editQuestion Action()

    function answerTypeDropdown(
        $answerTypeID,
        $templateName = 'QuestionEdit',
        $blockName = 'answerTypeBlock'
    )
    {
        // Display list of answerTypes that are current at given activity date
        $this->buQuestionnaire->dbeAnswerType->getRows();

        $this->template->set_block(
            $templateName,
            $blockName,
            'answerTypes'
        );

        while ($this->buQuestionnaire->dbeAnswerType->fetchNext()) {

            $answerTypeSelected = ($this->buQuestionnaire->dbeAnswerType->getValue(
                    'answerTypeID'
                ) == $answerTypeID) ? CT_SELECTED : '';

            $shouldBeConfig = (int)$this->buQuestionnaire->dbeAnswerType->getValue(
                DBEAnswerType::needsOptions
            );

            $isConfigured = $this->buQuestionnaire->dbeAnswerType->getValue(DBEAnswerType::answerOptions);

            $configFailed = $shouldBeConfig && !$isConfigured;

            var_dump($shouldBeConfig, $isConfigured, $configFailed);

            $this->template->set_var(
                array(
                    'answerTypeID'          => $this->buQuestionnaire->dbeAnswerType->getValue(
                        DBEAnswerType::answerTypeID
                    ),
                    'answerTypeDescription' => $this->buQuestionnaire->dbeAnswerType->getValue(
                            DBEAnswerType::description
                        ) . ($configFailed ? ' (Needs configuration)' : '')
                    ,
                    'answerTypeSelected'    => $answerTypeSelected,
                    'disabled'              => $configFailed ? 'disabled' : ''
                )
            );

            $this->template->parse(
                'answerTypes',
                $blockName,
                true
            );


        }

    }// end questionTypeDropdown

    /**
     * Update details
     * @access private
     */
    function updateQuestion()
    {
        $this->setMethodName('updateQuestion');
        $dsQuestion = &$this->dsQuestion;
        var_dump($_REQUEST['question']);
        $this->formError = (!$this->dsQuestion->populateFromArray($_REQUEST['question']));
        if ($this->formError) {
            if ($this->dsQuestion->getValue('questionID') == '') {          // attempt to insert
                $_REQUEST['action'] = 'editQuestion';
            } else {
                $_REQUEST['action'] = 'createQuestion';
            }
            $this->edit();
            exit;
        }

        $this->buQuestionnaire->updateQuestion($this->dsQuestion);

        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'questionnaireID' => $this->dsQuestion->getValue('questionnaireID'),
                    'action'          => 'displayQuestionList'
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Question
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function deleteQuestion()
    {
        $this->setMethodName('deleteQuestion');
        if (!$this->buQuestionnaire->deleteQuestion($_REQUEST['questionID'])) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'displayQuestionList'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>
