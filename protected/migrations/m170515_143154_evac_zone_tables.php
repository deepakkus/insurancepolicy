<?php

class m170515_143154_evac_zone_tables extends CDbMigration
{
	public function safeUp()
	{
        //create resEvacZone
        $this->execute("
           CREATE TABLE [res_evac_zone]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [notice_id] [int] NOT NULL,
                [geog] [geography] NOT NULL,
                [notes] [varchar](200) NOT NULL,
                CONSTRAINT [PK_res_evac_zone] PRIMARY KEY CLUSTERED ([id] ASC)
            )
          ");
        $this->execute("ALTER TABLE res_evac_zone ADD CONSTRAINT FK_notice FOREIGN KEY ([notice_id]) REFERENCES [res_notice] ([notice_id])");

	}

	public function safeDown()
	{
        $this->dropForeignKey('FK_notice', 'res_evac_zone');
        $this->dropTable('res_evac_zone');
	}

}