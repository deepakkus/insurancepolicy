<div class="column-form" style="width:250px;">

    <?php $form=$this->beginWidget('CActiveForm', array(
        'action'=>Yii::app()->createUrl($this->route),
        'method'=>'get',
    )); ?>

    <h3 class="center">Columns To Show</h3>

    <div class="paddingTop20 paddingBottom10">
        Quick Views: 
        <?php echo CHtml::link('Default', '#', array('class' => 'default-quick-view')); ?>
    </div>

    <div class="columnsToShow">
        <div class="row-fluid marginTop10">
            <div class="span6">
                <h4>Fire Details</h4>
                <div><input type="checkbox" name="columnsToShow[Fire_City]" value="Fire_City" <?php Helper::checkIfInArray($columnsToShow, 'Fire_City'); ?> />Fire City</div>
                <div><input type="checkbox" name="columnsToShow[Fire_State]" value="Fire_State" <?php Helper::checkIfInArray($columnsToShow, 'Fire_State'); ?> />Fire State</div>
                <div><input type="checkbox" name="columnsToShow[Fire_Size]" value="Fire_Size" <?php Helper::checkIfInArray($columnsToShow, 'Fire_Size'); ?> />Fire Size</div>
                <div><input type="checkbox" name="columnsToShow[Fire_Containment]" value="Fire_Containment" <?php Helper::checkIfInArray($columnsToShow, 'Fire_Containment'); ?> />Fire Containment</div>
                <div><input type="checkbox" name="columnsToShow[Fire_Fuels]" value="Fire_Fuels" <?php Helper::checkIfInArray($columnsToShow, 'Fire_Fuels'); ?> />Fire Fuels</div>
                <br />
            </div>
        </div>
    </div>

    <div class="clearfix width100 paddingTop20">
        <div style="padding-top: 3px">
            <?php echo CHtml::link('Clear Selections','#',array('class'=>'clear-checked')); ?>
        </div>
        <div class="paddingTop10">
            <?php echo CHtml::submitButton('Update Grid', array('name'=>'columnsSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeColumnsToShow', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>

    <?php $this->endWidget(); ?>

</div>