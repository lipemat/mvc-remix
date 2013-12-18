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
 * @uses extend into another class or construct
 * 
 * @param string [$postType] = the post type to display (defaults to post )
 * @param class [$class] = a class to use for the column output methods
 * 
 * @since 12.18.13
 * 
 * @author Mat Lipe <mat@matlipe.com>
 * 
 */
class MvcPostListsTable extends WP_Posts_List_Table{
    private $post_type;
    public $wp_list_table;
    private $attached_class;
    
    //default args
    private $args = array(
        'paginate' => true,
        'date_filter' => true,
        'bulk_action' => true
    
    );
    
    
    /**
     * Assign the post type and init the table
     * 
     * @param string $postType (defaults to post)
     * @param class $class = The class to use for the custom column outputs (if not extending this class );
     */
    function __construct( $postType = 'post', $class = false, $args = array()){
        
        $this->args = wp_parse_args( $args, $this->args );
        
        if( !$this->args['paginate'] ){
            $this->setPaginateArgs(array('per_page' => 999999999) );   
        }
  
        $this->post_type = $postType;
         
        $_GET['post_type'] = $this->post_type;
        $args['screen'] = WP_Screen::get( $this->post_type );
        
        parent::__construct( $args );
        
        if( $class && is_object($class) ){
            $this->attached_class = $class;
        } else {
            $this->attached_class = $this;   
        }
        
        add_action( "manage_{$postType}_posts_custom_column", array( $this, 'attachCustomColumnsToClass'),9, 2 );
       
    }
    
    
    /**
     * Overrides the default pagination args
     * 
     * @param array $args array( ['total_items'] => int,[ 'per_page'] => int, [total_pages] => int );
     * @uses all are option so may be used to override one or all
     * 
     * @since 12.18.13
     */
    public function setPaginateArgs($args){
         $this->set_pagination_args( $args );
    }
        
    
    
    /**
     * Ability to turn off pagination if set in the args to do so
     * 
     * @overrides the parent method
     *
     * @uses called automatically
     * @uses defaults to parent::pagination()
     * 
     * 
     */
    function pagination($where){
        if( !$this->args['paginate'] ) return;
        
        parent::pagination($where);
    }
    
    
    
    /**
     * Ability to turn off bulk_actions if set in the args to do so
     * 
     * @overrides the parent method
     *
     * @uses called automatically
     * @uses defaults to parent::bulk_actions()
     * 
     * 
     */
    function bulk_actions(){
        if( !$this->args['bulk_action'] ) return;
        
        parent::bulk_actions();
        
    }
    
    /**
     * Ability to turn off date_filter if set in the args to do so
     * 
     * @overrides the parent method
     *
     * @uses called automatically
     * @uses defaults to parent::extra_tablenav()
     * 
     * 
     */
    function extra_tablenav(){
         if( !$this->args['date_filter'] ) return;
        
        parent::extra_tablenav();
        
    }


    /**
     * Runs the attached classes method 'column_$column' or 'column_default' if that does not exist
     * 
     * If a class was passed during construct that class will be used, otherwise the class extending this will be used
     * This overrides the default behavior of the Wp_Post List Table to use the Wp List Table structure
     * 
     * @since 12.18.13
     * 
     * @param string $column - column name
     * @param int $postId - the post's id
     * 
     * @uses added to the manage_$postType_posts_custom_column action by self::__construct()
     * 
     * 
     */
    function attachCustomColumnsToClass( $column, $postId ){
          if( method_exists( $this->attached_class, 'column_' . $column ) ) {
              echo $this->attached_class->{'column_' . $column}($postId);;
          } elseif( method_exists( $this->attached_class, 'column_default' ) ){
              echo $this->attached_class->column_default($postId, $column);
          }
    }
    

    /**
     * Overrides the columns using data from self::setColumns()
     * 
     * @uses parent::get_column_info() if empty
     * 
     * @since 12.18.13
     */
    public function get_column_info(){
        if( empty( $this->columns ) ) return parent::get_column_info();

   //     return parent::get_column_info();

        return $this->columns;
        
    }
    
    
    /**
     * Set the columns for the table
     * 
     * @since 12.18.13
     * 
     * @param array $columns array( 'key' => 'label' )
     * @param array $sortable array( 'key' => 'orderby' ) //columns which can sort
     * @param array $hidden array( 'key' ) //columns which are hidden
     * 
     * 
     */
    public function setColumns($columns, $_sortable = array(), $hidden = array()){
        
        $sortable = array();
        foreach ( $_sortable as $id => $data ) {
            if ( empty( $data ) )
                continue;

            $data = (array) $data;
            if ( !isset( $data[1] ) )
                $data[1] = false;

            $sortable[$id] = $data;
        }


        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        
        $this->columns = array( $columns, $hidden, $sortable);   
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

        if( $action = $this->current_action() ){
            $this->displayMessage( $this->updatePosts($action) );
       }

        $this->prepare_items();
        ob_start();
            $this->views();
        echo str_replace( 'edit.php', '', ob_get_clean() ); 
        ?>
        <form id="posts-filter" action="" method="get">
            <input type="hidden" name="post_status" class="post_status_page" value="<?php echo !empty($_REQUEST['post_status']) ? esc_attr($_REQUEST['post_status']) : 'all'; ?>" />
            <input type="hidden" name="post_type" class="post_type_page" value="<?php echo $this->post_type; ?>" />
            <input type="hidden" name="show_sticky" value="1" />

            <?php $this->display(); ?>
       </form>
       
       <?php

        if ( $this->has_items() && $this->args['bulk_action'] ){
            $this->inline_edit();
        }
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
