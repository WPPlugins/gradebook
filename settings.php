<?php
if ( !current_user_can( 'manage_grades' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
 	if ( isset($_POST['updateLeague']) && !isset($_POST['deleteit']) ) {
		check_admin_referer('leaguemanager_manage-league-options');
		
		$message = $gradebook->editLeague( $_POST['league_title'], $_POST['match_calendar'], $_POST['type'], $_POST['league_id'] );
		echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	}
	
	if ( isset( $_GET['edit'] ) ) {
		$league_id = $_GET['edit'];
		$league = $gradebook->getLeagues( $league_id );
		$form_title = __( 'Student Preferences', 'gradebook' );
		$league_title = $league['title'];
		
		$league_preferences = $gradebook->getLeaguePreferences( $league_id );
	}
	
	$match_calendar = array( 1 => __('Show All', 'gradebook'), 2 => __('Only own grades', 'gradebook') );
	$league_types = array( 1 => __('Ball Game', 'gradebook'), 2 => __('Other', 'gradebook') );
		
	if ( 1 == $league_preferences->show_logo && !wp_mkdir_p( $gradebook->getImagePath() ) )
		echo "<div class='error'><p>".sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $gradebook->getImagePath() )."</p></div>";
	?>	
	<form action="" method="post">
		<?php wp_nonce_field( 'leaguemanager_manage-league-options' ) ?>
		
		<div class="wrap">
			<p class="leaguemanager_breadcrumb"><a href="edit.php?page=gradebook/manage-students.php"><?php _e( 'Gradebook', 'gradebook' ) ?></a> &raquo; <a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php echo $form_title ?></p>
			
			<h2><?php echo $form_title ?></h2>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="league_title"><?php _e( 'Name', 'gradebook' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="<?php echo $league_title ?>" size="30" /></td>
				</tr>
			
				<tr valign="top">
					<th scope="row"><label for="match_calendar"><?php _e( 'Grades Plan', 'gradebook' ) ?></label></th>
					<td>
						<select size="1" name="match_calendar" id="match_calendar">
							<?php foreach ( $match_calendar AS $id => $title ) : ?>
							<option value="<?php echo $id ?>"<?php if ( $id == $league_preferences->match_calendar ) echo ' selected="selected"' ?>><?php echo $title ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			
				
			</table>
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<p class="submit"><input type="submit" name="updateLeague" value="<?php _e( 'Save Preferences', 'gradebook' ) ?> &raquo;" class="button" /></p>
		</div>
	</form>
<?php endif; ?>
