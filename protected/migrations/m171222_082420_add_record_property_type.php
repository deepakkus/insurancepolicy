<?php

class m171222_082420_add_record_property_type extends CDbMigration
{
	//safe up
	public function safeUp()
	{
        $this->execute("INSERT INTO [properties_type] VALUES ('Second Look')");
	}
    //safe down
	public function safeDown()
	{
        $this->execute("DELETE FROM [properties_type] WHERE  [type] = 'Second Look'");
	}
	
}