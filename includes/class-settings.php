<?php
/**
 * Класс настроек плагина
 *
 * @package DevBrothers_Counter_Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class DBCM_Settings {

    /**
     * @var string
     */
    private $option_name = 'dbcm_settings';

    /**
     * Получение текущих настроек
     *
     * @return array
     */
    public function get_settings() {
        $defaults = [
            'yandex_metrika_enabled'  => false,
            'yandex_metrika_code'     => '',
            'google_analytics_enabled' => false,
            'google_analytics_code'   => '',
            'cookie_banner_enabled'   => false,
            'cookie_consent_mode'     => 'opt_in',
            'cookie_policy_url'       => '',
            'cookie_banner_theme'     => 'light',
            'cookie_banner_position'  => 'bottom_left',
            'cookie_hide_fab_after_choice' => false,
            'cookie_banner_exclude_paths' => '',
        ];

        return wp_parse_args(get_option($this->option_name, $defaults), $defaults);
    }

    /**
     * Отрисовка страницы настроек
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Недостаточно прав', 'devbrothers-counter-manager'));
        }

        $can_edit_counter_code = current_user_can('unfiltered_html');
        $settings_saved = false;

        if (isset($_POST['dbcm_save_settings'])) {
            if (
                !isset($_POST['_wpnonce'])
                || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'dbcm_settings_nonce')
            ) {
                wp_die(esc_html__('Ошибка безопасности', 'devbrothers-counter-manager'));
            }

            $this->save_settings_internal();
            $settings_saved = true;
        }

        $settings = $this->get_settings();

        if ($settings_saved) {
            echo '<div class="notice notice-success"><p>' .
                 esc_html__('Настройки сохранены!', 'devbrothers-counter-manager') .
                 '</p></div>';
            $this->maybe_show_counter_validation_notices();
        }

        $ym_active = !empty($settings['yandex_metrika_enabled']);
        $ga_active = !empty($settings['google_analytics_enabled']);
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('dbcm_settings_nonce'); ?>

            <!-- Категория: Счетчики -->
            <div id="counters" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-chart-line"></span>
                    <?php esc_html_e('Счетчики аналитики', 'devbrothers-counter-manager'); ?>
                </h2>
                <?php if (!$can_edit_counter_code) : ?>
                    <p class="description"><?php esc_html_e('Для изменения кода счётчиков требуется право unfiltered_html. Остальные настройки доступны.', 'devbrothers-counter-manager'); ?></p>
                <?php endif; ?>

                <!-- Яндекс.Метрика -->
                <div class="dbcm-counter-card<?php echo esc_attr($ym_active ? ' dbcm-active' : ''); ?>">
                    <div class="dbcm-counter-header">
                        <div class="dbcm-counter-title">
                            <span class="dbcm-counter-icon dbcm-icon-yandex">Я</span>
                            <div>
                                <h3><?php esc_html_e('Яндекс.Метрика', 'devbrothers-counter-manager'); ?></h3>
                                <span class="dbcm-counter-status<?php echo esc_attr($ym_active ? ' dbcm-status-active' : ''); ?>">
                                    <?php echo $ym_active
                                        ? esc_html__('Включён', 'devbrothers-counter-manager')
                                        : esc_html__('Выключен', 'devbrothers-counter-manager'); ?>
                                </span>
                            </div>
                        </div>
                        <label class="dbcm-toggle">
                            <input type="checkbox"
                                   name="dbcm_settings[yandex_metrika_enabled]"
                                   id="dbcm_yandex_metrika_enabled"
                                   value="1"
                                   <?php checked($ym_active); ?> />
                            <span class="dbcm-toggle-track">
                                <span class="dbcm-toggle-thumb"></span>
                            </span>
                        </label>
                    </div>
                    <div class="dbcm-counter-body">
                        <div class="dbcm-code-wrapper">
                            <label for="dbcm_yandex_metrika_code">
                                <?php esc_html_e('Код счётчика', 'devbrothers-counter-manager'); ?>
                            </label>
                            <textarea name="dbcm_settings[yandex_metrika_code]"
                                      id="dbcm_yandex_metrika_code"
                                      class="dbcm-code-editor"
                                      rows="12"
                                      <?php disabled(!$can_edit_counter_code); ?>
                                      placeholder="<?php esc_attr_e('Вставьте полный код счётчика Яндекс.Метрики...', 'devbrothers-counter-manager'); ?>"><?php echo esc_textarea($settings['yandex_metrika_code']); ?></textarea>
                        </div>
                        <p class="dbcm-hint">
                            <?php esc_html_e('Вставьте полный код из настроек Яндекс.Метрики. Код будет добавлен в <head> всех страниц.', 'devbrothers-counter-manager'); ?>
                        </p>
                    </div>
                </div>

                <!-- Google Analytics -->
                <div class="dbcm-counter-card<?php echo esc_attr($ga_active ? ' dbcm-active' : ''); ?>">
                    <div class="dbcm-counter-header">
                        <div class="dbcm-counter-title">
                            <span class="dbcm-counter-icon dbcm-icon-google">G</span>
                            <div>
                                <h3><?php esc_html_e('Google Analytics', 'devbrothers-counter-manager'); ?></h3>
                                <span class="dbcm-counter-status<?php echo esc_attr($ga_active ? ' dbcm-status-active' : ''); ?>">
                                    <?php echo $ga_active
                                        ? esc_html__('Включён', 'devbrothers-counter-manager')
                                        : esc_html__('Выключен', 'devbrothers-counter-manager'); ?>
                                </span>
                            </div>
                        </div>
                        <label class="dbcm-toggle">
                            <input type="checkbox"
                                   name="dbcm_settings[google_analytics_enabled]"
                                   id="dbcm_google_analytics_enabled"
                                   value="1"
                                   <?php checked($ga_active); ?> />
                            <span class="dbcm-toggle-track">
                                <span class="dbcm-toggle-thumb"></span>
                            </span>
                        </label>
                    </div>
                    <div class="dbcm-counter-body">
                        <div class="dbcm-code-wrapper">
                            <label for="dbcm_google_analytics_code">
                                <?php esc_html_e('Код счётчика', 'devbrothers-counter-manager'); ?>
                            </label>
                            <textarea name="dbcm_settings[google_analytics_code]"
                                      id="dbcm_google_analytics_code"
                                      class="dbcm-code-editor"
                                      rows="12"
                                      <?php disabled(!$can_edit_counter_code); ?>
                                      placeholder="<?php esc_attr_e('Вставьте полный код Google Analytics (gtag.js)...', 'devbrothers-counter-manager'); ?>"><?php echo esc_textarea($settings['google_analytics_code']); ?></textarea>
                        </div>
                        <p class="dbcm-hint">
                            <?php esc_html_e('Вставьте полный код gtag.js из настроек Google Analytics. Код будет добавлен в <head> всех страниц.', 'devbrothers-counter-manager'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Категория: Consent / Cookie-баннер -->
            <div id="consent" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-shield"></span>
                    <?php esc_html_e('Cookie-баннер (согласие)', 'devbrothers-counter-manager'); ?>
                </h2>

                <div class="dbcm-counter-card<?php echo esc_attr(!empty($settings['cookie_banner_enabled']) ? ' dbcm-active' : ''); ?>">
                    <div class="dbcm-counter-header">
                        <div class="dbcm-counter-title">
                            <span class="dbcm-counter-icon dbcm-icon-consent">C</span>
                            <div>
                                <h3><?php esc_html_e('Запрашивать согласие на аналитику', 'devbrothers-counter-manager'); ?></h3>
                                <span class="dbcm-counter-status<?php echo esc_attr(!empty($settings['cookie_banner_enabled']) ? ' dbcm-status-active' : ''); ?>">
                                    <?php echo !empty($settings['cookie_banner_enabled'])
                                        ? esc_html__('Включён', 'devbrothers-counter-manager')
                                        : esc_html__('Выключен', 'devbrothers-counter-manager'); ?>
                                </span>
                            </div>
                        </div>
                        <label class="dbcm-toggle">
                            <input type="checkbox"
                                   name="dbcm_settings[cookie_banner_enabled]"
                                   id="dbcm_cookie_banner_enabled"
                                   value="1"
                                   <?php checked(!empty($settings['cookie_banner_enabled'])); ?> />
                            <span class="dbcm-toggle-track">
                                <span class="dbcm-toggle-thumb"></span>
                            </span>
                        </label>
                    </div>
                    <div class="dbcm-counter-body">
                        <p class="dbcm-hint">
                            <?php esc_html_e('Если включено: показывается баннер и кнопка «Настройки cookie». Поведение счётчиков зависит от выбранного режима согласия ниже. Если выключено: счётчики загружаются сразу.', 'devbrothers-counter-manager'); ?>
                        </p>

                        <div class="dbcm-form-field dbcm-form-field--consent-mode">
                            <span class="dbcm-field-label"><?php esc_html_e('Режим согласия', 'devbrothers-counter-manager'); ?></span>
                            <fieldset class="dbcm-consent-mode-fieldset">
                                <label class="dbcm-consent-mode-option">
                                    <input type="radio"
                                           name="dbcm_settings[cookie_consent_mode]"
                                           value="opt_in"
                                           <?php checked($settings['cookie_consent_mode'], 'opt_in'); ?> />
                                    <span class="dbcm-consent-mode-title"><?php esc_html_e('Согласие до загрузки (opt-in)', 'devbrothers-counter-manager'); ?></span>
                                    <span class="dbcm-hint dbcm-consent-mode-desc"><?php esc_html_e('Счётчики не вставляются, пока посетитель не нажмёт «Принять». «Отклонить» ничего не меняет — счётчики по-прежнему не загружаются.', 'devbrothers-counter-manager'); ?></span>
                                </label>
                                <label class="dbcm-consent-mode-option">
                                    <input type="radio"
                                           name="dbcm_settings[cookie_consent_mode]"
                                           value="opt_out"
                                           <?php checked($settings['cookie_consent_mode'], 'opt_out'); ?> />
                                    <span class="dbcm-consent-mode-title"><?php esc_html_e('Отказ отключает (opt-out)', 'devbrothers-counter-manager'); ?></span>
                                    <span class="dbcm-hint dbcm-consent-mode-desc"><?php esc_html_e('Счётчики загружаются сразу. «Принять» ничего не меняет. После «Отклонить» и перезагрузки страницы счётчики отключаются.', 'devbrothers-counter-manager'); ?></span>
                                </label>
                            </fieldset>
                            <div class="dbcm-legal-notice">
                                <p><strong><?php esc_html_e('Требования Роскомнадзора', 'devbrothers-counter-manager'); ?></strong></p>
                                <p><?php esc_html_e('Роскомнадзор считает cookie персональными данными. Требуется явное, недвусмысленное согласие — клик по кнопке. Прямо запрещено:', 'devbrothers-counter-manager'); ?></p>
                                <ul class="dbcm-legal-list">
                                    <li><?php esc_html_e('предустановленные галочки', 'devbrothers-counter-manager'); ?></li>
                                    <li><?php esc_html_e('подразумеваемое согласие при продолжении просмотра', 'devbrothers-counter-manager'); ?></li>
                                    <li><?php esc_html_e('автоматическое согласие после задержек', 'devbrothers-counter-manager'); ?></li>
                                </ul>
                                <p class="dbcm-hint"><?php esc_html_e('Для соответствия этим требованиям рекомендуется режим opt-in.', 'devbrothers-counter-manager'); ?></p>
                            </div>
                        </div>

                        <div class="dbcm-form-field">
                            <span class="dbcm-field-label" id="dbcm_cookie_policy_url_label">
                                <?php esc_html_e('Ссылка на политику конфиденциальности / cookie', 'devbrothers-counter-manager'); ?>
                            </span>
                            <input type="url"
                                   name="dbcm_settings[cookie_policy_url]"
                                   id="dbcm_cookie_policy_url"
                                   class="regular-text dbcm-input"
                                   placeholder="https://example.com/privacy-policy/"
                                   value="<?php echo esc_attr($settings['cookie_policy_url']); ?>"
                                   aria-labelledby="dbcm_cookie_policy_url_label" />
                        </div>

                        <div class="dbcm-form-field">
                            <span class="dbcm-field-label" id="dbcm_cookie_banner_theme_label">
                                <?php esc_html_e('Тема баннера', 'devbrothers-counter-manager'); ?>
                            </span>
                            <select name="dbcm_settings[cookie_banner_theme]"
                                    id="dbcm_cookie_banner_theme"
                                    class="dbcm-select"
                                    aria-labelledby="dbcm_cookie_banner_theme_label">
                                <option value="light" <?php selected($settings['cookie_banner_theme'], 'light'); ?>>
                                    <?php esc_html_e('Светлая', 'devbrothers-counter-manager'); ?>
                                </option>
                                <option value="dark" <?php selected($settings['cookie_banner_theme'], 'dark'); ?>>
                                    <?php esc_html_e('Тёмная', 'devbrothers-counter-manager'); ?>
                                </option>
                            </select>
                        </div>

                        <div class="dbcm-form-field">
                            <span class="dbcm-field-label" id="dbcm_cookie_banner_position_label">
                                <?php esc_html_e('Положение баннера', 'devbrothers-counter-manager'); ?>
                            </span>
                            <select name="dbcm_settings[cookie_banner_position]"
                                    id="dbcm_cookie_banner_position"
                                    class="dbcm-select"
                                    aria-labelledby="dbcm_cookie_banner_position_label">
                                <option value="bottom_left" <?php selected($settings['cookie_banner_position'], 'bottom_left'); ?>>
                                    <?php esc_html_e('Нижний левый угол', 'devbrothers-counter-manager'); ?>
                                </option>
                                <option value="bottom_center" <?php selected($settings['cookie_banner_position'], 'bottom_center'); ?>>
                                    <?php esc_html_e('По центру внизу', 'devbrothers-counter-manager'); ?>
                                </option>
                                <option value="bottom_right" <?php selected($settings['cookie_banner_position'], 'bottom_right'); ?>>
                                    <?php esc_html_e('Нижний правый угол', 'devbrothers-counter-manager'); ?>
                                </option>
                            </select>
                        </div>

                        <div class="dbcm-form-field">
                            <label class="dbcm-checkbox-label" for="dbcm_cookie_hide_fab_after_choice">
                                <input type="checkbox"
                                       name="dbcm_settings[cookie_hide_fab_after_choice]"
                                       id="dbcm_cookie_hide_fab_after_choice"
                                       value="1"
                                       <?php checked(!empty($settings['cookie_hide_fab_after_choice'])); ?> />
                                <span><?php esc_html_e('Скрывать кнопку «Настройки cookie» после выбора', 'devbrothers-counter-manager'); ?></span>
                            </label>
                            <p class="dbcm-hint">
                                <?php esc_html_e('После «Принять» или «Отклонить» маленькая кнопка не показывается. Удобно, если она перекрывает нижние панели на мобильных. Изменить решение посетитель сможет только очистив cookies браузера.', 'devbrothers-counter-manager'); ?>
                            </p>
                        </div>

                        <div class="dbcm-form-field">
                            <span class="dbcm-field-label" id="dbcm_cookie_banner_exclude_paths_label">
                                <?php esc_html_e('Не показывать баннер на страницах', 'devbrothers-counter-manager'); ?>
                            </span>
                            <textarea name="dbcm_settings[cookie_banner_exclude_paths]"
                                      id="dbcm_cookie_banner_exclude_paths"
                                      class="dbcm-exclude-paths"
                                      rows="5"
                                      placeholder="/dashboard/&#10;/dashboard/billing/"
                                      aria-labelledby="dbcm_cookie_banner_exclude_paths_label"><?php echo esc_textarea($settings['cookie_banner_exclude_paths']); ?></textarea>
                            <p class="dbcm-hint">
                                <?php esc_html_e('Один путь или URL на строку. Можно указать полный адрес (https://example.com/dashboard/) или только путь (/dashboard/). Страница и все вложенные URL с этим префиксом будут исключены — достаточно одной строки /dashboard/ для /dashboard/billing/ и т.д. На исключённых страницах баннер не показывается, счётчики загружаются сразу.', 'devbrothers-counter-manager'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Категория: Диагностика -->
            <div id="diagnostics" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-performance"></span>
                    <?php esc_html_e('Проверка счётчика (диагностика)', 'devbrothers-counter-manager'); ?>
                </h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Ошибка 499 при загрузке tag.js', 'devbrothers-counter-manager'); ?></th>
                        <td>
                            <p class="description">
                                <?php esc_html_e('Если в консоли браузера (F12) видно: GET https://mc.yandex.ru/metrika/tag.js net::ERR_ABORTED 499 — запрос к Яндекс.Метрике часто блокируется расширениями (блокировщики рекламы, приватность). Счётчик при этом на сайте не работает.', 'devbrothers-counter-manager'); ?>
                            </p>
                            <p><strong><?php esc_html_e('Что сделать:', 'devbrothers-counter-manager'); ?></strong></p>
                            <ol class="dbcm-steps-list">
                                <li><?php esc_html_e('Проверьте сайт в режиме инкогнито без расширений — счётчик должен загружаться.', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Добавьте свой сайт в исключения блокировщика или отключите его на своей странице.', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Для обычных посетителей без блокировщиков счётчик работает; 499 видят только у себя при включённой блокировке.', 'devbrothers-counter-manager'); ?></li>
                            </ol>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Категория: Инструкция -->
            <div id="usage" class="devbrothers-settings-category">
                <h2>
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e('Инструкция', 'devbrothers-counter-manager'); ?>
                </h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Как получить код счетчика?', 'devbrothers-counter-manager'); ?>
                        </th>
                        <td>
                            <p><strong><?php esc_html_e('Яндекс.Метрика:', 'devbrothers-counter-manager'); ?></strong></p>
                            <ol>
                                <li><?php esc_html_e('Войдите в ваш аккаунт Яндекс.Метрики', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Выберите нужный счетчик', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Перейдите в раздел "Настройки" → "Код счетчика"', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Скопируйте полный код счетчика и вставьте в поле выше', 'devbrothers-counter-manager'); ?></li>
                            </ol>

                            <p class="dbcm-instructions-block"><strong><?php esc_html_e('Google Analytics:', 'devbrothers-counter-manager'); ?></strong></p>
                            <ol>
                                <li><?php esc_html_e('Войдите в Google Analytics', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Перейдите в "Администратор" → "Потоки данных"', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Выберите ваш поток данных и нажмите "Добавить поток"', 'devbrothers-counter-manager'); ?></li>
                                <li><?php esc_html_e('Скопируйте код gtag.js и вставьте в поле выше', 'devbrothers-counter-manager'); ?></li>
                            </ol>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Кнопка сохранения -->
            <p class="submit">
                <button type="submit" name="dbcm_save_settings" class="button button-primary">
                    <span class="dashicons dashicons-yes"></span>
                    <?php esc_html_e('Сохранить настройки', 'devbrothers-counter-manager'); ?>
                </button>
            </p>
        </form>
        <?php
    }

    /**
     * Сохранение настроек
     *
     * Nonce и права доступа проверяются в render_settings_page() перед вызовом.
     */
    private function save_settings_internal() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce проверяется в render_settings_page
        if (!isset($_POST['dbcm_settings']) || !is_array($_POST['dbcm_settings'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized via sanitize_settings()
        $input     = wp_unslash($_POST['dbcm_settings']);
        $sanitized = $this->sanitize_settings($input);

        update_option($this->option_name, $sanitized);
    }

    /**
     * Санитизация всех полей настроек
     *
     * @param array $input Сырые данные из формы
     * @return array
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        $current = $this->get_settings();

        $sanitized['yandex_metrika_enabled'] = !empty($input['yandex_metrika_enabled']);
        $sanitized['google_analytics_enabled'] = !empty($input['google_analytics_enabled']);

        foreach (['yandex_metrika_code' => 'yandex', 'google_analytics_code' => 'google'] as $field => $provider) {
            $sanitized[$field] = current_user_can('unfiltered_html') && is_string($input[$field] ?? null)
                ? $this->sanitize_counter_code($input[$field], $provider)
                : $current[$field];
        }

        $sanitized['cookie_banner_enabled'] = !empty($input['cookie_banner_enabled']);

        $consent_mode = isset($input['cookie_consent_mode']) ? sanitize_key($input['cookie_consent_mode']) : 'opt_in';
        $sanitized['cookie_consent_mode'] = in_array($consent_mode, ['opt_in', 'opt_out'], true) ? $consent_mode : 'opt_in';

        if (isset($input['cookie_policy_url'])) {
            $sanitized['cookie_policy_url'] = esc_url_raw(trim((string) $input['cookie_policy_url']));
        } else {
            $sanitized['cookie_policy_url'] = '';
        }

        $theme = isset($input['cookie_banner_theme']) ? sanitize_key($input['cookie_banner_theme']) : 'light';
        $sanitized['cookie_banner_theme'] = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';

        $position = isset($input['cookie_banner_position']) ? sanitize_key($input['cookie_banner_position']) : 'bottom_left';
        $sanitized['cookie_banner_position'] = in_array($position, ['bottom_left', 'bottom_center', 'bottom_right'], true)
            ? $position
            : 'bottom_left';

        $sanitized['cookie_hide_fab_after_choice'] = !empty($input['cookie_hide_fab_after_choice']);

        if (isset($input['cookie_banner_exclude_paths'])) {
            $sanitized['cookie_banner_exclude_paths'] = $this->sanitize_exclude_paths((string) $input['cookie_banner_exclude_paths']);
        } else {
            $sanitized['cookie_banner_exclude_paths'] = '';
        }

        return $sanitized;
    }

    /**
     * Санитизация списка путей/URL для исключения cookie-баннера
     *
     * @param string $raw
     * @return string
     */
    private function sanitize_exclude_paths($raw) {
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if (!is_array($lines)) {
            return '';
        }

        $normalized = [];
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
            if ($line === '') {
                continue;
            }

            $normalized[] = $line;
        }

        return implode("\n", array_unique($normalized));
    }

    /**
     * Проверка кода счётчика перед сохранением и выводом.
     *
     * @param string $code
     * @param string $provider yandex|google
     * @return bool
     */
    public function is_allowed_counter_code($code, $provider) {
        if (empty($code) || !is_string($code)) {
            return false;
        }

        $code = trim($code);

        $blocked_tags = ['iframe', 'object', 'embed', 'form', 'base', 'link', 'style', 'meta'];
        foreach ($blocked_tags as $tag) {
            if (preg_match('/<\s*' . $tag . '[\s>]/i', $code)) {
                return false;
            }
        }

        if (!preg_match('/<\s*script[\s>]/i', $code)) {
            return false;
        }

        if (preg_match('/\bon\w+\s*=/i', $code) || stripos($code, 'javascript:') !== false) {
            return false;
        }

        if ($provider === 'yandex' && !$this->looks_like_yandex_counter_code($code)) {
            return false;
        }

        if ($provider === 'google' && !$this->looks_like_google_counter_code($code)) {
            return false;
        }

        return true;
    }

    /**
     * Санитизация кода счетчика
     *
     * Блокирует HTML-теги, не ожидаемые в коде аналитики.
     * Код <script> и <noscript> допускается, т.к. это штатные теги счётчиков.
     * Для записи кода на странице настроек нужны manage_options и unfiltered_html.
     *
     * @param string $code
     * @param string $provider yandex|google
     * @return string
     */
    private function sanitize_counter_code($code, $provider) {
        if (!$this->is_allowed_counter_code($code, $provider)) {
            return '';
        }

        return trim((string) $code);
    }

    /**
     * @param string $code
     * @return bool
     */
    private function looks_like_yandex_counter_code($code) {
        return (
            strpos($code, 'mc.yandex') !== false
            || strpos($code, 'yandex.ru/metrika') !== false
            || preg_match('/\bym\s*\(/i', $code)
        );
    }

    /**
     * @param string $code
     * @return bool
     */
    private function looks_like_google_counter_code($code) {
        return (
            strpos($code, 'googletagmanager.com') !== false
            || strpos($code, 'gtag(') !== false
            || strpos($code, 'google-analytics.com') !== false
        );
    }

    /**
     * Предупреждения о валидности кода счётчика после сохранения
     */
    private function maybe_show_counter_validation_notices() {
        $settings = $this->get_settings();

        if (!empty($settings['yandex_metrika_enabled']) && !empty($settings['yandex_metrika_code'])) {
            $code         = $settings['yandex_metrika_code'];
            if (!$this->looks_like_yandex_counter_code($code)) {
                echo '<div class="notice notice-warning"><p><strong>' .
                     esc_html__('Яндекс.Метрика:', 'devbrothers-counter-manager') . '</strong> ' .
                     esc_html__('Вставленный код не похож на стандартный код счётчика (нет mc.yandex или ym). Убедитесь, что скопировали полный код из настроек счётчика в кабинете Метрики.', 'devbrothers-counter-manager') .
                     '</p></div>';
            }
        }

        if (!empty($settings['google_analytics_enabled']) && !empty($settings['google_analytics_code'])) {
            $code         = $settings['google_analytics_code'];
            if (!$this->looks_like_google_counter_code($code)) {
                echo '<div class="notice notice-warning"><p><strong>' .
                     esc_html__('Google Analytics:', 'devbrothers-counter-manager') . '</strong> ' .
                     esc_html__('Вставленный код не похож на стандартный gtag/GA (нет googletagmanager или gtag). Убедитесь, что скопировали полный код из потока данных.', 'devbrothers-counter-manager') .
                     '</p></div>';
            }
        }
    }
}
