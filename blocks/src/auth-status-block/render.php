<?php

use wpshop\Pages;

// get user if logged in
$user = wp_get_current_user();

if ($user) {
    $user_email = $user->user_email;
    $user_avatar = get_avatar_url($user->ID, ['size' => 24]);
}

$my_account_page = Pages::g()->get_account_link();

?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> <?php if ( isset( $user_email ) ) : ?> data-user-email="<?php echo esc_attr( $user_email ); ?>" <?php endif; ?> <?php if ( isset( $user_avatar ) ) : ?> data-user-avatar="<?php echo esc_url( $user_avatar ); ?>" <?php endif; ?> data-my-account-link="<?php echo esc_url( $my_account_page ); ?>">
</div>