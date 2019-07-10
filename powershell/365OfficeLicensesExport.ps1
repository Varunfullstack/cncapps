Param($User, $Password)
$securePassword = ConvertTo-SecureString $Password -AsPlainText -Force
$Credentials = New-Object System.Management.Automation.PSCredential($User, $securePassword)
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
    $Session = New-PSSession -ConfigurationName Microsoft.Exchange -ConnectionUri https://outlook.office365.com/powershell-liveid/ -Credential $Credentials -Authentication Basic -AllowRedirection
    if (!$Session)
    {
        write-host $Session.GetType()
        throw "No session"
    }
    Import-PSSession $Session -DisableNameChecking | Out-Null
    Start-Sleep -s 5
    $Report = @()
    $Mailboxes = Get-Mailbox -ResultSize Unlimited | Where-Object { $_.RecipientTypeDetails -ne "DiscoveryMailbox" }
    $MSOLDomain = Get-MsolDomain | Where-Object { $_.Authentication -eq "Managed" -and $_.IsDefault -eq "True" }
    foreach ($mailbox in $Mailboxes)
    {
        $DisplayName = $mailbox.DisplayName
        $UserPrincipalName = $mailbox.UserPrincipalName
        $UserDomain = $UserPrincipalName.Split('@')[1]
        $MailboxStat = Get-MailboxStatistics $UserPrincipalName
        $TotalItemSize = $MailboxStat | Select-Object @{ name = "TotalItemSize"; expression = { [math]::Round(($_.TotalItemSize.ToString().Split("(")[1].Split(" ")[0].Replace(",", "")/1MB), 2) } }
        $TotalItemSize = $TotalItemSize.TotalItemSize
        $RecipientTypeDetails = $mailbox.RecipientTypeDetails
        $MSOLUSER = Get-MsolUser -UserPrincipalName $UserPrincipalName
        if ($UserDomain -eq $MSOLDomain.name)
        {
            $DaysToExpiry = $MSOLUSER |  Select-Object @{ Name = "DaysToExpiry"; Expression = { (New-TimeSpan -start (get-date) -end ($_.LastPasswordChangeTimestamp + $MSOLPasswordPolicy)).Days } }; $DaysToExpiry = $DaysToExpiry.DaysToExpiry
        }
        $Information = $MSOLUSER | Select-Object @{ Name = 'DisplayName'; Expression = { $DisplayName }}, @{ Name = 'TotalItemSize'; Expression = { $TotalItemSize } }, @{ Name = 'RecipientTypeDetails'; Expression = { [String]::join(";", $RecipientTypeDetails) } }, islicensed, @{ Name = "Licenses"; Expression = { $_.Licenses.AccountSkuId } }
        $Report += $Information

    }
    $Report = $Report| Sort-Object TotalItemSize
    Get-PSSession | Remove-PSSession
    Remove-TypeData System.Array
    $JSOn = ConvertTo-Json $Report
    Write-Host $JSOn
}
catch
{
    $stackTrace = $PSItem.ScriptStackTrace
    $positionMessage = $PSItem.InvocationInfo.PositionMessage
    Write-Host "{`"error`": true, `"errorMessage`": `"$PSItem`", `"stackTrace`":`"$stackTrace`", `"position`":`"$positionMessage`" }"
}