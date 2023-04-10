<?php


class PageCustomMeta
{

    public function __construct()
    {
        add_action('init', 'register_taxonomy_section');
    }

    function register_taxonomy_section()
    {
        $labels = array(
            'name'              => 'Sections',
            'singular_name'     => 'Section',
            'search_items'      => 'Search Sections',
            'all_items'         => 'All Sections',
            'parent_item'       => 'Parent Section',
            'parent_item_colon' => 'Parent Section:',
            'edit_item'         => 'Edit Section',
            'update_item'       => 'Update Section',
            'add_new_item'      => 'Add New Section',
            'new_item_name'     => 'New Section Name',
            'menu_name'         => 'Section',
        );
        $args   = array(
            'hierarchical'      => true, // make it hierarchical (like categories)
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'section'],
        );
        register_taxonomy('section', ['page'], $args);
    }

}


