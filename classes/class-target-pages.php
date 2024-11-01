<?php 
/*
WPmanage Admin Terget Pages
@Version: 1.0
Author: Raul Ujog
Author URI: http://www.wpmanage.com/
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Uji_Target_Pages{
    /**
     * Init metaboxes
     *
     * @since 1.0
     */
    public function uji_target_init() {       
        
        if ( ! function_exists( 'wp_nav_menu_item_post_type_meta_box' ) ) {
            include ABSPATH . 'wp-admin/includes/nav-menu.php';
        }
 
        wp_enqueue_script('nav-menu');
        wp_localize_script( 'nav-menu', 'menus', false );
        
        ?>
        <div class="wrap" id="menu-to-edit"><!--  Add: #menu-to-edit  to fix Select all remove href -->
            <div id="nav-menus-frame">
                <div id="menu-settings-column" class="metabox-holder<?php if ( isset( $_GET['menu'] ) && '0' == $_GET['menu'] ) { echo ' metabox-holder-disabled'; } ?>">
                    <?php wp_nonce_field( 'add-menu_item', 'menu-settings-column-nonce' ); ?>               
        <?php
    }
    
    /**
     * Ends
     *
     * @since 1.0
     */
    public function uji_target_end(){
        ?>
                </div><!-- /#menu-settings-column -->
            </div><!-- /#nav-menus-frame -->
        </div><!-- /.wrap-->
                    
        <?php            
    }
    
    /**
     * Traget Post & Pages or Tax
     *
     * @since 1.0
     */
    public function uji_target_type($type) {    
        $this->uji_target_ids($type);
        
        if($type == 'post'){
            $this->uji_post_type_meta_boxes();
        }
           
    ?>
		<div class="clear"></div>
			<?php do_accordion_sections( 'popups-'.$type, 'ujiside', null ); ?>
        
    <?php
        
    }
    
    /**
     * Creates metaboxes for post/pages items.
     *
     * @since 1.0 from nav-menu.php WP core
     */
    public function uji_post_type_meta_boxes() {   
     	$post_types = get_post_types( array( 'show_in_nav_menus' => true ), 'object' );
        
	if ( ! $post_types )
		return;

	foreach ( $post_types as $post_type ) {
	
		$post_type = apply_filters( 'nav_menu_meta_box_object', $post_type );
		if ( $post_type && ( 'page' == $post_type->name || 'post' == $post_type->name ) ) {
			$id = $post_type->name;
          
			// Give pages a higher priority.
			$priority = ( 'page' == $post_type->name ? 'core' : 'default' );
			add_meta_box( "add-{$id}", $post_type->labels->name, array( &$this,'uji_item_post_type_meta_box'), 'popups-post', 'ujiside', $priority, $post_type );
		}
	}
    }
    
    
    /**
     * Creates metaboxes for post/pages items.
     *
     * @since 1.0 from nav-menu.php WP core
     */
    public function uji_target_ids($type) { 
        global $post;
        
        if($type == 'post'){
            $pages = get_post_meta( $post->ID, 'pop_posts', true );
        }
        //printf( '<input type="hidden" name="pop_ids_%s" id="pop_ids_%s" class="uji-pop-id-holder" value="%s" />', $type, $type, trim($pages) );
                
        //$pages = explode( ",", trim( $pages ) );
        
            
        $tags = '<div class="tagchecklist selected-posts" id="pop_ids_'.$type.'_hold">';
         
        if( isset($pages) && !empty($pages) ){
        
            foreach ( $pages as $pop_ct => $pop_ids ){
                foreach ( $pop_ids as  $pop_id ){
                
                    if($pop_id){

                        if( $type == 'post' ){
                            $tar_title = get_the_title( $pop_id );
                        }
                        $tags .= '<input type="hidden" class="pop-item-'.$pop_ct.'-'.$pop_id.'" name="pop_ids_'.$type.'['.$pop_ct.'][]" value="'.$pop_id.'">';
                        $tags .= '<span><a class="tardelbutton" data-id="'.$pop_id.'" data-cat="'.$pop_ct.'" data-type="pop_ids_'.$type.'">X</a> '.$tar_title.' ('. ucfirst($pop_ct).') </span>';
                    }    
                }
            }
        }
                
        $tags .= '</div>';
            
        
        
        if(isset($tags)) echo $tags;
        
    }
    
    public function uji_item_post_type_meta_box( $object, $post_type ) {
	global $_nav_menu_placeholder, $nav_menu_selected_id;

	$post_type_name = $post_type['args']->name;

	// Paginate browsing for large numbers of post objects.
	$per_page = 50;
	$pagenum = isset( $_REQUEST[$post_type_name . '-tab'] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
	$offset = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;

	$args = array(
		'offset' => $offset,
		'order' => 'ASC',
		'orderby' => 'title',
		'posts_per_page' => $per_page,
		'post_type' => $post_type_name,
		'suppress_filters' => true,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false
	);

	if ( isset( $post_type['args']->_default_query ) )
		$args = array_merge($args, (array) $post_type['args']->_default_query );

	// @todo transient caching of these results with proper invalidation on updating of a post of this type
	$get_posts = new WP_Query;
	$posts = $get_posts->query( $args );
	if ( ! $get_posts->post_count ) {
		echo '<p>' . __( 'No items.' ) . '</p>';
		return;
	}

	$num_pages = $get_posts->max_num_pages;

	$page_links = paginate_links( array(
		'base' => add_query_arg(
			array(
				$post_type_name . '-tab' => 'all',
				'paged' => '%#%',
				'item-type' => 'post_type',
				'item-object' => $post_type_name,
			)
		),
		'format' => '',
		'prev_text' => __('&laquo;'),
		'next_text' => __('&raquo;'),
		'total' => $num_pages,
		'current' => $pagenum
	));

	$db_fields = false;
	if ( is_post_type_hierarchical( $post_type_name ) ) {
		$db_fields = array( 'parent' => 'post_parent', 'id' => 'ID' );
	}

	$walker = new Walker_Nav_Menu_Checklist( $db_fields );

	$current_tab = 'most-recent';
	if ( isset( $_REQUEST[$post_type_name . '-tab'] ) && in_array( $_REQUEST[$post_type_name . '-tab'], array('all', 'search') ) ) {
		$current_tab = $_REQUEST[$post_type_name . '-tab'];
	}

	if ( ! empty( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
		$current_tab = 'search';
	}

	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);

	?>
	<div id="posttype-<?php echo $post_type_name; ?>" class="posttypediv">
		<ul id="posttype-<?php echo $post_type_name; ?>-tabs" class="posttype-tabs add-menu-item-tabs">
			<li <?php echo ( 'most-recent' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-most-recent" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($post_type_name . '-tab', 'most-recent', remove_query_arg($removed_args))); ?>#tabs-panel-posttype-<?php echo $post_type_name; ?>-most-recent">
					<?php _e( 'Most Recent' ); ?>
				</a>
			</li>
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="<?php echo esc_attr( $post_type_name ); ?>-all" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($post_type_name . '-tab', 'all', remove_query_arg($removed_args))); ?>#<?php echo $post_type_name; ?>-all">
					<?php _e( 'View All' ); ?>
				</a>
			</li>
			<li <?php echo ( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-posttype-<?php echo esc_attr( $post_type_name ); ?>-search" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($post_type_name . '-tab', 'search', remove_query_arg($removed_args))); ?>#tabs-panel-posttype-<?php echo $post_type_name; ?>-search">
					<?php _e( 'Search'); ?>
				</a>
			</li>
		</ul><!-- .posttype-tabs -->

		<div id="tabs-panel-posttype-<?php echo $post_type_name; ?>-most-recent" class="tabs-panel <?php
			echo ( 'most-recent' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
			<ul id="<?php echo $post_type_name; ?>checklist-most-recent" class="categorychecklist form-no-clear">
				<?php
				$recent_args = array_merge( $args, array( 'orderby' => 'post_date', 'order' => 'DESC', 'posts_per_page' => 15 ) );
				$most_recent = $get_posts->query( $recent_args );
				$args['walker'] = $walker;

				/**
				 * Filter the posts displayed in the 'Most Recent' tab of the current
				 * post type's menu items meta box.
				 *
				 * The dynamic portion of the hook name, `$post_type_name`, refers to the post type name.
				 *
				 * @since 4.3.0
				 *
				 * @param array  $most_recent An array of post objects being listed.
				 * @param array  $args        An array of WP_Query arguments.
				 * @param object $post_type   The current post type object for this menu item meta box.
				 */
				$most_recent = apply_filters( "nav_menu_items_{$post_type_name}_recent", $most_recent, $args, $post_type );

				echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $most_recent), 0, (object) $args );
				?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div class="tabs-panel <?php
			echo ( 'search' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>" id="tabs-panel-posttype-<?php echo $post_type_name; ?>-search">
			<?php
			if ( isset( $_REQUEST['quick-search-posttype-' . $post_type_name] ) ) {
				$searched = esc_attr( $_REQUEST['quick-search-posttype-' . $post_type_name] );
				$search_results = get_posts( array( 's' => $searched, 'post_type' => $post_type_name, 'fields' => 'all', 'order' => 'DESC', ) );
			} else {
				$searched = '';
				$search_results = array();
			}
			?>
			<p class="quick-search-wrap">
				<input type="search" class="quick-search input-with-default-title" title="<?php esc_attr_e('Search'); ?>" value="<?php echo $searched; ?>" name="quick-search-posttype-<?php echo $post_type_name; ?>" />
				<span class="spinner"></span>
				<?php submit_button( __( 'Search' ), 'button-small quick-search-submit button-secondary hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-posttype-' . $post_type_name ) ); ?>
			</p>

			<ul id="<?php echo $post_type_name; ?>-search-checklist" data-wp-lists="list:<?php echo $post_type_name?>" class="categorychecklist form-no-clear">
			<?php if ( ! empty( $search_results ) && ! is_wp_error( $search_results ) ) : ?>
				<?php
				$args['walker'] = $walker;
				echo walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $search_results), 0, (object) $args );
				?>
			<?php elseif ( is_wp_error( $search_results ) ) : ?>
				<li><?php echo $search_results->get_error_message(); ?></li>
			<?php elseif ( ! empty( $searched ) ) : ?>
				<li><?php _e('No results found.'); ?></li>
			<?php endif; ?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div id="<?php echo $post_type_name; ?>-all" class="tabs-panel tabs-panel-view-all <?php
			echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
		?>">
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
			<ul id="<?php echo $post_type_name; ?>checklist" data-wp-lists="list:<?php echo $post_type_name?>" class="categorychecklist form-no-clear">
				<?php
				$args['walker'] = $walker;

				/*
				 * If we're dealing with pages, let's put a checkbox for the front
				 * page at the top of the list.
				 */
				if ( 'page' == $post_type_name ) {
					$front_page = 'page' == get_option('show_on_front') ? (int) get_option( 'page_on_front' ) : 0;
					if ( ! empty( $front_page ) ) {
						$front_page_obj = get_post( $front_page );
						$front_page_obj->front_or_home = true;
						array_unshift( $posts, $front_page_obj );
					} else {
						$_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;
						array_unshift( $posts, (object) array(
							'front_or_home' => true,
							'ID' => 0,
							'object_id' => $_nav_menu_placeholder,
							'post_content' => '',
							'post_excerpt' => '',
							'post_parent' => '',
							'post_title' => _x('Home', 'nav menu home label'),
							'post_type' => 'nav_menu_item',
							'type' => 'custom',
							'url' => home_url('/'),
						) );
					}
				}

				/**
				 * Filter the posts displayed in the 'View All' tab of the current
				 * post type's menu items meta box.
				 *
				 * The dynamic portion of the hook name, `$post_type_name`, refers
				 * to the slug of the current post type.
				 *
				 * @since 3.2.0
				 *
				 * @see WP_Query::query()
				 *
				 * @param array  $posts     The posts for the current post type.
				 * @param array  $args      An array of WP_Query arguments.
				 * @param object $post_type The current post type object for this menu item meta box.
				 */
				$posts = apply_filters( "nav_menu_items_{$post_type_name}", $posts, $args, $post_type );
				$checkbox_items = walk_nav_menu_tree( array_map('wp_setup_nav_menu_item', $posts), 0, (object) $args );

				if ( 'all' == $current_tab && ! empty( $_REQUEST['selectall'] ) ) {
					$checkbox_items = preg_replace('/(type=(.)checkbox(\2))/', '$1 checked=$2checked$2', $checkbox_items);

				}

				echo $checkbox_items;
				?>
			</ul>
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
		</div><!-- /.tabs-panel -->

		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php
					echo esc_url( add_query_arg(
						array(
							$post_type_name . '-tab' => 'all',
							'selectall' => 1,
						),
						remove_query_arg( $removed_args )
					));
				?>#posttype-<?php echo $post_type_name; ?>" class="select-all"><?php _e('Select All');  ?></a>
			</span>

			<span class="add-to-menu">
				<input type="button"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> data-format="pop_ids_post" data-type="<?php echo $post_type_name ?>" class="button-secondary uji-add-target right" value="<?php esc_attr_e( 'Add Selected' ); ?>" name="add-post-type-menu-item" id="<?php echo esc_attr( 'submit-posttype-' . $post_type_name ); ?>" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.posttypediv -->
	<?php
}


}


?>