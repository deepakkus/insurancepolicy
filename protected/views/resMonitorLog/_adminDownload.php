<?php $this->beginWidget('bootstrap.widgets.TbCollapse'); ?>
    <div class="accordion-group marginTop10">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse"
                data-parent="#accordion2" href="#collapseTwo">
                <b>Export Monitor Log (click to expand)</b>
            </a>
        </div>

        <div id="collapseTwo" class="accordion-body collapse">
            <div class="accordion-inner marginTop20">
                <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	                'id'=>'monitor-log-form',
                    'type' => 'inline',
                    'enableAjaxValidation' => false,
                    'htmlOptions' => array( 'class'=>'well', 'onsubmit'=>"return false;")
                )); ?>

                Start Date: <?php $this->widget('bootstrap.widgets.TbDatePicker', array(
                    'name' => 'dateStart',
                    'value' => date('m/d/Y',strtotime(date('Y-m') . "-01"))
                )); ?>

                End Date: <?php $this->widget('bootstrap.widgets.TbDatePicker', array(
                    'name' => 'dateEnd',
                    'value' => date('m/d/Y')
                )); ?>

                <?php $this->widget('bootstrap.widgets.TbSelect2', array(
                    'asDropDownList' => true,
                    'name' => 'clientID',
                    'data' => Helper::getFireClients(),
                )); ?>

                <?php echo CHtml::submitButton('Download Monitor Log', array('class'=>'submit marginTop20', 'onclick'=>'send();', 'style'=>'display:block')); ?>

                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>
<?php $this->endWidget(); ?>


<script type="text/javascript">
 
function send()
{
    var data = $("#monitor-log-form").serializeArray();
    var dateStart = data[0]['value'];
    var dateEnd = data[1]['value'];
    var clientID = data[2]['value'];

    var url = '<?php echo Yii::app()->createAbsoluteUrl("resMonitorLog/download"); ?>' + '&dateStart=' + encodeURIComponent(dateStart) + '&dateEnd=' + encodeURIComponent(dateEnd) + '&clientID=' + clientID;

    window.location.href = url;
}
 
</script>