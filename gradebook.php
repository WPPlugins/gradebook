<?php

class WP_GradeBook
{
	/**
	 * supported image types
	 *
	 * @var array
	 */
	var $supported_image_types = array( "jpg", "jpeg", "png", "gif" );
	
	
	/**
	 * Array of months
	 *
	 * @param array
	 */
	var $months = array();

	
	/**
	 * Preferences of Student
	 *
	 * @param array
	 */
	var $preferences = array();
	
	
	/**
	 * error handling
	 *
	 * @param boolean
	 */
	var $error = false;
	
	
	/**
	 * error message
	 *
	 * @param string
	 */
	var $message = '';
	
	
	/**
	 * Initializes plugin
	 *
	 * @param none
	 * @return void
	 */
	function __construct()
	{
	 	global $wpdb;
	 	
		$wpdb->gradebook = $wpdb->prefix . 'gradebook_leagues';
		$wpdb->gradebook_teams = $wpdb->prefix . 'gradebook_teams';
		$wpdb->gradebook_matches = $wpdb->prefix . 'gradebook_matches';		

		$this->getMonths();
		return;
	}
	function WP_LeagueManager()
	{
		$this->__construct();
	}
	
	
	/**
	 * get months
	 *
	 * @param none
	 * @return void
	 */
	function getMonths()
	{
		$locale = get_locale();
		setlocale(LC_ALL, $locale);
		for ( $month = 1; $month <= 12; $month++ ) 
			$this->months[$month] = htmlentities( strftime( "%B", mktime( 0,0,0, $month, date("m"), date("Y") ) ) );
	}
	
	
	/**
	 * return error message
	 *
	 * @param none
	 */
	function getErrorMessage()
	{
		if ($this->error)
			return $this->message;
	}
	
	
	/**
	 * print formatted error message
	 *
	 * @param none
	 */
	function printErrorMessage()
	{
		echo "\n<div class='error'><p>".$this->getErrorMessage()."</p></div>";
	}
	
	
	/**
	 * gets supported file types
	 *
	 * @param none
	 * @return array
	 */
	function getSupportedImageTypes()
	{
		return $this->supported_image_types;
	}
	
	
	/**
	 * checks if image type is supported
	 *
	 * @param string $filename image file
	 * @return boolean
	 */
	function imageTypeIsSupported( $filename )
	{
		if ( in_array($this->getImageType($filename), $this->supported_image_types) )
			return true;
		else
			return false;
	}
	
	
	/**
	 * gets image type of supplied image
	 *
	 * @param string $filename image file
	 * @return string
	 */
	function getImageType( $filename )
	{
		$file_info = pathinfo($filename);
		return strtolower($file_info['extension']);
	}
	
	
	/**
	 * returns image directory
	 *
	 * @param string|false $file
	 * @return string
	 */
	function getImagePath( $file = false )
	{
		if ( $file )
			return WP_CONTENT_DIR.'/gradebook/'.$file;
		else
			return WP_CONTENT_DIR.'/gradebook';
	}
	
	
	/**
	 * returns url of image directory
	 *
	 * @param string|false $file image file
	 * @return string
	 */
	function getImageUrl( $file = false )
	{
		if ( $file )
			return WP_CONTENT_URL.'/gradebook/'.$file;
		else
			return WP_CONTENT_URL.'/gradebook';
	}
	
	
	/**
	 * get students from database
	 *
	 * @param int $league_id (default: false)
	 * @param string $search
	 * @return array
	 */
	function getLeagues( $league_id = false, $search = '' )
	{
		global $wpdb;
		
		$leagues = array();
		if ( $league_id ) {
			$leagues_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->gradebook} WHERE id = '".$league_id."' ORDER BY id ASC" );
			
			$leagues['title'] = $leagues_sql[0]->title;
			$this->preferences = $this->getLeaguePreferences( $league_id );
		} else {
			if ( $leagues_sql = $wpdb->get_results( "SELECT title, id FROM {$wpdb->gradebook} $search ORDER BY id ASC" ) ) {
				foreach( $leagues_sql AS $league ) {
					$leagues[$league->id]['title'] = $league->title;
				}
			}
		}
			
