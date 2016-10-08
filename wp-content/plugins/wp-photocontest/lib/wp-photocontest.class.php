<?php
/*  
    WP PhotoContest: Adds a photo contest to Wordpress
    Copyright (C) 2009  Frank van der Stad

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/*
+----------------------------------------------------------------+
|																 |
|	WordPress 2.8 Plugin: WP-PhotoContest 1.0                    |
|	Copyright (c) 2009 Frank van der Stad						 |
|																 |
|	File Written By:											 |
|	- Frank van der Stad										 |
|	- http://www.vanderstad.nl/wp-photocontest									 |
|																 |
|	File Information:											 |
|	- Add PhotoContestClass										 |
|	- ../wp-photocontest/lib/wp-photocontest.class.php			 |
|																 |
+----------------------------------------------------------------+
*/
class photoContest {
	
	var $css;
	var  $initHtml;
	var $db;
		
	function __construct()
 	{
 		$this->init();
 	}

	function init() 
 	{
 		global $wpdb;
 		if ( defined('ABSPATH') )
		{
			require_once( ABSPATH . 'wp-config.php');
		}
		else
		{
			require_once('../../../wp-config.php');
			require_once('../../../wp-includes/wp-db.php');
		}
		$this->db = $wpdb;
	}

	function photoContest() {
 		return $this->__construct();
	}
	
	
	function setInitHtml ($content)
	{
		$this->initHtml = $content;
			
	}
	
	function getInitHtml ()
	{
		return $this->initHtml;
	}
	
	
	
	function get_page_data($contest_id, $post_id=0, $p=0, $n_photo_x_page, $order='chrono', $usermail=false)
	{
		$how_many	= (array) $this->db->get_row( $this->db->prepare( "SELECT count(vote) as howmanyvote FROM ".$this->db->prefix."photocontest_votes where vote > 0 AND voter_status='publish'" ) ); 
		if ($post_id == 0)
		{
			$post_id	= (int) $this->db->get_var( $this->db->prepare( "SELECT post_id FROM ".$this->db->prefix."photocontest_admin where contest_id = %d" ,$contest_id )); 		
		}
	
		switch($order)
		{
			case "chrono":
				$q2 = "SELECT wp_uid, img_id, img_path, img_name, img_title,img_comment, sum_votes, img_view_count, insert_time FROM ".$this->db->prefix."photocontest WHERE contest_id= %d AND visibile = 1 ORDER BY insert_time DESC LIMIT ".($p * $n_photo_x_page).",".$n_photo_x_page; 
			break;
			
			case "most_voted":
				$q2 = "SELECT wp_uid, v.img_id as img_id, pr.img_path as img_path, pr.img_name as img_name,img_title, img_comment, sum_votes, img_view_count, insert_time  FROM ".$this->db->prefix."photocontest_votes AS v JOIN ".$this->db->prefix."photocontest AS pr ON (v.img_id = pr.img_id) WHERE pr.contest_id= %d AND pr.visibile = 1  AND sum_votes > 0 GROUP BY img_id ORDER BY sum_votes DESC LIMIT ".($p * $n_photo_x_page).",".$n_photo_x_page; 
			break;
			
			case "most_viewed":
				$q2 = "SELECT wp_uid, v.img_id as img_id, pr.img_path as img_path, pr.img_name as img_name, img_title,img_comment, sum_votes, img_view_count, insert_time  FROM ".$this->db->prefix."photocontest_votes AS v JOIN ".$this->db->prefix."photocontest AS pr ON (v.img_id = pr.img_id) WHERE pr.contest_id= %d AND pr.visibile = 1 GROUP BY img_id ORDER BY img_view_count DESC LIMIT ".($p * $n_photo_x_page).",".$n_photo_x_page; 
			break;	
			
			case "recent":
				$q2 = "SELECT wp_uid, img_id, img_path, img_name, img_title,img_comment, sum_votes, img_view_count, insert_time FROM ".$this->db->prefix."photocontest WHERE contest_id= %d AND visibile = 1 ORDER BY insert_time DESC LIMIT ".($p * $n_photo_x_page).",".$n_photo_x_page; 
			break;
				
			default:
				$q2 = "SELECT wp_uid, img_id, img_path, img_name, img_title,img_comment, sum_votes, img_view_count, insert_time FROM ".$this->db->prefix."photocontest WHERE contest_id= %d AND visibile = 1 ORDER BY insert_time DESC LIMIT ".($p * $n_photo_x_page).",".$n_photo_x_page; 
			break;
		}
		
		$out2	= (array) $this->db->get_results( $this->db->prepare( $q2 , $contest_id )); 

		$data_array = array();
		
		if (!$out2)
		{	
			return $data_array;
		}
		
		$cols		= 0;
		$i			= 0;
		
		
		foreach ($out2 as $k2=>$v2)
		{
			$v2 							= (array) $v2;
			$data_array[$i]					= $v2;
			$img_name						= $v2['img_name'];
			$img_path						= $v2['img_path'];
			$data_array[$i]['med_thumb'] 	= get_option('siteurl').preg_replace("/$img_name/", "med_".$img_name, $img_path);
			$data_array[$i]['pre_thumb'] 	= get_option('siteurl').preg_replace("/$img_name/", "pre_".$img_name, $img_path);
			$data_array[$i]['swf_thumb']	= urlencode($data_array[$i]['pre_thumb']);
			$data_array[$i]['swf_thumb_ok']	= $data_array[$i]['pre_thumb'];
			$data_array[$i]['rows']			= rand(7,10);
			$data_array[$i]['cols']			= ($cols + 1) % 2; 
			$data_array[$i]['contest_id']	= $contest_id;
			$data_array[$i]['post_id']		= $post_id;
			$data_array[$i]['voted']		= -1;
			if ($usermail)
			{
				$emailtocheck = $usermail;
			}
			elseif (isset($current_user->data))
			{	
				$emailtocheck = $current_user->data;
			}
			else
			{
				$emailtocheck = false;
			}
			
			if ($emailtocheck)
			{
				$email_check	= (int)  $this->db->get_var( $this->db->prepare( "SELECT vote FROM ". $this->db->prefix."photocontest_votes WHERE img_id= %d  AND voter_email = %s  AND voter_email!=''", $v2['img_id'] , $emailtocheck) );
				if ($email_check>0)
				{
					$data_array[$i]['voted']		= 1;
				}
			}
			$data_array[$i]['wpu'] 			= get_userdata($v2['wp_uid']);			
			$i++;
		}			

		return $data_array;
	}	
	
	
	
