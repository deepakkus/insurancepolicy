<?php

/* @var $this ResNoticeController */
/* @var $models ResNotice */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Fire History'
);

?>

<h1 class ="center"><?php echo $models[0]->client->name; ?> Fire History For The <?php echo $models[0]->fire->Name; ?></h1>

<?php foreach($models as $model): ?>

<h2><?php echo $model->date_created . " | " . $model->resStatus->status_type; ?></h2>

<div class ="row-fluid">

    <div class ="span6">

    <?php $this->widget('zii.widgets.CDetailView', array(
        'data' => $model,
        'htmlOptions' => array(
            'class' => 'table table-striped table-hover table-condensed',
            'style' => 'width: 70%;'
        ),
        'itemTemplate' => '<tr><th style="width:1%; white-space: nowrap;">{label}</th><td>{value}</td></tr>',
        'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
        'attributes' => array(
            'date_updated',
            'date_published',
            'date_created',
            array(
                'label'=>'wds_status',
                'type'=>'raw',
                'value'=>function($data){ 
                    if($data->wds_status == 1){
                        return "<span style='color:red;'>" . $data->resStatus->status_type . "</span>";
                    }
                    else{
                        return $data->resStatus->status_type;
                    }
              }),
            'fireObs.Size',
            'fireObs.Containment',
            'fireObs.Supression',
        )
    )); ?>

    </div>

        <div class ="span6">

    <?php $this->widget('zii.widgets.CDetailView', array(
        'data' => $model,
        'htmlOptions' => array(
            'class' => 'table table-striped table-hover table-condensed',
            'style' => 'width: 70%;'
        ),
        'itemTemplate' => '<tr><th style="width:1%; white-space: nowrap;">{label}</th><td>{value}</td></tr>',
        'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
        'attributes' => array(
            'comments',
            'notes'
        )
    )); ?>

    </div>

</div>

<?php endforeach; ?>

