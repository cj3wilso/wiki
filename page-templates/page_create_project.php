<?php
/*

Template Name: Create project

*/
?>
<?php
get_header();

/*
* Important 
* This part finds all current projects so we can delete them
* And also not try to recreate if submitted as a new project
*/
if($_SERVER['HTTP_HOST'] == "wiki.christinewilson.ca") {
	$dir = "/var/env";
}else{
	$dir = "D:\sites\wiki\wp-content";
}
$projects = scandir($dir);
$projects_for_delete = $projects;
foreach($projects as $k=>$project) { 
    if($project == '.' || $project == '..'){
		unset($projects[$k]);
		unset($projects_for_delete[$k]);
	}
	if(strpos($project, "_") !== false){
		unset($projects[$k]);
	}
}
?>

<div class="wrap">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/page/content', 'page' );

				?>
				<h2>Create a New Project</h2>
				<form class="create-project" method="post">
					<input type="hidden" name="currentprojects" value="<?php echo implode(",",$projects); ?>">
					<div class="row">
						<div class="col-lg-6 form-group">
							<label for="projectname">Name of Project (Name of Git project) *</label>
							<input id="projectname" name="projectname" class="form-control" type="text" required="required"
							pattern="^[a-zA-Z\s]+$"
							data-bv-regexp-message="The project name can consist of alphabetical characters and spaces only">
						</div>
						<div class="col-lg-6 form-group">
							<label for="domain">Domain (optional)</label>
							<input id="domain" name="domain" class="form-control" type="text"
							pattern="^[a-zA-Z0-9-]+\.?[a-zA-Z0-9-]+\.[a-zA-Z]{2,3}$"
							data-bv-regexp-message="Domain name without www or http(s)">
						</div>
					</div>
					<h3>Options</h3>
					<div class="row form-group">
						<div class="col-lg-3 ">
							<label for="wordpress">WordPress</label>
						</div>
						<div class="col-lg-9">
							<input id="wordpress" name="wordpress" type="checkbox" data-toggle="toggle" data-on="Enabled" data-off="Disabled">
						</div>
					</div>
					<div class="row form-group">
						<div class="col-lg-3 ">
							<label for="staging">Include Staging</label>
						</div>
						<div class="col-lg-9">
							<input id="staging" name="staging" type="checkbox" data-toggle="toggle" data-on="Enabled" data-off="Disabled">
						</div>
					</div>
					<div class="row form-group">
						<div class="col-lg-3 ">
							<label for="staging">Language</label>
						</div>
						<div class="col-lg-9">
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="language" id="php" value="php" checked>
							  <label class="form-check-label" for="php">PHP</label>
							</div>
							<div class="form-check form-check-inline">
							  <input class="form-check-input" type="radio" name="language" id="python" value="python">
							  <label class="form-check-label" for="python">Python</label>
							</div>
						</div>
					</div>
					<!--
					<div class="row">
						<div class="col-lg-4 form-group">
							<label for="pp-identify">I identify as: *</label>
							<select id="pp-identify" name="identify" class="form-control" required="required">
								<option value=""></option>
								<option value="Male">Male</option>
								<option value="Female">Female</option>
								<option value="Prefer not to say">Prefer not to say</option>
							</select>
						</div>
						<div class="col-lg-4 form-group">
							<label for="pp-age">Age range *</label>
							<select id="pp-age" name="age" class="form-control" required="required">
								<option value=""></option>
								<option value="11">11 &amp; Under</option>
								<option value="12-17">12-17</option>
								<option value="18-24">18-24</option>
								<option value="25-39">25-39</option>
								<option value="40-54">40-54</option>
								<option value="55+">55+</option>
								<option value="55-74">55-74</option>
							</select>
						</div>
						<div class="col-lg-4 form-group">
							<label for="pp-student">Are you a high school student? *</label>
							<select id="pp-student" name="student" class="form-control" required="required">
								<option value=""></option>
								<option value="Yes">Yes</option>
								<option value="No">No</option>
							</select>
						</div>
					</div>
					-->
					<div class="row">
						<div class="col-lg-12">
							<div class="status"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12 form-group">
							<button type="submit">Create</button>
						</div>
					</div>
				</form>
				
				
				<h2>Current Projects</h2>
				<form class="delete-project" method="post">
					<input type="hidden" name="currentprojects" value="<?php echo implode(",",$projects_for_delete); ?>">
					<div class="row">
						<div class="col-lg-12 form-group">
								<?php 
								foreach ($projects as $project) {
									$projectname = ucwords(str_replace("-", " ", $project));
									?>
									<div class="form-check">
										<?php 
										$siteurl = "http://".$project.".christinewilson.ca";
										if($project!="wiki"){	
										?>
										<input type="checkbox" class="form-check-input" name="projectname" id="<?php echo $project; ?>" value="<?php echo $project; ?>" required="required">
										<?php 
										}
										?>
										<label class="form-check-label" for="<?php echo $project; ?>">
										<?php
										echo $projectname ." <a href='$siteurl' target='_blank'>$siteurl</a>"; 
										?>
										</label>
									</div>
									<?php
								}
								?>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12">
							<div class="status"></div>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-12 form-group">
							<button type="submit">Delete</button>
						</div>
					</div>
				</form>
				
				<?php
			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->
<?php get_footer(); ?>