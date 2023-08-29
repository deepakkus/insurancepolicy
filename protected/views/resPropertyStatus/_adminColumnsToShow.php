<div class="column-form hidden">
	<?php
	    $form = $this->beginWidget('CActiveForm', array(
		    'action' => Yii::app()->createUrl($this->route),
		    'method' => 'get',
	    ));
    ?>

	<h3>Columns To Show</h3>
        
    <div class="paddingTop10 paddingBottom10">
        Quick Views:
    	<?php echo CHtml::link('Default', '#', array('id'=>'defaultQuickView')); ?>
    </div>
            
	<div class="columnsToShow clearfix">
        <div class="floatLeft paddingRight20">
            <h4>General</h4>
            <?php
                $columnList = array(
                    'id' => 'ID', 
                    'client_name' => 'Client',
                    'fire_name' => 'Fire Name',
                    'notice_name' => 'Notice',
                    'property_pid' => 'Property ID',
                );
                
                foreach ($columnList as $name => $uiName) 
                {
                    echo "<div><input type='checkbox' name='" . $columnsToShowName . "[$name]' value='$name' data-type='propertyStatusColumn' ";
                    echo Helper::checkIfInArray($columnsToShow, $name);
                    echo "/> $uiName</div>";
                }
            ?>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>Property</h4>
            <?php
                $propColumnList = array(
                    'member_first_name' => 'First Name',
                    'member_last_name' => 'Last Name',
                    'property_address_line_1' => 'Address',
                    'property_address_line_2' => 'Address Line 2',
                    'property_city' => 'City',
                    'property_state' => 'State',
                    'property_zip' => 'Zip',
                    'property_response_status' => 'Response Status',
                );
                
                foreach ($propColumnList as $name => $uiName) 
                {
                    echo "<div><input type='checkbox' name='" . $columnsToShowName . "[$name]' value='$name' data-type='propertyStatusColumn' ";
                    echo Helper::checkIfInArray($columnsToShow, $name);
                    echo "/> $uiName</div>";
                }
            ?>
        </div>
        <div class="floatLeft paddingRight20">
            <h4>Property Status</h4>
            <?php
                $memColumnList = array(
                    'priority' => 'Priority', 
                    'threat' => 'Threat',
                    'distance' => 'Distance',
                    'division' => 'Division',
                    'engine_name' => 'Engine',
                    'status' => 'Status',
                    'date_visited' => 'Date Visited',
                    'has_photo' => 'Has Photo',
                    'actions' => 'Actions',
                    'other_issues' => 'Other Issues',
                );
                
                foreach ($memColumnList as $name => $uiName) 
                {
                    echo "<div><input type='checkbox' name='" . $columnsToShowName . "[$name]' value='$name' data-type='propertyStatusColumn' ";
                    echo Helper::checkIfInArray($columnsToShow, $name);
                    echo "/> $uiName</div>";
                }
            ?>
        </div>
	</div>

	<div class="paddingTop10">
		<label for="<?php echo $pageSizeName; ?>">Items Per Page:</label>
		<select name="<?php echo $pageSizeName; ?>">                        
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
