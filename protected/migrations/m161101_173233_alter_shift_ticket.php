<?php

class m161101_173233_alter_shift_ticket extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->addColumn('eng_shift_ticket_draft', 'safety_meeting', 'tinyint');
	}

	public function safeDown()
	{
        $this->dropColumn('eng_shift_ticket_draft', 'safety_meeting');
	}
}