		return $leagues;
	}
	
	
	/**
	 * get student settings
	 * 
	 * @param int $league_id
	 * @return array
	 */
	function getLeaguePreferences( $league_id )
	{
		global $wpdb;
		
		$preferences = $wpdb->get_results( "SELECT `forwin`, `fordraw`, `forloss`, `match_calendar`, `type` FROM {$wpdb->gradebook} WHERE id = '".$league_id."'" );
		
		$preferences[0]->colors = maybe_unserialize($preferences[0]->colors);
		return $preferences[0];
	}
	
	
	/**
	 * gets student name
	 *
	 * @param int $league_id
	 * @return string
	 */
	function getLeagueTitle( $league_id )
	{
		global $wpdb;
		$league = $wpdb->get_results( "SELECT `title` FROM {$wpdb->gradebook} WHERE id = '".$league_id."'" );
		return ( $league[0]->title );
	}
	
	
	/**
	 * get all active students
	 *
	 * @param none
	 * @return array
	 */
	function getActiveLeagues()
	{
		return ( $this->getLeagues( false, 'WHERE active = 1' ) );
	}
	

	/**
	 * checks if student is active
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function leagueIsActive( $league_id )
	{
		global $wpdb;
		$league = $wpdb->get_results( "SELECT active FROM {$wpdb->gradebook} WHERE id = '".$league_id."'" );
		if ( 1 == $league[0]->active )
			return true;
		
		return false;
	}
	
	
	/**
	 * activates given student depending on status
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function activateLeague( $league_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->gradebook} SET active = '1' WHERE id = '".$league_id."'" );
		return true;
	}
	
	
	/**
	 * deactivate student
	 *
	 * @param int $league_id
	 * @return boolean
	 */
	function deactivateLeague( $league_id )
	{
		global $wpdb;
		$wpdb->query( "UPDATE {$wpdb->gradebook} SET active = '0' WHERE id = '".$league_id."'" );	
		return true;
	}
	
	
	/**
	 * toggle student status text
	 *
	 * @param int $league_id
	 * @return string
	 */
	function toggleLeagueStatusText( $league_id )
	{
		if ( $this->leagueIsActive( $league_id ) )
			_e( 'Active', 'gradebook');
		else
			_e( 'Inactive', 'gradebook');
	}
	
	
	/**
	 * toogle student status action link
	 *
	 * @param int $league_id
	 * @return string
	 */
	function toggleLeagueStatusAction( $league_id )
	{
		if ( $this->leagueIsActive( $league_id ) )
			echo '<a href="edit.php?page=gradebook/manage-students.php&amp;deactivate_league='.$league_id.'">'.__( 'Deactivate', 'gradebook' ).'</a>';
		else
			echo '<a href="edit.php?page=gradebook/manage-students.php&amp;activate_league='.$league_id.'">'.__( 'Activate', 'gradebook' ).'</a>';
	}
	
	
	/**
	 * get items from database
	 *
	 * @param string $search search string for WHERE clause.
	 * @param string $output OBJECT | ARRAY
	 * @return array database results
	 */
	function getTeams( $search, $output = 'OBJECT' )
	{
		global $wpdb;
		
		$teams_sql = $wpdb->get_results( "SELECT `title`, `short_title`, `logo`, `home`, `league_id`, `id` FROM {$wpdb->gradebook_teams} WHERE $search ORDER BY id ASC" );
		
		if ( 'ARRAY' == $output ) {
			$teams = array();
			foreach ( $teams_sql AS $team ) {
				$teams[$team->id]['title'] = $team->title;
				$teams[$team->id]['short_title'] = $team->short_title;
				$teams[$team->id]['logo'] = $teams->logo;
				$teams[$team->id]['home'] = $team->home;
			}
			
			return $teams;
		}
		return $teams_sql;
	}
	
	
	/**
	 * get single item
	 *
	 * @param int $team_id
	 * @return object
	 */
	function getTeam( $team_id )
	{
		$teams = $this->getTeams( "`id` = {$team_id}" );
		return $teams[0];
	}
	
	
	/**
	 * gets number of items for specific student
	 *
	 * @param int $league_id
	 * @return int
	 */
	function getNumTeams( $league_id )
	{
		global $wpdb;
	
		$num_teams = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_teams} WHERE `league_id` = '".$league_id."'" );
		return $num_teams;
	}
	
	
	/**
	 * gets number of grades
	 *
	 * @param string $search
	 * @return int
	 */
	function getNumMatches( $league_id )
	{
		global $wpdb;
	
		$num_matches = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_matches} WHERE `league_id` = '".$league_id."'" );
		return $num_matches;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumWonMatches( $team_id )
	{
		global $wpdb;
		$num_win = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_matches} WHERE `winner_id` = '".$team_id."'" );
		return $num_win;
	}
	
	
	/**
	 *not applicable
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumDrawMatches( $team_id )
	{
		global $wpdb;
		$num_draw = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_matches} WHERE `winner_id` = -1 AND `loser_id` = -1 AND (`home_team` = '".$team_id."' OR `away_team` = '".$team_id."')" );
		return $num_draw;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumLostMatches( $team_id )
	{
		global $wpdb;
		$num_lost = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_matches} WHERE `loser_id` = '".$team_id."'" );
		return $num_lost;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $team_id
	 * @param int $league_id
	 * @param string $option
	 * @return int
	 */
	function calculatePoints( $team_id, $league_id, $option )
	{
		global $wpdb;
		
		$num_win = $this->getNumWonMatches( $team_id );
		$num_draw = $this->getNumDrawMatches( $team_id );
		$num_lost = $this->getNumLostMatches( $team_id );
		
		$points['plus'] = 0; $points['minus'] = 0;
		$points['plus'] = $num_win * $this->preferences->forwin + $num_draw * $this->preferences->fordraw + $num_lost * $league_settings->forloss;
		$points['minus'] = $num_draw * $this->preferences->fordraw + $num_lost * $this->preferences->forwin;
		return $points[$option];
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateApparatusPoints( $team_id, $option )
	{
		global $wpdb;
		
		$apparatus_home = $wpdb->get_results( "SELECT `home_apparatus_points`, `away_apparatus_points` FROM {$wpdb->gradebook_matches} WHERE `home_team` = '".$team_id."'" );
		$apparatus_away = $wpdb->get_results( "SELECT `home_apparatus_points`, `away_apparatus_points` FROM {$wpdb->gradebook_matches} WHERE `away_team` = '".$team_id."'" );
			
		$apparatus_points['plus'] = 0;
		$apparatus_points['minus'] = 0;
		if ( count($apparatus_home) > 0 )
		foreach ( $apparatus_home AS $home_apparatus ) {
			$apparatus_points['plus'] += $home_apparatus->home_apparatus_points;
			$apparatus_points['minus'] += $home_apparatus->away_apparatus_points;
		}
		
		if ( count($apparatus_away) > 0 )
		foreach ( $apparatus_away AS $away_apparatus ) {
			$apparatus_points['plus'] += $away_apparatus->away_apparatus_points;
			$apparatus_points['minus'] += $away_apparatus->home_apparatus_points;
		}
		
		return $apparatus_points[$option];
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $team_id
	 * @param string $option
	 * @return int
	 */
	function calculateGoals( $team_id, $option )
	{
		global $wpdb;
		
		$goals_home = $wpdb->get_results( "SELECT `home_points`, `away_points` FROM {$wpdb->gradebook_matches} WHERE `home_team` = '".$team_id."'" );
		$goals_away = $wpdb->get_results( "SELECT `home_points`, `away_points` FROM {$wpdb->gradebook_matches} WHERE `away_team` = '".$team_id."'" );
			
		$goals['plus'] = 0;
		$goals['minus'] = 0;
		if ( count($goals_home) > 0 ) {
			foreach ( $goals_home AS $home_goals ) {
				$goals['plus'] += $home_goals->home_points;
				$goals['minus'] += $home_goals->away_points;
			}
		}
		
		if ( count($goals_away) > 0 ) {
			foreach ( $goals_away AS $away_goals ) {
				$goals['plus'] += $away_goals->away_points;
				$goals['minus'] += $away_goals->home_points;
			}
		}
		
		return $goals[$option];
	}
	
	
	/**
	 * not applicable
	 *
	 * @param int $plus
	 * @param int $minus
	 * @return int
	 */
	function calculateDiff( $plus, $minus )
	{
		$diff = $plus - $minus;
		if ( $diff >= 0 )
			$diff = '+'.$diff;
		
		return $diff;
	}
	
	
	/**
	 * get number of grades for item
	 *
	 * @param int $team_id
	 * @return int
	 */
	function getNumDoneMatches( $team_id )
	{
		global $wpdb;
		
		$num_matches = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->gradebook_matches} WHERE (`home_team` = '".$team_id."' OR `away_team` = '".$team_id."') AND `home_points` IS NOT NULL AND `away_points` IS NOT NULL" );
		return $num_matches;
	}
	
	
	/**
	 * not applicable
	 *
	 * @param none
	 * @return boolean
	 */
	function isGymnasticsLeague( $league_id )
	{
		if ( 1 == $this->preferences->type )
			return true;
		else
			return false;
	}
	
	
	/**
	 * rank items
	 *
	 * @param array $teams
	 * @return array $teams ordered
	 */
	function rankTeams( $league_id )
	{
		global $wpdb;

		$teams = array();
		foreach ( $this->getTeams( "league_id = '".$league_id."'" ) AS $team ) {
			$p['plus'] = $this->calculatePoints( $team->id, $league_id, 'plus' );
			$p['minus'] = $this->calculatePoints( $team->id, $league_id, 'minus' );
			
			$ap['plus'] = $this->calculateApparatusPoints( $team->id, 'plus' );
			$ap['minus'] = $this->calculateApparatusPoints( $team->id, 'minus' );
			
			$match_points['plus'] = $this->calculateGoals( $team->id, 'plus' );
			$match_points['minus'] = $this->calculateGoals( $team->id, 'minus' );
			
			if ( $this->isGymnasticsLeague( $league_id ) )
				$d = $this->calculateDiff( $ap['plus'], $ap['minus'] );
			else
				$d = $this->calculateDiff( $match_points['plus'], $match_points['minus'] );
						
			$teams[] = array('id' => $team->id, 'home' => $team->home, 'title' => $team->title, 'short_title' => $team->short_title, 'logo' => $team->logo, 'points' => array('plus' => $p['plus'], 'minus' => $p['minus']), 'apparatus_points' => array('plus' => $ap['plus'], 'minus' => $ap['minus']), 'goals' => array('plus' => $match_points['plus'], 'minus' => $match_points['minus']), 'diff' => $d );
		}
		
		foreach ( $teams AS $key => $row ) {
			$points[$key] = $row['points']['plus'];
			$apparatus_points[$key] = $row['apparatus_points']['plus'];
			$diff[$key] = $row['diff'];
		}
		
		if ( count($teams) > 0 ) {
			if ( $this->isGymnasticsLeague($league_id) )
				array_multisort($points, SORT_DESC, $apparatus_points, SORT_DESC, $teams);
			else
				array_multisort($points, SORT_DESC, $diff, SORT_DESC, $teams);
		}
		
		return $teams;
	}
	
	
	/**
	 * gets grades from database
	 * 
	 * @param string $search
	 * @return array
	 */
	function getMatches( $search, $output = 'OBJECT' )
	{
	 	global $wpdb;
		
		$sql = "SELECT `home_team`, `away_team`, DATE_FORMAT(`date`, '%Y-%m-%d %H:%i') AS date, DATE_FORMAT(`date`, '%e') AS day, DATE_FORMAT(`date`, '%c') AS month, DATE_FORMAT(`date`, '%Y') AS year, DATE_FORMAT(`date`, '%H') AS `hour`, DATE_FORMAT(`date`, '%i') AS `minutes`, `location`, `league_id`, `home_apparatus_points`, `away_apparatus_points`, `home_points`, `away_points`, `winner_id`, `id` FROM {$wpdb->gradebook_matches} WHERE $search ORDER BY `date` ASC";
		return $wpdb->get_results( $sql, $output );
	}
	
	
	/**
	 * get single grade
	 *
	 * @param int $match_id
	 * @return object
	 */
	function getMatch( $match_id )
	{
		$matches = $this->getMatches( "`id` = {$match_id}" );
		return $matches[0];
	}
	
	
	/**
	 * add new Student
	 *
	 * @param string $title
	 * @return string
	 */
	function addLeague( $title )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "INSERT INTO {$wpdb->gradebook} (title) VALUES ('%s')", $title ) );
		return __('Student added', 'gradebook');
	}


	/**
	 * edit Student
	 *
	 * @param string $title
	 * @param int $forwin
	 * @param int $fordraw
	 * @param int $forloss
	 * @param int $match_calendar
	 * @param int $type
	 * @param int $show_logo
	 * @param int $league_id
	 * @return string
	 */
	function editLeague( $title, $match_calendar, $type, $league_id )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->gradebook} SET `title` = '%s', `match_calendar` = '%d', `type` = '%d' = '%d' WHERE `id` = '%d'", $title, $match_calendar, $type, $league_id ) );
		return __('Settings saved', 'gradebook');
	}


	/**
	 * delete Student 
	 *
	 * @param int $league_id
	 * @return void
	 */
	function delLeague( $league_id )
	{
		global $wpdb;
		
		foreach ( $this->getTeams( "league_id = '".$league_id."'" ) AS $team )
			$this->delTeam( $team->id );

		$wpdb->query( "DELETE FROM {$wpdb->gradebook} WHERE `id` = {$league_id}" );
	}

	
	/**
	 * add new item
	 *
	 * @param int $league_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @return string
	 */
	function addTeam( $league_id, $short_title, $title, $home )
	{
		global $wpdb;
			
		$sql = "INSERT INTO {$wpdb->gradebook_teams} (title, short_title, home, league_id) VALUES ('%s', '%s', '%d', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $title, $short_title, $home, $league_id ) );
		$team_id = $wpdb->insert_id;

		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo']);
		
		if ( $this->error ) $this->printErrorMessage();
			
		return __('Item added','gradebook');
	}


	/**
	 * edit item
	 *
	 * @param int $team_id
	 * @param string $short_title
	 * @param string $title
	 * @param int $home 1 | 0
	 * @param boolean $del_logo
	 * @param string $image_file
	 * @param boolean $overwrite_image
	 * @return string
	 */
	function editTeam( $team_id, $short_title, $title, $home, $del_logo = false, $image_file = '', $overwrite_image = false )
	{
		global $wpdb;
		
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->gradebook_teams} SET `title` = '%s', `short_title` = '%s', `home` = '%d' WHERE `id` = %d", $title, $short_title, $home, $team_id ) );
			
		// Delete Image if options is checked
		if ($del_logo || $overwrite_image) {
			$wpdb->query("UPDATE {$wpdb->gradebook_teams} SET `logo` = '' WHERE `id` = {$team_id}");
			$this->delLogo( $image_file );
		}
		
		if ( isset($_FILES['logo']) && $_FILES['logo']['name'] != '' )
			$this->uploadLogo($team_id, $_FILES['logo'], $overwrite_image);
		
		if ( $this->error ) $this->printErrorMessage();
			
		return __('Item updated','gradebook');
	}


	/**
	 * delete Item
	 *
	 * @param int $team_id
	 * @return void
	 */
	function delTeam( $team_id )
	{
		global $wpdb;
		
		$team = $this->getTeam( $team_id );
	
			
		$wpdb->query( "DELETE FROM {$wpdb->gradebook_matches} WHERE `home_team` = '".$team_id."' OR `away_team` = '".$team_id."'" );
		$wpdb->query( "DELETE FROM {$wpdb->gradebook_teams} WHERE `id` = '".$team_id."'" );
		return;
	}


	
	
	/**
	 * add Grade
	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param string $location
	 * @param int $league_id
	 * @return string
	 */
	function addMatch( $date, $home_team, $away_team, $location, $league_id )
	{
	 	global $wpdb;
		$sql = "INSERT INTO {$wpdb->gradebook_matches} (date, home_team, away_team, location, league_id) VALUES ('%s', '%d', '%d', '%s', '%d')";
		$wpdb->query( $wpdb->prepare ( $sql, $date, $home_team, $away_team, $location, $league_id ) );
	}


	/**
	 * edit Grade	 *
	 * @param string $date
	 * @param int $home_team
	 * @param int $away_team
	 * @param string $location
	 * @param int $league_id
	 * @param int $cid
	 * @return string
	 */
	function editMatch( $date, $home_team, $away_team, $location, $league_id, $match_id )
	{
	 	global $wpdb;
		$wpdb->query( $wpdb->prepare ( "UPDATE {$wpdb->gradebook_matches} SET `date` = '%s', `home_team` = '%d', `away_team` = '%d', `location` = '%s', `league_id` = '%d' WHERE `id` = %d", $date, $home_team, $away_team, $location, $league_id, $match_id ) );
		return __('Grade updated','gradebook');
	}


	/**
	 * delete Grade
	 *
	 * @param int $cid
	 * @return void
	 */
	function delMatch( $match_id )
	{
	  	global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->gradebook_matches} WHERE `id` = '".$match_id."'" );
		return;
	}


	/**
	 * update match results
	 *
	 * @param array $match_id
	 * @param array $home_apparatus_points
	 * @param array $away_apparatus_points
	 * @param array $home_points
	 * @param array $away_points
	 * @return string
	 */
	function updateResults( $matches, $home_apparatus_points, $away_apparatus_points, $home_points, $away_points, $home_team, $away_team )
	{
		global $wpdb;
		if ( null != $matches ) {
			foreach ( $matches AS $match_id ) {
				$home_points[$match_id] = ( '' == $home_points[$match_id] ) ? 'NULL' : intval($home_points[$match_id]);
				$away_points[$match_id] = ( '' == $away_points[$match_id] ) ? 'NULL' : intval($away_points[$match_id]);
				$home_apparatus_points[$match_id] = ( '' == $home_apparatus_points[$match_id] ) ? 'NULL' : intval($home_apparatus_points[$match_id]);
				$away_apparatus_points[$match_id] = ( '' == $away_apparatus_points[$match_id] ) ? 'NULL' : intval($away_apparatus_points[$match_id]);
				
				$winner = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'winner' );
				$loser = $this->getMatchResult( $home_points[$match_id], $away_points[$match_id], $home_team[$match_id], $away_team[$match_id], 'loser' );
				
				$wpdb->query( "UPDATE {$wpdb->gradebook_matches} SET `home_points` = ".$home_points[$match_id].", `away_points` = ".$away_points[$match_id].", `home_apparatus_points` = ".$home_apparatus_points[$match_id].", `away_apparatus_points` = ".$away_apparatus_points[$match_id].", `winner_id` = ".intval($winner).", `loser_id` = ".intval($loser)." WHERE `id` = {$match_id}" );
			}
		}
		return __('Updated Student Results','gradebook');
	}
	
	
	/**
	 * determine match result
	 *
	 * @param int $home_points
	 * @param int $away_points
	 * @param int $home_team
	 * @param int $away_team
	 * @param string $option
	 * @return int
	 */
	function getMatchResult( $home_points, $away_points, $home_team, $away_team, $option )
	{
		if ( $home_points > $away_points ) {
			$match['winner'] = $home_team;
			$match['loser'] = $away_team;
		} elseif ( $home_points < $away_points ) {
			$match['winner'] = $away_team;
			$match['loser'] = $home_team;
		} elseif ( 'NULL' === $home_points && 'NULL' === $away_points ) {
			$match['winner'] = 0;
			$match['loser'] = 0;
		} else {
			$match['winner'] = -1;
			$match['loser'] = -1;
		}
		
		return $match[$option];
	}
	
	
	/**
	 * replace shortcodes with respective HTML in posts or pages
	 *
	 * @param string $content
	 * @return string
	 */
	function insert( $content )
	{
		if ( stristr( $content, '[leaguestandings' )) {
			$search = "@\[leaguestandings\s*=\s*(\w+)\]@i";
			
			if ( preg_match_all($search, $content , $matches) ) {
				if (is_array($matches)) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getStandingsTable( $league_id );
			
						$content = str_replace($search, $replace, $content);
					}
				}
			}
		}
		
		if ( stristr ( $content, '[leaguematches' )) {
			$search = "@\[leaguematches\s*=\s*(\w+),(.*?)\]@i";
		
			if ( preg_match_all($search, $content , $matches) ) {
				if (is_array($matches)) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getMatchTable( $league_id, $matches[2][$key] );
			
						$content = str_replace($search, $replace, $content);
					}
				}
			}
		}
		
		if ( stristr ( $content, '[leaguecrosstable' )) {
			$search = "@\[leaguecrosstable\s*=\s*(\w+),(|embed|popup|)\]@i";
			
			if ( preg_match_all($search, $content , $matches) ) {
				if ( is_array($matches) ) {
					foreach($matches[1] AS $key => $v0) {
						$league_id = $v0;
						$search = $matches[0][$key];
						$replace = $this->getCrossTable( $league_id, $matches[2][$key] );
						
						$content = str_replace( $search, $replace, $content );
					}
				}
			}
		}
		
		$content = str_replace('<p></p>', '', $content);
		return $content;
	}


	/**
	 * gets league standings table
	 *
	 * @param int $league_id
	 * @param boolean $widget
	 * @return string
	 */
	function getStandingsTable( $league_id, $widget = false )
	{
		global $wpdb;
		
		$this->preferences = $this->getLeaguePreferences( $league_id );
		$secondary_points_title = ( $this->isGymnasticsLeague( $league_id ) ) ? __('AP','gradebook') : __('Goals','gradebook');
			
		$out = '</p><table class="leaguemanager" summary="" title="'.__( 'Standings', 'gradebook' ).' '.$this->getLeagueTitle($league_id).'">';
		$out .= '<tr><th class="num">&#160;</th>';
		
		$out .= '<th>'.__( 'Club', 'gradebook' ).'</th>';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'Pld', 'gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'W','gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'T','gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'L','gradebook' ).'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.$secondary_points_title.'</th>' : '';
		$out .= ( !$widget ) ? '<th class="num">'.__( 'Diff', 'gradebook' ).'</th>' : '';
		$out .= '<th class="num">'.__( 'Pts', 'gradebook' ).'</th>
		   	</tr>';

		$teams = $this->rankTeams( $league_id );
		if ( count($teams) > 0 ) {
			$rank = 0; $class = array();
			foreach( $teams AS $team ) {
				$rank++;
				$class = ( in_array('alternate', $class) ) ? array() : array('alternate');
				$home_class = ( 1 == $team['home'] ) ? 'home' : '';
				
				// Add Divider class
				if ( $rank == 1 || $rank == 3 || count($teams)-$rank == 3 || count($teams)-$rank == 1)
					$class[] =  'divider';
				
			 	$team_title = ( $widget ) ? $team['short_title'] : $team['title'];
			 	if ( $this->isGymnasticsLeague( $league_id ) )
			 		$secondary_points = $team['apparatus_points']['plus'].':'.$team['apparatus_points']['minus'];
				else
					$secondary_points = $team['goals']['plus'].':'.$team['goals']['minus'];
		
				$out .= "<tr class='".implode(' ', $class)."'>";
				$out .= "<td class='rank'>$rank</td>";
				if ( 1 == $this->preferences->show_logo ) {
					$out .= '<td class="logo">';
					if ( $team['logo'] != '' )
					$out .= "<img src='".$this->getImageUrl($team['logo'])."' alt='".__('Logo','gradebook')."' title='".__('Logo','gradebook')." ".$team['title']."' />";
					$out .= '</td>';
				}
				$out .= "<td><span class='$home_class'>".$team_title."</span></td>";
				$out .= ( !$widget ) ? "<td class='num'>".$this->getNumDoneMatches( $team['id'] )."</td>" : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumWonMatches( $team['id'] ).'</td>' : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumDrawMatches( $team['id'] ).'</td>' : '';
				$out .= ( !$widget ) ? '<td class="num">'.$this->getNumLostMatches( $team['id'] ).'</td>' : '';
				if ( $this->isGymnasticsLeague( $league_id ) && !$widget )
					$out .= "<td class='num'>".$team['apparatus_points']['plus'].":".$team['apparatus_points']['minus']."</td><td class='num'>".$team['diff']."</td>";
				elseif ( !$widget )
					$out .= "<td class='num'>".$team['goals']['plus'].":".$team['goals']['minus']."</td><td class='num'>".$team['diff']."</td>";
				
				if ( $this->isGymnasticsLeague( $league_id ) )
					$out .= "<td class='num'>".$team['points']['plus'].":".$team['points']['minus']."</td>";
				else
					$out .= "<td class='num'>".$team['points']['plus']."</td>";
				$out .= "</tr>";
			}
		}
		
		$out .= '</table><p>';
		
		return $out;
	}


	/**
	 * gets match table for given league
	 *
	 * @param int $league_id
	 * @param string $date date in MySQL format YYYY-MM-DD
	 * @return string
	 */
	function getMatchTable( $league_id, $date = '' )
	{
		$leagues = $this->getLeagues( $league_id );
		$preferences = $this->getLeaguePreferences( $league_id );
		
		$teams = $this->getTeams( $league_id, 'ARRAY' );
		
		$search = "league_id = '".$league_id."'";
		if ( $date != '' ) {
			$dates = explode( '|', $date );
			$s = array();
			foreach ( $dates AS $date )
				$s[] = "`date` LIKE '$date __:__:__'";
				
			$search .= ' AND ('.implode(' OR ', $s).')';
		}
		$matches = $this->getMatches( $search );
		
		$home_only = false;
		if ( 2 == $preferences->match_calendar )
			$home_only = true;
			
		if ( $matches ) {
			$out = "</p><table class='gradebook' summary='' title='".__( 'Match Plan', 'gradebook' )." ".$leagues['title']."'>";
			$out .= "<tr>
					<th class='match'>".__( 'Match', 'gradebook' )."</th>
					<th class='score'>".__( 'Score', 'gradebook' )."</th>";
					if ( $this->isGymnasticsLeague( $league_id ) )
					$out .= "<th class='ap'>".__( 'AP', 'gradebook' )."</th>";	
			$out .=	"</tr>";
			foreach ( $matches AS $match ) {
				$match->home_apparatus_points = ( NULL == $match->home_apparatus_points ) ? '-' : $match->home_apparatus_points;
				$match->away_apparatus_points = ( NULL == $match->away_apparatus_points ) ? '-' : $match->away_apparatus_points;
				$match->home_points = ( NULL == $match->home_points ) ? '-' : $match->home_points;
				$match->away_points = ( NULL == $match->away_points ) ? '-' : $match->away_points;
				
				if ( !$home_only || ($home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) ) {
					$class = ( 'alternate' == $class ) ? '' : 'alternate';
					$location = ( '' == $match->location ) ? 'N/A' : $match->location;
					$start_time = ( '0' == $match->hour && '0' == $match->minutes ) ? 'N/A' : mysql2date(get_option('time_format'), $match->date);
									
					$matchclass = ( $this->isOwnHomeMatch( $match->home_team, $teams ) ) ? 'home' : '';
							
					$out .= "<tr class='$class'>";
					$out .= "<td class='match'>".mysql2date(get_option('date_format'), $match->date)." ".$start_time." ".$location."<br /><span class='$matchclass'>".$teams[$match->home_team]['title'].' - '. $teams[$match->away_team]['title']."</span></td>";
					$out .= "<td class='score' valign='bottom'>".$match->home_points.":".$match->away_points."</td>";
					if ( $this->isGymnasticsLeague( $league_id ) )
						$out .= "<td class='ap' valign='bottom'>".$match->home_apparatus_points.":".$match->away_apparatus_points."</td>";
					$out .= "</tr>";
				}
			}
			$out .= "</table><p>";
		}
		
		return $out;
	}
	

	/**
	 * get cross-table with home team down the left and away team across the top
	 *
	 * @param int $league_id
	 * @return string
	 */
	function getCrossTable( $league_id, $mode )
	{
		$leagues = $this->getLeagues( $league_id );
		$teams = $this->rankTeams( $league_id );
		$rank = 0;
		
		$out = "</p>";
		
		// Thickbox Popup
		if ( 'popup' == $mode ) {
 			$out .= "<div id='leaguemanager_crosstable' style='width=800px;overfow:auto;display:none;'><div>";
		}
		
		$out .= "<table class='leaguemanager crosstable' summary='' title='".__( 'Crosstable', 'gradebook' )." ".$leagues['title']."'>";
		$out .= "<th colspan='2' style='text-align: center;'>".__( 'Club', 'gradebook' )."</th>";
		for ( $i = 1; $i <= count($teams); $i++ )
			$out .= "<th class='num'>".$i."</th>";
		$out .= "</tr>";
		foreach ( $teams AS $team ) {
			$rank++; $home_class = ( 1 == $team['home'] ) ? 'home' : '';
			
			$out .= "<tr>";
			$out .= "<th scope='row' class='rank'>".$rank."</th><td><span class='$home_class'>".$team['title']."</span></td>";
			for ( $i = 1; $i <= count($teams); $i++ ) {
				if ( ($rank == $i) )
					$out .= "<td class='num'>-</td>";
				else
					$out .= $this->getScore($team['id'], $teams[$i-1]['id']);
			}
			$out .= "</tr>";
		}
		$out .= "</table>";
	
		// Thickbox Popup End
		if ( 'popup' == $mode ) {
			$out .= "</div></div>";
			$out .= "<p><a class='thickbox' href='#TB_inline?width=800&inlineId=leaguemanager_crosstable' title='".__( 'Crosstable', 'gradebook' )." ".$leagues['title']."'>".__( 'Crosstable', 'gradebook' )." ".$leagues['title']." (".__('Popup','gradebook').")</a></p>";
		}
		
		$out .= "<p>";
	
		return $out;
	}
	

	/**
	 * get match and score for teams
	 *
	 * @param int $curr_team_id
	 * @param int $opponent_id
	 * @return string
	 */
	function getScore($curr_team_id, $opponent_id)
	{
		global $wpdb;

		$match = $this->getMatches("(`home_team` = $curr_team_id AND `away_team` = $opponent_id) OR (`home_team` = $opponent_id AND `away_team` = $curr_team_id)");
		$out = "<td class='num'>-:-</td>";
		if ( $match ) {
			// match at home
			if ( NULL == $match[0]->home_points && NULL == $match[0]->away_points )
				$out = "<td class='num'>-:-</td>";
			elseif ( $curr_team_id == $match[0]->home_team )
				$out = "<td class='num'>".$match[0]->home_points.":".$match[0]->away_points."</td>";
			// match away
			elseif ( $opponent_id == $match[0]->home_team )
				$out = "<td class='num'>".$match[0]->away_points.":".$match[0]->home_points."</td>";
			
		}

		return $out;
	}


	/**
	 * test if match is home match
	 *
	 * @param array $teams
	 * @return boolean
	 */
	function isOwnHomeMatch( $home_team, $teams )
	{
		if ( 1 == $teams[$home_team]['home'] )
			return true;
		else
			return false;
	}
	
	
	/**
	 * displays widget
	 *
	 * @param $args
	 *
	 */
	function displayWidget( $args )
	{
		$options = get_option( 'leaguemanager_widget' );
		$widget_id = $args['widget_id'];
		$league_id = $options[$widget_id];

		$defaults = array(
			'before_widget' => '<li id="'.sanitize_title(get_class($this)).'" class="widget '.get_class($this).'_'.__FUNCTION__.'">',
			'after_widget' => '</li>',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => '</h2>',
			'match_display' => $options[$league_id]['match_display'],
			'table_display' => $options[$league_id]['table_display'],
			'info_page_id' => $options[$league_id]['info'],
		);
		$args = array_merge( $defaults, $args );
		extract( $args );
		
		$league = $this->getLeagues( $league_id );
		echo $before_widget . $before_title . $league['title'] . $after_title;
		
		echo "<div id='leaguemanager_widget'>";
		if ( 1 == $match_display ) {
			$home_only = ( 2 == $this->preferences->match_calendar ) ? true : false;
				
			echo "<p class='title'>".__( 'Upcoming Matches', 'gradebook' )."</p>";
			$matches = $this->getMatches( "league_id = '".$league_id."' AND DATEDIFF(NOW(), `date`) < 0" );
			$teams = $this->getTeams( $league_id, 'ARRAY' );
			
			if ( $matches ) {
				echo "<ul class='matches'>";
				foreach ( $matches AS $match ) {
					if ( !$home_only || ($home_only && (1 == $teams[$match->home_team]['home'] || 1 == $teams[$match->away_team]['home'])) )
						echo "<li>".mysql2date(get_option('date_format'), $match->date)." ".$teams[$match->home_team]['short_title']." - ".$teams[$match->away_team]['short_title']."</li>";
				}
				echo "</ul>";
			} else {
				echo "<p>".__( 'Nothing found', 'gradebook' )."</p>";
			}
		}
		if ( 1 == $table_display ) {
			echo "<p class='title'>".__( 'Table', 'gradebook' )."</p>";
			echo $this->getStandingsTable( $league_id, true );
		}
		if ( $info_page_id AND '' != $info_page_id )
			echo "<p class='info'><a href='".get_permalink( $info_page_id )."'>".__( 'More Info', 'gradebook' )."</a></p>";
		
		echo "</div>";
		echo $after_widget;
	}


	/**
	 * widget control panel
	 *
	 * @param none
	 */
	function widgetControl( $args )
	{
		extract( $args );
	 	$options = get_option( 'leaguemanager_widget' );
		if ( $_POST['league-submit'] ) {
			$options[$widget_id] = $league_id;
			$options[$league_id]['table_display'] = $_POST['table_display'][$league_id];
			$options[$league_id]['match_display'] = $_POST['match_display'][$league_id];
			$options[$league_id]['info'] = $_POST['info'][$league_id];
			
			update_option( 'leaguemanager_widget', $options );
		}
		
		$checked = ( 1 == $options[$league_id]['match_display'] ) ? ' checked="checked"' : '';
		echo '<p style="text-align: left;"><label for="match_display_'.$league_id.'" class="leaguemanager-widget">'.__( 'Show Matches','gradebook' ).'</label>';
		echo '<input type="checkbox" name="match_display['.$league_id.']" id="match_display_'.$league_id.'" value="1"'.$checked.'>';
		echo '</p>';
			
		$checked = ( 1 == $options[$league_id]['table_display'] ) ? ' checked="checked"' : '';
		echo '<p style="text-align: left;"><label for="table_display_'.$league_id.'" class="leaguemanager-widget">'.__( 'Show Table', 'gradebook' ).'</label>';
		echo '<input type="checkbox" name="table_display['.$league_id.']" id="table_display_'.$league_id.'" value="1"'.$checked.'>';
		echo '</p>';
		echo '<p style="text-align: left;"><label for="info['.$league_id.']" class="leaguemanager-widget">'.__( 'Page' ).'<label>';
		wp_dropdown_pages(array('name' => 'info['.$league_id.']', 'selected' => $options[$league_id]['info']));
		echo '</p>';		

		echo '<input type="hidden" name="league-submit" id="league-submit" value="1" />';
	}


	/**
	 * adds code to Wordpress head
	 *
	 * @param none
	 */
	function addHeaderCode($show_all=false)
	{
		$options = get_option('gradebook');
		
		echo "\n\n<!-- WP LeagueManager Plugin Version ".LEAGUEMANAGER_VERSION." START -->\n";
		echo "<link rel='stylesheet' href='".LEAGUEMANAGER_URL."/style.css' type='text/css' />\n";

		if ( !is_admin() ) {
			// Table styles
			echo "\n<style type='text/css'>";
			echo "\n\ttable.leaguemanager th { background-color: ".$options['colors']['headers']." }";
			echo "\n\ttable.leaguemanager tr { background-color: ".$options['colors']['rows'][1]." }";
			echo "\n\ttable.leaguemanager tr.alternate { background-color: ".$options['colors']['rows'][0]." }";
			echo "\n\ttable.crosstable th, table.crosstable td { border: 1px solid ".$options['colors']['rows'][0]."; }";
			echo "\n</style>";
		}

		if ( is_admin() AND (isset( $_GET['page'] ) AND substr( $_GET['page'], 0, 13 ) == 'gradebook' || $_GET['page'] == 'gradebook') || $show_all ) {
			wp_register_script( 'gradebook', LEAGUEMANAGER_URL.'/leaguemanager.js', array('thickbox', 'colorpicker', 'sack' ), LEAGUEMANAGER_VERSION );
			wp_print_scripts( 'gradebook' );
			echo '<link rel="stylesheet" href="'.get_option( 'siteurl' ).'/wp-includes/js/thickbox/thickbox.css" type="text/css" media="screen" />';
			
			?>
			<script type='text/javascript'>
			//<![CDATA[
				   LeagueManagerAjaxL10n = {
				   blogUrl: "<?php bloginfo( 'wpurl' ); ?>", pluginPath: "<?php echo LEAGUEMANAGER_PATH; ?>", pluginUrl: "<?php echo LEAGUEMANAGER_URL; ?>", requestUrl: "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php", imgUrl: "<?php echo LEAGUEMANAGER_URL; ?>/images", Edit: "<?php _e("Edit"); ?>", Post: "<?php _e("Post"); ?>", Save: "<?php _e("Save"); ?>", Cancel: "<?php _e("Cancel"); ?>", pleaseWait: "<?php _e("Please wait..."); ?>", Revisions: "<?php _e("Page Revisions"); ?>", Time: "<?php _e("Insert time"); ?>"
				   }
			//]]>
			  </script>
			<?php
		}
		
		echo "<!-- WP LeagueManager Plugin END -->\n\n";
	}


	/**
	 * add TinyMCE Button
	 *
	 * @param none
	 * @return void
	 */
	function addTinyMCEButton()
	{
		// Don't bother doing this stuff if the current user lacks permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
		
		// Check for LeagueManager capability
		if ( !current_user_can('manage_studens') ) return;
		
		// Add only in Rich Editor mode
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", array(&$this, 'addTinyMCEPlugin'));
			add_filter('mce_buttons', array(&$this, 'registerTinyMCEButton'));
		}
	}
	function addTinyMCEPlugin( $plugin_array )
	{
		$plugin_array['LeagueManager'] = LEAGUEMANAGER_URL.'/tinymce/editor_plugin.js';
		return $plugin_array;
	}
	function registerTinyMCEButton( $buttons )
	{
		array_push($buttons, "separator", "LeagueManager");
		return $buttons;
	}
	function changeTinyMCEVersion( $version )
	{
		return ++$version;
	}
	
	
	/**
	 * display global settings page (e.g. color scheme options)
	 *
	 * @param none
	 * @return void
	 */
	function displayOptionsPage()
	{
		$options = get_option('gradebook');
		
		if ( isset($_POST['updateLeagueManager']) ) {
			check_admin_referer('leaguemanager_manage-global-league-options');
			$options['colors']['headers'] = $_POST['color_headers'];
			$options['colors']['rows'] = array( $_POST['color_rows_alt'], $_POST['color_rows'] );
			
			update_option( 'gradebook', $options );
			echo '<div id="message" class="updated fade"><p><strong>'.__( 'Settings saved', 'gradebook' ).'</strong></p></div>';
		}
		
		
		echo "\n<form action='' method='post'>";
		wp_nonce_field( 'leaguemanager_manage-global-league-options' );
		echo "\n<div class='wrap'>";
		echo "\n\t<h2>".__( 'Leaguemanager Global Settings', 'gradebook' )."</h2>";
		echo "\n\t<h3>".__( 'Color Scheme', 'gradebook' )."</h3>";
		echo "\n\t<table class='form-table'>";
		echo "\n\t<tr valign='top'>";
		echo "\n\t\t<th scope='row'><label for='color_headers'>".__( 'Table Headers', 'gradebook' )."</label></th><td><input type='text' name='color_headers' id='color_headers' value='".$options['colors']['headers']."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[0].color_headers,\"pick_color_headers\"); return false;' name='pick_color_headers' id='pick_color_headers'>&#160;&#160;&#160;</a></td>";
		echo "\n\t</tr>";
		echo "\n\t<tr valign='top'>";
		echo "\n\t<th scope='row'><label for='color_rows'>".__( 'Table Rows', 'gradebook' )."</label></th>";
		echo "\n\t\t<td>";
		echo "\n\t\t\t<p class='table_rows'><input type='text' name='color_rows_alt' id='color_rows_alt' value='".$options['colors']['rows'][0]."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[0].color_rows_alt,\"pick_color_rows_alt\"); return false;' name='pick_color_rows_alt' id='pick_color_rows_alt'>&#160;&#160;&#160;</a></p>";
		echo "\n\t\t\t<p class='table_rows'><input type='text' name='color_rows' id='color_rows' value='".$options['colors']['rows'][1]."' size='10' /><a href='#' class='colorpicker' onClick='cp.select(document.forms[0].color_rows,\"pick_color_rows\"); return false;' name='pick_color_rows' id='pick_color_rows'>&#160;&#160;&#160;</a></p>";
		echo "\n\t\t</td>";
		echo "\n\t</tr>";
		echo "\n\t</table>";
		echo "\n<input type='hidden' name='page_options' value='color_headers,color_rows,color_rows_alt' />";
		echo "\n<p class='submit'><input type='submit' name='updateLeagueManager' value='".__( 'Save Preferences', 'gradebook' )." &raquo;' class='button' /></p>";
		echo "\n</form>";
	
		echo "<script language='javascript'>
			syncColor(\"pick_color_headers\", \"color_headers\", document.getElementById(\"color_headers\").value);
			syncColor(\"pick_color_rows\", \"color_rows\", document.getElementById(\"color_rows\").value);
			syncColor(\"pick_color_rows_alt\", \"color_rows_alt\", document.getElementById(\"color_rows_alt\").value);
		</script>";
		
		echo "<p>".sprintf(__( "To add and manage leagues, go to the <a href='%s'>Management Page</a>", 'gradebook' ), get_option( 'siteurl' ).'/wp-admin/edit.php?page=gradebook/manage-students.php')."</p>";
		if ( !function_exists('register_uninstall_hook') ) { ?>
		<div class="wrap">
			<h3 style='clear: both; padding-top: 1em;'><?php _e( 'Uninstall Leaguemanager', 'gradebook' ) ?></h3>
			<form method="get" action="index.php">
				<input type="hidden" name="leaguemanager" value="uninstall" />
				<p><input type="checkbox" name="delete_plugin" value="1" id="delete_plugin" /> <label for="delete_plugin"><?php _e( 'Yes I want to uninstall Leaguemanager Plugin. All Data will be deleted!', 'gradebook' ) ?></label> <input type="submit" value="<?php _e( 'Uninstall Leaguemanager', 'gradebook' ) ?> &raquo;" class="button" /></p>
			</form>
		</div>
		<?php }
	}
	
	
	/**
	 * initialize widget
	 *
	 * @param none
	 */
	function activateWidget()
	{
		if ( !function_exists('register_sidebar_widget') )
			return;
		
		foreach ( $this->getActiveLeagues() AS $league_id => $league ) {
			$name = __( 'League', 'gradebook' ) .' - '. $league['title'];
			register_sidebar_widget( $name , array( &$this, 'displayWidget' ) );
			register_widget_control( $name, array( &$this, 'widgetControl' ), '', '', array( 'league_id' => $league_id, 'widget_id' => sanitize_title($name) ) );
		}
	}


	/**
	 * initialize plugin
	 *
	 * @param none
	 */
	function activate()
	{
		global $wpdb;
		include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );
		
		$options = array();
		$options['version'] = LEAGUEMANAGER_VERSION;
		$options['colors']['headers'] = '#dddddd';
		$options['colors']['rows'] = array( '#ffffff', '#efefef' );
		
		$old_options = get_option( 'gradebook' );
		if ( !isset($old_options['version']) || version_compare($old_options['version'], LEAGUEMANAGER_VERSION, '<') ) {
			require_once( LEAGUEMANAGER_PATH . '/leaguemanager-upgrade.php' );
			update_option( 'gradebook', $options );
		}
		
		$charset_collate = '';
		if ( $wpdb->supports_collation() ) {
			if ( ! empty($wpdb->charset) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty($wpdb->collate) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
		
		$create_leagues_sql = "CREATE TABLE {$wpdb->gradebook} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 30 ) NOT NULL ,
						`forwin` tinyint( 4 ) NOT NULL default '2',
						`fordraw` tinyint( 4 ) NOT NULL default '1',
						`forloss` tinyint( 4 ) NOT NULL default '0',
						`match_calendar` tinyint( 1 ) NOT NULL default '1',
						`type` tinyint( 1 ) NOT NULL default '2',
						`show_logo` tinyint( 1 ) NOT NULL default '0',
						`active` tinyint( 1 ) NOT NULL default '1' ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->gradebook, $create_leagues_sql );
			
		$create_teams_sql = "CREATE TABLE {$wpdb->gradebook_teams} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`title` varchar( 25 ) NOT NULL ,
						`short_title` varchar( 25 ) NOT NULL,
						`logo` varchar( 50 ) NOT NULL,
						`home` tinyint( 1 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->gradebook_teams, $create_teams_sql );
		
		$create_matches_sql = "CREATE TABLE {$wpdb->gradebook_matches} (
						`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
						`date` datetime NOT NULL ,
						`home_team` int( 11 ) NOT NULL ,
						`away_team` int( 11 ) NOT NULL ,
						`location` varchar( 100 ) NOT NULL ,
						`league_id` int( 11 ) NOT NULL ,
						`home_apparatus_points` tinyint( 4 ) NULL default NULL,
						`away_apparatus_points` tinyint( 4 ) NULL default NULL,
						`home_points` tinyint( 4 ) NULL default NULL,
						`away_points` tinyint( 4 ) NULL default NULL,
						`winner_id` int( 11 ) NOT NULL,
						`loser_id` int( 11 ) NOT NULL,
						PRIMARY KEY ( `id` )) $charset_collate";
		maybe_create_table( $wpdb->gradebook_matches, $create_matches_sql );
			
		add_option( 'gradebook', $options, 'Leaguemanager Options', 'yes' );
		
		/*
		* Add widget options
		*/
		if ( function_exists('register_sidebar_widget') ) {
			$options = array();
			add_option( 'leaguemanager_widget', $options, 'Leaguemanager Widget Options', 'yes' );
		}
		
		/*
		* Set Capabilities
		*/
		$role = get_role('administrator');
		$role->add_cap('manage_grades');
	}
	
	
	/**
	 * Uninstall Plugin
	 *
	 * @param none
	 */
	function uninstall()
	{
		global $wpdb;
		
		$wpdb->query( "DROP TABLE {$wpdb->gradebook_matches}" );
		$wpdb->query( "DROP TABLE {$wpdb->gradebook_teams}" );
		$wpdb->query( "DROP TABLE {$wpdb->gradebook}" );
		
		delete_option( 'leaguemanager_widget' );
		delete_option( 'gradebook' );
		
		if ( !function_exists('register_uninstall_hook') ) {
			$plugin = basename(__FILE__, ".php") .'/plugin-hook.php';
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( function_exists( "deactivate_plugins" ) )
				deactivate_plugins( $plugin );
			else {
				$current = get_option('active_plugins');
				array_splice($current, array_search( $plugin, $current), 1 ); // Array-fu!
				update_option('active_plugins', $current);
				do_action('deactivate_' . trim( $plugin ));
			}
		}
	}
	
	
	/**
	 * adds menu to the admin interface
	 *
	 * @param none
	 */
	function addAdminMenu()
	{
		$plugin = 'gradebook/plugin-hook.php';
 		add_management_page( __( 'Gradebook', 'gradebook' ), __( 'Gradebook', 'gradebook' ), 'manage_students', basename( __FILE__, ".php" ).'/manage-students.php' );
		
		
	}
	
	
	
}
?>
