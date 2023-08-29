<?php

class m161219_193332_history_model_table extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("IF OBJECT_ID('[model_history]', 'U') IS NOT NULL DROP TABLE [model_history]");

        $this->execute('
            CREATE TABLE [model_history]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [user_id] [smallint] NOT NULL,
                [date] [datetime] NOT NULL,
                [table] [varchar](50) NOT NULL,
                [table_pk] [int] NOT NULL,
                [data] [varchar](max) NOT NULL
                CONSTRAINT [PK_model_history] PRIMARY KEY CLUSTERED ([id] ASC)
            )
        ');
	}

	public function safeDown()
	{
        $this->execute("IF OBJECT_ID('[model_history]', 'U') IS NOT NULL DROP TABLE [model_history]");
	}
}