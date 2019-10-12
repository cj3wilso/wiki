<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_styles' );

function enqueue_styles() {
	$version = '1.0.0';
	if($_SERVER['HTTP_HOST']!="wiki.christinewilson.ca"){
		$version = time();
	}
	// Register Bootstrap Script
    wp_enqueue_script("bootstrap_js", "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js", array('jquery'), "4.3.1");

    //Register Bootstrap Styles
    wp_enqueue_style("bootstrap", "https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css", "4.3.1");
	
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css', array(), $version );
	wp_enqueue_style('twentyseventeen-style', get_stylesheet_uri(), array(), $version);
}

define('INCLUDES_DIR', get_stylesheet_directory() . '/includes');

//require INCLUDES_DIR . '/enqueue.php';
//require INCLUDES_DIR . '/admin.php';

add_action( 'set_object_terms', 'auto_set_parent_terms', 9999, 6 );

/**
 * Automatically set/assign parent taxonomy terms to posts
 *
 * This function will automatically set parent taxonomy terms whenever terms are set on a post,
 * with the option to configure specific post types, and/or taxonomies.
 *
 *
 * @param int    $object_id  Object ID.
 * @param array  $terms      An array of object terms.
 * @param array  $tt_ids     An array of term taxonomy IDs.
 * @param string $taxonomy   Taxonomy slug.
 * @param bool   $append     Whether to append new terms to the old terms.
 * @param array  $old_tt_ids Old array of term taxonomy IDs.
 */
function auto_set_parent_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {

    /**
     * We only want to move forward if there are taxonomies to set
     */
    if( empty( $tt_ids ) ) return FALSE;

    /**
     * Set specific post types to only set parents on.  Set $post_types = FALSE to set parents for ALL post types.
     */
    $post_types = array( 'post' );
    if( $post_types !== FALSE && ! in_array( get_post_type( $object_id ), $post_types ) ) return FALSE;

    /**
     * Set specific post types to only set parents on.  Set $post_types = FALSE to set parents for ALL post types.
     */
    $tax_types = array( 'category' );
    if( $tax_types !== FALSE && ! in_array( get_post_type( $object_id ), $post_types ) ) return FALSE;

    foreach( $tt_ids as $tt_id ) {

        $parent = wp_get_term_taxonomy_parent_id( $tt_id, $taxonomy );

        if( $parent ) {
            wp_set_post_terms( $object_id, array($parent), $taxonomy, TRUE );
        }

    }

}


/*=============================================
                BREADCRUMBS
=============================================*/
function wpse_get_category_parents( $id, $link = false, $separator = '/', $nicename = false, $visited = array(), $iscrumb=false ) {
    $chain = '';
    $parent = get_term( $id, 'category' );
	$search="";
	if(isset($_GET['s'])){
		$search = "?s=".$_GET['s'];
	}
    if ( is_wp_error( $parent ) ) {
        return $parent;
    }
    if ( $nicename ) {
        $name = $parent->slug;
    } else {
        $name = $parent->name;
    }
    if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
        $visited[] = $parent->parent;
        $chain .= wpse_get_category_parents( $parent->parent, $link, $separator, $nicename, $visited , $iscrumb);
    }
    if (is_rtl()){
        $sep_direction ='\\';
    } else {
        $sep_direction ='/';
    }
    if ($iscrumb){
        $chain .= '<li><span class="sep">'.$sep_direction.'</span><a href="' . esc_url( get_category_link( $parent->term_id ) ).$search. '"><span class="entry-meta">'.$name.'</span></a></li>' . $separator ;
    } elseif ( $link && !$iscrumb) {
        $chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ).$search . '">'.$name.'</a>' . $separator ;
    } else {
        $chain .= $name.$separator;
    }
    return $chain;
}

