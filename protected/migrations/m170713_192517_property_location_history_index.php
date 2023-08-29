<?php

class m170713_192517_property_location_history_index extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('CREATE NONCLUSTERED INDEX IX_properties_location_history_pid ON [properties_location_history] ([property_pid])');
    }

    public function safeDown()
    {
        $this->execute('DROP INDEX IX_properties_location_history_pid ON [properties_location_history]');
    }
}