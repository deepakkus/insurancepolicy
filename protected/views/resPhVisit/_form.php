<?php

/* @var $this ResPhVisitController */
/* @var $model ResPhVisit */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerScriptFile('/js/resPolicyholderActions/updateVisit.js');

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'visit_photos_form',
    'type' => 'horizontal',
    'method' => 'post',
    'htmlOptions' => array(
        'enctype' => 'multipart/form-data'
    )
));


if($model->isNewRecord)
{
    $this->widget('bootstrap.widgets.TbTabs', array(
        'type' => 'tabs',
        'id'=>'action-tabs',
        'tabs' => array(
            array(
                'label' => 'Actions',
                'content' => $this->renderPartial('_form_visit', array(
                    'model' => $model,
                    'pid' => $pid,
                    'fire_id' => $fireID,
                    'client_id' => $clientID,
                    'form' => $form,
                ), true),
                'active' => true
            )
        )
    ));
}
else
{

	$this->renderPartial('_policyHeader', array('model'=>$model));
	
    $this->widget('bootstrap.widgets.TbTabs', array(
        'type' => 'tabs',
        'id'=>'action-tabs',
        'tabs' => array(
            array(
                'label' => 'Actions',
                'content' => $this->renderPartial('_form_visit',array(
                    'model'=>$model,
                    'form' => $form,
                    'showStatus' => $showStatus,
                ), true),
                'active' => isset($_GET['photoTab']) ? false : true
            ),
            array(
                'label' => 'Photos',
                'content' => $this->renderPartial('_form_photos',array(
                    'visitmodel' => $model,
					'photos' => $photos,
                ), true),
                'active' => isset($_GET['photoTab']) ? true : false
            )
        )
    ));

}

echo CHtml::submitButton('Save All', array('class'=>'submit pull-right btn-large','id'=>'save_all_items'));

$this->endWidget();
?>
<script type="application/javascript">
$( "#save_all_items" ).click(function() {
  var ext = $('#create_file_id').val().split('.').pop().toLowerCase();
        if(($.inArray(ext, ['png','jpg','jpeg']) == -1) && $('#create_file_id').val() != '') {
            alert('Please upload jpg/png image only!');
            return false;
        }
});
</script>