<?php

class m170113_190708_action_type extends CDbMigration
{
	public function safeUp()
	{
        //Create
        $this->addColumn('res_ph_action_type', 'action_type', 'varchar(25)');
        //Populate
        $this->update('res_ph_action_type', array('action_type'=>'Physical'));
        $this->update('res_ph_action_type', array('action_type'=>'Recon'), "name in ('Photos', 'Homeowner Visit', 'Property Triage', 'Left Brochure')");
	}

	public function safeDown()
	{
		$this->dropColumn('res_ph_action_type', 'action_type');
	}
}