<?php
class NewImportCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		
		//IMPORT
		//print "Starting Import NEW!\n";
		//$filesToImport = Yii::app()->db->createCommand("SELECT TOP 1 * FROM import_files WHERE status = 'Uploaded' AND type = 'USAA - Full PIF New'")->queryAll();
		//foreach($filesToImport as $fileToImport)
		//{
		//	$this->importUSAAPIF($fileToImport);
		//}
		//print "\ndone importing all files NEW!";
		
		//STATUS AND OTHER DATE MIGRATION FROM CURRENT MEM/PROP TABLES
		$this->migrateGeoRisk();
	}
	
	private function migrateData()
	{
		print "Starting Response Enrollment Migration\n";
		
		//setup error_file
		$error_fh = fopen('C:\inetpub\wwwroot\pro.wildfire-defense.com\protected\migrate_data_errors.csv', 'w');
		$header_fields = array('pid', 'mid', 'member_num', 'pre_risk_status', 'response_status', 'policy_status', 'Error');
		fputcsv($error_fh, $header_fields);
		
		//find all properties with response_status of enrolled
		$resEnrolledProps = Property::model()->findAllByAttributes(array('response_status'=>'enrolled'));
		print "There are ".count($resEnrolledProps)." response enrolled properties\n";
		$counter = 0;
		$errorCounter = 0;
		foreach($resEnrolledProps as $prop)
		{
			$counter++;
			//find new member by USAA member num
			$newMember = MemberNew::model()->findByAttributes(array('member_num'=>$prop->member->member_num, 'client'=>'USAA'));
			if(isset($newMember))
			{
				$newProp = PropertyNew::model()->findByAttributes(array('member_mid'=>$newMember->mid, 'policy'=>$prop->policy));
				if(isset($newProp))
				{
					//update new table
					$newProp->response_status = $prop->response_status;
					$newProp->res_status_date = $prop->res_status_date;
					$newProp->response_enrolled_date = $prop->response_enrolled_date;
					if(!$newProp->save())
					{
						fputcsv($error_fh, array($prop->pid, $prop->member->mid, $prop->member->member_num, $prop->pre_risk_status, $prop->response_status, $prop->policy_status, 'COULD NOT SAVE PROPERTY. DETAILS: '.preg_replace('/\s+/', ' ', trim(var_export($newProp->getErrors(), true)))));
						$errorCounter++;
					}
				}
				else
				{
					fputcsv($error_fh, array($prop->pid, $prop->member->mid, $prop->member->member_num, $prop->pre_risk_status, $prop->response_status, $prop->policy_status, 'COULD NOT FIND NEW PROPERTY'));
					$errorCounter++;
				}
			}
			else
			{
				fputcsv($error_fh, array($prop->pid, $prop->member->mid, $prop->member->member_num, $prop->pre_risk_status, $prop->response_status, $prop->policy_status, 'COULD NOT FIND NEW MEMBER'));
				$errorCounter++;
			}
			
			if($counter % 50 == 0)
			{
				print "Processed ".$counter." properties. ".$errorCounter." errors so far.\n";
			}
		}
		
		print "End Response Enrollment Migration!";
		
		//find all properties with pre_risk_status of enrolled
		//$prEnrolledProps = Property::model()->findAllByAttributes(array('pre_risk_status'=>'enrolled'));
		//print "There are ".count($prEnrolledProps)." pre risk enrolled properties\n";
		
		//find all properties with pre_risk_status of offered
		//$prOfferedProps = Property::model()->findAllByAttributes(array('pre_risk_status'=>'offered'));
		//print "There are ".count($prOfferedProps)." pre risk offered properties\n";
		
	}
	
	private function migrateGeoRisk()
	{
		print "starting geo risk update run\n";
		$file_path = 'C:\inetpub\wwwroot\pro.wildfire-defense.com\protected\imports\new_geo_risk_update.csv';
		$fh = fopen($file_path, 'r');
		//get header rows
		$header_fields = fgetcsv($fh);
		//check to make sure required headers are in file
		$fields_to_check = array('geo_risk', 'comments', 'geocode_level', 'policy', 'member_num');
		foreach($fields_to_check as $field)
		{
			if(array_search($field, $header_fields) === false)
			{
				print "ERROR missing column with header: ".$field."\n";
				return false;
			}
		}
		
		//column indexes
		$member_num_col = array_search('member_num', $header_fields);
		$geo_risk_col = array_search('geo_risk', $header_fields);
		$comments_col = array_search('comments', $header_fields);
		$geocode_level_col = array_search('geocode_level', $header_fields);
		$policy_col = array_search('policy', $header_fields);
		
		$counter = 0;
		$updateCounter = 0;
		while($data = fgetcsv($fh))
		{
			$counter++;
			if(in_array($data[$geo_risk_col], array('1', '2', '3')))
			{
				$mem = Member::model()->findByAttributes(array('client'=>'USAA', 'member_num'=>$data[$member_num_col]));
				if(isset($mem))
				{
					$updateCounter++;
					Yii::app()->db->createCommand()->update('properties', array('geo_risk'=>$data[$geo_risk_col], 'geocode_level'=>$data[$geocode_level_col], 'comments'=>$data[$comments_col]),"policy = '" . $data[$policy_col] . "' AND member_mid = '" . $mem->mid . "'");
				}
			}
			if($counter % 500 == 0)
			{
				print "Attempted to Update $counter ($updateCounter) rows so far...\n";
			}
		}
		print "done with geo_risk update run";
	}

	//Takes the various PIF list csv file types and adds/updates the database with values.
	//$fileToImport is a dataRow from the import_files table
	private function importUSAAPIF($fileToImport)
	{
		$connection = Yii::app()->db;
		$command = $connection->createCommand();

		//open file
		$fh = fopen($fileToImport['file_path'], 'r');
		//get header row
		$header_fields = fgetcsv($fh);

		//setup error_file
		$error_fh = fopen($fileToImport['file_path'].'_errors', 'w');
		$header_fields[] = 'Errors';
		fputcsv($error_fh, $header_fields);

		//variables to track successfull row imports and errors
		$rows_imported = 0;
		$import_results = '';

		//check to make sure required headers are in file

		$fields_to_check = array('Member','Rated Company','Member Salutation','Member Rank', 'Member First Name', 'Member Middle Name', 'Member Last Name', 'Cell Phone', 'Home Phone', 'Business Phone', 'Mailing Address1', 'Mailing Address2', 'Mailing City',
		'Mailing_State', 'Mailing Zip', 'Mailing Zip Supp', 'LOB', 'Dwelling Type', 'Roof Type', 'Primary Email', 'Secondary Email', 'Signed OLA', 'Special Handling Code', 'Risk Address1', 'Risk Address2',
		'Property Location City', 'Property Location County', 'Property Location Zip', 'Property Location Zip Supp', 'Property Location State', 'Property Policy Number', 'Property Latitude', 'Property Longitude',
		'Geocode Level', 'Dwelling Coverage A Amt', 'Policy Effective Date', 'Policy Expiration Date', 'Spouse Member Number', 'Spouse First Name', 'Spouse Middle Name', 'Spouse Last Name', 'Spouse Salutation',
		'Spouse Rank', 'Inspection BrushFire Supplmt Ind', 'Inspection Received Date');

		foreach($fields_to_check as $field)
		{
			if(array_search($field, $header_fields) === false)
			{
				print "ERROR missing column with header: ".$field."\n";
				return false;
			}
		}

		//column indexes
		$member_num_col = array_search('Member', $header_fields);
		$rated_company_col = array_search('Rated Company', $header_fields);
		$salutation_col = array_search('Member Salutation', $header_fields);
		$rank_col = array_search('Member Rank', $header_fields);
		$first_name_col = array_search('Member First Name', $header_fields);
		$middle_name_col = array_search('Member Middle Name', $header_fields);
		$last_name_col = array_search('Member Last Name', $header_fields);
		$cell_phone_col = array_search('Cell Phone', $header_fields);
		$home_phone_col = array_search('Home Phone', $header_fields);
		$work_phone_col = array_search('Business Phone', $header_fields);
		$mailing_address_line_1_col = array_search('Mailing Address1', $header_fields);
		$mailing_address_line_2_col = array_search('Mailing Address2', $header_fields);
		$mailing_city_col = array_search('Mailing City', $header_fields);
		$mailing_state_col = array_search('Mailing_State', $header_fields);
		$mailing_zip_col = array_search('Mailing Zip', $header_fields);
		$mailing_zip_supp_col = array_search('Mailing Zip Supp', $header_fields);
		$lob_col = array_search('LOB', $header_fields);
		$dwelling_type_col = array_search('Dwelling Type', $header_fields);
		$roof_type_col = array_search('Roof Type', $header_fields);
		$email_1_col = array_search('Primary Email', $header_fields);
		$email_2_col = array_search('Secondary Email', $header_fields);
		$signed_ola_col = array_search('Signed OLA', $header_fields);
		$spec_handling_code_col = array_search('Special Handling Code', $header_fields);
		$address_line_1_col	= array_search('Risk Address1', $header_fields);
		$address_line_2_col = array_search('Risk Address2', $header_fields);
		$city_col = array_search('Property Location City', $header_fields);
		$county_col = array_search('Property Location County', $header_fields);
		$zip_col = array_search('Property Location Zip', $header_fields);
		$zip_supp_col = array_search('Property Location Zip Supp', $header_fields);
		$state_col = array_search('Property Location State', $header_fields);
		$policy_col = array_search('Property Policy Number', $header_fields);
		$lat_col = array_search('Property Latitude', $header_fields);
		$long_col = array_search('Property Longitude', $header_fields);
		$geocode_level_col = array_search('Geocode Level', $header_fields);
		$coverage_a_amt_col = array_search('Dwelling Coverage A Amt', $header_fields);
		$policy_effective_col = array_search('Policy Effective Date', $header_fields);
		$policy_expiration_col = array_search('Policy Expiration Date', $header_fields);
		$spouse_member_num_col = array_search('Spouse Member Number', $header_fields);
		$spouse_first_name_col = array_search('Spouse First Name', $header_fields);
		$spouse_middle_name_col = array_search('Spouse Middle Name', $header_fields);
		$spouse_last_name_col = array_search('Spouse Last Name', $header_fields);
		$spouse_salutation_col = array_search('Spouse Salutation', $header_fields);
		$spouse_rank_col = array_search('Spouse Rank', $header_fields);
		$errors_col = array_search('Errors', $header_fields);

		//index variables to track progress/results
		$counter = 0;
		$updated_members = 0;
		$new_members = 0;
		$new_properties = 0;
		$updated_properties = 0;

		//loop through each row in the file storing the data fields in a temp array
		while($data = fgetcsv($fh))
		{
			$counter++;
			//check if member already exists
			$member = MemberNew::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>'USAA'));
			if(!isset($member)) //didn't find member, so make new one and a new property
			{
				$member = new MemberNew();
				$property = new PropertyNew();
				$new_members++;
				$new_properties++;
			}
			else //existing member
			{
				$updated_members++;
				//check if property already exists for member
				$property = PropertyNew::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$data[$policy_col],));
				if(!isset($property)) //didnt find property, so make new one
				{
					$property = new PropertyNew();
					$new_properties++;
				}
				else
					$updated_properties++;
			}

			$property->rated_company = trim($data[$rated_company_col]);
			$member->salutation = trim($data[$salutation_col]);
			$member->rank = trim($data[$rank_col]);
			$member->first_name = trim($data[$first_name_col]);
			$member->middle_name = trim($data[$middle_name_col]);
			$member->last_name = trim($data[$last_name_col]);
			$member->cell_phone = trim($data[$cell_phone_col]);
			$member->home_phone = trim($data[$home_phone_col]);
			$member->work_phone = trim($data[$work_phone_col]);
			$member->mail_address_line_1 = trim($data[$mailing_address_line_1_col]);
			$member->mail_address_line_2 = trim($data[$mailing_address_line_2_col]);
			$member->mail_city = trim($data[$mailing_city_col]);
			$member->mail_zip = trim($data[$mailing_zip_col]);
			$member->mail_zip_supp = trim($data[$mailing_zip_supp_col]);
			$member->mail_state = trim($data[$mailing_state_col]);
			$property->lob = trim($data[$lob_col]);
			$property->dwelling_type = trim($data[$dwelling_type_col]);
			$property->roof_type = trim($data[$roof_type_col]);
			$property->city = trim($data[$city_col]);
			$property->county = trim($data[$county_col]);
			$property->zip_supp = trim($data[$zip_supp_col]);
			$property->state = trim($data[$state_col]);
			$property->address_line_1 = trim($data[$address_line_1_col]);
			$property->address_line_2 = trim($data[$address_line_2_col]);
			$property->zip = trim($data[$zip_col]);
			$member->email_1 = trim($data[$email_1_col]);
			$member->email_2 = substr(trim($data[$email_2_col]), 0, 50);
			$member->signed_ola = trim($data[$signed_ola_col]);
			$member->spec_handling_code = trim($data[$spec_handling_code_col]);
			$property->lat = trim($data[$lat_col]);
			$property->long = trim($data[$long_col]);
			$property->geocode_level = trim($data[$geocode_level_col]);
			$property->coverage_a_amt = trim($data[$coverage_a_amt_col]);
			$property->policy_effective = trim($data[$policy_effective_col]);
			$property->policy_expiration = trim($data[$policy_expiration_col]);
			$member->spouse_member_num = trim($data[$spouse_member_num_col]);
			$member->spouse_first_name = trim($data[$spouse_first_name_col]);
			$member->spouse_middle_name = trim($data[$spouse_middle_name_col]);
			$member->spouse_last_name = trim($data[$spouse_last_name_col]);
			$member->spouse_salutation = trim($data[$spouse_salutation_col]);
			$member->spouse_rank = trim($data[$spouse_rank_col]);

			//default values
			if($member->isNewRecord)
			{
				$member->member_num = trim($data[$member_num_col]);
				$member->client = 'USAA';
			}
			if($property->isNewRecord)
			{
				$property->policy = trim($data[$policy_col]);
				$property->policy_status = 'active';
				$property->policy_status_date = date('Y-m-d');
				$property->response_status = 'not enrolled';
				$property->pre_risk_status = 'not enrolled';
				$property->fireshield_status = 'not enrolled';
				$property->fs_status_date = date("Y-m-d");
				$property->res_status_date = date("Y-m-d");
				$property->pr_status_date = date("Y-m-d");
			}

			//Save process
			if($property->dwelling_type == 'Dwelling')
			{
				if($member->save())
				{
					$property->member_mid = $member->mid;
					if(!$property->save())
					{
						print 'Could not import row. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
						$data[$errors_col] = 'Could not import property for member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
						fputcsv($error_fh, $data);
					}

					//hack for crappy response program software (arc gis) that can't see/use/takes-over the primary key column for some dumb reason
					$property->p_id = $property->pid;
					$property->save();
					$member->m_id = $member->mid;
					$member->save();
				}
				else
				{
					print 'Could not import row. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
					$data[$errors_col] = 'Could not import Member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
					fputcsv($error_fh, $data);
				}
			}
			else //no dwelling types that != Dwelling are allowed
			{
				print 'Could not import row. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
				$data[$errors_col] = 'Could not import property for member. Details: Dwelling Type of '.$property->dwelling_type.' is not accepted (only type `Dwelling` is allowed)';
				fputcsv($error_fh, $data);
			}
			

			if($counter % 100 == 0)
			{
				$command->update('import_files', array('status'=>'Processing',
									'details'=>'Imported ' . $counter . ' rows so far...',
									),
						'id = ' . $fileToImport['id']);
				print 'Imported ' . $counter . " rows so far...\n";
			}
		}
		fclose($fh);
		fclose($error_fh);
		$command->update('import_files', array('status'=>'Finished',
										'details'=>'Imported: ' . $counter . ' | Member Updates: ' . $updated_members.' | New Members: '.$new_members.' | Property Updates: '.$updated_properties.' | New Properties: '.$new_properties
										),
							'id = ' . $fileToImport['id']);
	}

}
?>