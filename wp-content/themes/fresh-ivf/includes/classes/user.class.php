<?php
/**
 * Class for users
 */

class User
{
    public $useremail       = '';
    public $userpass        = '';
    public $register_info   = array();
    public $errors          = array();
    public $new_user_id     = '';
    public $current_user    = '';

    /**
     * Create user
     *
     * @param mixed$register_info
     * @return User
     */
    public function createUser($register_info)
    {
        $this->register_info['edit']           = $register_info['edit'];
        $this->register_info['ID']             = $register_info['user_id'];
        $this->register_info['user_login']     = esc_sql($register_info['email']);
        $this->register_info['first_name']     = $register_info['f_name'];
        $this->register_info['last_name']      = $register_info['l_name'];
        $this->register_info['user_email']     = $register_info['email'];
        $this->register_info['pass1']          = $register_info['pass1'];
        $this->register_info['pass2']          = $register_info['pass2'];
        $this->register_info['approved']       = 0;
        $validate = $this->createValidation($this->register_info);
        if ($validate)
        {
            return $this;
        }
        if ($this->register_info['first_name'] != '' || $this->register_info['last_name'] != '')
        {
            $this->register_info['display_name'] = $this->register_info['first_name'] . ' ' . $this->register_info['last_name'];
        }
        else
        {
            $this->register_info['display_name'] = $this->register_info['user_login'];
        }
        /* create user */
        if ($this->register_info['edit'] !== 'true')
        {
            $this->register_info['user_pass'] = $this->register_info['pass1'];
            $this->new_user_id = wp_insert_user($this->register_info);
            $this->sendApprovingEmail();
        }
        /* update user */
        else
        {
            $this->new_user_id = $register_info['user_id'];
            if (trim($this->register_info['pass1']) !== '')
            {
                $this->register_info['user_pass'] = $this->register_info['pass1'];
            }
            wp_update_user($this->register_info);
        }
    }

    /**
     * Login user by email
     *
     * @param string $email
     * @param string $pass
     * @return bool|User
     */
    public function loginUser($email, $pass)
    {
        $this->useremail = $email;
        if (!$this->isUserApproved())
        {
            $this->setErrors(array(22 => 'Your account has not been approved yet by admin'));
            return $this;
        }
        if ($this->isUser())
        {
            $user_data = get_user_by('email', $this->useremail);
            $creds = array();
            $creds['user_login']    = $user_data->user_login;
            $creds['user_password'] = $pass;
            $user = wp_signon($creds, false);
            if (is_wp_error($user))
            {
                $this->setErrors(array(7 => $user->get_error_message()));
                return $this;
            }
            else
            {
                return true;
            }
        }
        else
        {
            $this->setErrors(array(8 => 'There is no user registered with that email address'));
            return $this;
        }
    }

