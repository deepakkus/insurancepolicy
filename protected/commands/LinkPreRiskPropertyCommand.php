<?php

/*
 * This was built to make the connection from the Pre Risk table to the Properties and members tables. The connection is based off retreiving the pid from property
 * and setting it in the pre risk table (foreign key).
 */

class LinkPreRiskPropertyCommand extends CConsoleCommand
{
    public function run($args)
    {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
			ini_set('memory_limit', '1000M');

            if($args[0]=="join")
            {
              $this->findLinks();
            }
			elseif($args[0] == "fix55")
			{
				$this->fix55();
			}
            elseif($args[0] == "fix99")
            {
                if(isset($args[1]))
                    $this->fix99($args[1]);
                else
                    echo "Argument #2 requred. Values 'like' or 'explode'";
            }
            elseif($args[0] == "fix88")
            {
                    if(isset($args[1]) && is_file($args[1]))
                            $this->fix88($args[1]); //path to csv import file;
                    else
                            echo "Second parameter must be a valid file path";
            }
            elseif($args[0] == "import2009data")
            {
                if(isset($args[1]) && is_file($args[1]))
                    $this->import2009data($args[1]); //path to csv import file;
                else
                    echo "Second parameter must be a valid file path";
            }
            else
            {
                echo "Parameter not valid. Choose 'join' or 'fix99'";
            }

    }

    //Looks for matchins comparing the pre_risk table with the property and member tables
    //This was run first, and does a straight comparison based on member number and address.
    // -99 means member was matched, but no property
    //-88 means no member was matched
    //-22 means duplicate property matches were found in the property table.
    public function findLinks()
    {
        $loop = true;
        $i=1;

        while($loop == true)
        {
            $criteria = new CDbCriteria;
            $criteria->select = '*';
            $criteria->condition = "property_pid = 0";
            $criteria->limit = 5000; // Batches include 5000 people
            $result = PreRisk::model()->findAll($criteria);

            if((count($result)) < 1)
            // No results...kill loop
            {
                $loop = false;
            }

            else
            //Bite off data into 5000 entry chunks
            {
                foreach($result as $preRisk)
                {
                       if($i % 20 == 0)
                            echo "------------- On record $i ----------------- \n";
                       $member = Member::model()->findByAttributes(array('member_num'=>$preRisk->member_number, 'client'=>'USAA'));

                       //Can't find member
                       if(empty($member))
                       {
                           $preRisk->property_pid ="-88";
                            if(!$preRisk->save())
                                echo "ERROR with save \n";
                       }

                       //Member found...look for property
                       else
                       {
                            $property = Property::model()->findByAttributes(array("address_line_1"=>$preRisk->street_address, "member_mid"=>$member->mid)); //get property for member

                            //A single entry was found in the property table (1 to 1)
                            if(count($property)==1)
                            {
                                $preRisk->property_pid = $property->pid;
                                if(!$preRisk->save())
                                    echo "ERROR with save \n";
                            }

                            //Duplicate entries were found in the property table...would need to investigate these
                            elseif(count($property)>1)
                            {
                                echo "Duplicate ".$property->pid." ".$property->address_line_1."\n";
                                $preRisk->property_pid ="-22";
                            }

                            //Record does not exist in properties table - either member has canceled, no longer with USAA, or a data mismatch (typo, spelling etc)
                            else
                            {
                               $preRisk->property_pid ="-99";
                                if(!$preRisk->save())
                                    echo "ERROR with save \n";
                            }
                       }
                        //Clear for memory
                        $member = null;
                        $property = null;

                        $i++;

                }

               $r = $i-1; //since $i is already incremented above, subtract 1 for correct result
                echo "Finished searching though $r records. \n";

            } //count > 0

        }//while

    }

