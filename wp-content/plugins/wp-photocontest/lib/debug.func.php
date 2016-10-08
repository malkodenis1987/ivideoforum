<?php

/*========================================================================
                    _          _                     _ 
                   | |    __ _| |_ _   _ _ __  _   _(_)
                   | |   / _` | __| | | | '_ \| | | | |
                   | |__| (_| | |_| |_| | | | | |_| | |
                   |_____\__,_|\__|\__,_|_| |_|\__, |_|
                                               |___/   

=========================================================================*/

/**
*	Debug library
*
*	@author		Raymond van Velzen <raymond@latunyi.com>
*	@version	10
**/

// -----------------------------------------------------------------------

/**
*	pr
*	
*	Function to print anything in a convenient way
*	Will print any string/number/etc, array, or object.
*	
*	@param	mixed	$x		Anything
*	@param	string	$descr		Description to print above output, optional
*	@return	void
**/
function wppc_pr($x, $descr = '') 
{
	if (is_object($x)) {
		wppc_printobj($x, $descr);
	} elseif(is_array($x)) {
		wppc_printarr($x, $descr);
	} else {
		if (is_string($x) && substr($x, 0, 5) == '<?xml')
		{
			wppc_printxml($x, $descr);
		}
		else
		{
			wppc_printbr($x, $descr);
		}
	}
}

/**
*	prd
*	
*	Print something (d)irectly to the screen, using wppc_pr().
*	Will flush (not destroy) output buffer.
*	
*	@param	mixed	$x		Anything
*	@return	void
**/
function wppc_prd($x, $descr = '') 
{
	wppc_pr($x, $descr);
	ob_flush();
	flush();
}

// -----------------------------------------------------------------------

/**
*	prx
*
*	Print something using wppc_pr() and exit.
*	
*	@param	mixed	$x		Anything
*	@param	string	$descr		Description to print above output, optional
*	@return	void
**/
function wppc_prx($x, $descr = '') 
{
	wppc_pr($x, $descr);
	exit;
}

// -----------------------------------------------------------------------

/**
*	printarr
*
*	Print an array
*	
*	@param	mixed	$a		Any array
*	@param	string	$descr		Description to print above output, optional
*	@return	void
**/
function wppc_printarr($a, $descr = '') 
{
	if (!is_array($a)) 
	{
		ob_start();
		if (!empty($descr))
		{
			wppc_printbr('<b>' . $descr . '</b>');
		}
		var_dump($a);
		$str = ob_get_clean();
		wppc_printNicely($str);
	} else {
		ob_start();
		print '<pre>';
		if (!empty($descr))
		{
			print('<b>' . $descr . '</b>' . "\n");
		}
		print_r($a);
		print '</pre>';
		$str = ob_get_clean();
		wppc_printNicely($str);
	}
}

// -----------------------------------------------------------------------

/**
*	printobj
*
*	Print an object
*	
*	@param	mixed	$a		Any object
*	@param	string	$descr		Description to print above output, optional
*	@return	void
**/
function wppc_printobj($a, $descr = '') 
{
	ob_start();
	print '<pre>';
	if (!empty($descr))
	{
		print('<b>' . $descr . '</b>' . "\n");
	}
	print_r($a);
	print '</pre>';
	$str = ob_get_clean();
	wppc_printNicely($str);
}

// -----------------------------------------------------------------------

/**
*       wppc_printbr
*
*       Print something with a HTML line break. Obsolete; use pr() instead.
*      
*       @param  string  $text           Any array
*       @param  string  $descr          Description to print before output, optional
*       @return void
**/
function wppc_printbr ($text = '', $descr = '')
{
        wppc_printNicely($descr . ' ' . $text);
}


// -----------------------------------------------------------------------

/**
*	printNicely
*
*	Print some text in a box with fixed-width font
*	
*	@param	string	$text		Any text
*	@return	void
**/
function wppc_printNicely($str) 
{
	print '<div style="font: normal 13px Courier New !important; border: 1px solid #CCC; background-color: #EEE; padding: 10px; margin: 10px; text-align: left !important;">';
	print '<pre>' . trim($str) . '</pre>';
	print '</div>';
}

// -----------------------------------------------------------------------

/**
*	wppc_prg
*
*	Print GET array
*	
*	@return	void
**/
function wppc_prg() 
{
	wppc_printarr($_GET, '$_GET:');
}

/**
*	wppc_prg
*
*	Print GET array and exit
*	
*	@return	void
**/
function wppc_prgx() 
{
	wppc_prg();
	exit;
}

// -----------------------------------------------------------------------

/**
*	wppc_prp
*
*	Print POST array
*	
*	@return	void
**/
function wppc_prp() 
{
	wppc_printarr($_POST, '$_POST:');
}

/**
*	wppc_prpx
*
*	Print POST array and exit
*	
*	@return	void
**/
function wppc_prpx() 
{
	wppc_prp();
	exit;
}

// -----------------------------------------------------------------------

