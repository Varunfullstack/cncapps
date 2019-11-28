Param($User, $Password)
$decodedPassword = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($Password))
$decodedUser = [System.Text.Encoding]::UTF8.GetString([System.Convert]::FromBase64String($User))
$securePassword = ConvertTo-SecureString $decodedPassword -AsPlainText -Force
$Credentials = New-Object System.Management.Automation.PSCredential($decodedUser, $securePassword)
Import-Module msonline
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
    Import-PSSession $Session -DisableNameChecking | Out-Null
    Start-Sleep -s 5
    $MailboxesReport = @()
    $Mailboxes = Get-Mailbox -ResultSize Unlimited | Where-Object { $_.RecipientTypeDetails -ne "DiscoveryMailbox" }
    $MSOLDomain = Get-MsolDomain | Where-Object { $_.Authentication -eq "Managed" -and $_.IsDefault -eq "True" }
    foreach ($mailbox in $Mailboxes)
    {
        $DisplayName = $mailbox.DisplayName
        $UserPrincipalName = $mailbox.UserPrincipalName
        $UserDomain = $UserPrincipalName.Split('@')[1]
        $MailboxStat = Get-MailboxStatistics $UserPrincipalName -WarningAction SilentlyContinue
        $TotalItemSize = $MailboxStat | Select-Object @{ name = "TotalItemSize"; expression = { [math]::Round(($_.TotalItemSize.ToString().Split("(")[1].Split(" ")[0].Replace(",", "")/1MB), 2) } }
        $TotalItemSize = $TotalItemSize.TotalItemSize
        $RecipientTypeDetails = $mailbox.RecipientTypeDetails
        $MSOLUSER = Get-MsolUser -UserPrincipalName $UserPrincipalName
        $OWA = Get-CASMailbox -Identity $UserPrincipalName | Where-Object { (!$_.OWAEnabled) -eq $False } | Select-Object -ExpandProperty OWAEnabled
        $2FA = Get-MsolUser -UserPrincipalName $UserPrincipalName | Where-Object { (!$_.StrongAuthenticationRequirements) -eq $False }
        if ($UserDomain -eq $MSOLDomain.name)
        {
            $DaysToExpiry = $MSOLUSER |  Select-Object @{ Name = "DaysToExpiry"; Expression = { (New-TimeSpan -start (get-date) -end ($_.LastPasswordChangeTimestamp + $MSOLPasswordPolicy)).Days } }; $DaysToExpiry = $DaysToExpiry.DaysToExpiry
        }
        $Information = $MSOLUSER | Select-Object @{ Name = 'DisplayName'; Expression = { $DisplayName } }, @{ Name = 'TotalItemSize'; Expression = { $TotalItemSize } }, @{ Name = 'RecipientTypeDetails'; Expression = { [String]::join(";", $RecipientTypeDetails) } }, islicensed, @{ Name = "Licenses"; Expression = { [array]$_.Licenses.AccountSkuId }, @{ Name = 'OWA Enabled'; Expression = { $OWA } }, @{ Name = '2FA'; Expression = { $2FA } } }
        $MailboxesReport += $Information
    }
    [array]$MailboxesReport = $MailboxesReport | Sort-Object TotalItemSize -Descending
    [array]$LicensesData = Get-MsolAccountSku | Select-Object AccountSkuId, ActiveUnits, @{ Name = 'Unallocated'; Expression = { $_.ActiveUnits - $_.ConsumedUnits } }
    $Report = @{
        mailboxes = $MailboxesReport
        licenses = $LicensesData
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
    Write-Host $erroJSON
}