    //Compares all -99 values (no match) in the pre_risk table to the properties and members with the 2 parameters
    //like: compare the entire pre risk string with LIKE to the address in property table
    //explode: compare only the street # from prerisk with a LIKE to the whole street address in property (ST vs. STREET, LANE vs LN, N vs NORTH)
    public function fix99($type)
    {

        $i=1;

        $criteria = new CDbCriteria;
        $criteria->select = '*';
        $criteria->condition = "property_pid = -99";
        $result = PreRisk::model()->findAll($criteria);

        foreach($result as $preRisk)
        {
               if($i % 100 == 0)
                    echo "------------- On record $i ----------------- \n";
               $member = Member::model()->findByAttributes(array('member_num'=>$preRisk->member_number, 'client'=>'USAA'));

               //Can't find member
               if(empty($member))
               {
                    $preRisk->preoperty_pid ="-88";
                    if(!$preRisk->save())
                        echo "ERROR with save \n";
               }

               //Member found...look for property
               else
               {
                   if($type == "like")
                   {
                        $streetAddress = addcslashes($preRisk->street_address, '%_'); // escape LIKE's special characters
                        $criteria = new CDbCriteria;
                        $criteria->select = 'pid, address_line_1, member_mid';
                        $criteria->condition = "address_line_1 LIKE :address AND member_mid = $member->mid";
                        $criteria->params = array(':address'=>"$streetAddress%");
                        $property = Property::model()->findAll($criteria);

                        //A single entry was found in the property table (1 to 1)
                        if(count($property)==1)
                        {
                            echo "Matched ".$property[0]->pid." ".$property[0]->address_line_1."\n";
                            $preRisk->property_pid = $property[0]->pid;
                        }

                        //Duplicate entries were found in the property table...would need to investigate these
                        elseif(count($property)>1)
                        {
                            echo "Duplicate ".$property[0]->pid." ".$property[0]->address_line_1."\n";
                            $preRisk->property_pid ="-22";
                        }

                        //Record does not exist in properties table - either member has canceled, no longer with USAA, or a data mismatch (typo, spelling etc)
                        else
                        {
                           $preRisk->property_pid ="-99";
                        }

                        if(!$preRisk->save())
                            echo "ERROR with save \n";

                   }
                   elseif($type == "explode")
                   {
                        $streetNumber = explode(' ', $preRisk->street_address);
                        $streetAddress = addcslashes($streetNumber[0], '%_'); // escape LIKE's special characters
                        $criteria = new CDbCriteria;
                        $criteria->select = 'pid, address_line_1, member_mid';
                        $criteria->condition = "address_line_1 LIKE :address AND member_mid = $member->mid AND zip = $preRisk->zip_code";
                        $criteria->params = array(':address'=>"$streetAddress%");
                        $property = Property::model()->findAll($criteria);

                        //A single entry was found in the property table (1 to 1)
                        if(count($property)==1)
                        {
                            echo "Matched ".$property[0]->pid." ".$property[0]->address_line_1."\n";
                            $preRisk->property_pid = $property[0]->pid;

                        }

                        //Duplicate entries were found in the property table...would need to investigate these
                        elseif(count($property)>1)
                        {
                            echo "Duplicate ".$property[0]->pid." ".$property[0]->address_line_1."\n";
                            $preRisk->property_pid ="-22";
                        }

                        //Record does not exist in properties table - either member has canceled, no longer with USAA...at this point should not be a data typo
                        else
                        {
                           $preRisk->property_pid ="-99";
                        }

                        if(!$preRisk->save())
                            echo "ERROR with save \n";
                   }

               }

                $i++;

        }

       $r = $i-1; //since $i is already incremented above, subtract 1 for correct result

       echo "Finished searching though $r records. \n";
    }
    
