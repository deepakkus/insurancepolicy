<?php

/* @var $this ResFireScrapeController */
/* @var $model ResFireScrape */

Yii::app()->clientScript->registerCoreScript('jquery');

$formatter = new CFormatter;
$formatter->dateFormat = 'Y-m-d';
$formatter->timeFormat = 'H:i';
$formatter->numberFormat = array('decimals'=>6, 'decimalSeparator'=>'.', 'thousandSeparator'=>'');

?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title>Fire Monitor</title>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/images/firescraper/fire-icon.ico" type="image/x-icon">
        <style type="text/css">
            body {
                margin: 40px 0 100px 0;
                background-color: rgb(255,255,255);
            }
            .center {
                text-align: center;
            }
            .headers {
                font-weight: bold;
                background-color: rgb(208,218,253);
            }
            table {
                background-color: rgb(232,237,255);
                font-size: 14px;
                border-collapse: collapse;
                width: 80%;
                margin: 10px auto 40px auto;
            }
            caption { 
                font-weight: bold;
                font-size: 18px;
            }
            th {
                position: relative;
                padding: 2px 0;
                font-size: 15px;
                background-color: rgb(150,200,253);
                -webkit-box-shadow: 0 2px 2px -1px rgba(0,0,0,0.3);
                   -moz-box-shadow: 0 2px 2px -1px rgba(0,0,0,0.3);
                        box-shadow: 0 2px 2px -1px rgba(0,0,0,0.3);
            }
            td { 
                padding: 2px 10px 2px 10px;
                border-bottom: solid #CCCCCC 1px;
                border-left: solid #CCCCCC 1px;
                border-right: solid #CCCCCC 1px;
            }
			a{
				color: #007FFF;
                text-decoration: none;
			}
            a.absolute {
                position: absolute;
                right: 10px;
                top: 5px;
            }
            a:hover { 
                color: red; 
            }
            #updated {
                position: fixed;
                top: 20px;
                left: 12px;
                z-index: 100;
            }
            #records {
                position: relative;
                bottom: 10px;
                left: 20px;
                width: 300px;
            }
            #records select {
                width: 200px;
                display: inline;
            }
            .viewed {
                text-decoration: line-through;
                color: red;
            }
            iframe {
                width:900px;
                height:800px;
                margin: 0 auto;
                display: block;
            }
        </style>
    </head>
    <body>

        <div id="updated">Updated: <?php echo $dateStamp; ?></div>

        <a class="absolute" target="_blank" href="<?php echo $this->createUrl('/resFireScrape/map'); ?>">Map Here</a>


        <?php
        
        $insertHeaders = function($model)
        {
            echo '<tr><th colspan="100%">' . $model->dispatch . '</th></tr>';
            echo '<tr class="headers">';

            echo '<td>Time</td>';
            echo '<td>' . $model->getAttributeLabel('acres') . '</td>';
            echo '<td>' . $model->getAttributeLabel('fuels') . '</td>';
            echo '<td>' . $model->getAttributeLabel('ic') . '</td>';
            echo '<td>' . $model->getAttributeLabel('inc_num') . '</td>';
            echo '<td>Lat/Lon</td>';
            echo '<td>' . $model->getAttributeLabel('location') . '</td>';
            echo '<td>' . $model->getAttributeLabel('name') . '</td>';
            echo '<td>' . $model->getAttributeLabel('resources') . '</td>';
            echo '<td>' . $model->getAttributeLabel('type') . '</td>';
            echo '<td>' . $model->getAttributeLabel('web_comment') . '</td>';
            echo '<td>' . $model->getAttributeLabel('viewed') . '</td>';

            echo '</tr>';
        };
        
        $insertRow = function($model) use ($formatter)
        {
            echo $model->viewed ? '<tr class="viewed">' : '<tr>';
            
            echo '<td>' . $formatter->formatTime($model->date) . '</td>';
            echo '<td>' . $model->acres . '</td>';
            echo '<td>' . $model->fuels . '</td>';
            echo '<td>' . $model->ic . '</td>';
            echo '<td>' . $model->inc_num . '</td>';
            echo ($model->point) 
                ? '<td><a href = "' . $this->createUrl('resMonitorLog/monitorFire',array('dispatchLat'=>$formatter->formatNumber($model->lat), 'dispatchLong' => $formatter->formatNumber($model->lon))) . '">' . $formatter->formatNumber($model->lat) . ', ' . $formatter->formatNumber($model->lon) . '</a></td>' 
                : '<td></td>';
            echo '<td>' . $model->location . '</td>';
            echo '<td>' . $model->name . '</td>';
            echo '<td>' . $model->resources . '</td>';
            echo '<td>' . $model->type . '</td>';
            echo '<td>' . $model->web_comment . '</td>';
            echo '<td><input type="checkbox" ' . ($model->viewed ? 'checked="checked"' : '') . '></td>';
            
            echo '</tr>';
        };

        if ($models)
        {
            $state = null;
            $dispatch = null;
            $n = 0;
            
            foreach($models as $model)
            {
                // Testing to see if a new state
                
                if ($model->state !== $state)
                {
                    $state = $model->state;
                    
                    if ($n !== 0)
                    {
                        echo '</table>';
                    }
                    
                    echo '<table class="center">';
                    echo '<caption>' . $state . '</caption>';
                    
                    //Every time there's a new state, there will also be a new dispatch / headers
                    
                    $dispatch = $model->dispatch;
                    $insertHeaders($model);
                    $insertRow($model);
                }
                else // Same state ... testing to see if a new dispatch.
                {
                    if ($dispatch !== $model->dispatch)
                    {
                        $dispatch = $model->dispatch;
                        $insertHeaders($model);
                        $insertRow($model);
                    }
                    else  // Same state, same dispatch ... continue adding new trs.
                    {
                        $insertRow($model);
                    }
                }
                $n++;
            }

            echo '</table>';

            echo '
            <div>
                <h3 class="center">
                    Look at <a href="http://www.wildcad.net/WildCADWeb.asp" target="_blank">WildCADWeb</a> for more information.
                </h3>
            </div>';
        }
        else
        {
            echo '
            <div>
                <h3 class="center">
                    No fires were found!<br />Look at <a href="http://www.wildcad.net/WildCADWeb.asp" target="_blank">WildCADWeb</a> or below for more information.
                </h3>
                <iframe src="http://www.wildcad.net/WildCADWeb.asp" frameborder="0"></iframe>
            </div>';
        }

        ?>

        <script type="text/javascript">

            var viewedChecks = document.getElementsByTagName('input');
            for (var i = 0; i < viewedChecks.length; i++) {
                if (viewedChecks[i].type === 'checkbox') {
                    viewedChecks[i].addEventListener('click', function(event) {
                        this.parentNode.parentNode.className = this.checked ? 'viewed' : '';
                        var incNum = this.parentNode.parentNode.childNodes[4].innerText.trim();

                        $.post('<?php echo $this->createUrl('resFireScrape/viewedChecked'); ?>', { incNum: incNum, viewed: this.checked ? 1 : 0 }, function(data) {
                            console.log(data);
                        }, 'json').error(function(jqXHR) {
                            console.log(jqXHR);
                        });

                    });
                }
            }

            setInterval(function() {
                var date = new Date();
                if (date.getMinutes() % 15 === 0) {
                    setTimeout(function() {
                        window.location.reload();
                    }, 60000);
                }
            }, 15000);

        </script>
    </body>
</html>