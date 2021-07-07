<?php
/**
 * Home controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');

class CTReviewList extends CTCNC
{

    /** @var BUCustomer */
    public $buCustomer;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = ACCOUNT_MANAGEMENT_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(401);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $i = $this->action;
        if ($i == "getData") {
            $this->getData();
        } else {
            $this->displayForm();
        }
    }

        /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function displayForm()
    {
        $this->setPageTitle('Review List');
        $this->setTemplateFiles(
            'ReviewList',
            'ReviewList.inc'
        );
        $this->template->parse(
            'CONTENTS',
            'ReviewList',
            true
        );
        $this->loadReactScript('ReviewListComponent.js');
        $this->loadReactCSS('ReviewListComponent.css'); 
        $this->parsePage();       
    }

    private function getData()
    {
        $baseQuery =
            "select cus_name                                      as customerName,
       reviewAction                                  as reviewAction,
       reviewDate,
       reviewTime,
       reviewUserID,
       consultant.cns_name                           as reviewUserName,
       cus_custno                                    as customerId,
       concat_ws(' ', con_first_name, con_last_name) as contactName,
       con_email                                     as contactEmail,
       coalesce(
               if(con_phone, con_phone, null),
               if(con_mobile_phone, con_mobile_phone, null),
               if(add_phone, add_phone, null)
           ) as contactPhone,
       c.name                                        as leadStatus,
       (select cno_details
        from customernote
        where cno_custno = customer.cus_custno
        order by cno_created desc
        limit 1)                                     as latestUpdate
from customer
         left join consultant on reviewUserID = consultant.cns_consno
         left join contact on cus_custno = contact.con_custno and con_contno =
                                                                  (select min(b.con_contno)
                                                                   from contact b
                                                                   where b.con_custno = cus_custno
                                                                     and active
                                                                  )
         left join address on add_custno = cus_custno and add_siteno = con_siteno
         left join customerleadstatus c on customer.leadStatusId = c.id
where reviewDate IS NOT NULL
  and reviewDate <= CURDATE() ";
        $offset = $_REQUEST['start'];
        $limit = $_REQUEST['length'];

        /** @var dbSweetcode $db */
        global $db;
        $countResult = $db->query($baseQuery);
        $totalCount = $countResult->num_rows;
        $filteredCount = $totalCount;
        $columns = $_REQUEST['columns'];
        $order = @$_REQUEST['order'];
        $orderItems = [];
        foreach ($order as $orderItem) {
            $orderItems[] = mysqli_real_escape_string(
                $db->link_id(),
                "{$columns[$orderItem['column']]['name']} {$orderItem['dir']}"
            );
        }
        if (count($orderItems)) {
            $baseQuery .= " order by " . implode(', ', $orderItems);
        }
        $baseQuery .= " limit ?, ?";
        $parameters[] = ["type" => "i", "value" => $offset];
        $parameters[] = ["type" => "i", "value" => $limit];
        $result = $db->preparedQuery(
            $baseQuery,
            $parameters
        );
        $overtimes = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(
            [
                "draw"            => $_REQUEST['draw'],
                "recordsTotal"    => $totalCount,
                "recordsFiltered" => $filteredCount,
                "data"            => $overtimes
            ]
        );


    }

    /**
     * Displays list of customers to review
     *
     * @throws Exception
     */
    function displayReviewList()
    {

        $this->setMethodName('displayReviewList');

        $this->setTemplateFiles('CustomerReviewList', 'CustomerReviewList.inc');


        $this->template->parse('CONTENTS', 'CustomerReviewList', true);

        $this->parsePage();
    }
}
