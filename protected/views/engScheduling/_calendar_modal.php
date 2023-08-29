<?php

// Initialize modal script
echo CHtml::script('AdminEngineModal.init();');

$enginesViewOnly = in_array('Engine View',Yii::app()->user->types) && (!in_array('Engine',Yii::app()->user->types) && !in_array('Engine Manager',Yii::app()->user->types));

Yii::app()->format->dateFormat = 'm/d/Y H:i';

?>

<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Engine Information</h3>
</div>
<div class="modal-body">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="table-responsive">
                    <table class="table table-condensed table-hover">
                        <caption style="margin-bottom:10px; font-weight:bold;"><i>Engine Information</i></caption>
                        <tbody>
                            <tr>
                                <th style="width: 1%; white-space: nowrap; margin-right:60px;">Client Name: </th>
                                <td><?php echo join(' / ', $model->client_names); ?></td>
                            </tr>
                            <tr>
                                <th style="width: 1%; white-space: nowrap; margin-right:60px;">Engine Name: </th>
                                <td><?php echo $model->engine_name; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 1%; white-space: nowrap; margin-right:60px;">Comment: </th>
                                <td><?php echo $model->comment; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 1%; white-space: nowrap; margin-right:60px;">Assignment: </th>
                                <td><?php echo $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : ''); ?></td>
                            </tr>
                            <tr>
                                <th style="width: 1%; white-space: nowrap; margin-right:60px;">Location: </th>
                                <td><?php echo $model->city . ', ' . $model->state; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 1%; white-space: nowrap; margin-right:60px;">Time: </th>
                                <td>
                                    <?php echo date('m-d-Y H:i', strtotime($model->start_date)) . ' -<br />' .
                                               date('m-d-Y H:i', strtotime($model->end_date)); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="center marginBottom20">
            <?php 
            
            if ($model->resource_order_id)
            {
                echo CHtml::link('Resource Order',$this->createUrl('/engScheduling/resourceOrder', array('id'=>$model->id)),array(
                    'class'=>'btn btn-info center',
                    'target'=>'_blank'
                ));
            }
            
            ?>
        </div>
        
        <div class="row-fluid">
            <div class="span12">

                <h4 class="center"><em>Scheduled Employees</em></h4>
                <div class="table-responsive">
                    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array (
                        'id'=>'eng-scheduling-employee-grid',
                        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
                        'type' => 'striped hover condensed',
                        'dataProvider' => $employeeDataProvider,
                        'columns' => array(
                            array(
                                'header' => 'Crew Name',
                                'value' => '$data->crew_last_name . ", " . $data->crew_first_name'
                            ),
                            'engine_boss:boolean',
                            'on_engine:boolean',
                            array(
                                'header' => 'Start Date',
                                'value' => '$data->start_date . " " . $data->start_time',
                                'type' => 'date'
                            ),
                            array(
                                'header' => 'End Date',
                                'value' => '$data->end_date . " " . $data->end_time',
                                'type' => 'date'
                            ),
                            array(
                                'class' => 'bootstrap.widgets.TbButtonColumn',
                                'template' => '{update}',
                                'header' => 'Update',
                                'updateButtonUrl' => '$this->grid->controller->createUrl("/engScheduling/update", array("id"=>$data->engine_scheduling_id, "employeeID"=>$data->id))',
                                'visible' => !$enginesViewOnly
                            ),
                        ),
                        'enableSorting' => false,
                        'emptyText' => 'No Employees have been scheduled.'
                    )); ?>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">

    <?php
    
   /* if (in_array('Engine Manager',Yii::app()->user->types))
    {
        echo CHtml::link('Delete Scheduled Engine &nbsp;<i class="icon-trash"></i>', array('engScheduling/delete', 'id' => $model->id), array(
            'class' => 'btn btn-danger btn-engine',
            'id' => 'btnDeleteScheduledEngine',
            'data-dismiss' => 'modal'
        ));
    }*/
    
    if (!$enginesViewOnly)
    {
        echo CHtml::link('Update Schedule',$this->createUrl('/engScheduling/update',array('id'=>$model->id)),array(
            'class' => 'btn btn-primary btn-engine',
            'id' => 'btnAssignCaller'
        ));
    }
    
    echo CHtml::link('Cancel','#',array(
        'class' => 'btn btn-engine',
        'data-dismiss' => 'modal'
    ));
    
    ?>

</div>