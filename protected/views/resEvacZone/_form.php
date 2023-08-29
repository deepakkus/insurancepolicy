<?php

Yii::app()->clientScript->registerScriptFile('/js/resEvacZone/admin.js', CClientScript::POS_HEAD);
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
Assets::registerEvacControl();

?>

<div class="form" style="float:none; background-color:transparent; border:none;">

    <?php $form = $this->beginWidget('CActiveForm', array(
	    'id' => 'res-notice'
    )); ?>

    <div class="row-fluid marginTop20 marginBottom20">
        <div class="span4">
            <?php
            echo CHtml::activeLabel($notice, 'client_id');
            $responseClients = ResEvacZone::getResponseClients();
            echo CHtml::activeDropDownList($notice, 'client_id', $responseClients, array(
                'empty' => '( Select a client )',
                'data-url' => $this->createUrl('/resEvacZone/getClientFires'))
            );
            ?>
        </div>
        <div class="span4">
            <?php
            echo CHtml::activeLabel($notice, 'fire_id');
            $fires = array();
            if(isset($notice->client_id))
            {
                $fires = ResEvacZone::getClientFires($notice->client_id);
            }
            echo CHtml::activeDropDownList($notice, 'fire_id', $fires, array(
                'prompt' => 'Select a fire',
                'data-url' => $this->createUrl('/resEvacZone/getNotices'))
            );
            ?>
        </div>
        <div class="span4">
            <?php
            echo CHtml::activeLabel($notice, 'notice_id');
            $notices = array();
            if(isset($notice->client_id, $notice->fire_id))
            {
                $notices = ResEvacZone::getClientFireNotices($notice->client_id, $notice->fire_id);
            }
            echo CHtml::activeDropDownList($notice, 'notice_id', $notices, array(
                'prompt' => 'Select a notice',
                'data-url' => $this->createUrl('/resEvacZone/getPerimeterIds'))
            );
            ?>
        </div>
    </div>

    <?php $this->endWidget(); ?>

    <?php
    if(isset($notice->notice_id))
    {
        // Load map automatically on update
        Yii::app()->clientScript->registerScript(1, '
            $.get("' . $this->createUrl('/resEvacZone/getPerimeterIds', array('noticeID' => $notice->notice_id)) . '", function(data) {
                $.event.trigger({ type: "map:perimeter", perimeterID: data.perimeterID });
                $.event.trigger({ type: "map:buffer", perimeterID: data.perimeterID });
                $.event.trigger({ type: "map:threat", perimeterID: data.perimeterID, threatID: data.threatID });
                $.event.trigger({ type: "map:policyholders", perimeterID: data.perimeterID, clientID: data.clientID });
            }, "json").error(function(jqXHR) {
                console.log(jqXHR);
            });
        ');
    }
    ?>

  

</div>

<div class="row-fluid">
    <div id="map-wrapper">
        <?php 
        $this->renderPartial('_map', array('notice'=>$notice, 'mapEvacZones'=>(isset($mapEvacZones) ? $mapEvacZones : null))); 
        ?>
    </div>
</div>