	function view_contest($contest_id,$post_id, $p=0)
	{
	
		$onum		= (array) $this->db->get_row( $this->db->prepare( "SELECT count(*) AS total_nr,post_id FROM ".$this->db->prefix."photocontest pr JOIN ".$this->db->prefix."photocontest_admin pra ON (pr.contest_id = pra.contest_id) WHERE pra.contest_id=%d GROUP BY pra.contest_id", $contest_id  ));		

		$total_nr	= $onum['total_nr'];
		$post_id 	= $onum['post_id'];
		$npages 	= $this->calculate_pages($total_nr, 10);
				
		$scrolling 	= $this->get_page_scrolling($contest_id, $total_nr, $p, $npages, 'recent', 'admin');
		
		//check real num photo:
		$outnum_photo = (array) $this->db->get_row( $this->db->prepare( "SELECT count(distinct(img_id)) as total_nr,img_id FROM ".$this->db->prefix."photocontest_votes ORDER BY img_id GROUP BY img_id"));	

		//$out 			= $this->db->get_results( $this->db->prepare( "SELECT pr.wp_uid AS userid, pr.sum_votes AS sumvotes, wpu.user_login AS userlogin, pr.img_id AS img_id, pr.img_path AS img_path, pr.img_name AS img_name, pr.insert_time AS insert_time, pr.visibile AS visibile FROM ".$this->db->prefix."photocontest AS pr join ".$this->db->prefix."users as wpu on (pr.wp_uid = wpu.ID) AND pr.contest_id= %d ORDER BY pr.sum_votes DESC, pr.wp_uid ASC, pr.insert_time ASC LIMIT %d, %d", $contest_id, ($p * 10),(($p + 1) * 10) ) );	
		
		$out = $this->db->get_results( $this->db->prepare( "SELECT pr.wp_uid AS userid, pr.wp_email AS useremail, pr.sum_votes AS sumvotes, pr.img_id AS img_id, pr.img_path AS img_path, pr.img_name AS img_name, pr.insert_time AS insert_time, pr.visibile AS visibile FROM ".$this->db->prefix."photocontest AS pr WHERE pr.contest_id= %d ORDER BY pr.sum_votes DESC, pr.wp_uid ASC, pr.insert_time ASC LIMIT %d, %d", $contest_id, ($p * 10),(($p + 1) * 10) ) );

		$i=0;
		$data_array = array();
		$votes_array = array();
		$total_votes = 0;
		foreach ($out as $k=>$v)
		{
			/*
			$v = (array) $v;
			$votes = (array) $this->db->get_row( $this->db->prepare( "SELECT count(*) as number_of_votes FROM ".$this->db->prefix."photocontest_votes WHERE img_id= %d AND vote>0" ,$v['img_id'] ) );
			$total_votes +=$votes['number_of_votes'];
			$votes_array['nr_of_votes'][$v['img_id']]=$votes['number_of_votes'];
			$votes_array['total_votes']=$total_votes;
			*/
			$votes = (array) $v;
			$total_votes +=$votes['sumvotes'];
			$votes_array['nr_of_votes'][$votes['img_id']]=$votes['sumvotes'];
			$votes_array['total_votes']=$total_votes;
		}
		
		foreach ($out as $k=>$v)
		{
			$v = (array) $v;
			$data_array[$i]					= $v;
			if ($v['userid']>0)
			{
				$user_details = $this->db->get_results( $this->db->prepare( "SELECT * FROM ".$this->db->prefix."users as wpu WHERE wpu.ID= %d", $v['userid'] ) );
				foreach ($user_details as $user_k=>$user_v)
				{
					$user_v = (array) $user_v;
					$data_array[$i]['userlogin'] = $user_v['display_name'];
				}
			}
			else
			{
				$data_array[$i]['userlogin'] = $v['useremail']; 
			}				
			$data_array[$i]['format_date']	= wppc_niceDateTime($v['insert_time'],'date');
			$data_array[$i]['format_time']	= wppc_niceDateTime($v['insert_time'],'time');
			$img_name						= $v['img_name'];
			$img_path						= $v['img_path'];			
			$data_array[$i]['med_thumb'] 	= preg_replace("/$img_name/", "med_".$img_name, $img_path);			
			$data_array[$i]['nr_of_votes'] 	= $votes_array['nr_of_votes'][$v['img_id']];	
			if ($votes_array['total_votes']>0)
			{
				$data_array[$i]['rank'] 		= round(($votes_array['nr_of_votes'][$v['img_id']]/$votes_array['total_votes'])*100,0);	
			}
			else
			{
				$data_array[$i]['rank'] 		= 0;
			}
			$i++;
		}

		$return_array['total_votes']	= $data_array;
		$return_array['scrolling'] 		= $scrolling;
		$return_array['data'] 			= $data_array;
		
		return $return_array;
	}
	
