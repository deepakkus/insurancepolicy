<?php

class m170623_073812_add_equipment_check_column extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket] ADD [equipment_check] VARCHAR(500) NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket] DROP COLUMN [equipment_check]');
	}
}