    public function import2009data($importFilePath)
    {
        print "Starting 2009 Data Import\n";
        //open file
		$fh = fopen($importFilePath, 'r');
		//get header row
		$header_fields = fgetcsv($fh);
        //check headers
		$fields_to_check = array('property_pid', 'member_number', 'call_center_comments', 'member_name', 'street_address', 'city', 'state', 'zip_code', 'home_phone', 'work_phone', 'cell_phone', 'call_list_year', 'call_list_month', 'status', 'ha_date', 'recommended_actions');
		foreach($fields_to_check as $field)
		{
			if(array_search($field, $header_fields) === false)
			{
				print "ERROR missing column with header: ".$field."\n";
				return false;
			}
		}
        //column index setup
        $property_pid_col = array_search('property_pid', $header_fields);
        $member_number_col = array_search('member_number', $header_fields);
        $call_center_comments_col = array_search('call_center_comments', $header_fields);
        $member_name_col = array_search('member_name', $header_fields);
        $street_address_col = array_search('street_address', $header_fields);
        $city_col = array_search('city', $header_fields);
        $state_col = array_search('state', $header_fields);
        $zip_code_col = array_search('zip_code', $header_fields);
        $home_phone_col = array_search('home_phone', $header_fields);
        $work_phone_col = array_search('work_phone', $header_fields);
        $cell_phone_col = array_search('cell_phone', $header_fields);
        $call_list_year_col = array_search('call_list_year', $header_fields);
        $call_list_month_col = array_search('call_list_month', $header_fields);
        $status_col = array_search('status', $header_fields);
        $ha_date_col = array_search('ha_date', $header_fields);
        $recommended_actions_col = array_search('recommended_actions', $header_fields);
        //loop through all rows and import them
        $counter = 0;
        while($data = fgetcsv($fh))
		{
            $counter++;
            print "\nProcessing Row $counter \n";
            //if pid is -88 then member doesnt exist, need to create it
            if($data[$property_pid_col] == '-88')
            {
                print "-88 Entry, Creating new Member\n";
                $member = new Member();
                //attributes from file
                $member->member_num = $data[$member_number_col];
                $member->client = 'USAA';
                $name_pieces = explode(' ', preg_replace('/[^(\x20-\x7F)]*/','', $data[$member_name_col]));
                $member->first_name = preg_replace('/[^(\x20-\x7F)]*/','', $name_pieces[0]);
                $member->last_name = preg_replace('/[^(\x20-\x7F)]*/','', str_replace($name_pieces[0], '', implode(' ', $name_pieces)));
                $member->home_phone = $data[$home_phone_col];
                $member->work_phone = $data[$work_phone_col];
                $member->cell_phone = $data[$cell_phone_col];
                $member->mem_fireshield_status = 'ineligible';
                $member->mem_fs_status_date = date('Y-m-d H:i:s');
                if($member->save(false))
			    {
                    print "Successfully created new Member (mid: ".$member->mid.")\n";
                }
                else
                {
                    print "Error saving new Member\n";
                }
            }
            //if pid is -88 or -99 then property doesnt exist, need to create it
            if($data[$property_pid_col] == '-88' || $data[$property_pid_col] == '-99')
            {
                print "-88/-99 Entry, Creating new Property\n";
                $property = new Property();
                $member = Member::model()->findByAttributes(array('member_num'=>$data[$member_number_col], 'client'=>'USAA'));
                $property->member_mid = $member->mid;
                //rest of attributes from file
                $property->address_line_1 = $data[$street_address_col];
				$property->city = $data[$city_col];
				$property->state = $data[$state_col];
				$property->zip = $data[$zip_code_col];
                $property->pre_risk_status = 'enrolled';
                $property->pr_status_date = $data[$ha_date_col];
                $property->response_status = 'ineligible';
				$property->res_status_date = date('Y-m-d H:i:s');
				$property->fireshield_status = 'ineligible';
				$property->fs_status_date = date('Y-m-d H:i:s');
				$property->policy_status = 'canceled';
				$property->policy_status_date = date('Y-m-d H:i:s');
                $num_props = count($member->properties)+1;
				$property->policy = 'unknown-'.$num_props;
                if($property->save(false))
			    {
                    print "Successfully created new Property (pid: ".$property->pid.")\n";
                }
                else
                {
                    print "Error saving new Property\n";
                }
            }
            //create pre_risk entry
            print "Creating new PreRisk\n";
            $pre_risk = new PreRisk();
            if(isset($property)) //was a -88 or -99 so new one was created, use that pid
                $pre_risk->property_pid = $property->pid;
            else //already existed, use the pid from the file
                $pre_risk->property_pid = $data[$property_pid_col];
            //rest of attributes from file
            $pre_risk->member_number = $data[$member_number_col];
            $pre_risk->call_center_comments = $data[$call_center_comments_col];
            $pre_risk->member_name = $data[$member_name_col];
            $pre_risk->street_address = $data[$street_address_col];
            $pre_risk->city = $data[$city_col];
            $pre_risk->state = $data[$state_col];
            $pre_risk->zip_code = $data[$zip_code_col];
            $pre_risk->home_phone = $data[$home_phone_col];
            $pre_risk->work_phone = $data[$work_phone_col];
            $pre_risk->cell_phone = $data[$cell_phone_col];
            $pre_risk->call_list_year = $data[$call_list_year_col];
            $pre_risk->call_list_month = $data[$call_list_month_col];
            $pre_risk->status = $data[$status_col];
            $pre_risk->ha_date = $data[$ha_date_col];
            $pre_risk->recommended_actions = $data[$recommended_actions_col];
            if($pre_risk->save(false))
            {
                print "Successfully created new PreRisk (id: ".$pre_risk->id.")\n";
            }
            else
            {
                print "Error saving new PreRisk\n";
            }
        }
    }

