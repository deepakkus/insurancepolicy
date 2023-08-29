<?php

class m170627_135233_length_update_hours_used extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [res_dedicated] ALTER COLUMN [hours_used] VARCHAR(20) NULL');
	}

	public function safeDown()
	{
         $this->execute('ALTER TABLE [res_dedicated] ALTER COLUMN [hours_used] VARCHAR(8)');
	}	
}