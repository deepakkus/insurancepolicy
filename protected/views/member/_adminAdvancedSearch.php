<div class="search-form">
	<h3>Advanced Search</h3>
    <div>
        Quick Views: 
    	<?php echo CHtml::link('Default','#',array('class'=>'default-advanced')); ?>
    </div>
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	));
	?>
    
    <div class="paddingTop10 paddingBottom10">
        FireShield Statuses (hold ctrl for multi-select):
    </div>
    
    <div>
        <?php
            $options = array();
            $selectedOptions = array();
            foreach($members->getProgramStatuses() as $option)
            {
                $options[$option] = $option;
                if(in_array($option, $advSearch['fs_statuses']))
                {
                    $selectedOptions[$option] = array('selected'=>"selected");
                }
            }

            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => '5',
                'name' => 'advSearch[fs_statuses]',
                'id' => 'adv-search-fs-statuses',                        
            );

            echo CHtml::activeDropDownList($members, 'mem_fireshield_status', $options, $htmlOptions); 
        ?>
    </div>
    
    <div class="clearfix width100 paddingTop20">
        <div class="floatRight">
            <?php echo CHtml::submitButton('Search', array('name' => 'columnsSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeAdvancedSearch', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>
    
<?php $this->endWidget(); ?>
</div><!-- search-form -->
