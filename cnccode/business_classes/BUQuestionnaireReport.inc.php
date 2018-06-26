<?php
/**
 * Service desk report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg["path_dbe"] . "/DBEPrizewinner.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

class BUQuestionnaireReport extends Business
{
    public $customerID = false;

    public $questionnaireID;

    public $startDate;

    public $endDate;

    public $period;

    private $startDateOneYearAgo;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function setPeriod($period)
    {
        $this->year = substr($period, 0, 4);
        $this->month = substr($period, 5, 2);
        $this->period = $period;

        $endDateUnix = strtotime($period . 'last day next month');
        $startDateUnix = strtotime($period);

        $this->startDate = date('Y-m-d', $startDateUnix);

        $this->endDate = date('Y-m-d', $endDateUnix);

        $this->startDateOneYearAgo = date('Y-m-d', strtotime('-1 year', $startDateUnix));


    }

    function setQuestionnaireID($ID)
    {
        $this->questionnaireID = $ID;

    }

    function getMonthName()
    {
        return date('F', strtotime($this->period));
    }

    function getYear()
    {
        return $this->year;
    }

    function getRatings($queID)
    {
        $sql =
            "SELECT
          que_questionno, que_desc, ans_answer, COUNT(*) as Total

          FROM
           answer
           JOIN question ON ans_questionno = que_questionno
          WHERE
           que_answertypeno = 1
           AND que_questionnaireno = $questionnaireID
           AND ans_date BETWEEN '$this->startDate' AND '$this->endDate'
          GROUP BY
           que_desc, ans_answer
          ORDER BY
           que_questionno, ans_answer";

        return $this->db->query($sql)->fetch_object();
    }

    function getQuestions($answerTypeID = 1)
    {
        $sql =
            "SELECT
          que_questionno, que_desc

          FROM
           question
          WHERE
           que_questionnaireno = $this->questionnaireID
           AND que_active_flag = 'Y'
           AND que_answertypeno = $answerTypeID
          ORDER BY
           que_weight";

        $results = $this->db->query($sql);

        $questions = [];
        while ($row = $results->fetch_assoc()) {
            $questions[] = $row;
        }
        return $questions;
    }

    function getRatingPercentage($questionID, $rating, $total)
    {
        $sql =
            "SELECT
          count(*) as count

          FROM
           answer
          WHERE
           ans_questionno = $questionID
           AND ans_date BETWEEN '$this->startDate' AND '$this->endDate'
           AND ans_answer = '$rating'";

        $count = $this->db->query($sql)->fetch_object()->count;


        if ($total != 0) {
            return ($count / $total) * 100;
        } else {
            return 0;
        }

    }

    function getTotal($questionID)
    {
        $sql =
            "SELECT
          count(*) as total

          FROM
           answer
          WHERE
           ans_questionno = $questionID
           AND ans_date BETWEEN '$this->startDate' AND '$this->endDate'";

        $sql .=
            " AND ans_answer <> 'X'";          // exclude N/A

        return $this->db->query($sql)->fetch_object()->total;
    }

    function getAnswers()
    {
        $sql =
            "SELECT
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
           que_questionnaireno = $this->questionnaireID
           AND ans_date BETWEEN '$this->startDate' AND '$this->endDate'
           AND ans_answer <> ''";

        return $this->db->query($sql);
    }


    function getQuestionnaire()
    {
        $sql =
            "SELECT
          qur_desc,
          qur_rating_1_desc,
          qur_rating_5_desc

          FROM
           questionnaire
          WHERE
           qur_questionnaireno = $this->questionnaireID";

        return $this->db->query($sql)->fetch_object();
    }

    function getRespondantsUniqueContact()
    {
        $sql =
            "SELECT
          DISTINCT caa_contno as contactID,
          CONCAT( con_first_name, ' ', con_last_name ) AS requestContact,
          cus_name AS customer
          FROM
           answer
           JOIN question ON ans_questionno = que_questionno
           JOIN problem ON ans_problemno = pro_problemno
           JOIN callactivity ON caa_problemno = pro_problemno
           JOIN contact ON caa_contno = con_contno
           JOIN customer ON cus_custno = pro_custno
           
          WHERE
           ans_date BETWEEN '$this->startDate' AND '$this->endDate'
           AND que_questionnaireno = $this->questionnaireID
          ORDER BY
           cus_name";

        return $this->db->query($sql);
//          ans_name AS surveyContact,

    }

    function getRespondantsUniqueSurveyContact()
    {
        $sql =
            "SELECT
          ans_name AS surveyContact,
          pro_contno AS contactID,
          CONCAT( con_first_name, ' ', con_last_name ) AS requestContact,
          cus_name AS customer,
          GROUP_CONCAT( DISTINCT pro_problemno ORDER BY pro_problemno SEPARATOR ' ' ) AS srNumbers
         
         FROM
           answer
           JOIN question ON ans_questionno = que_questionno
           JOIN problem ON ans_problemno = pro_problemno
           JOIN contact ON pro_contno = con_contno
           JOIN customer ON cus_custno = pro_custno
           
          WHERE
           ans_date BETWEEN '$this->startDate' AND '$this->endDate'
           AND que_questionnaireno = $this->questionnaireID
           AND cus_mailshot = 'Y'
           AND con_mailflag6 = 'Y'
          
          GROUP BY
            ans_name           ";

        return $this->db->query($sql);

    }

    /**
     * Gets one person who has responded with their name
     * but has not won a prize in the previous 6 months
     *
     * @return databaseRow $databaseRow
     */
    function getPrizewinnerNotLastSixMonths()
    {
        $sql =
            "SELECT
          DISTINCT pro_contno AS contactID,
          CONCAT( con_first_name, ' ', con_last_name ) AS requestName,
          cus_name AS customer
         FROM
           answer
           JOIN question ON ans_questionno = que_questionno
           JOIN problem ON pro_problemno = ans_problemno
           JOIN contact ON con_contno = pro_contno
           JOIN customer ON cus_custno = pro_custno 
          WHERE
           ans_date BETWEEN '$this->startDate' AND '$this->endDate'
           AND que_questionnaireno = $this->questionnaireID
           AND (
              SELECT
                COUNT(*)
              FROM prizewinner
              WHERE prz_contno = pro_contno
                AND prz_yearmonth > DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 6 MONTH), '%Y-%m')
                AND prz_approved_flag = 'Y' 
            ) = 0
           AND ans_name <> ''
          ORDER BY RAND() LIMIT 0,1";

