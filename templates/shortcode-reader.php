<?php
/**
 * Shortcode template for front-end reader output.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$reader_id    = isset( $reader['id'] ) ? $reader['id'] : '';
$reader_name  = ! empty( $reader['name'] ) ? $reader['name'] : '';
$reader_books = ! empty( $reader['books'] ) && is_array( $reader['books'] ) ? $reader['books'] : array();
?>
<div class="wrhr-reader-wrapper"
     data-reader-id="<?php echo esc_attr( $reader_id ); ?>"
     data-title="<?php echo esc_attr( $reader['name'] ?? '' ); ?>">

    <div class="wrhr-header">
        <h2><?php echo esc_html( $reader_name ); ?></h2>
    </div>

    <div class="wrhr-book-list">

        <?php if ( empty( $reader_books ) ) : ?>
            <p class="wrhr-reader__empty">
                <?php esc_html_e( 'No books available for this reader yet.', 'wisdomrain-html-reader' ); ?>
            </p>
        <?php else : ?>
            <?php foreach ( $reader_books as $i => $book ) :
                $title    = ! empty( $book['title'] ) ? $book['title'] : '';
                $author   = ! empty( $book['author'] ) ? $book['author'] : '';
                $html_url = ! empty( $book['html_url'] ) ? $book['html_url'] : '';
                $buy_link = ! empty( $book['buy_link'] ) ? $book['buy_link'] : '';
                ?>
                <div class="wrhr-book-card">

                    <div class="wrhr-book-info">

                        <div class="wrhr-book-title">
                            <?php echo esc_html( $title ); ?>
                        </div>

                        <div class="wrhr-book-author">
                            <?php echo esc_html( $author ); ?>
                        </div>

                    </div>

                    <div class="wrhr-actions">
                        <button
                            class="wrhr-read-btn"
                            data-html="<?php echo esc_attr( $html_url ); ?>"
                            data-reader-id="<?php echo esc_attr( $reader_id ); ?>"
                            data-reader="<?php echo esc_attr( $reader_id ); ?>"
                            data-index="<?php echo esc_attr( intval( $i ) ); ?>">
                            <?php esc_html_e( 'Read', 'wisdomrain-html-reader' ); ?>
                        </button>

                        <?php if ( $buy_link ) : ?>
                            <a class="wrhr-buy-link" href="<?php echo esc_url( $buy_link ); ?>" target="_blank" rel="noopener noreferrer">
                                <?php esc_html_e( 'Buy Now', 'wisdomrain-html-reader' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>
