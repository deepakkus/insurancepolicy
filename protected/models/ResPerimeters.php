<?php

/**
 * This is the model class for table "res_perimeters".
 *
 * The followings are the available columns in table 'res_perimeters':
 * @property integer $id
 * @property integer $fire_id
 * @property string $date_created
 * @property string $date_updated
 * @property integer $perimeter_location_id
 * @property integer $threat_location_id
 *
 * The followings are the available model relations:
 * @property ResFireName $resFireName
 * @property ResMonitorLog[] $resMonitorLog
 * @property Location $perimeterLocation
 * @property Location $threatLocation
 */
class ResPerimeters extends CActiveRecord
{
    public $fire_name;

    // Create Threat form
    public $kmlFileUpload;
    public $threatIDToCopy;

    // attributes to store well known text in from location table
    public $wktPerimeter;
    public $wktThreat;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'res_perimeters';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('fire_id', 'required'),
            array('id, fire_id, perimeter_location_id, threat_location_id', 'numerical', 'integerOnly' => true),
            array('date_updated', 'safe'),

            // new-threat form validation
            array('kmlFileUpload, threatIDToCopy', 'safe', 'on' => 'threat'),
            array('kmlFileUpload, threatIDToCopy', 'threatChoosen', 'on' => 'threat'),

            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('id, fire_id, date_created, date_updated, fire_name', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Custom validation rule.
     * Checking that either a file was uploaded or an old threat was chosen
     * @param string $attribute
     */
    public function threatChoosen($attribute)
    {
        if (empty(CUploadedFile::getInstance($this, 'kmlFileUpload')) && empty($this->threatIDToCopy))
        {
            $this->addError($attribute, 'You must select either a new or existing threat!');
        }
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'resFireName' => array(self::BELONGS_TO, 'ResFireName', 'fire_id'),
            'resMonitorLog' => array(self::HAS_MANY, 'ResMonitorLog', 'Perimeter_ID'),
            'perimeterLocation' => array(self::BELONGS_TO, 'Location', 'perimeter_location_id'),
            'threatLocation' => array(self::BELONGS_TO, 'Location', 'threatLocation'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'fire_id' => 'Fire',
            'date_created' => 'Date Created',
            'date_updated' => 'Date Updated',
            'perimeter_location_id' => 'Perimeter',
            'threat_location_id' => 'Threat',

            // Create Threat Form
            'kmlFileUpload' => 'File Upload',
            'threatIDToCopy' => 'Threat'
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->with = array(
            'resFireName' => array(
                'select' => array('Fire_ID','Name')
            )
        );

        $criteria->together = true;

        $criteria->select = array(
            't.id',
            't.fire_id',
            't.date_created',
            't.date_updated',
            't.perimeter_location_id',
            't.threat_location_id'
        );

        $criteria->compare('resFireName.Name', $this->fire_name, true);
        $criteria->compare('t.fire_id', $this->fire_id, true);
        if ($this->date_created)
        {
            $criteria->addCondition('t.date_created >= :today_created AND t.date_created < :tomorrow_created');
            $criteria->params[':today_created'] = date('Y-m-d', strtotime($this->date_created));
            $criteria->params[':tomorrow_created'] = date('Y-m-d', strtotime($this->date_created . ' + 1 day'));
        }

        if ($this->date_updated)
        {
            $criteria->addCondition('t.date_updated >= :today_updated AND t.date_updated < :tomorrow_updated');
            $criteria->params[':today_updated'] = date('Y-m-d', strtotime($this->date_updated));
            $criteria->params[':tomorrow_updated'] = date('Y-m-d', strtotime($this->date_updated . ' + 1 day'));
        }

        return new CActiveDataProvider($this, array(
             'sort' => array(
                'defaultOrder' => array('id' => CSort::SORT_DESC),
                'attributes' => array(
                    'fire_name' => array(
                        'asc' => 'resFireName.Name',
                        'desc' => 'resFireName.Name DESC',
                    ),
                    '*'
                ),
            ),
            'criteria' => $criteria,
            'pagination' => array('pageSize' => 20)
        ));
    }

    //----------------------------------------------------Standard Yii--------------------------------------------------

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ResPerimeters the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord)
        {
            $this->date_created = date('Y-m-d H:i');
        }

        $this->date_updated = date('Y-m-d H:i');

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        // Generate buffers

        if ($this->isNewRecord)
        {
            ResPerimeterBuffers::createBuffers($this->getPrimaryKey());
        }
        else
        {
            ResPerimeterBuffers::updateBuffers($this->id);
        }

        // Update monitor log and notices zip code entries when a perimeter or threat is detected

        if ($this->perimeter_location_id || $this->threat_location_id)
        {
            $zipcodes = GIS::getPerimeterZipcodes(null, $this->id);
            $zipcodeNumbers = array_map(function($data) { return $data['zipcode']; }, $zipcodes);
            $zipcodeNumbers = join(', ', $zipcodeNumbers);

            $command = Yii::app()->db->createCommand();

            $command->setText("UPDATE res_monitor_log SET Zip_Codes = '{$zipcodeNumbers}' WHERE Perimeter_ID = {$this->id}")->execute();
            $command->setText("UPDATE res_notice SET zip_codes = '{$zipcodeNumbers}' WHERE perimeter_id = {$this->id}")->execute();

            // Updating monitor log triggered data

            $monitorLogs = ResMonitorLog::model()->findAll(array(
                'select' => 'Zip_Codes',
                'with' => array(
                    'resMonitorTriggered'
                ),
                'condition' => 'Perimeter_ID = :perimeter_id',
                'params' => array(':perimeter_id' => $this->id)
            ));

            // This logic is shared with ResMonitorLog->runAnalysis()

            foreach ($monitorLogs as $monitorLog)
            {
                if (isset($monitorLog->resMonitorTriggered))
                {
                    foreach ($monitorLog->resMonitorTriggered as $monitorTriggered)
                    {
                        $unmatched = Helper::getUnmatchedForZipCodes(explode(', ', $monitorLog->Zip_Codes), $monitorTriggered->client_id);

                        $monitorTriggered->unmatched_enrolled = 0;
                        $monitorTriggered->unmatched_not_enrolled = 0;

                        foreach ($unmatched as $policy)
                        {
                            if ($policy['response_status'] === 'enrolled')
                                $monitorTriggered->unmatched_enrolled += $policy['count'];
                            if ($policy['response_status'] !== 'enrolled')
                                $monitorTriggered->unmatched_not_enrolled += $policy['count'];
                        }

                        $unmatched = $monitorTriggered->unmatched_enrolled + $monitorTriggered->unmatched_not_enrolled;

                        $monitorTriggered->unmatched = $unmatched;

                        $monitorTriggered->save();
                    }
                }
            }
        }

        return parent::afterSave();
    }

