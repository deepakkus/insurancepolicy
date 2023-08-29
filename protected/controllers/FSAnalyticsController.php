<?php

class FSAnalyticsController extends Controller
{
    /**
	 * @var string the default layout for the views.
	 */
	public $layout = '//layouts/column1';

    /**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl'
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
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions' => array(
                    'admin',
                    'fsOffered'
                ),
				'users'=>array('@'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

    /**
	 * Administration page for FSAnalytics.
	 */
	public function actionAdmin()
	{
        $startDate = NULL;
        $endDate = NULL;

        // Apply the print layout if print=true is in the URL query string.
        if (isset($_GET['print']))
        {
            if ($_GET['print'] == 'true')
            {
                $this->layout = '//layouts/FSMetricsPrintable';
            }
        }

        if (isset($_POST['analyticsParamStartDate']))
            $startDate = $_POST['analyticsParamStartDate'];
        else if (isset($_GET['startDate']))
            $startDate = $_GET['startDate'];
        else
            $startDate = date('m/01/Y');

        if (isset($_POST['analyticsParamEndDate']))
            $endDate = $_POST['analyticsParamEndDate'];
        else if (isset($_GET['endDate']))
            $endDate = $_GET['endDate'];
        else
            $endDate = date('m/d/Y');

        $firstOfYear = date('01/01/Y');
        $firstOfMonth = date('m/01/Y');
        $firstOfPeriod = date('01/01/2000');
        $today = date('m/d/Y');

        // Retrieve the data for the view. The summary data is retrieved four times for various date ranges.
        $summaryMTD = $this->getAdminSummaryData($firstOfMonth, $today);
        $summaryYTD = $this->getAdminSummaryData($firstOfYear, $today);
        $summaryPTD = $this->getAdminSummaryData($firstOfPeriod, $today);
        $selectedSummary = $this->getAdminSummaryData($startDate, $endDate);
        $dataProviderMemberSummary = $this->getAdminMemberListData($startDate, $endDate);
        $conditionResponseSummary = $this->getAdminConditionResponseSummary($startDate, $endDate);

        // Render the view with data.
        $this->render('admin', array(
            'selectedSummary' => $selectedSummary,
            'summaryMTD' => $summaryMTD,
            'summaryYTD' => $summaryYTD,
            'summaryPTD' => $summaryPTD,
            'dataProviderMemberSummary' => $dataProviderMemberSummary,
            'conditionResponseSummary' => $conditionResponseSummary,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ));
    }

    public function actionFsOffered()
    {
        $fsOfferedForm = new FSOfferedForm;
        $fsOfferedForm->includeResponseNotEnrolled = true;

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'fs-offered-form')
		{
			echo CActiveForm::validate($fsOfferedForm);
			Yii::app()->end();
		}

        if (isset($_POST['FSOfferedForm']))
        {
            $fsOfferedForm->attributes = $_POST['FSOfferedForm'];
            if ($fsOfferedForm->validate())
            {
                $fsOfferedForm->getFSOfferedCSV();
            }
        }

