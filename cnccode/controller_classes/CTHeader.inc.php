<?php
/**
 * System header controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPortalDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define(
    'CTHEADER_ACT_EDIT',
    'editHeader'
);
define(
    'CTHEADER_ACT_UPDATE',
    'updateHeader'
);

class CTHeader extends CTCNC
{
    const GET_PRIORITIES_DESCRIPTIONS = 'getPrioritiesDescriptions';
    const GET_NUMBER_ALLOWED_MISTAKES = "numberOfAllwoedMistaks";
    const KEYWORD_MATCHING_PERCENT    = "keywordMatchingPercent";
    const CONST_HEADER                = "header";
    const CONST_PORTAL_DOCUMENTS      = "portalDocument";
    /** @var DSForm */
    public $dsHeader;
    /** @var BUHeader */
    public $buHeader;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $this->setMenuId(901);
        $this->buHeader = new BUHeader($this);
        $this->dsHeader = new DSForm($this);
        $this->dsHeader->copyColumnsFrom($this->buHeader->dbeJHeader);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::CONST_HEADER:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getHeader(), JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        $this->checkPermissions(MAINTENANCE_PERMISSION);
                        $roles = SENIOR_MANAGEMENT_PERMISSION;
                        if (!self::hasPermissions($roles)) {
                            Header("Location: /NotAllowed.php");
                            exit;
                        }
                        echo json_encode($this->updateHeader(), JSON_NUMERIC_CHECK);
                        break;
                }
                exit;
            case self::GET_PRIORITIES_DESCRIPTIONS:
            {
                $this->getPrioritiesDescriptions();
                exit;
            }
            case self::GET_NUMBER_ALLOWED_MISTAKES:
                echo json_encode($this->getNumberAllwoedMistakes());
                exit;
            case self::KEYWORD_MATCHING_PERCENT:
                echo json_encode($this->getKeywordMatchingPercent());
                exit;
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $roles = SENIOR_MANAGEMENT_PERMISSION;
                if (!self::hasPermissions($roles)) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $this->initTemplate();
                break;
        }
    }

    function initTemplate()
    {
        $this->setPageTitle('Edit Header');
        $this->setTemplateFiles(
            array('HeaderEdit' => 'HeaderEdit.inc')
        );
        $this->template->parse(
            'CONTENTS',
            'HeaderEdit',
            true
        );
        $this->loadReactScript('HeaderComponent.js');
        $this->loadReactCSS('HeaderComponent.css');
        $this->parsePage();
    }


    private function getPrioritiesDescriptions()
    {
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        echo json_encode(
            [
                "status" => "ok",
                "data"   => [
                    [
                        "id"          => "1",
                        "description" => $dbeHeader->getValue(DBEHeader::priority1Desc)
                    ],
                    [
                        "id"          => "2",
                        "description" => $dbeHeader->getValue(DBEHeader::priority2Desc)
                    ],
                    [
                        "id"          => "3",
                        "description" => $dbeHeader->getValue(DBEHeader::priority3Desc)
                    ],
                    [
                        "id"          => "4",
                        "description" => $dbeHeader->getValue(DBEHeader::priority4Desc)
                    ],
                    [
                        "id"          => "5",
                        "description" => $dbeHeader->getValue(DBEHeader::priority5Desc)
                    ],
                ]
            ]
        );

    }

    function getNumberAllwoedMistakes()
    {
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        return ["value" => $dbeHeader->getValue(DBEHeader::numberOfAllowedMistakes)];
    }

    function getKeywordMatchingPercent()
    {
        $dbeHeader = new DBEHeader($this);
        $dbeHeader->getRow(1);
        return $dbeHeader->getValue(DBEHeader::keywordMatchingPercent);
    }

    // -------------new code
    function getHeader()
    {
        $dbeHeader = new DBEJHeader($this);
        $dbeHeader->getRow(1);
        $header = [
            "headerID"                                       => $dbeHeader->getValue(DBEHeader::headerID),
            "name"                                           => $dbeHeader->getValue(DBEHeader::name),
            "add1"                                           => $dbeHeader->getValue(DBEHeader::add1),
            "add2"                                           => $dbeHeader->getValue(DBEHeader::add2),
            "add3"                                           => $dbeHeader->getValue(DBEHeader::add3),
            "town"                                           => $dbeHeader->getValue(DBEHeader::town),
            "county"                                         => $dbeHeader->getValue(DBEHeader::county),
            "postcode"                                       => $dbeHeader->getValue(DBEHeader::postcode),
            "phone"                                          => $dbeHeader->getValue(DBEHeader::phone),
            "fax"                                            => $dbeHeader->getValue(DBEHeader::fax),
            "goodsContact"                                   => $dbeHeader->getValue(DBEHeader::goodsContact),
            "gscItemID"                                      => $dbeHeader->getValue(DBEHeader::gscItemID),
            'gscItemDescription'                             => $dbeHeader->getValue(DBEJHeader::gscItemDescription),
            "yearlySicknessThresholdWarning"                 => $dbeHeader->getValue(
                DBEHeader::yearlySicknessThresholdWarning
            ),
            "stdVATCode"                                     => $dbeHeader->getValue(DBEHeader::stdVATCode),
            "billingStartTime"                               => $dbeHeader->getValue(DBEHeader::billingStartTime),
            "billingEndTime"                                 => $dbeHeader->getValue(DBEHeader::billingEndTime),
            "overtimeStartTime"                              => $dbeHeader->getValue(DBEHeader::overtimeStartTime),
            "overtimeEndTime"                                => $dbeHeader->getValue(DBEHeader::overtimeEndTime),
            "hourlyLabourCost"                               => $dbeHeader->getValue(DBEHeader::hourlyLabourCost),
            "minimumOvertimeMinutesRequired"                 => $dbeHeader->getValue(
                DBEHeader::minimumOvertimeMinutesRequired
            ),
            "daysInAdvanceExpensesNextMonthAlert"            => $dbeHeader->getValue(
                DBEHeader::daysInAdvanceExpensesNextMonthAlert
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "srAutocompleteThresholdHours"                   => $dbeHeader->getValue(
                DBEHeader::srAutocompleteThresholdHours
            ),
            "srStartersLeaversAutoCompleteThresholdHours"    => $dbeHeader->getValue(
                DBEHeader::srStartersLeaversAutoCompleteThresholdHours
            ),
            "srPromptContractThresholdHours"                 => $dbeHeader->getValue(
                DBEHeader::srPromptContractThresholdHours
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "fixSLABreachWarningHours"                       => $dbeHeader->getValue(
                DBEHeader::fixSLABreachWarningHours
            ),
            "closureReminderDays"                            => $dbeHeader->getValue(DBEHeader::closureReminderDays),
            "closingSRBufferMinutes"                         => $dbeHeader->getValue(DBEHeader::closingSRBufferMinutes),
            "hdTeamLimitMinutes"                             => $dbeHeader->getValue(DBEHeader::hdTeamLimitMinutes),
            "hdTeamTargetLogPercentage"                      => $dbeHeader->getValue(
                DBEHeader::hdTeamTargetLogPercentage
            ),
            "hdTeamTargetSlaPercentage"                      => $dbeHeader->getValue(
                DBEHeader::hdTeamTargetSlaPercentage
            ),
            "hdTeamTargetFixHours"                           => $dbeHeader->getValue(DBEHeader::hdTeamTargetFixHours),
            "hdTeamTargetFixQtyPerMonth"                     => $dbeHeader->getValue(
                DBEHeader::hdTeamTargetFixQtyPerMonth
            ),
            "esTeamTargetLogPercentage"                      => $dbeHeader->getValue(
                DBEHeader::esTeamTargetLogPercentage
            ),
            "esTeamLimitMinutes"                             => $dbeHeader->getValue(DBEHeader::esTeamLimitMinutes),
            "esTeamTargetSlaPercentage"                      => $dbeHeader->getValue(
                DBEHeader::esTeamTargetSlaPercentage
            ),
            "esTeamTargetFixHours"                           => $dbeHeader->getValue(DBEHeader::esTeamTargetFixHours),
            "esTeamTargetFixQtyPerMonth"                     => $dbeHeader->getValue(
                DBEHeader::esTeamTargetFixQtyPerMonth
            ),
            "smallProjectsTeamTargetLogPercentage"           => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamTargetLogPercentage
            ),
            "smallProjectsTeamLimitMinutes"                  => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamLimitMinutes
            ),
            "smallProjectsTeamMinutesInADay"                 => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamMinutesInADay
            ),
            "smallProjectsTeamTargetSlaPercentage"           => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamTargetSlaPercentage
            ),
            "smallProjectsTeamTargetFixHours"                => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamTargetFixHours
            ),
            "smallProjectsTeamTargetFixQtyPerMonth"          => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamTargetFixQtyPerMonth
            ),
            "projectTeamTargetLogPercentage"                 => $dbeHeader->getValue(
                DBEHeader::projectTeamTargetLogPercentage
            ),
            "projectTeamLimitMinutes"                        => $dbeHeader->getValue(
                DBEHeader::projectTeamLimitMinutes
            ),
            "projectTeamMinutesInADay"                       => $dbeHeader->getValue(
                DBEHeader::projectTeamMinutesInADay
            ),
            "projectTeamTargetSlaPercentage"                 => $dbeHeader->getValue(
                DBEHeader::projectTeamTargetSlaPercentage
            ),
            "projectTeamTargetFixHours"                      => $dbeHeader->getValue(
                DBEHeader::projectTeamTargetFixHours
            ),
            "projectTeamTargetFixQtyPerMonth"                => $dbeHeader->getValue(
                DBEHeader::projectTeamTargetFixQtyPerMonth
            ),
            "SDDashboardEngineersInSREngineersMaxCount"      => $dbeHeader->getValue(
                DBEHeader::SDDashboardEngineersInSREngineersMaxCount
            ),
            "SDDashboardEngineersInSRInPastHours"            => $dbeHeader->getValue(
                DBEHeader::SDDashboardEngineersInSRInPastHours
            ),
            "serviceDeskNotification24hBegin"                => $dbeHeader->getValue(
                DBEHeader::serviceDeskNotification24hBegin
            ),
            "serviceDeskNotification24hEnd"                  => $dbeHeader->getValue(
                DBEHeader::serviceDeskNotification24hEnd
            ),
            "highActivityAlertCount"                         => $dbeHeader->getValue(DBEHeader::highActivityAlertCount),
            "customerContactWarnHours"                       => $dbeHeader->getValue(
                DBEHeader::customerContactWarnHours
            ),
            "remoteSupportWarnHours"                         => $dbeHeader->getValue(DBEHeader::remoteSupportWarnHours),
            "RemoteSupportMinWarnHours"                      => $dbeHeader->getValue(
                DBEHeader::RemoteSupportMinWarnHours
            ),
            "autoCriticalP1Hours"                            => $dbeHeader->getValue(DBEHeader::autoCriticalP1Hours),
            "autoCriticalP2Hours"                            => $dbeHeader->getValue(DBEHeader::autoCriticalP2Hours),
            "autoCriticalP3Hours"                            => $dbeHeader->getValue(DBEHeader::autoCriticalP3Hours),
            "hdTeamManagementTimeApprovalMinutes"            => $dbeHeader->getValue(
                DBEHeader::hdTeamManagementTimeApprovalMinutes
            ),
            "esTeamManagementTimeApprovalMinutes"            => $dbeHeader->getValue(
                DBEHeader::esTeamManagementTimeApprovalMinutes
            ),
            "smallProjectsTeamManagementTimeApprovalMinutes" => $dbeHeader->getValue(
                DBEHeader::smallProjectsTeamManagementTimeApprovalMinutes
            ),
            "sevenDayerTarget"                               => $dbeHeader->getValue(DBEHeader::sevenDayerTarget),
            "sevenDayerAmberDays"                            => $dbeHeader->getValue(DBEHeader::sevenDayerAmberDays),
            "sevenDayerRedDays"                              => $dbeHeader->getValue(DBEHeader::sevenDayerRedDays),
            "pendingTimeLimitActionThresholdMinutes"         => $dbeHeader->getValue(
                DBEHeader::pendingTimeLimitActionThresholdMinutes
            ),
            "numberOfAllowedMistakes"                        => $dbeHeader->getValue(
                DBEHeader::numberOfAllowedMistakes
            ),
            "keywordMatchingPercent"                         => $dbeHeader->getValue(DBEHeader::keywordMatchingPercent),
            "portalPin"                                      => $dbeHeader->getValue(DBEHeader::portalPin),
            "portal24HourPin"                                => $dbeHeader->getValue(DBEHeader::portal24HourPin),
            "backupTargetSuccessRate"                        => $dbeHeader->getValue(
                DBEHeader::backupTargetSuccessRate
            ),
            "backupReplicationTargetSuccessRate"             => $dbeHeader->getValue(
                DBEHeader::backupReplicationTargetSuccessRate
            ),
            "secondSiteReplicationAdditionalDelayAllowance"  => $dbeHeader->getValue(
                DBEHeader::secondSiteReplicationAdditionalDelayAllowance
            ),
            "projectCommenceNotification"                    => $dbeHeader->getValue(
                DBEHeader::projectCommenceNotification
            ),
            "holdAllSOSmallProjectsP5sforQAReview"           => $dbeHeader->getValue(
                DBEHeader::holdAllSOSmallProjectsP5sforQAReview
            ),
            "holdAllSOProjectsP5sforQAReview"                => $dbeHeader->getValue(
                DBEHeader::holdAllSOProjectsP5sforQAReview
            ) ? true : false,
            "OSSupportDatesThresholdDays"                    => $dbeHeader->getValue(
                DBEHeader::OSSupportDatesThresholdDays
            ),
            "antivirusOutOfDateThresholdDays"                => $dbeHeader->getValue(
                DBEHeader::antivirusOutOfDateThresholdDays
            ),
            "offlineAgentThresholdDays"                      => $dbeHeader->getValue(
                DBEHeader::offlineAgentThresholdDays
            ),
            "office365MailboxYellowWarningThreshold"         => $dbeHeader->getValue(
                DBEHeader::office365MailboxYellowWarningThreshold
            ),
            "office365MailboxRedWarningThreshold"            => $dbeHeader->getValue(
                DBEHeader::office365MailboxRedWarningThreshold
            ),
            "office365ActiveSyncWarnAfterXDays"              => $dbeHeader->getValue(
                DBEHeader::office365ActiveSyncWarnAfterXDays
            ),
            "cDriveFreeSpaceWarningPercentageThreshold"      => $dbeHeader->getValue(
                DBEHeader::cDriveFreeSpaceWarningPercentageThreshold
            ),
            "otherDriveFreeSpaceWarningPercentageThreshold"  => $dbeHeader->getValue(
                DBEHeader::otherDriveFreeSpaceWarningPercentageThreshold
            ),
            "computerLastSeenThresholdDays"                  => $dbeHeader->getValue(
                DBEHeader::computerLastSeenThresholdDays
            ),
            "allowedClientIpPattern"                         => $dbeHeader->getValue(DBEHeader::allowedClientIpPattern),
            "solarwindsPartnerName"                          => $dbeHeader->getValue(DBEHeader::solarwindsPartnerName),
            "solarwindsUsername"                             => $dbeHeader->getValue(DBEHeader::solarwindsUsername),
            "solarwindsPassword"                             => $dbeHeader->getValue(DBEHeader::solarwindsPassword),
            "customerReviewMeetingText"                      => $dbeHeader->getValue(
                DBEHeader::customerReviewMeetingText
            ),
            "mailshot2FlagDesc"                              => $dbeHeader->getValue(DBEHeader::mailshot2FlagDesc),
            "mailshot2FlagDef"                               => $dbeHeader->getValue(
                DBEHeader::mailshot2FlagDef
            ) == 'Y' ? true : false,
            "mailshot3FlagDesc"                              => $dbeHeader->getValue(DBEHeader::mailshot3FlagDesc),
            "mailshot3FlagDef"                               => $dbeHeader->getValue(
                DBEHeader::mailshot3FlagDef
            ) == 'Y' ? true : false,
            "mailshot4FlagDesc"                              => $dbeHeader->getValue(DBEHeader::mailshot4FlagDesc),
            "mailshot4FlagDef"                               => $dbeHeader->getValue(
                DBEHeader::mailshot4FlagDef
            ) == 'Y' ? true : false,
            "mailshot8FlagDesc"                              => $dbeHeader->getValue(DBEHeader::mailshot8FlagDesc),
            "mailshot8FlagDef"                               => $dbeHeader->getValue(
                DBEHeader::mailshot8FlagDef
            ) == 'Y' ? true : false,
            "mailshot9FlagDesc"                              => $dbeHeader->getValue(DBEHeader::mailshot9FlagDesc),
            "mailshot9FlagDef"                               => $dbeHeader->getValue(
                DBEHeader::mailshot9FlagDef
            ) == 'Y' ? true : false,
            "mailshot11FlagDesc"                             => $dbeHeader->getValue(DBEHeader::mailshot11FlagDesc),
            "mailshot11FlagDef"                              => $dbeHeader->getValue(
                DBEHeader::mailshot11FlagDef
            ) == 'Y' ? true : false,
            "priority1Desc"                                  => $dbeHeader->getValue(DBEHeader::priority1Desc),
            "priority2Desc"                                  => $dbeHeader->getValue(DBEHeader::priority2Desc),
            "priority3Desc"                                  => $dbeHeader->getValue(DBEHeader::priority3Desc),
            "priority4Desc"                                  => $dbeHeader->getValue(DBEHeader::priority4Desc),
            "priority5Desc"                                  => $dbeHeader->getValue(DBEHeader::priority5Desc),
        ];
        return $this->success($header);
    }

    function updateHeader()
    {
        try {
            $body = $this->getBody(true);
            if (!$body) {
                return $this->fail(APIException::badRequest, "Bad Request2");
            }
            $existingHeader = new DataSet($this);
            $this->buHeader->getHeader($existingHeader);
            $body[DBEHeader::expensesNextProcessingDate] = $existingHeader->getValue(
                DBEHeader::expensesNextProcessingDate
            );
            if (!$this->dsHeader->populateFromArray([$body])) {
                $this->setFormErrorOn();
                $this->setAction(CTCNC_ACT_ITEM_EDIT);
                return $this->fail(APIException::badRequest, $this->getFormErrorMessage());
            }
            $this->setAction(CTCNC_ACT_ITEM_EDIT);
            $this->buHeader->updateHeader($this->dsHeader);
            return $this->success();
        } catch (Exception $ex) {
            return $this->fail($ex->getMessage());
        }
    }

}