	public function fix88($importFilePath)
    {
		//open file
		$fh = fopen($importFilePath, 'r');
		//get header row
		$header_fields = fgetcsv($fh);

		$fields_to_check = array('pre_risk_id', 'property_pid', 'member_number', 'company', 'member_name', 'home_phone', 'work_phone', 'cell_phone', 'street_address', 'city', 'county', 'state', 'zip_code', 'plus_4', 'client_email', 'status', 'completion_date');
		foreach($fields_to_check as $field)
		{
			if(array_search($field, $header_fields) === false)
			{
				print "ERROR missing column with header: ".$field."\n";
				return false;
			}
		}

		//column indexes
		$pre_risk_id_col = array_search('pre_risk_id', $header_fields);
		$property_pid_col = array_search('property_id', $header_fields);
		$member_number_col = array_search('member_number', $header_fields);
		$company_col = array_search('company', $header_fields);
		$member_name_col = array_search('member_name', $header_fields);
		$home_phone_col = array_search('home_phone', $header_fields);
		$work_phone_col = array_search('work_phone', $header_fields);
		$cell_phone_col = array_search('cell_phone', $header_fields);
		$street_address_col = array_search('street_address', $header_fields);
		$city_col = array_search('city', $header_fields);
		$county_col = array_search('county', $header_fields);
		$state_col = array_search('state', $header_fields);
		$zip_code_col = array_search('zip_code', $header_fields);
		$plus_4_col = array_search('plus_4', $header_fields);
		$client_email_col = array_search('client_email', $header_fields);
		$status_col = array_search('status', $header_fields);
		$completion_date_col = array_search('completion_date', $header_fields);

		$rows_imported = 0;
		print "Starting 88 Fix import \n";
		while($data = fgetcsv($fh))
		{
			print "Importing ".$rows_imported." row \n";
			$member = Member::model()->findByAttributes(array('member_num'=>$data[$member_number_col], 'client'=>'USAA'));
			if(!isset($member)) //didn't find member, so make new one and a new property
			{
				$member = new Member();
				$property = new Property();
			}
			else //existing member
			{
				$property = new Property();
			}

			//set member info
			$member->client = 'USAA';
			$member->member_num = $data[$member_number_col];
			$name_pieces = explode(' ', preg_replace('/[^(\x20-\x7F)]*/','', $data[$member_name_col]));
			$member->first_name = preg_replace('/[^(\x20-\x7F)]*/','', $name_pieces[0]);
			$member->last_name = preg_replace('/[^(\x20-\x7F)]*/','', str_replace($name_pieces[0], '', implode(' ', $name_pieces)));
			$member->home_phone = $data[$home_phone_col];
			$member->work_phone = $data[$work_phone_col];
			$member->cell_phone = $data[$cell_phone_col];
			$member->email_1 = $data[$client_email_col];
			$member->mem_fireshield_status = 'ineligible';
			$member->mem_fs_status_date = date('Y-m-d H:i:s');

			//save member
			if($member->save(false))
			{
				//set property info
				$property->rated_company = $data[$company_col];
				$property->address_line_1 = $data[$street_address_col];
				$property->city = $data[$city_col];
				$property->county = $data[$county_col];
				$property->state = $data[$state_col];
				$property->zip = $data[$zip_code_col];
				$property->zip_supp = $data[$plus_4_col];
				//if there was a completed assessment done this column will have a date and we need to set it to enrolled. otherwise just set it to offered.
				if(empty($data[$completion_date_col]))
				{
					$property->pre_risk_status = 'offered';
					$property->pr_status_date = date('Y-m-d H:i:s');
				}
				else
				{
					$property->pre_risk_status = 'enrolled';
					$property->pr_status_date = $data[$completion_date_col];
				}
				//set all the other statuses to ineligible
				$property->response_status = 'ineligible';
				$property->res_status_date = date('Y-m-d H:i:s');
				$property->fireshield_status = 'ineligible';
				$property->fs_status_date = date('Y-m-d H:i:s');
				$property->policy_status = 'canceled';
				$property->policy_status_date = date('Y-m-d H:i:s');

				//for the unique key in props (policy-member_mid) we obviously need a policy, but stupid PR doesnt have one, so just putting something unique and descriptive in there
				$num_props = count($member->properties)+1;
				$property->policy = 'unknown-'.$num_props;
				$property->member_mid = $member->mid;

				//save property
				if($property->save(false))
				{
					//get the pre_risk model this was for and set the property_pid foriegn key to the newly created property
					$pre_risk = PreRisk::model()->findByPk($data[$pre_risk_id_col]);
					$pre_risk->property_pid = $property->pid;
					if($pre_risk->save(false))
					{
						$rows_imported++;
					}
					else
						print "Error saving pre_risk for id ".$data[$pre_risk_id_col]." \n";

				}
				else
				{
					print "Error saving property for pre_risk_id ".$data[$pre_risk_id_col]." \n";
				}
			}
			else
			{
				print "Error saving member for pre_risk_id ".$data[$pre_risk_id_col]." \n";
			}

			//if($rows_imported % 50 == 0)
			print "Imported ".$rows_imported." so far \n";
		}

	}
	
