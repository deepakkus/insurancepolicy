<?php

class m170824_204503_adding_members_unique_constraint extends CDbMigration
{
public function safeUp()
{
        $this->execute('
            ALTER TABLE [members] ADD CONSTRAINT [UN_member_num_client_id] UNIQUE NONCLUSTERED
            (
            [member_num] ASC,
            [client_id] ASC
            )
        ');
}

public function safeDown()
{
        $this->execute('ALTER TABLE [members] DROP CONSTRAINT [UN_member_num_client_id]');
}
}