    protected function afterFind()
    {
        if ($this->resFireName)
        {
            $this->fire_name = $this->resFireName->Name;
        }

        return parent::afterFind();
    }

    //----------------------------------------------------- General Functions -------------------------------------------------------------

    /**
     * Receives an CUploadedFile instance and converts the kml/kmz to WKT
     * @param CUploadedFile $uploadedFile
     * @return string
     */
    public function getWKTFromUpload($uploadedFile)
    {
        $wkt = null;

        if ($uploadedFile instanceof CUploadedFile)
        {
            // If kml, get WKT directly from temp file
            if ($uploadedFile->extensionName === 'kml')
            {
                $wkt = GIS::convertKmlToWkt(file_get_contents($uploadedFile->tempName));
            }

            // If kmz, unpack it, get the WKT, and clean up
            if ($uploadedFile->extensionName === 'kmz')
            {
                $tempDir = Yii::getPathOfAlias('webroot.tmp');
                $kmzTempFile = $tempDir . DIRECTORY_SEPARATOR . $uploadedFile->name;
                move_uploaded_file($uploadedFile->tempName, $kmzTempFile);
                $fileData = file_get_contents("zip://$kmzTempFile#doc.kml");
                $kmlTempFile = $tempDir . DIRECTORY_SEPARATOR . current(explode('.',$uploadedFile->name)) . '.kml';
                file_put_contents($kmlTempFile, $fileData);
                $wkt = GIS::convertKmlToWkt(file_get_contents($kmlTempFile));
                if (file_exists($kmzTempFile)) unlink($kmzTempFile);
                if (file_exists($kmlTempFile)) unlink($kmlTempFile);
            }
        }

        return $wkt;
    }

