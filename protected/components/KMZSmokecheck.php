<?php

/**
 * This class outputs a smokecheck KMZ for all impacted clients for a given fire.
 *
 * Constructor accepts
 *      perimeter WKT           - Well known text string representation of fire geometry
 *      name (optional)         - Fire name
 *      perimeter_id (optional) - ID of fire perimeter
 *
 * The optional parameters are used for a fire that is already saved in the system.
 *
 * Refer to this link for KML specifications and documentation:
 * https://developers.google.com/kml/documentation/
 */
class KMZSmokecheck
{
    private $clientIDs = array();
    private $perimeterStyle;
    private $perimeterWKT;
    private $clients;
    private $name;
    private $kml;

    private $buffer;

    /**
     * Constructor
     * @param string|null $perimeter (optional)
     * @param string|null $name (optional)
     * @param integer|null $perimeter_id (optional)
     * @param integer $miles (optional)
     */
    public function __construct($perimeter = null, $name = null, $perimeter_id = null, $miles = 6)
    {
        $this->constructKML($perimeter, $name, $perimeter_id, $miles);
    }

    /**
     * Constructs the KML
     * @param string $perimeter
     * @param string $name
     * @param integer $perimeter_id
     */
    private function constructKML($perimeter, $name, $perimeter_id, $miles)
    {
        $this->buffer = Helper::milesToMeters($miles);

        Assets::registerGeoPHP();

        $simplifiedPerimeterWKT = '';
        $threatWKT = null;
        $isThreat = false;

        if ($perimeter_id)
        {
            $sql = '
                SET NOCOUNT ON;

                DECLARE @perimeter geography
                DECLARE @isThreat int

                SELECT
	                @perimeter = l.geog,
	                @isThreat = p.threat_location_id
                FROM res_perimeters p
                INNER JOIN location l ON p.perimeter_location_id = l.id
                WHERE p.id = :perimeter_id

                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10);
                END

                SELECT @perimeter.STAsText() [wkt], CASE WHEN @isThreat IS NOT NULL THEN 1 ELSE @isThreat END [isThreat]
            ';

            $perimeterData = Yii::app()->db->createCommand($sql)->queryRow(true, array(
                ':perimeter_id' => $perimeter_id
            ));

            $isThreat = filter_var($perimeterData['isThreat'], FILTER_VALIDATE_BOOLEAN);

            $simplifiedPerimeterWKT = $perimeterData['wkt'];
            $threatWKT = GIS::erasePerimeterFromThreat($perimeter_id);
        }
        else
        {
            $sql = '
                DECLARE @perimeter geography = geography::STGeomFromText(:wkt, 4326);
                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10);
                END
                SELECT @perimeter.ToString();
            ';

