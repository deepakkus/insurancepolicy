<?php

class m170709_221859_buffer_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute('
            CREATE TABLE [dbo].[res_perimeter_buffers](
	            [id] [int] IDENTITY(1,1) NOT NULL,
	            [date_created] [datetime] NULL,
	            [date_updated] [datetime] NULL,
	            [perimeter_id] [int] NOT NULL,
	            [location_id] [int] NOT NULL,
                [buffer_distance] varchar(2) NOT NULL,
                CONSTRAINT [PK_res_perimeter_buffers] PRIMARY KEY CLUSTERED ([id] ASC),
                CONSTRAINT [FK_location_id] FOREIGN KEY ([location_id]) REFERENCES [location] ([id]),
                CONSTRAINT [FK_res_perimeters] FOREIGN KEY ([perimeter_id]) REFERENCES [res_perimeters] ([id])
            )
        ');
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE [res_perimeter_buffers] DROP CONSTRAINT FK_location_id');
        $this->execute('ALTER TABLE [res_perimeter_buffers] DROP CONSTRAINT FK_res_perimeters');
        $this->execute('DROP TABLE [res_perimeter_buffers]');
    }
}