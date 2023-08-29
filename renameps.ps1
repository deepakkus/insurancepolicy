cd C:\inetpub\WDS-G1-App-Tier

$items = Get-ChildItem * -recurse
# enumerate the items array
foreach ($item in $items)
{
    # if the item is a directory, then process it.
    if ($item.Attributes -ne "Directory")
    {
        if($item.Name -eq 'config.json'){
            (Get-Content $item.FullName ) |
            Foreach-Object { $_ -replace 'localhost', 'vmwds-ci-d1.excellimatrix.local' } |
            Set-Content $item.FullName

            (Get-Content $item.FullName ) |
            Foreach-Object { $_ -replace 'wdsg1passcode', 'admin@123456' } |
            Set-Content $item.FullName

			(Get-Content $item.FullName ) |
            Foreach-Object { $_ -replace 'https://dashboard.wildfire-defense.com', 'http://vmwds-ci-w1.excellimatrix.local:85' } |
            Set-Content $item.FullName
        }
    }
}

IISReset /RESTART