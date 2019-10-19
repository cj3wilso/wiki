<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 
define( 'THEME_TEXTDOMAIN', 'wiki-textdomain' );
define( 'INCLUDES_DIR', get_stylesheet_directory() . '/includes' );

require INCLUDES_DIR . '/admin.php';
require INCLUDES_DIR . '/enqueue.php';


add_action('wp_ajax_create_new_project', 'create_new_project');
add_action('wp_ajax_nopriv_create_new_project', 'create_new_project');
function create_new_project(){
	// First check the nonce, if it fails the function will break
    //check_ajax_referer( 'ajax-login-nonce', 'security' );

    parse_str($_POST['form'], $form);
	
	
	//$a = shell_exec("mkdir /var/www/jjj 2>&1");
	//echo "mkdir:<br />";
	//echo $a;
	//echo "<br />----------------<br />";

	/* exec("/csvexport.sh $table"); */

	/* double quote here because you want PHP to expand $table */
	/* Escape double quotes so they are passed to the shell because you do not wnat the shell to choke on spaces */
	$projectname = str_replace(" ", "-", strtolower(trim($form["projectname"])));
	$command_with_parameters = "/var/www/project-create.sh \"${projectname}\"";
	$output = $return = "";

	/* double quote here because you want PHP to expand $command_with_parameters, a string */
	$exec = exec("${command_with_parameters}", $output, $return);
	echo "Exec:<br />";
	print_r( $exec );
	echo "<br />----------------<br />";
	echo "Output:<br />";
	print_r( $output );
	echo "<br />----------------<br />";
	echo "Return:<br />";
	print_r( $return );
	
	
	//chdir($old_path);
	
	//echo json_encode(array('message'=>__("next part You've successfully create a new project named: ").$form["projectname"]));
    die();
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


/*
 * Only get posts or pages that have categories that aren't uncategorized
 */
add_action('pre_get_posts', 'get_posts_and_pages');
function get_posts_and_pages( $query ) {
	
	if (!is_admin() && $query->is_main_query()) {
		//Get all categories created
		$term_ids = array_map(function($e) {
			return is_object($e) ? $e->term_id : $e['term_id'];
		}, get_categories());
		//Remove uncategorized
		$key = array_search(1, $term_ids);
		unset($term_ids[$key]);
		$query->set('post_type',array('post','page'));
		$query->set( 'category__in', $term_ids );
	}
	return $query;
}
