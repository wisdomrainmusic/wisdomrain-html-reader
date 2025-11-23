<?php
/**
 * Default template for the WisdomRain HTML Reader output.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$source = ! empty( $attributes['source'] ) ? esc_url( $attributes['source'] ) : '';
?>
<div class="wrhr-reader" <?php echo $source ? 'data-source="' . esc_attr( $source ) . '"' : ''; ?>>
    <div class="wrhr-reader__content">
        <?php if ( $source ) : ?>
            <p><?php echo sprintf( esc_html__( 'Content from %s will appear here.', 'wisdomrain-html-reader' ), esc_html( $source ) ); ?></p>
        <?php else : ?>
            <p><?php esc_html_e( 'No source provided. Use the shortcode attribute "source" to specify content.', 'wisdomrain-html-reader' ); ?></p>
        <?php endif; ?>
    </div>
</div>
