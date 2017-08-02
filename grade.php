<?php
if ( !current_user_can( 'manage_students' ) ) : 
	echo '<p style="text-align: center;">'.__("You do not have sufficient permissions to access this page.").'</p>';
	
else :
	if ( isset( $_GET['edit'] ) ) {
		$form_title = __( 'Edit Grade', 'gradebook' );

		$match = $gradebook->getMatch( $_GET['edit'] );

		if ( $match ) {
			$league_id = $match->league_id;
			$match_day = $match->day;
			$match_month = $match->month;
			$match_year = $match->year;
			$begin_hour = $match->hour;
			$begin_minutes = $match->minutes;
			$location = $match->location;
			$home_team = $match->home_team;
			$away_team = $match->away_team;
			$match_id = $match->id;
	
			$league = $gradebook->getLeagues( $league_id );
			$league_title = $league['title'];
			
			$max_matches = 1;
		}
	} else {
		$form_title = __( 'Add Grade', 'gradebook' );

		$league_id = $_GET['league_id'];
		$league = $gradebook->getLeagues( $league_id );
		$league_title = $league['title'];
		$match_day = ''; $match_month = ''; $match_year = date("Y"); $home_team = ''; $away_team = '';
		$begin_hour = ''; $begin_minutes = ''; $location = ''; $match_id = ''; $max_matches = 10;
	}
	?>
	
	<div class="wrap">
	<p class="leaguemanager_breadcrumb"><a href="edit.php?page=gradebook/manage-students.php"><?php _e( 'Gradebook', 'gradebook' ) ?></a> &raquo; <a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $league_id ?>"><?php echo $league_title ?></a> &raquo; <?php echo $form_title ?></p>
		<h2><?php echo $form_title ?></h2>
		
		<form class="leaguemanager" action="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $league_id?>" method="post">
			<?php wp_nonce_field( 'leaguemanager_manage-matches' ) ?>
			
			<label for="date" class="date"><?php _e('Date', 'gradebook') ?>:</label>
			<select size="1" name="match_day" class="date">
			<?php for ( $day = 1; $day <= 31; $day++ ) : ?>
				<option value="<?php echo $day ?>"<?php if ( $day == $match_day ) echo ' selected="selected"' ?>><?php echo $day ?></option>
			<?php endfor; ?>
			</select>
			<select size="1" name="match_month" class="date">
			<?php foreach ( $gradebook->months AS $key => $month ) : ?>
				<option value="<?php echo $key ?>"<?php if ( $key == $match_month ) echo ' selected="selected"' ?>><?php echo $month ?></option>
			<?php endforeach; ?>
			</select>
			<select size="1" name="match_year" class="date">
			<?php for ( $year = date("Y"); $year <= date("Y")+40; $year++ ) : ?>
				<option value="<?php echo $year ?>"<?php if ( $year == $match_year ) echo ' selected="selected"' ?>><?php echo $year ?></option>
			<?php endfor; ?>
			</select>
			<br />
			
			<p class="match_info"><?php _e( 'Note: Previously,in the "Add Items" section, add the Assignment generic Type (I.e:exam,exercices...).To grade and assignment, add the Assignment Description (I.e:Lessons 1,2 & 3","Cervantes bio"...).', 'gradebook' ) ?></p>
			<?php $teams = $gradebook->getTeams( "league_id = '".$league_id."'" ); ?>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col"><?php _e( 'Assignment Type', 'gradebook' ) ?></th>
						<th scope="col"><?php _e( 'Assignment Description', 'gradebook' ) ?></th>
						<th scope="col"><?php _e( 'Observations','gradebook' ) ?></th>
						<th scope="col"><?php _e( 'Grade','gradebook' ) ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
				<?php for ( $i = 1; $i <= $max_matches; $i++ ) : $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class ?>">
					<td>
						<select size="1" name="home_team[<?php echo $i ?>]" id="home_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<option value="<?php echo $team->id ?>"<?php if ( $team->id == $home_team ) echo ' selected="selected"' ?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td>
						<select size="1" id="away_team[<?php echo $i ?>]" name="away_team[<?php echo $i ?>]">
						<?php foreach ( $teams AS $team ) : ?>
							<option value="<?php echo $team->id ?>"<?php if ( $team->id == $away_team ) echo ' selected="selected"' ?>><?php echo $team->title ?></option>
						<?php endforeach; ?>
						</select>
					</td>
					<td><input type="text" name="location[<?php echo $i ?>]" id="location[<?php echo $i ?>]" size="20" value="<?php echo $location ?>" size="30" /></td>
					<td>
						<select size="1" name="begin_hour[<?php echo $i ?>]">
						<?php for ( $hour = 1; $hour <= 10; $hour++ ) : ?>
							<option value="<?php echo $hour ?>"<?php if ( $hour == $begin_hour ) echo ' selected="selected"' ?>><?php echo str_pad($hour, 1, 0, STR_PAD_LEFT) ?></option>
						<?php endfor; ?>
						</select>
						<select size="1" name="begin_minutes[<?php echo $i ?>]">
						<?php for ( $minute = 0; $minute <= 10; $minute++ ) : ?>
							<?php if ( 0 == $minute % 1 && 10 != $minute ) : ?>
							<option value="<?php echo $minute ?>"<?php if ( $minute == $begin_minutes ) echo ' selected="selected"' ?>><?php echo str_pad($minute, 1, 0, STR_PAD_LEFT) ?></option>
							<?php endif; ?>
						<?php endfor; ?>
						</select>
					</td>
				</tr>
				<input type="hidden" name="match[<?php echo $i ?>]" value="<?php echo $i ?>" />
				<?php endfor; ?>
				</tbody>
			</table>
			
			<input type="hidden" name="match_id" value="<?php echo $match_id ?>" />
			<input type="hidden" name="league_id" value="<?php echo $league_id ?>" />
			<input type="hidden" name="updateLeague" value="match" />
			
			<p class="submit"><input type="submit" value="<?php echo $form_title ?> &raquo;" class="button" /></p>
		</form>
	</div>
<?php endif; ?>