<?php

class m170606_101116_add_record_res_fuel_type extends CDbMigration
{
    public function safeUp()
    {
        $this->insert('res_fuel_type', array(
            "Type" => "Pine Timber Litter"
        ));

        $this->insert('res_fuel_type', array(
            "Type" => "Freshwater Marsh"
        ));
    }

    public function safeDown()
    {
        $this->execute("delete from res_fuel_type where Type='Pine Timber Litter'");
        $this->execute("delete from res_fuel_type where Type='Freshwater Marsh'");

    }
}