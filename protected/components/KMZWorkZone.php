<?php

/**
 * This class outputs a notice KMZ for a given client for a given fire.
 *
 * Constructor accepts
 *  perimeter_id - ID of fire perimeter
 *  threat_id - ID of the fire threat
 *  name - Fire name
 *  client_id - ID of client
 *
 * Refer to this link for KML specifications and documentation:
 * https://developers.google.com/kml/documentation/
 */
class KMZWorkZone
{
    private $perimeterStyle;
    private $perimeterWKT;
    private $threatWKT;
    private $client;
    private $workZoneID;
    private $workZoneWKT;
    private $priority;
    private $name;
    public $fireName;
    private $kml;
    private $noticeID;
    private $properties;
    public $folderPath;
    public $baseFolderPath;

    /**
     * Constructor
     * @param integer $perimeter_id
     * @param integer $client_id
     */
    public function __construct($notice_id, $work_zone_id, $priority)
    {
        $this->constructKML($notice_id, $work_zone_id, $priority);
    }

    /**
     * Constructs the KML
     * @param integer $perimeter_id
     * @param string $name
     * @param integer $client_id
     */
    private function constructKML($notice_id, $work_zone_id, $priority)
    {
        Assets::registerGeoPHP();
        $this->priority = $priority;
        $this->workZoneID = $work_zone_id;
        $this->noticeID = $notice_id;

        $sql = '
            SET NOCOUNT ON;

            DECLARE @perimeter geography;
            DECLARE @isThreat int;
            DECLARE @id int;
            DECLARE @client_id int;
            DECLARE @fire_name varchar(200);
            SELECT
	            @perimeter = l.geog,
	            @isThreat = p.threat_location_id,
                @id = p.id,
                @client_id = n.client_id,
                @fire_name = f.name
            FROM res_perimeters p
            INNER JOIN location l ON p.perimeter_location_id = l.id
            INNER JOIN res_notice n on n.perimeter_id = p.id
            INNER JOIN res_fire_name f on f.fire_id = n.fire_id
            WHERE n.notice_id = :notice_id

            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10);
            END

            SELECT @perimeter.STAsText() [wkt], @fire_name [fire_name], @id [id], @client_id [client_id], CASE WHEN @isThreat IS NOT NULL THEN 1 ELSE @isThreat END [isThreat]
        ';

        $perimeterData = Yii::app()->db->createCommand($sql)->queryRow(true, array(
            ':notice_id' => $notice_id
        ));

        $isThreat = filter_var($perimeterData['isThreat'], FILTER_VALIDATE_BOOLEAN);
        $clientID = $perimeterData['client_id'];

        $perimeterWKT = $perimeterData['wkt'];
        $threatWKT = GIS::erasePerimeterFromThreat($perimeterData['id']);
        //$this->threatWKT = $threatWKT;

        $perimeterGeom = geoPHP::load($perimeterWKT, 'wkt');
        $threatGeom = geoPHP::load($threatWKT, 'wkt');

        // Fixing GeoPHP glitch
        $perimeterKML = str_replace('LineString','LinearRing',$perimeterGeom->out('kml'));
        $threatKML = $isThreat ? str_replace('LineString','LinearRing',$threatGeom->out('kml')) : '';

        //Work Zone
        $workZoneGeom = geoPHP::load($this->getWorkZone(), 'wkt');
        $workZoneKML = $workZoneGeom->out('kml');

        // Buffers
        $buffers = GIS::getPerimeterBuffers($perimeterData['id']);
        $bufferThreeGeom = geoPHP::load($buffers['three'], 'wkt');
        $bufferOneGeom = geoPHP::load($buffers['one'], 'wkt');
        $bufferHalfGeom = geoPHP::load($buffers['half'], 'wkt');


        $this->threatWKT = $threatWKT;
        $this->perimeterWKT = $perimeterWKT;
        $this->client = Client::model()->findByPk($clientID);
        $this->perimeterStyle = ($perimeterGeom->geometryType() == 'Point') ? 'perimeterPointStyle' : 'perimeterStyle';

