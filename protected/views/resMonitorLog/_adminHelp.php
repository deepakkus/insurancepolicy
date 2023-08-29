<?php $this->beginWidget('bootstrap.widgets.TbCollapse'); ?>
    <div class="accordion-group marginTop10">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse"
                data-parent="#accordion2" href="#collapseOne">
                <b>Monitoring links (click to expand)</b>
            </a>
        </div>

        <div id="collapseOne" class="accordion-body collapse">
            <div class="accordion-inner">
                <?php
                    $this->widget(
                        'bootstrap.widgets.TbTabs',
                        array(
                            'type' => 'tabs',
                            'tabs' => array(
                                array(
                                    'label' => 'Weather',
                                    'content' => '<ul>
					                        <li><a href = "http://www.nws.noaa.gov/largemap.php" target = "_blank">NOAA National Weather Hazard Map</a></li>
					                        <li><a href = "http://www.wunderground.com/wundermap/" target = "_blank">Animated Weather Maps from Weather Underground</a></li>
					                        <li><a href = "http://www.intellicast.com/Storm/Severe/Lightning.aspx" target = "_blank">Lightning Strikes Intellicast.com</a></li>
					                        <li><a href = "http://www.strikestarus.com/" target = "_blank">Astrogenic Strike Star Lightning Activity</a></li>
					                        <li><a href = "http://hint.fm/wind/" target = "_blank">US Wind Map</a></li>
					                        <li><a href = "http://earth.nullschool.net/" target = "_blank">Earth Wind Map</a></li>
					                        <li><a href = "https://earthdata.nasa.gov/labs/worldview/" target = "_blank">Aerials from NASA Worldview</a></li>
				                        </ul>
                                    ',
                                    'active' => true
                                ),
                                array(
                                    'label' => 'National Fire Monitoring',
                                    'content' => '
                                        <ul>
					                        <li><a href = "https://fsapps.nwcg.gov/afm/" target = "_blank">Forest Service Large Incidents Map</a></li>
					                        <li><a href = "http://inciweb.nwcg.gov/" target = "_blank">Inciweb Incident Information System</a></li>
					                        <li><a href = "http://www.wildlandfire.com" target = "_blank">Wildland Fire - the Home of the Wildland Firefighter</a></li>
					                        <li><a href = "https://fsapps.nwcg.gov/afm/googleearth.php" target = "_blank">MODIS - Fire Data in Google Earth</a></li>
					                        <li><a href = "http://www.wildcad.net/WildCADWeb.asp" target = "_blank">Wildcad Wildweb Dispatch Site Home</a></li>
					                        <li><a href = "http://www.nifc.gov/" target = "_blank">National Interagency Fire Center</a></li>
				                        </ul>
                                    '
                                ),
                                array(
                                    'label' => 'State Agency',
                                    'content' => '
                                        <ul>
					                        <li>Arizona: <a href = "http://wildlandfire.az.gov/" target = "_blank">Arizona Interagency Wildfire</a></li>
					                        <li>New Mexico: <a href = "http://nmfireinfo.com/" target = "_blank">New Mexico Fire Infornation</a></li>
					                        <!--<li>Oregon: <a href = "http://http://www.swofire.com/" target = "_blank">Oregon Forestry - SW District</a></li>-->
					                        <li>Texas: <a href = "http://tfseoc.tamu.edu/dispatchfield/tracker/" target = "_blank">Texas Interagency Coordination Center - Fire Activity</a></li>
					                        <li>Utah: <a href = "http://www.utahfireinfo.gov/" target = "_blank">Utah Fire Information</a></li>
				                        </ul>
                                    '
                                ),
                                array(
                                    'label' => 'GACC',
                                    'content' => '
                                        <ul>
					                        <li><a href = "https://gacc.nifc.gov/" target = "_blank">Geographic Area Coordination Center Home Page</a></li>
                                        </ul>
                                        
                                        <p><b>CA</b></p>
                                        
                                        <ul>
					                        <li><a href = "https://gacc.nifc.gov/oncc/" target = "_blank">Northern California</a></li>
					                        <li><a href = "https://gacc.nifc.gov/oscc/" target = "_blank">Southern California</a></li>
                                        </ul>
                                        
					                    <p><b>Rockies</b></p>
                                        
                                        <ul>
					                        <li><a href = "https://gacc.nifc.gov/nrcc/" target = "_blank">Northern Rocky Mountains</a></li>
					                        <li><a href = "https://gacc.nifc.gov/rmcc/" target = "_blank">Central Rocky Mountains</a></li>
                                        </ul>
					                        
                                        <p><b>Great Basin</b></p>
                                        
                                        <ul>
					                        <li><a href = "https://gacc.nifc.gov/egbc/" target = "_blank">Eastern Great Basin</a></li>
					                        <li><a href = "https://gacc.nifc.gov/wgbc/" target = "_blank">Western Great Basin</a></li>
                                        </ul>
					                        
                                        <p><b>Other</b></p>
                                        
                                        <ul>
					                        <li><a href = "http://www.nwccweb.us/index.aspx" target = "_blank">Pacific Northwest</a></li>
					                        <li><a href = "https://gacc.nifc.gov/sacc/" target = "_blank">Southern United States and Texas</a></li>
					                        <li><a href = "https://gacc.nifc.gov/swcc/" target = "_blank">Southwest</a></li>
				                        </ul>
                                    '
                                ),
                                array(
                                    'label' => 'Other',
                                    'content' => '<ul>
                                        <li><a href = "http://ftp.nifc.gov/incident_specific_data/" target = "_blank">Fire Perimeter Data from NIFC</a></li>
					                    <li><a href = "http://www.publiclands.org/firenews/AZ.php" target = "_blank">Arizona Public Lands Information Center</a></li>
					                    <li><a href = "http://www.idahofireinfo.blm.gov/south/dispatchctrs.htm" target = "_blank">South Central Idaho Interagency Dispatch Center</a></li>
					                    <li><a href = "http://www.coemergency.com/" target = "_blank">Colorado Emergency Management</a></li>
					                    <li><a href = "http://wildfiretoday.com/" target = "_blank">Wildfire Today</a></li>
					                    <li><a href = "http://www.earthpoint.us/Townships.aspx" target = "_blank">Township &amp; Range in Google Earth</a></li>
					                </ul>
                                    <p><b>Radio/Scanner</b></p>
                                    <ul>
					                    <li><a href = "http://www.radioreference.com/" target = "_blank">Radio Reference</a></li>
					                    <li><a href = "http://www.scancal.org/" target = "_blank">Radio Scanners for parts of Southern California</a></li>
				                    </ul>
                                '
                                )
                            )
                        )
                    );

                ?>
            </div>
        </div>
    </div>
<?php $this->endWidget(); ?>

<?php //$this->beginWidget('bootstrap.widgets.TbCollapse'); ?>
    <!--<div class="accordion-group">
        <div class="accordion-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
                <b>Monitoring Log Instructions (click to expand)</b>
            </a>
        </div>
        <div id="collapseTwo" class="accordion-body collapse">
            <div class="accordion-inner">
                <p><b>Create Entry:</b></p>
                <ol>
                    <li>Create Fire (using "Create New Fire Button")</li>
                    <li>Create Fire Details</li>
                    <li>Adding Log Entry</li>
                </ol>
                <p><b>Create Updated Entry:</b></p>
                <ol>
                    <li>Click button "Add New Entry".</li>
                    <li style="list-style-type:none"><b>When No Change to Fire Details</b>
                        <ul>
                            <li>Add New Log Entry for most recent detail entry.</li>
                        </ul>
                    </li>
                    <li style="list-style-type:none"><b>When there are New Details</b>
                        <ul>
                            <li>Select "Create Details" next to most recent entry.</li>
                            <li>Fill Out and save details.</li>
                            <li>Add Log Entry for new details.</li>
                        </ul>
                    </li>
                </ol>
            </div>
        </div>
    </div>-->
<?php //$this->endWidget(); ?>