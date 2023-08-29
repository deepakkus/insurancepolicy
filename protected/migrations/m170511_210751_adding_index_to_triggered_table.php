<?php

class m170511_210751_adding_index_to_triggered_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('CREATE NONCLUSTERED INDEX IX_res_triggered_property_pid_id ON [res_triggered] ([property_pid]) INCLUDE ([id])');
    }

    public function safeDown()
    {
        $this->execute('DROP INDEX IX_res_triggered_property_pid_id ON [res_triggered]');
    }
}