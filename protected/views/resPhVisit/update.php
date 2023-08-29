<?php

/* @var $this ResPhVisitController */
/* @var $model ResPhVisit */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Notifications' => array('resNotice/admin'),
    'Policyholder Visits' => array('resPhVisit/admin', 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName),
    'Update'
);

?>

<h1>Update Policyholder Visit</h1>
<p class="lead"><?php echo $fireName . ', ' . $clientName;  ?></p>

<?php

$this->renderPartial('_form', array(
    'model' => $model,
	'photos' => $photos,
    'clientID' => $clientID,
    'showStatus' => $showStatus
));

?>