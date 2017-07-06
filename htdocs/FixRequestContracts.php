<?php
/**
Script to set missing contracts on Request Header(problem) table
*/
require_once("config.inc.php");
GLOBAL $cfg;
page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
		'auth' => PHPLIB_CLASSNAME_AUTH,
		'',
		''
	)
);
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

$buActivity = new BUActivity( $this );

$dbSelect = new dbSweetcode ( );        // database connection for select
$dbUpdate = new dbSweetcode ( ); // database connection for update

$sql =
  "SELECT
    pro_problemno
  FROM
    problem
  WHERE
    pro_monitor_agent_name IS NOT NULL AND pro_monitor_agent_name <> ''";

$dbSelect->query( $sql );

while ( $dbSelect->next_record() ) {
  $requests[] = $dbSelect->Record[ 'pro_problemno' ];
}
foreach( $requests as $key => $requestID ){
  echo "Request " . $requestID . "<BR/>";
  if ( $contractNo = $buActivity->getServiceRequestContractID( $requestID ) ){
    $sql =
      "UPDATE
        problem
      SET
        pro_contract_cuino = $contractNo,
        pro_rootcauseno = 55
      WHERE
        pro_problemno = " . $requestID;
    $dbUpdate->query( $sql );
    echo "Updated " . $requestID . " to contract " . $contractNo . "<BR/>";
  }
}
echo "Done Requests";


page_close();
?>