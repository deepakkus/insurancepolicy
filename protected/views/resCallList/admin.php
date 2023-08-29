<?php

$this->setTitle('Response Call List');
    
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/resCallList/admin.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/resCallList/admin.js');
    
if (Yii::app()->user->hasFlash('message'))
{
    echo '<div style="color:red">';
    echo Yii::app()->user->getFlash('message');
    echo '</div>';
}
    
$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Call List'
);

?>

<div class="padding20">
    <h1>Response Call List</h1>

    <div class ="row-fluid">
        <div class ="span8">
            <?php echo CHtml::link('Columns','#', array('class'=>'column-toggle paddingRight20')); ?>
            <?php echo CHtml::link('Reset Filters', $this->createUrl('rescalllist/admin', array('resetFilters' => 1)), array('class'=>'paddingRight20')); ?>
            <?php echo CHtml::link('Download CSV', $this->createUrl('rescalllist/admin', array('download' => 1)), array('class'=>'paddingRight20')); ?>
        </div>

        <div class ="span4" style ="text-align:right;">
            <?php echo CHtml::link('Add To Call List',$this->createUrl('resCallList/create'), array('class'=>'paddingRight20')); ?>
        </div>
    </div>
        
    <?php echo $this->renderPartial('_adminColumnsToShow', array(
        'columnsToShow' => $columnsToShow,
        'columnsToShowName' => $columnsToShowName,
        'pageSize' => $pageSize,    
        'pageSizeName' => $pageSizeName,
    ));
    
    $columnArray = array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}',
            'header' => 'Update',
            'buttons' => array(
                'update' => array('url'=>'$this->grid->controller->createUrl("/resCallList/update", array("id"=>$data->id,))',)
            ),
        ),
    );
        
    foreach ($columnsToShow as $columnToShow)
    {
        if ($columnToShow == 'assigned_caller_user_name')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '(isset($data->assigned_caller_user->name)) ? $data->assigned_caller_user->name : "";',
                'filter' => CHtml::activeDropDownList($model, 'assigned_caller_user_name', CHtml::listData($callerUsers, 'name', 'name'), array('empty' => '')),
            );
        }
        else if ($columnToShow == 'client_name')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '(isset($data->client_name)) ? $data->client_name : "";', 
                'filter' => CHtml::activeDropDownList($model, 'client_name', CHtml::listData($clients, 'name', 'name'), array('empty' => '')),
            );
        }
        else if ($columnToShow == 'fire_name')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '(isset($data->fire_name)) ? $data->fire_name : "";',
                'filter' => CHtml::activeDropDownList($model, 'fire_name', CHtml::listData($fireNames, 'name', 'name'), array('empty' => '')),
            );
        }
        else if ($columnToShow == 'notice_type')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '(isset($data->notice_type)) ? ResNotice::getDispatchedType($data->notice_type) : "";',
                'filter' => CHtml::activeDropDownList($model, 'notice_type', 
                    CHtml::listData($noticeTypes,'wds_status', function($data) { return ResNotice::getDispatchedType($data->wds_status); }),
                    array('empty' => '')
                )
            );
        }
        else if ($columnToShow == 'res_triggered_threat')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'type' => 'boolean',
                'value' => '$data->'.$columnToShow, 
                'filter' => CHtml::activeDropDownList($model,'res_triggered_threat', array('Yes' => 'Yes','NO' => 'NO'), array('prompt'=>''))

            );
        }
        else if ($columnToShow == 'triggered')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'type' => 'boolean',
                'value' => '$data->'.$columnToShow, 
                'filter' => CHtml::activeDropDownList($model,'triggered', array(1 => 'Yes',0 => 'NO'), array('prompt'=>''))

            );
        }
        else if ($columnToShow == 'res_triggered_distance')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => 'isset($data->'.$columnToShow.') ? number_format($data->'.$columnToShow.', 2) : null', 
            );                
        }
        else if ($columnToShow == 'do_not_call')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '($data->'.$columnToShow.' == 1) ? "DNC" : ""',
                'type' => 'text',
                'filter' => CHtml::activeDropDownList($model,'do_not_call', array('1' => 'Selected','0' => 'Not-Selected'), array('prompt'=>''))
            );                
        }
        else if (strpos($columnToShow, 'member_') !== FALSE)
        {
            if ($columnToShow == 'member_num')
                $data_attr = 'property->member->member_num';
            else
                $data_attr = str_replace('member_', 'property->member->', $columnToShow);

            $columnArray[] = array(
                'name' => $columnToShow, 
                'value' => '(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
            );
        }
        else if ($columnToShow == 'assigned_caller_user_name')
        {
            $columnArray[] = array(
                'name' => 'assigned_caller_user_name', 
                'value' => '(isset($data->assigned_caller_user->name)) ? $data->assigned_caller_user->name : "";',
                'filter' => CHtml::activeDropDownList($model, 'assigned_caller_user_name', CHtml::listData($callerUsers, 'name', 'name'), array('empty' => '')),
            );
        }
        else if ($columnToShow == 'evacuated')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'type' => 'html',
                'value' => '(isset($data->evacuated)) ? str_replace(array("1","0"), array("Yes","No"), join("<br />", $data->evacuated)) : "";',
            ); 
        }
        else if ($columnToShow == 'published')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'published', array('1' => 'Yes','0' => 'No'), array('prompt'=>'')),
                'value' => '(isset($data->published)) ? str_replace(array("1","0"), array("Yes","No"), join("<br />", $data->published)) : "";',
            ); 
        }
        else if ($columnToShow == 'dashboard_comments')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'type' => 'html',
                'value' => '$this->grid->controller->getGridDashboardComments($data)',
                'htmlOptions'=>array('class'=>'grid-column-width-300'),
            );                
        }
        else if ($columnToShow == 'general_comments')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'type' => 'html',
                'value' => '$this->grid->controller->getGridGeneralComments($data)',
                'htmlOptions'=>array('class'=>'grid-column-width-300'),
            );                
        }
        else if ($columnToShow == 'prop_res_status')
        {
            $columnArray[] = array(
                'name' => 'prop_res_status',
                'header' => 'Call Status',
                'type' => 'html',
                'value' => '$data->prop_res_status',
                'htmlOptions'=>array('class'=>'grid-column-width-100'),
                'filter' => CHtml::activeDropDownList($model, 'prop_res_status', array(
                    'enrolled' => 'Enrolled',
                    'not enrolled' => 'Not Enrolled',
                ), array('empty' => '')),
            );                
        }
        else if ($columnToShow == 'res_triggered_response_status')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '(isset($data->res_triggered_response_status)) ? $data->res_triggered_response_status : "";',
                'filter' => CHtml::activeDropDownList($model, 'res_triggered_response_status', array(
                    'enrolled' => 'Enrolled',
                    'not enrolled' => 'Not Enrolled',
                    'declined' => 'Declined',
                    'ineligible' => 'Ineligible'
                ), array('empty' => '')),
            );   
        }
        else 
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'value' => '$data->'.$columnToShow, 
            );
        }
    }

