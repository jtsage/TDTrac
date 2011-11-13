<?php
/**
 * TDTrac Show Functions
 * 
 * Contains all show related functions. 
 * Data hardened
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/**
 * SHOWS Module
 *  Allows configuration of shows
 * 
 * @package tdtrac
 * @version 3.0.0
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
	 * @return void
	 */
	public function output() {
		global $TEST_MODE, $HEAD_LINK, $CANCEL;
		if ( !$this->output_json ) { // HTML METHODS
			switch ( $this->action['action'] ) {
				case "add":
					$CANCEL = true;
					$this->title .= "::Add";
					if ( $this->user->can("addshow") ) {
						$this->html = $this->add_form();
					} else {
						$this->html = error_page('Access Denied :: You cannot add new shows');
					} break;
				case "edit":
					$CANCEL = true;
					$this->title .= "::Edit";
					if ( $this->user->can("editshow") ) {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->html = $this->edit_form(intval($this->action['id']));
						} else {
							$this->html = error_page("Error :: Data Mismatch Detected");
						}
					} else {
						$this->html = error_page('Access Denied :: You Cannot Edit Shows');
					} break;
				default:
					if ( $this->user->can('viewshow') ) {
						$HEAD_LINK = array('/shows/add/', 'plus', 'Add Show'); 
						$this->title .= "::View";
						$this->html = $this->view();
					} else {
						$this->html = error_page("Access Denied :: You Cannot View Shows");
					} break;
			}
			makePage($this->html, $this->title);
		} else { 
			switch($this->action['action']) {
				case "save":
					if ( $this->action['new'] == 0 ) {
						if ( $this->user->can("editshow") ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								$this->json = $this->save(true);
								$this->json['location'] = "/shows/";
							}
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
					} elseif ( $this->action['new'] == 1 ) {
						if ( $this->user->can("addshow") ) {
							$this->json = $this->save(false);
							$this->json['location'] = "/shows/";
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
					} else {
						$this->json['success'] = false;
						$this->json['msg'] = "Poorly Formed Request";
					} break;
				case "delete":
					if ( $TEST_MODE ) {
						$this->json['success'] = true;
					} else {
						if ( $this->user->can("editshow") ) {
							if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->admin ) {
								$this->delete(intval($this->action['id']));
							} else {
								$this->json['success'] = false;
								$this->json['msg'] = "Poorly formed request";
							}
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
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
	 * @return array HTML output
	 */
	private function add_form() {
		GLOBAL $TDTRAC_SITE;
		$form = new tdform(array('action' => "{$TDTRAC_SITE}shows/save/json:1/new:1/", 'id' => 'show-add-form'));
		
		$result = $form->addText(array('name' => 'showname', 'label' => 'Show Name'));
		$result = $form->addText(array('name' => 'company', 'label' => 'Show Company'));
		$result = $form->addText(array('name' => 'venue', 'label' => 'Show Venue'));
		$result = $form->addDate(array('name' => 'dates', 'label' => 'Show Opening', 'options' => '{"mode":"calbox", "useModal": true}'));
		
		return $form->output('Add Show');
	}

	/**
	 * Show the show edit form
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @param integer Show ID
	 * @return array HTML Output
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
		$form = new tdform(array('action' => "{$TDTRAC_SITE}shows/save/json:1/new:0/id:{$id}/", 'id' => "showedit"));
		
		$fesult = $form->addText(array('name' => 'showname', 'label' => 'Show Name', 'preset' => $row['showname']));
		$result = $form->addText(array('name' => 'company', 'label' => 'Show Company', 'preset' => $row['company']));
		$result = $form->addText(array('name' => 'venue', 'label' => 'Show Venue', 'preset' => $row['venue']));
		$result = $form->addDate(array('name' => 'dates', 'label' => 'Show Dates', 'preset' => $row['dates']));
		$result = $form->addToggle(array('name' => 'closed', 'label' => 'Show Record Open', 'preset' => $row['closed'], 'options' => array(array(1,'Closed'),array(0,'Open'))));
		$result = $form->addHidden('id', $id);
		return array_merge($form->output('Commit'));
	}

	/**
	 * Logic to remove a show from the database
	 *
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer ShowID to nuke
	 * @return void
	 */
	private function delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		if ( !$this->user->admin || !is_numeric($id) || $id < 1 ) {
			$this->json['success'] = false;
		} else {
			$sqla = "DELETE FROM `{$MYSQL_PREFIX}todo` WHERE showid = ".intval($id);
			$sqlb = "DELETE FROM `{$MYSQL_PREFIX}hours` WHERE showid = ".intval($id);
			$sqlc = "DELETE FROM `{$MYSQL_PREFIX}budget` WHERE showid = ".intval($id);
			$sqld = "DELETE FROM `{$MYSQL_PREFIX}shows` WHERE showid = ".intval($id);
			// GARBAGE COLLECT RECIEPTS - DELETES ALL HANDLED, UNREFERNCED RECIEPTS.
			$rgc = "DELETE FROM `{$MYSQL_PREFIX}rcpts` WHERE imgid NOT IN (SELECT imgid FROM `{$MYSQL_PREFIX}budget`) AND handled = 1";
			$result = mysql_query($sqla, $db);
			$result = mysql_query($sqlb, $db);
			$result = mysql_query($sqlc, $db);
			$result = mysql_query($sqld, $db);
			$result = mysql_query($rgc, $db);
			$this->json['success'] = true;
		}
	}

	/**
	 * Logic to save show to database
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool MySQL DEBUG Status
	 * @param bool True for new record, false for overwrite
	 * @return void
	 */
	private function save($exists = false) {
		GLOBAL $db, $MYSQL_PREFIX, $TEST_MODE;
		if ( !$exists ) {
			$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}shows` ( showname, company, venue, dates )";
			$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s' )";
			
			if ( empty($_REQUEST['showname']) ) { return json_error('Show Name Required');	}
			
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
				intval($_REQUEST['closed']),
				intval($_REQUEST['id'])
			);
		}
	
		$result = mysql_query($sql, $db);
		if ( $result ) {
			return array('success' => true, 'msg' => "Show `{$_REQUEST['showname']}` Saved");
		} else {
			return array('success' => false, 'msg' => "Show Save Failed".(($TEST_MODE)?mysql_error():""));
		}
	}

	/**
	 * View all shows in database
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Base HREF
	 * @global array JavaScript
	 * @return array HTML Output
	 */
	private function view() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $SITE_SCRIPT;
		$sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` ORDER BY `closed` ASC, `created` DESC";
		$result = mysql_query($sql, $db);
		$list = new tdlist(array('id' => 'show_view', 'actions' => false));
		$showsopen = true;
		
		$list->setFormat("<a data-recid='%d' data-admin='".(($this->user->admin)?1:0)."' class='show-menu' href='#'><h3>%s</h3><p><strong>Company:</strong> %s<br /><strong>Venue:</strong> %s<br /><strong>Dates:</strong> %s</p></a>");
		$list->addDivide('Open Shows');
		while ( $row = mysql_fetch_array($result) ) {
			if ( $showsopen && $row['closed'] == 1 ) {
				$list->addDivide('Closed Shows');
				$showsopen = false;
			}
			$list->addRow(array($row['showid'], $row['showname'], $row['company'], $row['venue'], $row['dates']), $row);
		}
		return $list->output();
	}
	
	/** 
	 * Show available Show Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		global $TDTRAC_SITE;
		$html[] = "<div class=\"tasks\"><ul class=\"linklist\"><li><h3>Show Information</h3><ul class=\"linklist\">";
		$html[] = "  <li>Manage shows tracked by TDTrac</li>";
		$html[] = ( $this->user->can('addshow') ) ? "  <li><a href=\"{$TDTRAC_SITE}shows/add/\">Add Show</a></li>" : "";
		$html[] = ( $this->user->can('viewshow') ) ? "  <li><a href=\"{$TDTRAC_SITE}shows/view/\">View Shows</a></li>" : "";
		$html[] = "</ul></li></ul></div>";
		return $html;
	}
}



?>
