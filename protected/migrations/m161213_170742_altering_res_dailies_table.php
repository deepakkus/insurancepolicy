<?php

class m161213_170742_altering_res_dailies_table extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("EXEC sp_rename 'res_daily_threat.north_california', 'california_north', 'COLUMN';");
        $this->execute("EXEC sp_rename 'res_daily_threat.south_california', 'california_south', 'COLUMN';");
        $this->execute("EXEC sp_rename 'res_daily_threat.pnw', 'northwest', 'COLUMN';");
        $this->execute("EXEC sp_rename 'res_daily_threat.southeast', 'southern', 'COLUMN';");

        $this->execute("EXEC sp_rename 'res_daily_threat.fx_north_california', 'fx_california_north', 'COLUMN';");
        $this->execute("EXEC sp_rename 'res_daily_threat.fx_south_california', 'fx_california_south', 'COLUMN';");
        $this->execute("EXEC sp_rename 'res_daily_threat.fx_pnw', 'fx_northwest', 'COLUMN';");
        $this->execute("EXEC sp_rename 'res_daily_threat.fx_southeast', 'fx_southern', 'COLUMN';");

        $this->execute('
            ALTER TABLE res_daily_threat ADD
	            great_basin VARCHAR(10) NULL,
	            fx_great_basin VARCHAR(10) NULL,
	            alaska VARCHAR(10) NULL,
	            fx_alaska VARCHAR(10) NULL,
	            eastern VARCHAR(10) NULL,
	            fx_eastern VARCHAR(10) NULL
        ');

        $this->execute("UPDATE res_daily_threat SET great_basin = '', fx_great_basin = '', alaska = '', fx_alaska = '', eastern = '', fx_eastern = ''");

        $this->execute('ALTER TABLE res_daily_threat ALTER COLUMN great_basin VARCHAR(10) NOT NULL');
        $this->execute('ALTER TABLE res_daily_threat ALTER COLUMN fx_great_basin VARCHAR(10) NOT NULL');
        $this->execute('ALTER TABLE res_daily_threat ALTER COLUMN alaska VARCHAR(10) NOT NULL');
        $this->execute('ALTER TABLE res_daily_threat ALTER COLUMN fx_alaska VARCHAR(10) NOT NULL');
        $this->execute('ALTER TABLE res_daily_threat ALTER COLUMN eastern VARCHAR(10) NOT NULL');
        $this->execute('ALTER TABLE res_daily_threat ALTER COLUMN fx_eastern VARCHAR(10) NOT NULL');
	}

	public function safeDown()
	{
        $this->execute("EXEC sp_rename 'res_daily_threat.california_north', 'north_california', 'COLUMN'");
        $this->execute("EXEC sp_rename 'res_daily_threat.california_south', 'south_california', 'COLUMN'");
        $this->execute("EXEC sp_rename 'res_daily_threat.northwest', 'pnw', 'COLUMN'");
        $this->execute("EXEC sp_rename 'res_daily_threat.southern', 'southeast', 'COLUMN'");

        $this->execute("EXEC sp_rename 'res_daily_threat.fx_california_north', 'fx_north_california', 'COLUMN'");
        $this->execute("EXEC sp_rename 'res_daily_threat.fx_california_south', 'fx_south_california', 'COLUMN'");
        $this->execute("EXEC sp_rename 'res_daily_threat.fx_northwest', 'fx_pnw', 'COLUMN'");
        $this->execute("EXEC sp_rename 'res_daily_threat.fx_southern', 'fx_southeast', 'COLUMN'");

        $this->execute('ALTER TABLE res_daily_threat DROP COLUMN great_basin');
        $this->execute('ALTER TABLE res_daily_threat DROP COLUMN fx_great_basin');
        $this->execute('ALTER TABLE res_daily_threat DROP COLUMN alaska');
        $this->execute('ALTER TABLE res_daily_threat DROP COLUMN fx_alaska');
        $this->execute('ALTER TABLE res_daily_threat DROP COLUMN eastern');
        $this->execute('ALTER TABLE res_daily_threat DROP COLUMN fx_eastern');
	}
}