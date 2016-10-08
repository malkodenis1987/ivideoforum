<?php

/*
Plugin Name: Popularity Contest
Description: This plugin has been discontinued.
Version: 2.0.1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/ 

// stub functions for compatibility in case template functions have been hard-coded into a theme

function akpc_the_popularity($post_id = null) {}

function akpc_most_popular($limit = 10, $before = '<li>', $after = '</li>', $report = false, $echo = true) {}

function akpc_get_popular_posts_array($type, $limit, $custom = array()) {}

function akpc_most_popular_in_cat($limit = 10, $before = '<li>', $after = '</li>', $cat_ID = '') {}

function akpc_most_popular_in_month($limit = 10, $before = '<li>', $after = '</li>', $m = '') {}

function akpc_most_popular_in_last_days($limit = 10, $before = '<li>', $after = '</li>', $days = 45) {}
