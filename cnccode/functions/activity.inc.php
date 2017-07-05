<?php
require_once($cfg["path_dbe"]."/DBEItem.inc.php");

/*=========================================================================
Functions that relate to call activities
===========================================================================*/
  /*
    Calculate hours and rates of pay
  */
  function getRatesAndHours(
//    &$dbeJCallActivity,
    $date,
    $startTime,
    $endTime,
    $minHours,              // if zero then no min
    $maxHours,              // if zero then no max
    $oohMultiplier,          // out of hours multiplier
    $itemID,                // activity type itemID
    $underContractFlag,
    &$dsHeader,
    &$normalHours,
    &$hoursBeforeWork,
    &$hoursAfterWork,
    &$overtimeRate,
    &$normalRate,
    $otFlag = 'Y'
  ){
    // get activity times as decimals
    $activityStartTime = common_convertHHMMToDecimal($startTime);
    $activityEndTime = common_convertHHMMToDecimal($endTime);

    // get system office hours as decimals
    $officeStartTime = common_convertHHMMToDecimal($dsHeader->getValue('billingStartTime'));
    $officeEndTime = common_convertHHMMToDecimal($dsHeader->getValue('billingEndTime'));

    // in hours rate for activity type (from attached item row)
    $dbeItem = new DBEItem($this);
    $dbeItem->getRow($itemID);

    $normalRate = $dbeItem->getValue('curUnitSale');                // Normal, in office hours rate

/* remove as per request from GL on 30/4/2008

    // Contract rate is ?65 ph
    if (  $underContractFlag == 'Y' ) {
      if ($normalRate != 0){          // FOC remains FOC!
        $normalRate = 65;
      }
    }
*/
    // apply out-of-hours multiplier to get overtime rate
    $overtimeRate = $normalRate * $oohMultiplier;

    $activityEndTime = getCalculationEndTime($activityStartTime, $activityEndTime, $minHours, $maxHours);

    $totalHours = $activityEndTime - $activityStartTime;

    
    // weekend days are counted as out-of-hours so simply set hoursAfterWork and return
    $weekDayNo = date('w', strtotime($date));
    if ( ($weekDayNo == 0) OR ($weekDayNo == 6) ){
      $hoursBeforeWork = 0;
      $normalHours = 0;
      $hoursAfterWork = $totalHours;
      return;
    }

    // Hours before office start time
    if ($activityStartTime < $officeStartTime) {
      $useStartTime = $activityStartTime;
      if ($activityEndTime > $officeStartTime) {
        $useEndTime = $officeStartTime;
      }
      else{
        $useEndTime = $activityEndTime;
      }
      $hoursBeforeWork = $useEndTime - $useStartTime;
    }
    else{
      $hoursBeforeWork = 0;
    }
    // Hours during office hours
    if ( ($activityStartTime < $officeEndTime) AND ($activityEndTime >= $officeStartTime) ){
      if ( $activityStartTime > $officeStartTime ){
        $useStartTime = $activityStartTime;                                                      
      }
      else{
        $useStartTime = $officeStartTime;
      }
      if ($activityEndTime < $officeEndTime) {
        $useEndTime = $activityEndTime;
      }
      else{
        $useEndTime = $officeEndTime;
      }
      # Work during hours
      $normalHours = $useEndTime - $useStartTime;
    }
    else{
      $normalHours = 0;
    }
    // Hours after office hours
    if ($activityEndTime > $officeEndTime) {
      $useEndTime = $activityEndTime;
      if ($activityStartTime < $officeEndTime) {
        $useStartTime = $officeEndTime;
      }
      else{
        $useStartTime = $activityStartTime;  
      }
      $hoursAfterWork = $useEndTime - $useStartTime;
    }
    else{
      $hoursAfterWork = 0;
    }
    return;
  } // end function

  function getCalculationEndTime(
    $activityStartTime,
    $activityEndTime,
    $minHours,
    $maxHours
  ){
  
    $decimalTime = $activityEndTime - $activityStartTime;

    $ret = $activityEndTime;  // default
        
    if ( ($minHours != 0) && ($decimalTime < $minHours)) {
      $ret = $activityStartTime + $minHours;
    }
    if ( ($maxHours != 0) && ($decimalTime > $maxHours)) {
      $ret = $activityStartTime + $maxHours;
    }
    
    return $ret;
    
  }
  function limitTime($decimalTime, $minHours, $maxHours){
    $ret = $decimalTime;
    if ( ($minHours != 0) && ($decimalTime < $minHours)) {
      $ret = $minHours;
    }
    if ( ($maxHours != 0) && ($decimalTime > $maxHours)) {
      $ret = $maxHours;
    }
    return $ret;
  }  
?>
