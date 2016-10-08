<?php
class nggvGalleryVote {
	public static function convertErrorCode($err) {
		$out = '';
		if($err == 'VOTING NOT ENABLED') {
			$out .= nggVoting::msg('This gallery has not turned on voting.');
		}else if($err == 'NOT LOGGED IN') {
			$out .= nggVoting::msg('You need to be logged in to vote.');
		}else if($err == 'USER HAS VOTED') {
			$out .= nggVoting::msg('You have already voted on this gallery.');
		}else if($err == 'IP HAS VOTED') {
			$out .= nggVoting::msg('This IP has already voted on this gallery.');
		}
		
		$out = apply_filters('nggv_convert_error_code', $out, $err);
		
		if(!$out) {
			$out .= nggVoting::msg('There was a problem saving your vote, please try again in a few moments.');
		}
		
		return $out;
	}
	
	public static function getUrl() {
		$tmpUrl = $_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI'];
		$url = strtok($tmpUrl, '?');
		$get = $_GET;
		if(isset($get['nggv_pid']) || isset($get['nggv_criteria_id'])) {
			unset($get['nggv_pid']);
			unset($get['nggv_criteria_id']);
			unset($get['r']);
		}
		if($get) {
			$url .= '?'.http_build_query($get);
		}
		$url .= (strpos($url, '?') === false ? '?' : (substr($url, -1) == '&' ? '' : '&')); //make sure the url ends in '?' or '&' correctly
		
		return $url;
	}
	
	// Drop Down (gallery)
	public static function galleryVoteFormDropDown($nggv, $options) {
		$return = array();
		if($options->voting_type == 1) {
			$return['form'] .= '<style>';
			$return['form'] .= '.nggv-gallery-pot {display:none;}';
			$return['form'] .= '</style>';
			
			$return['form'] .= '<form method="post" action="">';
			$return['form'] .= '<input type="text" class="nggv-gallery-pot" name="nggv[required_pot_field]" value="" />'; //honey pot attempt, not sure how useful this will be. I will consider better options for cash :)
			$return['form'] .= '<input type="hidden" name="nggv[vote_gid_id]" value="'.$options->gid.'" />';
			$return['form'] .= '<label forid="nggv_rating">'.nggVoting::msg('Rate this gallery:').'</label>';
			$return['form'] .= '<select id="nggv_rating" name="nggv[vote]">';
			$return['form'] .= '<option value="0">0</option>';
			$return['form'] .= '<option value="10">1</option>';
			$return['form'] .= '<option value="20">2</option>';
			$return['form'] .= '<option value="30">3</option>';
			$return['form'] .= '<option value="40">4</option>';
			$return['form'] .= '<option value="50">5</option>';
			$return['form'] .= '<option value="60">6</option>';
			$return['form'] .= '<option value="70">7</option>';
			$return['form'] .= '<option value="80">8</option>';
			$return['form'] .= '<option value="90">9</option>';
			$return['form'] .= '<option value="100">10</option>';
			$return['form'] .= '</select>';
			$return['form'] .= '<input type="submit" value="Rate" />';
			$return['form'] .= '</form>';
		}
		
		return $return;
	}
	
	public static function galleryCatchVoteDropDown($nggv, $options) {
		$out = '';
		if($options->voting_type == 1) {
			if($_POST && !$_POST['nggv']['vote_pid_id'] && $_POST['nggv']['vote_gid_id'] == $options->gid) {
				if($_POST['nggv']['required_pot_field']) { //seems spammy
					$out .= nggVoting::msg('Vote not saved. Spam like activity detected.');
				}else if(($msg = $nggv->saveVote(array('gid'=>$options->gid, 'vote'=>$_POST['nggv']['vote']))) === true) {
					return true;
				}else{
					$out .= self::convertErrorCode($msg);
				}
			}
		}
		
		return $out;
	}
	
