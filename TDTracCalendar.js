// TDTracCalendar.js
//
// Author: JTSage (jtsage@gmail.com)
// NOTICE: You may use this code for any purpose, in any form, for 
// whatever reason.  You need neither credit me or leave this notice 
// intact.  You may sell products based on it, and include it in any
// programs / scripts / projects you distribute.
//
// ===================================================================

var LONG_MONTH    = new Array('January','February','March','April','May','June','July','August','September','October','November','December');

var NOW = new Date();
var THIS_MONTH = NOW.getMonth();
var THIS_DAY   = NOW.getDate();
var THIS_YEAR  = NOW.getFullYear();

// WORKHORSE - MAKE AND SHOW THE CALENDAR
function tdt_show_calendar(cMonth,cYear,cDiv,cForm) {

	var CAL_DAYS = tdt_daysInMonth(cMonth,cYear);
	var CAL_WIRK = new Date();
	CAL_WIRK.setMonth(cMonth);
	CAL_WIRK.setYear(cYear);
	CAL_WIRK.setDate(1);
	var CAL_ITTR = 1; 
	var CAL_NEW_ROW = 0;
	var CAL_HIGH = 0;

	if ( cMonth == 0 ) { var LAST_MONTH = 11; var LAST_YEAR = cYear - 1; 
	} else { var LAST_MONTH = cMonth - 1; var LAST_YEAR = cYear; }
	if ( cMonth == 11 ) { var NEXT_MONTH = 0; var NEXT_YEAR = cYear + 1; 
	} else { var NEXT_MONTH = cMonth + 1; var NEXT_YEAR = cYear; }

	if ( cMonth == THIS_MONTH && cYear == THIS_YEAR ) { CAL_HIGH = 1; }

	var outHtml = "<table class=\"cal\"><tr>";
	outHtml += "<th><a href=\"javascript:tdt_show_calendar(" + LAST_MONTH + "," + LAST_YEAR + ",'" + cDiv + "','" + cForm + "')\">&lt;&lt;</a></th>";
	outHtml += "<th colspan=5>" + LONG_MONTH[cMonth] + " " + cYear + "</th>";
	outHtml += "<th><a href=\"javascript:tdt_show_calendar(" + NEXT_MONTH + "," + NEXT_YEAR + ",'" + cDiv + "','" + cForm + "')\">&gt;&gt;</a></th></tr>";

	var CAL_DAY = CAL_WIRK.getDay();
	outHtml += "<tr class=\"days\"><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>";
	outHtml += "<tr class=\"dates\">";
	
	var CAL_ROWONE = 0;
	while ( CAL_ROWONE < CAL_DAY ) {
		outHtml += "<td></td>";
		CAL_ROWONE++;
	}
	while ( CAL_ITTR <= CAL_DAYS ) {
		var CAL_SKIP_WKND = 0;
		if ( CAL_DAY == 7 ) { CAL_NEW_ROW = 1; CAL_DAY = 0; }
		if ( CAL_NEW_ROW == 1 ) { outHtml += "</tr><tr class=\"dates\">"; CAL_NEW_ROW = 0; }
		if ( CAL_HIGH == 1 && CAL_ITTR == THIS_DAY ) { outHtml += "<td style=\"background-color: #FFDDDD\">"; CAL_SKIP_WKND = 1; }
		if ( ( CAL_DAY == 0 || CAL_DAY == 6) && CAL_SKIP_WKND == 0 ) { outHtml += "<td style=\"background-color: #DDDDDD\">"; 
		} else { if ( CAL_SKIP_WKND == 0 ) { outHtml += "<td>"; } }
		outHtml += "<a href=\"#\" onClick=\"javascript:tdt_setform('" + cYear + "-" + ( cMonth + 1 ) + "-" + CAL_ITTR + "','" + cForm + "','"+ cDiv +"')\">";
		outHtml += CAL_ITTR;
		outHtml += "</a></td>";
		CAL_ITTR++;
		CAL_DAY++;
	}
	outHtml += "</table>";

	
	document.getElementById(cDiv).innerHTML = outHtml;
}

// SET THE FORM ELEMENT LISTED
function tdt_setform(sDate, sForm, sDiv) {
	document.getElementById(sForm).value = sDate;
	document.getElementById(sDiv).innerHTML = "";
}

// GET THE NUMBER OF DAYS IN THE MONTH
function tdt_daysInMonth(iMonth, iYear)
{
	return 32 - new Date(iYear, iMonth, 32).getDate();
}
