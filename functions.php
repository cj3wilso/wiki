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


add_action('wp_ajax_create_project', 'create_project');
function create_project(){
	// First check the nonce, if it fails the function will break
    //check_ajax_referer( 'ajax-login-nonce', 'security' );

    parse_str($_POST['form'], $form);
	
	//Format Project Name for server
	$projectname = str_replace(" ", "-", strtolower(trim($form["projectname"])));
	
	//Exit if user tries to create a project that already exists
	$currentprojects = explode(',', $form["currentprojects"]);	
	if (in_array($projectname, $currentprojects)){
		echo "<p>This project name already exists! Please create a new one</p>";
		die();
	}
	
	//Create an array to loop through different site stages
	$stages = array();
	$stages[] = "main";
	if($form["staging"]=="on"){
		$stages[] = "staging";
	}
	
	$gitremote = $html_url = "";
	foreach ($stages as $stage) {
		if($stage=="main"){
			$projectdir = $projecturl = $projectname;
			$siteurl = "http://".$projectname.".christinewilson.ca";
		}else{
			$projectdir = $projectname."_".$stage;
			$projecturl = $projectname."-".$stage;
			$siteurl = "http://".$projecturl.".christinewilson.ca";	
		}
		$html_url .= "<li><a href='$siteurl' target='_blank'>$siteurl</a></li>";
		$gitremote .= "<pre>git remote add deploy ssh://christine@35.192.41.230/var/git/".$projectdir.".git/</pre><br>";
		
		//Is this a WordPress site?
		if($form["wordpress"]=="on"){
			create_wordpress_directory($projectdir);
			/*
			* CREATING GIT PROJECT 
			*/
			create_git_project($projectdir,"wordpress-create");
			/*
			* CREATING WORDPRESS DATABASE 
			*/
			create_database($projectdir,"database-wordpress-create");
		}else{
			/*
			* CREATING GIT PROJECT 
			*/
			create_git_project($projectdir,"project-create");
		}
		/*
		* CREATING SUBDOMAIN 
		*/
		create_subdomain($projectdir,$projecturl,$stage);
		if($form["wordpress"]!="on"){
			$command_with_parameters = "echo '<!DOCTYPE html>
			<html>
			<body>
			<h1>Project \"${projectdir}\" is set up</h1>
			<p>Move your Git files to put real site up ;)</p>
			</body>
			</html>' >> /var/www/\"${projectdir}\"/public_html/index.html;";
			$command_with_parameters .= "sudo chmod 644 -R /var/www/\"${projectdir}\"/public_html/index.html";
			$output = $return = "";
			$exec = exec("${command_with_parameters}", $output, $return);
			display_errors($exec, $output, $return, 'Create Git Project');
		}
		sleep(0.5);
	}
	
	$headers = 'From: Wiki <'.get_option('admin_email').'>' . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	$title = "Instructions to complete set up for project: ".$form["projectname"];
	$body = "<ol>
		<li>You're new website URL(s) are:
			<ul>".
				$html_url
			."</ul>
		</li>
		<li>Add new Git remote to your Bitbucket repo (create a bitbucket repo if not created already)
			<ul>
				<li>Open terminal and paste this:<br>".
				$gitremote
				."</li>
				<li>Either deploy current files, or add a test file to see that it moves to server</li>
			</ul>
		</li>
		</ol>";
	$email_message = $title."<br>".$body;
	
	echo "<p>You've successfully create a new project named: ".$form["projectname"]."</p>";
	echo "<h3>".$title."</h3>";
	echo $body;
	
	wp_mail( get_option('admin_email'), $title, $email_message, $headers );
	
    die();
}

function create_wordpress_directory($projectdir){
	$site_path = '/var/www/'.$projectdir.'/public_html';
	$project_full_path = '/var/www/'.$projectdir;
	
	//Create project site base so can move WordPress files over
	if (!file_exists($site_path)) {
		mkdir($site_path, 0775, true);
	}
	/* Escape double quotes so they are passed to the shell because you do not want the shell to choke on spaces */
	$command_with_parameters = "cp -a /var/www/default/public_html/. \"${site_path}\"";
	$output = $return = "";

	/* double quote here because you want PHP to expand $command_with_parameters, a string */
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Move WordPress files from default site');
	
	//Make folders proper permissions
	$output = $return = "";
	$exec = exec ("find \"${project_full_path}\" -type d -exec chmod 0775 {} +", $output, $return);
	display_errors($exec, $output, $return, 'Folders with permissions');
}

