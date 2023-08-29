<?php

class m170104_223518_adding_shift_ticket_user_id extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE [eng_shift_ticket] ADD [user_id] smallint NULL CONSTRAINT FK_eng_shift_ticket_user FOREIGN KEY ([user_id]) REFERENCES [user] ([id])');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE [eng_shift_ticket] DROP CONSTRAINT [FK_eng_shift_ticket_user], COLUMN [user_id]');
    }
}