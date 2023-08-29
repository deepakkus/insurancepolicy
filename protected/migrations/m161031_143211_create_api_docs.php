<?php

class m161031_143211_create_api_docs extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('
            CREATE TABLE [api_documentation]
            (
	            [id] [int] IDENTITY(1,1) NOT NULL PRIMARY KEY,
	            [name] [varchar](100) NOT NULL,
	            [docs] [varchar] (max) NULL,
	            [active] [tinyint] NOT NULL DEFAULT 1
            )
        ');
	}

	public function safeDown()
	{
        $this->execute("IF OBJECT_ID('api_documentation', 'U') IS NOT NULL DROP TABLE dbo.api_documentation");
	}
}