/**
 * Enables post/page duplication as a draft.
 *
 * If a WordPress update causes the loss of this feature, it should be easy to add it back in.
 * Simply append all of this code to the end of the functions.php located in the wp-includes folder, and save it.
 *
 * Adapted from code originally provided courtesy of Misha Rudrastyh
 * found at https://www.hostinger.com/tutorials/how-to-duplicate-wordpress-page-or-post#Duplicating_WordPress_Page_or_Post_Without_Plugins
 */
function duplicate_as_draft() {
	global $wpdb;
	
	if (!(isset($_GET['post']) || isset($_POST['post'])	|| (isset($_REQUEST['action']) && $_REQUEST['action'] == 'duplicate_as_draft'))) {
		wp_die('Duplication target required.');
	}
	
	if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
		return;
	}
	
	$orig_id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));
	$post = get_post($orig_id);
	$post_type = null;
	
	if (isset($post) && $post != null) {
		$post_type = $post->post_type;
		$data = [];
		
		foreach ($post as $key => $value) {
			$data["$key"] = $value;
		}
		
		unset($data['ID']);
		$data['post_status'] = 'draft';
		$data['post_name'] .= '-backup';
		$new_id = wp_insert_post($data);
		
		$taxonomies = get_object_taxonomies($post->post_type);
		foreach($taxonomies as $taxonomy) {
			$terms = wp_get_object_terms($orig_ig, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_id, $terms, $taxonomy, false);
		}
		
		$meta = $wpdb->get_results("SELECT meta_key, meat_value FROM $wpdb->postmeta WHERE post_id=$orig_id");
		if(count($meta) > 0) {
			$q = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach($meta as $m) {
				$key = $m->meta_key;
				if ($key == '_wp_old_slug') continue;
				$value = addslashes($m->meta_value);
				$sel[] = "SELECT $new_id, '$key', '$value'";
			}
			
			$q .= implode(" UNION ALL ", $sel);
			$wpdb->query($q);
		}
		wp_redirect(admin_url("edit.php?post_type=$post_type"));
	}
	else {
		wp_die("Post creation failed, ID not found: $orig_id");
	}
 }
 
 add_action("admin_action_duplicate_as_draft", "duplicate_as_draft");
 
 function duplicate_link($actions, $post) {
	if (current_user_can('edit_posts')) {
		$pid = $post->ID;
		$actions['duplicate'] = '<a href="' 
		. wp_nonce_url("admin.php?action=duplicate_as_draft&post=$pid", basename(__FILE__), 'duplicate_nonce')
		. '" title="Duplicates this item." rel="permalink">Duplicate</a>';
	}
	return $actions;
 }
 
 add_filter('post_row_actions', 'duplicate_link', 10, 2);
 add_filter('page_row_actions', 'duplicate_link', 10, 2);
