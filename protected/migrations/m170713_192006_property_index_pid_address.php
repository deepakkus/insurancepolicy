<?php

class m170713_192006_property_index_pid_address extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('CREATE NONCLUSTERED INDEX IX_properties_pid_address ON [properties] ([type_id]) INCLUDE ([pid],[address_line_1])');
    }

    public function safeDown()
    {
        $this->execute('DROP INDEX IX_properties_pid_address ON [properties]');
    }
}