	public static function galleryVoteResultsDropDown($nggv, $options) {
		$return = array();
		if($options->voting_type == 1) {
			$results = $nggv->getVotingResults($options->gid, array('avg'=>true));
			$return['form'] .= nggVoting::msg('Current Average:').' '.round(($results['avg'] / 10), 1).' / 10';
		}
		return $return;
	}
	
	// Drop Down (image)
	public static function imageVoteFormDropDown($nggv, $options) {
		$return = array();
		if($options->voting_type == 1) {
			$return['form'] .= '<style>';
			$return['form'] .= '.nggv-gallery-pot {display:none;}';
			$return['form'] .= '</style>';
			
			$return['form'] .= '<form method="post" action="">';
			$return['form'] .= '<input type="text" class="nggv-gallery-pot" name="nggv[required_pot_field]" value="" />'; //honey pot attempt, not sure how useful this will be. I will consider better options for cash :)
			$return['form'] .= '<input type="hidden" name="nggv[vote_pid_id]" value="'.$options->pid.'" />';
			$return['form'] .= '<input type="hidden" name="nggv[vote_criteria_id]" value="'.$options->criteria_id.'" />';
			$return['form'] .= '<label forid="nggv_rating">'.nggVoting::msg('Rate this image:').'</label>';
			$return['form'] .= '<select id="nggv_rating" name="nggv[vote]">';
			$return['form'] .= '<option value="0">0</option>';
			$return['form'] .= '<option value="10">1</option>';
			$return['form'] .= '<option value="20">2</option>';
			$return['form'] .= '<option value="30">3</option>';
			$return['form'] .= '<option value="40">4</option>';
			$return['form'] .= '<option value="50">5</option>';
			$return['form'] .= '<option value="60">6</option>';
			$return['form'] .= '<option value="70">7</option>';
			$return['form'] .= '<option value="80">8</option>';
			$return['form'] .= '<option value="90">9</option>';
			$return['form'] .= '<option value="100">10</option>';
			$return['form'] .= '</select>';
			$return['form'] .= '<input type="submit" value="Rate" />';
			$return['form'] .= '</form>';
		}
		
		return $return;
	}
	
	public static function imageCatchVoteDropDown($nggv, $options) {
		$out = '';
		if(!isset($_POST['nggv']['vote_criteria_id'])) {
			$_POST['nggv']['vote_criteria_id'] = 0;
		}
		
		if($options->voting_type == 1) {
			if($_POST && $_POST['nggv']['vote_pid_id'] && $_POST['nggv']['vote_pid_id'] == $options->pid && $_POST['nggv']['vote_criteria_id'] == $options->criteria_id) {
				if($_POST['nggv']['required_pot_field']) { //seems spammy
					$out .= nggVoting::msg('Vote not saved. Spam like activity detected.');
				}else if(($msg = $nggv->saveVoteImage(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id, 'vote'=>$_POST['nggv']['vote']))) === true) {
					return true;
				}else{
					$out .= self::convertErrorCode($msg);
				}
			}
		}
		
