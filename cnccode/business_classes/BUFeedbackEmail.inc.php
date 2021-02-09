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
        $sql ="SELECT 
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
        cal.caa_consno,
        cons.`cns_name` cons_name,
        CONCAT(cons.cns_logname,'@cnc-ltd.co.uk') cons_email,
        teamLeader.`cns_name` leader_name,
        CONCAT(teamLeader.cns_logname,'@cnc-ltd.co.uk') leader_email            
         FROM `customerfeedback` f 
         JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
         JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId
         JOIN `consultant`  cons ON cons.`cns_consno`=cal.`caa_consno`
         JOIN team ON team.`teamID` = cons.teamID
         JOIN `consultant`  teamLeader ON teamLeader.`cns_consno`=team.`leaderId`
        JOIN customer ON customer.`cus_custno`=problem.`pro_custno`
         WHERE cal.caa_callacttypeno=51
         AND f.notified=0         
        ";

        return $this->db->query($sql);

    }

    function sendEmail()
    {
        $buMail = new BUMail($this);        
        $results = $this->getFeedback();
        if ($row = $results->fetch_object()) {            
            do {

                $urlService = SITE_URL. '/SRActivity.php?action=displayActivity&callActivityID=' . $row->serviceRequestId;
                global $twig;
                $subject="You've just had feedback for SR".$row->serviceRequestId." for customer ".$row->customer;
                $body = $twig->render(
                    '@internal/feedbackEmail.html.twig',
                    [
                        'customer'   => $row->customer,
                        'urlService' => $urlService,
                        'rate'       => $row->rate,
                        'comments'   => $row->comments,
                        'serviceRequestId'=> $row->serviceRequestId,
                    ]
                );
                $buMail->sendSimpleEmail($body, $subject, $row->cons_email,CONFIG_SUPPORT_EMAIL,[ $row->leader_email]);                
                // mark it as notified 
                DBConnect::execute("update customerfeedback set notified=1 where id=:id",["id"=>$row->id]);
            } while ($row = $results->fetch_object()); 
        }
    }
}
