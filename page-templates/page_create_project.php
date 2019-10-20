<?php
/*

Template Name: Create project

*/
?>
<?php
get_header();
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
					<div class="row">
						<div class="col-lg-12 form-group">
							<label for="projectname">Name of Project *</label>
							<input id="projectname" name="projectname" class="form-control" type="text" required="required"
							pattern="^[a-zA-Z\s]+$"
							data-bv-regexp-message="The project name can consist of alphabetical characters and spaces only">
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
					<div class="row">
						<div class="col-lg-12 form-group">
								<?php 
								if($_SERVER['HTTP_HOST'] == "wiki.christinewilson.ca") {
									$dir = "/var/env";
								}else{
									$dir = "D:\sites\wiki\wp-content";
								}
								$projects = scandir($dir);
								foreach ($projects as $project) {
									if($project=="." || $project==".."){
										continue;
									}
									$projectname = ucwords(str_replace("-", " ", $project));
									?>
									<div class="form-check">
										<?php 
										if($project!="wiki"){
										?>
										<input type="checkbox" class="form-check-input" name="projectname" id="<?php echo $project; ?>" value="<?php echo $project; ?>" required="required">
										<label class="form-check-label" for="<?php echo $project; ?>">
										<?php 
										}
										echo $projectname; 
										if($project!="wiki"){
										?>
										</label>
										<?php 
										}
										?>
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
				
				
print_r($a);

			endwhile; // End of the loop.
			?>

		</main><!-- #main -->
	</div><!-- #primary -->
</div><!-- .wrap -->
<?php get_footer(); ?>