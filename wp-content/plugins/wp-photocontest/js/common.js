/**
*	Site-wide preparations
**/
wppc_jQuery.browser = {
	version: (navigator.userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
	safari: /webkit/.test( navigator.userAgent ),
	opera: /opera/.test( navigator.userAgent ),
	msie: /msie/.test( navigator.userAgent ) && !/opera/.test( navigator.userAgent ),
	mozilla: /mozilla/.test( navigator.userAgent ) && !/(compatible|webkit)/.test( navigator.userAgent )
};

wppc_jQuery(document).ready(function()
{
});

// -------------------------------------------------------------------

/**
*	HighlightActiveInput
*
*	Adds event handlers to all inputs with class 'text', that adds 
*	the class 'active-input' to them onfocus and removes it onblur.
**/
function HighlightActiveInput()
{
	// Target all inputs, but not selects on IE
	var inputs = 'input.text, textarea.text';
	if (wppc_jQuery.browser.msie)
	{
		inputs = 'input.text, textarea.text';
	}
	wppc_jQuery(inputs).each(function()
	{
		wppc_jQuery(this).focus(function()
		{
			wppc_jQuery(this).addClass('active-input');
		});
		wppc_jQuery(this).blur(function()
		{
			wppc_jQuery(this).removeClass('active-input');
		});
	});
}

// -------------------------------------------------------------------

/**
*	$$
*
*	Returns an object reference
*	for the given object id
**/
function dollarsigndollarsign(obj_id)
{
	return document.getElementById(obj_id);
}

// -------------------------------------------------------------------

/**
*	redirect
*
*	Redirects to the given page, with WEB_ROOT
**/
function redirect(url)
{
	window.location = WEB_ROOT + url;
}

// -------------------------------------------------------------------

/**
*	log
*
*	Logs to the console
**/
function log(str, to_div)
{
	if (window.console)
	{
		console.log(str);
	}
}

// -------------------------------------------------------------------

/**
*	ParseDate
*
*	Converts a string date (DD-MM-YYYY or YYYY-MM-DD) to Date object.
**/
function ParseDate(str)
{
	var parts = str.split('-');
	var d = new Date;
	d.setSeconds(0);
	d.setMinutes(0);
	d.setHours(0);
	
	if (parts[0].length == 4)
	{
		// YYYY-MM-DD 
		d.setDate(parts[2]);
		d.setMonth((parts[1] - 1));
		d.setFullYear(parts[0]);
	}
	else
	{
		// DD-MM-YYYY
		d.setDate(parts[0]);
		d.setMonth((parts[1] - 1));
		d.setFullYear(parts[2]);
	}
	return d;
}

// -----------------------------------------------------------------------

/**
*	Flashes feedback for 2 secs.
**/
function FlashFeedback(msg, target)
{
	if (!target)
	{
		target = 'feedback';
	}

	$('#' + target).html(msg).fadeIn();
	$('#' + target).oneTime('2s', function() {
		$(this).fadeOut();
	});
}

// -----------------------------------------------------------------------

/**
*	ResetListStriping
*
*	Iterates over the given table and resets the "odd"/"even" row classes.
*
*	- obj_id: ID of table object
**/
function ResetListStriping(obj_id)
{
	if (!$$(obj_id))
	{
		alert('ResetListStriping: There is no object with ID "' + obj_id + '".');
		return;
	}
	
	var row_class = 'odd';
	$('#' + obj_id + ' tbody tr').each(function()
	{
		var opposite = (row_class == 'odd' ? 'even' : 'odd');
		$(this).removeClass(opposite).addClass(row_class);
		row_class = (row_class == 'odd' ? 'even' : 'odd');
	});
}

// ----------------------------------------------

/**
*	Add in_array function to array
**/
Array.prototype.hasValue = function(value) 
{
	var len = this.length;
	for (var x = 0; x <= len; x++) 
	{
		if (this[x] == value) 
		{
			return true;
		}
	}
	return false;
};

String.prototype.trim = function() 
{
	return this.replace(/^\s+/, '').replace(/\s+$/, ''); 
}

