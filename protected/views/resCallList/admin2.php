<?php

$this->setTitle('Response Call List');
    
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/resCallList/admin.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/resCallList/admin.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/resCallList/table-gridview.js');

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
            <?php echo CHtml::link('Download CSV', $this->createUrl('rescalllist/admin2', array('download' => 1)), array('class'=>'paddingRight20')); ?>
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
    
   /* $columnArray = array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}',
            'header' => 'Update',
            'buttons' => array(
                'update' => array('url'=>'$this->grid->controller->createUrl("/resCallList/update", array("id"=>$data->id,))',)
            ),
        ),
    );*/

      ?> 
<?php
Yii::app()->clientScript->registerCss('res_call_lists','

.res_cal .slct select{
width:100%;}

.hdr_anch a{
color:#fff;}

div.grid-view table.items th {
    position: relative;
}

div.grid-view table.items th span.caret {
    display: inherit;
    width: 0;
    height: 0;
    position: absolute;
    right: 3px;
    top: 19px;
    vertical-align: top;
    border-top: 4px solid #000000;
    border-right: 4px solid transparent;
    border-left: 4px solid transparent;
    content: "";
}
.table-striped tbody>tr:nth-child(odd)>td, .table-striped tbody>tr:nth-child(odd)>th {
    background-color: #EBEBEB;
}

div.grid-view table.items tr.odd {
    background: none repeat scroll 0 0 #fff;
}
');

$grid_column = array(
array(
'id'=>'s_do_not_call',
'label'=>'Do Not Call',
'column'=>'do_not_call',
'gridcolumn'=>'do_not_call',
'filterid'=>'do_not_call'
),
array(
'id'=>'assign_caller',
'label'=>'Assigned Caller',
'column'=>'username',
'gridcolumn'=>'assigned_caller_user_name',
'filterid'=>'assigned_caller_user_name'
),
array(
'id'=>'s_client_name',
'label'=>'Client Name',
'column'=>'clientname',
'gridcolumn'=>'client_name',
'filterid'=>'client_name'
),
array(
'id'=>'s_fire_name',
'label'=>'Fire Name',
'column'=>'firename',
'gridcolumn'=>'fire_name',
'filterid'=>'fire_name'
),
array(
'id'=>'nocite_type',
'label'=>'Notice Type',
'column'=>'wds_status',
'gridcolumn'=>'notice_type',
'filterid'=>'notice_type'
),
array(
'id'=>'priority',
'label'=>'Priority',
'column'=>'priority',
'gridcolumn'=>'res_triggered_priority',
'filterid'=>'res_triggered_priority'
),
array(
'id'=>'threat',
'label'=>'Threat',
'column'=>'threat',
'gridcolumn'=>'res_triggered_threat',
'filterid'=>'res_triggered_threat'
),
array(
'id'=>'distance',
'label'=>'Distance',
'column'=>'distance',
'gridcolumn'=>'res_triggered_distance',
'filterid'=>'res_triggered_distance'
),
array(
'id'=>'response_status',
'label'=>'Response Status',
'column'=>'rtresponsestatus',
'gridcolumn'=>'res_triggered_response_status',
'filterid'=>'res_triggered_response_status'
),
array(
'id'=>'triggered',
'label'=>'Triggered',
'column'=>'triggered',
'gridcolumn'=>'triggered',
'filterid'=>'triggered'
),
array(
'id'=>'',
'label'=>'Evacuated',
'column'=>'evacuated',
'gridcolumn'=>'evacuated',
'filterid'=>'evacuated'
),
array(
'id'=>'',
'label'=>'Published',
'column'=>'publish',
'gridcolumn'=>'published',
'filterid'=>'published'
),
array(
'id'=>'',
'label'=>'Dashboard Comments',
'column'=>'dashboard_comments',
'gridcolumn'=>'dashboard_comments',
'filterid'=>'dashboard_comments'
),
array(
'id'=>'',
'label'=>'General Comments',
'column'=>'general_comments',
'gridcolumn'=>'general_comments',
'filterid'=>'general_comments'
),
array(
'id'=>'',
'label'=>'Call Status',
'column'=>'prop_res_status',
'gridcolumn'=>'prop_res_status',
'filterid'=>'prop_res_status'
),
array(
'id'=>'p_id',
'label'=>'Property ID',
'column'=>'property_id',
'gridcolumn'=>'property_id',
'filterid'=>'property_id'
),
array(
'id'=>'address_line_1',
'label'=>'Address Line 1',
'column'=>'address_line_1',
'gridcolumn'=>'property_address_line_1',
'filterid'=>'property_address_line_1'
),
array(
'id'=>'address_line_2',
'label'=>'Address Line 2',
'column'=>'address_line_2',
'gridcolumn'=>'property_address_line_2',
'filterid'=>'property_address_line_2'
),
array(
'id'=>'p_city',
'label'=>'City',
'column'=>'city',
'gridcolumn'=>'property_city',
'filterid'=>'property_city'
),
array(
'id'=>'p_state',
'label'=>'State',
'column'=>'state',
'gridcolumn'=>'property_state',
'filterid'=>'property_state'
),
array(
'id'=>'p_zip',
'label'=>'Zip',
'column'=>'zip',
'gridcolumn'=>'property_zip',
'filterid'=>'property_zip'
),
array(
'id'=>'s_member_num',
'label'=>'Member Num',
'column'=>'member_num',
'gridcolumn'=>'member_num',
'filterid'=>'member_num'
),
array(
'id'=>'first_name',
'label'=>'First Name',
'column'=>'first_name',
'gridcolumn'=>'member_first_name',
'filterid'=>'firstname'
),
array(
'id'=>'last_name',
'label'=>'Last Name',
'column'=>'last_name',
'gridcolumn'=>'member_last_name',
'filterid'=>'lastname'
),
);
$grid = new WDSGrid();
?>

<div class="table-responsive">
  <div id="gridResponseCallList" class="grid-view">
    <div class="summary">Displaying 1-<?php echo $pageSize;?> of <?php echo $pages['totalcount'];?> results.</div>
          <?php
          $updateid = 'calllistid';
          
          foreach($columnsToShow as $columnToShow)
          {
               if($columnToShow=='res_triggered_distance')
                {
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => 'number_format',
                        'type' => '',
                        'filter' => '',
                        'class' => ''
                    );
                }
               else if($columnToShow=='do_not_call')
                {
                    $options = array(''=> '','1' => 'Selected', '0' => 'Not-Selected');
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => 'boolean',
                        'type' => '',
                        'filter' => CHtml::dropDownList('do_not_call', '', $options, array('data-filter' => 'rcldonotcall')),
                        'value' => '',
                        'class' => ''
                    );
                }

                else if($columnToShow=='assigned_caller_user_name')
                {
                    $assignCallerlist = CHtml::listData($callerUsers, 'name', 'name');
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => CHtml::dropDownList('assigned_caller_user_name', '', $assignCallerlist, array('empty' => '','data-filter' => 'callerUser')),
                        'value' => '',
                        'class' => 'assign_caller_'
                    );
                }
                else if($columnToShow=='client_name')
                {
                    $clientfilter = CHtml::listData($clients, 'name', 'name');
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => CHtml::dropDownList('client_name', '', $clientfilter, array('empty' => '','data-filter' => 'client')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else if($columnToShow=='fire_name')
                {
                    $fireNames = ResNotice::model()->getFireNames();
                    $firefilter = CHtml::listData($fireNames, 'name', 'name');
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => CHtml::dropDownList('fire_name', '', $firefilter, array('empty' => '','data-filter' => 'fire')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else if ($columnToShow == 'notice_type')
                {
                    
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => CHtml::dropDownList('res_notice_wds_status', '', array(''=>'','1' => 'Dispatched', '2' => 'Non Dispatched','3' => 'Demobed'), array('data-filter'=> 'wds_status')),
                        'value' => 'ResNotice::getDispatchedType($data["wds_status"])',
                        'class' => ''
                    );
                }
                else if($columnToShow =='res_triggered_threat')
                {
                    $options = array(''=> '','1' => 'Yes', '0' => 'No');
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => 'boolean_number',
                        'filter' => CHtml::dropDownList('res_triggered_threat', '', $options, array('data-filter'=> 'threat')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else if($columnToShow =='res_triggered_response_status')
                {
                    $options = array(''=> '','enrolled' => 'Enrolled', 'not enrolled' => 'Not enrolled',
                                'declined' => 'Declined', 'ineligible' => 'Ineligible');
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => CHtml::dropDownList('res_triggered_response_status', '', $options, array('data-filter'=> 'responseStatus')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else if($columnToShow =='triggered')
                {
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => 'boolean_number',
                        'filter' => CHtml::dropDownList('resCallList_triggered', '', array(''=>'','1' => 'Yes', '0' => 'No'), array('data-filter'=> 'triggered')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else if($columnToShow =='published')
                {
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => 'boolean_number',
                        'filter' => CHtml::dropDownList('ResCallAttempt_publish', '', array(''=>'','1' => 'Yes', '0' => 'No'), array('data-filter'=> 'callpublish')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else if($columnToShow =='prop_res_status')
                {
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => CHtml::dropDownList('ResCallAttempt_prop_res_status', '', array(''=>'','enrolled' => 'Enrolled', 'not enrolled' => 'Not enrolled'), array('data-filter'=> 'propresstatus')),
                        'value' => '',
                        'class' => ''
                    );
                }
                else
                {
                    $columnArray[] = array(
                        'name' => $columnToShow,
                        'format' => '',
                        'type' => '',
                        'filter' => '',
                        'value' => '',
                        'class' => ''
                    );
                }
          }
          
         echo $grid->getGridDataItems($grid_column,$columnArray,$dataProvider,$updateid);
          //$grid->getGridData($grid_column,$columnArray,$dataProvider,$updateid); ?>
    <div class="pagination">
    <?php
        $page = 0;
        $prevclass = 'previous';
        if(($pages['totalcount']%$pageSize)>0)
        {
            $page = 1;
            $prevclass = 'previous disabled';
        }
     $totalpages = (int)($pages['totalcount']/$pageSize) + $page;
     $curpage = $page + 1;
    ?>
      <ul id="yw1" class="yiiPager yp1">
      <?php
      if($page>1)
      {
      ?>
      <li class="<?php echo $prevclass;?>"><a href="javascript:void(0)" id="call_prev" data-prev="<?php echo ($page-1);?>">←</a></li>
      <?php
      }
      else
      {
      ?>
      <li class="<?php echo $prevclass;?>"><a href="javascript:void(0)">←</a></li>
      <?php
      }
      $nextclass = '';
      
      for($i=1; $i<=$totalpages; $i++)
      {
          if($i<=10)
          {
      ?>
      <li id="r_call_list_pages" rel = "<?php echo $i;?>" class="<?php echo ($i==1)?'active':''?>" ><a href="javascript:void(0)" id="call_list_pages_"><?php echo $i;?></a></li>
      <?php
          }
      }
      ?>
      <li class="next<?php echo $nextclass;?>"><a href="javascript:void(0)" id="call_next" data-next="<?php echo ($page + 1);?>">→</a></li>
        
      </ul>
    </div>
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
                'id' => 'btnAssignCaller1',
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
                'id' => 'btnPublishCall1',
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
