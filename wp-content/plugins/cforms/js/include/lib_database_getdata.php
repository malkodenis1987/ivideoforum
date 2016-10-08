<?php

require_once('../../../../../wp-config.php');

if( !current_user_can('track_cforms') )
	wp_die("access restricted.");

global $wpdb;

$wpdb->cformssubmissions	= $wpdb->prefix . 'cformssubmissions';
$wpdb->cformsdata       	= $wpdb->prefix . 'cformsdata';

function countRec() {
	global $wpdb, $where, $sort, $limit;
	$sql = "SELECT count(id) FROM {$wpdb->cformssubmissions} $where $sort $limit";
	return $wpdb->get_var($sql);
}

$page = $_POST['page'];
$rp = $_POST['rp'];
$sortname = $_POST['sortname'];
$sortorder = $_POST['sortorder'];

$qtype = $_POST['qtype'];
$query = $_POST['query'];

### get form id from name
$query = str_replace('*','',$query);
if ( $qtype == 'form_id' && $query <> '' ){

	$form_id = false;
	$forms = get_option('cforms_formcount');	

	for ($i=0;$i<$forms;$i++) {
		$no = ($i==0)?'':($i+1);

		if ( preg_match( '/'.$query.'/i', get_option('cforms'.$no.'_fname') ) ){
			$form_id = $no;
		}
	}
	$query = ( !$form_id )?'$%&/':$form_id;
}else{
	$query = '%'.$query.'%';
}

if ( $query<>'' )
	$where = "WHERE $qtype LIKE '$query'";
else
	$where = '';

if (!$sortname)
	$sortname = 'id';
if (!$sortorder) $sortorder = 'desc';
	$sort = "ORDER BY $sortname $sortorder";
if (!$page)
	$page = 1;
if (!$rp)
	$rp = 10;

$start = (($page-1) * $rp);
$limit = "LIMIT $start, $rp";

for ($i=1; $i <= get_option('cforms_formcount'); $i++){
	$n = ( $i==1 )?'':$i; 
	$fnames[$i]=stripslashes(get_option('cforms'.$n.'_fname'));
}


$total = countRec();


$sql="SELECT * FROM {$wpdb->cformssubmissions} $where $sort $limit";
$result = $wpdb->get_results($sql);


header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/xml");

$xml = "<?xml version=\"1.0\"?>\n";
$xml .= "<rows>";
$xml .= "<page>$page</page>";
$xml .= "<total>$total</total>";

foreach ($result as $entry) {
	$n = ( $entry->form_id=='' )?'1':$entry->form_id;	
	$xml .= "<row id='".$entry->id."'>";
	$xml .= "<cell><![CDATA[".$entry->id."]]></cell>";
	$xml .= "<cell><![CDATA[".( $fnames[$n] )."]]></cell>";
	$xml .= "<cell><![CDATA[".( $entry->email )."]]></cell>";
	$xml .= "<cell><![CDATA[".( $entry->sub_date )."]]></cell>";
	$xml .= "<cell><![CDATA[".( $entry->ip )."]]></cell>";
	$xml .= "</row>";
}

$xml .= "</rows>";
echo $xml;
?>
