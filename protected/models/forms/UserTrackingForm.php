<?php

class UserTrackingForm extends CFormModel
{
    public $userID;
    public $startDate;
    public $endDate;

    public $route;
    public $platformID;
    
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
            array('userID', 'required'),
            array('userID, platformID', 'numerical', 'integerOnly'=>true),
            array('startDate, endDate, route', 'safe')
		);
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		return array(
            'userID' => 'User',
            'startDate' => 'Start Date',
            'endDate' => 'End Date'
		);
	}

    /**
     * Getting data for all users who are currently tracked in the user_tracking table
     * @return array
     */
    public function getTrackedUsers()
    {
        $users = User::model()->findAllBySql('SELECT id, username FROM [user] WHERE id in (
	        SELECT DISTINCT user_id FROM user_tracking
        ) ORDER BY username');

        return CHtml::listData($users, 'id', 'username');
    }

    /**
     * Retrieves user tracking stats by user_id
     * @return array
     */
    public function getTrackedUserStats()
    {
        $sql = "
        DECLARE @startdate datetime = :start_date;
        DECLARE @enddate datetime = :end_date;

        SELECT
            COUNT(t.route) count,
            t.route,
            p.platform,
            t.platform_id,
            FORMAT(MAX(t.date), 'yyyy-MM-dd hh:mm') date
        FROM user_tracking t
            INNER JOIN user_tracking_platform p ON t.platform_id = p.id
        WHERE t.user_id = :user_id
            AND t.date >= ISNULL(@startdate, CONVERT(DATETIME, '1990-01-01'))
            AND t.date < ISNULL(@enddate, GETDATE())
        GROUP BY t.route, p.platform, t.platform_id
        ORDER BY p.platform, count DESC --t.route";

        return Yii::app()->db->createCommand($sql)->queryAll(true, array(
            ':user_id' => $this->userID,
            ':start_date' => !empty($this->startDate) ? $this->startDate : null,
            ':end_date' => !empty($this->endDate) ? $this->endDate : null
        ));
    }
}
