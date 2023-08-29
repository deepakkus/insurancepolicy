<?php
class ResFireScrapeCommand extends CConsoleCommand
{
    private $dbCommand;
    private $tableName = 'res_fire_scrape';
    private $attrMap = array(
        'Acres' => 'acres',
        'Date' => 'date',
        'Fuels' => 'fuels',
        'IC' => 'ic',
        'Inc #' => 'inc_num',
        'Location' => 'location',
        'Name' => 'name',
        'Resources' => 'resources',
        'Type' => 'type',
        'WebComment' => 'web_comment',
        'Lat/Lon' => 'point'
    );

	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print '-----STARTING COMMAND--------' . PHP_EOL;

        $time_start = microtime(true);

        $this->dbCommand = Yii::app()->db->createCommand();
        $this->dbCommand->setText('DELETE FROM res_fire_scrape WHERE id IS NOT NULL');
        $this->dbCommand->execute();

        $urls = $this->getUrls();

        foreach ($urls as $url)
        {
            $this->scrapeUrl($url);
        }

        print '-----DONE WITH COMMAND-------' . PHP_EOL;
        print PHP_EOL;
        print 'Finished in ' . round(microtime(true) - $time_start, 2) . ' secs' . PHP_EOL;
    }

	/**
     * Creates an array of URLs to scrape.
     * @return array
     */
    private function getUrls()
    {
        $html = CurlRequest::getRequest('http://www.wildcad.net/WildCADWeb.asp');

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $urls = array();
        foreach ($dom->getElementsByTagName('a') as $link)
        {
            $urls[] = 'http://www.wildcad.net/WC' . $link->nodeValue . 'recent.htm';
        }

        return $urls;
    }

    /**
     * Scrape HTML of WildCAD page and insert into database
     * @param string $url - Url from WildCAD to be scraped
     */
    private function scrapeUrl($url)
    {
        $html = CurlRequest::getRequest($url);

        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $titleString = '';
        $list = $dom->getElementsByTagName("title");
        if ($list->length > 0)
        {
            $titleString = $list->item(0)->nodeValue;
        }

        $state = substr($titleString, 2, 2);
        $title = substr($titleString, 2);
        $attrs = array();
        $header = array();
        $attr = array();
        $n = 0;

        foreach ($dom->getElementsByTagName('tr') as $tr)
        {
            foreach ($tr->getElementsByTagName('td') as $td)
            {
                switch ($n) {
                    case 0: break;                              // Title
                    case 1: $header[] = $td->nodeValue; break;  // Header
                    default: $attr[] = $td->nodeValue; break;   // Data
                }
            }

            // Only executes if the $attr array isn't empty (after header array is populated).
            if (!empty($attr))
            {
                date_default_timezone_set('America/Denver');
                $date = preg_quote(date('m/d/Y'));
                $typeIndex = array_search('Type', $header);
                $dateIndex = array_search('Date', $header);

                // testing line items for today's date and the word "fire".
                if (isset($attr[$dateIndex], $attr[$typeIndex]) &&
                    preg_match('#' . $date . '#', $attr[$dateIndex]) &&
                    preg_match( '/fire/i', $attr[$typeIndex]))
                {
                    // Replaces Acres with 0 if it is "." or "*******".
                    $acresIndex = array_search('Acres', $header);
                    if ($attr[$acresIndex] === '*******' || $attr[$acresIndex] === '.')
                    {
                        $attr[$acresIndex] = 0;
                    }

                    // Replace whitespace with an underscore
                    $incNumIndex = array_search('Inc #', $header);
                    $attr[$incNumIndex] = preg_replace('/\s+/', '_', $attr[$incNumIndex]);

                    $coordsIndex = array_search('Lat/Lon', $header);
                    if ($coordsIndex)
                    {
                        $attr[$coordsIndex] = $this->convertCoordinates($attr[$coordsIndex]);
                    }

                    // skipping if header and <td> length don't match up.
                    // (sometimes WildCAD doesn't finish it's last <tr></tr> ...)
                    if (count($header) !== count($attr))
                    {
                        continue;
                    }

                    $attrs[] = array_combine($header, $attr);
                    $attr = array();
                }
                else
                {
                    $attr = array();
                    continue;
                }
            }
            $n++;
        }

        if (count($attrs))
        {
            $this->sqlInsert($state, $title, $header, $attrs);
            $this->sqlInsertViewed($attrs);
            print 'inserted for dispatch ' . $title . PHP_EOL;
        }
    }

    /**
     * Convert coordinates of formats "Degrees Minutes Seconds" and "Decimal Minutes" to decimal degrees
     * @param string $coords - string of coordinates from WildCAD
     */
    private function convertCoordinates($coords)
    {
        try
        {
            preg_match('/(\d{1,3}\.\d+?)[^\d]+?(\d{1,3}\.\d+)/', $coords, $decDegrees);
            preg_match('/(\d{1,3}).+?(\d{1,3}\.\d+?)[^\d]+?(\d{1,3}).+?(\d{1,3}\.\d+)/', $coords, $decMinutes);

            if ($decDegrees)
            {
                $decDegrees = $decDegrees[1] . ', -' . $decDegrees[2];
                return $decDegrees;
            }

            if ($decMinutes)
            {
                $decMinutes = strval(floatval($decMinutes[1]) + (floatval($decMinutes[2]) / 60.0)) . ', -' . strval(floatval($decMinutes[3]) + (floatval($decMinutes[4]) / 60.0));
                return $decMinutes;
            }

        }
        catch (Exception $e)
        {
            echo 'Caught conversion exception: ' . $e->getMessage() . PHP_EOL;
            return $coords;
        }
    }

    /**
     * Insert scraped data into table
     * @param string $state - state of dispatch
     * @param string $title - name of dispatch
     * @param array $header - table header row
     * @param array $attrs - array of data rows
     */
    private function sqlInsert($state, $title, $header, $attrs)
    {
        $insertArray = array();

        foreach ($attrs as $fire)
        {
            foreach ($fire as $key => $value)
            {
                $modelAttr = isset($this->attrMap[$key]) ? $this->attrMap[$key] : null;

                // Only save this value if it is a pre-determined database value
                if ($modelAttr !== null)
                {
                    $insertArray[$modelAttr] = $value;
                }
            }

            if (isset($insertArray['date']))
            {
                $insertArray['date'] = date('Y-m-d H:i', strtotime($insertArray['date']));
            }

            if (isset($insertArray['point']))
            {
                $coordsArray = explode(', ', $insertArray['point']);
                $longitude = $coordsArray[1];
                $latitude = $coordsArray[0];
                $insertArray['point'] = 'POINT (' . $longitude . ' ' . $latitude . ')';

                $latitude = (double)$latitude;
                $longitude = (double)$longitude;

                // Checking for valid coordinates
                // If coordinates are not valid, then skip this entry
                if (($latitude < -90.0   || $latitude > 90.0  ) ||
                    ($longitude < -180.0 || $longitude > 180.0))
                {
                    continue;
                }
            }

            $insertArray['dispatch'] = $title;
            $insertArray['state'] = $state;
            $insertArray['date_created'] = date('Y-m-d H:i');

            $this->dbCommand->insert($this->tableName, $insertArray);
        }
    }

    /**
     * Insert Inc_Nums into Viewed table if they don't already exist
     * @param array $attrs - array of data rows
     */
    private function sqlInsertViewed($attrs)
    {
        $incNums = @array_map(function($fire) { return $fire['Inc #']; }, $attrs);

        if (!in_array(NULL, $incNums))
        {
            foreach ($incNums as $inc_num)
            {
                $this->dbCommand->setText('
                DECLARE @inc_num [varchar](255) = :inc_num

                IF NOT EXISTS(SELECT * FROM res_fire_scrape_viewed WHERE inc_num = @inc_num)
                BEGIN
                INSERT INTO res_fire_scrape_viewed (inc_num, viewed) VALUES (@inc_num, 0)
                END');

                $this->dbCommand->bindParam(':inc_num', $inc_num, PDO::PARAM_STR);
                $this->dbCommand->execute();
            }
        }
    }
}