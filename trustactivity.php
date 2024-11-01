<?php

/*
Plugin Name: TrustActivity - Recent Sales and SignUp Popups
Plugin URI: https://www.trustactivity.com
Description:TrustActivity plugin will show verified information about recent and sales activity on your website. It is the greatest tool to convert your visitors into customers. Must have module.
Version: 1.0.0
Author: TrustActivity
Author URI: https://www.trustactivity.com
*/
 
add_action('admin_notices', 'trustactivity_admin_notice_html');
add_action('admin_menu', 'trustactivity_add_plugin_page');
add_action('wp_head', 'trustactivity_add_script');

function trustactivity_add_script()
{
    $key = trustactivity_get_key();
    if ($key !== false) {
        $arrContextOptions = array(
            'sslverify' => false,
        );

        $response = wp_remote_get('https://www.trustactivity.com/api/get-script?acc=' . $key, $arrContextOptions);
        $body = wp_remote_retrieve_body($response);
        $html = json_decode($body);

        if (!empty($html->html)) {
            echo $html->html;
        }
    }
}

function trustactivity_add_plugin_page()
{
    add_options_page('TrustActivity', 'TrustActivity', 'manage_options', 'trust_activity', 'trustactivity_options_page_output');
}

function trustactivity_options_page_output()
{
    ?>
    <div class="wrap">
        <h2><?php echo get_admin_page_title() ?></h2>
        <h3>Don't have an account? Sign up <a target="_blank"
                                              href="https://www.trustactivity.com/auth/register">here</a>!</h3>
        <form action="options.php" method="POST">
            <?php
            settings_fields('option_group');     // скрытые защитные поля
            do_settings_sections('trustactivity_setting_page'); // секции с настройками (опциями). У нас она всего одна 'section_id'
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'plugin_settings');
function plugin_settings()
{
    // параметры: $option_group, $option_name, $sanitize_callback
    register_setting('option_group', 'trustactivity_app_key_set', 'sanitize_callback');

    // параметры: $id, $title, $callback, $page
    add_settings_section('trustactivity_settings', 'Settings', '', 'trustactivity_setting_page');

    // параметры: $id, $title, $callback, $page, $section, $args
    add_settings_field('trustactivity_app_key_set', 'Api Key', 'trustactivity_app_key', 'trustactivity_setting_page', 'trustactivity_settings');
}

function trustactivity_app_key()
{
    $val = get_option('trustactivity_app_key_set');
//    $val = $val ? $val['input'] : null;
    ?>
    <input type="text" name="trustactivity_app_key_set" value="<?php echo esc_attr($val) ?>"/>
    <p class="description">Where is my <a target="_blank"
                                          href="https://www.trustactivity.com/admin/faq/integrations/integrate-with-wordpress">API
            Key?</a></p>
    <?php
}

function trustactivity_get_key()
{
    $apiKey = get_option('trustactivity_app_key_set');
    if (empty($apiKey)) {
        return false;
    }
    return $apiKey;
}

function trustactivity_admin_notice_html()
{
    $apiKey = trustactivity_get_key();
    if (!empty($apiKey)) return;


    ?>

    <div class="notice notice-error is-dismissible">
        <p class="ps-error">TrustActivity is not configured! <a
                    href="<?php admin_url('options-general.php?page=trust_activity'); ?>">Click here</a></p>
    </div>

    <?php
}
