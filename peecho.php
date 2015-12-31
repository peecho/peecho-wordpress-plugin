<?php
/*
	Plugin Name: Peecho
	Plugin URI: https://wordpress.org/plugins/
	Description: The Peecho Wordpress plug -in will make it easy for Wordpress users to include Peecho Print button in posts and pages.
	Author: Peecho
	Author URI: http://www.peecho.com/
	Version: 1.0
	License: GPLv2 or later 
	Text Domain: peecho
	
	Copyright 2009-2015 Peecho  (email : artstorm [at] gmail [dot] com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

/** Load all of the necessary class files for the plugin */
@ini_set( 'upload_max_size' , '1264M' );
@ini_set( 'post_max_size', '1264M');
@ini_set( 'max_execution_time', '300000' );
spl_autoload_register('Peecho::autoload');
define('PLUGINURL',plugin_dir_url( __FILE__ ));
define('BASENAME',plugin_basename( __FILE__ )); 

/**
 * Init Singleton Class.
 *
 * @author  Peecho <artstorm at gmail dot com>
 * @link    http://www.peecho.com/
 */
class Peecho{
    /** Holds the plugin instance */
    private static $instance = false;
    /** Define plugin constants */
	
	const MIN_PHP_VERSION     = '5.3.0';
	const MIN_WP_VERSION      = '3.3';
	const OPTION_KEY          = 'peecho_options';
	const USER_META_KEY       = 'peecho';
	const TEXT_DOMAIN         = 'peecho';
	const FILE                = __FILE__;


    public static function getInstance(){
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct(){
        if (!$this->testHost()) {
        	return;
        }
        add_action('init', array($this, 'textDomain'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        add_action('after_setup_theme', array(&$this, 'phpExecState'));
        add_action( 'admin_notices', array(&$this ,'peecho_plugin_notices') );
        
		$style_url = plugins_url('/assets/peecho.css', Peecho::FILE);
        wp_register_style('peecho', $style_url, false, '2.0');
        wp_enqueue_style('peecho');
		
		$script_url1 = plugins_url('/assets/bootstrap.min.js', Peecho::FILE);
        wp_register_script('bootstrap.min.js', $script_url1, false, '2.0');
        wp_enqueue_script('bootstrap.min.js');
		
		$script_url2 = plugins_url('/assets/bootstrap.min.css', Peecho::FILE);
        wp_register_style('bootstrap.min.css', $script_url2, false, '2.0');
        wp_enqueue_style('bootstrap.min.css');
        
		$style_url3 = plugins_url('/assets/magnific-popup.css', Peecho::FILE);
        wp_register_style('popup.css', $style_url3, false, '2.0');
        wp_enqueue_style('popup.css');
		
		$script_url4 = plugins_url('/assets/jquery.magnific-popup.min.js', Peecho::FILE);
        wp_register_script('popup.min.js', $script_url4, false, '2.0');
        wp_enqueue_script('popup.min.js');
		
		new Peecho_Admin;
        new Peecho_WPEditor;
        new Peecho_Shortcode;

		
    }
    public static function autoload($className)
    {
        if (__CLASS__ !== mb_substr($className, 0, strlen(__CLASS__))) {
            return;
        }
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $fileName .= DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, 'src_'.$className);
        $fileName .='.php';

        require $fileName;
    }
    public function textDomain()
    {
        $domain = self::TEXT_DOMAIN;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);
        load_textdomain(
            $domain,
            WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo'
        );
        load_plugin_textdomain(
            $domain,
            false,
            dirname(plugin_basename(__FILE__)).'/lang/'
        );
    }
    public function uninstall()
    {
        delete_option('peecho_options');
        delete_option('user_script_id');
        delete_option('peecho_button_id');
        global $wpdb;
        $wpdb->query(
            "
            DELETE FROM $wpdb->usermeta
            WHERE meta_key = 'peecho'
            "
        );
    }
    public static function getSnippet($name, $variables = ''){
        $snippets = get_option(self::OPTION_KEY, array());
        for ($i = 0; $i < count($snippets); $i++) {
            if ($snippets[$i]['title'] == $name) {
                if (!is_array($variables)) {
                    parse_str(htmlspecialchars_decode($variables), $variables);
                }

                $snippet = $snippets[$i]['snippet'];
                $var_arr = explode(",", $snippets[$i]['vars']);

                if (!empty($var_arr[0])) {
                    for ($j = 0; $j < count($var_arr); $j++) {
                        $snippet = str_replace(
                            "{".$var_arr[$j]."}",
                            $variables[$var_arr[$j]],
                            $snippet
                        );
                    }
                }
                break;
            }
        }
        return do_shortcode($snippet);
    }
    private function testHost(){
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', array(&$this, 'phpVersionError'));
            return false;
        }
        global $wp_version;
        if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
            add_action('admin_notices', array(&$this, 'wpVersionError'));
            return false;
        }
        return true;
    }
    public function phpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %3$s requires PHP version %1$s or greater.<br/>'.
            'Your installed PHP version: %2$s',
            self::MIN_PHP_VERSION,
            PHP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }
    public function wpVersionError()
    {
        echo '<div class="error"><p><strong>';
        printf(
            'Error: %2$s requires WordPress version %1$s or greater.',
            self::MIN_WP_VERSION,
            $this->getPluginName()
        );
        echo '</strong></p></div>';
    }

    private function getPluginName()
    {
        $data = get_plugin_data(self::FILE);
        return $data['Name'];
    }
    public function phpExecState()
    {
        $filter = apply_filters('peecho_php_execution_enabled', true);
        if ($filter == false and !defined('PEECHO_DISABLE_PHP')) {
            _deprecated_function(
                'peecho_php_execution_enabled',
                '2.3',
                'define(\'PEECHO_DISABLE_PHP\', true);'
            );
            define('PEECHO_DISABLE_PHP', true);
        }
    }

    public function peecho_plugin_notices()
    {
        ob_start();
        $plugin = plugin_basename(__FILE__);
        global $pagenow;       
        $userId = get_option('user_script_id');
        $buttonId = get_option('peecho_button_id');
        if($pagenow == 'plugins.php') {
            if (is_plugin_active($plugin)){
                if($userId == '' && $buttonId == ''){ 
				    $dir = plugin_dir_url( __FILE__ ); 
						$x = plugin_basename( __FILE__ );
          
                    echo '<div class="updated" style="background-color:#73A477;">
                        <div><img src="'.$dir.'/image/peecho.png"></div><div style="font-size:17px; color: #fff;  margin-top: -35px; margin-left: 60px; width: 30%;">Almost done. Activate your account </div><div><a href="'.home_url().'/wp-admin/admin.php?page='.$x.'&tab=tools"><div style="padding: 10px;background-color: #508B61;border: 1px solid green;border-radius: 7px;color: #fff;font-size: 15px;  width: 20%; margin-left: 372px; margin-top: -29px;margin-bottom: 3px;">Activate your Peecho account</div></a></div>
                     </div>';
                }
            	
			}
			
        }
		
		
    }
	
	
}


