
var FIRESHIELD = FIRESHIELD || {};

FIRESHIELD = function(){
	// constanst
	var STYLE_TAG,
		canFONT_DOWN, 
		canFONT_UP,
		canLINE_DOWN,
		canLINE_UP;

	var CURRENT_FONT_PERCENT = parseInt(window.localStorage.getItem('fontPercent')) || 100;
	var CURRENT_LINE_PERCENT = parseInt(window.localStorage.getItem('linePercent')) || 100;
	//preset innitial view to cut down css repaint
	var CURRENT_CSS = '*{font-size:'+CURRENT_FONT_PERCENT+'% !important; line-height:'+CURRENT_LINE_PERCENT+'% !important;}';
	

	(function setUpFontAdjusting(){
		var head = document.getElementsByTagName('head')[0];
		var style = document.createElement('style');

		style.id = 'fontSizeTag';
		style.type = 'text/css';
		if (style.styleSheet){
		  style.styleSheet.cssText = CURRENT_CSS;
		} else {
		  style.appendChild(document.createTextNode(CURRENT_CSS));
		}

		head.appendChild(style);

		STYLE_TAG = document.getElementById('fontSizeTag');

		canFONT_DOWN = CURRENT_FONT_PERCENT > 95 ? true : false;
		canFONT_UP = CURRENT_FONT_PERCENT < 112 ? true : false;
		canLINE_DOWN = CURRENT_LINE_PERCENT > 92 ? true : false;
		canLINE_UP = CURRENT_LINE_PERCENT < 200 ? true : false;

	}());

	function updateChanges(percent){
		CURRENT_CSS = '*{font-size:'+CURRENT_FONT_PERCENT+'% !important; line-height:'+CURRENT_LINE_PERCENT+'% !important;}';
		STYLE_TAG.innerHTML = CURRENT_CSS;
		window.localStorage.setItem('fontPercent',CURRENT_FONT_PERCENT);
		window.localStorage.setItem('linePercent', CURRENT_LINE_PERCENT);
			
	}

	function increaseFont(){
		if(CURRENT_FONT_PERCENT < 112){
			CURRENT_FONT_PERCENT++;
			updateChanges();
			return 1;
		}
		else{
			canFONT_UP = false;
			return 0;
		}

	}
	function decreaseFont(){
		if(CURRENT_FONT_PERCENT > 95){
			CURRENT_FONT_PERCENT--;
			updateChanges();

			return 1;
		}
		else{
			canFONT_DOWN = false;
			return 0;
		}
	}

	function increaseLineSpace(){
		if(CURRENT_LINE_PERCENT < 200){
			CURRENT_LINE_PERCENT = CURRENT_LINE_PERCENT + 7;
			updateChanges();
			return 1;
		}
		else{
			canLINE_UP = false;
			return 0;
		}

	}

	function decreaseLineSpace(){
		if(CURRENT_LINE_PERCENT > 92){
			CURRENT_LINE_PERCENT = CURRENT_LINE_PERCENT - 7;
			updateChanges();
			return 1;
		}
		else{
			canLINE_DOWN = false;
			return 0;
		}
		
	}

	//resets back to default font/line sizes
	function resetCopy(){
		CURRENT_FONT_PERCENT = 100;
		CURRENT_LINE_PERCENT = 100;
		updateChanges();

		canFONT_DOWN = true;
		canFONT_UP = true;
		canLINE_DOWN = true;
		canLINE_UP = true;
	}

	// returns which parameteres can be udjusted
	function canAdjustText(){

		return JSON.stringify({
			canIncreaseFont : canFONT_UP,
			canDecreaseFont : canFONT_DOWN,
			canIncreaseLineHeight : canLINE_UP,
			canDecreaseLineHeight : canLINE_DOWN
		});

	}

	return {
		increaseFont : increaseFont,
		decreaseFont : decreaseFont,
		increaseLineSpace : increaseLineSpace,
		decreaseLineSpace : decreaseLineSpace,
		resetCopy : resetCopy,
		canAdjustText : canAdjustText
	};


}();



