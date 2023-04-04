<?php

/**
 * Create customized post meta fields and render
 * - https://developer.wordpress.org/reference/functions/add_meta_box/
 */
class PostCustomMeta
{

    /**
     * Add functions to WordPress hooks  
     */
    public function __construct()
    {
        add_action('init', array($this, 'change_post_labels'));

        // Add meta box
        add_action('add_meta_boxes', array($this, 'add_box'));

        // Handle save post to save meta fields
        add_action('save_post', array($this, 'save'));

        // Register meta fields in REST
        $this->register_in_rest();

        // Add filter by meta fields to REST
        add_action('rest_post_query', array($this, 'filter_request_meta'), 10, 2);

        // Functions to add column, populate its content and to remove it on Screen Options
        add_filter('manage_posts_columns', array($this, 'add_column'), 10, 2);
        add_filter('manage_posts_custom_column', array($this, 'populate_column'), 10, 2);
        add_filter('manage_edit_post_columns', array($this, 'remove_column'));

        // Add custom box to Quick Edit & Bulk Edit
        add_action('quick_edit_custom_box', array($this, 'quick_edit_custom_box'), 10, 2);
        add_action('bulk_edit_custom_box', array($this, 'bulk_edit_custom_box'), 10, 2);

        // Handle save post to save meta fields on quick edit
        add_action('save_post', array($this, 'quickedit_save'));
    }

    public function change_post_labels()
    {
        $post_type = get_post_type_object('post');
        $labels = $post_type->labels;
        $labels->name = 'Articles';
        $labels->singular_name = 'Article';
        $labels->add_new = 'Add Article';
        $labels->add_new_item = 'Add New Article';
        $labels->edit_item = 'Edit Article';
        $labels->new_item = 'Article';
        $labels->view_item = 'View Article';
        $labels->search_items = 'Search Articles';
        $labels->not_found = 'No Article found';
        $labels->not_found_in_trash = 'No Article found in Trash';
        $labels->all_items = 'All Articles';
        $labels->menu_name = 'Articles';
        $labels->name_admin_bar = 'Articles';
    }

    /**
     * Add new meta box to post
     *
     * @param String $post_type
     * @return void
     */
    public function add_box($post_type)
    {
        // Limit meta box to certain post types.
        $post_types = array('post');

        if (in_array($post_type, $post_types)) {
            add_meta_box(
                'post-meta-box',
                esc_html__('Additional'),
                array($this, 'render'),
                $post_type,
                'side',
                'core'
            );
        }
    }

    /**
     * Render meta box content
     *
     * @param WP_Post $post
     * @return void
     */
    public function render($post)
    {
        // Add an nonce field for security verification
        wp_nonce_field('ph_post_meta', 'ph_post_meta_nonce');

        // Get current values for meta fields
        $featured_spotlight = get_post_meta($post->ID, 'featured_spotlight', true);
        $featured_newspage = get_post_meta($post->ID, 'featured_newspage', true);

        // Render
?>
        <div class="form-field">
            <input type="checkbox" name="featured_newspage" id="featured_newspage" <?php if ($featured_newspage) echo 'checked="true"'; ?>">
            <label for="featured_newspage"><?php _e('Featured in News Page Slider?') ?></label>
            <p class="components-form-token-field__help">Slider shown in the main News page.</p>
        </div>
        <div class="form-field">
            <input type="checkbox" name="featured_spotlight" id="featured_spotlight" <?php if ($featured_spotlight) echo 'checked="true"'; ?>">
            <label for="featured_spotlight"><?php _e('Featured in Spotlight?') ?></label>
            <p class="components-form-token-field__help">News spotlight can be shown in various pages, such as Meet the Team.</p>
        </div>
        <?php
    }

    /**
     * Save meta data
     *
     * @param int $post_id
     * @return void|int
     */
    public function save($post_id)
    {
        // Check if nonce is set
        if (!isset($_POST['ph_post_meta_nonce'])) {
            return $post_id;
        }

        // Check if nonce is valid
        if (
            !wp_verify_nonce($_POST['ph_post_meta_nonce'], 'ph_post_meta')
            && !wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')
        ) {
            return $post_id;
        }

        // Check if is it doing autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check the user's permissions.
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }

        /* Sanitize input and update post meta */

        $fields = array(
            'featured_newspage',
            'featured_spotlight'
        );

        foreach ($fields as $field) {
            if (!isset($_POST[$field])) {
                delete_post_meta($post_id, $field);
                continue;
            }
            $value = $_POST[$field] == 'on' ? 1 : 0;
            update_post_meta($post_id, $field, $value);
        }
    }

    /**
     * Function to register fields in REST
     *
     * @return void
     */
    public function register_in_rest()
    {

        $fields = array(
            'featured_newspage',
            'featured_spotlight'
        );

        foreach ($fields as $field) {
            register_rest_field(
                'post',
                $field,
                array(
                    'get_callback' => array($this, 'get_meta')
                )
            );
        }
    }

    /**
     * Callback function to get meta field
     *
     * @param WP_Post $post
     * @param string $field_name
     * @return void
     */
    public function get_meta($post, $field_name)
    {
        return get_post_meta($post['id'], $field_name, true);
    }

    /**
     * Filter request by meta fields
     *
     * @return void
     */
    public function filter_request_meta($args, $request)
    {
        $args['meta_key'] = $request['meta_key'];
        $args['meta_value'] = $request['meta_value'];

        return $args;
    }

    /**
     * Add column to manage posts page
     *
     * @param mixed $posts_columns
     * @param string $post_type
     * @return void
     */
    public function add_column($posts_columns, $post_type)
    {
        $posts_columns['featured'] = '★ Featured in ★';
        return $posts_columns;
    }

    /**
     * Populate column in manage posts page
     *
     * @param string $column_name
     * @param int $post_id
     * @return void
     */
    public function populate_column($column_name, $post_id)
    {
        // if you have to populate more that one columns, use switch()
        if ($column_name == 'featured') {
            $newspage = get_post_meta($post_id, 'featured_newspage', true);
            $spotlight = get_post_meta($post_id, 'featured_spotlight', true);
            if ($newspage) {
                echo 'Newspage';
                if ($spotlight)  echo ', <br>'; // Echo separator if both are true
            }
            if ($spotlight)
                echo 'Spotlight';
        }
    }

    /**
     * Remove column
     *
     * @param Array|Object $posts_columns
     * @return Array|Object
     */
    public function remove_column($posts_columns)
    {
        unset($posts_columns['featured']);
        return $posts_columns;
    }

    /**
     * Quick edit custom box
     *
     * @param string $column_name
     * @param string $post_type
     * @return void
     */
    public function quick_edit_custom_box($column_name, $post_type)
    {
        if ($column_name == 'featured') :
        ?>
            <fieldset class="inline-edit-col-left">
                <div class="inline-edit-col featured">
                    <label for="featured_newspage">
                        <input type="checkbox" name="featured_newspage" id="featured_newspage">
                        <span class="checkbox-title">Feature in Newspage?</span>
                    </label>
                </div>
                <div class="inline-edit-col featured">
                    <label for="featured_spotlight">
                        <input type="checkbox" name="featured_spotlight" id="featured_spotlight">
                        <span class="checkbox-title">Feature in Spotlight?</span>
                    </label>
                </div>
            </fieldset>
<?php
        endif;
    }

    /**
     * Bulk edit custom box
     *
     * @param string $column_name
     * @param string $post_type
     * @return void
     */
    public function bulk_edit_custom_box($column_name, $post_type)
    {
        if ($column_name == 'featured') {
            echo 'Extra content in the bulk edit box';
        }
    }

    /**
     * Save meta data after quick edit
     *
     * @param int $post_id
     * @return void|int
     */
    public function quickedit_save($post_id)
    {
        // Check if nonce is valid
        if (!isset($_POST['_inline_edit']) || !wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce')) {
            return;
        }

        /* Sanitize input and update post meta */

        $fields = array(
            'featured_newspage',
            'featured_spotlight'
        );

        foreach ($fields as $field) {
            if (!isset($_POST[$field])) {
                delete_post_meta($post_id, $field);
                continue;
            }
            $value = $_POST[$field] == 'on' ? 1 : 0;
            update_post_meta($post_id, $field, $value);
        }
    }
}

new PostCustomMeta();