function wpse_get_breadcrumbs() {
    global $wp_query;
        if (is_rtl()){
            $sep_direction ='\\';
        } else {
            $sep_direction ='/';
        }
		$search="";
		if(isset($_GET['s'])){
			$search = "?s=".$_GET['s'];
		}
		?>
    <ul id="breadcrumbs"><?php
        // Adding the Home Page  ?>
        <li><a href="<?php echo esc_url( home_url() ).$search; ?>"><span class="entry-meta"> Home </span></a></li><?php
        if ( ! is_front_page() ) {
            // Check for categories, archives, search page, single posts, pages, the 404 page, and attachments
            if ( is_category() ) {
                $cat_obj     = $wp_query->get_queried_object();
                $thisCat     = get_category( $cat_obj->term_id );
                $parentCat   = get_category( $thisCat->parent );
                if($thisCat->parent != 0) {
                    $cat_parents = wpse_get_category_parents( $parentCat, true, '', false, array(), true );
                }
                if ( $thisCat->parent != 0 && ! is_wp_error( $cat_parents ) ) {
                    echo $cat_parents;
                }
                if(is_search()) {
					//remove search parameter
					$url = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
					echo '<li><span class="sep">'.$sep_direction.'</span><a href="'. esc_url( home_url($url) ).'"><span class="entry-meta">'.single_cat_title( '', false ).'</span></a></li>';
				}else{
					echo '<li><span class="sep">'.$sep_direction.'</span><span class="entry-meta">'.single_cat_title( '', false ).'</span></li>';
				}
            } elseif ( is_archive() && ! is_category() ) { ?>
                <li><span class="sep"><?php echo $sep_direction;?></span> <?php _e( 'Archives' ); ?></li><?php
            } elseif ( is_search() ) { ?>
                <li><span class="sep"><?php echo $sep_direction;?></span> <?php _e( 'Search Results' ); ?></li><?php
            } elseif ( is_404() ) { ?>
                <li><span class="sep"><?php echo $sep_direction;?></span> <?php _e( '404 Not Found' ); ?></li><?php
            } elseif ( is_singular() ) {
                $category    = get_the_category();
                $category_id = get_cat_ID( $category[0]->cat_name );
                $cat_parents = wpse_get_category_parents( $category_id, true, '',false, array(), true );
                if ( ! is_wp_error( $cat_parents ) ) {
                    echo $cat_parents;
                }?>
                <li>
                    <a href="<?php the_permalink();?>"><span class="sep"><?php echo $sep_direction;?></span><?php the_title();?></a>
                </li><?php
            } elseif ( is_singular( 'attachment' ) ) { ?>
                <li>
                    <span class="sep"><?php echo $sep_direction;?></span> <?php the_title(); ?>
                </li><?php
            } elseif ( is_page() ) {
                $post = $wp_query->get_queried_object();
                if ( $post->post_parent == 0 ) { ?>
                    <li><?php _e( '<span class="sep">/</span>' ); the_title(); ?></li><?php
                } else {
                    $title = the_title( '','', false );
                    $ancestors = array_reverse( get_post_ancestors( $post->ID ) );
                    array_push( $ancestors, $post->ID );
                    foreach ( $ancestors as $ancestor ) {
                        if ( $ancestor != end( $ancestors ) ) { ?>
                            <li>
                                <span class="sep"><?php echo $sep_direction;?></span><a href="<?php echo esc_url( get_permalink( $ancestor ) ); ?>"> <span><?php echo strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ); ?></span></a>
                            </li><?php
                        } else { ?>
                            <li>
                                <span class="sep"><?php echo $sep_direction;?></span><?php echo strip_tags( apply_filters( 'single_post_title', get_the_title( $ancestor ) ) ); ?>
                            </li><?php
                        }
                    }
                }
            }
        } ?>
    </ul><?php
}
function prefix_add_content ($content){
		if ( !is_single() && has_excerpt()  ) {
			$content = get_the_excerpt();
			$content .= '<a href="'.get_the_permalink().'" class="link-more" >Read more &#62;</a>';
		}
	return $content;
}
add_filter ('the_content', 'prefix_add_content');


/*
 * Add categories to pages
 */
function add_cats_to_pages_definition()
{
    register_taxonomy_for_object_type('category', 'page');
}

add_action('init', 'add_cats_to_pages_definition');
