<?php
/*
 * This was built to fill in Recommended Action individual entries.
 */

class PreRiskRecActionFillCommand extends CConsoleCommand
{
    public function run($args)
    {
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		ini_set('memory_limit', '1000M');
		$min_id = 16000;
		$max_id = 17000;
        $pre_risks = PreRisk::model()->findAll("id >= $min_id AND id < $max_id");
		$counter = 0;
		$pr_count = count($pre_risks);
		while($pr_count > 0)
		{
			foreach($pre_risks as $pre_risk)
			{
				if(strpos(strtolower($pre_risk->recommended_actions), 'replace/repair roof') !== FALSE)
					$pre_risk->replace_repair_roof = 1;
				else
					$pre_risk->replace_repair_roof = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'clean roof') !== FALSE)
					$pre_risk->clean_roof = 1;
				else
					$pre_risk->clean_roof = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'replace/repair skylight/roof attachment') !== FALSE)
					$pre_risk->repair_roof_attachment = 1;
				else
					$pre_risk->repair_roof_attachment = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'screen/openings vents') !== FALSE)
					$pre_risk->screen_openings_vents = 1;
				else
					$pre_risk->screen_openings_vents = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'clean gutters') !== FALSE)
					$pre_risk->clean_gutters = 1;
				else
					$pre_risk->clean_gutters = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'clean/enclose eaves') !== FALSE)
					$pre_risk->clean_enclose_eaves = 1;
				else
					$pre_risk->clean_enclose_eaves = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'replace windows') !== FALSE)
					$pre_risk->replace_windows = 1;
				else
					$pre_risk->replace_windows = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'replace/treat siding') !== FALSE)
					$pre_risk->replace_treat_siding = 1;
				else
					$pre_risk->replace_treat_siding = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'clean/enclose underside of home') !== FALSE)
					$pre_risk->clean_under_home = 1;
				else
					$pre_risk->clean_under_home = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'replace/treat attachment') !== FALSE)
					$pre_risk->replace_attachment = 1;
				else
					$pre_risk->replace_attachment = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'clear vegetation/materials in 0-5 ft. zone') !== FALSE)
					$pre_risk->clear_veg_0_5 = 1;
				else
					$pre_risk->clear_veg_0_5 = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'manage vegetation in 5-30 ft. zone') !== FALSE)
					$pre_risk->manage_veg_5_30 = 1;
				else
					$pre_risk->manage_veg_5_30 = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'clear materials in 5-30 ft. zone') !== FALSE)
					$pre_risk->clear_materials_5_30 = 1;
				else
					$pre_risk->clear_materials_5_30 = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'additional structure') !== FALSE)
					$pre_risk->additional_structure = 1;
				else
					$pre_risk->additional_structure = 0;
				if(strpos(strtolower($pre_risk->recommended_actions), 'manage vegetation in 30-100 ft. zone') !== FALSE)
					$pre_risk->manage_veg_30_100 = 1;
				else
					$pre_risk->manage_veg_30_100 = 0;

				if(!$pre_risk->save())
					print "Error updating $pre_risk->id \n";

				$counter++;
				if($counter % 100 == 0)
					print "Processed $counter rows so far.\n";
			}
			$min_id = $max_id;
			$max_id += 1000;
			unset($pre_risks);
	        $pre_risks = PreRisk::model()->findAll("id >= $min_id AND id < $max_id");
	        $pr_count = count($pre_risks);
		}

    }
}

