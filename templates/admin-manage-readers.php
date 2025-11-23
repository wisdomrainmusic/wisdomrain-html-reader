<?php
/**
 * Admin manage readers template.
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

    <h2 class="title"><?php esc_html_e( 'Add New Reader', 'wisdomrain-html-reader' ); ?></h2>
    <p><?php esc_html_e( 'Create a reader profile to manage books and sources.', 'wisdomrain-html-reader' ); ?></p>

    <form method="post">
        <?php wp_nonce_field( 'wrhr_create_reader' ); ?>
        <input type="hidden" name="wrhr_create_reader" value="1" />
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="wrhr_reader_name"><?php esc_html_e( 'Reader Name', 'wisdomrain-html-reader' ); ?></label></th>
                    <td>
                        <input name="wrhr_reader_name" id="wrhr_reader_name" type="text" class="regular-text" required />
                        <p class="description"><?php esc_html_e( 'Friendly label shown in the admin UI.', 'wisdomrain-html-reader' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="wrhr_reader_slug"><?php esc_html_e( 'Slug (optional)', 'wisdomrain-html-reader' ); ?></label></th>
                    <td>
                        <input name="wrhr_reader_slug" id="wrhr_reader_slug" type="text" class="regular-text" />
                        <p class="description"><?php esc_html_e( 'Leave blank to generate from the name.', 'wisdomrain-html-reader' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( __( 'Add Reader', 'wisdomrain-html-reader' ) ); ?>
    </form>

    <h2 class="title" style="margin-top:2em;">
        <?php esc_html_e( 'Existing Readers', 'wisdomrain-html-reader' ); ?>
    </h2>
    <p><?php esc_html_e( 'Manage or delete current readers.', 'wisdomrain-html-reader' ); ?></p>

    <?php if ( ! empty( $readers ) ) : ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th scope="col"><?php esc_html_e( 'Name', 'wisdomrain-html-reader' ); ?></th>
                    <th scope="col"><?php esc_html_e( 'Slug', 'wisdomrain-html-reader' ); ?></th>
                    <th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'wisdomrain-html-reader' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $readers as $reader ) : ?>
                    <tr>
                        <td><?php echo esc_html( $reader['name'] ?? '' ); ?></td>
                        <td><code><?php echo $reader['slug'] ? esc_html( $reader['slug'] ) : '&mdash;'; ?></code></td>
                        <td>
                            <?php
                            $delete_url = wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'page'   => 'wrhr-manage-readers',
                                        'delete' => $reader['id'],
                                    ),
                                    admin_url( 'admin.php' )
                                ),
                                'wrhr_delete_reader_' . $reader['id']
                            );
                            ?>
                            <a class="submitdelete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this reader?', 'wisdomrain-html-reader' ) ); ?>');">
                                <?php esc_html_e( 'Delete', 'wisdomrain-html-reader' ); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e( 'No readers found. Add your first reader above.', 'wisdomrain-html-reader' ); ?></p>
    <?php endif; ?>
</div>
