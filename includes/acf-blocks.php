<?php
add_action('acf/init', 'my_acf_init');
function my_acf_init() {

    // check function exists
    if( function_exists('acf_register_block') ) {


        //home slider (fullwidth)
        acf_register_block(array(
            'name'				=> 'premierelocates_homeslider',
            'title'				=> __('Premiere Locates Homepage Slider'),
            'description'		=> __('Slider built for Premiere Locates homepage.'),
            'render_template'	=> '/template-parts/ACF/home-slider-block.php',
            'category'			=> 'formatting',
            'icon'				=> 'admin-comments',
            'keywords'			=> array( 'slider' ),
            'mode' => 'edit'
        ));

        //accordion (within container)
        acf_register_block(array(
            'name'				=> 'premierelocates_accordionsection',
            'title'				=> __('Premiere Locates Accordion Section'),
            'description'		=> __('Accordion within a container area.'),
            'render_template'	=> '/template-parts/ACF/accordion-block.php',
            'category'			=> 'formatting',
            'icon'				=> 'admin-comments',
            'keywords'			=> array( 'accordion' ),
            'mode' => 'edit'
        ));

        //tabbed (within container)
        acf_register_block(array(
            'name'				=> 'premierelocates_tabbedsection',
            'title'				=> __('Premiere Locates Tabbed Section'),
            'description'		=> __('Tabbed functionality within a container area.'),
            'render_template'	=> '/template-parts/ACF/tabbed-block.php',
            'category'			=> 'formatting',
            'icon'				=> 'admin-comments',
            'keywords'			=> array( 'tabs' ),
            'mode' => 'edit'
        ));

        //form fields (within container)
        acf_register_block(array(
            'name'				=> 'premierelocates_formsection',
            'title'				=> __('Premiere Locates Form Section'),
            'description'		=> __('Gravity Forms functionality within a container area.'),
            'render_template'	=> '/template-parts/ACF/form-block.php',
            'category'			=> 'formatting',
            'icon'				=> 'admin-comments',
            'keywords'			=> array( 'form' ),
            'mode' => 'edit'
        ));

        //form fields (within container)
        acf_register_block(array(
            'name'				=> 'premierelocates_contentsection',
            'title'				=> __('Premiere Locates Content Section'),
            'description'		=> __('Unique basic content repeater.'),
            'render_template'	=> '/template-parts/ACF/content-block.php',
            'category'			=> 'formatting',
            'icon'				=> 'admin-comments',
            'keywords'			=> array( 'content' ),
            'mode' => 'edit'
        ));


    }
}


//Allow blocks on certain pages // post types - Allowing all for now so this is not needed until further direction on the website

//global $pagenow;
//
//if ( ('page' === get_post_type( $_GET['post'] ) ) || ($pagenow == 'post-new.php'  && $_GET['post_type'] == 'page') ){
//
//    add_filter( 'allowed_block_types', 'rc_allowed_block_types' );
//    function rc_allowed_block_types( ) {
//
//        return array(
//
//            'acf/main-content-block',
//            'core/shortcode',
//
//        );
//    }
//
//} elseif ( 'post' != $pagenow->post_type ) {
//
//    add_filter( 'allowed_block_types', 'rc_allowed_block_types' );
//    function rc_allowed_block_types( ) {
//
//        return array(
//
//            'acf/main-content-block',
//
//        );
//    }
//
//} else {
//
//    add_filter( 'allowed_block_types', 'rc_allowed_block_types' );
//    function rc_allowed_block_types( ) {
//
//        return array(
//
//            'core/paragraph',
//            'core/blockquote',
//            'core/heading',
//            'core/list',
//            'core/freeform',
//            'core/html',
//            'core/video',
//            'core/table',
//              'core/media-text',
//              'core/separator',
//              'core/spacer',
//              'core/columns',
//              'core/shortcode',
//              'core-embed/youtube',
//
//            'acf/column-link-block',
//            'acf/contact-information',
//            'acf/block-quote',
//            'acf/accordion',
//            'acf/download-list',
//            'acf/bordered-button',
//            'acf/form-selection',
//            'acf/color-bg-text-block',
//            'acf/main-content-block',
//            'acf/image-columns',
//            'acf/text-columns',
//            'gravityforms/form'
//
//
//            /**
//             *
//             * Tabbed content removed - This will not be available
//             *
//             */
//            //'acf/tabs',
//        );
//    }
//
//}
//
