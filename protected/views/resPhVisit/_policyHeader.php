<?php
    //For the last Published property status
     $visitStatus = ResPhVisit::model()->getPhVisitStatus($model->property_pid, $model->date_action, $model->id);
	//For the contact header
	$submittedByUser = User::model()->findByPk($model->user_id);
	$submittedBy = $submittedByUser ? $submittedByUser->name : null;
	//For the status history header
	$history = $model->getVisitHistory($model->id);
	$counter = 0;
?>

<div class="row-fluid" style="margin-top:20px;">
	<div class="span6">
		<h4>Policyholder</h4>
		<?php

        $this->widget('zii.widgets.CDetailView', array(
            'data' => ($model->isNewRecord) ? Property::model()->findByPk($pid) : $model->property,
            'htmlOptions' => array(
                'class' => 'table table-bordered',
                'style' => 'border-collapse: collapse;'
            ),
            'itemTemplate' => '<tr><td style="font-weight: 600;">{label}</th><td>{value}</td></tr>',
            'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
            'attributes' => array(
                array(
                    'label' => 'Policyholder Name',
                    'value' => function($data) { return isset($data->member) ? $data->member->first_name . ' ' . $data->member->last_name : ''; }
                ),
                array(
                    'name' => 'address_line_1',
                    'label' => 'Address'
                ),
                array(
                    'label' => 'City/State',
                    'value' => function($data) { return $data->city . ', ' . $data->state; }
                ),
                'policy',
                array(
                    'label' => "Submitted By",
                    'value' => $submittedBy,
                ),
                array(
                    'label' => "Previously Reported Status",
                    'value' => $visitStatus,
                )
            )
        )); ?>
	</div>
	<div class="span6">
		<h4>Change History</h4>
        <div style="max-height: 350px; overflow-y: auto; overflow-x: auto;">
		    <table class="table table-bordered">
			    <thead>
				    <tr>
					    <th>User Name</th>
					    <th>Review Status</th>
					    <th>Date</th>
                        <th>Data</th>
				    </tr>
			    </thead>
			    <tbody>
                    <?php
                    $foundCurrentlyPublishedEntry = false;
                    foreach($history as $historyDate => $historyEntry)
                    {
                        //data sub table
                        $dataView = '<table><tr><td>Status:</td><td>'.$historyEntry['status'].'</td></tr>';
                        $dataView .= '<tr><td>ActionDate:</td><td>'.$historyEntry['date_action'].'</td></tr>';
                        if(!empty($historyEntry['approval_user_id']))
                        {
                            $approvalUser = User::model()->findByPk($historyEntry['approval_user_id'])->name;
                            $dataView .= "<tr><td>Approval User:</td><td>$approvalUser</td></tr>";
                        }
                        if(!empty($historyEntry['comments']))
                        {
                            $dataView .= '<tr><td>Engine Comments:</td><td>'.$historyEntry['comments'].'</td></tr>';
                        }
                        if(!empty($historyEntry['publish_comments']))
                        {
                            $dataView .= '<tr><td>Dashboard Comments:</td><td>'.$historyEntry['publish_comments'].'</td></tr>';
                        }
                        if(!empty($historyEntry['phActions']))
                        {
                            $dataView .= '<tr><td>Actions:</td><td><ul>';
                            foreach($historyEntry['phActions'] as $action)
                            {
                                $actionType = ResPhActionType::model()->findByPk($action['action_type_id']);
                                $dataView .= '<li>'.$actionType->name;
                                if(!empty($action['qty']))
                                {
                                    $dataView .= ' ('.$action['qty'].' '.$actionType->units.')';
                                }
                                $dataView .= '</li>';
                            }
                            $dataView .= '</ul></td></tr>';
                        }
                        $dataView .= '</table>';

                        $updatedBy = User::model()->findByPk($historyEntry['user_id'])->name;
                        $current = '';
                        if($counter === 0)
                        {
                            $current = '*';
                        }
                        $currentlyPublished = '';
                        if($historyEntry['review_status'] === 'published' && $foundCurrentlyPublishedEntry === false)
                        {
                            $currentlyPublished = '^';
                            $foundCurrentlyPublishedEntry = true;
                        }

                        //main change history table row
                        echo '<tr>';
					    echo "<td> $updatedBy </td>";
					    echo '<td>'.$historyEntry['review_status'].$current.$currentlyPublished.'</td>';
					    echo '<td>'.date("Y-m-d H:i:s", $historyDate).'</td>';
                        echo '<td>'.$dataView.'</td>';
					    echo '</tr>';
                        $counter++;
                    }
                    ?>
			    </tbody>

		    </table>
        </div>
		<p>* Current</p>
        <p>^ Currently Published</p>

	

	</div>

</div>