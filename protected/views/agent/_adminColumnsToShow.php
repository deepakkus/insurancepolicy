<div class="column-form" style="min-width: 300px;">
	<?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	));
	?>

	<h3>Columns To Show</h3>
        
    <div class="paddingTop10 paddingBottom10">
        Quick Views:
    	<?php echo CHtml::link('Default','#defaultView',array('class'=>'default-quick-view')); ?>
    </div>
            
	<div class="columnsToShow clearfix">
        <div class="floatLeft paddingRight20">
            <h4>Agent</h4>
            <?php

            $agent = Agent::model();

            $columnList = array(
                'id', 
                'agent_num', 
                'first_name', 
                'last_name', 
                'fs_carrier_key', 
                'agent_client_name',
                'agent_type'
            );
                
            foreach ($columnList as $column) 
            {
                echo "<div><input type='checkbox' name='columnsToShow[$column]' value='$column' ";
                echo Helper::checkIfInArray($columnsToShow, $column);
                echo "/> {$agent->getAttributeLabel($column)}</div>";
            }

            ?>
        </div>                
	</div>

	<div class="paddingTop10">
		<label for="pageSize">Items Per Page:</label>
		<select name="pageSize">                        
			<option value="10" <?php if($pageSize == 10){echo 'selected="selected"';} ?> >10</option>
			<option value="25" <?php if($pageSize == 25){echo 'selected="selected"';} ?> >25</option>
			<option value="50" <?php if($pageSize == 50){echo 'selected="selected"';} ?> >50</option>
			<option value="100" <?php if($pageSize == 100){echo 'selected="selected"';} ?> >100</option>
			<option value="200" <?php if($pageSize == 200){echo 'selected="selected"';} ?> >200</option>
			<option value="500" <?php if($pageSize == 500){echo 'selected="selected"';} ?> >500</option>
			<option value="1000" <?php if($pageSize == 1000){echo 'selected="selected"';} ?> >1000</option>
		</select>
	</div>
	
    <div class="clearfix width100 paddingTop20">
        <div class="floatLeft" style="padding-top: 3px">
            <?php echo CHtml::link('Clear Selections','#',array('class'=>'clear-checked')); ?>
        </div>
        <div class="floatRight">
        	<?php echo CHtml::submitButton('Update View', array('name'=>'columnsSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeColumnsToShow', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>

	<?php $this->endWidget(); ?>

</div><!-- column-form -->
