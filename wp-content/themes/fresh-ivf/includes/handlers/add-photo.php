<?php
    session_start();
    require_once('../../../../../wp-load.php');
    require_once('../classes/contest.class.php');

    $contest = new Contest();
    $photo = $contest->addPhoto($_POST, $_FILES);
    if ($contest->errors)
    {
        $_SESSION['submitted_data'] = $_POST;
        $errorCodes = implode(',', array_keys($contest->errors));
        header( 'Location: ' . $_POST['return_url'] . '?error_code=' .$errorCodes );
    }
    else
    {
        unset($_SESSION['submitted_data']);
        header( 'Location: ' . $_POST['return_url'] . '?success=1' ) ;
    }
