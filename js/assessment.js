/********** Condition 1 Start  ************/
var c1_other = $('#c1-other');
var c1_select = $('#c1-select');

c1_select.live('change', function(){
    if($('#c1-select option:selected').text() == 'Other')
        c1_other.show().removeAttr('disabled').val('');
    else 
        c1_other.hide().prop('disabled', true);   
    
    if($('#c1-select option:selected').text() == 'Wood'){
        $('#c1-check').prop('checked', true);
        $('#rec1').parent().show();
        
    }  
    else{
        $('#c1-check').prop('checked', false);
        $('#rec1').parent().hide();
    }
});

/********* Condition 1 End **************/


/********* Condition 2 Start***********/
var c2_other = $('#c2-other');

$('#c2-check').live('click',function() {
    $('#c2-yes').slideToggle();
});


$('#c2-bad-condition-check').live('click',function() {
    $('.c2-condition').toggle();
});

$('#c2-select').live('change', function(){
    if($('#c2-select option:selected').text() == 'Other')
        c2_other.show().removeAttr('disabled').val('');
    else 
        c2_other.hide().prop('disabled', true);  
});

/*********** Condition 2 End************/


/********* Condition 3 Start ***********/
var c3_other = $('#c3-other');
$('#c3-check').live('click',function() {
    $('#c3-yes').slideToggle();
    
    if($('#c3-select option:selected').text() == 'No vent protection exist' || $('#c3-select option:selected').text() == 'Other'){
        $('#rec3-not-covered').show();
        $('#rec3-covered').hide();
    }
    if($('#c3-select option:selected').text() == 'Gaps in screen are too large' || $('#c3-select option:selected').text() == 'Vent screen in poor condition/improperly attached'){
        
        $('#rec3-not-covered').hide();
        $('#rec3-covered').show();
    }
        
    
});


$('#c3-select').live('change', function(){
    if($('#c3-select option:selected').text() == 'Other')
        c3_other.show().removeAttr('disabled').val('');
    else 
        c3_other.hide().prop('disabled', true); 
    
    if($('#c3-select option:selected').text() == 'No vent protection exist' || $('#c3-select option:selected').text() == 'Other'){
        $('#rec3-not-covered').show();
        $('#rec3-covered').hide();
    }
    if($('#c3-select option:selected').text() == 'Gaps in screen are too large' || $('#c3-select option:selected').text() == 'Vent screen in poor condition/improperly attached'){
        
        $('#rec3-not-covered').hide();
        $('#rec3-covered').show();
    }
        
});


/*********** Condition 3 End************/


/********* Condition 4 Start ***********/
var c4_other = $('#c4-other');
$('#c4-check').live('click',function() {
    $('#c4-yes').slideToggle();
});

$('#c4-select').live('change', function(){
    if($('#c4-select option:selected').text() == 'Other')
        c4_other.show().removeAttr('disabled').val('');
    else 
        c4_other.hide().prop('disabled', true);
});

/*********** Condition 4 End************/


/********* Condition 5 Start ***********/
var c5_eave_issue_other = $('#c5-eave-issue-other');
$('#c5-check').live('click',function() {
    $('#c5-yes').slideToggle();
});

var c5_eave_issue = $('#c5-eave-issue');
$('#c5-eaves-select').live('change', function(){
    if($('option:selected', $(this)).text() == 'Eaves are threatened')
        c5_eave_issue.show();
    else 
        c5_eave_issue.hide();
});

$('#c5-eave-issue-select').live('change', function(){
    if($('option:selected', $(this)).text() == 'Other')
        c5_eave_issue_other.show().removeAttr('disabled').val('');
    else 
        c5_eave_issue_other.hide().prop('disabled', true);
});

/*********** Condition 5 End************/

/********* Condition 6 Start ***********/
var c6_other = $('#c6-other');
$('#c6-check').live('click',function() {
    $('#c6-yes').slideToggle();
    
    if($('#c6-select option:selected').text() == 'Vents covered/gaps too large'){
        $('#rec6-not-covered').show();
        $('#rec6-covered').hide();
    }
    if($('#c6-select option:selected').text() == 'Vents not covered' || $('#c6-select option:selected').text() == 'Holes/Gaps'){
        
        $('#rec6-not-covered').hide();
        $('#rec6-covered').show();
    }
});

