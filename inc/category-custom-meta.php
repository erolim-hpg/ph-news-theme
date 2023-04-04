<?php

/**
 * Add new color picker field to "Add new Category" screen
 * - https://developer.wordpress.org/reference/hooks/taxonomy_add_form_fields/
 *
 * @param String $taxonomy
 * @return void
 */
function colorpicker_field_add_new_category($taxonomy)
{
    register_term_meta('category', '_category_color', ['show_in_rest' => true]);
?>

    <div class="form-field term-colorpicker-wrap">
        <label for="term-colorpicker"><?php _e('Category Color') ?></label>
        <input name="_category_color" value="#d9ab4d" class="colorpicker" id="term-colorpicker">
    </div>

<?php
}
add_action('category_add_form_fields', 'colorpicker_field_add_new_category');

/**
 * Add new colopicker field to "Edit Category" screen
 * - https://developer.wordpress.org/reference/hooks/taxonomy_add_form_fields/
 *
 * @param WP_Term_Object $term
 * @return void
 */
function colorpicker_field_edit_category($term)
{
    $color = get_term_meta($term->term_id, '_category_color', true);
    $color = (!empty($color)) ? "#{$color}" : "#d9ab4d";
?>

    <tr class="form-field term-colorpicker-wrap">
        <th scope="row"><label for="term-colorpicker"><?php _e('Category Color') ?></label></th>
        <td>
        <input name="_category_color" value="<?php echo $color; ?>" class="colorpicker" id="term-colorpicker">
        </td>
    </tr>

<?php

}
add_action( 'category_edit_form_fields', 'colorpicker_field_edit_category' );   

/**
 * Term Metadata - Save Created and Edited Term Metadata
 * - https://developer.wordpress.org/reference/hooks/created_taxonomy/
 * - https://developer.wordpress.org/reference/hooks/edited_taxonomy/
 *
 * @param Integer $term_id
 *
 * @return void
 */
function save_termmeta( $term_id ) {

    // Save term color if possible
    if( isset( $_POST['_category_color'] ) && ! empty( $_POST['_category_color'] ) ) {
        update_term_meta( $term_id, '_category_color', sanitize_hex_color_no_hash( $_POST['_category_color'] ) );
    } else {
        delete_term_meta( $term_id, '_category_color' );
    }

}
add_action( 'created_category', 'save_termmeta' );  
add_action( 'edited_category',  'save_termmeta' );  

/**
 * Enqueue colorpicker styles and scripts.
 * - https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/
 *
 * @return void
 */
function category_colorpicker_enqueue( $taxonomy ) {

    if( null !== ( $screen = get_current_screen() ) && 'edit-category' !== $screen->id ) {
        return;
    }

    // Colorpicker Scripts
    wp_enqueue_script( 'wp-color-picker' );

    // Colorpicker Styles
    wp_enqueue_style( 'wp-color-picker' );

}
add_action( 'admin_enqueue_scripts', 'category_colorpicker_enqueue' );
