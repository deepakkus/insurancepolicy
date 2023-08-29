<?php

class m161027_160412_update_client_policy_id_length extends CDbMigration
{
	public function safeUp()
	{
        $this->alterColumn('properties', 'client_policy_id', 'varchar(15) NULL');
	}

	public function safeDown()
	{
        $this->alterColumn('properties', 'client_policy_id', 'varchar(25) NULL');
	}
}