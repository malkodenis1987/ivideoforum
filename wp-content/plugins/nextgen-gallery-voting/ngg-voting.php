<?php
/*
Plugin Name: NextGEN Gallery Voting
Plugin URI: http://shauno.co.za/wordpress/nextgen-gallery-voting/
Description: This plugin allows you to add user voting and rating to NextGEN Galleries and Images
Version: 2.6.1
Author: Shaun Alberts
Author URI: http://shauno.co.za
*/
/*
Copyright 2013  Shaun Alberts  (email : shaunalberts@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
This plugin has been completely rewritten, from the ground up, for version 2.0.
Backwards compatibility has been maintained with previous installs, which might mean some 'interesting' code below :)
*/

// stop direct call
if(preg_match('#'.basename(__FILE__).'#', $_SERVER['PHP_SELF'])) {die('You are not allowed to call this page directly.');}

class nggVoting {
	private $dbVersion = 2.6;
	public $slug;
	public $types = array(
		'2'=>array('name'=>'Star Rating', 'gallery'=>true, 'image'=>true,
			'galleryCallback'=>array(
				'class'=>'nggvGalleryVote',
				'form'=>'galleryVoteFormStar',
				'catch'=>'galleryCatchVoteStar',
				'results'=>'galleryVoteResultsStar'
			),
			'imageCallback'=>array(
				'class'=>'nggvGalleryVote',
				'form'=>'imageVoteFormStar',
				'catch'=>'imageCatchVoteStar',
				'results'=>'imageVoteResultsStar'
			)),
		
		'1'=>array('name'=>'Drop Down', 'gallery'=>true, 'image'=>true,
			'galleryCallback'=>array(
				'class'=>'nggvGalleryVote',
				'form'=>'galleryVoteFormDropDown',
				'catch'=>'galleryCatchVoteDropDown',
				'results'=>'galleryVoteResultsDropDown'
			),
			'imageCallback'=>array(
				'class'=>'nggvGalleryVote',
				'form'=>'imageVoteFormDropDown',
				'catch'=>'imageCatchVoteDropDown',
				'results'=>'imageVoteResultsDropDown'
			)),
		
		'3'=>array('name'=>'Like / Dislike', 'gallery'=>true, 'image'=>true,
			'galleryCallback'=>array(
				'class'=>'nggvGalleryVote',
				'form'=>'galleryVoteFormDisLike',
				'catch'=>'galleryCatchVoteDisLike',
				'results'=>'galleryVoteResultsDisLike'
			),
			'imageCallback'=>array(
				'class'=>'nggvGalleryVote',
				'form'=>'imageVoteFormDisLike',
				'catch'=>'imageCatchVoteDisLike',
				'results'=>'imageVoteResultsDisLike'
			))
	);
	private $initCatchVote = array();
	
	function __construct() {
		require_once('voting-types.php');
		
		register_activation_hook(__FILE__, array(&$this, 'dbUpgrade'));
		
		$this->adminUrl = get_admin_url().'admin.php?page='; //not sure this is ideal? TODO, research better way of getting pre-slug admin page URL
		//use of dirname(__FILE__) as __DIR__ is only available from 5.3
		$this->slug = basename(dirname(__FILE__));
		
		$tmp = explode('/', str_replace('\\', '/', dirname(__FILE__)));
		$dir = array_pop($tmp);
		$this->pluginUrl = trailingslashit(WP_PLUGIN_URL.'/'.$dir);
		$this->pluginPath = trailingslashit(str_replace('\\', '/', dirname(__FILE__)));
		
		//general hooks - admin
		add_action('admin_init', array(&$this, 'adminInits'));
		add_action('admin_menu', array(&$this, 'adminMenu'));
		
		//image voting hooks - admin
		add_action('ngg_manage_images_columns', array(&$this, 'addImageVoteOptionsCol')); //actions added in 1.7. I think it's safe to assume it exists now.
		add_action('ngg_manage_image_custom_column', array(&$this, 'addImageVotingOptions'), 10, 2);
		add_action('ngg_added_new_image', array(&$this, 'addNewImage'));
		
		//used for saving both image and gallery fields - admin
		add_action('ngg_update_gallery', array(&$this, 'onGalleryUpdate'), 10, 2);
		
		//gallery voting hooks - admin
		add_action('ngg_manage_gallery_settings', array(&$this, 'addGalleryVotingOptions'));
		add_action('ngg_add_new_gallery_form', array(&$this, 'newGalleryForm')); //new in ngg 1.4.0a
		add_action('ngg_created_new_gallery', array(&$this, 'onCreateGallery')); //new in ngg 1.4.0a
		//gallery voting hooks - user
		add_filter('ngg_show_gallery_content', array(&$this, 'showGallery'), 10, 2);
		
		add_action('init', array(&$this, 'wpInit'));
		
	}
	
