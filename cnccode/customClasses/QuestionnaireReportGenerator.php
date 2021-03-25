<?php

namespace CNCLTD;

use DateTime;
use DateTimeInterface;

class QuestionnaireReportGenerator
{
    private $questionnaireId;
    /**
     * @var DateTimeInterface|null
     */
    private $startDate;
    /**
     * @var DateTimeInterface|null
     */
    private $endDate;
    /**
     * @var \dbSweetcode|\MDB_PEAR_PROXY|mixed|object|\PDO
     */
    private $db;
    private $period;
    private $year;

    /**
     * QuestionnaireReportGenerator constructor.
     * @param $questionnaireId
     * @param DateTimeInterface|null $startDate
     * @param DateTimeInterface|null $endDate
     */
    public function __construct($questionnaireId,
                                DateTimeInterface $startDate = null,
                                DateTimeInterface $endDate = null
    )
    {

        $this->questionnaireId = $questionnaireId;
        if (!$startDate) {
            $startDate = (new DateTime())->modify('-1 month');
        }
        if (!$endDate) {
            $endDate = new DateTime();
        }
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        global $db;
        $this->db = $db;
    }

    function getMonthName()
    {
        return date('F', strtotime($this->period));
    }

    function getYear()
    {
        return $this->year;
    }

