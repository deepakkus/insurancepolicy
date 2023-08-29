<?php

Yii::import('application.vendors.*'); 
Yii::import('application.vendors.PHPWord.*'); 
spl_autoload_unregister(array('YiiBase','autoload'));
require_once 'PHPWord.php';
spl_autoload_register(array('YiiBase','autoload'));


class DocCreator {
    private $doc;
    private $PHPWord;
    private $assessment;
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
                                'c11'    => 'top_of_deck',
                                'c12'    => 'under_deck',
                                'c13'    => 'cont_comb_veg_0_30',
                                'c14'    => 'cont_comb_material',
                                'c15'    => 'additional_struct_0_30',
                                'c16'    => 'cont_comb_veg_30_100',
                                'c17'    => 'additional_struct_30_100',
                                'cA1'    => 'slope',
                                'cA2'    => 'canyon_valley_draw',
                                'cA3'    => 'wind',
                                'cB'     => 'unmanaged_wildland_fuel',
                                'cC'     => 'neighboring_property',
                                'cAA1'   => 'address',
                                'cAA2'   => 'driveway',
                                'cAA3'   => 'bridge',
                                'cBB'    => 'suppression_resources');
    
    private $section;
    
    function DocCreator() {
        $this->PHPWord = new PHPWord();
    }
    
    function createDoc($assessment) {
        $this->assessment = $assessment;   
        // Every element you want to append to the word document is placed in a section. So you need a section:
        $this->section = $this->PHPWord->createSection();
        
        $this->PHPWord->addFontStyle('bold', array('name'=>'Arial', 'size'=>10, 'color'=>'000', 'bold'=>true));
        $this->PHPWord->addFontStyle('header', array('name'=>'Arial', 'size'=>14, 'bold'=>true));
        $this->PHPWord->addParagraphStyle('center', array('align'=>'center'));
        $style_align_right = array('align'=>'right');

        $this->section->addImage('images/logo.png', array('align'=>'center'));
        $this->section->addTextBreak();

        $this->section->addText('WILDFIRE DEFENSE SYSTEMS, INC.', array('name'=>'Arial', 'size'=>14, 'color'=>'3399ff'), 'center');
        $this->section->addTextBreak(2);
        $this->section->addText('We are pleased to provide this hazard assessment report for your property located at:');
        $this->section->addTextBreak();

        if(isset($assessment->homeowner->address)) $this->section->addText($assessment->homeowner->address, 'bold', 'center');
        $this->section->addTextBreak(2);
        $this->section->addText('This service was provided at the request of USAA to complement the safety of your home and family. Understanding your wildlife hazards and how to address them can greatly reduce the risk of losing your home to a wildfire.');
        
        $this->section->addTextBreak(3);
        

        
                

        // Add table
        $cover_table = $this->section->addTable();
        // Add row
        $cover_table->addRow();
        $cover_table->addCell(3000)->addText('This report was prepared for:');
        $cover_table->addCell(2000)->addText('NAME:', null, $style_align_right);
        $cover_table->addCell(2000)->addText(isset($assessment->homeowner->name) ? $assessment->homeowner->name : "", 'bold');
        $cover_table->addRow();
        $cover_table->addCell(2000);
        $cover_table->addCell(2000)->addText('MEMBER #:', null, $style_align_right);
        $cover_table->addCell(2000)->addText(isset($assessment->homeowner->member_num) ? $assessment->homeowner->member_num : "", 'bold');

        $this->section->addTextBreak(2);

        
        $cover_table_2 = $this->section->addTable();
        $cover_table_2->addRow();
        $cover_table_2->addCell(2000)->addText('Inspection Date:');
        $cover_table_2->addCell(2000)->addText(isset($assessment->homeowner->completion_date) ? $assessment->homeowner->completion_date : "", 'bold');
        $cover_table_2->addRow();
        $cover_table_2->addCell(2000)->addText('Inspected By:');
        $cover_table_2->addCell(2000)->addText(isset($assessment->homeowner->assessor->name) ? $assessment->homeowner->assessor->name : "", 'bold');

        $this->section->addTextBreak(2);

        
        /********** View of Home Image ************/
        $images = SelectedImage::model()->findAll("assessment_id='".$assessment->id."' AND condition='".$condition."'");
        
        
        if ($images){
            $image1 = !empty($images[0]) ? Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$images[0]->filename : null;
            $image2 = !empty($images[1]) ? Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$images[1]->filename : null;
            
            echo Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$images[0]->filename;
            echo Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$images[1]->filename;
            
            $imageStyle = array('align'=>'center', 'height'=>200, 'width'=>250);
            if($image1)
                $this->section->addImage($image1, $imageStyle);
        }
        
        $this->section->addTextBreak();
        $this->section->addText('View of Home', 'bold', 'center');
        $this->section->addPageBreak();

        $this->section->addText('Your Wildfire Assessment Report', array('name'=>'Arial', 'size'=>14, 'bold'=>true), 'center');
        $this->section->addText('The information in this report identifies conditions and offers recommendations for reducing wildfire risk. The condition numbers correspond with those on the Wildfire Hazoard Assessment Checklist provided at the time of the assessment.');
        $this->section->addTextBreak();

        $textrun = $this->section->createTextRun();
        $textrun->addText('The following pictures record conditions of concern that were noted during the inspection of your home.');
        $textrun->addText(' When these hazards are modified, your wilfire risk can re reduced,', 'bold');
        $textrun->addText(' and your home will have an improved chance of surviving a wildfire');
        $this->section->addTextBreak();

        $this->section->addText('HOUSE/STRUCTURE', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
        
        /********** Roof Material ************/
        if ($assessment->roof_material->checked || $assessment->roof_condition->checked || $assessment->roof_venting->checked || $assessment->roof_eaves->checked) $this->section->addText('Roof:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
        if ($assessment->roof_material->checked) {
            $c1_textrun = $this->section->createTextRun();
            $c1_textrun->addText('Condition 1:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c1_textrun->addText('Roof covering or assembly is NOT resistant to burning ember of ignitions due to materials being wood and/or in poor condition.', array('bold'=>true));
            $this->section->addTextBreak();
            $this->addRec('c1');
            $this->addExample('c1');
            $this->section->addPageBreak();
        }
        


        /********** Roof Condition ************/
        if ($assessment->roof_condition->checked) {
            $c2_textrun = $this->section->createTextRun();
            $c2_textrun->addText('Condition 2:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c2_textrun->addText('Roof contains combustible material, debris, and/or litter (leaves, pine needles, etc.). Embers could land in this material, ignite the roofing material/underlayment, and spread fire into the home.', array('bold'=>true));
            $this->section->addTextBreak();
            $this->addRec('c2');
            $this->addExample('c2');
            $this->section->addPageBreak();
        }

        /********** Roof Ventilation ************/
        if ($assessment->roof_venting->checked) {
            $c3_textrun = $this->section->createTextRun();
            $c3_textrun->addText('Condition 3:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c3_textrun->addText('Roof venting provides openings that could allow ember entry into the attic and the interior of structure. Blowing embers or firebrands can precede a wildfire and blow into small spaces or openings which can then ignite the home.', array('bold'=>true));
            $this->section->addTextBreak();
            $this->addRec('c3', 'vent_covered', $assessment->roof_venting->vent_covered);
            $this->addExample('c3');
            $this->section->addPageBreak();
        }
        
        /********** Rain Gutters ************/
        if ($assessment->gutters->checked) {
            $c4_textrun = $this->section->createTextRun();
            $c4_textrun->addText('Condition 4:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c4_textrun->addText('Gutters are present and contain combustible materials, debris, and/or litter (leaves, pine needles, etc.) which are easily ignited by embers. Smoldering or flaming debris in the gutter can ignite roofing material or the tar paper and plywood underlayment and spread into the structure.');
            $this->section->addTextBreak();
            $this->addRec('c4');
            $this->addExample('c4');
            $this->section->addPageBreak();
        }
        
        /********** Rain Gutters ************/
        if ($assessment->gutters->checked) {
            $c5_textrun = $this->section->createTextRun();
            $c5_textrun->addText('Condition 5:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c5_textrun->addText('Gutters are present and contain combustible materials, debris, and/or litter (leaves, pine needles, etc.) which are easily ignited by embers. Smoldering or flaming debris in the gutter can ignite roofing material or the tar paper and plywood underlayment and spread into the structure.');
            $this->section->addTextBreak();
            $this->addRec('c5');
            $this->addExample('c5');
            $this->section->addPageBreak();
        }

        /********** Wall Openings ************/
        if ($assessment->wall_openings->checked) {
            $c6_textrun = $this->section->createTextRun();
            $c6_textrun->addText('Condition 6:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c6_textrun->addText('Structure contains openings (vents, crawl space, holes/gaps, etc.) that are large enough to allow ember entry to the interior of the structure. Flaming embers or firebrands can precede a wildfire, blowing into small spaces or openings, which can then ignite and spread into the home.');
            $this->section->addTextBreak();
            $this->addRec('c6');
            $this->addExample('c6');
            $this->section->addPageBreak();
        }
        
        /********** Windows ************/
        if ($assessment->windows->checked) {
            $c7_textrun = $this->section->createTextRun();
            $c7_textrun->addText('Condition 7:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c7_textrun->addText('Structure has single-pane windows that could be fractured by the radiant heat from burning combustible materials/vegetation or strong, seasonal/fire-influenced winds, and allow ember entry to interior of structure.');
            $this->section->addTextBreak();
            $this->addRec('c7');
            $this->addExample('c7');
            $this->section->addPageBreak();
        }

        /********** Siding ************/
        if ($assessment->siding->checked) {
            $c8_textrun = $this->section->createTextRun();
            $c8_textrun->addText('Condition 8:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c8_textrun->addText('Siding materials are combustible and could be threatened by blowing embers or combustible materials/vegetation in the immediate area, which could ignite the siding and spread into the home.');
            $this->section->addTextBreak();
            $this->addRec('c8');
            $this->addExample('c8', 'condition', $assessment->siding->condition);
            $this->section->addPageBreak();
        }
        
        /********** Home Elevated ************/
        if ($assessment->home_elevated->checked) {
            $c9_textrun = $this->section->createTextRun();
            $c9_textrun->addText('Condition 9:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c9_textrun->addText('The home is elevated off the ground and combustible materials are present underneath the structure that could be ignited by embers or reached by flame, which could then burn into the house.');
            $this->section->addTextBreak();
            $this->addRec('c9');
            $this->addExample('c9');
            $this->section->addPageBreak();
        }
        
        /********** Attachment ************/
        if ($assessment->attachment->checked) {
            $c10_textrun = $this->section->createTextRun();
            $c10_textrun->addText('Condition 10:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c10_textrun->addText('Combustible attachment is connected to the structure which could be ignited by blowing embers or flame contact from nearby combustible materials/ vegetation and spread fire to the home.');
            $this->section->addTextBreak();
            $this->addRec('c10');
            $this->addExample('c10', 'attachment', $assessment->attachment->attachment);
            $this->section->addPageBreak();
        }
        
//        /********** Above Deck ************/
//        if ($assessment->top_of_deck->checked) {
//            $c3_textrun = $this->section->createTextRun();
//            $c3_textrun->addText('Condition 11:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
//            $c3_textrun->addText('Combustible materials are present on combustible decking that could be ignited by embers (examples: patio cushions, coco mats, planters, etc.). These could ignite the deck, which could then spread fire into the home.');
//            $this->section->addTextBreak();
//            $this->addRec('c11');
//            $this->addExample('c11');
//            $this->section->addPageBreak();
//        }
//        
//        /********** Under Deck ************/
//        if ($assessment->under_deck->checked) {
//            $c3_textrun = $this->section->createTextRun();
//            $c3_textrun->addText('Condition 12:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
//            $c3_textrun->addText('Combustible materials are present under decking. Embers could ignite this material, spread fire to the deck, which could then burn into the house.');
//            $this->section->addTextBreak();
//            $this->addRec('c12');
//            $this->addExample('c12');
//            $this->section->addPageBreak();
//        }
        
        /********** Continuous Combustible Vegetation 0-30ft ************/
        if ($assessment->cont_comb_veg_0_30->checked) {
            $c11_textrun = $this->section->createTextRun();
            $c11_textrun->addText('Condition 11:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c11_textrun->addText('Continuous combustible vegetation exists that could allow a fire to reach the structure or attachment(s). Lack of defensible space significantly increases the risk of wildfire intrusion to the property.');
            $this->section->addTextBreak();
            $this->addRec('c11');
            $this->addExample('c11');
            $this->section->addPageBreak();
        }
        
        /********** Continuous Combustible Material ************/
        if ($assessment->cont_comb_material->checked) {
            $c12_textrun = $this->section->createTextRun();
            $c12_textrun->addText('Condition 12:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c12_textrun->addText('Combustible materials exist that could ignite from ember or flame contact. Fire embers or flames can ignite this kindling and may allow fire to move onto or into the structure.');
            $this->section->addTextBreak();
            $this->addRec('c12');
            if($assessment->cont_comb_material->firewood_checked){
                $this->addExample('c12', 'firewood_checked', '1');
                $this->section->addTextBreak();
            }
            if($assessment->cont_comb_material->mulch_checked){
                $this->addExample('c12', 'mulch_checked', '1');
                $this->section->addTextBreak();
            }
           
            $this->section->addPageBreak();
        }
        
        /********** Additional Structure (0-30ft) ************/
        if ($assessment->additional_struct_0_30->checked) {
            $c13_textrun = $this->section->createTextRun();
            $c13_textrun->addText('Condition 13:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c13_textrun->addText('Additional structure(s) on homeowner’s property could contribute to fire spread/behavior');
            $this->section->addTextBreak();
            $this->addRec('c13');
            $this->addExample('c13', 'structure', $assessment->additional_struct_0_30->structure);
            $this->section->addPageBreak();
        }
        
        /********** Continuous Combustible Vegetation (30-100ft) ************/
        if ($assessment->cont_comb_veg_30_100->checked) {
            $c14_textrun = $this->section->createTextRun();
            $c14_textrun->addText('Condition 14:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c14_textrun->addText('Continuous combustible vegetation exists that could create intense fire behavior. Close proximity to unmanaged wildland fuels can significantly increase the risk of wildfire intrusion to the property.');
            $this->section->addTextBreak();
            $this->addRec('c14');
            $this->addExample('c14');
            $this->section->addPageBreak();
        }
        
        /********** Additional Structure (30-100ft) ************/
        if ($assessment->additional_struct_30_100->checked) {
            $c15_textrun = $this->section->createTextRun();
            $c15_textrun->addText('Condition 15:', array('underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
            $c15_textrun->addText('Additional structure(s) on homeowner’s property could contribute to fire spread/behavior');
            $this->section->addTextBreak();
            $this->addRec('c15');
            $this->addExample('c15', 'structure', $assessment->additional_struct_30_100->structure);
            $this->section->addPageBreak();
        }
        
        $this->section->addText('SUMMARY OF RESIDENTIAL WILDFIRE HAZARDS:', array('name'=>'Arial', 'size'=>14, 'bold'=>true, 'underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
        $this->section->addText('The preceding recommendations are within the control of the homeowner and when completed can improve the chances of the home surviving a wildfire.');
        $this->section->addText('ADJACENT SURROUNDING/ENVIRONMENT', array('name'=>'Arial', 'size'=>14, 'bold'=>true, 'underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
        $this->section->addText('WILDFIRE HAZARDS OUTSIDE OF THE RESIDENT\'S CONTROL');
        
        if ($assessment->slope->checked || $assessment->canyon_valley_draw->checked || $assessment->wind->checked)
            $this->section->addText('Item A. Topographical or environmental features exist that contribute to extreme wildland fire behavior.');

            
        /********** Slope ************/
        if ($assessment->slope->checked) {
            $this->section->addText('Due to location on slope, wildfire risk in increased.');
            $this->section->addTextBreak();
            $this->addRec('cA1');
            $this->section->addTextBreak(2);
        }
        
        /********** Canyon, Valley, Draw ************/
        if($assessment->canyon_valley_draw->checked) {
            $this->section->addText('Due to proximity of home to canyons, saddles, or draws, wildfire risk is increased.', array('name'=>'Arial', 'bold'=>true));
            $this->section->addTextBreak();
            $this->addRec('cA2');
            $this->section->addTextBreak(2);
        }
        
        /********** Wind ************/
        if ($assessment->wind) {
            $this->section->addText('Due to exposure to strong winds or other critical fire weather conditions, wildfire risk is increased.');
            $this->section->addTextBreak();
            $this->addRec('cA3');
            $this->section->addTextBreak(2);
        }
        
        /********** Unmanaged Wildland Fuel ************/
        if ($assessment->unmanaged_wildland_fuel) {
            $this->section->addText('Item B. Volatile vegetation exist adjacent to Home Ignition Zone (100+ feet from home).');
            $this->section->addTextBreak();
            $this->addRec('cB');
            $this->section->addTextBreak(2);
        }

        /********** Neighboring Property ************/
        if ($assessment->neighboring_property) {
            $this->section->addText('Item C. Combustible fuels on a neighboring property are in the Home Ignition Zone (<100 feet from home).');
            $this->section->addTextBreak();
            $this->addRec('cC');
            $this->section->addTextBreak(2);
        }
        
        
        $this->section->addText('FIRE PROTECTION CONCERNS NOT RELATED TO THE POTENTIAL IGNITION OF THE HOME', array('name'=>'Arial', 'bold'=>true, 'underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
        $this->section->addText('There are no additional fire protection concerns at this time.');
        $this->section->addPageBreak();
        
        $this->section->addText('Additional Comments', array('name'=>'Arial', 'bold'=>true, 'underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE));
        $this->section->addText($assessment->additional_comments);
        
        $this->section->addTextBreak(3);
        $this->section->addText('The homeowner is encouraged to abide by all State fire codes pertaining to the Wildland Urban Interface, which can be found in Chapter 47 of the California State Fire Code. For additional information regarding fire codes in your specific community, contact your local fire department.', null, array('align'=>'center'));
        $this->section->addTextBreak(2);
        $this->section->addText('For inquiries regarding this report or the materials/suggestions herein, please feel free to contact:', null, array('align'=>'center'));
        $this->section->addText('Wildfire Defense Systems, Inc.', array('bold'=>true, 'size'=>14), array('align'=>'center'));
        $this->section->addText('(877)323-4730   ha@wildfire-defense.com', array('bold'=>true, 'size'=>14), array('align'=>'center'));
        $this->section->addTextBreak(2);
        $this->section->addText('The components of this assessment are derived from the National Fire Protection Associa- tion’s© National FirewiseTM Program and the NFPA Standard 1144.');
        $this->section->addText('www.nfpa.org', null, array('align'=>'center'));
        $this->section->addText('www.firewise.org', null, array('align'=>'center'));
        $this->section->addText('There are no guarantees that mitigation steps taken as a result of this report will prevent damage. Neither USAA, Wildfire Defense Systems, Inc. nor their representatives take responsibility for personal injury or property damage arising out of reliance on the wildfire mitigation recommendations or this assessment report.');
        $this->section->addTextBreak(2);
        $this->section->addText('The evaluations, reports and recommendations regarding changes that should be considered to help protect your property are designed to provide additional protection in the event of wildfire. Even if every recommended step is taken, your property could still be destroyed because wildfire is unpredictable and can be impossible to stop or control, no matter what mitigation efforts have been undertaken. WDS does not represent or warrant that taking the steps suggested can or will protect your property from destruction by fire. No warranties or representations of any kind are provided to the recipient of this evaluation.');

        
        $objWriter = PHPWord_IOFactory::createWriter($this->PHPWord, 'Word2007');
        if (!is_dir(Yii::app()->basePath.'/reports/')) {
            mkdir(Yii::app()->basePath.'/reports/', 0755, true);
        }       
        
        if(file_exists(Yii::app()->basePath.'/reports/assessment_'.$assessment->id.'.docx')){
            unlink (Yii::app()->basePath.'/reports/assessment_'.$assessment->id.'.docx');
            $objWriter->save(Yii::app()->basePath.'/reports/assessment_'.$assessment->id.'.docx');
        }
        else
            $objWriter->save(Yii::app()->basePath.'/reports/assessment_'.$assessment->id.'.docx');

    }
    
    
    function addRec($condition, $field=null, $value=null){
        
        $images = SelectedImage::model()->findAll("assessment_id='".$this->assessment->id."' AND condition='".$condition."'");
        
        echo "assessment id ". $this->assessment->id;
        echo "condition ". $condition. "\n";
        
        if ($images){
            $image1 = !empty($images[0]) ? Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$images[0]->filename : null;
            $image2 = !empty($images[1]) ? Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$images[1]->filename : null;

            if($image1 && !$image2){
                $table = $this->section->addTable();
                $table->addRow();
                foreach ($images as $image) {
                    $table->addCell(600)->addImage(Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$image->filename, array('height'=>200, 'width'=>250));
                }
                $rec_cell = $table->addCell(4000, array('valign'=>'center'));
                $rec_cell->addText('Recommendation:', array('bold'=>true));
                if (is_null($field)) 
                    $rec_cell->addText(Recommendation::model()->find("condition='".$this->conditions[$condition]."'")->rec);
                else
                    $rec_cell->addText(Recommendation::model()->find("condition='".$this->conditions[$condition]."' AND field='".$field."' AND value='".$value."'")->rec);
                
            }
            elseif ($image2){
                $table = $this->section->addTable();
                $table->addRow();
                foreach ($images as $image) {
                    $table->addCell(600)->addImage(Yii::getPathOfAlias('webroot')."/images/assessments/".$assessment0>id."/".$image->filename, array('height'=>200, 'width'=>250));
                }
                $this->section->addText('Recommendation:', array('bold'=>true));
                if (is_null($field)) 
                    $this->section->addText(Recommendation::model()->find("condition='".$this->conditions[$condition]."'")->rec);
                else
                    $this->section->addText(Recommendation::model()->find("condition='".$this->conditions[$condition]."' AND field='".$field."' AND value='".$value."'")->rec);
            }
            else continue;
            
            $this->section->addTextBreak(2);
        }
    }
    
    function addExample($condition, $field=null, $value=null, $include_general=null){
        
        $exImgDir = 'images/assessment/examples/';
            if (file_exists($exImgDir)){
                /***  Example Image  ***/
                $table = $this->section->addTable();
                $table->addRow();
                if (is_null($field)){
                    if(file_exists('images/assessment/examples/'.$condition.'.jpg'))
                        $table->addCell(600)->addImage('images/assessment/examples/'.$condition.'.jpg', array('height'=>200, 'width'=>250));
                }
                else{
                    if(file_exists('images/assessment/examples/'.$condition.'_'.$field.'_'.$value.'.jpg'))
                    $table->addCell(600)->addImage('images/assessment/examples/'.$condition.'_'.$field.'_'.$value.'.jpg', array('height'=>200, 'width'=>250));
                    //echo $condition.'_'.$field.'_'.$value.'.jpg';
                }
                $exCell = $table->addCell(4000, array('valign'=>'center'));
                $exCell->addText('Example:', array('bold'=>true));
                if (is_null($field)) 
                    $exCell->addText(Recommendation::model()->find("condition='".$this->conditions[$condition]."'")->example);
                else
                    $exCell->addText(Recommendation::model()->find("condition='".$this->conditions[$condition]."' AND field='".$field."' AND value='".$value."'")->example);

            }
    }
}

?>
