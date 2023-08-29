<?php

class m180129_112139_modify_column_triggered_enrolled extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [triggered_enrolled] bigint NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [res_notice] ALTER COLUMN [triggered_enrolled] int NULL');
    }
}