    function getReport($csv = false)
    {
        global $twig;
        $template = '@internal/questionnaireReport/htmlReport.html.twig';
        if ($csv) {
            $template = '@internal/questionnaireReport/csvReport.csv.twig';
        }
        $questionnaire = $this->getQuestionnaire();
        $questionType = 1; // ratings
        $questions = $this->getQuestions($questionType);
        $context = [
            "ratings" => []
        ];
        foreach ($questions as $value) {

            $total = $this->getTotal($value['que_questionno']);
            $context["ratings"][] = [
                'ratingTotal'         => $total,
                'questionDescription' => $value['que_desc'],
                'rating1Percent'      => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        1,
                        $total
                    )
                ),
                'rating2Percent'      => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        2,
                        $total
                    )
                ),
                'rating3Percent'      => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        3,
                        $total
                    )
                ),
                'rating4Percent'      => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        4,
                        $total
                    )
                ),
                'rating5Percent'      => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        5,
                        $total
                    )
                ),
            ];


        }
        $questionType = 2; // Yes/No
        $questions = $this->getQuestions($questionType);
        $context['yesNoQuestions'] = [];
        foreach ($questions as $value) {

            $total = $this->getTotal($value['que_questionno']);
            $context['yesNoQuestions'][] = [
                'ynTotal'             => $total,
                'questionDescription' => $value['que_desc'],
                'yesPercent'          => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        'Y',
                        $total
                    )
                ),
                'noPercent'           => number_format(
                    $this->getRatingPercentage(
                        $value['que_questionno'],
                        'N',
                        $total
                    )
                ),
            ];
        }
        $questionType = 7; // MultiChoice
        $questions                       = $this->getQuestions($questionType);
        $context['multiChoiceQuestions'] = [];
        foreach ($questions as $value) {

            $total = $this->getTotal($value['que_questionno']);
            $valuesAndLabels = $this->getMultiChoiceValuesAndLabels($value['que_questionno']);
            $values          = [];
            $labels          = [];
            foreach ($valuesAndLabels as $label => $amount) {
                $labels[] = $label;
                $values[] = $amount;
            }
            $context['multiChoiceQuestions'][] = [
                'multiChoiceTotal'     => $total,
                'questionDescription'  => $value['que_desc'],
                'multiChoiceValues'    => implode(',', $values),
                'multiChoiceLabels'    => implode('|', $labels),
                'multiChoiceLegends'   => implode('|', $values),
                'multiChoiceLabelsCSV' => implode(
                    ',',
                    array_map(function ($label) { return "\"$label\""; }, $labels)
                ),
            ];
        }
        $freeText = $this->getAnswers();
        $context['textQuestions'] = [];
        while ($row = $freeText->fetch_object()) {
            $context['textQuestions'][] = [
                'freeTextQuestion' => $row->que_desc,
                'freeTextAnswer'   => $row->ans_answer,
                'freeTextName'     => $row->ans_name,
                'freeTextDate'     => $row->answerDate,
                'freeTextCustomer' => $row->cus_name,
            ];
        }
        $context['questionnaireDescription'] = $questionnaire->qur_desc;
        $context['rating1Description']       = $questionnaire->qur_rating_1_desc;
        $context['rating5Description']       = $questionnaire->qur_rating_5_desc;
        $context['period']                   = $this->getPeriod();
        $output                              = $twig->render($template, $context);
        if ($csv) {
            $output = html_entity_decode($output);
        }
        return $output;
    }

    function getQuestionnaire()
    {
        $sql = "SELECT
          qur_desc,
          qur_rating_1_desc,
          qur_rating_5_desc

          FROM
           questionnaire
          WHERE
           qur_questionnaireno = ?";
        $statement = $this->db->preparedQuery($sql, [["type" => "i", "value" => $this->questionnaireId]]);
        return $statement->fetch_object();
    }

    function getQuestions($answerTypeID = 1)
    {
        $sql = "SELECT
          que_questionno, que_desc

          FROM
           question
          WHERE
           que_questionnaireno = ?
           AND que_active_flag = 'Y'
           AND que_answertypeno = ?
          ORDER BY
           que_weight";
        $statement = $this->db->preparedQuery(
            $sql,
            [
                [
                    "type"  => "i",
                    "value" => $this->questionnaireId
                ],
                [
                    "type"  => "i",
                    "value" => $answerTypeID
                ],
            ]
        );
        $questions = [];
        while ($row = $statement->fetch_assoc()) {
            $questions[] = $row;
        }
        return $questions;
    }

    function getTotal($questionID)
    {
        $sql       = "SELECT
          count(*) as total
          FROM
           answer
          WHERE
           ans_questionno = $questionID
           AND ans_date BETWEEN '{$this->startDate->format(DATE_MYSQL_DATE)}' AND '{$this->endDate->format(DATE_MYSQL_DATE)}' 
            AND ans_answer <> 'X'";
        $statement = $this->db->preparedQuery(
            $sql,
            [
                [
                    "type"  => "i",
                    "value" => $questionID
                ],
                [
                    "type"  => "s",
                    "value" => $this->startDate->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => "i",
                    "value" => $this->endDate->format(DATE_MYSQL_DATE)
                ],
            ]
        );
        return $statement->fetch_object()->total;
    }

    function getRatingPercentage($questionID, $rating, $total)
    {
        $sql = "SELECT
          count(*) as count
          FROM
           answer
          WHERE
           ans_questionno = ?
           AND ans_date BETWEEN ? AND ?
           AND ans_answer = ?";
        $statement = $this->db->preparedQuery(
            $sql,
            [
                [
                    "type"  => "i",
                    "value" => $questionID
                ],
                [
                    "type"  => "s",
                    "value" => $this->startDate->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => "s",
                    "value" => $this->endDate->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => "s",
                    "value" => $rating
                ],
            ]
        );
        $count     = $statement->fetch_object()->count;
        if ($total != 0) {
            return ($count / $total) * 100;
        } else {
            return 0;
        }

    }

    private function getMultiChoiceValuesAndLabels($que_questionno)
    {
        $sql = "SELECT
*        
         FROM
           answer           
          WHERE
           ans_date BETWEEN ? AND ?
           AND ans_questionno = $que_questionno
       ";
        $mysqliResult = $this->db->preparedQuery(
            $sql,
            [
                [
                    "type"  => "s",
                    "value" => $this->startDate->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => "s",
                    "value" => $this->endDate->format(DATE_MYSQL_DATE)
                ],
            ]
        );
        $labels       = [];
        while ($answer = $mysqliResult->fetch_assoc()) {
            if (!$answer['ans_answer']) {
                continue;
            }
            $answerArray = json_decode($answer['ans_answer']);
            foreach ($answerArray as $answerLabel) {
                if (!isset($labels[$answerLabel])) {
                    $labels[$answerLabel] = 0;
                }
                $labels[$answerLabel]++;
            }
        }
        return $labels;
    }

    function getAnswers()
    {
        $sql = "SELECT
          que_desc,
          ans_answer,
          ans_name,
          cus_name,
          date_format(ans_date, '%d/%m/%y') as answerDate
         FROM
           answer
           JOIN question ON que_questionno = ans_questionno
           LEFT JOIN problem ON pro_problemno = ans_problemno
           LEFT JOIN customer ON cus_custno = pro_custno
         WHERE
           que_questionnaireno = ?
           AND ans_date BETWEEN ? AND ?
           AND ans_answer <> ''";
        return $this->db->preparedQuery(
            $sql,
            [
                [
                    "type"  => "i",
                    "value" => $this->questionnaireId
                ],
                [
                    "type"  => "s",
                    "value" => $this->startDate->format(DATE_MYSQL_DATE)
                ],
                [
                    "type"  => "s",
                    "value" => $this->endDate->format(DATE_MYSQL_DATE)
                ],
            ]
        );
    }

    function getPeriod()
    {
        return "{$this->startDate->format('d/m/Y')} to {$this->endDate->format('d/m/Y')}";
    }

    function setPeriod($period)
    {
        $this->year      = substr($period, 0, 4);
        $this->period    = $period;
        $endDateUnix     = strtotime($period . 'last day next month');
        $startDateUnix   = strtotime($period);
        $this->startDate = new DateTime($startDateUnix);
        $this->endDate   = new DateTime($endDateUnix);
    }

    function getQuestionnaireDescription()
    {
        return '';
    }
}