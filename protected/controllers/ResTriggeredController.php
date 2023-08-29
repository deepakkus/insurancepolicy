<?php

class ResTriggeredController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_DASH => array(
                        'apiGetGeoJson',
                        'apiGetGeoJsonMultipleClients',
                        'apiGetCountsTriggeredProgramFiresByDate'
                    ),
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetGeoJsonMultipleClients'
                    ),
                )
            )
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array(),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array(
                    'apiGetGeoJson',
                    'apiGetGeoJsonMultipleClients',
                    'apiGetCountsTriggeredProgramFiresByDate'
                ),
                'users' => array('*')),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
            )
        );
    }

    /**
     * API method: resTriggered/apiGetGeoJson
     * Returns a geojson object of triggered policyholders for a map
     *
     * Post data parameters:
     * @param integer noticeID
     * @param integer clientID
     * @param integer realTime
     * @param integer perimeterID
     *
     * Post data example:
     * {
     *     "data": {
     *         "noticeID": 123,
     *         "clientID": 9
     *     }
     * }
     */
    public function actionApiGetGeoJson()
    {
        if (!WDSAPI::getInputDataArray($data, array('clientID')))
            return;

        $realTime = (isset($data['realTime'])) ? $data['realTime'] : 0;
        $triggered = (isset($data['triggered'])) ? $data['triggered'] : 0;
        $noticeID = (isset($data['noticeID'])) ? $data['noticeID'] : 0;
        $perimeterID = (isset($data['perimeterID'])) ? $data['perimeterID'] : 0;

        $result = ($triggered)
            ? ResTriggered::getGeoJsonTriggered($noticeID, $data['clientID'], $realTime) //uses the notice id to select entries by attributes
            : ResTriggered::getGeoJsonAll($perimeterID, $data['clientID'], $realTime);  //uses the perimeter to select entries spatially

        if ($result)
        {
            $returnArray['error'] = 0;
            $returnArray['data'] = $result;
        }
        else
        {
            $returnArray['error'] = 0;
            $returnArray['data'] = null;
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resTriggered/apiGetGeoJsonMultipleClients
     * Returns a geojson object of triggered policyholders for multiple clients for a map
     *
     * Post data parameters:
     * @param integer clientID
     * @param integer perimeterID
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 3,
     *         "perimeterID": 12459
     *     }
     * }
     */
    public function actionApiGetGeoJsonMultipleClients()
    {
        if (!WDSAPI::getInputDataArray($data, array('clientIDs', 'perimeterID')))
            return;

        $result = ResTriggered::getGeoJsonMultipleClients($data['clientIDs'], $data['perimeterID']);

        if ($result)
        {
            $returnArray['error'] = 0; // success
            $returnArray['data'] = $result;
        }
        else
        {
            $returnArray['error'] = 0;
            $returnArray['data'] = null;
        }

        return WDSAPI::echoResultsAsJson($returnArray);
    }

    /**
     * API method: resTriggered/apiGetCountsTriggeredProgramFiresByDate
     * Returns a geojson object of triggered policyholders for multiple clients for a map
     *
     * @param integer clientID
     * @param string startDate
     * @param string endDate
     * @param string statusType - (optional) wds_status of program fires ("Dispatched" by default)
     *
     * Post data example:
     * {
     *     "data": {
     *         "clientID": 3,
     *         "startDate": "2015-02-04",
     *         "endDate": "2016-05-12"
     *     }
     * }
     */
    public function actionApiGetCountsTriggeredProgramFiresByDate()
    {
        if (!WDSAPI::getInputDataArray($data, array('clientID', 'startDate', 'endDate')))
            return;

        $statusType = isset($data['statusType']) ? $data['statusType'] : 'Dispatched';

        $sql = "
        SET NOCOUNT ON;

        DECLARE
            @statusType varchar(20) = :status_type,
            @startdate datetime = :start_date,
            @enddate datetime = :end_date,
            @clientID int = :client_id,
            @statusTypeId int
			Set @statusTypeId  = (SELECT id FROM res_status WHERE status_type = @statusType)
			
        -- Declare temp table to aggregate data into
        DECLARE @data TABLE(
            [fire_id] int,
            [name] varchar(200),
            [date_created] datetime,
            [count] int,
            [date_demobilized] datetime,
            [highest_exposure_enrolled] int,
            [highest_exposure_not_enrolled] int,
            [highest_threatened_enrolled] int,
            [highest_threatened_not_enrolled] int
        );

        -- Insert first few columns of data
        INSERT INTO @data ([fire_id], [name], [date_created], [count])
		SELECT
            f.Fire_ID [fire_id],
            f.Name [name],
            f.Date_Created [date_created],
            COUNT(DISTINCT t.property_pid) [count]
        FROM res_triggered t
            INNER JOIN res_notice n ON t.notice_id = n.notice_id
            INNER JOIN res_fire_name f ON n.fire_id = f.Fire_ID
        WHERE n.wds_status = (SELECT id FROM res_status WHERE status_type = @statusType)
            AND n.client_id = @clientID
            AND n.date_created >= @startdate
            AND n.date_created < @enddate
        GROUP BY f.Fire_ID, f.Name, f.Date_Created
        ORDER BY f.Date_Created DESC

		-- Collect filtered data into temp table 		
		SELECT SUM(t.coverage) [sum_coverage], n.fire_id, n.notice_id,n.wds_status,n.date_created,t.threat,  SUM(t.threat) [sum_threat],t.response_status   into  #vw_temp
                FROM res_triggered t
                    INNER JOIN res_notice n ON t.notice_id = n.notice_id
                WHERE n.client_id = @clientID
                    AND n.date_created >= @startdate
                    AND n.date_created < @enddate
                   GROUP BY n.fire_id, n.notice_id,n.wds_status,n.date_created,t.threat,t.response_status
				

         -- Updating max coverage by fire for threatened and enrolled
        UPDATE d
        SET highest_exposure_enrolled = ISNULL(t.max_coverage, 0)
        FROM @data d
		 LEFT OUTER JOIN (
		  SELECT  MAX(t.sum_coverage) [max_coverage],t.fire_id FROM #vw_temp t
				WHERE  t.threat = 1 
				AND t.response_status = 'enrolled' 
				AND t.wds_status = @statusTypeId
				GROUP BY t.fire_id
		 )t ON d.fire_id = t.fire_id
		 
		        
        -- Updating max coverage by fire for threatened and not enrolled
        
		UPDATE d
        SET highest_exposure_not_enrolled = ISNULL(t.max_coverage, 0)
        FROM @data d
		 LEFT OUTER JOIN (
		  SELECT  MAX(t.sum_coverage) [max_coverage],t.fire_id FROM #vw_temp t
				WHERE  t.threat = 1 
				AND t.response_status = 'not enrolled' 
				AND t.wds_status = @statusTypeId
				GROUP BY t.fire_id
		 )t ON d.fire_id = t.fire_id
		
		 -- Updating max threatened by fire for threatened and enrolled
		UPDATE d
        SET highest_threatened_enrolled = ISNULL(t.max_threat, 0)
        FROM @data d
		 LEFT OUTER JOIN (
		  SELECT  MAX(t.sum_threat) [max_threat],t.fire_id FROM #vw_temp t
				WHERE  t.threat = 1 
				AND t.response_status = 'enrolled' 
				AND t.wds_status = @statusTypeId
				GROUP BY t.fire_id
		 )t ON d.fire_id = t.fire_id
		
		 -- Updating max threatened by fire for threatened and not enrolled
		UPDATE d
        SET highest_threatened_not_enrolled = ISNULL(t.max_threat, 0)
        FROM @data d
		 LEFT OUTER JOIN (
		  SELECT  MAX(t.sum_threat) [max_threat],t.fire_id FROM #vw_temp t
				WHERE  t.threat = 1 
				AND t.response_status = 'not enrolled' 
				AND t.wds_status = @statusTypeId
				GROUP BY t.fire_id
		 )t ON d.fire_id = t.fire_id
		
		 -- Updating demobilized data, if exists
		UPDATE d
        SET date_demobilized = x.date_created
        FROM @data d
        LEFT OUTER JOIN (
            SELECT t.fire_id, t.date_created FROM  #vw_temp t
			where t.notice_id in(
			SELECT MIN(notice_id) [notice_id]
                FROM #vw_temp
                WHERE wds_status = @statusTypeId                   
                GROUP BY fire_id
				)
			  )x ON d.fire_id = x.fire_id
		   	
              
  SELECT * FROM @data ORDER BY date_created ASC
  drop table #vw_temp
        ";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':client_id', $data['clientID'], PDO::PARAM_INT)
            ->bindParam(':status_type', $statusType, PDO::PARAM_STR)
            ->bindParam(':start_date', $data['startDate'], PDO::PARAM_STR)
            ->bindParam(':end_date', $data['endDate'], PDO::PARAM_STR)
            ->queryAll();

        return WDSAPI::echoResultsAsJson(array('error' => 0, 'data' => $results));
    }
}
