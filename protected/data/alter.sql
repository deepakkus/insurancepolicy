/*Adding a field in res_fire_name DB*/
use wds_dev;
IF Not Exists (Select Table_Name,COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS  Where Table_Name = 'res_fire_name' and Column_Name='DisasterGUID')
ALTER TABLE res_fire_name ADD DisasterGUID varchar(40) Default '{00000000-0000-0000-0000-1000000000d1}'
GO
Update res_fire_name SET DisasterGUID='{00000000-0000-0000-0000-1000000000d1}'
GO