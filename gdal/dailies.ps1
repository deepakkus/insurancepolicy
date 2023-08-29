<#

Author: Matt Eiben
Email: meiben@wildfire-defense.com

Description:

This script downloads data from the NOAA National Digital Guidance Database.

The data is unpacked, and geospatially processed, then output to a minified geojson files.

GDAL ogr2ogr reference:
http://www.gdal.org/ogr2ogr.html

NOAA Map Hazards and Colors:
https://www.weather.gov/help-map

#>

Write-Host
Write-Host "Dailies Script: Started..." -ForegroundColor Magenta
Write-Host

# Variables

$rootDirPath = Get-Location
$rootProjectPath = Split-Path -Path $rootDirPath -Parent
$scriptStartTime = Get-Date
$baseDataPath = Join-Path -Path $rootDirPath -ChildPath basedata

$workspaceHazards = "$rootDirPath\workspace_harzards"
$workspaceSmoke  = "$rootDirPath\workspace_smoke"

Get-ChildItem -Path $workspaceHazards -Exclude .gitkeep | Foreach-Object `
{
    Remove-Item $_.FullName -Force -Recurse
}

Get-ChildItem -Path $workspaceSmoke -Exclude .gitkeep | Foreach-Object `
{
    Remove-Item $_.FullName -Force -Recurse
}

# Including Functions

. "$rootDirPath\library.ps1"

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Updating runtime env variables

$env:Path += ";"
$env:Path += "$rootDirPath\bin;"
$env:Path += "$rootDirPath\bin\gdal\python\osgeo;"
$env:Path += "$rootDirPath\bin\proj\apps;"
$env:Path += "$rootDirPath\bin\gdal\apps;"
$env:Path += "$rootDirPath\bin\ms\apps;"
$env:Path += "$rootDirPath\bin\gdal\csharp;"
$env:Path += "$rootDirPath\bin\ms\csharp;"
$env:Path += "$rootDirPath\bin\curl"
$env:GDAL_DATA = "$rootDirPath\bin\gdal-data"
$env:GDAL_DRIVER_PATH = "$rootDirPath\bin\gdal\plugins"
$env:PYTHONPATH = "$rootDirPath\bin\gdal\python;"
$env:PYTHONPATH += "$rootDirPath\bin\ms\python"
$env:PROJ_LIB = "$rootDirPath\bin\proj\SHARE"

Set-Alias degrib "$rootDirPath\degrib.exe"

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Downloading data

Write-Host "Downloading Data" -ForegroundColor Magenta

DownloadFile -FileURL "http://tgftp.nws.noaa.gov/SL.us008001/ST.opnl/DF.gr2/DC.ndfd/AR.conus/VP.001-003/ds.wwa.bin" -FilePath "$workspaceHazards\hazards.bin"
DownloadFile -FileURL "http://tgftp.nws.noaa.gov/SL.us008001/ST.opnl/DF.gr2/DC.ndgd/GT.aq/AR.conus/ds.smokes01.bin" -FilePath "$workspaceSmoke\smoke.bin"

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Unpacking data

Write-Host "Upacking binary data to shapefiles" -ForegroundColor Magenta

degrib -in $workspaceHazards\hazards.bin -namePath $workspaceHazards -C -msg all -Shp -poly big -nMissing
degrib -in $workspaceSmoke\smoke.bin -namePath $workspaceSmoke -C -msg all -Shp -poly big -nMissing

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Geospatial Work - Hazards

Write-Host "Starting Geospatial Work - Hazards" -ForegroundColor Magenta

$hazards = "(
    'Blizzard Warning',
    'Blizzard Watch',
    'Coastal Flood Advisory',
    'Coastal Flood Statement',
    'Coastal Flood Warning',
    'Coastal Flood Watch',
    'Dense Smoke Advisory',
    'Evacuation - Immediate',
    'Excessive Heat Warning',
    'Excessive Heat Watch',
    'Extreme Cold Warning',
    'Extreme Cold Watch',
    'Extreme Fire Danger',
    'Extreme Wind Warning',
    'Fire Warning',
    'Fire Weather Watch',
    'Flash Flood Statement',
    'Flash Flood Warning',
    'Flash Flood Watch',
    'Flood Advisory',
    'Flood Statement',
    'Flood Warning',
    'Flood Watch',
    'Hazardous Weather Outlook',
    'Heat Advisory',
    'High Wind Warning',
    'High Wind Watch',
    'Ice Storm Warning',
    'Red Flag Warning',
    'Severe Thunderstorm Warning',
    'Severe Thunderstorm Watch',
    'Severe Weather Statement',
    'Small Stream Flood Advisory',
    'Tornado Warning',
    'Tornado Watch',
    'Wind Advisory',
    'Winter Storm Warning',
    'Winter Storm Watch',
    'Winter Weather Advisory'
)"

