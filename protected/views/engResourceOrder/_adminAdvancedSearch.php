<div class="search-form">
	<h3>Advanced Search</h3>

	<?php $form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get'
	)); ?>

    <div class="paddingTop10 paddingBottom10">
        Clients (hold ctrl for multi-select):
    </div>

    <div>    
        <?php
            $clients = $model->availibleFireClients();
            $options = array();
            $selectedOptions = array();
            foreach ($clients as $client)
            {
                $options[$client->id] = $client->name;
                if (in_array($client->id, $advSearch['eng-clients']))
                    $selectedOptions[$client->id] = array('selected'=>'selected');
            }
            
            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => count($clients),
                'id' => 'adv-search-eng-clients'                   
            );
            
            echo CHtml::dropDownList('advSearch[eng-clients]', '', $options, $htmlOptions);
        ?>
    </div>

    <div class="paddingTop10 paddingBottom10">
        Assignments (hold ctrl for multi-select):
    </div>

    <div>
        <?php
            $assignments = EngScheduling::model()->getEngineAssignments();
            $options = array();
            $selectedOptions = array();
            foreach ($assignments as $assignment)
            {
                $options[$assignment] = $assignment;
                if(in_array($assignment, $advSearch['eng-assignments']))
                    $selectedOptions[$assignment] = array('selected'=>'selected');
            }
            
            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => count($assignments),
                'id' => 'adv-search-eng-assignments'                   
            );
            
            echo CHtml::dropDownList('advSearch[eng-assignments]', '', $assignments, $htmlOptions);
        ?>
    </div>

    <div class="clearfix width100 paddingTop20">
        <div style="padding-top: 3px">
            <?php echo CHtml::link('Clear Selections','#',array('class'=>'clear-form')); ?>
        </div>
        <div class="paddingTop10">
            <?php echo CHtml::submitButton('Search', array('name' => 'searchSubmit', 'class'=>'submitButton')); ?>
            <?php echo CHtml::link('close', '#', array('id' => 'closeAdvancedSearch', 'class' => 'paddingLeft10')); ?>
        </div>
    </div>

    <?php $this->endWidget(); ?>
</div>
