<?php

class m170329_121210_add_field_billable_eng_shift_ticket_activity extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket_activity] ADD [billable] tinyint DEFAULT 0 WITH VALUES');
	}

	public function safeDown()
	{
        echo "m170329_121210_add_field_billable_eng_shift_ticket_activity does not support migration down.\n";
		return false;
	}
}