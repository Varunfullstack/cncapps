<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 28/11/2018
 * Time: 10:57
 */

namespace CNCLTD;
use Controller;
use DBEAnswerType;
use DBEStaffAppraisalObjectives;
use DBEStaffAppraisalQuestion;
use DBEStaffAppraisalQuestionAnswer;
use DBEStaffAppraisalQuestionnaireAnswer;
use DBEUser;
use setasign\Fpdi\Fpdi;

require_once __DIR__."/../../cnccode/fpdf/fpdf_protection.php";

class StaffAppraisalPDF extends Fpdi
{

    private $leftMargin = 10;
    private $rightMargin = 10;
    private $topMargin = 10;
    private $bottomMargin = 10;
    private $headerImage = IMAGES_DIR . '/cnc_logo.png';
    private $headerImageRatio;
    private $headerImageWidth;
    private $headerHeight;
    private $headerImagePositionFromRightMargin = 10;
    private $headerImagePositionFromTopEdge = 10;
    private $marginFromHeader = 10;


    private $footerImage;
    private $footerImageRatio;
    private $footerImageWidth;
    private $footerHeight;
    private $footerImagePositionFromRightMargin = 10;
    private $footerImagePositionFromTopEdge = 10;
    private $marginFromFooter = 10;
    private $footerPosition;
    private $questionDescriptionWidth = 60;
    private $staffAnswerWidth = 60;
    private $managerAnswerWidth = 60;
    private $managerCommentsWidth = 60;

    private $questionSeparation = 6;
    private $questionSpacer = 8;
    private $questionDescriptionFreeTextWidth;
    private $staffAnswerFreeTextWidth;
    private $managerAnswerFreeTextWidth;
    private $objectiveDescriptionWidth;
    private $objectiveMeasureWidth;
    private $objectiveCommentWidth;
    private $salaryColumnWidth;

    private $teamLeaderTitleWidth;
    private $teamLeaderCommentWidth;
    private $hideFooter = false;
    private $hideHeader = false;
    private $passPhrase;
    /** @var DBEUser */
    private $staffMember;

    public function __construct(DBEStaffAppraisalQuestionnaireAnswer $questionnaireAnswer,
                                $passPhrase
    )
    {
        parent::__construct();

        $this->passPhrase = $passPhrase;

        $this->SetFont(
            'Arial',
            '',
            10
        );
        // we need to calculate how much space is left after header and footer is rendered
        list($originalWidth, $originalHeight) = getimagesize($this->headerImage);
        $this->headerImageRatio = $originalHeight / $originalWidth;
        $this->headerImageWidth = $this->GetPageWidth() / 5;
        $this->headerHeight = $this->headerImagePositionFromTopEdge + ($this->headerImageWidth * $this->headerImageRatio) + $this->marginFromHeader;


        $this->footerImage = $GLOBALS['cfg']['cncaddress_path'];
        list($originalWidth, $originalHeight) = getimagesize($this->footerImage);
        $this->footerImageRatio = $originalHeight / $originalWidth;
        $this->footerImageWidth = $this->GetPageWidth() - $this->leftMargin - $this->rightMargin;
        $this->footerHeight = ($this->footerImageWidth * $this->footerImageRatio) + $this->marginFromFooter;
        $this->footerPosition = $this->GetPageHeight() - $this->footerHeight;

        $availableWidth = $this->GetPageWidth() - $this->leftMargin * 2;

        $this->questionDescriptionWidth = $availableWidth / 3;
        $this->questionDescriptionFreeTextWidth = $availableWidth * 0.2;
        $this->staffAnswerWidth = $availableWidth / 6;
        $this->staffAnswerFreeTextWidth = $availableWidth * 0.4;
        $this->managerAnswerWidth = $availableWidth / 6;
        $this->managerAnswerFreeTextWidth = $availableWidth * 0.4;
        $this->managerCommentsWidth = $availableWidth / 3;


        $this->objectiveDescriptionWidth = $availableWidth * 0.3;
        $this->objectiveMeasureWidth = $availableWidth * 0.3;
        $this->objectiveCommentWidth = $availableWidth * 0.4;

        $this->salaryColumnWidth = $availableWidth / 3;

        $this->teamLeaderTitleWidth = $availableWidth * 0.3;
        $this->teamLeaderCommentWidth = $availableWidth * 0.7;

        $this->SetAutoPageBreak(
            false,
            $this->footerHeight
        );

        $questionnaireAnswerID = $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::id);

