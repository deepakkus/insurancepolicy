<?php
echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/fsReport/update.js');

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'rs-report-form',
    'enableAjaxValidation'=>false,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
));

echo '<h1>Update Agent Report ' . $fsReport->id . '</h1>';

echo '<div class="paddingBottom10">';
echo CHtml::link('Return to Agent Reports List', array('fsReport/allReports'));
echo '</div>';
echo '<div class="form">';
echo '<table id="fs-report-info-table" class="formContainer100">';
echo '<tr>';

$agentProperty = $fsReport->agent_property;
$propertyAddress = $agentProperty->address_line_1;
if (!empty($agentProperty->address_line_2)) {
	$propertyAddress .= ', ' . $agentProperty->address_line_2;
}
$propertyAddress .= ', ' . $agentProperty->city . ', ' . $agentProperty->state . ' ' . $agentProperty->zip;

$agentName = $agentProperty->agent->first_name . ' ' . $agentProperty->agent->last_name;
echo '<td><strong>Agent:</strong></td>';
echo '<td>'.CHtml::link($agentName, array('agent/update', 'id'=>$agentProperty->agent_id), array('target'=>'_blank')).'</td>';

echo '    <td><strong>Report Status:</strong></td>';
echo '    <td>';
echo $form->dropDownList($fsReport, 'status', $fsReport->getStatuses());
echo $form->error($fsReport,'status');

echo '    </td>';
echo '</tr>';
echo '<tr>';
echo '<td><strong>Agent Property:</strong></td>';
echo '<td>';
echo 'ID #: '.CHtml::link($agentProperty->id.' (Work Order #: '.$agentProperty->work_order_num.')', array('agentProperty/update', 'id'=>$agentProperty->id), array('target'=>'_blank')).'<br>';
echo 'Address: '.$agentProperty->address_line_1.' '.$agentProperty->city.', '.$agentProperty->state.' '.$agentProperty->zip.'<br>';
echo '</td>'; //end prop details
echo '    <td>';
echo '        <strong>Report Status Date:</strong>';
echo '    </td>';
echo '    <td>';
echo $form->textField($fsReport,'status_date',array('readonly'=>true));
if (!$fsReport->isNewRecord) {
echo '        <a href="javascript:void(0);" id="lnkViewStatusHistory">View Status History</a>';
}
echo '    </td>';
echo '</tr>';
echo '<tr>';
echo '    <td><strong>Property:</strong></td>';
echo '    <td>';
echo '      PreRiskID: '.$form->textField($fsReport, 'pre_risk_id',array('style'=>'width:100px;', 'size'=>10, 'maxlength'=>20, 'value'=>(empty($fsReport->pre_risk_id) ? '': $fsReport->pre_risk_id))).'<br>';
if(!empty($pr_info))
    echo $pr_info;
echo        'PID: '.$form->textField($fsReport,'property_pid',array('style'=>'width:100px', 'size'=>10,'maxlength'=>20,'value'=>(empty($fsReport->property_pid) ? '': $fsReport->property_pid)));
if(!empty($mem_prop_info))
    echo $mem_prop_info;
    
echo '    </td>';
echo '    <td><strong>Report Notes:</strong></td>';
echo '    <td>'.$form->textArea($fsReport, 'notes', array('rows'=>'5', 'cols'=>'75')).'</td>';
echo '</tr>';
echo '<tr>';
echo '  <td><strong>User Report Start Date:</strong></td><td>'.Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->start_date)).'</td>';
echo '  <td><strong>Scheduled Call DateTime (TZ: '.$fsReport->scheduled_call_tz.'):</strong></td><td>';
$this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
    'model'=>$fsReport,
    'attribute' => 'scheduled_call',
    'options'=>array(
        'showAnim'=>'fold',
        'showButtonPanel'=>true,
        'autoSize'=>true,
        'dateFormat'=>'mm/dd/yy',
        'timeFormat'=>'h:mm TT',
        'ampm'=> true,
        'separator'=> ' ',
    ),
));
echo '  </td>';
echo '</tr>';
echo '<tr><td><strong>User Report End Date:</strong></td><td>'.Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->end_date)).'</td></tr>';
echo '<tr><td><strong>Report Submit Date:</strong></td><td>'.Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->submit_date)).'</td></tr>';

$reportDueDate = '';
if (!empty($fsReport->due_date)) {
    $reportDueDate = Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->due_date));
}

