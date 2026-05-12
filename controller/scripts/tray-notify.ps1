param(
    [Parameter(Mandatory = $true)][string]$Title,
    [Parameter(Mandatory = $true)][string]$Body
)

Add-Type -AssemblyName System.Windows.Forms
$ni = New-Object System.Windows.Forms.NotifyIcon
$ni.Icon = [System.Drawing.SystemIcons]::Information
$ni.Visible = $true
$ni.ShowBalloonTip(12000, $Title, $Body, [System.Windows.Forms.ToolTipIcon]::Info)
Start-Sleep -Seconds 3
$ni.Visible = $false
$ni.Dispose()
