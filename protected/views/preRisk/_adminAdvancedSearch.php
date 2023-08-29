<div class="search-form preRiskAdvSearchForm">
	<h3>Advanced Search</h3>
    <div>
        Quick Views: 
        <?php echo CHtml::link('Default','#',array('class'=>'default-advanced')); ?>
    </div>
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'post',
	));
	?>
    
    <div class="paddingTop10 paddingBottom10">
        <b>Statuses</b> (hold ctrl for multi-select):
    </div>
    <div>
        <?php
            $options = array();
            $selectedOptions = array();
            foreach($model->wdsStatuses() as $option)
            {
                $options[$option] = $option;
                if(in_array($option, $advSearch['statuses']))
                {
                    $selectedOptions[$option] = array('selected'=>"selected");
                }
            }

            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => '5',
                'name' => 'advSearch[statuses]',
                'id' => 'adv-search-statuses',
                'class' => 'width100',
            );
                        
            echo CHtml::activeDropDownList($model, 'status', $options, $htmlOptions); 
        ?>        
    </div>

    <div class="paddingTop10">
        <p><b>Completion Date Between</b></p>
        <div>
			<?php 
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'model'=>$model,
				'name'=>'advSearch[completionDate1]',
				'options'=>array(
					'showAnim'=>'fold',
					'dateFormat'=>'yy-mm-dd',
                    
                    'onSelect' => 'js: function(selectedDate) {
                        var minDate = new Date(selectedDate);
                        minDate.setDate(minDate.getDate() + 2);
                        $("#advSearch_completionDate2").datepicker("option", "minDate", minDate);
                    }'
				),
				'value'=>$advSearch['completionDate1'],
                 'htmlOptions'=>array(
                        'readonly'=>"readonly",
                         'style' => 'cursor:pointer;',
                            ),

			));  
			?>
			and 
            <?php
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'model'=>$model,
				'name'=>'advSearch[completionDate2]',
				'options'=>array(
					'showAnim'=>'fold',
					'dateFormat'=>'yy-mm-dd',
                    'onSelect' => 'js: function(selectedDate) {
                        var maxDate = new Date(selectedDate);
                        $("#advSearch_completionDate1").datepicker("option", "maxDate", maxDate);
                    }'
				),
				'value'=>$advSearch['completionDate2'],
                'htmlOptions'=>array(
                         'readonly'=>"readonly",
                          'style' => 'cursor:pointer;',
                            ),
			));
            ?>            
        </div>
    </div>

    <div class="paddingTop10">
        <p><b>Follow Up Campaign Between</b></p>
        <div>
			<?php 
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'model'=>$model,
				'name'=>'advSearch[followUpDate1]',
				'options'=>array(
					'showAnim'=>'fold',
					'dateFormat'=>'yy-mm-dd',
                    'onSelect' => 'js: function(selectedDate) {
                        var minDate = new Date(selectedDate);
                        minDate.setDate(minDate.getDate() + 2);
                        $("#advSearch_followUpDate2").datepicker("option", "minDate", minDate);
                    }'
				),
				'value'=>$advSearch['followUpDate1'],
                'htmlOptions'=>array(
                      'readonly'=>"readonly",
                       'style' => 'cursor:pointer;',
                            ),
			));  
			?>
			and 
			<?php 
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'model'=>$model,
				'name'=>'advSearch[followUpDate2]',
				'options'=>array(
					'showAnim'=>'fold',
					'dateFormat'=>'yy-mm-dd',
                    'onSelect' => 'js: function(selectedDate) {
                        var maxDate = new Date(selectedDate);
                        $("#advSearch_followUpDate1").datepicker("option", "maxDate", maxDate);
                    }'
				),
				'value'=>$advSearch['followUpDate2'],
                'htmlOptions'=>array(
                       'readonly'=>"readonly",
                        'style' => 'cursor:pointer;',
                            ),
			));  
			?>
        </div>
    </div>                        

    <div class="paddingTop10">
        <p><b>HA Date Between</b></p>
        <div>
			<?php 
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'model'=>$model,
				'name'=>'advSearch[haDateBegin]',
				'options'=>array(
					'showAnim'=>'fold',
					'dateFormat'=>'yy-mm-dd',
                    'onSelect' => 'js: function(selectedDate) {
                        var minDate = new Date(selectedDate);
                        minDate.setDate(minDate.getDate() + 2);
                        $("#advSearch_haDateEnd").datepicker("option", "minDate", minDate);
                    }'
				),
				'value'=>$advSearch['haDateBegin'],
                'htmlOptions'=>array(
                       'readonly'=>"readonly",
                        'style' => 'cursor:pointer;',
                            ),
			));  
			?>
			and 
			<?php 
			$this->widget('zii.widgets.jui.CJuiDatePicker', array(
				'model'=>$model,
				'name'=>'advSearch[haDateEnd]',
				'options'=>array(
					'showAnim'=>'fold',
					'dateFormat'=>'yy-mm-dd',
                    'onSelect' => 'js: function(selectedDate) {
                        var maxDate = new Date(selectedDate);
                        $("#advSearch_haDateBegin").datepicker("option", "maxDate", maxDate);
                    }'
				),
				'value'=>$advSearch['haDateEnd'],
                'htmlOptions'=>array(
                       'readonly'=>"readonly",
                        'style' => 'cursor:pointer;',
                            ),
			));  
			?>
        </div>
    </div>                        

    <div class="clearfix width100 paddingTop20">
        <div class="floatRight">
            <?php echo CHtml::submitButton('Search', array('name' => 'searchSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeAdvancedSearch', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>
    
<?php $this->endWidget(); ?>
</div><!-- search-form -->
