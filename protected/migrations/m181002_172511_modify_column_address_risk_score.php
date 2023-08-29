<?php

class m181002_172511_modify_column_address_risk_score extends CDbMigration
{
	public function safeUp()
	{
       $this->execute("alter table risk_score alter column [address] varchar(100)");
	}

	public function safeDown()
	{
        $this->execute("alter table risk_score alter column [address] varchar(50)");
	}
	
}