<div class="wrap">
    <h1>Edit Books for: <?php echo esc_html($reader['name']); ?></h1>

    <form method="post">
        <?php wp_nonce_field('wrhr_save_books'); ?>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:25%">Title</th>
                    <th style="width:20%">Author</th>
                    <th style="width:35%">HTML URL</th>
                    <th style="width:15%">Buy Link</th>
                    <th style="width:5%">Delete</th>
                </tr>
            </thead>
            <tbody id="wrhr-books-body">

                <?php if (!empty($reader['books'])): ?>
                    <?php foreach ($reader['books'] as $i => $b): ?>
                        <tr>
                            <td><input type="text" name="wrhr_title[]" class="regular-text" value="<?php echo esc_attr($b['title']); ?>"></td>
                            <td><input type="text" name="wrhr_author[]" class="regular-text" value="<?php echo esc_attr($b['author']); ?>"></td>
                            <td><input type="url" name="wrhr_html_url[]" class="regular-text" value="<?php echo esc_attr($b['html_url']); ?>"></td>
                            <td><input type="url" name="wrhr_buy_link[]" class="regular-text" value="<?php echo esc_attr($b['buy_link']); ?>"></td>
                            <td><input type="checkbox" class="wrhr-delete-row"></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>

            </tbody>
        </table>

        <p>
            <button type="button" class="button" id="wrhr-add-book">+ Add New Book</button>
        </p>

        <p>
            <input type="submit" class="button button-primary" name="wrhr_save_books" value="Update Reader Record">
        </p>
    </form>
</div>

<script>
document.getElementById('wrhr-add-book').addEventListener('click', function() {

    let row = `
        <tr>
            <td><input type="text" name="wrhr_title[]" class="regular-text"></td>
            <td><input type="text" name="wrhr_author[]" class="regular-text"></td>
            <td><input type="url" name="wrhr_html_url[]" class="regular-text"></td>
            <td><input type="url" name="wrhr_buy_link[]" class="regular-text"></td>
            <td><input type="checkbox" class="wrhr-delete-row"></td>
        </tr>
    `;

    document.getElementById('wrhr-books-body').insertAdjacentHTML('beforeend', row);
});

// Delete selected rows before submit
document.addEventListener('submit', function(e) {
    document.querySelectorAll('.wrhr-delete-row:checked')
        .forEach(ch => ch.closest('tr').remove());
});
</script>