	function view_votes($img_id)
	{
		$i=0;
		$data_array = array();
		$votes = (array) $this->db->get_results( $this->db->prepare( "SELECT * FROM ".$this->db->prefix."photocontest_votes WHERE img_id=%d AND vote>0",$img_id));
		foreach ($votes as $k=>$v)
		{
			$data_array[$i] = (array) $v;
			$i++;
		}
		return $data_array;
	}

	
	function refresh_page($contest_id,$p=0)
	{
		$out = (array) $this->db->get_row( $this->db->prepare( "SELECT post_id, contest_id, start_date, end_date, contest_path, contest_name, intro_text, enter_text, num_photo FROM ".$this->db->prefix."photocontest_admin WHERE contest_id=%d",$contest_id));
		if ($out)
		{
			$post_id	 	= $out['post_id'];
			$contest_id 	= $out['contest_id'];
			$contest_name	= $out['contest_name'];
			$intro_text 	= $out['intro_text'];
			$enter_text 	= $out['enter_text'];
			$num_photo 		= $out['num_photo'];
			
			//check real num photo:
			$outnum_photo 		= (array) $this->db->get_row( $this->db->prepare( "SELECT count(*) AS total_nr FROM ".$this->db->prefix."photocontest WHERE visibile = 1 AND contest_id=%d",$contest_id));
			$number_of_photos	= $outnum_photo['total_nr'];
			
			//default is 10 photo per page 
			$n_photo_x_page 	= 9;
			$number_of_pages 	= (($number_of_photos / $n_photo_x_page) < 1 ? 1 : ($number_of_photos / $n_photo_x_page));
			
			$page_data			= $this->get_page_data($contest_id, $post_id, $p, $n_photo_x_page,'recent');
			$page_menu			= $this->get_top_menu(get_option('siteurl'),$post_id);
			$scrolling			= $this->get_page_scrolling($post_id, $number_of_photos, $p, $number_of_pages,'recent','refresh');

			$content_title	= "Recently added";
			$ordere			= "recent";
			ob_start();
			?>
			<link id="wp-photocontest-style-css" media="all" type="text/css" href="<?php echo WP_PLUGIN_URL;?>/wp-photocontest/skins/<?php echo CONTESTS_SKIN;?>/theme.css?ver=2.8.4" rel="stylesheet">
			<?php
			if (WP_USE_FLASH != 'false')
			{
				echo '<script src="'.WP_PLUGIN_URL.'/wp-photocontest/js/swfobject2.js?ver=2.8.4" type="text/javascript"></script>';
			}
			?>
			<br clear="all" />
			<?php echo stripslashes($intro_text);?>
			<p>				
				<table class="prtable" border=0>
					<tr>
						<td>
							<p><?php echo $page_menu;?></p>
							<div class="polaroid_container">
								<?php 
								if (!empty($scrolling)) 
								{
									?>
									<br clear="all" />
									<center><?php echo($scrolling);?></center>
									<?php 
								}
								foreach ($page_data as $page_row)
								{
									if (WP_USE_FLASH == 'false')
									{
										?>
										<div class="polaroid">
											<div class="polaroid_photo" id="photo_<?php echo $page_row['img_id'];?>">
												<span class="polaroid_title"><?php echo $page_row['img_title'];?></span>
												<a href="<?php echo get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php?img_id='.$page_row['img_id'].'&post_id='.$post_id;?>"><img src="<?php echo $page_row['pre_thumb'];?>" border="0" /></a>
												
												<br />
												<span class="polaroid_votes"><?php printf(_n("%d vote", "%d votes", $page_row['sum_votes'], 'wp-photocontest'), $page_row['sum_votes']); ?></span>
												<br />
												<span class="polaroid_button"><a href="<?php echo get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php?img_id='.$page_row['img_id'].'&post_id='.$post_id;?>"><?php __('Vote', 'wp-photocontest');?>Гласувай</a></span>
												<br />
											</div>
										</div>
										<?php
									}
									else
									{
										?>
										<div class="polaroid">
											<div id="photo_<?php echo $page_row['img_id'];?>"><img src="<?php echo $page_row['med_thumb'];?>" /></div>
											<script type="text/javascript">
												var flashvars = {photonaam:"<?php echo $page_row['img_title'];?>", photo: "<?php echo $page_row['swf_thumb'];?>", foto_id :"<?php echo $page_row['img_id'];?>", post_id :"<?php echo $post_id;?>", order :"<?php echo $order;?>", poltype: "<?php echo $page_row['rows'];?>", stemmen:"<?php printf(_n("%d vote", "%d votes", $page_row['sum_votes'], 'wp-photocontest'), $page_row['sum_votes']); ?>", vote_url:"<?php echo urlencode(get_option('siteurl').'/wp-content/plugins/wp-photocontest/viewimg.php');?>" }; 
												var params = {  menu: "false", wmode:"transparent"};
												var attributes = {  id: "<?php echo $page_row['img_id'];?>" };
												swfobject2.embedSWF("<?php echo get_option('siteurl');?>/wp-content/plugins/wp-photocontest/skins/<?php echo CONTESTS_SKIN;?>/polaroid.swf", "photo_<?php echo $page_row['img_id'];?>", "162", "200", "7.0.0","expressInstall.swf", flashvars, params, attributes);
											</script>
										</div>
										<?php
	
									}
								}
								if (!empty($scrolling)) 
								{
									?>
									<br clear="all" />
									<center><?php echo($scrolling);?></center>
									<?php 
								}
								?>
							</div>
						</td>
					</tr>
				</table>
			</p>
			<?php
			$newcontent = ob_get_contents();
			ob_end_clean();
				
			$pd = get_post($post_id);
			$pd->post_content = $newcontent; 
			wp_update_post($pd);
			return $newcontent;
		}
	}
	
