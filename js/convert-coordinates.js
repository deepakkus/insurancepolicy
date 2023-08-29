//Coverts Degrees Minutes Seconds coordinates to Decimal Degrees
function convertDMS()
{
	// Get the latitude that needs to be converted
	var deg = parseFloat(document.getElementById('dDmsLat').value);
	var min = parseFloat(document.getElementById('mDmsLat').value);
	var sec = parseFloat(document.getElementById('sDmsLat').value);
	
	// Get the latitude that needs to be converted
	var degLong = parseFloat(document.getElementById('dDmsLong').value);
	var minLong = parseFloat(document.getElementById('mDmsLong').value);
	var secLong = parseFloat(document.getElementById('sDmsLong').value);

	// Output - Decimal degrees variables
	var coord = 0;
	var coordLong = 0;

	// Math for the conversion
	coord = deg + (min + sec/60)/60;
	coordLong = degLong - (minLong + secLong/60)/60;
	
    // Write the outputs to the form

	document.getElementsByName('smokecheck[lat]')[0].value = Number(coord.toFixed(4));
	document.getElementsByName('smokecheck[long]')[0].value = Number(coordLong.toFixed(4));

	event.preventDefault();
}
	
//Coverts Degrees Decimal Minutes to Decimal Degrees
function convertDDM(event)
{
	// Get the latitude that needs to be converted
	var deg = parseFloat(document.getElementById('d_ddm').value);
	var min = parseFloat(document.getElementById('m_ddm').value);
	
	// Get the longitude that needs to be converted
	var degLong = parseFloat(document.getElementById('dl_ddm').value);
	var minLong = parseFloat(document.getElementById('ml_ddm').value);
	
	// Output - Decimal degrees variables
	var coord = 0;
	var coordLong = 0;

	// Math for the conversion
	coord = deg + min/60;
	coordLong = degLong - minLong/60;
	
	// Write the outputs to the form
	document.getElementsByName('smokecheck[lat]')[0].value = Number(coord.toFixed(4));
	document.getElementsByName('smokecheck[long]')[0].value = Number(coordLong.toFixed(4));

	event.preventDefault();
}