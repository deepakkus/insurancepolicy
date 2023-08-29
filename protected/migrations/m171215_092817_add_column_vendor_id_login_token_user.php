<?php

class m171215_092817_add_column_vendor_id_login_token_user extends CDbMigration
{
	//safe up
	public function safeUp()
	{
        $this->execute("
            ALTER TABLE [user] ADD [vendor_id] VARCHAR(50) CONSTRAINT DF_vendor_id DEFAULT NULL
            ALTER TABLE [user] ADD [login_token] VARCHAR(50) CONSTRAINT DF_login_token DEFAULT NULL
        ");
	}

    // safe down
    public function safeDown()
	{
        $this->execute("
            ALTER TABLE [user] DROP CONSTRAINT DF_vendor_id
            ALTER TABLE [user] DROP CONSTRAINT DF_login_token
            ALTER TABLE [user] DROP COLUMN [vendor_id]
            ALTER TABLE [user] DROP COLUMN [login_token]
        ");
	}
}