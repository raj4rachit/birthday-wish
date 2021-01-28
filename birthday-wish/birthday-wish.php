<?php
/*
Plugin Name: Birthday Wish
Plugin URI: http://technobrains.io
Description: Send email to employee's birthday
Version: 1.0.0
Author: Technobrains
Author URI: http://technobrains.io
*/

/**
 * Basic plugin definitions 
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */


/**
 * Activation Hook
 *
 * Register plugin activation hook.
 *
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_activation(){
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_prefix = $wpdb->prefix;
    $table_name = $table_prefix. 'email_send';
    if($wpdb->get_var( "show tables like '$db_table_name'" ) != $db_table_name ) {
        $sql = "CREATE TABLE $table_name(
            id int(10) AUTO_INCREMENT,
            employee_id int(10),
            employee_date date,
            email_send int(5),
            PRIMARY KEY(`id`)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    if ( ! wp_next_scheduled( 'bw_cron' ) ) {
        wp_schedule_event( time(), 'twice_per_day' , 'bw_cron' );
    }

}
register_activation_hook( __FILE__, 'bw_activation' );


/**
 * Cron Schedule
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_cron_schedule($schedules){
    $schedules['once_per_day'] = array(
        'interval' => 60,
        'display' => 'Once Every 1 Hour',
    );

    $schedules['twice_per_day'] = array(
        'interval' => 7200,
        'display' => 'Once Every 2 Hours',
    );

    $schedules['every_hour'] = array(
		'interval' => 14400,
		'display'  => 'Once Every 4 Hours',
    );
    
	return $schedules;
}
add_filter( 'cron_schedules', 'bw_cron_schedule', 10, 1 );


/**
 * Plugin Menu
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_menu(){

    add_menu_page('Birthday Wish', 'Birthday Wish', 'manage_options', 'birthday-wish', 'bw_settings_page');
    add_action( 'admin_init', 'bw_settings_page_init');
}
add_action('admin_menu', 'bw_menu');


/**
 * Settings
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_settings_page(){ 
    
    $bw_settings_options = get_option('bw_settings_option_name');
    $bw_cron_time = $bw_settings_options['bw_select_cron_time'];
    
?>
	<div class="wrap">
	    <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php
                settings_fields( 'bw_settings_option_group' );
                do_settings_sections( 'bw-settings-admin' );
                submit_button('Submit');
            ?>
        </form>
	</div>
<?php
}
	
function bw_settings_page_init(){
	register_setting(
		'bw_settings_option_group', // option_group
		'bw_settings_option_name', // option_name
		'bw_settings_sanitize'
	);
	add_settings_section(
		'bw_settings_setting_section', // id
		'Birthday Wish Settings', // title
		'bw_settings_section_info', // callback
		'bw-settings-admin' // page
    );
    add_settings_field(
        'bw_api_url',
        'API URL',
        'bw_api_callback',
        'bw-settings-admin',
        'bw_settings_setting_section'
    );
    add_settings_field(
        'bw_subject', // id
        'Subject', // title
        'bw_subject_callback', // callback
        'bw-settings-admin', // page
        'bw_settings_setting_section' // section
    );
	add_settings_field(
		'bw_message',
		'Birthday Message',
		'bw_message_callback',
		'bw-settings-admin',
		'bw_settings_setting_section'
    );
    add_settings_field(
        'bw_aniversary_message',
        'Anniversary Message',
        'bw_anniversary_message_callback',
        'bw-settings-admin',
        'bw_settings_setting_section'
    );

}
	
function bw_settings_sanitize($input) {
    $sanitary_values = array();
    if( isset( $input['bw_api_url'] ) ) {
        $sanitary_values['bw_api_url'] = sanitize_text_field( $input['bw_api_url'] );
    }

    if( isset( $input['bw_subject'] ) ) {
        $sanitary_values['bw_subject'] = sanitize_text_field( $input['bw_subject'] );
    }

    if ( isset( $input['bw_message'] ) ) {
	    $sanitary_values['bw_message'] = sanitize_text_field( $input['bw_message'] );
    }

    if( isset( $input['bw_aniversary_message'] ) ) {
        $sanitary_values['bw_aniversary_message'] = sanitize_text_field( $input['bw_aniversary_message'] );
    }

	return $sanitary_values;
}

function bw_settings_section_info() {
    echo '';
}


/**
 * Callback Function for bw_api_url
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_api_callback() {
    $bw_settings_options = get_option('bw_settings_option_name');
    $bw_api_url = $bw_settings_options['bw_api_url'];
?>
    <input type="text" name="bw_settings_option_name[bw_api_url]" id="bw_api_url" value="<?= $bw_api_url; ?>">
<?php
}

/**
 * Callback Function for bw_subject
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_subject_callback() {
    $bw_settings_options = get_option('bw_settings_option_name');
    $bw_subject = $bw_settings_options['bw_subject']
?>
    <input type="text" name="bw_settings_option_name[bw_subject]" id="bw_subject" value="<?= $bw_subject; ?>">
<?php
}

/**
 * Callback Function for bw_message
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_message_callback() {
    $bw_settings_options = get_option('bw_settings_option_name');
    $bw_message = $bw_settings_options['bw_message'];
?>
    <textarea name="bw_settings_option_name[bw_message]" id="bw_message" rows="10" cols="50"><?= $bw_message; ?></textarea>
<?php
}

/**
 * Callback Function for bw_aniversary_message
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_anniversary_message_callback() {
    $bw_settings_options = get_option('bw_settings_option_name');
    $bw_aniversary_msg = $bw_settings_options['bw_aniversary_message'];
?>
    <textarea name="bw_settings_option_name[bw_aniversary_message]" id="bw_aniversary_message" rows="10" cols="50"><?= $bw_aniversary_msg; ?></textarea>
<?php
}



/**
 * Call API
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
function callApi($collection){

    $bw_settings_options = get_option('bw_settings_option_name');
    $api_url = $bw_settings_options['bw_api_url'];

    if(!empty($api_url)){
        $url = $api_url.$collection;
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $curl_data = curl_exec($curl_handle);
        curl_close($curl_handle);
        $response_data = json_decode($curl_data);
    
        return $response_data;
    }

    return false;

}


/**
 * To Send Mail
 * 
 * @package Birthday Wish
 * @since 1.0.0
 */
 */
