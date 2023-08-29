<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engines'=>array('index'),
	'Manage Engines'
);

$enginesViewOnly = in_array('Engine View',Yii::app()->user->types) && (!in_array('Engine',Yii::app()->user->types) && !in_array('Engine Manager',Yii::app()->user->types));

$script = '
// Remove the href from the link when click to prevent multiple emails being sent.
$(".email-link a").click(function(e) {
    if (!confirm("Do you sure you want to send an email?")) return false;
    window.location.href = $(this).attr("href");
    $(this).removeAttr("href").css("color", "black");
});
';

Yii::app()->clientScript->registerScript(1, $script, CClientScript::POS_READY);

?>

<h1>Manage Engines</h1>

<?php if (in_array('Engine Manager',Yii::app()->user->types)): ?>

    <div class="marginTop10">
        <a class="btn btn-success" href="<?php echo $this->createUrl('/engEngines/create'); ?>">Create New Engine</a>
    </div>

<?php endif; ?>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
	    'id'=>'eng-engines-grid',
	    'dataProvider'=>$model->search(),
	    'filter'=>$model,
	    'columns'=>array(
		    array(
			    'class'=>'bootstrap.widgets.TbButtonColumn',
                'template'=>'{update}{delete}',
                'header' => 'Actions',
                'buttons' => array(
                    'delete' => array(
                        'visible' => 'false'
                    )
                ),
                'visible' => !$enginesViewOnly
		    ),
		    'engine_name',
		    'make',
		    'model',
		    array(
                'name' => 'type',
                'filter' => CHtml::activeDropDownList($model,'type',$model->getEngineTypes(),array('prompt'=>''))
            ),
            array(
                'name' => 'comment',
                'htmlOptions' => array('class'=>'grid-column-overflow-max-200')
            ),
            array(
                'name' => 'availible',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'availible',array(0 => 'No', 1 => 'Yes'), array('prompt'=>''))
            ),
            array(
                'name' => 'active',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'active',array(0 => 'No', 1 => 'Yes'), array('prompt'=>''))
            ),
		    array(
                'name' => 'reason',
                'htmlOptions' => array('class'=>'grid-column-overflow-max-200')
            ),
            array(
                'name' => 'engine_source',
                'value' => '$data->getEngineSource();',
                'filter' => CHtml::activeDropDownList($model,'engine_source',$model->getEngineSources(),array('prompt'=>''))
            ),
            array(
                'name' => 'alliance_partner',
                'filter' => CHtml::activeDropDownList($model,'alliance_partner',CHtml::listData(Alliance::model()->findAll(array(
                    'select' => 'name',
                )),'name','name'),array('prompt'=>''))
            ),
            array(
                'name' => 'date_updated',
                'type' => 'html',
                'value' => '$this->grid->controller->getOlderThanOneWeek($data) ? "<b style=\'color:red;\'>$data->date_updated</b>" : $data->date_updated',
                'filter' => ''
            ),
            array(
                'class' => 'CLinkColumn',
                'header' => 'Send Email Reminder',
                'label' => 'Send Email',
                'htmlOptions' => array('class' => 'email-link'),
                'urlExpression' => 'array("/engEngines/sendAllianceReminderEmail","id"=>$data->id)',
                'cssClassExpression' => '$this->grid->controller->getOlderThanOneWeek($data) ? "" : "no-link"',
                'visible' => in_array('Engine Manager',Yii::app()->user->types)
            ),
            array(
                'name' => 'date_email',
                'value' => '$data->date_email ? date("Y-m-d H:i", strtotime($data->date_email)) : ""',
                'filter' => '',
                'visible' => in_array('Engine Manager',Yii::app()->user->types)
            )
	    )
    )); ?>

</div>

<p><b style="color: red;">* Date Updated in bold red</b> means that Alliance Engine hasn't been updated in one week.</p>