    /**
     * Downloads a kml of the perimeter
     */
    public function downloadPerimeterKML()
    {
        Assets::registerGeoPHP();
        $perimeterGeom = geoPHP::load($this->wktPerimeter, 'wkt');
        $perimeterKML = str_replace('LineString','LinearRing',$perimeterGeom->out('kml'));
        $perimeterStyle = ($perimeterGeom->geometryType() == 'Point') ? 'perimeterPointStyle' : 'perimeterStyle';

        $kml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
        <Document>
            <name>{$this->resFireName->Name}</name>
            <!-- Perimeter -->
            <Placemark id=\"perimeter\">
                <name>{$this->resFireName->Name} Perimeter</name>
                <styleUrl>$perimeterStyle</styleUrl>
                <MultiGeometry>
                    $perimeterKML
                </MultiGeometry>
            </Placemark>
            <!-- Perimeter Style -->";

        if ($perimeterStyle === 'perimeterStyle')
        {
            $kml .= '
            <Style id="perimeterStyle">
                <LabelStyle>
                    <color>00000000</color>
                    <scale>0</scale>
                </LabelStyle>
                <LineStyle>
                    <color>b26e6e6e</color>
                </LineStyle>
                <PolyStyle>
                    <color>b20000ff</color>
                </PolyStyle>
            </Style>';
        }
        else
        {
            $kml .= '
                <Style id="perimeterPointStyle">
                    <IconStyle>
                        <Icon>
                            <href>' . Yii::app()->getBaseUrl(true) . '/images/fire-icon.png</href>
                        </Icon>
                    </IconStyle>
                </Style>';
        }

        $kml .= '
            </Document>
            </kml>';

        Yii::app()->request->sendFile($this->resFireName->Name . '.kml', $kml, 'application/vnd.google-earth.kml+xml', false);
        Yii::app()->end();
    }

    /**
     * Downloads a kml of the threat
     */
    public function downloadThreatKML()
    {
        Assets::registerGeoPHP();
        $threatGeom = geoPHP::load($this->wktThreat, 'wkt');

        $kml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://www.opengis.net/kml/2.2\" xmlns:gx=\"http://www.google.com/kml/ext/2.2\" xmlns:kml=\"http://www.opengis.net/kml/2.2\" xmlns:atom=\"http://www.w3.org/2005/Atom\">
<Document>
    <name>{$this->resFireName->Name} Threat</name>
    <!-- Perimeter -->
    <Placemark id=\"perimeter\">
        <name>{$this->resFireName->Name} Threat</name>
        <styleUrl>#threatStyle</styleUrl>
        <MultiGeometry>
            {$threatGeom->out('kml')}
        </MultiGeometry>
    </Placemark>
    <!-- Threat Style -->
    <Style id=\"threatStyle\">
        <LabelStyle>
            <color>00000000</color>
            <scale>0</scale>
        </LabelStyle>
        <PolyStyle>
            <color>7f00ffff</color>
            <outline>0</outline>
        </PolyStyle>
    </Style>
</Document>
</kml>";

        Yii::app()->request->sendFile($this->resFireName->Name . ' Threat.kml', $kml, 'application/vnd.google-earth.kml+xml', false);
        Yii::app()->end();
    }

    public function downloadMonitoredFireKMLUpdate()
    {
        Assets::registerGeoPHP();

        $sql = '
            SELECT
                n.Name name,
                loc.geog.STAsText() geog,
                l.monitored_date
            FROM res_monitor_log l
                INNER JOIN res_perimeters p ON l.Perimeter_ID = p.id
                INNER JOIN location loc ON p.perimeter_location_id = loc.id
                INNER JOIN res_fire_name n ON p.fire_id = n.Fire_ID
            WHERE l.monitored_date > DATEADD(DAY,-3,GETDATE())
            ORDER BY l.monitored_date DESC
        ';

        $results = Yii::app()->db->createCommand($sql)->queryAll();

        if ($results)
        {
            $monitoredFiresArray = array();

            foreach ($results as $result)
            {
                $monitoredFiresArray[date('Y-m-d', strtotime($result['monitored_date']))][] = $result;
            }

            try
            {
                $perimeterPlacemarkFolders = '';
                $i = 1;

                foreach ($monitoredFiresArray as $monitoredDate => $monitoredArray)
                {
                    $perimeterPlacemarks = '';

                    foreach ($monitoredArray as $monitoredEntry)
                    {
                        $perimeterGeom = geoPHP::load($monitoredEntry['geog'], 'wkt');
                        $perimeterKML = str_replace('LineString','LinearRing',$perimeterGeom->out('kml'));
                        $perimeterStyle = ($perimeterGeom->geometryType() == 'Point') ? 'perimeterPointStyle' : 'perimeterStyle';

                        $perimeterPlacemarks .= "<Placemark>
                        <name><![CDATA[{$monitoredEntry['name']}]]></name>
                        <Snippet maxLines=\"1\">" . date('H:i', strtotime($monitoredEntry['monitored_date'])) . "</Snippet>
                        <visibility>" . ($i > 1 ? '0' : '1') . "</visibility>
                        <styleUrl>#$perimeterStyle</styleUrl>
                            <MultiGeometry>
                                $perimeterKML
                            </MultiGeometry>
                        </Placemark>";
                    }

                    $perimeterPlacemarkFolders .= "<Folder>
                        <name><![CDATA[$monitoredDate]]></name>
                        <open>0</open>
                        $perimeterPlacemarks
                    </Folder>";

                    $i++;
                }

                $kml = '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://www.opengis.net/kml/2.2">
                    <Document>
                        <Style id="perimeterStyle">
                            <LabelStyle>
                                <color>00000000</color>
                                <scale>0</scale>
                            </LabelStyle>
                            <LineStyle>
                                <color>b26e6e6e</color>
                            </LineStyle>
                            <PolyStyle>
                                <color>b20000ff</color>
                            </PolyStyle>
                        </Style>
                        <Style id="perimeterPointStyle">
                            <IconStyle>
                                <Icon>
                                    <href>' . Yii::app()->getBaseUrl(true). '/images/fire-icon.png</href>
                                </Icon>
                            </IconStyle>
                        </Style>

                        <!-- Perimeters --> ' .
                        $perimeterPlacemarkFolders . '
                    </Document>
                </kml>';

                header('Content-Type: application/vnd.google-earth.kml+xml');
                print $kml;
                exit();
            }
            catch (Exception $e)
            {
                header('Content-Type: application/vnd.google-earth.kml+xml');
                print '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://www.opengis.net/kml/2.2">
                <Document>
                    <Folder>
                        <name>' . $e->getMessage() . '</name>
                    </Folder>
                </Document>
                </kml>';
                exit();
            }
        }

        header('Content-Type: application/vnd.google-earth.kml+xml');
        print '<?xml version="1.0" encoding="UTF-8"?>
        <kml xmlns="http://www.opengis.net/kml/2.2">
        <Document>
            <Folder>
                <name>No fires where found</name>
            </Folder>
        </Document>
        </kml>';
        exit();
    }