$('#c6-select').live('change', function(){
    if($('#c6-select option:selected').text() == 'Other')
        c6_other.show().removeAttr('disabled').val('');
    else 
        c6_other.hide().prop('disabled', true); 
    
    if($('#c6-select option:selected').text() == 'Vents covered/gaps too large'){
        $('#rec6-not-covered').show();
        $('#rec6-covered').hide();
    }
    if($('#c6-select option:selected').text() == 'Vents not covered' || $('#c6-select option:selected').text() == 'Holes/Gaps'){
        
        $('#rec6-not-covered').hide();
        $('#rec6-covered').show();
    }
        
});

/*********** Condition 6 End************/


/********** Condition 7 Start  ************/
$('#c7-check').live('click',function() {
    $('#c7-yes').slideToggle();
});

$('#c7-type-select input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Single-page windows are threatened' || $(this).val() == 'Windows are damaged') {
            $('#c7-window-issue').show();
        }
    }
    else{
        if (!($('input[value="Single-page windows are threatened"]').attr("checked") || $('input[value="Windows are damaged"]').attr("checked")))
            $('#c7-window-issue').hide();
    }
});

/********* Condition 7 End **************/

/********** Condition 8 Start  ************/

$('input[name="Siding[threat]"]').click(function(){
    var c11_siding_veg_threat = $('#c11-siding-veg-threat');
    if($('input[name="Siding[threat]"]:checked').val() == 'Threatened by vegetation' || $('input[name="Siding[threat]"]:checked').val() == 'Threatened by combustible materials and vegetation')
        c11_siding_veg_threat.show();
    else
        c11_siding_veg_threat.hide();
});

/********* Condition 8 End **************/



/********** Condition 9 Start  ************/
$('#c9-check').live('click',function() {
    $('#c9-yes').slideToggle();
});

$('input[name="HomeElevated[elevated]"]').click(function(){
    var c9_explain = $('#c9-explain');
    if($('input[name="HomeElevated[elevated]"]:checked').val() == 'Threatened - Elevated Home/Portions')
        c9_explain.show();
    else
        c9_explain.hide();
});



/********* Condition 9 End **************/


/********** Condition 10 Start  ************/

var c10_check = $('#c10-check');
var c10_deck = $('#c10-deck');
var c10_fence = $('#c10-fence');
var c10_patio = $('#c10-patio');

c10_check.live('click',function() {
    $('#c10-yes').slideToggle();
    if($(this).attr("checked")) {
        
    }
    else {
       
    }
});

var c11_check = $("#c11-check");

$('#c10-attachment-select input').live('click', function(){
    
    if($(this).siblings('input:checked').length > 0)
        c11_check.attr("checked", true);
    else
        c11_check.attr("checked", false);
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Deck')
            c10_deck.show();
        
        if ($(this).val() == 'Fence')
            c10_fence.show();
        
        if ($(this).val() == 'Patio awning')
            c10_patio.show();
        
        if ($(this).val() == 'Patio awning' || $(this).val() == 'Fence' || $(this).val() == 'Deck')
            c11_check.attr("checked", true);
    }
    else{
        if ($(this).val() == 'Deck') {
            c10_deck.hide();
        }
        if ($(this).val() == 'Fence') {
            c10_fence.hide();
        }
        if ($(this).val() == 'Patio awning') {
            c10_patio.hide();
        }
    }
});

var c11_deck_threat = $('#c11-deck-threat');
var c12_deck_threat = $('#c12-deck-threat');
$('#c10-deck-details input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Threatened by combustible vegetation') {
            c11_deck_threat.show();
        }
        if ($(this).val() == 'Threatened by combustible materials') {
            c12_deck_threat.show();
        }
    }
    else{
        if ($(this).val() == 'Threatened by combustible vegetation') {
            c11_deck_threat.hide();
        }
        if ($(this).val() == 'Threatened by combustible materials') {
            c12_deck_threat.hide();
        }
    }
});

var c11_fence_threat = $('#c11-fence-threat');
var c12_fence_threat = $('#c12-fence-threat');
$('#c10-fence-details input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Threatened by combustible vegetation') {
            c11_fence_threat.show();
        }
        if ($(this).val() == 'Threatened by combustible materials') {
            c12_fence_threat.show();
        }
    }
    else{
        if ($(this).val() == 'Threatened by combustible vegetation') {
            c11_fence_threat.hide();
        }
        if ($(this).val() == 'Threatened by combustible materials') {
            c12_fence_threat.hide();
        }
    }
});

