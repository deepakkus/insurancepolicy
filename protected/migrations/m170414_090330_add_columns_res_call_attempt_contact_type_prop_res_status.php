<?php

class m170414_090330_add_columns_res_call_attempt_contact_type_prop_res_status extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('res_call_attempt', 'contact_type', 'varchar(50)');
	    $this->addColumn('res_call_attempt', 'prop_res_status', 'varchar(25)');        
	}

	public function safeDown()
	{
        $this->dropColumn('res_call_attempt', 'contact_type');
	    $this->dropColumn('res_call_attempt', 'prop_res_status');        
	}
}