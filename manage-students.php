<?php
if ( isset($_POST['addLeague']) && !isset($_POST['deleteit']) ) {
	check_admin_referer('leaguemanager_add-league');
	$message = $gradebook->addLeague( $_POST['league_title'] );
	echo '<div id="message" class="updated fade"><p><strong>'.$message.'</strong></p></div>';
} elseif ( isset($_GET['deactivate_league']) ) {
	$gradebook->deactivateLeague( $_GET['deactivate_league'] );
} elseif ( isset( $_GET['activate_league'] ) ) {
	$gradebook->activateLeague( $_GET['activate_league'] );
} elseif ( isset($_POST['deleteit']) && isset($_POST['delete']) ) {
	check_admin_referer('leaguemanager_delete-league');
	foreach ( $_POST['delete'] AS $league_id )
		$gradebook->delLeague( $league_id );
}
?>
<div class="wrap" style="margin-bottom: 1em;">
	<h2><?php _e( 'Gradebook', 'gradebook' ) ?></h2>
	
	<form id="leagues-filter" method="post" action="">
	<?php wp_nonce_field( 'leaguemanager_delete-league' ) ?>
	
	<div class="tablenav" style="margin-bottom: 0.1em;"><input type="submit" name="deleteit" value="<?php _e( 'Delete','gradebook' ) ?>" class="button-secondary" /></div>
	
	<table class="widefat" summary="" title="GradeBook">
		<thead>
		<tr>
                        <th scope="col" class="check-column"><input type="checkbox" onclick="Leaguemanager.checkAll(document.getElementById('leagues-filter'));" /></th>
			<th scope="col" class="num">ID</th>
			<th scope="col"><?php _e( 'Student', 'gradebook' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Items', 'gradebook' ) ?></th>
			<th scope="col" class="num"><?php _e( 'Grades', 'gradebook' ) ?></th>
			<th scope="col"><?php _e( 'Status', 'gradebook' ) ?></th>
			<th scope="col"><?php _e( 'Action', 'gradebook' ) ?></th>
		</tr>
		<tbody id="the-list">
			<?php if ( $leagues = $gradebook->getLeagues() ) : ?>
			<?php foreach ( $leagues AS $l_id => $league ) : ?>
			<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column"><input type="checkbox" value="<?php echo $l_id ?>" name="delete[<?php echo $l_id ?>]" /></th>
				<td class="num"><?php echo $l_id ?></td>
				<td><a href="edit.php?page=gradebook/show-student.php&amp;id=<?php echo $l_id ?>"><?php echo $league['title'] ?></a></td>
				<td class="num"><?php echo $gradebook->getNumTeams( $l_id ) ?></td>
				<td class="num"><?php echo $gradebook->getNumMatches( $l_id ) ?></td>
				<td><?php $gradebook->toggleLeagueStatusText( $l_id ) ?></td>
				<td><?php $gradebook->toggleLeagueStatusAction( $l_id ) ?></td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	</form>

	<!-- Add New League -->
	<form action="" method="post" style="margin-top: 3em;">
		<?php wp_nonce_field( 'leaguemanager_add-league' ) ?>
		<h3><?php _e( 'Add Student', 'gradebook' ) ?></h3>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="league_title"><?php _e( 'Student', 'gradebook' ) ?></label></th><td><input type="text" name="league_title" id="league_title" value="" size="30" style="margin-bottom: 1em;" /></td>
		</tr>
		</table>
		<input type="hidden" name="league_id" value="" />
		<p class="submit"><input type="submit" name="addLeague" value="<?php _e( 'Add Student', 'gradebook' ) ?> &raquo;" class="button" /></p>
	</form>
</div>
