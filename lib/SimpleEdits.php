<?php
/**
 * Mimicks the Genesis Simple Edits 
 * @since 5.14.13
 * @author Mat Lipe
 * 
 * @filters simple-edits-output - for filtering the footer output
 * 
 * @uses had to add do_shortcode to the links
 */
class SimpleEdits extends MvcString{
    
    /** Constructor */
    function __construct() {
        
        define( 'SE_SETTINGS_FIELD', 'vimm-gse-settings' );

        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_menu' ), 15 );
        add_action( 'admin_notices', array( $this, 'notices' ) );
        
        add_filter( 'genesis_post_info', array( $this, 'post_info_filter' ), 20 );
        add_filter( 'genesis_post_meta', array( $this, 'post_meta_filter' ), 20 );
        add_filter( 'genesis_footer_output', array( $this, 'footer_output_filter' ), 20 );
        add_filter( 'genesis_footer_output', 'shortcode_unautop' );
        add_filter( 'genesis_footer_output', 'do_shortcode' );
    }
    
    
    
    function register_settings() {
        register_setting( SE_SETTINGS_FIELD, SE_SETTINGS_FIELD );
        add_option( SE_SETTINGS_FIELD, $this->settings_defaults() );
    }



    function notices() {
        if ( ! isset( $_REQUEST['page'] ) || 'genesis-simple-edits' != $_REQUEST['page'] )
            return;
        elseif ( isset( $_REQUEST['updated'] ) && 'true' == $_REQUEST['updated'] ) {  
            echo '<div id="message" class="updated"><p><strong>' . __( 'Simple Edits Saved', 'gse' ) . '</strong></p></div>';
        }
        
    }
    
    
    /**
     * Set the defaults HEre
     * @since 4.24.13
     */
    function settings_defaults() {
        $simple_edits_options['footer_output'] = '[footer_copyright]   [vimm_sitemap_link]  [vimm_link]    [footer_loginout]';
        $simple_edits_options['post_meta'] ='[post_categories] [post_tags]';
        $simple_edits_options['post_info']='[post_date] By [post_author_posts_link] [post_comments] [post_edit]'; 
        
        return $simple_edits_options;
    }
    
    
    function add_menu() {
        remove_submenu_page('genesis', 'genesis-simple-edits');
        add_submenu_page('genesis', __('Simple Edits','gse'), __('Simple Edits','gse'), 'manage_options', 'simple-edits', array( &$this, 'admin_page' ) );
    
    }
    
    
    /**
     * The Output of the settings PAGe
     * @since 4.24.13
     */
    function admin_page() { ?>
        <script type="text/javascript">
        jQuery(document).ready(function ( $) {
        $('a.post-shortcodes-toggle').click(function() {
            $('.post-shortcodes').toggle();
            return false;
        });
        $('a.footer-shortcodes-toggle').click(function() {
            $('.footer-shortcodes').toggle();
            return false;
        });    
        }) ;
        </script>
        
        <div class="wrap">
            <form method="post" action="options.php">
            <?php settings_fields( SE_SETTINGS_FIELD ); // important! ?>
            
            <?php screen_icon( 'options-general' ); ?>  
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
                
                <table class="form-table"><tbody>
                    
                    <tr>
                        <th scope="row"><p><label for="<?php echo SE_SETTINGS_FIELD; ?>[post_info]"><b><?php _e('Post Info', 'genesis'); ?></b></label></p></th>
                        <td>
                            <p><input type="text" name="<?php echo SE_SETTINGS_FIELD; ?>[post_info]" id="<?php echo SE_SETTINGS_FIELD; ?>[post_info]" value="<?php echo esc_attr( genesis_get_option('post_info', SE_SETTINGS_FIELD) ); ?>" size="125" /></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><p><label for="<?php echo SE_SETTINGS_FIELD; ?>[post_meta]"><b><?php _e('Post Meta', 'genesis'); ?></b></label></p></th>
                        <td>
                            <p><input type="text" name="<?php echo SE_SETTINGS_FIELD; ?>[post_meta]" id="<?php echo SE_SETTINGS_FIELD; ?>[post_meta]" value="<?php echo esc_attr( genesis_get_option('post_meta', SE_SETTINGS_FIELD) ); ?>" size="125" /></p>
                            
                            <p><small><a class="post-shortcodes-toggle" href="#">Show Available Post Info/Meta Shortcodes</a></small></p>
                            
                        </td>
                    </tr>
                    
                    <tr class="post-shortcodes" style="display: none;">
                        <th scope="row"><p><span class="description"><?php _e('Shortcode Reference'); ?></span></p></th>
                        <td>
                                <ul>
                                    <li>[post_date] - <span class="description"><?php _e('Date the post was published', ''); ?></span></li>
                                    <li>[post_time] - <span class="description"><?php _e('Time the post was published', ''); ?></span></li>
                                    <li>[post_author] - <span class="description"><?php _e('Post author display name', ''); ?></span></li>
                                    <li>[post_author_link] - <span class="description"><?php _e('Post author display name, linked to their website', ''); ?></span></li>
                                    <li>[post_author_posts_link] - <span class="description"><?php _e('Post author display name, linked to their archive', ''); ?></span></li>
                                    <li>[post_comments] - <span class="description"><?php _e('Post comments link', ''); ?></span></li>
                                    <li>[post_tags] - <span class="description"><?php _e('List of post tags', ''); ?></span></li>
                                    <li>[post_categories] - <span class="description"><?php _e('List of post categories', ''); ?></span></li>
                                    <li>[post_edit] - <span class="description"><?php _e('Post edit link (visible to admins)', ''); ?></span></li>
                                </ul>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><p><b><?php _e('Footer Output', 'gse'); ?></b></p></th>
                        <td>
                            <p><textarea name="<?php echo SE_SETTINGS_FIELD; ?>[footer_output]" cols="80" rows="5"><?php echo esc_html( htmlspecialchars( genesis_get_option('footer_output', SE_SETTINGS_FIELD) ) ); ?></textarea></p>
                            
                            <p><small><a class="footer-shortcodes-toggle" href="#">Show Available Footer Shortcodes</a></small></p>
                        </td>
                    </tr>
                    
                    <tr class="footer-shortcodes" style="display: none;">
                        <th scope="row"><p><span class="description"><?php _e('Shortcode Reference'); ?></span></p></th>
                        <td>
                            <p><span class="description"><?php _e('NOTE: For a more comprehensive shortcode usage guide, <a href="http://dev.studiopress.com/shortcode-reference" target="_blank">see this page</a>.') ?>
                            <p>
                                <ul>
                                    <li>[vimm_link] - <span class="description"><?php _e('"Site by Vivid Image" Link', ''); ?></span></li>
                                    <li>[vimm_sitemap_link] - <span class="description"><?php _e('"LInk to Sitemap Page"', ''); ?></span></li>
                                    <li>[footer_backtotop] - <span class="description"><?php _e('The "Back to Top" Link', ''); ?></span></li>
                                    <li>[footer_copyright] - <span class="description"><?php _e('The Copyright notice', ''); ?></span></li>
                                    <li>[footer_childtheme_link] - <span class="description"><?php _e('The Child Theme Link', ''); ?></span></li>
                                    <li>[footer_genesis_link] - <span class="description"><?php _e('The Genesis Link', ''); ?></span></li>
                                    <li>[footer_studiopress_link] - <span class="description"><?php _e('The StudioPress Link', ''); ?></span></li>
                                    <li>[footer_wordpress_link] - <span class="description"><?php _e('The WordPress Link', ''); ?></span></li>
                                    <li>[footer_loginout] - <span class="description"><?php _e('Log In/Out Link', ''); ?></span></li>
                                </ul>
                            </p>
                        </td>
                    </tr>
                    
                </tbody></table>
                
                <div class="bottom-buttons">
                    <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'genesis') ?>" />
                    <input type="submit" class="button-highlighted" name="<?php echo SE_SETTINGS_FIELD; ?>[reset]" value="<?php _e('Reset Settings', 'genesis'); ?>" />
                </div>
                
            </form>
        </div>
        
    <?php }
    
    
    
    
    function post_info_filter( $output ) {
       
        $output = $this->wrapPipes( genesis_get_option( 'post_info', SE_SETTINGS_FIELD ) );
        return do_shortcode($output);
        
    }
    
    
    
    
    function post_meta_filter( $output ) {
        
        $output = $this->wrapPipes(genesis_get_option( 'post_meta', SE_SETTINGS_FIELD ) );

        return do_shortcode($output);
        
    }
    
    
    function footer_output_filter( $output ) {
            
         $output =  $this->wrapPipes(apply_filters('simple-edits-output', genesis_get_option( 'footer_output', SE_SETTINGS_FIELD ) ));
         
         return do_shortcode( $output );
         
    }
    
}

$Genesis_Simple_Edits = new SimpleEdits;