var c11_patio_threat = $('#c11-patio-threat');
var c12_patio_threat = $('#c12-patio-threat');
$('#c10-patio-details input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Threatened by combustible vegetation') {
            c11_patio_threat.show();
        }
        if ($(this).val() == 'Threatened by combustible materials') {
            c12_patio_threat.show();
        }
    }
    else{
        if ($(this).val() == 'Threatened by combustible vegetation') {
            c11_patio_threat.hide();
        }
        if ($(this).val() == 'Threatened by combustible materials') {
            c12_patio_threat.hide();
        }
    }
});


$('#c10-radio-good').live('click',function() {
    $('#rec10-good').show();
    $('#rec10-threat').hide();
});

$('#c10-radio-threat').live('click',function() {
   $('#rec10-good').hide();
   $('#rec10-threat').show();
});


/********* Condition 10 End **************/


/********* Condition 11 Start ***********/
$('#c11-check').live('click',function() {
    $('#c11-yes').slideToggle();
});
/*********** Condition 11 End************/


/********* Condition 12 Start ***********/
$('#c12-check').live('click',function() {
    
    $('#c12-yes').slideToggle();
});
/*********** Condition 12 End************/


/********* Condition 13 Start ***********/
var c13_garage_location = $('#c13-garage-location');
var c13_shed_location = $('#c13-shed-location');
var c13_carport_location = $('#c13-carport-location');
var c13_other_location = $('#c13-other-location');

$('#c13-check').live('click',function() {
    $('#c13-yes').slideToggle();
});

$('#c13-structure-select input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Garage(s)')
            c13_garage_location.show();
        
        else if ($(this).val() == 'Shed(s)')
            c13_shed_location.show();
        
        else if ($(this).val() == 'Carport(s)')
            c13_carport_location.show();
        
        else
            c13_other_location.show();
    }
    else{
        if ($(this).val() == 'Garage(s)') 
            c13_garage_location.hide();
        
        else if ($(this).val() == 'Shed(s)') 
            c13_shed_location.hide();
        
        else if ($(this).val() == 'Carport(s)')
            c13_carport_location.hide();
        
        else
            c13_other_location.hide();
        
    }
});

/*********** Condition 13 End************/



/********* Condition 14 Start ***********/
var c14_yes = $('#c14-yes');
var c14_no_issue = $('#c14-no-issue');
var c14_radio_1 = $("#c14-radio-1");
var c14_radio_2 = $("#c14-radio-2");

var c13_grass_location = $('#c14-grass-location');
var c14_brush_location = $('#c14-brush-location');
var c14_trees_location = $('#c14-trees-location');
var c14_ladder_fuels_location = $('#c14-ladder-fuels-location');
var c14_other_location = $('#c14-other-location');

$('#c14-type-select input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Grass')
            c13_grass_location.show();
        
        else if ($(this).val() == 'Brush')
            c14_brush_location.show();
        
        else if ($(this).val() == 'Trees')
            c14_trees_location.show();
        
        else if ($(this).val() == 'Ladder fuels')
            c14_ladder_fuels_location.show();
        
        else
            c14_other_location.show();
    }
    else{
        if ($(this).val() == 'Grass') 
            c13_grass_location.hide();
        
        else if ($(this).val() == 'Brush') 
            c14_brush_location.hide();
        
        else if ($(this).val() == 'Trees')
            c14_trees_location.hide();
        
        else if ($(this).val() == 'Ladder fuels')
            c14_ladder_fuels_location.hide();
        
        else
            c14_other_location.hide();
    }
});

$('#c14-check').live('click',function() {
    var this_condition = $(this).parents('.condition');

    if($(this).attr("checked")) {
        c14_yes.show().children().show();
        c14_no_issue.hide();
    }
    else{
        c14_no_issue.show();
        if (c14_radio_2.is(':checked')){
            c14_yes.show();
        }
        else{
            c14_yes.hide();
        }
        
        if (c14_radio_1.is(':checked')){
            c14_yes.hide();
        }
    }

});

c14_radio_1.live('click',function() {
    c14_yes.hide();
});

c14_radio_2.live('click',function() {
    c14_yes.show().children().show();
});


/*********** Condition 14 End************/



/********* Condition 15 Start ***********/
var c15_garage_location = $('#c15-garage-location');
var c15_shed_location = $('#c15-shed-location');
var c15_carport_location = $('#c15-carport-location');
var c15_other_location = $('#c15-other-location');

$('#c15-check').live('click',function() {
    $('#c15-yes').slideToggle();
});

