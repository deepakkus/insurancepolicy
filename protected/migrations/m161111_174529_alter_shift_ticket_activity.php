<?php

class m161111_174529_alter_shift_ticket_activity extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->addColumn('eng_shift_ticket_activity', 'eng_scheduling_id', 'int');
        $this->addForeignKey('FK_eng_scheduling_id', 'eng_shift_ticket_activity', 'eng_scheduling_id', 'eng_scheduling', 'id');
        $this->addColumn('eng_shift_ticket_activity', 'comment', 'varchar(200)');
	}

	public function safeDown()
	{
        $this->dropForeignKey('FK_eng_scheduling_id', 'eng_shift_ticket_activity');
        $this->dropColumn('eng_shift_ticket_activity', 'eng_scheduling_id');
        $this->dropColumn('eng_shift_ticket_activity', 'comment');
	}
}