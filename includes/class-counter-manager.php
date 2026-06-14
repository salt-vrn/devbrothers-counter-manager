<?php
/**
 * Класс для управления вставкой кода счетчиков
 * 
 * @package DevBrothers_Counter_Manager
 */

// Защита от прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Класс для вставки кода счетчиков в head страницы
 */
class DBCM_Counter_Manager {
    
    /**
     * Экземпляр настроек
     * @var DBCM_Settings
     */
    private $settings;
    private $consent_cookie_name = 'dbcm_cookie_consent';
    
    /**
     * Конструктор
     * 
     * @param DBCM_Settings $settings Экземпляр настроек
     */
    public function __construct($settings) {
        $this->settings = $settings;
        $this->init_hooks();
    }
    
    /**
     * Инициализация хуков WordPress
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets'], 999);
        add_action('wp_head', [$this, 'output_counters'], 1);
        add_action('wp_footer', [$this, 'output_cookie_banner']);
    }
    
    /**
     * Вывод кода счетчиков в head
     */
    public function output_counters() {
        $settings = $this->settings->get_settings();
        if (!$this->should_output_counters($settings)) {
            return;
        }

        if (!empty($settings['yandex_metrika_enabled']) && !empty($settings['yandex_metrika_code'])) {
            echo "\n<!-- DevBrothers Counter Manager - Yandex.Metrika -->\n";
            $this->print_counter_markup($settings['yandex_metrika_code'], 'yandex');
            echo "\n<!-- /DevBrothers Counter Manager - Yandex.Metrika -->\n";
        }

        if (!empty($settings['google_analytics_enabled']) && !empty($settings['google_analytics_code'])) {
            echo "\n<!-- DevBrothers Counter Manager - Google Analytics -->\n";
            $this->print_counter_markup($settings['google_analytics_code'], 'google');
            echo "\n<!-- /DevBrothers Counter Manager - Google Analytics -->\n";
        }
    }

    /**
     * Вывод проверенного кода счётчика: script через WP API, noscript через wp_kses.
     *
     * @param string $code
     * @param string $provider yandex|google
     */
    private function print_counter_markup($code, $provider) {
        if (!$this->settings->is_allowed_counter_code($code, $provider)) {
            return;
        }

        $code = preg_replace('/<!--.*?-->/s', '', (string) $code);
        $offset = 0;
        $length = strlen($code);

        while ($offset < $length) {
            if (preg_match('/<\s*script\b([^>]*)>(.*?)<\/script>/is', $code, $match, PREG_OFFSET_CAPTURE, $offset)) {
                $attributes = $this->parse_script_attributes($match[1][0]);
                $content    = $match[2][0];

                if (!empty($attributes['src']) && !$this->is_allowed_script_src($attributes['src'], $provider)) {
                    $offset = $match[0][1] + strlen($match[0][0]);
                    continue;
                }

                $this->print_script_tag($attributes, $content);
                $offset = $match[0][1] + strlen($match[0][0]);
                continue;
            }

            if (preg_match('/<\s*noscript\b[^>]*>(.*?)<\/noscript>/is', $code, $match, PREG_OFFSET_CAPTURE, $offset)) {
                echo '<noscript>' . wp_kses($match[1][0], $this->get_allowed_noscript_html()) . '</noscript>';
                $offset = $match[0][1] + strlen($match[0][0]);
                continue;
            }

            break;
        }
    }

    /**
     * @param array<string, bool|string> $attributes
     * @param string                     $content
     */
    private function print_script_tag($attributes, $content = '') {
        $safe_attributes = [];

        foreach ($attributes as $name => $value) {
            $name = strtolower((string) $name);

            if ($name === 'async' || $name === 'defer') {
                if ($value) {
                    $safe_attributes[$name] = true;
                }
                continue;
            }

            if (!in_array($name, ['type', 'src', 'charset', 'id', 'crossorigin'], true)) {
                continue;
            }

            if ($name === 'src') {
                $safe_attributes[$name] = esc_url((string) $value);
                continue;
            }

            $safe_attributes[$name] = (string) $value;
        }

        wp_print_inline_script_tag($content, $safe_attributes);
    }

