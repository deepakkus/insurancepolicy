<?php
/* @var $this ResTriageZoneController */
/* @var $model ResTriageZone */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Work Zones'
);

Yii::app()->format->dateFormat = 'Y-m-d H:i';

Yii::app()->getClientScript()->registerCoreScript('jquery.ui');
Yii::app()->clientScript->registerCssFile(
    Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css');

?>

<h1>Work Zones</h1>

<?php $this->beginWidget('bootstrap.widgets.TbCollapse'); ?>
<div class="accordion-group marginTop10">
    <div class="accordion-heading">
        <a class="accordion-toggle" data-toggle="collapse"
            data-parent="#accordion2" href="#collapseOne">
            <b>Download Engine Attachments</b>
        </a>
    </div>

    <div id="collapseOne" class="accordion-body collapse">
        <div class="accordion-inner">
            <div class="container">
                <form class="form-inline" method="post" action="<?php echo $this->createAbsoluteUrl('resTriageZone/downloadEngineAttachments'); ?>">

                    <div class="row-fluid">
                        <div class="span3">
                            <h4>Select a fire</h4>
                            <?php echo CHtml::dropDownList('fire', null,  Helper::getDispatchedFires()); ?>
                        </div>

                        <div class="span3">
                            <h4>Select a client(s)</h4>
                            <?= CHtml::checkBoxList('client[]',null,Helper::getFireClients(), array('separator'=>'<br>')); ?>
                        </div>
                        <div class="span3">
                            <h4>Download Attachements</h4>
                            <input type="submit" value="Submit" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>

<a class="btn btn-success marginTop10" href="<?php echo $this->createUrl('/resTriageZone/create'); ?>">Create New Zone</a>

<div class = "table-responsive">

	<?php $this->widget('zii.widgets.grid.CGridView', array(

'id' => 'res-triage-zone-grid',
'dataProvider' => $model->search(),
'filter' => $model,
'columns' => array(
    array(
				'class' => 'bootstrap.widgets.TbButtonColumn',
				'template' => '{update}{delete}',
				'header' => 'Actions'
			),
			'clientName',
			'fireName',
			array(
				'header' => 'Notice',
				'value' => '$data->notice->recommended_action . " - " . date("Y-m-d H:i", strtotime($data->notice->date_created))'
			),
			array(
				'header' => 'Number Zones',
				'value' => '$data->resTriageZoneAreas ? count($data->resTriageZoneAreas) : ""',
			),
			array(
				'name' => 'date_created',
				'type' => 'date',
				'filter' => false
			),
			array(
				'name' => 'date_updated',
				'type' => 'date',
				'filter' => false
			),
            array(
                'class' => 'CLinkColumn',
                'header' => 'Notice',
                'labelExpression' => 'isset($data->notice->fire) ? $data->notice->fire->Name : "View Notice"',
                'urlExpression' => 'array("/resNotice/update", "id" => $data->notice_id, "client_id" => $data->notice->client_id)'
            ),
            array(
                'class' => 'CLinkColumn',
                'header' => 'Copy',
                'label' => 'Copy',
                'urlExpression' => 'array("/resTriageZone/copy", "id" => $data->id)'
            )
		)
	)); ?>

</div>