<?php

class m170418_221959_more_shift_ticket_schedule_work extends CDbMigration
{
	public function safeUp()
	{
        $this->dropForeignKey('FK_eng_shift_ticket_activity_scheduling', 'eng_shift_ticket_activity');
        $this->dropColumn('eng_shift_ticket_activity', 'eng_scheduling_id');
	}

	public function safeDown()
	{
	}
	
}