	// Install Functions {
		/**
		 * Create the database tables needed for the plugin to run. Called on activation
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function dbUpgrade() {
			global $wpdb;
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			
			//new col 'enforce_with_cookie'
			
			$sql = 'CREATE TABLE '.$wpdb->prefix.'nggv_settings (
				id BIGINT(19) NOT NULL AUTO_INCREMENT,
				gid BIGINT NOT NULL DEFAULT 0,
				pid BIGINT NOT NULL DEFAULT 0,
				enable TINYINT NOT NULL DEFAULT 0,
				force_login TINYINT NOT NULL DEFAULT 0,
				force_once TINYINT NOT NULL DEFAULT 0,
				enforce_with_cookie TINYINT NOT NULL DEFAULT 0,
				force_once_time TINYINT NOT NULL DEFAULT 0,
				user_results TINYINT NOT NULL DEFAULT 0,
				voting_type INT NOT NULL DEFAULT 1,
				criteria_id BIGINT NOT NULL DEFAULT 0,
				show_in_popup TINYINT NOT NULL DEFAULT 0,
				UNIQUE KEY id (id)
			);';
			dbDelta($sql);
						
			$sql = 'CREATE TABLE '.$wpdb->prefix.'nggv_votes (
			id BIGINT(19) NOT NULL AUTO_INCREMENT,
			gid BIGINT NOT NULL,
			pid BIGINT NOT NULL,
			criteria_id BIGINT NOT NULL DEFAULT 0,
			vote INT NOT NULL DEFAULT 0,
			user_id BIGINT NOT NULL DEFAULT 0,
			ip VARCHAR(32) NULL,
			proxy VARCHAR(32) NULL,
			dateadded DATETIME NOT NULL DEFAULT "0000-00-00 00:00:00",
			UNIQUE KEY id (id)
			);';
			dbDelta($sql);
		}
	// }
	
	// API Functions {
		/**
			* Gets the voting options for a specific image
			* @param int $pid The image ID
			* @param int $criteriaId The criteria ID. 0 for hardcoded 'overview' for backwards compatibility
			* @author Shaun <shaunalberts@gmail.com>
			* @return object of options on success, empty false on failure
			*/
		function getImageVotingOptions($pid, $criteriaId) {
			global $wpdb;
			$opts = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'nggv_settings WHERE pid = "'.$wpdb->escape($pid).'" AND criteria_id = "'.$wpdb->escape($criteriaId).'"');
			return is_numeric($pid) && isset($opts->pid) && $opts->pid == $pid ? $opts : false;
		}
		
		/**
		 * Get the voting results of an image
		 * @param int $pid The image ID (deprecated, but supported)
		 * @param array $pid
		 *  int pid[pid] The image Id
		 *  int pid[criteria_id] The criteria ID. Defaults to 0 for backwards compatibility
		 * @param array $type The type of results to return (can limit amount of queries if you only need the avg for example)
		 *  bool type[avg] : Get average vote
		 *  bool type[list] : Get all the votes for the gallery
		 *  bool type[number] : Get the number of votes for the gallery
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return array("avg"=>double average for image, "list"=>array of objects of all votes of the image, "number"=>integer the number of votes for the image)
		 */
		function getImageVotingResults($pid, $type=array("avg"=>true, "list"=>true, "number"=>true, "likes"=>true, "dislikes"=>true), $extraWhere='') {
			if(is_numeric($pid)) { //old style arg where just 'pid' was needed
				$criteriaId = 0; //default for old calls and add ons
			}else if(is_array($pid)) {
				$tmp = $pid;
				$pid = $tmp['pid'];
				$criteriaId = is_numeric($tmp['criteria_id']) ? $tmp['criteria_id'] : 0;
			}
			
			if(is_numeric($pid) && is_numeric($criteriaId)) {
				global $wpdb;
				
				$results = array();
				
				if(isset($type["avg"]) && $type["avg"]) {
					$avg = $wpdb->get_row("SELECT SUM(vote) / COUNT(vote) AS avg FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."' GROUP BY pid");
					$results['avg'] = (isset($avg->avg) && $avg->avg ? $avg->avg : 0);
				}
				if(isset($type["list"]) && $type["list"]) {
					$where = "pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."'";
					if($extraWhere) {
						$where = '('.$where.') AND ('.$extraWhere.')';
					}
					$list = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE ".$where." ORDER BY dateadded DESC");
					$results['list'] = $list;
				}
				if(isset($type["num"]) && $type["num"]) {
					$num = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."' GROUP BY pid");
					$results['number'] = (isset($num->num) && $num->num ? $num->num : 0);
				}
				if(isset($type["likes"]) && $type["likes"]) {
					$likes = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."' AND vote = 100 GROUP BY pid");
					$results['likes'] = (isset($likes->num) && $likes->num ? $likes->num : 0);
				}
				if(isset($type["dislikes"]) && $type["dislikes"]) {
					$dislikes = $wpdb->get_row("SELECT COUNT(vote) AS num FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."' AND vote = 0 GROUP BY pid");
					$results['dislikes'] = (isset($dislikes->num) && $dislikes->num ? $dislikes->num : 0);
				}

				return $results;
			}else{
				return array();
			}
		}
		
		/**
		 * Gets the voting options for a specific gallery
		 * @param int $gid The NextGEN Gallery ID
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return object of options on success, empty false on failure
		 */
		function getVotingOptions($gid) {
			global $wpdb;
			$opts = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'nggv_settings WHERE gid = "'.$wpdb->escape($gid).'"');
			return $opts ? $opts : false;
		}

		/**
		 * Get the voting results of a gallery
		 * @param int $gid The NextGEN Gallery ID
		 * @param array $type The type of results to return (can limti admoun of queries if you only need the avg for example)
		 *  bool type[avg] : Get average vote
		 *  bool type[list] : Get all the votes for the gallery
		 *  bool type[number] : Get the number of votes for the gallery
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return array("avg"=>double average for gallery, "list"=>array of objects of all votes of the gallery, "number"=>integer the number of votes for the gallery)
		 */
		function getVotingResults($gid, $type=array('avg'=>true, 'list'=>true, 'number'=>true, 'likes'=>true, 'dislikes'=>true)) {
			if(is_numeric($gid)) {
				global $wpdb;
				
				$results = array();
								
				if(isset($type['avg']) && $type['avg']) {
					$avg = $wpdb->get_row('SELECT SUM(vote) / COUNT(vote) AS avg FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" GROUP BY gid');
					$results['avg'] = (isset($avg->avg) && $avg->avg ? $avg->avg : 0);
				}
				if(isset($type['list']) && $type['list']) {
					$list = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" ORDER BY dateadded DESC');
					$results['list'] = $list;
				}
				if(isset($type['num']) && $type['num']) {
					$num = $wpdb->get_row('SELECT COUNT(vote) AS num FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" GROUP BY gid');
					$results['number'] = (isset($num->num) && $num->num ? $num->num : 0);
				}
				if(isset($type['likes']) && $type['likes']) {
					$likes = $wpdb->get_row('SELECT COUNT(vote) AS num FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" AND vote = 100 GROUP BY gid');
					$results['likes'] = (isset($likes->num) && $likes->num ? $likes->num : 0);
				}
				if(isset($type['dislikes']) && $type['dislikes']) {
					$dislikes = $wpdb->get_row('SELECT COUNT(vote) AS num FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" AND vote = 0 GROUP BY gid');
					$results['dislikes'] = (isset($dislikes->num) && $dislikes->num ? $dislikes->num : 0);
				}
				
				return $results;
			}else{
				return array();
			}
		}

		/**
		 Checks if the current user can vote on a gallery
		 * @param int $gid The NextGEN Gallery ID
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return true if the user can vote, string of reason if the user can not vote
		 */
		function canVote($gid) {
			$options = $this->getVotingOptions($gid);
			
			if(!$options) {
				return false;
			}
			
			if(!$options->enable) {
				return 'VOTING NOT ENABLED';
			}
			
			if($options->force_login) {
				global $current_user;
				get_currentuserinfo();

				if(!$current_user->ID) {
					return 'NOT LOGGED IN';
				}
			}
			
			if($options->force_once) {
				if($options->force_login) { //force login, so check userid has voted already
					if($this->userHasVoted($gid, $current_user->ID)) {
						return 'USER HAS VOTED';
					}
				}else{ //no forced login, so just check the IP for a vote
					if($this->ipHasVoted($gid)) {
						return 'IP HAS VOTED';
					}
				}
			}
			
			return true;
		}
		
		/**
		 Checks if the current user can vote on an image (current almost identical to self::canVote(), but is seperate function for scalability)
		 * @param int $pid The image ID
		 * @param int $criteriaId The criteria ID. 0 for hardcoded 'overview' for backwards compatibility
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return true if the user can vote, string of reason if the user can not vote
		 */
		function canVoteImage($pid, $criteriaId) {
			$options = $this->getImageVotingOptions($pid, $criteriaId);
			
			if(!$options) {
				return false;
			}
			
			if(!$options->enable) {
				return "VOTING NOT ENABLED";
			}
			
			if($options->force_login) {
				global $current_user;
				get_currentuserinfo();

				if(!$current_user->ID) {
					return "NOT LOGGED IN";
				}
			}
			
			if($options->force_once == 1) {
				if($options->force_login) { //force login, so check userid has voted already
					if($vote = $this->userHasVotedImage(array('pid'=>$pid, 'criteria_id'=>$criteriaId), $current_user->ID)) {
						$canVote = apply_filters('nggv_check_vote_time', null, $pid, $criteriaId, $options, $vote[0]->dateadded);
						
						if($canVote === true) { //time allows it!
							return true;
						}else if($canVote) { //returned a string, so thats an error
							return 'USER '.$canVote;
						}else{ //not allowing or error message, so fall back to old default
							return 'USER HAS VOTED';
						}
					}
				}else{ //no forced login, so just check the IP for a vote
					//old premium users have this method defined with 'wrong' variable order. keep their installs working
					if(class_exists('nggVotingPremium') && !defined('nggVotingPremium::VERSION')) {
						$canVote = apply_filters('nggv_image_can_vote', $pid, $criteriaId, $options, null);
					}else{
						$canVote = apply_filters('nggv_image_can_vote', null, $pid, $criteriaId, $options);
					}
										
					if($canVote === true) { //cookie allows it!
						return true;
					}else if($canVote) { //returned a string, so thats an error
						return $canVote;
					}else if($canVote === null) { //cookie voting not set, so use IP
						if($vote = $this->ipHasVotedImage(array('pid'=>$pid, 'criteria_id'=>$criteriaId))) {
							$canVote = apply_filters('nggv_check_vote_time', null, $pid, $criteriaId, $options, $vote[0]->dateadded);
							
							if($canVote === true) { //filter (cookie for now) allows it!
								return true;
							}else if($canVote) {
								return 'IP '.$canVote;
							}else{
								return "IP HAS VOTED";
							}
						}
					}
				}
			}else if($options->force_once == 2) {
				if($options->force_login) { //force login, so check userid has voted already
					
					if($vote = $this->userHasVotedOnGalleryImage(array('pid'=>$pid, 'criteria_id'=>$criteriaId), $current_user->ID)) {
						$canVote = apply_filters('nggv_check_vote_time', null, $pid, $criteriaId, $options, $vote[0]->dateadded);
						
						if($canVote === true) { //time allows it!
							return true;
						}else if($canVote) { //returned a string, so thats an error
							return 'USER '.$canVote;
						}else{ //not allowing or error message, so fall back to old default
							return 'USER HAS VOTED';
						}
					}
				}else{ //no forced login, so just check (a filter return value), then the IP for a vote
					//old premium users have this method defined with 'wrong' variable order. keep their installs working
					if(class_exists('nggVotingPremium') && !defined('nggVotingPremium::VERSION')) {
						$canVote = apply_filters('nggv_image_can_vote', $pid, $criteriaId, $options, null);
					}else{
						$canVote = apply_filters('nggv_image_can_vote', null, $pid, $criteriaId, $options);
					}
															
					if($canVote === true) { //cookie allows it!
						return true;
					}else if($canVote) { //returned a string, so thats an error
						return $canVote;
					}else if($canVote === null) { //cookie voting not set, so use IP
						if($vote = $this->ipHasVotedOnGalleryImage(array('pid'=>$pid, 'criteria_id'=>$criteriaId))) {
							$canVote = apply_filters('nggv_check_vote_time', null, $pid, $criteriaId, $options, $vote[0]->dateadded);
							
							if($canVote === true) { //cookie allows it!
								return true;
							}else if($canVote) {
								return 'IP '.$canVote;
							}else{
								return "IP HAS VOTED";
							}
						}
					}

					
					
					/*
					$canVote = apply_filters('nggv_image_can_vote', $pid, $criteriaId, $options, null);
					
					if($canVote === true) {
						return true;
					}else if($canVote === false) {
						return "COOKIE HAS VOTED";
					}else{
						if($this->ipHasVotedOnGalleryImage(array('pid'=>$pid, 'criteria_id'=>$criteriaId))) {
							return "IP HAS VOTED";
						}
					}
					*/
				}
			}
			
			return true;
		}
		
		/**
		 * Check if a user has voted on a gallery before
		 * @param int $gid The NextGEN Gallery ID
		 * @param int $userid The users id to check
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return array of objects of all the votes the user has cast for this gallery, or blank array
		 */
		function userHasVoted($gid, $userid) {
			global $wpdb;
			
			if($votes = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" AND user_id = "'.$wpdb->escape($userid).'"')) {
				return $votes;
			}else{
				return array();
			}
		}
		
		/**
			* Check if a user has voted on an image before
			* @param int $pid The image ID to check (deprecated)
			* @param array $pid
			*  int pid[pid] The image Id
			*  int pid[criteria_id] The criteria ID. Defaults to 0 for backwards compatibility
			* @param int $userid The users id to check
			* @author Shaun <shaunalberts@gmail.com>
			* @return object of all the votes the user has cast for this image, or blank array
			*/
		function userHasVotedImage($pid, $userid) {
			global $wpdb;
			
			if(is_numeric($pid)) { //old style arg where just 'pid' was needed
				$criteriaId = 0; //default for old calls and add ons
			}else if(is_array($pid)) {
				$tmp = $pid;
				$pid = $tmp['pid'];
				$criteriaId = is_numeric($tmp['criteria_id']) ? $tmp['criteria_id'] : 0;
			}
			
			if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."' AND user_id = '".$wpdb->escape($userid)."' ORDER BY dateadded DESC")) {
				return $votes;
			}else{
				return array();
			}
		}

		/**
			* Check if a user has voted on any image in this $pid gallery before
			* @param int $pid The image ID to check (deprecated)
			* @param array $pid
			*  int pid[pid] The image Id
			*  int pid[criteria_id] The criteria ID. Defaults to 0 for backwards compatibility
			* @param int $userid The users id to check
			* @author Shaun <shaunalberts@gmail.com>
			* @return bool true if the user has voted on any image in the same gallery as this $pid, false of not
			*/
		function userHasVotedOnGalleryImage($pid, $userid) {
			global $wpdb;
			
			if(is_numeric($pid)) { //old style arg where just 'pid' was needed
				$criteriaId = 0; //default for old calls and add ons
			}else if(is_array($pid)) {
				$tmp = $pid;
				$pid = $tmp['pid'];
				$criteriaId = is_numeric($tmp['criteria_id']) ? $tmp['criteria_id'] : 0;
			}
						
			if(!$image = nggdb::find_image($pid)) {
				return true; //huh, cant find image, so dont let the person vote to be safe (this should never happen)
			}
			
			$picturelist = nggdb::get_gallery($image->gid);
			$inPid = array();
			foreach ((array)$picturelist as $key=>$val) {
				$inPid[] = $val->pid;
			}
			
			if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid IN (".implode(', ', $inPid).") AND criteria_id = '".$wpdb->escape($criteriaId)."' AND user_id = '".$wpdb->escape($userid)."' ORDER BY dateadded DESC")) {
				return $votes;
			}else{
				return array();
			}
		}

		/**
		 * Check if an IP has voted on any images in the gallery of the $pid passed
		 * @param int $pid The image ID (deprecated)
		 * @param array $pid
		 *  int pid[pid] The image Id
		 *  int pid[criteria_id] The criteria ID. Defaults to 0 for backwards compatibility
		 * @param string The IP to check.  If not passed, the current users IP will be assumed
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return bool true if the $ip has voted on any image in the same gallery as this $pid, false of not
		 */
		function ipHasVotedOnGalleryImage($pid, $ip=null) {
			global $wpdb;
			
			if(!$ip) {
				$tmp = $this->getUserIp();
				$ip = $tmp["ip"];
			}
			
			if(is_numeric($pid)) { //old style arg where just 'pid' was needed
				$criteriaId = 0; //default for old calls and add ons
			}else if(is_array($pid)) {
				$tmp = $pid;
				$pid = $tmp['pid'];
				$criteriaId = is_numeric($tmp['criteria_id']) ? $tmp['criteria_id'] : 0;
			}
			
			if(!$image = nggdb::find_image($pid)) {
				return true; //huh, cant find image, so dont let the person vote to be safe (this should never happen)
			}
			
			$picturelist = nggdb::get_gallery($image->gid);
			$inPid = array();
			foreach ((array)$picturelist as $key=>$val) {
				$inPid[] = $val->pid;
			}
			
			if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid IN (".implode(', ', $inPid).") AND criteria_id = '".$wpdb->escape($criteriaId)."' AND ip = '".$wpdb->escape($ip)."' ORDER BY dateadded DESC")) {
				return $votes;
			}else{
				return array();
			}
		}
		
		/**
		 * Check if an IP has voted on a gallery before
		 * @param int $gid The NextGEN Gallery ID
		 * @param string The IP to check.  If not passed, the current users IP will be assumed
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return array of objects of all the votes this IP has cast for this gallery, or blank array
		 */
		function ipHasVoted($gid, $ip=null) {
			global $wpdb;
			if(!$ip) {
				$tmp = $this->getUserIp();
				$ip = $tmp["ip"];
			}
			
			if($votes = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'nggv_votes WHERE gid = "'.$wpdb->escape($gid).'" AND ip = "'.$wpdb->escape($ip).'"')) {
				return $votes;
			}else{
				return array();
			}
		}

		/**
		 * Check if an IP has voted on an image before
		 * @param int $pid The image ID
		 * @param array $pid
		 *  int pid[pid] The image Id
		 *  int pid[criteria_id] The criteria ID. Defaults to 0 for backwards compatibility
		 * @param string The IP to check.  If not passed, the current users IP will be assumed
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return object of all the votes this IP has cast for this image, or blank array
		 */
		function ipHasVotedImage($pid, $ip=null) {
			global $wpdb;
			
			if(is_numeric($pid)) { //old style arg where just 'pid' was needed
				$criteriaId = 0; //default for old calls and add ons
			}else if(is_array($pid)) {
				$tmp = $pid;
				$pid = $tmp['pid'];
				$criteriaId = is_numeric($tmp['criteria_id']) ? $tmp['criteria_id'] : 0;
			}
			
			if(!$ip) {
				$tmp = $this->getUserIp();
				$ip = $tmp["ip"];
			}
			
			if($votes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."nggv_votes WHERE pid = '".$wpdb->escape($pid)."' AND criteria_id = '".$wpdb->escape($criteriaId)."' AND ip = '".$wpdb->escape($ip)."' ORDER BY dateadded DESC")) {
				return $votes;
			}else{
				return array();
			}
			
		}
		
		/**
		 * Get a users IP.  If the users proxy allows, we get their actual IP, not just the proxy's
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return array("ip"=>string The IP found[might be proxy IP, sorry], "proxy"=>string The proxy IP if the proxy was nice enough to tell us it)
		 */
		function getUserIp() {
			$proxy = '';
			$ip = '';
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
				if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
					$proxy = $_SERVER['HTTP_CLIENT_IP'];
				} else {
					if(isset($_SERVER['REMOTE_ADDR'])) {
						$proxy = $_SERVER['REMOTE_ADDR'];
					}
				}
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
					if(isset($_SERVER['HTTP_CLIENT_IP'])) {
						$ip = $_SERVER['HTTP_CLIENT_IP'];
					}
				} else {
					if(isset($_SERVER['REMOTE_ADDR'])) {
						$ip = $_SERVER['REMOTE_ADDR'];
					}
				}
			}
			
			//if comma list of IPs, get the LAST one
			if($proxy) {
				$proxy = explode(',', $proxy);
				$proxy = trim(array_pop($proxy));
			}
			if($ip) {
				$ip = explode(',', $ip);
				$ip = trim(array_pop($ip));
			}
			
			return array('ip'=>$ip, 'proxy'=>$proxy);
		}
		
		/**
		 * Save the vote.  Checks sefl::canVote() to be sure you aren't being sneaky
		 * @param array $config The array that makes up a valid vote
		 *  int config[gid] : The NextGEN Gallery ID
		 *  int config[vote] : The cast vote, must be between 0 and 100 (inclusive)
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return true on success, false on DB failure, string on self::canVote() not returning true
		 */
		function saveVote($config) {
			if(is_numeric($config['gid']) && $config['vote'] >= 0 && $config['vote'] <= 100) {
				if(($msg = $this->canVote($config['gid'])) === true) {
					global $wpdb, $current_user;
					get_currentuserinfo();
					$ip = $this->getUserIp();
					//TODO 2.0, consider using wpdb insert methods
					if($wpdb->query('INSERT INTO '.$wpdb->prefix.'nggv_votes (id, pid, gid, vote, user_id, ip, proxy, dateadded) VALUES (null, 0, "'.$wpdb->escape($config['gid']).'", "'.$wpdb->escape($config['vote']).'", "'.$current_user->ID.'", "'.$ip['ip'].'", "'.$ip['proxy'].'", "'.date('Y-m-d H:i:s', time()).'")')) {
						return true;
					}else{
						return false;
					}
				}else{
					return $msg;
				}
			}
		}

		/**
		 * Delete all votes for a specific image
		 * @param int $pid The picture id from NGG
		 * @param int $criteriaId The criteria ID. 0 for hardcoded 'overview' for backwards compatibility
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return true on success, false on failure
		 */
		function deleteImageVotes($pid, $criteriaId) {
			global $wpdb;
			if($wpdb->query("DELETE FROM ".$wpdb->prefix."nggv_votes WHERE gid = 0 AND pid = ".$wpdb->escape($pid)." AND criteria_id = ".$wpdb->escape($criteriaId)) !== false) { //check for FALSE vs 0 (0 rows isn't a db error!)
				return true;
			}else{
				return false;
			}
		}
	
		/**
		 * Save the vote.  Checks self::canVoteImage() to be sure you aren't being sneaky
		 * @param array $config The array that makes up a valid vote
		 *  int config[pid] : The image id
		 *  int config[vote] : The cast vote, must be between 0 and 100 (inclusive)
		 *  int config[criteria_id] : The criteria ID. 0 for hardcoded 'overview' for backwards compatibility
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return true on success, false on DB failure, string on self::canVoteImage() not returning true
		 */
		function saveVoteImage($config) {
			if(is_numeric($config["pid"]) && $config["vote"] >= 0 && $config["vote"] <= 100) {
				$criteriaId = isset($config['criteria_id']) && is_numeric($config['criteria_id']) ? $config['criteria_id'] : 0;
				if(($msg = $this->canVoteImage($config["pid"], $criteriaId)) === true) {
					global $wpdb, $current_user;
					get_currentuserinfo();
					$ip = $this->getUserIp();
					if($wpdb->query("INSERT INTO ".$wpdb->prefix."nggv_votes (id, gid, pid, criteria_id, vote, user_id, ip, proxy, dateadded) VALUES (null, 0, '".$wpdb->escape($config["pid"])."', '".$wpdb->escape($criteriaId)."', '".$wpdb->escape($config["vote"])."', '".$current_user->ID."', '".$ip["ip"]."', '".$ip["proxy"]."', '".date("Y-m-d H:i:s", time())."')")) {
						return true;
					}else{
						return false;
					}
				}else{
					return $msg;
				}
			}
		}
		
		function getImageCriteria() {
			$tmp = array();
			$tmp[0] = new stdClass;
			$tmp[0]->id = 0;
			$tmp[0]->name = 'Overall';
			
			return apply_filters('nggv_image_options_criteria', $tmp);
		}

		/**
		 * Stops the script including a JS file more than once.  wp_enqueue_script only works
		 * before any buffers have been outputted, so this will have to do
		 * @param string $filename The path/url to the js file to be included
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return string with the <script> tags if not included already, else nothing
		 */
		function includeJs($filename) {
			global $nggv_front_scripts;
			
			if(!$nggv_front_scripts) {
				$nggv_front_scripts = array();
			}
			
			if(!isset($nggv_front_scripts[$filename])) {
				$nggv_front_scripts[$filename] = array('filename'=>$filename, 'added'=>true);
				return '<script type="text/javascript" src="'.$filename.'"></script>';
			}
		}
		
		/**
		 * Stops the script including a CSS file more than once
		 * @param string $filename The path/url to the js file to be included
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return string with the <link> tags if not included already, else nothing
		 */
		function includeCss($filename) {
			global $nggv_front_css;
			
			if(!$nggv_front_css) {
				$nggv_front_css = array();
			}
			
			if(!isset($nggv_front_css[$filename])) {
				$nggv_front_css[$filename] = array('filename'=>$filename, 'added'=>true);
				return '<link rel="stylesheet" href="'.$filename.'" type="text/css" media="all" />';
			}
		}
		
		public static function msg($msg) {
			return apply_filters('nggv_msg', $msg);
		}
	// }
	
	// Admin Functions {
		function adminInits() {
			// auto update the db if new version found.
			if(get_option('nggv_database_version') === false) { //bool false means does not exists
				add_option('nggv_database_version', 0, null, 'no');
			}
			if(get_option('nggv_database_version') < $this->dbVersion) {
				$this->dbUpgrade();
				update_option('nggv_database_version', $this->dbVersion);
			}
			
			wp_enqueue_script('jquery');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
			
			wp_enqueue_script('jquery-ui-datepicker');
			wp_enqueue_script('nggv-top-voted', $this->pluginUrl.'js/top-voted.js');
			wp_enqueue_style('jquery-ui-nggv', $this->pluginUrl.'css/jquery-ui/jquery-ui-1.10.3.custom.min.css');
		}
		
		/**
		 * Hook used: admin_menu
		 * Create menu option in WP. Considered adding under the NGG main option, but not sure of plugin load order.
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function adminMenu() {
			add_menu_page('NextGEN Gallery Voting', 'NGG Voting', 'manage_options', $this->slug, array(&$this, 'settings'));
			add_submenu_page($this->slug, 'NextGEN Gallery Voting Defaults', 'Settings', 'manage_options', $this->slug, array(&$this, 'settings'));
			add_submenu_page($this->slug, 'NextGEN Gallery Top Voted', 'Top Voted', 'manage_options', $this->slug.'-top-voted', array(&$this, 'topVoted'));
			do_action('nggv_admin_menu', $this->slug);
		}
		
		/**
		 * Edit default options and settings.
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function settings() {
			if(isset($_GET['action']) && $_GET['action'] == 'admin-vote-info' && isset($_GET['gid']) && $_GET['gid']) {
				$options = $this->getVotingOptions($_GET['gid']);
				$results = $this->getVotingResults($_GET['gid']);
				
				echo '<table style="width:100%;">';
				echo '<thead>';
				echo '<tr>';
				echo '<td><strong>Date</strong></td>';
				echo '<td>';
				echo '<strong>Vote</strong><br />';
				if($options->voting_type == 1) {
					echo '<em>(out 10)</em>';
				}else if($options->voting_type == 2){
					echo '<em>/ 5 stars)</em>';
				}
				echo '</td>';
				echo '<td><strong>User Name</strong><br ><em>(if logged in)</em></td>';
				echo '<td><strong>IP</strong></td>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				
				$cnt = 0;
				foreach ((array)$results['list'] as $key=>$val) {
					$bgcol = $cnt % 2 == 0 ? "" : "#DFDFDF";
					echo '<tr style="background-color:'.$bgcol.'">';
					echo '<td>'.$val->dateadded.'</td>';
					if($options->voting_type == 3) {
						echo '<td>'.($val->vote == 100 ? 'Like' : 'Dislike').'</td>';
					}else if($options->voting_type == 2){
						echo '<td>'.round($val->vote/20, 2).'</td>';
					}else if($options->voting_type == 1) {
						echo '<td>'.round($val->vote/10, 2).'</td>';
					}
					do_action('nggv_gallery_results_detail_vot_col', $options, $val);
					$user_info = $val->user_id ? get_userdata($val->user_id) : array();
					
					echo '<td>'.(isset($user_info->data->display_name) && $user_info->data->display_name ? $user_info->data->display_name : $val->user_id).'</td>';
					echo '<td>'.$val->ip.'</td>';
					echo '</tr>';
					
					$cnt++;
				}
				echo '</tbody>';
				echo '</table>';
				
				exit;
			}else if(isset($_GET['action']) && $_GET['action'] == 'admin-vote-info' && isset($_GET['pid']) && $_GET['pid']) {
				$options = $this->getImageVotingOptions($_GET['pid'], $_GET['criteria_id']);
				$results = $this->getImageVotingResults(array('pid'=>$_GET['pid'], 'criteria_id'=>$_GET['criteria_id']));
				
				echo '<table style="width:100%;">';
				echo '<thead>';
				echo '<tr>';
				echo '<td><strong>Date</strong></td>';
				echo '<td>';
				echo '<strong>Vote</strong><br />';
				if($options->voting_type == 1) {
					echo '<em>(out 10)</em>';
				}else if($options->voting_type == 2){
					echo '<em>/ 5 stars)</em>';
				}
				echo '</td>';
				echo '<td><strong>User Name</strong><br ><em>(if logged in)</em></td>';
				echo '<td><strong>IP</strong></td>';
				echo '<td></td>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				
				$cnt = 0;
				foreach ((array)$results['list'] as $key=>$val) {
					$bgcol = $cnt % 2 == 0 ? "" : "#DFDFDF";
					echo '<tr style="background-color:'.$bgcol.'">';
					echo '<td>'.$val->dateadded.'</td>';
					if($options->voting_type == 3) {
						echo '<td>'.($val->vote == 100 ? 'Like' : 'Dislike').'</td>';
					}else if($options->voting_type == 2){
						echo '<td>'.round($val->vote/20, 2).'</td>';
					}else if($options->voting_type == 1){
						echo '<td>'.round($val->vote/10, 2).'</td>';
					}
					do_action('nggv_image_results_detail_vot_col', $options, $val);
					$user_info = $val->user_id ? get_userdata($val->user_id) : array();
					
					echo '<td>'.(isset($user_info->data->display_name) && $user_info->data->display_name ? $user_info->data->display_name : $val->user_id).'</td>';
					echo '<td>'.$val->ip.'</td>';
					echo '<td>';
					echo apply_filters('nggv_top_voted_row_actions', '', $this, $val);
					echo '</td>';
					echo '</tr>';
					
					$cnt++;
				}
				
				echo do_action('nggv_popup_votes_list_bottom', $this, $options);
				
				echo '</tbody>';
				echo '</table>';
				
				exit;
				/*
				echo "var nggv_votes_list = [];";
				foreach ((array)$results["list"] as $key=>$val) {
					$user_info = $val->user_id ? get_userdata($val->user_id) : array();
					echo "
					nggv_votes_list[nggv_votes_list.length] = [];
					nggv_votes_list[nggv_votes_list.length-1][0] = '".$val->vote."';
					nggv_votes_list[nggv_votes_list.length-1][1] = '".$val->dateadded."';
					nggv_votes_list[nggv_votes_list.length-1][2] = '".$val->ip."';
					nggv_votes_list[nggv_votes_list.length-1][3] = [];
					nggv_votes_list[nggv_votes_list.length-1][3][0] = '".$val->user_id."';
					nggv_votes_list[nggv_votes_list.length-1][3][1] = '".$user_info->user_login."';
					";
					}
				*/
			}else if(isset($_GET['action']) && $_GET['action'] == 'clear-image-votes' && $_GET['pid']) {
				if(!isset($_GET['criteria_id'])) {
					echo 'Criteria failed.';
				}
				$deleted = $this->deleteImageVotes($_GET['pid'], $_GET['criteria_id']);
				//force a crappy reload. yay... TODO, make this more reliable
				echo "<script>window.location = 'admin.php?page=nggallery-manage-gallery&mode=edit&gid=".$_GET['gid']."';</script>";
				exit;
			}else{
				if(isset($_POST['nggv']) && $_POST['nggv']) {
					//Gallery
					if(get_option('nggv_gallery_enable') === false) { //bool false means does not exists
						add_option('nggv_gallery_enable', (isset($_POST['nggv']['gallery']['enable']) && $_POST['nggv']['gallery']['enable'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_gallery_enable', (isset($_POST['nggv']['gallery']['enable']) && $_POST['nggv']['gallery']['enable'] ? '1' : '0'));
					}
					if(get_option('nggv_gallery_force_login') === false) { //bool false means does not exists
						add_option('nggv_gallery_force_login', (isset($_POST['nggv']['gallery']['force_login']) && $_POST['nggv']['gallery']['force_login'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_gallery_force_login', (isset($_POST['nggv']['gallery']['force_login']) && $_POST['nggv']['gallery']['force_login'] ? '1' : '0'));
					}
					if(get_option('nggv_gallery_force_once') === false) { //bool false means does not exists
						add_option('nggv_gallery_force_once', (isset($_POST['nggv']['gallery']['force_once']) && $_POST['nggv']['gallery']['force_once'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_gallery_force_once', (isset($_POST['nggv']['gallery']['force_once']) && $_POST['nggv']['gallery']['force_once'] ? '1' : '0'));
					}
					if(get_option('nggv_gallery_user_results') === false) { //bool false means does not exists
						add_option('nggv_gallery_user_results', (isset($_POST['nggv']['gallery']['user_results']) && $_POST['nggv']['gallery']['user_results'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_gallery_user_results', (isset($_POST['nggv']['gallery']['user_results']) && $_POST['nggv']['gallery']['user_results'] ? '1' : '0'));
					}
					if(get_option('nggv_gallery_voting_type') === false) { //bool false means does not exists
						add_option('nggv_gallery_voting_type', $_POST['nggv']['gallery']['voting_type'], null, 'no');
					}else{
						update_option('nggv_gallery_voting_type', $_POST['nggv']['gallery']['voting_type']);
					}
					
					//Images
					if(get_option('nggv_image_enable') === false) { //bool false means does not exists
						add_option('nggv_image_enable', (isset($_POST['nggv']['image']['enable']) && $_POST['nggv']['image']['enable'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_image_enable', (isset($_POST['nggv']['image']['enable']) && $_POST['nggv']['image']['enable'] ? '1' : '0'));
					}
					if(get_option('nggv_image_force_login') === false) { //bool false means does not exists
						add_option('nggv_image_force_login', (isset($_POST['nggv']['image']['force_login']) && $_POST['nggv']['image']['force_login'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_image_force_login', (isset($_POST['nggv']['image']['force_login']) && $_POST['nggv']['image']['force_login'] ? '1' : '0'));
					}
					if(get_option('nggv_image_force_once') === false) { //bool false means does not exists
						add_option('nggv_image_force_once', (isset($_POST['nggv']['image']['force_once']) && $_POST['nggv']['image']['force_once'] <= 2 ? $_POST['nggv']['image']['force_once'] : '0'), null, 'no');
					}else{
						update_option('nggv_image_force_once', (isset($_POST['nggv']['image']['force_once']) && $_POST['nggv']['image']['force_once'] <= 2 ? $_POST['nggv']['image']['force_once'] : '0'));
					}
					if(get_option('nggv_image_user_results') === false) { //bool false means does not exists
						add_option('nggv_image_user_results', (isset($_POST['nggv']['image']['user_results']) && $_POST['nggv']['image']['user_results'] ? '1' : '0'), null, 'no');
					}else{
						update_option('nggv_image_user_results', (isset($_POST['nggv']['image']['user_results']) && $_POST['nggv']['image']['user_results'] ? '1' : '0'));
					}
					if(get_option('nggv_image_voting_type') === false) { //bool false means does not exists
						add_option('nggv_image_voting_type', $_POST['nggv']['image']['voting_type'], null, 'no');
					}else{
						update_option('nggv_image_voting_type', $_POST['nggv']['image']['voting_type']);
					}
					
					do_action('nggv_saving_settings');
				}
				
				$action = $this->adminUrl.$this->slug;
				?>
				<div class="wrap">
					<h2>Welcome to NextGEN Gallery Voting</h2>
					<p>This plugin adds the ability for users to vote on NextGEN Galleries and Images. If you need any help or find any bugs, please create a post on the <a href="http://wordpress.org/support/plugin/nextgen-gallery-voting" target="_blank">plugin's support forum.</a></p>
				
					<h2>Default Options</h2>
					<p>
						Here you can set the default voting options for Galleries and Images.
						Changing these options will not affect any existing Galleries or Images. You can change the settings per image or gallery from the 'Manage Gallery' screen in NextGEN Gallery.
					</p>
					
					<div id="poststuff">
						<form id="" method="POST" action="<?php echo $action; ?>" accept-charset="utf-8" enctype="multipart/form-data">
							<input type="hidden" name="nggv[force]" value="1" /> <!-- this will just force _POST['nggv'] even if all checkboxes are unchecked -->
							<table class="widefat fixed" cellspacing="0">
								<thead>
									<tr>
										<th style="text-align:left;"></td>
										<th style="text-align:center;"><h3>Gallery</h3></th>
										<th style="text-align:center;"><h3>Image</h3></th>
									</tr>
								</thead>
								
								<tr valign="top">
									<th style="">Enable:</th>
									<td style="text-align:center;"><input type="checkbox" name="nggv[gallery][enable]" <?php echo (get_option('nggv_gallery_enable') ? 'checked="checked"' : ''); ?> /></td>
									<td style="text-align:center;"><input type="checkbox" name="nggv[image][enable]" <?php echo (get_option('nggv_image_enable') ? 'checked="checked"' : ''); ?> /></td>
								</tr>
      	
								<tr valign="top">
									<th>Only allow logged in users to vote:</th>
									<td style="text-align:center;"><input type="checkbox" name="nggv[gallery][force_login]" <?php echo (get_option('nggv_gallery_force_login') ? 'checked="checked"' : ''); ?> /></td>
									<td style="text-align:center;"><input type="checkbox" name="nggv[image][force_login]" <?php echo (get_option('nggv_image_force_login') ? 'checked="checked"' : ''); ?> /></td>
								</tr>
      	
								<tr valign="top">
									<th>Limit user to 1 vote:<br ><em><small>IP or userid is used to stop multiple</small></em></th>
									<td style="text-align:center;"><input type="checkbox" name="nggv[gallery][force_once]" <?php echo (get_option('nggv_gallery_force_once') ? 'checked="checked"' : ''); ?> /></td>
									<td style="text-align:center;">
										<input type="radio" name="nggv[image][force_once]" class="nggv-force-once" <?php echo (get_option('nggv_image_force_once') == 0 ? 'checked="checked"' : ''); ?> value="0" /> Unlimited votes<br />
										<input type="radio" name="nggv[image][force_once]" class="nggv-force-once" <?php echo (get_option('nggv_image_force_once') == 1 ? 'checked="checked"' : ''); ?> value="1" /> One per image<br />
										<input type="radio" name="nggv[image][force_once]" class="nggv-force-once" <?php echo (get_option('nggv_image_force_once') == 2 ? 'checked="checked"' : ''); ?> value="2" /> One per gallery image is in<br />
										<?php echo apply_filters('nggv_settings_table_image_number_votes', ''); ?>
									</td>
								</tr>
      	
								<tr valign="top">
									<th>Allow users to see results:</th>
									<td style="text-align:center;"><input type="checkbox" name="nggv[gallery][user_results]" <?php echo (get_option('nggv_gallery_user_results') ? 'checked="checked"' : ''); ?> /></td>
									<td style="text-align:center;"><input type="checkbox" name="nggv[image][user_results]" <?php echo (get_option('nggv_image_user_results') ? 'checked="checked"' : ''); ?> /></td>
								</tr>
      	
								<tr valign="top">
									<th>Rating Type:</th>
									<td style="text-align:center;">
									<?php /* The order is weird, because 'drop down' was first option created. But 'star' is more awesomer, so I'm making te decision to push it up :) */ ?>
										<select name="nggv[gallery][voting_type]">
											<?php
											foreach ((array)$this->types as $key=>$val) {
												if($val['gallery']) {
													echo '<option value="'.$key.'" '.(get_option('nggv_gallery_voting_type') == $key ? 'selected="selected"' : '').'>'.$val['name'].'</option>';
												}
											}
											?>
										</select>
									</td>
									<td style="text-align:center;">
										<select name="nggv[image][voting_type]">
											<?php
											foreach ((array)$this->types as $key=>$val) {
												if($val['image']) {
													echo '<option value="'.$key.'" '.(get_option('nggv_image_voting_type') == $key ? 'selected="selected"' : '').'>'.$val['name'].'</option>';
												}
											}
											?>
										</select>
      	
									</td>
								</tr>
      	
								<?php echo apply_filters('nggv_settings_table', ''); ?>
								
								<tr>
									<td colspan="2">
										<div class="submit"><input class="button-primary" type="submit" value="Save Defaults"/>
										</div>
									</td>
								</tr>
							</table>
						</form>
					</div>
				</div>
				<?php
			}
		}
		
		/**
		 * Filter images by rating
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function topVoted() {
			global $nggdb, $wpdb;
			$gallerylist = $nggdb->find_all_galleries('gid', 'asc');

			$_GET['nggv']['limit'] = isset($_GET['nggv']['limit']) && is_numeric($_GET['nggv']['limit']) ? $_GET['nggv']['limit'] : 25;
			$_GET['nggv']['order'] = isset($_GET['nggv']['order']) && $_GET['nggv']['order'] ? $_GET['nggv']['order'] : 'DESC';
			//premium feature, but add it here to keep consistency (wont affect anything if premium not installed)
			$_GET['nggv']['export_type'] = isset($_GET['nggv']['export_type']) && $_GET['nggv']['export_type'] ? $_GET['nggv']['export_type'] : 'avg';
			
			$listWhere = '';
			
			$qry = 'SELECT ';
			$qry .= 'v.pid, v.criteria_id, SUM(v.vote) AS total, AVG(v.vote) AS avg, MIN(v.vote) AS min, MAX(v.vote) AS max, COUNT(v.vote) AS num';
			$qry .= ', p.*';
			$qry .= ' FROM '.$wpdb->prefix.'nggv_votes AS v LEFT JOIN '.$wpdb->prefix.'ngg_pictures AS p ON v.pid = p.pid';
			$qry .= ' WHERE';
			$qry .= ' v.pid > 0';
			if(isset($_GET['nggv']['gallery']) && $_GET['nggv']['gallery']) {
				$qry .= ' AND p.galleryid = '.$wpdb->escape($_GET['nggv']['gallery']);
			}
			if(isset($_GET['nggv']['date_from']) && $_GET['nggv']['date_from']) {
				$qry .= ' AND v.dateadded >= "'.$wpdb->escape($_GET['nggv']['date_from']).' 00:00:00"';
				$listWhere .= $listWhere ? ' AND ' : '';
				$listWhere .= 'dateadded >= "'.$wpdb->escape($_GET['nggv']['date_from']).' 00:00:00"';
			}
			if(isset($_GET['nggv']['date_to']) && $_GET['nggv']['date_to']) {
				$qry .= ' AND v.dateadded <= "'.$wpdb->escape($_GET['nggv']['date_to']).' 23:59:59"';
				$listWhere .= $listWhere ? ' AND ' : '';
				$listWhere .= 'dateadded <= "'.$wpdb->escape($_GET['nggv']['date_to']).' 23:59:59"';
			}
						
			$qry .= ' GROUP BY v.pid, v.criteria_id';
			$qry .= ' ORDER BY avg '.$_GET['nggv']['order'].', num '.$_GET['nggv']['order'];
			if($_GET['nggv']['limit']) {
				$qry .= ' LIMIT 0, '.$_GET['nggv']['limit'];
			}
			
			$list = $wpdb->get_results($qry);
			foreach ((array)$list as $key=>$val) {
				$tmp = $this->getImageVotingResults(array('pid'=>$val->pid, 'criteria_id'=>$val->criteria_id), array('list'=>true), $listWhere);
				$list[$key]->details = $tmp['list'];
			}
			$list = apply_filters('nggv_top_voted_results', $list);
			
			if (isset($_GET['noheader'])) {require_once(ABSPATH.'wp-admin/admin-header.php');} //replace the header if we need output...

			?>
						
			<div class="wrap">
				<h2>Top Rated Images</h2>
			
				<div id="poststuff">
					<form id="" method="GET" action="" accept-charset="utf-8">
						<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
						<input type="hidden" name="noheader" value="1" />
						<div class="postbox">
							<h3>Filter</h3>
							<table class="form-table">
								<tr>
									<th>Limit <br /><small>(set to 0 for no limit)</small></th>
									<td>
										<input type="text" name="nggv[limit]" value="<?php echo $_GET['nggv']['limit'] ?>" />
									</td>
									
									<th style="width:20%;">Order</th>
									<td style="width:30%;">
										<select name="nggv[order]">
											<option value="desc" <?php echo ($_GET['nggv']['order'] == 'desc' ? 'selected' : ''); ?>>Highest to Lowest</option>
											<option value="asc" <?php echo ($_GET['nggv']['order'] == 'asc' ? 'selected' : ''); ?>>Lowest to Highest</option>
										</select>
									</td>
								</tr>
								
								<tr>
									<th>From Date</th>
									<td><input type="text" id="nggv_date_from" name="nggv[date_from]" value="<?php echo (isset($_GET['nggv']['date_from']) ? $_GET['nggv']['date_from'] : '') ?>" /></td>
									<th>To Date</th>
									<td><input type="text" id="nggv_date_to" name="nggv[date_to]" value="<?php echo (isset($_GET['nggv']['date_to']) ? $_GET['nggv']['date_to'] : '') ?>" /></td>
								</tr>
								
								<?php
								$selGallery = isset($_GET['nggv']['gallery']) ? $_GET['nggv']['gallery'] : 0;
								?>
								<tr>
									<th>Gallery</th>
									<td>
									<select name="nggv[gallery]">
											<option value="0">All</option>
											<?php foreach ((array)$gallerylist as $key=>$val) { ?>
												<option value="<?php echo $val->gid ?>" <?php echo $val->gid == $selGallery ? 'selected="selected"' : '' ?>><?php echo $val->title ?></option>
											<?php } ?>
										</select>
									</td>
								</tr>
								
								<tr>
									<td colspan=2>
										<input class="button-primary" name="nggv[button_filter]" type="submit" value="Filter Images" />
									</td>
									<td colspan=2>
										<?php do_action('nggv_top_voted_form_footer', $_GET); ?>
									</td>
								</tr>
							</table>
						</div>
					</form>
				</div>

				<?php if($list && !class_exists('nggVotingPremium')) { ?>
					<div class="updated below-h2">
						Look at the people voing for your images! Have you returned the favour by <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/nextgen-gallery-voting">rating NGG Voting</a>?<br />
						Maybe you're even more awesome, and might consider <a target="_blank" href="http://shauno.co.za/donate/">donating</a>?
					</div>
				<?php } ?>
				
				<table cellspacing="0" class="wp-list-table widefat fixed">
  				<thead>
  					<tr>
  						<th style="width:30px;">pid</th>
  						<th>
  							Criteria
  							<?php if(!class_exists('nggVotingePremium')) { ?>
  								<br /><small>(Premium Feature)</small>
  							<?php } ?>
  						</th>
  						<th>Gallery Name</th>
  						<th>Filename</th>
  						<th>Avg / 10</th>
  						<th>Max / 10</th>
  						<th>Min / 10</th>
  						<th>Number Votes</th>
  						<th></th>
  					</tr>
  				</thead>
  				<?php if($list) { ?>
  					<tbody>
  						<?php if($list) { ?>
  							<?php $cnt = 0; ?>
  							<?php foreach ($list as $key=>$val) { ?>
  								<?php $image = nggdb::find_image($val->pid); ?>
									<tr <?php echo $cnt % 2 == 0 ? 'class="alternate"' : '' ?>>
										<td><?php echo $val->pid ?></td>
										<td>
											<?php
											if($val->criteria_id) {
												if(class_exists('nggVotingPremium')) {
													if($c = nggVotingPremium::getCriteria(array('id'=>$val->criteria_id))) {
														echo $c->name;
													}else{
														echo 'Critera has been deleted';
													}
												}
											}else{
												echo 'Overall';
											}
											?>
										</td>
										<td><?php echo $image->title; ?></td>
										<td><?php echo $image->filename; ?></td>
										<td><?php echo round($val->avg / 10, 2) ?></td>
										<td><?php echo round($val->max / 10, 2) ?></td>
										<td><?php echo round($val->min / 10, 2) ?></td>
										<td><?php echo $val->num ?> <a href="#" class="nggv-top-vote-item">(View Votes)</a></td>
										<td><img src="<?php echo $image->thumbURL; ?>" /></td>
									</tr>
									<tr style="display:none;">
										<td colspan="9">
											<table cellspacing="0" class="wp-list-table widefat fixed">
  											<thead>
  												<tr>
  													<th>Date</th>
  													<th>Username</th>
  													<th>IP</th>
  													<th>Vote / 10</th>
  												</tr>
  											</thead>
  											<?php foreach ((array)$val->details as $vote) { ?>
  												<?php $user_info = $vote->user_id ? get_userdata($vote->user_id) : array(); ?>
													<tr>
														<td><?php echo $vote->dateadded ?></td>
														<td><?php echo (isset($user_info->data->display_name) && $user_info->data->display_name ? $user_info->data->display_name : $vote->user_id) ?></td>
														<td><?php echo $vote->ip ?></td>
														<td><?php echo $vote->vote / 10 ?></td>
													</tr>
												<?php } ?>
  										</table>

										</td>
									</tr>
									<?php $cnt++; ?>
								<?php } ?>
  						<?php }else{ ?>
  							<tr>
  								<td colspan="4">No records found. <a href="<?php echo $this->pluginUrl; ?>page=sf-gallery-add">Click here</a> to add your first gallery.</td>
  							</tr>
  						<?php } ?>
  					</tbody>
  				<?php }else{ ?>
  					<td colspan=6>No results found</td>
  				<?php } ?>
  			</table>

			</div>
			<?php
		}
		
		//Image Voting Functions
		/**
		 * Hook used: ngg_manage_images_columns
		 * Add a custom field to the images field list.  This give us a place to add the voting options for each image.
		 * @param array $gallery_columns The array of current fields
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return array $gallery_columns with an added field
		 */
		function addImageVoteOptionsCol($gallery_columns) {
			//this method is only called once, and its the admin dashboard, so this will work (shhhhh)
			echo '<style>
			.nggv-tab-list, .nggv-tab-list li {
				list-style: none;
				margin: 0;
				padding: 0;
			}
			
			.nggv-tab-list li {
				display: inline;
				margin-right: 2px;
				border: 1px solid black;
				padding: 3px;
				background-color: #e3e3e3;
			}
			
			.nggv-tab-list li.active {
				border-bottom: 1px solid transparent;
				background-color: transparent;
				font-size: 14px;
			}
			
			.nggv-tab-content {
				margin-top: 2px;
				display: none;
			}
			
			.nggv-tab-content.active {
				display:block;
			}
			</style>';
			
			$gallery_columns['nggv_image_vote_options'] = 'Voting Options';
			return $gallery_columns;
		}
		
		/**
		 * Add the voing options to each image
		 * @param string $gallery_column_key The key value of the 'custom' fields added by addImageVoteOptionsCol()
		 * @param int $pid The NGG image database id
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function addImageVotingOptions($gallery_column_key, $pid) {
			$uri = $_SERVER["REQUEST_URI"];
			$info = parse_url($uri);
			$dirName = plugin_basename(dirname(__FILE__));
			$popup = $info["path"]."?page=".$dirName."/".basename(__FILE__)."&action=get-votes-list";
			
			if($gallery_column_key == 'nggv_image_vote_options') { //this method is called for every column, so we check we have the right column
				$criteria = $this->getImageCriteria();
				
				if(count($criteria) > 1) { //if only 1 criteria, don't confuse the user with a tab. sheesh
					echo '<div class="nggv-voting-options">';
					echo '<ul class="nggv-tab-list">';
					foreach ((array)$criteria as $key=>$val) {
						echo '<li class="'.(!$key ? 'active' : '').'">';
						echo '<a href="#">';
						echo $val->name;
						echo '</a>';
						echo '</li>';
					}
					echo '<ul>';
				}
				
				foreach ((array)$criteria as $key=>$val) {
					$opts = $this->getImageVotingOptions($pid, $val->id);
					echo '<div class="nggv-tab-content '.(!$key ? 'active' : '').'">';
					if(count($criteria) > 1) { //don't need to show title label if only 1 criteria to show
						echo '<h4>';
						echo $val->name;
						if($val->id) {
							echo ' (ID: '.$val->id.')';
						}
						echo '</h4>';
					}
					echo '<table width="100%" class="nggv-image-voting-options">';
					echo '<tr><td width="1px"><input type="checkbox" name="nggv_image['.$pid.']['.$val->id.'][enable]" value=1 '.(isset($opts->enable) && $opts->enable ? 'checked' : '').' /></td><td>Enable for image</td></tr>';
					echo '<tr><td width="1px"><input type="checkbox" name="nggv_image['.$pid.']['.$val->id.'][force_login]" value=1 '.(isset($opts->force_login) && $opts->force_login ? 'checked' : '').' /></td><td>Only allow logged in users</td></tr>';
					echo '<tr class="nggv-force-once"><td width="1px"><input type="radio" name="nggv_image['.$pid.']['.$val->id.'][force_once]" value=3 '.(empty($opts->force_once) ? 'checked' : '').' /></td><td>Unlimited votes for this image</td></tr>';
					echo '<tr class="nggv-force-once"><td width="1px"><input type="radio" name="nggv_image['.$pid.']['.$val->id.'][force_once]" value=1 '.(isset($opts->force_once) && $opts->force_once == 1 ? 'checked' : '').' /></td><td>Only allow 1 vote per person for this image</td></tr>';
					echo '<tr class="nggv-force-once"><td width="1px"><input type="radio" name="nggv_image['.$pid.']['.$val->id.'][force_once]" value=2 '.(isset($opts->force_once) && $opts->force_once == 2 ? 'checked' : '').' /></td><td>Only allow 1 vote per person for this gallery</td></tr>';
					
					echo apply_filters('nggv_manage_gallery_image_number_votes', '', $opts);

					echo '<tr><td width="1px"><input type="checkbox" name="nggv_image['.$pid.']['.$val->id.'][user_results]" value=1 '.(isset($opts->user_results) && $opts->user_results ? 'checked' : '').' /></td><td>Allow users to see results</td></tr>';
					
					echo '<tr><td colspan=2>';
					echo 'Rating Type: <select name="nggv_image['.$pid.']['.$val->id.'][voting_type]">';
					foreach ((array)$this->types as $tKey=>$tVal) {
						if($tVal['image']) {
							echo '<option value="'.$tKey.'" '.(isset($opts->voting_type) && $opts->voting_type == $tKey ? 'selected="selected"' : '').'>'.$tVal['name'].'</option>';
						}
					}
					echo '</select>';
					echo '</td></tr>';
					echo '</table>';
					
					if(isset($opts->voting_type) && $opts->voting_type == 2) {
						$results = $this->getImageVotingResults(array('pid'=>$pid, 'criteria_id'=>$val->id), array('avg'=>true, 'num'=>true));
						echo 'Current Avg: '.round(($results['avg'] / 20), 1).' / 5 stars<br />';
						echo '<a href="#" class="nggv_more_results_image" id="nggv_more_results_image_'.$pid.'" data-criteria_id="'.$val->id.'">('.($results['number'] ? $results['number'] : '0').' votes cast)</a>';
					}else if(isset($opts->voting_type) && $opts->voting_type == 3) {
						$results = $this->getImageVotingResults(array('pid'=>$pid, 'criteria_id'=>$val->id), array('likes'=>true, 'dislikes'=>true, 'num'=>true));
						echo 'Current Votes: ';
						echo $results['likes'].' ';
						echo $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
						echo $results['dislikes'].' ';
						echo $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
						echo '</a><br />';
						echo '<a href="#" class="nggv_more_results_image" id="nggv_more_results_image_'.$pid.'" data-criteria_id="'.$val->id.'">('.($results['number'] ? $results['number'] : '0').' votes cast)</a>';
					}else if(isset($opts->voting_type) && $opts->voting_type == 1){
						$results = $this->getImageVotingResults(array('pid'=>$pid, 'criteria_id'=>$val->id), array('avg'=>true, 'num'=>true));
						echo 'Current Avg: '.round(($results['avg'] / 10), 1).' / 10<br />';
						echo '<a href="#" class="nggv_more_results_image" id="nggv_more_results_image_'.$pid.'" data-criteria_id="'.$val->id.'">('.($results['number'] ? $results['number'] : '0').' votes cast)</a>';
					}
					echo do_action('nggv_image_options_bottom', $this, $opts);
					echo '<br />[&nbsp;<a class="nggv_clear_image_results" href="'.$this->adminUrl.$this->slug.'&action=clear-image-votes&pid='.$pid.'&gid='.$_GET["gid"].'&criteria_id='.$val->id.'">Clear Votes</a>&nbsp;]';
					echo '</div>';
				}
				echo '</div>'; /* /.nggv-voting-options */
			}
		}

		/**
		 * Hook: ngg_update_gallery
		 * Save the options for a gallery and/or images
		 * @param int $gid The NextGEN Gallery ID
		 * @param array $post the _POST array from the gallery save form. The following fields for our options
		 *  bool (int 1/0) post["nggv"]["enable"] : Enable voting for the gallery
		 *  bool (int 1/0) post["nggv"]["force_login"] : Force the user to login to cast vote
		 *  bool (int 1/0) post["nggv"]["force_once"] : Only allow a user to vote once
		 *  bool (int 1/0) post["nggv"]["user_results"] : If users see results
		 *  bool (int 1/0) post["nggv_image"][image ID]["enable"] : Enable voting for the image
		 *  bool (int 1/0) post["nggv_image"][image ID]["force_login"] : Only allow a user to vote once
		 *  integer (0, 1, 2) post["nggv_image"][image ID]["force_once"] : Only allow a user to vote once(1), Only allow user to vote once per image in this gallery(2)
		 *  bool (int 1/0) post["nggv_image"][image ID]["user_results"] : If users see results
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function onGalleryUpdate($gid, $post) {
			global $wpdb;
			
			if(isset($post['nggvg']) && $post['nggvg']) { //gallery options
				$enable = isset($post['nggvg']['enable']) && $post['nggvg']['enable'] ? '1' : '0';
				$login = isset($post['nggvg']['force_login']) && $post['nggvg']['force_login'] ? '1' : '0';
				$once = isset($post['nggvg']['force_once']) && $post['nggvg']['force_once'] ? '1' : '0';
				$user_results = isset($post['nggvg']['user_results']) && $post['nggvg']['user_results'] ? '1' : '0';
				$voting_type = isset($post['nggvg']['voting_type']) && is_numeric($post['nggvg']['voting_type']) ? $post['nggvg']['voting_type'] : '1';
				
				//TODO 2.0 Consider APIing these queries, using new wpdb insert/update methods
				if($this->getVotingOptions($gid)) {
					$wpdb->query('UPDATE '.$wpdb->prefix.'nggv_settings SET force_login = "'.$login.'", force_once = "'.$once.'", user_results = "'.$user_results.'", enable = "'.$enable.'", voting_type = "'.$voting_type.'" WHERE gid = "'.$wpdb->escape($gid).'"');
				}else{
					$wpdb->query('INSERT INTO '.$wpdb->prefix.'nggv_settings (id, gid, enable, force_login, force_once, user_results, voting_type) VALUES (null, "'.$wpdb->escape($gid).'", "'.$enable.'", "'.$login.'", "'.$once.'", "'.$user_results.'", "'.$voting_type.'")');
				}
			}
			
			if(isset($post['nggv_image']) && $post['nggv_image']) { //image options
				foreach ((array)$post['nggv_image'] as $pid=>$tmp) {
					foreach ((array)$tmp as $criteriaId=>$val) {
						$enable = isset($val['enable']) && $wpdb->escape($val['enable']) ? '1' : '0';
						$login = isset($val['force_login']) && $wpdb->escape($val['force_login']) ? '1' : '0';
						$once = isset($val['force_once']) && $wpdb->escape($val['force_once']) <= 2 ? $wpdb->escape($val['force_once']) : '0';
						$user_results = isset($val['user_results']) && $wpdb->escape($val['user_results']) ? '1' : '0';
						$voting_type = isset($val['voting_type']) && is_numeric($val['voting_type']) ? $val['voting_type'] : 1;
						$criteriaId = is_numeric($criteriaId) ? $criteriaId : 0;
						
						
						//TODO 2.0 Consider APIing these queries, using new wpdb insert/update methods
						if($this->getImageVotingOptions($pid, $criteriaId)) {
							$wpdb->query('UPDATE '.$wpdb->prefix.'nggv_settings SET criteria_id = "'.$criteriaId.'", force_login = "'.$login.'", force_once = "'.$once.'", user_results = "'.$user_results.'", enable = "'.$enable.'", voting_type = "'.$voting_type.'" WHERE pid = "'.$wpdb->escape($pid).'" AND criteria_id = "'.$wpdb->escape($criteriaId).'"');
						}else{
							$wpdb->query('INSERT INTO '.$wpdb->prefix.'nggv_settings (id, pid, criteria_id, enable, force_login, force_once, user_results, voting_type) VALUES (null, "'.$wpdb->escape($pid).'", "'.$criteriaId.'", "'.$enable.'", "'.$login.'", "'.$once.'", "'.$user_results.'", "'.$voting_type.'")');
						}
					}
				}
			}
			
			echo do_action('nggv_ongallery_update', $gid, $post);
		}
		
		/**
		 * Hook: ngg_added_new_image
		 * Add the image voting options for a new image (pulled from the defaaults
		 * @param array $image the new image details
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function addNewImage($image) {
			if(defined('NEXTGEN_GALLERY_PLUGIN_VERSION') && NEXTGEN_GALLERY_PLUGIN_VERSION >= 2) {
				if($image->id() && $image->__get('galleryid')) {
					$pid = $image->id();
					$post = array();
					$post['nggv_image'] = array();
					$post['nggv_image'][$pid] = array();
					
					$criteria = $this->getImageCriteria();
					foreach ((array)$criteria as $key=>$val) {
						$post['nggv_image'][$pid][$val->id] = array();
						$post['nggv_image'][$pid][$val->id]['enable'] = get_option('nggv_image_enable');
						$post['nggv_image'][$pid][$val->id]['force_login'] = get_option('nggv_image_force_login');
						$post['nggv_image'][$pid][$val->id]['force_once'] = get_option('nggv_image_force_once');
						$post['nggv_image'][$pid][$val->id]['user_results'] = get_option('nggv_image_user_results');
						$post['nggv_image'][$pid][$val->id]['voting_type'] = get_option('nggv_image_voting_type');
					}
					$this->onGalleryUpdate($image->__get('galleryid'), $post);
				}
			}else{
				if($image['id']) {
					$post = array();
					$post['nggv_image'] = array();
					$post['nggv_image'][$image['id']] = array();
					
					$criteria = $this->getImageCriteria();
					foreach ((array)$criteria as $key=>$val) {
						$post['nggv_image'][$image['id']][$val->id] = array();
						$post['nggv_image'][$image['id']][$val->id]['enable'] = get_option('nggv_image_enable');
						$post['nggv_image'][$image['id']][$val->id]['force_login'] = get_option('nggv_image_force_login');
						$post['nggv_image'][$image['id']][$val->id]['force_once'] = get_option('nggv_image_force_once');
						$post['nggv_image'][$image['id']][$val->id]['user_results'] = get_option('nggv_image_user_results');
						$post['nggv_image'][$image['id']][$val->id]['voting_type'] = get_option('nggv_image_voting_type');
					}
					
					$this->onGalleryUpdate($image['galleryID'], $post);
				}
			}
		}
		
		//Gallery Voting Functions
		/**
		 * Hook: ngg_manage_gallery_settings
		 * Add the voing options to the gallery
		 * @param int $gid The NGG gallery database id
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function addGalleryVotingOptions($gid) {
			$options = $this->getVotingOptions($gid);
			$results = $this->getVotingResults($gid, array("avg"=>true, "num"=>true, "likes"=>true, "dislikes"=>true));
			
			echo '<tr>';
			echo '<th>Gallery Voting Options:</th>';
			echo '<th colspan="3">';
				echo '<input type="checkbox" name="nggvg[enable]" value=1 '.(isset($options->enable) && $options->enable ? 'checked' : '').' /> Enable voting for this gallery<br />';
				echo '<input type="checkbox" name="nggvg[force_login]" value=1 '.(isset($options->force_login) && $options->force_login ? 'checked' : '').' /> Only allow logged in users to vote<br />';
				echo '<input type="checkbox" name="nggvg[force_once]" value=1 '.(isset($options->force_once) && $options->force_once ? 'checked' : '').' /> Only allow 1 vote per person (IP or userid is used to stop multiple)<br />';
				echo '<input type="checkbox" name="nggvg[user_results]" value=1 '.(isset($options->user_results) && $options->user_results ? 'checked' : '').' /> Allow users to see results<br />';
				echo 'Rating Type: <select name="nggvg[voting_type]">';
				foreach ((array)$this->types as $key=>$val) {
					if($val['gallery']) {
						echo '<option value="'.$key.'" '.(isset($options->voting_type) && $options->voting_type == $key ? 'selected="selected"' : '').'>'.$val['name'].'</option>';
					}
				}
				echo '</select><br />';
				
				echo '<script>
					var nggv_ajax_url = "'.$this->adminUrl.$this->slug.'&action=admin-vote-info&noheader=1";
					var nggv_gid = "'.$gid.'";
				</script>';
				echo $this->includeJs($this->pluginUrl.'js/admin_gallery.js');
				if(isset($options->voting_type) && $options->voting_type == 2) { //star
					echo 'Currently: '.($results['avg'] ? round($results['avg'] / 20, 2) : '0').' / 5 stars';
					echo '<a href="#" id="nggv_more_results">('.($results['number'] ? $results['number'] : '0').' votes cast)</a>';
				}else if(isset($options->voting_type) && $options->voting_type == 3) { //likes/dislikes
					echo ($results['likes'] ? $results['likes'] : '0').' ';
					echo $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
					echo ($results['dislikes'] ? $results['dislikes'] : '0').' ';
					echo $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
					echo ' <a href="#" id="nggv_more_results">('.($results['number'] ? $results['number'] : '0').' votes cast)</a>';
				}else if(isset($options->voting_type) && $options->voting_type == 1) {
					echo ($results['avg'] ? round(($results['avg'] / 10), 2) : '0').' / 10 ';
					echo '<a href="#" id="nggv_more_results">('.($results['number'] ? $results['number'] : '0').' votes cast)</a>';
				}
				echo do_action('nggv_gallery_options_bottom', $options, $results);
			echo '</th>';
			echo '</tr>';
			
			//the popup window for results
			echo '<div id="nggvShowList" style="display:none;">';
			echo '<span style="float:right;" width: 100px; height: 40px; border:>';
			echo '<a href="#" id="nggv_more_results_close">Close Window</a>';
			echo '</span>';
			
			echo '<div style="clear:both;"></div>';
			echo '<div id="nggvShowList_content">';
			echo '<img src="'.$this->pluginUrl.'/images/loading.gif" />';
			echo '</div>';
			echo '</div>';
		}
		
		/**
		 * Hook: ngg_add_new_gallery_form
		 * Adds the default voting options for a new gallery.  Can be tweaked for the specif gallery without affecting the defaults
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return void
		 */
		function newGalleryForm() {
			?>
			<tr valign="top">
			<th scope="row">Gallery Voting Options:<br /><em>(Pre-set from <a href="<?php echo $this->adminUrl.$this->slug ?>">here</a>)</em></th> 
			<td>
			<input type="checkbox" name="nggvg[gallery][enable]" <?php echo (get_option('nggv_gallery_enable') ? 'checked="checked"' : ''); ?> />
			Enable<br />
			
			<input type="checkbox" name="nggvg[gallery][force_login]" <?php echo (get_option('nggv_gallery_force_login') ? 'checked="checked"' : ''); ?> />
			Only allow logged in users to vote<br />
			
			<input type="checkbox" name="nggvg[gallery][force_once]" <?php echo (get_option('nggv_gallery_force_once') ? 'checked="checked"' : ''); ?> />
			Only allow 1 vote per person <em>(IP or userid is used to stop multiple)</em><br />
			
			<input type="checkbox" name="nggvg[gallery][user_results]" <?php echo (get_option('nggv_gallery_user_results') ? 'checked="checked"' : ''); ?> />
			Allow users to see results<br />
			
			Rating Type:
			<select name="nggvg[gallery][voting_type]">
			<?php
			foreach ((array)$this->types as $key=>$val) {
				if($val['gallery']) {
					echo '<option value="'.$key.'" '.(get_option('nggv_gallery_voting_type') == $key ? 'selected="selected"' : '').'>'.$val['name'].'</option>';
				}
			}
			?>
			</select>
			</td>
			</tr>
			<?php
		}
		
		/**
		 * Hook: ngg_created_new_gallery
		 * Saves the voting options for the new gallery
		 * @param int $gid the gallery id
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return voide
		 */
		function onCreateGallery($gid) {
			if($gid) {
				$post = array();
				$post['nggvg'] = $_POST['nggvg']['gallery'];
				$this->onGalleryUpdate($gid, $post, true);
			}
		}

	// }
	
	// Front End Functions {
		function wpInit() {
			//I have added this code to the init, so the scripts are loaded correctly. The trade off is they load every page. Meh, seems better than users getting problems
			//(calling this->includeJs() because I haven't removed calls in body code. this prevents them actually being included at that point)
			$this->includeJs($this->pluginUrl.'js/ajaxify-stars.js');	//ajaxify voting, from v1.7
			$this->includeJs($this->pluginUrl.'js/ajaxify-likes.js');
			$this->includeCss($this->pluginUrl.'css/star_rating.css');
			
			wp_enqueue_script('nggv-stars', $this->pluginUrl.'js/ajaxify-stars.js', array('jquery'));
			wp_enqueue_script('nggv-like', $this->pluginUrl.'js/ajaxify-likes.js', array('jquery'));
			wp_enqueue_style('nggv-stars-css', $this->pluginUrl.'css/star_rating.css');
			
			if((isset($_GET['nggv_pid']) && $_GET['nggv_pid']) || isset($_POST['nggv']['vote_pid_id']) && $_POST['nggv']['vote_pid_id']) {
				if(isset($_GET['nggv_pid'])) {
					if(!isset($_GET['nggv_criteria_id'])) {
						$_GET['nggv_criteria_id'] = 0;
					}
					$options = $this->getImageVotingOptions($_GET['nggv_pid'], $_GET['nggv_criteria_id']);
				}else if(isset($_POST['nggv']['vote_pid_id'])) {
					if(!isset( $_POST['nggv']['vote_criteria_id'])) {
						 $_POST['nggv']['vote_criteria_id'] = 0;
					}
					$options = $this->getImageVotingOptions($_POST['nggv']['vote_pid_id'], $_POST['nggv']['vote_criteria_id']);
				}
				$voteFuncs = $this->types[$options->voting_type]['imageCallback'];
				$votedOrErr = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['catch']), array($this, $options));
				
				//store in class var, so we have it for this page execution
				$this->initCatchVote[$options->pid][$options->criteria_id] = array(
					'pid'=>$options->pid,
					'criteria_id'=>$options->criteria_id,
					'result'=>$votedOrErr,
					'dateadded'=>date('Y-m-d H:i:s', time())
					);
				
				do_action('nggv_catch_vote', $options->pid, $options->criteria_id, $this->initCatchVote);
				
				if($_GET['ajaxify']) { //catch the ajax vote
					$form = $this->getImageVoteMarkup($options);

					//echo '<!-- NGGV START AJAX RESPONSE -->';
					//echo '<script>';
					echo 'var nggv_ajax_ver = 2;'; //new in 2.6.1 to help js minification messing up ajac voting
					echo 'var nggv_js = {};';
					echo 'nggv_js.options = {};';
					echo 'nggv_js.saved = '.($votedOrErr === true ? '1' : '0').';';
					echo 'nggv_js.msg = "'.addslashes($votedOrErr).'";';
					echo 'nggv_js.voting_form = "'.addslashes($form['form']).'";';
					//echo '<script><!-- NGGV END AJAX RESPONSE -->';
					exit;
				}
			}
			
			do_action('nggv_wp_init_bottom', $this);
		}
		
		function getImageVoteMarkup($options) {
			$pid = $options->pid;
			$criteriaId = $options->criteria_id;
			$voteFuncs = $this->types[$options->voting_type]['imageCallback'];
			
			//is there a vote happing for this pid right now?
			$votedOrErr = isset($this->initCatchVote[$pid][$criteriaId]['result']) ? $this->initCatchVote[$pid][$criteriaId]['result'] : '';
			
			$form = array();
			if((($canVote = $this->canVoteImage($pid, $criteriaId)) === true) && !$votedOrErr) { //they can vote, show the form
				//$return = apply_filters('nggv_gallery_vote_form', $nggv, $options);
				$form = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['form']), array($this, $options));
			}else{ //ok, they cant vote.  what next?
				if($options->enable) {
					if($canVote === 'NOT LOGGED IN') { //the api wants them to login to vote
						$form['form'] = nggVoting::msg('Only registered users can vote. Please login to cast your vote.');
					}else if($canVote === 'USER HAS VOTED'
						|| $canVote === 'IP HAS VOTED' || $canVote == 'IP HAS VOTED TODAY' || $canVote == 'IP HAS VOTED THIS WEEK' || $canVote == 'IP HAS VOTED THIS MONTH' || $canVote == 'IP HAS VOTED THIS YEAR'
						|| $canVote === 'USER HAS VOTED' || $canVote == 'USER HAS VOTED TODAY' || $canVote == 'USER HAS VOTED THIS WEEK' || $canVote == 'USER HAS VOTED THIS MONTH' || $canVote == 'USER HAS VOTED THIS YEAR'
						|| $canVote === 'COOKIE HAS VOTED' || $canVote == 'COOKIE HAS VOTED TODAY' || $canVote == 'COOKIE HAS VOTED THIS WEEK' || $canVote == 'COOKIE HAS VOTED THIS MONTH' || $canVote == 'COOKIE HAS VOTED THIS YEAR'
						|| $canVote === true) { //api tells us they have voted, can they see results? (canVote will be true if they have just voted successfully)
						if($options->user_results) { //yes! show it
							$form = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['results']), array($this, $options));
						}else{ //nope, but thanks for trying
							$form['form'] = nggVoting::msg('Thank you for casting your vote.');
						}
					}
				}
			}
			return $form;
		}
		
		function imageVoteForm($pid, $criteriaId) {
			if(!is_numeric($pid)) {
				//trigger_error("Invalid argument 1 for function ".__FUNCTION__."(\$galId).", E_USER_WARNING);
				return;
			}
			
			$options = $this->getImageVotingOptions($pid, $criteriaId);
			
			$out = "";
			$errOut = "";
			
			if(isset($options->enable) && $options->enable) {
				/* i needed to change GET['pid'] to GET['nggv_pid'] to stop a conflict with NGG itself
				Unfortunately that means NGG V Premium v1 users will break. This is a hack fix to keep them
				working as normal until they upgrade */
				if(!isset($_GET['nggv_pid']) && isset($_GET['pid']) && isset($_GET['r'])) { //no new nggv_pid, but old pid and rating
					$_GET['nggv_pid'] = $_GET['pid'];
				}
				
				if(!isset($_GET['nggv_criteria_id'])) {
					$_GET['nggv_criteria_id'] = 0;
				}
				
				//$url = $_SERVER['REQUEST_URI'];
				//$url .= (strpos($url, '?') === false ? '?' : (substr($url, -1) == '&' ? '' : '&')); //make sure the url ends in '?' or '&' correctly
				
				//$voteFuncs = $this->types[$options->voting_type]['imageCallback'];
				
				//moved voting catch into 'init' hook, so I can set cookies.
				//$votedOrErr = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['catch']), array($this, $options));
				
				/*
				$votedOrErr = isset($this->initCatchVote[$pid][$criteriaId]['result']) ? $this->initCatchVote[$pid][$criteriaId]['result'] : '';
				*/
				
				/*
				if(isset($_GET['ajaxify']) && $_GET['ajaxify'] && isset($_GET['nggv_pid']) &&  $_GET['nggv_pid'] == $pid && $_GET['nggv_criteria_id'] == $criteriaId) {
					$out .= '<!-- NGGV START AJAX RESPONSE -->';
					$out .= '<script>';
					$out .= 'var nggv_js = {};';
					$out .= 'nggv_js.options = {};';
					$out .= 'nggv_js.saved = '.($votedOrErr === true ? '1' : '0').';';
					$out .= 'nggv_js.msg = "'.addslashes($votedOrErr).'";';
				}
				*/
				
				//$form = nggvGalleryVote::showVoteForm($this, $options, $votedOrErr);
				
				/*
				if((($canVote = $this->canVoteImage($pid, $criteriaId)) === true) && !$votedOrErr) { //they can vote, show the form
					//$return = apply_filters('nggv_gallery_vote_form', $nggv, $options);
					$form = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['form']), array($this, $options));
				}else{ //ok, they cant vote.  what next?
					if($options->enable) {
						if($canVote === 'NOT LOGGED IN') { //the api wants them to login to vote
							$form['form'] = nggVoting::msg('Only registered users can vote. Please login to cast your vote.');
						}else if($canVote === 'USER HAS VOTED'
							|| $canVote === 'IP HAS VOTED' || $canVote == 'IP HAS VOTED TODAY' || $canVote == 'IP HAS VOTED THIS WEEK' || $canVote == 'IP HAS VOTED THIS MONTH' || $canVote == 'IP HAS VOTED THIS YEAR'
							|| $canVote === 'USER HAS VOTED' || $canVote == 'USER HAS VOTED TODAY' || $canVote == 'USER HAS VOTED THIS WEEK' || $canVote == 'USER HAS VOTED THIS MONTH' || $canVote == 'USER HAS VOTED THIS YEAR'
							|| $canVote === 'COOKIE HAS VOTED' || $canVote == 'COOKIE HAS VOTED TODAY' || $canVote == 'COOKIE HAS VOTED THIS WEEK' || $canVote == 'COOKIE HAS VOTED THIS MONTH' || $canVote == 'COOKIE HAS VOTED THIS YEAR'
							|| $canVote === true) { //api tells us they have voted, can they see results? (canVote will be true if they have just voted successfully)
							if($options->user_results) { //yes! show it
								$form = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['results']), array($this, $options));
							}else{ //nope, but thanks for trying
								$form['form'] = nggVoting::msg('Thank you for casting your vote.');
							}
						}
					}
				}
				*/
				
				$form = $this->getImageVoteMarkup($options);
				
				/*
				if(isset($_GET['ajaxify']) && $_GET['ajaxify']) {
					$out .= 'nggv_js.voting_form = "'.addslashes($form['form']).'";';
				}else{
				*/
					$out .= '<div class="nggv_container">';
						$out .= (isset($form['scripts']) ?  $form['scripts'] : '');
						
						//make sure the generic err is only every output once. hackity, but needs to be in for backwards compat with premium releases blah blah etc
						global $_nggv_generic_err;
						if(!$_nggv_generic_err) {
							$out .= '<input type="hidden" id="ngg-genric-err-msg" value="'.esc_attr(nggVoting::msg('There was a problem saving your vote, please try again in a few moments.')).'" />';
							$_nggv_generic_err = true;
						}
						
						$votedOrErr = isset($this->initCatchVote[$pid][$criteriaId]['result']) ? $this->initCatchVote[$pid][$criteriaId]['result'] : '';

						$out .= '<div class="nggv-error" style="'.(!$votedOrErr || $votedOrErr === true ? 'display:none;' : '').' border: 1px solid red;">';
							$out .= $votedOrErr;
						$out .= '</div>';
						
						$out .= '<div class="nggv-vote-form">';
							$out .= $form['form'];
						$out .= '</div>';
					$out .= '</div>';
					
					do_action('nggv_image_vote_bottom', $options);
				/*
				}
				*/
				
				/*
				if(isset($_GET['ajaxify']) && $_GET['ajaxify'] && isset($_GET['nggv_pid']) && $_GET['nggv_pid'] == $pid && $_GET['nggv_criteria_id'] == $criteriaId) {
					$out .= '<script><!-- NGGV END AJAX RESPONSE -->';
				}
				*/
			}
			
			return $out;
		}
		
		/**
		 * Hook: ngg_show_gallery_content
		 * The function adds the voting form/results to the gallery content
		 * @param string $out The entire markup of the gallery passed from NextGEN
		 * @param int $gid The NextGEN Gallery ID
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return string The voting form (or results) appended to the original gallery markup given
		 */
		function showGallery($out, $gid) {
			return $out.$this->galleryVoteForm($gid);
		}
		
		/**
		 * Using self::canVote() display the voting form, or results, or thank you message.  Also calls the nggv_saveVote() once a user casts their vote 
		 * @param int $gid The NextGEN Gallery ID
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return string The voting form, or results, or thank you message markup
		 */
		function galleryVoteForm($gid) {
			if(!is_numeric($gid)) {
				return;
			}
			
			$options = $this->getVotingOptions($gid);
			$out = '';
			
			if(isset($options->enable) && $options->enable) {
				/* i needed to change GET['pid'] to GET['nggv_pid'] to stop a conflict with NGG itself.
				I figured i would change the gallery (gid) while i was at it :)
				Unfortunately that means NGG V Premium v1 users will break. This is a hack fix to keep them
				working as normal until they upgrade */
				if(!isset($_GET['nggv_gid']) && isset($_GET['gid']) && isset($_GET['r'])) { //no new nggv_gid, but old gid and rating
					$_GET['nggv_gid'] = $_GET['gid'];
				}
				
				$url = $_SERVER['REQUEST_URI'];
				$url .= (strpos($url, '?') === false ? '?' : (substr($url, -1) == '&' ? '' : '&')); //make sure the url ends in '?' or '&' correctly
				
				
				//$votedOrErr = nggvGalleryVote::checkVoteData($this, $options);
				$voteFuncs = $this->types[$options->voting_type]['galleryCallback'];
				$votedOrErr = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['catch']), array($this, $options));
				
				if(isset($_GET['ajaxify']) && $_GET['ajaxify'] && isset($_GET['nggv_gid']) && $_GET['nggv_gid'] == $gid) {
					$out .= '<!-- NGGV START AJAX RESPONSE -->';
					$out .= '<script>';
					$out .= 'var nggv_js = {};';
					$out .= 'nggv_js.options = {};';
					$out .= 'nggv_js.saved = '.($votedOrErr === true ? '1' : '0').';';
					$out .= 'nggv_js.msg = "'.addslashes($votedOrErr).'";';
				}
				
				//$form = nggvGalleryVote::showVoteForm($this, $options, $votedOrErr);
				
				if((($canVote = $this->canVote($gid)) === true) && !$votedOrErr) { //they can vote, show the form
					//$return = apply_filters('nggv_gallery_vote_form', $nggv, $options);
					$form = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['form']), array($this, $options));
				}else{ //ok, they cant vote.  what next?
					if($options->enable) {
						if($canVote === 'NOT LOGGED IN') { //the api wants them to login to vote
							$form['form'] = nggVoting::msg('Only registered users can vote. Please login to cast your vote.');
						}else if($canVote === 'USER HAS VOTED' || $canVote === 'IP HAS VOTED' || $canVote === true) { //api tells us they have voted, can they see results? (canVote will be true if they have just voted successfully)
							if($options->user_results) { //yes! show it
								$form = @call_user_func_array(array($voteFuncs['class'], $voteFuncs['results']), array($this, $options));
							}else{ //nope, but thanks for trying
								$form['form'] = nggVoting::msg('Thank you for casting your vote.');
							}
						}
					}
				}
				
				if(isset($_GET['ajaxify']) && $_GET['ajaxify']) {
					$out .= 'nggv_js.voting_form = "'.addslashes($form['form']).'";';
				}else{
					$out .= '<div class="nggv_container">';
						$out .= '<input type="hidden" id="ngg-genric-err-msg" value="'.esc_attr(nggVoting::msg('There was a problem saving your vote, please try again in a few moments.')).'" />';
						$out .= isset($form['scripts']) ? $form['scripts'] : '';
						$out .= '<div class="nggv-error" style="'.(!$votedOrErr || $votedOrErr === true ? 'display:none;' : '').' border: 1px solid red;">';
							$out .= $votedOrErr;
						$out .= '</div>';
						
						$out .= '<div class="nggv-vote-form">';
							$out .= $form['form'];
						$out .= '</div>';
					$out .= '</div>';
				}
				
				if(isset($_GET['ajaxify']) && $_GET['ajaxify'] && isset($_GET['nggv_gid']) && $_GET['nggv_gid'] == $gid) {
					$out .= '<script><!-- NGGV END AJAX RESPONSE -->';
				}
			}
			
			return $out;
		}
	// }
}

//Went with '$offical..', just incase someone needs to create another instance of the object and names collide.
global $officalNggVoting;
$officalNggVoting = new nggVoting();

function nggv_imageVoteForm($pid, $criteriaId=0) {
	global $officalNggVoting;
	return $officalNggVoting->imageVoteForm($pid, $criteriaId);
}
//}
?>