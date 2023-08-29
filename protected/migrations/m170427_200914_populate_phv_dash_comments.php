<?php

class m170427_200914_populate_phv_dash_comments extends CDbMigration
{
	public function up()
	{
        $this->execute("UPDATE res_ph_visit SET publish_comments = comments WHERE date_created < '2017-04-27'");
	}

	public function down()
	{
		echo "m170427_200914_populate_phv_dash_comments does not support migration down.\n";
		return false;
	}

}