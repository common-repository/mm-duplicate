<?php

class mm_duplicate
{
	var $post_tags;
	var $post_categories;
	
	
	function mm_duplicate()
	{
		//
	}
	
	function get_options_for_posts($post)
	{
		$url = get_option('siteurl');
		$url = $url.'/wp-admin/page.php?action=edit&post='.$post->ID.'&duplicate=';
		$str = '
			<div id="options_for_posts">
				<p>
					<a href="'.$url.'post">Create a copy this post</a>
				</p>
							
				<p>
					<a href="'.$url.'page">Create a copy of this post in Pages</a>
				</p>
			</div>';
			
		echo $str;
		
	}
	
	function get_options_for_pages($post)
	{
		$url = get_option('siteurl');
		$url = $url.'/wp-admin/page.php?action=edit&post='.$post->ID.'&duplicate=';
		?>
			<div id="options_for_posts">
				<p>
					<a href="<?php echo $url.'page'?>">Create a copy of this page</a>
				</p>
							
				<p>
					<a href="<?php echo $url.'post'?>">Create a copy of this page in Posts</a>
				</p>
			</div>
		<?php
	}
	
	function get_post_terms($id)
	{
		global $wpdb;
		$this->post_categories = wp_get_post_categories($id);
			
		$select = "select term_taxonomy_id from ".$wpdb->prefix."term_taxonomy where term_id in (".implode(",",wp_get_post_categories($id)).")";
		return $terms = $wpdb->get_results($select);
		
		
	}
	
	function duplicate_post_page($id)
	{
		global $wpdb, $wp_version;

		$post = get_post($id);
		$post_data = array();
		
		$post_data['post_author'] = $post->post_author;
		$post_data['post_date'] = $post->post_date;
		$post_data['post_date_gmt'] = $post->post_date_gmt;
		$post_data['post_content'] = $post->post_content;
		$post_data['post_title'] = $post->post_title.' (copy)';
		if ($wp_version < 2.8) {
			$post_data['post_category'] = $post->post_category;
		}
		$post_data['post_excerpt'] = $post->post_excerpt;
		$post_data['post_status'] = 'draft';
		$post_data['comment_status'] = $post->comment_status;
		$post_data['ping_status'] = $post->ping_status;
		$post_data['post_password'] = $post->post_password;
		$post_data['post_name'] = $post->post_name;
		$post_data['to_ping'] = $post->to_ping;
		$post_data['pinged'] = $post->pinged;
		$post_data['post_modified'] = $post->post_modified;
		$post_data['post_modified_gmt'] = $post->post_modified_gmt;
		$post_data['post_content_filtered'] = $post->post_content_filtered;
		$post_data['post_parent'] = $post->post_parent;
		$post_data['guid'] = uniqid($post->guid . ' ');
		$post_data['menu_order'] = $post->menu_order;
		$post_data['post_type'] = $_GET['duplicate'];
		$post_data['post_mime_type'] = $post->post_mime_type;
		$post_data['comment_count'] = 0;
		
		if($wp_version < 2.5)
		{
			$this->insert($wpdb->prefix."posts",$post_data);
		}
		else
		{
			$wpdb->insert($wpdb->prefix."posts",$post_data);
		}
		
		$post_id = $wpdb->insert_id;
		
		$select = "select * from ".$wpdb->prefix."postmeta where post_id = $id";
		$postmeta = $wpdb->get_results($select);
		if($postmeta)
		{
			$meta_data = array();
			foreach($postmeta as $meta)
			{
				$meta_data['post_id'] = $post_id;
				$meta_data['meta_key'] = $meta->meta_key;
				$meta_data['meta_value'] = $meta->meta_value;
				if($wp_version < 2.5)
				{
					$this->insert($wpdb->prefix."postmeta",$meta_data);
				}
				else
				{
					$wpdb->insert($wpdb->prefix."postmeta",$meta_data);
				}
			}
		}			
		$url = get_option('siteurl');
		$page = $_GET['duplicate'].'.php';
		$url = $url.'/wp-admin/'.$page.'?action=edit&post='.$post_id.'';
		
		//Adding post tags
		if($this->post_tags)
		{
			$tags_data = array();
			foreach($this->post_tags as $tag)
			{
				$tags_data['object_id'] = $post_id;
				$tags_data['term_taxonomy_id'] = $tag->term_taxonomy_id;
				if($wp_version < 2.5) 
				{
					$this->insert($wpdb->prefix."term_relationships",$tags_data);
				}
				else
				{
					$wpdb->insert($wpdb->prefix."term_relationships",$tags_data);
				}
			}
		}		
		
		//Adding Categories		
		$categories_terms = $this->get_post_terms($id);
		if($categories_terms)
		{
			$category_data = array();
			foreach($categories_terms as $term)
			{
				$category_data['object_id'] = $post_id;
				$category_data['term_taxonomy_id'] = $term->term_taxonomy_id;
				if($wp_version < 2.5)
				{
					$this->insert($wpdb->prefix."term_relationships",$category_data);
				}
				else
				{
					$wpdb->insert($wpdb->prefix."term_relationships",$category_data);
				}
			}
			
		}
		header('location:'.$url);
		exit;
	}
	function insert($table, $data) 
	{
		global $wpdb;
		$data = add_magic_quotes($data);
		$fields = array_keys($data);
		return $wpdb->query("INSERT INTO $table (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$data)."')");
	}	
}
?>
