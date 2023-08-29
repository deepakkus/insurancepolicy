<?php

class m170329_064706_insert_rec_eng_shift_ticket_activity_type extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->insert('eng_shift_ticket_activity_type', array(
        "type" => "Other"
        ));
	}

	public function safeDown()
	{
        echo "m170329_064229_insert_rec_eng_shift_ticket_activity_type does not support migration down.\n";
		return false;
	}
}