            $simplifiedPerimeterWKT = Yii::app()->db->createCommand($sql)->queryScalar(array(
                ':wkt' => $perimeter
            ));
        }

        // Perimeter
        $perimeterGeom = geoPHP::load($simplifiedPerimeterWKT, 'wkt');
        $threatGeom = geoPHP::load($threatWKT, 'wkt');

        // Fixing GeoPHP glitch
        $perimeterKML = str_replace('LineString','LinearRing',$perimeterGeom->out('kml'));
        $threatKML = $isThreat ? str_replace('LineString','LinearRing',$threatGeom->out('kml')) : '';

        // Buffers
        $buffers = GIS::getPerimeterBuffers($perimeter_id, $miles);
        $bufferOuterGeom = geoPHP::load($buffers['outer'], 'wkt');
        $bufferThreeGeom = geoPHP::load($buffers['three'], 'wkt');
        $bufferOneGeom = geoPHP::load($buffers['one'], 'wkt');
        $bufferHalfGeom = geoPHP::load($buffers['half'], 'wkt');

        $this->clients = Client::model()->findAll(array('condition' => 'wds_fire = 1', 'order' => 'name ASC'));
        $this->name = is_null($name) ? 'Fire' : preg_replace('/[^A-Za-z0-9_\-]/', '', trim($name));
        $this->threatWKT = $threatWKT;
        $this->perimeterWKT = $simplifiedPerimeterWKT;

        $this->perimeterStyle = ($perimeterGeom->geometryType() == 'Point') ? 'perimeterPointStyle' : 'perimeterStyle';

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
        <Placemark id="bufferAlert">
            <name>$miles</name>
            <styleUrl>#ThreeMile</styleUrl>
            <MultiGeometry>
                {$bufferOuterGeom->out('kml')}
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

        foreach ($this->clients as $client)
        {
            if (in_array($client->id, $this->clientIDs))
            {
                $zip->addFromString($client->code . 'Enrolled.png', $this->getIconData($client->map_enrolled_color, true));
                $zip->addFromString($client->code . 'NotEnrolled.png', $this->getIconData($client->map_not_enrolled_color, false));
            }
        }

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
     * Creates KMZ file on server
     * @param boolean $minify
     * @return string
     */
    public function createKMZ($minify = false)
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
        
        foreach ($this->clients as $client)
        {
            if (in_array($client->id, $this->clientIDs))
            {
                $zip->addFromString($client->code . 'Enrolled.png', $this->getIconData($client->map_enrolled_color, true));
                $zip->addFromString($client->code . 'NotEnrolled.png', $this->getIconData($client->map_not_enrolled_color,  false));
            }
        }

        if ($this->perimeterStyle === 'perimeterPointStyle')
        {
            $zip->addFile($imagesDir . 'fire-icon.png', 'fire-icon.png');
        }

        $zip->close();

        return $filename;
    }

    /**
     * Deletes KMZ file from the server
     */
    public function removeKMZ()
    {
        $filename = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . $this->name . '.kmz';

        if (file_exists($filename))
        {
            unlink($filename);
        }
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
    </Style>
    ';

        foreach ($this->clients as $client)
        {
            if (in_array($client->id, $this->clientIDs))
            {
                $clientCode = $client->code;

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
            }
        }

        return $styles;
    }

    /**
     * Adds policyholder placemarks to KML
     * @return string
     */
    public function getPolicyholders()
    {
        $kmlPlacemarks = '';

        $sql = "
            DECLARE @perimeterWKT varchar(max) = :perimeter
            DECLARE @threatWKT varchar(max) = :threat
            DECLARE @bufferDistance float = :buffer

            DECLARE @perimeter geography = geography::STGeomFromText(@perimeterWKT, 4326)
            DECLARE @buffer geography = @perimeter.STBuffer(@bufferDistance)

            SELECT
                p.pid,
                p.client_id,
                p.member_mid,
                p.response_status,
                p.coverage_a_amt,
                p.geog.STDistance(@perimeter) distance,
                p.address_line_1,
                p.city,
                p.state,
                m.first_name member_first_name,
                m.last_name member_last_name,
                p.wds_long long,
                p.wds_lat lat,
                c.name client_name,
                c.code client_code
            FROM properties p
                INNER JOIN members m ON m.mid = p.member_mid
                INNER JOIN client c ON m.client_id = c.id
            WHERE p.geog.STIntersects(@buffer) = 1
                AND p.policy_status = 'active'
            ORDER BY m.last_name ASC
        ";

        $properties = Yii::app()->db->createCommand($sql)
            ->bindValue(':perimeter', $this->perimeterWKT)
            ->bindValue(':threat', $this->threatWKT)
            ->bindValue(':buffer', $this->buffer)
            ->queryAll();

        $uniqueClientIDs = array_unique(array_map(function($propety) { return $propety['client_id']; }, $properties));
        asort($uniqueClientIDs);

        // Add each client to KML structure
        foreach ($uniqueClientIDs as $clientID)
        {
            $this->clientIDs[] = $clientID;

            $clientProperties = array_filter($properties, function($propety) use ($clientID) { return $propety['client_id'] === $clientID; });

            // Sorting by distance
            usort($clientProperties, function($a, $b) { return $a['distance'] - $b['distance']; });

            $placemarks = '';

            foreach($clientProperties as $property)
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
                                        <td style="padding:3px">First</td>
                                        <td style="padding:3px">' . $property['member_first_name'] . '</td>
                                    </tr>
                                    <tr bgcolor="#D4E4F3">
                                        <td style="padding:3px">Last</td>
                                        <td style="padding:3px">' . $property['member_last_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:3px">Address</td>
                                        <td style="padding:3px">' . $property['address_line_1'] . '</td>
                                    </tr>
                                    <tr bgcolor="#D4E4F3">
                                        <td style="padding:3px">Distance</td>
                                        <td style="padding:3px">' . $distance . ' miles</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:3px">City</td>
                                        <td style="padding:3px">' . $property['city'] . '</td>
                                    </tr>
                                    <tr bgcolor="#D4E4F3">
                                        <td style="padding:3px">State</td>
                                        <td style="padding:3px">' . $property['state'] . '</td>
                                    </tr>
                                </table>
                            </body>
                        </html>
                    ]]>
                </description>
                <styleUrl>#' . ($property['response_status'] === 'enrolled' ? $property['client_code'] . 'EnrolledStyleMap' : $property['client_code'] . 'NotEnrolledStyleMap') . '</styleUrl>
                <Point>
                    <altitudeMode>clampToGround</altitudeMode>
                    <coordinates>' . $property['long'] . ',' . $property['lat'] . ',0</coordinates>
                </Point>
            </Placemark>
                ';
            }

            reset($clientProperties);

            $kmlPlacemarks .= "
        <Folder>
            <name>" . current($clientProperties)['client_name'] . "</name>
            $placemarks
        </Folder>
            ";
        }

        return $kmlPlacemarks;
    }

    /**
     * Returns base64 representation of policyholder icon
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
