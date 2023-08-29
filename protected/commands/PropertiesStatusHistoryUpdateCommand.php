<?php
/**
 * This script adds rows to the status_history table for all properties under the following conditions:
 * 
 * 1. If the response_status, pr_status or fs_status fields are set to 'enrolled', then
 *    a status_history entry of 'not enrolled' at the initial import date will be inserted.
 * 
 * 2. If the policy_status is set to 'cancelled', then a status_history entry of 'active'
 *    at the initial import date will be inserted.
 * 
 * To run this:
 * > yiic propertiesstatushistoryupdate
 */
//ONE TIME RUN TYPE OF SCRIPT. Commenting out to avoid future accidential runs, but leaving in repo for reference
/*
class PropertiesStatusHistoryUpdateCommand extends CConsoleCommand
{
    // This is the initial import date.
    private $_initialImportDate = '2013-05-31';
    private $_userID = 0;
    
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		print "\nStarting Properties/StatusHistory update command!\n";

        // Lookup the ebarnes user so that status_history records created by this script are marked appropriately (i.e., people know who to blame!).
        $this->_userID = User::model()->findByAttributes(array('username' => 'ebarnes'))->id;
                
        //$this->processPropertyWithStatus('response_status', 'enrolled', 'not enrolled');
        //$this->processPropertyWithStatus('pre_risk_status', 'enrolled', 'not enrolled');
        //$this->processPropertyWithStatus('fireshield_status', 'enrolled', 'not enrolled');
        $this->processPropertyWithStatus('policy_status', 'canceled', 'active');
        
		print "\nDone adding status history records!\n";
	}
       
    private function processPropertyWithStatus($statusField, $oldStatus, $newStatus)
    {                
        // Look up properties that are enrolled in given status field.
        $properties = Property::model()->findAllByAttributes(array($statusField => $oldStatus));
        
        print "\n" . count($properties) . " properties found with $oldStatus $statusField...\n\n";
        
        // Insert a status_history for each for them.
        foreach ($properties as $property)
        {
            $this->insertStatusHistory($statusField, $property->pid, $newStatus, $this->_initialImportDate);
        }
    }

    private function insertStatusHistory($tableField, $tableID, $status, $dateChanged)
    {
        $statusHistories = StatusHistory::model()->findAllByAttributes(array(
            'table_name' => 'properties',
            'table_field' => $tableField,
            'table_id' => $tableID,
        ));
        
        if (count($statusHistories) == 0)
        {        
            print "Adding status_history for ID = $tableID, status = $status, date = $dateChanged, userID = $this->_userID \n";

            $statusHistory = new StatusHistory();
            $statusHistory->table_name = 'properties';
            $statusHistory->table_field = $tableField; 
            $statusHistory->table_id = $tableID;
            $statusHistory->status = $status;
            $statusHistory->date_changed = date($dateChanged);
            $statusHistory->user_id = $this->_userID; 
            $statusHistory->save();
        }
        else
        {
            print "Skipping status_history for ID = $tableID, status = $status. Already found a status_history record.\n";
        }
    }
}
 */