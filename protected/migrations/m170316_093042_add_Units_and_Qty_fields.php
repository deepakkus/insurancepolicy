<?php

class m170316_093042_add_Units_and_Qty_fields extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('res_ph_action', 'qty', 'float');
        $this->addColumn('res_ph_action_type', 'units', 'varchar(25)');        
	}

	public function safeDown()
	{
        $this->dropColumn('res_ph_action', 'qty');
        $this->dropColumn('res_ph_action_type', 'units');
	}
}