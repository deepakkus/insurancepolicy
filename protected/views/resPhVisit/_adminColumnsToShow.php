<?php

/* @var $this EngShiftTicketController */
/* @var $columnsToShow string[] */

$model = new ResPhVisit;

$columns = array(
    'memberFirstName' => $model->getAttributeLabel('memberFirstName'),
    'memberLastName' => $model->getAttributeLabel('memberLastName'),
    'status' => $model->getAttributeLabel('status'),
    'date_action' => $model->getAttributeLabel('date_action'),
    'date_created' => $model->getAttributeLabel('date_created'),
    'date_updated' => $model->getAttributeLabel('date_updated'),
    'userName' => $model->getAttributeLabel('userName'),
    'lastUpdateUserName' => $model->getAttributeLabel('lastUpdateUserName'),
    'approvalUserName' => $model->getAttributeLabel('approvalUserName'),
    'review_status' => $model->getAttributeLabel('review_status'),
    'comments' => $model->getAttributeLabel('comments'),
    'publish_comments' => $model->getAttributeLabel('publish_comments'),
    'propertyAddress' => $model->getAttributeLabel('propertyAddress'),
    'propertyPolicy' => $model->getAttributeLabel('propertyPolicy'),
    'response_status' => $model->getAttributeLabel('response_status'),
);

?>

<?php $form = $this->beginWidget('CActiveForm', array(
    'action'=>Yii::app()->createUrl($this->route),
    'method'=>'get'
)); ?>

<div style="margin: 10px;">

    <div class="clearfix">
        <div class="floatLeft marginRight20">
            <h4>Columns</h4>
            <div class="control-group">
                <?php echo CHtml::checkBoxList('columnsToShow', $columnsToShow, $columns, array(
                    'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                    'labelOptions' => array('class' => 'checkbox'),
                    'separator' => ''
                )); ?>
            </div>
        </div>
    </div>

    <div class="paddingTop10">
        <?php echo CHtml::submitButton('Update View', array('class' => 'submitButton')); ?>
        <?php echo CHtml::button('Close', array('id' => 'close-columns-select', 'class' => 'submitButton')); ?>
    </div>

</div>

<?php $this->endWidget(); ?>