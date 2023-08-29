<?php

$this->breadcrumbs = array(
	'Client Analytics'
);

?>

<div class = "table-responsive">

    <table class = 'table'>
        <thead>
            <tr>
                <td bgcolor="#f5f5f5"></td>
                <td colspan ="3" bgcolor="#ece2f0">
                    <h3 class = "center">Services</h3>
                </td>
                <td colspan ="5" bgcolor="#f5f5f5">
                    <h3 class = "center">Dashboard Extensions</h3>
                </td>
                <td colspan ="4" bgcolor="#ece2f0">
                    <h3 class = "center" >Usage</h3>
                </td>
            </tr>
            <tr>
                <td bgcolor="#f5f5f5"><b>Client</b></td>
                <td bgcolor="#ece2f0"><b>WDS Fire</b></td>
                <td bgcolor="#ece2f0"><b>WDS Risk</b></td>
                <td bgcolor="#ece2f0"><b>WDS Pro</b></td>
                <td bgcolor="#f5f5f5"><b>Dash. Enrollments</b></td>
                <td bgcolor="#f5f5f5"><b>Call Interface</b></td>
                <td bgcolor="#f5f5f5"><b>WDS Calls</b></td>
                <td bgcolor="#f5f5f5"><b>Unmatched</b></td>
                <td bgcolor="#f5f5f5"><b>Dedicated</b></td>
                <td bgcolor="#ece2f0"><b>Dash. Users</b></td>
                <td bgcolor="#ece2f0"><b>PTD Notices</b></td>
                <td bgcolor="#ece2f0"><b>YTD Notices</b></td>
                <td bgcolor="#ece2f0"><b>WDS Fire States</b></td>
            </tr>
        </thead>
        <?php foreach($clients as $client): ?>
            <tr>
                <td bgcolor="#f5f5f5"><a href = "<?php echo $this->createUrl('/client/stats', array('id'=>$client['id'])); ?>"><?php echo $client['name']; ?></a></td>
                <td bgcolor="#ece2f0"><?php echo Helper::getCheckMark($client['wds_fire'], true); ?></td>
                <td bgcolor="#ece2f0"><?php echo Helper::getCheckMark($client['wds_risk'], true); ?></td>
                <td bgcolor="#ece2f0"><?php echo Helper::getCheckMark($client['wds_pro'], true); ?></td>
                <td><?php echo Helper::getCheckMark($client['enrollment'], true); ?></td>
                <td><?php echo Helper::getCheckMark($client['client_call_list'], true); ?></td>
                <td><?php echo Helper::getCheckMark($client['call_list'], true); ?></td>
                <td><?php echo Helper::getCheckMark($client['unmatched'], true); ?></td>
                <td><?php echo Helper::getCheckMark($client['dedicated'], true); ?></td>
                <td bgcolor="#ece2f0"><?php echo ($client['num_users']) ? $client['num_users'] : 0; ?></td>
                <td bgcolor="#ece2f0"><?php echo ($client['ptd_notices']) ? $client['ptd_notices'] : 0; ?></td>
                <td bgcolor="#ece2f0"><?php echo ($client['ytd_notices']) ? $client['ytd_notices'] : 0; ?></td>
                <td bgcolor="#ece2f0"><?php echo ($client['states']) ? $client['states'] : 0; ?></td>
            </tr>

        <?php endforeach; ?>

    </table>
</div>