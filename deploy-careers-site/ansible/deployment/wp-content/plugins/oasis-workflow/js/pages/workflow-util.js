function add_option_to_select(obj,dt,lbl,vl)
{
	var sel = jQuery("#" + obj).find('option');
	sel.remove();
	var appendStr = "";
	if(numKeys(dt)> 1 && !jQuery("#"+obj).attr("size")) {
		appendStr = "<option></option>";
	}
//	alert(numKeys(dt));
	/*
	If only user found then select it
	*/
    var selected_opt = '';
	if(numKeys(dt)==1)
	{
		selected_opt = " selected=selected ";		
	}
	
	
	if(typeof(dt)=="object" && numKeys(dt)>0 && lbl)
	{		
		for(var k in dt)
		{
			if(vl)
			{
				appendStr +="<option value='" + dt[k][vl] + "' " + selected_opt + " >" + dt[k][lbl] + "</option>";
			}
			else
			{
				appendStr +="<option value='" + k + "'>" + dt[k][lbl] + "</option>";
			}
		}
		jQuery('#'+obj).append(appendStr);
		
	}
	
	if(!lbl && !vl)
	{
		for(var k in dt)
		{		
			appendStr +="<option value='" + k + "'>" + dt[k] + "</option>";			
		}
		jQuery('#'+obj).append(appendStr);
	}
	
	if(numKeys(dt)==1)
	{
		jQuery("#assignee-set-point").click();		
	}	
}

function numKeys(obj)
{
    var count = 0;
    for(var prop in obj)
    {
        count++;
    }
    return count;
}

function chk_due_date(id1, dateFormat)
{
	var d_date = jQuery("#" + id1).val();
	if(!d_date){
		jQuery("#" + id1).css({"background-color":"#FBF3F3"});
		return;
	}
	//split into array
	parsedDate = jQuery.datepicker.parseDate('mm-M dd, yy', d_date.trim());
	var due_date_mm_dd_yyyy = jQuery.datepicker.formatDate('mm/dd/yy', parsedDate);	
	
	var c_datetime = new Date();
	var c_d = c_datetime.getDate() ;
	var c_m = c_datetime.getMonth() + 1 ;
	var c_y = c_datetime.getFullYear() ;	
	var arr_date = due_date_mm_dd_yyyy.split("/") ;	
	if( (c_y*10000 + c_m*100 + c_d*1) > (arr_date[2]*10000 + arr_date[0]*100 + arr_date[1]*1) ){
		alert(owf_workflow_util_vars.dueDateInPast);
		return false ;
	}
	return true;
}

/*
 * function to escape special characters specially when dealing with json data
 */
String.prototype.escapeSpecialChars = function() {
    return this.replace(/\\n/g, "\\n")
               .replace(/\\'/g, "\\'")
               .replace(/\\"/g, '\\"')
               .replace(/\\&/g, "\\&")
               .replace(/\\r/g, "\\r")
               .replace(/\\t/g, "\\t")
               .replace(/\\b/g, "\\b")
               .replace(/\\f/g, "\\f");
};

function normalWorkFlowSubmit(submitFunction) {
	submitFunction();
}

var jQueryACFValidation = jQuery.noConflict();
(function(jQuery){
	owThirdPartyValidation = {
		run : function (fnParam) {
			if ( typeof acf !== 'undefined' ) {
				workflowSubmitWithACF(fnParam);
			}
			else {
				normalWorkFlowSubmit(fnParam);
			}									
		}
	};
} (jQueryACFValidation));

/**
 * Unescape HTML entities in Javascript - http://stackoverflow.com/questions/1912501/unescape-html-entities-in-javascript
 *
 * @param input
 * @returns {string|JQuery}
 */
function html_decode(input){
	var doc = new DOMParser().parseFromString(input, "text/html");
	return doc.documentElement.textContent;
}