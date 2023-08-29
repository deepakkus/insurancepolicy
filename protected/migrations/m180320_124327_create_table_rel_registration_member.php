<?php

class m180320_124327_create_table_rel_registration_member extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE TABLE [rel_registration_member]
            (
                [id] [bigint] IDENTITY(1,1) NOT NULL PRIMARY KEY,
                [registration_code_id] [int] NOT NULL,
		        [member_id] [int] NOT NULL,
                [created_on] [datetime] NULL,
                [updated_on] [datetime] NULL,
                [is_active] [tinyint] NOT NULL DEFAULT 1,
                [is_deleted] [tinyint] NOT NULL DEFAULT 0
            )
       ");
	}

	public function safeDown()
	{
        $this->execute("
            DROP TABLE [rel_registration_member]
        ");
	}
	
}