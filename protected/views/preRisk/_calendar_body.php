<div id="engines" style="background-color:rgba(0,0,255,0.2);">
    <h3>Scheduled Engines</h3>
    <?php foreach($engines as $engine): ?>
        <?php echo CHtml::checkBox($engine,true,array('class'=>'engine-checkbox')); ?>
        <?php echo CHtml::label($engine,'',array('style'=>'font-weight: bold;')); ?><br />
    <?php endforeach; ?>
</div>

<!-- Calendar -->
<div id="calendar-wrapper">
    <div id="calendar"></div>
</div>

<!-- Modal Calendar Popup -->
<?php $this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'calendarmodal', 
    'fade'=>true,
    'options'=>array('backdrop'=>True)
)); ?>
 
<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h4 class="modal-title" style="text-align:center">Modal header</h4>
</div>
 
<div class="modal-body" style="padding-top:0;">
    <p></p>
</div>
 
<div class="modal-footer">
    <?php 
        $this->widget(
            'bootstrap.widgets.TbButton', 
            array(
                'label'=>'Close',
                'type' => TbHtml::BUTTON_COLOR_SUCCESS,
                'url'=>'javascript:void(0)',
                'htmlOptions'=>array('data-dismiss'=>'modal')
            )
        );
        $this->endWidget();
    ?>
</div>