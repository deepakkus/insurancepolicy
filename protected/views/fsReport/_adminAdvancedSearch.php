<div class="search-form">
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
        Statuses (hold ctrl for multi-select):
    </div>

    <div>
        <?php
            $options = array();
            $selectedOptions = array();
            foreach($fsReports->getStatuses() as $option)
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
            );
            
            echo CHtml::activeDropDownList($fsReports, 'status', $options, $htmlOptions); 
        ?>

        <?php if(empty($advSearch['types']) || $advSearch['types'] == 'agent'): ?>
            <div class="paddingTop10">
                <p><b>HA Date Between</b></p>
                <div>
			        <?php 
			        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				        'model'=>$fsReports,
				        'name'=>'advSearch[haDateBegin]',
				        'options'=>array(
					        'showAnim'=>'fold',
					        'dateFormat'=>'yy-mm-dd',
				        ),
				        'value'=>$advSearch['haDateBegin'],
			        ));  
			        ?>
			        and 
			        <?php 
			        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				        'model'=>$fsReports,
				        'name'=>'advSearch[haDateEnd]',
				        'options'=>array(
					        'showAnim'=>'fold',
					        'dateFormat'=>'yy-mm-dd',
				        ),
				        'value'=>$advSearch['haDateEnd'],
			        ));  
			        ?>
                </div>
            </div>
            <div class="paddingTop10">
                <p><b>Status Date Between</b></p>
                <div>
			        <?php 
			        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				        'model'=>$fsReports,
				        'name'=>'advSearch[statusDateBegin]',
				        'options'=>array(
					        'showAnim'=>'fold',
					        'dateFormat'=>'yy-mm-dd',
				        ),
				        'value'=>$advSearch['statusDateBegin'],
			        ));  
			        ?>
			        and 
			        <?php 
			        $this->widget('zii.widgets.jui.CJuiDatePicker', array(
				        'model'=>$fsReports,
				        'name'=>'advSearch[statusDateEnd]',
				        'options'=>array(
					        'showAnim'=>'fold',
					        'dateFormat'=>'yy-mm-dd',
				        ),
				        'value'=>$advSearch['statusDateEnd'],
			        ));  
			        ?>
                </div>
            </div>          
        <?php endif; ?>        
    </div>
    
    <div class="clearfix width100 paddingTop20">
        <div class="floatRight">
            <?php echo CHtml::submitButton('Search', array('name' => 'columnsSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeAdvancedSearch', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>

<?php $this->endWidget(); ?>
</div><!-- search-form -->