        $this->workZoneWKT = $this->getWorkZone();
        $this->name = $this->client->name . "_" . preg_replace('/[^A-Za-z0-9_\-]/', '', trim($perimeterData['fire_name'])) . "_WZ" . $priority;
        $this->fireName = preg_replace('/[^A-Za-z0-9_\-]/', '', trim($perimeterData['fire_name']));

        if ($isThreat)
        {
            $threatKML = "
                <Placemark id=\"threat\">
                    <name>$this->name Threat</name>
                    <styleUrl>#threatStyle</styleUrl>
                    <MultiGeometry>
                        $threatKML
                    </MultiGeometry>
                </Placemark>
            ";
        }

        $this->kml = <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
    <name><![CDATA[$this->name]]></name>

    <!-- Perimeter -->
    <Placemark id="perimeter">
        <name><![CDATA[$this->name Perimeter]]></name>
        <styleUrl>#$this->perimeterStyle</styleUrl>
        <MultiGeometry>
            $perimeterKML
        </MultiGeometry>
    </Placemark>

    <!-- Threat -->
    $threatKML

    <!-- Work Zone -->

    <Placemark id="work_zone">
        <name><![CDATA[Work zone]]></name>
        <styleUrl>#workZoneStyle</styleUrl>
        <MultiGeometry>
            $workZoneKML
        </MultiGeometry>
    </Placemark>

    <!-- Buffers -->
    <Folder>
        <name>Buffers</name>
        <Placemark id="buffer0.5">
            <name>0.5</name>
            <styleUrl>#HalfMile</styleUrl>
            <MultiGeometry>
                {$bufferHalfGeom->out('kml')}
            </MultiGeometry>
        </Placemark>
        <Placemark id="buffer1">
            <name>1</name>
            <styleUrl>#OneMile</styleUrl>
            <MultiGeometry>
                {$bufferOneGeom->out('kml')}
            </MultiGeometry>
        </Placemark>
        <Placemark id="buffer3">
            <name>3</name>
            <styleUrl>#ThreeMile</styleUrl>
            <MultiGeometry>
                {$bufferThreeGeom->out('kml')}
            </MultiGeometry>
        </Placemark>
    </Folder>

    <!-- Policyholders -->

    <Folder>
        <name>Policyholders</name>
        {$this->getPolicyholders()}
    </Folder>

