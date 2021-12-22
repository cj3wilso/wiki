<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

/*
 * Create a .htpasswd file under /etc/apache2 with user:staging and pwd:website
 * https://hostingcanada.org/htpasswd-generator/
 * Move file to apache2 folder:
 * mv /var/www/scripts/.htpasswd /etc/apache2
 *
 * PYTHON
 * Check if you have Python installed: python3 --version
 * If not install: https://docs.python-guide.org/starting/install3/linux/
 * Allow Apache to run scripts
 * sudo a2enmod mpm_prefork cgi
 * Disable multi-threading processes
 * sudo a2dismod mpm_event
 * Check the python path at top of file to see if server has it
 * If errors check the error log: tail /var/log/apache2/error.log
 * Convert line returns to unix with this: dos2unix path_to_file/file.ext
 *
 * WORDPRESS FILES
 * You'll need a folder on the server to hold the default WordPress files (/var/www/html/wordpressfiles)
 * Add all your default plugins here: ACF Pro, Duplicator Pro, Limit Login Attempts Reloaded, Post Types Order, WooCommerce, WP Mail SMTP, WP Migrate DB, Yoast Duplicate Post, Yoast SEO, Yoast SEO WooCommerce
 * Right now I don't have any good parent themes but do have Kalium and Shopkeeper 
 *
 * SCRIPT FILES
 * Bash scripts are kept in /var/www/scripts folder
 * Update var/www to 777, original is 755 (chmod 777 /var/www) and move files, then change back (chmod 755 /var/www)
 * Create these directories in /var: cd /var, mkdir -m 775 git, mkdir -m 775 env
 * Find your bash project and open up project-create.sh
 * At the top of the file you'll find all the locations you need to move files around
 * To create a Git Project you need to install docker-compose
 * Follow these instructions for Linux: https://docs.docker.com/compose/install/
 * Return 126 means “command not executable" for exec, try changing the permissions of the file to 755 (can't write but can read and execute). 
 * Next you can echo a string in the file and exit early with "exit 1" to test where going wrong.
 * echo "$USER" to see linux user and $GROUPS to see linux groups of current user in bash
 * <?php echo exec('whoami'); ?> to see linux user in PHP
 * Add to the end of any commands not working to see errors written: 2>&1
 *
 * USERS
 * PHP and shell scripts run on www-data user. You need to edit this user to require no password on your script files only (to keep rest of web files secure)
 * sudo visudo
 * www-data ALL=(ALL) NOPASSWD: ALL
 * www-data ALL=(ALL) NOPASSWD: /var/www/html/newsite1, /var/www/html/newsite2
 * https://www.tecmint.com/run-sudo-command-without-password-linux/
 * Right now requiring no password for www-data which is not secure.. you'll need to figure out and fix
 *
 * New user named as project name
 * See all users in system: less /etc/passwd
 * Delete user and user home: userdel -r usernamehere 
 * https://www.cyberciti.biz/faq/linux-remove-user-command/
 
 * 
 * THINGS TO DO 
 * Domain record lists ssl when it wasn't created (??)
 * Domain not listed in Current Projects list
 * Domain conf file not deleted when delete site
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
	
	//Language
	$language = $form["language"];
	
	//Format Project Name for server
	$projectname = str_replace(" ", "-", strtolower(trim($form["projectname"])));
	$orginal_projectname = $form["projectname"];
	
	//Set variables
	$staging="";
	if(isset($form["staging"])){
		$staging = $form["staging"];
	}
	$wordpress="";
	if(isset($form["wordpress"])){
		$wordpress = $form["wordpress"];
	}
	$domain="";
	if(isset($form["domain"])){
		$domain = $form["domain"];
	}
	
	//Exit if user tries to create a project that already exists
	$currentprojects = explode(',', $form["currentprojects"]);	
	if (in_array($projectname, $currentprojects)){
		echo "<p>This project name already exists! Please create a new one</p>";
		die();
	}
	
	//Create an array to loop through different site stages
	$stages = array();
	$stages[] = "main";
	if($staging=="on"){
		$stages[] = "staging";
	}
	
	$gitremote = $html_url = "";
	foreach ($stages as $stage) {
		$projectdir = $projecturl = $projectname;
		$branch_name = "production";
		if($stage!="main"){
			$projectdir = $projectname."_".$stage;
			$projecturl = $projectname."-".$stage;
			$branch_name = "staging";
		}
		$siteurl = "https://".$projecturl.".christinewilson.ca";
		if($domain!=""){
			if($stage=="main"){
				$siteurl = "https://".$domain;
			}else{
				$siteurl = "https://staging.".$domain;
			}
		}
		$html_url .= "<li><a href='$siteurl' target='_blank'>$siteurl</a></li>";
		$gitremote .= "<pre>git remote add server-$branch_name ssh://christine@35.184.97.246/var/git/".$projectdir.".git/</pre><br>";
		
		//Is this a WordPress site?
		if($wordpress=="on"){
			create_wordpress_directory($projectdir);
			/*
			* CREATING WORDPRESS DATABASE 
			*/
			create_database($projectdir,"database-wordpress-create",$domain);
			/*
			* CREATING GIT PROJECT 
			*/
			create_git_project($projectdir,"wordpress-create");
		}else{
			/*
			* CREATING GIT PROJECT 
			*/
			create_git_project($projectdir,"project-create");
		}
		/*
		* CREATING SUBDOMAIN 
		*/
		create_subdomain($projectdir,$projecturl,$stage,$language,$domain);
		if($wordpress!="on"){
			$site_path = "/var/www/html/".$projectdir;
			if($language=="python"){
				//Create index file
				$command_with_parameters = "echo '#!/usr/bin/python3.7
# Print necessary headers.
print(\"Content-Type: text/html\")
print()
print(\"<h1>Python project ${projectdir} is set up</h1>\")
print(\"<p>Move your Git files to put real site up ;)</p>\")' >> /var/www/html/\"${projectdir}\"/index.py;";
				$output = $return = "";
				$exec = exec("${command_with_parameters}", $output, $return);
				display_errors($exec, $output, $return, 'Create Git Project (create_project function)');
				//Make folders proper permissions
				$output = $return = "";
				$exec = exec ("find \"${site_path}\" -type d -exec chmod 0755 {} +", $output, $return);
				display_errors($exec, $output, $return, 'Folders with permissions');
				//Make files proper permissions
				$output = $return = "";
				$exec = exec ("find \"${site_path}\" -type f -exec chmod 0755 {} +", $output, $return);
				display_errors($exec, $output, $return, 'Files with permissions');
				//Make unix encoded		
				$output = $return = "";
				$exec = exec ("dos2unix \"${site_path}\"/index.py", $output, $return);
				display_errors($exec, $output, $return, 'Unix encoded');
			}else{
				//Create index file
				$command_with_parameters = "echo '<!DOCTYPE html>
				<html>
				<body>
				<h1>Project \"${projectdir}\" is set up</h1>
				<p>Move your Git files to put real site up ;)</p>
				</body>
				</html>' >> /var/www/html/\"${projectdir}\"/index.html;";
				$output = $return = "";
				$exec = exec("${command_with_parameters}", $output, $return);
				display_errors($exec, $output, $return, 'Create Git Project (create_project function)');
				//Make folders proper permissions
				$output = $return = "";
				$exec = exec ("find \"${site_path}\" -type d -exec chmod 0755 {} +", $output, $return);
				display_errors($exec, $output, $return, 'Folders with permissions');
				//Make files proper permissions
				$output = $return = "";
				$exec = exec ("find \"${site_path}\" -type f -exec chmod 0644 {} +", $output, $return);
				display_errors($exec, $output, $return, 'Files with permissions');
			}
		}
		add_project_user($projectdir);
		sleep(0.5);
	}
	
	$headers = 'From: Wiki <'.get_option('admin_email').'>' . "\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	$title = "Instructions to complete set up for project: ".$orginal_projectname;
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
				<li>Either deploy current files, or add a test file to see that it moves to the server</li>
			</ul>
		</li>
		</ol>";
	$email_message = $title."<br>".$body;
	
	echo "<p>You've successfully created a new project named: ".$orginal_projectname."</p>";
	echo "<h3>".$title."</h3>";
	echo $body;
	
	wp_mail( get_option('admin_email'), $title, $email_message, $headers );
	
    die();
}

