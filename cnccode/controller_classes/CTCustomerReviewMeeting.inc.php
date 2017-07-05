<?php
/**
 * Customer Review Meeting Controller Class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ['path_ct'] . '/CTCNC.inc.php');
require_once ($cfg ['path_bu'] . '/BUCustomerReviewMeeting.inc.php');
require_once ($cfg ['path_bu'] . '/BUCustomerNew.inc.php');
require_once ($cfg ['path_bu'] . '/BUContact.inc.php');
require_once ($cfg ['path_bu'] . '/BUServiceDeskReport.inc.php');
require_once ($cfg ['path_bu'] . '/BUCustomerSrAnalysisReport.inc.php');
require_once ($cfg ['path_bu'] . '/BUCustomerItem.inc.php');
require_once ($cfg ['path_bu'] . '/BUActivity.inc.php');
require_once ($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTCustomerReviewMeeting extends CTCNC {
	
	function CTCustomerReviewMeeting($requestMethod, $postVars, $getVars, $cookieVars, $cfg) {
		$this->constructor ( $requestMethod, $postVars, $getVars, $cookieVars, $cfg );
	}
	function constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg) {
		parent::constructor ( $requestMethod, $postVars, $getVars, $cookieVars, $cfg, "", "", "", "" );
		$this->buCustomerReviewMeeting = new BUCustomerReviewMeeting ( $this );
	}
	/**
	 * Route to function based upon action passed
	 */
	function defaultAction() {
      switch ($_REQUEST['action']){
      
        case 'generatePdf':
          $this->generatePdf();
          break;
        
        default:
          $this->search();
          break;
      }
	}
	function search() {
		global $cfg;
    
		$this->setMethodName ( 'search' );
		
    $dsSearchForm = new DSForm ( $this );
    $dsResults = new DataSet ( $this );

		$this->buCustomerReviewMeeting->initialiseSearchForm ( $dsSearchForm );

    $this->setTemplateFiles ( array ('CustomerReviewMeeting' => 'CustomerReviewMeeting.inc' ) );

    if ( isset( $_REQUEST ['searchForm'] ) ) {
			
			if (!$dsSearchForm->populateFromArray ( $_REQUEST ['searchForm'] )) {
				$this->setFormErrorOn ();
			}
      else{
        /*
        generate default contents of edit box
        
        */
        $buCustomerItem = new BUCustomerItem( $this );
        
        $buCustomer = new BUCustomer( $this );
        
        $buActivity = new BUActivity( $this );

        $buServiceDeskReport = new BUServiceDeskReport( $this );

        $buCustomerSrAnalysisReport = new BUCustomerSrAnalysisReport( $this );

        $buContact = new BUContact( $this );

        $buCustomer->getCustomerByID( $dsSearchForm->getValue( 'customerID' ), $dsCustomer );
        
        $textTemplate = new Template ( $GLOBALS ["cfg"] ["path_templates"], "remove" );

        $textTemplate->set_file ( 'page', 'CustomerReviewMeetingText.inc.html' );
        
        $textTemplate->set_var(
          array(
            'customerName'   => $dsCustomer->getValue( 'name' ),
            'meetingDate'    => self::dateYMDtoDMY( $dsSearchForm->getValue( 'meetingDate' ) ),
            'slaP1'         => $dsCustomer->getValue( 'slaP1' ),
            'slaP2'         => $dsCustomer->getValue( 'slaP2' ),
            'slaP3'         => $dsCustomer->getValue( 'slaP3' ),
            'slaP4'         => $dsCustomer->getValue( 'slaP4' ),
            'slaP5'         => $dsCustomer->getValue( 'slaP5' )
          )
        );
        /*
        Support contacts
        */
        $textTemplate->set_block( 'page',  'supportContactBlock', 'supportContacts' );

        $buContact->getSupportContacts( $dsSupportContact, $dsSearchForm->getValue( 'customerID' ) );
        
        
        while ( $dsSupportContact->fetchNext() ){
          
          $textTemplate->set_var(
            array(
              'supportContactName'  => $dsSupportContact->getValue( 'firstName' ). ' ' . $dsSupportContact->getValue( 'lastName' )
            )
          );

          $textTemplate->parse('supportContacts', 'supportContactBlock', true);
        }
        /*
        End support contacts
        */
        /*
        SR Performance Statistics
        */
        $textTemplate->set_block( 'page',  'srStatsBlock', 'stats' );

        $results = $buCustomerSrAnalysisReport->getResultsByPeriodRange(
          $dsSearchForm->getValue( 'customerID' ),
          $dsSearchForm->getValue( 'startYearMonth' ),
          $dsSearchForm->getValue( 'endYearMonth' )
        );
        
        
        foreach ( $results as $key => $row ){
          
          $textTemplate->set_var(
            array(
              'monthName'             => $row[ 'monthName' ],
              'year'                  => $row[ 'year' ],
              'period'                => $row[ 'period'],
              'scP1to3Count'          => $row[ 'serverCareCount1And3'],
              'scP1to3ResponseHours'  => number_format( $row[ 'serverCareHoursResponded'], 1),
              'scP4Count'             => $row[ 'serverCareCount4'],
              'sdP1to3Count'          => $row[ 'serviceDeskCount1And3'] + $row[ 'prepayCount1And3'],
              'sdP1to3ResponseHours'  => number_format( $row[ 'serviceDeskHoursResponded'] + $row[ 'prepayHoursResponded'], 1),
              'sdP4Count'             => $row[ 'serviceDeskCount4'] + $row[ 'prepayCount4'],
              'otherP1to3Count'       => $row[ 'otherCount1And3'],
              'otherP1to3ResponseHours' => number_format( $row[ 'otherHoursResponded'], 1),
              'otherP1to3FixHours'    => number_format($row[ 'otherHoursFix'],1),
              'otherP4Count'          => $row[ 'otherCount4'],
              'totalP1to3Count'       => $row[ 'otherCount1And3'] + $row[ 'serviceDeskCount1And3'] + $row[ 'serverCareCount1And3'],
              'totalP4Count'          => $row[ 'otherCount4'] + $row[ 'serviceDeskCount4'] + $row[ 'serverCareCount4']
            )
          );

          $textTemplate->parse('stats', 'srStatsBlock', true);
        }
        /*
        End SR Performance Statistics
        */
        $textTemplate->set_block( 'page',  'serverBlock', 'servers' );

        $buCustomerItem->getServersByCustomerID( $dsSearchForm->getValue( 'customerID'), $dsServer );
                
        while( $dsServer->fetchNext() ){
          
          if( $dsServer->getValue( 'sOrderDate' ) != '0000-00-00' ){
            $purchaseDate = self::dateYMDtoDMY( $dsServer->getValue( 'sOrderDate' ) );
          }
          else{
            $purchaseDate = '';
          }
          
          $textTemplate->set_var(
            array(
              'itemDescription'   => $dsServer->getValue( 'itemDescription' ),
              'serialNo'          => $dsServer->getValue( 'serialNo' ),
              'serverName'        => $dsServer->getValue( 'serverName' ),
              'purchaseDate'      => $purchaseDate,
            )
          );
          
          $textTemplate->parse('servers', 'serverBlock', true);

        } // end while        

        $textTemplate->set_block( 'page',  'managementReviewBlock', 'reviews' );

        $buActivity->getManagementReviewsInPeriod(
          $dsSearchForm->getValue( 'customerID'),
          $dsSearchForm->getValue( 'startYearMonth'),
          $dsSearchForm->getValue( 'endYearMonth'),
          $dsReviews
        );

        $itemNo = 0;
        
        while( $dsReviews->fetchNext() ){
          
          $itemNo++;
          
          $urlServiceRequest =
            $this->buildLink(
              'Activity.php',
              array(
                'action'     =>  'displayLastActivity',
                'problemID'  =>  $dsReviews->getValue( 'problemID' )
              )
             );
          
          $textTemplate->set_var(
            array(
              'reviewHeading'          => 'Review Item ' . $itemNo . '. SR no ' . $dsReviews->getValue( 'problemID' ),
              'urlServiceRequest'      => $urlServiceRequest,
              'managementReviewText'   => $dsReviews->getValue( 'managementReviewReason' ),
            )
          );
          
          $textTemplate->parse('reviews', 'managementReviewBlock', true);

        } // end while     
        
        $buServiceDeskReport->setStartPeriod( $dsSearchForm->getValue( 'startYearMonth') );
        $buServiceDeskReport->setEndPeriod( $dsSearchForm->getValue( 'endYearMonth') );
        $buServiceDeskReport->customerID = $dsSearchForm->getValue( 'customerID' );

        $srCountByUser = $buServiceDeskReport->getIncidentsGroupedByUser();
        
        $textTemplate->set_block( 'page',  'userBlock', 'users' );

        while ( $row = $srCountByUser->fetch_object() ) {

          $textTemplate->set_var(
            array(
              'srUserName'   => $row->name,
              'srCount'      => $row->count
            )
          );
          
          $textTemplate->parse ( 'users', 'userBlock', true );
        }
        
        $srCountByRootCause = $buServiceDeskReport->getIncidentsGroupedByRootCause();
        
        $textTemplate->set_block( 'page',  'rootCauseBlock', 'rootCauses' );

        while ( $row = $srCountByRootCause->fetch_object() ) {

          $textTemplate->set_var(
            array(
              'srRootCauseDescription'  => $row->rootCauseDescription,
              'srCount'                 => $row->count
            )
          );
          
          $textTemplate->parse ( 'rootCauses', 'rootCauseBlock', true );
          
        }
     

        $textTemplate->parse( 'output', 'page', true );

        $meetingText =  $textTemplate->get_var( 'output' );        
      }
	
		}
    else{
      if( $_REQUEST[ 'customerID' ] ){
        $dsSearchForm->setValue( 'customerID', $_REQUEST[ 'customerID' ] );
        $dsSearchForm->setValue( 'startYearMonth', $_REQUEST[ 'startYearMonth' ] );
        $dsSearchForm->setValue( 'endYearMonth', $_REQUEST[ 'endYearMonth' ] );
        $dsSearchForm->setValue( 'meetingDate', $_REQUEST[ 'meetingDateYmd' ] );
        $meetingText = $_REQUEST[ 'meetingText' ];
      }
    }

    $urlCustomerPopup = $this->buildLink ( CTCNC_PAGE_CUSTOMER, array ('action' => CTCNC_ACT_DISP_CUST_POPUP, 'htmlFmt' => CT_HTML_FMT_POPUP ) );
    
    $urlSubmit = $this->buildLink ( $_SERVER ['PHP_SELF'], array ('action' => CTCNC_ACT_SEARCH ) );
    
    $urlGeneratePdf =
      $this->buildLink (
        $_SERVER ['PHP_SELF'],
        array (
          'action' => 'generatePdf'
        )
      );

    $this->setPageTitle ( 'Customer Review Meeting' );
    
    if ($dsSearchForm->getValue ( 'customerID' ) != 0) {
      $buCustomer = new BUCustomer ( $this );
      $buCustomer->getCustomerByID ( $dsSearchForm->getValue ( 'customerID' ), $dsCustomer );
      $customerString = $dsCustomer->getValue ( 'name' );
    }
    
    $this->template->set_var (
      array (
        'customerID'          => $dsSearchForm->getValue ( 'customerID' ),
        'customerIDMessage'   => $dsSearchForm->getMessage ( 'customerID' ),
        'customerString'      => $customerString,
        'startYearMonth'      => $dsSearchForm->getValue('startYearMonth'),
        'startYearMonthMessage'  => $dsSearchForm->getMessage('startYearMonth'),
        'endYearMonth'        => $dsSearchForm->getValue('endYearMonth'),
        'endYearMonthMessage' => $dsSearchForm->getMessage('endYearMonth'),
        'meetingDate'         => self::dateYMDtoDMY( $dsSearchForm->getValue('meetingDate') ),
        'meetingDateYmd'      => $dsSearchForm->getValue('meetingDate') ,
        'urlCustomerPopup'    => $urlCustomerPopup,
        'meetingText'         => $meetingText,
        'urlSubmit'           => $urlSubmit,
        'urlGeneratePdf'      => $urlGeneratePdf,
        )
      );
    
    $this->template->parse ( 'CONTENTS', 'CustomerReviewMeeting', true );
    $this->parsePage ();
	}
  /**
  * Create PDF reports and save to disk
  * 
  */
  
  function generatePdf() {
    
    $this->buCustomerReviewMeeting->generateAgendaPdf(
      $_REQUEST[ 'customerID' ],
      $_REQUEST[ 'meetingText' ],
      $_REQUEST[ 'meetingDateYmd' ]
    );
    
    $this->buCustomerReviewMeeting->generateSalesPdf(
      $_REQUEST[ 'customerID' ],
      $_REQUEST[ 'startYearMonth' ],
      $_REQUEST[ 'endYearMonth' ],
      $_REQUEST[ 'meetingDateYmd' ]
    );
  
    $this->search();  // redisplays text
    
  }
  
} // end of class
?>