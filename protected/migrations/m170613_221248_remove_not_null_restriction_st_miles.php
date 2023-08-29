<?php

class m170613_221248_remove_not_null_restriction_st_miles extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket] ALTER COLUMN [start_miles] int NULL');
        $this->execute('ALTER TABLE [eng_shift_ticket] ALTER COLUMN [end_miles] int NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket] ALTER COLUMN [start_miles] VARCHAR(15) NOT NULL');
        $this->execute('ALTER TABLE [eng_shift_ticket] ALTER COLUMN [end_miles] VARCHAR(15) NOT NULL');
	}
}