<?php
/**
Commercium for WooCommerce
https://github.com/CommerciumBlockchian/commercium-for-woocommerce
 */

//---------------------------------------------------------------------------
// Global definitions
if (!defined('CMM_PLUGIN_NAME'))
  {
  define('CMM_VERSION',           '0.0.1');

  //-----------------------------------------------
  define('CMM_EDITION',           'Standard');


  //-----------------------------------------------
  define('CMM_SETTINGS_NAME',     'CMM-Settings');
  define('CMM_PLUGIN_NAME',       'Commercium for WooCommerce');


  // i18n plugin domain for language files
  define('CMM_I18N_DOMAIN',       'cmm');

  if (extension_loaded('gmp') && !defined('USE_EXT'))
    define ('USE_EXT', 'GMP');
  else if (extension_loaded('bcmath') && !defined('USE_EXT'))
    define ('USE_EXT', 'BCMATH');
  }
//---------------------------------------------------------------------------

//------------------------------------------
// Load wordpress for POSTback, WebHook and API pages that are called by external services directly.
if (defined('CMM_MUST_LOAD_WP') && !defined('WP_USE_THEMES') && !defined('ABSPATH'))
   {
   $g_blog_dir = preg_replace ('|(/+[^/]+){4}$|', '', str_replace ('\\', '/', __FILE__)); // For love of the art of regex-ing
   define('WP_USE_THEMES', false);
   require_once ($g_blog_dir . '/wp-blog-header.php');

   // Force-elimination of header 404 for non-wordpress pages.
   header ("HTTP/1.1 200 OK");
   header ("Status: 200 OK");

   require_once ($g_blog_dir . '/wp-admin/includes/admin.php');
   }
//------------------------------------------


// This loads necessary modules and selects best math library
if (!class_exists('bcmath_Utils')) require_once (dirname(__FILE__) . '/libs/util/bcmath_Utils.php');
if (!class_exists('gmp_Utils')) require_once (dirname(__FILE__) . '/libs/util/gmp_Utils.php');
if (!class_exists('CurveFp')) require_once (dirname(__FILE__) . '/libs/CurveFp.php');
if (!class_exists('Point')) require_once (dirname(__FILE__) . '/libs/Point.php');
if (!class_exists('NumberTheory')) require_once (dirname(__FILE__) . '/libs/NumberTheory.php');
require_once (dirname(__FILE__) . '/libs/CMMElectroHelper.php');

require_once (dirname(__FILE__) . '/cmm-cron.php');
require_once (dirname(__FILE__) . '/cmm-mpkgen.php');
require_once (dirname(__FILE__) . '/cmm-utils.php');
require_once (dirname(__FILE__) . '/cmm-admin.php');
require_once (dirname(__FILE__) . '/cmm-render-settings.php');
require_once (dirname(__FILE__) . '/cmm-commercium-gateway.php');