function add_project_user($projectdir){
	//Add new user based on folder name
	$output = $return = "";
	$exec = exec ("sudo useradd -p $(openssl passwd -1 575757aA) \"${projectdir}\" -m -g www-data", $output, $return);
	display_errors($exec, $output, $return, 'New user added '.$projectdir);
	
	//Add user to user config
	$output = $return = "";
	$exec = exec ("sudo sh -c 'echo \"local_root=/var/www/html/\"${projectdir}\"\" >> /etc/vsftpd/user_config_dir/\"${projectdir}\"'", $output, $return);
	display_errors($exec, $output, $return, 'New user added to user config '.$projectdir);
	
	//Append file with another user
	$output = $return = "";
	$exec = exec ("sudo -- bash -c 'echo \"\"${projectdir}\"\" >> /etc/vsftpd.userlist'", $output, $return);
	display_errors($exec, $output, $return, 'New user appended to userlist '.$projectdir);
	
	//Change owner of site
	$output = $return = "";
	$exec = exec ("sudo chown \"${projectdir}\":www-data /var/www/html/\"${projectdir}\"", $output, $return);
	display_errors($exec, $output, $return, 'Changed owner of site '.$projectdir);
}

function create_wordpress_directory($projectdir){
	$site_path = '/var/www/html/'.$projectdir;
	
	//Create project site base so can move WordPress files over
	if (!file_exists($site_path)) {
		mkdir($site_path, 0775, true);
	}
	/* Escape double quotes so they are passed to the shell because you do not want the shell to choke on spaces */
	$command_with_parameters = "cp -a /var/www/html/wordpressfiles/. \"${site_path}\"";
	$output = $return = "";

	/* double quote here because you want PHP to expand $command_with_parameters, a string */
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Move WordPress files from default site');
	
	//Update WP Config with new database creds
	$output = $return = "";
	$exec = exec ("sed -i 's/copy/${projectdir}/g' /var/www/html/sweetiebee/wp-config.php", $output, $return);
	display_errors($exec, $output, $return, 'Update wp_config');
	
	//Make files proper permissions
	$output = $return = "";
	$exec = exec ("find \"${site_path}\" -type f -exec chmod 0664 {} +", $output, $return);
	display_errors($exec, $output, $return, 'Folders with permissions');
	//Make folders proper permissions
	$output = $return = "";
	$exec = exec ("find \"${site_path}\" -type d -exec chmod 0775 {} +", $output, $return);
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
	$command_with_parameters = "/var/www/scripts/\"${shfile}\".sh \"${projectdir}\"";
	$output = $return = "";

	/* double quote here because you want PHP to expand $command_with_parameters, a string */
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Create Git Project  (create_git_project function)');
	//print_r(array(exec('whoami')));
}

