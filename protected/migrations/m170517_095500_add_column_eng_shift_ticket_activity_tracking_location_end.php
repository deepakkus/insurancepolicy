<?php

class m170517_095500_add_column_eng_shift_ticket_activity_tracking_location_end extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket_activity] ADD [tracking_location_end] VARCHAR(50) NULL');
	}
	public function safeDown()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket_activity] DROP COLUMN [tracking_location_end]');
	}
}