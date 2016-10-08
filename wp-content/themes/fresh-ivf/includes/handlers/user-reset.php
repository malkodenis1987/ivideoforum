<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Elvis
 * Date: 20.10.13
 * Time: 18:19
 * To change this template use File | Settings | File Templates.
 */

    require_once('../../../../../wp-load.php');
    require_once('../classes/user.class.php');

    $user = new User();
    $reset = $user->resetPassword($_POST);
    if ($reset->errors)
    {
        $reset_errors = implode($reset->errors, ';');
        echo $reset_errors;
    }
    else
    {
        echo 'success';
    }