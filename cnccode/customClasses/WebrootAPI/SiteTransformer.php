<?php


namespace CNCLTD\WebrootAPI;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class SiteTransformer implements Transformer
{
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding("siteId", "SiteId", false));
        $classBindings->register(new AliasBinding("siteName", "SiteName", false));
        $classBindings->register(new AliasBinding("siteType", "SiteType", false));
        $classBindings->register(new AliasBinding("totalEndpoints", "TotalEndpoints", false));
        $classBindings->register(new AliasBinding("pCsInfected24", "PCsInfected24", false));
        $classBindings->register(new AliasBinding("accountKeyCode", "AccountKeyCode", false));
        $classBindings->register(new AliasBinding("devicesAllowed", "DevicesAllowed", false));
        $classBindings->register(new AliasBinding("mobileSeats", "MobileSeats", false));
        $classBindings->register(new AliasBinding("deactivated", "Deactivated", false));
        $classBindings->register(new AliasBinding("suspended", "Suspended", false));
        $classBindings->register(new AliasBinding("endDate", "EndDate", false));
        $classBindings->register(new AliasBinding("device", "Device", false));
        $classBindings->register(new AliasBinding("infect", "Infect", false));
        $classBindings->register(new AliasBinding("billingCycle", "BillingCycle", false));
        $classBindings->register(new AliasBinding("billingDate", "BillingDate", false));
        $classBindings->register(new AliasBinding("companyComments", "CompanyComments", false));
        $classBindings->register(new AliasBinding("deactivatedBy", "DeactivatedBy", false));
        $classBindings->register(new AliasBinding("suspendedBy", "SuspendedBy", false));
        $classBindings->register(new AliasBinding("createdBy", "CreatedBy", false));
        $classBindings->register(new AliasBinding("globalPolicies", "GlobalPolicies", false));
        $classBindings->register(new AliasBinding("globalOverrides", "GlobalOverrides", false));
        $classBindings->register(new AliasBinding("globalAlerts", "GlobalAlerts", false));
        $classBindings->register(new AliasBinding("allKeysExpired", "AllKeysExpired", false));
        $classBindings->register(new AliasBinding("description", "Description", false));
        $classBindings->register(new AliasBinding("policyId", "PolicyId", false));
        $classBindings->register(new AliasBinding("policyName", "PolicyName", false));
        $classBindings->register(new AliasBinding("policyDescription", "PolicyDescription", false));
        $classBindings->register(new AliasBinding("emails", "Emails", false));
        $classBindings->register(new AliasBinding("accessLevel", "AccessLevel", false));
        $classBindings->register(new AliasBinding("modules", "Modules", false));
    }

    public function transforms()
    {
        return Site::class;
    }
}