<?php
/**
 * Plugin Name: Commercium for WooCommerce
 * Plugin URI: https://github.com/CommerciumBlockchian/commercium-for-woocommerce
 * Description: Commercium for WooCommerce plugin allows you to accept payments in Commercium for physical and digital products at your WooCommerce-powered online store.
 * Version: 1.0.0
 * Author: Commercium Team
 * Author URI: https://www.commercium.net
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: commercium-for-woocommerce
 *
 * @package WordPress
 * @author innerfire
 * @since 1.0.0
 */


// Include everything
include (dirname(__FILE__) . '/cmm-include-all.php');

//---------------------------------------------------------------------------
// Add hooks and filters

// create custom plugin settings menu
add_action( 'admin_menu',                   'CMM_create_menu' );

register_activation_hook(__FILE__,          'CMM_activate');
register_deactivation_hook(__FILE__,        'CMM_deactivate');
register_uninstall_hook(__FILE__,           'CMM_uninstall');

add_filter ('cron_schedules',               'CMM__add_custom_scheduled_intervals');
add_action ('BWWC_cron_action',             'CMM_cron_job_worker');     // Multiple functions can be attached to 'BWWC_cron_action' action

CMM_set_lang_file();
//---------------------------------------------------------------------------

//===========================================================================
// activating the default values
function CMM_activate()
{
    global  $g_CMM__config_defaults;

    $cmm_default_options = $g_CMM__config_defaults;

    // This will overwrite default options with already existing options but leave new options (in case of upgrading to new version) untouched.
    $cmm_settings = CMM__get_settings ();

    foreach ($cmm_settings as $key=>$value)
    	$cmm_default_options[$key] = $value;

    update_option (CMM_SETTINGS_NAME, $cmm_default_options);

    // Re-get new settings.
    $cmm_settings = CMM__get_settings ();

    // Create necessary database tables if not already exists...
    CMM__create_database_tables ($cmm_settings);
    CMM__SubIns ();

    //----------------------------------
    // Setup cron jobs

    if ($cmm_settings['enable_soft_cron_job'] && !wp_next_scheduled('CMM_cron_action'))
    {
    	$cron_job_schedule_name = strpos($_SERVER['HTTP_HOST'], 'ttt.com')===FALSE ? $cmm_settings['soft_cron_job_schedule_name'] : 'seconds_30';
    	wp_schedule_event(time(), $cron_job_schedule_name, 'CMM_cron_action');
    }
    //----------------------------------

}
//---------------------------------------------------------------------------
// Cron Subfunctions
function CMM__add_custom_scheduled_intervals ($schedules)
{
	$schedules['seconds_30']     = array('interval'=>30,     'display'=>__('Once every 30 seconds'));     // For testing only.
	$schedules['minutes_1']      = array('interval'=>1*60,   'display'=>__('Once every 1 minute'));
	$schedules['minutes_2.5']    = array('interval'=>2.5*60, 'display'=>__('Once every 2.5 minutes'));
	$schedules['minutes_5']      = array('interval'=>5*60,   'display'=>__('Once every 5 minutes'));

	return $schedules;
}
//---------------------------------------------------------------------------
//===========================================================================

//===========================================================================
// deactivating
function CMM_deactivate ()
{
    // Do deactivation cleanup. Do not delete previous settings in case user will reactivate plugin again...

   //----------------------------------
   // Clear cron jobs
   wp_clear_scheduled_hook ('CMM_cron_action');
   //----------------------------------
}
//===========================================================================

//===========================================================================
// uninstalling
function CMM_uninstall ()
{
    $cmm_settings = CMM__get_settings();

    if ($cmm_settings['delete_db_tables_on_uninstall'])
    {
        // delete all settings.
        delete_option(CMM_SETTINGS_NAME);

        // delete all DB tables and data.
        CMM__delete_database_tables ();
    }
}
//===========================================================================

//===========================================================================
function CMM_create_menu()
{

    // create new top-level menu
    // http://www.fileformat.info/info/unicode/char/e3f/index.htm
    add_menu_page (
        __('Woo Commercium', CMM_I18N_DOMAIN),                    // Page title
        __('Commercium', CMM_I18N_DOMAIN),                        // Menu Title - lower corner of admin menu
        'administrator',                                        // Capability
        'cmm-settings',                                        // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
        'CMM__render_general_settings_page',                   // Function

        plugins_url('/images/cmm_16x.png', __FILE__)      // Icon URL
        );

    add_submenu_page (
        'cmm-settings',                                        // Parent
        __("Commercium for WooCommerce", CMM_I18N_DOMAIN),                   // Page title
        __("General Settings", CMM_I18N_DOMAIN),               // Menu Title
        'administrator',                                        // Capability
        'cmm-settings',                                        // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
        'CMM__render_general_settings_page'                    // Function
        );
}
//===========================================================================

//===========================================================================
// load language files
function CMM_set_lang_file()
{
    # set the language file
    $currentLocale = get_locale();
    if(!empty($currentLocale))
    {
        $moFile = dirname(__FILE__) . "/lang/" . $currentLocale . ".mo";
        if (@file_exists($moFile) && is_readable($moFile))
        {
            load_textdomain(CMM_I18N_DOMAIN, $moFile);
        }

    }
}
//===========================================================================
/*
function tl_save_error() {
    update_option( 'plugin_error',  ob_get_contents() );
}
add_action( 'activated_plugin', 'tl_save_error' );

echo get_option( 'plugin_error' );

file_put_contents( 'C:\errors' , ob_get_contents() );
*/