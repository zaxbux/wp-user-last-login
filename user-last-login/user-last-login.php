<?php
/*
Plugin Name:  User Last Login
Plugin URI:   https://github.com/zaxbux/wp-user-last-login
Description:  Adds a column to the users table to see last login time.
Version:      1.0
Author:       zaxbux
Author URI:   https://github.com/zaxbux
License:      MIT
License URI:  https://spdx.org/licenses/MIT.html
Text Domain:  user-last-login
Domain Path:  /languages
*/

//Record user's last login to custom meta
add_action('wp_login', 'z_capture_login_time', 10, 2);

function z_capture_login_time($user_login, $user)
{
	update_user_meta($user->ID, 'last_login', time());
}

//Register new custom column with last login time
add_filter('manage_users_columns', 'z_user_last_login_column');
add_filter('manage_users_custom_column', 'z_last_login_column', 10, 3);

function z_user_last_login_column($columns)
{
	$columns['last_login'] = 'Last Login';
	return $columns;
}

function z_last_login_column($output, $column_id, $user_id)
{
	if ($column_id == 'last_login') {
		$last_login = get_user_meta($user_id, 'last_login', true);
		$date_format = 'Y-m-d H:i:s e';

		$output = $last_login ? '<time datetime="' . date('c', $last_login) . '" title="Last login: ' . date($date_format, $last_login) . '">' . human_time_diff($last_login) . '</time>' : '&mdash;';
	}

	return $output;
}

//Allow the last login columns to be sortable
add_filter('manage_users_sortable_columns', 'z_sortable_last_login_column');
add_action('pre_get_users', 'z_sort_last_login_column');

function z_sortable_last_login_column($columns)
{
	return wp_parse_args(array(
		'last_login' => 'last_login'
	), $columns);
}

function z_sort_last_login_column($query)
{
	if (!is_admin()) {
		return $query;
	}

	$screen = get_current_screen();

	if (isset($screen->base) && $screen->base !== 'users') {
		return $query;
	}

	if (isset($_GET['orderby']) && $_GET['orderby'] == 'last_login') {

		$query->query_vars['meta_key'] = 'last_login';
		$query->query_vars['orderby'] = 'meta_value';
	}

	return $query;
}
