<?php

/**
 * Plugin Name: Assessment
 * Author: Sheikh Abdullah
 * Description: Test Task for ROPSTAM Solutions
 */

add_action('admin_menu','sma_remove_metaboxes');
function sma_remove_metaboxes() {
	remove_meta_box( 'authordiv','websites','normal' ); // Author Metabox
	remove_meta_box( 'commentstatusdiv','websites','normal' ); // Comments Status Metabox
	remove_meta_box( 'commentsdiv','websites','normal' ); // Comments Metabox
	remove_meta_box( 'postcustom','websites','normal' ); // Custom Fields Metabox
	remove_meta_box( 'postexcerpt','websites','normal' ); // Excerpt Metabox
	remove_meta_box( 'revisionsdiv','websites','normal' ); // Revisions Metabox
	remove_meta_box( 'slugdiv','websites','normal' ); // Slug Metabox
	remove_meta_box( 'trackbacksdiv','websites','normal' ); // Trackback Metabox
	remove_meta_box( 'submitdiv', 'websites', 'side' ); //Publish metabox
}



add_action('admin_init','psp_add_role_caps',999);
function psp_add_role_caps() {


	$roles = array('editor','administrator');

	foreach($roles as $the_role) { 

		$role = get_role($the_role);
		$role->add_cap( 'read' );
		$role->add_cap( 'read_website');
		$role->add_cap( 'edit_website' );
		$role->add_cap( 'edit_websites' );
		
	}
}




add_action('init','init_hook');

function init_hook() {
	
	//call the function which will register a new post type
	register_custom_post_type();
	
}


add_action( 'wp_enqueue_scripts', 'custom_scripts' );

function custom_scripts() {
	wp_register_style('custom-css',plugin_dir_url(__FILE__).'/assets/js/custom.css');
	wp_enqueue_style('custom-css');
}



function register_custom_post_type() {
	$labels = array(
		'name' => __( 'Websites' ),
		'singular_name' => __( 'Website' )
	);

	$args = array(
		'label'               => 'Websites',
		'description'         => 'Website description',
		'labels'              => $labels,  
		'supports'            => array( 'title', 'excerpt', 'author', 'thumbnail' ),     
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => array('website','websites'),
		'map_meta_cap'        => true,
		'capabilities' => array(
			'create_posts' => false,
		),
		
		'show_in_rest' => true, 
	);


	register_post_type( 'websites', $args	);
}

add_shortcode('ropstam_test','ropstam_test');

function ropstam_test() {

	if (isset($_POST['sma_submit'])) {
		$sma_name=''; $sma_website = '';
		if (isset($_POST['sma_name'])) {
			$sma_name = $_POST['sma_name'];
		}
		if (isset($_POST['sma_website'])) {
			$sma_website = $_POST['sma_website'];
		}

		if ($sma_website!='') {
			$website_source_code= file_get_contents($sma_website);
			$sma_post_id = wp_insert_post(array (
				'post_type' => 'websites',
				'post_title' => $sma_name,
				'post_status' => 'publish',

			));
			if ($sma_post_id) {
				update_post_meta($sma_post_id, 'sma_website_source_code',$website_source_code);
				update_post_meta($sma_post_id, 'sma_website_url',$sma_website);

			}
		}
	}
	?>


	<h2>Welcome to site</h2>

	<form action="" method="post" name="sma_form">
		<label for="sma_name">Name</label>
		<input class="sma_input" type="text" id="sma_name" name="sma_name">
		<label for="sma_website">Website URL</label>
		<input class="sma_input" type="text" id="sma_website" name="sma_website">

		<input class="sma_submit" type="submit" name="sma_submit" id="sma_submit">
	</form>

	<?php
}

add_action( 'add_meta_boxes', 'sma_meta_boxes' );
function sma_meta_boxes() {

	add_meta_box(
		'sma_custom_metabox','Websites Source Code',
		'sma_custom_metabox_callback',
		'websites'
	);
}

function sma_custom_metabox_callback() {
	
	if( is_user_logged_in() ) {
		$sma_user = wp_get_current_user();
		$sma_roles = ( array ) $sma_user->roles;
		if (in_array('administrator', $sma_roles)) {
			$sma_website_url='';
			$sma_website_source_code='';
			$sma_post_id = get_the_ID();

			$sma_website_source_code = get_post_meta($sma_post_id,'sma_website_source_code',true);
			$sma_website_url = get_post_meta($sma_post_id,'sma_website_url',true);
			?>
			<label>Website Source Code</label>
			<textarea id="sma_website_source_code" name="sma_website_source_code"><?php echo json_encode($sma_website_source_code); ?>></textarea>
			<br>
			<label>Website URL</label>
			<input id="sma_website_url" name="sma_website_url" value="<?php echo $sma_website_url; ?>">
			<?php
		}
	}
}

//link for rest API is "http://{your-site}/wp-json/rest/v1/websites"
add_action( 'rest_api_init', function () {
	register_rest_route( 'rest/v1', '/websites', array(
		'methods' => 'GET',
		'callback' => 'my_awesome_func',
	) );
} );


function my_awesome_func( $data ) {
	$posts = get_posts( array(
		'post_type' => 'websites',
	) );

	if ( empty( $posts ) ) {
		return null;
	}

	return $posts;
}

