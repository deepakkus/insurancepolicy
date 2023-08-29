<?php

class m170216_183254_fire_perimeter_view extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
        ALTER VIEW [dbo].[view_perimeters] AS
            SELECT
            [p].[id],
            [p].[fire_id],
            [f].[Name],
            [f].[City],
            [f].[State],
            [f].[Start_Date] [fire_start_date],
            [f].[Contained],
            [p].[date_created] [perimeter_date_created],
            [p].[date_updated] [perimeter_date_updated],
            [p].[geog]
            FROM [dbo].[res_perimeters] [p]
                INNER JOIN [dbo].[res_fire_name] [f] ON [f].[Fire_ID] = [p].[fire_id]
            WHERE [p].[geog].STGeometryType() = 'Polygon' OR  [p].[geog].STGeometryType() = 'MultiPolygon'
            "
         );
	}

	public function safeDown()
	{
        $this->execute("
        ALTER VIEW [dbo].[view_perimeters] AS
            SELECT
            [p].[id],
            [p].[fire_id],
            [f].[Name],
            [f].[City],
            [f].[State],
            [f].[Start_Date] [fire_start_date],
            [f].[Contained],
            [p].[date_created] [perimeter_date_created],
            [p].[date_updated] [perimeter_date_updated],
            [p].[geog]
            FROM [dbo].[res_perimeters] [p]
                INNER JOIN [dbo].[res_fire_name] [f] ON [f].[Fire_ID] = [p].[fire_id]
            WHERE [p].[geog].STGeometryType() = 'Polygon'
            "
         );
	}
}