function bw_cron() {
    if ( is_plugin_active('birthday-wish/birthday-wish.php') ) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        global $wpdb;
        //call apis
        $employee = callApi('employees');
        $employeetoexcl = callApi('do-not-send-birthday-wishes');
    
        foreach($employee as $getbdy){
    
            //employee birthday date compare
            $bdy = strtotime($getbdy->dateOfBirth);
            $bdydate = date('d-m',$bdy);
            $bdyDate = $bdydate.date('-Y');
    
            $addbdy = date('Y-m-d',$bdy);
    
            //today's date
            $today = date('d-m-Y');
    
            //employee exit date
            $exitDate = $getbdy->employmentEndDate;
    
            //employee join date compare
            $currentDate = date('d-m-Y');
            $empstartdate = strtotime($getbdy->employmentStartDate);
            $empjoin = date('d-m-Y',$empstartdate);
            
            //get field value
            $bw_settings_options = get_option('bw_settings_option_name');
            $bw_message = $bw_settings_options['bw_message'];
            $bw_subject = $bw_settings_options['bw_subject'];
            
            //employee name
            $employeeName = $getbdy->name;
            
            //employee id
            $id = $getbdy->id;
    
            //id to exclude
            foreach($employeetoexcl as $excl){
                $exid = $excl;
            }
    
            $table = $wpdb->prefix.'email_send';
            $result = $wpdb->get_results ( "SELECT * FROM $table WHERE `email_send` = 1");
            foreach($result as $res){
                $emailid = $res->employee_id;
                $emailSend = $res->email_send;
            }
    
            if(!empty($bw_message) && !empty($bw_subject) && ($emailSend != 1) ){
                if(($today == $bdyDate) && ($exitDate == '') && ($currentDate >= $empjoin) && ($id != $exid)){
    
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    $headers[] = 'From: TechnoBrains <info@technobrains.io>';

                    if(wp_mail('test@gmail.com', $bw_subject, $bw_message .' '.$employeeName, $headers)){
                        $table_name = $wpdb->prefix.'email_send';
                        $wpdb->insert(
                            $table_name,
                            array(
                                'employee_id' => $id,
                                'employee_date' => $addbdy,
                                'email_send' => 1
                            )
                        );
                    }
                }
            }
        }
    }else{
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
}
add_action('bw_cron','bw_cron');


/**
 * Deactivation Hook
 *
 * Register plugin deactivation hook.
 *
 * @package Birthday Wish
 * @since 1.0.0
 */
function bw_deactivation(){
    wp_unschedule_event( wp_next_scheduled( 'bw_cron' ), 'bw_cron' );
}
register_deactivation_hook( __FILE__, 'bw_deactivation' );


/**
 * Uninstall Hook
 *
 * Register plugin uninstall hook.
 *
 * @package Birthday Wish
 * @since 1.0.0
 */
register_uninstall_hook( __FILE__, 'bw_uninstall');
function bw_uninstall(){
    global $wpdb;
    $table_name = $wpdb->prefix. 'email_send';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);

    delete_option('bw_settings_option_name');
}