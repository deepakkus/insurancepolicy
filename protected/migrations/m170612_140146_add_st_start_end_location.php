<?php

class m170612_140146_add_st_start_end_location extends CDbMigration
{

	public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket] ADD [start_location] VARCHAR(50) NULL');
        $this->execute('ALTER TABLE [eng_shift_ticket] ADD [end_location] VARCHAR(50) NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket] DROP COLUMN [start_location]');
        $this->execute('ALTER TABLE [eng_shift_ticket] DROP COLUMN [end_location]');
	}

}