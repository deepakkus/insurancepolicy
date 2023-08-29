<?php

/* @var $this ResPhVisitController */
/* @var $model ViewPolicyActionForm */
/* @var $form CActiveForm */

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Policyholder Actions'
);

Assets::registerMapboxPackage();

$clientScript = Yii::app()->clientScript;
$baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')).'/gridview';

// Pre-register gridview files for asynch rendering
$clientScript->registerCssFile($baseScriptUrl . '/styles.css');
$clientScript->registerScriptFile($baseScriptUrl . '/jquery.yiigridview.js', CClientScript::POS_END);
$clientScript->registerCoreScript('bbq');
$clientScript->registerCssFile(CHtml::asset(Yii::getPathOfAlias('system.web.widgets.pagers.pager').'.css'));

$clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/map-fire-style.js', CClientScript::POS_HEAD);
$clientScript->registerScript('selectMenuScript', '

    var $fires = $("#' . CHtml::activeId($model, 'fireID') . '");

    $(document).on("change", "#' . CHtml::activeId($model, 'clientID') . '", function() {
        var clientID = $("#' . CHtml::activeId($model, 'clientID') . '").val();
        $.get("' . $this->createUrl('resPhVisit/getDispatchedFires') . '", { clientID: clientID }, function(data) {
            $fires.html(data);
        }, "html");
    });

    window.gridJsRegistered = false;

');

$clientScript->registerCss('view-policy-css','
    .not-set {
        color: #c55;
        font-style: italic;
    }
');

echo '<h2>Select a client and fire:</h2>';

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'view-policyholder-action-form',
    'enableAjaxValidation' => true,
    'action' => array('resPhVisit/viewPolicyActionData'),
    'htmlOptions' => array(
        'class' => 'well'
    ),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'hideErrorMessage' => true,
        'validationUrl' => $this->createUrl($this->route),
        'afterValidate' => new CJavaScriptExpression('function(form, data, hasError) {
            if (!hasError) {
                $.ajax({
                    "type": "POST",
                    "url": form.attr("action"),
                    "data": form.serialize(),
                    "success": function(data) {
                        $("#results-container").html(data);
                    }
                });
            }
            return false;
        }')
    )
));

echo $form->errorSummary($model);

echo $form->dropDownListRow($model, 'clientID', Helper::getFireClients(), array(
    'style' => 'margin-right: 10px',
    'prompt' => 'Select a client',
    'labelOptions' => array('label' => false)
));

echo $form->dropDownListRow($model, 'fireID', array(), array(
    'style' => 'margin-right: 10px',
    'prompt' => 'Select a fire',
    'labelOptions' => array('label' => false)
));

$this->widget('bootstrap.widgets.TbSelect2', array(
    'asDropDownList' => true,
    'model' => $model,
    'attribute' => 'policyholders',
    'data' => array(
        '2' => 'All policyholders',
        '0' => 'Not visited by an engine',
        '1' => 'Visted by an engine',
        '3' => 'Visited by an engine today'
    )
));

echo CHtml::submitButton('View Policyholders', array(
    'class' => 'submit marginTop20',
    'style' => 'display: block;'
));

$this->endWidget();
unset($form);

echo '<div id="results-container"></div>';
