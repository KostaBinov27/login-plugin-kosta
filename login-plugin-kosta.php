<?php
/*
Plugin Name: Login Plugin Kosta
Plugin URI: https://www.linkedin.com/in/kosta-binov/
Description:  On login data will be sent to a 3rd party login service, that will happen on server side.
Author: Kosta Binov
Author URI: https://www.linkedin.com/in/kosta-binov/
Version: 1.0.0
*/

function js_script() {
    wp_register_script( 'login_jquery',  plugins_url( '/assets/js/jquery.min.js', __FILE__ ), array(), false, true);
    wp_enqueue_script( 'login_jquery' );

    wp_register_script('login-scripts', plugins_url( '/assets/js/scripts.js', __FILE__ ), array(), false, true);
    wp_enqueue_script( 'login-scripts' );

    wp_enqueue_style('bootstrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css');
    wp_enqueue_style( 'bootstrap4' );

    wp_enqueue_script( 'boot2','https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot2' );

    wp_enqueue_script( 'boot3','https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js', array( 'jquery' ),'',true );
    wp_enqueue_script( 'boot3' );
}
add_action( 'wp_enqueue_scripts', 'js_script' );

function ip_blocking(){
    global $table_prefix, $wpdb;

    $tblname = 'ip_blocking';
    $wp_track_table = $table_prefix . "$tblname ";

    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $wp_track_table ( id int(255) NOT NULL AUTO_INCREMENT, email varchar(128) NOT NULL, counter int(255) NOT NULL, ip_address varchar(128) NOT NULL, last_login timestamp NOT NULL, PRIMARY KEY  (id)) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}
register_activation_hook( __FILE__, 'ip_blocking' );

//Register a custom menu page.
function wpdocs_register_my_custom_menu_page(){
    add_menu_page( __( 'Login Settings', 'textdomain' ), 'Login Settings', 'manage_options', 'login-settings', 'login_view', 'dashicons-admin-generic', 80 ); 
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );
 
// Display a custom menu page
function login_view() {
    include_once( 'login-view.php' );
}

//Shortcode including
function shortcode_funct() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    ob_start();
    include_once( 'shortcode.php' );   
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

add_shortcode('login_form', 'shortcode_funct');

//API Register
add_action( 'rest_api_init', function () {
    register_rest_route( 'login/api', '/endpoint', array(
        'methods'  => 'GET',
        'callback' => 'login_api',
    ));
});

function login_api($request) {
    $mail = $_GET['emailaddress'];
    $pass = base64_decode($_GET['password']);

    //open CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://reqres.in/api/login");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "email=".$mail."&password=".$pass);

    // Receive server response
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);  
    print_r($server_output);
    curl_close ($ch);  
}

//API Register for IP block
add_action( 'rest_api_init', function () {
    register_rest_route( 'ip/block', '/endpoint', array(
        'methods'  => 'GET',
        'callback' => 'ip_block',
    ));
});

function ip_block($request) {
    $ipAddress = $_GET['ipAddress'];
    $mail = $_GET['emailaddress'];
    $ressetcounter = $_GET['ressetcounter'];

    global $table_prefix, $wpdb;

    $insertTable = $table_prefix.'ip_blocking';
    $result = $wpdb->get_results( "SELECT * FROM $insertTable WHERE email LIKE '$mail' AND ip_address LIKE '$ipAddress'");

    if ($result){
        $counter = $result['0']->counter;
        if ($counter < 3){
            $counter = $counter + 1;
            $wpdb->query("UPDATE $insertTable SET counter = $counter WHERE email LIKE '$mail'");
            echo '0';
        } else {
            $rezMinBlock = $wpdb->get_results("SELECT * FROM $insertTable WHERE email LIKE '$mail' AND TIMESTAMPDIFF(MINUTE, last_login, NOW()) > 30");
            if ($rezMinBlock){
                $wpdb->query("UPDATE $insertTable SET counter = '0' WHERE email LIKE '$mail'");
                echo '0';
            } else {
                if ($ressetcounter == 'true'){
                    $wpdb->query("UPDATE $insertTable SET counter = '0' WHERE email LIKE '$mail'");
                    echo '0';
                } else {
                    echo '1';
                }
            }
        }
    } else {
        $wpdb->insert($insertTable, array( 'email' => $mail, 'ip_address' => $ipAddress, 'counter' => '0', ));
        echo '0';
    }
}
