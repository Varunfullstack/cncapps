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
    $Mailboxes = Get-Mailbox -ResultSize Unlimited | where { $_.RecipientTypeDetails -ne "DiscoveryMailbox" }
    $MSOLDomain = Get-MsolDomain | where { $_.Authentication -eq "Managed" -and $_.IsDefault -eq "True" }
    foreach ($mailbox in $Mailboxes)
    {
        $DisplayName = $mailbox.DisplayName
        $UserPrincipalName = $mailbox.UserPrincipalName
        $UserDomain = $UserPrincipalName.Split('@')[1]
        $Alias = $mailbox.alias
        $MailboxStat = Get-MailboxStatistics $UserPrincipalName
        $TotalItemSize = $MailboxStat | select @{ name = "TotalItemSize"; expression = { [math]::Round(($_.TotalItemSize.ToString().Split("(")[1].Split(" ")[0].Replace(",", "")/1MB), 2) } }
        $TotalItemSize = $TotalItemSize.TotalItemSize
        $RecipientTypeDetails = $mailbox.RecipientTypeDetails
        $MSOLUSER = Get-MsolUser -UserPrincipalName $UserPrincipalName
        if ($UserDomain -eq $MSOLDomain.name)
        {
            $DaysToExpiry = $MSOLUSER |  select @{ Name = "DaysToExpiry"; Expression = { (New-TimeSpan -start (get-date) -end ($_.LastPasswordChangeTimestamp + $MSOLPasswordPolicy)).Days } }; $DaysToExpiry = $DaysToExpiry.DaysToExpiry
        }
        $Information = $MSOLUSER | select @{ Name = 'DisplayName'; Expression = { [String]::join(";", $DisplayName) } }, @{ Name = 'TotalItemSize'; Expression = { [String]::join(";", $TotalItemSize) } }, @{ Name = 'RecipientTypeDetails'; Expression = { [String]::join(";", $RecipientTypeDetails) } }, islicensed, @{ Name = "Licenses"; Expression = { $_.Licenses.AccountSkuId } }
        $Report = $Report + $Information

    }
    Get-PSSession | Remove-PSSession
    Remove-TypeData System.Array
    $JSOn = ConvertTo-Json $Report
    Write-Host $JSOn
}
catch
{
    Write-Host "{`"error`": true, `"errorMessage`": `"$PSItem`"}"
}