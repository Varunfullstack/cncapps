<?php
/**
 * Sales Service Request Alert Emails
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_dbe"] . "/DBConnect.php");

class BUFeedbackEmail extends Business
{
    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function getFeedback()
    {
        $sql = "SELECT 
        f.id,
        f.serviceRequestId,
        f.comments,
        f.value,
        (
        CASE 
            WHEN  f.value=1 THEN 'Happy'
            WHEN  f.value=2 THEN 'Average'
            ELSE 'Unhappy'
        END
        ) AS rate,
        problem.`pro_custno`,
        customer.`cus_name` customer,
        fixed.caa_consno,
        cons.`cns_name` cons_name,
        CONCAT(cons.cns_logname,'@cnc-ltd.co.uk') cons_email,
        teamLeader.`cns_name` leader_name,
        CONCAT(teamLeader.cns_logname,'@cnc-ltd.co.uk') leader_email,
        concat(contact.con_first_name, ' ', contact.con_last_name) as contactName
         FROM `customerfeedback` f 
         JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
         JOIN callactivity `fixed`  ON fixed.caa_problemno = f.serviceRequestId AND fixed.caa_callacttypeno = 57
         JOIN `consultant`  cons ON cons.`cns_consno`= fixed.`caa_consno`
         JOIN team ON team.`teamID` = cons.teamID
         JOIN `consultant`  teamLeader ON teamLeader.`cns_consno`=team.`leaderId`
         JOIN customer ON customer.`cus_custno` = problem.`pro_custno`
        join contact on  contact.con_contno = cal.caa_contno
WHERE  f.notified = 0 
        ";
        return $this->db->query($sql);

    }

    function sendEmail()
    {
        $buMail  = new BUMail($this);
        $results = $this->getFeedback();
        if ($row = $results->fetch_object()) {
            do {

                $urlService = SITE_URL . '/SRActivity.php?action=displayActivity&serviceRequestId=' . $row->serviceRequestId;
                global $twig;
                $subject = "You've just had feedback for SR" . $row->serviceRequestId . " for customer " . $row->customer;
                $body    = $twig->render(
                    '@internal/feedbackEmail.html.twig',
                    [
                        'customer'         => $row->customer,
                        'urlService'       => $urlService,
                        'rate'             => $row->rate,
                        'comments'         => $row->comments,
                        'serviceRequestId' => $row->serviceRequestId,
                        'contactName'      => $row->contactName
                    ]
                );
                $buMail->sendSimpleEmail($body, $subject, $row->cons_email, CONFIG_SUPPORT_EMAIL, [$row->leader_email]);
                // mark it as notified 
                DBConnect::execute("update customerfeedback set notified=1 where id=:id", ["id" => $row->id]);
            } while ($row = $results->fetch_object());
        }
    }
}
