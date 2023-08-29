<?php

Yii::import('application.vendors.tcpdf.*'); 


class PdfCreator {
    private $pdf;
    private $assessment;
    private $rec_count = 0;
    private $c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $c10, $c11, $c12, $c13, $c14, $c15, $cA1, $cA2, $cA3, $cB, $cC, $cAA1, $cAA2, $cAA3, $cBB;
    private $conditions = array('c1'     => 'roof_material',
                                'c2'     => 'roof_condition',
                                'c3'     => 'roof_venting',
                                'c4'     => 'gutters',
                                'c5'     => 'roof_eaves',
                                'c6'     => 'wall_openings',
                                'c7'     => 'windows',
                                'c8'     => 'siding',
                                'c9'     => 'home_elevated',
                                'c10'    => 'attachment',
                                'c11'    => 'cont_comb_veg_0_30',
                                'c12'    => 'cont_comb_material',
                                'c13'    => 'additional_struct_0_30',
                                'c14'    => 'cont_comb_veg_30_100',
                                'c15'    => 'additional_struct_30_100',
                                'cA1'    => 'slope',
                                'cA2'    => 'canyon_valley_draw',
                                'cA3'    => 'wind',
                                'cB'     => 'unmanaged_wildland_fuel',
                                'cC'     => 'neighboring_property',
                                'cAA1'   => 'address',
                                'cAA2'   => 'driveway',
                                'cAA3'   => 'bridge',
                                'cBB'    => 'suppression_resources');
    
    
    function PdfCreator() {
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 
        
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor(Yii::app()->user->name);
        
        //set margins
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        //set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }
   
    
    function createPdf($assessment) {
        $this->assessment = $assessment;
        $this->c1     = $this->assessment->roof_material;
        $this->c2     = $this->assessment->roof_condition;
        $this->c3     = $this->assessment->roof_venting;
        $this->c4     = $this->assessment->gutters;
        $this->c5     = $this->assessment->roof_eaves;
        $this->c6     = $this->assessment->wall_openings;
        $this->c7     = $this->assessment->windows;
        $this->c8     = $this->assessment->siding;
        $this->c9     = $this->assessment->home_elevated;
        $this->c10    = $this->assessment->attachment;
        $this->c11    = $this->assessment->cont_comb_veg_0_30;
        $this->c12    = $this->assessment->cont_comb_material;
        $this->c13    = $this->assessment->additional_struct_0_30;
        $this->c14    = $this->assessment->cont_comb_veg_30_100;
        $this->c15    = $this->assessment->additional_struct_30_100;
        $this->cA1    = $this->assessment->slope;
        $this->cA2    = $this->assessment->canyon_valley_draw;
        $this->cA3    = $this->assessment->wind;
        $this->cB     = $this->assessment->unmanaged_wildland_fuel;
        $this->cC     = $this->assessment->neighboring_property;
        $this->cAA1   = $this->assessment->address;
        $this->cAA2   = $this->assessment->driveway;
        $this->cAA3   = $this->assessment->bridge;
        $this->cBB    = $this->assessment->suppression_resources;
        
        $this->pdf->SetTitle("Assessment #".$assessment->id);

        
        // set font
        $this->pdf->SetFont('helvetica', '', 12);

        // add a page
        $this->pdf->AddPage();
        
        $html = '<img src="'.Yii::getPathOfAlias('webroot').'/images/logo.png" />';
        $this->pdf->writeHTML($html, true, false, true, false, 'C');

        $html = '<span style="font-color: #3399ff; font-size: 12pt"><b>WILDFIRE DEFENSE SYSTEMS, INC.</b></span>';
        $this->pdf->writeHTML($html, true, false, true, false, 'C');

        $html = '<br><br><br><br>We are pleased to provide this hazard assessment report for your property located at:<br>';
        
        $this->pdf->writeHTML($html, true, false, true, false, '');

        $this->pdf->lastPage();
        
        
        if(isset($assessment->homeowner->fullAddress)) $this->pdf->writeHTML("<b>".$assessment->homeowner->fullAddress."</b>", true, false, true, false, 'C');
        $this->pdf->writeHTML('<br><br>This service was provided at the request of USAA to complement the safety of your home and family. Understanding your wildlife hazards and how to address them can greatly reduce the risk of losing your home to a wildfire.<br><br><br>', true, false, true, false, '');
       
        
        $html = <<<HTML
                <table>
                    <tr>
                        <td>
                            This report was prepared for:
                        </td>
                        <td align="right">
                            NAME:
                        </td>
                        <td>
                            <b>
HTML;
                            $html .=  isset($assessment->homeowner->name) ? $assessment->homeowner->name : "";
                            $html .= <<<HTML
                            </b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                        <td align="right">
                            MEMBER #:
                        </td>
                        <td>
                            <b>
HTML;
                            $html .= isset($assessment->homeowner->member_num) ? $assessment->homeowner->member_num : "";
                            $html .= <<<HTML
                            </b>
                        </td>
                    </tr>
                </table><br><br>
HTML;
                            
                $html .= <<<HTML
                <table>
                    <tr>
                        <td align="right">
                            Inspection Date:
                        </td>
                        <td>
                            <b>
HTML;
                            $html .=  isset($assessment->homeowner->completion_date) ? $assessment->homeowner->completion_date : "";
                            $html .= <<<HTML
                            </b>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            Inspected By:
                        </td>
                        <td>
                            <b>
HTML;
                            $html .= isset($assessment->homeowner->assessor->name) ? $assessment->homeowner->assessor->name : "";
                            $html .= <<<HTML
                            </b>
                        </td>
                        <td>
                        </td>
                    </tr>
                </table><br><br>
HTML;
                            
        $this->pdf->writeHTML($html, true, false, true, false, '');

       
        /********** View of Home Image ************/
        $images = SelectedImage::model()->findAll("assessment_id='".$assessment->id."' AND condition='c0'");
        
        if ($images){
            $image1 = !empty($images[0]) ? "images/assessment/".$assessment->id."/field/".$images[0]->filename : null;
            //$image2 = !empty($images[1]) ? Yii::getPathOfAlias('webroot')."\\images\\assessments\\".$assessment->id."\\".$images[1]->filename : null;
                        
            if($image1)
                $this->pdf->writeHTML('<img src="'.$image1.'" width="275" /><br>View of Home', true, false, true, false, 'C');
        }
        
        $this->pdf->lastPage();
        $this->pdf->AddPage();
        
        $this->pdf->writeHTML('<b>Your Wildfire Assessment Report</b>', true, false, true, false, 'C');
        
        $html = <<<HTML
            The information in this report identifies conditions and offers recommendations for reducing wildfire risk. The condition numbers correspond with those on the Wildfire Hazoard Assessment Checklist provided at the time of the assessment.
            <br><br>The following pictures record conditions of concern that were noted during the inspection of your home.
            <b>When these hazards are modified, your wilfire risk can re reduced</b>, and your home will have an improved chance of surviving a wildfire.
            <br>
HTML;
        
        $this->pdf->writeHTML($html);
        
        if($assessment->roof_material->checked  ||
           $assessment->roof_condition->checked ||
           $assessment->roof_venting->checked   ||
           $assessment->gutters->checked        ||
           $assessment->wall_openings->checked  ||
           $assessment->windows->checked        ||
           $assessment->siding->checked         ||
           $assessment->home_elevated->checked  ||
           $assessment->attachment->checked) $this->pdf->writeHTML('<u>HOUSE/STRUCTURE</u>');
        
            
            
        if ($assessment->roof_material->checked  ||
            $assessment->roof_condition->checked ||
            $assessment->roof_venting->checked   || 
            $assessment->roof_eaves->checked) $this->pdf->writeHTML('<u>Roof:</u>');
        
        
        /********** Roof Material ************/
        if ($assessment->roof_material->checked) {
            $this->pdf->writeHTML('<u>Condition 1:</u>');
            $this->pdf->writeHTML('<b>Roof covering or assembly is NOT resistant to burning ember of ignitions due to materials being wood and/or in poor condition.</b><br>'); 
            $this->addRec('c1');
            $this->addExample('c1');
        }

        /********** Roof Condition ************/
        if ($assessment->roof_condition->checked) {
            $this->pdf->writeHTML('<u>Condition 2:</u>');
            $this->pdf->writeHTML('<b>Roof contains combustible material, debris, and/or litter (leaves, pine needles, etc.). Embers could land in this material, ignite the roofing material/underlayment, and spread fire into the home.</b><br>');
            $this->addRec('c2');
            $this->addExample('c2');
        }

        /********** Roof Ventilation ************/
        if ($assessment->roof_venting->checked) {
            
            $this->pdf->writeHTML('<u>Condition 3:</u>');
            $this->pdf->writeHTML('<b>Roof venting provides openings that could allow ember entry into the attic and the interior of structure. Blowing embers or firebrands can precede a wildfire and blow into small spaces or openings which can then ignite the home.</b><br>');
            $this->addRec('c3');
            $this->addExample('c3');
        }
        
        /********** Rain Gutters ************/
        if ($assessment->gutters->checked) {
            $this->pdf->writeHTML('<u>Condition 4:</u>');
            $this->pdf->writeHTML('<b>Gutters are present and contain combustible materials, debris, and/or litter (leaves, pine needles, etc.) which are easily ignited by embers. Smoldering or flaming debris in the gutter can ignite roofing material or the tar paper and plywood underlayment and spread into the structure.</b><br>');
            $this->addRec('c4');
            $this->addExample('c4');
        }
        
        /********** Rain Gutters ************/
        if ($assessment->gutters->checked) {
            $this->pdf->writeHTML('<u>Condition 5:</u>');
            $this->pdf->writeHTML('<b>Open eaves have cracks and/or openings allowing ember entry to the structure. Igniting the eaves and any combustible material (dead leaves, animal nest, etc.) that could allow entry to interior of structure.</b><br>');
            $this->addRec('c5');
            $this->addExample('c5');
        }
        

        /********** Wall Openings ************/
        if ($assessment->wall_openings->checked) {
            $this->pdf->writeHTML('<u>Condition 6:</u>');
            $this->pdf->writeHTML('<b>Structure contains openings (vents, crawl space, holes/gaps, etc.) that are large enough to allow ember entry to the interior of the structure. Flaming embers or firebrands can precede a wildfire, blowing into small spaces or openings, which can then ignite and spread into the home.</b><br>');
            $this->addRec('c6');
            $this->addExample('c6');
        }
        
        /********** Windows ************/
        if ($assessment->windows->checked) {
            $this->pdf->writeHTML('<u>Condition 7:</u>');
            $this->pdf->writeHTML('<b>Structure has single-pane windows that could be fractured by the radiant heat from burning combustible materials/vegetation or strong, seasonal/fire-influenced winds, and allow ember entry to interior of structure.</b><br>');
            $this->addRec('c7');
            $this->addExample('c7');
        }

        /********** Siding ************/
        if ($assessment->siding->checked) {
            $this->pdf->writeHTML('<u>Condition 8:</u>');
            $this->pdf->writeHTML('<b>Siding materials are combustible and could be threatened by blowing embers or combustible materials/vegetation in the immediate area, which could ignite the siding and spread into the home.</b><br>');
            $this->addRec('c8');
            $this->addExample('c8');
        }
        
        /********** Home Elevated ************/
        if ($assessment->home_elevated->checked) {
            $this->pdf->writeHTML('<u>Condition 9:</u>');
            $this->pdf->writeHTML('<b>The home is elevated off the ground and combustible materials are present underneath the structure that could be ignited by embers or reached by flame, which could then burn into the house.</b><br>');
            $this->addRec('c9');
            $this->addExample('c9');
        }
        
        /********** Attachment ************/
        if ($assessment->attachment->checked) {
            $this->pdf->writeHTML('<u>Condition 10:</u>');
            $this->pdf->writeHTML('<b>Combustible attachment is connected to the structure which could be ignited by blowing embers or flame contact from nearby combustible materials/ vegetation and spread fire to the home.</b><br>');
            $this->addRec('c10');
            $this->addExample('c10');
        }

        /********** Continuous Combustible Vegetation 0-30ft ************/
        if ($assessment->cont_comb_veg_0_30->checked) {
            $this->pdf->writeHTML('<u>Condition 11:</u>');
            $this->pdf->writeHTML('<b>Continuous combustible vegetation exists that could allow a fire to reach the structure or attachment(s). Lack of defensible space significantly increases the risk of wildfire intrusion to the property.</b><br>');
            $this->addRec('c11');
            $this->addExample('c11');
        }
        
        /********** Continuous Combustible Material ************/
        if ($assessment->cont_comb_material->checked) {
            $this->pdf->writeHTML('<u>Condition 12:</u>');
            $this->pdf->writeHTML('<b>Combustible materials exist that could ignite from ember or flame contact. Fire embers or flames can ignite this kindling and may allow fire to move onto or into the structure.</b><br>');
            $this->addRec('c12');
            $this->addExample('c12');
        }
        
        /********** Additional Structure (0-30ft) ************/
        if ($assessment->additional_struct_0_30->checked) {
            $this->pdf->writeHTML('<u>Condition 13:</u>');
            $this->pdf->writeHTML('<b>Additional structure(s) on homeowner’s property could contribute to fire spread/behavior</b><br>');
            $this->addRec('c13');
            $this->addExample('c13');
        }
        
        /********** Continuous Combustible Vegetation (30-100ft) ************/
        if ($assessment->cont_comb_veg_30_100->checked) {
            $this->pdf->writeHTML('<u>Condition 14:</u>');
            $this->pdf->writeHTML('<b>Continuous combustible vegetation exists that could create intense fire behavior. Close proximity to unmanaged wildland fuels can significantly increase the risk of wildfire intrusion to the property.</b><br>');
            $this->addRec('c14');
            $this->addExample('c14');
        }
        
        
        /********** Additional Structure (30-100ft) ************/
        if ($assessment->additional_struct_30_100->checked) {
            $this->pdf->writeHTML('<u>Condition 15:</u>');
            $this->pdf->writeHTML('<b>Additional structure(s) on homeowner’s property could contribute to fire spread/behavior</b><br>');
            $this->addRec('c15');
            $this->addExample('c15');
        }
        
        
        /********** Summary Info ************/
        $this->pdf->writeHTML('<b><u>SUMMARY OF RESIDENTIAL WILDFIRE HAZARDS:</u></b>');
        
        $this->pdf->writeHTML('The preceding recommendations are within the control of the homeowner and when completed can improve the chances of the home surviving a wildfire.<br>');
        
        
        if(!$assessment->roof_material->checked          ||
           !$assessment->roof_condition->checked         ||
           !$assessment->roof_venting->checked           ||
           !$assessment->gutters->checked                ||
           !$assessment->roof_eaves->checked             ||
           !$assessment->wall_openings->checked          ||
           !$assessment->windows->checked                ||
           !$assessment->siding->checked                 ||
           !$assessment->home_elevated->checked          ||
           !$assessment->attachment->checked){
                $this->pdf->writeHTML('<b><u>HOUSE/STRUCTURE</u></b>');

                $this->pdf->writeHTML('The house construction and materials used are exceptional choices for a <b>Firewise</b> home. The exterior of the home is well maintained.<br>');
        }
        
        if(!$assessment->cont_comb_veg_0_30->checked     ||
           !$assessment->cont_comb_material->checked     ||
           !$assessment->additional_struct_0_30->checked ||
           !$assessment->cont_comb_veg_30_100->checked   ||
           !$assessment->additional_struct_30_100->checked){
                $this->pdf->writeHTML('<b><u>COMBUSTIBLE VEGETATION & MATERIALS</u></b>');

                $this->pdf->writeHTML('The landscaping is well-managed and manicured, with well-maintained, well-irrigated lawns. The combustible vegetation and materials do not pose a significant risk at this time.<br>');
        }
        
        $this->pdf->writeHTML('<b><u>ADJACENT SURROUNDING/ENVIRONMENT</u></b>');
        
        $this->pdf->writeHTML('WILDFIRE HAZARDS OUTSIDE OF THE RESIDENT\'S CONTROL<br>');
        
        if ($assessment->slope->checked || $assessment->canyon_valley_draw->checked || $assessment->wind->checked)
            $this->pdf->writeHTML('Item A. Topographical or environmental features exist that contribute to extreme wildland fire behavior.');

            
            
        if ($assessment->slope->checked) {
            $this->pdf->writeHTML('<b>Due to location on slope, wildfire risk is increased.</b><br>');
            $this->addRec('cA1', 'The property is located on a slope. Wildfires run faster upslope and generate greater flame lengths that can come into direct contact with the structure.');
        }
        
        if($assessment->canyon_valley_draw->checked) {
            $this->pdf->writeHTML('<b>Due to proximity of home to canyons, saddles, or draws, wildfire risk is increased.</b><br>');
            $this->addRec('cA2', 'In a wildfire event, winds can funnel through canyons, valleys and drainages, picking up speed, and increasing the wildfire risk.');
        
            if (!$assessment->slope->checked){
                $this->pdf->lastPage();
                $this->pdf->AddPage();
            }
        }
        
        if ($assessment->wind->checked) {
            $this->pdf->writeHTML('<b>Due to exposure to strong winds or other critical fire weather conditions, wildfire risk is increased.</b><br>');
            $this->addRec('cA3', 'Erratic winds can intensify wildfire activity in the immediate area.');
        
            if (!$assessment->slope->checked && !$assessment->canyon_valley_draw->checked){
                $this->pdf->lastPage();
                $this->pdf->AddPage();
            }
        }
        
        /********** Unmanaged Wildland Fuel ************/
        if ($assessment->unmanaged_wildland_fuel->checked) {
            $this->pdf->writeHTML('<b>Item B. Volatile vegetation exist adjacent to Home Ignition Zone (100+ feet from home).</b><br>');
            $this->addRec('cB', 'Close proximity to unmanaged wildland fuels can significantly increase the risk of wildfire intrusion to the property. Volatile fuels can create extreme fire conditions which can increase the risk to a structure. Wildland fuels can generate a considerable ember storm which may fall upon any burnable material adjacent to the structure.');
        
            if (!$assessment->slope->checked && !$assessment->canyon_valley_draw->checked && !$assessment->wind->checked){
                $this->pdf->lastPage();
                $this->pdf->AddPage();
            }
        }

        /********** Neighboring Property ************/
        if ($assessment->neighboring_property->checked) {
            $this->pdf->writeHTML('<b>Item C. Combustible fuels on a neighboring property are in the Home Ignition Zone (&lt;100 feet from home).</b><br>');
            $this->addRec('cC', 'The approaching fire may ignite fuels (structure, vegetation, etc.) on a neighboring property, increase fire behavior, and spread it to the home. Working closely with neighbors on a neighborhood fire plan can greatly reduce the present fire risk.');
        
            if (!$assessment->slope->checked && !$assessment->canyon_valley_draw->checked && !$assessment->wind->checked && !$assessment->unmanaged_wildland_fuel->checked){
                $this->pdf->lastPage();
                $this->pdf->AddPage();
            }
        }
       
        if(!$assessment->canyon_valley_draw->checked      ||
           !$assessment->wind->checked                    ||
           !$assessment->unmanaged_wildland_fuel->checked ||
           !$assessment->neighboring_property->checked    ||
           !$assessment->address->checked                 ||
           !$assessment->driveway->checked                ||
           !$assessment->bridge->checked                  ||
           !$assessment->suppression_resources->checked){
                $this->pdf->writeHTML('<b><u>WILDFIRE HAZARDS OUTSIDE OF THE RESIDENT\'S CONTROL</u></b>');

                $this->pdf->writeHTML('The adjacent surrounding/environment does not pose a significant risk at this time.<br>');
        }
        
        
        $this->pdf->writeHTML('<b><u>FIRE PROTECTION CONCERNS NOT RELATED TO THE POTENTIAL IGNITION OF THE HOME</u></b>');
        $this->pdf->writeHTML('There are no additional fire protection concerns at this time.<br>');
        
        
        $this->pdf->lastPage();
        $this->pdf->AddPage();
            
        $this->pdf->writeHTML('<b><u>Additional Comments</u></b>');
        $this->pdf->writeHTML($assessment->additional_comments);
        
        $this->pdf->lastPage();
        $this->pdf->AddPage();
        
        $this->pdf->writeHTML('<br><br><br>');
        
        if(isset($assessment->homeowner) && $assessment->homeowner->state == "California")
            $this->pdf->writeHTML('The homeowner is encouraged to abide by all State fire codes pertaining to the Wildland Urban Interface, which can be found in Chapter 47 of the California State Fire Code. For additional information regarding fire codes in your specific community, contact your local fire department.<br><br>', true, false, true, false, "C");
        else
            $this->pdf->writeHTML('The homeowner is encouraged to abide by all State fire codes pertaining to the Wildland Urban Interface.<br><br>', true, false, true, false, "C");

            
        $this->pdf->writeHTML('For inquiries regarding this report or the materials/suggestions herein, please feel free to contact:', true, false, true, false, "C");
        
        $this->pdf->writeHTML('<b>Wildfire Defense Systems, Inc.</b>', true, false, true, false, "C");
        $this->pdf->writeHTML('<b>(877)323-4730   ha@wildfire-defense.com</b><br><br>', true, false, true, false, "C");
        
        $this->pdf->writeHTML('The components of this assessment are derived from the National Fire Protection Associa- tion’s© National FirewiseTM Program and the NFPA Standard 1144.');
        $this->pdf->writeHTML('www.nfpa.org', true, false, true, false, "C");
        $this->pdf->writeHTML('www.firewise.org', true, false, true, false, "C");
        $this->pdf->writeHTML('There are no guarantees that mitigation steps taken as a result of this report will prevent damage. Neither USAA, Wildfire Defense Systems, Inc. nor their representatives take responsibility for personal injury or property damage arising out of reliance on the wildfire mitigation recommendations or this assessment report.<br><br>');
        $this->pdf->writeHTML('The evaluations, reports and recommendations regarding changes that should be considered to help protect your property are designed to provide additional protection in the event of wildfire. Even if every recommended step is taken, your property could still be destroyed because wildfire is unpredictable and can be impossible to stop or control, no matter what mitigation efforts have been undertaken. WDS does not represent or warrant that taking the steps suggested can or will protect your property from destruction by fire. No warranties or representations of any kind are provided to the recipient of this evaluation.');

        
        if (!is_dir(Yii::getPathOfAlias('webroot').'/protected/reports/')) {
            mkdir(Yii::getPathOfAlias('webroot').'/protected/reports/', 0755, true);
        }       
        
        if(file_exists(Yii::getPathOfAlias('webroot').'/protected/reports/assessment_'.$assessment->id.'.pdf')){
            unlink (Yii::getPathOfAlias('webroot').'/protected/reports/assessment_'.$assessment->id.'.pdf');
            //Close and output PDF document
            $this->pdf->Output(Yii::getPathOfAlias('webroot').'/protected/reports/assessment_'.$assessment->id.'.pdf', 'F'); 
        }
        else
            $this->pdf->Output(Yii::getPathOfAlias('webroot').'/protected/reports/assessment_'.$assessment->id.'.pdf', 'F');
    }
    
    
    function addRec($condition, $static_text = null){
        
        $images = SelectedImage::model()->findAll("assessment_id='".$this->assessment->id."' AND condition='".$condition."'");
        
        
        if ($images){
            $image1 = !empty($images[0]) ? "images/assessment/".$this->assessment->id."/field/".$images[0]->filename : null;
            $image2 = !empty($images[1]) ? "images/assessment/".$this->assessment->id."/field/".$images[1]->filename : null;

            if($image1 && !$image2){
                $html = '<table cellpadding="5">' .
                                '<tr nobr="true">' .
                                    "<td>" .
                                    '<img src="'.$image1.'" width="275" />' .
                                '</td>' .
                                '<td>';
                               
                                    $html .= isset($static_text) ? $static_text : '<b>Recommendation:</b> '.$this->$condition->recommendation;
                   $html .=     '</td>'.
                            '</tr></table>';
                        
            }
            elseif (isset($image2)){
                $html = "<table><tr nobr='true'>"; 
                foreach ($images as $key => $image) {
                    if ($key < 2){
                        $html .= '<td>'.
                                '  <img src="'.$image.'" width="275" />'.
                                '</td>';
                    }
                }
                $html .= '</tr></table>';
                $html .= '<b>Recommendation:</b><br>';
                $html .= $this->$condition->recommendation;
                
            }
            else continue;
            
            $html .= '<br><br>';
            $this->pdf->writeHTML($html);
        }
        
        $this->rec_count++;
        if ($this->rec_count == 2){
            
            
            $this->pdf->lastPage();
            $this->pdf->AddPage();
            $this->rec_count = 0;
        }
    }
    
    function addExample($condition){
        
        $img_url = Yii::app()->baseUrl."/".$this->$condition->example_photo_url;

        if (!is_null($this->$condition->example_photo_url)){
            /***  Example Image  ***/
            $html = '<table><tr>';
            $html .= '<td>'.
                     '  <img src="'.$img_url.'" width="275" />'.
                     '</td>';

            
            $html .= '<td valign="center"><b>Example:</b>';
            $html .= $this->$condition->example;

            $html .= '</td>';
            $html .= '</tr></table>';
            $this->pdf->writeHTML($html);
            
            $this->pdf->lastPage();
            $this->pdf->AddPage();
        }
    }
}

?>