add_action( 'admin_menu', 'register_my_custom_menu_page' );
function register_my_custom_menu_page(){
     $capability = 'manage_options';
    if (defined('PEECHO_ALLOW_EDIT_POSTS')
        and current_user_can('edit_posts')
    ) {
        $allowed = true;
        $capability = 'edit_posts';
    }
    add_menu_page( 'Settings', 'Peecho', 'manage_options', 'customteam', 'my_custom_menu_page',plugins_url( 'assets/peecho.png', __FILE__ ));
    add_submenu_page( 'customteam', 'Button', 'Buttons', 'manage_options', 'customteam', 'my_custom_submenu_page'); 
    add_submenu_page( 'customteam', 'Settings', 'Settings', 'manage_options', 'peecho-settings', 'my_custom_submenu_page_2');
}

function my_custom_menu_page() {
    global $wpdb;
    echo '<div class="wrap">';
	require_once('views/button.php');
    echo '</div>';
}

function my_custom_submenu_page(){
	global $wpdb;
    echo '<div class="wrap">';
    require_once('views/button.php');
    echo '</div>';
}

function my_custom_submenu_page_2(){
	global $wpdb;
	$x = plugin_basename( __FILE__ );
	echo '<script>
	  window.location = "?page='.$x.'&tab=tools";
	</script>';
}

/*=======custom css for the icon ============*/
add_action('admin_head', 'my_custom_css');
function my_custom_css() {
  echo '<style>
    #adminmenu #toplevel_page_customteam .wp-menu-image img {
        height: 30px;
        opacity: 1;
        padding: 2px 0 0;
    }
  </style>';
}

add_action('plugins_loaded', array('Peecho', 'getInstance'));  