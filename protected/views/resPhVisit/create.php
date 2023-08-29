<?php

/* @var $this ResPhVisitController */
/* @var $model ResPhVisit */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Notifications' => array('resNotice/admin'),
    'Policyholder Visits' => array('resPhVisit/admin', 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName),
    'Add Policyholder Visits'=>array('resPhVisit/policyAdd', 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName),
    'Create'
);

?>

<h1>Create Policyholder Visit</h1>
<p class="lead"><?php echo $fireName . ', ' . $clientName;  ?></p>

<?php 

$this->renderPartial('_form', array(
    'model' => $model,
    'pid' => $pid,
    'clientID' => $clientID,
    'fireID' => $fireID
));

?>