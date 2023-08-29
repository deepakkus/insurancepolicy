<?php

/**
 * Common GIS tasks that may need to be performed throughout WDS Admin
 *
 */
class GIS
{
    /**
     * Running the smokecheck GIS
     * @param string $geoJson
     * @param string $centroidLat
     * @param string $centroidLong
     * @return array
     */
    public static function runSmokecheck($geoJson, $centroidLat, $centroidLong)
    {
        Assets::registerGeoPHP();

        $returnArray = array();

        $geom = geoPHP::load($geoJson, 'geojson');
        $wkt = $geom->out('wkt');

        // Fixing bad geometries, if they exist, before sticking WKT into session
        $sql = '
            DECLARE @perimeter geography = geography::STGeomFromText(:wkt, 4326);
            SET @perimeter = CASE
                WHEN @perimeter.MakeValid().EnvelopeAngle() >= 90 THEN @perimeter.MakeValid().ReorientObject()
                WHEN @perimeter.STIsValid() = 0 THEN @perimeter.MakeValid()
                ELSE @perimeter
            END
            SELECT @perimeter.STAsText();
        ';

        $wkt = Yii::app()->db->createCommand($sql)->bindParam(':wkt', $wkt, PDO::PARAM_STR)->queryScalar();

        $returnArray['firePerimeter'] = $wkt;
        $returnArray['centroidLat'] = $centroidLat;
        $returnArray['centroidLong'] = $centroidLong;

        //Buffer = 3 miles in meters
        $buffer = Helper::milesToMeters(3);

        $bufferSql = '
            DECLARE @perimeter geography = geography::STGeomFromText(:wkt, 4326);
            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10);
            END
            DECLARE @buffer geography = @perimeter.STBuffer(:meters);
            SELECT @buffer.STAsText();
        ';

        $bufferWkt = Yii::app()->db->createCommand($bufferSql)
            ->bindParam(':wkt', $wkt, PDO::PARAM_STR)
            ->bindParam(':meters', $buffer, PDO::PARAM_INT)
            ->queryScalar();

        //Get Zip Codes
        $zipcodes = self::getPerimeterZipcodes(null, null, $wkt);
        $zipcodeNumbers = array_map(function($data) { return $data['zipcode']; }, $zipcodes);

        //Looks like a monster, but pretty straight forward. Select the properties that intersect with the buffer, than join it to the unmatched for the zip codes
        $sql = "
            declare @buffer geography = geography::STGeomFromText(:bufferWkt, 4326);

            select
                t.enrolled,
                t.not_enrolled,
                z.unmatched,
                c.name as client
            from (
                select
                    sum(case when response_status = 'enrolled' then 1 else 0 end) as enrolled,
                    sum(case when response_status = 'not enrolled' then 1 else 0 end) as not_enrolled,
                    client_id
                from
                    properties
                where
                    geog.STIntersects(@buffer) = 1
                    and policy_status = 'active'
                    and type_id = 1
                    and client_id in ( select id from client where wds_fire = 1 )
                group by
                    client_id
            ) t

            left outer join
                (
                    select
                        count(p.pid) as unmatched,
                        p.client_id
                    from
                        properties p
                    where
                        p.zip in ('". implode("','", $zipcodeNumbers) . "')
                        and p.type_id = 1
                        and p.policy_status = 'active'
                        and wds_geocode_level = 'unmatched'
                    group by
                        p.client_id
                ) z on z.client_id = t.client_id

            inner join
                client c on c.id = t.client_id";

        $returnArray['policyholderData'] = Yii::app()->db->createCommand($sql)
            ->bindParam(':bufferWkt', $bufferWkt, PDO::PARAM_STR)
            ->queryAll();

