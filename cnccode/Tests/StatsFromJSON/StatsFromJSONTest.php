<?php

namespace CNCLTD\StatsFromJSON;

use PHPUnit\Framework\TestCase;

class StatsFromJSONTest extends TestCase
{
    private $data = '[{"priority":1,"sla":2,"fixSLA":12,"raised":null,"FIXED":null,"responseTime":null,"slaMet":null,"slaMetRaw":null,"fixSLAFailedPct":null,"fixSLAFailedCount":null,"overFixSLAWorkingHours":null,"closedWithin8Hours":null,"reopened":null,"reopenedCount":null,"avgChargeableTime":null,"avgTimeAwaitingCNC":null,"avgTimeFromRaiseToFixHours":null},{"priority":2,"sla":4,"fixSLA":16,"raised":2,"FIXED":2,"responseTime":1.385,"slaMet":1,"slaMetRaw":2,"fixSLAFailedPct":0.5,"fixSLAFailedCount":1,"overFixSLAWorkingHours":2,"closedWithin8Hours":0,"reopened":0,"reopenedCount":0,"avgChargeableTime":0.34,"avgTimeAwaitingCNC":19.765,"avgTimeFromRaiseToFixHours":22.16},{"priority":3,"sla":8,"fixSLA":24,"raised":5,"FIXED":5,"responseTime":2.044,"slaMet":1,"slaMetRaw":5,"fixSLAFailedPct":0.2,"fixSLAFailedCount":1,"overFixSLAWorkingHours":2,"closedWithin8Hours":0.6,"reopened":0,"reopenedCount":0,"avgChargeableTime":0.256,"avgTimeAwaitingCNC":10.342,"avgTimeFromRaiseToFixHours":14.36},{"priority":4,"sla":16,"fixSLA":48,"raised":7,"FIXED":6,"responseTime":7.335714,"slaMet":1,"slaMetRaw":6,"fixSLAFailedPct":0.1667,"fixSLAFailedCount":1,"overFixSLAWorkingHours":4,"closedWithin8Hours":0,"reopened":0,"reopenedCount":0,"avgChargeableTime":0.701667,"avgTimeAwaitingCNC":29.791667,"avgTimeFromRaiseToFixHours":33.38}]';

    function testDataIsTransformedInTheExpectedWay()
    {

        $stats  = new StatsFromJSON();
        $result = $stats->__invoke($this->data, true);
        self::assertEquals(
            [
                new ResponseSLAStat(2, 4, 8, 16),
                new TotalServiceRequestsRaisedStat(null, 2, 5, 7),
                new AverageResponseTimeStat(null, 1.385, 2.044, 7.3357140000000003),
                new SlaMetStat(null, 1, 1, 1),
                new FixSLAStat(12, 16, 24, 48, true),
                new AverageTimeAwaitingCNCStat(null, 19.765000000000001, 10.342000000000001, 29.791667),
            ],
            $result
        );
    }
}
