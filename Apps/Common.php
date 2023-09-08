<?php

namespace Jakaria_Istauk_Plugins\Apps;

class Common
{
    public function __construct()
    {
        add_action( 'init', [$this, 'register_post_type_recipe'] );
    }
    public function register_post_type_recipe() {
	    $labels = array(
		    'name'               => _x( 'Recipe', 'post type general name', 'your-plugin-textdomain' ),
		    'singular_name'      => _x( 'Recipe', 'post type singular name', 'your-plugin-textdomain' ),
		    'menu_name'          => _x( 'Recipe', 'admin menu', 'your-plugin-textdomain' ),
		    'name_admin_bar'     => _x( 'Recipe', 'add new on admin bar', 'your-plugin-textdomain' ),
		    'add_new'            => _x( 'Add New', 'Recipe', 'your-plugin-textdomain' ),
		    'add_new_item'       => __( 'Add New Recipe', 'your-plugin-textdomain' ),
		    'new_item'           => __( 'New Recipe', 'your-plugin-textdomain' ),
		    'edit_item'          => __( 'Edit Recipe', 'your-plugin-textdomain' ),
		    'view_item'          => __( 'View Recipe', 'your-plugin-textdomain' ),
		    'all_items'          => __( 'All Recipes', 'your-plugin-textdomain' ),
		    'search_items'       => __( 'Search Recipe', 'your-plugin-textdomain' ),
		    'parent_item_colon'  => __( 'Parent Recipe:', 'your-plugin-textdomain' ),
		    'not_found'          => __( 'No Recipe found.', 'your-plugin-textdomain' ),
		    'not_found_in_trash' => __( 'No Recipe found in Trash.', 'your-plugin-textdomain' )
	    );

	    $args = array(
		    'labels'                => $labels,
		    'description'           => __( 'Description.', 'your-plugin-textdomain' ),
		    'public'                => true,
		    'publicly_queryable'    => true,
		    'show_ui'               => true,
		    'show_in_menu'          => true,
		    'show_in_rest'          => true,
		    'query_var'             => true,
		    'menu_icon'             => 'dashicons-admin-users',
		    'rewrite'               => array( 'slug' => __( 'recipe', 'recipe' ) ),
		    'capability_type'       => 'post',
		    'has_archive'           => true,
		    'hierarchical'          => false,
		    'rest_base'             => 'recipe',
		    'rest_controller_class' => 'WP_REST_Posts_Controller',
		    'menu_position'         => 5,
		    'supports'              => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ],
		    'taxonomies'            => [ 'recipe_cat', 'recipe_tag' ]
	    );

	    $recipe_cat_args = [
		    'label'             => __( 'Category', 'txtdomain' ),
		    'hierarchical'      => true,
		    'rewrite'           => [ 'slug' => 'recipe_cat' ],
		    'show_admin_column' => true,
		    'show_in_rest'      => true,
		    'labels'            => [
			    'singular_name'     => __( 'Category', 'txtdomain' ),
			    'all_items'         => __( 'All Categories', 'txtdomain' ),
			    'edit_item'         => __( 'Edit Category', 'txtdomain' ),
			    'view_item'         => __( 'View Category', 'txtdomain' ),
			    'update_item'       => __( 'Update Category', 'txtdomain' ),
			    'add_new_item'      => __( 'Add New Category', 'txtdomain' ),
			    'new_item_name'     => __( 'New Category Name', 'txtdomain' ),
			    'search_items'      => __( 'Search Categories', 'txtdomain' ),
			    'parent_item'       => __( 'Parent Category', 'txtdomain' ),
			    'parent_item_colon' => __( 'Parent Category:', 'txtdomain' ),
			    'not_found'         => __( 'No Category found', 'txtdomain' ),
		    ]
	    ];

	    register_taxonomy( 'recipe_cat', [ 'recipe' ], $recipe_cat_args );
	    register_taxonomy_for_object_type( 'recipe_cat', 'recipe' );
	    register_post_type( 'recipe', $args );
    }
}