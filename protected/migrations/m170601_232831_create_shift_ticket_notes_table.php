<?php

class m170601_232831_create_shift_ticket_notes_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('
            CREATE TABLE [eng_shift_ticket_notes]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [eng_shift_ticket_id] [int] NOT NULL,
                [user_id] [smallint] NOT NULL,
                [notes] [varchar](300) NOT NULL,
                [date_created] [datetime2] NOT NULL,
                [date_updated] [datetime2] NOT NULL,
                CONSTRAINT [PK_eng_shift_ticket_notes] PRIMARY KEY CLUSTERED ([id] ASC),
                CONSTRAINT [FK_eng_shift_ticket_notes_stid] FOREIGN KEY ([eng_shift_ticket_id]) REFERENCES [eng_shift_ticket] ([id]),
                CONSTRAINT [FK_eng_shift_ticket_notes_uid] FOREIGN KEY ([user_id]) REFERENCES [user] ([id])
            )
        ');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE [eng_shift_ticket_notes] DROP CONSTRAINT FK_eng_shift_ticket_notes_stid');
        $this->execute('ALTER TABLE [eng_shift_ticket_notes] DROP CONSTRAINT FK_eng_shift_ticket_notes_uid');
        $this->execute('DROP TABLE [eng_shift_ticket_notes]');
    }
}