<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> data-countries="<?php echo esc_attr( json_encode( \wpshop\get_countries() ) ); ?>">
</div>