	//looksup all existing pre-risks with -55 set as the property_pid and makes a new Member and Property for them with all the info it can and links it to the PR entry
	public function fix55()
    {
		$pre_risks = PreRisk::model()->findAll('property_pid = -55');
		$counter = 1;
		$rows_imported = 0;
		print "Starting -55 Fix \n";
		foreach($pre_risks as $pr)
		{
			
			print "fixing pre_risk id: (member_number: ".$pr->member_number.") ".$pr->id."\n";
			$member = Member::model()->findByAttributes(array('member_num'=>$pr->member_number, 'client'=>'USAA'));
			if(!isset($member)) //didn't find member, so make new one and a new property
			{
				$member = new Member();
				$property = new Property();
			}
			else //existing member
			{
				$property = Property::model()->findByAttributes(array('member_mid'=> $member->mid, 'address_line_1'=> $pr->street_address));
				if(!isset($property))
					$property = new Property();
			}

			//set member info
			$member->client = 'USAA';
			$member->member_num = $pr->member_number;
			$name_pieces = explode(' ', preg_replace('/[^(\x20-\x7F)]*/','', $pr->member_name));
			$member->first_name = preg_replace('/[^(\x20-\x7F)]*/','', $name_pieces[0]);
			$member->last_name = preg_replace('/[^(\x20-\x7F)]*/','', str_replace($name_pieces[0], '', implode(' ', $name_pieces)));
			$member->home_phone = $pr->home_phone;
			$member->work_phone = $pr->work_phone;
			$member->cell_phone = $pr->cell_phone;
			$member->email_1 = $pr->client_email;
			$member->mem_fireshield_status = 'ineligible';
			$member->mem_fs_status_date = date('Y-m-d H:i:s');

			//save member
			if($member->save(false))
			{
				//set property info
				$property->rated_company = $pr->company;
				$property->address_line_1 = $pr->street_address;
				$property->city = $pr->city;
				$property->county = $pr->county;
				$property->state = $pr->state;
				$property->zip = $pr->zip_code;
				$property->zip_supp = $pr->plus_4;
				//if there was a completed assessment done this column will have a date and we need to set it to enrolled. otherwise just set it to offered.
				if(empty($pr->completion_date))
				{
					$property->pre_risk_status = 'offered';
					$property->pr_status_date = date('Y-m-d H:i:s');
				}
				else
				{
					$property->pre_risk_status = 'enrolled';
					$property->pr_status_date = $pr->completion_date;
				}
				//set all the other statuses to ineligible
				$property->response_status = 'ineligible';
				$property->res_status_date = date('Y-m-d H:i:s');
				$property->fireshield_status = 'ineligible';
				$property->fs_status_date = date('Y-m-d H:i:s');
				$property->policy_status = 'canceled';
				$property->policy_status_date = date('Y-m-d H:i:s');

				//for the unique key in props (policy-member_mid) we obviously need a policy, but stupid PR doesnt have one, so just putting something unique and descriptive in there
				$num_props = count($member->properties)+1;
				$property->policy = 'unknown-'.$num_props;
				$property->member_mid = $member->mid;

				//save property
				if($property->save(false))
				{
					//set the property_pid foriegn key to the newly created property
					$pr->property_pid = $property->pid;
					if($pr->save(false))
					{
						$rows_imported++;
					}
					else
						print "Error saving pre_risk for id ".$pr->id." \n";

				}
				else
				{
					print "Error saving property for pre_risk_id ".$pr->id." \n";
				}
			}
			else
			{
				print "Error saving member for pre_risk_id ".$pr->id." \n";
			}

			if($rows_imported % 50 == 0)
				print "Fixed ".$rows_imported." so far \n";
		}
		print "Done with -55 fix \n";
	}
}
?>
