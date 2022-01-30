<?php

/**
 * Versions
 */
$version = '0.1';
if($_SERVER['HTTP_HOST']!="wiki.christinewilson.ca"){
	$version = time();
}


add_action( 'wp_enqueue_scripts', 'wiki_scripts' );
add_action( 'wp_footer', 'wiki_localize', 99 );

/**
 * Enqueue and register theme scripts.
 */
function wiki_scripts() {
    global $version;
	
	wp_localize_script( 'jquery', 'ajax_login_object', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'redirecturl' => get_permalink(),
        'adminurl' => admin_url(),
        'loadingmessage' => __('Sending info, please wait...')
    ));

    // Dependency Management.
    wp_register_script( "jquery-serialize-json", get_stylesheet_directory_uri() . "/assets/js/jquery.serializejson.min.js", [ "jquery" ], "2.8.1", true );
    wp_register_script( "underscore_js", get_stylesheet_directory_uri() . "/assets/js/underscore-min.js", array(), "1.8.3", true );
    wp_enqueue_script( "bootstrap_js", "https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js", array(), "4.3.1", true );
    wp_enqueue_style('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.7.2/css/all.css');

    $style_dependencies    = [ 'bootstrap','font-awesome' ];
    $script_dependencies[] = 'jquery-serialize-json';
    $script_dependencies[] = 'underscore_js';
	
	wp_enqueue_style('bootstrap-toggle', 'https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css');
	wp_enqueue_script('bootstrap-toggle_js', 'https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js', array('bootstrap_js'),'', true);
	
	wp_enqueue_script('bootstrap-validator', get_stylesheet_directory_uri() . '/assets/js/bootstrapValidator.min.js', array('bootstrap_js'),'', true);
	
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css', array(), $version );
    wp_enqueue_style( 'blank-template', get_stylesheet_directory_uri() . '/assets/css/style.css', $style_dependencies, $version );
    wp_enqueue_script( 'blank-template', get_stylesheet_directory_uri() . '/assets/js/script.js', $script_dependencies, $version, true );
	
	//Code styler also used on Stack Exchange
	//https://meta.stackexchange.com/questions/347738/google-code-prettify-has-been-officially-discontinued-its-time-for-se-to-maint
	//https://highlightjs.org/download/
	//wp_enqueue_style( 'highlightjs', get_stylesheet_directory_uri() . '/assets/css/highlightjs.min.css', 'twentyseventeen-style', $version );
    //wp_enqueue_script( 'highlightjs', get_stylesheet_directory_uri() . '/assets/js/highlight.min.js', 'twentyseventeen', $version, true );
	
	wp_enqueue_style( 'prism', get_stylesheet_directory_uri() . '/assets/css/prism.css', 'twentyseventeen-style', $version );
    wp_enqueue_script( 'prism', get_stylesheet_directory_uri() . '/assets/js/prism.js', 'twentyseventeen', $version, true );

}


/**
 * Localize, yo.
 */
function wiki_localize(){
    return;
}