	function get_top_menu($siteurl, $post_id, $login_true=false)
	{
		$topmenu	= '';
		$out 		= (array) $this->db->get_row( $this->db->prepare( "SELECT contest_id FROM ".$this->db->prefix."photocontest_admin WHERE post_id= %d", $post_id));
		if ($out)
		{
			$topmenu	= "";
			$topmenu	.= "  <a href=\"$siteurl/wp-content/plugins/wp-photocontest/play.php?post_id=".$post_id."\">";
			$topmenu	.= __('Post photos', 'wp-photocontest');
			$topmenu	.= "</a> ";
			$topmenu	.= "| <a href=\"$siteurl/wp-content/plugins/wp-photocontest/view.php?post_id=".$post_id."&order=chrono\">";
			$topmenu	.= __('Recently added', 'wp-photocontest');
			$topmenu	.= "</a> ";		
			$topmenu	.= "| <a href=\"$siteurl/wp-content/plugins/wp-photocontest/view.php?post_id=".$post_id."&order=most_viewed\">";
			$topmenu	.= __('Most views', 'wp-photocontest');
			$topmenu	.= "</a> ";		
			$topmenu	.= "| <a href=\"$siteurl/wp-content/plugins/wp-photocontest/view.php?post_id=".$post_id."&order=most_voted\">";
			$topmenu	.= __('Top rated', 'wp-photocontest');		
			$topmenu	.= "</a> ";	
			if ($login_true)
			{
				$topmenu	.= "| <a href=\"".wp_logout_url( '/' )."\">";
				$topmenu	.= __('Logout', 'wp-photocontest');		
				$topmenu	.= "</a> ";	
			}			
		}

		return $topmenu;
	}
	
