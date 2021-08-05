Param($User, $Password, $OutputPath)
$decodedPassword = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($Password))
$decodedUser = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($User))
$securePassword = ConvertTo-SecureString $decodedPassword -AsPlainText -Force
$Credentials = New-Object System.Management.Automation.PSCredential($decodedUser, $securePassword)
try
{
    try
    {
        Connect-ExchangeOnline -Credential $Credentials -ShowProgress $false -ErrorAction Stop
    }
    catch
    {
        throw "Failed to connect to ExchangeOnline:$PSItem"
    }
    try
    {
        Connect-MsolService -Credential $Credentials -ErrorAction Stop
    }
    catch
    {
        throw "Failed to connect to MSOLService:$PSItem"
    }
    $MailboxesReport = @()
    $DevicesReport = @()
    $PermissionsReport = [System.Collections.Generic.List[Object]]::new()
    $Mailboxes = Get-EXOMailbox -ResultSize Unlimited -Properties DisplayName,UserPrincipalName,RecipientTypeDetails,ArchiveStatus | Where-Object { $_.RecipientTypeDetails -ne "DiscoveryMailbox" }
    [array]$LicensesData = Get-MsolAccountSku | Select-Object  AccountSkuId, ActiveUnits, @{ Name = 'Unallocated'; Expression = { $_.ActiveUnits - $_.ConsumedUnits } }
    $TenantDomainName = (Get-AcceptedDomain | Where-Object { $_.DomainName -like "*onmicrosoft.com" -and $_.DomainName -notlike "*mail.onmicrosoft.com" }).DomainName
    $SharePointName = $TenantDomainName.split('.')[0]
    $SharePointAdmin = "-admin.sharepoint.com"
    $URL = "https://$SharePointname$SharePointAdmin"
    $errors = @()
    try
    {
        Connect-SPOService -Url $URL  -Credential $Credentials -ErrorAction Stop
        $storageData = Get-SPOSite -IncludePersonalSite $True -Limit All -Filter "Url -like '-my.sharepoint.com/personal/'"
        $allSitesCollection = Get-SPOSite -Limit All
        $siteCollection = $allSitesCollection | Select-Object @{ Name = 'SiteURL'; Expression = { $_.URL } }, @{ Name = 'Used'; Expression = { $_.StorageUsageCurrent } }
        $totalSiteUsed = ($siteCollection | Measure-Object -Sum Used).Sum
    }
    catch
    {
        $errors += [String]::Concat("", $PSItem)
    }
    $totalOneDriveStorageUsed = 0
    $totalEmailStorageUsed = 0
    $mailboxesCount = ($Mailboxes).count
    $mailboxIndex = 0
    Remove-TypeData System.Array
    foreach ($mailbox in $Mailboxes)
    {

        $DisplayName = $mailbox.DisplayName
        $UserPrincipalName = $mailbox.UserPrincipalName
        $devices = Get-EXOMobileDeviceStatistics -UserPrincipalName $UserPrincipalName
        if ($devices)
        {
            foreach ($device in $devices)
            {
                $DeviceInfo = $device | Select-Object @{ Name = 'DisplayName'; Expression = { $DisplayName } }, @{ Name = 'Email'; Expression = { $UserPrincipalName } }, DeviceType, DeviceModel, DeviceFriendlyName, DeviceOS, @{ Name = 'FirstSyncTime'; Expression = { "{0:dd-MM-yyyy HH.mm}" -f $_.FirstSyncTime } }, @{ Name = 'LastSuccessSync'; Expression = { "{0:dd-MM-yyyy HH.mm}" -f $_.LastSuccessSync } }

                if ($DeviceInfo.DeviceOs -Notlike '*Windows*')
                {
                    $DevicesReport += $DeviceInfo
                }
            }
        }

        $storageItem = $storageData |Sort-Object -Property LastContentModifiedDate -Descending|  Where-Object { $_.Owner -eq $UserPrincipalName }
        $oneDriveStorageUsage = 0

        if ($null -ne $storageItem)
        {
            if ($storageItem -is [array])
            {
                $storageItem = $storageItem[0]
            }

            $oneDriveStorageUsage = $storageItem.StorageUsageCurrent
            $totalOneDriveStorageUsed = $totalOneDriveStorageUsed + $oneDriveStorageUsage
        }

        try
        {
            $MailboxStat = Get-EXOMailboxStatistics -UserPrincipalName $UserPrincipalName -WarningAction SilentlyContinue
            $TotalItemSize = $MailboxStat.TotalItemSize.ToString().Split("(")[1].Split(" ")[0].Replace(",", "")/1MB
            $totalEmailStorageUsed = $totalEmailStorageUsed + $TotalItemSize
            $RecipientTypeDetails = $mailbox.RecipientTypeDetails
            $IsArchiveEnabled = $mailbox.ArchiveStatus -eq "Active"
            $MSOLUSER = Get-MsolUser -UserPrincipalName $UserPrincipalName -ErrorAction Stop
            $CASMailBox = Get-EXOCASMailbox -Identity $UserPrincipalName -ErrorAction Stop

            if ($CASMailBox.OWAEnabled)
            {
                $OWA = 'Yes'
            }
            else
            {
                $OWA = 'No'
            }
            $2FA = if ($MSOLUSER.StrongAuthenticationRequirements.Count)
            {
                'Yes'
            }
            else
            {
                'No'
            }
            [array]$licenses = @()
            foreach ($license in $MSOLUSER.licenses)
            {
                $licenses += $license.AccountSkuId
            }
            $Information = $MSOLUSER | Select-Object @{ Name = 'DisplayName'; Expression = { $DisplayName + " (" + $UserPrincipalName + ")" } }, @{ Name = 'TotalItemSize'; Expression = { $TotalItemSize } }, @{ Name = 'RecipientTypeDetails'; Expression = { [String]::join(";", $RecipientTypeDetails) } },@{ Name = 'IsArchiveEnabled'; Expression = { $IsArchiveEnabled } }, islicensed, @{ Name = "Licenses"; Expression = { $licenses.SyncRoot } }, @{ Name = 'OWAEnabled'; Expression = { $OWA } }, @{ Name = '2FA'; Expression = { $2FA } },  @{ Name = 'OneDriveStorageUsed'; Expression = { $oneDriveStorageUsage } }
            $MailboxesReport += $Information
        }
        catch
        {
            $errors += [String]::Concat("", $PSItem)
        }

        $Permissions = Get-EXOMailboxPermission -Identity $UserPrincipalName | Where-Object { $_.User -Like "*@*" }

        If ($Null -ne $Permissions)
        {
            # Grab each permission and output it into the report
            ForEach ($Permission in $Permissions)
            {
                $ReportLine = [PSCustomObject]@{
                    "Mailbox Name" = $DisplayName
                    "Email Address" = $UserPrincipalName
                    "Mailbox Type" = $mailbox.RecipientTypeDetails
                    "Permission" = $Permission | Select-Object -ExpandProperty AccessRights
                    "Assigned To" = $Permission.User
                }
                $PermissionsReport.Add($ReportLine)
            }
        }

        $mailboxIndex++
        $progressPCT = 0
        if ($mailboxesCount -gt 0)
        {
            $progressPCT = [math]::Round(($mailboxIndex /$mailboxesCount) * 100)
        }
        Write-Progress -Activity "Procesing Mailboxes" -Status "$progressPCT% Complete:" -PercentComplete $progressPCT
    }
    [array]$PermissionsReport = $PermissionsReport | Sort-Object -Property @{ Expression = { $_.MailboxType }; Ascending = $False }
    [array]$MailboxesReport = $MailboxesReport | Sort-Object TotalItemSize -Descending
    [array]$DevicesReport = $DevicesReport | Sort-Object DisplayName
    $Report = @{
        mailboxes = $MailboxesReport
        licenses = $LicensesData
        permissions = $PermissionsReport
        totalOneDriveStorageUsed = $totalOneDriveStorageUsed
        totalEmailStorageUsed = $totalEmailStorageUsed
        totalSiteUsed = $totalSiteUsed
        devices = $DevicesReport
        sharePointAndTeams = $siteCollection
        errors = [array]$errors
    }
    if (-Not$Report)
    {
        $Report = @{ }
    }
    $JSOn = ConvertTo-Json $Report
    [IO.File]::WriteAllLines($OutputPath, $JSOn)
}
catch
{
    $stackTrace = $PSItem.ScriptStackTrace
    $positionMessage = $PSItem.InvocationInfo.PositionMessage
    $object = @{
        error = $true
        errorMessage = [String]::Concat("", $PSItem)
        stackTrace = $stackTrace
        position = $positionMessage
    }
    $erroJSON = ConvertTo-Json $object
    [IO.File]::WriteAllLines($OutputPath, $erroJSON)
}