# Copy first item
Get-ChildItem -Path $workspaceHazards -File -Filter "*.shp" | Select-Object -First 1 | Foreach-Object `
{
    ogr2ogr $workspaceHazards\merged.shp $_.FullName `
        -f "ESRI Shapefile" `
        -sql "SELECT WWA_1 AS hazard FROM $($_.BaseName) WHERE WWA_1 IN $hazards"
}

# Append rest of files to the first copy
Get-ChildItem -Path $workspaceHazards -File -Filter "*.shp" | Select-Object -Skip 1 | Foreach-Object `
{
    ogr2ogr $workspaceHazards\merged.shp $_.FullName `
        -f "ESRI Shapefile" `
        -update `
        -append `
        -nln merged `
        -sql "SELECT WWA_1 AS hazard FROM $($_.BaseName) WHERE WWA_1 IN $hazards"
}

# Dissolve features based on hazards attribute
ogr2ogr $workspaceHazards\dissolved.shp $workspaceHazards\merged.shp `
    -f "ESRI Shapefile" `
    -dialect SQLite `
    -sql "SELECT ST_union(Geometry),hazard FROM merged GROUP BY hazard"

# Add color column
ogrinfo $workspaceHazards\dissolved.shp -sql "ALTER TABLE dissolved ADD color character(10)"


# Update new column
ogrinfo $workspaceHazards\dissolved.shp `
    -dialect SQLite `
    -sql @"
        UPDATE dissolved SET color = CASE
            WHEN hazard = 'Blizzard Warning' THEN '#FF4500'
            WHEN hazard = 'Blizzard Watch' THEN '#ADFF2F'
            WHEN hazard = 'Coastal Flood Advisory' THEN '#7CFC00'
            WHEN hazard = 'Coastal Flood Statement' THEN '#6B8E23'
            WHEN hazard = 'Coastal Flood Warning' THEN '#228B22'
            WHEN hazard = 'Coastal Flood Watch' THEN '#66CDAA'
            WHEN hazard = 'Dense Smoke Advisory' THEN '#F0E68C'
            WHEN hazard = 'Evacuation - Immediate' THEN '#7FFF00'
            WHEN hazard = 'Excessive Heat Warning' THEN '#C71585'
            WHEN hazard = 'Excessive Heat Watch' THEN '#800000'
            WHEN hazard = 'Extreme Cold Warning' THEN '#0000FF'
            WHEN hazard = 'Extreme Cold Watch' THEN '#0000FF'
            WHEN hazard = 'Extreme Fire Danger' THEN '#E9967A'
            WHEN hazard = 'Extreme Wind Warning' THEN '#FF8C00'
            WHEN hazard = 'Fire Warning' THEN '#A0522D'
            WHEN hazard = 'Fire Weather Watch' THEN '#FFDEAD'
            WHEN hazard = 'Flash Flood Statement' THEN '#8B0000'
            WHEN hazard = 'Flash Flood Warning' THEN '#8B0000'
            WHEN hazard = 'Flash Flood Watch' THEN '#2E8B57'
            WHEN hazard = 'Flood Advisory' THEN '#00FF7F'
            WHEN hazard = 'Flood Statement' THEN '#00FF00'
            WHEN hazard = 'Flood Warning' THEN '#00FF00'
            WHEN hazard = 'Flood Watch' THEN '#2E8B57'
            WHEN hazard = 'Hazardous Weather Outlook' THEN '#EEE8AA'
            WHEN hazard = 'Heat Advisory' THEN '#FF7F50'
            WHEN hazard = 'High Wind Warning' THEN '#DAA520'
            WHEN hazard = 'High Wind Watch' THEN '#B8860B'
            WHEN hazard = 'Ice Storm Warning' THEN '#8B008B'
            WHEN hazard = 'Red Flag Warning' THEN '#FF1493'
            WHEN hazard = 'Severe Thunderstorm Warning' THEN '#FFA500'
            WHEN hazard = 'Severe Thunderstorm Watch' THEN '#DB7093'
            WHEN hazard = 'Severe Weather Statement' THEN '#00FFFF'
            WHEN hazard = 'Small Stream Flood Advisory' THEN '#00FF7F'
            WHEN hazard = 'Tornado Warning' THEN '#FF0000'
            WHEN hazard = 'Tornado Watch' THEN '#FFFF00'
            WHEN hazard = 'Wind Advisory' THEN '#D2B48C'
            WHEN hazard = 'Winter Storm Warning' THEN '#FF69B4'
            WHEN hazard = 'Winter Storm Watch' THEN '#4682B4'
            WHEN hazard = 'Winter Weather Advisory' THEN '#7B68EE'
            ELSE 'transparent'
        END
"@

# Clipping to USA, output to WGS84
ogr2ogr $workspaceHazards\clipped.shp $workspaceHazards\dissolved.shp `
    -f "ESRI Shapefile" `
    -t_srs EPSG:4326 `
    -clipsrc $baseDataPath\usa_boundary.shp

# Export to geojson with 4 decimal place precision
ogr2ogr $rootDirPath\hazards.json $workspaceHazards\clipped.shp `
    -f "GeoJSON" `
    -lco COORDINATE_PRECISION=3

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Geospatial Work - Smoke

Write-Host "Starting Geospatial Work - Smoke" -ForegroundColor Magenta

# Copy first item
Get-ChildItem -Path $workspaceSmoke -File -Filter "*.shp" | Select-Object -First 1 | Foreach-Object `
{
    ogr2ogr $workspaceSmoke\merged.shp $_.FullName `
        -f "ESRI Shapefile" `
        -sql "SELECT smokes FROM $($_.BaseName) WHERE smokes >= 10.0"
}

# Append rest of files to the first copy
Get-ChildItem -Path $workspaceSmoke -File -Filter "*.shp" | Select-Object -Skip 1 | Foreach-Object `
{
    ogr2ogr $workspaceSmoke\merged.shp $_.FullName `
        -f "ESRI Shapefile" `
        -update `
        -append `
        -nln merged `
        -sql "SELECT smokes FROM $($_.BaseName) WHERE smokes >= 10.0"
}

