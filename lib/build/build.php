<?php
            /**
             * Builds the scaffoding
             * 
             * * Tincr config file
             * * MVC Structure
             * * Widget
             * 
             * 
             * @since 7.30.13
             * @author Mat Lipe
             * 
             * @TODO Add a Build for the Homepage Widget Structure
             */

error_reporting(0);         
$controller = ucfirst($argv[3]);                  
/**----------------------------------------------------------------
     Main Array which will replace the %key% in all the template Files
     Add/Change Keys and Values Here
     Arguments passed from CLI will be in $argv[]
#------------------------------------------------------------------ */         
$replace = array(
                'date'       => date('m.d.y'),
                'controller' => $controller,
                'name'       => strtolower($argv[4])
               );

/**
 * Build the Tincr Config File
 * @since 2.18.13
 */
if( $controller == 'Tincr' ){
    $content = str_replace('%theme%', $argv[4], file_get_contents('template/tincr.json') );
    file_put_contents('../../../../../tincr.json', $content);
    echo 'Tincr Config File Built';
    die();
}


/**
* Build the Widget
 * @since 7.30.13
*/
if( $controller == 'Widget' ){
    
    if( file_exists('../../widgets/'.$argv[4].'.php') ) fail();

    $content = str_replace('%class-name%', $argv[4], file_get_contents('template/Widget.php') );
    $success = file_put_contents('../../widgets/'.$argv[4].'.php', replace_content($content) );
    if( $success === false ) fail();
    echo 'Widget File Built';
    die();
}
               
              
        
/**
 * Replace all the vars with values from $replace array
 * @since 2.18.13
 */               
function replace_content( $content ){
    global $replace;
    foreach( $replace as $var => $value ){
        $content = str_replace('%'.$var.'%', $value, $content);
    }
    return $content;
}


/**
 * Outputs a failure message then kills the script
 */
function fail(){
    echo "\r\n Build Failed";
    die();
}

if( !$controller ) fail();
if( file_exists('../../Controller/'.$controller.'Controller.php') ) fail();

/**
 * Build the Controller
 */
$cont = file_get_contents('template/Controller.php'); 
$success = file_put_contents('../../Controller/'.$controller.'Controller.php', replace_content($cont) );

if( $success === false ) fail();
 
/**
 * Build the Model
 */
$cont = file_get_contents('template/Model.php'); 
$success = file_put_contents('../../Model/'.$controller.'.php', replace_content($cont) );

if( $success === false ) fail();
 
/**
 * Create the Views
 */
mkdir('../../View/'.$controller);


echo ' Build Completed';


