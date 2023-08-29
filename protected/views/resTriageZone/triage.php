<?php

Yii::app()->clientScript->registerScriptFile('/js/resTriageZone/admin.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('/js/map-fire-style.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCss(1, '
    form select {
        -webkit-transition: background-color .3s ease;
           -moz-transition: background-color .3s ease;
                transition: background-color .3s ease;
    }
');

Assets::registerMapboxPackage();
Assets::registerMapboxLeafletOmnivore();
Assets::registerTurfJs();
Assets::registerTriageControl();

?>

<div class="form" style="float:none; background-color:transparent; border:none;">

    <?php $form = $this->beginWidget('CActiveForm', array(
	    'id' => 'res-triage-zone'
    )); ?>

    <?php echo $form->errorSummary($triageZone); ?>

    <?php if ($triageZone->isNewRecord): ?>

    <div class="row-fluid marginTop20 marginBottom20">
        <div class="span4">
            <?php echo CHtml::activeLabel($triageZone, 'clientID'); ?>
            <?php echo CHtml::activeDropDownList($triageZone, 'clientID', ResTriageZone::getResponseClients(), array(
                'empty' => '( Select a client )',
                'data-url' => $this->createUrl('/resTriageZone/getDispatchedFires'))
            ); ?>
        </div>
        <div class="span4">
            <?php echo CHtml::activeLabel($triageZone, 'fireID'); ?>
            <?php echo CHtml::activeDropDownList($triageZone, 'fireID', array(), array('data-url' => $this->createUrl('/resTriageZone/getNotices'))); ?>
        </div>
        <div class="span4">
            <?php echo CHtml::activeLabel($triageZone, 'notice_id'); ?>
            <?php echo CHtml::activeDropDownList($triageZone, 'notice_id', array(), array('data-url' => $this->createUrl('/resTriageZone/getPerimeterIds'))); ?>
        </div>
    </div>

    <?php else: ?>

    <div class="row-fluid marginTop20 marginBottom20">
        <div class="span4">
            <?php echo CHtml::activeLabel($triageZone, 'clientID'); ?>
            <?php echo CHtml::activeDropDownList($triageZone, 'clientID', ResTriageZone::getResponseClients(), array(
                'empty' => '( Select a client )',
                'data-url' => $this->createUrl('/resTriageZone/getDispatchedFires'))
            ); ?>
        </div>
        <div class="span4">
            <?php echo CHtml::activeLabel($triageZone, 'fireID'); ?>
            <?php echo CHtml::activeDropDownList($triageZone, 'fireID', ResTriageZone::getDispatchedFires($triageZone->clientID), array(
                'prompt' => 'Select a fire',
                'data-url' => $this->createUrl('/resTriageZone/getNotices')
            )); ?>
        </div>
        <div class="span4">
            <?php echo CHtml::activeLabel($triageZone, 'notice_id'); ?>
            <?php echo CHtml::activeDropDownList($triageZone, 'notice_id', ResTriageZone::getDispatchedNotices($triageZone->clientID, $triageZone->fireID), array(
                'prompt' => 'Select a notice',
                'data-url' => $this->createUrl('/resTriageZone/getPerimeterIds')
            )); ?>
        </div>
    </div>

    <?php

    // Load map automatically on update
    Yii::app()->clientScript->registerScript(1, '
        $.get("' . $this->createUrl('/resTriageZone/getPerimeterIds', array('noticeID' => $triageZone->notice_id)) . '", function(data) {
            $.event.trigger({ type: "map:perimeter", perimeterID: data.perimeterID });
            $.event.trigger({ type: "map:buffer", perimeterID: data.perimeterID });
            $.event.trigger({ type: "map:threat", perimeterID: data.perimeterID, threatID: data.threatID });
            $.event.trigger({ type: "map:policyholders", perimeterID: data.perimeterID, clientID: data.clientID });
        }, "json").error(function(jqXHR) {
            console.log(jqXHR);
        });
    ');

    ?>

    <?php endif; ?>

    <?php $this->endWidget(); ?>

</div>

<?php

$mapArray = array('triageZone' => $triageZone);

if (!$triageZone->isNewRecord)
{
    $mapArray['resTriageZoneAreas'] = $resTriageZoneAreas;
}

?>

<div class="row-fluid">
    <div id="map-wrapper">
        <?php $this->renderPartial('_map', $mapArray); ?>
    </div>
</div>