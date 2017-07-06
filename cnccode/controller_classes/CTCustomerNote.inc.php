<?php
/**
* Customer controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_bu'].'/BUCustomerNote.inc.php');
require_once($cfg['path_ct'].'/CTCNC.inc.php');

class CTCustomerNote extends CTCNC{

	function CTCustomerNote($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){

		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){

			case 'getCustomerNote':
				$this->getCustomerNote();
				break;

      case 'updateNote':
        $this->updateNote();
        break;
		
      case 'customerNotePopup':
        $this->customerNotePopup();
        break;
      case 'customerNoteHistoryPopup':
        $this->customerNoteHistoryPopup();
        break;
      
      case 'deleteCustomerNote':
        $this->deleteCustomerNote();
        break;
    
    	default:
				
				break;

    }
	}
  function getCustomerNote(){

    if ( !$_REQUEST[ 'identifier'] ){
      $this->raiseError( 'No identifier Passed');
    }
    if ( !$_REQUEST[ 'customerID'] ){
      $this->raiseError( 'No customerID Passed');
    }
    
    $buCustomerNote = new BUCustomerNote( $this );

    if ( $record = $buCustomerNote->getNote(
      $_REQUEST['customerID'],
      $_REQUEST['created'],
      $_REQUEST['identifier']
    )){
    
      $noteHistory = $this->getTextStringOfHistory( $record->cno_custno );    
        
      echo  $this->createReturnJavascriptString( $record, $noteHistory );
    }
  }          

  function deleteCustomerNote(){

    if ( !$_REQUEST[ 'customerNoteID'] ){
      $this->raiseError( 'No customerNoteID Passed');
    }
    
    $buCustomerNote = new BUCustomerNote( $this );

    if ( $record = $buCustomerNote->deleteNote(
      $_REQUEST['customerNoteID']
    )){
    
      $noteHistory = $this->getTextStringOfHistory( $record->cno_custno );    

      echo  $this->createReturnJavascriptString( $record, $noteHistory );
      
    }
  }          

  
  function updateNote(){

    if ( !$_REQUEST[ 'customerID'] ){
      $this->raiseError( 'No customerID Passed');
    }

    $buCustomerNote = new BUCustomerNote( $this );

    if ( $record = $buCustomerNote->updateNote(
      $_REQUEST['customerID'],
      $_REQUEST['customerNoteID'],
      $_REQUEST['details'],
      $_REQUEST['ordheadID']
    )){
      
      $noteHistory = $this->getTextStringOfHistory( $record->cno_custno );    

      echo  $this->createReturnJavascriptString( $record, $noteHistory );
    }

  }
  /**
  * Form to create a new customer note
  * 
  */
  function customerNotePopup()
  {
    $this->setTemplateFiles('CustomerNotePopup',  'CustomerNotePopup.inc');

    $this->pageTitle = 'Customer Note';

    $buCustomerNote = new BUCustomerNote( $this );
    
    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){

      if ( !$_REQUEST[ 'customerID'] ){
        $this->raiseError( 'No customerID Passed');
      }

      if ( !$_REQUEST[ 'details'] ){
        $this->raiseError( 'No details Passed');
      }
      
      $buCustomerNote = new BUCustomerNote( $this );

      $record =
        $buCustomerNote->updateNote(
          $_REQUEST['customerID'],
          $_REQUEST['customerNoteID'],
          $_REQUEST['details'],
          $_REQUEST['ordheadID']
        );
      
      echo '<script language="javascript">window.close()</script>;';
      
    }
    else{
        if ( $_REQUEST ['customerID'] ){
          $record =
            $buCustomerNote->getNote(
              $_REQUEST ['customerID'],
              false,
              'salesOrder',
              false,
              $_REQUEST ['ordheadID']
            );
          if ( $record ){
            $_REQUEST['customerID'] = $record->cno_custno; 
            $_REQUEST['customerNoteID'] = $record->cno_customernoteno; 
            $_REQUEST['ordheadID'] = $record->cno_ordno; 
            $_REQUEST['details'] = $record->cno_details; 
          }
        }
    }
    

    $urlSubmit =
      $this->buildLink(
        $_SERVER['PHP_SELF'],
        array(
          'action' => 'customerNotePopup'
        )
      );

    $this->template->set_var(
      array(
        'customerID' => $_REQUEST['customerID'],
        'ordheadID' => $_REQUEST['ordheadID'],
        'customerNoteID' => $_REQUEST['customerNoteID'],
        'details'   => $_REQUEST['details'],
        'urlSubmit' => $urlSubmit
      )
    );
    $this->template->parse('CONTENTS',   'CustomerNotePopup', true);
    $this->parsePage();
    
  }
  function createReturnJavascriptString( $record, $history )
  {
      $details = str_replace(array("\r", "\n"), array('\r', '\n'), $record->cno_details);
      $details = addcslashes($details, "'\"");
      
      $history = str_replace(array("\r", "\n"), array('\r', '\n'), $history);
      $history = addcslashes($history, "'\"");

      $javascript = '
        var im = document.getElementById(\'customerNoteDetails\');
        im.value = "' . $details . '";
        var im = document.getElementById(\'customerNoteHistory\');
        im.value = "' . $history . '";
        var im = document.getElementById(\'customerNoteCreated\');
        im.value = "' . $record->cno_created . '";
        var im = document.getElementById(\'customerNoteModified\');
        im.value = "' . $record->cno_modified . '";
        var im = document.getElementById(\'customerNoteModifiedText\');
        im.innerHTML = "' . Controller::dateYMDtoDMY($record->cno_modified) . ' by ' . $record->cns_logname . '";
        var im = document.getElementById(\'customerNoteID\');
        im.value = "' . $record->cno_customernoteno . '";
        var im = document.getElementById(\'customerNoteOrdheadID\');
        im.value = "' . $record->cno_ordno . '";';
  
  /*
        im.value = "' . $history . '";
  */
        return $javascript;
  }
  function customerNoteHistoryPopup(  )
  {
      $returnOutput = false;
      
      if ( !$_REQUEST[ 'customerID'] ){
        $this->raiseError( 'No customerID Passed');
      }
      else{
        $customerID = $_REQUEST['customerID'];
      }
      
      $this->setTemplateFiles  ('CustomerNoteHistoryPopup',  'CustomerNoteHistoryPopup.inc');

      $buCustomerNote = new BUCustomerNote( $this );

      if ( $results = $buCustomerNote->getNotesByCustomerID( $customerID ) ){
      
        $this->template->set_block('CustomerNoteHistoryPopup','notesBlock', 'rows');

        while ( $row = $results->fetch_object() ) {

          $this->template->set_var(
            array(
              'details'       => Controller::formatForHTML($row->cno_details),
              'date'          => Controller::dateYMDtoDMY($row->cno_modified),
              'name'          => $row->cns_name
            )
          );

          $this->template->parse('rows', 'notesBlock', true);

        }
        
        $this->template->parse('CONTENTS',   'CustomerNoteHistoryPopup', true);

        $this->parsePage();
          
      }
      
      exit;
    }
    function getTextStringOfHistory( $customerID ){

      $buCustomerNote = new BUCustomerNote( $this );
      
      if ( $results = $buCustomerNote->getNotesByCustomerID( $customerID ) ){
      
        $returnString = '';
        
        while ( $row = $results->fetch_object() ) {                     
        
          if ( $returnString != '' ){
            $returnString .= "\\n\\n";
          }
          if ( substr($row->cno_modified,0,10) != '2010-09-28' ){
            $returnString .=
              Controller::dateYMDtoDMY($row->cno_modified) . ' - ' . $row->cns_name . " ####################################################################\\n\\n";
          }
          
          $returnString .= $row->cno_details;
          
        }
        
        return $returnString;
      }              
    
    }

}// end of class
?>