		return $out;
	}
	
	public static function imageVoteResultsDropDown($nggv, $options) {
		$return = array();
		if($options->voting_type == 1) {
			$results = $nggv->getImageVotingResults(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id), array('avg'=>true));
			$return['form'] = nggVoting::msg('Current Average:').' '.round(($results['avg'] / 10), 1).' / 10';
		}
		return $return;
	}
	
	
	// Like / Dislike (gallery)
	public static function galleryVoteFormDisLike($nggv, $options) {
		$return = array();
		
		if($options->voting_type == 3) {
			$return['scripts'] .= $nggv->includeJs($nggv->pluginUrl.'js/ajaxify-likes.js');	//ajaxify voting, from v1.7
			
			$url = self::getUrl();
			
			$return['form'] .= '<a href="'.$url.'nggv_gid='.$options->gid.'&r=1" class="nggv-link-like"><img src="'.$nggv->pluginUrl.'images/thumbs_up.png" alt="Like" /></a>';
			$return['form'] .= '<a href="'.$url.'nggv_gid='.$options->gid.'&r=0" class="nggv-link-dislike"><img src="'.$nggv->pluginUrl.'images/thumbs_down.png" alt="Dislike" /></a>';
			$return['form'] .= '<img class="nggv-star-loader" src="'.$nggv->pluginUrl.'images/loading.gif" style="display:none;" />';
			
			if($options->user_results) {
				$results = $nggv->getVotingResults($options->gid, array('likes'=>true, 'dislikes'=>true));
				$return['form'] .= '<div class="like-results">';
				$return['form'] .= $results['likes'].' ';
				$return['form'] .= $results['likes'] == 1 ? nggVoting::msg('Like') : nggVoting::msg('Likes');
				$return['form'] .= ' '.$results['dislikes'].' ';
				$return['form'] .= $results['dislikes'] == 1 ? nggVoting::msg('Dislike') : nggVoting::msg('Dislikes');
				$return['form'] .= '</div>';
			}
		}
		
		return $return;
	}
	
	public static function galleryCatchVoteDisLike($nggv, $options) {
		$out = '';
		if($options->voting_type == 3 && $_GET['nggv_gid'] && $options->gid == $_GET['nggv_gid'] && is_numeric($_GET['r'])) {
			if($_GET['r']) {$_GET['r'] = 100;} //like/dislike is all or nothing :)
			
			if(($msg = $nggv->saveVote(array('gid'=>$options->gid, 'vote'=>$_GET['r']))) === true) {
				return true;
			}else{
				$out .= self::convertErrorCode($msg);
			}
		}
		
		return $out;
		
	}
	
	public static function galleryVoteResultsDisLike($nggv, $options) {
		$return = array();
		
		if($options->voting_type == 3) {
			$results = $nggv->getVotingResults($options->gid, array('likes'=>true, 'dislikes'=>true));
			
			$return['form'] .= $results['likes'].' ';
			$return['form'] .= $results['likes'] == 1 ? nggVoting::msg('Like') : nggVoting::msg('Likes');
			$return['form'] .= ' '.$results['dislikes'].' ';
			$return['form'] .= $results['dislikes'] == 1 ? nggVoting::msg('Dislike') : nggVoting::msg('Dislikes');
		}
		
		return $return;
	}
	
	// Like / Dislike (image)
	public static function imageVoteFormDisLike($nggv, $options) {
		$return = array();
		
		if($options->voting_type == 3) {
			$return['scripts'] .= $nggv->includeJs($nggv->pluginUrl.'js/ajaxify-likes.js');	//ajaxify voting, from v1.7
			
			$url = self::getUrl();
			
			$return['form'] .= '<a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=1" class="nggv-link-like"><img src="'.$nggv->pluginUrl.'images/thumbs_up.png" alt="Like" /></a>';
			$return['form'] .= '<a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=0" class="nggv-link-dislike"><img src="'.$nggv->pluginUrl.'images/thumbs_down.png" alt="Dislike" /></a>';
			$return['form'] .= '<img class="nggv-star-loader" src="'.$nggv->pluginUrl.'images/loading.gif" style="display:none;" />';
			
			if($options->user_results) {
				$results = $nggv->getImageVotingResults(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id), array('likes'=>true, 'dislikes'=>true));
				$return['form'] .= '<div class="like-results">';
				$return['form'] .= $results['likes'].' ';
				$return['form'] .= $results['likes'] == 1 ? nggVoting::msg('Like') : nggVoting::msg('Likes');
				$return['form'] .= ' '.$results['dislikes'].' ';
				$return['form'] .= $results['dislikes'] == 1 ? nggVoting::msg('Dislike') : nggVoting::msg('Dislikes');
				$return['form'] .= '</div>';
			}
		}
		
		return $return;
	}
	
	public static function imageCatchVoteDisLike($nggv, $options) {
		$out = '';
		if(!isset($_GET['nggv_criteria_id'])) {
			$_GET['nggv_criteria_id'] = 0;
		}
		if($options->voting_type == 3 && $_GET['nggv_pid'] && $options->pid == $_GET['nggv_pid'] && $_GET['nggv_criteria_id'] == $options->criteria_id && is_numeric($_GET['r'])) {
			if($_GET['r']) {$_GET['r'] = 100;} //like/dislike is all or nothing :)
			
			if(($msg = $nggv->saveVoteImage(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id, 'vote'=>$_GET['r']))) === true) {
				return true;
			}else{
				$out .= self::convertErrorCode($msg);
			}
		}
		
		return $out;
		
	}
	
	public static function imageVoteResultsDisLike($nggv, $options) {
		$return = array();
		
		if($options->voting_type == 3) {
			$results = $nggv->getImageVotingResults(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id), array('likes'=>true, 'dislikes'=>true));
			
			$return['form'] = $results['likes'].' ';
			$return['form'] .= $results['likes'] == 1 ? nggVoting::msg('Like') : nggVoting::msg('Likes');
			$return['form'] .= ' '.$results['dislikes'].' ';
			$return['form'] .= $results['dislikes'] == 1 ? nggVoting::msg('Dislike') : nggVoting::msg('Dislikes');
		}
		
		return $return;
	}

	// Star Rating (gallery)
	public static function galleryVoteFormStar($nggv, $options) {
		$return = array();
		if($options->voting_type == 2	) {
			$return['scripts'] .= $nggv->includeJs($nggv->pluginUrl.'js/ajaxify-stars.js');	//ajaxify voting, from v1.7
			$return['scripts'] .= $nggv->includeCss($nggv->pluginUrl.'css/star_rating.css');
			
			$return['form'] .= '<span class="inline-rating">';
			$return['form'] .= '<ul class="star-rating">';
			if($options->user_results) { //user can see curent rating
				$results = $nggv->getVotingResults($options->gid, array('avg'=>true));
				$return['form'] .= '<li class="current-rating" style="width:'.round($results['avg']).'%;">Currently '.round($results['avg'] / 20, 1).'/5 Stars.</li>';
			}
			
			$url = self::getUrl();

			$return['form'] .= '<li><a href="'.$url.'nggv_gid='.$options->gid.'&r=20" title="1 star out of 5" class="one-star">1</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_gid='.$options->gid.'&r=40" title="2 stars out of 5" class="two-stars">2</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_gid='.$options->gid.'&r=60" title="3 stars out of 5" class="three-stars">3</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_gid='.$options->gid.'&r=80" title="4 stars out of 5" class="four-stars">4</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_gid='.$options->gid.'&r=100" title="5 stars out of 5" class="five-stars">5</a></li>';
			$return['form'] .= '</ul>';
			$return['form'] .= '</span>';
			$return['form'] .= '<img class="nggv-star-loader" src="'.$nggv->pluginUrl.'images/loading.gif" style="display:none;" />';
		}
		
		return $return;
	}
	
	public static function galleryCatchVoteStar($nggv, $options) {
		$out = '';
		if($options->voting_type == 2 && $_GET['nggv_gid'] && $options->gid == $_GET['nggv_gid'] && is_numeric($_GET['r'])) {
			if(($msg = $nggv->saveVote(array('gid'=>$options->gid, 'vote'=>$_GET['r']))) === true) {
				return true;
			}else{
				$out .= self::convertErrorCode($msg);
			}
		}
		
		return $out;
	}
	
	public static function galleryVoteResultsStar($nggv, $options) {
		$return = array();
		if($options->voting_type == 2) {
			$results = $nggv->getVotingResults($options->gid, array('avg'=>true));
			
			$return['scripts'] .= $nggv->includeJs($nggv->pluginUrl.'js/ajaxify-stars.js');	//ajaxify voting, from v1.7
			$return['scripts'] .= $nggv->includeCss($nggv->pluginUrl.'css/star_rating.css');

			$return['form'] = '<span class="inline-rating">';
			$return['form'] .= '<ul class="star-rating">';
			$return['form'] .= '<li class="current-rating" style="width:'.round($results['avg']).'%;">Currently '.round($results['avg'] / 20, 1).'/5 Stars.</li>';
			$return['form'] .= '<li>1</li>';
			$return['form'] .= '<li>2</li>';
			$return['form'] .= '<li>3</li>';
			$return['form'] .= '<li>4</li>';
			$return['form'] .= '<li>5</li>';
			$return['form'] .= '</ul>';
			$return['form'] .= '</span>';
			
		}
		return $return;
	}
	
	//Star (image)
	public static function imageVoteFormStar($nggv, $options) {
		$return = array();
		if($options->voting_type == 2	) {
			$return['scripts'] .= $nggv->includeJs($nggv->pluginUrl.'js/ajaxify-stars.js');	//ajaxify voting, from v1.7
			$return['scripts'] .= $nggv->includeCss($nggv->pluginUrl.'css/star_rating.css');
			
			$return['form'] .= '<span class="inline-rating">';
			$return['form'] .= '<ul class="star-rating">';
			if($options->user_results) { //user can see curent rating
				$results = $nggv->getImageVotingResults(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id), array('avg'=>true));
				$return['form'] .= '<li class="current-rating" style="width:'.round($results['avg']).'%;">Currently '.round($results['avg'] / 20, 1).'/5 Stars.</li>';
			}
			
			$url = self::getUrl();
			
			$return['form'] .= '<li><a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=20" title="1 star out of 5" class="one-star">1</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=40" title="2 stars out of 5" class="two-stars">2</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=60" title="3 stars out of 5" class="three-stars">3</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=80" title="4 stars out of 5" class="four-stars">4</a></li>';
			$return['form'] .= '<li><a href="'.$url.'nggv_pid='.$options->pid.'&nggv_criteria_id='.$options->criteria_id.'&r=100" title="5 stars out of 5" class="five-stars">5</a></li>';
			$return['form'] .= '</ul>';
			$return['form'] .= '</span>';
			$return['form'] .= '<img class="nggv-star-loader" src="'.$nggv->pluginUrl.'images/loading.gif" style="display:none;" />';
		}
		
		return $return;
	}
	
	public static function imageCatchVoteStar($nggv, $options) {
		$out = '';
		if(!isset($_GET['nggv_criteria_id'])) {
			$_GET['nggv_criteria_id'] = 0;
		}
		if($options->voting_type == 2 && $_GET['nggv_pid'] && $options->pid == $_GET['nggv_pid'] && $options->criteria_id == $_GET['nggv_criteria_id'] && is_numeric($_GET['r'])) {
			if(($msg = $nggv->saveVoteImage(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id, 'vote'=>$_GET['r']))) === true) {
				return true;
			}else{
				$out .= self::convertErrorCode($msg);
			}
		}
		
		return $out;
	}
	
	public static function imageVoteResultsStar($nggv, $options) {
		$return = array();
		if($options->voting_type == 2) {
			$results = $nggv->getImageVotingResults(array('pid'=>$options->pid, 'criteria_id'=>$options->criteria_id), array('avg'=>true));
			
			$return['scripts'] = $nggv->includeJs($nggv->pluginUrl.'js/ajaxify-stars.js');	//ajaxify voting, from v1.7
			$return['scripts'] .= $nggv->includeCss($nggv->pluginUrl.'css/star_rating.css');

			$return['form'] = '<span class="inline-rating">';
			$return['form'] .= '<ul class="star-rating">';
			$return['form'] .= '<li class="current-rating" style="width:'.round($results['avg']).'%;">Currently '.round($results['avg'] / 20, 1).'/5 Stars.</li>';
			$return['form'] .= '<li>1</li>';
			$return['form'] .= '<li>2</li>';
			$return['form'] .= '<li>3</li>';
			$return['form'] .= '<li>4</li>';
			$return['form'] .= '<li>5</li>';
			$return['form'] .= '</ul>';
			$return['form'] .= '</span>';
			
		}
		return $return;
	}
}
?>
