<?php

/* @var $this RiskModelController */

$this->breadcrumbs=array(
	'WDS Risk Query',
);

?>

<h3>Risk API Test</h3>

<a id="run-risk-test" href="#">Run Risk Test</a>
<div class="test-loading clearfix" style="height: 30px; width: 30px;"></div>
<a id="clear-risk-test" href="#" data-url="<?php echo $this->createUrl($this->route); ?>">Clear</a>

<div class="marginTop20">
    <p>
        <strong><u>Results</u></strong>
    </p>
    <pre id="risk-test-response">
    </pre>
</div>

<script type="text/javascript">

    $riskTestResponse = $("#risk-test-response");
    $riskTestLoading = $('.test-loading');

    function writeToReponse(html) {
        $riskTestResponse.html(html);
    }

    $(function() {

        $("#run-risk-test").click(function() {

            $riskTestLoading.addClass("grid-view-loading");

            $.post($(this).data("url"), "RiskTest", function(response) {
                $riskTestLoading.removeClass("grid-view-loading");
                if (response.data !== undefined && response.success !== undefined) {
                    if (response.success === true) {
                        if (response.data !== "") {
                            writeToReponse(response.data);
                        } else {
                            writeToReponse("Something went wrong!");
                        }
                    }
                } else {
                    writeToReponse("Something went wrong!");
                }
            }, "json").error(function(xhr) {
                $riskTestLoading.removeClass("grid-view-loading");
                writeToReponse("Something went wrong!");
                console.log(xhr);
            });

            return false;
        });

        $("#clear-risk-test").click(function() {
            writeToReponse("");
            return false;
        });

    });

</script>