?>

    <div class ="table-responsive">
    
        <?php
                        
        $this->widget('bootstrap.widgets.TbExtendedGridView',
            array (
                'id' => 'gridResponseCallList',
                //'beforeAjaxUpdate'=>'js:function(id, data) { console.log(data); }',
                'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'dataProvider' => $dataProvider,
                'filter' => $model,
                'template' => "{summary}{items}{pager}",
                'columns' => $columnArray,
                'bulkActions' => array (
                    'align' => 'left',
                    'actionButtons' => array (
                        array (
                            'id' => 'btnLaunchAssignCallerDialog',
                            'buttonType' => 'button',
                            'type' => 'primary',
                            'size' => 'small',
                            'label' => 'Assign Caller',
                            'click' => 'js:function(values){WDSResCallList.assignItemsToCaller(values);}',
                        ),
                        array(
                            'id' => 'btnPublishCalls',
                            'buttonType' => 'button',
                            'type' => TbHtml::BUTTON_COLOR_PRIMARY,
                            'size' => TbHtml::INPUT_SIZE_SMALL,
                            'label' => 'Pubish Calls',
                            'click' => 'js:function(values){ WDSResCallList.publishCalls(values); }',
                        ),
                    ),

                    // if grid doesn't have a checkbox column type, it will attach
                    // one and this configuration will be part of it
                    'checkBoxColumnConfig' => array(
                        'class' => 'CCheckBoxColumn',
                        'name' => 'id',
                        'htmlOptions' => array('style' => 'text-align: center;'),
                        'checkBoxHtmlOptions' => array('style' => 'width: 30px;')
                    ),
                ),
            )
        ); ?>
    </div>
</div>

<?php 
    // Assign Caller Modal
    $this->beginWidget('bootstrap.widgets.TbModal',
        array(
            'id' => 'selectCallerModal',
            'htmlOptions' => array('class' => 'modalSmall')
        )
    ); 
?> 
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3>Assign Caller</h3>
    </div>
    <input type="hidden" id="hiddenSelectedCallerIDs" />
    <div class="modal-body">
        <div class="smallHeading">Select a caller to assign to the checked rows:</div>
        <select id="ddlCaller">
            <option id="0" value="0"></option>
            <?php
                foreach ($callerUsers as $user) 
                {
                    echo "<option id='$user->id' value='$user->id'>$user->name</option>";
                }
            ?>
        </select>
    </div>
 
    <div class="modal-footer">
        <?php $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'id' => 'btnAssignCaller',
                'type' => 'primary',
                'label' => 'Assign',
                'url' => '#',
                'htmlOptions' => array('data-dismiss' => 'modal'),
            )
        ); ?>
        <?php $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'label' => 'Cancel',
                'url' => '#',
                'htmlOptions' => array('data-dismiss' => 'modal'),
            )
        ); ?>
    </div>
 
<?php $this->endWidget(); ?>
<?php $this->widget(
    'bootstrap.widgets.TbButton',
    array(
        'id' => 'btnSelectCallerModal',
        'label' => 'Select Caller',
        'type' => 'primary',
        'htmlOptions' => array(
            'data-toggle' => 'modal',
            'data-target' => '#selectCallerModal',
            'class' => 'hidden',
        ),
    )
); ?>





<?php 
    // Assign Caller Modal
    $this->beginWidget('bootstrap.widgets.TbModal',
        array(
            'id' => 'publishCallsModal',
            'htmlOptions' => array('class' => 'modalSmall')
        )
    ); 
?> 
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3>Publish Calls</h3>
    </div>
    <input type="hidden" id="hiddenSelectedCallerPublishIDs" />
    <div class="modal-body">
        <div class="smallHeading">Select Published Type to update selected rows:</div>
        <select id="ddlPublish">
            <option value="-1"></option>
            <option id="publish" value="1">Publish</option>
            <option id="unpublish" value="0">Unpublish</option>
        </select>
    </div>
 
    <div class="modal-footer">
        <?php $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'id' => 'btnPublishCall',
                'type' => 'primary',
                'label' => 'Update',
                'url' => '#',
                'htmlOptions' => array('data-dismiss' => 'modal'),
            )
        ); ?>
        <?php $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'label' => 'Cancel',
                'url' => '#',
                'htmlOptions' => array('data-dismiss' => 'modal'),
            )
        ); ?>
    </div>
 
<?php $this->endWidget(); ?>