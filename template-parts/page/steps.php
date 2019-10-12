<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.2
 */

?>

<?php
	$add_steps = get_field('add_steps',get_the_ID());
	$steps = get_field('add_step',get_the_ID());
	if($add_steps){
		?>
		<div class="accordion" id="accordionExample">
		<?php
		$step_number = 1;
		foreach ($steps as $step) {
			?>
			  <div class="card">
				<div class="card-header" id="heading<?php echo $step_number; ?>">
				  <h2 class="mb-0">
					<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?php echo $step_number; ?>" aria-expanded="true" aria-controls="collapse<?php echo $step_number; ?>">
					  <h2><?php echo $step_number; ?>. 
					  <?php 
						echo $step['find_post']->post_title;
					  ?>
					  </h2>
					</button>
				  </h2>
				</div>

				<div id="collapse<?php echo $step_number; ?>" class="collapse" aria-labelledby="heading<?php echo $step_number; ?>" data-parent="#accordionExample">
				  <div class="card-body">
					<?php 
					echo $step['find_post']->post_content;
					?>
				  </div>
				</div>
			  </div>
			<?php
			$step_number++;
		}
?>
	</div>
<?php
	}
?>