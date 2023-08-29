<?php
/* @var $this ResFireObsController */
/* @var $model ResFireObs */
/* @var $form CActiveForm */
?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/resFireObs/form.js',CClientScript::POS_HEAD); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/resFireObs/form.css'); ?>
<?php Yii::app()->bootstrap->init(); ?>

<script type="text/html" id="weather-error">
    <div>
        <h2>Current Observations</h2>
        <div class ="row">
            <p>An <span style='color:red;'>error was encountered</span> while obtaining NOAA weather data. Please:</p>
            <ul>
                <li>Make sure the <a href = "<?php echo $this->createUrl('/resFireName/update', array('id'=>$fire->Fire_ID)); ?>">coordinates for this fire</a> are valid. NOAA uses the coordinates to get the forecast.</li>
                <li>Check the <a href = 'http://noaa.gov' target ="_blank">NOAA page manually</a> (the point click forecast may be temporarily down for the specific region).</li>
                <li>Use another weather source such as <a href = "http://www.wunderground.com/" target = "_blank">Wunderground.</a></li>
            </ul>
        </div>
    </div>
</script>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id' => 'res-fire-obs-form'
)); ?>

    <p class="note">Fields with <span class="required">*</span> are required.</p>

    <?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <div class="form-section">
                    <h2>Fire size, containment and behavior</h2>
        
                    <div>
                        <?php echo $form->labelEx($model,'Size'); ?>
                        <?php echo $form->textField($model,'Size',array('size'=>15,'maxlength'=>15)); ?> (Leave blank if unknown)
                        <?php echo $form->error($model,'Size'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Containment'); ?>
                        <?php echo $form->textField($model,'Containment'); ?> (Leave blank if unknown)
                        <?php echo $form->error($model,'Containment'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Behavior'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'Behavior',$model->getBehaviorRatings(),array('separator' => " &nbsp; ")); ?>
                            <?php echo $form->error($model,'Behavior'); ?>
                        </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Growth_Potential'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'Growth_Potential',$model->getGrowthPotentialRatings(),array('separator' => " &nbsp; ")); ?>
                            <?php echo $form->error($model,'Growth_Potential'); ?>
                        </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Supression'); ?>
                        <?php echo $form->textArea($model,'Supression',array('maxlength'=>500, 'rows' => 6, 'cols' => 50, 'style'=>'width:auto;')); ?>
                        <?php echo $form->error($model,'Supression'); ?>
                    </div>
                </div>
                <div class="form-section">
                    <h2>Current Weather</h2>
                    <div>
                        <?php echo $form->labelEx($model,'Temp'); ?>
                        <?php echo $form->textField($model,'Temp'); ?>
                        <?php echo $form->error($model,'Temp'); ?>
                    </div>

                    <div>
                        <div class="clearfix">
                            <?php echo $form->labelEx($model,'Wind_Speed'); ?>
                            <?php echo $form->textField($model,'Wind_Speed',array('size'=>6,'maxlength'=>6, 'style'=>'width: 75px;')); ?>
                            <?php echo $form->error($model,'Wind_Speed'); ?>
                        </div>

                        <div class="clearfix">
                            <?php echo $form->labelEx($model,'Gusting'); ?>
                            <?php echo $form->textField($model,'Gust',array('size'=>15,'maxlength'=>25, 'style'=>'width: 75px;')); ?>
                            <?php echo $form->error($model,'Gust'); ?>
                        </div>
        
                        <div class="clearfix">
                            <?php echo $form->labelEx($model,'Wind_Dir'); ?>
                            <?php echo $form->dropDownList($model,'Wind_Dir',$model->getWindDirections(),array('style'=>'width: 75px;')); ?>
                            <?php echo $form->error($model,'Wind_Dir'); ?>
                        </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Precip'); ?>
                        <?php echo $form->textField($model,'Precip',array('size'=>35,'maxlength'=>35)); ?>
                        <?php echo $form->error($model,'Precip'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Humidity'); ?>
                        <?php echo $form->textField($model,'Humidity'); ?>
                        <?php echo $form->error($model,'Humidity'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Red_Flags'); ?>
                        <?php echo $form->checkBox($model,'Red_Flags'); ?>
                        <?php echo $form->error($model,'Red_Flags'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Rating'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'Rating',$model->getWeatherRatings(),array('separator' => " &nbsp; ")); ?>
                            <?php echo $form->error($model,'Rating'); ?>
                        </div>
                    </div>

                </div>
                <div class="form-section">
                    <h2>Forecast Weather</h2>
                    <div>
                        <?php echo $form->labelEx($model,'Fx_Time'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'Fx_Time',$model->getForecastPeriod(),array('separator' => " &nbsp; ")); ?>
                            <?php echo $form->error($model,'Fx_Time'); ?>
                        </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Temperature'); ?>
                        <?php echo $form->textField($model,'Fx_Temp'); ?>
                        <?php echo $form->error($model,'Fx_Temp'); ?>
                    </div>

                    <div>
                        <div class="clearfix">
                            <?php echo $form->labelEx($model,'Wind_Speed'); ?>
                            <?php echo $form->textField($model,'Fx_Wind_Speed',array('size'=>6,'maxlength'=>6, 'style'=>'width: 75px;')); ?>
                            <?php echo $form->error($model,'Fx_Wind_Speed'); ?>
                        </div>

                        <div class="clearfix">
                            <?php echo $form->labelEx($model,'Gusting'); ?>
                            <?php echo $form->textField($model,'Fx_Gust',array('size'=>25,'maxlength'=>25, 'style'=>'width: 75px;')); ?>
                            <?php echo $form->error($model,'Fx_Gust'); ?>
                        </div>
        
                        <div class="clearfix">
                            <?php echo $form->labelEx($model,'Wind_Dir'); ?>
                            <?php echo $form->dropDownList($model,'Fx_Wind_Dir',$model->getWindDirections(), array('style'=>'width: 75px;')); ?>
                            <?php echo $form->error($model,'Fx_Wind_Dir'); ?>
                        </div>
                    </div>

                    <div>
                        <div id="container">
                            <table class="compass">
                                <tr>
                                    <td><input type="radio" name="direction" value="NW" id="NW" data-angle="315">NW</td>
                                    <td><input type="radio" name="direction" value="NNW" id="NNW" data-angle="337">NNW</td>
                                    <td><input type="radio" name="direction" value="N" id="N" data-angle="0"><span class="bold">N</span></td>
                                    <td><input type="radio" name="direction" value="NNE" id="NNE" data-angle="22">NNE</td>
                                    <td><input type="radio" name="direction" value="NE" id="NE" data-angle="45">NE</td>
                                </tr>
                                <tr>
                                    <td><input type="radio" name="direction" value="WNW" id="WNW" data-angle="292">WNW</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="radio" name="direction" value="ENE" id="ENE" data-angle="67">ENE</td>
                                </tr>
                                <tr>
                                    <td><input type="radio" name="direction" value="W" id="W" data-angle="270"><span class="bold">W</span></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="radio" name="direction" value="E" id="E" data-angle="90"><span class="bold">E</span></td>
                                </tr>
                                <tr>
                                    <td><input type="radio" name="direction" value="WSW" id="WSW" data-angle="247">WSW </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="radio" name="direction" value="ESE" id="ESE" data-angle="112">ESE </td>
                                </tr>
                                <tr>
                                    <td><input type="radio" name="direction" value="SW" id="SW" data-angle="225">SW </td>
                                    <td><input type="radio" name="direction" value="SSW" id="SSW" data-angle="202">SSW</td>
                                    <td><input type="radio" name="direction" value="S" id="S" data-angle="180"><span class="bold">S</span></td>
                                    <td><input type="radio" name="direction" value="SSE" id="SSE" data-angle="157">SSE</td>
                                    <td><input type="radio" name="direction" value="SE" id="SE" data-angle="135">SE </td>
                                </tr>
                            </table>
                            <?php echo CHtml::image('images/needle.png','wind-arrow',array('class'=>'needle')); ?>
                        </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Precipatation'); ?>
                        <?php echo $form->textField($model,'Fx_Precip',array('size'=>35,'maxlength'=>35)); ?>
                        <?php echo $form->error($model,'Fx_Precip'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Humidity'); ?>
                        <?php echo $form->textField($model,'Fx_Humidity'); ?>
                        <?php echo $form->error($model,'Fx_Humidity'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Fx_Rating'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'Fx_Rating',$model->getWeatherRatings(),array('separator' => " &nbsp; ")); ?>
                            <?php echo $form->error($model,'Fx_Rating'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="span6">
                <div class="form-section">
                    <?php 
                    echo '<div id="weather-details-anchor"></div>';
                    echo '<div id="weather-details">';
                    $this->widget(
                        'bootstrap.widgets.TbTabs',
                        array(
                            'type' => 'tabs',
                            'tabs' => array(
                                array(
                                    'label' => 'Forecast',
                                    'content' => '',
                                    'active' => true
                                ),
                                array(
                                    'label' => 'Hazard Outlooks',
                                    'content' => ''
                                ),
                                array(
                                    'label' => 'Current Observations',
                                    'content' => ''
                                )
                            ),
                        )
                    );
                    $this->widget('bootstrap.widgets.TbProgress', array(
                        'percent' => 100,
                        'striped' => true,
                        'animated' => true,
                        'htmlOptions' => array('style'=>'width: 75%; margin: 0 auto;','class'=>'weather-progress')
                    ));
                    echo '</div>';
                    ?>
                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">

                <?php if($model->isNewRecord) echo $form->hiddenField($model,'Fire_ID',array('value'=>Yii::app()->getRequest()->getQuery('fireid'))); ?>

	            <div id="button-row" class="buttons">
		            <?php echo CHtml::submitButton(($model->isNewRecord ? 'Create' : 'Save'), array('class'=>'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('resFireObs/admin')); ?>
                    </span>
	            </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<script type="text/javascript">
    // Add progress bar when submitted
    $('#res-fire-obs-form').submit(function () {
        var submitButton = this.querySelector('input[type="submit"]');
        $('#button-row').empty();
        $('#button-row').html(
            '<div style="width: 300px; margin: 5px 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">' +
                '<div class="bar" style="width: 100%;margin:0;"></div>' +
            '</div>');
    });
</script>