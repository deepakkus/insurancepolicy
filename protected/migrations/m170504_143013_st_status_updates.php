<?php

class m170504_143013_st_status_updates extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        //need PK on new shift ticket status table for it to work properly
        $this->execute("ALTER TABLE [eng_shift_ticket_status] ADD CONSTRAINT PK_eng_shift_ticket_status PRIMARY KEY CLUSTERED ([id]);");

        //Update Status Types for Shift tickets to more closely match the new work flow
        $this->delete('eng_shift_ticket_status_type', "[type] = 'new'"); //don't need this one anymore cause we will just check if submitted is complete
        $this->update('eng_shift_ticket_status_type', array('type'=>'Submitted', 'order'=>10), "[type] = 'submitted'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'Duty Officer', 'order'=>20), "[type] = 'duty officer'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'Finance', 'order'=>30), "[type] = 'finance'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'Program', 'order'=>40), "[type] = 'program'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'Final', 'order'=>50), "[type] = 'complete'");

        ////though there should not be any STs in production, this will setup any STs in dev environments
        ////with the status entries they need to function properly
        $shiftTickets = EngShiftTicket::model()->findAll();
        foreach($shiftTickets as $shiftTicket)
        {
            $statusTypes = EngShiftTicketStatusType::model()->getAllActiveStatuses();
            foreach($statusTypes as $statusType)
            {
                //check if it doesn't already exist
                $existingStatus = EngShiftTicketStatus::model()->findByAttributes(array('shift_ticket_id'=>$shiftTicket->id, 'status_type_id'=>$statusType->id));
                if(!isset($existingStatus))
                {
                    $stStatus = new EngShiftTicketStatus;
                    $stStatus->shift_ticket_id = $shiftTicket->id;
                    $stStatus->status_type_id = $statusType->id;
                    $stStatus->completed = 0;
                    if($statusType->type == 'Submitted')
                    {
                        $stStatus->completed_by_user_id = $shiftTicket->submitted_by_user_id;
                    }
                    $stStatus->save();
                }
            }
        }
	}

	public function safeDown()
	{
        $this->insert('eng_shift_ticket_status_type', array('type' => 'new'));
        $this->update('eng_shift_ticket_status_type', array('type'=>'submitted', 'order'=>null), "[type] = 'Submitted'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'duty officer', 'order'=>null), "[type] = 'Duty Officer'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'finance', 'order'=>null), "[type] = 'Finance'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'program', 'order'=>null), "[type] = 'Program'");
        $this->update('eng_shift_ticket_status_type', array('type'=>'complete', 'order'=>null), "[type] = 'Final'");

        $this->truncateTable('eng_shift_ticket_status');
	}
}