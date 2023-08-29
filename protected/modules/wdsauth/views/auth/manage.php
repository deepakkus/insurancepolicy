<?php

/* @var $viewAuthItemForm ViewAuthItemForm */

$this->breadcrumbs = array(
    'WDSauth Module' => array('auth/manage'),
    'Manage Auth Items'
);

$script = <<<JAVASCRIPT

    $(function() {

        var hash = window.location.hash;
        if (hash) {
            $('#auth-item-tabs a[href="' + hash + '"]').tab('show');
        }

        $('#auth-item-tabs a').click(function(e) {
            history.pushState(null, null, this.href);
        });

    });

JAVASCRIPT;

$css = <<<CSS

    .auth-item-view-loading {
        margin-left: 10px;
        height: 16px;
        width: 16px;
    }
    .auth-item-view-loading.active {
        background-image: url("images/loading-small.gif");
        background-repeat: no-repeat;
        background-size: 16px 16px;
        background-position: center;
        position: absolute;
    }

CSS;

// Force publishing yii grid css file (needed since grid is in a partial render)
$clientScript = Yii::app()->clientScript;
$clientScript->registerCssFile(Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('zii.widgets.assets')) . '/gridview/styles.css');
$clientScript->registerScript('auth-manage-manage-script', $script);
$clientScript->registerCss('auth-manage-manage-css', $css);

echo CHtml::tag('p', array('class' => 'lead'), 'Manage auth items');

$this->widget('bootstrap.widgets.TbTabs', array(
    'type' => 'tabs',
    'id' => 'auth-item-tabs',
    'tabs' => array(
        array(
            'id' => 'role',
            'label' => 'Roles',
            'content' => $this->renderPartial('_manage_roles', array('viewAuthItemForm' => $viewAuthItemForm), true),
            'active' => true
        ),
        array(
            'id' => 'task',
            'label' => 'Tasks',
            'content' => $this->renderPartial('_manage_tasks', array('viewAuthItemForm' => $viewAuthItemForm), true)
        ),
        array(
            'id' => 'operation',
            'label' => 'Operations',
            'content' => $this->renderPartial('_manage_operations', array('viewAuthItemForm' => $viewAuthItemForm), true)
        )
    )
));

?>