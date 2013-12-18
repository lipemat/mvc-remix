<?php
//wp did not design for the front end so we have to include a bunch of admin files
require_once( ABSPATH . 'wp-admin/includes/template.php' );
require_once( ABSPATH . 'wp-admin/includes/comment.php' );
require_once( ABSPATH . 'wp-admin/includes/screen.php' );
require_once( ABSPATH . 'wp-admin/includes/post.php' );
require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );


/**
 * List the posts on the frontend
 * 
 * @since 0.6.0
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * 
 */
class MvcPostListsTable extends WP_Posts_List_Table{
    private $post_type;
    public $wp_list_table;
    
    /**
     * Assign the post type and init the table
     * 
     * @param string $postType (defaults to post)
     */
    function __construct( $postType = 'post' ){
        $this->post_type = $postType;
         
        $_GET['post_type'] = $this->post_type;
        $args['screen'] = WP_Screen::get( $this->post_type );
        
        $wp_list_table = new WP_Posts_List_Table($args);
        $this->wp_list_table = new WP_Posts_List_Table($args);
        
       
        
    }
    
    
    /**
     * Display the table
     * 
     * @since 12.18.13
     * 
     * 
     */
    public function output(){

        wp_enqueue_script(
             'inline-edit-post',
             get_bloginfo('url').'/wp-admin/load-scripts.php?c=1&load%5B%5D=inline-edit-post'
        );

        if( $action = $this->wp_list_table->current_action() ){
            $this->displayMessage( $this->updatePosts($action) );
       }

        $this->wp_list_table->prepare_items();
        ob_start();
            $this->wp_list_table->views();
        echo str_replace( 'edit.php', '', ob_get_clean() ); 
        ?>
        <form id="posts-filter" action="" method="get">
            <input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
            <input type="hidden" name="post_type" class="post_type_page" value="<?php echo $this->post_type; ?>" />
            <input type="hidden" name="show_sticky" value="1" />

            <?php $this->wp_list_table->display(); ?>
       </form>
       
       <?php
        if ( $this->wp_list_table->has_items() )
            $this->wp_list_table->inline_edit();
        ?>

        <div id="ajax-response"></div>
        <br class="clear" />
        <?php 
        
        
    }


