<?php

/**
 * This class outputs a KMZ for a given client for a given fire.
 *
 * Constructor accepts
 *  @param integer $perimeter_id
 *  @param array $client_ids
 *  @param integer $buffer (optional - miles to include policies in KMZ)
 *
 * Refer to this link for KML specifications and documentation:
 * https://developers.google.com/kml/documentation/
 */
class KMZEngine
{
    private $perimeterStyle;
    private $perimeterWKT;
    private $threatWKT;
    private $clients;
    private $buffer;
    private $name;
    private $kml;
    private $fireId;

    private $triageNoticeIDs = array();
    private $includeTriage;

    /**
     * Constructor
     * @param integer $perimeter_id
     * @param array $client_ids
     * @param integer $buffer (optional) - miles to include policies in KMZ
     * @param boolean $includeTriage (optional) - should render triage zones on map
     * @param integer $fid (optional) - fire_id
     */
    public function __construct($perimeter_id, $client_ids, $buffer = 15, $includeTriage = true, $fid = null)
    {
        $this->constructKML($perimeter_id, $client_ids, $buffer, $includeTriage, $fid);
    }

    /**
     * Constructs the KML
     * @param integer $perimeter_id
     * @param array $client_ids
     * @param integer $buffer (miles)
     * @param integer $fid fire_id
     */
    private function constructKML($perimeter_id, $client_ids, $buffer, $includeTriage, $fid = null)
    {
        $this->buffer = Helper::milesToMeters($buffer);
        $this->includeTriage = $includeTriage;
        $this->fireId = $fid;
        Assets::registerGeoPHP();

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

        $perimeter = ResPerimeters::model()->findByPk($perimeter_id);

        $perimeterData = Yii::app()->db->createCommand($sql)->queryRow(true, array(
            ':perimeter_id' => $perimeter_id
        ));

        $isThreat = filter_var($perimeterData['isThreat'], FILTER_VALIDATE_BOOLEAN);

        $perimeterWKT = $perimeterData['wkt'];
        $threatWKT = GIS::erasePerimeterFromThreat($perimeter_id);

        $perimeterGeom = geoPHP::load($perimeterWKT, 'wkt');
        $threatGeom = geoPHP::load($threatWKT, 'wkt');

        $perimeterKML = str_replace('LineString','LinearRing',$perimeterGeom->out('kml'));
        $threatKML = $isThreat ? str_replace('LineString','LinearRing',$threatGeom->out('kml')) : '';

        // Buffers
        $buffers = GIS::getPerimeterBuffers($perimeter_id);
        $bufferThreeGeom = geoPHP::load($buffers['three'], 'wkt');
        $bufferOneGeom = geoPHP::load($buffers['one'], 'wkt');
        $bufferHalfGeom = geoPHP::load($buffers['half'], 'wkt');

        // Triage Zones - Getting any clients that have triage zones by notice_id
        $triageNoticeIDs = Yii::app()->db->createCommand('
        SELECT n.client_id, tz.notice_id
        FROM res_triage_zone tz
            INNER JOIN res_notice n ON tz.notice_id = n.notice_id
        WHERE n.perimeter_id = ' . $perimeter->id . ' AND n.client_id IN (' . implode(', ', $client_ids)  . ')')->queryAll();

        // Storing notice ids in private class var for later use
        foreach ($triageNoticeIDs as $result)
        {
            $this->triageNoticeIDs[$result['client_id']] = $result['notice_id'];
        }

        $this->name = preg_replace('/[^A-Za-z0-9_\-]/', '', trim($perimeter->resFireName->Name));
        $this->threatWKT = $threatWKT;
        $this->perimeterWKT = $perimeterWKT;
        $this->clients = Client::model()->findAllByPk($client_ids);
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
    //Initialize $policyHolders
    $policyHolders = '';
    //if fireid exist then call getTriggeredThreatenedPolicyholders() if not then getPolicyholders()
    $policyHolders = ($this->fireId) ?  $this->getTriggeredThreatenedPolicyholders() : $this->getPolicyholders();
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
    </Folder>

    <!-- Policyholders -->

    <Folder>
        <name>Policyholders</name>
        {$policyHolders}
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

        foreach($this->clients as $client)
        {
            $zip->addFromString($client->code . 'Enrolled.png', $this->getIconData($client->map_enrolled_color, true));
            $zip->addFromString($client->code . 'NotEnrolled.png', $this->getIconData($client->map_not_enrolled_color, false));
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
     * Creates KMZ file on server and returns the file path
     * @param boolean $minify
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

        foreach($this->clients as $client)
        {
            $zip->addFromString($client->code . 'Enrolled.png', $this->getIconData($client->map_enrolled_color, true));
            $zip->addFromString($client->code . 'NotEnrolled.png', $this->getIconData($client->map_not_enrolled_color, false));
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

    <!-- Triage Style -->
    <Style id="triageStyle">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <PolyStyle>
            <color>b3ffff66</color>
            <outline>0</outline>
        </PolyStyle>
    </Style>

';

        foreach ($this->clients as $client)
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
        return $styles;
    }

    /**
     * Adds triggered/Threatened policyholder placemarks to KML
     * @return string
     */
    private function getTriggeredThreatenedPolicyholders()
    {
        $kmlPlacemarks = '';

        $sql = "
        DECLARE @perimeterWKT varchar(max) = :perimeter
        DECLARE @threatWKT varchar(max) = :threat
        DECLARE @fireId int = :fireId
        DECLARE @perimeter geography = geography::STGeomFromText(@perimeterWKT, 4326)
        DECLARE @centroid geography = (SELECT @perimeter.EnvelopeCenter())

        -- This is only needed when the buffer distance could be smaller than the threat ... it defaults to 15
        -- IF @threatWKT IS NOT NULL
        -- BEGIN
        --     DECLARE @threat geography = geography::STGeomFromText(@threatWKT, 4326);
        --     SET @buffer = (@buffer.STUnion(@threat));
        -- END

            SELECT
                p.pid,
                p.client_id,
                p.member_mid,
                p.response_status,
                p.coverage_a_amt,
                r.distance,
                p.address_line_1,
                p.city,
                p.state,
                m.first_name member_first_name,
                m.last_name member_last_name,
                p.wds_lat lat,
                p.wds_long long,
                c.name client_name,
                c.code client_code
            FROM properties p
                INNER JOIN members m on m.mid = p.member_mid
                INNER JOIN client c ON c.id = m.client_id
                INNER JOIN res_triggered r on r.property_pid = p.pid and notice_id IN (select max(notice_id) from res_notice where wds_status = 1 and fire_id = @fireId group by client_id)
            WHERE p.client_id in ( " . implode(',', array_map(function($data) { return $data->id; }, $this->clients)) . ")
                AND p.policy_status = 'active'
                AND p.type_id = 1
                AND p.wds_geocode_level != 'unmatched'
                ORDER BY r.distance ASC

        ";

        $properties = Yii::app()->db->createCommand($sql)
            ->bindValue(':perimeter', $this->perimeterWKT)
            ->bindValue(':threat', $this->threatWKT)
            ->bindValue(':fireId', $this->fireId)
            ->queryAll();

        $uniqueClientIDs = array_unique(array_map(function($propety) { return $propety['client_id']; }, $properties));
        asort($uniqueClientIDs);

        // Add each client to KML structure
        foreach ($uniqueClientIDs as $clientID)
        {
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
            <Folder>
                <name>Properties</name>
                $placemarks
            </Folder>
            ";

            // Checking if this client needs a triage zones entry
            if ($this->includeTriage === true)
            {
                if (array_key_exists(current($clientProperties)['client_id'], $this->triageNoticeIDs))
                {
                    $triageNoticeID = $this->triageNoticeIDs[current($clientProperties)['client_id']];
                    $triageZones = GIS::getTriageZones($triageNoticeID);
                    $triageZoneKML = '';

                    foreach ($triageZones as $zone)
                    {
                        $zoneObject = geoPHP::load($zone['geog'], 'wkt');
                        $zoneKMLOutput = $zoneObject->out('kml');

                        $triageZoneKML .= "
                        <Placemark id=\"{$zone['notes']}\">
                            <name><![CDATA[Work Zone]]></name>
                            <description><![CDATA[Work Zone: {$zone['notes']}]]></description>
                            <!--<Snippet maxLines=\"1\">Snippet Here</Snippet>-->
                            <visibility>1</visibility>
                            <styleUrl>triageStyle</styleUrl>
                            <MultiGeometry>
                                $zoneKMLOutput
                            </MultiGeometry>
                        </Placemark>
                        ";
                    }


                    $kmlPlacemarks .= "
                <Folder>
                    <name>Triage Zones</name>
                    $triageZoneKML
                </Folder>
                    ";
                }
            }

            $kmlPlacemarks .= '</Folder>';
        }

        return $kmlPlacemarks;
    }

    /**
     * Adds policyholder placemarks to KML
     * @return string
     */
    private function getPolicyholders()
    {
        $kmlPlacemarks = '';

        $sql = "
        DECLARE @perimeterWKT varchar(max) = :perimeter
        DECLARE @threatWKT varchar(max) = :threat
        DECLARE @bufferDistance float = :buffer

        DECLARE @perimeter geography = geography::STGeomFromText(@perimeterWKT, 4326)
        DECLARE @centroid geography = (SELECT @perimeter.EnvelopeCenter())
        DECLARE @buffer geography = @centroid.STBuffer(@bufferDistance)
        DECLARE @boundingboxgeom geometry = geometry::STGeomFromWKB(@buffer.STAsBinary(), 4326).STEnvelope()

        -- This is only needed when the buffer distance could be smaller than the threat ... it defaults to 15
        -- IF @threatWKT IS NOT NULL
        -- BEGIN
        --     DECLARE @threat geography = geography::STGeomFromText(@threatWKT, 4326);
        --     SET @buffer = (@buffer.STUnion(@threat));
        -- END

        SELECT * FROM (
            SELECT TOP 500
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
                p.wds_lat lat,
                p.wds_long long,
                c.name client_name,
                c.code client_code
            FROM properties p
                INNER JOIN members m on m.mid = p.member_mid
                INNER JOIN client c ON c.id = m.client_id
            WHERE p.client_id in ( " . implode(',', array_map(function($data) { return $data->id; }, $this->clients)) . ")
                AND p.policy_status = 'active'
                AND p.type_id = 1
                AND p.wds_geocode_level != 'unmatched'
                AND p.wds_lat >= @boundingboxgeom.STPointN(1).STY
                AND p.wds_lat <= @boundingboxgeom.STPointN(3).STY
                AND p.wds_long <= @boundingboxgeom.STPointN(2).STX
                AND p.wds_long >= @boundingboxgeom.STPointN(4).STX
            ORDER BY distance ASC
        ) s
        WHERE s.distance <= @bufferDistance --now filter down by distance from perimeter;";

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
            <Folder>
                <name>Properties</name>
                $placemarks
            </Folder>
            ";

            // Checking if this client needs a triage zones entry
            if ($this->includeTriage === true)
            {
                if (array_key_exists(current($clientProperties)['client_id'], $this->triageNoticeIDs))
                {
                    $triageNoticeID = $this->triageNoticeIDs[current($clientProperties)['client_id']];
                    $triageZones = GIS::getTriageZones($triageNoticeID);
                    $triageZoneKML = '';

                    foreach ($triageZones as $zone)
                    {
                        $zoneObject = geoPHP::load($zone['geog'], 'wkt');
                        $zoneKMLOutput = $zoneObject->out('kml');

                        $triageZoneKML .= "
                        <Placemark id=\"{$zone['notes']}\">
                            <name><![CDATA[Work Zone]]></name>
                            <description><![CDATA[Work Zone: {$zone['notes']}]]></description>
                            <!--<Snippet maxLines=\"1\">Snippet Here</Snippet>-->
                            <visibility>1</visibility>
                            <styleUrl>triageStyle</styleUrl>
                            <MultiGeometry>
                                $zoneKMLOutput
                            </MultiGeometry>
                        </Placemark>
                        ";
                    }


                    $kmlPlacemarks .= "
                <Folder>
                    <name>Triage Zones</name>
                    $triageZoneKML
                </Folder>
                    ";
                }
            }

            $kmlPlacemarks .= '</Folder>';
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