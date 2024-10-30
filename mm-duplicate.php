<?php
/*
Plugin Name: MM Duplicate
Plugin URI:  http://plugins.motionmill.com/mm-duplicate
Description: MM Duplicate plugin allows you to duplicate your posts and pages.
Author: Tom Belmans (Motionmill)
Version: 1.2
Author URI: http://www.motionmill.com
*/

require_once(dirname(__FILE__) .'/class/mm-duplicate.php');

class mm_duplicate_pages_posts
{

	var $duplicate_option_label;
	var $duplicate_options_key;
	
	function mm_duplicate_pages_posts()
	{
		$this->duplicate_option_label	= "MM Duplicate";
		$this->duplicate_options_key	= "duplicate";		
		
		add_action('admin_head', array(&$this, 'admin_head_duplicate_options'));
		add_action('admin_menu', array(&$this,'admin_init_func'));
		add_action('init', array(&$this, 'dup'));
		
	}

	function dup()
	{
		if($_GET['duplicate'])
		{
			$id = $_GET['post'];
			$dup = new mm_duplicate();			
			
			$dup->post_tags = wp_get_post_tags( $id );
			$dup->duplicate_post_page($id);
		}
	}
	
	function admin_head_duplicate_options()
	{
		$style_sheet = get_option('siteurl') . '/wp-content/plugins/mm-duplicate/options_layout.css';
		echo '<link rel="stylesheet" href="' . $style_sheet . '" type="text/css" />';		
	}
	
	function admin_init_func()
	{
		if($_GET['post'])
		{
			if (function_exists('add_meta_box')) 
			{
				add_meta_box($this->duplicate_options_key, $this->duplicate_option_label, array(&$this,'show_duplicate_dbx_post'), 'post');
				add_meta_box($this->duplicate_options_key, $this->duplicate_option_label, array(&$this,'show_duplicate_dbx_page'), 'page');		
			}
			else 
			{ 
				add_action('dbx_page_advanced', array(&$this,'show_duplicate_dbx_page'));
				add_action('dbx_post_advanced', array(&$this,'show_duplicate_dbx_post'));
			}
		}
	}
	
	function show_duplicate_dbx_page()
	{
		global $post, $current_user;		
		$obj_dup = new mm_duplicate();

		if (!$post->ID)
			return;

		if ( !current_user_can('edit_page', $post->ID) )
			return;
		
		$this->show_admin_advanced('begin');		
		$obj_dup->get_options_for_pages($post);		
		$this->show_admin_advanced('end');
	}
	
	function show_duplicate_dbx_post()
	{
		global $post, $current_user;
		$obj_dup = new mm_duplicate();
			
		if (!$post->ID)
			return;

		if ( !current_user_can('edit_post', $post->ID) )
			return;

		$this->show_admin_advanced('begin');		
		$obj_dup->get_options_for_posts($post);		
		$this->show_admin_advanced('end');
	}	
	
	function show_admin_advanced($type='')
	{
		global $wp_version;
		
		switch($type)
		{
			case 'begin':
				if ($wp_version < "2.5")
				{
					?>
					<div class="dbx-b-ox-wrapper">
						<fieldset id="dbx-versions" class="dbx-box">
							<div class="dbx-h-andle-wrapper">
								<h3 class="dbx-handle"><?php _e($this->duplicate_option_label) ?></h3>
							</div>
							<div class="dbx-c-ontent-wrapper">
								<div class="dbx-content">
					<?php
				}
				break;
			
			case 'end':
				if ($wp_version < "2.5")
				{
					?>							
								</div>
							</div>
						</fieldset>
					</div>
					<?php
				}
				break;
				
			default:
				break;
		}
	}	
}

$obj_dup_pages_posts = new mm_duplicate_pages_posts();

?>
