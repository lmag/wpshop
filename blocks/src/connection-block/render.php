<?php

use wpshop\Pages;
use wpshop\Settings;

$my_account_page = Pages::g()->get_account_link();

if (is_user_logged_in() && !is_admin()) {
    wp_redirect($my_account_page);
    exit;
}

$dolibarr_option = get_option('wps_dolibarr', Settings::g()->default_settings);
$use_re_captcha = !empty($dolibarr_option['re_captcha']);

?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> data-account-link="<?php echo esc_url( $my_account_page ); ?>" data-use-re-captcha="<?php echo esc_attr( $use_re_captcha ); ?>">
</div>