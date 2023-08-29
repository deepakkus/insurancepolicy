<?php

class m171225_120137_add_column_member_id_user extends CDbMigration
{
	//safe up
	public function safeUp()
	{
        $this->execute("
            ALTER TABLE [user] ADD [member_mid] [INT] CONSTRAINT DF_member_mid DEFAULT NULL
        ");
	}

    // safe down
    public function safeDown()
	{
        $this->execute("
            ALTER TABLE [user] DROP CONSTRAINT DF_member_mid
            ALTER TABLE [user] DROP COLUMN [member_mid]
        ");
	}
}