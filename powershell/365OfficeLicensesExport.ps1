Param($User, $Password)
$decodedPassword = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($Password))
$decodedUser = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($User))
$securePassword = ConvertTo-SecureString $decodedPassword -AsPlainText -Force
$Credentials = New-Object System.Management.Automation.PSCredential($decodedUser, $securePassword)
Import-Module msonline
Import-Module Microsoft.Online.SharePoint.PowerShell -DisableNameChecking
try
{
    try
    {
        Connect-MsolService -Credential $Credentials -ErrorAction Stop
    }
    catch
    {
        throw "Failed to connect to MSOLService:$PSItem"
    }
    $Session = New-PSSession -ConfigurationName Microsoft.Exchange -ConnectionUri https://outlook.office365.com/powershell-liveid/ -Credential $Credentials -Authentication Basic -AllowRedirection -ErrorAction Stop
    if (!$Session)
    {
        throw "No session"
    }
    Import-PSSession $Session -DisableNameChecking -AllowClobber | Out-Null
    Start-Sleep -s 5
    $MailboxesReport = @()
    $Mailboxes = Get-Mailbox -ResultSize Unlimited | Where-Object { $_.RecipientTypeDetails -ne "DiscoveryMailbox" }
    $MSOLDomain = Get-MsolDomain | Where-Object { $_.Authentication -eq "Managed" -and $_.IsDefault -eq "True" }
    [array]$LicensesData = Get-MsolAccountSku | Select-Object  AccountSkuId, ActiveUnits, @{ Name = 'Unallocated'; Expression = { $_.ActiveUnits - $_.ConsumedUnits } }, AccountObjectID

    if ($LicensesData.Length)
    {
        $tenantID = $LicensesData[0].AccountObjectID
    }

    $TenantDomainName = (Get-AcceptedDomain | Where-Object { $_.DomainName -like "*onmicrosoft.com" -and $_.DomainName -notlike "*mail.onmicrosoft.com" }).DomainName
    $SharePointName = $TenantDomainName.split('.')[0]
    $SharePointAdmin = "-admin.sharepoint.com"

    Connect-SPOService -Url "https://$SharePointname$SharePointAdmin" -Credential $Credentials
    $storageData = Get-SPOSite -IncludePersonalSite $True -Limit All -Filter "Url -like '-my.sharepoint.com/personal/'"
    $totalOneDriveStorageUsed = 0
    $totalEmailStorageUsed = 0
    foreach ($mailbox in $Mailboxes)
    {
        $DisplayName = $mailbox.DisplayName
        $UserPrincipalName = $mailbox.UserPrincipalName
        $storageItem = $storageData | Where-Object { $_.Owner -eq $UserPrincipalName }
        $oneDriveStorageUsage = 0
        if ($null -ne $storageItem)
        {
            $oneDriveStorageUsage = $storageItem.StorageUsageCurrent
            $totalOneDriveStorageUsed = $totalOneDriveStorageUsed + $oneDriveStorageUsage
        }
        $UserDomain = $UserPrincipalName.Split('@')[1]
        $MailboxStat = Get-MailboxStatistics $UserPrincipalName -WarningAction SilentlyContinue
        $TotalItemSize = $MailboxStat | Select-Object @{ name = "TotalItemSize"; expression = { [math]::Round(($_.TotalItemSize.ToString().Split("(")[1].Split(" ")[0].Replace(",", "")/1MB), 2) } }
        $TotalItemSize = $TotalItemSize.TotalItemSize
        $totalEmailStorageUsed = $totalEmailStorageUsed + $TotalItemSize
        $RecipientTypeDetails = $mailbox.RecipientTypeDetails
        $MSOLUSER = Get-MsolUser -UserPrincipalName $UserPrincipalName
        if ((Get-CASMailbox -Identity $UserPrincipalName).OWAEnabled)
        {
            $OWA = 'Yes'
        }
        else
        {
            $OWA = 'No'
        }
        $2FA = if ((Get-MsolUser -UserPrincipalName $UserPrincipalName).StrongAuthenticationRequirements.Count)
        {
            'Yes'
        }
        else
        {
            'No'
        }
        if ($UserDomain -eq $MSOLDomain.name)
        {
            $DaysToExpiry = $MSOLUSER |  Select-Object @{ Name = "DaysToExpiry"; Expression = { (New-TimeSpan -start (get-date) -end ($_.LastPasswordChangeTimestamp + $MSOLPasswordPolicy)).Days } }; $DaysToExpiry = $DaysToExpiry.DaysToExpiry
        }
        $Information = $MSOLUSER | Select-Object @{ Name = 'DisplayName'; Expression = { $DisplayName } }, @{ Name = 'TotalItemSize'; Expression = { $TotalItemSize } }, @{ Name = 'RecipientTypeDetails'; Expression = { [String]::join(";", $RecipientTypeDetails) } }, islicensed, @{ Name = "Licenses"; Expression = { [array]$_.Licenses.AccountSkuId } }, @{ Name = 'OWAEnabled'; Expression = { $OWA } }, @{ Name = '2FA'; Expression = { $2FA } }, @{ Name = 'OneDriveStorageUsed'; Expression = { $oneDriveStorageUsage } }
        $MailboxesReport += $Information
    }
    [array]$MailboxesReport = $MailboxesReport | Sort-Object TotalItemSize -Descending
    $Report = @{
        mailboxes = $MailboxesReport
        licenses = $LicensesData
        totalOneDriveStorageUsed = $totalOneDriveStorageUsed
        totalEmailStorageUsed = $totalEmailStorageUsed
    }
    Get-PSSession | Remove-PSSession
    Remove-TypeData System.Array
    if (-Not$Report)
    {
        Write-Host "{}"
        exit
    }
    $JSOn = ConvertTo-Json $Report
    $decodedJSON = [Text.Encoding]::UTF8.GetString([Text.Encoding]::GetEncoding(28591).GetBytes($JSOn))
    Write-Host $decodedJSON
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
    Get-PSSession | Remove-PSSession
    Write-Host $erroJSON
}