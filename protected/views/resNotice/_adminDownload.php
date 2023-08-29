<?php $this->beginWidget('bootstrap.widgets.TbCollapse'); ?>

<div class="accordion-group marginTop10">
    <div class="accordion-heading">
        <a class="accordion-toggle" data-toggle="collapse"
            data-parent="#accordion2" href="#collapseTwo">
            <b>Export Notices (click to expand)</b>
        </a>
    </div>

    <div id="collapseTwo" class="accordion-body collapse">
        <div class="accordion-inner marginTop20">
            <?php $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	            'id'=>'notice-form',
                'type' => 'horizontal',
                'enableAjaxValidation' => false,
                'htmlOptions' => array( 'class' => 'well')
            )); ?>

            Start Date: <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'dateStart', 'value' => date('m/01/Y') )); ?>
            End Date: <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'dateEnd', 'value' => date('m/d/Y') )); ?>

            <?php $this->widget('bootstrap.widgets.TbSelect2', array(
                'asDropDownList' => true,
                'name' => 'clientID',
                'data' => Helper::getFireClients(),
            )); ?>

            <?php echo CHtml::submitButton('Download Notice Data', array('class' => 'submit marginTop20', 'style' => 'display:block')); ?>

            <?php $this->endWidget(); ?>
        </div>
    </div>
</div>

<?php $this->endWidget(); ?>

<?php

$script = '
    var url = "' . $this->createAbsoluteUrl("resNotice/downloadNotices") . '";

    document.getElementById("notice-form").addEventListener("submit", function(e) {
        e.preventDefault();
        var dateStart = this.elements[0].value;
        var dateEnd = this.elements[1].value;
        var clientID = this.elements[3].value;
        window.location.href = url + "&dateStart=" + encodeURIComponent(dateStart) + "&dateEnd=" + encodeURIComponent(dateEnd) + "&clientID=" + clientID;
    });
';

Yii::app()->clientScript->registerScript('noticeExportFormSCript', $script, CClientScript::POS_END);

?>