        $this->render('fs_offered', array(
            'fsOfferedForm' => $fsOfferedForm,
        ));
    }

    /**
     * Get the metrics summary data
     */
    private function getAdminSummaryData($startDate, $endDate)
    {
        $results = $this->getAdminSummaryTotalledData($startDate, $endDate);
        $memPropResults = $this->getAdminSummaryMemPropData($startDate, $endDate);

        // Prepare the model.
        $data = new FSAnalyticsSummary($memPropResults);

        $totalCompleted = count($results);

        if ($totalCompleted == 0)
            return $data;

        $totalTakeUpTime = 0;
        $totalCycleTime = 0;
        $totalCompletedWithinSLO = 0;

        // Sum up the totals for computing the averages.
        foreach ($results as $row)
        {
            $totalTakeUpTime += $row['take_up_time'];
            $totalCycleTime += $row['cycle_time'];

            if ($row['cycle_time'] <= 5) // Under 5 business days
            {
                $totalCompletedWithinSLO++;
            }
        }

        $this->processLOSBreakdownAndServiceLevelSummary($startDate, $endDate, $data);

        $data->avgCycleTime = $totalCycleTime / $totalCompleted;
        $data->avgTakeUpTime = $totalTakeUpTime / $totalCompleted;
        $data->completedWithinSLO = $totalCompletedWithinSLO / $totalCompleted;
        $data->additionalCompleted = $data->totalCompleted - $data->propertyCompleted;

        return $data;
    }

    private function processLOSBreakdownAndServiceLevelSummary($startDate, $endDate, &$data)
    {
        $fsReports = $this->getCompletedReports($startDate, $endDate);

        $totalCompleted = count($fsReports);

        if ($totalCompleted == 0)
            return;

        foreach ($fsReports as $report)
        {
            $geoRisk = $report['geo_risk'];
            if($geoRisk == 99 || empty($geoRisk))
                $geoRisk = 1;

            $conditionRisk = $report['condition_risk'];
            if (!isset($conditionRisk))
                $conditionRisk = 0;

            // Counts for Level of Service Breakdown matrix
            if ($conditionRisk < 9)
            {
                if ($geoRisk == 1)
                    ++$data->LOSBreakdown[2][0]; //level 1
                else if ($geoRisk == 2)
                    ++$data->LOSBreakdown[1][0]; //level 1
                else if ($geoRisk == 3)
                    ++$data->LOSBreakdown[0][0]; //level 2
            }
            else if ($conditionRisk >= 9 && $conditionRisk <= 27)
            {
                if ($geoRisk == 1)
                    ++$data->LOSBreakdown[2][1]; //level 1
                else if ($geoRisk == 2)
                    ++$data->LOSBreakdown[1][1]; //level 2
                else if ($geoRisk == 3)
                    ++$data->LOSBreakdown[0][1]; //level 2
            }
            else if ($conditionRisk > 27)
            {
                if ($geoRisk == 1)
                    ++$data->LOSBreakdown[2][2]; //level 1
                else if ($geoRisk == 2)
                    ++$data->LOSBreakdown[1][2]; //level 2
                else if ($geoRisk == 3)
                    ++$data->LOSBreakdown[0][2]; //level 3
            }

            $riskLevel = $report['risk_level'];
            $origRiskLevel = $report['orig_risk_level'];

            // Count level of service moves
            if ($origRiskLevel == 1 && $riskLevel == 2)
                ++$data->LOSMove[0];
            else if ($origRiskLevel == 2 && $riskLevel == 3)
                ++$data->LOSMove[1];
            else if ($origRiskLevel == 3 && $riskLevel == 2)
                ++$data->LOSMove[2];
            else if ($origRiskLevel == 2 && $riskLevel == 1)
                ++$data->LOSMove[3];
            else if ($origRiskLevel == 1 && $riskLevel == 3)
                ++$data->LOSMove[4];
            else if ($origRiskLevel == 3 && $riskLevel == 1)
                ++$data->LOSMove[5];
            else
                ++$data->LOSMove[6];

            // Counts for the various risk levels (AKA LOS)
            switch ($riskLevel)
            {
                case 1:
                    ++$data->serviceLevelSummary[0];
                    break;
                case 2:
                    ++$data->serviceLevelSummary[1];
                    break;
                case 3:
                    ++$data->serviceLevelSummary[2];
                    break;
            }
        }

        // Calculate Service Level Summary percentages
        $data->serviceLevelSummaryPercentages[0] = $data->serviceLevelSummary[0] / $totalCompleted;
        $data->serviceLevelSummaryPercentages[1] = $data->serviceLevelSummary[1] / $totalCompleted;
        $data->serviceLevelSummaryPercentages[2] = $data->serviceLevelSummary[2] / $totalCompleted;

        // Calculate the LOS breakdown percentages
        for ($i = 0; $i <= 2; $i++)
        {
            for ($j = 0; $j <= 2; $j++)
            {
                $data->LOSBreakdownPercentages[$i][$j] = $data->LOSBreakdown[$i][$j] / $totalCompleted;
            }
        }

        // Calculate the LOS Move percentages
        for ($i = 0; $i < count($data->LOSMove); $i++)
        {
            $data->LOSMovePercentages[$i] = $data->LOSMove[$i] / $totalCompleted;
        }

        // Calculate the non-take up percentages
        if ($data->propertyReleased > 0)
        {
            for ($i = 0; $i < 3; $i++)
            {
                $data->nonTakeUpPercentages[$i] = $data->nonTakeUpTotals[$i] / $data->propertyReleased;
            }
        }
    }

    private function getCompletedReports($startDate, $endDate)
    {
        $sql = "EXECUTE sp_fs_metrics_get_reports @start_date=:start_date_value, @end_date=:end_date_value";

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);

        // Make sure the date parameters are in a SQL-friendly format.
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d H:i', strtotime($this->adjustEndDateToMidnight($endDate)));

        $command->bindValue(":start_date_value", $startDate);
        $command->bindValue(":end_date_value", $endDate);

        // Execute the stored procedure
        return $command->queryAll();
    }

    private function getAdminSummaryMemPropData($startDate, $endDate)
    {
        $sql = "EXECUTE sp_fs_metrics_get_mem_prop_summary @start_date=:start_date_value, @end_date=:end_date_value";

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);

        // Make sure the date parameters are in a SQL-friendly format.
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d H:i', strtotime($this->adjustEndDateToMidnight($endDate)));

        $command->bindValue(":start_date_value", $startDate);
        $command->bindValue(":end_date_value", $endDate);

        // Execute the stored procedure
        return $command->queryAll();
    }

    private function getAdminSummaryTotalledData($startDate, $endDate)
    {
        $sql = "EXECUTE sp_fs_metrics_get_summary @start_date=:start_date_value, @end_date=:end_date_value";

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);

        // Make sure the date parameters are in a SQL-friendly format.
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d H:i', strtotime($this->adjustEndDateToMidnight($endDate)));

        $command->bindValue(":start_date_value", $startDate);
        $command->bindValue(":end_date_value", $endDate);

        // Execute the stored procedure
        return $command->queryAll();
    }

    /**
     * Retrieves data for the Member Summary grid.
     */
    private function getAdminMemberListData($startDate, $endDate)
    {
        $sql = "EXECUTE sp_fs_metrics_get_member_list @start_date=:start_date_value, @end_date=:end_date_value";

        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);

        // Make sure the date parameters are in a SQL-friendly format.
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d H:i', strtotime($this->adjustEndDateToMidnight($endDate)));

        $command->bindValue(":start_date_value", $startDate);
        $command->bindValue(":end_date_value", $endDate);

        // Execute the stored procedure
        $results = $command->queryAll();

        return new CArrayDataProvider($results, array(
            'pagination' => false,
        ));
    }

    private function getAdminConditionResponseSummary($startDate, $endDate)
    {
        $endDate = $this->adjustEndDateToMidnight($endDate);

        // Look up the conditions for the completed reports within the given date range.
        $criteria = new CDbCriteria();
        $criteria->addBetweenCondition('status_date', $startDate, $endDate);
        $criteria->compare('status', 'Completed');
        $criteria->compare('member.is_tester', 0);
        $conditions = FSCondition::model()->with('report', 'report.member')->together()->findAll($criteria);

        $data = new ArrayObject(array());

        for ($i = 1; $i <= 15; $i++)
        {
            // Skip the infamous condition 4
            if ($i == 4)
                continue;

            $yesSum = 0;
            $noSum = 0;
            $notSureSum = 0;

            foreach ($conditions as $condition)
            {
                if ($condition->condition_num == $i)
                {
                    if ($condition->response == 0)
                        $yesSum++;
                    else if ($condition->response == 1)
                        $noSum++;
                    else if ($condition->response == 2)
                        $notSureSum++;
                }
            }

            $data->append(array(
                'id' => $i,
                'condition_num' => $i,
                'condition_text' => FSCondition::model()->getType($i),
                'yes_sum' => $yesSum,
                'no_sum' => $noSum,
                'not_sure_sum' => $notSureSum,
            ));
        }

        $sqlDataProvider = new CArrayDataProvider($data, array(
             'pagination' => false,
        ));

        return $sqlDataProvider;
    }

    private function adjustEndDateToMidnight($endDate)
    {
        // Append the very last second from midnight to the end date so that all records
        // for that day are included in the queries below.
        return $endDate . ' 23:59:59';
    }
}
?>
