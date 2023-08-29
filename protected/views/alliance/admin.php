<?php
/* @var $this AllianceController */
/* @var $model Alliance */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Alliance'
);

$enginesViewOnly = in_array('Engine View',Yii::app()->user->types) && (!in_array('Engine',Yii::app()->user->types) && !in_array('Engine Manager',Yii::app()->user->types));

?>

<h1>Manage Alliance Partners</h1>

<?php if (in_array('Engine Manager',Yii::app()->user->types)): ?>

    <div class="marginTop10 marginBottom10">
        <a class="btn btn-success" href="<?php echo $this->createUrl('/alliance/create'); ?>">Create New Alliance Partner</a>
    </div>

<?php endif; ?>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => '../../css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
	    'id' => 'alliance-grid',
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
                    )
                ),
                'visible' => !$enginesViewOnly
		    ),
		    'name',
		    'contact_first',
		    'contact_last',
		    'phone',
            'phone_alt',
            'email',
		    'preseason_agreement',
            array(
                'name' => 'email_reminder',
                'value' => '($data->email_reminder == 1) ? "Yes" : "No"',
                'type' => 'raw',
                'filter' => CHtml::activeDropDownList($model, 'email_reminder', array('1' => 'Yes', '0' => 'No'), array('encode' => false,'prompt' => ''))
            ),
            array(
                'name' => 'active',
                'value' => '($data->active == 1) ? "Yes" : "No"',
                'type' => 'raw',
                'filter' => CHtml::activeDropDownList($model, 'active', array('1' => 'Yes', '0' => 'No'), array('encode' => false,'prompt' => ''))
            ),
	    )
    )); ?>

</div>
