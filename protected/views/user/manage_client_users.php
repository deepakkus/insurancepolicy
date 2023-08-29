<?php
/* @var $this UserController */

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/user/admin.js');
$this->breadcrumbs=array(
        'Users' => array('admin'),
        'Manage Client Users',
    );
    echo '<h1>Manage Client Users</h1>';
    echo '<div class ="table-responsive">';

    echo '<a href="'.$this->createUrl("user/createClientUser").'" class="btn btn-success btn-small">Add New User</a>';
    
    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped bordered condensed',
    	'id' => 'gridclientUser',
	    'dataProvider' => $model->searchClientUsers($pageSize),
	    'filter' => $model,
	    'columns' => array(
            array(
                'class' => 'CButtonColumn',
                'template' => '{update}',
                'header' => 'Actions',
                'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
                'htmlOptions' => array('style' => 'text-align: center'),
                'updateButtonUrl' => '$this->grid->controller->createUrl("user/updateClientUser", array("id" => $data->id))'
            ),
		    array(
			'name' => 'id',
			'filter' => CHtml::activeNumberField($model, 'id', array('max' => 99999999)),
		    ),
            array (
		        'name' => 'active',
                'type' => 'raw',
                'value' => '$data->activeMask;',
                'filter' => CHtml::activeDropDownList($model, 'active', $model->getActiveFilter(), array('encode'=>false, 'empty'=>''))
            ),
		    'name',
		    'username',
            'email',
            array(
                'name' => 'client',
                'value' => '(isset($data->client->name)) ? $data->client->name : "";',
                'filter' => CHtml::activeDropDownList($model, 'client_id', CHtml::listData(Client::model()->findAll(), 'id', 'name'), array('empty'=>''))
            ),
            array(
                'name' => 'removed',
                'type' => 'raw',
                'value' => '$data->removedMask;',
                'filter' => CHtml::activeDropDownList($model, 'removed', $model->getRemovedFilter(), array('encode'=>false, 'empty'=>''))
            ),
            array(
                'name' => 'pw_exp',
                'type' => 'date',
                'filter' => ''
            ),
		    array(
                'name' => 'type',
                'value' => '(isset($data->type)) ? $data->type : "";',
                'filter' => CHtml::activeDropDownList($model, 'type', $model->getTypes(), array('empty'=>''))
            )
        ),
        'bulkActions' => array (
                    'align' => 'left',
                    'actionButtons' => array (
                        array (
                            'id' => 'btnChangeStatus',
                            'buttonType' => 'button',
                            'type' => 'primary',
                            'size' => 'small',
                            'label' => 'Change Status',
                            'click' => 'js:function(values){WDSUser.changeStatus(values);}',
                        ),                       
                    ),                    
                    // if grid doesn't have a checkbox column type, it will attach
                    // one and this configuration will be part of it
                    'checkBoxColumnConfig' => array(
                        'class' => 'CCheckBoxColumn',
                        'name' => 'id',
                        'htmlOptions' => array('style' => 'text-align: center;'),
                        'checkBoxHtmlOptions' => array('style' => 'width: initial;')
                    ),
          ),
    ));
   echo '<label for="pageSize">Items Per Page</label>
		<select name="pageSize" id="optnpageSize">
		    <option value="10" '; if ($pageSize == 10) { echo 'selected="selected"'; } 
            echo ' >10</option>
			<option value="25" '; if ($pageSize == 25) { echo 'selected="selected"'; } 
            echo ' >25</option>
			<option value="50" '; if ($pageSize == 50) { echo 'selected="selected"'; } 
            echo ' >50</option>
			<option value="100" '; if ($pageSize == 100) { echo 'selected="selected"'; } 
            echo ' >100</option>
			</select>';
    echo '</div>';
?>
<?php 
    // Assign Client User Modal
    $this->beginWidget('bootstrap.widgets.TbModal',
        array(
            'id' => 'changeStatusModal',
            'htmlOptions' => array('class' => 'modalSmall')
        )
    ); 
?> 
    <div class="modal-header">
        <a class="close" data-dismiss="modal">&times;</a>
        <h3>Client Users</h3>
    </div>
    <input type="hidden" id="hiddenclientUserIDs" />
    <div class="modal-body">
        <div class="smallHeading">Are you Sure?</div>
        <select id="ddlChangestatus">            
            <option id="activate" value="1">Activate</option>
            <option id="deactivate" value="0">Deactivate</option>
        </select>
    </div>
 
    <div class="modal-footer">
        <?php $this->widget(
            'bootstrap.widgets.TbButton',
            array(
                'id' => 'btnClientUser',
                'type' => 'primary',
                'label' => 'Yes',
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
