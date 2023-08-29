<?php
    $this->breadcrumbs=array('Members' => array('admin'), 'Trial Generator');
    echo CHtml::cssFile(Yii::app()->baseUrl.'/css/trialGenerator.css');
?>

<h1>Trial Generator</h1>

<div class="form marginTop10">
    
    <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'trialGeneratorForm',
            'enableAjaxValidation' => false,
        ));
    ?>
    <div class="paddingTop10">
        <b>x</b> denotes that the trial iteration number will be concatenated to the field's text.
    </div>
    
    <h3 class="paddingTop10">Member</h3>
    <div class="clearfix formContainer">
        <div class="fluidField">
            <?php
                echo $form->labelEx($member, 'member_num');
                echo $form->textField($member, 'member_num', array('class' => 'shortTextField')) . ' + <b>x</b>';
                echo $form->error($member, 'member_num');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member, 'first_name');
                echo $form->textField($member, 'first_name');
                echo $form->error($member, 'first_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member, 'last_name');
                echo $form->textField($member, 'last_name', array('class' => 'shortTextField')) . ' + <b>x</b>';
                echo $form->error($member, 'last_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'client');
                echo $form->dropDownList($member,'client',array_combine($member->getClients(), $member->getClients()), array('empty'=>''));
                echo $form->error($member,'client');
            ?>
        </div>
    </div>

    <h3>Property</h3>
    <div class="clearfix formContainer">
        <div class="fluidField">
            <?php
                echo $form->labelEx($property, 'address_line_1');
                echo '<b>x</b> + ' . $form->textField($property, 'address_line_1', array('class' => 'shortTextField'));
                echo $form->error($property, 'address_line_1');
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'city');
                echo $form->textField($property,'city');
                echo $form->error($property,'city'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'county');
                echo $form->textField($property,'county');
                echo $form->error($property,'county'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'state');
                echo $form->textField($property,'state');
                echo $form->error($property,'state'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'zip');
                echo $form->textField($property,'zip');
                echo $form->error($property,'zip'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'policy');
                echo $form->textField($property,'policy');
                echo $form->error($property,'policy'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'geo_risk');
                echo $form->textField($property,'geo_risk');
                echo $form->error($property,'geo_risk'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'fs_assessments_allowed');
                echo $form->textField($property,'fs_assessments_allowed');
                echo $form->error($property,'fs_assessments_allowed'); 
            ?>
        </div>
    </div>
    <div class="clearfix">
        <div class="fluidField">
            <label>Number of members to generate:</label>
            <input name="generateCount" id="generateCount" type="text" maxlength="3" value='<?php echo $generatorCount; ?>' />
        </div>
    </div>
       
    <?php     
        if (count($generatedData) > 0)
        {
            echo '<h3>Generated Output</h3>';
            $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'generatedDataGrid',
                'dataProvider' => new CArrayDataProvider($generatedData, array(
                    'pagination' => false,
                )),
                'columns' => array(
                    array('name' => 'first_name', 'header' => 'First Name'),
                    array('name' => 'last_name', 'header' => 'Last Name'),
                    array('name' => 'carrier_key', 'header' => 'Registration Code'),
                ),
            ));
        }
        else 
        {
            echo '<div class="row buttons">' . CHtml::submitButton('Generate', array('id' => 'btnGenerate', 'class'=>'submit', 'style' => 'margin-left :30px')) . '</div>';
        }

        $this->endWidget();
    ?>
</div>