    /**
     * Create new password for user
     *
     * @param string $email
     * @return User
     */
    public function forgotPassword($email)
    {
        global $wpdb;
        $this->useremail = $email;
        if (trim($this->useremail) == '')
        {
            $this->setErrors(array(9 => 'Enter a username or e-mail address'));
            return $this;
        }
        if ($this->isUser())
        {
            if ( strpos($this->useremail, '@') )
            {
                $user_data = get_user_by_email(trim($this->useremail));
                if ( empty($user_data) )
                {
                    $this->setErrors(array(8 => 'There is no user registered with that email address'));
                    return $this;
                }
            }
            else
            {
                $login = trim($this->useremail);
                $user_data = get_userdatabylogin($login);
            }
            do_action('lostpassword_post');
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;

            do_action('retreive_password', $user_login);
            do_action('retrieve_password', $user_login);

            $allow = apply_filters('allow_password_reset', true, $user_data->ID);

            if (!$allow)
            {
                $this->setErrors(array(10 => 'Password reset is not allowed for this user'));
                return $this;
            }
            else if (is_wp_error($allow))
            {
                $this->setErrors(array(10 => $allow->get_error_message()));
                return $this;
            }
            $key = $wpdb->get_var($wpdb->prepare(
                    "
                SELECT user_activation_key
                FROM $wpdb->users
                WHERE user_login = %s
            ",
                $user_login)
            );
            if (empty($key)) {
                $key = wp_generate_password(20, false);
                do_action('retrieve_password_key', $user_login, $key);
                $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
            }
            $message = __('Someone has asked to reset the password for the following site and username.') . "\r\n\r\n";
            $message .= network_site_url() . "\r\n\r\n";
            $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
            $message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.') . "\r\n\r\n";
            $message .= network_site_url("account/reset-password/?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . "\r\n";
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $title = sprintf( __('[%s] Password Reset'), $blogname );
            $title = apply_filters('retrieve_password_title', $title);
            $message = apply_filters('retrieve_password_message', $message, $key);
            if ($message && !wp_mail($user_email, $title, $message))
            {
                $this->setErrors(array(11 => 'The e-mail could not be sent. Possible reason: your host may have disabled the mail() function...'));
                return $this;
            }
        }
        else
        {
            $this->setErrors(array(8 => 'User with this email does not exist'));
            return $this;
        }
    }

    /**
     * Reset password for user
     *
     * @param mixed $data
     * @return User
     */
    public function resetPassword($data)
    {
        $user_data = get_userdatabylogin($data['login']);
        $this->useremail = $user_data->data->user_email;
        $validate = $this->resetValidation($user_data->ID, $data);
        if ($validate)
        {
            return $this;
        }
        $this->register_info['user_pass'] = $data['pass1'];
        $this->register_info['ID'] = $user_data->ID;
        wp_update_user($this->register_info);
    }

    protected function setCurrentUser()
    {
        $this->current_user = wp_get_current_user();
    }

    /**
     * Check is user present in DB by user email
     *
     * @return bool
     */
    protected function isUser()
    {
        $key = false;
        $user = get_user_by_email($this->useremail);
        if ($user) $key = true;
        return $key;
    }

    /**
     * Check is user approved by admin
     *
     * @return mixed $approved
     */
    protected function isUserApproved()
    {
        global $wpdb;
        $user = get_user_by_email($this->useremail);
        $approved = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'approved' AND user_id = $user->ID");
        return $approved;
    }

    /**
     * Adding errors
     *
     * @param string $error
     */
    protected function setErrors($error)
    {
        foreach($error as $key => $val)
        {
            $this->errors[$key] = $val;
        }
    }

    /**
     * Validation on user create
     *
     * @param mixed $fields
     * @return array
     */
    protected function createValidation($fields)
    {
        if(trim($fields['first_name']) == ''){
            $this->setErrors(array(1 => 'Please, enter correct First Name'));
        }
        if(trim($fields['last_name']) == ''){
            $this->setErrors(array(2 => 'Please, enter correct Last Name'));
        }
        if(!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$", $fields['user_email'])){
            $this->setErrors(array(3 => 'Please, enter correct Email'));
        }
        if ($fields['edit'] !== 'true')
        {
            if(trim($fields['pass1']) == ''){
                $this->setErrors(array(4 => 'Please, enter Password'));
            }
            $this->new_user_id = get_user_by_email($fields['user_email']);
            if ($this->new_user_id)
            {
                $this->setErrors(array(5 => 'Email address already exists!'));
            }
        }
        if($fields['pass1'] != $fields['pass2']){
            $this->setErrors(array(6 => 'Password do not match!'));
        }
        return $this->errors;
    }

    /**
     * Validation on user reset password
     *
     * @param int $user_id
     * @param array $fields
     * @return array
     */
    protected function resetValidation($user_id, $fields)
    {
        global $wpdb;
        if(trim($fields['pass1']) == ''){
            $this->setErrors(array(12 => 'Please, enter new Password'));
        }
        if($fields['pass1'] != $fields['pass2']){
            $this->setErrors(array(6 => 'Password do not match!'));
        }
        $key = $wpdb->get_var("SELECT user_activation_key FROM $wpdb->users WHERE ID = $user_id");
        if ($key !== $fields['key'])
        {
            $this->setErrors(array(13 => 'Activation key do not match!'));
        }
        return $this->errors;
    }

    /**
     * Update user meta
     */
    public function updateUserData()
    {
        update_user_meta($this->new_user_id, 'approved', $this->register_info['approved']);
    }

    /**
     * Update user meta
     */
    public function userApprove($user_id)
    {
        update_user_meta($user_id, 'approved', '1');
    }

    protected function sendApprovingEmail()
    {
		$subject = 'Подтверждение аккаунта';
		
        $message = __('Подтверждение аккаунта на сайте:') . "\r\n\r\n";
        $message .= network_site_url() . "\r\n\r\n";
        $message .= __('Для подтверждения аккаунта перейдите по ссылке.') . "\r\n\r\n";
        $message .= network_site_url("account/login/?action=approving&user_id=$this->new_user_id", 'login') . "\r\n";
        
		$headers[] = 'From: '.get_option('blogname').' <info@ivideoforum.org>';
		
		wp_mail($this->register_info['user_email'], $subject, $message, $headers);
    }
}
?>