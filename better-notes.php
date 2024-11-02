<?php
/*
Plugin Name: Better Notes
Plugin URI: http://mondaybynoon.com/wordpress-better-notes/
Description: Attach rich text Notes to your Pages and your Posts
Version: 1.0.1b
Author: Jonathan Christopher
Author URI: http://mondaybynoon.com/
*/

/*  Copyright 2009 Jonathan Christopher  (email : jonathandchr@gmail.com)

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


// ===========
// = GLOBALS =
// ===========

global $wpdb;



// =========
// = HOOKS =
// =========

add_action('admin_menu', 'betternotes_init');
add_action('save_post', 'betternotes_save');
add_action('admin_menu', 'betternotes_menu');



// =============
// = FUNCTIONS =
// =============

/**
 * Compares two array values with the same key "order"
 *
 * @param string $a First value
 * @param string $b Second value
 * @return int
 * @author Jonathan Christopher
 */
function betternotes_cmp($a, $b)
{
	$a = intval( $a['order'] );
	$b = intval( $b['order'] );
	
	if( $a < $b )
	{
		return -1;
	}
	else if( $a > $b )
	{
		return 1;
	}
	else
	{
		return 0;
	}
}




/**
 * Creates the markup for the WordPress admin options page
 *
 * @return void
 * @author Jonathan Christopher
 */
function betternotes_options()
{ ?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Better Notes Options</h2>
		<p>Coming (or going away) soon</p>
	</div>
<?php }




/**
 * Creates the entry for betternotes Options under Settings in the WordPress Admin
 *
 * @return void
 * @author Jonathan Christopher
 */
function betternotes_menu()
{
	add_options_page('Settings', 'Better Notes', 8, __FILE__, 'betternotes_options');
}




/**
 * Inserts HTML for meta box, including all existing betternotes
 *
 * @return void
 * @author Jonathan Christopher
 */
function betternotes_add()
{?>
	
	<p id="betternote-add-new"><a href="#" class="button">Add New</a></p>
	
	<div id="betternotes">
		
		<input type="hidden" name="betternotes_nonce" id="betternotes_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
		
		<?php
			if( !empty($_GET['post']) )
			{
				// get all betternotes
				$existing_betternotes = betternotes_get_betternotes( intval( $_GET['post'] ) );
				
				if( is_array( $existing_betternotes ) && !empty( $existing_betternotes ) )
				{
					$betternote_index = 0;
					foreach ($existing_betternotes as $betternote) : $betternote_index++; ?>
					<div class="betternote-wrapper">
						<div class="betternote-inner">
							<div class="betternote-actions">
								<a href="#" class="betternote-handle">Move</a>
								<a href="#" class="betternote-delete">Delete</a>
							</div>
							<div class="betternote-copy">
								<textarea id="betternote_<?php echo $betternote_index; ?>" name="betternote_<?php echo $betternote_index; ?>" class="betternote betternote-copy"><?php echo $betternote['copy']; ?></textarea>
							</div>
							<div class="betternotes-data">
								<input type="hidden" name="betternote_id_<?php echo $betternote_index; ?>" id="betternote_id_<?php echo $betternote_index; ?>" value="<?php echo $betternote['id']; ?>" />
								<input type="hidden" class="betternote_order" name="betternote_order_<?php echo $betternote_index; ?>" id="betternote_order_<?php echo $betternote_index; ?>" value="<?php echo $betternote['order']; ?>" />
							</div>
						</div>
					</div>
					
					<?php endforeach;
				}
			}
		?>
		
		<script type="text/javascript" charset="utf-8">
			jQuery('.betternote').each(function() {
				jQuery(this).addClass('theEditor');
			});
			if( typeof(tinyMCEPreInit.mceInit) != 'undefined' )
			{		
				tinyMCEPreInit.mceInit.height = '300';
				tinyMCEPreInit.mceInit.theme_advanced_resizing = false;
				tinyMCE.init(tinyMCEPreInit.mceInit);
			} else {
				alert("There was an error initializing TinyMCE");
			}
		</script>
		
	</div>
	
<?php }



