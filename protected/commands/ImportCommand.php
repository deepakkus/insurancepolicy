<?php

class ImportCommand extends CConsoleCommand
{
    public function run($args)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        print "Starting Import for:\n";
        $filesToImport = Yii::app()->db->createCommand("SELECT * FROM import_files WHERE status = 'Uploaded' ORDER BY id ASC")->queryAll();
        
        foreach($filesToImport as $fileToImport)
        {
            print "File Type: ".$fileToImport['type']."\n";
            print "File Path: ".$fileToImport['file_path']."\n";
           
            if(in_array($fileToImport['type'], array('USAA - Change PIF', 'USAA - Add Drop PIF')))
                $this->transDateSensImportUSAAPIF($fileToImport);
            elseif($fileToImport['type'] == 'WDS - PR Status Update')
                $this->importPRStatusFile($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Update Geo Risk')
                $this->updatePropGeoRisk($fileToImport);
            elseif($fileToImport['type'] == 'WDS - FS Offered')
                $this->updateUSAAFSOffered($fileToImport);
            elseif($fileToImport['type'] == 'USAA - Full PIF Merge')
                $this->mergeUSAAFullPIF($fileToImport);
            elseif($fileToImport['type'] == 'USAA - Post Merge Cancel')
                $this->postMergeUSAACancel($fileToImport);
            elseif($fileToImport['type'] == 'WDS - PR Completed Prop Status Update')
                $this->update_completed_pr_props($fileToImport);
            elseif($fileToImport['type'] == 'LM/SAF - Full PIF Import')
                $this->importLibertySafecoPIF($fileToImport, 'LM/SAF', 'Full');
            elseif($fileToImport['type'] == 'LM/SAF - Incremental PIF Import')
                $this->importLibertySafecoPIF($fileToImport, 'LM/SAF', 'Incremental');
            elseif($fileToImport['type'] == 'WDS - LMSaf Res Enroll')
                $this->importLMSafResEnroll($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Activate Properties')
                $this->importActivateProperties($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Update Lat Long')
                $this->importWDSLatLongUpdate($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Update User Passwords')
                $this->importUserPWUpdate($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Update USAA Transactions')
                $this->importUpdateUSAATransactions($fileToImport);
            elseif($fileToImport['type'] == 'Chubb - Eligible PIF Import' || $fileToImport['type'] == 'Chubb - Enrolled PIF Import')
                $this->importChubbPIF($fileToImport);
            elseif($fileToImport['type'] == 'Flag Active USAA Props')
                $this->flagActiveUSAAProps($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Property Statuses Update')
                $this->updatePropertyStatuses($fileToImport);
            elseif($fileToImport['type'] == 'WDS - Get PIDs')
                $this->getPIDs($fileToImport);
            elseif($fileToImport['type'] == 'WDS - PR Call List')
                $this->importPRCallList($fileToImport);
            elseif($fileToImport['type'] == 'Nationwide - Eligible PIF Import' || $fileToImport['type'] == 'Nationwide - Enrolled PIF Import')
                $this->importNWPIF($fileToImport);
            elseif($fileToImport['type'] == 'USAA - Audit')
                $this->usaaAudit($fileToImport);
            elseif($fileToImport['type'] == 'Pharm - PIF Import')
                $this->importPharmPIF($fileToImport);
            elseif($fileToImport['type'] == 'MOE - PIF Import')
                $this->importMoEPIF($fileToImport);
            elseif($fileToImport['type'] == 'Ace - PIF Import')
                $this->importAcePIF($fileToImport);
            elseif($fileToImport['type'] == 'Firemans Fund - PIF Import')
                $this->importFundPIF($fileToImport);
            elseif($fileToImport['type'] == 'Pemco - PIF Import')
                $this->importPemcoPIF($fileToImport);
            elseif($fileToImport['type'] == 'Cincinnati - PIF Import')
                $this->importCinFinPIF($fileToImport);
            elseif($fileToImport['type'] == 'Non-standard')
                $this->importNonStandard($fileToImport);
               
        }
        print "\ndone importing all files!";
    }
    /**
     * Imports the CinFin Full PIF list (csv file with |'s as the seperator)
     * Outputs a *_results.csv file for details of each record imported
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importCinFinPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting CinFin PIF Import");

        //look up client
        $cin_client = Client::model()->findByAttributes(array('name'=>'Cincinnati Insurance'));

        //Need to reset all cin property flags (for cancel routine after import)
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Cin Import Flag Reset Routine.");
        $this->reset_property_flags($cin_client->id);
        $this->printUpdateStatus($fileToImport, "Processing", "Done with Cin Import Flag Reset Routine.");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh, null, '|');
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);
        //policyholder number field has non-utf8 quote chars in it, need to trim them out
        $temp_header_fields = $header_fields;
        $i = 0;
        foreach($temp_header_fields as $temp_header)
        {
            if(strpos($temp_header, 'policyholder number'))
                $header_fields[$i] = 'policyholder number';
            $i++;
        }

        //check required and get col indexes
        $fields_to_check = array('policyholder number','policy number','salutation','first name','middle name','last name','address','city','state','zip code',
            'property zip code suppliment','property county','property latitude','property longitude','property geocode confidence level','property geocode level','wds program enrollment status',
            'home phone','work phone','cell phone','email','secondary email','policy coverage a amount','policy lob','policy effective date','policy expiration date',
            'mailing address','mailing city','mailing state','mailing zip','mailing zip suppliment','mailing county','spouse salutation','spouse first name','spouse middle name',
            'spouse last name','property roof type','property dwelling type','producer info','rated company','agency code','agency name'
        );
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field."(Header Fields availible:".implode(',',$header_fields).")");
                return false;
            }
        }
        $policyholder_number_col = array_search('policyholder number', $header_fields);
        $policy_number_col = array_search('policy number', $header_fields);
        $salutation_col = array_search('salutation', $header_fields);
        $first_name_col = array_search('first name', $header_fields);
        $middle_name_col = array_search('middle name', $header_fields);
        $last_name_col = array_search('last name', $header_fields);
        $prop_address_col = array_search('address', $header_fields);
        $prop_city_col = array_search('city', $header_fields);
        $prop_st_col = array_search('state', $header_fields);
        $prop_zip_col = array_search('zip code', $header_fields);
        $prop_zip_sup_col = array_search('property zip code suppliment', $header_fields);
        $prop_county_col = array_search('property county', $header_fields);
        $lat_col = array_search('property latitude', $header_fields);
        $long_col = array_search('property longitude', $header_fields);
        $geo_code_level_col = array_search('property geocode level', $header_fields);
        $geo_code_confidence_col = array_search('property geocode confidence level', $header_fields);
        $response_enrollment_col = array_search('wds program enrollment status', $header_fields);
        $home_phone_col = array_search('home phone', $header_fields);
        $work_phone_col = array_search('work phone', $header_fields);
        $cell_phone_col = array_search('cell phone', $header_fields);
        $email_col = array_search('email', $header_fields);
        $secondary_email_col = array_search('secondary email', $header_fields);
        $coverage_a_col = array_search('policy coverage a amount', $header_fields);
        $lob_col = array_search('policy lob', $header_fields);
        $effective_date_col = array_search('policy effective date', $header_fields);
        $expiration_date_col = array_search('policy expiration date', $header_fields);
        $mail_address_col = array_search('mailing address', $header_fields);
        $mail_city_col = array_search('mailing city', $header_fields);
        $mail_state_col = array_search('mailing state', $header_fields);
        $mail_zip_col = array_search('mailing zip', $header_fields);
        $mail_zip_sup_col = array_search('mailing zip suppliment', $header_fields);
        $mail_county_col = array_search('mailing county', $header_fields);
        $spouse_salutation_col = array_search('spouse salutation', $header_fields);
        $spouse_first_name_col = array_search('spouse first name', $header_fields);
        $spouse_middle_name_col = array_search('spouse middle name', $header_fields);
        $spouse_last_name_col = array_search('spouse last name', $header_fields);
        $roof_type_col = array_search('property roof type', $header_fields);
        $dwelling_type_col = array_search('property dwelling type', $header_fields);
        $producer_info_col = array_search('producer info', $header_fields);
        $rated_company_col = array_search('rated company', $header_fields);
        $agency_code_col = array_search('agency code', $header_fields);
        $agency_name_col = array_search('agency name', $header_fields);

        //setup import_results file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('PolicyHolderNumber', 'PolicyNumber', 'ImportAction',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;

        while(($data = fgetcsv($fh,null,'|')) !== FALSE)
        {
            $counter++;
            $skip = false;
            $results_action = '';

            if(count($data) !== count($header_fields))
            {
                print "Error: Number of fields in data row do not match number of header fields. Skipping line: $counter \n";
                fputcsv($error_fh, array("Error: Number of fields in data row do not match number of header fields. Skipping line: $counter \n"));
            }
            else
            {
                //trim up the attributes
                $data = array_map('trim', $data);

                //set our primary mem and prop keys
                $member_num = $data[$policyholder_number_col];
                $policy = $data[$policy_number_col];

                //check if member already exists
                $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$cin_client->id));
                if(!isset($member)) //didn't find member, so make new one and a new property
                {
                    $member = new Member();
                    $member->member_num = $member_num;
                    $property = new Property();
                    $property->policy = $policy;
                    $property->client_policy_id = $data[$policy_number_col];
                    $new_members++;
                    $new_properties++;
                    $results_action = "Added New Policy Holder and New Policy. ";
                }
                else //existing member
                {
                    $updated_members++;
                    //check if property already exists for member
                    $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>'1'));
                    if(!isset($property)) //didnt find property, so make new one
                    {
                        $property = new Property();
                        $property->policy = $policy;
                        $property->client_policy_id = $data[$policy_number_col];
                        $new_properties++;
                        $results_action = "Updated Existing Policy Holder, Added New Policy. ";
                    }
                    else //property already existed
                    {
                        $updated_properties++;
                        $results_action = "Updated Existing Policy Holder and Policy. ";
                    }
                }

                //---set/map attributes section---//
                $member->salutation = substr($data[$salutation_col],0,50);
                $member->first_name = substr($data[$first_name_col], 0, 50);
                $member->middle_name = substr($data[$first_name_col], 0, 25);
                $member->last_name = substr($data[$last_name_col], 0, 50);
                $member->mail_address_line_1 = substr($data[$mail_address_col], 0, 100);
                $member->mail_city = substr($data[$mail_city_col], 0, 50);
                $member->mail_state = substr($data[$mail_state_col], 0, 25);
                $member->mail_zip = substr($data[$mail_zip_col], 0, 25);
                $member->mail_zip_supp = substr($data[$mail_zip_sup_col], 0, 25);
                $member->mail_county = substr($data[$mail_county_col], 0, 50);
                $member->home_phone = substr($data[$home_phone_col], 0, 25);
                $member->work_phone = substr($data[$work_phone_col], 0, 25);
                $member->cell_phone = substr($data[$cell_phone_col], 0, 25);
                $member->email_1 = substr($data[$email_col], 0, 50);
                $member->email_2 = substr($data[$secondary_email_col], 0, 50);
                $member->spouse_salutation = substr($data[$spouse_salutation_col], 0, 50);
                $member->spouse_first_name = substr($data[$spouse_first_name_col], 0, 50);
                $member->spouse_middle_name = substr($data[$spouse_middle_name_col], 0, 25);
                $member->spouse_last_name = substr($data[$spouse_last_name_col], 0, 50);
                $property->producer = substr($data[$producer_info_col], 0, 100);
                $property->policy_effective = $data[$effective_date_col];
                $property->policy_expiration = $data[$expiration_date_col];
                $property->address_line_1 = substr($data[$prop_address_col], 0, 100);
                $property->city = substr($data[$prop_city_col], 0, 50);
                $property->county = substr($data[$prop_county_col], 0, 50);
                $property->state = substr($data[$prop_st_col], 0, 25);
                $property->zip = substr($data[$prop_zip_col], 0, 25);
                $property->zip_supp = substr($data[$prop_zip_sup_col], 0, 25);
                $property->lob = substr($data[$lob_col], 0, 15);
                $property->wds_lob = 'HOM';
                $property->coverage_a_amt = round($data[$coverage_a_col], 0);
				/*
                $property->lat = $data[$lat_col];
                $property->long = $data[$long_col];
                $property->geocode_level = substr($data[$geo_code_level_col], 0, 15);
				*/
				//set default lat, Long etc
				$property->lat = NULL;
				$property->long = NULL;	
				
				$property->rated_company  = substr($cin_client->name,0,40) ;

                $property->roof_type = substr($data[$roof_type_col], 0, 50);
                $property->dwelling_type = substr($data[$dwelling_type_col], 0, 50);
                $property->agency_code = substr($data[$agency_code_col], 0, 25);
                $property->agency_name = substr($data[$agency_name_col],0, 100);

                //setup default program and policy statuses
                if($member->isNewRecord)
                {
                    $member->mem_fireshield_status = 'not enrolled';
                    $member->mem_fs_status_date = date('Y-m-d H:i:s');
                }
                if($property->isNewRecord)
                {
                    $property->pre_risk_status = 'not enrolled';
                    $property->fireshield_status = 'not enrolled';
                    $property->response_status = strtolower($data[$response_enrollment_col]);
                    //$property->response_status = 'not enrolled';
                    $property->fs_status_date = date('Y-m-d H:i:s');
                    $property->pr_status_date = date('Y-m-d H:i:s');
                    $property->res_status_date = date('Y-m-d H:i:s');
                    $property->policy_status = 'active';
                    $property->policy_status_date = date('Y-m-d H:i:s');
					$property->wds_lat = NULL;
					$property->wds_long = NULL;
					$property->geog = NULL;
					$property->wds_geocode_level = 'unmatched';
                }

                //if its in the PIF, then it's an active policy
                if(!$property->isNewRecord && $property->policy_status != 'active')
                {
                    $property->policy_status = 'active';
                    $property->policy_status_date = date('Y-m-d H:i:s');
                }

                //other required fields:
                $member->client_id = $cin_client->id;
                $property->client_id = $cin_client->id;
                $property->flag = 1;
                $member->client = 'Cincinnati Insurance';

                //--------Save Routine-----------//
                if($member->save())
                {
                    $property->member_mid = $member->mid; //now that we know the mid set it

                    if(!$property->save()) //prop failed saving
                    {
                        print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                        $results_action = 'Error: Could not import Policy info (PolicyHolder saved, but Policy did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        $errors++;
                    }
                    else
                    {
						//fetch last inserted property id after property save
						$pid_lastID = $property->pid;
						if($property->pid == '')
						{
							$lastproperty= Yii::app()->db->createCommand('select top 1* from properties order by pid desc')->queryAll();
							$pid_lastID = $lastproperty[0]['pid'];
						}
						$this->setGeoSpatial($pid_lastID, $data[$lat_col], $data[$long_col]);

                        $contactsRecieved = array();

                        $insured_name = trim($member->first_name) . ' ' . trim($member->last_name);

                        // Add contacts
                        if (!empty($data[$home_phone_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$home_phone_col],'notes'=>null));
                        if (!empty($data[$work_phone_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'work','priority'=>'Primary 2','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$work_phone_col],'notes'=>null));
                        if (!empty($data[$cell_phone_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 3','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$cell_phone_col],'notes'=>null));
                        if (!empty($data[$email_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$email_col],'notes'=>null));
                        if (!empty($data[$secondary_email_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 2','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$secondary_email_col],'notes'=>null));


                        // Remove any old contacts that were not recieved in this list
                        $this->cleanContacts($property->pid, $contactsRecieved);
                    }
                }
                else
                {
                    print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $results_action = 'Error: Could not import PolicyHolder info. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                    $errors++;
                }

                //add to results file with the action that was done
                fputcsv($results_fh, array($member->member_num, $property->policy, $results_action));

            }//end check for # fields matching # header fields

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //run cancel routine
        $props_to_cancel = Property::model()->findAll("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND flag = 0 AND client_id = ".$cin_client->id." AND policy_status != 'canceled'");
        //loop through all CinFin properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = count($props_to_cancel);
        $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
        foreach($props_to_cancel as $prop_to_cancel)
        {
            $results_action = 'Cancel';
            $prop_to_cancel->policy_status = 'canceled';
            $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
            if(!$prop_to_cancel->save())
            {
                print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true) . "\n";
                fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                $errors++;
            }
            else
            {
                //add to results file with the action that was done
                fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->member_mid, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
            }
        }

        //clean up and finish
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Cin Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Imports the Pemco Full PIF list (csv file)
     * Outputs a *_results.csv file for details of the import of each record.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importPemcoPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Pemco PIF Import");

        //look up client
        $pemco_client = Client::model()->findByAttributes(array('name'=>'Pemco'));

        //Need to reset all pemco property flags (for cancel routine after import)
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Pemco Import Flag Reset Routine.");
        $this->reset_property_flags($pemco_client->id);
        $this->printUpdateStatus($fileToImport, "Processing", "Done with Pemco Import Flag Reset Routine.");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);
        //for some reason the policyholder_number field has a stange characters in it, need to trim them out
        $temp_header_fields = $header_fields;
        $i = 0;
        foreach($temp_header_fields as $temp_header)
        {
            if(strpos($temp_header, 'policyholder_number'))
                $header_fields[$i] = 'policyholder_number';
            $i++;
        }

        //check required and get col indexes
        $fields_to_check = array('policyholder_number','policy_number','nin_first_name','nin_last_name','property_address','property_address_city','property_address_state',
            'property_address_zip','property_address_county','property_latitude','property_longitude','home_phone','work_phone','cell_phone','email','policy_coverage_a_amount',
            'policy_lob','policy_effective_date','policy_expiration_date','mailing_address','mailing_address_city','mailing_address_state','mailing_address_zip',
            'mailing_address_county','additional_named_insd','producer_info','producer_channel');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field."(Header Fields availible:".implode(',',$header_fields).")");
                return false;
            }
        }
        $policyholder_number_col = array_search('policyholder_number', $header_fields);
        $policy_number_col = array_search('policy_number', $header_fields);
        $first_name_col = array_search('nin_first_name', $header_fields);
        $last_name_col = array_search('nin_last_name', $header_fields);
        $prop_address_col = array_search('property_address', $header_fields);
        $prop_city_col = array_search('property_address_city', $header_fields);
        $prop_st_col = array_search('property_address_state', $header_fields);
        $prop_zip_col = array_search('property_address_zip', $header_fields);
        $prop_county_col = array_search('property_address_county', $header_fields);
        $lat_col = array_search('property_latitude', $header_fields);
        $long_col = array_search('property_longitude', $header_fields);
        $home_phone_col = array_search('home_phone', $header_fields);
        $work_phone_col = array_search('work_phone', $header_fields);
        $cell_phone_col = array_search('cell_phone', $header_fields);
        $email_col = array_search('email', $header_fields);
        $coverage_a_col = array_search('policy_coverage_a_amount', $header_fields);
        $lob_col = array_search('policy_lob', $header_fields);
        $effective_date_col = array_search('policy_effective_date', $header_fields);
        $expiration_date_col = array_search('policy_expiration_date', $header_fields);
        $mail_address_col = array_search('mailing_address', $header_fields);
        $mail_city_col = array_search('mailing_address_city', $header_fields);
        $mail_state_col = array_search('mailing_address_state', $header_fields);
        $mail_zip_col = array_search('mailing_address_zip', $header_fields);
        $mail_county_col = array_search('mailing_address_county', $header_fields);
        $additional_name_col = array_search('additional_named_insd', $header_fields);
        $producer_info_col = array_search('producer_info', $header_fields);
        $producer_channel_col = array_search('producer_channel', $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('PolicyHolderNumber', 'PolicyNumber', 'ImportAction',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $skip = false;
            $results_action = '';

            //trim up the attributes
            $data = array_map('trim', $data);

            //set our primary mem and prop keys
            $member_num = $data[$policyholder_number_col];
            $policy = $data[$policy_number_col];

            //check if member already exists
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$pemco_client->id));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $member->member_num = $member_num;
                $property = new Property();
                $property->policy = $policy;
                $property->client_policy_id = $data[$policy_number_col];
                $new_members++;
                $new_properties++;
                $results_action = "Added New Policy Holder and New Policy. ";
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>'1'));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    $property->policy = $policy;
                    $property->client_policy_id = $data[$policy_number_col];
                    $new_properties++;
                    $results_action = "Updated Existing Policy Holder, Added New Policy. ";
                }
                else //property already existed
                {
                    $updated_properties++;
                    $results_action = "Updated Existing Policy Holder and Policy. ";
                }
            }

            //---set/map attributes section---//
            $member->first_name = substr($data[$first_name_col], 0, 50);
            $member->last_name = substr($data[$last_name_col], 0, 50);
            $member->mail_address_line_1 = substr($data[$mail_address_col], 0, 100);
            $member->mail_city = substr($data[$mail_city_col], 0, 50);
            $member->mail_state = substr($data[$mail_state_col], 0, 25);
            $member->mail_zip = substr($data[$mail_zip_col], 0, 25);
            $member->home_phone = substr($data[$home_phone_col], 0, 25);
            $member->work_phone = substr($data[$work_phone_col], 0, 25);
            $member->cell_phone = substr($data[$cell_phone_col], 0, 25);
            $member->email_1 = substr($data[$email_col], 0, 50);
            $member->spouse_last_name = substr($data[$additional_name_col], 0, 50);
            if(!empty($data[$producer_info_col]))
                $property->producer = $data[$producer_info_col]." ";
            if(!empty($data[$producer_channel_col]))
                $property->producer .= '(Ch: '.$data[$producer_channel_col].") ";
            $property->producer = substr($property->producer, 0, 100); //trim it up if too long
            $property->policy_effective = $data[$effective_date_col];
            $property->policy_expiration = $data[$expiration_date_col];
            $property->address_line_1 = substr($data[$prop_address_col], 0, 100);
            $property->city = substr($data[$prop_city_col], 0, 50);
            $property->county = substr($data[$prop_county_col], 0, 50);
            $property->state = substr($data[$prop_st_col], 0, 25);
            $property->zip = substr($data[$prop_zip_col], 0, 25);
            $property->lob = substr($data[$lob_col], 0, 15);
            $property->coverage_a_amt = $data[$coverage_a_col];
            $property->lat = $data[$lat_col];
            $property->long = $data[$long_col];

            //setup default program and policy statuses
            if($member->isNewRecord)
            {
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }
            if($property->isNewRecord)
            {
                //currently monitoring only so setting all program statuses to 'not enrolled'
                $property->pre_risk_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->response_status = 'not enrolled';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->res_status_date = date('Y-m-d H:i:s');
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //if its in the PIF, then it's an active policy
            if(!$property->isNewRecord && $property->policy_status != 'active')
            {
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //other required fields:
            $member->client_id = $pemco_client->id;
            $property->client_id = $pemco_client->id;
            $property->flag = 1;
            $member->client = 'Pemco';

            //--------Save Routine-----------//
            if($member->save())
            {
                $property->member_mid = $member->mid; //now that we know the mid set it

                if(!$property->save()) //prop failed saving
                {
                    print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $results_action = 'Error: Could not import Policy info (PolicyHolder saved, but Policy did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    $errors++;
                }
                else
                {
                    $contactsRecieved = array();

                    $insured_name = trim($member->first_name) . ' ' . trim($member->last_name);

                    // Add contacts
                    if (!empty($data[$home_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$home_phone_col],'notes'=>null));
                    if (!empty($data[$work_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'work','priority'=>'Primary 2','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$work_phone_col],'notes'=>null));
                    if (!empty($data[$cell_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 3','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$cell_phone_col],'notes'=>null));
                    if (!empty($data[$email_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$email_col],'notes'=>null));

                    // Remove any old contacts that were not recieved in this list
                    $this->cleanContacts($property->pid, $contactsRecieved);
                }
            }
            else
            {
                print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $results_action = 'Error: Could not import PolicyHolder info. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                $errors++;
            }

            //add to results file with the action that was done
            fputcsv($results_fh, array($member->member_num, $property->policy, $results_action));

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //run cancel routine
        $props_to_cancel = Property::model()->findAll("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND flag = 0 AND client_id = ".$pemco_client->id." AND policy_status != 'canceled'");
        //loop through all Pemco properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = count($props_to_cancel);
        $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
        foreach($props_to_cancel as $prop_to_cancel)
        {
            $results_action = 'Cancel';
            $prop_to_cancel->policy_status = 'canceled';
            $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
            if(!$prop_to_cancel->save())
            {
                print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true) . "\n";
                fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                $errors++;
            }
            else
            {
                //add to results file with the action that was done
                fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->member_mid, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
            }
        }

        //clean up and finish
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Pemco Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Imports the Ace Full PIF list (csv file)
     * Outputs a *_results.csv file for details of the import of each record.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importAcePIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Ace PIF Import");

        //look up client
        $ace_client = Client::model()->findByAttributes(array('name'=>'Ace'));

        //Need to reset all ace property flags (for cancel routine after import)
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Ace Import Flag Reset Routine.");
        $this->reset_property_flags($ace_client->id);
        $this->printUpdateStatus($fileToImport, "Processing", "Done with Ace Import Flag Reset Routine.");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //for some reason the account_id field has a stange character in it, need to trim it out
        $temp_header_fields = $header_fields;
        $i = 0;
        foreach($temp_header_fields as $temp_header)
        {
            if(strpos($temp_header, 'account_id'))
                $header_fields[$i] = 'account_id';
            $i++;
        }

        //check required and get col indexes
        $fields_to_check = array('account_id','policy_number','policy_eff_date','insured_name','mailing_address','mailing_city','mailing_state','mailing_zip','home_phone','work_phone','email_addr','producer_code',
            'producer_name','producer_phone','location_unit','location_number','location_address','location_city','location_county','location_state','location_zip','coverage_type','wds_enrolled','wds_contact_phone_1',
            'wds_contact_phone_2', 'wds_contact_phone_3', 'wds_contact_phone_4', 'wds_contact_email_1', 'wds_contact_email_2', 'wds_contact_email_3', 'wds_contact_email_4','wds_gate_code','wds_misc');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field."(Header Fields availible:".implode(',',$header_fields).")");
                return false;
            }
        }
        $account_id_col = array_search('account_id', $header_fields);
        $policy_number_col = array_search('policy_number', $header_fields);
        $policy_eff_date_col = array_search('policy_eff_date', $header_fields);
        $insured_name_col = array_search('insured_name', $header_fields);
        $mailing_address_col = array_search('mailing_address', $header_fields);
        $mailing_city_col = array_search('mailing_city', $header_fields);
        $mailing_state_col = array_search('mailing_state', $header_fields);
        $mailing_zip_col = array_search('mailing_zip', $header_fields);
        $home_phone_col = array_search('home_phone', $header_fields);
        $work_phone_col = array_search('work_phone', $header_fields);
        $email_addr_col = array_search('email_addr', $header_fields);
        $producer_code_col = array_search('producer_code', $header_fields);
        $producer_name_col = array_search('producer_name', $header_fields);
        $producer_phone_col = array_search('producer_phone', $header_fields);
        $location_unit_col = array_search('location_unit', $header_fields);
        $location_number_col = array_search('location_number', $header_fields);
        $location_address_col = array_search('location_address', $header_fields);
        $location_city_col = array_search('location_city', $header_fields);
        $location_county_col = array_search('location_county', $header_fields);
        $location_state_col = array_search('location_state', $header_fields);
        $location_zip_col = array_search('location_zip', $header_fields);
        $coverage_type_col = array_search('coverage_type', $header_fields);
        $wds_enrolled_col = array_search('wds_enrolled', $header_fields);
        $wds_contact_phone_1_col = array_search('wds_contact_phone_1', $header_fields);
        $wds_contact_phone_2_col = array_search('wds_contact_phone_2', $header_fields);
        $wds_contact_phone_3_col = array_search('wds_contact_phone_3', $header_fields);
        $wds_contact_phone_4_col = array_search('wds_contact_phone_4', $header_fields);
        $wds_contact_email_1_col = array_search('wds_contact_email_1', $header_fields);
        $wds_contact_email_2_col = array_search('wds_contact_email_2', $header_fields);
        $wds_contact_email_3_col = array_search('wds_contact_email_3', $header_fields);
        $wds_contact_email_4_col = array_search('wds_contact_email_4', $header_fields);
        $wds_gate_code_col = array_search('wds_gate_code', $header_fields);
        $wds_misc_col = array_search('wds_misc', $header_fields);

        //setup error file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('PolicyHolderNumber', 'PolicyNumber', 'ImportAction',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;
        $enrolled_props = 0;

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $skip = false;
            $results_action = '';

            //trim up the attributes
            $data = array_map('trim', $data);

            //set our primary mem and prop keys
            $member_num = $data[$account_id_col];
            $policy = current(explode('-', $data[$policy_number_col]));
            $location = $data[$location_number_col];

            //check if member already exists
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$ace_client->id));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $member->member_num = $member_num;
                $property = new Property();
                $property->policy = $policy;
                $property->client_policy_id = $data[$policy_number_col];
                $property->location = $location;
                $new_members++;
                $new_properties++;
                $results_action = "Added New Policy Holder and New Policy. ";
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>$location));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    $property->policy = $policy;
                    $property->client_policy_id = $data[$policy_number_col];
                    $property->location = $location;
                    $new_properties++;
                    $results_action = "Updated Existing Policy Holder, Added New Policy. ";
                }
                else //property already existed
                {
                    $updated_properties++;
                    $results_action = "Updated Existing Policy Holder and Policy. ";
                }
            }

            //---set/map attributes section---//
            $member->last_name = substr($data[$insured_name_col], 0, 50); //put everything in the last name field.
            $member->mail_address_line_1 = $data[$mailing_address_col];
            $member->mail_city = $data[$mailing_city_col];
            $member->mail_state = $data[$mailing_state_col];
            $member->mail_zip = $data[$mailing_zip_col];
            $member->home_phone = $data[$home_phone_col];
            $member->work_phone = $data[$work_phone_col];
            $member->cell_phone = substr($data[$wds_contact_phone_1_col], 0, 25);
            $member->email_1 = $data[$email_addr_col];
            $member->email_2 = $data[$wds_contact_email_1_col];
            if(!empty($data[$producer_name_col]))
                $property->producer = $data[$producer_name_col]." ";
            if(!empty($data[$producer_phone_col]))
                $property->producer .= '(Ph: '.$data[$producer_phone_col].") ";
            if(!empty($data[$producer_code_col]))
                $property->producer .= '(Code: '.$data[$producer_code_col].")";
            $property->producer = substr($property->producer, 0, 100); //trim it up if too long
            $property->policy_effective = $data[$policy_eff_date_col];
            $property->address_line_1 = $data[$location_address_col];
            $property->city = $data[$location_city_col];
            $property->county = $data[$location_county_col];
            $property->state = $data[$location_state_col];
            $property->zip = $data[$location_zip_col];
            $property->agency_phone = $data[$producer_phone_col];
            $property->agency_code = $data[$producer_code_col];
            $property->agency_name = $data[$producer_name_col];
            if(!isset($property->comments))
                $property->comments = '';
            if(!empty($data[$location_unit_col]) && strpos($property->comments, 'Location Unit: '.$data[$location_unit_col]) === FALSE)
                $property->comments .= 'Location Unit: '.$data[$location_unit_col];
            if(!empty($data[$wds_gate_code_col]) && strpos($property->comments, 'Gate Code: '.$data[$wds_gate_code_col]) === FALSE)
                $property->comments .= 'Gate Code: '.$data[$wds_gate_code_col]."\n";
            if(!empty($data[$wds_misc_col]) && strpos($property->comments, 'WDS Misc: '.$data[$wds_misc_col]) === FALSE)
                $property->comments .= 'WDS Misc: '.$data[$wds_misc_col]."\n";

			//set default lat, Long etc -- March 2, 2020 - Removing lines 872 thru 879 - Mistakenly put into production, flipped all properties to unmatched.
			/**$property->lat = NULL;
			$property->long = NULL;	
			$property->wds_lat = NULL;
			$property->wds_long = NULL;
			$property->geog = NULL;
			$property->wds_geocode_level = 'unmatched'; **/
			$property->rated_company  = substr($ace_client->name,0,40) ;

            //setup default program and policy statuses
            if($member->isNewRecord)
            {
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }

            if($property->isNewRecord)
            {
                $property->pre_risk_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //if its in the PIF, then it's an active policy
            if(!$property->isNewRecord && $property->policy_status != 'active')
            {
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //enrolled props counter
            if($data[$wds_enrolled_col] == '1')
                $enrolled_props++;

            //if its a new property OR there has been no response status changes on the dashboard for this property, then set according to PIF. Otherwise leave repsonse status as is because what is set on dash trumps
            if($property->isNewRecord || !isset($property->currentWdsFireEnrollmentStatus))
            {
                if($data[$wds_enrolled_col] == '1')
                    $property->response_status = 'enrolled';
                else // == '0'
                    $property->response_status = 'not enrolled';

                $property->res_status_date = date('Y-m-d H:i:s');
            }

            //warning if response status is out of sync with pif
            if(!$property->isNewRecord)
            {
                if(($data[$wds_enrolled_col] == '1' && $property->response_status !== 'enrolled') ||
                   ($data[$wds_enrolled_col] == '0' && $property->response_status !== 'not enrolled'))
                {
                    print "Warning: Response Status does not match PIF list and was left as is because it was set in WDSFire dashboard which trumps.\n";
                    $results_action = 'Warning: Response Status does not match PIF list and was left as is because it was set in WDSFire dashboard which trumps.';
                }
            }

            //other required fields:
            $member->client_id = $ace_client->id;
            $property->client_id = $ace_client->id;
            $property->flag = 1;
            $member->client = 'Ace';

            //--------Save Routine-----------//
            if($member->save())
            {
                $property->member_mid = $member->mid; //now that we know the mid set it

                if(!$property->save()) //prop failed saving
                {
                    print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $results_action = 'Error: Could not import Policy info (PolicyHolder saved, but Policy did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    $data[$errors_col] = $results_action;
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else
                {
                    $contactsRecieved = array();

                    // Add contacts
                    //always add a special one to deal with the changing Insured Name on the member level problem
                    $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid, 'type'=>'name', 'priority'=>'Insured Name', 'name'=>$data[$insured_name_col], 'relationship'=>null, 'detail'=>'Policy Level Insured Name', 'notes'=>null));
                    //other contacts from pif list columns
                    if (!empty($data[$home_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$home_phone_col],'notes'=>null));
                    if (!empty($data[$work_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'work','priority'=>'Primary 2','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$work_phone_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 3','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_1_col],'notes'=>null));
                    if (!empty($data[$email_addr_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Primary 4','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$email_addr_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_1_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'phone','priority'=>'Secondary 2','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_2_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_3_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'phone','priority'=>'Secondary 3','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_3_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_4_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'phone','priority'=>'Secondary 4','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_4_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Tertiary 1','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_2_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_3_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Tertiary 2','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_3_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_4_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Tertiary 3','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_4_col],'notes'=>null));

                    // Remove any old contacts that were not recieved
                    $this->cleanContacts($property->pid, $contactsRecieved);
                }
            }
            else
            {
                print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $results_action = 'Error: Could not import PolicyHolder info. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                $data[$errors_col] = $results_action;
                fputcsv($error_fh, $data);
                $errors++;
            }

            //add to results file with the action that was done
            fputcsv($results_fh, array($member->member_num, $property->policy, $results_action));

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //run cancel routine
        $props_to_cancel = Property::model()->findAll("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND flag = 0 AND client_id = ".$ace_client->id." AND policy_status != 'canceled'");
        //loop through all Ace properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = count($props_to_cancel);
        $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
        foreach($props_to_cancel as $prop_to_cancel)
        {
            $results_action = 'Cancel';
            $prop_to_cancel->policy_status = 'canceled';
            $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
            if(!$prop_to_cancel->save())
            {
                print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true) . "\n";
                fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                $errors++;
            }
            else
            {
                //add to results file with the action that was done
                fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->member_mid, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
            }
        }

        //clean up and finish
        fclose($error_fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with ACE Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Imports the Fund Full PIF list (csv file)
     * Outputs a *_results.csv file for details of the import of each record.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importFundPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Firemans Fund PIF Import");

        //look up client
        $fund_client = Client::model()->findByAttributes(array('name'=>"Firemans Fund"));

        //Need to reset all Fund property flags (for cancel routine after import)
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Fund Import Flag Reset Routine.");
        $this->reset_property_flags($fund_client->id);
        $this->printUpdateStatus($fileToImport, "Processing", "Done with Fund Import Flag Reset Routine.");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //for some reason the account_id field has a stange character in it, need to trim it out
        $temp_header_fields = $header_fields;
        $i = 0;
        foreach($temp_header_fields as $temp_header)
        {
            if(strpos($temp_header, 'account_id'))
                $header_fields[$i] = 'account_id';
            $i++;
        }

        //check required and get col indexes
        $fields_to_check = array('account_id','policy_number','policy_eff_date','insured_name','mailing_address','mailing_city','mailing_state','mailing_zip','home_phone','work_phone','email_addr','producer_code',
            'producer_name','producer_phone','location_unit','location_number','location_address','location_city','location_county','location_state','location_zip','coverage_type','wds_enrolled','wds_contact_phone_1',
            'wds_contact_phone_2', 'wds_contact_phone_3', 'wds_contact_phone_4', 'wds_contact_email_1', 'wds_contact_email_2', 'wds_contact_email_3', 'wds_contact_email_4','wds_gate_code','wds_misc');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $account_id_col = array_search('account_id', $header_fields);
        $policy_number_col = array_search('policy_number', $header_fields);
        $policy_eff_date_col = array_search('policy_eff_date', $header_fields);
        $insured_name_col = array_search('insured_name', $header_fields);
        $mailing_address_col = array_search('mailing_address', $header_fields);
        $mailing_city_col = array_search('mailing_city', $header_fields);
        $mailing_state_col = array_search('mailing_state', $header_fields);
        $mailing_zip_col = array_search('mailing_zip', $header_fields);
        $home_phone_col = array_search('home_phone', $header_fields);
        $work_phone_col = array_search('work_phone', $header_fields);
        $email_addr_col = array_search('email_addr', $header_fields);
        $producer_code_col = array_search('producer_code', $header_fields);
        $producer_name_col = array_search('producer_name', $header_fields);
        $producer_phone_col = array_search('producer_phone', $header_fields);
        $location_unit_col = array_search('location_unit', $header_fields);
        $location_number_col = array_search('location_number', $header_fields);
        $location_address_col = array_search('location_address', $header_fields);
        $location_city_col = array_search('location_city', $header_fields);
        $location_county_col = array_search('location_county', $header_fields);
        $location_state_col = array_search('location_state', $header_fields);
        $location_zip_col = array_search('location_zip', $header_fields);
        $coverage_type_col = array_search('coverage_type', $header_fields);
        $wds_enrolled_col = array_search('wds_enrolled', $header_fields);
        $wds_contact_phone_1_col = array_search('wds_contact_phone_1', $header_fields);
        $wds_contact_phone_2_col = array_search('wds_contact_phone_2', $header_fields);
        $wds_contact_phone_3_col = array_search('wds_contact_phone_3', $header_fields);
        $wds_contact_phone_4_col = array_search('wds_contact_phone_4', $header_fields);
        $wds_contact_email_1_col = array_search('wds_contact_email_1', $header_fields);
        $wds_contact_email_2_col = array_search('wds_contact_email_2', $header_fields);
        $wds_contact_email_3_col = array_search('wds_contact_email_3', $header_fields);
        $wds_contact_email_4_col = array_search('wds_contact_email_4', $header_fields);
        $wds_gate_code_col = array_search('wds_gate_code', $header_fields);
        $wds_misc_col = array_search('wds_misc', $header_fields);

        //setup error file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('PolicyHolderNumber', 'PolicyNumber', 'ImportAction',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;
        $enrolled_props = 0;

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $skip = false;
            $results_action = '';

            //trim up the attributes
            $data = array_map('trim', $data);

            //set our primary mem and prop keys
            $member_num = $data[$account_id_col];
            $policy = $data[$policy_number_col];

            //check if member already exists
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$fund_client->id));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $member->member_num = $member_num;
                $property = new Property();
                $property->policy = $policy;
                $property->client_policy_id = $data[$policy_number_col];
                $new_members++;
                $new_properties++;
                $results_action = "Added New Policy Holder and New Policy. ";
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>'1'));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    $property->policy = $policy;
                    $property->client_policy_id = $data[$policy_number_col];
                    $new_properties++;
                    $results_action = "Updated Existing Policy Holder, Added New Policy. ";
                }
                else //property already existed
                {
                    $updated_properties++;
                    $results_action = "Updated Existing Policy Holder and Policy. ";
                }
            }

            //---set/map attributes section---//
            $member->last_name = $data[$insured_name_col]; //put everything in the last name field.
            $member->mail_address_line_1 = $data[$mailing_address_col];
            $member->mail_city = $data[$mailing_city_col];
            $member->mail_state = $data[$mailing_state_col];
            $member->mail_zip = $data[$mailing_zip_col];
            $member->home_phone = $data[$home_phone_col];
            $member->work_phone = $data[$work_phone_col];
            $member->cell_phone = substr($data[$wds_contact_phone_1_col], 0, 25);
            $member->email_1 = $data[$email_addr_col];
            $member->email_2 = $data[$wds_contact_email_1_col];
            if(!empty($data[$producer_name_col]))
                $property->producer = $data[$producer_name_col]." ";
            if(!empty($data[$producer_phone_col]))
                $property->producer .= '(Ph: '.$data[$producer_phone_col].") ";
            if(!empty($data[$producer_code_col]))
                $property->producer .= '(Code: '.$data[$producer_code_col].")";
            $property->producer = substr($property->producer, 0, 100); //trim it up if too long
            $property->policy_effective = $data[$policy_eff_date_col];
            $property->address_line_1 = $data[$location_address_col];
            $property->city = $data[$location_city_col];
            $property->county = $data[$location_county_col];
            $property->state = $data[$location_state_col];
            $property->zip = $data[$location_zip_col];

            if(!isset($property->comments))
                $property->comments = '';
            if(!empty($data[$location_unit_col]) && strpos($property->comments, 'Location Unit: '.$data[$location_unit_col]) === FALSE)
                $property->comments .= 'Location Unit: '.$data[$location_unit_col];
            if(!empty($data[$wds_gate_code_col]) && strpos($property->comments, 'Gate Code: '.$data[$wds_gate_code_col]) === FALSE)
                $property->comments .= 'Gate Code: '.$data[$wds_gate_code_col]."\n";
            if(!empty($data[$wds_misc_col]) && strpos($property->comments, 'WDS Misc: '.$data[$wds_misc_col]) === FALSE)
                $property->comments .= 'WDS Misc: '.$data[$wds_misc_col]."\n";

            //setup default program and policy statuses
            if($member->isNewRecord)
            {
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }

            if($property->isNewRecord)
            {
                $property->pre_risk_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->res_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //if its in the PIF, then it's an active policy
            if(!$property->isNewRecord && $property->policy_status != 'active')
            {
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //enrolled counter
            if($data[$wds_enrolled_col] == '1')
                $enrolled_props++;

            //if its a new property OR there has been no response status changes on the dashboard for this property, then set according to PIF. Otherwise leave repsonse status as is because what is set on dash trumps
            if($property->isNewRecord || !isset($property->currentWdsFireEnrollmentStatus))
            {
                if($data[$wds_enrolled_col] == '1')
                    $property->response_status = 'enrolled';
                else // == '0'
                    $property->response_status = 'not enrolled';
            }

            //warning if response status is out of sync with pif
            if(!$property->isNewRecord)
            {
                if(($data[$wds_enrolled_col] == '1' && $property->response_status !== 'enrolled') ||
                   ($data[$wds_enrolled_col] == '0' && $property->response_status !== 'not enrolled'))
                {
                    print "Warning: Response Status does not match PIF list and was left as is because it was set in WDSFire dashboard which trumps.\n";
                    $results_action = 'Warning: Response Status does not match PIF list and was left as is because it was set in WDSFire dashboard which trumps.';
                }
            }

            //other required fields:
            $member->client_id = $fund_client->id;
            $property->client_id = $fund_client->id;
            $member->client = "Firemans Fund";
            $property->flag = 1;

            //--------Save Routine-----------//
            if($member->save())
            {
                $property->member_mid = $member->mid; //now that we know the mid set it

                if(!$property->save()) //prop failed saving
                {
                    print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $results_action = 'Error: Could not import Policy info (PolicyHolder saved, but Policy did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    $data[$errors_col] = $results_action;
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else
                {
                    $contactsRecieved = array();

                    // Add contacts
                    if (!empty($data[$home_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$home_phone_col],'notes'=>null));
                    if (!empty($data[$work_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'work','priority'=>'Primary 2','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$work_phone_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 3','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_1_col],'notes'=>null));
                    if (!empty($data[$email_addr_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Primary 4','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$email_addr_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_1_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'phone','priority'=>'Secondary 2','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_2_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_3_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'phone','priority'=>'Secondary 3','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_3_col],'notes'=>null));
                    if (!empty($data[$wds_contact_phone_4_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'phone','priority'=>'Secondary 4','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_phone_4_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Tertiary 1','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_2_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_3_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Tertiary 2','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_3_col],'notes'=>null));
                    if (!empty($data[$wds_contact_email_4_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Tertiary 3','name'=>$data[$insured_name_col],'relationship'=>null,'detail'=>$data[$wds_contact_email_4_col],'notes'=>null));

                    // Remove any old contacts that were not recieved
                    $this->cleanContacts($property->pid, $contactsRecieved);
                }
            }
            else
            {
                print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $results_action = 'Error: Could not import PolicyHolder info. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                $data[$errors_col] = $results_action;
                fputcsv($error_fh, $data);
                $errors++;
            }

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //run cancel routine
        $props_to_cancel = Property::model()->findAll("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND flag = 0 AND client_id = ".$fund_client->id." AND policy_status != 'canceled'");
        //loop through all FF properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = count($props_to_cancel);
        $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
        foreach($props_to_cancel as $prop_to_cancel)
        {
            $results_action = 'Cancel';
            $prop_to_cancel->policy_status = 'canceled';
            $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
            if(!$prop_to_cancel->save())
            {
                print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true) . "\n";
                fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                $errors++;
            }
            else
            {
                //add to results file with the action that was done
                fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->member_mid, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
            }
        }

        //clean up and finish
        fclose($error_fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Fund Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Imports the MoE Full PIF list (csv file)  --March 2020 -- MOE moved to PIF 4
     * Outputs a *_results.csv file for details of the import of each record.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importMoEPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting MoE PIF Import");

        //openfile  
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('asofdate','policyholdernumber','policynumber','firstname','lastname','otherinsuredname','address','city','state','zip','propertyexposure','action','insuredphonenumber','insuredemailaddress','programstatus','forceprogramstatusflag','agencycode','agencyname');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $as_of_date_col = array_search('asofdate', $header_fields);
        $policy_holder_number_col = array_search('policyholdernumber', $header_fields);
        $policy_number_col = array_search('policynumber', $header_fields);
        $first_name_col = array_search('firstname', $header_fields);
        $last_name_col = array_search('lastname', $header_fields);
        $other_insured_name_col = array_search('otherinsuredname', $header_fields);
        $address_col = array_search('address', $header_fields);
        $city_col = array_search('city', $header_fields);
        $state_col = array_search('state', $header_fields);
        $zip_col = array_search('zip', $header_fields);
        $property_exposure_col = array_search('propertyexposure', $header_fields);
        $action_col = array_search('action', $header_fields);
        $insured_phone_number_col = array_search('insuredphonenumber', $header_fields);
        $insured_email_address_col = array_search('insuredemailaddress', $header_fields);
        $program_status_col = array_search('programstatus', $header_fields);
        $force_program_status_flag_col = array_search('forceprogramstatusflag', $header_fields);
        $agency_code = array_search('agencycode', $header_fields);
        $agency_name = array_search('agencyname', $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        //fputcsv($results_fh, array('PolicyHolderNumber', 'PolicyNumber', 'ImportAction',));
		$this->fputcsv_eol($results_fh, array('PolicyHolderNumber', 'PolicyNumber', 'ImportAction',),"\r\n");

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;

        //look up client
        $moe_client = Client::model()->findByAttributes(array('name'=>'Mutual of Enumclaw'));

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $skip_save = false;
            $results_action = '';

            //trim up the attributes
            $data = array_map('trim', $data);

            //set our primary mem and prop keys
            $member_num = $data[$policy_holder_number_col];
            $policy_array = explode('-', $data[$policy_number_col]);

            $policy = $policy_array[0];
            $location = $policy_array[1];

            //check if member already exists
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$moe_client->id));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $property = new Property();
                if($data[$action_col] == 'Cancel')
                {
                    $results_action = "Neither Policy Holder nor Policy existed in WDS database and Action was Cancel, so no import action was taken for this record.";
                    $property->policy_status = 'canceled';
                    $skip_save = true; //no need to save a canceled prop that didnt exist in the first place
                    $canceled_props++;
                }
                elseif($data[$action_col] == 'Add' || $data[$action_col] == 'Update')
                {
                    $member->member_num = $member_num;
                    $member->client = 'Mutual of Enumclaw';
                    $property->policy = $policy;
                    $property->client_policy_id = $data[$policy_number_col];
                    $property->location = $location;
                    $property->policy_status = 'active';
                    $new_members++;
                    $new_properties++;
                    $results_action = "Added New Policy Holder and New Policy. ";
                }
                else
                {
                    print 'Error: Action must be Add, Update, or Cancel. This record was not imported';
                    $results_action = 'Error: Action must be Add, Update, or Cancel. This record was not imported';
                    $errors++;
                    $skip_save = true;
                }
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>$location));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    if($data[$action_col] == 'Cancel')
                    {
                        $results_action = "Policy did not exist in WDS database and Action was Cancel, so no import action was taken for this record. ";
                        $property->policy_status = 'canceled';
                        $skip_save = true; //no need to save a cancel if it didn't exist in the first place
                        $canceled_props++;
                    }
                    elseif($data[$action_col] == 'Add' || $data[$action_col] == 'Update')
                    {
                        $property->policy = $policy;
                        $property->client_policy_id = $data[$policy_number_col];
                        $property->location = $location;
                        $property->policy_status = 'active';
                        $new_properties++;
                        $results_action = "Updated Existing Policy Holder, Added New Policy. ";
                    }
                    else
                    {
                        print 'Error: Action must be Add, Update, or Cancel. This record was not imported';
                        $results_action = 'Error: Action must be Add, Update, or Cancel. This record was not imported';
                        $errors++;
                        $skip_save = true;
                    }
                }
                else //property already existed
                {
                    if($data[$action_col] == 'Cancel')
                    {
                        $results_action = "Policy was set to Canceled Status. ";
                        $canceled_props++;
                        if($property->policy_status == 'canceled') //if it is already canceled
                        {
                            $results_action = "Policy was already set to Canceled Status. ";
                        }
                        else //switching to canceled so update the date
                            $property->policy_status_date = date('Y-m-d H:i:s');

                        $property->policy_status = 'canceled';
                    }
                    elseif($data[$action_col] == 'Add' || $data[$action_col] == 'Update')
                    {
                        $updated_properties++;
                        $results_action = "Updated Existing Policy Holder and Policy. ";
                        if($property->policy_status !== 'active') //if it is not active, need to update the date
                            $property->policy_status_date = date('Y-m-d H:i:s');
                        $property->policy_status = 'active';

                    }
                    else
                    {
                        print 'Error: Action must be Add, Update, or Cancel. This record was not imported';
                        $results_action = 'Error: Action must be Add, Update, or Cancel. This record was not imported';
                        $errors++;
                        $skip_save = true;
                    }
                }
            }

            //---set/map attributes section---//
            //attributes:  as_of_date, policy_holder_number, policy_number, first_name, last_name, other_insured_name, address, city, state, zip, property_exposure, action
            $member->first_name = substr($data[$first_name_col], 0, 50);
            $member->last_name = substr($data[$last_name_col], 0, 50);
            $member->spouse_last_name = substr($data[$other_insured_name_col], 0, 50);
            $member->home_phone = $data[$insured_phone_number_col];
            $member->email_1 = $data[$insured_email_address_col];
            $property->address_line_1 = $data[$address_col];
            $property->city = $data[$city_col];
            $property->state = $data[$state_col];
            $property->zip = $data[$zip_col];
            $property->coverage_a_amt = (int)$data[$property_exposure_col];
            $property->agency_code = (int)$data[$agency_code];
            $property->agency_name = $data[$agency_name];

			//set default lat, Long etc
			$property->lat = NULL;
			$property->long = NULL;	
			$property->wds_lat = NULL;
			$property->wds_long = NULL;
			$property->geog = NULL;
			$property->wds_geocode_level = 'unmatched';
			$property->rated_company  = substr($moe_client->name,0,40) ;

            //setup default program and policy statuses
            if($member->isNewRecord)
            {
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }

            if($property->isNewRecord)
            {
                $property->pre_risk_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->response_status = strtolower($data[$program_status_col]);
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->res_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status_date = date('Y-m-d H:i:s');
            }
            else //existing prop
            {
                //if there has already been a dashboard set of response status, then leave it, UNLESS the override column is set to yes
                if(!isset($property->currentWdsFireEnrollmentStatus) || $data[$force_program_status_flag_col] == 'yes')
                {
                    if($property->response_status !== strtolower($data[$program_status_col])) //only update it if it's changed
                    {
                        $property->response_status = strtolower($data[$program_status_col]);
                        $property->res_status_date = date('Y-m-d H:i:s');
                    }
                }
            }

            //other required fields:
            $member->client_id = $moe_client->id;
            $property->client_id = $moe_client->id;
            $member->client = 'Mutual of Enumclaw';

            //--------Save Routine-----------//
            if(!$skip_save)
            {
                if($member->save())
                {
                    $property->member_mid = $member->mid; //now that we know the mid set it

                    if(!$property->save()) //prop failed saving
                    {
                        print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                        $results_action = 'Error: Could not import Policy info (PolicyHolder saved, but Policy did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        $errors++;
                    }
                    else
                    {
                        $contactsRecieved = array();

                        $insured_name = '';
                        if (!empty($member->first_name))
                            $insured_name .= trim($member->first_name) . ' ';
                        $insured_name .= trim($member->last_name);

                        // Add contacts
                        if (!empty($data[$insured_phone_number_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$insured_phone_number_col],'notes'=>null));
                        if (!empty($data[$insured_email_address_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Primary 2','name'=>$insured_name,'relationship'=>null,'detail'=>$data[$insured_email_address_col],'notes'=>null));

                        // Remove any old contacts that were not recieved in this list
                        $this->cleanContacts($property->pid, $contactsRecieved);
                    }
                }
                else
                {
                    print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $results_action = 'Error: Could not import PolicyHolder info. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                    $errors++;
                }
            }

            //add to results file with the action that was done
            //fputcsv($results_fh, array($member->member_num, $property->policy, $results_action));
            $this->fputcsv_eol($results_fh, array($member->member_num, $property->policy, trim($results_action)), "\r\n");

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //clean up and finish
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with MOE Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Special function for MoE results file to use to write csvs with windows line endings
     * Writes an array to an open CSV file with a custom end of line
     *
     * @param File $fp: file pointer
     * @param array $array the row data
     * @param string $eol what to use as your eol, probably one of "\r\n", "\n"
     */
    private function fputcsv_eol($fp, $array, $eol) {
        fputcsv($fp, $array);
        if("\n" != $eol && 0 === fseek($fp, -1, SEEK_CUR)) {
            fwrite($fp, $eol);
        }
    }

    /**
     * Imports the Pharmacists Full PIF list (csv file)
     * Outputs a *_results.csv file for details of the import of each record.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importPharmPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Pharm PIF Import");

        //get client
        $pharm_client = Client::model()->findByAttributes(array('name'=>'Pharmacists'));

        //Need to reset all pharm property flags (for cancel routine after import)
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Pharm Import Flag Reset Routine.");
        $this->reset_property_flags($pharm_client->id);
        $this->printUpdateStatus($fileToImport, "Processing", "Done with Pharm Import Flag Reset Routine.");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('custno','policy','loc','insured','address','city','st','zip','coverage','effdate','expdate','email1','email2','phone1','phone2','wds_enrolled');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $custno_col = array_search('custno', $header_fields);
        $policy_col = array_search('policy', $header_fields);
        $loc_col = array_search('loc', $header_fields);
        $insured_col = array_search('insured', $header_fields);
        $address_col = array_search('address', $header_fields);
        $city_col = array_search('city', $header_fields);
        $st_col = array_search('st', $header_fields);
        $zip_col = array_search('zip', $header_fields);
        $coverage_col = array_search('coverage', $header_fields);
        $effdate_col = array_search('effdate', $header_fields);
        $expdate_col = array_search('expdate', $header_fields);
        $email1_col = array_search('email1', $header_fields);
        $email2_col = array_search('email2', $header_fields);
        $phone1_col = array_search('phone1', $header_fields);
        $phone2_col = array_search('phone2', $header_fields);
        $wds_enrolled = array_search('wds_enrolled', $header_fields);
        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('wds_pid', 'wds_mid', 'Policy Holder #', 'Policy #', 'Import Action',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $skip = false;
            $results_action = '';

            //trim up the attributes
            $data = array_map('trim', $data);

            $member_num = $data[$custno_col];
            $policy = $data[$policy_col];

            //check if member already exists
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client'=>'Pharmacists'));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $member->member_num = $member_num;
                $member->client = 'Pharmacists';
                $property = new Property();
                $property->policy = $policy;
                $property->client_policy_id = $data[$policy_col];
                $new_members++;
                $new_properties++;
                $results_action = "Added New Policy Holder and New Policy. ";
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>$data[$loc_col]));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    $property->policy = $policy;
                    $property->client_policy_id = $data[$policy_col];
                    $new_properties++;
                    $results_action = "Updated Existing Policy Holder, Added New Policy. ";
                }
                else //property already existed
                {
                    $updated_properties++;
                    $results_action = "Updated Existing Policy Holder and Policy. ";
                }
            }

            //---set/map attributes section---//
            //attributes: 'custno','policy','loc','insured','address','city','st','zip','coverage','effdate','expdate','email1','email2','phone1','phone2'
            $exploded_name = explode(' ',$data[$insured_col]);
            $member->first_name = $exploded_name[0];
            //$member->last_name = $exploded_name[count($exploded_name)-1];
            $member->last_name = $data[$insured_col];
            $property->location = $data[$loc_col];
            $property->address_line_1 = $data[$address_col];
            $property->city = $data[$city_col];
            $property->state = $data[$st_col];
            $property->zip = $data[$zip_col];
            $property->coverage_a_amt = (int)$data[$coverage_col];
            $property->policy_effective = $data[$effdate_col];
            $property->policy_expiration = $data[$expdate_col];
            if(!empty($data[$phone1_col]))
                $member->home_phone = $data[$phone1_col];
            if(!empty($data[$phone2_col]))
                $member->cell_phone = $data[$phone2_col];
            if(!empty($data[$email1_col]))
                $member->email_1 = substr($data[$email1_col],0,50);
            if(!empty($data[$email2_col]))
                $member->email_2 = substr($data[$email2_col],0,50);

			//set default lat, Long etc -- March 2, 2020 This may have been added to production by mistake. Removing lines 1811 thru 1816
			/**$property->lat = NULL;
			$property->long = NULL;	
			$property->wds_lat = NULL;
			$property->wds_long = NULL;
			$property->geog = NULL;
			$property->wds_geocode_level = 'unmatched'; **/
			$property->rated_company  = substr($pharm_client->name,0,40) ;

            if(substr($policy, 0, 3) === 'BOP')
            {
                $property->lob = 'BOP';
                $property->wds_lob = 'BUS';
            }
            else
            {
                $property->lob = 'HOM';
                $property->wds_lob = 'HOM';
                if($data[$wds_enrolled] == 0)
                {
                    $property -> response_status = 'not enrolled';  
                }
                else if($data[$wds_enrolled] == 1)
                {
                    $property -> response_status = 'enrolled';  
                }
            }

            if($member->isNewRecord)
            {
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }

            if($property->isNewRecord)
            {
                $property->pre_risk_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                if($property->lob === 'BOP')
                    $property->response_status = 'ineligible';
                else if ($property->lob === 'HOM' || $property->wds_lob === 'HOM')
                {
                    if($data[$wds_enrolled] == 0)
                    {
                        $property -> response_status = 'not enrolled';  
                    }
                    else if($data[$wds_enrolled] == 1)
                    {
                        $property -> response_status = 'enrolled';  
                    }
                }
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->res_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status_date = date('Y-m-d H:i:s');
            }
			$property->policy_status = 'active'; //policy status is always active if in this file

            //set client_ids
            $member->client_id = $pharm_client->id;
            $property->client_id = $pharm_client->id;

            //--------Save Routine-----------//
            if($member->save())
            {
                $property->member_mid = $member->mid;
                $property->flag = 1;

                if(!$property->save()) //prop failed saving
                {
                    print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Could not import property for member (member saved, but prop did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else //prop saved correctly
                {
                    //add to results file with the action that was done
                    fputcsv($results_fh, array($property->pid, $member->mid, $member->member_num, $property->policy, $results_action));

                    $contactsRecieved = array();

                    // Add contacts
                    if (!empty($data[$phone1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>$data[$insured_col],'relationship'=>null,'detail'=>$data[$phone1_col],'notes'=>null));
                    if (!empty($data[$phone2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 2','name'=>$data[$insured_col],'relationship'=>null,'detail'=>$data[$phone2_col],'notes'=>null));
                    if (!empty($data[$email1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>$data[$insured_col],'relationship'=>null,'detail'=>$data[$email1_col],'notes'=>null));
                    if (!empty($data[$email2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 2','name'=>$data[$insured_col],'relationship'=>null,'detail'=>$data[$email2_col],'notes'=>null));

                    // Remove any old contacts that were not recieved in this list
                    $this->cleanContacts($property->pid, $contactsRecieved);
                }
            }
            else
            {
                print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $data[$errors_col] = 'Could not import member details (neither member nor prop saved). Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                fputcsv($error_fh, $data);
                $errors++;
            }

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //run cancel routine
        $props_to_cancel = Property::model()->findAll("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND flag = 0 AND client_id = ".$pharm_client->id." AND policy_status != 'canceled'");
        //loop through all Nationwide properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = count($props_to_cancel);
        $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
        foreach($props_to_cancel as $prop_to_cancel)
        {
            $results_action = 'Cancel';
            $prop_to_cancel->policy_status = 'canceled';
            $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
            if(!$prop_to_cancel->save())
            {
                print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true) . "\n";
                fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                $errors++;
            }
            else
            {
                //add to results file with the action that was done
                fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->member_mid, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
            }
        }

        //clean up and finish
        fclose($error_fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Pharm Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Runs an audit based on a csv file sent Bi-Yearly by USAA
     * Outputs a *_results.csv file that details the audit results for each record
     * NOTE: sets flags so that a mutual exclusion sql query can be run to determine Active properties that were not in the Audit file.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function usaaAudit($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting USAA Audit");

        //look up client
        $usaa_client = Client::model()->findByAttributes(array('name'=>'USAA'));
        //reset flags on all usaa props
        $this->reset_property_flags($usaa_client->id);

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('member_num', 'policy', 'lob', 'state', 'zip', 'eff_date', 'exp_date', 'exists_in_wds_db', 'wds_policy_status', 'response_status', 'state_check', 'zip_check', 'lob_check', 'eff_date_check', 'exp_date_check'));

        //index variables to track progress/results
        $counter = 0;

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;

            //trim them up and lowercase them
            $data = array_map('trim', $data);

            //pull data into vars
            $member_num = $data[0];
            $policy = $data[1];
            $lob = $data[2];
            $state = trim($data[3]);
            $zip = $data[4];
            $eff_date = $data[5];
            $exp_date = $data[6];

            //look up mem and prop
            //NOTE, NEW USAA FILES HAVE LEADING 0's on member number, need to strip them cause wds db doesn't have them
            $member_num = ltrim($member_num, '0');
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client'=>'USAA'));
            if(!isset($member)) //didn't find member,
            {
                $exists_in_wds_db = '0';
                $wds_policy_status = 'n/a';
                $wds_response_status = 'n/a';
                $state_check = 'n/a';
                $zip_check = 'n/a';
                $lob_check = 'n/a';
                $eff_date_check = 'n/a';
                $exp_date_check = 'n/a';
            }
            else //found member
            {
                //check if property exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy, 'location'=>'1'));
                if(!isset($property)) //didnt find property
                {
                    $exists_in_wds_db = '0';
                    $wds_policy_status = 'n/a';
                    $state_check = 'n/a';
                    $zip_check = 'n/a';
                    $lob_check = 'n/a';
                    $eff_date_check = 'n/a';
                    $exp_date_check = 'n/a';
                }
                else //found property
                {
                    $exists_in_wds_db = '1';
                    $wds_policy_status = $property->policy_status;
                    $wds_response_status = $property->response_status;

                    if($state == $property->state)
                        $state_check = '1';
                    else
                        $state_check = '0';

                    if($zip == $property->zip)
                        $zip_check = '1';
                    else
                        $zip_check = '0';

                    if($lob == $property->lob)
                        $lob_check = '1';
                    else
                        $lob_check = '0';

                    $data_eff_date = date_format(date_create_from_format('Y-m-d', $eff_date), 'm/d/Y'); //put date in format of database
                    $prop_eff_date = substr($property->policy_effective, 0, 10); //strip off all the time part of datetime from the db
                    if($data_eff_date == $prop_eff_date)
                        $eff_date_check = '1';
                    else
                        $eff_date_check = '0';

                    $data_exp_date = date_format(date_create_from_format('Y-m-d', $exp_date), 'm/d/Y'); //put date in format of database
                    $prop_exp_date = substr($property->policy_expiration, 0, 10); //strip off all the time part of datetime from the db
                    if($data_exp_date == $prop_exp_date)
                        $exp_date_check = '1';
                    else
                        $exp_date_check = '0';

                    //set flag = 1 so that we can do some mutual exclusion checks by hand afterwards
                    $command = Yii::app()->db->createCommand("UPDATE properties SET flag = 1 WHERE pid = ".$property->pid);
                    $command->execute();
                }

            }

            //add to results file with the results ONLY if there was some sort of mismatch or it wasn't an active property
            if($exists_in_wds_db === '0' || $lob_check === '0' || $zip_check === '0' || $state_check === '0' || $wds_policy_status !== 'active' || $eff_date_check === '0' || $exp_date_check === '0')
                fputcsv($results_fh, array($member_num, $policy, $lob, $state, $zip, $eff_date, $exp_date, $exists_in_wds_db, $wds_policy_status, $wds_response_status, $state_check, $zip_check, $lob_check, $eff_date_check, $exp_date_check));

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Audited $counter rows so far.");

        } //end main loop

        fclose($fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with USAA Audit. Processed $counter rows.");
    }

    /**
     * Imports the Nationwide PIF list files. They come in 2 parts, one that is for Response eligible and one for Response enrolled
     * NOTE: They need to be run in order Eligible, then Enrolled so that the cancel routine works correctly
     * Outputs a *_results.csv file for details of the import of each record.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importNWPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Nationwide PIF Import");

        //look up client
        $nationwide_client = Client::model()->findByAttributes(array('name'=>'Nationwide'));

        //Eligible file should be imported first, and if so need to reset all Nationwide property flags (for cancel routine after Enroll import)
        if($fileToImport['type'] == 'Nationwide - Eligible PIF Import')
        {
            $this->printUpdateStatus($fileToImport, "Processing", "Starting NW Import Flag Reset Routine.");
            $this->reset_property_flags($nationwide_client->id);
            $this->printUpdateStatus($fileToImport, "Processing", "Done with NW Import Flag Reset Routine.");
        }

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('record action', 'agent number', 'agent name', 'agent state', 'policy number', 'policy version', 'policy status', 'eff date', 'exp date', 'line of business', 'customer name', 'customer number', 'insured given name', 'insured surname', 'insured other given name', 'insured joint given name', 'insured joint surname', 'insured joint other given name', 'insured phone number', 'location addr 1', 'location addr 2', 'location city', 'location state', 'location zip', 'location county', 'cova', 'brushhazardscore', 'latitude', 'longitude');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $action_col = array_search('record action', $header_fields);
        $agent_num_col = array_search('agent number', $header_fields);
        $agent_name_col = array_search('agent name', $header_fields);
        $agent_state_col = array_search('agent state', $header_fields);
        $policy_num_col = array_search('policy number', $header_fields);
        $policy_version_col = array_search('policy version', $header_fields);
        $policy_status_col = array_search('policy status', $header_fields);
        $eff_date_col = array_search('eff date', $header_fields);
        $exp_date_col = array_search('exp date', $header_fields);
        $lob_col = array_search('line of business', $header_fields);
        $customer_name_col = array_search('customer name', $header_fields);
        $customer_num_col = array_search('customer number', $header_fields);
        $first_name_col = array_search('insured given name', $header_fields);
        $last_name_col = array_search('insured surname', $header_fields);
        $middle_name_col = array_search('insured other given name', $header_fields);
        $spouse_first_name_col = array_search('insured joint given name', $header_fields);
        $spouse_last_name_col = array_search('insured joint surname', $header_fields);
        $spouse_middle_name_col = array_search('insured joint other given name', $header_fields);
        $phone_num_col = array_search('insured phone number', $header_fields);
        $address_1_col = array_search('location addr 1', $header_fields);
        $address_2_col = array_search('location addr 2', $header_fields);
        $city_col = array_search('location city', $header_fields);
        $state_col = array_search('location state', $header_fields);
        $zip_col = array_search('location zip', $header_fields);
        $county_col = array_search('location county', $header_fields);
        $cov_a_col = array_search('cova', $header_fields);
        $brushfire_score_col = array_search('brushhazardscore', $header_fields);
        $latitude_col = array_search('latitude', $header_fields);
        $longitude_col = array_search('longitude', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('wds_pid', 'wds_mid', 'Customer #', 'Policy #', 'Import Action',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;
        $results_action = '';

        //get client
        $nw_client = Client::model()->findByAttributes(array('name'=>'Nationwide'));

        //loop through whole file and put it in an array so we can sort it
        $data_array = array();
        $index = 0;
        while($temp_data_row = fgetcsv($fh))
        {
            if($temp_data_row[$action_col] == 'Delete')
                $order_by = '1';
            elseif($temp_data_row[$action_col] == 'Add')
                $order_by = '2';
            elseif($temp_data_row[$action_col] == 'Change')
                $order_by = '3';
            else //this should not happen
                $order_by = '4';
            //load up the data array with the action (as an assined order_by number above) as the key and the row data as the value
            $data_array[$order_by.'-'.$index] = $temp_data_row;
            $index++;
        }
        fclose($fh);
        //sort the array putting the 'Delete's on top and the 'Add's and 'Update's on bottom
        ksort($data_array);

        //Main Loop to go through each row in the import file and process it
        foreach($data_array as $data)
        {
            $counter++;
            $skip = false;

            //trim up the attributes
            $data = array_map('trim', $data);

            $member_num = $data[$customer_num_col];
            $policy_num = $data[$policy_num_col];
            $action = $data[$action_col];
            if($data[$lob_col]=='Condominium' || $data[$lob_col]=='Homeowners' || $data[$lob_col]=='Tenant' || $data[$lob_col]=='Home') //do further checks and setup if passes these highlevel qualifiers
            {
                //check if member already exists
                $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client'=>'Nationwide'));
                if(!isset($member)) //didn't find member, so make new one and a new property
                {
                    if($action == 'Add' || $action == 'Change')
                    {
                        $member = new Member();
                        $member->member_num = $member_num;
                        $member->client = 'Nationwide';
                        $property = new Property();
                        $property->policy = $policy_num;
                        $property->client_policy_id = $data[$policy_num_col];
                        $new_members++;
                        $new_properties++;
                        $results_action = "Add";
                        if($action == 'Change')
                            $results_action = "Warning: Record Action was 'Change' but the Member(PolicyHolder) did not exist. A new Member(PolicyHolder) and new Property(Policy) were created with this info instead.";
                    }
                    elseif($action == 'Delete')
                    {
                        $results_action = "Warning: Record Action was 'Delete' but the member/policyholder (and thus none of his properties/policies) did not exist in the first place. No database updates were done.";
                        $skip = true;
                    }
                    else //action not accounted for
                    {
                        $results_action = "ERROR: Record Action was ".$action." WHICH IS NOT AN ACTION THAT IS ACCOUNTED FOR IN THIS IMPORT SCRIPT! SKIPPING!";
                        $skip = true;
                    }
                }
                else //existing member
                {
                    if($action == 'Add' || $action == 'Change')
                    {
                        $updated_members++;
                        //check if property already exists for member
                        $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy_num, 'location'=>'1'));
                        if(!isset($property)) //didnt find property, so make new one
                        {
                            $property = new Property();
                            $property->policy = $policy_num;
                            $property->client_policy_id = $policy_num_col;
                            $new_properties++;
                            $results_action = "Add";
                            if($action == 'Change')
                                $results_action = "Warning: Record Action was 'Change' but the property/policy did NOT exist in the first place. A Property(Policy) was created with this info instead.";
                        }
                        else //property already existed
                        {
                            $updated_properties++;
                            $results_action = "Update";
                            if($action == 'Add')
                                $results_action = "Warning: Record Action was 'Add' but the property/policy already existed. The existing property was updated with this info instead";
                        }
                    }
                    elseif($action == 'Delete')
                    {
                        $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy_num, 'location'=>'1'));
                        if(!isset($property)) //didnt find property
                        {
                            $results_action = "Warning: Record Action was 'Delete' but the property/policy for the member did not exist in the first place (mem existed, just not the prop). No database updates were done.";
                            $skip = true;
                        }
                        else //found property to cancel
                        {
                            $canceled_props++;
                            $results_action = "Cancelled";
                        }
                    }
                    else //action not accounted for
                    {
                        $results_action = "ERROR: Record Action was ".$action." WHICH IS NOT AN ACTION THAT IS ACCOUNTED FOR IN THIS IMPORT SCRIPT! SKIPPING!";
                        $skip = true;
                    }
                }
            }
            else
            {
                $skip = true;
                $results_action = "Warning: Did NOT import this row because it was not a LOB=".$data[$lob_col].". No database updates were done.";
            }
            
            if(!$skip)
            {
                //---set/map attributes section---//
                if(!empty($data[$agent_name_col]))
                    $property->producer = $data[$agent_name_col]." ";
                if(!empty($data[$agent_num_col]))
                    $property->producer .= '(agent #: '.$data[$agent_num_col].") ";
                if(!empty($data[$agent_state_col]))
                    $property->producer .= '(agent state: '.$data[$agent_state_col].")";
                if(strlen($property->producer) > 100)
                    $property->producer = substr($property->producer, 0, 100);
                $property->agency_code = substr($data[$agent_num_col], 0, 25);
                $property->agency_name = substr($data[$agent_name_col], 0, 100);
                $property->comments = '';
                $property->comments .= 'Customer Full Name: '.$data[$customer_name_col]."\n";
                if(!empty($data[$policy_version_col]))
                    $property->comments .= "CRB Policy Version: ".$data[$policy_version_col]."\n";
                if(!empty($data[$policy_status_col]))
                    $property->comments .= "CRB Policy Status: ".$data[$policy_status_col]."\n";
                if($data[$eff_date_col] != "00:00.0") //temp hack till CRB fixes this column
                    $property->policy_effective = $data[$eff_date_col];
                if($data[$exp_date_col] != "00:00.0") //temp hack till CRB fixes this column
                    $property->policy_expiration = $data[$exp_date_col];
                $property->lob = $data[$lob_col];
                $member->first_name = $data[$first_name_col];
                $member->last_name = $data[$last_name_col];
                $member->middle_name = $data[$middle_name_col];
                $member->spouse_first_name = $data[$spouse_first_name_col];
                $member->spouse_last_name = $data[$spouse_last_name_col];
                $member->spouse_middle_name = $data[$spouse_middle_name_col];
                $member->home_phone = $data[$phone_num_col];
                $property->address_line_1 = $data[$address_1_col];
                $property->address_line_2 = $data[$address_2_col];
                $property->city = $data[$city_col];
                $property->state = $data[$state_col];
                $property->zip = $data[$zip_col];
                $property->county = $data[$county_col];
                $property->coverage_a_amt = (int)$data[$cov_a_col];
                $property->brushfire_inspect = $data[$brushfire_score_col];

                $property->comments = mb_convert_encoding($property->comments, 'UTF-8');

                //if(!(isset($property->geocode_level) && $property->geocode_level == 'WDS')) //if WDS already set this, then leave it be, otherwise set it
                //{
                   // $property->lat = $data[$latitude_col];
                    //$property->long = $data[$longitude_col];
                   // $property->geocode_level = '';
                //}

				//Set Invalid/Null lat, Long
                $property->lat = NULL;
				$property->long = NULL;	
				$property->rated_company  = substr($nationwide_client->name,0,40) ;
                if($member->isNewRecord)
                {
                    $member->mem_fireshield_status = 'not enrolled';
                    $member->mem_fs_status_date = date('Y-m-d H:i:s');
                }
                else //if not a new member
                {
                    if($property->isNewRecord) //but is a new prop, look for another one with same addy
                    {
                        //check if there is a same address property under this member
                        $other_props = Property::model()->findAllByAttributes(array('member_mid'=>$member->mid));
                        foreach($other_props as $other_prop)
                        {
                            if($other_prop->address_line_1 == $property->address_line_1)
                                $results_action = 'ReWrite';
                        }
                    }
                }

                if($property->isNewRecord)
                {
                    $property->pre_risk_status = 'not enrolled';
                    $property->fireshield_status = 'not enrolled';
                    $property->fs_status_date = date('Y-m-d H:i:s');
                    $property->res_status_date = date('Y-m-d H:i:s');
                    $property->pr_status_date = date('Y-m-d H:i:s');
                    $property->policy_status = 'active';
                    $property->policy_status_date = date('Y-m-d H:i:s');
					$property->wds_lat = NULL;
					$property->wds_long = NULL;
					$property->geog = NULL;
					$property->wds_geocode_level = 'unmatched';
                }
                elseif($action != 'Delete' && $property->policy_status != 'active')
                {
                    $property->policy_status = 'active';
                    $property->policy_status_date = date('Y-m-d H:i:s');
                }
                elseif($action == 'Delete')
                {
                    $property->policy_status = 'canceled';
                    $property->policy_status_date = date('Y-m-d H:i:s');
                }

                if($fileToImport['type'] == 'Nationwide - Eligible PIF Import')
                    $property->response_status = 'not enrolled';
                else
                    $property->response_status = 'enrolled';

                //set client_ids
                $property->client_id = $nw_client->id;
                $member->client_id = $nw_client->id;

                //--------Save Routine-----------//
                if($member->save())
                {
                    $property->member_mid = $member->mid;
                    $property->flag = 1;

                    if(!$property->save()) //prop failed saving
                    {
                        print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                        $data[$errors_col] = 'Could not import property for member (member saved, but prop did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        fputcsv($error_fh, $data);
                        $errors++;
                    }
                    else //prop saved correctly
                    {
                        //add to results file with the action that was done
                        fputcsv($results_fh, array($property->pid, $member->mid, $member->member_num, $property->policy, $results_action));

						//fetch last inserted property id after property save
						$pid_lastID = $property->pid;
						if($property->pid == '')
						{
							$lastproperty= Yii::app()->db->createCommand('select top 1* from properties order by pid desc')->queryAll();
							$pid_lastID = $lastproperty[0]['pid'];
						}
						$this->setGeoSpatial($pid_lastID, $data[$latitude_col], $data[$longitude_col]);

                        $contactsRecieved = array();

                        // Add contacts
                        if (!empty($data[$phone_num_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>"$data[$first_name_col] $data[$last_name_col]",'relationship'=>null,'detail'=>$data[$phone_num_col],'notes'=>null));

                        // Remove any old contacts that were not recieved in this list
                        $this->cleanContacts($property->pid, $contactsRecieved);
                    }
                }
                else
                {
                    print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Could not import member details (neither member nor prop saved). Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                }
            } //end don't skip if
            else
            { //Skipped
                //add to results file with the action that was done
                fputcsv($results_fh, array('n/a', 'n/a', $data[$customer_num_col], $data[$policy_num_col], $results_action));
            }

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");
        } //end main loop

        //if Enrolled file, then run cancel routine
        if($fileToImport['type'] == 'Nationwide - Enrolled PIF Import')
        {
            $props_to_cancel = Property::model()->findAll("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND flag = 0 AND client_id = ".$nationwide_client->id." AND policy_status != 'canceled'");
            //loop through all Nationwide properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
            $canceled_props = count($props_to_cancel);
            $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
            foreach($props_to_cancel as $prop_to_cancel)
            {
                $results_action = 'Cancel';
                $other_props = Property::model()->findAllByAttributes(array('member_mid'=>$prop_to_cancel->member_mid, 'policy_status'=>'active'));
                foreach($other_props as $other_prop)
                {
                    if($other_prop->address_line_1 == $prop_to_cancel->address_line_1 && $prop_to_cancel->pid != $other_prop->pid)
                        $results_action = 'Cancel-ReWrite';
                }

                $prop_to_cancel->policy_status = 'canceled';
                $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
                if(!$prop_to_cancel->save())
                {
                    print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true) . "\n";
                    fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                    $errors++;
                }
                else
                {
                    //add to results file with the action that was done
                    fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->member_mid, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
                }
            }
        }

        //clean up and finish
        fclose($error_fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Nationwide Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Imports a standard csv file with the required headers into the PreRisk table.
     * Outputs an *_error.csv file that includes details on any records that could not be imported.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importPRCallList($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting PR Call List Import script");

        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('engine', 'week_to_schedule', 'call_center_comments', 'call_list_year', 'call_list_month', 'received_date_of_list', 'assignment_date_start', 'status', 'pid');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $engine_col = array_search('engine', $header_fields);
        $week_to_schedule_col = array_search('week_to_schedule', $header_fields);
        $call_center_comments_col = array_search('call_center_comments', $header_fields);
        $call_list_year_col = array_search('call_list_year', $header_fields);
        $call_list_month_col = array_search('call_list_month', $header_fields);
        $received_date_of_list_col = array_search('received_date_of_list', $header_fields);
        $assignment_date_start_col = array_search('assignment_date_start', $header_fields);
        $status_col = array_search('status', $header_fields);
        $pid_col = array_search('pid', $header_fields);
        //$ha_date_col = array_search('ha_date', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //index variables to track progress/results
        $counter = 0;
        $new_pr_entries = 0;
        $errors = 0;

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $error = false;
            $errorMsg = 'Error: ';
            $counter++;
            //trim up the attributes
            $data = array_map('trim', $data);

            //New record (insert)
            $preRisk = new PreRisk();

            //Assign values from csv to columns in database
            if(empty($data[$engine_col]))
            {
                $error = true;
                $errorMsg .= 'Engine value cannot be empty! ';
            }
            else
                $preRisk->engine = $data[$engine_col];

            if(empty($data[$week_to_schedule_col]))
            {
                $error = true;
                $errorMsg .= 'Week_to_schedule value cannot be empty! ';
            }
            else
                $preRisk->week_to_schedule = date("Y-m-d H:i:s", strtotime($data[$week_to_schedule_col]));

            $preRisk->call_center_comments = $data[$call_center_comments_col];

            if(empty($data[$call_list_year_col]))
            {
                $error = true;
                $errorMsg .= 'Call_list_year value cannot be empty! ';
            }
            else
                $preRisk->call_list_year = $data[$call_list_year_col];

            if(empty($data[$call_list_month_col]))
            {
                $error = true;
                $errorMsg .= 'Call_list_month value cannot be empty! ';
            }
            else
                $preRisk->call_list_month = strtoupper($data[$call_list_month_col]);

            if(empty($data[$received_date_of_list_col]))
            {
                $error = true;
                $errorMsg .= 'Received_date_of_list value cannot be empty! ';
            }
            else
                $preRisk->received_date_of_list = date("Y-m-d H:i:s", strtotime($data[$received_date_of_list_col]));

            if(empty($data[$assignment_date_start_col]))
            {
                $error = true;
                $errorMsg .= 'Assignment_date_start value cannot be empty! ';
            }
            else
                $preRisk->assignment_date_start = date("Y-m-d H:i:s", strtotime($data[$assignment_date_start_col]));

            if(empty($data[$status_col]))
            {
                $error = true;
                $errorMsg .= 'Status value cannot be empty! ';
            }
            else
                $preRisk->status = strtoupper($data[$status_col]);

            $prop = Property::model()->findByPk($data[$pid_col]);
            if(empty($data[$pid_col]))
            {
                $error = true;
                $errorMsg .= 'Pid value cannot be empty! ';
            }
            else if(!isset($prop))
            {
                $error = true;
                $errorMsg .= 'Pid value IS NOT for an existing property! ';
            }
            else
                $preRisk->property_pid = $data[$pid_col];

            //if(!$ha_date_col && !empty($data[$ha_date_col]))
            //    $preRisk->ha_date = date("Y-m-d", strtotime($data[$ha_date_col]));

            //Save and print status of import (take out for large file)
            if(!$error)
            {
                if($preRisk->save())
                {
                    $new_pr_entries++;
                }
                else
                {
                    print 'Could not import row. Details: ' . var_export($preRisk->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Error: Could not import PR Entry, error on save. Details: '.preg_replace('/\s+/', ' ', trim(var_export($preRisk->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                }
            }
            else
            {
                print $errorMsg . "\n";
                $data[$errors_col] = $errorMsg;
                fputcsv($error_fh, $data);
                $errors++;
            }

        }
        fclose($fh); //close input file
        fclose($error_fh); //close error file
        $this->printUpdateStatus($fileToImport, "Finished", "Done with PR Call Import. Processed $counter rows (New PR Entries: $new_pr_entries, Errors: $errors, see error file for specifics)");
    }

    /**
     * Given a list (csv file) of rows with client, member_num, and policy,
     * this funciton will look up and output the pid for each property in an output file
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function getPIDs($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Get PID script");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('client','member_num','policy');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $client_col = array_search('client', $header_fields);
        $member_num_col = array_search('member_num', $header_fields);
        $policy_col = array_search('policy', $header_fields);

        //setup output file
        $output_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_output_w_pids.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'pid';
        $pid_col = array_search('pid', $header_fields);
        fputcsv($output_fh, $header_fields);

        //index variables to track progress/results
        $counter = 0;
        $errors = 0;

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            //trim up the attributes
            $data = array_map('trim', $data);

            $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>$data[$client_col]));
            if(!isset($member)) //didn't find member
            {
                $this->printUpdateStatus($fileToImport, "Processing", "Error, Could not find member in DB (line: $counter)");
                $errors++;
            }
            else
            {
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$data[$policy_col]));
                if(!isset($property)) //didnt find property
                {
                    $this->printUpdateStatus($fileToImport, "Processing", "Error, Could not find property in DB (line: $counter)");
                    $errors++;
                }
                else
                {
                    $data[$pid_col] = $property->pid;
                    fputcsv($output_fh, $data);
                }
            }

            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Processed $counter rows so far (Errors: $errors)");
        }

        fclose($fh);
        fclose($output_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with PID lookup script. Processed $counter rows (Errors: $errors)");
    }

    /**
     * Given a list (csv file) of rows with pid, policy_status, pre_risk_status, response_status, and fireshield_status
     * it updates the given pid accordingly.
     * NOTE: you have to have all the columns, but if you leave the status blank in the row then it will just leave it as it currently is.
     * Outputs an error file with details on any issues it has for specific rows.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function updatePropertyStatuses($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Property Statuses Update");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('pid', 'policy_status', 'pre_risk_status', 'response_status', 'fireshield_status');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $pid_col = array_search('pid', $header_fields);
        $policy_status_col = array_search('policy_status', $header_fields);
        $pre_risk_status_col = array_search('pre_risk_status', $header_fields);
        $response_status_col = array_search('response_status', $header_fields);
        $fireshield_status_col = array_search('fireshield_status', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //index variables to track progress/results
        $counter = 0;
        $updated_properties = 0;
        $errors = 0;

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            //trim up the attributes
            $data = array_map('trim', $data);

            $prop = Property::model()->findByPk($data[$pid_col]);
            if(isset($prop))
            {
				if(!empty($data[$policy_status_col]))
				{
					$prop->policy_status = $data[$policy_status_col];
					$prop->policy_status_date = date('Y-m-d');
				}
				if(!empty($data[$pre_risk_status_col]))
				{
					$prop->pre_risk_status = $data[$pre_risk_status_col];
					$prop->pr_status_date = date('Y-m-d');
				}
				if(!empty($data[$fireshield_status_col]))
				{
					$prop->fireshield_status = $data[$fireshield_status_col];
					$prop->fs_status_date = date('Y-m-d');
				}
				if(!empty($data[$response_status_col]))
				{
					$prop->response_status = $data[$response_status_col];
					$prop->res_status_date = date('Y-m-d');
				}

				if(!$prop->save())
				{
					print 'Could not import row. Details: ' . var_export($prop->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
					$data[$errors_col] = 'Could not update Property. Details: '.preg_replace('/\s+/', ' ', trim(var_export($prop->getErrors(), true)));
					fputcsv($error_fh, $data);
					$errors++;
				}
				else
					$updated_properties++;
            }
            else
            {
                print 'Could not import row. Details: pid did not exist ROW VALUES: ' . var_export($data, true) . "\n";
                $data[$errors_col] = 'Could not import Member. Details: pid did not exist';
                fputcsv($error_fh, $data);
                $errors++;
            }

            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Props: $updated_properties, Errors: $errors)");
        }
        fclose($fh);
        fclose($error_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Property Statuses Update. Processed $counter rows (Updated Props: $updated_properties, Errors: $errors)");
    }

    /**
     * Imports the Chubb PIF list files. They come in 2 parts, one that is for Response eligible and one for Response enrolled
     * NOTE: They need to be run in order Eligible, then Enrolled so that the cancel routine works correctly
     * Outputs a *_results.csv file for details of the import of each record.
     * Also outputs a *_errors.csv file with details of any errors it ran into.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importChubbPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Chubb PIF Import");
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //look up client
        $chubb_client = Client::model()->findByAttributes(array('name'=>'Chubb'));

        //Eligible file should be imported first, and if so need to reset all chubb property flags (for cancel routine after Enroll import)
        if($fileToImport['type'] == 'Chubb - Eligible PIF Import')
        {
            $this->printUpdateStatus($fileToImport, "Processing", "Starting Chubb Import Flag Reset Routine.");
            $this->reset_property_flags($chubb_client->id);
            $this->printUpdateStatus($fileToImport, "Processing", "Done with Chubb Import Flag Reset Routine.");
        }

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        if($fileToImport['type'] == 'Chubb - Eligible PIF Import')
            $fields_to_check = array('as of', 'policy number', 'policy effective date', 'signature indicator', 'location', 'coverage sequence number', 'insureds name first', 'middle', 'last', 'eligible address street', 'city', 'county', 'state', 'zip code', 'near hydrant (y/n)', 'category(1/2/3)', 'operational zone (1/2/3/4)', 'producer code', 'producer name', 'phone number', 'lat', 'long', 'geostatus', 'geodefinition');
        elseif($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
            $fields_to_check = array('as of', 'policy number', 'policy effective date', 'signature indicator', 'location', 'coverage sequence number', 'insureds name first', 'middle', 'last', 'eligible address street', 'city', 'county', 'state', 'zip code', 'property description', 'near hydrant (y/n)', 'category(1/2/3)', 'operational zone (1/2/3/4)', 'producer code', 'producer name', 'phone number', 'primary contact name', 'relationship1', '1st  type', 'phone/email1', '2nd  type', 'phone/email2', '3rd  type', 'phone/email3', '4th  type', 'phone/email4', 'secondary contact', 'relationship2', '1st   type', 'phone/email5', '2nd   type', 'phone/email6', '3rd   type', 'phone/email7', '4th   type', 'phone/email8', 'tertiary contact', 'relationship3', '1st type', 'phone/email9', '2nd type', 'phone/email10', '3rd type', 'phone/email11', '4th type', 'phone/email12', 'lat', 'long', 'geostatus','geodefinition');

        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $as_of_col = array_search('as of', $header_fields);
        $policy_number_col = array_search('policy number', $header_fields);
        $policy_effective_date_col = array_search('policy effective date', $header_fields);
        $signature_indicator_col = array_search('signature indicator', $header_fields);
        $location_col = array_search('location', $header_fields);
        $coverage_sequence_number_col = array_search('coverage sequence number', $header_fields);
        $insureds_name_first_col = array_search('insureds name first', $header_fields);
        $middle_col = array_search('middle', $header_fields);
        $last_col = array_search('last', $header_fields);
        $eligible_address_street_col = array_search('eligible address street', $header_fields);
        $city_col = array_search('city', $header_fields);
        $county_col = array_search('county', $header_fields);
        $state_col = array_search('state', $header_fields);
        $zip_code_col = array_search('zip code', $header_fields);
        $near_hydrant_col = array_search('near hydrant (y/n)', $header_fields);
        $category_col = array_search('category(1/2/3)', $header_fields);
        $operational_zone_col = array_search('operational zone (1/2/3/4)', $header_fields);
        $producer_code_col = array_search('producer code', $header_fields);
        $producer_name_col = array_search('producer name', $header_fields);
        $phone_number_col = array_search('phone number', $header_fields);
        $lat_col = array_search('lat', $header_fields);
        $long_col = array_search('long', $header_fields);
        $geostatus_col = array_search('geostatus', $header_fields);
        $geodefinition_col = array_search('geodefinition', $header_fields);
        if($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
        {
            $property_description_col = array_search('property description', $header_fields);
            $primary_contact_name_col = array_search('primary contact name', $header_fields);
            $primary_contact_relationship_col = array_search('relationship1', $header_fields);
            $primary_contact_type_1_col = array_search('1st  type', $header_fields);
            $primary_contact_phone_email_1_col = array_search('phone/email1', $header_fields);
            $primary_contact_type_2_col = array_search('2nd  type', $header_fields);
            $primary_contact_phone_email_2_col = array_search('phone/email2', $header_fields);
            $primary_contact_type_3_col = array_search('3rd  type', $header_fields);
            $primary_contact_phone_email_3_col = array_search('phone/email3', $header_fields);
            $primary_contact_type_4_col = array_search('4th  type', $header_fields);
            $primary_contact_phone_email_4_col = array_search('phone/email4', $header_fields);
            $secondary_contact_name_col = array_search('secondary contact', $header_fields);
            $secondary_contact_relationship_col = array_search('relationship2', $header_fields);
            $secondary_contact_type_1_col = array_search('1st   type', $header_fields);
            $secondary_contact_phone_email_1_col = array_search('phone/email5', $header_fields);
            $secondary_contact_type_2_col = array_search('2nd   type', $header_fields);
            $secondary_contact_phone_email_2_col = array_search('phone/email6', $header_fields);
            $secondary_contact_type_3_col = array_search('3rd   type', $header_fields);
            $secondary_contact_phone_email_3_col = array_search('phone/email7', $header_fields);
            $secondary_contact_type_4_col = array_search('4th   type', $header_fields);
            $secondary_contact_phone_email_4_col = array_search('phone/email8', $header_fields);
            $tertiary_contact_name_col = array_search('tertiary contact', $header_fields);
            $tertiary_contact_relationship_col = array_search('relationship3', $header_fields);
            $tertiary_contact_type_1_col = array_search('1st type', $header_fields);
            $tertiary_contact_phone_email_1_col = array_search('phone/email9', $header_fields);
            $tertiary_contact_type_2_col = array_search('2nd type', $header_fields);
            $tertiary_contact_phone_email_2_col = array_search('phone/email10', $header_fields);
            $tertiary_contact_type_3_col = array_search('3rd type', $header_fields);
            $tertiary_contact_phone_email_3_col = array_search('phone/email11', $header_fields);
            $tertiary_contact_type_4_col = array_search('4th type', $header_fields);
            $tertiary_contact_phone_email_4_col = array_search('phone/email12', $header_fields);
        }

        //setup error file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('wds_pid', 'Policy #', 'Sequence #', 'Import Action', 'Address Line 1', 'City', 'State', 'Zip'));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;
        $results_action = '';
        $enrolled_props = 0;

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            //trim up the attributes
            $data = array_map('trim', $data);

            //check if member already exists

            if(strpos($data[$policy_number_col], '-') === false)
            {
	            $policy_number = $data[$policy_number_col];
	            $policyholder_number = $data[$policy_number_col];
            }
            else
            {
                $policy_number = $data[$policy_number_col];
	            $policyholder_number = current(explode('-', $data[$policy_number_col]));
            }

            $policy_location = ltrim($data[$location_col], '0');

            // if location number is blank, use address as unique location #
            if(empty($data[$location_col]))
                $policy_location = $data[$eligible_address_street_col];

            $member = Member::model()->findByAttributes(array('member_num'=>$policyholder_number, 'client_id'=>$chubb_client->id));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $member->member_num = $policyholder_number;
                $member->client = 'Chubb';
                $property = new Property();
                $property->policy = $policy_number;
                $property->client_policy_id = $data[$policy_number_col];
                $new_members++;
                $new_properties++;
                $results_action = "Add";
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$policy_number, 'location'=>$policy_location));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    $property->policy = $policy_number;
                    $property->client_policy_id = $data[$policy_number_col];
                    $new_properties++;
                    $results_action = "Add";
                }
                else
                {
                    $updated_properties++;
                    $results_action = "Update";
                }
            }

            //---set/map attributes section---//
            $property->seq_num = ltrim($data[$coverage_sequence_number_col], '0');
            $property->location = $policy_location;
            $property->transaction_effective = $data[$as_of_col];
            $property->policy_effective = $data[$policy_effective_date_col];
            $member->signed_ola = $data[$signature_indicator_col];
            $member->first_name = $data[$insureds_name_first_col];
            $member->middle_name = $data[$middle_col];
            $member->last_name = $data[$last_col];
            $property->address_line_1 = mb_convert_encoding($data[$eligible_address_street_col], 'UTF-8');
            $property->city = $data[$city_col];
            $property->county = $data[$county_col];
            $property->agency_phone = $data[$phone_number_col];
            $property->agency_code = $data[$producer_code_col];
            //chubb uses completely non-standard random crappy state abbreviations. use helper dictionary setup to convert them to standard 2 letter abbreviations if needed
            if(in_array($data[$state_col], Helper::$statesToAbbrDict))
                $property->state = $data[$state_col];
            else
                $property->state = Helper::$statesToAbbrDict[$data[$state_col]];
            $property->zip = $data[$zip_code_col];
            $property->comments = '';
            if($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
            {
                if(!empty($data[$property_description_col]))
                    $property->comments .= 'Prop Desc.: '.$data[$property_description_col]."\n";
            }

            if(!empty($data[$producer_name_col]))
                $property->producer = $data[$producer_name_col]." ";
            if(!empty($data[$phone_number_col]))
                $property->producer .= '(Ph: '.$data[$phone_number_col].") ";
            if(!empty($data[$producer_code_col]))
                $property->producer .= '(Code: '.$data[$producer_code_col].")";

            
            $property->producer = $data[$producer_name_col];
            if(!empty($data[$near_hydrant_col]))
                $property->comments .= 'Near Hydrant: '.$data[$near_hydrant_col]."\n";
            if(!empty($data[$category_col]))
                $property->comments .= 'Category: '.$data[$category_col]."\n";
            if(!empty($data[$operational_zone_col]))
                $property->comments .= 'Operational Zone: '.$data[$operational_zone_col]."\n";

            $property->comments = mb_convert_encoding($property->comments, 'UTF-8');


            $member->work_phone = 'See Prop Addnl Contacts';
            $member->home_phone = 'See Prop Addnl Contacts';
            $member->cell_phone = 'See Prop Addnl Contacts';

            //if(!(isset($property->geocode_level) && $property->geocode_level == 'WDS')) //if WDS already set this, then leave it be, otherwise set it
            //{
                //$property->lat = $data[$lat_col];
                //$property->long = $data[$long_col];
                //$property->geocode_level = $data[$geostatus_col];
            //}

			$property->lat = NULL;
			$property->long = NULL;	
				
			$property->rated_company  = substr($chubb_client->name,0,40) ;

            if($member->isNewRecord)
            {
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }
            else //if not a new member
            {
                if($property->isNewRecord) //but is a new prop, look for another one with same addy
                {
                    //check if there is a same address property under this member
                    $other_props = Property::model()->findAllByAttributes(array('member_mid'=>$member->mid));
                    foreach($other_props as $other_prop)
                    {
                        if($other_prop->address_line_1 == $property->address_line_1)
                            $results_action = 'ReWrite';
                    }
                }
            }
            if($property->isNewRecord)
            {
                $property->pre_risk_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
				$property->wds_lat = NULL;
				$property->wds_long = NULL;
				$property->geog = NULL;
				$property->wds_geocode_level = 'unmatched';
            }
            elseif($property->policy_status != 'active')
            {
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
            }

            //enrolled counter
            if($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
                $enrolled_props++;

            //if its a new property OR there has been no response status changes on the dashboard for this property, then set according to PIF. Otherwise leave repsonse status as is because what is set on dash trumps
            if($property->isNewRecord || !isset($property->currentWdsFireEnrollmentStatus))
            {
                if($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
                    $property->response_status = 'enrolled';
                elseif($fileToImport['type'] == 'Chubb - Eligible PIF Import')
                    $property->response_status = 'not enrolled';

                $property->res_status_date = date('Y-m-d H:i:s');
            }

            //warning if response status is out of sync with pif
            if(!$property->isNewRecord)
            {
                if(($fileToImport['type'] == 'Chubb - Enrolled PIF Import' && $property->response_status !== 'enrolled') ||
                   ($fileToImport['type'] == 'Chubb - Eligible PIF Import' && $property->response_status !== 'not enrolled'))
                {
                    print "Warning: Response Status does not match PIF list and was left as is because it was set in WDSFire dashboard which trumps.\n";
                    $data[$errors_col] = 'Warning: Response Status does not match PIF list and was left as is because it was set in WDSFire dashboard which trumps.';
                    fputcsv($error_fh, $data);
                    $errors++;
                }
            }

            //set client_ids
            $member->client_id = $chubb_client->id;
            $property->client_id = $chubb_client->id;

            //Save process
            if($member->save())
            {
                $property->member_mid = $member->mid;
                $property->flag = 1;
                if(!$property->save())
                {
                    print 'Could not import row. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Could not import property for member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else //prop saved correctly
                {
                    //add to results file with the action that was done
                    //columns are pid, chubb policy #, chubb sequence #, and action
                    fputcsv($results_fh, array($property->pid, $data[$policy_number_col], $data[$coverage_sequence_number_col], $results_action, $property->address_line_1, $property->city, $property->state, $property->zip));

					//fetch last inserted property id after property save
					$pid_lastID = $property->pid;
					if($property->pid == '')
					{
						$lastproperty= Yii::app()->db->createCommand('select top 1* from properties order by pid desc')->queryAll();
						$pid_lastID = $lastproperty[0]['pid'];
					}
					$this->setGeoSpatial($pid_lastID, $data[$lat_col], $data[$long_col]);

                    if($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
                    {
                        $contactsRecieved = array();

                        // Add contacts
                        if (!empty($data[$primary_contact_name_col]))
                        {
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$primary_contact_type_1_col],'priority'=>'Primary 1','name'=>$data[$primary_contact_name_col],'relationship'=>$data[$primary_contact_relationship_col],'detail'=>$data[$primary_contact_phone_email_1_col],'notes'=>null));

                            if (!empty($data[$primary_contact_type_2_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$primary_contact_type_2_col],'priority'=>'Primary 2','name'=>$data[$primary_contact_name_col],'relationship'=>$data[$primary_contact_relationship_col],'detail'=>$data[$primary_contact_phone_email_2_col],'notes'=>null));
                            if (!empty($data[$primary_contact_type_3_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$primary_contact_type_3_col],'priority'=>'Primary 3','name'=>$data[$primary_contact_name_col],'relationship'=>$data[$primary_contact_relationship_col],'detail'=>$data[$primary_contact_phone_email_3_col],'notes'=>null));
                            if (!empty($data[$primary_contact_type_4_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$primary_contact_type_4_col],'priority'=>'Primary 4','name'=>$data[$primary_contact_name_col],'relationship'=>$data[$primary_contact_relationship_col],'detail'=>$data[$primary_contact_phone_email_4_col],'notes'=>null));
                        }
                        if (!empty($data[$secondary_contact_name_col]))
                        {
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$secondary_contact_type_1_col],'priority'=>'Secondary 1','name'=>$data[$secondary_contact_name_col],'relationship'=>$data[$secondary_contact_relationship_col],'detail'=>$data[$secondary_contact_phone_email_1_col],'notes'=>null));

                            if (!empty($data[$secondary_contact_type_2_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$secondary_contact_type_2_col],'priority'=>'Secondary 2','name'=>$data[$secondary_contact_name_col],'relationship'=>$data[$secondary_contact_relationship_col],'detail'=>$data[$secondary_contact_phone_email_2_col],'notes'=>null));
                            if (!empty($data[$secondary_contact_type_3_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$secondary_contact_type_3_col],'priority'=>'Secondary 3','name'=>$data[$secondary_contact_name_col],'relationship'=>$data[$secondary_contact_relationship_col],'detail'=>$data[$secondary_contact_phone_email_3_col],'notes'=>null));
                            if (!empty($data[$secondary_contact_type_4_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$secondary_contact_type_4_col],'priority'=>'Secondary 4','name'=>$data[$secondary_contact_name_col],'relationship'=>$data[$secondary_contact_relationship_col],'detail'=>$data[$secondary_contact_phone_email_4_col],'notes'=>null));
                        }
                        if (!empty($data[$tertiary_contact_name_col]))
                        {
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$tertiary_contact_type_1_col],'priority'=>'Tertiary 1','name'=>$data[$tertiary_contact_name_col],'relationship'=>$data[$tertiary_contact_relationship_col],'detail'=>$data[$tertiary_contact_phone_email_1_col],'notes'=>null));

                            if (!empty($data[$tertiary_contact_type_2_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$tertiary_contact_type_2_col],'priority'=>'Tertiary 2','name'=>$data[$tertiary_contact_name_col],'relationship'=>$data[$tertiary_contact_relationship_col],'detail'=>$data[$tertiary_contact_phone_email_2_col],'notes'=>null));
                            if (!empty($data[$tertiary_contact_type_3_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$tertiary_contact_type_3_col],'priority'=>'Tertiary 3','name'=>$data[$tertiary_contact_name_col],'relationship'=>$data[$tertiary_contact_relationship_col],'detail'=>$data[$tertiary_contact_phone_email_3_col],'notes'=>null));
                            if (!empty($data[$tertiary_contact_type_4_col]))
                                $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>$data[$tertiary_contact_type_4_col],'priority'=>'Tertiary 4','name'=>$data[$tertiary_contact_name_col],'relationship'=>$data[$tertiary_contact_relationship_col],'detail'=>$data[$tertiary_contact_phone_email_4_col],'notes'=>null));
                        }

                        // Remove any old contacts that were not recieved in this list
                        $this->cleanContacts($property->pid, $contactsRecieved);
                    }
                }
            }
            else
            {
                print 'Could not import row. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $data[$errors_col] = 'Could not import Member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                fputcsv($error_fh, $data);
                $errors++;
            }

            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Errors: $errors)");

        }
        fclose($fh);

        //if Enrolled file, then run cancel routine
        if($fileToImport['type'] == 'Chubb - Enrolled PIF Import')
        {
            $pifPropType = PropertiesType::model()->findByAttributes(array('type' => 'PIF'));
            $props_to_cancel = Property::model()->findAllByAttributes(array('flag' => 0, 'client_id' => $chubb_client->id, 'policy_status' => 'active', 'type_id'=>$pifPropType->id));
            //loop through all Chubb properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
            $canceled_props = count($props_to_cancel);
            $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
            foreach($props_to_cancel as $prop_to_cancel)
            {
                $results_action = 'Cancel';
                $other_props = Property::model()->findAllByAttributes(array('member_mid'=>$prop_to_cancel->member_mid, 'policy_status'=>'active'));
                foreach($other_props as $other_prop)
                {
                    if($other_prop->address_line_1 == $prop_to_cancel->address_line_1 && $prop_to_cancel->pid != $other_prop->pid)
                        $results_action = 'Cancel-ReWrite';
                }

                $prop_to_cancel->policy_status = 'canceled';
                $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
                if(!$prop_to_cancel->save())
                {
                    print 'Error on saveing property being cancelled. Details: ' . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true)));
                    $errors++;
                }
                else
                {
                    //add to results file with the action that was done
                    //columns are pid, chubb policy #, chubb location #, and action
                    fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->policy, $prop_to_cancel->location, $results_action, $prop_to_cancel->address_line_1, $prop_to_cancel->city, $prop_to_cancel->state, $prop_to_cancel->zip));
                }
            }
        }

        fclose($error_fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Chubb Import. Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members, Enrolled Props: $enrolled_props, New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Likely a one time function that takes in a ~ seperated csv file from USAA and updated
     * the transaction_type on the property accordingly. This was used when we first changed to tracking transactions.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importUpdateUSAATransactions($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Update USAA Transactions");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh, null, '~');

        //trim them up
        $header_fields = array_map('trim', $header_fields);

        $fields_to_check = array('USAA-NR', 'DSP-POL-NR', 'ISS_POL_IND', 'NON_RNW_IND', 'CAN_POL_IND', 'POL_REW_IND', 'POL_REI_IND', 'TRANS_EFFECT_DT');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $member_num_col = array_search('USAA-NR', $header_fields);
        $policy_col = array_search('DSP-POL-NR', $header_fields);
        $issue_col = array_search('ISS_POL_IND', $header_fields);
        $non_renew_col = array_search('NON_RNW_IND', $header_fields);
        $cancel_col = array_search('CAN_POL_IND', $header_fields);
        $rewrite_col = array_search('POL_REW_IND', $header_fields);
        $reinstate_col = array_search('POL_REI_IND', $header_fields);
        $trans_effective_col = array_search('TRANS_EFFECT_DT', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'Errors';
        $errors_col = array_search('Errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //loop through each line and update properties for each entry to response_status = enrolled
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Main Loop");
        $counter = 0;
        $success = 0;
        $error = 0;
        while(($data = fgetcsv($fh, null, '~')) !== FALSE)
        {
            $counter++;
            //trim up the data
            $data = array_map('trim', $data);
            //strip 0s from front of mem num
            $data[$member_num_col] = ltrim($data[$member_num_col], '0');
            //look up member
            $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>'USAA',));
            if(!isset($member))
            {
                $data[$errors_col] = 'Could not find Member in database.';
                fputcsv($error_fh, $data);
                $error++;
            }
            else
            {
                //look up property
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$data[$policy_col], 'location'=>'1'));
                if(!isset($property))
                {
                    $data[$errors_col] = 'Could not find Property in database.';
                    fputcsv($error_fh, $data);
                    $error++;
                }
                else
                {
                    //update transaction info
                    if($data[$issue_col] == '1')
                        $property->transaction_type = 'issue';
                    else if($data[$non_renew_col] == '1')
                        $property->transaction_type = 'non-renew';
                    else if($data[$cancel_col] == '1')
                        $property->transaction_type = 'cancel';
                    else if($data[$rewrite_col] == '1')
                        $property->transaction_type = 're-write';
                    else if($data[$reinstate_col] == '1')
                        $property->transaction_type = 'reinstate';

                    $property->transaction_effective = $data[$trans_effective_col];
                    if(!$property->save())  //save property with new transaction info
                    {
                        $data[$errors_col] = 'Could not save property after updating USAA Transaction Info. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        fputcsv($error_fh, $data);
                        $error++;
                    }
                    else
                        $success++;
                }
            }
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Processed $counter rows ($success successful, $error errors)");
        }
        fclose($fh);
        fclose($error_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Update USAA Transactions. Processed $counter rows ($success successful, $error errors)");
    }

    /**
     * Import function that will update user/passwords based on a csv file
     * Not used recently to my knowledge, maybe was used to to a bulk update for a client (LM??)
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importUserPWUpdate($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Update User Passwords");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);

        $fields_to_check = array('username', 'password');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $username_col = array_search('username', $header_fields);
        $pw_col = array_search('password', $header_fields);

        //loop through each line and update properties for each entry to response_status = enrolled
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Main Loop");
        $counter = 0;
        $success = 0;
        $error = 0;
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $user = User::model()->findByAttributes(array('username'=>$data[$username_col]));
            if(isset($user))
            {
                $user->salt = $user->generateSalt();
                $user->password = $user->hashPassword($data[$pw_col], $user->salt);
                $user->pw_exp = date('Y-m-d', strtotime('+ 90 days'));
                if($user->save())
                    print "Successfully updated password for ".$user->username."\n";
                else
                    print "Error updating password ".$user->username."\n";
            }
            else
                print "Error, couldn't find user entry for ".$data[$username_col];
        }
        $this->printUpdateStatus($fileToImport, "Finished", "Done Update User Passwords");
    }

    /**
     * Import function that will update the lat/long for a given pid (property) based on a csv file
     * NOTE: this sets the geocode level  = wds
     * Outputs an error file with any errors encountered
     * Was largely used by Casey I think to fix unmatched back in the day
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importWDSLatLongUpdate($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Update WDS Lat Long on Properties");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');
        if(($fh === false))
        {
            $this->printUpdateStatus($fileToImport, "Error", "Error Details: Could not open file. Check path.");
            return false;
        }

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('pid', 'lat', 'long');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $pid_col = array_search('pid', $header_fields);
        $lat_col = array_search('lat', $header_fields);
        $long_col = array_search('long', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'Errors';
        $errors_col = array_search('Errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //loop through each line and update properties for each entry to response_status = enrolled
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Main Loop");
        $counter = 0;
        $success = 0;
        $error = 0;
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $property = Property::model()->findByPk($data[$pid_col]);
            if(!isset($property))
            {
                $data[$errors_col] = 'Could not find Property in database.';
                fputcsv($error_fh, $data);
                $error++;
            }
            else
            {
                $property->geog = 'POINT (' . $data[$long_col] . ' ' . $data[$lat_col] . ')';
                $property->wds_lat = $data[$lat_col];
                $property->wds_long = $data[$long_col];
                $property->wds_geocode_level = Property::GEOCODE_WDS;
                $property->wds_geocoder = null;
                $property->wds_match_address = null;
                $property->wds_match_score = null;
                $property->wds_geocode_date = date('Y-m-d H:i');
                $property->geocoded = true;

                if(!$property->save())
                {
                    $data[$errors_col] = 'Could not save property after updating geog. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $error++;
                }
                else
                    $success++;
            }
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Processed $counter rows ($success successful, $error errors)");
        }
        fclose($fh);
        fclose($error_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with USAA WDS Update Lat/Long on Properties. Processed $counter rows ($success successful, $error errors)");
    }

    /**
     * Simple Import function that will update a property record and set it's policy_status = 'active'
     * Just takes in a list of pids, one per line (with a 'pid' header)
     * Was likely used to activate a bunch of policies that were mistakenly canceled and were found that they shouldn't be
     * This function is redundant with the importUpdatePropertyStatuses function as the same could be done with that. This can likely be removed
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importActivateProperties($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Activate Properties");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);

        $fields_to_check = array('pid');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $pid_col = array_search('pid', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'Errors';
        $errors_col = array_search('Errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //loop through each line and update properties for each entry to response_status = enrolled
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Main Loop");
        $counter = 0;
        $success = 0;
        $error = 0;
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $property = Property::model()->findByPk($data[$pid_col]);
            if(!isset($property))
            {
                $data[$errors_col] = 'Could not find Property in database.';
                fputcsv($error_fh, $data);
                $error++;
            }
            else
            {
                $property->policy_status = 'active';
                if(!$property->save())
                {
                    $data[$errors_col] = 'Could not save property after updating policy status to active. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $error++;
                }
                else
                    $success++;
            }
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Processed $counter rows ($success successful, $error errors)");
        }
        fclose($fh);
        fclose($error_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with Activate Properties. Processed $counter rows ($success successful, $error errors)");
    }

    /**
     * Import function that was used at some point to bulk response enroll a bunch of policy numbers that LM sent
     * It is using the "ID" single field csv to lookup both member.member_num and property.policy (one to one) policies.
     * this was likely a one time thing and can probably safely be removed.
     * Outputs an error file of any issues it had saving.
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importLMSafResEnroll($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting LMSaf Res Enroll");

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);

        $fields_to_check = array('ID');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $member_num_col = array_search('ID', $header_fields);
        $policy_col = array_search('ID', $header_fields);
        $client = $fileToImport['client'];

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'Errors';
        $errors_col = array_search('Errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //loop through each line and update properties for each entry to response_status = enrolled
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Main Loop");
        $counter = 0;
        $success = 0;
        $error = 0;
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>$client));
            if(!isset($member))
            {
                $data[$errors_col] = 'Could not find Member in database.';
                fputcsv($error_fh, $data);
                $error++;
            }
            else
            {
                $property = $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$data[$policy_col], 'location'=>'1'));
                if(!isset($property))
                {
                    $data[$errors_col] = 'Could not find Property in database.';
                    fputcsv($error_fh, $data);
                    $error++;
                }
                else
                {
                    $property->response_status = 'enrolled';
                    if(!$property->save())
                    {
                        $data[$errors_col] = 'Could not save property after updating response status to enrolled. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        fputcsv($error_fh, $data);
                        $error++;
                    }
                    else
                        $success++;
                }
            }
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Processed $counter rows ($success successful, $error errors)");
        }
        fclose($fh);
        fclose($error_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with LMSaf Res Enroll. Processed $counter rows ($success successful, $error errors)");
    }

    /**
     * Incremental Import function the combined LM+Safeco PIF list that is sent once a month
     * NOTE: Because it is incremental it does not have a cancel routine like the Full PIF import functions do.
     * Instead it takes in 'canceled' transactions that LM sends.
     * Also NOTE: Some LM/Saf records don't have Policyholder IDs / member_nums, so we just use the policy # for that space and it is a one to one relationship with the property then.
     * Outputs both a Results and Error file
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function importLibertySafecoPIF($fileToImport, $fileClient, $type)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting import for $fileClient PIF ($type)");

        //look up clients
        $saf_client = Client::model()->findByAttributes(array('name'=>'Safeco'));
        $lm_client = Client::model()->findByAttributes(array('name'=>'Liberty Mutual'));

        if($type === 'Full')
        {
            //Full PIF import so need to reset all client property flags (for cancel routine after import)
            $this->printUpdateStatus($fileToImport, "Processing", "Starting LM/SAF Import Flag Reset Routine.");
            $this->reset_property_flags($saf_client->id);
            $this->reset_property_flags($lm_client->id);
            $this->printUpdateStatus($fileToImport, "Processing", "Done with LM/SAF Import Flag Reset Routine.");
        }

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('company','policyid','customerid','pol_home_state','pol_eff_date','policy_term_end_date','policy_holder_1_first_name','policy_holder_1_last_name','street','city','state','zip','residential_phone','mobile_phone','business_phone','email_address','channel','agent','sales_office_nme','agent_phone_number','agent_email_address','coverage_a','run_dt','record_type');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $rated_company_col = array_search('company', $header_fields);
        $policy_col = array_search('policyid', $header_fields);
        $member_num_col = array_search('customerid', $header_fields);
        $first_name_col = array_search('policy_holder_1_first_name', $header_fields);
        $last_name_col = array_search('policy_holder_1_last_name', $header_fields);
        $cell_phone_col = array_search('mobile_phone', $header_fields);
        $home_phone_col = array_search('residential_phone', $header_fields);
        $work_phone_col = array_search('business_phone', $header_fields);
        $email_1_col = array_search('email_address', $header_fields);
        $address_line_1_col = array_search('street', $header_fields);
        $city_col = array_search('city', $header_fields);
        $zip_col = array_search('zip', $header_fields);
        $state_col = array_search('state', $header_fields);
        $coverage_a_amt_col = array_search('coverage_a', $header_fields);
        $policy_effective_col = array_search('pol_eff_date', $header_fields);
        $policy_expiration_col = array_search('policy_term_end_date', $header_fields);
        $agent_name_col = array_search('agent', $header_fields);
        $channel_col = array_search('channel', $header_fields);
        $sales_office_nme_col = array_search('sales_office_nme', $header_fields);
        $run_dt_col = array_search('run_dt', $header_fields);
        $agent_phone_col = array_search('agent_phone_number', $header_fields);
        $agent_email_col = array_search('agent_email_address', $header_fields);
        $action_col = array_search('record_type', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('wds_mid', 'wds_pid', 'Client', 'Customer #', 'Policy #', 'Import Action',));

        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $canceled_props = 0;
        $errors = 0;
        $results_action = '';

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $skip = false;
            //trim up the attributes
            $data = array_map('trim', $data);

            $client = '';

            //get main indexes
            if(empty($data[$policy_col]) || empty($data[$action_col]))
            {
                $results_action = "ERROR: POLICYID and/or RECORD_TYPE fields were NOT set! SKIPPING!";
                $data[$errors_col] = $results_action;
                fputcsv($error_fh, $data);
                $errors++;
                $skip = true;
            }
            else
            {
                $action = $data[$action_col];
                if($type === 'Full')
                    $action = 'MODIFY';

                $policy = $data[$policy_col];
                //if customer id (member_num) column is blank, use policy as the member_num which will form a 1 to 1 relationship in the database logic.
                if(empty($data[$member_num_col]))
                    $member_num = $policy;
                else
                    $member_num = $data[$member_num_col];

                $results_action = '';

                if($fileClient === 'LM/SAF')
                {
                    if(!empty($data[$rated_company_col]) && $data[$rated_company_col] === 'PL')
                    {
                        $client_id = $lm_client->id;
                        $client = 'Liberty Mutual';
                    }
                    else if(!empty($data[$rated_company_col]) && $data[$rated_company_col] === 'SAFECO')
                    {
                        $client_id = $saf_client->id;
                        $client = 'Safeco';
                    }
                    else
                    {
                        $results_action = "ERROR: Client for import was set to LM/SAF and the Company field was not set to PL or SAFECO, could not determine client! SKIPPING!";
                        $data[$errors_col] = $results_action;
                        fputcsv($error_fh, $data);
                        $errors++;
                        $skip = true;
                    }
                }
                else
                {
                    $results_action = "ERROR: Company field needs to be either PL or SAFECO";
                    $data[$errors_col] = $results_action;
                    fputcsv($error_fh, $data);
                    $errors++;
                    $skip = true;
                }

                //check if member already exists (first check by member_num, then if that doesn't exist look up by using policy as the member_num which is how it used to be done, in a 1 to 1 fashion cause they didn't provide customerids)
                $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$client_id));
                if(!isset($member)) //didn't find member, so check to see if there is one with member_num = policy (how it was done before we got the customerid)
                {
                    $member = Member::model()->findByAttributes(array('member_num'=>$policy, 'client_id'=>$client_id));
                    if(!isset($member) && ($action === 'ADD' || $action === 'MODIFY')) //still didn't find member, so make a new one (only if its an add/modify record NOT a DELETE
                    {
                        $member = new Member();
                        $member->member_num = $member_num;
                        $member->client_id = $client_id;
                        $member->client = $client;
                        $new_members++;
                        $results_action .= 'Did not find PolicyHolder based on POLICYID(legacy) or by CUSTOMERID key lookup, creating new PolicyHolder. ';
                    }
                    elseif(!isset($member) && $action === 'DELETE')
                    {
                        $results_action .= 'WARNING: Did not find PolicyHolder based on POLICYID(legacy), Could not set to canceled based on DELETE Record_type action. ';
                        $skip = true;
                    }
                    else
                    {
                        $updated_members++;
                        $member->member_num = $member_num;
                        $results_action .= 'Found PolicyHolder based on POLICYID (legacy) key lookup, updating with new CUSTOMERID. ';
                    }

                }
                else
                {
                    $updated_members++;
                    $results_action .= 'Found PolicyHolder based on CUSTOMERID key lookup. ';
                }

                if(!$skip) //should skip here if it was a delete where the member didn't even exist
                {
                    //check if property already exists
                    $property = Property::model()->findByAttributes(array('policy'=>$policy, 'client_id'=>$client_id, 'location'=>'1'));

                    if(!isset($property) && ($action === 'ADD' || $action === 'MODIFY'))
                    {
                        $property = new Property();
                        $property->policy = $policy;
                        $property->client_policy_id = $data[$policy_col];
                        $new_properties++;
                        if($action === 'MODIFY')
                            $results_action .= 'WARNING: Record_type action was MODIFY but Policy did not exist. ';
                        $results_action .= 'Did not find Policy based on POLICYID, creating new Policy under this PolicyHolder. ';
                    }
                    else if(!isset($property) && $action === 'DELETE')
                    {
                        $results_action .= 'WARNING: Did not find Policy based on POLICYID, Could not set to canceled based on DELETE Record_type action. ';
                        $skip = true;
                    }
                    else //existing property record found
                    {
                        if(!$member->isNewRecord && $member->mid == $property->member_mid) //existing member with matching prop->member_mid
                        {
                            if($type === 'Incremental' && $action === 'DELETE')
                                $canceled_props++;
                            else
                                $updated_properties++;
                            $results_action .= 'Found Policy based on POLICYID key lookup, and the PolicyHolder foriegn key is correct. ';
                        }
                        else //$member->mid != $property->member_mid
                        {
                            if($type === 'Incremental' && $action === 'DELETE')
                                $canceled_props++;
                            else
                                $updated_properties++;
                            $results_action .= 'Found Policy based on POLICYID key lookup, But the PolicyHolder foriegn key is incorrect and will be updated for normalization. ';
                        }
                        //commenting out below as we found this is a viable situation where a policy gets moved under a new/different policyholder
                        //else  //$member->isNewRecord && $member->mid != $property->member_mid //new member but existing policy, a situation we found happens where a policy gets put under an different policyholder
                        //{
                        //$skip = true;
                        //$results_action .= 'Found Policy but there was no PolicyHolder foriegn key set....this should NOT BE POSSIBLE. ERROR!';
                        //$data[$errors_col] = $results_action;
                        //fputcsv($error_fh, $data);
                        //$errors++;
                        //}
                    }
                }
            }

            $results_pid = 'n/a';
            $results_mid = 'n/a';

            if(!$skip)
            {
                if($action === 'DELETE')
                {
                    //action == 'DELETE', no need to set all the fields, just the policy status to canceled
                    $property->policy_status = 'canceled';
                }
                else //all other actions (Add/Modify)
                {
                    //---set/map attributes section---//
                    if(!empty($data[$first_name_col]))
                        $member->first_name = $data[$first_name_col];
                    if(!empty($data[$last_name_col]))
                        $member->last_name = $data[$last_name_col];
                    elseif(!isset($member->last_name))
                        $member->last_name = '';
                    if(!empty($data[$home_phone_col]))
                        $member->home_phone = $data[$home_phone_col];
                    if(!empty($data[$agent_name_col]))
                    {
                        $property->producer = $data[$agent_name_col];
                        $property->agency_name = $data[$agent_name_col];
                    }
                    if(!empty($data[$agent_phone_col]))
                        $property->producer .= ' (Ph: '.$data[$agent_phone_col].')';
                    if(!empty($data[$agent_email_col]))
                        $property->producer .= ' (EM: '.$data[$agent_email_col].')';

                    //shrink up producer field if it went over
                    if(isset($property->producer))
                        $property->producer = substr($property->producer, 0, 100);

                    //rest of the field values mapping
                    if(!empty($data[$cell_phone_col]))
                        $member->cell_phone = $data[$cell_phone_col];
                    if(!empty($data[$work_phone_col]))
                        $member->work_phone = $data[$work_phone_col];
                    if(!empty($data[$email_1_col]))
                        $member->email_1 = $data[$email_1_col];

                    if(!empty($data[$address_line_1_col]))
                        $property->address_line_1 = $data[$address_line_1_col];
                    if(!empty($data[$rated_company_col]))
                        $property->rated_company = $data[$rated_company_col];
                    if(!empty($data[$city_col]))
                        $property->city = $data[$city_col];
                    if(!empty($data[$state_col]))
                        $property->state = $data[$state_col];
                    if(!empty($data[$zip_col]))
                        $property->zip = substr($data[$zip_col], 0, 5);
                    if(!empty($data[$coverage_a_amt_col]))
                        $property->coverage_a_amt = $data[$coverage_a_amt_col];
                    if(!empty($data[$policy_effective_col]))
                        $property->policy_effective = $data[$policy_effective_col];
                    if(!empty($data[$policy_expiration_col]))
                        $property->policy_expiration = $data[$policy_expiration_col];

                    //additional fields we couldn't find a good map to are put in comments
                    if(empty($property->comments))
                    {
                        if(!empty($data[$channel_col]))
                            $property->comments .= 'CHANNEL: '.$data[$channel_col]."\n";
                        if(!empty($data[$sales_office_nme_col]))
                            $property->comments .= 'SALES_OFFICE_NME: '.$data[$sales_office_nme_col]."\n";
                        if(!empty($data[$run_dt_col]))
                            $property->comments .= 'RUN_DT: '.$data[$run_dt_col]."\n";
                    }

                    $property->comments = mb_convert_encoding($property->comments, 'UTF-8');

                    if($member->isNewRecord)
                    {
                        $member->mem_fireshield_status = 'not enrolled';
                        $member->mem_fs_status_date = date('Y-m-d H:i:s');
                    }

                    if($property->isNewRecord)
                    {
                        $property->pre_risk_status = 'not enrolled';
                        $property->fireshield_status = 'not enrolled';
                        $property->response_status = 'not enrolled';
                        $property->fs_status_date = date('Y-m-d H:i:s');
                        $property->res_status_date = date('Y-m-d H:i:s');
                        $property->pr_status_date = date('Y-m-d H:i:s');

                        $property->policy_status_date = date('Y-m-d H:i:s');
                    }
                    else if(!$property->isNewRecord && $property->policy_status !== 'active') //if its not already active its about to change to active and need to update date too
                        $property->policy_status_date = date('Y-m-d H:i:s');

                    $property->policy_status = 'active'; //policy status is always active if it is not a Delete action
                }

                //if there is another property/policy under this member/policyholder with the same address then need to transfer persistant info (like policy comments & program statuses)
                if(!$member->isNewRecord && $property->isNewRecord) //if member exists already and this is a new prop
                {
                    $otherProperty = Property::model()->find("member_mid = :mid AND address_line_1 = :address AND policy != :policy", array(':mid'=>$member->mid, ':address'=>$data[$address_line_1_col], ':policy'=>$data[$policy_col]));
                    if(isset($otherProperty)) //if there is another policy property for this member with the same addy, then transfer over the status's and status dates
                    {
                        $property->response_status = $otherProperty->response_status;
                        $property->pre_risk_status = $otherProperty->pre_risk_status;
                        $property->fireshield_status = $otherProperty->fireshield_status;
                        $property->fs_status_date = $otherProperty->fs_status_date;
                        $property->res_status_date = $otherProperty->res_status_date;
                        $property->response_enrolled_date = $otherProperty->response_enrolled_date;
                        $property->pr_status_date = $otherProperty->pr_status_date;
                        if((isset($otherProperty->geocode_level) && $otherProperty->geocode_level == 'WDS'))
                        {
                            $property->lat = $otherProperty->lat;
                            $property->long = $otherProperty->long;
                            $property->geocode_level = 'WDS';
                        }
                        $property->comments .= "\nNOTE (".date("Y-m-d H:i:s")."): LM/SAF Import function Copied program statuses, WDS geocode, and other comments over from property pid: ".$otherProperty->pid."\n";
                        $property->comments .= "\nPrevious Property Comments: ".$otherProperty->comments."\n";
                        $results_action .= ' NOTE: Found another Policy under the PolicyHolder('.$member->member_num.') that had the same address, so Copied program statuses, WDS geocode, and other comments over from other policy ('.$otherProperty->policy.'). ';
                    }
                }

                //set client_ids
                $member->client_id = $client_id;
                $property->client_id = $client_id;

                //--------Save Routine-----------//
                if($member->save())
                {
                    $results_mid = $member->mid;
                    $results_action .= 'Successfully saved PolicyHolder. ';
                    $property->member_mid = $member->mid;
                    $property->flag = 1;

                    if(!$property->save()) //prop failed saving
                    {
                        $results_action .= 'Could not import Policy for PolicyHolder (PolicyHolder saved, but Policy did not). Check Errors file for details. ';
                        $data[$errors_col] = 'Could not import property for member (member saved, but prop did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        fputcsv($error_fh, $data);
                        $errors++;
                        if($property->isNewRecord)
                            $new_properties--;
                        else if($action == 'DELETE')
                            $canceled_props--;
                        else
                            $updated_properties--;
                    }
                    else //prop saved correctly
                    {
                        $results_pid = $property->pid;
                        $results_action .= 'Successfully saved Policy. ';

                        $contactsRecieved = array();

                        // Add contacts
                        if (!empty($data[$home_phone_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>"$member->first_name $member->last_name",'relationship'=>null,'detail'=>$data[$home_phone_col],'notes'=>null));
                        if (!empty($data[$work_phone_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'work','priority'=>'Primary 2','name'=>"$member->first_name $member->last_name",'relationship'=>null,'detail'=>$data[$work_phone_col],'notes'=>null));
                        if (!empty($data[$cell_phone_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 3','name'=>"$member->first_name $member->last_name",'relationship'=>null,'detail'=>$data[$cell_phone_col],'notes'=>null));
                        if (!empty($data[$email_1_col]))
                            $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>"$member->first_name $member->last_name",'relationship'=>null,'detail'=>$data[$email_1_col],'notes'=>null));

                        // Remove any old contacts that were not recieved in this list
                        $this->cleanContacts($property->pid, $contactsRecieved);
                    }
                }
                else
                {
                    $results_action .= 'Could not import row, PolicyHolder did not save. Check Error File for Details. ';
                    $data[$errors_col] = 'Could not import member details (neither member nor prop saved). Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                    if($member->isNewRecord)
                        $new_members--;
                    else
                        $updated_members--;
                }

            } //end don't skip if

            //add key details and what happened to results file
            fputcsv($results_fh, array($results_mid, $results_pid, $client, $member_num, $policy, $results_action));

            //update process status
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Cancelled Props: $canceled_props,  Errors: $errors)");

        } //end main loop

        //Cancel routine for FULL PIF imports only
        if($type === 'Full')
        {
            //loop through all client properties in the database that did not get their flags set to 1 and set their policy_status to canceled
            $this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
            $props_to_cancel = Yii::app()->db->createCommand()
                ->select('p.pid')
                ->from('properties p')
                ->where("type_id = (SELECT id FROM properties_type WHERE [type] = 'PIF') AND p.flag = 0 AND (p.client_id = ".$saf_client->id." OR p.client_id = ".$lm_client->id.") AND p.policy_status != 'canceled'")
                ->queryAll();
            foreach($props_to_cancel as $property)
            {
                $prop_to_cancel = Property::model()->findByPk($property['pid']);
                $results_action = 'Set Policy Status to Canceled because it was not in the Full PIF.';
                $prop_to_cancel->policy_status = 'canceled';
                $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
                if(!$prop_to_cancel->save())
                {
                    $results_action = 'Error on saveing property being cancelled. See Error File for Details';
                    fputcsv($error_fh, array("ERROR cancelling property policy (on save), Details: " . var_export($prop_to_cancel->getErrors(), true) . ' ROW VALUES: ' . var_export($prop_to_cancel, true)));
                    $errors++;
                }
                else
                    $canceled_props++;

                //add to results file with the action that was done
                fputcsv($results_fh, array($prop_to_cancel->member_mid, $prop_to_cancel->pid, $prop_to_cancel->member->client, $prop_to_cancel->member->member_num, $prop_to_cancel->policy, $results_action));
            }
        }

        //clean up and finish
        fclose($error_fh);
        fclose($results_fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Done with LM/SAF Import ($type). Processed $counter rows (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props,  Errors: $errors)");
    }

    /**
     * Helper function that outputs a given message as well as updates the import_file database record with the status as well.
     *
     * @param ImportFile $fileToImport
     * @param string $status
     * @param string $msg
     * @return null
     */
    private function printUpdateStatus($fileToImport, $status, $msg)
    {
        print $msg."\n";
        Yii::app()->db->createCommand()->update('import_files', array('status'=>$status, 'details'=>$msg),'id = ' . $fileToImport['id']);
    }

    /**
     * One time function to run through all usaa properties with a pre_risk entry and set their pr_status accordingly.
     * Likely can be removed
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function update_completed_pr_props($fileToImport)
    {
        print "Starting PR Completed Prop Update!\n";

        //openfile and db cnxn.
        $connection = Yii::app()->db;
        $command = $connection->createCommand();

        //setup log_file
        $log_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_log.txt', strtolower($fileToImport['file_path'])), 'w');

        //loop through all USAA properties in the database that did not get their pre_risk_status updated when got set to complete
        $completed_pre_risks = PreRisk::model()->with('property')->findAll("status = 'COMPLETED - Delivered to Member' AND property_pid IS NOT NULL AND property.pre_risk_status != 'enrolled'");
        $counter = 0;
        $total = count($completed_pre_risks);
        print "Starting Update Props with completed PR Loop (for $total props)\n";
        foreach($completed_pre_risks as $pre_risk)
        {
            $log = "";
            $counter++;

            $pre_risk->property->pre_risk_status = 'enrolled';
            $pre_risk->property->pr_status_date = date('Y-m-d H:i:s');
            if(!$pre_risk->property->save())
            {
                print 'Error updating property. Details: ' . var_export($pre_risk->property->getErrors(), true) . "\n";
                $log .= 'Error updating property. Details: ' . var_export($pre_risk->property->getErrors(), true) . "\n";
            }
            else
            {
                $log .= "Updated Property with 'enrolled' pre_risk status (pid: ".$pre_risk->property->pid.")\n";
            }

            if($counter % 100 == 0)
            {
                print "Processed $counter of $total in update props with completed PR loop\n";
            }
            fwrite($log_fh, $log."\n");
        }

        //close up
        fclose($log_fh);
        $command->update('import_files', array('status'=>'Finished'), 'id = ' . $fileToImport['id']);
        print "Done with Property status update for completed PRs\n";

    }

    /**
     * Takes a csv file with simply 2 columns (member number, date) in it and updates all properties for that USAA member so that the fs_status = offered
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function updateUSAAFSOffered($fileToImport)
    {
        print "Starting usaa fs offerd update import\n";
        $file_path = $fileToImport['file_path'];
        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');
        if(($fh === false))
        {
            $this->printUpdateStatus($fileToImport, "Error", "Error Details: Could not open file. Check path.");
            return false;
        }
        //get header rows
        $header_fields = fgetcsv($fh);
        //make them all lower case
        $header_fields = array_map('strtolower', $header_fields);
        //check to make sure required headers are in file
        $fields_to_check = array('member number', 'date');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                print "ERROR missing column with header: ".$field."\n";
                print_r($header_fields);
                return false;
            }
        }

        //column indexes
        $member_num_col = array_search('member number', $header_fields);
        $offered_date_col = array_search('date', $header_fields);

        $counter = 0;
        $success = 0;
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>'USAA'));
            foreach($member->properties as $property)
            {
                //check if there is a pre risk entry and if its set to completed, do not offer it per USAA restrictions
                $pre_risk = PreRisk::model()->findByAttributes(array('status'=>'COMPLETED - Delivered to Member', 'property_pid'=>$property->pid));
                if(isset($pre_risk) || $property->pre_risk_status == 'enrolled' || $property->fireshield_status == 'ineligible')
                {
                    //print "Warning: Property has a completed Pre Risk or fs_status was ineligible, pid = ".$property->pid."\n";
                }
                else
                {
                    $property->geog = Yii::app()->db->createCommand('SELECT geog.STAsText() FROM properties WHERE pid = :pid')->bindValue(':pid', $property->pid, PDO::PARAM_INT)->queryScalar();
                    $property->fireshield_status = 'offered';
                    $property->fs_status_date = $data[$offered_date_col];
                    if($property->save())
                        $success++;
                    else
                        print "error with pid: ".$property->pid."\n";
                }
            }

            if($counter % 200 == 0)
            {
                print "Imported $counter member rows so far (props sucessfully set to FS Status of offered: $success)...\n";
                Yii::app()->db->createCommand()->update('import_files', array('status'=>'Processing',
                                            'details'=>'Imported ' . $counter . ' member rows so far (props successfully set to FS Status of offered: '. $success .')...',
                                            ),
                                'id = ' . $fileToImport['id']);
            }
        }

        $connection = Yii::app()->db;
        $command = $connection->createCommand();
        $command->update('import_files', array('status'=>'Finished',
                                        'details'=>"imported a total of $counter member rows (properties successfully set to FS Status of offered: $success)",
                                        ),
                            'id = ' . $fileToImport['id']);
        print "imported a total of $counter member rows and sucessfully updated $success properties to FS Status of offered\n";
        print "done with usaa fs offerd update import\n";
    }

    /**
     * takes a csv file that has Property ID and Geo Risk columns and updates the properties in the database accordingly
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function updatePropGeoRisk($fileToImport)
    {
        $file_path = $fileToImport['file_path'];
        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');
        if(($fh === false))
        {
            $this->printUpdateStatus($fileToImport, "Error", "Error Details: Could not open file. Check path.");
            return false;
        }
        //get header rows
        $header_fields = fgetcsv($fh);
        //make them all lower case
        $header_fields = array_map('strtolower', $header_fields);
        //check to make sure required headers are in file
        $fields_to_check = array('p_id', 'geo_risk');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: missing column with header: ".$field);
                return false;
            }
        }

        //column indexes
        $pid_col = array_search('p_id', $header_fields);
        $geo_risk_col = array_search('geo_risk', $header_fields);

        $counter = 0;
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            //only 1,2,3, or 99 are allowed for this field. 99 indicates "unmatched" in the GIS system
            if($data[$geo_risk_col] == 1 || $data[$geo_risk_col] == 2 || $data[$geo_risk_col] == 3 || $data[$geo_risk_col] == 99)
                Yii::app()->db->createCommand()->update('properties', array('geo_risk'=>$data[$geo_risk_col]),'pid = ' . $data[$pid_col]);

            if($counter % 1000 == 0)
            {
                print "Imported $counter rows so far...\n";
                Yii::app()->db->createCommand()->update('import_files', array('status'=>'Processing',
                                            'details'=>'Imported ' . $counter . ' rows so far...',
                                            ),
                                'id = ' . $fileToImport['id']);
            }
        }

        $connection = Yii::app()->db;
        $command = $connection->createCommand();
        $command->update('import_files', array('status'=>'Finished',
                                        'details'=>"imported a total of $counter rows",
                                        ),
                            'id = ' . $fileToImport['id']);
        print "imported a total of $counter rows\n";
        print "done with geo risk update import\n";
    }


    /**
    * function to update property.pr_status for existing pre_risk reports from a csv file (see below for required columns)
    * This is likely a one time use function, but keeping here for reference
    *
    * @param ImportFile $fileToImport is a dataRow from the import_files table
    * @return null
    */
    private function importPRStatusFile($fileToImport)
    {
        //open update file
        $file_path = $fileToImport['file_path'];
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$file_path, 'r');
        //get header rows. NEEDS: MEMBER NUMBER, STREET ADDRESS, ZIP CODE, HA DATE
        $header_fields = fgetcsv($fh);
        //check to make sure required headers are in file
        $fields_to_check = array('MEMBER NUMBER', 'STREET ADDRESS', 'ZIP CODE', 'HA DATE');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                print "ERROR missing column with header: ".$field."\n";
                return false;
            }
        }

        //setup error file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$file_path.'_error.csv', 'w');
        $header_fields[] = 'Errors';
        fputcsv($error_fh, $header_fields);

        //column indexes
        $member_num_col = array_search('MEMBER NUMBER', $header_fields);
        $address_col = array_search('STREET ADDRESS', $header_fields);
        $zip_col = array_search('ZIP CODE', $header_fields);
        $date_col = array_search('HA DATE', $header_fields);
        $errors_col = array_search('Errors', $header_fields);

        //counters and temp vars
        $rows_updated = 0;
        $counter = 0;

        print "starting update\n";
        //loop through each row in the file storing the data fields in a temp array
        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>'USAA'));
            if(!isset($member))
            {
                print 'could not find member: '.$data[$member_num_col]."\n";
                $data[$errors_col] = 'Could not update properties Pre Risk Status/date because the MEMBER NUMBER did not exist in the members database table.';
                fputcsv($error_fh, $data);
            }
            else //member found
            {
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'address_line_1'=>$data[$address_col], 'zip'=>$data[$zip_col]));
                if(!isset($property))
                {
                    print 'could not find member ('.$data[$member_num_col].') property: '.$data[$address_col].' '.$data[$zip_col]."\n";
                    $data[$errors_col] = 'Could not update properties Pre Risk Status/date because the members property (STREET ADDRESS + ZIP) did not exist for this Member in the properties database table.';
                    fputcsv($error_fh, $data);
                }
                else
                {
                    if(empty($data[$date_col]))
                    {
                        $property->pre_risk_status = 'offered';
                        $property->pr_status_date = date('Y-m-d H:i:s');
                    }
                    else
                    {
                        $property->pre_risk_status = 'enrolled';
                        $property->pr_status_date = $data[$date_col];
                    }
                    if($property->save())
                        $rows_updated++;
                    else
                    {
                        print "error saving the property with the updated PR status and date. Details: ". preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true))) ."\n";
                        $data[$errors_col] = 'error saving the property with the updated PR status and date. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    }
                }
            }

            if($counter % 100 == 0)
            {
                print "Attempted to Update $counter rows so far ($rows_updated successfully)...\n";
            }
        }

        $connection = Yii::app()->db;
        $command = $connection->createCommand();
        $command->update('import_files', array('status'=>'Finished',
                                        'details'=>"updated $rows_updated out of $counter",
                                        ),
                            'id = ' . $fileToImport['id']);
        print "updated $rows_updated out of $counter \n";
        print "done with PR update import\n";
    }

    /**
    * Import method for new tilde seperated Full USAA PIF file
    * it will add any new members and properties that are not found in the database, and update ones that are, and then cancel any remaining that were not in the file
    * NOTE: BEFORE RUNNING THIS SCRIPT SET ALL THE properties.flag's TO NULL. This will allow you to set all the properties to policy_status=cancelled at the end.
    * NOTE: Has been awhile since this has been run, might need to be reviewed and worked on before ever using again (i.e. the auto-enrollment stuff isn't in here...)
    * Outputs both a results log and error log file
    *
    * @param ImportFile $fileToImport
    * @return null
    */
    private function mergeUSAAFullPIF($fileToImport)
    {
        print "Starting New USAA Full PIF Merge Import!\n";

        //openfile and db cnxn.
        $connection = Yii::app()->db;
        $command = $connection->createCommand();
        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');
        if(($fh === false))
        {
            $this->printUpdateStatus($fileToImport, "Error", "Error Details: Could not open file. Check path.");
            return false;
        }

        //get headers/indexes
        $header_fields = fgetcsv($fh, null, '~');

        $fields_to_check = array('USAA-NR', 'RTD-CO-SHA', 'SALUTATION', 'RANK-CD', 'FIRST-NAME', 'MIDDLE-NAME', 'LAST-NAME', 'CEL-PH-NR', 'RES-PH-NR', 'BUS-PH-NR', 'CUST-ADDRESS1', 'CUST-ADDRESS2', 'CUST-CITY',
            'CUST-ZIP', 'CUST-ZIP-SUPP-CD', 'CUST-STATE', 'LOB-CODE', 'PROP-ITEM-CLASS-DC', 'ROF-TYP-DC', 'PRI-EMAIL-ADR', 'SEC-EMAIL-ADR', 'OLA-IND', 'STD-LOC-CD', 'CUST-SPCL-HNDL-CD', 'PRP-LOC-STR-TXT1',
            'PRP-LOC-STR-TXT2', 'PROP-LOC-CITY-NM', 'PROP_LOC-CO-NM', 'PROP-ZIP-CODE', 'PROP-LOC-ZIP-SUPPL', 'PROP-LOC-ST-CD', 'DSP-POL-NR', 'LAT-NR', 'LNG-NR', 'RLT-HIE-LEV-DC', 'DWG-AOI-AMT', 'POL-EFF-DT',
            'POL-EXP-DT', 'CUST-SPOUSE-NR', 'SPOUSE-FIRST-NAME', 'SPOUSE-MIDDLE-NAME', 'SPOUSE-LAST-NAME', 'SPOUSE-SALUTATION', 'SPOUSE-RANK', 'INS-BRS-SUP-IND', 'INS-RCV-DT');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                print "ERROR missing column with header: ".$field."\n";
                return false;
            }
        }
        $member_num_col = array_search('USAA-NR', $header_fields);
        $rated_company_col = array_search('RTD-CO-SHA', $header_fields);
        $salutation_col = array_search('SALUTATION', $header_fields);
        $rank_col = array_search('RANK-CD', $header_fields);
        $first_name_col = array_search('FIRST-NAME', $header_fields);
        $middle_name_col = array_search('MIDDLE-NAME', $header_fields);
        $last_name_col = array_search('LAST-NAME', $header_fields);
        $cell_phone_col = array_search('CEL-PH-NR', $header_fields);
        $home_phone_col = array_search('RES-PH-NR', $header_fields);
        $work_phone_col = array_search('BUS-PH-NR', $header_fields);
        $mailing_address_line_1_col = array_search('CUST-ADDRESS1', $header_fields);
        $mailing_address_line_2_col = array_search('CUST-ADDRESS2', $header_fields);
        $mailing_city_col = array_search('CUST-CITY', $header_fields);
        $mailing_state_col = array_search('CUST-STATE', $header_fields);
        $mailing_zip_col = array_search('CUST-ZIP', $header_fields);
        $mailing_zip_supp_col = array_search('CUST-ZIP-SUPP-CD', $header_fields);
        $lob_col = array_search('LOB-CODE', $header_fields);
        $dwelling_type_col = array_search('PROP-ITEM-CLASS-DC', $header_fields);
        $roof_type_col = array_search('ROF-TYP-DC', $header_fields);
        $email_1_col = array_search('PRI-EMAIL-ADR', $header_fields);
        $email_2_col = array_search('SEC-EMAIL-ADR', $header_fields);
        $signed_ola_col = array_search('OLA-IND', $header_fields);
        $spec_handling_code_col = array_search('CUST-SPCL-HNDL-CD', $header_fields);
        $address_line_1_col = array_search('PRP-LOC-STR-TXT1', $header_fields);
        $address_line_2_col = array_search('PRP-LOC-STR-TXT2', $header_fields);
        $city_col = array_search('PROP-LOC-CITY-NM', $header_fields);
        $county_col = array_search('PROP_LOC-CO-NM', $header_fields);
        $zip_col = array_search('PROP-ZIP-CODE', $header_fields);
        $zip_supp_col = array_search('PROP-LOC-ZIP-SUPPL', $header_fields);
        $state_col = array_search('PROP-LOC-ST-CD', $header_fields);
        $policy_col = array_search('DSP-POL-NR', $header_fields);
        $lat_col = array_search('LAT-NR', $header_fields);
        $long_col = array_search('LNG-NR', $header_fields);
        $geocode_level_col = array_search('RLT-HIE-LEV-DC', $header_fields);
        $coverage_a_amt_col = array_search('DWG-AOI-AMT', $header_fields);
        $policy_effective_col = array_search('POL-EFF-DT', $header_fields);
        $policy_expiration_col = array_search('POL-EXP-DT', $header_fields);
        $spouse_member_num_col = array_search('CUST-SPOUSE-NR', $header_fields);
        $spouse_first_name_col = array_search('SPOUSE-FIRST-NAME', $header_fields);
        $spouse_middle_name_col = array_search('SPOUSE-MIDDLE-NAME', $header_fields);
        $spouse_last_name_col = array_search('SPOUSE-LAST-NAME', $header_fields);
        $spouse_salutation_col = array_search('SPOUSE-SALUTATION', $header_fields);
        $spouse_rank_col = array_search('SPOUSE-RANK', $header_fields);
        $brushfire_inspect_col = array_search('INS-BRS-SUP-IND', $header_fields);
        $brushfire_inspect_date_col = array_search('INS-RCV-DT', $header_fields);
        $analysis_col = array_search('WDS MERGE ANALYSIS', $header_fields);

        //setup error_file and log_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'Errors';
        $errors_col = array_search('Errors', $header_fields);
        fputcsv($error_fh, $header_fields);
        $log_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_log.txt', strtolower($fileToImport['file_path'])), 'w');

        //loop through each line and try to lookup property/member in the database
        print "Starting Add/Update Loop\n";
        $counter = 0;
        while(($data = fgetcsv($fh, null, '~')) !== FALSE)
        {
            $counter++;
            $log = "";
            if($counter > 0)
            {
                //do checks: if member exists, if property exists, write to output file results for each line
                //strip 0s from front of mem num
                $data[$member_num_col] = ltrim($data[$member_num_col], '0');
                $data[$policy_effective_col] .= ' 00:00:00.0000000';
                $data[$policy_expiration_col] .= ' 00:00:00.0000000';
                $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_num_col], 'client'=>'USAA'));
                if(!isset($member)) //didn't find member, so make new one and a new property
                {
                    $member = new Member();
                    $property = new Property();
                    $log .= "Creating New Member (Mem #: ".$data[$member_num_col].") and New Property for that Member (Policy: ".$data[$policy_col].")\n";
                }
                else //existing member
                {
                    //check if property already exists for member
                    $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>$data[$policy_col], 'location'=>'1'));
                    if(!isset($property)) //didnt find property, so make new one
                    {
                        $property = new Property();
                        $log .= "Creating New Property (Policy: ".$data[$policy_col]." for existing Member (Mem #".$data[$member_num_col].", mid: ".$member->mid.") AND updating member info\n";
                    }
                    else
                    {
                        $log .= "Updating existing Member (Mem #".$data[$member_num_col].", mid: ".$member->mid.") and Property, updating both with info from PIF (if changed at all then before and after values are below)\n";
                    }
                }

                $mem_orig_attr = $member->attributes;
                $prop_orig_attr = $property->attributes;

                $property->flag = 1; //this sets properties that are in db to 1, anything thats null or not 1 should be a cancelled prop
                //property fields to update/insert
                $property->rated_company = trim($data[$rated_company_col]);
                $member->salutation = trim($data[$salutation_col]);
                $member->rank = trim($data[$rank_col]);
                $member->first_name = trim($data[$first_name_col]);
                $member->middle_name = trim($data[$middle_name_col]);
                $member->last_name = trim($data[$last_name_col]);
                if(trim($data[$cell_phone_col]) != '0000000000')
                    $member->cell_phone = trim($data[$cell_phone_col]);
                if(trim($data[$home_phone_col]) != '0000000000')
                    $member->home_phone = trim($data[$home_phone_col]);
                if(trim($data[$work_phone_col]) != '0000000000')
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
                //if(!(isset($property->geocode_level) && $property->geocode_level == 'WDS')) //if WDS already set this, then leave it be, otherwise set it
                //{
                    if($property->isNewRecord || round($property->lat, 14) !==  round($data[$lat_col], 14) || round($property->long, 14) !==  round($data[$long_col], 14))
                    {
                        $property->lat = trim($data[$lat_col]);
                        $property->long = trim($data[$long_col]);
                    }
                    $property->geocode_level = trim($data[$geocode_level_col]);
                //}
                $property->coverage_a_amt = trim($data[$coverage_a_amt_col]);
                $property->policy_effective = trim($data[$policy_effective_col]);
                $property->policy_expiration = trim($data[$policy_expiration_col]);
                $member->spouse_member_num = trim($data[$spouse_member_num_col]);
                $member->spouse_first_name = trim($data[$spouse_first_name_col]);
                $member->spouse_middle_name = trim($data[$spouse_middle_name_col]);
                $member->spouse_last_name = trim($data[$spouse_last_name_col]);
                $member->spouse_salutation = trim($data[$spouse_salutation_col]);
                $member->spouse_rank = trim($data[$spouse_rank_col]);
                if(trim($data[$brushfire_inspect_col]) != '')
                    $property->brushfire_inspect = trim($data[$brushfire_inspect_col]);
                if(trim($data[$brushfire_inspect_date_col]) != '')
                    $property->brushfire_inspect_date = trim($data[$brushfire_inspect_date_col]);

                //default values
                $member->client_id = 1;
                $member->client = 'USAA';
                if($member->isNewRecord)
                {
                    $member->member_num = trim($data[$member_num_col]);
                    $member->mem_fireshield_status = 'not enrolled';
                    $member->mem_fs_status_date = date('Y-m-d H:i:s');
                }
                else if(count(array_diff_assoc($mem_orig_attr, $member->attributes)) > 0) //if not a new record and the attributes have changed lets put them in the log
                {
                    $log .= "Original Member Attributes: ".json_encode($mem_orig_attr)."\n";
                    $log .= "Updated  Member Attributes: ".json_encode($member->attributes)."\n";
                }

                $property->policy_status = 'active'; //if its in the full pif then its an active policy even if it already exists and is set to canceled
                if($property->isNewRecord)
                {
                    $property->policy = trim($data[$policy_col]);

                    $property->policy_status_date = date('Y-m-d H:i:s');
                    $property->response_status = 'not enrolled';
                    $property->pre_risk_status = 'not enrolled';
                    if($member->mem_fireshield_status == 'offered' || $member->mem_fireshield_status == 'enrolled') //if this is an existing member and they have been offered FS, then need to make new prop also offered for FS
                        $property->fireshield_status = 'offered';
                    else
                        $property->fireshield_status = 'not enrolled';
                    $property->fs_status_date = date('Y-m-d H:i:s');
                    $property->res_status_date = date('Y-m-d H:i:s');
                    $property->pr_status_date = date('Y-m-d H:i:s');
                }
                else if(count(array_diff_assoc($prop_orig_attr, $property->attributes)) > 0) //if not a new record and the attributes have changed lets put them in the log
                {
                    $log .= "Original Property Attributes: ".json_encode($prop_orig_attr)."\n";
                    $log .= "Updated  Property Attributes: ".json_encode($property->attributes)."\n";
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
                            $log .= "ERROR saving property, see error file for details\n";
                        }
                    }
                    else
                    {
                        print 'Could not import row. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                        $data[$errors_col] = 'Could not import Member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                        fputcsv($error_fh, $data);
                        $log .= "ERROR saving member, see error file for details\n";
                    }
                }
                else //no dwelling types that != Dwelling are allowed
                {
                    print 'Could not import row. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Could not import property for member. Details: Dwelling Type of '.$property->dwelling_type.' is not accepted (only type `Dwelling` is allowed)';
                    fputcsv($error_fh, $data);
                    $log .= "ERROR saving property, see error file for details\n";
                }

                if($counter % 100 == 0)
                {
                    print "Processed $counter rows in analysis loop\n";
                }
            }
            fwrite($log_fh, $log."\n");
        }

        //close up
        fclose($error_fh);
        fclose($log_fh);
        fclose($fh);
        $command->update('import_files', array('status'=>'Finished'), 'id = ' . $fileToImport['id']);
        print "Done with New USAA Full PIF merge Import\n";
    }

    /**
     * This funciton is for use after the above mergeUSAAFullPIF import is done to go through and cancel any that weren't flagged and that are still active
     * Outputs a results log file
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function postMergeUSAACancel($fileToImport)
    {
        print "Starting Post USAA PIF Merge Cancelations!\n";

        //openfile and db cnxn.
        $connection = Yii::app()->db;
        $command = $connection->createCommand();

        //setup log_file
        $log_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_log.txt', strtolower($fileToImport['file_path'])), 'w');

        //loop through all USAA properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = Property::model()->with('member')->findAll("flag IS NULL AND member.client = 'USAA' AND member.is_tester = 0 AND policy_status != 'canceled'");
        $counter = 0;
        $total = count($canceled_props);
        print "Starting Cancel Loop (for $total props)\n";
        foreach($canceled_props as $property)
        {
            $log = "";
            $counter++;

            $property->policy_status = 'canceled';
            $property->policy_status_date = date('Y-m-d H:i:s');
            if(!$property->save())
            {
                print 'Error on saveing property being cancelled. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $log .= "ERROR cancelling property policy (on save), Details: " . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
            }
            else
            {
                //----SPECIAL CASE TOWN HOUSE DEAL FROM CARSON/CASEY------//
                if($property->member->member_num == "535513" || $property->member->member_num == "1145883")
                {
                    $log .= 'IMPORTANT NOTE: SPECIAL CASE, member number '.$property->member->member_num." property was cancelled, need to cancel the attached one!!!\n";
                }
                $log .= "Canceled Property Policy (pid: ".$property->pid.")\n";
            }

            $reissueProperty = Property::model()->findByAttributes(array('member_mid'=>$property->member_mid, 'address_line_1'=>$property->address_line_1, 'policy_status'=>'active'));
            if(isset($reissueProperty)) //if there was a reissue policy property for this member with the same addy, then transfer over the status's and status dates
            {
                $reissueProperty->response_status = $property->response_status;
                $reissueProperty->pre_risk_status = $property->pre_risk_status;
                $reissueProperty->fireshield_status = $property->fireshield_status;
                $reissueProperty->fs_status_date = $property->fs_status_date;
                $reissueProperty->res_status_date = $property->res_status_date;
                $reissueProperty->response_enrolled_date = $property->response_enrolled_date;
                $reissueProperty->pr_status_date = $property->pr_status_date;
                $reissueProperty->save();
                if(!$reissueProperty->save())
                {
                    print 'Could not update reissue statuses. Details: ' . var_export($reissueProperty->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $log .= "ERROR updating reissue property policy with old statuses, Details: " . var_export($reissueProperty->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                }
                else
                    $log.= "Found reissue property policy (pid: ".$reissueProperty->pid.") for canceled one (pid: ".$property->pid.") and successfully transfered past statuses to it.\n";
            }

            if($counter % 100 == 0)
            {
                print "Processed $counter of $total in cancel policy loop\n";
            }
            fwrite($log_fh, $log."\n");
        }

        //close up
        fclose($log_fh);
        $command->update('import_files', array('status'=>'Finished'), 'id = ' . $fileToImport['id']);
        print "Done with Post USAA merge cancelations\n";
    }

    /**
     * This was likely a one-time use funciton to import a USAA sent list and flag any of the properties in the list likely for use to cancel the others later
     * Outputs an error log file
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function flagActiveUSAAProps($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting Flag Active USAA Props");
        //open file
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');
        //get header row
        $header_fields = fgetcsv($fh, null, '~');
        //trim them up
        $header_fields = array_map('trim', $header_fields);
        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'WDS-IMPORT-ERRORS';
        fputcsv($error_fh, $header_fields);

        //column indexes
        $member_num_col = array_search('USAA-NR', $header_fields);
        $policy_col = array_search('DSP-POL-NR', $header_fields);
        $errors_col = array_search('WDS-IMPORT-ERRORS', $header_fields);

        //index variables to track progress/results
        $counter = 0;
        $errors = 0;

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh, null, '~')) !== FALSE)
        {
            $counter++;
            //check if member already exists
            //trim up the attributes
            $data = array_map('trim', $data);
            //NOTE, NEW FILES HAVE LEADING 0's on member number, need to strip them
            $member_num = ltrim(trim($data[$member_num_col]), '0');
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client'=>'USAA'));
            if(!isset($member)) //didn't find member,
            {
                print "Could not find member $member_num \n";
                $data[$errors_col] = 'Could not find member';
                fputcsv($error_fh, $data);
                $errors++;
            }
            else //existing member
            {
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>trim($data[$policy_col]), 'location'=>'1'));
                if(!isset($property)) //didnt find property
                {
                    print "Could not find prop ".trim($data[$policy_col])." under member $member_num \n";
                    $data[$errors_col] = 'Could not find prop under mem';
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else
                {
                    $property->flag = 1; //this sets properties that are in PIF to 1
                    if(!$property->save())
                    {
                        print 'Could not import row. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                        $data[$errors_col] = 'Could not import property for member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                        fputcsv($error_fh, $data);
                        $errors++;
                    }
                }
            }
            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Errors: $errors");

        }//end of main while loop that goes through each row of import file

        fclose($error_fh);
        fclose($fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Imported $counter rows so far (Errors: $errors");
    }

    /**
     * Imports the weekly incremental USAA lists that contain transactions. Will do both types, the add/drop and the change.
     * NOTEs: This is incremental so has no cancel routine. It should be run add/drop first then change.
     * Outputs an error log file
     *
     * @param ImportFile $fileToImport
     * @return null
     */
    private function transDateSensImportUSAAPIF($fileToImport)
    {
        $this->printUpdateStatus($fileToImport, "Processing", "Starting USAA Add/Drop/Change PIF Import (transaction date sensitive version).");

        //open file
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');
        if(($fh === false))
        {
            $this->printUpdateStatus($fileToImport, "Error", "Error Details: Could not open file. Check path.");
            return false;
        }
        //get header row
        $header_fields = fgetcsv($fh, null, '~');
        //trim them up
        $header_fields = array_map('trim', $header_fields);

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'WDS-IMPORT-ERRORS';
        fputcsv($error_fh, $header_fields);

        //check to make sure required headers are in file
        if($fileToImport['type'] == 'USAA - Change PIF')
        {
            $fields_to_check = array('USAA-NR','RTD-CO-SHA','SALUTATION','RANK-CD', 'FIRST-NAME', 'MIDDLE-NAME', 'LAST-NAME', 'CEL-PH-NR', 'RES-PH-NR', 'BUS-PH-NR', 'CUST-ADDRESS1', 'CUST-ADDRESS2', 'CUST-CITY',
            'CUST-STATE', 'CUST-ZIP', 'CUST-ZIP-SUPP-CD', 'LOB-CODE', 'PROP-ITEM-CLASS-DC', 'ROF-TYP-DC', 'PRI-EMAIL-ADR', 'SEC-EMAIL-ADR', 'OLA-IND', 'STD-LOC-CD', 'CUST-SPCL-HNDL-CD', 'PRP-LOC-STR-TXT1', 'PRP-LOC-STR-TXT2',
            'PROP-LOC-CITY-NM', 'PROP_LOC-CO-NM', 'PROP-ZIP-CODE', 'PROP-LOC-ZIP-SUPPL', 'PROP-LOC-ST-CD', 'DSP-POL-NR', 'LAT-NR', 'LNG-NR',
            'RLT-HIE-LEV-DC', 'DWG-AOI-AMT', 'POL-EFF-DT', 'POL-EXP-DT', 'CUST-SPOUSE-NR', 'SPOUSE-FIRST-NAME', 'SPOUSE-MIDDLE-NAME', 'SPOUSE-LAST-NAME', 'SPOUSE-SALUTATION',
            'SPOUSE-RANK', 'INS-BRS-SUP-IND', 'INS-RCV-DT', 'MULT_FAM_IND');

        }
        elseif($fileToImport['type'] == 'USAA - Add Drop PIF')
        {
            $fields_to_check = array('USAA-NR','RTD-CO-SHA','SALUTATION','RANK-CD', 'FIRST-NAME', 'MIDDLE-NAME', 'LAST-NAME', 'CEL-PH-NR', 'RES-PH-NR', 'BUS-PH-NR', 'CUST-ADDRESS1', 'CUST-ADDRESS2', 'CUST-CITY',
            'CUST-STATE', 'CUST-ZIP', 'CUST-ZIP-SUPP-CD', 'LOB-CODE', 'PROP-ITEM-CLASS-DC', 'ROF-TYP-DC', 'PRI-EMAIL-ADR', 'SEC-EMAIL-ADR', 'OLA-IND', 'STD-LOC-CD', 'CUST-SPCL-HNDL-CD', 'PRP-LOC-STR-TXT1', 'PRP-LOC-STR-TXT2',
            'PROP-LOC-CITY-NM', 'PROP_LOC-CO-NM', 'PROP-ZIP-CODE', 'PROP-LOC-ZIP-SUPPL', 'PROP-LOC-ST-CD', 'DSP-POL-NR', 'LAT-NR', 'LNG-NR',
            'RLT-HIE-LEV-DC', 'DWG-AOI-AMT', 'POL-EFF-DT', 'POL-EXP-DT', 'CUST-SPOUSE-NR', 'SPOUSE-FIRST-NAME', 'SPOUSE-MIDDLE-NAME', 'SPOUSE-LAST-NAME', 'SPOUSE-SALUTATION',
            'SPOUSE-RANK', 'CAN_POL_IND', 'ISS_POL_IND', 'NON_RNW_IND', 'POL_REW_IND', 'POL_REI_IND', 'SCNDRY_IATV_RSN_DC', 'TRANS_EFFECT_DT', 'MULT_FAM_IND');
        }
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "ERROR missing column with header: ".$field);
                return false;
            }
        }

        //column indexes
        $member_num_col = array_search('USAA-NR', $header_fields);
        $rated_company_col = array_search('RTD-CO-SHA', $header_fields);
        $salutation_col = array_search('SALUTATION', $header_fields);
        $rank_col = array_search('RANK-CD', $header_fields);
        $first_name_col = array_search('FIRST-NAME', $header_fields);
        $middle_name_col = array_search('MIDDLE-NAME', $header_fields);
        $last_name_col = array_search('LAST-NAME', $header_fields);
        $cell_phone_col = array_search('CEL-PH-NR', $header_fields);
        $home_phone_col = array_search('RES-PH-NR', $header_fields);
        $work_phone_col = array_search('BUS-PH-NR', $header_fields);
        $mailing_address_line_1_col = array_search('CUST-ADDRESS1', $header_fields);
        $mailing_address_line_2_col = array_search('CUST-ADDRESS2', $header_fields);
        $mailing_city_col = array_search('CUST-CITY', $header_fields);
        $mailing_state_col = array_search('CUST-STATE', $header_fields);
        $mailing_zip_col = array_search('CUST-ZIP', $header_fields);
        $mailing_zip_supp_col = array_search('CUST-ZIP-SUPP-CD', $header_fields);
        $lob_col = array_search('LOB-CODE', $header_fields);
        $dwelling_type_col = array_search('PROP-ITEM-CLASS-DC', $header_fields);
        $roof_type_col = array_search('ROF-TYP-DC', $header_fields);
        $email_1_col = array_search('PRI-EMAIL-ADR', $header_fields);
        $email_2_col = array_search('SEC-EMAIL-ADR', $header_fields);
        $signed_ola_col = array_search('OLA-IND', $header_fields);
        $spec_handling_code_col = array_search('CUST-SPCL-HNDL-CD', $header_fields);
        $address_line_1_col = array_search('PRP-LOC-STR-TXT1', $header_fields);
        $address_line_2_col = array_search('PRP-LOC-STR-TXT2', $header_fields);
        $city_col = array_search('PROP-LOC-CITY-NM', $header_fields);
        $county_col = array_search('PROP_LOC-CO-NM', $header_fields);
        $zip_col = array_search('PROP-ZIP-CODE', $header_fields);
        $zip_supp_col = array_search('PROP-LOC-ZIP-SUPPL', $header_fields);
        $state_col = array_search('PROP-LOC-ST-CD', $header_fields);
        $policy_col = array_search('DSP-POL-NR', $header_fields);
        $lat_col = array_search('LAT-NR', $header_fields);
        $long_col = array_search('LNG-NR', $header_fields);
        $geocode_level_col = array_search('RLT-HIE-LEV-DC', $header_fields);
        $coverage_a_amt_col = array_search('DWG-AOI-AMT', $header_fields);
        $policy_effective_col = array_search('POL-EFF-DT', $header_fields);
        $policy_expiration_col = array_search('POL-EXP-DT', $header_fields);
        $spouse_member_num_col = array_search('CUST-SPOUSE-NR', $header_fields);
        $spouse_first_name_col = array_search('SPOUSE-FIRST-NAME', $header_fields);
        $spouse_middle_name_col = array_search('SPOUSE-MIDDLE-NAME', $header_fields);
        $spouse_last_name_col = array_search('SPOUSE-LAST-NAME', $header_fields);
        $spouse_salutation_col = array_search('SPOUSE-SALUTATION', $header_fields);
        $spouse_rank_col = array_search('SPOUSE-RANK', $header_fields);
        $multi_fam_ind_col = array_search('MULT_FAM_IND', $header_fields);

        if($fileToImport['type'] != 'USAA - Add Drop PIF')//these columns are not in the add/drop lists
        {
            $brushfire_inspect_col = array_search('INS-BRS-SUP-IND', $header_fields);
            $brushfire_inspect_date_col = array_search('INS-RCV-DT', $header_fields);
        }

        if($fileToImport['type'] == 'USAA - Add Drop PIF')
        {
            $issue_col = array_search('ISS_POL_IND', $header_fields); //issue
            $cancel_col = array_search('CAN_POL_IND', $header_fields); //cancel
            $non_renew_col = array_search('NON_RNW_IND', $header_fields); //non-renew
            $rewrite_col = array_search('POL_REW_IND', $header_fields); //re-write
            $reinstate_col = array_search('POL_REI_IND', $header_fields); //reinstate
            $transaction_effective_col = array_search('TRANS_EFFECT_DT', $header_fields);
        }
        $errors_col = array_search('WDS-IMPORT-ERRORS', $header_fields);

        //temp exception list for usaa auto enroll (only needed for first run, commenting out but leaving here for possible future auto-enroll implements that it may be needed again)
        //$temp_mem_prop_num_exception_list = $this->load_usaa_exception_list('usaa_ca_auto_enroll_first_week_exceptions.csv');

        //temp OK exception list that should only be filled on the Feb 13th 2017 list run
        $ok_temp_mem_prop_num_exception_list = array();
        if(strpos($fileToImport['file_path'], 'D170213') !== false)
            $ok_temp_mem_prop_num_exception_list = $this->load_usaa_exception_list('usaa_ok_auto_enroll_first_week_exceptions.csv');


        //index variables to track progress/results
        $counter = 0;
        $updated_members = 0;
        $new_members = 0;
        $new_properties = 0;
        $updated_properties = 0;
        $errors = 0;

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($fh, null, '~')) !== FALSE)
        {
            $counter++;
            //check if member already exists
            //trim up the attributes
            $data = array_map('trim', $data);
            //NOTE, NEW FILES HAVE LEADING 0's on member number, need to strip them
            $member_num = ltrim(trim($data[$member_num_col]), '0');
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client'=>'USAA'));
            if(!isset($member)) //didn't find member, so make new one and a new property
            {
                $member = new Member();
                $property = new Property();
                $new_members++;
                $new_properties++;
            }
            else //existing member
            {
                $updated_members++;
                //check if property already exists for member
                $property = Property::model()->findByAttributes(array('member_mid'=>$member->mid, 'policy'=>trim($data[$policy_col]), 'location'=>'1'));
                if(!isset($property)) //didnt find property, so make new one
                {
                    $property = new Property();
                    $new_properties++;
                }
                else
                {
                    $updated_properties++;
                    //check to see if current property is cancelled but is coming over in change file
                    // taking this check out after discussing with USAA data team. It is a frequent occurance that will continue happening and is expected, so no need to check for it. TCC 9/25/2015
                    //if($fileToImport['type'] == 'USAA - Change PIF' && $property->policy_status == 'canceled')
                    //{
                    //    print "WARNING: Policy was in change list, but has a WDS policy_status = canceled.\n";
                    //    $data[$errors_col] = 'WARNING: Policy was in change list, but has a WDS policy_status = canceled.';
                    //    fputcsv($error_fh, $data);
                    //    $errors++;
                    //}
                }
            }

            //---set/map attributes section---//
            //$property->flag = 1; //this sets properties that are in PIF to 1
            $renewal_flag = false; //for tracking if its a renewal...need this in case it was already set to renewal...only want to track renewals for this file
            $new_transaction = ''; //for tracking transaction...need this in case it was already set...only want to track what it is for this file
            if($fileToImport['type'] == 'USAA - Add Drop PIF')
            {
                //transaction info
                if($data[$issue_col] == '1')
                    $property->transaction_type = 'issue';
                else if($data[$non_renew_col] == '1')
                    $property->transaction_type = 'non-renew';
                else if($data[$cancel_col] == '1')
                    $property->transaction_type = 'cancel';
                else if($data[$rewrite_col] == '1')
                    $property->transaction_type = 're-write';
                else if($data[$reinstate_col] == '1')
                    $property->transaction_type = 'reinstate';

                $new_transaction = $property->transaction_type;
                $property->transaction_effective = $data[$transaction_effective_col];
            }
            //if in the change file there is an existing property that has an updated expiration date, assume it is a 'renew' transaction
            else if($fileToImport['type'] == 'USAA - Change PIF' && !$property->isNewRecord)
            {
                $previous_exp_date = date('Y-m-d', strtotime($property->policy_expiration));
                $new_exp_date = date('Y-m-d', strtotime($data[$policy_expiration_col]));
                if($previous_exp_date !== $new_exp_date)
                {
                    $property->transaction_type = 'renew';
                    $new_transaction = 'renew';
                    $renewal_flag = true;
                    $property->transaction_effective = date('Y-m-d');
                }
            }
            //if it is in either file and doesn't exist in the WDS db yet and doesnt fall into one of the above cases, then treat it as a renew transaction
            else if($property->isNewRecord)
            {
                $property->transaction_type = 'renew';
                $new_transaction = 'renew';
                $renewal_flag = true;
                $property->transaction_effective = date('Y-m-d');
            }

            //set mem/prop attributes according to data row
            $property->rated_company = $data[$rated_company_col];
            $member->salutation = $data[$salutation_col];
            $member->rank = $data[$rank_col];
            $member->first_name = $data[$first_name_col];
            $member->middle_name = $data[$middle_name_col];
            $member->last_name = $data[$last_name_col];
            $member->cell_phone = $data[$cell_phone_col];
            $member->home_phone = $data[$home_phone_col];
            $member->work_phone = $data[$work_phone_col];
            $member->mail_address_line_1 = $data[$mailing_address_line_1_col];
            $member->mail_address_line_2 = $data[$mailing_address_line_2_col];
            $member->mail_city = $data[$mailing_city_col];
            $member->mail_zip = $data[$mailing_zip_col];
            $member->mail_zip_supp = $data[$mailing_zip_supp_col];
            $member->mail_state = $data[$mailing_state_col];
            $property->lob = $data[$lob_col];
            $property->dwelling_type = $data[$dwelling_type_col];
            $property->roof_type = $data[$roof_type_col];
            $property->city = $data[$city_col];
            $property->county = $data[$county_col];
            $property->zip_supp = $data[$zip_supp_col];
            $property->state = $data[$state_col];
            $property->address_line_1 = $data[$address_line_1_col];
            $property->address_line_2 = $data[$address_line_2_col];
            $property->zip = $data[$zip_col];
            $member->email_1 = $data[$email_1_col];
            $member->email_2 = substr($data[$email_2_col], 0, 50);
            $member->signed_ola = $data[$signed_ola_col];
            $member->spec_handling_code = $data[$spec_handling_code_col];
            //if(!(isset($property->geocode_level) && $property->geocode_level == 'WDS')) //if WDS already set this, then leave it be, otherwise set it
            //{
            if(!empty($data[$lat_col]))
                $property->lat = $data[$lat_col];
            if(!empty($data[$long_col]))
                $property->long = $data[$long_col];
            if(!empty($data[$geocode_level_col]))
                $property->geocode_level = $data[$geocode_level_col];
            //}
            $property->coverage_a_amt = $data[$coverage_a_amt_col];
            $property->policy_effective = $data[$policy_effective_col];
            $property->policy_expiration = $data[$policy_expiration_col];
            if($data[$multi_fam_ind_col] === 'Y')
                $property->multi_family = 1;
            else
                $property->multi_family = 0;

            $member->spouse_member_num = $data[$spouse_member_num_col];
            $member->spouse_first_name = $data[$spouse_first_name_col];
            $member->spouse_middle_name = $data[$spouse_middle_name_col];
            $member->spouse_last_name = $data[$spouse_last_name_col];
            $member->spouse_salutation = $data[$spouse_salutation_col];
            $member->spouse_rank = $data[$spouse_rank_col];
            if($fileToImport['type'] == 'USAA - Change PIF') //these columns are not in the add/drop lists
            {
                $property->brushfire_inspect = $data[$brushfire_inspect_col];
                $property->brushfire_inspect_date = $data[$brushfire_inspect_date_col];
            }

            //default values
            $member->client_id = 1;
            $member->client = 'USAA';
            if($member->isNewRecord)
            {
                $member->member_num = $member_num;
                $member->mem_fireshield_status = 'not enrolled';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
            }
            if($property->isNewRecord)
            {
                $property->policy = $data[$policy_col];
                $property->client_policy_id = $data[$policy_col];
                $property->response_status = 'not enrolled';
                $property->pre_risk_status = 'not enrolled';
                if($member->mem_fireshield_status == 'offered' || $member->mem_fireshield_status == 'enrolled') //if this is an existing member and they have been offered FS, then need to make new prop also offered for FS
                    $property->fireshield_status = 'offered';
                else
                    $property->fireshield_status = 'not enrolled';
                $property->fs_status_date = date('Y-m-d H:i:s');
                $property->res_status_date = date('Y-m-d H:i:s');
                $property->pr_status_date = date('Y-m-d H:i:s');
                $property->policy_status = $property->getUSAAPolicyStatus();
                $property->policy_status_date = date('Y-m-d H:i:s');
            }
            else //current property
            {
                $current_policy_status = $property->policy_status;
                $property->policy_status = $property->getUSAAPolicyStatus();
                if($current_policy_status != $property->policy_status) //if it changed
                    $property->policy_status_date = date('Y-m-d H:i:s');
                //otherwise dont update the date
            }

            //if there is another property/policy under this member with the same address then need to transfer persistant info (like policy/program statuses)
            if(!$member->isNewRecord && $property->isNewRecord) //if member exists already and this is a new prop
            {
                $otherProperty = Property::model()->find("member_mid = :mid AND address_line_1 = :address AND policy != :policy", array(':mid'=>$member->mid, ':address'=>$data[$address_line_1_col], ':policy'=>$data[$policy_col]));
                if(isset($otherProperty)) //if there is another policy property for this member with the same addy, then transfer over the status's and status dates
                {
                    $property->response_status = $otherProperty->response_status;
                    $property->pre_risk_status = $otherProperty->pre_risk_status;
                    $property->fireshield_status = $otherProperty->fireshield_status;
                    $property->fs_status_date = $otherProperty->fs_status_date;
                    $property->res_status_date = $otherProperty->res_status_date;
                    $property->response_enrolled_date = $otherProperty->response_enrolled_date;
                    $property->pr_status_date = $otherProperty->pr_status_date;
                    if((isset($otherProperty->geocode_level) && $otherProperty->geocode_level == 'WDS'))
                    {
                        $property->lat = $otherProperty->lat;
                        $property->long = $otherProperty->long;
                        $property->geocode_level = 'WDS';
                    }
                    $property->comments .= "\nNOTE (".date("Y-m-d H:i:s")."): USAA Import function Copied program statuses, WDS geocode, and other comments over from propety pid: ".$otherProperty->pid."\n";
                    $property->comments .= "\nPrevious Property Comments: ".$otherProperty->comments."\n";
                    //NOTE: at some point this also probably needs to have any PR or FS assessments that are pointing at the canceled property pointed to the new one...and possibly other pertinetnt fields
                }
            }

            //-----  Response Auto Enroll Logic -----------------------------------------//
            //member-policy key that have declined the response program and need to be not auto enrolled
            //if its already enrolled or if its been declined, then don't apply the logic
            if($property->response_status !== 'declined' && $property->response_status !== 'enrolled')
            {
                $auto_enroll_flag = false;
                $effective_date = new DateTime($property->policy_effective);
                $renew_auto_enroll_start_date = new DateTime('2015-10-29');
                //if its a renew transaction with an effective date between >= 2015-10-29 and is a Single Family home and is and in the state of CO, MT, NM, or NV then set property to auto enroll in response program
                if($renewal_flag
                    && in_array($property->state, array('CO','MT','NM','NV'))
                    && $effective_date >= $renew_auto_enroll_start_date
                    && $property->multi_family === 0)
                {
                    $auto_enroll_flag = true;
                }
                //if its an issue transaction with an effective date between >= 2015-08-17 and is a Single Family home and is in the state of CO, MT, NM, or NV then set property to auto enroll in response program
                $issue_auto_enroll_start_date = new DateTime('2015-08-17');
                if(!empty($new_transaction)
                    && ($new_transaction === 'issue' || $new_transaction === 're-write')
                    && in_array($property->state, array('CO','MT','NM','NV'))
                    && $effective_date >= $issue_auto_enroll_start_date
                    && $property->multi_family === 0)
                {
                    $auto_enroll_flag = true;
                }

                //2016 new logic
                $mem_num_policy = $member_num.'-'.$property->policy; //used for lookup in exception lists
                //if its a renew and effective date >= 2016-10-08 and in the sate of 'CO','ID','MT','ND','NM','NV','OR','SD','TX','UT','WA','WY' (can be single or multi fam), then set property to auto enroll in response program
                $renew_auto_enroll_start_date = new DateTime('2016-10-08');
                if($renewal_flag
                    && in_array($property->state, array('CO','ID','MT','ND','NM','NV','OR','SD','TX','UT','WA','WY'))
                    && $effective_date >= $renew_auto_enroll_start_date)
                    // && !in_array($mem_num_policy, $temp_mem_prop_num_exception_list))
                {
                    $auto_enroll_flag = true;
                }
                //if its an issue transaction and effective date >= 2016-07-15 and in the sate of 'CO','ID','MT','ND','NM','NV','OR','SD','TX','UT','WA','WY' (can be single or multi fam), then set property to auto enroll in response program
                $issue_auto_enroll_start_date = new DateTime('2016-07-15');
                if(!empty($new_transaction)
                    && ($new_transaction === 'issue' || $new_transaction === 're-write')
                    && in_array($property->state, array('CO','ID','MT','ND','NM','NV','OR','SD','TX','UT','WA','WY'))
                    && $effective_date >= $issue_auto_enroll_start_date)
                    // && !in_array($mem_num_policy, $temp_mem_prop_num_exception_list))
                {
                    $auto_enroll_flag = true;
                }
                //CA auto enroll logic
                $ca_auto_enroll_start_date = new DateTime('2016-11-01');
                if(!empty($new_transaction)
                    && ($new_transaction === 'issue' || $new_transaction === 'renew'  || $new_transaction === 're-write')
                    && $property->state === 'CA'
                    && $effective_date >= $ca_auto_enroll_start_date
                    && ($property->lob == 'HO' || $property->lob == 'HOM')
                    && $property->multi_family === 0)
                    // && !in_array($mem_num_policy, $temp_mem_prop_num_exception_list))
                {
                    $auto_enroll_flag = true;
                }
                //OK auto enroll logic
                $ok_auto_enroll_start_date = new DateTime('2017-04-26');
                if(
                    !empty($new_transaction)
                    && ($new_transaction === 'issue' || $new_transaction === 'renew'  || $new_transaction === 're-write')
                    && $property->state === 'OK'
                    && $effective_date >= $ok_auto_enroll_start_date
                    && !in_array($mem_num_policy, $ok_temp_mem_prop_num_exception_list))
                {
                    $auto_enroll_flag = true;
                }

                //auto-enroll if was triggered in the above logic
                if($auto_enroll_flag)
                {
                    $property->response_status = 'enrolled';
                    if($property->policy_status == 'pending') //if its pending status don't set the response enrolled date till the effective date
                    {
                        $property->res_status_date = $property->policy_effective;
                        $property->response_enrolled_date = $property->policy_effective;
                    }
                    else //if its already active then set the res enrolled date to now
                    {
                        $property->res_status_date = date('Y-m-d H:i:s');
                        $property->response_enrolled_date = date('Y-m-d H:i:s');
                    }
                    $property->response_auto_enrolled = 1;
                }
            }
            //----- END of response auto enroll logic -------------------------------//

            //set client_ids
            $member->client_id = 1;
            $property->client_id = 1;

            //Save process
            if($member->save())
            {
                $property->member_mid = $member->mid;
                if(!$property->save())
                {
                    print 'Could not import row. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Could not import property for member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else //successful prop save
                {
                    //----SPECIAL CASE TOWN HOUSE DEAL FROM CARSON/CASEY------//
                    if($member->member_num == "535513" || $member->member_num == "1145883")
                    {
                        print 'IMPORTANT NOTE: SPECIAL CASE, member number '.$member->member_num." property was cancelled, need to cancel the attached one!!!\n";
                        $data[$errors_col] = 'IMPORTANT NOTE: SPECIAL CASE, member number '.$member->member_num.' property was cancelled, need to cancel the attached one!!!';
                        fputcsv($error_fh, $data);
                    }

                    $contactsRecieved = array();

                    // Add contacts
                    if (!empty($data[$cell_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'cell','priority'=>'Primary 1','name'=>"$data[$first_name_col] $data[$last_name_col]",'relationship'=>null,'detail'=>$data[$cell_phone_col],'notes'=>null));
                    if (!empty($data[$home_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 2','name'=>"$data[$first_name_col] $data[$last_name_col]",'relationship'=>null,'detail'=>$data[$home_phone_col],'notes'=>null));
                    if (!empty($data[$work_phone_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'work','priority'=>'Primary 3','name'=>"$data[$first_name_col] $data[$last_name_col]",'relationship'=>null,'detail'=>$data[$work_phone_col],'notes'=>null));
                    if (!empty($data[$email_1_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 1','name'=>"$data[$first_name_col] $data[$last_name_col]",'relationship'=>null,'detail'=>$data[$email_1_col],'notes'=>null));
                    if (!empty($data[$email_2_col]))
                        $contactsRecieved[] = $this->saveContact(array('property_pid'=>$property->pid,'type'=>'email','priority'=>'Secondary 2','name'=>"$data[$first_name_col] $data[$last_name_col]",'relationship'=>null,'detail'=>$data[$email_2_col],'notes'=>null));

                    // Remove any old contacts that were not recieved in this list
                    $this->cleanContacts($property->pid, $contactsRecieved);
                }
            }
            else
            {
                print 'Could not import row. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $data[$errors_col] = 'Could not import Member. Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                fputcsv($error_fh, $data);
                $errors++;
            }

            if($counter % 100 == 0)
                $this->printUpdateStatus($fileToImport, "Processing", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Errors: $errors");

        }//end of main while loop that goes through each row of import file

        fclose($error_fh);
        fclose($fh);
        $this->printUpdateStatus($fileToImport, "Finished", "Imported $counter rows so far (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Errors: $errors");
    }

    //read in exception list for usaa auto-enroll
    /**
     * read in exception list for usaa auto-enroll (see usaatrans sensitive import for more details).
     * Used only occasionally for loading a temporary exception lists when onboarding a new set of auto-enroll policies.
     * @param mixed $file_name
     * @return string[]
     */
    private function load_usaa_exception_list($file_name)
    {
        $exception_list_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$file_name,'r');
        $exception_list = array();

        //Main Loop to go through each row in the import file and process it
        while(($data = fgetcsv($exception_list_fh, null, '~')) !== FALSE)
        {
            //trim up the attributes
            $data = array_map('trim', $data);
            //NOTE, NEW FILES HAVE LEADING 0's on member number, need to strip them
            $member_num = ltrim($data[0], '0');
            $policy = $data[1];
            $exception_list[] = $member_num.'-'.$policy;
        }
        fclose($exception_list_fh);
        return $exception_list;
    }

    /**
     *  General use function for the Full PIF import functions to reset
     *  all the flags for a certain clients properties so they can later be used in cancel routines
     *
     * @param int $client_id
     * @return null
     */
    private function reset_property_flags($client_id)
    {
        $reset_flag_props= Yii::app()->db->createCommand("SELECT [pid] FROM [properties] WHERE [flag] != 0 AND [client_id] = $client_id")->queryAll();
        $reset_counter = 0;
        $reset_total = count($reset_flag_props);
        foreach($reset_flag_props as $reset_flag_prop)
        {
            $reset_counter++;
            $command = Yii::app()->db->createCommand("UPDATE [properties] SET [properties].[flag] = 0 WHERE [pid] = ".$reset_flag_prop['pid'])->execute();
            if($reset_counter % 100 === 0)
                print "Reset $reset_counter of $reset_total Flags.\n";
        }
    }

    /**
     * Imports the bare-bones required fields for the "in-between" regular pif imports (for example, during a fire) No client specific logic
     * @param mixed $fileToImport
     * @return bool
     */
    private function importNonStandard($fileToImport)
    {

        $this->printUpdateStatus($fileToImport, "Processing", "Starting One-off PIF Import");

        //get client
        $client = Client::model()->findByPk($fileToImport['client_id']);

        $this->printUpdateStatus($fileToImport, "Processing", "Client: " . $client->name);

        //openfile
        $fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.$fileToImport['file_path'], 'r');

        //get headers/indexes
        $header_fields = fgetcsv($fh);
        //trim them up and lower case them
        $header_fields = array_map('trim', $header_fields);
        $header_fields = array_map('strtolower', $header_fields);

        //check required and get col indexes
        $fields_to_check = array('policyholder_number','policy_number','first_name','last_name','address','city','state','zip','effective_date','expiration_date');
        foreach($fields_to_check as $field)
        {
            if(array_search($field, $header_fields) === false)
            {
                $this->printUpdateStatus($fileToImport, "Error", "Error Details: Missing column with header: ".$field);
                return false;
            }
        }
        $policyholder_col = array_search('policyholder_number', $header_fields);
        $policy_col = array_search('policy_number', $header_fields);
        $first_name_col = array_search('first_name', $header_fields);
        $last_name_col = array_search('last_name', $header_fields);
        $address_col = array_search('address', $header_fields);
        $city_col = array_search('city', $header_fields);
        $state_col = array_search('state', $header_fields);
        $zip_col = array_search('zip', $header_fields);
        $coverage_col = array_search('coverage_amt', $header_fields);
        $effdate_col = array_search('effective_date', $header_fields);
        $expdate_col = array_search('expiration_date', $header_fields);
        $phone_col = array_search('phone', $header_fields);
        $location_col = array_search('location_number', $header_fields); // Not doing anything with this yet - will need to add this in when loc column is added!!!!!

        //setup error_file
        $error_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_errors.csv', strtolower($fileToImport['file_path'])), 'w');
        $header_fields[] = 'errors';
        $errors_col = array_search('errors', $header_fields);
        fputcsv($error_fh, $header_fields);

        //setup import_results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.str_replace('.csv', '_import_results.csv', strtolower($fileToImport['file_path'])), 'w');
        fputcsv($results_fh, array('wds_pid', 'wds_mid', 'Policy Holder #', 'Policy #', 'Import Action',));

        //index variables to track progress/results
        $counter = 0;
        $new_members = 0;
        $new_properties = 0;
        $errors = 0;

        while(($data = fgetcsv($fh)) !== FALSE)
        {
            $counter++;
            $results_action = '';

            //trim up the attributes
            $data = array_map('trim', $data);

            $member_num = $data[$policyholder_col];
            $policy = $data[$policy_col];

            //check if member already exists
            $member = Member::model()->findByAttributes(array('member_num'=>$member_num, 'client_id'=>$client->id));

            if(!isset($member)) //didn't find member, so make new one and a new property
            {

                $new_members++;
                $new_properties++;

                //Member
                $member = new Member();
                $member->member_num = $member_num;
                $member->client = $client->name;
                $member->client_id = $client->id;
                $member->first_name = $data[$first_name_col];
                $member->last_name = $data[$last_name_col];
                $member->home_phone = $data[$phone_col];

                //Property
                $property = new Property();
                $property->policy = $policy;
                $property->client_policy_id = $data[$policy_col];
                $property->address_line_1 = $data[$address_col];
                $property->city = $data[$city_col];
                $property->state = $data[$state_col];
                $property->zip = $data[$zip_col];
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
                $property->response_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->pre_risk_status = 'not enrolled';
                $property->client_id = $client->id;

                if($coverage_col)
                    $property->coverage_a_amt = $data[$coverage_col];

                if($effdate_col)
                    $property->policy_effective = $data[$effdate_col];

                if($expdate_col)
                    $property->policy_expiration = $data[$expdate_col];

                $results_action = "Added New Policyholder and New Policy. ";
            }
            //Attach to existing member...
            else
            {
                $new_properties++;

                //Property
                $property = new Property();
                $property->policy = $policy;
                $property->client_policy_id = $data[$policy_col];
                $property->address_line_1 = $data[$address_col];
                $property->city = $data[$city_col];
                $property->state = $data[$state_col];
                $property->zip = $data[$zip_col];
                $property->policy_status = 'active';
                $property->policy_status_date = date('Y-m-d H:i:s');
                $property->response_status = 'not enrolled';
                $property->fireshield_status = 'not enrolled';
                $property->pre_risk_status = 'not enrolled';

                $property->client_id = $client->id;

                if($coverage_col)
                    $property->coverage_a_amt = $data[$coverage_col];

                if($effdate_col)
                    $property->policy_effective = $data[$effdate_col];

                if($expdate_col)
                    $property->policy_expiration = $data[$expdate_col];
            }

            //Save
            if($member->save())
            {
                $property->member_mid = $member->mid;

                if(!$property->save()) //prop failed saving
                {
                    print 'Could not import row, property did not save. Details: ' . var_export($property->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                    $data[$errors_col] = 'Could not import property for member (member saved, but prop did not). Details: '.preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true)));
                    fputcsv($error_fh, $data);
                    $errors++;
                }
                else //prop saved correctly
                {
                    //add to results file with the action that was done
                    fputcsv($results_fh, array($property->pid, $member->mid, $member->member_num, $property->policy, $results_action));

                    // Add contacts
                    if (!empty($data[$phone_col]))
                        $this->saveContact(array('property_pid'=>$property->pid,'type'=>'home','priority'=>'Primary 1','name'=>"$member->first_name $member->last_name",'relationship'=>null,'detail'=>$data[$phone_col],'notes'=>null));
                }
            }
            else
            {
                print 'Could not import row, member did not save. Details: ' . var_export($member->getErrors(), true) . ' ROW VALUES: ' . var_export($data, true) . "\n";
                $data[$errors_col] = 'Could not import member details (neither member nor prop saved). Details: '.preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true)));
                fputcsv($error_fh, $data);
                $errors++;
            }

        }

        $this->printUpdateStatus($fileToImport, "Finished", "Processed $counter rows (New Mems: $new_members,  New Props: $new_properties,  Errors: $errors)");
    }

    /**
     * Save PIF import contact
     * @param array $data
     * @return string
     */
    private function saveContact($data)
    {
        // Find contact
        $contact = Contact::model()->findByAttributes(array(
            'property_pid' => $data['property_pid'],
            'priority' => $data['priority']
        ));

        if (!$contact)
        {
            // If contact doesn't already exists, create new one
            $contact = new Contact;
        }
        else
        {
            if ($contact->property_pid == $data['property_pid'] &&
                $contact->type == $data['type'] &&
                $contact->priority == $data['priority'] &&
                $contact->name == $data['name'] &&
                $contact->relationship == $data['relationship'] &&
                $contact->detail == $data['detail'] &&
                $contact->notes == $data['notes'])
            {
                // If contact remains the same, exit now
                return $data['priority'];
            }
        }

        $contact->property_pid = $data['property_pid'];
        $contact->type = $data['type'];
        $contact->priority = $data['priority'];
        $contact->name = $data['name'];
        $contact->relationship = $data['relationship'];
        $contact->detail = $data['detail'];
        $contact->notes = $data['notes'];

        if (!$contact->save())
        {
            print 'Policy was imported, But there was an error saving ' . $data['priority'] . ' contact. Details: ' . var_export($contact->getErrors(), true) . "\n";
        }

        return $data['priority'];
    }

    /**
     * Delete contacts that exist, but weren't in the PIF import
     * @param integer $pid
     * @param array $contactsRecieved
     */
    private function cleanContacts($pid, $contactsRecieved)
    {
        Contact::model()->deleteAll("property_pid = :property_pid AND priority NOT IN ('" . implode("','", $contactsRecieved) . "')", array(':property_pid'=>$pid));
    }
	/**
	 * @param integer $pid , $lat, $long
	 * update wds_lat, wds_long, geog
	 */
	private function setGeoSpatial($pid ="", $lat = "", $long = "")
    {
		if($lat != '' && $long != '')
		{
			$isNumericCheck =  $this -> latLongNumericValidation($lat,  $long);
			if($isNumericCheck)
			{
				$sql = "DECLARE @perimeter geography = geography::STGeomFromText('POINT('+convert(varchar(25), ".$long.",128)+' '+convert(varchar(25),
		".$lat.",128)+')',4326)

				update properties set geog = @perimeter,
				lat = $lat,
				long = $long,
				wds_lat = convert(varchar(25),$lat,128),
				wds_long = convert(varchar(25),$long,128),
				wds_geocode_level = 'client'
				where pid=".$pid;
			$command = Yii::app()->db->createCommand($sql)->execute();
			}
       }
    }
	/**
     * PIF1x logic numeric validation
     * @param integer $lat, $lat
     * @return boolean
     */
    private function latLongNumericValidation($lat, $long)
    {
		if (is_numeric($lat) && is_numeric($long))
		{
			if(($lat >= -90 && $lat <= 90) && ($long >= -180 && $long <= 180))
			{
				return true;
			}
		}
		return false;
       
    }
}
?>