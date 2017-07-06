<?php
/**
* Further Action controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once($cfg['path_bu'].'/BUPortalDocument.inc.php');
require_once($cfg['path_dbe'].'/DSForm.inc.php');
// Actions
define('CTPORTALDOCUMENT_ACT_DISPLAY_LIST', 'documentList');
define('CTPORTALDOCUMENT_ACT_ADD',			'add');
define('CTPORTALDOCUMENT_ACT_EDIT',		'edit');
define('CTPORTALDOCUMENT_ACT_DELETE',	'delete');
define('CTPORTALDOCUMENT_ACT_UPDATE',	'update');
class CTPortalDocument extends CTCNC {
	var $dsPortalDocument ='';
	var $buPortalDocument='';
	function CTPortalDocument($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buPortalDocument=new BUPortalDocument($this);
		$this->dsPortalDocument = new DSForm($this);
		$this->dsPortalDocument->copyColumnsFrom($this->buPortalDocument->dbePortalDocument);
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){
			case CTPORTALDOCUMENT_ACT_EDIT:
			case CTPORTALDOCUMENT_ACT_ADD:
				$this->edit();
				break;
			case CTPORTALDOCUMENT_ACT_DELETE:
				$this->delete();
				break;
			case CTPORTALDOCUMENT_ACT_UPDATE:
				$this->update();
				break;
      case 'viewFile':
        $this->viewFile();
        break;
		}
	}
	/**
	* Edit/Add Further Action 
	* @access private
	*/
	function edit()
	{
		$this->setMethodName('edit');
		$dsPortalDocument = &$this->dsPortalDocument; // ref to class var

		if (!$this->getFormError()){
			if ($_REQUEST['action'] == CTPORTALDOCUMENT_ACT_EDIT){
				$this->buPortalDocument->getDocumentByID($_REQUEST['portalDocumentID'], $dsPortalDocument);
				$portalDocumentID = $_REQUEST['portalDocumentID'];
			}
			else{																	// creating new
				$dsPortalDocument->initialise();
				$dsPortalDocument->setValue('portalDocumentID', '0');
				$portalDocumentID = '0';
			}
		}
		else{																		// form validation error
			$dsPortalDocument->initialise();
			$dsPortalDocument->fetchNext();
			$portalDocumentID = $dsPortalDocument->getValue('portalDocumentID');
		}
		if ($_REQUEST['action'] == CTPORTALDOCUMENT_ACT_EDIT && $this->buPortalDocument->canDelete($_REQUEST['portalDocumentID'])){
			$urlDelete =
				$this->buildLink(
					$_SERVER['PHP_SELF'],
					array(
						'action'				=>	CTPORTALDOCUMENT_ACT_DELETE,
						'portalDocumentID'	=>	$portalDocumentID
					)
				);
			$txtDelete = 'Delete';
		}
		else{
			$urlDelete = '';
			$txtDelete = '';
		}
		$urlUpdate =
			$this->buildLink(
				$_SERVER['PHP_SELF'],
				array(
					'action'		=>	CTPORTALDOCUMENT_ACT_UPDATE,
					'portalDocumentID'	=>	$portalDocumentID
				)
			);
		$urlDisplayHeader =
			$this->buildLink(
				'Header.php',
        array()
			);
		$this->setPageTitle('Edit Document');
		$this->setTemplateFiles	(
			array('PortalDocumentEdit' => 'PortalDocumentEdit.inc')
		);
		$this->template->set_var(
			array(
				'portalDocumentID' 	        => $portalDocumentID,
        'filename'                  => Controller::htmlDisplayText($dsPortalDocument->getValue('filename')),
				'description' 							=> Controller::htmlInputText($dsPortalDocument->getValue('description')),
				'descriptionMessage' 				=> Controller::htmlDisplayText($dsPortalDocument->getMessage('description')),
        'mainContactOnlyFlagChecked'=> Controller::htmlChecked($dsPortalDocument->getValue('mainContactOnlyFlag')),
        'mainContactOnlyFlagMessage'         => Controller::htmlDisplayText($dsPortalDocument->getMessage('mainContactOnlyFlag')),
        'requiresAcceptanceFlagChecked'=> Controller::htmlChecked($dsPortalDocument->getValue('requiresAcceptanceFlag')),
        'requiresAcceptanceFlagMessage'         => Controller::htmlDisplayText($dsPortalDocument->getMessage('requiresAcceptanceFlag')),
				'urlUpdate'									=> $urlUpdate,
				'urlDelete' 								=> $urlDelete,
        'urlDisplayHeader'          => $urlDisplayHeader,
				'txtDelete'									=> $txtDelete,
				'urlDisplay'				        => $urlDisplay
			)
		);
		$this->template->parse('CONTENTS', 	'PortalDocumentEdit', true);
		$this->parsePage();
	}// end function editFurther Action()	
  function viewFile()
  {
    // Validation and setting of variables
    $this->setMethodName('viewFile');

    $this->buPortalDocument->getDocumentByID(
      $_REQUEST['portalDocumentID'],
      $dsPortalDocument
    );

    header('Content-type: ' . $dsPortalDocument->getValue('fileMimeType') );
    header('Content-Disposition: attachment; filename="'.$dsPortalDocument->getValue('filename').'"');
    print $dsPortalDocument->getValue('file');

    exit;
  }
	function update()
	{
		$this->setMethodName('update');

		$dsPortalDocument = & $this->dsPortalDocument;
    $this->formError = (!$this->dsPortalDocument->populateFromArray($_REQUEST['portalDocument']));
    /*
    Need a file when creating new
    */
    if ( $_FILES['userfile']['name'] == '' && $this->dsPortalDocument->getValue('portalDocumentID') == '' ){
      $this->setFormErrorMessage('Please enter a file path');
    }
    else{
      /* uploading a file */
      
      if( $_FILES['userfile']['name'] != '' && !is_uploaded_file($_FILES['userfile']['tmp_name'])){
        $this->setFormErrorMessage('Document not loaded - is it bigger than 6 MBytes?');
      }
      
    }

		if ($this->formError){
			if ( $this->dsPortalDocument->getValue('portalDocumentID') == '' ){					// attempt to insert
				$_REQUEST['action'] = CTPORTALDOCUMENT_ACT_EDIT;
			}
			else{
				$_REQUEST['action'] = CTPORTALDOCUMENT_ACT_ADD;
			}
			$this->edit();
			exit;
		}

		$this->buPortalDocument->updateDocument($this->dsPortalDocument, $_FILES[ 'userfile' ]);

		$urlNext =
			$this->buildLink(
			'Header.php',
				array()
			);
		header('Location: ' . $urlNext);
	}
	/**	
	* Delete Further Action 
	*
	* @access private
	* @authors Karim Ahmed - Sweet Code Limited
	*/
	function delete(){
		$this->setMethodName('delete');
		
		$this->buPortalDocument->getDocumentByID($_REQUEST['portalDocumentID'], $dsPortalDocument);
		
		if ( !$this->buPortalDocument->deleteDocument($_REQUEST['portalDocumentID']) ){
			$this->displayFatalError('Cannot delete this document');
			exit;
		}
		else{
			$urlNext =				
				$this->buildLink(
					'Header.php',
					array(
						'action'				=>	CTCNC_ACT_DISP_EDIT
					)
				);
			header('Location: ' . $urlNext);
			exit;
		}
	}
  
}// end of class
?>