echo '<tr><td><strong>App Version:</strong></td><td>'.$fsReport->version.'</td>';
echo '<td>';
echo '<strong>Report Type:</strong></td><td>';
echo $form->dropDownList($fsReport, 'type', array('uw'=>'uw', 'edu'=>'edu', 'edu-b'=>'edu-b'));
echo $form->error($fsReport,'type');
echo ' &nbsp;&nbsp<strong>No Scoring:</strong> &nbsp;&nbsp';
echo $form->checkBox($fsReport, 'no_scoring');
echo $form->error($fsReport,'no_scoring');
echo '</td>';
echo '</tr>';

echo '<tr><td><strong>Lat/Long:</strong></td><td>'.round($fsReport->agent_property->lat,4).', '.round($fsReport->agent_property->long,4).'</td>';
echo '<td><strong>Report Due Date:</strong></td><td>';
	$this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
		'model' => $fsReport,
		'attribute' => 'due_date',
		'options' => array(
			'showAnim' => 'fold',
			'showButtonPanel' => true,
			'autoSize' => true,
			'dateFormat' => 'mm/dd/yy',
			'timeFormat' => 'h:mm TT',
			'ampm' => true,
			'separator' => ' ',
		),
	));
echo '</td>';
echo '</tr><tr>';
echo '<td><strong>Site Risk:</strong></td><td>'.$fsReport->calcConditionRisk().'</td>';
echo '    <td><strong>Site Risk Override:</strong></td><td>';
echo          $form->textField($fsReport,'condition_risk',array('size'=>20,'maxlength'=>20,'value'=>($fsReport->condition_risk == $fsReport->calcConditionRisk() ? '': $fsReport->condition_risk)));
echo          $form->error($fsReport,'condition_risk');
echo '        (leave blank to use Condition Sum)';
echo '    </td>';
echo '</tr>';
echo '<tr>';
echo '<td><strong>Property Geo Risk:</strong></td><td>'.(!empty($fsReport->agent_property->geo_risk) ? $fsReport->agent_property->geo_risk : 'n/a').'</td>';    
echo '    <td><strong>GeoRisk Override:</strong></td><td>';
echo          $form->textField($fsReport,'geo_risk',array('size'=>20,'maxlength'=>20,'value'=>($fsReport->geo_risk == 0 ? '': $fsReport->geo_risk)));
echo          $form->error($fsReport,'geo_risk');
echo '        (leave blank to use Property GeoRisk)';
echo '    </td>';
echo '</tr>';

echo '<tr>';
echo '    <td><strong>'.CHtml::link('Edit client ('.$fsReport->client->name.')', array('client/update', 'id'=>$fsReport->client->id), array('target'=>'_blank')).'</strong></td><td></td>';
echo '    <td><strong>Client FRA Report Threshold:</strong></td><td>'.$fsReport->client->fra_report_threshold.'</td>';
echo '</tr>';

echo '<tr>';
echo '  <td><strong>Total Risk: (orig: '.$fsReport->orig_risk_level.')</strong><br>Site Score (S) = (Site Risk*100)/'.$fsReport->client->getMaxPts('site').'<br>Geo Score (G) = (Geo Risk*100)/'.$fsReport->client->getMaxPts('geo').'<br>Total Score = (0.4*G)+(0.6*S)</td><td>'.$fsReport->risk_level.'</td>';
echo '  <td><strong>PDF Report Score Settings:</strong></td>';
echo '  <td>';
echo $form->labelEx($fsReport,'show_site_risk');
echo $form->checkBox($fsReport,'show_site_risk');
echo $form->error($fsReport,'show_site_risk');
echo $form->labelEx($fsReport,'show_geo_risk');
echo $form->checkBox($fsReport,'show_geo_risk');
echo $form->error($fsReport,'show_geo_risk');
echo $form->labelEx($fsReport,'show_los_risk');
echo $form->checkBox($fsReport,'show_los_risk');
echo $form->error($fsReport,'show_los_risk');
echo '  </td>';
echo '</tr>';

echo '<tr>';
echo '	<td><strong>Assigned User:</strong></td>';
echo '	<td>'.User::model()->userDropDownList($fsReport, 'assigned_user_id', $fsReport->assigned_user_id).'</td>';

