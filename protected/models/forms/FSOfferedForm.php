<?php

class FSOfferedForm extends CFormModel
{
    public $states;
    public $responseEnrolledDate;
    public $includeResponseNotEnrolled;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('states, responseEnrolledDate', 'required'),
            array('includeResponseNotEnrolled', 'numerical', 'integerOnly' => true)
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array(
            'states' => 'States',
            'responseEnrolledDate' => 'Response Enrolled Date'
        );
    }

    /**
     * Creates and downloads the FS Offered CSV
     */
    public function getFSOfferedCSV()
    {
        // Getting array of states means indexed by state abbr (Ex: "CA")
        $stateMeans = $this->getStateMeans();

        $sql = "
            DECLARE @enrolledDate datetime = :enrolled_date

            SELECT
                p.state,
                m.member_num,
                m.first_name,
                m.last_name,
                m.fs_carrier_key,
                p.lob,
                p.geo_risk,
                p.pre_risk_status,
                p.response_status,
                p.fireshield_status,
                r.score_wds
            FROM properties p
            INNER JOIN members m ON p.member_mid = m.mid
			LEFT OUTER JOIN (
				SELECT * FROM risk_score WHERE id IN (
					SELECT MAX(id) FROM risk_score where client_id = 1 GROUP BY property_pid
				)
			) r ON r.property_pid = p.pid
            WHERE p.type_id = 1
                AND ((m.email_1 IS NOT NULL AND m.email_1 != '') OR (m.email_2 IS NOT NULL AND m.email_2 != ''))
                AND p.policy_status = 'active'
                AND p.client_id = 1
                AND p.state IN ('" . implode("','", $this->states) . "')
                AND p.response_enrolled_date < @enrolledDate
                AND p.fireshield_status IN ('offered', 'not enrolled')
                AND p.pre_risk_status NOT IN ('enrolled','declined')
        ";

        if ($this->includeResponseNotEnrolled == false)
            $sql .= " AND p.response_status = 'enrolled'";

        $sql .= " ORDER BY p.response_status, m.member_num";

        $results = Yii::app()->db->createCommand($sql)
            ->bindParam(':enrolled_date', $this->responseEnrolledDate, PDO::PARAM_STR)
            ->queryAll(true);

        $filename = 'fs_offered_list_foruser-' . Yii::app()->user->id . '_' . date('Y-m-d') . '.csv';
        $filepath = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . $filename;

        // Write csv file to disk
        $csvfile = fopen($filepath, 'w');

        fputcsv($csvfile, array('member_num','first_name','last_name','fs_carrier_key','lob','geo_risk','wds_risk','std_dev','pre_risk_status','response_status','fireshield_status'));

        foreach ($results as $result)
        {
            $wds_risk_score = $state_mean = $state_std_dev = $dev_text = 'n/a';

            // Check that wds_score is valid and a state_mean/std_dev exists
            if ($result['score_wds'] && isset($stateMeans[$result['state']]))
            {
                $wds_risk_score = $result['score_wds'];

                $state_std_dev = $stateMeans[$result['state']]['std_dev'];
                $state_mean = $stateMeans[$result['state']]['mean'];
                $dev_text = RiskScore::getStandardDevText($state_mean, $state_std_dev, $wds_risk_score);
            }

            fputcsv($csvfile, array(
                $result['member_num'],
                $result['first_name'],
                $result['last_name'],
                $result['fs_carrier_key'],
                $result['lob'],
                $result['geo_risk'],
                $wds_risk_score,
                $dev_text,
                $result['pre_risk_status'],
                $result['response_status'],
                $result['fireshield_status']
            ));
        }

        fclose($csvfile);

        // Download csv file
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        header('Content-Type: text/csv');
        header('Content-Length: ' . filesize($filepath));
        // Downloading file by chunks in case this turns out to be a large file (possible)
        $this->streamFile($filepath);
        //readfile($filepath);
        ignore_user_abort(true);
        unlink($filepath);
        exit(0);

        /*
        $criteria = new CDbCriteria;
        $criteria->select = array('pid','state','lob','geo_risk','pre_risk_status','response_status','fireshield_status','member_mid');
        $criteria->with = array('member', array(
            'select' => array('mid','member_num','first_name','last_name','fs_carrier_key')
        ));
        $criteria->condition = "
            t.type_id = 1
            AND ((member.email_1 IS NOT NULL AND member.email_1 != '') OR (member.email_2 IS NOT NULL AND member.email_2 != ''))
            AND t.policy_status = 'active'
            AND t.client_id = 1
            AND t.state IN ('" . implode("','", $this->states) . "')
            AND t.response_enrolled_date < :enrolledDate
            AND t.fireshield_status IN ('offered', 'not enrolled')
            AND t.pre_risk_status NOT IN ('enrolled','declined')
        ";
        $criteria->order = 't.response_status, member.member_num';
        $criteria->params = array(':enrolledDate' => $this->responseEnrolledDate);

        if ($this->includeResponseNotEnrolled == false)
            $criteria->condition .= " AND t.response_status = 'enrolled'";

        $properties = Property::model()->findAll($criteria);

        // Setup import_results file
        $file_name = 'fs_offered_list_foruser-' . Yii::app()->user->id . '_' . date('Y-m-d') . '.csv';
        $file_path = Helper::getDataStorePath() . 'tmp' . DIRECTORY_SEPARATOR . $file_name;
        $csvfile = fopen($file_path, 'w');

        fputcsv($csvfile, array('member_num','first_name','last_name','fs_carrier_key','lob','geo_risk','wds_risk','std_dev','pre_risk_status','response_status','fireshield_status'));

        foreach ($properties as $property)
        {
            $wds_risk_score = $state_mean = $state_std_dev = $dev_text = 'n/a';

            if ($property->wdsRisk && isset($property->wdsRiskStateMeans) && isset($property->wdsRiskDev))
            {
                $wds_risk_score = $property->wdsRisk->score_wds;
                $state_std_dev = $property->wdsRiskStateMeans->std_dev;
                $state_mean = $property->wdsRiskStateMeans->mean;
                $dev_text = RiskScore::getStandardDevText($state_mean, $state_std_dev, $wds_risk_score);
            }

            fputcsv($csvfile, array(
                $property->member->member_num,
                $property->member->first_name,
                $property->member->last_name,
                $property->member->fs_carrier_key,
                $property->lob,
                $property->geo_risk,
                $wds_risk_score,
                $dev_text,
                $property->pre_risk_status,
                $property->response_status,
                $property->fireshield_status
            ));
        }

        fclose($csvfile);

        header('Content-Description: FS Offered File Download');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.$file_name.'"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: '.filesize($file_path));
        readfile($file_path);
        exit;
        */
    }

    /**
     * Getting current state means in the following format
     *
     * [
     *     "CA": [ "std_dev": "1234", "mean": "0.5" ],
     *     "AZ": [ "std_dev": "1235", "mean": "0.4" ]
     * ]
     *
     * @return array[]
     */
    private function getStateMeans()
    {
        $sql = '
            SELECT s.id, s.abbr, m.mean, m.std_dev
            FROM geog_states s
            INNER JOIN risk_state_means m ON s.id = m.state_id
        ';

        $results = Yii::app()->db->createCommand($sql)->queryAll(true);

        $returnArray = array();

        foreach ($results as $result)
        {
            $returnArray[$result['abbr']] = array(
                'mean' => $result['mean'],
                'std_dev' => $result['std_dev']
            );
        }

        return $returnArray;
    }

    /**
     * Stream File in case this is a large file (possible)
     * @param string $filepath
     * @return boolean
     */
    private function streamFile($filepath)
    {
        $buffer = '';
        $chunkSize = 1024 * 1024;
        $handle = fopen($filepath, 'rb');
        while (!feof($handle))
        {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }
        $status = fclose($handle);
        return $status;
    }
}