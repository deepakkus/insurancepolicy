<?php

class m161110_155711_alter_shift_ticket_activity_time extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->alterColumn('eng_shift_ticket_activity', 'start_time', 'time');
        $this->alterColumn('eng_shift_ticket_activity', 'end_time', 'time');
	}

	public function safeDown()
	{
        $this->alterColumn('eng_shift_ticket_activity', 'start_time', 'datetime');
        $this->alterColumn('eng_shift_ticket_activity', 'end_time', 'datetime');
	}
}