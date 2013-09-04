Usage:

          wp_enqueue_script(
                'easy-articles', //Change the name to your custom handle
                ARTICLES_DIR . '/articles.js', //Change to location of your js file
                array('jquery' , 'jquery-ui-datepicker' ),  //Requires both of these
                '1.0.0'      //The Version of your script
        
        );
        
        wp_enqueue_style(
              'datepicker_css',
              CSS_DIR . 'datepicker/datepicker.css'
        );