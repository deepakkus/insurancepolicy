<?php
class m180321_095514_add_column_agency_phone_property extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE [properties] ADD [agency_phone] [varchar](20) CONSTRAINT DF_properties_agency_phone DEFAULT NULL");
	}

	public function safeDown()
	{
        $this->execute("
            ALTER TABLE [properties] DROP CONSTRAINT DF_properties_agency_phone
            ALTER TABLE [properties] DROP COLUMN [agency_phone]
        ");
	}
}