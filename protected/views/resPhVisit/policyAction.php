<?php

/* @var $this ResPhVisitController */
/* @var $model ResPhVisit */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Notifications' => array('resNotice/admin'),
    'Policyholder Actions'=>array('resPhVisit/admin', 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName),
    'Edit Policyholder Actions'
);

?>

<h1>Edit Policyholder Actions</h1>
<p class="lead"><?php echo $fireName . ', ' . $clientName;  ?></p>

<?php

$dataProvider = $model->search_edit($pid, $fireID);

$columns = array(
    array(
        'class' => 'CButtonColumn',
        'template' => '{update}',
        'header' => 'Edit Actions',
        'buttons' => array(
            'update' => array(
                'url' => function($data) use ($fireID, $clientID, $fireName, $clientName) {
                    return $this->createUrl('resPhVisit/update', array('id' => $data->id, 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName));
                },
                'label' => 'Edit Actions'
            )
        )
    ),
    'property_pid',
    'memberFirstName',
    'memberLastName',
    array(
        'name' => 'date_created',
        'value' => function($data) { return date('Y-m-d H:i', strtotime($data->date_created)); }
    ),
    array(
        'name' => 'date_updated',
        'value' => function($data) { return date('Y-m-d H:i', strtotime($data->date_updated)); }
    ),
    array(
        'name' => 'date_action',
        'value' => function($data) { return date('Y-m-d H:i', strtotime($data->date_action)); }
    ),
    'approvalUserName',
    'userName'
);

$this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'res-ph-visit-edit-grid',
	'dataProvider' => $dataProvider,
	'columns' => $columns
));

?>