    <!-- Styles -->
    {$this->styles()}

</Document>
</kml>
KML;

    }

    private function getWorkZone()
    {
        $sql = "select a.geog.ToString() as wkt from res_triage_zone z
                inner join res_triage_zone_area a on a.triage_zone_id = z.id
                where z.notice_id = :notice_id and a.notes = :work_zone";

        //echo $this->noticeID;
        //echo " z"  . $this->priority;
        $zone = Yii::app()->db->createCommand($sql)->queryRow(true, array(
            ':notice_id' => $this->noticeID, ':work_zone' => $this->priority
        ));

        return $zone['wkt'];
    }

    /**
     * Creates KMZ file on server and downloads the file
     * @param boolean $minify
     */
    public function downloadKMZ($minify = false)
    {
        $filename = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . $this->name . '.kmz';
        $imagesDir = Yii::getPathOfAlias('webroot.images') . DIRECTORY_SEPARATOR;

        if ($minify === true)
        {
            $this->kml = preg_replace('/\<!--.*?--\>/','',$this->kml);
            $this->kml = preg_replace('/\>\s+\</','><',$this->kml);
        }

        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE);
        $zip->addFromString('doc.kml', $this->kml);
        $zip->addFromString($this->client->code . 'Enrolled.png', $this->getIconData($this->client->map_enrolled_color, true));
        $zip->addFromString($this->client->code . 'NotEnrolled.png', $this->getIconData($this->client->map_not_enrolled_color, false));

        if ($this->perimeterStyle === 'perimeterPointStyle')
        {
            $zip->addFile($imagesDir . 'fire-icon.png', 'fire-icon.png');
        }

        $zip->close();

        header('HTTP/1.1 200 OK');
        header("Content-Description: File Transfer");
        header('Content-Type: application/vnd.google-earth.kmz');
        header('Content-Length: ' . filesize($filename));
        header('Content-disposition: attachment; filename=' . str_replace('#', '', $this->name) . '_' . date('Y-m-d Hi') . '.kmz');
        header('Content-Transfer-Encoding: binary');
        readfile($filename);
        unlink($filename);
        exit(0);
    }

    /**
     * Creates KMZ file on server and returns the file path
     * @param boolean $minify
     */
    public function createKMZ($minify = false)
    {
        $this->baseFolderPath = Yii::getPathOfAlias('webroot.protected.downloads') .  DIRECTORY_SEPARATOR . $this->fireName . "_" . date('Y-m-d');
        $this->folderPath = $this->baseFolderPath . DIRECTORY_SEPARATOR . $this->fireName . "_" . $this->client->name . "_" . date("Ymd_Hi");

        $filename = $this->folderPath . DIRECTORY_SEPARATOR . $this->name . '.kmz';
        $imagesDir = Yii::getPathOfAlias('webroot.images') . DIRECTORY_SEPARATOR;

        if (!file_exists($this->baseFolderPath))
        {
            mkdir($this->baseFolderPath);
        }

        if (!file_exists($this->folderPath))
        {
            mkdir($this->folderPath);
        }

        if ($minify === true)
        {
            $this->kml = preg_replace('/\<!--.*?--\>/','',$this->kml);
            $this->kml = preg_replace('/\>\s+\</','><',$this->kml);
        }

        $zip = new ZipArchive();
        $zip->open($filename, ZipArchive::CREATE);
        $zip->addFromString('doc.kml', $this->kml);
        $zip->addFromString($this->client->code . 'Enrolled.png', $this->getIconData($this->client->map_enrolled_color, true));
        $zip->addFromString($this->client->code . 'NotEnrolled.png', $this->getIconData($this->client->map_not_enrolled_color,  false));

        if ($this->perimeterStyle === 'perimeterPointStyle')
        {
            $zip->addFile($imagesDir . 'fire-icon.png', 'fire-icon.png');
        }

        $zip->close();

        return $filename;
    }

    public function createList()
    {

        $fields = array('member_last_name', 'address_line_1', 'city', 'state', 'response_status', 'client_name');
        $filePath = $this->folderPath . DIRECTORY_SEPARATOR . $this->name . ".csv";
        if(!empty($this->properties))
        {
            //$headers = array_keys($this->properties[0]);
            $out = fopen($filePath, 'w');
            fputcsv($out, $fields);
            foreach($this->properties as $property)
            {
                $newRow = array();
                foreach($fields as $field)
                {
                    $newRow[] = $property[$field];
                }

                fputcsv($out, array_values($newRow));
            }
            fclose($out);
            $out = null;
        }


    }

    /**
     * Deletes KMZ file from the server
     */
    public function removeKMZ()
    {
        $filename = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . $this->name . '.kmz';
        if (file_exists($filename))
            unlink($filename);
    }

    /**
     * Adds styles to KML
     * @return string
     */
    private function styles()
    {
        $styles = '
    <!-- Buffer Styles -->
    <Style id="SixMile">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <LineStyle>
            <color>ff00e64c</color>
            <width>2</width>
        </LineStyle>
        <PolyStyle>
            <color>00f0f0f0</color>
        </PolyStyle>
    </Style>
    <Style id="ThreeMile">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <LineStyle>
            <color>ff00ffff</color>
            <width>2</width>
        </LineStyle>
        <PolyStyle>
            <color>00f0f0f0</color>
        </PolyStyle>
    </Style>
    <Style id="OneMile">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <LineStyle>
            <color>ff00aaff</color>
            <width>2</width>
        </LineStyle>
        <PolyStyle>
            <color>00f0f0f0</color>
        </PolyStyle>
    </Style>
    <Style id="HalfMile">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <LineStyle>
            <color>ff0000ff</color>
            <width>2</width>
        </LineStyle>
        <PolyStyle>
            <color>00f0f0f0</color>
        </PolyStyle>
    </Style>

     <!-- Triage Style -->
    <Style id="workZoneStyle">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <PolyStyle>
            <color>b3ffff66</color>
            <outline>0</outline>
        </PolyStyle>
    </Style>

    <!-- Perimeter Style -->'.

    ($this->perimeterStyle === 'perimeterStyle' ? '

    <Style id="perimeterStyle">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <LineStyle>
            <color>b26e6e6e</color>
        </LineStyle>
        <PolyStyle>
            <color>b20000ff</color>
        </PolyStyle>
    </Style>'
    :
    '
    <Style id="perimeterPointStyle">
        <IconStyle>
            <Icon>
                <href>fire-icon.png</href>
            </Icon>
        </IconStyle>
    </Style>') . '

    <!-- Threat Style -->
    <Style id="threatStyle">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <PolyStyle>
            <color>7f00ffff</color>
            <outline>0</outline>
        </PolyStyle>
    </Style>';

        $clientCode = $this->client->code;
        $styles .= '

    <!-- Enrolled Style -->
    <StyleMap id="' . $clientCode . 'EnrolledStyleMap">
        <Pair>
            <key>normal</key>
            <styleUrl>#' . $clientCode . 'EnrolledStyle</styleUrl>
        </Pair>
        <Pair>
            <key>highlight</key>
            <styleUrl>#' . $clientCode . 'EnrolledStyle_h</styleUrl>
        </Pair>
    </StyleMap>
    <Style id="' . $clientCode . 'EnrolledStyle">
        <IconStyle>
            <scale>0.8</scale>
            <Icon>
                <href>'. $clientCode . 'Enrolled.png</href>
            </Icon>
        </IconStyle>
        <LabelStyle>
            <scale>0</scale>
        </LabelStyle>
    </Style>
    <Style id="' . $clientCode . 'EnrolledStyle_h">
        <IconStyle>
            <scale>1.0</scale>
            <Icon>
                <href>'. $clientCode . 'Enrolled.png</href>
            </Icon>
        </IconStyle>
        <LabelStyle>
            <scale>1.0</scale>
        </LabelStyle>
        <BalloonStyle>
            <!-- styling of the balloon text -->
            <text><![CDATA[
                <b><font size="+1">$[name]</font></b>
                <br/><br/>
                <font face="Courier">$[description]</font>
            ]]></text>
        </BalloonStyle>
    </Style>

    <!-- Not Enrolled Style -->
    <StyleMap id="' . $clientCode . 'NotEnrolledStyleMap">
        <Pair>
            <key>normal</key>
            <styleUrl>#' . $clientCode . 'NotEnrolledStyle</styleUrl>
        </Pair>
        <Pair>
            <key>highlight</key>
            <styleUrl>#' . $clientCode . 'NotEnrolledStyle_h</styleUrl>
        </Pair>
    </StyleMap>
    <Style id="' . $clientCode . 'NotEnrolledStyle">
        <IconStyle>
            <scale>0.5</scale>
            <Icon>
                <href>'. $clientCode . 'NotEnrolled.png</href>
            </Icon>
        </IconStyle>
        <LabelStyle>
            <scale>0</scale>
        </LabelStyle>
    </Style>
    <Style id="' . $clientCode . 'NotEnrolledStyle_h">
        <IconStyle>
            <scale>0.6</scale>
            <Icon>
                <href>'. $clientCode . 'NotEnrolled.png</href>
            </Icon>
        </IconStyle>
        <LabelStyle>
            <scale>1.0</scale>
        </LabelStyle>
        <BalloonStyle>
            <!-- styling of the balloon text -->
            <text><![CDATA[
                <b><font size="+1">$[name]</font></b>
                <br/><br/>
                <font face="Courier">$[description]</font>
            ]]></text>
        </BalloonStyle>
    </Style>';

        return $styles;
    }

    /**
     * Adds policyholder placemarks to KML
     * @return string
     */
    private function getPolicyholders()
    {
        $kmlPlacemarks = '';

        $buffer = Helper::milesToMeters(6);

        $sql = "
        SELECT
            p.pid,
            p.member_mid,
            p.response_status,
            p.coverage_a_amt,
            t.distance,
            p.address_line_1,
            p.city,
            p.state,
            m.first_name member_first_name,
            m.last_name member_last_name,
            p.wds_long long,
            p.wds_lat lat,
            c.name as client_name
        FROM properties p
            INNER JOIN members m ON m.mid = p.member_mid
            INNER JOIN client c ON p.client_id = c.id
            INNER JOIN res_triggered t on t.property_pid = p.pid
        WHERE
            t.notice_id = :notice_id
            and t.priority = :priority
            and p.response_status = 'enrolled'
        ";

        $properties = Yii::app()->db->createCommand($sql)

            ->bindValue(':notice_id', $this->noticeID)
            ->bindValue(':priority', $this->priority)
            ->queryAll();

        if ($properties)
        {
            $this->properties = $properties;
            // Sorting by distance
            usort($properties, function($a, $b) { return $a['distance'] - $b['distance']; });

            $placemarks = '';

            foreach($properties as $property)
            {
                $distance = round($property['distance'] * 0.000621371, 2);

                $placemarks .= '
            <Placemark>
                <name><![CDATA[' . $property['member_last_name'] . ']]></name>
                <Snippet maxLines="1">' . $distance . ' miles</Snippet>
                <description>
                    <![CDATA[
                        <html>
                            <head>
                                <meta http-equiv="content-type" content="text/html; charset=UTF-8">
                            </head>
                            <body style="margin:0px 0px 0px 0px;overflow:auto;background:#FFFFFF;" cellpadding="10">
                                <table style="font-family:Arial,Verdana,Times;font-size:12px;text-align:left;width:100%;border-spacing:0px; padding:3px 3px 3px 3px">
                                    <tr style="text-align:center;font-weight:bold;background:#9CBCE2">
                                        <td colspan="2">' . $property['client_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>First</td>
                                        <td>' . $property['member_first_name'] . '</td>
                                    </tr>
                                    <tr bgcolor="#D4E4F3">
                                        <td>Last</td>
                                        <td>' . $property['member_last_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>' . $property['address_line_1'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>City</td>
                                        <td>' . $property['city'] . '</td>
                                    </tr>
                                    <tr bgcolor="#D4E4F3">
                                        <td>State</td>
                                        <td>' . $property['state'] . '</td>
                                    </tr>
                                </table>
                            </body>
                        </html>
                    ]]>
                </description>
                <styleUrl>#' . ($property['response_status'] === 'enrolled' ? $this->client->code . 'EnrolledStyleMap' : $this->client->code . 'NotEnrolledStyleMap') . '</styleUrl>
                <Point>
                    <altitudeMode>clampToGround</altitudeMode>
                    <coordinates>' . $property['long'] . ',' . $property['lat'] . ',0</coordinates>
                </Point>
            </Placemark>
                ';
            }

            $kmlPlacemarks .= "
        <Folder>
            <name>{$this->client->name}</name>
            $placemarks
        </Folder>
        ";
        }

        return $kmlPlacemarks;
    }

    /**
     * Returns string representation of policyholder icon
     *
     * Use pre-created fill and stroke icons.  Method fills the fill icon with passed color
     * and covers it with the stroke.  This results in an image that has the appearance of
     * being antialiased.
     *
     * @param string $hexcolor
     * @param boolean $enrolled
     * @return string
     */
    private function getIconData($hexcolor, $enrolled)
    {
        list($filenameBorder, $filenameFill) = $enrolled ?
            array('images/ph-icon-enrolled-border.png', 'images/ph-icon-enrolled-fill.png') :
            array('images/ph-icon-not-enrolled-border.png', 'images/ph-icon-not-enrolled-fill.png');
        $imageBorder = imagecreatefrompng($filenameBorder);
        $imageFill = imagecreatefrompng($filenameFill);
        $width = imagesx($imageBorder);
        $height = imagesy($imageBorder);
        imagealphablending($imageBorder, true);
        imagealphablending($imageFill, true);
        imagesavealpha($imageBorder, true);
        imagesavealpha($imageFill, true);
        list($red, $green, $blue) = Helper::hexToRgb($hexcolor);
        imagefill($imageFill , 10, 10, imagecolorallocate($imageFill, $red, $green, $blue));
        imagecopy($imageFill, $imageBorder, 0, 0, 0, 0, $width, $height);
        ob_start();
        imagepng($imageFill);
        $data = ob_get_contents();
        ob_end_clean();
        imagedestroy($imageBorder);
        imagedestroy($imageFill);
        return $data;
    }
}