    public static function getPerimeterGeoJson($perimeterID)
    {
        $model = ResPerimeters::model()->findByPk($perimeterID);

        if (!empty($model))
        {
            $wkt = Yii::app()->db->createCommand('SELECT geog.STAsText() FROM location WHERE id = :id')->queryScalar(array(':id' => $model->perimeter_location_id));

            $feature_collection = array(
                'type' => 'FeatureCollection',
                'features' => array()
            );

            $feature_collection['features'][] = array(
                'type' => 'Feature',
                'geometry' => json_decode(GIS::convertWktToGeoJson($wkt))
            );

            return $feature_collection;
        }

        return null;
    }

    public static function getPerimeterGeoJsonBuffer($perimeterID, $forthRing = null)
    {
        $buffers = GIS::getPerimeterBuffers($perimeterID, $forthRing);

        $feature_collection = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );

        foreach ($buffers as $key => $value)
        {
            $feature_collection['features'][] = array(
                'type' => 'Feature',
                'geometry' => json_decode(GIS::convertWktToGeoJson($value)),
                'properties' => array(
                    'distance' => $key
                )
            );
        }

        return $feature_collection;
    }

    public static function getThreatGeoJson($perimeterID)
    {
        $result = GIS::erasePerimeterFromThreat($perimeterID);

        $feature_collection = array(
            'type' => 'FeatureCollection',
            'features' => array()
        );

        if (!empty($result))
        {
            $feature_collection['features'][] = array(
                'type' => 'Feature',
                'geometry' => json_decode(GIS::convertWktToGeoJson($result), true)
            );
        }

        return $feature_collection;
    }

    /**
     * Get all perimeter entries for a fire that have a populated threat
     * @param integer $fireID
     * @return ResPerimeters[]
     */
    public static function getThreatsForFireID($fireID)
    {
        return ResPerimeters::model()->findAll(array(
            'alias' => 'p',
            'select' => array(
                'p.threat_location_id',
                'p.fire_id',
                'p.date_created',
                'p.date_updated'
            ),
            'condition' => 'p.fire_id = :fire_id AND p.threat_location_id IS NOT NULL',
            'params' => array(':fire_id' => $fireID),
            'order' => 'p.id DESC',
            'with' => array(
                'resFireName' => array(
                    'select' => array('Fire_ID', 'Name')
                )
            )
        ));
    }

    /**
     * Determine if a threat exists for a given perimeter ID
     * @param integer $perimeterID
     * @return boolean Is this perimeter associated with a threat?
     */
    public static function hasThreatForPerimeter($perimeterID)
    {
        $sql = '
        SELECT
            CASE
                WHEN [threat_location_id] IS NOT NULL THEN 1
                ELSE [threat_location_id]
            END [isThreat]
        FROM [res_perimeters]
        WHERE [id] = :perimeter_id';

        $hasThreat = Yii::app()->db->createCommand($sql)->queryScalar(array(
            ':perimeter_id' => $perimeterID
        ));

        return filter_var($hasThreat, FILTER_VALIDATE_BOOLEAN);
    }
}
