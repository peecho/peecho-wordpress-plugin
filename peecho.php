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
spl_autoload_register('Peecho::autoload');

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

    /**
     * Singleton class
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        if (!$this->testHost()) {
            return;
        }
        add_action('init', array($this, 'textDomain'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

        add_action('after_setup_theme', array(&$this, 'phpExecState'));
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
    private function testHost()
    {
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
}

add_action('plugins_loaded', array('Peecho', 'getInstance'));