$('#c15-structure-select input').live('click', function(){
    
    if($(this).attr("checked")) {
        if ($(this).val() == 'Garage(s)')
            c15_garage_location.show();
        
        else if ($(this).val() == 'Shed(s)')
            c15_shed_location.show();
        
        else if ($(this).val() == 'Carport(s)')
            c15_carport_location.show();
        
        else
            c15_other_location.show();
    }
    else{
        if ($(this).val() == 'Garage(s)') 
            c15_garage_location.hide();
        
        else if ($(this).val() == 'Shed(s)') 
            c15_shed_location.hide();
        
        else if ($(this).val() == 'Carport(s)')
            c15_carport_location.hide();
        
        else
            c15_other_location.hide();
        
    }
});

/*********** Condition 15 End************/




/********* Condition A Start ***********/
$('#cA-check').live('click',function() {
    $('#cA1, #cA2, #cA3').toggle();
});

/*********** Condition A End************/


/********* Condition A1 Start ***********/
$('#cA1-check').live('click',function() {
    $('#cA1-location, #cA1-photo').slideToggle();
});

/*********** Condition A1 End************/


/********* Condition A2 Start ***********/
$('#cA2-check').live('click',function() {
    $('#cA2-location, #cA2-photo').slideToggle();
});

/*********** Condition A2 End************/


/********* Condition A3 Start ***********/

$('#cA3-check').live('click',function() {
    var check = $(this);
    if(!$(this).attr("checked")){
        if(!confirm("Are you sure you want to remove wind as a condition with potential to influence fire behavior?"))
        {
            $(this).prop("checked", true);
        }
    }
});

/*********** Condition A3 End************/


/********* Condition B Start ***********/
$('#cB-check').live('click',function() {
    var check = $(this);
    if(!$(this).attr("checked")){
        if(!confirm("Are you sure unmanaged wildland fuel does not have potential to influence fire behavior in the area?"))
        {
            $(this).prop("checked", true);
        }
    }
});

/*********** Condition B End************/


/********* Condition C Start ***********/
$('#cC-check').live('click',function() {
    $('#cC-photo').slideToggle();
});

/*********** Condition B End************/


/********* Condition AA Start ***********/
$('#cAA-check').live('click',function() {
    $('#cAA1, #cAA2, #cAA3').toggle();
});

/*********** Condition AA End************/


/********* Condition AA1 Start ***********/
$('#cAA1-check').live('click',function() {
    $('#cAA1-yes').toggle();
});

/*********** Condition AA1 End************/


/********* Condition AA2 Start ***********/
$('#cAA2-check').live('click',function() {
    $('#cAA2-yes, #cAA2-photo').toggle();
});

/*********** Condition AA2 End************/


/********* Condition AA3 Start ***********/
$('#cAA3-check').live('click',function() {
    $('#cAA3-yes, #cAA3-photo').toggle();
});

/*********** Condition AA2 End************/

$('.clear-field').focus(function(){
    $(this).select();
});




/*********** Recommendations *************/

$('div.rec-button').live('click',function(){
    $(this).siblings('div.rec-wrapper').slideToggle();
    $(this).text($(this).text() == 'Hide Recommendation' ? 'Show Recommendation' : 'Hide Recommendation');
    return false;
});



$('.condition-check').click(function(){
    
    if($(this).attr("checked")) {
        $(this).parent().siblings('.rec-ex-wrapper').slideDown();
    }
    else{
        $(this).parent().siblings('.rec-ex-wrapper').slideUp();
    }
});



$('.file-browser-button').click(function() {
    var browser = $(this).siblings('.file-browser');
    if(browser.is(':visible')) {
        $(this).siblings('.file-browser').slideUp();
    }
    else {
        $(this).siblings('.file-browser').slideDown();
    }
});


$(".rec-multiselect").bind("multiselectopen", function(event, ui){
    $('.tint').show();
});


$(".rec-multiselect").bind("multiselectclose", function(event, ui){
    var rec_text = "";
    if($(this).val()){
        $.each($(this).val(), function(key, value){
            rec_text += value;
        });
    }
    $(this).siblings('.custom-rec-textarea').val(rec_text);
    $('.tint').hide();
});



$(".ex-multiselect").bind("multiselectopen", function(event, ui){
    $('.tint').show();
});


$(".ex-multiselect").bind("multiselectclose", function(event, ui){
    var ex_text = "";
    if($(this).val()){
        $.each($(this).val(), function(key, value){
            ex_text += value;
        });
    }
    $(this).siblings('.custom-ex-textarea').val(ex_text);
    $('.tint').hide();
});


$(".rec-multiselect").bind("multiselectopen", function(event, ui){
    $('.tint').show();
});



