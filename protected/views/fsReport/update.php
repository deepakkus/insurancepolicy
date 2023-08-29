<?php 

echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/fsReport/update.js');
Yii::app()->bootstrap->init();
Yii::app()->clientScript->registerCssFile('/css/fsReport/update.css');

$this->breadcrumbs=array(
        'Reports' => array('admin'),
        'Update',
    );

$form=$this->beginWidget('CActiveForm', array(
    'id'=>'rs-report-form',
    'enableAjaxValidation'=>false,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
));

$propertyAddress = $fsReport->property->address_line_1;
if (!empty($fsReport->property->address_line_2)) {
	$propertyAddress .= ', ' . $fsReport->property->address_line_2;
}
$propertyAddress .= ', ' . $fsReport->property->city . ', ' . $fsReport->property->state . ' ' . $fsReport->property->zip;

$memberName = $fsReport->property->member->first_name . ' ' . $fsReport->property->member->last_name;

$property = (isset($fsReport->property)) ? $fsReport->property : null;

?>

<h1>Update FireShield Report <?php echo $fsReport->id; ?></h1>

<div class="paddingBottom10">
    <?php echo CHtml::link('Return to Reports List', array('fsReport/admin')); ?>
</div>

<div class="form">
    <table id="fs-report-info-table" class="formContainer100">
        <tr>
            <td><strong>Member:</strong></td>
            <td> <?php echo CHtml::link($memberName, array('member/view', 'mid' => $fsReport->property->member_mid)).' (#'.$fsReport->property->member->member_num; ?></td>    
            <td><strong>Report Status:</strong></td>
            <td> <?php echo $form->dropDownList($fsReport, 'status', $fsReport->getStatuses()); ?>
                <?php echo $form->error($fsReport,'status'); ?>
            </td>
        </tr>
        <tr>
            <td><strong>Property:</strong></td>
            <td><?php echo CHtml::link($propertyAddress, array('property/view', 'pid'=>$fsReport->property_pid)); ?></td>
            <td><strong>Report Status Date:</strong></td>
            <td><?php echo $form->textField($fsReport,'status_date',array('readonly'=>true)); ?>
                <?php if (!$fsReport->isNewRecord): ?>
                <a href="javascript:void(0);" id="lnkViewStatusHistory">View Status History</a>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td></td><td></td>
            <td><strong>Report Notes:</strong></td>
            <td rowspan="5"> <?php echo $form->textArea($fsReport, 'notes', array('rows'=>'5', 'cols'=>'75')); ?></td>
        </tr>

        <?php 
echo '<tr><td><strong>User Report Start Date:</strong></td><td>'.Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->start_date)).'</td></tr>';
echo '<tr><td><strong>User Report End Date:</strong></td><td>'.Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->end_date)).'</td></tr>';
echo '<tr><td><strong>Report Submit Date:</strong></td><td>'.Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->submit_date)).'</td></tr>';

$reportDueDate = '';
if (!empty($fsReport->due_date)) {
    $reportDueDate = Yii::app()->dateFormatter->format("M/d/y h:mm a", strtotime($fsReport->due_date));
}

echo '<tr><td><strong>App Version:</strong></td><td>'.$fsReport->version.'</td><td></td></tr>';
echo '<tr><td><strong>Lat/Long:</strong></td><td>'.$fsReport->latitude.', '.$fsReport->longitude.'</td>';
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
echo '<td><strong>Home Char. Risk:</strong></td><td>'.$fsReport->condition_risk.'</td>';
echo '<td><strong>Scheduled Call DateTime (TZ: '.$fsReport->scheduled_call_tz.'):</strong></td><td>';
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
echo '</td>';
echo '</tr>';
echo '<tr>';
echo '    <td><strong>LOS: (orig: '.$fsReport->orig_risk_level.')</strong></td><td>'.$fsReport->risk_level.'</td>';
echo '    <td><strong>Scheduled Call Notes:</strong></td>';
echo '    <td rowspan="5">'.$form->textArea($fsReport, 'scheduled_call_notes', array('rows'=>'5', 'cols'=>'75')).'</td>';
echo '</tr>';
echo '<tr></tr><tr></tr><tr></tr><tr></tr>';
echo '<tr>';

