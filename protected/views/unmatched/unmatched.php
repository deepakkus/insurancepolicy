<?php 

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Unmatched' => array('/unmatched/index'),
    'Find Unmatched'
);

Yii::app()->clientScript->registerScript(1, "

    $('#findProperty').submit(function() {
        var pid = $('#unmatched').val();
        $.fn.yiiGridView.update('gridUnmatched', {
            type: 'get',
            url: this.action,
            data: {
                pid: pid
            }
        });

        return false;
    });

");

?>

<div class="container-fluid">
    <h1>Find Unmatched</h1>
    <div class="row-fluid">
        <div class="span4">
            <?php
            $this->beginWidget('CActiveForm', array(
                'action' => $this->createUrl($this->route),
                'id' => 'findProperty'
            ));
            echo CHtml::numberField('unmatched', '', array('placeholder' => 'Enter PID'));
            echo CHtml::submitButton('Find Policy', array('class' => 'btn show marginTop10'));
            $this->endWidget();
            ?>
        </div>
        <div class="span8"></div>
    </div>

    <div class="row-fluid marginTop20">
        <div class="span10">
            <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
                'id' => 'gridUnmatched',
                'cssFile' => '../../css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'dataProvider' => $dataProvider,
                'columns' => array(
                    array(
                        'class' => 'bootstrap.widgets.TbButtonColumn',
                        'template' => '{update}',
                        'header' => 'Actions',
                        'updateButtonUrl' => '$this->grid->controller->createUrl("/unmatched/unmatchedUpdate", array("pid" => $data->pid))'
                    ),
                    'pid',
                    'address_line_1',
                    'city',
                    'state',
                    'zip',
                    'member.last_name',
                    'member.first_name',
                    'geocode_level',
                    'wds_geocode_level',
                    array(
                        'header' => 'Coordinates',
                        'value' => function($data) {
                            if (!empty($data->geog)) {
                                return $data->wds_lat . ', ' . $data->wds_long;
                            }
                            return '';
                        }
                    )
                ),
                'enableSorting' => false,
                'emptyText' => 'Search for PID to populate grid with a property',
                'htmlOptions' => array('style' => 'padding: 0;')
            )); ?>
        </div>
    </div>
</div>