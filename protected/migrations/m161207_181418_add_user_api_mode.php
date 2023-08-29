<?php

class m161207_181418_add_user_api_mode extends CDbMigration
{
    // Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->addColumn('user', 'api_mode', 'varchar(10)');
        // Update the user table and populate 'live' for all existing api users
        $this->execute("update [user] set api_mode = 'live' where client_secret is not null");
	}

	public function safeDown()
	{
        $this->dropColumn('user', 'api_mode');
	}
}