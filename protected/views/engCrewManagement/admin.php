<?php

/* @var $this EngCrewManagementController */
/* @var $model EngCrewManagement */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Crew Management'
);

$engineManager = (in_array('Engine Manager',Yii::app()->user->types) || in_array('Admin',Yii::app()->user->types)) ? true : false;

// Fixing picture resizing issue on Chrome where picture was getting resized too small.
Yii::app()->clientScript->registerCss('crewPictureCSS','
    .crew-picture img {
        max-width: 100px;
    }
');

?>

<h1>Crew Management</h1>

<?php if (in_array('Engine Manager',Yii::app()->user->types)): ?>

<div class="marginTop10 marginBottom10">
    <p><i>Note: An engine user must be created first, than attached to a crew member</i></p>
    <a class="btn btn-success" href="<?php echo $this->createUrl('/user/createEngineUser'); ?>">Create New Engine User</a>
    <a class="btn btn-success" href="<?php echo $this->createUrl('/engCrewManagement/create'); ?>">Create New Crew Member</a>
</div>

<?php endif; ?>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
	    'id' => 'eng-crew-management-grid',
	    'dataProvider' => $model->search(),
	    'filter' => $model,
	    'columns' => array(
		    array(
			    'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}{delete}',
                'header' => 'Actions',
                'buttons' => array(
                    'delete' => array(
                        'visible' => 'false'
                    ),
                    'update' => array(
                        'visible' => "'$engineManager'"
                    )
                ),
                'visible' => $engineManager
		    ),
		    'first_name',
		    'last_name',
            //'address',
		    'work_phone',
		    'cell_phone',
		    'email',
		    array(
                'name' => 'crew_type',
                'filter' => CHtml::activeDropDownList($model,'crew_type',$model->getCrewTypes(),array('prompt'=>' '))
            ),
            array(
                'name' => 'fire_officer',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'fire_officer', array(0 => 'No',1 => 'Yes'), array('prompt'=>''))
            ),
            array(
                'name' => 'alliance',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'alliance', array(0 => 'No',1 => 'Yes'), array('prompt'=>''))
            ),
            array(
                'name' => 'wdsfleet_active',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'wdsfleet_active', array(0 => 'No',1 => 'Yes'), array('prompt'=>''))
            ),
            array(
                'name' => 'wdsfleet_download_kmz',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'wdsfleet_download_kmz', array(0 => 'No',1 => 'Yes'), array('prompt'=>''))
            ),
            array(
                'name' => 'wdsfleet_download_policy',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($model,'wdsfleet_download_policy', array(0 => 'No',1 => 'Yes'), array('prompt'=>''))
            ),
            'alliance_partner',
            array(
                'header' => 'Image',
                'type' => 'html',
                'value' => function($data) {
                    if ($data->photo_id)
                        return CHtml::image($this->createUrl('/file/loadFile', array('id'=>$data->photo_id)), 'crew member image', array('width'=>'100'));
                    return '';
                },
                'htmlOptions' => array('class' => 'crew-picture')
            ),
            array(
                'header' => 'Engine Website',
                'type' => 'html',
                'value' => function($data) {
                    //Crew member that is tied to an active user
                    if (isset($data->user) && $data->user->active && in_array('Engine User', $data->user->getSelectedTypes()))
                    {
                        return CHtml::link('Login', $this->createUrl('engCrewManagement/viewEngineWebsite', array('crewID'=>$data->id)));
                    }
                    //Crew Member that is tied to user, but is deactivated
                    else if (isset($data->user) && !$data->user->active)
                    {
                        return "<i>User is deactivated</i>";
                    }
                    //No tie - not setup properly
                    else
                    {
                        return "<i>User is not setup</i>";
                    }
                }
            )
	    )
    )); ?>

</div>