<?php
/**
 * Plugin Name: DevBrothers Counter Manager
 * Plugin URI: https://devbrothers.ru/counter-manager/
 * Description: Manage analytics counters (Yandex.Metrika, Google Analytics) for WordPress. Easy insertion of counter code into page head.
 * Version: 1.0.1
 * Author: DevBrothers
 * Author URI: https://devbrothers.ru
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: devbrothers-counter-manager
 * Requires at least: 5.8
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * Requires Plugins: devbrothers-admin-panel
 */

if (!defined('ABSPATH')) {
    exit;
}

define('DBCM_VERSION', '1.0.1');
define('DBCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DBCM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DBCM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DBCM_PREFIX', 'dbcm');

class DevBrothers_Counter_Manager {

    /**
     * @var DevBrothers_Counter_Manager
     */
    private static $instance = null;

    /**
     * @var DBCM_Settings
     */
    public $settings;

    /**
     * @var DBCM_Counter_Manager
     */
    public $counter_manager;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->init_components();
    }

    private function load_dependencies() {
        require_once DBCM_PLUGIN_DIR . 'includes/class-settings.php';
        require_once DBCM_PLUGIN_DIR . 'includes/class-counter-manager.php';
    }

    private function init_hooks() {
        add_action('devbrothers_ready', [$this, 'register_in_devbrothers']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    private function init_components() {
        $this->settings = new DBCM_Settings();
        $this->counter_manager = new DBCM_Counter_Manager($this->settings);
    }

    /**
     * Регистрация плагина в системе DevBrothers
     */
    public function register_in_devbrothers() {
        if (!function_exists('devbrothers_register_plugin')) {
            return;
        }

        devbrothers_register_plugin([
            'id'   => 'counter-manager',
            'name' => __('Counter Manager', 'devbrothers-counter-manager'),
            'name_ru' => __('Счетчики аналитики', 'devbrothers-counter-manager'),
            'description' => __('Управление счетчиками аналитики', 'devbrothers-counter-manager'),
            'version' => DBCM_VERSION,
            'icon' => 'dashicons-chart-line',
            'settings_callback' => [$this->settings, 'render_settings_page'],
            'categories' => [
                [
                    'id'   => 'counters',
                    'name' => __('Счетчики', 'devbrothers-counter-manager'),
                    'icon' => 'dashicons-chart-line',
                ],
                [
                    'id'   => 'consent',
                    'name' => __('Cookie-баннер', 'devbrothers-counter-manager'),
                    'icon' => 'dashicons-shield',
                ],
                [
                    'id'   => 'diagnostics',
                    'name' => __('Диагностика', 'devbrothers-counter-manager'),
                    'icon' => 'dashicons-performance',
                ],
                [
                    'id'   => 'usage',
                    'name' => __('Инструкция', 'devbrothers-counter-manager'),
                    'icon' => 'dashicons-editor-code',
                ],
            ],
        ]);
    }

    /**
     * Подключение стилей и скриптов для админки
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'devbrothers') === false) {
            return;
        }

        wp_enqueue_style(
            'dbcm-admin',
            DBCM_PLUGIN_URL . 'assets/css/admin.css',
            ['devbrothers-admin'],
            DBCM_VERSION
        );

        $js_deps     = ['jquery'];
        $cm_settings = wp_enqueue_code_editor(['type' => 'text/html']);

        if (false !== $cm_settings) {
            $js_deps[] = 'code-editor';
        }

        wp_enqueue_script(
            'dbcm-admin',
            DBCM_PLUGIN_URL . 'assets/js/admin.js',
            $js_deps,
            DBCM_VERSION,
            true
        );

        $config = [
            'strings' => [
                'enabled'  => __('Включён', 'devbrothers-counter-manager'),
                'disabled' => __('Выключен', 'devbrothers-counter-manager'),
            ],
        ];

        if (false !== $cm_settings) {
            $config['codeEditor'] = $cm_settings;
        }

        wp_localize_script('dbcm-admin', 'dbcmConfig', $config);
    }
}

function dbcm_plugin() {
    return DevBrothers_Counter_Manager::get_instance();
}

add_action('plugins_loaded', 'dbcm_plugin', 10);
