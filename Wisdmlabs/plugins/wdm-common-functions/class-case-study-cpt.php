<?php
namespace WDMCommonFunctions;

/**
* Class to handle Elumine Renewals Notification to admin, Lpp to Leap
*/

class CaseStudyCpt
{
    // To store current class object
    private static $instance;
    /**
     * __construct
     *
     * @return void
     */
    private function __construct()
    {
        add_action( 'init', array($this,'wdm_cpt_init') );
    }
    
    /**
     * wdm_cpt_init register custom taxnomy and post type
     *
     * @return void
     */
    public function wdm_cpt_init()
    {
        $taxArray = array(
            array(
              "tax_name" => 'Case Study Category',
              "tax_name_en" => 'case-study-category'
            )
          );
         
        foreach ($taxArray as $tax) {
            $labels = array(
              "name" => __( "Case Study Categories", "" ),
              "singular_name" => __( $tax['tax_name'], "" ),
              "menu_name" => __( $tax['tax_name'], "" ),
              "all_items" => __( "All Case Study Categories", "" ),
              "edit_item" => __( "Edit ".$tax['tax_name'], "" ),
              "view_item" => __( "View ".$tax['tax_name'], "" ),
              "update_item" => __( "Update ".$tax['tax_name'], "" ),
              "add_new_item" => __( "Add New ".$tax['tax_name'], "" ),
              "new_item_name" => __( "New ".$tax['tax_name'], "" ),
              "search_items" => __( "Search ".$tax['tax_name'], "" ),
            );
         
            $args = array(
              "label" => __( $tax['tax_name'], "" ),
              "labels" => $labels,
              "public" => true,
              "hierarchical" => true,
              "label" => $tax['tax_name'],
              "show_ui" => true,
              "show_in_menu" => true,
              "show_in_nav_menus" => true,
              "show_admin_column" => true,
              "query_var" => true,
              "rewrite" => array( 'slug' => $tax['tax_name_en'], 'with_front' => false ),
              "show_admin_column" => true,
              "show_in_rest" => true,
              "rest_base" => $tax['tax_name_en'],
              "show_in_quick_edit" => true,
            );
            register_taxonomy( $tax['tax_name_en'], 'post', $args );
        }
        
        $labels = array(
            'name'                  => __( 'Case Studies', 'recipe' ),
            'singular_name'         => __( 'Case Study', 'recipe' ),
            'menu_name'             => __( 'Case Studies', 'recipe' ),
            'name_admin_bar'        => __( 'Case Study', 'recipe' ),
            'add_new'               => __( 'Add New', 'recipe' ),
            'add_new_item'          => __( 'Add New case study', 'recipe' ),
            'new_item'              => __( 'New case Study', 'recipe' ),
            'edit_item'             => __( 'Edit case Study', 'recipe' ),
            'view_item'             => __( 'View case study', 'recipe' ),
            'all_items'             => __( 'All case studies', 'recipe' ),
            'search_items'          => __( 'Search case studies', 'recipe' ),
            'parent_item_colon'     => __( 'Parent case studies:', 'recipe' ),
            'not_found'             => __( 'No case studies found.', 'recipe' ),
            'not_found_in_trash'    => __( 'No case studies found in Trash.', 'recipe' ),
            'featured_image'        => __( 'Case study Cover Image', 'recipe' ),
            'set_featured_image'    => __( 'Set case study image', 'recipe' ),
            'remove_featured_image' => __( 'Remove case study image', 'recipe' ),
            'use_featured_image'    => __( 'Use as case study image', 'recipe' ),
            'archives'              => __( 'Case study archives', 'recipe' ),
            'insert_into_item'      => __( 'Insert into case study', 'recipe' ),
            'uploaded_to_this_item' => __( 'Uploaded to this case study', 'recipe' ),
            'filter_items_list'     => __( 'Filter case studies list', 'recipe' ),
            'items_list_navigation' => __( 'Case studies list navigation', 'recipe' ),
            'items_list'            => __( 'Case studies list', 'recipe' ),
        );     
        $args = array(
            'labels'             => $labels,
            'description'        => 'Case Study custom post type.',
            'public'             => true,
            'exclude_from_search'=> true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'           => array(
                                        'slug'          => 'case-study',
                                        'with_front'    =>  false
                                    ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => true,
            'menu_position'      => 20,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
            'taxonomies'         => array( 'case-study-category' ),
            'show_in_rest'       => true
        );
          
        register_post_type( 'case-study', $args );
    }

    // To get object of the current class
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CaseStudyCpt;
        }
        return self::$instance;
    }
}
