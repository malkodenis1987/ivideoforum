<?php
session_start();
require_once('../../../../../wp-load.php');
require_once('../classes/ads.class.php');

$ads = new Ads();
$add = $ads->editAds($_POST, $_FILES);
if ($ads->errors)
{
    $_SESSION['submitted_data'] = $_POST;
    $errorCodes = implode(',', array_keys($ads->errors));
    header( 'Location: ' . $_POST['return_url'] . '?user_id='.$_POST['author'].'&ads_id='.$_POST['ID'].'&error_code=' .$errorCodes );
}
else
{
    unset($_SESSION['submitted_data']);
    header( 'Location: ' . $_POST['return_url'] . '?user_id='.$_POST['author'].'&ads_id='.$_POST['ID'].'&success=1' ) ;
}