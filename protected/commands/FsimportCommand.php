<?php

class FsimportCommand extends CConsoleCommand
{
	public function run($args)
	{
		//Loop through all the zip files and process the new ones into the database
		$reportsPath = Yii::app()->basePath.'/fs_reports/incoming/';
		$dirHandle = opendir($reportsPath);
		while($filename = readdir($dirHandle))
		{
			if(is_file($reportsPath.'/'.$filename)) //check to see if it is a file (not a dir)
			{
				$report_guid = str_replace('.zip', '', $filename); //get report guid based on report name
				//if(is_null(FSReport::model()->find("report_guid = '".$report_guid."'"))) //see if the report is already in the db or not
				if(in_array($report_guid, array('5D73F76E-BA54-46E5-95D4-0552E96C110D-1', '5D73F76E-BA54-46E5-95D4-0552E96C110D-2', '5D73F76E-BA54-46E5-95D4-0552E96C110D-3', '5D73F76E-BA54-46E5-95D4-0552E96C110D-4', '5D73F76E-BA54-46E5-95D4-0552E96C110D-5', '5D73F76E-BA54-46E5-95D4-0552E96C110D-6', '5D73F76E-BA54-46E5-95D4-0552E96C110D-7', '5D73F76E-BA54-46E5-95D4-0552E96C110D-8', '5D73F76E-BA54-46E5-95D4-0552E96C110D-9')))
				{
					print "importing ".$report_guid."\n";
					$fsReport = new FSReport();
					$import_error = $fsReport->import($report_guid);
					if($import_error)
					{
						print "ERROR: ".$import_error."\n";
					}
					else  //successful return ($import_error == false)
					{
						print "success importing ".$report_guid."\n";
					}
				}
				else 
				{
					print 'Already had report with GUID: '.$report_guid."\n";
				}
			}
			else		
			{
				print 'Not a File: '.$reportsPath.'/'.$filename."\n";
			}
		}
		closedir($dirHandle);
		print "Complete";

	}
}
?>
