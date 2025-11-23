<?php
/**
 * Manage readers admin template.
 *
 * @package WisdomRain\HTMLReader
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Manage Readers', 'wisdomrain-html-reader' ); ?></h1>

    <?php if ( ! empty( $notices ) ) : ?>
        <?php foreach ( $notices as $notice ) : ?>
            <div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible">
                <p><?php echo esc_html( $notice['message'] ); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2><?php esc_html_e( 'Create New Reader', 'wisdomrain-html-reader' ); ?></h2>
    <form method="post">
        <?php wp_nonce_field( 'wrhr_create_reader' ); ?>
        <input type="hidden" name="wrhr_create_reader" value="1" />

        <table class="form-table">
            <tr>
                <th>
                    <label for="wrhr_reader_name"><?php esc_html_e( 'Reader Name', 'wisdomrain-html-reader' ); ?></label>
                </th>
                <td>
                    <input type="text" name="wrhr_reader_name" id="wrhr_reader_name" required class="regular-text" />
                </td>
            </tr>
            <tr>
                <th>
                    <label for="wrhr_reader_slug"><?php esc_html_e( 'Slug (optional)', 'wisdomrain-html-reader' ); ?></label>
                </th>
                <td>
                    <input type="text" name="wrhr_reader_slug" id="wrhr_reader_slug" class="regular-text" />
                </td>
            </tr>
        </table>

        <p>
            <input type="submit" class="button button-primary" name="wrhr_create_reader" value="<?php esc_attr_e( 'Create Reader', 'wisdomrain-html-reader' ); ?>" />
        </p>
    </form>

    <hr />

    <h2><?php esc_html_e( 'Existing Readers', 'wisdomrain-html-reader' ); ?></h2>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'wisdomrain-html-reader' ); ?></th>
                <th><?php esc_html_e( 'Slug', 'wisdomrain-html-reader' ); ?></th>
                <th><?php esc_html_e( 'Books', 'wisdomrain-html-reader' ); ?></th>
                <th><?php esc_html_e( 'Shortcode', 'wisdomrain-html-reader' ); ?></th>
                <th><?php esc_html_e( 'Actions', 'wisdomrain-html-reader' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $readers ) ) : ?>
            <tr><td colspan="5"><?php esc_html_e( 'No readers yet.', 'wisdomrain-html-reader' ); ?></td></tr>
        <?php else : ?>
            <?php foreach ( $readers as $reader ) : ?>
                <?php
                $reader_id  = ! empty( $reader['id'] ) ? $reader['id'] : '';
                $edit_url   = $reader_id ? add_query_arg( array( 'page' => 'wrhr-reader-edit', 'id' => $reader_id ), admin_url( 'admin.php' ) ) : '';
                $delete_url = $reader_id ? wp_nonce_url(
                    add_query_arg(
                        array(
                            'page'   => 'wrhr-manage-readers',
                            'delete' => $reader_id,
                        ),
                        admin_url( 'admin.php' )
                    ),
                    'wrhr_delete_reader_' . $reader_id
                ) : '';
                ?>
                <tr>
                    <td><?php echo esc_html( $reader['name'] ?? '' ); ?></td>
                    <td><?php echo ! empty( $reader['slug'] ) ? esc_html( $reader['slug'] ) : '&mdash;'; ?></td>
                    <td><?php echo isset( $reader['books'] ) && is_array( $reader['books'] ) ? count( $reader['books'] ) : 0; ?></td>
                    <td><code>[wrhr_reader id="<?php echo esc_attr( $reader_id ); ?>"]</code></td>
                    <td>
                        <?php if ( $edit_url ) : ?>
                            <a href="<?php echo esc_url( $edit_url ); ?>">
                                <?php esc_html_e( 'Edit Books', 'wisdomrain-html-reader' ); ?>
                            </a>
                        <?php else : ?>
                            <span class="notice-error"><?php esc_html_e( 'Invalid reader.', 'wisdomrain-html-reader' ); ?></span>
                        <?php endif; ?>

                        <?php if ( $delete_url ) : ?>
                            | <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this reader?', 'wisdomrain-html-reader' ) ); ?>');">
                                <?php esc_html_e( 'Delete', 'wisdomrain-html-reader' ); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
