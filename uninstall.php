<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package DevBrothers_Counter_Manager
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Удаляем опции плагина
delete_option('dbcm_settings');