    /**
     * Display a message
     * 
     * @since 12.18.13
     * 
     * @param string $message - the message to display
     */
    function displayMessage($message){
        
        
        $_REQUEST = explode('?', $message);
        array_shift($_REQUEST);
        $_REQUEST = wp_parse_args($_REQUEST[0]);

        $_REQUEST['updated'] = 2;

        if ( isset( $_REQUEST['locked'] ) || isset( $_REQUEST['updated'] ) || isset( $_REQUEST['deleted'] ) || isset(   $_REQUEST['trashed'] ) || isset( $_REQUEST['untrashed'] ) ) {
             $messages = array();
             ?>
             <div id="message" class="updated"><p>
                <?php 
                if ( isset( $_REQUEST['updated'] ) && $updated = absint( $_REQUEST['updated'] ) ) {
                        
                        if( $updated > 1 ){
                            $plural = $this->plural($this->post_type);
                            $name = $plural;
                        } else {
                            $name = $this->post_type;
                        }
                    
                        $messages[] = sprintf( '%s %s updated.', number_format_i18n( $updated ), $name );
                }

                if ( isset( $_REQUEST['locked'] ) && $locked = absint( $_REQUEST['locked'] ) ) {
                    $messages[] = sprintf( _n( '%s item not updated, somebody is editing it.', '%s items not updated, somebody is editing them.', $locked ), number_format_i18n( $locked ) );
                }

                if ( isset( $_REQUEST['deleted'] ) && $deleted = absint( $_REQUEST['deleted'] ) ) {
                    $messages[] = sprintf( _n( 'Item permanently deleted.', '%s items permanently deleted.', $deleted ), number_format_i18n( $deleted ) );
                }

                if ( isset( $_REQUEST['trashed'] ) && $trashed = absint( $_REQUEST['trashed'] ) ) {
                     $messages[] = sprintf( _n( 'Item moved to the Trash.', '%s items moved to the Trash.', $trashed ), number_format_i18n( $trashed ) );
                     $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : 0;
                     $messages[] = '<a href="' . esc_url( wp_nonce_url( "edit.php?post_type=$post_type&doaction=undo&action=untrash&ids=$ids", "bulk-posts" ) ) . '">' . __('Undo') . '</a>';
                }

                if ( isset( $_REQUEST['untrashed'] ) && $untrashed = absint( $_REQUEST['untrashed'] ) ) {
                    $messages[] = sprintf( _n( 'Item restored from the Trash.', '%s items restored from the Trash.', $untrashed ),      number_format_i18n( $untrashed ) );
                }

                if ( $messages )
                    echo join( ' ', $messages );
                    unset( $messages );

                    $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'locked', 'skipped', 'updated', 'deleted', 'trashed', 'untrashed' ), $_SERVER['REQUEST_URI'] );
                ?>
            </p></div>
            <?php 
         }   
      
      }


     /**
      * Plural version of a string
      * 
      * @param string $title
      */
     function plural( $title ){
        $end = substr($title,-1);
        if( $end == 's' ){
            return $title.'es';
        } elseif( $end == 'y' ){
            return rtrim($title, 'y') . 'ies';
        }
        
        return $title.'s';
      }


     /**
       * Handle the bulk updates 
       * 
       * @since 12.18.13
       * 
       * 
       */
     private function updatePosts($action){
                check_admin_referer('bulk-posts');

                $sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'locked', 'ids'), wp_get_referer() );
                if ( ! $sendback )
                    $sendback = admin_url( $parent_file );
                
                $sendback = add_query_arg( 'paged', $pagenum, $sendback );
                if ( strpos($sendback, 'post.php') !== false )
                    $sendback = admin_url($post_new_file);

                if ( 'delete_all' == $doaction ) {
                    $post_status = preg_replace('/[^a-z0-9_-]+/i', '', $_REQUEST['post_status']);
                    if ( get_post_status_object($post_status) ) // Check the post status exists first
                        $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type=%s AND post_status = %s", $post_type, $post_status ) );
                        $doaction = 'delete';
                } elseif ( isset( $_REQUEST['ids'] ) ) {
                        $post_ids = explode( ',', $_REQUEST['ids'] );
                } elseif ( !empty( $_REQUEST['post'] ) ) {
                        $post_ids = array_map('intval', $_REQUEST['post']);
                }

                if ( !isset( $post_ids ) ) {
                    wp_redirect( $sendback );
                    exit;
                }

                switch ( $action ) {
                    case 'trash':
                        $trashed = $locked = 0;

                        foreach( (array) $post_ids as $post_id ) {
                            if ( !current_user_can( 'delete_post', $post_id) )
                                wp_die( __('You are not allowed to move this item to the Trash.') );

                            if ( wp_check_post_lock( $post_id ) ) {
                                $locked++;
                                continue;
                            }

                            if ( !wp_trash_post($post_id) )
                                wp_die( __('Error in moving to Trash.') );

                            $trashed++;
                        }

                        $sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
                    break;
                    case 'untrash':
                        $untrashed = 0;
                        foreach( (array) $post_ids as $post_id ) {
                            if ( !current_user_can( 'delete_post', $post_id) )
                                wp_die( __('You are not allowed to restore this item from the Trash.') );

                            if ( !wp_untrash_post($post_id) )
                                wp_die( __('Error in restoring from Trash.') );

                            $untrashed++;
                        }
                        $sendback = add_query_arg('untrashed', $untrashed, $sendback);
                    break;
                    case 'delete':
                        $deleted = 0;
                        foreach( (array) $post_ids as $post_id ) {
                            $post_del = get_post($post_id);

                            if ( !current_user_can( 'delete_post', $post_id ) )
                                wp_die( __('You are not allowed to delete this item.') );

                            if ( !wp_delete_post($post_id) ){
                                wp_die( __('Error in deleting.') );
                            }
                            $deleted++;
                        }
                        $sendback = add_query_arg('deleted', $deleted, $sendback);
                    break;
                    case 'edit':
                        if ( isset($_REQUEST['bulk_edit']) ) {
                            $done = bulk_edit_posts($_REQUEST);

                            if ( is_array($done) ) {
                                $done['updated'] = count( $done['updated'] );
                                $done['skipped'] = count( $done['skipped'] );
                                $done['locked'] = count( $done['locked'] );
                                $sendback = add_query_arg( $done, $sendback );
                            }
                        }
                    break;
                }

                $sendback = remove_query_arg( array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view'), $sendback );

                return $sendback;
        }
    
    
    
    
}
