<?php

class m170425_142427_drop_res_daily_map_table extends CDbMigration
{
	public function up()
	{
        $this->execute('DELETE FROM [file] WHERE id IN (SELECT smoke_file_id FROM res_daily_map)');
        $this->execute('DELETE FROM [file] WHERE id IN (SELECT weather_file_id FROM res_daily_map)');
        $this->execute('DELETE FROM [file] WHERE id IN (SELECT danger_file_id FROM res_daily_map)');
        $this->execute('DROP TABLE res_daily_map');
	}

	public function down()
	{
		print "m170425_142427_drop_res_daily_map_table does not support migration down.\n";
		return false;
	}
}