function display_errors($exec, $output, $return, $function_name, $development_mode = false){
	if($return || $development_mode==true){
		echo "<br /><br />";
		echo $function_name."<br /><br />";
		echo "Execution stopped at:<br />";
		print_r( $exec );
		echo "<br />----------------<br />";
		echo "Output:<br />";
		print_r( $output );
		echo "<br />----------------<br />";
		echo "Return:<br />";
		print_r( $return );
		//If this isn't for development then stop script here
		if($development_mode==false){
			die();
		}
	}
}

function create_git_project($projectdir,$shfile){
	/* Escape double quotes so they are passed to the shell because you do not want the shell to choke on spaces */
	$command_with_parameters = "/var/www/\"${shfile}\".sh \"${projectdir}\"";
	$output = $return = "";

	/* double quote here because you want PHP to expand $command_with_parameters, a string */
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Create Git Project');
}

function create_database($projectdir,$shfile){
	$command_with_parameters = "/var/www/\"${shfile}\".sh \"${projectdir}\"";
	$output = $return = "";
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Create WordPress Database');
}

function create_subdomain($projectdir,$projecturl,$stage){
	if($stage=="main"){
		$command_with_parameters = "/var/www/site-add.sh \"${projectdir}\" \"${projecturl}\"";
	}else{
		$command_with_parameters = "/var/www/site-add-password.sh \"${projectdir}\" \"${projecturl}\"";
	}
	$output = $return = "";
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Create Subdomain');
}

add_action('wp_ajax_delete_project', 'delete_project');
function delete_project(){
	// First check the nonce, if it fails the function will break
    //check_ajax_referer( 'ajax-login-nonce', 'security' );

    parse_str($_POST['form'], $form);
	
	$projects = explode(',', $form["projectname"]);
	foreach ($projects as $project) {
		$currentprojects = explode(',', $form["currentprojects"]);
		foreach ($currentprojects as $checkproject) {
			//Checking for underscore with project name which lets us know there is a staging site
			//Or that it matches completely
			if (strpos($checkproject, $project."_") !== false || $checkproject == $project) {
				$projectname = str_replace(" ", "-", strtolower(trim($project)));
				if (strpos($checkproject, "_") !== false) {
					$stage = substr($checkproject, strrpos($checkproject, '_' )+1); 
					$projecturl = $projectname."-".$stage;
					$projectdir = $projectname."_".$stage;
				}else{
					$projectdir = $projecturl = $projectname;
				}
				
				/*
				* REMOVE PROJECT 
				*/
				$command_with_parameters = "/var/www/project-delete.sh \"${projectdir}\"";
				$output = $return = "";
				$exec = exec("${command_with_parameters}", $output, $return);
				display_errors($exec, $output, $return, 'Project Delete');
				
				/*
				* REMOVE SUBDOMAIN 
				*/
				$command_with_parameters = "/var/www/site-remove.sh \"${projecturl}\"";
				$output = $return = "";
				$exec = exec("${command_with_parameters}", $output, $return);
				display_errors($exec, $output, $return, 'Remove Subdomain');
				
				/*
				* REMOVE DATABASE IF WORDPRESS FOUND 
				*/
				$dir = "/var/www/$projectdir/public_html/wp-content";
				//if (file_exists($dir)) {
					$command_with_parameters = "/var/www/database-delete.sh \"${projectdir}\"";
					$output = $return = "";
					$exec = exec("${command_with_parameters}", $output, $return);
					display_errors($exec, $output, $return, 'Remove Database');     
				//}
			}
			sleep(0.5);
		}
	}
	echo json_encode(array('message'=>__("You've successfully deleted project(s): ").$form["projectname"]));
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