        $staffMemberID = $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::staffMemberID);
        $staffMember = new DBEUser($this);
        $staffMember->getRow($staffMemberID);

        $this->staffMember = $staffMember;

        $managerID = $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerID);
        $manager = new DBEUser($this);
        $manager->getRow($managerID);

        $this->AddPage();
        $this->SetY($this->headerHeight);
        $this->setBold();
        $this->Cell(
            50,
            10,
            'Appraisee:'
        );
        $this->resetFont();
        $this->Cell(
            50,
            10,
            $staffMember->getValue(DBEUser::firstName) . " " . $staffMember->getValue(DBEUser::lastName)
        );
        $this->setBold();
        $this->Cell(
            50,
            10,
            'Appraiser:'
        );
        $this->resetFont();
        $this->Cell(
            50,
            10,
            $manager->getValue(DBEUser::firstName) . " " . $manager->getValue(DBEUser::lastName)
        );
        $this->Ln();
        $this->setBold();
        $this->Cell(
            50,
            10,
            'Employment Start:'
        );
        $this->resetFont();

        $this->Cell(
            50,
            10,
            Controller::dateYMDtoDMY(
                $staffMember->getValue(DBEUser::startDate),
                '-'
            )
        );
        $this->setBold();
        $this->Cell(
            50,
            10,
            'Position:'
        );
        $this->resetFont();
        $this->Cell(
            50,
            10,
            $staffMember->getValue(DBEUser::jobTitle)
        );
        $this->Ln();
        $this->setBold();
        $this->Cell(
            50,
            10,
            'Sick Days This Year:'
        );
        $this->resetFont();
        $this->Cell(
            50,
            10,
            $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::sickDaysThisYear)
        );

        $this->Ln();
        $this->Ln();
        $questionnaireID = $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::questionnaireID);
        $dbeQuestions = new DBEStaffAppraisalQuestion($this);

        $dbeQuestions->getRowsForQuestionnaire($questionnaireID);
        $previousQuestionType = null;
        while ($dbeQuestions->fetchNext()) {

            $this->renderQuestion(
                $dbeQuestions,
                $questionnaireAnswerID,
                $previousQuestionType
            );

            $previousQuestionType = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID);
            //lets assume we need at least 30mm per question
        }


        // now we are going to render the objectives
        $this->renderObjectives($questionnaireAnswerID);

        $this->renderSalarySection($questionnaireAnswer);

        $this->renderTeamLeaderComments($questionnaireAnswer);

        $this->renderManagerComments($questionnaireAnswer);

        $this->hideHeader = true;

        $pageCount = $this->setSourceFile(PDF_RESOURCE_DIR . '/StafflastPage.pdf');
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pageId = $this->importPage($pageNo);
            $s = $this->getTemplatesize($pageId);
            $this->AddPage(
                $s['orientation'],
                $s
            );
            $this->useImportedPage($pageId);
        }

        $this->hideFooter = true;
    }

    private function setBold()
    {
        $this->SetFont(
            'Arial',
            'B',
            10
        );
    }

    private function resetFont()
    {
        $this->SetFont(
            'Arial',
            '',
            10
        );
    }

    private function renderQuestion(DBEStaffAppraisalQuestion $dbeQuestions,
                                    $questionnaireAnswerID,
                                    $previousQuestionType
    )
    {
        // we now look at the current question and render it as we should
        $questionDescription = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::description);
        $questionID = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::id);
        $questionType = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID);
        $question = "";

        $dbeQuestionAnswer = new DBEStaffAppraisalQuestionAnswer($this);

        $dbeQuestionAnswer->getRowByIDAndQuestionnaireAnswerID(
            $questionID,
            $questionnaireAnswerID
        );
        $staffAnswer = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::staffAnswer);
        $managerAnswer = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::managerAnswer);
        if ($dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID) == 5) {
            $dbeQuestionType = new DBEAnswerType($this);
            $dbeQuestionType->getRow($questionType);

            $answerOptionsString = $dbeQuestionType->getValue(DBEAnswerType::answerOptions);

            $answerOptions = json_decode($answerOptionsString);

            $staffAnswer = $answerOptions[$staffAnswer];
            $managerAnswer = $answerOptions[$managerAnswer];
        }
        $managerComments = $dbeQuestionAnswer->getValue(DBEStaffAppraisalQuestionAnswer::managerComment);

        $freeTextQuestions = [3, 4];
        $currentQuestionType = $dbeQuestions->getValue(DBEStaffAppraisalQuestion::answerTypeID);
        $isCurrentFreeType = in_array(
            $currentQuestionType,
            $freeTextQuestions
        );
        $descriptionWidth = $isCurrentFreeType ? $this->questionDescriptionFreeTextWidth : $this->questionDescriptionWidth;
        $staffAnswerWidth = $isCurrentFreeType ? $this->staffAnswerFreeTextWidth : $this->staffAnswerWidth;
        $managerAnswerWidth = $isCurrentFreeType ? $this->managerAnswerFreeTextWidth : $this->managerAnswerWidth;


        $thing = [
            [$descriptionWidth, $questionDescription],
            [$staffAnswerWidth, $staffAnswer],
            [$managerAnswerWidth, $managerAnswer],
        ];

        if (!$isCurrentFreeType) {
            $thing[] = [$this->managerCommentsWidth, $managerComments];
        }

        if (!$previousQuestionType) {
            $this->renderQuestionsHeader($isCurrentFreeType);
        } else {
            $isPreviousFreeType = in_array(
                $previousQuestionType,
                $freeTextQuestions
            );


            $considerHeader = ($previousQuestionType != $currentQuestionType && (($isCurrentFreeType && !$isPreviousFreeType) || (!$isCurrentFreeType && $isPreviousFreeType)));
            $pageAdded = false;
            if ($this->newPageIfNeeded(
                $thing,
                $this->questionSeparation,
                $considerHeader
            )) {
                $this->AddPage();
                $this->SetY($this->headerHeight);
                $pageAdded = true;
            }

            if ($pageAdded || $considerHeader) {
                $this->renderQuestionsHeader($isCurrentFreeType);
            }

        }

        $this->setBold();
        $lineBreaks = $this->NewMultiCell(
            $descriptionWidth,
            $this->questionSeparation,
            $questionDescription
        );
        $this->resetFont();


        $tempLineBreaks = $this->NewMultiCell(
            $staffAnswerWidth,
            $this->questionSeparation,
            $staffAnswer
        );

        $lineBreaks = $lineBreaks > $tempLineBreaks ? $lineBreaks : $tempLineBreaks;

        $tempLineBreaks = $this->NewMultiCell(
            $managerAnswerWidth,
            $this->questionSeparation,
            $managerAnswer
        );

        $lineBreaks = $lineBreaks > $tempLineBreaks ? $lineBreaks : $tempLineBreaks;

        if (!$isCurrentFreeType) {
            $tempLineBreaks = $this->NewMultiCell(
                $this->managerCommentsWidth,
                $this->questionSeparation,
                $managerComments
            );
            $lineBreaks = $lineBreaks > $tempLineBreaks ? $lineBreaks : $tempLineBreaks;
        }


        for ($i = $lineBreaks + 3; $i > 0; $i--) {
            $this->Ln($this->questionSeparation);
        }

        return $question;

    }

    private function renderQuestionsHeader($isFreeText = false)
    {

        $descriptionWidth = $isFreeText ? $this->questionDescriptionFreeTextWidth : $this->questionDescriptionWidth;
        $staffAnswerWidth = $isFreeText ? $this->staffAnswerFreeTextWidth : $this->staffAnswerWidth;
        $managerAnswerWidth = $isFreeText ? $this->managerAnswerFreeTextWidth : $this->managerAnswerWidth;
        //we assume we are possitioned in the right place
        $this->SetFontSize(12);
        $this->Cell(
            $descriptionWidth,
            $this->questionSeparation,
            'Question'
        );
        $this->Cell(
            $staffAnswerWidth,
            $this->questionSeparation,
            'Employee'
        );
        $this->Cell(
            $managerAnswerWidth,
            $this->questionSeparation,
            'Manager'
        );

        if (!$isFreeText) {
            $this->Cell(
                $this->managerCommentsWidth,
                $this->questionSeparation,
                'Manager Comment'
            );
        }

        $this->Ln();
        $this->SetFontSize(10);
    }

    private function newPageIfNeeded(array $thing,
                                     $h,
                                     $considerHeader = false
    )
    {
        $maxLineBreaks = 0;
        foreach ($thing as $item) {

            $lineBreaks = 0;
            // Output text with automatic or explicit line breaks
            $cw = &$this->CurrentFont['cw'];
            $txt = $item[1];
            $w = $item[0];
            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
            $s = str_replace(
                "\r",
                '',
                $txt
            );
            $nb = strlen($s);
            if ($nb > 0 && $s[$nb - 1] == "\n")
                $nb--;
            $b = 0;
            $sep = -1;
            $i = 0;
            $j = 0;
            $l = 0;
            $ns = 0;
            $nl = 1;
            while ($i < $nb) {
                // Get next character
                $c = $s[$i];
                if ($c == ' ') {
                    $sep = $i;
                    $ls = $l;
                    $ns++;
                }
                $l += $cw[$c];
                if ($l > $wmax) {
                    // Automatic line break
                    if ($sep == -1) {
                        if ($i == $j) {
                            $i++;
                        }
                        if ($this->ws > 0) {
                            $this->ws = 0;
                            $this->_out('0 Tw');
                        }

                        $lineBreaks++;
                    } else {
                        $lineBreaks++;
                        $i = $sep + 1;
                    }
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $ns = 0;
                    $nl++;
                } else
                    $i++;
            }
            // Last chunk
            if ($this->ws > 0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }

            if ($lineBreaks > $maxLineBreaks) {
                $maxLineBreaks = $lineBreaks;
            }
        }
        return ($this->GetY(
            ) + (($maxLineBreaks + 2) * $this->questionSeparation) + ($considerHeader ? 25 : 0) > $this->GetPageHeight(
            ) - $this->footerHeight);
    }

    function NewMultiCell($w,
                          $h,
                          $txt,
                          $border = 0,
                          $ln = 0,
                          $align = 'J',
                          $fill = false
    )
    {
        // Custom Tomaz Ahlin
        if ($ln == 0) {
            $current_y = $this->GetY();
            $current_x = $this->GetX();
        }

        // Output text with automatic or explicit line breaks
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace(
            "\r",
            '',
            $txt
        );
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $b = 0;
        if ($border) {
            if ($border == 1) {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            } else {
                $b2 = '';
                if (strpos(
                        $border,
                        'L'
                    ) !== false)
                    $b2 .= 'L';
                if (strpos(
                        $border,
                        'R'
                    ) !== false)
                    $b2 .= 'R';
                $b = (strpos(
                        $border,
                        'T'
                    ) !== false) ? $b2 . 'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        $lineBreaks = 0;
        while ($i < $nb) {
            // Get next character
            $c = $s[$i];
            if ($c == "\n") {
                // Explicit line break
                if ($this->ws > 0) {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell(
                    $w,
                    $h,
                    substr(
                        $s,
                        $j,
                        $i - $j
                    ),
                    $b,
                    2,
                    $align,
                    $fill
                );
                $lineBreaks++;
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if ($border && $nl == 2)
                    $b = $b2;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                // Automatic line break
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                    if ($this->ws > 0) {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }

                    $this->Cell(
                        $w,
                        $h,
                        substr(
                            $s,
                            $j,
                            $i - $j
                        ),
                        $b,
                        2,
                        $align,
                        $fill
                    );
                    $lineBreaks++;
                } else {
                    if ($align == 'J') {
                        $this->ws = ($ns > 1) ? ($wmax - $ls) / 1000 * $this->FontSize / ($ns - 1) : 0;
                        $this->_out(
                            sprintf(
                                '%.3F Tw',
                                $this->ws * $this->k
                            )
                        );
                    }
                    $lineBreaks++;
                    $this->Cell(
                        $w,
                        $h,
                        substr(
                            $s,
                            $j,
                            $sep - $j
                        ),
                        $b,
                        2,
                        $align,
                        $fill
                    );
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if ($border && $nl == 2)
                    $b = $b2;
            } else
                $i++;
        }
        // Last chunk
        if ($this->ws > 0) {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if ($border && strpos(
                $border,
                'B'
            ) !== false) {
            $b .= 'B';
        }
        $this->Cell(
            $w,
            $h,
            substr(
                $s,
                $j,
                $i - $j
            ),
            $b,
            2,
            $align,
            $fill
        );
        $this->x = $this->lMargin;

        // Custom Tomaz Ahlin
        if ($ln == 0) {
            $this->SetXY(
                $current_x + $w,
                $current_y
            );
        }
        return $lineBreaks;
    }

    private function renderObjectives($questionnaireAnswerID)
    {
        $dbeObjective = new DBEStaffAppraisalObjectives($this);

        $dbeObjective->getRowsByAnswerID($questionnaireAnswerID);

        $shouldPrintHeader = true;
        while ($dbeObjective->fetchNext()) {
            if (!empty($dbeObjective->getValue(DBEStaffAppraisalObjectives::requirement)) ||
                !empty($dbeObjective->getValue(DBEStaffAppraisalObjectives::measure)) ||
                !empty($dbeObjective->getValue(DBEStaffAppraisalObjectives::comment))) {

                // do I need to print the header?
                $objectiveDescription = $dbeObjective->getValue(DBEStaffAppraisalObjectives::requirement);
                $objectiveMeasure = $dbeObjective->getValue(DBEStaffAppraisalObjectives::measure);
                $objectivesComment = $dbeObjective->getValue(DBEStaffAppraisalObjectives::comment);

                $widths = [
                    [$this->objectiveDescriptionWidth, $objectiveDescription],
                    [$this->objectiveMeasureWidth, $objectiveMeasure],
                    [$this->objectiveCommentWidth, $objectivesComment]
                ];
                $pageAdded = false;
                if ($this->newPageIfNeeded(
                    $widths,
                    $this->questionSeparation,
                    $shouldPrintHeader
                )) {
                    $this->AddPage();
                    $this->SetY($this->headerHeight);
                    $pageAdded = true;
                }

                if ($pageAdded || $shouldPrintHeader) {
                    $this->renderObjectiveHeaders();
                }

                $this->renderObjectiveLine(
                    $objectiveDescription,
                    $objectiveMeasure,
                    $objectivesComment
                );
                $shouldPrintHeader = false;
            }
        }
    }

    private function renderObjectiveHeaders()
    {
        $this->setBold();
        $this->SetFontSize(12);
        $this->Cell(
            $this->objectiveDescriptionWidth,
            $this->questionSeparation,
            'Objective'
        );
        $this->Cell(
            $this->objectiveMeasureWidth,
            $this->questionSeparation,
            'Measure/Standard'
        );
        $this->Cell(
            $this->objectiveCommentWidth,
            $this->questionSeparation,
            'Comment'
        );
        $this->resetFont();
        $this->Ln();
    }

    private function renderObjectiveLine($description,
                                         $measure,
                                         $comment
    )
    {
        $lineBreaks = 0;
        $tempLineBreaks = $this->NewMultiCell(
            $this->objectiveDescriptionWidth,
            $this->questionSeparation,
            $description
        );
        $lineBreaks = $lineBreaks > $tempLineBreaks ? $lineBreaks : $tempLineBreaks;
        $tempLineBreaks = $this->NewMultiCell(
            $this->objectiveMeasureWidth,
            $this->questionSeparation,
            $measure
        );
        $lineBreaks = $lineBreaks > $tempLineBreaks ? $lineBreaks : $tempLineBreaks;
        $tempLineBreaks = $this->NewMultiCell(
            $this->objectiveCommentWidth,
            $this->questionSeparation,
            $comment
        );
        $lineBreaks = $lineBreaks > $tempLineBreaks ? $lineBreaks : $tempLineBreaks;

        for ($i = $lineBreaks + 2; $i > 0; $i--) {
            $this->Ln($this->questionSeparation);
        }
    }

    private function renderSalarySection(DBEStaffAppraisalQuestionnaireAnswer $questionnaireAnswer)
    {

        if ($this->newPageIfNeeded(
            [[9, "test"]],
            8,
            true
        )) {
            $this->AddPage();
            $this->SetY($this->headerHeight);
        }
        $this->setBold();
        $this->SetFontSize(12);
        $this->Cell(
            $this->objectiveDescriptionWidth,
            $this->questionSeparation,
            'Current Salary'
        );
        $this->Cell(
            $this->objectiveMeasureWidth,
            $this->questionSeparation,
            'Proposed Salary'
        );
        $this->Cell(
            $this->objectiveCommentWidth,
            $this->questionSeparation,
            'Proposed Bonus'
        );
        $this->resetFont();
        $this->Ln();

        $currentSalary = "Not Set";

        $currentSalaryEncrypted = $this->staffMember->getValue(DBEUser::encryptedSalary);

        if ($currentSalaryEncrypted) {
            $currentSalaryValue = Encryption::decrypt(
                USER_ENCRYPTION_PRIVATE_KEY,
                $this->passPhrase,
                $currentSalaryEncrypted
            );
            $currentSalary = Controller::formatNumberCur(
                $currentSalaryValue,
                2,
                ',',
                false
            );

        }


        $this->Cell(
            $this->objectiveDescriptionWidth,
            $this->questionSeparation,
            $currentSalary
        );
        $this->Cell(
            $this->objectiveMeasureWidth,
            $this->questionSeparation,
            Controller::formatNumberCur(
                $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::proposedSalary),
                2,
                ',',
                false
            )
        );
        $this->Cell(
            $this->objectiveCommentWidth,
            $this->questionSeparation,
            Controller::formatNumberCur(
                $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::proposedBonus),
                2,
                ',',
                false
            )
        );
        $this->Ln();
        $this->Ln();
    }

    private function renderTeamLeaderComments(DBEStaffAppraisalQuestionnaireAnswer $questionnaireAnswer)
    {

        $teamLeaderComments = $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::teamLeaderComments);
        if ($this->newPageIfNeeded(
            [[$this->teamLeaderCommentWidth, $teamLeaderComments]],
            8,
            false
        )) {
            $this->AddPage();
            $this->SetY($this->headerHeight);
        }
        $this->setBold();
        $this->SetFontSize(12);
        $this->NewMultiCell(
            $this->teamLeaderTitleWidth,
            $this->questionSeparation,
            'Team Leader Comments'
        );
        $this->resetFont();
        $lineBreaks = $this->NewMultiCell(
            $this->teamLeaderCommentWidth,
            $this->questionSeparation,
            $teamLeaderComments
        );
        for ($i = $lineBreaks + 2; $i > 0; $i--) {
            $this->Ln($this->questionSeparation);
        }
    }

    private function renderManagerComments(DBEStaffAppraisalQuestionnaireAnswer $questionnaireAnswer)
    {
        $managerComments = $questionnaireAnswer->getValue(DBEStaffAppraisalQuestionnaireAnswer::managerComments);
        if ($this->newPageIfNeeded(
            [[$this->teamLeaderCommentWidth, $managerComments]],
            8,
            false
        )) {
            $this->AddPage();
            $this->SetY($this->headerHeight);
        }
        $this->setBold();
        $this->SetFontSize(12);
        $this->NewMultiCell(
            $this->teamLeaderTitleWidth,
            $this->questionSeparation,
            'Manager Comments'
        );
        $this->resetFont();
        $lineBreaks = $this->NewMultiCell(
            $this->teamLeaderCommentWidth,
            $this->questionSeparation,
            $managerComments
        );
        for ($i = $lineBreaks; $i > 0; $i--) {
            $this->Ln($this->questionSeparation);
        }
    }

    public function Header()
    {
        if ($this->hideHeader) {
            return;
        }
        $this->Image(
            $this->headerImage,
            $this->GetPageWidth(
            ) - ($this->headerImageWidth + $this->rightMargin + $this->headerImagePositionFromRightMargin),
            $this->headerImagePositionFromTopEdge,
            $this->headerImageWidth
        );
    }

    public function Footer()
    {
        if ($this->hideFooter) {
            return;
        }
        $this->Image(
            $this->footerImage,
            $this->leftMargin,
            $this->GetPageHeight() - ($this->footerImageWidth * $this->footerImageRatio),
            $this->footerImageWidth
        );
    }

    /**
     * MultiCell with alignment as in Cell.
     * @param float $w
     * @param float $h
     * @param string $text
     * @param mixed $border
     * @param int $ln
     * @param string $align
     * @param boolean $fill
     */
    private function MultiAlignCell($w,
                                    $h,
                                    $text,
                                    $border = 0,
                                    $ln = 0,
                                    $align = 'L',
                                    $fill = false
    )
    {
        // Store reset values for (x,y) positions
        $x = $this->GetX() + $w;
        $y = $this->GetY();

        // Make a call to FPDF's MultiCell
        $this->MultiCell(
            $w,
            $h,
            $text,
            $border,
            $align,
            $fill
        );

        // Reset the line position to the right, like in Cell
        if ($ln == 0) {
            $this->SetXY(
                $x,
                $y
            );
        }
    }

}