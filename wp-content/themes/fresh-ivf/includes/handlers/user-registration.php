<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Elvis
 * Date: 20.10.13
 * Time: 14:52
 * To change this template use File | Settings | File Templates.
 */
    session_start();
    require_once('../../../../../wp-load.php');
    require_once('../classes/user.class.php');

    $user = new User();
    $newUser = $user->createUser($_POST);
    if ($user->errors)
    {
        $_SESSION['submitted_data'] = $_POST;
        $errorCodes = implode(',', array_keys($user->errors));
        header( 'Location: ' . $_POST['return_url'] . '?error_code=' .$errorCodes );
    }
    else
    {
        unset($_SESSION['submitted_data']);
        header( 'Location: ' . $_POST['return_url'] . '?success=1' ) ;
    }
