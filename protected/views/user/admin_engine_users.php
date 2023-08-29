<?php
/* @var $this UserController */

$this->breadcrumbs=array('Manage Engine Users');
?>
    <h1>Manage Engine Users</h1>
    <div class ="table-responsive">
        <p>
            <i>Note: After adding an engine user, create a corrisponding crew member and attach them</i>
        </p>
        <a href="<?php echo $this->createUrl("user/createEngineUser"); ?>" class="btn btn-success btn-small">Add New User</a>
        <a href="<?php echo ($id) ? $this->createUrl("engCrewManagement/create", array("userID"=>$id)) : $this->createUrl("engCrewManagement/create"); ?>" class="btn btn-success btn-small">Add Crew Member</a>
        <a href="<?php echo $this->createUrl("engCrewManagement/admin"); ?>" class="btn btn-success btn-small">View Crew Members</a>
    
        <?php
        $this->widget('bootstrap.widgets.TbExtendedGridView', array(
            'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
            'type' => 'striped bordered condensed',
    	    'id' => 'gridclientUser',
	        'dataProvider' => $model->searchEngineUsers($pageSize),
	        'filter' => $model,
	        'columns' => array(
                array(
                    'class' => 'CButtonColumn',
                    'template' => '{update}',
                    'header' => 'Actions',
                    'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
                    'htmlOptions' => array('style' => 'text-align: center'),
                    'updateButtonUrl' => '$this->grid->controller->createUrl("user/updateEngineUser", array("id" => $data->id))'
                ),
                array (
		            'name' => 'active',
                    'type' => 'raw',
                    'value' => '$data->activeMask;',
                    'filter' => CHtml::activeDropDownList($model, 'active', $model->getActiveFilter(), array('encode'=>false, 'empty'=>''))
                ),
		        'name',
                array(
                    'name' => 'username',
                    'filter' => false
                ),
                array(
                    'name' => 'email',
                    'filter' => false
                ),
                array(
                    'name' => 'alliance',
                    'value' =>'(isset($data->alliance->name)) ? $data->alliance->name : "";',
                    'filter'=> CHtml::activeDropDownList($model, 'alliance_id', CHtml::listData(Alliance::model()->findAll(), 'id', 'name'), array('empty'=>''))
                ),
                array(
                    'name' => 'removed',
                    'type' => 'raw',
                    'value' => '$data->removedMask;',
                    'filter' => CHtml::activeDropDownList($model, 'removed', $model->getRemovedFilter(), array('encode'=>false, 'empty'=>''))
                ),
		        array(
                    'name' => 'type',
                    //'value' => '(isset($data->type)) ? $data->type : "";',
                    'value' => '$this->grid->controller->getDropdownsItems($data)',
                    'filter' => CHtml::activeDropDownList($model, 'type', $model->getEngineUserTypes(), array('empty'=>'')),
                    'type' => 'html'
                )
            )
        ));
        ?>

</div>
