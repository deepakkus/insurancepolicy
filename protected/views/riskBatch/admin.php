<?php

/* @var $this RiskBatchController */
/* @var $model RiskBatchFile */

$this->breadcrumbs = array(
	'Risk Batch',
);

Yii::app()->format->datetimeFormat = 'Y-m-d H:i';
Yii::app()->format->numberFormat = array('decimals'=>2, 'decimalSeparator'=>'.', 'thousandSeparator'=>'');

?>

<h1>Risk Batch Runs</h1>

<div class="marginTop20">
    <a class="btn btn-success" href="<?php echo $this->createUrl('/riskBatch/createFile'); ?>">Add File</a> 
    <a class="btn btn-success" href="<?php echo $this->createUrl('/riskBatch/runPif'); ?>">Run PIF</a>
</div>

<div class ="table-responsive">

    <?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'risk-batch-file-grid',
	'dataProvider'=>$model->search(),
	'columns'=>array(
        array(
            'class' => 'CLinkColumn',
            'header' => 'Run Batch',
            'label' => 'Run Batch',
            'urlExpression' => 'array("/riskBatch/runBatch", "id"=>$data->id)',
            'cssClassExpression'=> '($data->status != "uploaded") ? "no-link" : ""'
        ),
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}',
            'header' => 'Actions',
            'updateButtonUrl' => '$this->grid->controller->createUrl("/riskBatch/updateFile", array("id"=>$data->id))'
        ),
        array(
            'name' => 'file_name',
            'value' => '$data->file_name'
        ),
        'clientName',
        'version',
        'date_created:datetime',
        'date_run:datetime',
        'status',
        array(
            'header' => 'Progress',
            'value'=> function($data) {
                return $data->percentageComplete . '&#37;';
            },
            'type' => 'raw'
        ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'Download',
            'label' => 'Download Batch',
            'urlExpression' => 'array("/riskBatch/downloadBatch", "id"=>$data->id, "client_id"=>$data->client_id)',
            'cssClassExpression'=> '$data->status === "complete" ? "" : "no-link"'
         ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'View',
            'label' => 'View Batch Stats',
            'urlExpression' => 'array("/riskBatch/batchStats", "batchFileID"=>$data->id, "clientID"=>$data->client_id)'
         )
	)
)); ?>

</div>