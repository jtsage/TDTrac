<?php
/**
 * TDTrac Show Functions
 * 
 * Contains all show related functions. 
 * Data hardened
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/**
 * SHOWS Module
 *  Allows configuration of shows
 * 
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_shows {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var bool Output format (TURE = json, FALSE = html) */
	private $output_json = false;
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Shows";
	
	/** @var array JSON Data */
	private $json = array();
	
	/** 
	 * Create a new instance of the TO-DO module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Todo Object
	 */
	public function __construct($user, $action = null) {
		$this->post = ($_SERVER['REQUEST_METHOD'] == "POST") ? true : false;
		$this->user = $user;
		$this->action = $action;
		$this->output_json = $action['json'];
	}
	
	/**
	 * Output todo list operation
	 * 
	 * @return null
	 */
	public function output() {
		if ( !$this->output_json ) { // HTML METHODS
			switch ( $this->action['action'] ) {
				case "add":
					$this->title .= " :: Add";
					if ( $this->user->can("addshow") ) {
						if ( $this->post ) {
							thrower($this->save(false), 'shows/add/');
						} else {
							$this->html = $this->add_form();
						}
					} else {
						thrower('Access Denied :: You cannot add new shows', 'shows/');
					} break;
				case "view":
					if ( $this->user->can('viewshow') ) {
						$this->title .= " :: View";
						$this->html = $this->view();
					} else {
						thrower("Access Denied :: You Cannot View Shows");
					} break;
				case "edit":
					$this->title .= " :: Edit";
					if ( $this->user->can("editshow") ) {
						if ( $this->post ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								thrower($this->save(true), "shows/edit/id:".intval($_REQUEST['id'])."/");
							} else {
								thrower('Error :: Data Mismatch Detected', 'shows/');
							}
						} else {
							if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
								$this->html = $this->edit_form(intval($this->action['id']));
							} else {
								thrower("Error :: Data Mismatch Detected", 'shows/');
							}
						}
					} else {
						thrower('Access Denied :: You Cannot Edit Shows', 'shows/');
					} break;
				default:
					$this->html = $this->index();
					break;
			}
			makePage($this->html, $this->title);
		} else { 
			switch($this->action['action']) {
				case "delete":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->admin ) {
						$this->delete(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				default:
					$this->json['success'] = false;
					break;
			}
			echo json_encode($this->json);
		}
	} // END OUTPUT FUNCTION

	/**
	 * Show the show add form
	 * 
	 * @global string Site address for links
	 * @return string HTML output
	 */
	private function add_form() {
		GLOBAL $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}shows/add/", 'show-add-form', 1, 'genform', 'Add A Show');
		
		$result = $form->addText('showname', 'Show Name');
		$result = $form->addText('company', 'Show Company');
		$result = $form->addText('venue', 'Show Venue');
		$result = $form->addDate('dates', 'Show Opening');
		
		return $form->output('Add Show');
	}

	/**
	 * Show the show edit form
	 * 
	 * @global resource Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @param integer Show ID
	 * @return string HTML Output
	 */
	private function edit_form($id) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	
		$sqlstring  = "SELECT `showname`, `company`, `venue`, `dates`, `closed` FROM `{$MYSQL_PREFIX}shows`";
		$sqlstring .= " WHERE `showid` = %d LIMIT 1";
	
		$sql = sprintf($sqlstring,
			intval($id)
		);
	
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		$form = new tdform("{$TDTRAC_SITE}shows/edit/id:{$id}/", "showedit", 1, 'genform', 'Edit Show');
		
		$fesult = $form->addText('showname', 'Show Name', null, $row['showname']);
		$result = $form->addText('company', 'Show Company', null, $row['company']);
		$result = $form->addText('venue', 'Show Venue', null, $row['venue']);
		$result = $form->addDate('dates', 'Show Dates', null, $row['dates']);
		$openshow =  ( $row['closed'] ? 0 : 1 );
		$result = $form->addCheck('closed', 'Show Record Open', null, $openshow);
		$result = $form->addHidden('id', $id);
		
		return $form->output('Commit');
	}

	/**
	 * Logic to remove a show from the database
	 *
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer ShowID to nuke
	 */
	private function delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		if ( !$this->user->admin || !is_numeric($id) || $id < 1 ) {
			$json['success'] = false;
		} else {
			$sqla = "DELETE FROM `{$MYSQL_PREFIX}todo` WHERE showid = ".intval($id);
			$sqlb = "DELETE FROM `{$MYSQL_PREFIX}hours` WHERE showid = ".intval($id);
			$sqlc = "DELETE FROM `{$MYSQL_PREFIX}budget` WHERE showid = ".intval($id);
			$sqld = "DELETE FROM `{$MYSQL_PREFIX}shows` WHERE showid = ".intval($id);
			$result = mysql_query($sqla, $db);
			$result = mysql_query($sqlb, $db);
			$result = mysql_query($sqlc, $db);
			$result = mysql_query($sqld, $db);
			$json['success'] = true;
		}
	}

	/**
	 * Logic to save show to database
	 * 
	 * @global resource Database Link
	 * @global string MySQL Table Prefix
	 * @global bool MySQL DEBUG Status
	 */
	private function save($exists = false) {
		GLOBAL $db, $MYSQL_PREFIX, $MYSQL_DEBUG;
		if ( !$exists ) {
			$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}shows` ( showname, company, venue, dates )";
			$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s' )";
			
			$sql = sprintf($sqlstring,
				mysql_real_escape_string($_REQUEST['showname']),
				mysql_real_escape_string($_REQUEST['company']),
				mysql_real_escape_string($_REQUEST['venue']),
				mysql_real_escape_string($_REQUEST['dates'])
			);
		} else {
			$sqlstring  = "UPDATE `{$MYSQL_PREFIX}shows` SET showname='%s', company='%s', venue='%s', dates='%s',";
		    $sqlstring .= " closed=%d WHERE showid = %d";
		
			$sql = sprintf($sqlstring,
				mysql_real_escape_string($_REQUEST['showname']),
				mysql_real_escape_string($_REQUEST['company']),
				mysql_real_escape_string($_REQUEST['venue']),
				mysql_real_escape_string($_REQUEST['dates']),
				(($_REQUEST['closed'] == 'y') ? 0 : 1),
				intval($_REQUEST['id'])
			);
		}
	
		$result = mysql_query($sql, $db);
		if ( $result ) {
			return "Show {$_REQUEST['showname']} Saved";
		} else {
			return "Show Save :: Operation Failed". (($MYSQL_DEBUG) ? " (".mysql_error().")" : "");
		}
	}

	/**
	 * View all shows in database
	 * 
	 * @global resource Database Link
	 * @global string MySQL Table Prefix
	 * @return string HTML Output
	 */
	private function view() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $SITE_SCRIPT;
		$sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` ORDER BY `created` DESC";
		$result = mysql_query($sql, $db);
		$html = array();
		while ( $row = mysql_fetch_array($result) ) {
			$html[] = "<h3>{$row['showname']}</h3>";
			if ( $this->user->admin ) {
				$safename = preg_replace("/ /", "", $row['showname']);
				$temp = array();
				$SITE_SCRIPT[] = "var showdel{$safename} = true;";
				$SITE_SCRIPT[] = "$(function() { $('.sdel-{$safename}').click( function() {";
				$SITE_SCRIPT[] = "	if ( showdel{$safename} && confirm('Delete Show #{$row['showid']}?')) {";
				$SITE_SCRIPT[] = "		$.getJSON(\"{$TDTRAC_SITE}shows/delete/json:1/id:{$row['showid']}/\", function(data) {";
				$SITE_SCRIPT[] = "			if ( data.success === true ) { ";
				$SITE_SCRIPT[] = "				$('#popper').html(\"Show #{$row['showid']} Deleted\");";
				$SITE_SCRIPT[] = "			} else { $('#popper').html(\"Show #{$row['showid']} Delete :: Failed\"); }";
				$SITE_SCRIPT[] = "			showdel{$safename} = false;";
				$SITE_SCRIPT[] = "			$('#popperdiv').show('blind');";			
				$SITE_SCRIPT[] = "	});} return false;";
				$SITE_SCRIPT[] = "});});";
				$temp[] = "<span class=\"overright\">[<a href=\"{$TDTRAC_SITE}shows/edit/id:{$row['showid']}/\">Edit</a>]";
				$temp[] = " [<a href=\"#\" class=\"sdel-{$safename}\" />Delete</a>]";
				$temp[] = "</span>";
				$html[] = join($temp);
			}
			$html[] = "  <ul class=\"datalist\">";
			$html[] = "    <li><strong>Company</strong>: {$row['company']}</li>";
			$html[] = "    <li><strong>Venue</strong>: {$row['venue']}</li>";
			$html[] = "    <li><strong>Dates</strong>: {$row['dates']}</li>";
			$html[] = "    <li><strong>Show Record Open</strong>: " . (( $row['closed'] == 1 ) ? "NO" : "YES") . "</li>";
			$html[] = "</ul>";
		}
		return $html;
	}
	
	/** 
	 * Show available Show Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		global $TDTRAC_SITE;
		$html[] = "<ul class=\"linklist\"><li><h3>Show Information</h3><ul class=\"linklist\">";
		$html[] = "<li>Manage shows tracked by TDTrac</li>";
		$html[] = ( $this->user->can('addshow') ) ? "<li><a href=\"{$TDTRAC_SITE}shows/add/\">Add Show</a></li>" : "";
		$html[] = ( $this->user->can('viewshow') ) ? "<li><a href=\"{$TDTRAC_SITE}shows/view/\">View Shows</a></li>" : "";
		$html[] = "</ul></li></ul>";
		return $html;
	}
}



?>