/**
*	wppc_prs
*
*	Print SESSION
*	
*	@return	void
**/
function wppc_prs($key = null) 
{
	if (isset($key))
	{
		if (isset($_SESSION[$key]))
		{
			wppc_pr($_SESSION[$key]);
		}
		else
		{
			wppc_pr('Key "' . $key . '" not found in SESSION object.');
		}
	}
	else
	{
		wppc_printarr($_SESSION, '$_SESSION:');
	}
}

/**
*	wppc_prsx
*
*	Print SESSION and exit
*	
*	@return	void
**/
function wppc_prsx($key = null) 
{
	wppc_prs($key);
	exit;
}

// -----------------------------------------------------------------------

/**
*	wppc_prc
*
*	Print COOKIE
*	
*	@return	void
**/
function wppc_prc() 
{
	wppc_printarr($_COOKIE);
}

/**
*	wppc_prcx
*
*	Print COOKIE and exit
*	
*	@return	void
**/
function wppc_prcx() 
{
	wppc_prc();
	exit;
}

// -----------------------------------------------------------------------

/**
*	wppc_prsvr
*
*	Print SERVER
*	
*	@return	void
**/
function wppc_prsvr() 
{
	wppc_printarr($_SERVER, '$_SERVER:');
}

/**
*	wppc_prsvrx
*
*	Print SERVER and exit
*	
*	@return	void
**/
function wppc_prsvrx() 
{
	wppc_prsvr();
	exit;
}

// -----------------------------------------------------------------------

/**
*	wppc_prf
*
*	Print $_FILES array
*	
*	@return	void
**/
function wppc_prf() 
{
	wppc_printarr($_FILES, '$_FILES:');
}

/**
*	wppc_prfx
*
*	Print $_FILES and exit
*	
*	@return	void
**/
function wppc_prfx() 
{
	wppc_prf();
	exit;
}

// -----------------------------------------------------------------------

// Print ISO date from timestamp
function wppc_prdt($ts)
{
	wppc_pr(date('Y-m-d', $ts));
}

// Print ISO date from timestamp and exit
function wppc_prdtx($ts)
{
	wppc_prdt($ts);
	exit;
}

// -----------------------------------------------------------------------


/**
*	wppc_prv
*
*	Print var_dump 
*	
*	@param	mixed	$x		Some variable
*	@return	void
**/
function wppc_prv($x, $descr = '') 
{
	ob_start();
	if (!empty($descr))
	{
		print($descr . "\n");
	}
	var_dump($x);
	$str = ob_get_clean();
	wppc_printNicely($str);
}

/**
*	wppc_prvx
*
*	Print var_dump and exit
*	
*	@param	mixed	$x		Some variable
*	@return	void
**/
function wppc_prvx($x, $descr = '') 
{
	wppc_prv($x, $descr);
	exit;
}

// -----------------------------------------------------------------------

/**
*	wppc_printxml
*
*	Prints XML.
*	
*	@param	mixed	$xml		Some XML variable
*	@return	void
**/
function wppc_printxml($xml, $descr = '') 
{
	wppc_pr(nl2br(htmlentities(str_replace("><",">\n<",$xml))), $descr);
}

// -----------------------------------------------------------------------

/**
*	wppc_GetExecutionTime
*
*	Calculates the execution time of a script, depending on start time.
*	
*	@param	float	$start		Start time, as float
*	@return	void
**/
function wppc_GetExecutionTime($start) 
{
	return sprintf('%1.6f', (microtime(true) - $start)) . ' sec';
}

// -----------------------------------------------------------------------

/**
*	wppc_GetMemory
*
*	Reports the amount of memory used by PHP, wrapper
*	on memory_get_usage(). Returns warning if memory_get_usage
*	function doesn't exist.
*	
*	@param	string	$unit	Measure unit: All (default), B, KB, MB
*	@return	string			'x B', 'x KB', 'x MB';
**/
function wppc_GetMemory($unit = 'All')
{
	if (!function_exists('memory_get_usage'))
	{
		return 'Can\'t report memory usage; memory_get_usage is not available.';
	}
	else
	{
		$bytes = memory_get_usage(true);
		switch ($unit)
		{
			case 'All':
				$kb = number_format( ($bytes / 1024), 2) . ' KB';
				$mb = number_format( (($bytes / 1024) / 1024), 2) . ' MB';
				return $bytes . ' bytes, ' . $kb . ', ' . $mb;
				break;
			case 'B':
				return $bytes . ' bytes';
				break;
			case 'KB':
				return number_format( ($bytes / 1024), 2) . ' KB';
				break;
			case 'MB':
				return number_format( (($bytes / 1024) / 1024), 2) . ' MB';
				break;
		}
	}
}

// Print wppc_GetMemory()
function wppc_prmem() {
	wppc_pr(wppc_GetMemory());
}

// Print wppc_GetMemory() and exit
function wppc_prmemx() {
	wppc_prx(wppc_GetMemory());
}

if (!function_exists('vardump'))
{
	function vardump($x)
	{
		ob_start();
		var_dump($x);
		$str = ob_get_clean();
		return $str;
	}
}
?>
