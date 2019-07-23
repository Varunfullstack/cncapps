<?php
/**
 * Questionnaire controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUQuestionnaire.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions
class CTQuestionnaire extends CTCNC
{
    /** @var DSForm */
    public $dsQuestionnaire;
    /** @var BUQuestionnaire */
    public $buQuestionnaire;
    /** @var DSForm */
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
        $this->buQuestionnaire = new BUQuestionnaire($this);
        $this->dsQuestionnaire = new DSForm($this);
        $this->dsQuestionnaire->copyColumnsFrom($this->buQuestionnaire->dbeQuestionnaire);
        $this->dsQuestion = new DSForm($this);
        $this->dsQuestion->copyColumnsFrom($this->buQuestionnaire->dbeQuestion);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($this->getAction()) {
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
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Questionnaires');
        $this->setTemplateFiles(
            array('QuestionnaireList' => 'QuestionnaireList.inc')
        );
        $dsQuestionnaire = new DataSet($this);
        $this->buQuestionnaire->getAll($dsQuestionnaire);

        $urlCreate =
            Controller::buildLink(
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

                $questionnaireID = $dsQuestionnaire->getValue(DBEQuestionnaire::questionnaireID);

                $urlDisplayQuestionList =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'          => 'displayQuestionList',
                            'questionnaireID' => $questionnaireID
                        )
                    );
                $urlView =
                    Controller::buildLink(
                        'https://cnc-ltd.co.uk/questionnaire/index.php',
                        array(
                            'questionnaireno' => $questionnaireID
                        )
                    );

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'          => 'edit',
                            'questionnaireID' => $questionnaireID
                        )
                    );
                $txtEdit = '[edit]';

                $this->template->set_var(
                    array(
                        'questionnaireID'        => $questionnaireID,
                        'description'            => Controller::htmlDisplayText(
                            $dsQuestionnaire->getValue(DBEQuestionnaire::description)
                        ),
                        'urlEdit'                => $urlEdit,
                        'urlDisplayQuestionList' => $urlDisplayQuestionList,
                        'txtEdit'                => $txtEdit,
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
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsQuestionnaire = &$this->dsQuestionnaire; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == 'edit') {
                $this->buQuestionnaire->getQuestionnaireByID(
                    $this->getParam('questionnaireID'),
                    $dsQuestionnaire
                );
                $questionnaireID = $this->getParam('questionnaireID');
            } else {                                                                    // creating new
                $dsQuestionnaire->initialise();
                $dsQuestionnaire->setValue(
                    DBEQuestionnaire::questionnaireID,
                    '0'
                );
                $questionnaireID = '0';
            }
        } else {                                                                        // form validation error
            $dsQuestionnaire->initialise();
            $dsQuestionnaire->fetchNext();
            $questionnaireID = $dsQuestionnaire->getValue(DBEQuestionnaire::questionnaireID);
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => 'update',
                    'questionnaireID' => $questionnaireID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'displayList'
                )
            );
        $this->setPageTitle('Edit Questionnaire');
        $this->setTemplateFiles(
            array('QuestionnaireEdit' => 'QuestionnaireEdit.inc')
        );
        $this->template->set_var(
            array(
                'questionnaireID'     => $questionnaireID,
                'description'         => Controller::htmlInputText(
                    $dsQuestionnaire->getValue(DBEQuestionnaire::description)
                ),
                'descriptionMessage'  => Controller::htmlDisplayText(
                    $dsQuestionnaire->getMessage(DBEQuestionnaire::description)
                ),
                'intro'               => Controller::htmlInputText($dsQuestionnaire->getValue(DBEQuestionnaire::intro)),
                'introMessage'        => Controller::htmlDisplayText(
                    $dsQuestionnaire->getMessage(DBEQuestionnaire::intro)
                ),
                'thankYou'            => Controller::htmlInputText(
                    $dsQuestionnaire->getValue(DBEQuestionnaire::thankYou)
                ),
                'thankYouMessage'     => Controller::htmlDisplayText(
                    $dsQuestionnaire->getMessage(DBEQuestionnaire::thankYou)
                ),
                'rating1Desc'         => Controller::htmlInputText(
                    $dsQuestionnaire->getValue(DBEQuestionnaire::rating1Desc)
                ),
                'rating1DescMessage'  => Controller::htmlDisplayText(
                    $dsQuestionnaire->getMessage(DBEQuestionnaire::rating1Desc)
                ),
                'rating5Desc'         => Controller::htmlInputText(
                    $dsQuestionnaire->getValue(DBEQuestionnaire::rating5Desc)
                ),
                'rating5DescMessage'  => Controller::htmlDisplayText(
                    $dsQuestionnaire->getMessage(DBEQuestionnaire::rating5Desc)
                ),
                'nameRequiredChecked' => Controller::htmlChecked(
                    $dsQuestionnaire->getValue(DBEQuestionnaire::nameRequired)
                ),
                'nameRequiredMessage' => Controller::htmlDisplayText(
                    $dsQuestionnaire->getMessage(DBEQuestionnaire::nameRequired)
                ),
                'urlUpdate'           => $urlUpdate,
                'urlDisplayList'      => $urlDisplayList,
                'logo'                => $dsQuestionnaire->getValue(DBEQuestionnaire::logo)
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
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsQuestionnaire->populateFromArray($this->getParam('questionnaire')));
        if ($this->formError) {
            $this->setAction('create');
            if ($this->dsQuestionnaire->getValue(DBEQuestionnaire::questionnaireID)) {
                $this->setAction('edit');
            }
            $this->edit();
            exit;
        }
        $this->buQuestionnaire->updateQuestionnaire($this->dsQuestionnaire);


        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'questionnaireID' => $this->dsQuestionnaire->getValue(DBEQuestionnaire::questionnaireID),
                'action'          => 'view'
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Display list of questions
     * @access private
     * @throws Exception
     */
    function displayQuestions()
    {
        $this->setMethodName('displayQuestions');
        $this->setTemplateFiles(
            array('QuestionList' => 'QuestionList.inc')
        );
        $dsQuestion = new DataSet($this);
        $this->buQuestionnaire->getAllQuestions(
            $this->getParam('questionnaireID'),
            $dsQuestion
        );
        $dsQuestionnaire = new DataSet($this);
        $this->buQuestionnaire->getQuestionnaireByID(
            $dsQuestion->getValue(DBEQuestion::questionnaireID),
            $dsQuestionnaire
        );

        $this->setPageTitle($dsQuestionnaire->getValue(DBEQuestionnaire::description));

        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => 'createQuestion',
                    'questionnaireID' => $this->getParam('questionnaireID')

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

                $questionID = $dsQuestion->getValue(DBEQuestion::questionID);

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'editQuestion',
                            'questionID' => $questionID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete = null;
                $txtDelete = null;
                if ($this->buQuestionnaire->canDelete($questionID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => 'deleteQuestion',
                                'questionID' => $questionID
                            )
                        );
                    $txtDelete = '[delete]';
                }

                $this->template->set_var(
                    array(
                        'questionID'               => $questionID,
                        'description'              => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEQuestion::description)
                        ),
                        'answerType'               => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEJQuestion::answerType)
                        ),
                        'weight'                   => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEQuestion::weight)
                        ),
                        'activeFlag'               => Controller::htmlDisplayText(
                            $dsQuestion->getValue(DBEQuestion::activeFlag)
                        ),
                        'questionnaireDescription' => Controller::htmlDisplayText(
                            $dsQuestionnaire->getValue(DBEQuestionnaire::description)
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
     * @throws Exception
     */
    function editQuestion()
    {
        $this->setMethodName('editQuestion');
        $dsQuestion = &$this->dsQuestion; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == 'editQuestion') {
                $this->buQuestionnaire->getQuestionByID(
                    $this->getParam('questionID'),
                    $dsQuestion
                );
                $questionID = $this->getParam('questionID');
            } else {                                  // creating new
                $dsQuestion->initialise();
                $dsQuestion->setValue(
                    DBEQuestion::questionID,
                    '0'
                );
                $dsQuestion->setValue(
                    DBEQuestion::questionnaireID,
                    $this->getParam('questionnaireID')
                );
                $questionID = '0';
            }
        } else {                                    // form validation error
            $dsQuestion->initialise();
            $dsQuestion->fetchNext();
            $questionID = $dsQuestion->getValue(DBEQuestion::questionID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if (
            $this->getAction() == 'editQuestion' && $this->buQuestionnaire->canDelete($this->getParam('questionID'))
        ) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'deleteQuestion',
                        'questionID' => $questionID
                    )
                );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'updateQuestion',
                    'questionID' => $questionID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => 'displayQuestionList',
                    'questionnaireID' => $dsQuestion->getValue(DBEQuestion::questionnaireID)
                )
            );
        $this->setPageTitle('Edit Question');
        $this->setTemplateFiles(
            array('QuestionEdit' => 'QuestionEdit.inc')
        );
        $this->template->set_var(
            array(
                'questionID'      => $questionID,
                'questionnaireID' => $dsQuestion->getValue(DBEQuestion::questionnaireID),

                'description'         => Controller::htmlInputText($dsQuestion->getValue(DBEQuestion::description)),
                'descriptionMessage'  => Controller::htmlDisplayText($dsQuestion->getMessage(DBEQuestion::description)),
                'activeFlagChecked'   => $dsQuestion->getValue(DBEQuestion::activeFlag) == 'Y' ? 'CHECKED' : null,
                'activeFlagMessage'   => Controller::htmlDisplayText($dsQuestion->getMessage(DBEQuestion::activeFlag)),
                'requiredFlagChecked' => $dsQuestion->getValue(DBEQuestion::requiredFlag) == 'Y' ? 'CHECKED' : null,
                'requiredFlagMessage' => Controller::htmlDisplayText(
                    $dsQuestion->getMessage(DBEQuestion::requiredFlag)
                ),
                'weight'              => Controller::htmlInputText($dsQuestion->getValue(DBEQuestion::weight)),
                'weightMessage'       => Controller::htmlDisplayText($dsQuestion->getMessage(DBEQuestion::weight)),
                'urlUpdate'           => $urlUpdate,
                'urlDelete'           => $urlDelete,
                'txtDelete'           => $txtDelete,
                'urlDisplayList'      => $urlDisplayList
            )
        );
        /*
        Answer types
        */
        $this->answerTypeDropdown($dsQuestion->getValue(DBEQuestion::answerTypeID));

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
                    DBEAnswerType::answerTypeID
                ) == $answerTypeID) ? CT_SELECTED : null;

            $this->template->set_var(
                array(
                    'answerTypeID'          => $this->buQuestionnaire->dbeAnswerType->getValue(
                        DBEAnswerType::answerTypeID
                    ),
                    'answerTypeDescription' => $this->buQuestionnaire->dbeAnswerType->getValue(
                        DBEAnswerType::description
                    ),
                    'answerTypeSelected'    => $answerTypeSelected
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
     * @throws Exception
     */
    function updateQuestion()
    {
        $this->setMethodName('updateQuestion');
        $this->formError = (!$this->dsQuestion->populateFromArray($this->getParam('question')));
        if ($this->formError) {
            if ($this->dsQuestion->getValue(DBEQuestion::questionID)) {          // attempt to insert
                $this->setAction('editQuestion');
            } else {
                $this->setAction('createQuestion');
            }
            $this->edit();
            exit;
        }

        $this->buQuestionnaire->updateQuestion($this->dsQuestion);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'questionnaireID' => $this->dsQuestion->getValue(DBEQuestion::questionnaireID),
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
     * @throws Exception
     */
    function deleteQuestion()
    {
        $this->setMethodName('deleteQuestion');
        // we have to know from what questionnaire we have to delete the question so we can redirect the user back to
        // the correct list of questions

        $questionID = $this->getParam('questionID');

        if (!$questionID) {
            return $this->displayFatalError('Question ID not given');
        }
        $dsQuestion = new DataSet($this);
        $this->buQuestionnaire->getQuestionByID($questionID, $dsQuestion);

        $questionnaireID = $dsQuestion->getValue(DBEQuestion::questionnaireID);

        if (!$this->buQuestionnaire->deleteQuestion($questionID)) {
            $this->displayFatalError('Cannot delete this question, it has already been answered at least once');
            exit;
        }
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'          => 'displayQuestionList',
                    'questionnaireID' => $questionnaireID
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }
}
