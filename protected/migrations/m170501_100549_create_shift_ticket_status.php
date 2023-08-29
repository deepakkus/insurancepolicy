<?php

class m170501_100549_create_shift_ticket_status extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
           CREATE TABLE [eng_shift_ticket_status]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [shift_ticket_id] [int] NOT NULL,
                [status_type_id] [int] NOT NULL,
                [completed_by_user_id] [smallint] NULL,
                [completed] [tinyint] NULL CONSTRAINT DF_Completed_Zero DEFAULT 0
            )
          ");
        $this->execute("ALTER TABLE eng_shift_ticket_status ADD CONSTRAINT FK_eng_shift_ticket_sid FOREIGN KEY ([shift_ticket_id]) REFERENCES [eng_shift_ticket] ([id])");
        $this->execute("ALTER TABLE eng_shift_ticket_status ADD CONSTRAINT FK_eng_shift_ticket_sti FOREIGN KEY ([status_type_id]) REFERENCES [eng_shift_ticket_status_type] ([id])");
        $this->execute("ALTER TABLE eng_shift_ticket_status ADD CONSTRAINT FK_eng_shift_ticket_completed_by_user_id FOREIGN KEY ([completed_by_user_id]) REFERENCES [user] ([id])");
        $this->execute('ALTER TABLE [eng_shift_ticket_status_type] ADD [order] [int] NULL');
        $this->execute('ALTER TABLE [eng_shift_ticket_status_type] ADD [disabled] [tinyint] NULL');
        $this->execute("IF OBJECT_ID('FK_eng_shift_ticket_status', 'F') IS NOT NULL ALTER TABLE dbo.eng_shift_ticket DROP CONSTRAINT FK_eng_shift_ticket_status");
        $this->execute('ALTER TABLE [eng_shift_ticket] DROP COLUMN [eng_shift_ticket_status_type_id]');
	}

	public function safeDown()
	{
        $this->execute("IF OBJECT_ID('FK_eng_shift_ticket_sid', 'F') IS NOT NULL ALTER TABLE dbo.eng_shift_ticket_status DROP CONSTRAINT FK_eng_shift_ticket_sid");
        $this->execute("IF OBJECT_ID('FK_eng_shift_ticket_sti', 'F') IS NOT NULL ALTER TABLE dbo.eng_shift_ticket_status DROP CONSTRAINT FK_eng_shift_ticket_sti");
        $this->execute("IF OBJECT_ID('FK_eng_shift_ticket_completed_by_user_id', 'F') IS NOT NULL ALTER TABLE dbo.eng_shift_ticket_status DROP CONSTRAINT FK_eng_shift_ticket_completed_by_user_id");
        $this->execute("DROP TABLE [eng_shift_ticket_status]");
        $this->execute('ALTER TABLE [eng_shift_ticket_status_type] DROP COLUMN [order]');
        $this->execute('ALTER TABLE [eng_shift_ticket_status_type] DROP COLUMN [disabled]');
        $this->execute('ALTER TABLE [eng_shift_ticket] ADD [eng_shift_ticket_status_type_id] [int] NULL');
        $this->execute("ALTER TABLE eng_shift_ticket ADD CONSTRAINT FK_eng_shift_ticket_status FOREIGN KEY ([eng_shift_ticket_status_type_id]) REFERENCES [eng_shift_ticket_status_type] ([id])");

	}
}