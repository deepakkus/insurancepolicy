<?php

class m170116_204655_alter_wds_lob extends CDbMigration
{

	// For some reason the original migration (m160929_162413) did not setup the default correctly as all the DBs have a blank wds_log, trying again now
	public function safeUp()
	{
        //update all existing rows how they should be which is very basic right now, only Pharm has business props we identify (lob = 'BOP'). All rest should be 'HOM'
        $this->update('properties', array('wds_lob'=>'BUS'), "[lob] = 'BOP'");
        $this->update('properties', array('wds_lob'=>'HOM'), "[wds_lob] IS NULL");

        //find the wds_lob default constraint name
        $wdsLOBDefaultConstraint = Yii::app()->db->createCommand("
            SELECT
                default_constraints.name
            FROM
                sys.all_columns

                    INNER JOIN
                sys.tables
                    ON all_columns.object_id = tables.object_id

                    INNER JOIN
                sys.schemas
                    ON tables.schema_id = schemas.schema_id

                    INNER JOIN
                sys.default_constraints
                    ON all_columns.default_object_id = default_constraints.object_id
            WHERE
                schemas.name = 'dbo'
                AND tables.name = 'properties'
                AND all_columns.name = 'wds_lob'")->queryRow();
        //remove erroneous constraint
        $this->execute('ALTER TABLE [properties] DROP CONSTRAINT '.$wdsLOBDefaultConstraint['name']);
        //Add NOT NULL constraint
        $this->execute('ALTER TABLE [properties] ALTER COLUMN [wds_lob] varchar(3) NOT NULL');
        //Add default to 'HOM' constraint
        $this->execute("ALTER TABLE [properties] ADD CONSTRAINT [DF_tbl_properties_col_wds_lob] DEFAULT 'HOM' FOR [wds_lob]");
	}

	public function safeDown()
	{
        echo "m170116_204655_alter_wds_lob does not support migration down.\n";
		return false;
	}
}