# Add new column
ogrinfo $workspaceSmoke\merged.shp -sql "ALTER TABLE merged ADD smoke integer(1)"

# Update new column
ogrinfo $workspaceSmoke\merged.shp `
    -dialect SQLite `
    -sql @"
        UPDATE merged SET smoke = CASE
            WHEN smokes >= 10.0 AND smokes < 40.0 THEN 1
            WHEN smokes >= 40.0 AND smokes < 100.0 THEN 2
            ELSE 3
        END
"@

# Dissolve features based on smoke attribute
ogr2ogr $workspaceSmoke\dissolved.shp $workspaceSmoke\merged.shp `
    -f "ESRI Shapefile" `
    -dialect SQLite `
    -sql "SELECT ST_union(Geometry),smoke FROM merged GROUP BY smoke"

# Clipping to USA, output to WGS84
ogr2ogr $workspaceSmoke\clipped.shp $workspaceSmoke\dissolved.shp `
    -f "ESRI Shapefile" `
    -t_srs EPSG:4326 `
    -clipsrc $baseDataPath\usa_boundary.shp

# Export to geojson with 4 decimal place precision
ogr2ogr $rootDirPath\smoke.json $workspaceSmoke\clipped.shp `
    -f "GeoJSON" `
    -lco COORDINATE_PRECISION=3

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Processing files and copying to downloads directory

Write-Host "Processing outputs" -ForegroundColor Magenta

Minimize-File -FilePath $rootDirPath\hazards.json
Minimize-File -FilePath $rootDirPath\smoke.json

if ((Get-Content $rootDirPath\hazards.json) -eq $Null)
{
    New-Item $rootDirPath\hazards.json -ItemType file -Force -Value '{"type":"FeatureCollection","features":[]}'
}

if ((Get-Content $rootDirPath\smoke.json) -eq $Null)
{
    New-Item $rootDirPath\smoke.json -ItemType file -Force -Value '{"type":"FeatureCollection","features":[]}'
}

Copy-Item -Path $rootDirPath\hazards.json -Destination $rootProjectPath\protected\downloads\hazards.json -Force
Copy-Item -Path $rootDirPath\smoke.json   -Destination $rootProjectPath\protected\downloads\smoke.json   -Force

#------------------------------------------------------------------------------------------------------------
#------------------------------------------------------------------------------------------------------------
#----- Cleaning up

Write-Host "Cleaning up" -ForegroundColor Magenta

Get-ChildItem -Path $workspaceHazards -Exclude .gitkeep | Foreach-Object `
{
    Remove-Item $_.FullName -Force -Recurse
}

Get-ChildItem -Path $workspaceSmoke -Exclude .gitkeep | Foreach-Object `
{
    Remove-Item $_.FullName -Force -Recurse
}

Remove-Item -Path $rootDirPath\hazards.json -Force
Remove-Item -Path $rootDirPath\smoke.json -Force

Write-Host
Write-Host "Dailies Script: Ended" -ForegroundColor Magenta
Write-Host "Script time: $((Get-Date).Subtract($scriptStartTime).Seconds) second(s)" -ForegroundColor Magenta
Write-Host