<?php
/**
 * Questionnaire controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Signable\ApiClient;
use Signable\DocumentWithoutTemplate;
use Signable\Envelopes;
use Signable\Party;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUStaffAppraisalQuestionnaire.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEStaffAppraisalObjectives.php');
require_once($cfg['path_dbe'] . '/DBEStaffAppraisalQuestion.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once(PDF_DIR . '/fpdf_protection.php');

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
        switch ($_REQUEST['action']) {
            case 'generatePDF':
                $filename = $this->getPDFQuestionnaire(
                    $_REQUEST['questionnaireAnswerID'],
                    'grass-fridge-mouse-boat'
                );
                header('Pragma: public');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename=invoices.pdf;');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($filename));
                readfile($filename);
                break;
            case 'employeeAnswer':
                $this->showEmployeeAnswer($_REQUEST['questionnaireID']);
                break;
            case 'managerAnswer':
                $this->showManagerAnswer(
                    $_REQUEST['questionnaireID'],
                    $_REQUEST['staffID']
                );
                break;
            case 'managerQuestionnaireList':
                $this->showManagerQuestionnaireList();
                break;
            case 'sendQuestionnaire':
                $this->sendQuestionnaire($_REQUEST['questionnaireID']);
                break;
            case 'getQuestionnaireManagerData':
                $response = ["status" => "ok"];
                try {
                    $response['data'] = $this->getQuestionnaireManagerData(
                        @$_REQUEST['type'],
                        @$_REQUEST['questionnaireID']
                    );
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
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
            case 'autoSave':
                echo $this->saveQuestionnaire();
                break;
            case 'completeQuestionnaire':
                $this->completeQuestionnaire();
                break;
            case 'decrypt':
                $response = ["status" => "ok"];
                try {
                    $response['decryptedData'] = \CNCLTD\Encryption::decrypt(
                        USER_ENCRYPTION_PRIVATE_KEY,
                        @$_REQUEST['passphrase'],
                        @$_REQUEST['encryptedData']
                    );
                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
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

        if (!self::isAppraiser()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMethodName('displayList');
        $this->setPageTitle('Questionnaires');
        $this->setTemplateFiles(
            array('QuestionnaireList' => 'StaffAppraisalQuestionnaireList.inc')
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
                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'          => 'edit',
                            'questionnaireID' => $questionnaireID
                        )
                    );
                $txtEdit = '[edit]';

                $sendURL = $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    [
                        'action'          => 'sendQuestionnaire',
                        'questionnaireID' => $questionnaireID
                    ]
                );
                $sendLink = "<a href='$sendURL'>[Send To Staff members]</a>";
                $dateSent = $dsQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::dateSent);
                if ($dateSent && $dateSent != '0000-00-00 00:00:00') {
                    $sendLink = "";
                }

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
                            $dsQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::description)
                        ),
                        'urlEdit'                => $urlEdit,
                        'urlDisplayQuestionList' => $urlDisplayQuestionList,
                        'urlDelete'              => $urlDelete,
                        'txtEdit'                => $txtEdit,
                        'txtDelete'              => $txtDelete,
                        'urlView'                => $urlView,
                        'sendLink'               => $sendLink
                    )
                );

                $this->template->parse(
                    'rows',
                    'QuestionnaireBlock',
                    true
                );

            }//while $dsQuestionnaire->fetchNext()

        }

        $sendURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'sendQuestionnaire',
            ]
        );

        $this->template->setVar(
            ["sendQuestionnaireURL" => $sendURL]
        );

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
        $this->formError = (!$this->dsQuestionnaire->populateFromArray($_REQUEST['questionnaire']));
        if ($this->formError) {
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
                        'txtDelete'                => $txtDelete,

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

    private function showManagerAnswer($questionnaireID,
                                       $staffID
    )
    {
        if (!$questionnaireID) {
            $this->displayFatalError('Questionnaire ID is missing');
            exit;
        }
        $dbeQuestionnaire = new DBEStaffAppraisalQuestionnaire($this);

        $dbeQuestionnaire->getRow($questionnaireID);

        if (!$dbeQuestionnaire->rowCount()) {
            $this->displayFatalError('The questionnaire does not exist');
            exit;
        }

        if (!$staffID) {
            $this->displayFatalError('Staff ID is missing');
            exit;
        }

        // we first need to know if there's a questionnaire answer for this questionnaire ID and user
        $dbeQuestionnaireAnswer = new DBEStaffAppraisalQuestionnaireAnswer($this);

        $dbeQuestionnaireAnswer->getRowByQuestionnaireAndStaff(
            $questionnaireID,
            $staffID
        );

        if (!$dbeQuestionnaireAnswer->rowCount()) {
            $this->displayFatalError('The staff member has not answered yet');
            exit;
        }

        if ($dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerID) != $this->userID) {
            $this->displayFatalError('You are not the manager of the questionnaire/staff member');
            exit;
        }

        $staffMember = new DBEUser($this);
        $staffMember->getRow($staffID);


        //here we should have a valid and populated questionnaire answer :D
        $this->setTemplateFiles(
            array('StaffAppraisalEmployeeView' => 'StaffAppraisalEmployeeView')
        );
        $questionnaireAnswerID = $dbeQuestionnaireAnswer->getPKValue();
        $managerID = $dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerID);

        $dbeManager = new DBEUser($this);
        $dbeManager->getRow($managerID);

        $this->template->setVar(
            [
                "employeeName"             => $staffMember->getValue(
                        DBEUser::firstName
                    ) . ' ' . $staffMember->getValue(
                        DBEUser::lastName
                    ),
                "managerName"              => $dbeManager->getValue(DBEUser::firstName) . ' ' . $dbeManager->getValue(
                        DBEUser::lastName
                    ),
                "employeeStartDate"        => Controller::dateYMDtoDMY(
                    $staffMember->getValue(DBEUser::startDate),
                    '-'
                ),
                "employeePosition"         => $staffMember->getValue(DBEUser::jobTitle),
                "sickDaysThisYear"         => $dbeQuestionnaireAnswer->getValue(
                    DBEStaffAppraisalQuestionnaireAnswer::sickDaysThisYear
                ),
                "completePerson"           => "manager",
                "proposedSalary"           => $dbeQuestionnaireAnswer->getValue(
                    DBEStaffAppraisalQuestionnaireAnswer::proposedSalary
                ),
                "proposedBonus"            => $dbeQuestionnaireAnswer->getValue(
                    DBEStaffAppraisalQuestionnaireAnswer::proposedBonus
                ),
                "teamLeaderComments"       => $dbeQuestionnaireAnswer->getValue(
                    DBEStaffAppraisalQuestionnaireAnswer::teamLeaderComments
                ),
                "managerComments"          => $dbeQuestionnaireAnswer->getValue(
                    DBEStaffAppraisalQuestionnaireAnswer::managerComments
                ),
                "encryptedSalary"          => $staffMember->getValue(DBEUser::encryptedSalary),
                "completeQuestionnaireURL" => $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'completeQuestionnaire'
                    )
                ),

            ]
        );

        $dbeQuestions = new DBEStaffAppraisalQuestion($this);

        $dbeQuestions->getRowsForQuestionnaire($questionnaireID);


        $previousQuestionType = null;
        $questionsBody = "";
        while ($dbeQuestions->fetchNext()) {
            $currentQuestionType = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID);

            if ($previousQuestionType != $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID)) {
                // depending on what was the previous question we need to finish it
                $questionsBody .= $this->getQuestionFinish(
                    $previousQuestionType,
                    true
                );

                // now we look at the current question type..to see if we need a header or not
                $questionsBody .= $this->getQuestionHeader(
                    $currentQuestionType,
                    true
                );
            }

            // now we render the actual question
            $questionsBody .= $this->renderQuestion(
                $dbeQuestions,
                $questionnaireAnswerID,
                true
            );

            $previousQuestionType = $currentQuestionType;
        }

        //we have to finish off the last question
        $questionsBody .= $this->getQuestionFinish(
            $previousQuestionType,
            true
        );

        $this->template->setVar(
            [
                "questions"                => $questionsBody,
                "autoSaveQuestionnaireURL" => $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'autoSave'
                    )
                ),
                "questionnaireAnswerID"    => $questionnaireAnswerID
            ]
        );


        // render objectives

        $this->template->set_block(
            "StaffAppraisalEmployeeView",
            "objectivesBlock",
            'objectives'
        );

        $dbeObjective = new DBEStaffAppraisalObjectives($this);

        $dbeObjective->getRowsByAnswerID($questionnaireAnswerID);

        while ($dbeObjective->fetchNext()) {
            $this->template->set_var(
                array(
                    "id"        => $dbeObjective->getValue(DBEStaffAppraisalObjectives::id),
                    "number"    => $dbeObjective->getValue(DBEStaffAppraisalObjectives::id) + 1,
                    "objective" => $dbeObjective->getValue(DBEStaffAppraisalObjectives::requirement),
                    "measure"   => $dbeObjective->getValue(DBEStaffAppraisalObjectives::measure),
                    "comment"   => $dbeObjective->getValue(DBEStaffAppraisalObjectives::comment)
                )
            );
            $this->template->parse(
                'objectives',
                "objectivesBlock",
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'StaffAppraisalEmployeeView',
            true
        );
        $this->parsePage();
    }

    private function showEmployeeAnswer($questionnaireID)
    {

        if (!$questionnaireID) {
            $this->displayFatalError('Questionnaire ID is missing');
            exit;
        }

        //check if the questionnaire exists...
        $dbeQuestionnaire = new DBEStaffAppraisalQuestionnaire($this);

        $dbeQuestionnaire->getRow($questionnaireID);

        if (!$dbeQuestionnaire->rowCount()) {
            $this->displayFatalError('The questionnaire does not exist');
            exit;
        }

        $this->setPageTitle($dbeQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::description));

        // we first need to know if there's a questionnaire answer for this questionnaire ID and user
        $dbeQuestionnaireAnswer = new DBEStaffAppraisalQuestionnaireAnswer($this);
        $staffID = $this->userID;
        $dbeQuestionnaireAnswer->getRowByQuestionnaireAndStaff(
            $questionnaireID,
            $staffID
        );
        $managerID = $this->dbeUser->getValue(DBEUser::managerID);
        if (!$managerID) {
            $this->displayFatalError('The logged in user does not have a valid manager assigned');
            exit;
        }

        if (!$dbeQuestionnaireAnswer->rowCount()) {
            // we need to create it as there's none
            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::staffMemberID,
                $staffID
            );
            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::questionnaireID,
                $questionnaireID
            );

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::managerID,
                $managerID
            );

            $dbeQuestionnaireAnswer->insertRow();
            $questionnaireAnswerID = $dbeQuestionnaireAnswer->getPKValue();

            // we have to create the 4 Objectives

            $dbeObjective = new DBEStaffAppraisalObjectives($this);

            for ($i = 1; $i < 5; $i++) {

                $dbeObjective->setValue(
                    DBEStaffAppraisalObjectives::id,
                    $i
                );
                $dbeObjective->setValue(
                    DBEStaffAppraisalObjectives::questionnaireAnswerID,
                    $questionnaireAnswerID
                );
                $dbeObjective->insertRow();
            }
        }


        //here we should have a valid and populated questionnaire answer :D
        $this->setTemplateFiles(
            array('StaffAppraisalEmployeeView' => 'StaffAppraisalEmployeeView')
        );
        $questionnaireAnswerID = $dbeQuestionnaireAnswer->getPKValue();
        $managerID = $dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerID);

        $dbeManager = new DBEUser($this);
        $dbeManager->getRow($managerID);

        $this->template->setVar(
            [
                "employeeName"             => $this->dbeUser->getValue(
                        DBEUser::firstName
                    ) . ' ' . $this->dbeUser->getValue(
                        DBEUser::lastName
                    ),
                "managerName"              => $dbeManager->getValue(DBEUser::firstName) . ' ' . $dbeManager->getValue(
                        DBEUser::lastName
                    ),
                "employeeStartDate"        => $this->dbeUser->getValue(DBEUser::startDate),
                "employeePosition"         => $this->dbeUser->getValue(
                    DBEUser::jobTitle
                ),
                "displayManager"           => "style='display: none'",
                "completeQuestionnaireURL" => $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'completeQuestionnaire'
                    )
                ),
                "completePerson"           => "staffMember"
            ]
        );

        $dbeQuestions = new DBEStaffAppraisalQuestion($this);

        $dbeQuestions->getRowsForQuestionnaire($questionnaireID);


        $previousQuestionType = null;
        $questionsBody = "";
        while ($dbeQuestions->fetchNext()) {
            $currentQuestionType = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID);

            if ($previousQuestionType != $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID)) {
                // depending on what was the previous question we need to finish it
                $questionsBody .= $this->getQuestionFinish($previousQuestionType);

                // now we look at the current question type..to see if we need a header or not
                $questionsBody .= $this->getQuestionHeader($currentQuestionType);
            }

            // now we render the actual question
            $questionsBody .= $this->renderQuestion(
                $dbeQuestions,
                $questionnaireAnswerID
            );

            $previousQuestionType = $currentQuestionType;
        }

        //we have to finish off the last question
        $questionsBody .= $this->getQuestionFinish($previousQuestionType);

        $this->template->setVar(
            [
                "questions"                => $questionsBody,
                "autoSaveQuestionnaireURL" => $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'autoSave'
                    )
                ),
                "questionnaireAnswerID"    => $questionnaireAnswerID
            ]
        );


        // render objectives

        $this->template->set_block(
            "StaffAppraisalEmployeeView",
            "objectivesBlock",
            'objectives'
        );

        $dbeObjective = new DBEStaffAppraisalObjectives($this);

        $dbeObjective->getRowsByAnswerID($questionnaireAnswerID);

        while ($dbeObjective->fetchNext()) {
            $this->template->set_var(
                array(
                    "id"        => $dbeObjective->getValue(DBEStaffAppraisalObjectives::id),
                    "number"    => $dbeObjective->getValue(DBEStaffAppraisalObjectives::id) + 1,
                    "objective" => $dbeObjective->getValue(DBEStaffAppraisalObjectives::requirement),
                    "measure"   => $dbeObjective->getValue(DBEStaffAppraisalObjectives::measure),
                    "comment"   => $dbeObjective->getValue(DBEStaffAppraisalObjectives::comment)
                )
            );
            $this->template->parse(
                'objectives',
                "objectivesBlock",
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'StaffAppraisalEmployeeView',
            true
        );
        $this->parsePage();

    }

    private function getQuestionFinish($questionType,
                                       $isManager = false
    )
    {
        $questionFinish = "";
        switch ($questionType) {
            case 2: // yes/no
            case 5: // 1 to 7
            case 1: // 1 to 5
                // we would need to close the table
                $questionFinish = '</tbody></table>';
                break;
            case 3:
            case 4:
                $questionFinish = "<br>";
                if ($isManager) {
                    $questionFinish = '</tbody></table>';
                }
                break;
        }

        // we would need to put a break
        $questionFinish .= "<br>";
        return $questionFinish;
    }

    private function getQuestionHeader($questionType,
                                       $isManager = false
    )
    {
        $header = "";
        switch ($questionType) {
            case 3:
            case 4:
                if ($isManager) {
                    $header = "<table>
                                <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Staff Answer</th>
                                    <th>Manager Answer</th>
                                </tr>
                                </thead>
                                <tbody>";
                }
                break;
            case 2 : // yes/no answertype
                $header = "<table>
                            <thead>
                                <tr>
                                    <th></th>
                                    " . ($isManager ? "<th>Staff Answer</th>" : '') . "
                                    <th>N/A</th>
                                    <th>Yes</th>
                                    <th>No</th>
                                    " . ($isManager ? "<th>Manager Comment</th>" : '') . "
                                    </tr>
                                </thead>
                            <tbody>";
                break;
            case 5:
                // for this we need to pull the config
                $dbeQuestionType = new DBEAnswerType($this);
                $dbeQuestionType->getRow($questionType);

                $answerOptionsString = $dbeQuestionType->getValue(DBEAnswerType::answerOptions);

                $answerOptions = json_decode($answerOptionsString);

                $header = "<table class='1To7Question'>
                                <thead>
                                    <tr>
                                    <td></td>" . ($isManager ? "<th>Staff Answer</th>" : '');

                foreach ($answerOptions as $key => $option) {
                    $header .= "<th>$option</th>";
                }

                if ($isManager) {
                    $header .= "<th>Manager Comment</th>";
                }

                $header .= "        </tr>
                                </thead>
                                <tbody>";
                break;
            case 1:

                $header = "<table class='1To7Question'>
                                <thead>
                                    <tr>
                                        <th></th> " .
                    ($isManager ? "<th>Staff Answer</th>" : '') .
                    "<th>N/A</th>
                                        <th>Below Expectations</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th>Above Expectations</th>" .
                    ($isManager ? "<th>Manager Comment</th>" : '') .
                    "</tr>
                                </thead>
                                <tbody>";
                break;
        }
        return $header;
    }

    private function renderQuestion(DBEStaffAppraisalQuestion $dbeQuestions,
                                    $questionnaireAnswerID,
                                    $isManager = false
    )
    {
        // we now look at the current question and render it as we should
        $questionDescription = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::description);
        $questionID = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::id);
        $questionType = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID);
        $question = "";

        $dbeQuestionAnswer = new DBEStaffAppraisalQuestionAnswer($this);
        $dbeQuestionAnswer->getRowByIDAndQuestionnaireAnswerID(
            $dbeQuestions->getValue(DBEStaffAppraisalQuestion::id),
            $questionnaireAnswerID
        );

        $isRequired = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::requiredFlag) == 'Y';

        switch ($questionType) {
            case 2: //
                $possibleResponses = ['N/A', 'Yes', 'No'];
                $question = $this->renderMultipleChoiceQuestion(
                    $questionDescription,
                    $possibleResponses,
                    $dbeQuestionAnswer,
                    $questionID,
                    $isRequired,
                    $isManager
                );
                break;
            case 5:
                $possibleResponses = [0, 1, 2, 3, 4, 5, 6, 7];
                $dbeQuestionType = new DBEAnswerType($this);
                $dbeQuestionType->getRow($questionType);

                $answerOptionsString = $dbeQuestionType->getValue(DBEAnswerType::answerOptions);

                $answerOptions = json_decode($answerOptionsString);
                $question = $this->renderMultipleChoiceQuestion(
                    $questionDescription,
                    $possibleResponses,
                    $dbeQuestionAnswer,
                    $questionID,
                    $isRequired,
                    $isManager,
                    $answerOptions
                );
                break;
            case 1:
                $possibleResponses = [0, 1, 2, 3, 4, 5];

                $question = $this->renderMultipleChoiceQuestion(
                    $questionDescription,
                    $possibleResponses,
                    $dbeQuestionAnswer,
                    $questionID,
                    $isRequired,
                    $isManager
                );
                break;
            case 3:
            case 4:
                $value = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::staffAnswer);
                $required = $isRequired ? "required='required'" : '';
                $managerAnswer = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::managerAnswer);
                $question = "<p>$questionDescription " . ($isRequired ? '<span class="requiredStar">*</span>' : '') .
                    "</p><br><textarea rows='5' name='question[$questionID][staffMemberAnswer]'>$value</textarea><br><br>";
                if ($isManager) {
                    $question = "
                    <tr>
                        <td width='20%'>$questionDescription</td>
                        <td width='40%'>$value</td>
                        <td width='40%'><textarea rows='10' name='question[$questionID][managerAnswer]' $required>$managerAnswer</textarea></td>
                    </tr>
                    ";
                }

                break;
        }


        return $question;
    }

    private function renderMultipleChoiceQuestion($questionDescription,
                                                  $possibleResponses,
                                                  $dbeQuestionAnswer,
                                                  $questionID,
                                                  $isRequired,
                                                  $isManager = false,
                                                  $answerOptions = null
    )
    {
        $question = "<tr><td width='15%'>$questionDescription " . ($isRequired ? '<span class="requiredStar">*</span>' : '') . "</td>";
        $required = $isRequired ? "required='required'" : '';

        $whoAnswers = "staffMemberAnswer";

        if ($isManager) {
            $staffAnswer = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::staffAnswer);
            if ($answerOptions) {
                $staffAnswer = $answerOptions[$staffAnswer];
            }

            $question .= "<td>" . $staffAnswer . "</td>";
            $whoAnswers = "managerAnswer";
        }


        foreach ($possibleResponses as $possibleResponse) {
            $checked = "";

            $valueKey = DBEStaffAppraisalQuestionAnswer::staffAnswer;

            if ($isManager) {
                $valueKey = DBEStaffAppraisalQuestionAnswer::managerAnswer;
            }
            if ($dbeQuestionAnswer->getValue($valueKey) == $possibleResponse) {
                $checked = "checked='checked'";
            }

            $question .=
                "<td>
                  <input type='radio' name='question[$questionID][$whoAnswers]' $checked value='$possibleResponse' $required>
                </td>";
        }

        if ($isManager) {
            $managerComment = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::managerComment);
            $question .= "<td width='20%'><textarea name='question[$questionID][managerComment]'  rows='5'>$managerComment</textarea></td>";
        }

        $question .= "</tr>";
        return $question;
    }

    private function saveQuestionnaire()
    {
        // first we need to pull the questionnaireAnswerID

        $questionnaireAnswerID = @$_REQUEST['questionnaireAnswerID'];

        if (!$questionnaireAnswerID) {
            throw new Exception('Questionnaire Answer ID is missing!');
        }

        $dbeQuestionnaireAnswer = new DBEStaffAppraisalQuestionnaireAnswer($this);
        $dbeQuestionnaireAnswer->getRow($questionnaireAnswerID);

        // only the assigned user or manager can make changes to this

        $currentUserID = $this->userID;

        $isStaffMember = $dbeQuestionnaireAnswer->getValue(
                DBEStaffAppraisalQuestionnaireAnswer::staffMemberID
            ) == $currentUserID && isset($_REQUEST['completeFor']) && $_REQUEST['completeFor'] == 'staffMember';
        $isManager = $dbeQuestionnaireAnswer->getValue(
                DBEStaffAppraisalQuestionnaireAnswer::managerID
            ) == $currentUserID && isset($_REQUEST['completeFor']) && $_REQUEST['completeFor'] == 'manager';

        if (!($isStaffMember || $isManager)) {
            throw new Exception(
                'Not authorised, the current user is not the assigned staff member or manager of this questionnaire answer'
            );
        }

        if ($isManager) {
            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::sickDaysThisYear,
                $_REQUEST['sickDaysThisYear']
            );

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::proposedSalary,
                $_REQUEST['proposedSalary']
            );

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::proposedBonus,
                $_REQUEST['proposedBonus']
            );

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::teamLeaderComments,
                $_REQUEST['teamLeaderComments']
            );

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::managerComments,
                $_REQUEST['managerComments']
            );
        }

        $dbeQuestionnaireAnswer->updateRow();

        $dbeQuestions = new DBEStaffAppraisalQuestion($this);

        $dbeQuestions->getRowsForQuestionnaire(
            $dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::questionnaireID)
        );

        $questionAnswers = $_REQUEST['question'];

        while ($dbeQuestions->fetchNext()) {

            // check if we do have an answer for this in the form
            $questionID = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::id);

            if (isset($questionAnswers[$questionID])) {
                $currentAnswer = $questionAnswers[$questionID];
                // we need to see if we already have an answer for this question
                $dbeQuestionAnswer = new DBEStaffAppraisalQuestionAnswer($this);
                $dbeQuestionAnswer->getRowByIDAndQuestionnaireAnswerID(
                    $questionID,
                    $questionnaireAnswerID
                );

                if (!$dbeQuestionAnswer->rowCount()) {
                    //we don't have any answers ..so we need to create one
                    $dbeQuestionAnswer->setValue(
                        DBEStaffAppraisalQuestionAnswer::questionnaireAnswerID,
                        $questionnaireAnswerID
                    );
                    $dbeQuestionAnswer->setValue(
                        DBEStaffAppraisalQuestionAnswer::questionID,
                        $questionID
                    );
                    $dbeQuestionAnswer->insertRow();
                }

                if (isset($currentAnswer['staffMemberAnswer'])) {
                    $dbeQuestionAnswer->setValue(
                        DBEStaffAppraisalQuestionAnswer::staffAnswer,
                        @$currentAnswer['staffMemberAnswer']
                    );
                }

                //we only update the manager answers if the user is the assigned manager

                if ($isManager) {

                    if (isset($currentAnswer['managerAnswer'])) {
                        $dbeQuestionAnswer->setValue(
                            DBEStaffAppraisalQuestionAnswer::managerAnswer,
                            @$currentAnswer['managerAnswer']
                        );
                    }

                    if (isset($currentAnswer['managerComment'])) {
                        $dbeQuestionAnswer->setValue(
                            DBEStaffAppraisalQuestionAnswer::managerComment,
                            @$currentAnswer['managerComment']
                        );
                    }
                }


                $dbeQuestionAnswer->updateRow();
            }
        }

        //we now look at objectives

        $dbeObjective = new DBEStaffAppraisalObjectives($this);
        $dbeObjective->getRowsByAnswerID($questionnaireAnswerID);
        $objectives = $_REQUEST['objective'];

        while ($dbeObjective->fetchNext()) {
            $objectiveID = $dbeObjective->getValue(DBEStaffAppraisalObjectives::id);

            $currentObjective = $objectives[$objectiveID];

            $updateObjective = new DBEStaffAppraisalObjectives($this);
            $updateObjective->setValue(
                DBEStaffAppraisalObjectives::questionnaireAnswerID,
                $questionnaireAnswerID
            );
            $updateObjective->getRow($objectiveID);
            $updateObjective->setValue(
                DBEStaffAppraisalObjectives::comment,
                $currentObjective['comment']
            );
            $updateObjective->setValue(
                DBEStaffAppraisalObjectives::measure,
                $currentObjective['measure']
            );
            $updateObjective->setValue(
                DBEStaffAppraisalObjectives::requirement,
                $currentObjective['objective']
            );
            $updateObjective->updateRow();

        }
        return json_encode(['status' => 'ok']);
    }

    private function completeQuestionnaire()
    {
        // first we need to pull the questionnaireAnswerID

        $questionnaireAnswerID = @$_REQUEST['questionnaireAnswerID'];

        if (!$questionnaireAnswerID) {
            throw new Exception('Questionnaire Answer ID is missing!');
        }

        $dbeQuestionnaireAnswer = new DBEStaffAppraisalQuestionnaireAnswer($this);
        $dbeQuestionnaireAnswer->getRow($questionnaireAnswerID);

        // only the assigned user or manager can make changes to this

        $currentUserID = $this->userID;

        $isStaffMember = $dbeQuestionnaireAnswer->getValue(
                DBEStaffAppraisalQuestionnaireAnswer::staffMemberID
            ) == $currentUserID && isset($_REQUEST['completeFor']) && $_REQUEST['completeFor'] == 'staffMember';
        $isManager = $dbeQuestionnaireAnswer->getValue(
                DBEStaffAppraisalQuestionnaireAnswer::managerID
            ) == $currentUserID && isset($_REQUEST['completeFor']) && $_REQUEST['completeFor'] == 'manager';

        $this->saveQuestionnaire();
        if ($isStaffMember) {
            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::staffCompleted,
                1
            );

            $this->sendStaffCompleteManagerEmail($dbeQuestionnaireAnswer);
        }


        if ($isManager) {

            $passPhrase = $_REQUEST['passPhrase'];

            if (!$passPhrase) {
                throw new Exception('Passphrase is needed for decrypting data');
            }

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::managerCompleted,
                1
            );


            // we have to generate the PDF, and send it to signable
            $this->sendToSignable(
                $dbeQuestionnaireAnswer,
                $passPhrase
            );
        }

        $dbeQuestionnaireAnswer->updateRow();
        echo '<script>alert("Completed Successfully")</script>';
        Header("Location: /");
    }


    private function getPDFQuestionnaire($questionnaireAnswerID,
                                         $passPhrase
    )
    {

        $questionnaireAnswer = new DBEStaffAppraisalQuestionnaireAnswer($this);
        $questionnaireAnswer->getRow($questionnaireAnswerID);


        $mainPDF = new CNCLTD\StaffAppraisalPDF(
            $questionnaireAnswer,
            $passPhrase
        );

        $fileName = PDF_TEMP_DIR . '/test.pdf';
        $mainPDF->Output(
            'F',
            $fileName,
            true
        );

        return $fileName;
    }

    private function showManagerQuestionnaireList()
    {
        if (!self::isAppraiser()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMethodName('displayQuestions');
        $this->setTemplateFiles(
            array('StaffAppraisalManagerQuestionnaireList' => 'StaffAppraisalManagerQuestionnaireList.inc')
        );

        $stats = $this->buQuestionnaire->getStats($this->userID);


        if (count($stats)) {

            $this->template->set_block(
                'StaffAppraisalManagerQuestionnaireList',
                'QuestionnaireBlock',
                'rows'
            );

            foreach ($stats as $stat) {

                $questionnaireID = $stat['id'];

                $this->template->set_var(
                    array(
                        'questionnaireID' => $questionnaireID,
                        'description'     => $stat['description'],
                        'staffPending'    => $stat['staffPending'],
                        'managerPending'  => $stat['managerPending'],
                        'completed'       => $stat['completed'],
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
            'StaffAppraisalManagerQuestionnaireList',
            true
        );
        $this->parsePage();
    }

    private function sendQuestionnaire($questionnaireID)
    {
        if (!$questionnaireID) {
            throw new Exception('QuestionnaireID is missing');
        }

        $dbeQuestionnaire = new DBEStaffAppraisalQuestionnaire($this);

        $dbeQuestionnaire->getRow($questionnaireID);

        if (!$dbeQuestionnaire->rowCount()) {
            throw new Exception('The questionnaire does not exist');
        }

        $dateSent = $dbeQuestionnaire->getValue(DBEStaffAppraisalQuestionnaire::dateSent);


        if ($dateSent && $dateSent != "0000-00-00 00:00:00") {
            throw new Exception('This questionnaire has already been sent');
        }

        // we need to pull the list of employees to which we are going to send this
        $dbeUser = new DBEUser($this);
        $dbeUser->getAppraisalUsers();

        $buMail = new BUMail($this);
        while ($dbeUser->fetchNext()) {

            // we have to create the answer for the user, and send the link through email
            $dbeQuestionnaireAnswer = new DBEStaffAppraisalQuestionnaireAnswer($this);
            $staffID = $dbeUser->getValue(DBEUser::userID);
            $managerID = $dbeUser->getValue(DBEUser::managerID);
            // we need to create it as there's none
            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::staffMemberID,
                $staffID
            );
            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::questionnaireID,
                $questionnaireID
            );

            $dbeQuestionnaireAnswer->setValue(
                DBEStaffAppraisalQuestionnaireAnswer::managerID,
                $managerID
            );

            $dbeQuestionnaireAnswer->insertRow();
            $questionnaireAnswerID = $dbeQuestionnaireAnswer->getPKValue();

            // we have to create the 4 Objectives

            $dbeObjective = new DBEStaffAppraisalObjectives($this);

            for ($i = 1; $i < 5; $i++) {

                $dbeObjective->setValue(
                    DBEStaffAppraisalObjectives::id,
                    $i
                );
                $dbeObjective->setValue(
                    DBEStaffAppraisalObjectives::questionnaireAnswerID,
                    $questionnaireAnswerID
                );
                $dbeObjective->insertRow();
            }
            $template = new Template (
                EMAIL_TEMPLATE_DIR,
                "remove"
            );

            $template->setFile(
                'StaffAppraisalLinkEmail',
                'StaffAppraisalLinkEmail.html'
            );

            $subject = "Staff Appraisal";

            $appraisalURL = $this->buildLink(
                'http://cncapps/staffAppraisalQuestionnaire.php',
                [
                    "action"          => "employeeAnswer",
                    "questionnaireID" => $questionnaireID
                ]
            );

            $template->setVar(
                [
                    "staffName"        => $dbeUser->getValue(DBEUser::firstName),
                    "appraisalLinkURL" => $appraisalURL,
                ]
            );

            $template->parse(
                'OUTPUT',
                "StaffAppraisalLinkEmail"
            );

            $body = $template->getVar('OUTPUT');

            $emailTo = $dbeUser->getValue(DBEUser::username) . "@cnc-ltd.co.uk";

            $hdrs = array(
                'From'         => CONFIG_SUPPORT_EMAIL,
                'To'           => $emailTo,
                'Subject'      => $subject,
                'Date'         => date("r"),
                'Content-Type' => 'text/html; charset=UTF-8'
            );

            $mime = new Mail_mime();

            $mime->setHTMLBody($body);

            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset'  => 'UTF-8',
                'html_charset'  => 'UTF-8',
                'head_charset'  => 'UTF-8'
            );

            $body = $mime->get($mime_params);

            $hdrs = $mime->headers($hdrs);

            $buMail->putInQueue(
                CONFIG_SUPPORT_EMAIL,
                $emailTo,
                $hdrs,
                $body
            );
        }

        $dbeQuestionnaire->setValue(
            DBEStaffAppraisalQuestionnaire::dateSent,
            (new DateTime())->format(COMMON_MYSQL_DATETIME)
        );
        $dbeQuestionnaire->updateRow();
        return $this->showManagerQuestionnaireList();
    }

    private function getQuestionnaireManagerData($type,
                                                 $questionnaireID
    )
    {
        if (!isset($type)) {
            throw new Exception('Type is missing');
        }

        if (!isset($questionnaireID)) {
            throw new Exception('Questionnaire ID is missing');
        }

        return $this->buQuestionnaire->getManagerData(
            $this->userID,
            $type,
            $questionnaireID
        );
    }

    private function sendStaffCompleteManagerEmail(DBEStaffAppraisalQuestionnaireAnswer $dbeQuestionnaireAnswer)
    {
        $buMail = new BUMail($this);


        $staffMember = new DBEUser($this);

        $staffMember->getRow($dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::staffMemberID));

        $manager = new DBEUser($this);
        $manager->getRow($dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerID));


        $template = new Template (
            EMAIL_TEMPLATE_DIR,
            "remove"
        );

        $template->setFile(
            'StaffAppraisalStaffCompletedEmail',
            'StaffAppraisalStaffCompletedEmail.html'
        );

        $subject = "Appraisal completed by " . $staffMember->getValue(
                DBEUser::firstName
            ) . " " . $staffMember->getValue(DBEUser::lastName);

        $appraisalURL = $this->buildLink(
            'http://cncapps/staffAppraisalQuestionnaire.php',
            [
                "action"          => "managerAnswer",
                "questionnaireID" => $dbeQuestionnaireAnswer->getValue(
                    DBEStaffAppraisalQuestionnaireAnswer::questionnaireID
                ),
                "staffID"         => $staffMember->getValue(DBEUser::userID)
            ]
        );

        $template->setVar(
            [
                "appraisalLinkURL" => $appraisalURL,
            ]
        );

        $template->parse(
            'OUTPUT',
            "StaffAppraisalStaffCompletedEmail"
        );

        $body = $template->getVar('OUTPUT');

        $emailTo = $manager->getValue(DBEUser::username) . "@cnc-ltd.co.uk";

        $hdrs = array(
            'From'         => CONFIG_SUPPORT_EMAIL,
            'To'           => $emailTo,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $mime = new Mail_mime();

        $mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );

        $body = $mime->get($mime_params);

        $hdrs = $mime->headers($hdrs);

        $buMail->putInQueue(
            CONFIG_SUPPORT_EMAIL,
            $emailTo,
            $hdrs,
            $body
        );
    }

    private function sendToSignable(DBEStaffAppraisalQuestionnaireAnswer $dbeQuestionnaireAnswer,
                                    $passPhrase
    )
    {
        ApiClient::setApiKey("fc2d9ba05f3f3d9f2e9de4d831e8fed9");

        $envDocs = [];

        $fileName =
            $this->getPDFQuestionnaire(
                $dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::id),
                $passPhrase
            );

        $manager = new DBEUser($this);

        $manager->getRow($dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerID));

        $staffMember = new DBEUser($this);

        $staffMember->getRow($dbeQuestionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::staffMemberID));

        $firstName = $staffMember->getValue(DBEUser::firstName);
        $managerFirstName = $manager->getValue(DBEUser::firstName);
        $lastName = $staffMember->getValue(DBEUser::lastName);
        $managerLastName = $manager->getValue(DBEUser::lastName);
        $email = $staffMember->getValue(DBEUser::username) . '@cnc-ltd.co.uk';
        $managerEmail = $manager->getValue(DBEUser::username) . '@cnc-ltd.co.uk';

        $envelopeDocument = new DocumentWithoutTemplate(
            'Staff Appraisal',
            null,
            base64_encode(file_get_contents($fileName)),
            "StaffAppraisal.pdf"
        );

        $envDocs[] = $envelopeDocument;

        $envelopeParties = [];

        $envelopeParty = new Party(
            $firstName . ' ' . $lastName,
            $email,
            'signer1',
            'Please sign here',
            'no',
            false
        );

        $managerParty = new \Signable\Party(
            $managerFirstName . ' ' . $managerLastName,
            $managerEmail,
            'signer2',
            'Please sign here',
            'no',
            false
        );

        $envelopeParties[] = $envelopeParty;
        $envelopeParties[] = $managerParty;


        $response = Envelopes::createNewWithoutTemplate(
            "Document #Appraisal" . "_" . uniqid(),
            $envDocs,
            $envelopeParties,
            null,
            false,
            null,
            0,
            0
        );

        if ($response && $response->http == 202) {
            return true;
        }
        return false;
    }
}// end of class
?>
