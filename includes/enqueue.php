<?php
/**
 * Created by PhpStorm.
 * User: Ben
 * Date: 2019-06-03
 * Time: 10:34
 */

/**
 * Versions
 */
$version = '0.1';
if($_SERVER['HTTP_HOST']!="wiki.christinewilson.ca"){
	$version = time();
}


add_action( 'wp_enqueue_scripts', 'wiki_scripts' );
add_action( 'wp_footer', 'wiki_localize' );

//add_action( 'wp_enqueue_scripts', 'enqueue_styles' );

function enqueue_styles() {
	$version = '1.0.0';
	
	// Register Bootstrap Script
    wp_enqueue_script("bootstrap_js", "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js", array('jquery'), "4.3.1");

    //Register Bootstrap Styles
    wp_enqueue_style("bootstrap", "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css", "4.3.1");
	
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css', array(), $version );
	wp_enqueue_style('twentyseventeen-style', get_stylesheet_uri(), array(), $version);
}

/**
 * Enqueue and register theme scripts.
 */
function wiki_scripts() {
    global $version;

    // Dependency Management.
    wp_register_script( "jquery-serialize-json", get_stylesheet_directory_uri() . "/assets/js/jquery.serializejson.min.js", [ "jquery" ], "2.8.1", true );
    wp_register_script( "underscore_js", get_stylesheet_directory_uri() . "/assets/js/underscore-min.js", array(), "1.8.3", true );
    wp_enqueue_script( "bootstrap_js", "https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js", array(), "4.3.1", true );
    wp_enqueue_style('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.7.2/css/all.css');

    $style_dependencies    = [ 'bootstrap','font-awesome' ];
    $script_dependencies[] = 'jquery-serialize-json';
    $script_dependencies[] = 'underscore_js';
	
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css', array(), $version );
    wp_enqueue_style( 'blank-template', get_stylesheet_directory_uri() . '/assets/css/style.css', $style_dependencies, $version );
    wp_enqueue_script( 'blank-template', get_stylesheet_directory_uri() . '/assets/js/script.js', $script_dependencies, $version, true );


}


/**
 * Localize, yo.
 */
function wiki_localize(){

    return;
}