//          ans_name AS surveyName,

        return $this->db->query($sql)->fetch_object();

    }

    function getPeriod()
    {
        return date('d/m/Y', strtotime($this->startDate)) . ' to ' . date('d/m/Y', strtotime($this->endDate));
    }

    function getReport($csv = false)
    {
        global $cfg;

        $template = new Template ($cfg["path_templates"], "remove");

        if ($csv) {
            $template->set_file('page', 'QuestionnaireReportCsv.inc.csv');
        } else {
            $template->set_file('page', 'QuestionnaireReport.inc.html');
        }

        $questionnaire = $this->getQuestionnaire($this->questionnaireID);

        $questionType = 1; // ratings

        $questions = $this->getQuestions($questionType);

        $template->set_block('page', 'ratingsBlock', 'ratings');

        $totalRespondants = 0;

        foreach ($questions as $key => $value) {

            $total = $this->getTotal($value['que_questionno']);

            $template->set_var(
                array(
                    'ratingTotal'         => $total,
                    'questionDescription' => $value['que_desc'],
                    'rating1Percent'      => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      1,
                                                                                      $total)),
                    'rating2Percent'      => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      2,
                                                                                      $total)),
                    'rating3Percent'      => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      3,
                                                                                      $total)),
                    'rating4Percent'      => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      4,
                                                                                      $total)),
                    'rating5Percent'      => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      5,
                                                                                      $total))
                )
            );

            $template->parse('ratings', 'ratingsBlock', true);

            $totalRespondants += $total;

        }

        $questionType = 2; // Yes/No

        $questions = $this->getQuestions($questionType);

        $template->set_block('page', 'yesNoBlock', 'yesNo');

        foreach ($questions as $key => $value) {

            $total = $this->getTotal($value['que_questionno']);

            $template->set_var(
                array(
                    'ynTotal'             => $total,
                    'questionDescription' => $value['que_desc'],
                    'yesPercent'          => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      'Y',
                                                                                      $total)),
                    'noPercent'           => number_format($this->getRatingPercentage($value['que_questionno'],
                                                                                      'N',
                                                                                      $total))
                )
            );

            $template->parse('yesNo', 'yesNoBlock', true);

        }

        $freeText = $this->getAnswers();

        $template->set_block('page', 'textBlock', 'text');

        while ($row = $freeText->fetch_object()) {

            $template->set_var(
                array(
                    'freeTextQuestion' => $row->que_desc,
                    'freeTextAnswer'   => str_replace('\\', '', $row->ans_answer),
                    'freeTextName'     => str_replace('\\', '', $row->ans_name),
                    'freeTextDate'     => str_replace('\\', '', $row->answerDate),
                    'freeTextCustomer' => $row->cus_name
                )
            );

            $template->parse('text', 'textBlock', true);

        }

        $template->set_var(
            array(
                'questionnaireDescription' => $questionnaire->qur_desc,
                'rating1Description'       => $questionnaire->qur_rating_1_desc,
                'rating5Description'       => $questionnaire->qur_rating_5_desc,
                'period'                   => $this->getPeriod()
            )
        );

        $template->parse('output', 'page', true);

        return $template->get_var('output');


    }

    function getRespondantsCsv()
    {
        global $cfg;

        $template = new Template ($cfg["path_templates"], "remove");

        $template->set_file('page', 'QuestionnaireReportRespondants.inc.csv');

        $respondants = $this->getRespondantsUniqueSurveyContact();
        $template->set_block('page', 'rowBlock', 'rows');

        while ($row = $respondants->fetch_object()) {

            $template->set_var(
                array(
                    'customer'       => $row->customer,
                    'requestContact' => $row->requestContact,
                    'surveyContact'  => $row->surveyContact,
                    'srNumbers'      => $row->srNumbers
                )
            );

            $template->parse('rows', 'rowBlock', true);

        }

        $template->parse('output', 'page', true);

        return $template->get_var('output');
    }

    function setPrizewinner()
    {

        $prizewinnner = $this->getPrizewinnerNotLastSixMonths();

        $dbePrizewinner = new DBEPrizewinner($this);
        /*
        Have we already chosen a a winner this month?

        If so, if they have been approved then do nothing more
        If they havent been approved then delete row and assign new winner
        */
        $dbePrizewinner->setValue('yearMonth', '"' . date('Y-m', strtotime('last month')) . '"');

        $dbePrizewinner->getRowsByColumn('yearMonth');

        $createNewWinner = true;

        if ($dbePrizewinner->fetchNext()) {

            $ID = $dbePrizewinner->getPKValue();
            $dbePrizewinner->resetQueryString();

            if ($dbePrizewinner->getValue('approvedFlag') == 'N') {

                $dbePrizewinner->deleteRow();

            } else {

                $createNewWinner = false;

            }

        }

        if ($createNewWinner) {

            /*
            Populate prizewinner table
            */
            $dbePrizewinner->setValue('yearMonth', date('Y-m', strtotime('last month')));
            $dbePrizewinner->setValue('contactID', $prizewinnner->contactID);
//        $dbePrizewinner->setValue( 'surveyName', $prizewinnner->surveyName );
            $dbePrizewinner->setValue('approvedFlag', 'N');

            $dbePrizewinner->insertRow();

            $returnString = $prizewinnner->requestName . ' of ' . $prizewinnner->customer . ' Won this months draw.';

        } else {

            $returnString = 'There is already a prizewinner this month';

        }
        return $returnString;

    }

    function getQuestionnaireDescription()
    {
        return '';
    }


} // End of class
?>
