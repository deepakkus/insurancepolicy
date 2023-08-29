Function Minimize-File
{
    <#
        .SYNOPSIS
            This method minimized the content of a geojson formatted file.  It will leave any whitespace in the properties object.
        .PARAMETER FilePath
            This parameter is a string representing the path of the file to minimize.
        .EXAMPLE
            Minimize-File -FilePath "C:\temp\localfile.json"
    #>
    [CmdletBinding()]
    Param
    (
        [Parameter(Mandatory=$true)]
        [Alias("PATH")]
        [String] $FilePath
    )
    Process
    {
        $FileContent = [System.IO.File]::ReadAllText($FilePath)
        # Match all white space not between letters
        $RegexSearch = New-Object System.Text.RegularExpressions.Regex('\s*(?![a-zA-Z]*[a-zA-Z])')
        if ($RegexSearch.IsMatch($FileContent))
        {
            [System.IO.File]::WriteAllText($FilePath, $RegexSearch.Replace($FileContent, ""))
        }
    }
}

Function DownloadFile
{
    <#
        .SYNOPSIS
            This method downloads a file from a given url to a file system path.
        .PARAMETER FileURL
            This parameter is a string representing the URL to download the file from.
        .PARAMETER FilePath
            This parameter is a string representing the file path to download the file to.
        .EXAMPLE
            DownloadFile -FileURL "http://a.file.url/internetfile.bin" -FilePath "C:\temp\localfile.bin"
    #>
    [CmdletBinding()]
    Param
    (
        [Parameter(Mandatory=$true)]
        [Alias("URL")]
        [String] $FileURL,
        [Alias("PATH")]
        [String] $FilePath
    )
    Process
    {
        $startTime = Get-Date
        $webClient = New-Object System.Net.WebClient
        $webClient.DownloadFile($FileURL, $FilePath)
        Write-Host "Download time for $((Get-Item $FilePath ).Name): $((Get-Date).Subtract($startTime).Seconds) second(s)" -ForegroundColor Magenta
    }
}