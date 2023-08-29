<?php

/* @var $this ResFireNameController */
/* @var $model ResFireName */

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Manage Fires'
);

Yii::app()->format->datetimeFormat = 'Y-m-d H:i';
Yii::app()->format->dateFormat = 'Y-m-d';

?>

<h1>Add/Edit Fires</h1>

<div class="paddingTop20">
    <a class="btn btn-danger" href="<?php echo $this->createUrl('/resPerimeters/admin'); ?>">Threats</a>
</div>

<div class ="table-responsive">

    <?php

    $fireStates = ResFireName::model()->findAll(array(
        'select' => 'State',
        'distinct' => true,
        'order' => 'State ASC'
    ));

    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'id' => 'res-fire-name-grid',
        'type' => 'striped bordered',
        'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
        'dataProvider' => $model->search(),
        'filter' => $model,
        'columns' => array(
            array(
                'class' => 'CButtonColumn',
                'htmlOptions' => array('style' => 'min-width:50px'),
                'template' => '{update}{create}{perimeter}',
                'header' => 'Actions',
                'buttons' => array(
                    'update' => array(
                        'url' => '$this->grid->controller->createUrl("/resFireName/update", array("id" => $data->Fire_ID))',
                        'options' => array('style' => 'padding:5px;')
                    ),
                    'create' => array(
                        'url' => '$this->grid->controller->createUrl("/resFireObs/create", array("fireid" => $data->Fire_ID))',
                        'label' => 'Create Details',
                        'imageUrl' => '/images/create.png'
                    ),
                    'perimeter' => array(
                        'url' => '$this->grid->controller->createUrl("/resPerimeters/createPerimeter", array("fire_id" => $data->Fire_ID))',
                        'label' => 'New Perimeter',
                        'imageUrl' => '/images/res_perimeter.png',
                        'options' => array('style' => 'padding:5px;')
                    ),
                    //'threat' => array(
                    //    'url' => '$this->grid->controller->createUrl("/resPerimeters/createThreat", array("fireID" => $data->Fire_ID))',
                    //    'label' => 'New Threat',
                    //    'imageUrl' => '/images/res_threat.png',
                    //    'options' => array('style' => 'padding:5px;')
                    //)
                ),
            ),
            array(
            'header' => 'Fire ID',
            'name' => 'Fire_ID'
            ),
            'Name',
            'City',
            array(
                'name' => 'State',
                'filter' => CHtml::activeDropDownList($model, 'State', CHtml::listData($fireStates, 'State', 'State'), array('prompt' => ''))
            ),
            array(
                'name' => 'Start_Date',
                'type' => 'date',
                'filter' => false
            ),
            array(
                'name'=>'Date_Updated',
                'type' => 'datetime',
                'filter' => false
            ),
            array(
                'name' => 'Contained',
                'value' => '($data->Contained == 1) ? "&#x2713;" : "&#x2716;"',
                'type' => 'raw',
                'filter' => CHtml::activeDropDownList($model, 'Contained', array('0' => '&#x2716;', '1' => '&#x2713;'), array('encode' => false,'prompt' => ''))
            ),
            array(
                'header' => 'Perimeter',
                'value' => '$data->perimeterExists == 1 ? "&#x2713;" : ""',
                'type' => 'raw'
            ),
            array(
                'header' => 'Threat',
                'value' => '$data->threatExists == 1 ? "&#x2713;" : ""',
                'type' => 'raw'
            ),
            array(
                'header' => 'Details',
                'value' => '$data->fireObsExists == 1 ? "&#x2713;" : ""',
                'type' => 'raw'
            )
        )
    ));

    ?>

</div>