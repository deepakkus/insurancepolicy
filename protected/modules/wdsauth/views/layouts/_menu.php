<?php

$this->beginWidget('zii.widgets.CPortlet', array(
	'title' => 'WDSauth Module'
));

$this->widget('zii.widgets.CMenu', array(
    'activeCssClass' => 'auth-link-active',
	'htmlOptions' => array(
        'class' => 'operations'
    ),
	'items' => array(
		array(
			'label' => 'Manage',
			'url' => array('auth/manage'),
            'active' => $this->route === 'wdsauth/auth/manage',
		),
		array(
			'label' => 'Assign',
			'url' => array('assignment/index'),
            'active' => $this->route === 'wdsauth/assignment/index'
		)
	)
));

$this->endWidget('zii.widgets.CPortlet');