echo '<td><strong>Client LOS Settings:</strong></td>';
echo '<td>'.$fsReport->client->losHTMLTable($fsReport).'</td>';
echo '</tr>';
echo '</table>';
	
echo '<div class="row buttons">';
    echo CHtml::submitButton('Save', array('class'=>'submit'));
    echo " | ";
    echo CHtml::link('View PDF', array('fsReport/getPDFReport', 'guid'=>$fsReport->report_guid), array('target'=>'_blank')).' | ';
    echo CHtml::link('View KML', array('fsReport/getKML', 'guid'=>$fsReport->report_guid), array());
    echo '</span>';
    echo '</div><br />';

    
    echo '<div class ="">';
    echo '<h2>Risk Analysis Summary</h2>';
    echo $this->widget(
        'bootstrap.widgets.TbCKEditor',
        array(
            'model' => $fsReport,
            'attribute'=> 'summary',
            'editorOptions' => array(
                'plugins' => 'undo,basicstyles,toolbar,wysiwygarea,list,removeformat,pastefromword'
            ),
            'htmlOptions'=> array(
                'height'=> '600px',
                'width'=> '350px',
            )
        ), true
    );
    echo $form->error($fsReport,'summary');
    echo '</div>';//cell-left
    
    echo '<div>';

    echo '<h2>Condition Detail</h2>';
  
    echo '<table id="fsReportConditionTable">';
    echo '<tr><td width="10%" style="font-size:17px;text-align:center;border-bottom: 1px solid black;font-weight:bold;margin-bottom:10px;">Condition</td>';
    echo '<td width="10%" style="font-size:17px;text-align:center;border-bottom: 1px solid black;font-weight:bold;">Response</td>';
	echo '<td width="40%" style="font-size:17px;text-align:center;border-bottom: 1px solid black;font-weight:bold;">Recommendation</td>';
	echo '<td width="10%" style="font-size:17px;text-align:center;border-bottom: 1px solid black;font-weight:bold;">Yes Points</td>';
    echo '<td width="20%" style="font-size:17px;text-align:center;border-bottom: 1px solid black;font-weight:bold;">Submitted Photos</td>';
    echo '<td width="5%" style="font-size:17px;text-align:center;border-bottom: 1px solid black;font-weight:bold;"></td>';
    echo '</tr>';
    
    // Render the condition contents.
    foreach($fsReport->getOrderedConditions() as $condition)
	{
        echo $this->renderPartial('_agentCondition', array('condition' => $condition, 'fsReport' => $fsReport, 'form' => $form));
    }
    
    echo '</table>';    
	echo '<br /><div class="row buttons">';
	echo CHtml::submitButton('Save', array('class'=>'submit'));
        echo " | ".CHtml::link('View PDF', array('fsReport/getPDFReport', 'guid'=>$fsReport->report_guid), array('target'=>'_blank'));
    echo '</div><br />';

$this->endWidget();
	
	//echo '<div class="row">';

echo '<br /><div class="row buttons">';
//echo CHtml::submitButton('Save', array('class'=>'submit'));
echo '<br />';
echo '</div>';	//cell-right
echo '</div>';	//row
echo "<br>";

echo '</div></div>';
?>

<div id="hiddenHtmlTemplatePreview" style="display:none;">
    <iframe id="iframeHtmlTemplatePreview"></iframe>
</div>

<div id="hiddenReportStatusHistory" style="display:none;">
    <?php if (!$fsReport->isNewRecord) : ?>
    <div>
        <h3>Report Status History</h3>
        <div class="paddingRight10">
            <?php
            $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'gridReportStatusHistory',
                'dataProvider' => $reportStatusHistory,
                'summaryCssClass' => 'hidden',
                'emptyText' => 'No history found',
                'enableSorting' => false,
                'columns' => array(
                    array(
                        'name' => 'date_changed', 
                        'header' => 'Date Changed', 
                        'value' => 'Yii::app()->dateFormatter->format("MM/d/y h:mm a", strtotime($data->date_changed))',
                    ),
                    array('name' => 'status', 'header' => 'Status'),
                    array(
                        'name' => 'user_id', 
                        'header' => 'By',
                        'value' => 'isset($data->user) ? $data->user->name : "System"',    
                    ),
                )
            ));
            ?>
        </div>
        <a href="javascript:void(0)" id="lnkReportStatusHistoryClose" class="floatRight">Close</a>
    </div>
    <?php endif; ?>    
</div>