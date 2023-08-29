<?php

class m170410_070815_sync_ph_action_types extends CDbMigration
{

	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{

        $this->update('res_ph_action_type', array('name' => 'Homeowner Visit', 'active' => 1, 'category_id' => 1, 'definition' => 'The Program Engine met/talked with the resident (homeowner or tenant.)', 'action_type' => 'Recon', 'units' => ''), 'id = 1');
        $this->update('res_ph_action_type', array('name' => 'Left Brochure', 'active' => 1, 'category_id' => 1, 'definition' => 'Left client materials at the site to indicate purpose of visit, program details, etc.', 'action_type' => 'Recon', 'units' => ''), 'id = 2');
        $this->update('res_ph_action_type', array('name' => 'Property Triage', 'active' => 1, 'category_id' => 1, 'definition' => 'This will be checked only on the first visit to a property. The exception is if there has been a significant change in fire behavior, then it can be checked for a successive visit.', 'action_type' => 'Recon', 'units' => ''), 'id = 3');
        $this->update('res_ph_action_type', array('name' => 'Photos', 'active' => 1, 'category_id' => 1, 'definition' => 'Took photo(s) of site to substantiate visit, risk, etc.', 'action_type' => 'Recon', 'units' => ''), 'id = 4');
        $this->update('res_ph_action_type', array('name' => 'Relocate Combustibles', 'active' => 1, 'category_id' => 2, 'definition' => 'Typically this is moving patio furniture, firewood and lumber. This applies to any objects that are being moved with the intention of putting them back after the fire.', 'action_type' => 'Physical', 'units' => ''), 'id = 5');
        $this->update('res_ph_action_type', array('name' => 'Fuel Mitigation', 'active' => 1, 'category_id' => 2, 'definition' => 'Raking mulch, pine needles, or clearing other natural fuels.', 'action_type' => 'Physical', 'units' => ''), 'id = 6');
        $this->update('res_ph_action_type', array('name' => 'Fire Break Established', 'active' => 1, 'category_id' => 2, 'definition' => 'Digging hand line around the property or mowing tall grass around the property. ', 'action_type' => 'Physical', 'units' => ''), 'id = 7');
        $this->update('res_ph_action_type', array('name' => 'Sprinklers Set Up/Maintained', 'active' => 1, 'category_id' => 2, 'definition' => 'Must have a snap tank and pump set up to count as sprinklers. Check this box each day that the sprinklers are on the property, and fill out the "quantity field" for the number of sets present each day.', 'action_type' => 'Physical', 'units' => '# of sprinkler KITS'), 'id = 8');
        $this->update('res_ph_action_type', array('name' => 'Gel Applied/Maintained', 'active' => 1, 'category_id' => 2, 'definition' => 'Check this the day gel is applied (to structure or vegetation), and each day that the Program Engine returns to rehydrate the gel.', 'action_type' => 'Physical', 'units' => 'gallons'), 'id = 9');
        $this->update('res_ph_action_type', array('name' => 'Foam Applied', 'active' => 1, 'category_id' => 2, 'definition' => 'Applied foam to reduce likelihood of ignition/spread.', 'action_type' => 'Physical', 'units' => 'gallons'), 'id = 10');
        $this->update('res_ph_action_type', array('name' => 'Retardant Applied', 'active' => 1, 'category_id' => 2, 'definition' => 'Program Engine (not the incident), applied retardant to the Home Ignition Zone. Our retardant is a brown or clear product. The incident\'s retardant is red.', 'action_type' => 'Physical', 'units' => 'gallons'), 'id = 11');
        $this->update('res_ph_action_type', array('name' => 'Secure Structure', 'active' => 1, 'category_id' => 2, 'definition' => 'This is any work done to the structure itself – boarding windows, taping vents, etc.', 'action_type' => 'Physical', 'units' => ''), 'id = 12');
        $this->update('res_ph_action_type', array('name' => 'Damage Assessment', 'active' => 1, 'category_id' => 3, 'definition' => 'If the Program Engine is at the property the day after the fire front has passed through, or if there was ember fallout, this should be checked.', 'action_type' => 'Physical', 'units' => ''), 'id = 13');
        $this->update('res_ph_action_type', array('name' => 'Fire Suppression', 'active' => 1, 'category_id' => 3, 'definition' => 'There must be open flame for fire suppression. If it’s just smoldering/smoky then its mop up. The other instance to check “Fire Suppression” is if the engine is there while burning embers are falling on the property, and the engine is extinguishing these.', 'action_type' => 'Physical', 'units' => ''), 'id = 14');
        $this->update('res_ph_action_type', array('name' => 'Gel Cleaned', 'active' => 1, 'category_id' => 3, 'definition' => 'Cleaning gel off the property/structure.', 'action_type' => 'Physical', 'units' => ''), 'id = 15');
        $this->update('res_ph_action_type', array('name' => 'Mop Up', 'active' => 1, 'category_id' => 3, 'definition' => 'After a fire has gone through a property the black areas left behind need to be checked for any areas that are still smoldering or contain residual heat. Both the process of looking for these hot spots (cold trailing) and extinguishing them.', 'action_type' => 'Physical', 'units' => ''), 'id = 16');
        $this->update('res_ph_action_type', array('name' => 'Property Rehab', 'active' => 1, 'category_id' => 3, 'definition' => 'Returning the property to its pre-fire state. Includes: returning combustibles that had been moved to their original location, removing tape, removing window boards, rehabbing line, covering up a hand line, refilling a trench, etc.', 'action_type' => 'Physical', 'units' => ''), 'id = 17');
        $this->update('res_ph_action_type', array('name' => 'Retardant Cleaned', 'active' => 1, 'category_id' => 3, 'definition' => 'Cleaning off retardant that was applied by either Program Engine or the incident.', 'action_type' => 'Physical', 'units' => ''), 'id = 18');
        $this->update('res_ph_action_type', array('name' => 'Sprinklers Removed', 'active' => 1, 'category_id' => 3, 'definition' => 'Removal of sprinkler kit(s)/accessories that were set up by Program Engine.', 'action_type' => 'Physical', 'units' => ''), 'id = 19');
        $this->update('res_ph_action_type', array('name' => 'Customer Service', 'active' => 1, 'category_id' => 4, 'definition' => 'This is not the same as a homeowner visit, this is specifically intended to capture the random tasks that are sometimes done in service to the homeowner, but aren’t firefighting tactics. E.g. feeding livestock, closing gates, turning off well pumps, etc.', 'action_type' => 'Physical', 'units' => ''), 'id = 20');
        $this->update('res_ph_action_type', array('name' => 'Evacuation Assistance', 'active' => 0, 'category_id' => 4, 'definition' => '', 'action_type' => 'Physical', 'units' => ''), 'id = 21');
        $this->update('res_ph_action_type', array('name' => 'Staged', 'active' => 1, 'category_id' => 4, 'definition' => 'The engine has taken all the action they can on a house, and remains at the property to be positioned in the event the fire advances to the property.', 'action_type' => 'Physical', 'units' => ''), 'id = 22');
        $this->delete('res_ph_action', 'action_type_id > 22');
        $this->delete('res_ph_action_type', 'id > 22');
	}

	public function safeDown()
	{
        echo "m170410_070815_sync_ph_action_types does not support migration down.\n";
		return false;
	}
}