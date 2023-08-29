<?php

class m170721_090347_modify_column_condition_num extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [fs_report_text] ALTER COLUMN [condition_num] int NULL');
	}

	public function safeDown()
	{
         $this->execute('ALTER TABLE [fs_report_text] ALTER COLUMN [condition_num] tinyint');
	}
}