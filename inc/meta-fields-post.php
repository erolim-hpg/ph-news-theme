<?php

/**
 * Create customized post meta fields and render
 * - https://developer.wordpress.org/reference/functions/add_meta_box/
 */
class CustomPostMeta
{

    /**
     * Add functions to WordPress hooks  
     */
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_box'));
        add_action('save_post', array($this, 'save'));
        $this->register_in_rest();
        add_action('rest_post_query', array($this, 'filter_request_meta'), 10, 2);
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
            <input type="checkbox" name="featured_spotlight" id="featured_spotlight" <?php if ($featured_spotlight) echo 'checked="true"'; ?>">
            <label for="featured_spotlight"><?php _e('Featured in Spotlight?') ?></label>
            <p class="components-form-token-field__help">News spotlight can be shown in various pages, such as Meet the Team.</p>
        </div>
        <div class="form-field">
            <input type="checkbox" name="featured_newspage" id="featured_newspage" <?php if ($featured_newspage) echo 'checked="true"'; ?>">
            <label for="featured_newspage"><?php _e('Featured in News Page Slider?') ?></label>
            <p class="components-form-token-field__help">Slider shown in the main News page.</p>
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
        if (!wp_verify_nonce($_POST['ph_post_meta_nonce'], 'ph_post_meta')) {
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
            if (!isset($_POST[$field])){
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
}

new CustomPostMeta();