echo '<td><strong>GeoRisk:</strong></td><td>'.(!empty($fsReport->property->geo_risk) ? $fsReport->property->geo_risk : 'n/a').'</td>';

echo '    <td><strong>GeoRisk Override:</strong></td><td>';
echo          $form->textField($fsReport,'geo_risk',array('size'=>20,'maxlength'=>20,'value'=>($fsReport->geo_risk == 0 ? '': $fsReport->geo_risk)));
echo          $form->error($fsReport,'geo_risk');
echo '        (leave blank to use GeoRisk)';
echo '    </td>';
echo '</tr>';

echo '<tr>';
echo '	<td><strong>Assigned User:</strong></td>';
echo '	<td>'.User::model()->userDropDownList($fsReport, 'assigned_user_id', $fsReport->assigned_user_id).'</td>';


?>

</table>

<div class="buttons">
    <?php echo CHtml::submitButton('Save', array('class'=>'submit')); ?> | 
    <?php echo CHtml::link('View User PDF', array('fsReport/getPDFReport', 'guid'=>$fsReport->report_guid), array('target'=>'_blank')); ?> |
    <?php echo CHtml::link('View KML', array('fsReport/getKML', 'guid'=>$fsReport->report_guid), array()); ?>
</div>

<div class="form" style ="padding:25px 0 25px 0;">
    <?php
    $this->renderPartial('//property/_risk_score', array(
        'property' => $property
    ));
    ?>
</div>


<?php

echo '<div class ="cell-left">';

    echo '<h2>Conditions</h2>';
  
    echo '<table id="fsReportConditionTable">';
    echo '<tr><th>Condition</th>';
    echo '<th>Response</th>';
    echo '<th>HTML Template</th>';
    echo '<th>Submitted Photos</th>';
    echo '<th></th>';
    echo '</tr>';
    
    // Render the condition contents.
    foreach($fsReport->conditions as $condition)
	{
        echo $this->renderPartial('_condition', array('condition' => $condition, 'fsReport' => $fsReport, 'form' => $form));
    }
    
    echo '</table>';    
	echo '<br /><div class="buttons">';
	echo CHtml::submitButton('Save', array('class'=>'submit'));
        echo " | ".CHtml::link('View User PDF', array('fsReport/getPDFReport', 'guid'=>$fsReport->report_guid), array('target'=>'_blank'));
    echo '</div><br />';

    $this->endWidget();
	
    echo '</div>';//cell-left
    echo '<div class ="cell-right">';
    echo '<h2>Summary</h2>';
    echo $this->widget(
        'bootstrap.widgets.TbCKEditor',
        array(
            'model' => $fsReport,
            'attribute'=> 'summary',
            'editorOptions' => array(
                'plugins' => 'scayt,selectall,find,colorbutton,contextmenu,horizontalrule,justify,colordialog,basicstyles,resize,popup,font,htmlwriter,format,toolbar,enterkey,entities,floatingspace,wysiwygarea,link,list,liststyle,pastetext,removeformat,richcombo,dialog,dialogui,button,indent,fakeobjects,pastefromword'
            ),
            'htmlOptions'=> array(
                'height'=> '600px',
                'width'=> '350px',
            )
        ), true
    );
	echo $form->error($fsReport,'summary');

	echo '<br /><div class="row buttons">';

    if ($fsReport->risk_level > 1) 
    {
       $summaryHtmlTemplateUrl = Yii::app()->request->baseUrl.'/index.php?r=fsReport/showConditionHTML&condition_num=summary&report_guid='.$fsReport->report_guid;
        echo '<span class="paddingLeft10"> <a class="coloredLink paddingLeft5" href="javascript:showHtmlTemplatePreview(\''.$summaryHtmlTemplateUrl.'\');">View Summary HTML Template</a></span>';
    }
	echo '</div><br />';
	echo '</div>';	//cell-right
	echo '</div>';	//row
        echo "<br>";

echo '</div>';
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