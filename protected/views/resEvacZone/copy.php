<?php
/* @var $this ResEvacZoneController */
/* @var $notice ResNotice */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Evac Zones' => array('/resEvacZone/admin'),
    'Evac Zone Copy'
);

?>

<h1>Copy Evac Zones</h1>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <p class="lead"><u>Current Data</u></p>
                <?php $this->widget('zii.widgets.CDetailView', array(
                    'data' => $notice,
                    'htmlOptions' => array(
                        'class' => 'table table-striped table-hover table-condensed',
                        'style' => 'width: 70%;'
                    ),
                    'itemTemplate' => '<tr><th style="width:1%; white-space: nowrap;">{label}:</th><td>{value}</td></tr>',
                    'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
                    'attributes' => array(
                        'clientName',
                        'fireName',
                        array(
                            'label' => 'Notice',
                            'value' => $notice->recommended_action . " - " . date("Y-m-d H:i", strtotime($notice->date_created))
                        ),
                        array(
                            'label' => 'Number Zones',
                            'value' => count($notice->resEvacZones)
                        )  
                    )
                )); ?>
            </div>
            <div class="span6">

                <?php $form = $this->beginWidget('CActiveForm', array(
	                'id' => 'res-evac-zone-copy-form',
                )); ?>

                <div>
                    <?php 
                    echo CHtml::label('Which notice would you like to copy to?', CHtml::activeId($notice, 'notice_id'));

                    echo CHtml::activeDropDownList($notice, 'notice_id', ResEvacZone::getClientFireNotices($notice->client_id, $notice->fire_id, array($notice->notice_id)), array(
                        'prompt' => 'Select a notice',
                        'style' => 'width: 400px;',
                        'required' => 'required',
                    )); 
                    ?>
                </div>

	            <div class="marginTop20">
		            <?php echo CHtml::submitButton('Copy', array('class'=>'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('/resEvacZone/admin')); ?>
                    </span>
	            </div>

                <?php $this->endWidget(); ?>

            </div>
        </div>
    </div>

