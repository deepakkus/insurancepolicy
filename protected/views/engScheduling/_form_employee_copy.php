<?php
                            
$lastScheduledModel = EngScheduling::model()->with(array('engine'))->find(array(
    'condition' => "t.engine_id = :engine_id AND t.id < :id AND engine.active = 1",
    'params' => array(':engine_id' => $schedulingmodel->engine_id, ':id' => $schedulingmodel->id),
    'order' => 't.id desc',
    'limit' => 1
));

if ($lastScheduledModel):
    $lastScheduledModelDataProvider = EngSchedulingEmployee::model()->search($lastScheduledModel->id);
?>

    <div class="row-fluid">
        <div style="border: 2px solid #CCCCCC; padding: 10px;">
            <h3 style="font-weight: 200;">Last Employees Scheduled on Engine</h3>
            <div style="opacity: 0.7;">
                <div class="table-responsive">
                    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
                        'id' => 'gridEmployees',
                        'cssFile' => '../../css/wdsExtendedGridView.css',
                        'type' => 'striped bordered condensed',
                        'dataProvider' => $lastScheduledModelDataProvider,
                        'columns' => array(
                            array(
                                'header' => 'Crew Name',
                                'value' => '$data->crew_last_name . ", " .$data->crew_first_name'
                            ),
                            'engineScheduling.engine_name',
                            'engineScheduling.assignment',
                            'engineScheduling.start_date',
                            'engineScheduling.end_date',
                        ),
                        'emptyText' => 'No Employees Found Scheduled for this engine.'
                    )); ?>
                </div>
            </div>
            <a href="<?php echo $this->createUrl('/engSchedulingEmployee/copyScheduledEmployees', array('lastid'=>$lastScheduledModel->id,'engineid'=>$schedulingmodel->id)); ?>" style="font-size: 110%;">Copy to This Schedule</a>
        </div>
    </div>

<?php endif; ?>