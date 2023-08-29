<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php echo CHtml::cssFile(Yii::app()->baseUrl.'/css/fsAnalytics.css') ?>

<h1>FireShield Metrics</h1>

<div class="clearfix">
<?php 
    // Render the parameters form (for the start/end dates).
    echo $this->renderPartial('_form', array('startDate' => $startDate, 'endDate' => $endDate)); 
?>
</div>
<div id="analyticsDatesBox" class="paddingTop10 hidden">
    From <?php echo $startDate ?> to <?php echo $endDate ?>
</div>

<div id="analyticsDataContainer">
    <h3 class="paddingTop10">Summary Metrics</h3>
    <div id="summaryMetricsTable" class="grid-view">
        <table class="items">
            <thead>
                <tr>
                    <th class="whiteHeader"></th>
                    <th class="memberHeader" colspan="3">Member</th>
                    <th class="propertyHeader" colspan="3">Property</th>
                    <th class="whiteHeader" colspan="7"></th>
                </tr>
                <tr>
                    <th>Date Range</th>
                    <th>Released</th>
                    <th>Completed</th>
                    <th>Take Up</th>
                    <th>Released</th>
                    <th>Completed</th>
                    <th>Take Up</th>
                    <th>Add'l Completed</th>
                    <th>Total</th>
                    <th>Completed Within SLO</th>
                    <th>Avg Cycle Time (Days)</th>
                    <th>Avg Take Up Time (Days)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="odd">
                    <td>Selected Dates</td>
                    <td class="memberColumn"><?php echo $selectedSummary->memberReleased ?></td>
                    <td class="memberColumn"><?php echo $selectedSummary->memberCompleted ?></td>
                    <td class="memberColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($selectedSummary->memberTakeUp) ?></td>
                    <td class="propertyColumn"><?php echo $selectedSummary->propertyReleased ?></td>
                    <td class="propertyColumn"><?php echo $selectedSummary->propertyCompleted ?></td>
                    <td class="propertyColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($selectedSummary->propertyTakeUp) ?></td>
                    <td class="additionalCompletedColumn"><?php echo $selectedSummary->additionalCompleted ?></td>
                    <td class="totalColumn"><?php echo $selectedSummary->totalCompleted ?></td>
                    <td><?php echo Yii::app()->numberFormatter->formatPercentage($selectedSummary->completedWithinSLO) ?></td>
                    <td><?php echo sprintf("%.2f", $selectedSummary->avgCycleTime) ?></td>
                    <td><?php echo sprintf("%.1f", $selectedSummary->avgTakeUpTime) ?></td>
                </tr>
                <tr class="even">
                    <td>Month-to-Date</td>
                    <td class="memberColumn"><?php echo $summaryMTD->memberReleased ?></td>
                    <td class="memberColumn"><?php echo $summaryMTD->memberCompleted ?></td>
                    <td class="memberColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($summaryMTD->memberTakeUp) ?></td>
                    <td class="propertyColumn"><?php echo $summaryMTD->propertyReleased ?></td>
                    <td class="propertyColumn"><?php echo $summaryMTD->propertyCompleted ?></td>
                    <td class="propertyColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($summaryMTD->propertyTakeUp) ?></td>
                    <td class="additionalCompletedColumn"><?php echo $summaryMTD->additionalCompleted ?></td>
                    <td class="totalColumn"><?php echo $summaryMTD->totalCompleted ?></td>
                    <td><?php echo Yii::app()->numberFormatter->formatPercentage($summaryMTD->completedWithinSLO) ?></td>
                    <td><?php echo sprintf("%.2f", $summaryMTD->avgCycleTime) ?></td>
                    <td><?php echo sprintf("%.1f", $summaryMTD->avgTakeUpTime) ?></td>
                </tr>
                <tr class="odd">
                    <td>Year-to-Date</td>
                    <td class="memberColumn"><?php echo $summaryYTD->memberReleased ?></td>
                    <td class="memberColumn"><?php echo $summaryYTD->memberCompleted ?></td>
                    <td class="memberColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($summaryYTD->memberTakeUp) ?></td>
                    <td class="propertyColumn"><?php echo $summaryYTD->propertyReleased ?></td>
                    <td class="propertyColumn"><?php echo $summaryYTD->propertyCompleted ?></td>
                    <td class="propertyColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($summaryYTD->propertyTakeUp) ?></td>
                    <td class="additionalCompletedColumn"><?php echo $summaryYTD->additionalCompleted ?></td>
                    <td class="totalColumn"><?php echo $summaryYTD->totalCompleted ?></td>
                    <td><?php echo Yii::app()->numberFormatter->formatPercentage($summaryYTD->completedWithinSLO) ?></td>
                    <td><?php echo sprintf("%.2f", $summaryYTD->avgCycleTime) ?></td>
                    <td><?php echo sprintf("%.1f", $summaryYTD->avgTakeUpTime) ?></td>
                </tr>
                <tr class="even">
                    <td>Period-to-Date</td>
                    <td class="memberColumn"><?php echo $summaryPTD->memberReleased ?></td>
                    <td class="memberColumn"><?php echo $summaryPTD->memberCompleted ?></td>
                    <td class="memberColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($summaryPTD->memberTakeUp) ?></td>
                    <td class="propertyColumn"><?php echo $summaryPTD->propertyReleased ?></td>
                    <td class="propertyColumn"><?php echo $summaryPTD->propertyCompleted ?></td>
                    <td class="propertyColumn"><?php echo Yii::app()->numberFormatter->formatPercentage($summaryPTD->propertyTakeUp) ?></td>
                    <td class="additionalCompletedColumn"><?php echo $summaryPTD->additionalCompleted ?></td>
                    <td class="totalColumn"><?php echo $summaryPTD->totalCompleted ?></td>
                    <td><?php echo Yii::app()->numberFormatter->formatPercentage($summaryPTD->completedWithinSLO) ?></td>
                    <td><?php echo sprintf("%.2f", $summaryPTD->avgCycleTime) ?></td>
                    <td><?php echo sprintf("%.1f", $summaryPTD->avgTakeUpTime) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <h3 class="paddingTop20">Service Level Summary</h3>
    <div id="serviceLevelSummaryTable" class="grid-view">
        <table class="items">
            <thead>
                <tr>
                    <th colspan="3" class="header">Completed</th>
                    <th colspan="7" class="header">Changed LOS</th>
                    <th colspan="3" class="header">Non-Take Up By GeoRisk</th>
                </tr>
                <tr>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>L1-L2</th>
                    <th>L2-L3</th>
                    <th>L3-L2</th>
                    <th>L2-L1</th>
                    <th>L1-L3</th>
                    <th>L3-L1</th>
                    <th>No Change</th>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                </tr>
            </thead>
            <tbody>
                <tr class="odd">
                    <td><?php echo $selectedSummary->serviceLevelSummary[0] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->serviceLevelSummaryPercentages[0]) . ')' ?></td>
                    <td><?php echo $selectedSummary->serviceLevelSummary[1] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->serviceLevelSummaryPercentages[1]) . ')' ?></td>
                    <td><?php echo $selectedSummary->serviceLevelSummary[2] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->serviceLevelSummaryPercentages[2]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[0] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[0]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[1] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[1]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[2] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[2]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[3] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[3]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[4] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[4]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[5] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[5]) . ')' ?></td>
                    <td><?php echo $selectedSummary->LOSMove[6] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->LOSMovePercentages[6]) . ')' ?></td>
                    <td><?php echo $selectedSummary->nonTakeUpTotals[0] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->nonTakeUpPercentages[0]) . ')' ?></td>
                    <td><?php echo $selectedSummary->nonTakeUpTotals[1] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->nonTakeUpPercentages[1]) . ')' ?></td>
                    <td><?php echo $selectedSummary->nonTakeUpTotals[2] . ' (' . Yii::app()->numberFormatter->formatPercentage($selectedSummary->nonTakeUpPercentages[2]) . ')' ?></td>
                </tr>
            </tbody>
        </table>
    </div>                        

    <div class="clearfix">
        <div class="floatLeft paddingRight20">
            <h3 class="paddingTop20">Level of Service Breakdown</h3>

            <table class="levelOfServiceBreakdownTable">
                <tr>
                    <td rowspan="3" class="outerLabelColor">
                        Area<br/>Risk
                    </td>
                    <td class="labelColor">
                        High/Very High
                    </td>
                    <td class="midColor">
                        <div>Level 2</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[0][0] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[0][0]) . ')' ?></div>
                    </td>
                    <td class="midColor">
                        <div>Level 2</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[0][1] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[0][1]) . ')' ?></div>
                    </td>
                    <td class="highColor">
                        <div>Level 3</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[0][2] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[0][2]) . ')' ?></div>
                    </td>
                </tr>
                <tr>
                    <td class="labelColor">
                        Moderate
                    </td>
                    <td class="lowColor">
                        <div>Level 1</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[1][0] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[1][0]) . ')' ?></div>
                    </td>
                    <td class="midColor">
                        <div>Level 2</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[1][1] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[1][1]) . ')' ?></div>
                    </td>
                    <td class="midColor">
                        <div>Level 2</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[1][2] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[1][2]) . ')' ?></div>
                    </td>
                </tr>
                <tr>
                    <td class="labelColor">
                        Low
                    </td>
                    <td class="lowColor">
                        <div>Level 1</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[2][0] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[2][0]) . ')' ?></div>
                    </td>
                    <td class="lowColor">
                        <div>Level 1</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[2][1] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[2][1]) . ')' ?></div>
                    </td>
                    <td class="lowColor">
                        <div>Level 1</div>
                        <div><?php echo $selectedSummary->LOSBreakdown[2][2] . ' (' . Yii::app()->numberFormatter->format('###.##%', $selectedSummary->LOSBreakdownPercentages[2][2]) . ')' ?></div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="grayedOut"></td>
                    <td class="labelColor">
                        Low
                    </td>
                    <td class="labelColor">
                        Moderate
                    </td>
                    <td class="labelColor">
                        High/Very High
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="grayedOut"></td>
                    <td colspan="3" class="outerLabelColor">
                        Home Characteristic Risk Level
                    </td>
                </tr>
            </table>
        </div>

        <div class="floatLeft">

            <h3 class="paddingTop20">Condition Response Totals</h3>

            <?php
            $this->widget('zii.widgets.grid.CGridView', array(
               'id' => 'gridConditionResponseSummary',
                'dataProvider' => $conditionResponseSummary,
                'summaryText' => '',
                'columns' => array(
                    array (
                        'name' => 'condition_text',
                        'header' => 'Condition',
                    ),
                    array (
                        'name' => 'yes_sum',
                        'header' => 'Yes',
                    ),
                    array (
                        'name' => 'no_sum',
                        'header' => 'No',
                    ),
                    array (
                        'name' => 'not_sure_sum',
                        'header' => 'Not Sure',
                    ),
                ),
            ));
            ?>
        </div>
    </div>
    
    <div id="memberSummaryArtificialPageBreak" class="hidden" style="page-break-before: always"></div>
    
    <div>
        <h3 class="paddingTop20">Member Summary</h3>

        <?php
            $this->widget('zii.widgets.grid.CGridView', array(
               'id' => 'gridMemberSummary',
                'dataProvider' => $dataProviderMemberSummary,
                'summaryText' => '',
                'columns' => array(
                    array (
                        'name' => 'member_num',
                        'header' => 'Member Number',
                    ),
                    array (
                        'name' => 'first_name',
                        'header' => 'First Name',
                    ),
                    array (
                        'name' => 'last_name',
                        'header' => 'Last Name',
                    ),
                    array (
                        'name' => 'date_changed',
                        'header' => 'Invite Date',
                    ),
                    array (
                        'name' => 'address_line_1',
                        'header' => 'Address Line 1',
                    ),
                    array (
                        'name' => 'address_line_2',
                        'header' => 'Address Line 2',
                    ),
                    array (
                        'name' => 'city',
                        'header' => 'City',
                    ),
                    array (
                        'name' => 'state',
                        'header' => 'State',
                    ),
                    array (
                        'name' => 'zip',
                        'header' => 'Zip Code',
                    ),
                    array (
                        'name' => 'orig_risk_level',
                        'header' => 'Starting Level of Service',
                    ),
                ),
            ));
        ?>
    </div>              
</div>