/**
 * Creates meta box on all Posts and Pages
 *
 * @return void
 * @author Jonathan Christopher
 */

function betternotes_meta_box()
{
	// for posts
	add_meta_box( 'betternotes_list', __( 'Better Notes', 'betternotes_textdomain' ), 'betternotes_add', 'post', 'normal' );
	
	// for pages
	add_meta_box( 'betternotes_list', __( 'Better Notes', 'betternotes_textdomain' ), 'betternotes_add', 'page', 'normal' );
}



/**
 * Fired when Post or Page is saved. Serializes all note data and saves to post_meta
 *
 * @param int $post_id The ID of the current post
 * @return void
 * @author Jonathan Christopher
 * @author JR Tashjian
 */
function betternotes_save($post_id)
{
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['betternotes_nonce'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
	// to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
		return $post_id;

	// Check permissions
	if ( 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	}

	// OK, we're authenticated: we need to find and save the data
	
	// delete all current betternotes meta
	// moved outside conditional, else we can never delete all betternotes
	delete_post_meta($post_id, '_betternotes');
	
	// Since we're allowing betternotes to be sortable, we can't simply increment a counter
	// we need to keep track of the IDs we're given
	$betternotes_ids = array();
	
	// We'll build our array of betternotes
	foreach($_POST as $key => $data) {
		
		// Arbitrarily using the id
		if( substr($key, 0, 14) == 'betternote_id_' )
		{
			array_push( $betternotes_ids, substr( $key, 14, strlen( $key ) ) );
		}
		
	}
		
	// If we have betternotes, there's work to do
	if( !empty( $betternotes_ids ) )
	{
		foreach ( $betternotes_ids as $i )
		{
			if( isset( $_POST['betternote_id_' . $i] ) )
			{
				$betternote_details = array(
						'id' 				=> $_POST['betternote_id_' . $i],
						'copy' 				=> $_POST['betternote_' . $i],
						'order' 			=> $_POST['betternote_order_' . $i]
					);
				
				// serialize data and encode
				$betternote_serialized = base64_encode( serialize( $betternote_details ) );
				
				// add individual note
				add_post_meta( $post_id, '_betternotes', $betternote_serialized );
			}
		}	
	}
	
}



/**
 * Retrieves all betternotes for provided Post or Page
 *
 * @param int $post_id (optional) ID of target Post or Page, otherwise pulls from global $post
 * @return array $post_betternotes
 * @author Jonathan Christopher
 * @author JR Tashjian
 */
function betternotes_get_betternotes( $post_id=null )
{
	global $post;
	
	if( $post_id==null )
	{
		$post_id = $post->ID;
	}
	
	// get all betternotes
	$existing_betternotes = get_post_meta( $post_id, '_betternotes', false );
	
	// We can now proceed as normal, all legacy data should now be upgraded
	if( is_array( $existing_betternotes ) && count( $existing_betternotes ) > 0 )
	{
		$post_betternotes = array();
		
		foreach ($existing_betternotes as $betternote)
		{
			// decode and unserialize the data
			$data = unserialize( base64_decode( $betternote ) );
			
			array_push( $post_betternotes, array(
				'id' 			=> stripslashes( $data['id'] ),
				'copy' 			=> stripslashes( $data['copy'] ),
				'order' 		=> stripslashes( $data['order'] )
			));
		}
		
		// sort betternotes
		if( count( $post_betternotes ) > 1 )
		{
			usort( $post_betternotes, "betternotes_cmp" );
		}
	}
	
	return $post_betternotes;
}



/**
 * This is the main initialization function, it will invoke the necessary meta_box
 *
 * @return void
 * @author Jonathan Christopher
 */

function betternotes_init()
{
	wp_enqueue_style('betternotes', WP_PLUGIN_URL . '/better-notes/css/better-notes.css');
	wp_enqueue_script('betternotes', WP_PLUGIN_URL . '/better-notes/js/better-notes.js');
	
	betternotes_meta_box();
}