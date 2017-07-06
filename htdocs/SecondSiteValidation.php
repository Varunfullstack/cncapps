<?php
/**
* Action Alert Email controller
* CNC Ltd
*
*	Sends emails to internal email addresses when future actions on the 
* future_actions table become due.
*
* The rows are then deleted.
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUSecondsite.inc.php');
$buSecondsite = new BUSecondsite($this);

set_time_limit( 0 ); // unlimited execution time

$buSecondsite->validateBackups();

$template = new Template( EMAIL_TEMPLATE_DIR, "remove" );

$template->set_file( 'page', 'secondSiteCompletedEmail.inc.html' );

$template->set_block('page','logBlock', 'logs');

$errorCount = 0;
$successCount = 0;

foreach ( $buSecondsite->log as $logEntry ){

  if ( $logEntry[ 'type' ] == BUSecondsite::LOG_TYPE_SUCCESS ){
    $successCount++;
    continue; // don't report successes in detail
  }
  
  switch( $logEntry[ 'type' ] ){

    case BUSecondsite::LOG_TYPE_ERROR_INCOMPLETE:
      $errorCount++;
      $class = 'incomplete';
      break; 

    case BUSecondsite::LOG_TYPE_ERROR_PATH_MISSING:
      $errorCount++;
      $class = 'pathMissing';
      break; 

    case BUSecondsite::LOG_TYPE_ERROR_NO_IMAGE:
      $errorCount++;
      $class = 'noImage';
      break; 

/* Not required in report
    case BUSecondsite::LOG_TYPE_SUCCESS:
      $successCount++;
      $class = 'success';
      break; 
*/
  }

  $template->set_var(
    array(
      'message'   => $logEntry[ 'message' ],
      'class'     => $class
    )
  );
  $template->parse('logs', 'logBlock', true);
  
} // end foreach

$template->set_block('page','delayedCheckServerBlock', 'delayedServers');

$servers = $buSecondsite->getDelayedCheckServers();

foreach ( $servers as $server ){

  $template->set_var(
    array(
      'customerName'=> $server[ 'cus_name' ],
      'serverName'  => $server[ 'serverName' ],
      'delayDays'   => $server[ 'secondsiteImageDelayDays' ],
      'delayUser'   => $server[ 'delayUser' ],
      'delayDate'   => $server[ 'secondsiteImageDelayDate' ]
    )
  );
  $template->parse('delayedServers', 'delayedCheckServerBlock', true);
  
}

$template->set_block('page','suspendedCheckServerBlock', 'suspendedServers');

$servers = $buSecondsite->getSuspendedCheckServers();

foreach ( $servers as $server ){

  $template->set_var(
    array(
      'customerName'        => $server[ 'cus_name' ],
      'serverName'          => $server[ 'serverName' ],
      'suspendedUntilDate'  => $server[ 'secondsiteValidationSuspendUntilDate' ],
      'suspendUser'         => $server[ 'suspendUser' ],
      'suspendedDate'       => $server[ 'secondsiteSuspendedDate' ]
    )
  );
  $template->parse('suspendedServers', 'suspendedCheckServerBlock', true);
  
}

$template->set_block('page','excludedLocalServerBlock', 'excludedLocalServers');

$servers = $buSecondsite->getExcludedLocalServers();

foreach ( $servers as $server ){

  $template->set_var(
    array(
      'customerName'        => $server[ 'cus_name' ],
      'serverName'          => $server[ 'serverName' ]
    )
  );
  $template->parse('excludedLocalServers', 'excludedLocalServerBlock', true);
  
}

$template->setVar(
  array(
    'serverCount'   => $buSecondsite->serverCount,
    'imageCount'    => $buSecondsite->imageCount,      
    'errorCount'    => $errorCount,      
    'successCount'  => $successCount      
  )
);

$template->parse( 'output', 'page', true );

$html =  $template->get_var( 'output' );

$subject = '2nd Site Validation Completed';

$senderEmail = CONFIG_SUPPORT_EMAIL;
$senderName = 'CNC Support Department';

$toEmail = '2sprocesscompleted@' . CONFIG_PUBLIC_DOMAIN;

$hdrs = array(
  'To'      => $toEmail,
  'From'    => $senderEmail,
  'Subject' => $subject,
  'Date'    => date( "r" )
);

$buMail = new BUMail( $this );

$buMail->mime->setHTMLBody( $html );

$body = $buMail->mime->get();

$hdrs = $buMail->mime->headers( $hdrs );

$buMail->putInQueue(
  $senderEmail,
  $toEmail,
  $hdrs,
  $body,
  true
);

echo $html; // and output to page
?>