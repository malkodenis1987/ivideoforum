<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Elvis
 * Date: 19.10.13
 * Time: 22:28
 * To change this template use File | Settings | File Templates.
 */
class Contest
{
    public $errors          = array();
    public $max_file_size   = 10240000;
    public $folder          = '/konkurs/';
    public $valid_types 	=  array('gif', 'jpg', 'png', 'jpeg', 'tiff', 'JPG', 'JPEG');

    public function addPhoto($data, $file)
    {
        $validate = $this->addPhotoValidation($data);
        if ($validate)
        {
            return $this;
        }
        $newPhoto = array(
            'post_title'    => $data['title'],
            'post_content'  => '',
            'post_status'   => 'pending',
            'post_author'   => $data['author'],
            'post_type'     => 'photo'
        );
        $postId = wp_insert_post($newPhoto);
        add_post_meta($postId, 'fio', $data['fio']);
        add_post_meta($postId, 'city', $data['city']);
        add_post_meta($postId, 'email', $data['email']);
        add_post_meta($postId, 'phone', $data['phone']);
        add_post_meta($postId, 'ratings_average', '0');

        $uploads = wp_upload_dir();
        if (is_uploaded_file($file['photo']['tmp_name']))
        {
            $filename = $file['photo']['tmp_name'];
            $ext = substr($file['photo']['name'], 1 + strrpos($file['photo']['name'], "."));
            if (filesize($filename) > $this->max_file_size)
            {
                $this->setErrors(array(18 => 'File size > 8MB'));
                return $this;
            }
            else if (!in_array($ext, $this->valid_types))
            {
                $this->setErrors(array(19 => 'Invalid file type'));
                return $this;
            }
            else
            {
                $uploadFile = basename($file['photo']['name']);
                $file = $uploads['basedir'] . $this->folder . $uploadFile;
                if (!@move_uploaded_file($filename, $file))
                {
                    $this->setErrors(array(20 => 'Moving file failed.'));
                    return $this;
                }
                else
                {
                    $wpFileType = wp_check_filetype($uploadFile, null );
                    $attachment = array(
                        'post_mime_type' => $wpFileType['type'],
                        'post_title' => sanitize_file_name($filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    $attachId = wp_insert_attachment($attachment, $file, $postId);
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachData = wp_generate_attachment_metadata( $attachId, $file );
                    wp_update_attachment_metadata( $attachId, $attachData );
                    set_post_thumbnail( $postId, $attachId );
                }
            }
        }
        else
        {
            $this->setErrors(array(20 => 'Moving file failed.'));
            return $this;
        }
    }

    protected function addPhotoValidation($fields)
    {
        if(trim($fields['title']) == ''){
            $this->setErrors(array(14 => 'Please, enter correct Title'));
        }
        if(trim($fields['fio']) == ''){
            $this->setErrors(array(15 => 'Please, enter correct Name'));
        }
        if(trim($fields['city']) == ''){
            $this->setErrors(array(15 => 'Please, enter correct City'));
        }
        if(!ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)+$", $fields['email'])){
            $this->setErrors(array(3 => 'Please, enter correct Email'));
        }
        if(trim($fields['phone']) == ''){
            $this->setErrors(array(16 => 'Please, enter correct Phone'));
        }
    }

    protected function setErrors($error)
    {
        foreach($error as $key => $val)
        {
            $this->errors[$key] = $val;
        }
    }
}