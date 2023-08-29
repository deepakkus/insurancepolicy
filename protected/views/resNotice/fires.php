<?php

/* @var $fireData array */

/*

array (size=1)
  0 => 
    array (size=7)
      'fire_id' => string '13451' (length=5)
      'fire_name' => string 'Boulder Fire' (length=12)
      'state' => string 'CO' (length=2)
      'triggered_enrolled' => string '14' (length=2)
      'perimeter_id' => string '162' (length=3)
      'client_names' => string 'Chubb, Liberty Mutual, Mutual of Enumclaw, USAA' (length=48)
      'client_ids' => string '2, 3, 1004, 1' (length=14)

*/

$this->breadcrumbs = array(
    'Response'  =>  array('/resNotice/landing'),
    'Dispatched Fires'
);

?>

<?php if ($fireData): ?>

<h3 class="center">
    <?php echo count($fireData); ?> Dispatched Fires:
</h3>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Fire</th>
                <th>Location</th>
                <th>Clients</th>
                <th>Enrolled Policyholders</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            <?php

            foreach ($fireData as $data)
            {
                echo '<tr>';

                echo '<td>' . $data['fire_name'] . '</td>';
                echo '<td>' . $data['state'] . '</td>';
                echo '<td>' . implode('<br />', explode(',', $data['client_names'])) . '</td>';
                echo '<td>' . $data['triggered_enrolled'] . '</td>';

                if (!empty($data['perimeter_id']))
                {
                    echo '<td><a href="' . $this->createUrl('resPerimeters/downloadEngineFireKML', array('id' => $data['perimeter_id'], 'fid'=>$data['fire_id'],'cids' => json_encode(explode(',', $data['client_ids'])))) . '"><img src="images/kmz-small.png" /></a></td>';
                }
                else
                {
                    echo '<td>No threat uploaded!</td>';
                }

                echo '</tr>';
            }

            ?>
        </tbody>
    </table>
</div>


<?php else: ?>

<h3 class="center">
    No Dispatched Fires:
</h3>

<p class="lead center"><i>No dispatched fires</i></p>

<?php endif; ?>