        return $returnArray;
    }

    /**
     * Return data for fires intersecting given wkt within given timeframe.
     * Will also return last monitored entry, if any.
     * @param string $wkt
     * @return array
     */
    public static function monitorGetNearbyOldPerimeters($wkt)
    {
        $buffer = Helper::milesToMeters(3);

        $sql = "
        DECLARE @perimeter geography = geography::STGeomFromText(:wkt, 4326);
        IF @perimeter.STNumPoints() > 1000
        BEGIN
            SET @perimeter = @perimeter.Reduce(10)
        END
        DECLARE @buffer geography = @perimeter.STBuffer(:buffer);

        SELECT
            f.Name name,
            l.geog.STAsText() geog,
            FORMAT(f.Start_Date,'yyyy-MM-dd') start_date,
            FORMAT(m.monitored_date,'yyyy-MM-dd HH:mm') monitored_date
        FROM res_perimeters p
        INNER JOIN location l ON p.perimeter_location_id = l.id
        INNER JOIN res_fire_name f ON p.fire_id = f.Fire_ID
        CROSS APPLY
        (
            -- Include most recent monitored entry
            SELECT TOP 1 res_monitor_log.monitored_date
            FROM res_monitor_log
            WHERE res_monitor_log.Perimeter_ID = p.id
            ORDER BY res_monitor_log.Monitor_ID DESC
        ) m
        WHERE p.id IN
        (
            SELECT MAX(p.id) AS id
            FROM res_perimeters p
            INNER JOIN location l ON p.perimeter_location_id = l.id
            WHERE l.geog.STIntersects(@buffer) = 1
                AND p.date_created > DATEADD(MONTH, -1, GETDATE()) -- within last month
            GROUP BY p.fire_id
        )";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':wkt', $wkt, PDO::PARAM_STR)
            ->bindParam(':buffer', $buffer, PDO::PARAM_STR)
            ->queryAll();

        return $results;
    }

    /**
     * Find data for other fires in the area.
     * @param integer $perimeterID
     * @param integer $distance how far away from fire to look
     * @param integer $months how long ago to look
     * @return array
     */
    public static function smokecheckGetNearbyOldPerimeters($perimeterID, $distance = 5, $months = -24)
    {
        $buffer = Helper::milesToMeters($distance);

        $sql = "
        DECLARE @perimeterID INT = :perimeter_id
        DECLARE @perimeter GEOGRAPHY
        DECLARE @fireID INT
        SELECT
            @perimeter = l.geog,
            @fireID = p.fire_id
        FROM res_perimeters p
        INNER JOIN location l ON p.perimeter_location_id = l.id
        WHERE p.id = @perimeterID

        IF @perimeter.STNumPoints() > 1000
        BEGIN
            SET @perimeter = @perimeter.Reduce(10)
        END
        DECLARE @buffer GEOGRAPHY = @perimeter.STBuffer(:buffer);

        SELECT
            f.Name name,
            l.geog.STAsText() geog,
            FORMAT(f.Start_Date,'yyyy-MM-dd') start_date,
            FORMAT(m.monitored_date,'yyyy-MM-dd HH:mm') monitored_date
        FROM res_perimeters p
        INNER JOIN location l ON p.perimeter_location_id = l.id
        INNER JOIN res_fire_name f ON p.fire_id = f.Fire_ID
        CROSS APPLY
        (
            -- Include most recent monitored entry
            SELECT TOP 1 res_monitor_log.monitored_date
            FROM res_monitor_log
            WHERE res_monitor_log.Perimeter_ID = p.id
            ORDER BY res_monitor_log.Monitor_ID DESC
        ) m
        WHERE p.id IN
        (
            SELECT MAX(p.id) AS id
            FROM res_perimeters p
            INNER JOIN location l ON p.perimeter_location_id = l.id
            WHERE l.geog.STIntersects(@buffer) = 1
                AND p.date_created > DATEADD(MONTH, :months, GETDATE())  -- last 24 months
                AND p.fire_id != @fireID                                 -- not this fire
                AND l.geog.STArea() * 0.000247105 > 100.0                -- over 100 acres
            GROUP BY p.fire_id
        )";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':perimeter_id', $perimeterID, PDO::PARAM_INT)
            ->bindParam(':months', $months, PDO::PARAM_INT)
            ->bindParam(':buffer', $buffer, PDO::PARAM_STR)
            ->queryAll();

        return $results;
    }

    /**
     * Runs the analysis to see who is triggered by the fire
     * @param integer $perimeterID
     * @param integer $clientID
     * @return array
     */
    public static function runFireAnalysis($perimeterID, $clientID)
    {
        $buffer = Helper::milesToMeters(3); //Buffer = 3 miles

        $hasThreat = ResPerimeters::hasThreatForPerimeter($perimeterID);

        //Need to factor in threat - include people who are intersected in threat as well
        if ($hasThreat)
        {
            $sql = "
                DECLARE @perimeterID int = :perimeterID;
                DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = @perimeterID);
                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10);
                END
                DECLARE @buffer geography = (SELECT TOP 1 l.geog FROM res_perimeter_buffers b INNER JOIN location l ON b.location_id = l.id WHERE b.buffer_distance = '3' AND b.perimeter_id = @perimeterID);
                DECLARE @threat geography = (SELECT l.geog.STUnion(@perimeter) FROM res_perimeters p INNER JOIN location l ON p.threat_location_id = l.id WHERE p.id = @perimeterID);
                DECLARE @union geography = (@buffer.STUnion(@threat));
                SELECT
                    pid,
                    response_status,
                    coverage_a_amt,
                    geog.STAsText() geog,
                    geog.STDistance(@perimeter) distance,
                    geog.STIntersects(@threat) threat
                FROM properties
                WHERE client_id = :clientID
                    AND policy_status = 'active'
                    AND type_id = 1
                    AND geog.STIntersects(@union) = 1
                ";

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT);
        }
        else
        {
            $sql = "
                DECLARE @perimeterID int = :perimeterID
                DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = @perimeterID);
                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10);
                END
                DECLARE @buffer geography = (SELECT TOP 1 l.geog FROM res_perimeter_buffers b INNER JOIN location l ON b.location_id = l.id WHERE b.buffer_distance = '3' AND b.perimeter_id = @perimeterID);
                SELECT
                    pid,
                    response_status,
                    coverage_a_amt,
                    geog.STAsText() [geog],
                    geog.STDistance(@perimeter) [distance]
                FROM properties
                WHERE client_id = :clientID
                    AND policy_status = 'active'
                    AND type_id = 1
                    AND geog.STIntersects(@buffer) = 1
            ";

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
                ->bindParam(':clientID', $clientID, PDO::PARAM_INT);
        }

        $results = $command->queryAll();

        return $results;
    }

    /**
     * Selects policyholders who are triggered by the alert distance
     * Takes a substantial amount of time if the buffer distance is large and 6K policyholders are selected. Might need to run a raw sql query instead of dealing with the model?
     * @param integer $perimeterID
     * @param integer $bufferDistance
     * @param integer $clientID
     * @return array
     */
    public static function getPolicyAlert($perimeterID, $bufferDistance, $clientID)
    {
        //Buffer = x miles (alert distance)
        $buffer = Helper::milesToMeters($bufferDistance);

        //Perimeter is saved in our system
        if ($perimeterID)
        {
            $sql = "
            DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = :perimeterID);
            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10);
            END
            DECLARE @bufferMeters float(24) = :buffer;
            DECLARE @buffer geography = @perimeter.STBuffer(@bufferMeters)
            DECLARE @boundingboxgeom geometry = geometry::STGeomFromWKB(@buffer.STAsBinary(), @buffer.STSrid).STEnvelope();

            SELECT * FROM (
                SELECT
                    p.pid,
                    p.response_status,
                    p.coverage_a_amt,
                    p.wds_lat lat,
                    p.wds_long long,
                    p.wds_geocode_level,
                    p.wds_geocoder,
                    p.wds_match_score,
                    p.geog.STDistance(@perimeter) [distance],
                    p.address_line_1,
                    p.city,
                    p.state,
                    m.last_name,
                    m.client_id,
                    c.name as client,
                    c.map_enrolled_color,
                    c.map_not_enrolled_color
                FROM properties p
                    INNER JOIN members m ON m.mid = p.member_mid
                    INNER JOIN client c ON c.id = m.client_id
                WHERE p.policy_status = 'active'
                    AND p.type_id = 1
                    AND p.wds_geocode_level != 'unmatched'
                    AND p.wds_lat >= @boundingboxgeom.STPointN(1).STY
                    AND p.wds_lat <= @boundingboxgeom.STPointN(3).STY
                    AND p.wds_long <= @boundingboxgeom.STPointN(2).STX
                    AND p.wds_long >= @boundingboxgeom.STPointN(4).STX
            ) s
            WHERE s.distance <= @bufferMeters --now filter down by distance from perimeter
            ";

            if ($clientID !== null)
            {
                $sql .= ' AND s.client_id = :client_id';
            }

            $wkt = Yii::app()->session['firePerimeter'];

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':buffer', $buffer, PDO::PARAM_STR)
                ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT);

            if ($clientID !== null)
            {
                $command->bindParam(':client_id', $clientID, PDO::PARAM_INT);
            }

            $result = $command->queryAll();
        }
        else //Perimeter is not saved in our system - initial monitor w/ kmz or lat long
        {
            $sql = "
            DECLARE @perimeter geography = geography::STGeomFromText(:wkt, 4326);
            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10);
            END
            DECLARE @buffer float = :buffer;
            DECLARE @boundingboxgeom geometry = geometry::STGeomFromWKB(@perimeter.STBuffer(@buffer).STAsBinary(), 4326).STEnvelope();

            SELECT * FROM (
                SELECT
                    p.pid,
                    p.response_status,
                    p.coverage_a_amt,
                    p.wds_lat lat,
                    p.wds_long long,
                    p.geog.STDistance(@perimeter) [distance],
                    p.address_line_1,
                    p.city,
                    p.state,
                    p.wds_geocode_level,
                    p.wds_geocoder,
                    p.wds_match_score,
                    m.last_name,
                    c.name as client,
                    c.map_enrolled_color,
                    c.map_not_enrolled_color
                FROM properties p
                    INNER JOIN members m ON m.mid = p.member_mid
                    INNER JOIN client c ON c.id = m.client_id
                WHERE p.policy_status = 'active'
                    AND p.type_id = 1
                    AND p.wds_geocode_level != 'unmatched'
                    AND p.wds_lat >= @boundingboxgeom.STPointN(1).STY
                    AND p.wds_lat <= @boundingboxgeom.STPointN(3).STY
                    AND p.wds_long <= @boundingboxgeom.STPointN(2).STX
                    AND p.wds_long >= @boundingboxgeom.STPointN(4).STX
            ) s
            WHERE s.distance <= @buffer
            ";

            $wkt = Yii::app()->session['firePerimeter'];
            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':buffer', $buffer, PDO::PARAM_STR)
                ->bindParam(':wkt', $wkt, PDO::PARAM_STR);

            $result = $command->queryAll();
        }

        return $result;
    }

    /**
     * Selects policyholders who are triggered by the alert distance
     * @param integer $perimeterID
     * @param integer $bufferDistance
     * @param integer[] $clientIDs
     * @return array
     */
    public static function getPolicyFromCentroidBuffer($perimeterID, $bufferDistance, $clientIDs)
    {
        //BufferDistance Hardcoded to limit the policyholders within 5 Miles Only
        $bufferDistance = 5000;
        $sql = "
            DECLARE @perimeterID int = :perimeterID;
            DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = @perimeterID);
            DECLARE @centroid geography = (SELECT @perimeter.EnvelopeCenter());
            DECLARE @threat geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.threat_location_id = l.id WHERE p.id = @perimeterID);
            DECLARE @bufferDistance float = :bufferDistance;
            DECLARE @buffer geography = @centroid.STBuffer(@bufferDistance);
            DECLARE @boundingboxgeom geometry = geometry::STGeomFromWKB(@buffer.STAsBinary(), 4326).STEnvelope();

            SELECT TOP 500 * FROM (
                SELECT
                    m.last_name,
                    p.pid,
                    p.member_mid,
                    p.response_status,
                    CASE p.response_status
                        WHEN 'enrolled' THEN c.enrolled_label
                        WHEN 'not enrolled' THEN c.not_enrolled_label
                        ELSE p.response_status
                    END [response_status_label],
                    p.coverage_a_amt,
                    p.wds_lat lat,
                    p.wds_long long,
                    p.geog.STDistance(@perimeter) [distance],
                    p.geog.STIntersects(@threat) [threat],
                    p.address_line_1,
                    p.city,
                    p.state,
                    c.id as client_id,
                    c.name as client_name,
                    c.map_enrolled_color,
                    c.map_not_enrolled_color,
                    p.policy,
                    m.member_num,
                    c.policyholder_label
                FROM properties p
                INNER JOIN members m on m.mid = p.member_mid
                INNER JOIN client c ON c.id = m.client_id
                WHERE
                    p.client_id IN (" . implode(',', $clientIDs) . ")
                    AND p.policy_status = 'active'
                    AND p.type_id = 1
                    AND p.wds_geocode_level != 'unmatched'
                    AND p.wds_lat >= @boundingboxgeom.STPointN(1).STY
                    AND p.wds_lat <= @boundingboxgeom.STPointN(3).STY
                    AND p.wds_long <= @boundingboxgeom.STPointN(2).STX
                    AND p.wds_long >= @boundingboxgeom.STPointN(4).STX

            ) s
            WHERE s.distance <= @bufferDistance
            ORDER BY s.threat DESC, s.distance ASC
        ";

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
            ->bindParam(':bufferDistance', $bufferDistance, PDO::PARAM_STR);

        $results = $command->queryAll();

        return $results;
    }

     /**
     * Selects the triage zones for the given notice
     * @param integer $noticeID
     * @return array
     */
    public static function getTriageZones($noticeID)
    {
        $sql = "
        SELECT a.geog.STAsText() geog, notes
        FROM res_triage_zone z
        INNER JOIN res_triage_zone_area a ON a.triage_zone_id = z.id
        WHERE z.notice_id = :noticeID";
    
        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':noticeID', $noticeID, PDO::PARAM_INT);

        $results = $command->queryAll();

        return $results;
    }

    /**
     * Selects the evac zones for the given notice
     * @param integer $noticeID
     * @return array
     */
    public static function getEvacZones($noticeID)
    {
        $sql = "
        SELECT z.geog.STAsText() geog, notes
        FROM res_evac_zone z
        WHERE z.notice_id = :noticeID";

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':noticeID', $noticeID, PDO::PARAM_INT);

        $results = $command->queryAll();

        return $results;
    }

    /**
     * Convert WKT to GeoJson
     * @param string $Wkt (well known text)
     * @return string
     */
    public static function convertWktToGeoJson($Wkt)
    {
        Assets::registerGeoPHP();
        $geom = geoPHP::load($Wkt, 'wkt');
        return $geom->out('json');
    }

    /**
     * Convert GeoJson to WKT
     * @param string $geoJson
     * @return string
     */
    public static function convertGeoJsonToWkt($geoJson)
    {
        Assets::registerGeoPHP();
        $geom = geoPHP::load($geoJson, 'json');
        $wkt = $geom->out('wkt');

        $sql = '
        DECLARE @geog geography = geography::STGeomFromText(:wkt, 4326);
        SET @geog = CASE
            WHEN @geog.MakeValid().EnvelopeAngle() >= 90 THEN @geog.MakeValid().ReorientObject()
            WHEN @geog.STIsValid() = 0 THEN @geog.MakeValid()
            ELSE @geog
        END
        SELECT @geog.STAsText()
        ';

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':wkt', $wkt, PDO::PARAM_STR);

        $results = $command->queryScalar();

        return $results;
    }

    /**
     * Convert WKB to GeoJson
     * @param string $WKB (well known binary)
     * @return string
     */
    public static function convertWkbToGeoJson($Wkb)
    {
        Assets::registerGeoPHP();
        $wkb = pack('H*', $Wkb);
        $geom = geoPHP::load($wkb, 'wkb');
        return $geom->out('json');
    }

    /**
     * Convert KML to WTK
     * Checking to make sure the WKT string will make a valid geography instance
     * @param string $kml
     * @return string
     */
    public static function convertKmlToWkt($kml)
    {
        Assets::registerGeoPHP();

        $geom = geoPHP::load($kml, 'kml');
        $wkt = $geom->out('wkt');

        $sql = '
        DECLARE @geog geography = geography::STGeomFromText(:wkt, 4326);
        SET @geog = CASE
            WHEN @geog.MakeValid().EnvelopeAngle() >= 90 THEN @geog.MakeValid().ReorientObject()
            WHEN @geog.STIsValid() = 0 THEN @geog.MakeValid()
            ELSE @geog
        END
        IF @geog.STNumPoints() > 1000
        BEGIN
            SET @geog = @geog.Reduce(10);
        END
        SELECT @geog.STAsText()
        ';

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':wkt', $wkt, PDO::PARAM_STR);

        $results = $command->queryScalar();

        return $results;
    }

    /**
     * Returns the half, one and three mile buffers - six mile is to be determined
     * @param integer $perimeterID  - the id of the fire perimeter
     * @param integer $fourthRing - the miles of the outer ring (alert distance)
     * @return array
     */
    public static function getPerimeterBuffers($perimeterID = null, $fourthRing = null)
    {
        //End result
        $return = null;

        //Perimeter is in our system
        if ($perimeterID)
        {
            $buffers = self::getBuffers($perimeterID);
            //Buffers have already been created and saved, so use them
            if($buffers)
            {
                $return = $buffers;
            }
            //Haven't been cached yet, so create them
            else
            {
                ResPerimeterBuffers::createBuffers($perimeterID);
                $return = self::getBuffers($perimeterID);
            }
        }
        // Initial monitor log check - perimeter isn't in our system
        else
        {
            //Get values for dynamic generation
            $half = Helper::milesToMeters(0.5);
            $one = Helper::milesToMeters(1);
            $three = Helper::milesToMeters(3);
            $outerRing = ($fourthRing) ? Helper::milesToMeters($fourthRing) : null;

            $sql = '
            DECLARE @perimeter geography = geography::STGeomFromText(:wkt, 4326);
            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10)
            END
            SELECT
                @perimeter.STBuffer(:half).STAsText() [half],
                @perimeter.STBuffer(:one).STAsText() [one],
                @perimeter.STBuffer(:three).STAsText() [three]
            ';

            $wkt = Yii::app()->session['firePerimeter'];

            if ($outerRing)
            {
                $sql .= ' ,@perimeter.STBuffer(:outer).STAsText() [outer];';

                $command = Yii::app()->db->createCommand($sql)
                    ->bindParam(':wkt', $wkt, PDO::PARAM_STR)
                    ->bindParam(':half', $half, PDO::PARAM_INT)
                    ->bindParam(':one', $one, PDO::PARAM_INT)
                    ->bindParam(':three', $three, PDO::PARAM_INT)
                    ->bindParam(':outer', $outerRing, PDO::PARAM_INT);
            }
            else
            {
                $command = Yii::app()->db->createCommand($sql)
                    ->bindParam(':wkt', $wkt, PDO::PARAM_STR)
                    ->bindParam(':half', $half, PDO::PARAM_INT)
                    ->bindParam(':one', $one, PDO::PARAM_INT)
                    ->bindParam(':three', $three, PDO::PARAM_INT);
            }

            $return = $command->queryRow();

        }

        return $return;
    }

    /**
     * Description: Retreives buffers with the given perimeter ID
     * @param int $perimeterID
     * @return array - the buffers with wkt
     */
    public static function getBuffers($perimeterID)
    {

        $return = array();

        $sql = 'select l.type, l.geog.ToString() as geog
            from res_perimeter_buffers b
            inner join location l on l.id = b.location_id
            where b.perimeter_id = :perimeter_id';

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':perimeter_id', $perimeterID, PDO::PARAM_INT);

        $results = $command->queryAll();

        if($results)
        {
            //Parse them into the format that the existing geojson work is expecting
            foreach($results as $row)
            {
                $key = preg_split('/ +/',$row['type']);
                $return[$key[0]] = $row['geog'];
            }
        }

        return $return;
    }

    /**
     * Dynamically generates buffers using the STBuffer function
     * @param int $perimeterID
     * @param int $outerRing (optional)
     * @return array of buffers with wkt
     */
    public static function generateBuffers($perimeterID, $fourthRing = null)
    {
        $half = Helper::milesToMeters(0.5);
        $one = Helper::milesToMeters(1);
        $three = Helper::milesToMeters(3);
        $outerRing = ($fourthRing) ? Helper::milesToMeters($fourthRing) : null;

        $command = null;
        $sql = '
                DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = :perimeterID);
                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10);
                END
                SELECT
                    @perimeter.STBuffer(:half).STAsText() [half],
                    @perimeter.STBuffer(:one).STAsText() [one],
                    @perimeter.STBuffer(:three).STAsText() [three]
            ';

        if ($outerRing)
        {
            $sql .= ' ,@perimeter.STBuffer(:outer).STAsText() [outer];';

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
                ->bindParam(':half', $half, PDO::PARAM_INT)
                ->bindParam(':one', $one, PDO::PARAM_INT)
                ->bindParam(':three', $three, PDO::PARAM_INT)
                ->bindParam(':outer', $outerRing, PDO::PARAM_INT);
        }
        else
        {
            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
                ->bindParam(':half', $half, PDO::PARAM_INT)
                ->bindParam(':one', $one, PDO::PARAM_INT)
                ->bindParam(':three', $three, PDO::PARAM_INT);
        }

        $result = $command->queryRow();


        return $result;
    }

    /**
     * Dynamically generates buffers using the STBuffer function
     * @param int $perimeterID
     * @param int $distance
     * @return array the outer buffer entry wkt
     */
    public static function generateBuffer($perimeterID, $distance)
    {
        $outerRing = Helper::milesToMeters($distance);

        $command = null;
        $sql = '
                DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = :perimeterID);
                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10);
                END
                SELECT
                    @perimeter.STBuffer(:outer).STAsText() [outer];';

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT)
            ->bindParam(':outer', $outerRing, PDO::PARAM_INT);

        $result = $command->queryRow();

        return $result;
    }

    /**
     * Retrieves zipcodes binary geog from corresponding perimeter id
     * @param integer|null $noticeID
     * @param integer|null $perimeterID
     * @param string|null $wkt
     * @return array
     */
    public static function getPerimeterZipcodes($noticeID = null, $perimeterID = null, $wkt = null)
    {

        if ($noticeID)
        {
            $sql = '
                SET NOCOUNT ON

                DECLARE
                    @perimeterID int,
                    @perimeter geography,
                    @threatLocationID int

                SET @perimeterID = (SELECT perimeter_id FROM res_notice WHERE notice_id = :noticeID)

                SELECT
                    @perimeter = l.geog,
                    @threatLocationID = p.threat_location_id
                FROM res_perimeters p
                INNER JOIN location l ON p.perimeter_location_id = l.id
                WHERE p.id = @perimeterID

                -- If theat exists, combine with perimeter
                IF @threatLocationID IS NOT NULL
                BEGIN
                    DECLARE @threat geography = (SELECT geog FROM location WHERE id = @threatLocationID);
                    SET @perimeter = (@perimeter.STUnion(@threat));
                    IF @perimeter.STNumPoints() > 1000
                    BEGIN
                        SET @perimeter = @perimeter.Reduce(10);
                    END
                END

                SELECT zipcode
                FROM geog_zipcodes
                WHERE geog.STIntersects(@perimeter) = 1
                ORDER BY zipcode ASC;
            ';

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':noticeID', $noticeID, PDO::PARAM_INT);
        }
        else if ($perimeterID)
        {
            $sql = "
                SET NOCOUNT ON

                DECLARE
                    @perimeterID int = :perimeterID,
                    @perimeter geography,
                    @threatLocationID int

                SELECT
                    @perimeter = l.geog,
                    @threatLocationID = p.threat_location_id
                FROM res_perimeters p
                INNER JOIN location l ON p.perimeter_location_id = l.id
                WHERE p.id = @perimeterID

                -- If theat exists, combine with perimeter
                IF @threatLocationID IS NOT NULL
                BEGIN
                    DECLARE @threat geography = (SELECT geog FROM location WHERE id = @threatLocationID);
                    SET @perimeter = (@perimeter.STUnion(@threat));
                    IF @perimeter.STNumPoints() > 1000
                    BEGIN
                        SET @perimeter = @perimeter.Reduce(10);
                    END
                END
                ELSE
                BEGIN
                    IF @perimeter.STNumPoints() > 1000
                    BEGIN
                        SET @perimeter = @perimeter.Reduce(10)
                    END
                END

                SELECT
                    geog.STAsBinary() geog,
                    zipcode
                FROM geog_zipcodes
                WHERE geog.STIntersects(@perimeter) = 1
                ORDER BY zipcode ASC
            ";

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT);
        }
        else
        {
            $sql = "
                DECLARE @perimeter geography = (geography::STGeomFromText(:wkt, 4326));
                IF @perimeter.STNumPoints() > 1000
                BEGIN
                    SET @perimeter = @perimeter.Reduce(10)
                END
                SELECT zipcode
                FROM geog_zipcodes
                WHERE geog.STIntersects(@perimeter) = 1
                ORDER BY zipcode ASC;
            ";

            $command = Yii::app()->db->createCommand($sql)
                ->bindParam(':wkt', $wkt, PDO::PARAM_STR);
        }

        $results = $command->queryAll();

        return $results;
    }

    /**
     * Summary of erasePerimeterFromThreat
     * @param integer $perimeterID
     * @return string
     */
    public static function erasePerimeterFromThreat($perimeterID)
    {

        $sql = '
            DECLARE @perimeterID int = :perimeterID
            DECLARE @threat geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.threat_location_id = l.id WHERE p.id = @perimeterID);
            DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = @perimeterID);

            SELECT @threat.STDifference(@perimeter).MakeValid().STAsText();
        ';

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':perimeterID', $perimeterID, PDO::PARAM_INT);

        $result = $command->queryScalar();

        return $result;
    }

    /**
     * Accepts coordinates and aces, returns WKT of polygon
     * @param string $lat
     * @param string $lon
     * @param integer $acres
     * @return string
     */
    public static function pointToAcres($lat, $lon, $acres)
    {

        $sql = '
            DECLARE @acres int = :acres;
            DECLARE @perimeter geography = geography::Point(:lat, :lon, 4326);
            DECLARE @radius float(24) = SQRT((@acres/0.00024711) / PI());
            SELECT @perimeter.STBuffer(@radius).STAsText();
        ';

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':lat', $lat, PDO::PARAM_STR)
            ->bindParam(':lon', $lon, PDO::PARAM_STR)
            ->bindParam(':acres', $acres, PDO::PARAM_STR);

        $results = $command->queryScalar();

        return $results;
    }

    /**
     * Gets policyholder models for most recent perimeter on fire.
     * @param integer $clientID
     * @param integer $fireID
     * @return array
     */
    public static function getMonitoredPoliciesByFire($clientID, $fireID)
    {

        $buffer = Helper::milesToMeters(3);

        $sql = "
            DECLARE @clientID int = :clientID;
            DECLARE @perimeterID int = (SELECT MAX(id) FROM res_perimeters WHERE fire_id = :fireID);
            DECLARE @perimeter geography = (SELECT l.geog FROM res_perimeters p INNER JOIN location l ON p.perimeter_location_id = l.id WHERE p.id = @perimeterID);
            IF @perimeter.STNumPoints() > 1000
            BEGIN
                SET @perimeter = @perimeter.Reduce(10);
            END
            DECLARE @buffer geography = @perimeter.STBuffer(:buffer);
            SELECT
                m.first_name fname,
                m.last_name lname,
                p.address_line_1 address,
                p.city,
                p.state,
                p.zip,
                p.coverage_a_amt coverage,
                p.policy,
                m.member_num member_number,
                p.response_status,
                m.home_phone,
                m.cell_phone,
                m.email_1 email
            FROM properties p
                INNER JOIN members m ON p.member_mid = m.mid
            WHERE p.client_id = @clientID
                AND p.policy_status = 'active'
                AND p.type_id = 1
                AND p.geog.STIntersects(@buffer) = 1
        ";

        $command = Yii::app()->db->createCommand($sql)
            ->bindParam(':clientID', $clientID, PDO::PARAM_INT)
            ->bindParam(':fireID', $fireID, PDO::PARAM_INT)
            ->bindParam(':buffer', $buffer, (string) PDO::PARAM_STR);;

        $results = $command->queryAll();

        return $results;
    }

    public static function getWdsGeocodeReport()
    {

        $sql = "
        DECLARE @mile float = 1609.344;

        SELECT *, ROUND(t.distance_meters / @mile, 2) [distance_miles]
        FROM (
            SELECT
                p.pid,
                p.address_line_1,
                p.city,
                p.state,
                p.zip,
                c.name client,
                p.lat client_lat,
                p.long client_long,
                p.geocode_level,
                p.policy,
                p.response_status,
                p.wds_lat,
                p.wds_long,
                geography::Point(p.lat, p.long, 4326).STDistance(p.geog) [distance_meters]
            FROM properties p
            INNER JOIN client c ON p.client_id = c.id
            WHERE p.wds_geocode_level = 'wds'
                AND p.policy_status = 'active'
                AND p.type_id = 1
                AND p.lat IS NOT NULL
                AND p.long IS NOT NULL
                AND p.lat != ''
                AND p.long != ''
                AND ( p.geog IS NOT NULL  OR isnumeric(lat) <> 1 OR isnumeric(long) <> 1)
                AND p.geocode_level IN ('ADDRESS', 'PARCEL', 'PP', 'PR', 'PS', 'Point')
        ) t
        WHERE t.distance_meters > @mile
        ORDER BY t.client, t.pid
        ";

        $results = Yii::app()->db->createCommand($sql)->queryAll();

        return $results;
    }

    /**
     * Takes a kmz/kml file upload and figures out if it's a kmz and needs to be unzipped to get the geo data
     * @param object $uploadedFile
     * @return string (path to file) returns false if failed
     */
    public function getUploadedKmlKmz($uploadedFile)
    {
        // Move file to temp folder
        $tempDir = Yii::getPathOfAlias('webroot.tmp');
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . $uploadedFile->name;
        move_uploaded_file($uploadedFile->tempName, $tempFile);

        // Unzip KMZ if needed
        if ($uploadedFile->extensionName === 'kmz')
        {
            $tempDirUnzipFolder = $tempDir . DIRECTORY_SEPARATOR . pathinfo($uploadedFile->name, PATHINFO_FILENAME);

            $zipArchive = new ZipArchive();
            $zipOpenSuccess = $zipArchive->open($tempFile);

            if ($zipOpenSuccess === true)
            {
                $zipArchive->extractTo($tempDirUnzipFolder);
                $zipArchive->close();
            }
            else
            {
                return false;
            }

            $fileData = file_get_contents($tempDirUnzipFolder . DIRECTORY_SEPARATOR . 'doc.kml');
            $tempFileKml = $tempDirUnzipFolder . '.kml';
            file_put_contents($tempFileKml, $fileData);

            // Clean up temp file
            if (file_exists($tempFile))
            {
                @unlink($tempFile);
            }

            // Clean up the extracted zip file
            $iterator = new \RecursiveDirectoryIterator($tempDirUnzipFolder, \FilesystemIterator::SKIP_DOTS);
            $recursiveIterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($recursiveIterator as $fileInfo)
            {
                if ($fileInfo->isFile())
                {
                    @unlink($fileInfo->getPathName());
                }
                else
                {
                    rmdir($fileInfo);
                }
            }

            rmdir($tempDirUnzipFolder);

            $filePath = rawurlencode(basename($tempFileKml));
        }
        else
        {
            $filePath = rawurlencode($uploadedFile->name);
        }

        return $filePath;
    }

    public static function writeDailyGaccMapToFile()
    {
        Assets::registerGeoPHP();

        $ratings = array(
            'extreme' => '#FF0000',
            'high' => '#FF8000',
            'moderate' => '#FFFF00',
            'low' => '#38A800',
        );

        $sql1 = '
            DECLARE @wdsstates geography = (SELECT geography::UnionAggregate(geog) FROM geog_states INNER JOIN wds_states ON geog_states.id = wds_states.state_id)
            SELECT name, label, geog.STIntersection(@wdsstates).STAsText() geog FROM geog_gacc
        ';

        $sql2 = '
            SELECT TOP 1
                eastern,
                southern,
                southwest,
                california_south,
                california_north,
                great_basin,
                rocky_mountains,
                northern_rockies,
                northwest,
                alaska,
                fx_eastern,
                fx_southern,
                fx_southwest,
                fx_california_south,
                fx_california_north,
                fx_great_basin,
                fx_rocky_mountains,
                fx_northern_rockies,
                fx_northwest,
                fx_alaska
            FROM res_daily_threat ORDER BY threat_id DESC
        ';

        $command = Yii::app()->db->createCommand();

        $resultsGacc = $command->setText($sql1)->queryAll();
        $resultsDailyThreat = $command->setText($sql2)->queryRow();

        $featureCollection = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );

        foreach ($resultsGacc as $result)
        {
            $geom = geoPHP::load($result['geog'], 'wkt');
            $geojson = json_decode($geom->out('json'), true);
            $rating = $resultsDailyThreat[$result['name']];
            $ratingFx = $resultsDailyThreat['fx_' . $result['name']];
            $color = isset($ratings[$rating]) ? $ratings[$rating] : '#0000FF';

            // Rounding decimal values to lower precision/file size ... doesn't matter when displayed on such a small scale
            array_walk_recursive($geojson, function(&$value, $key) { if (is_float($value)) $value = round($value, 4); });

            $featureCollection['features'][] = array(
                'type' => 'Feature',
                'geometry' => $geojson,
                'properties' => array(
                    'name' => $result['name'],
                    'label' => $result['label'],
                    'rating' => $rating,
                    'ratingfx' => $ratingFx,
                    'color' => $color
                )
            );
        }

        $filepath = Yii::getPathOfAlias('application.downloads') . DIRECTORY_SEPARATOR . 'gacc_map.json';

        file_put_contents($filepath, json_encode($featureCollection));
    }
}

?>
