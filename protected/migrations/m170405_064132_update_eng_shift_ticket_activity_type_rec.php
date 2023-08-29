<?php

class m170405_064132_update_eng_shift_ticket_activity_type_rec extends CDbMigration
{
	public function safeUp()
	{        
        $this->update('eng_shift_ticket_activity_type', array('type'=>'Break'), "type = 'Other'");
	}

	public function safeDown()
	{
        $this->update('eng_shift_ticket_activity_type', array('type'=>'Other'), "type = 'Break'");
	}
}