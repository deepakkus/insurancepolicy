<div id="shiftTicketGridAdvSearchForm" class="column-form">
    <?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	));
    ?>
    <h3>Advanced Search</h3>

    <div id="shiftTicketGridAdvSearchForm" class="clearfix">
        
        <div class="floatLeft paddingTop10">
            <h4>Date Between</h4>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$gridShiftTickets,
                'name'=>'shiftTicketGridAdvSearch[dateBegin]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                    'onSelect' => new CJavaScriptExpression('function(selectedDate) {
                        var minDate = new Date(selectedDate);
                        minDate.setDate(minDate.getDate() + 2);
                        $("#shiftTicketGridAdvSearch_dateEnd").datepicker("option", "minDate", minDate);
                    }')
                ),
                'value'=>$shiftTicketGridAdvSearch['dateBegin']
            ));
            ?>
			    and
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$gridShiftTickets,
                'name'=>'shiftTicketGridAdvSearch[dateEnd]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                    'onSelect' => new CJavaScriptExpression('function(selectedDate) {
                        var maxDate = new Date(selectedDate);
                        $("#shiftTicketGridAdvSearch_dateBegin").datepicker("option", "maxDate", maxDate);
                    }')
                ),
                'value'=>$shiftTicketGridAdvSearch['dateEnd'],
            ));
            ?>
        </div>

        <div class="clearfix width100 paddingTop20">
            <div class="floatRight">
                <?php echo CHtml::submitButton('Search', array('name' => 'shiftTicketGridColumnsSubmit', 'class'=>'submitButton')); ?>
                <?php echo CHtml::link('close', '#', array('id' => 'shiftTicketGridCloseAdvancedSearch', 'class' => 'paddingLeft10')); ?>
            </div>
        </div>
    </div>

    <?php $this->endWidget(); ?>
</div>
<!-- search-form -->
