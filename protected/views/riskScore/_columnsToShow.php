<div class="column-form" style="width: 300px;">

	<?php $form = $this->beginWidget('CActiveForm', array(
		'action' => Yii::app()->createUrl($this->route),
		'method' => 'get'
	)); ?>

    <?php
        
    $model = new RiskScore;
    $column1 = array('score_v','score_whp','score_wds','address','city','state','scoreType','match_address','match_score','match_type');
    $column2 = array('processed','userName','clientName','geocoded','wds_geocode_level','version','property_pid','date_created');
        
    ?>

	<h3>Columns To Show</h3>

    <div class="paddingTop10 paddingBottom10">
        Quick Views: 
        <?php echo CHtml::link('Default', '#defaultView', array('class' => 'default-quick-view')); ?>
    </div>

    <div class="row-fluid">
        <div class="span6">
            <?php
            foreach ($column1 as $column)
            {
                echo CHtml::tag('div', array(), 
                    CHtml::checkBox("columnsToShow[$column]", in_array($column, $columnsToShow), array('value' => $column)) . ' ' . $model->getAttributeLabel($column)
                );
            }
            ?>
        </div>

        <div class="span6">
            <?php
            foreach ($column2 as $column)
            {
                echo CHtml::tag('div', array(), 
                    CHtml::checkBox("columnsToShow[$column]", in_array($column, $columnsToShow), array('value' => $column)) . ' ' . $model->getAttributeLabel($column)
                );
            }
            ?>
        </div>
    </div>
    
    <div class="clearfix width100 paddingTop20">
        <div class="floatLeft" style="padding-top: 3px">
            <?php echo CHtml::link('Clear Selections','#',array('class'=>'clear-checked')); ?>
        </div>
        <div class="floatRight">
        	<?php echo CHtml::submitButton('Update View', array('name' => 'columnsSubmit', 'class' => 'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeColumnsToShow', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>

	<?php $this->endWidget(); ?>

</div>