	function calculate_pages($num_photo, $n_photo_x_page)
	{
		return (($num_photo / $n_photo_x_page) < 1 ? 1 : ($num_photo / $n_photo_x_page));
	}
	
	function get_page_scrolling ($post_id, $num_photo, $lastpage, $npages, $order='chrono', $base_url='page')
	{
		if ($base_url == 'admin')
		{
			$base_url = '?page=wp-photocontest/photocontest-manager.php&mode=view_contest&contest_id='.$post_id.'&order='.$order;
		}
		elseif ($base_url == 'refresh')
		{
			$base_url = 'wp-content/plugins/wp-photocontest/view.php?post_id='.$post_id.'&order='.$order;
		}
		else
		{
			$base_url = '?post_id='.$post_id.'&order='.$order;
		}
		$scroll='';
		if ($npages > 1)
		{
			if ($lastpage >= 1) 
			{
				$scroll ='<a href="'.$base_url.'&p='.($lastpage - 1).'">&lt;--</a>';
			}
			for ($i = 0; $i < $npages ; $i ++)
			{
				if ($i < $lastpage) 
				{
					$scroll .= ' <a href="'.$base_url.'&p='.$i.'">'.($i + 1).'</a> ';
				}
				elseif ($i == $lastpage) 
				{
					$scroll .= ' '.($i + 1).' ';
				}		
				elseif ($i > $lastpage) 
				{
					$scroll .= ' <a href="'.$base_url.'&p='.$i.'">'.($i + 1).'</a> ';
				}
			}
			if ($lastpage < ($npages - 1)) {
				$scroll .= ' <a href="'.$base_url.'&p='.($lastpage + 1).'">--&gt;</a>';
			}
	
			if (trim($scroll) != "1")
			{		
				return $scroll;
			}
		}
		return $scroll;
	}
	
}
?>
