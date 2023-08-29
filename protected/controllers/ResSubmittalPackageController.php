<?php

class ResSubmittalPackageController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl',
            array(
                'WDSAPIFilter',
                'apiActions' => array(
                    WDSAPI::SCOPE_ENGINE => array(
                        'apiGetSubmittalPackagePDF'
                    )
                )
            )
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array(),
                'users'=>array('@'),
            ),
            array('allow',
				'actions' => array(
                    'apiGetSubmittalPackagePDF'
                ),
				'users' => array('*')
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function behaviors()
    {
        return array(
            'wdsLogger' => array(
                'class' => 'WDSActionLogger'
            )
        );
    }

    private function createSubmittalPackagePDF($notices, $engineSchedule)
    {
        // New instance of Res TCPDF subclass with custom footer
        $pdf = new ResPdfCreator(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Wildfire Defense Systems');
        $pdf->SetTitle('Submittal Package - ' . current($notices)->fire->Name);
        $pdf->SetSubject('Submittal Package');
        $pdf->SetKeywords('PDF, Submittal Package');
        $pdf->SetFont('times', '', 12);

		//set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 18, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 15);
		$pdf->setPrintHeader(false);
        //$pdf->setPrintFooter(false);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // list styles
        $pdf->setListIndentWidth(10);
        $pdf->setLIsymbol('circle');

        $pdf->AddPage();

        // ----------------- COVER PAGE -----------------------------

        $html = '
        <table>
            <tr>
                <td width="25%"><img height="101" width="92" src="/images/logo.jpg" alt="WDSLogo" /></td>
                <td width="75%"><h1>WILDFIRE DEFENSE SYSTEMS</h1></td>
            </tr>
        </table>
        <div style="text-align: center;">
            <h2 style="font-weight: normal;">';

        foreach ($notices as $notice)
        {
            $html .= $notice->client->name . '<br />';
        }

        $html .= '</h2>
        </div>';

        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        $html = '
        <div style="text-align: center;">
            <h2>Incident Submittal Package</h2>
            <h2>For</h2>
            <h2><i><u>' . $engineSchedule->fire->Name . '</u></i></h2>
            <h2>' . $engineSchedule->city . ', ' . $engineSchedule->state . '</h2>
            <h2 style="font-weight: normal;">Submitted To:</h2>
            <h2 style="font-weight: normal;">The Incident Commander or Team</h2>
            <h2 style="font-weight: normal;">Submitted By:</h2>
            <h2 style="font-weight: normal;">Wildfire Defense Systems Inc.<br />Operations Center<br />580 Zoot Enterprise Ln<br />Bozeman MT 59718</h2>
            <h2 style="font-weight: normal;">Operations Center Phone: (877) 323-4730 ext. 1</h2>
        </div>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        $bottomPage = $pdf->getPageHeight() - $pdf->getFooterMargin();

        $pdf->SetFont('times', '', 16);
        $pdf->Text(0,$bottomPage - 30,'Date:',false,false,true,0,0,'C',false,'',0,false,'T','M',false);
        $pdf->SetFont('times', 'B', 16);
        $pdf->Text(0,$bottomPage - 20,date('m/d/Y'),false,false,true,0,0,'C',false,'',0,false,'T','M',false);
        $pdf->SetFont('times', '', 12);

        $pdf->lastPage();
        $pdf->addPage();

        // ----------------- BOILERPLATE OPS STUFF ------------------

        $html = '
        <div style="text-align: center;">
            <h2>Insurance Resource Conditional Access Protocol </h2>
        </div>

        <h3>Insurance Program Resource Utilization Protocol</h3>
        <p>Insurance Resources are defined as wildland engines working under an Insurer Wildfire Program which contracts for services directly to the Insurer and not individual homeowners. The following protocols define the insurance resource requirements to be listed as an incident Cooperating Agency or Nongovernmental Organization (NGO) Agency and be granted conditional access within the wildfire evacuation zones. Any Insurance Program resource that does not meet the qualifications/credentials or protocols defined herein shall not be granted incident Cooperating Agency or Nongovernmental Organization (NGO) Agency status.</p>

        <h3>Conditional Access</h3>
        <p>Access shall be based upon Incident Command retaining situational awareness of the Insurance Program Resource in the same fashion as incident resources. Incident Command retains command and control to remove Insurance Program Resource (in the same fashion as incident resources) from any work zones in which fire behavior is deemed unsafe for wildfire operations.</p>

        <h3>Insurance Program Resource Mission </h3>
        <p>Wildland engine based pre-suppression and structure protection activities working within evacuated or restricted areas which consist primarily of incident ember zones, safety zones or in the black. This is not a first responder service. Pre-suppression defensive measures may include emergency fuel mitigation, zone sprinkler system setup, fuel break preparation, retardant application, fire blocking gel application, wildland engine operations and post fire front mop up to secure structures from residual wildfire threat.</p>

        <h3>Insurance Resource Required Qualifications/Requirements</h3>
        <br />
        <ul>
            <li>Wildfire operations  performed in accordance with the training, safety and operational requirements defined in the USFS Fireline Handbook (NWCG Handbook #3, PMS410-1) and the USFS Incident Response Pocket Guide (PMS#461), as well as other state requirements.</li>
            <li>Insurance Program Resource shall arrive and operate "wet" on the Incident (self-sufficient).</li>
            <li>For the purposes of qualification and inspection verification the insurance engine resource provider shall hold a current EERA or USFS Federal Suppression contract.</li>
            <li>Personnel to be NWCG 310-1 compliant with current Red Card to appropriate ICS position with photo identification and required PPE.  Personnel training and certification records to be inspected and approved annually under their EERA/Federal contract requirements.</li>
            <li>Engines shall meet NWCG standards for Type III to Type VII wildland engines, Tactical Tenders or Tenders which pass annual DOT and Federal/EERA contract equipment/compliment inspections.</li>
        </ul>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->lastPage();
        $pdf->addPage();

        $html = '
        <h3>Check In Procedure /Interface Procedures</h3>
        <br />
        <ul>
            <li>Insurance Program Resource shall contact dispatch while in route to incident and follow dispatcher instructions for check-in or locate ICP and check in with Liaison Officer or other appropriate ICT ranking personnel.</li>
            <li>If ICP is not established or located, report via radio and receive instructions.</li>
            <li>All Insurance Program Resources are required to check in with Incident Command and abide by Incident Command orders. Any Insurance Program which fails to meet these requirements shall be removed from the Incident.</li>
            <li>By the first attended briefing Insurance Program Resource shall provide a hard copy mission submittal packet to Incident Command and an electronic copy to the Liaison Officer if email address provided.</li>
        </ul>

        <h3>Reporting, Briefing and Communication Requirements</h3>
        <ul>
            <li>Insurance Program Resource to provide a liaison whom shall report to the ICT Liaison Officer and attend briefings as required by ICT.  The Insurance Program Liaison shall provide an updated submittal to include a list of all resources working in the area, properties effected and what level of pre-suppression activities have been initiated.</li>
            <li>The IC or Incident Liaison Officer shall provide ICT instructions to the Insurance Program Resources with whom they should coordinate their activities and communicate to while in the field.</li>
            <li>If pre-suppression occurs, Insurance Program Resource to document actions and report to Incident Command (i.e. gel application, zone sprinkler systems or other).</li>
            <li>Insurance Program Resources shall utilize programmable radios to federal standards outlined in the VIPR I-BPA for Water Handling, Engine and Support Water Tender or state EERA or equivalent. The radio must be capable of programming incident frequencies and of operating in the frequency range of 148MHz to 174MHz in the analog wide band (25KHz) and narrow band (12.5KHz) modes or P25 (digital) compliant radios in the frequency range of 138MHz to 174MHz.  Radios may be programmed at ICP via cloning or set to frequencies after receiving authorization by ICP or AHJ Dispatch.</li>
            <li>Insurance Program Resource contractor shall obtain their own FCC licensed frequency for their internal radio communications.</li>
        </ul>

        <h3>Engine Branding/Personnel Identification</h3>
        <ul>
            <li>Insurance engine resource may be equipped with red or yellow light bars (safety measure for visibility in smoke and visibility for aviation resources). Light bars shall be restricted to use on incident only.</li>
            <li>Insurance Program engines should not have presentation intended to mimic public emergency vehicles (color and markings similar to department/agency vehicles).</li>
            <li>Insurance Program engines shall be configured and equipped to meet NWCG specifications in order to meet engine and compliment specifications required by federal suppression or other state contracting. This may include appropriate wildland engine markings for federal and state service.</li>
            <li>Individual company and Insurance Program Resource branding shall be present on engines which is intended to designate engines as a non-public resource.</li>
            <li>Insurance Program personnel to carry photo identification and current Red Card (NWCG or CICCS) to verify qualifications and company identification.</li>
        </ul>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->lastPage();
        $pdf->addPage();

        $html = '
        <div style="text-aligh: center;">
            <h3>Additional Components</h3>
            <p>The following additional components provide supplementary details that are incorporated as part of this Incident Submittal Package.</p>
            <p>The Wildfire Defense Systems, Inc. Operation Center is located in Bozeman, Montana. The Operation Center utilizes internet based fire intelligence sources, local news, policyholder reporting and other available sources to monitor current fire activity. The policyholder property location in relationship to the fire activity is evaluated. The Duty Officer (minimum IC Type III or ICS position equivalent) determines whether response to a fire is warranted. Resources may be dispatched to the policyholder property when a wildfire threatens a policyholder property or a civil authority initiates an evacuation order which impacts a policyholder.</p>
            <p>Once access to the incident is granted, the enrolled properties are assessed and prepared for possible fire encroachment (see attached Insurance Resource Conditional Access Protocol). Policyholders are encouraged to obey all evacuation requests/orders. In addition to the Insurance Resource Conditional Access Protocol, the following templates are attached to all incident submittal packages provided to the ICT upon arrival to an incident.</p>
        </div>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->lastPage();
        $pdf->addPage();

        // ----------------- ADDRESS LIST ---------------------------

        $pdf->SetFont('times', '', 11);

        $noticeIDs = array_map(function($model) { return $model->notice_id; }, $notices);

        $enrolledTriggered = ResTriggered::model()->findAll(array(
            'condition' => "notice_id IN (" . implode(',', $noticeIDs) . ") AND property.response_status = 'enrolled'",
            'with' => 'property',
            'order' => 'property.address_line_1, property.city, property.zip',
            'limit' => 101
        ));

        $notEnrolledTriggered = ResTriggered::model()->findAll(array(
            'condition' =>  "notice_id IN (" . implode(',', $noticeIDs) . ") AND property.response_status != 'enrolled' AND threat = 1",
            'with' => 'property',
            'order' => 'property.address_line_1, property.city, property.zip, t.distance',
            'limit' => 101
        ));

        $html = '<h2><b><i>Homeowner Addresses</i></b></h2>';

        $html .= '
        <h3><b><i>Enrolled</i></b></h3>
        <table style="border: 1px solid #CCCCCC;">
            <tr>
                <td><b>ADDRESS</b></td>
                <td><b>CITY</b></td>
                <td><b>ZIP CODE</b></td>
            </tr>';

        $over100Enrolled = false;

        if (count($enrolledTriggered) === 101)
        {
            array_pop($enrolledTriggered);
            $over100Enrolled = true;
        }

        foreach ($enrolledTriggered as $policy)
        {
            $html .= '
            <tr style="border: 1px solid #CCCCCC;">
                <td style="border: 1px solid #CCCCCC;">' . $policy->property->address_line_1 . '</td>
                <td style="border: 1px solid #CCCCCC;">' . $policy->property->city . '</td>
                <td style="border: 1px solid #CCCCCC;">' . $policy->property->zip . '</td>
            </tr>';
        }

        $html .= '</table>';

        if ($over100Enrolled)
        {
            $html .= '<p>There are more than 100 enrolled properties.  Please see engine crew for full list.</p>';
        }

        $html .= '
        <br />
        <br />
        <br />
        <h3><b><i>Not Enrolled</i></b></h3>
        <table style="border: 1px solid #CCCCCC;">
            <tr>
                <td><b>ADDRESS</b></td>
                <td><b>CITY</b></td>
                <td><b>ZIP CODE</b></td>
            </tr>';

        $over100NotEnrolled = false;

        if (count($notEnrolledTriggered) === 101)
        {
            array_pop($notEnrolledTriggered);
            $over100NotEnrolled = true;
        }

        foreach ($notEnrolledTriggered as $policy)
        {
            $html .= '
            <tr style="border: 1px solid #CCCCCC;">
                <td style="border: 1px solid #CCCCCC;">' . $policy->property->address_line_1 . '</td>
                <td style="border: 1px solid #CCCCCC;">' . $policy->property->city . '</td>
                <td style="border: 1px solid #CCCCCC;">' . $policy->property->zip . '</td>
            </tr>';
        }

        $html .= '</table>';

        if ($over100NotEnrolled)
        {
            $html .= '<p>There are more than 100 not enrolled properties.  Please see engine crew for full list.</p>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->lastPage();
        $pdf->addPage();

        // ----------------- RESOURCE ORDER -------------------------

        // Setting client logo image size for resource order

        $pdf->SetFont('times', '', 10);

        $html = '
        <table style="text-align: center;" cellpadding="10px">
            <tr>
                <td style="text-align: left;">Incident: ' . $engineSchedule->assignment . ($engineSchedule->fire_id ? ' (<i>' . $engineSchedule->fire_name . '</i>)' : '') . '</td>
                <td style="text-align: right;">Resource Order: ' . $engineSchedule->resource_order_num . '</td>
            </tr>
            <tr>
                <td colspan="2">' .
                    implode('<br />', array_map(function($engineClient) { return $engineClient->client->name; }, $engineSchedule->engineClient)) .
                '</td>
            </tr>
        </table>';

        $resourceOrderUserName = isset($engineSchedule->resourceOrder->user_name) ? $engineSchedule->resourceOrder->user_name : '';

        $html .=
        '<br />

        <table style="text-align: center; vertical-align: middle;" cellpadding="4px">
            <tr>
                <td style="width: 25%; border: 1px solid #000000;">Assignment Name and Location</td>
                <td style="width: 20%; border: 1px solid #000000;">Order Time</td>
                <td style="width: 20%; border: 1px solid #000000;">Estimated Incident Arrival Time</td>
                <td style="width: 35%; border: 1px solid #000000;">Ordered By</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000000;"><b>' . $engineSchedule->resourceOrderGetAssignment() . '</b></td>
                <td style="border: 1px solid #000000;"><b>' . $engineSchedule->resourceOrderNearestQuarterHour(date('d-m-Y H:i')) . '</b></td>
                <td style="border: 1px solid #000000;"><b>' . date('m/d/Y \a\t H:i \M\D\T', strtotime($engineSchedule->arrival_date)) . '</b></td>
                <td style="border: 1px solid #000000;"><b>WDS Staff:<br />' . $resourceOrderUserName . '</b></td>
            </tr>
        </table>

        <br />

        <table style="text-align: center; vertical-align: middle;" cellpadding="4px">
            <tr>
                <td style="width: 25%; border: 1px solid #000000;">Company Name and Phone #</td>
                <td style="width: 20%; border: 1px solid #000000;">Preseason Agreement #</td>
                <td style="width: 20%; border: 1px solid #000000;">Resource Requested</td>
                <td style="width: 35%; border: 1px solid #000000;">Engine Boss and Contact Info</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . $engineSchedule->resourceOrderGetCompanyInfo() . '</b></td>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . ($engineSchedule->engine->alliancepartner ? $engineSchedule->engine->alliancepartner->preseason_agreement : '') . '</b></td>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . $engineSchedule->engine->engine_name . '</b></td>
                <td style="border: 1px solid #000000;" valign="middle"><b>' . $engineSchedule->resourceOrderGetEngineBoss() . '</b></td>
            </tr>
        </table>

        <br />

        <table style="border: 1px solid #000000;" cellpadding="6px">
            <tr>
                <td>
                    <span>Instructions</span>
                    <ul>
                        <li>Specific Instructions: ' . $engineSchedule->specific_instructions . '</li>
                        <li>Fire Officer: ' . $engineSchedule->resourceOrderGetFireOfficer() . '</li>
                        <li>Morning WDS briefing - 1000 hrs MDT and the number is (515) 604-9094 (contact supervisor for the code)</li>
                        <li>Insure engine is fully equipped with sprinkler kits, gel, and all electronic equipment.</li>
                        <li>Send all shift tickets daily to eest@wildfire-defense.com.</li>
                        <li>Wildfire Defense Systems, Inc. &ndash; (406)586-5400 ext. 1 or (877)-323-4730 ext. 1</li>
                    </ul>
                </td>
            </tr>
        </table>

        <br />
        <br />

        <table style="font-weight: bold;">
            <tr>
                <th width="12%" style="text-align: right;">Crew Manifest: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . implode(' / ', array_map(function($employee) { return "$employee->crew_first_name $employee->crew_last_name"; }, $engineSchedule->employees)) . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">Make: &nbsp;&nbsp;</th>
                <td width="88%" style="border: none; text-align: left;">' . $engineSchedule->engine->make . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">Model: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . $engineSchedule->engine->model . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">VIN: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . $engineSchedule->engine->vin . '</td>
            </tr>
            <tr>
                <th width="12%" style="text-align: right;">Plate: &nbsp;&nbsp;</th>
                <td width="88%" style="text-align: left;">' . $engineSchedule->engine->plates . '</td>
            </tr>
        </table>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        // ----------------- OUTPUT PDF -----------------------------

        //Allow user to choose where to save
        $fileName = Yii::getPathOfAlias('webroot.protected.downloads') . DIRECTORY_SEPARATOR . 'Submittal Package.pdf';
        $pdf->Output($fileName, 'F');
        return $fileName;
    }

    /*
     *  resSubmittalPackage/apiGetSubmittalPackagePDF
     *  $noticeID - id of the notice in which the fire information is pulled form
     *  $engineScheduleID - the id for the engine's schedule entry
     */
    public function actionApiGetSubmittalPackagePDF()
    {
        if (!WDSAPI::getInputDataArray($data, array('engineScheduleID', 'noticeIDs')))
            return;

        if (!is_array($data['noticeIDs']))
        {
            return WDSAPI::echoJsonError('ERROR: incorrect attributes recieved.', '"noticeIDs" must be an array of notice IDs');
        }

        $notices = array();

        foreach ($data['noticeIDs'] as $noticeID)
        {
            $notice = ResNotice::model()->findByPk($noticeID);
            if ($notice)
            {
                $notices[] = $notice;
            }
        }

        $engineSchedule = EngScheduling::model()->findByPk($data['engineScheduleID']);

        //Get the file
        $filepath = $this->createSubmittalPackagePDF($notices, $engineSchedule);
        $fp = fopen($filepath, 'rb');
        $content = fread($fp, filesize($filepath));
        $content = unpack('H*hex', $content)['hex'];
        fclose($fp);

        $returnArray = array(
            'error' => 0,
            'data' => array()
        );

        if ($content)
        {
            $returnArray['data']['name'] = 'Submittal Package.pdf';
            $returnArray['data']['type'] = 'application/pdf';
            $returnArray['data']['data'] = $content;
        }
        else
        {
            $returnArray['error'] = 1;
        }

        WDSAPI::echoResultsAsJson($returnArray);
    }
}
