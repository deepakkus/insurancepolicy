<?php

class ViewPolicyActionForm extends CFormModel
{
    public $clientID;
    public $fireID;
    public $policyholders;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('clientID, fireID', 'required'),
            array('clientID, fireID', 'numerical', 'integerOnly' => true),
            array('clientID, fireID, policyholders', 'safe')
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array(
            'clientID' => 'Client',
            'fireID' => 'Fire',
            'policyholders' => 'Policyholders'
        );
    }

    /**
     * Returning list data for dispatched fires in last 6 months
     * @param integer $clientID
     * @return array
     */
    public static function getDispatchedFires($clientID)
    {
        $criteria = new CDbCriteria;

        $criteria->select = array('Fire_ID', 'Name');
        $criteria->params['client_id'] = $clientID;
        $criteria->condition = '
            Fire_ID IN (
                SELECT fire_id
                FROM res_notice
                WHERE client_id = :client_id
                    AND wds_status = 1
                    AND date_created >= DATEADD(MONTH, -6, GETDATE())
                GROUP BY fire_id
            ) 
        ';
        $criteria->order = 'Name ASC'; 
        return CHtml::listData(ResFireName::model()->findAll($criteria), 'Fire_ID', 'Name');
    }

    /**
     * Retrieving data provider
     * @return CSqlDataProvider
     */
    public function getSQLDataProvider()
    {
        $sql = self::getPolicySQL($this->policyholders);

        // Get total number for the query - used for pagination. str_replace is kind of hacky...
        $countSQL = str_replace('SELECT * FROM', 'SELECT COUNT(*) FROM', $sql);
        $countSQL = str_replace('ORDER BY r.threat DESC', '', $countSQL);

        $count = Yii::app()->db->createCommand($countSQL)->queryScalar(array(
            ':client_id1' => $this->clientID,
            ':client_id2' => $this->clientID,
            ':client_id3' => $this->clientID,
            ':client_id4' => $this->clientID,
            ':fire_id1' => $this->fireID,
            ':fire_id2' => $this->fireID,
            ':fire_id3' => $this->fireID,
            ':fire_id4' => $this->fireID
        ));

        return new WDSCSqlDataProvider($sql, array(
            'params' => array(
                ':client_id1' => $this->clientID,
                ':client_id2' => $this->clientID,
                ':client_id3' => $this->clientID,
                ':client_id4' => $this->clientID,
                ':fire_id1' => $this->fireID,
                ':fire_id2' => $this->fireID,
                ':fire_id3' => $this->fireID,
                ':fire_id4' => $this->fireID
            ),
            'keyField' => 'pid',
            'totalItemCount' => $count,
            'sort' => array(
                'defaultOrder'=>array('distance' => CSort::SORT_ASC),
                'attributes' => array('pid','distance','date_action')
            ),
            'pagination' => array(
                'pageSize' => 25
            )
        ));
    }

    /**
     * Used for the map and the grid in the policy actions viwer
     * @param string $policyholders - 0 = engine has not visited, 1 = engine has visited, 2 = all policyholders (visited and not visited), 3 = visited today
     * @return array returnArray
     */
    public static function getPolicySQL($policyholders)
    {
        // Figure out what hte policyholder criteria is - default to all
        $where = '';

        // Engine has not visited
        if($policyholders == 0)
        {
            $where = 'WHERE r.date_action IS NULL';
        }
        // Engine has visited
        else if ($policyholders == 1)
        {
            $where = 'WHERE r.date_action IS NOT NULL';
        }
        else if ($policyholders == 3)
        {
            $date = date('Y-m-d');
            $where = "WHERE r.date_action >= '$date'";
        }

        // Note, can't use MSSQL DECLARE with CSqlDataProvider
        // CMssqlCommandBuilder->applyLimit() method can't deal with the first page pagination

        // This query is dumb complex because the users want two datasets from different sources combined into one ... thus the UNION

        $sql= "
        SELECT * FROM (

            SELECT
                p.pid,
                t.threat,
                t.distance,
                ISNULL(t.response_status, p.response_status) response_status,
                ISNULL(t.geog.Lat, p.geog.Lat) lat,
                ISNULL(t.geog.Long, p.geog.Long) long,
                m.first_name,
                m.last_name,
                p.address_line_1,
                p.city,
                p.state,
                p.policy,
                m.member_num,
                a.date_action
            FROM properties p
            INNER JOIN members m on m.mid = p.member_mid
            INNER JOIN (
                SELECT DISTINCT property_pid
                FROM res_triggered t
                WHERE t.notice_id IN (
                    SELECT notice_id FROM res_notice WHERE fire_id = :fire_id1 AND client_id = :client_id1
                )
                UNION
                SELECT DISTINCT property_pid
                FROM res_ph_visit
                WHERE fire_id = :fire_id2 AND client_id = :client_id2
            ) pids ON pids.property_pid = p.pid
            LEFT OUTER JOIN (
                SELECT property_pid, date_action, fire_id
                FROM res_ph_visit
                WHERE id IN (
                    SELECT MAX(id) id
                    FROM res_ph_visit
                    WHERE fire_id = :fire_id3 AND client_id = :client_id3
                    GROUP BY property_pid
                )
            ) a ON p.pid = a.property_pid
            LEFT OUTER JOIN res_triggered t ON t.property_pid = p.pid
            WHERE t.id IN (
                SELECT MAX(id) FROM res_triggered WHERE property_pid IN (
                    SELECT DISTINCT property_pid
                    FROM res_triggered t
                    WHERE t.notice_id IN (
                        SELECT notice_id FROM res_notice WHERE fire_id = :fire_id4 AND client_id = :client_id4
                    )
                ) GROUP BY property_pid
            ) OR t.id IS NULL

        ) r
        $where
        ORDER BY r.threat DESC";

        return $sql;
    }
}