function create_database($projectdir,$shfile,$domain){
	$command_with_parameters = "/var/www/scripts/\"${shfile}\".sh \"${projectdir}\" \"${domain}\"";
	$output = $return = "";
	$exec = exec("${command_with_parameters}", $output, $return);
	display_errors($exec, $output, $return, 'Create WordPress Database');
}

function create_subdomain($projectdir,$projecturl,$stage,$language,$domain=null){
	if($language == "python"){
		if($stage=="main"){
			$command_with_parameters = "/var/www/scripts/site-add-python.sh \"${projectdir}\" \"${projecturl}\"";
		}else{
			$command_with_parameters = "/var/www/scripts/site-add-password-python.sh \"${projectdir}\" \"${projecturl}\"";
		}
	}else{
		if($stage=="main"){
			$command_with_parameters = "/var/www/scripts/site-add.sh \"${projectdir}\" \"${projecturl}\"";
		}else{
			$command_with_parameters = "/var/www/scripts/site-add-password.sh \"${projectdir}\" \"${projecturl}\"";
		}
	}
	if($domain!=""){
		$command_with_parameters .= " \"${domain}\"";
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
				$command_with_parameters = "/var/www/scripts/project-delete.sh \"${projectdir}\"";
				$output = $return = "";
				$exec = exec("${command_with_parameters}", $output, $return);
				display_errors($exec, $output, $return, 'Project Delete');
				
				/*
				* REMOVE SUBDOMAIN 
				*/
				$command_with_parameters = "/var/www/scripts/site-remove.sh \"${projecturl}\"";
				$output = $return = "";
				$exec = exec("${command_with_parameters}", $output, $return);
				display_errors($exec, $output, $return, 'Remove Subdomain');
				
				/*
				* REMOVE DATABASE IF WORDPRESS FOUND 
				*/
				$dir = "/var/www/html/$projectdir/wp-content";
				//if (file_exists($dir)) {
					$command_with_parameters = "/var/www/scripts/database-delete.sh \"${projectdir}\"";
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