    /**
     * @param string $attrs_raw
     * @return array<string, bool|string>
     */
    private function parse_script_attributes($attrs_raw) {
        $attributes = [];

        if (!preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*(?:=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/', $attrs_raw, $matches, PREG_SET_ORDER)) {
            return $attributes;
        }

        foreach ($matches as $match) {
            $name = strtolower($match[1]);

            if ($name === 'async' || $name === 'defer') {
                $attributes[$name] = true;
                continue;
            }

            $value = $match[2];
            if ($value === '' && $match[3] !== '') {
                $value = $match[3];
            }
            if ($value === '' && isset($match[4]) && $match[4] !== '') {
                $value = $match[4];
            }

            $attributes[$name] = $value;
        }

        return $attributes;
    }

    /**
     * @param string $src
     * @param string $provider
     * @return bool
     */
    private function is_allowed_script_src($src, $provider) {
        $host = wp_parse_url($src, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        if ($provider === 'yandex') {
            return $host === 'mc.yandex.ru'
                || $host === 'yandex.ru'
                || substr($host, -11) === '.yandex.ru';
        }

        if ($provider === 'google') {
            return $host === 'www.googletagmanager.com'
                || $host === 'googletagmanager.com'
                || $host === 'www.google-analytics.com'
                || $host === 'google-analytics.com';
        }

        return false;
    }

    /**
     * Разрешённая разметка внутри <noscript>.
     *
     * @return array<string, array<string, bool>>
     */
    private function get_allowed_noscript_html() {
        return [
            'div' => [
                'class' => true,
                'style' => true,
            ],
            'img' => [
                'src'    => true,
                'style'  => true,
                'alt'    => true,
                'width'  => true,
                'height' => true,
            ],
            'a'   => [
                'href' => true,
            ],
        ];
    }

    public function enqueue_frontend_assets() {
        if (is_admin()) {
            return;
        }

        $settings = $this->settings->get_settings();
        if (empty($settings['cookie_banner_enabled']) || $this->is_cookie_banner_excluded_for_current_url($settings)) {
            return;
        }

        wp_enqueue_style(
            'dbcm-frontend',
            DBCM_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            DBCM_VERSION
        );

        wp_enqueue_script(
            'dbcm-frontend',
            DBCM_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            DBCM_VERSION,
            true
        );

        wp_localize_script('dbcm-frontend', 'dbcmFrontend', [
            'consentCookieName' => $this->consent_cookie_name,
            'policyUrl' => !empty($settings['cookie_policy_url']) ? esc_url($settings['cookie_policy_url']) : '',
            'theme' => in_array($settings['cookie_banner_theme'], ['light', 'dark'], true)
                ? $settings['cookie_banner_theme']
                : 'light',
            'strings' => [
                'title' => __('Наш сайт использует Google Analytics и Яндекс.Метрику для сбора статистики и улучшения работы сайта. Эти сервисы используют файлы cookie и требуют вашего согласия. Вы можете принять или отклонить их использование.', 'devbrothers-counter-manager'),
                'accept' => __('Принять', 'devbrothers-counter-manager'),
                'decline' => __('Отклонить', 'devbrothers-counter-manager'),
                'settings' => __('Настройки cookie', 'devbrothers-counter-manager'),
                'policy' => __('Политика конфиденциальности', 'devbrothers-counter-manager'),
            ],
        ]);
    }

    public function output_cookie_banner() {
        if (is_admin()) {
            return;
        }

        $settings = $this->settings->get_settings();
        if (empty($settings['cookie_banner_enabled']) || $this->is_cookie_banner_excluded_for_current_url($settings)) {
            return;
        }
        ?>
        <div class="dbcm-cookie-banner" id="dbcm-cookie-banner" hidden>
            <div class="dbcm-cookie-banner__content">
                <p class="dbcm-cookie-banner__text" id="dbcm-cookie-banner-text"></p>
                <div class="dbcm-cookie-banner__actions">
                    <a href="#" class="dbcm-cookie-banner__policy" id="dbcm-cookie-banner-policy" hidden></a>
                    <button type="button" class="dbcm-cookie-btn dbcm-cookie-btn--secondary" data-dbcm-consent="declined"></button>
                    <button type="button" class="dbcm-cookie-btn dbcm-cookie-btn--primary" data-dbcm-consent="accepted"></button>
                </div>
            </div>
        </div>
        <button type="button" class="dbcm-cookie-fab" id="dbcm-cookie-fab" hidden></button>
        <?php
    }

    private function should_output_counters($settings) {
        if (empty($settings['cookie_banner_enabled']) || $this->is_cookie_banner_excluded_for_current_url($settings)) {
            return true;
        }

        return $this->get_user_consent_status() === 'accepted';
    }

    /**
     * Проверка, исключён ли текущий URL из cookie-баннера
     *
     * @param array $settings
     * @return bool
     */
    private function is_cookie_banner_excluded_for_current_url($settings) {
        if (empty($settings['cookie_banner_exclude_paths'])) {
            return false;
        }

        $exclude_paths = $this->parse_exclude_paths($settings['cookie_banner_exclude_paths']);
        if (empty($exclude_paths)) {
            return false;
        }

        $current_path = $this->get_current_request_path();
        foreach ($exclude_paths as $exclude_path) {
            if ($this->path_matches_exclude($current_path, $exclude_path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $raw
     * @return string[]
     */
    private function parse_exclude_paths($raw) {
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if (!is_array($lines)) {
            return [];
        }

        $paths = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (preg_match('#^https?://#i', $line)) {
                $path = wp_parse_url($line, PHP_URL_PATH);
                $line = is_string($path) ? $path : '';
            }

            $line = '/' . ltrim($line, '/');
            $line = untrailingslashit($line);
            if ($line !== '') {
                $paths[] = $line;
            }
        }

        return $paths;
    }

    /**
     * @return string
     */
    private function get_current_request_path() {
        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '/';
        $path = wp_parse_url($request_uri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        return untrailingslashit($path);
    }

    /**
     * @param string $current_path
     * @param string $exclude_path
     * @return bool
     */
    private function path_matches_exclude($current_path, $exclude_path) {
        if ($current_path === $exclude_path) {
            return true;
        }

        return strpos($current_path, $exclude_path . '/') === 0;
    }

    private function get_user_consent_status() {
        if (empty($_COOKIE[$this->consent_cookie_name])) {
            return '';
        }

        $value = sanitize_text_field(wp_unslash($_COOKIE[$this->consent_cookie_name]));
        if ($value !== 'accepted' && $value !== 'declined') {
            return '';
        }

        return $value;
    }
}


