<?php
/**
 * TDTrac JSON Functions
 * 
 * Contains the json functions (all)
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 3.0.0
 */

/**
 * JSON Module
 *  Allows per-user and per-show task lists
 * 
 * @package tdtrac
 * @version 3.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_json {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array JSON Data */
	private $json = array();
	
	/** 
	 * Create a new instance of the JSON module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Todo Object
	 */
	public function __construct($user, $action = null) {
		$this->post = ($_SERVER['REQUEST_METHOD'] == "POST") ? true : false;
		$this->user = $user;
		$this->action = $action;
	}
	
	/**
	 * Output JSON operation
	 * 
	 * @global array Extra Header Link
	 * @global bool Make 'back' link a 'cancel' link
	 * @global bool Set app into test mode
	 * @return void
	 */
	public function handler() {
		global $HEAD_LINK, $CANCEL, $TEST_MODE;
		$this->json['success'] = false; $this->json['msg'] = "Unknown Error";
		
		if ( !isset($this->action['base']) || !isset($this->action['action']) || ! is_numeric($this->action['id']) ) {
			$this->json['success'] = false;
			$this->json['msg'] = "Poorly Formed Request";
		} elseif ( ! in_array($this->action['base'], array('todo','show','hours','budget')) ) {
			$this->json['success'] = false;
			$this->json['msg'] = "Bad Base Module Name";
		} else {
			switch($this->action['action']) {
				case "delete":
					if ( $this->user->can("edit{$this->action['name']}") ) {
						$this->json['success'] = false;
						$this->json['msg'] = "Permission Denied!";
					} else {
						switch($this->action['base']) {
							case "todo":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}todo` WHERE id = ".intval($this->action['id'])." LIMIT 1");
								break;
							case "hours":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}hours` WHERE id = ".intval($this->action['id'])." LIMIT 1");
								break;
							case "budget":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}budget` WHERE id = ".intval($this->action['id'])." LIMIT 1");
								break;
						}
					} break;
				case "mark":
					if ( $this->user->can("edittodo") && $this->action['base'] == 'todo' ) {
						$this->do_sql("UPDATE `{$MYSQL_PREFIX}todo` SET complete = 1 WHERE id = ".intval($this->action['id'])." LIMIT 1");
					} else {
						$this->json['success'] = false;
						$this->json['msg'] = "Permission Denied!";
					} break;
				case "email":
					if ( $this->user->can("view{$this->action['base']}") ) {
						$this->json['success'] = false;
						$this->json['msg'] = "Permission Denied!";
					} else {
						switch($this->action['base']) {
							case "todo":
								$mod = new tdtrac_todo($this->user, $this->action);
								$this->json['success'] = $mod->email();
								break;
							case "hours":
								$mod = new tdtrac_hours($this->user, $this->action);
								$this->json['success'] = $mod->email();
								break;
							case "budget":
								$mod = new tdtrac_budget($this->user, $this->action);
								$this->json['success'] = $mod->email();
								break;
						}
						if ( $this->json['success'] == true ) { 
							$this->json['msg'] = "E-Mail Sent";
						} else {
							$this->json['msg'] = "E-Mail Failed to Send";
						}
					} break;
				case "save":
					if ( $this->action['id'] == 0 && $this->user->can("add{$this->action['base']}") ) {
						$this->do_sql($this->get_insert_sql($this->action['base']), true);
					}
					if ( $this->action['id'] > 0 && $this->user->can("edit{$this->action['base']}") ) {
						$this->do_sql($this->get_update_sql($this->action['base']), false);
					} 
					if ( isset($_SESSION['tdtrac']['one']) ) {
						$this->json['location'] = $_SESSION['tdtrac']['one'];
					} else {
						$this->json['location'] = "/{$this->action['base']}/";
					} break;
				default:
					$this->json['success'] = false;
					$this->json['msg'] = "Unknown Operation";
					break;
			} 
		} echo json_encode($this->json);
	} // END OUTPUT FUNCTION
	
	/**
	 * General SQL Handler
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool MySQL DEBUG Status
	 * @param string SQL to execute
	 * @param bool Ignore DEBUG flag (i.e. save methods)
	 * @return void Noting
	 */
	private function do_sql($sql, $doalways = false) {
		GLOBAL $db, $MYSQL_PREFIX, $TEST_MODE;
		
		if ( $TEST_MODE && !$doalways ) { 
			$this->json['success'] = true;
			$this->json['msg'] = 'TEST MODE - Nothing Done';
			$this->json['sqlquery'] = $sql;
		} else {
			$result = mysql_query($sql, $db);
			if ( $result ) {
				$this->json['success'] = true;
				$this->json['msg'] = 'Success!';
			} else {
				$this->json['success'] = false;
				$this->json['msg'] = 'Oh oh, Something went wrong!';
				$this->json['sqlerror'] = mysql_error();
			}
		}
	}
	
	
	private function get_insert_sql($type) {
		GLOBAL $MYSQL_PREFIX;
		
		$sql = "NOT FOUND";
		
		switch ( $type ) {
			case "todo":
				$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}todo` ( showid, priority, due, assigned, dscr )";
				$sqlstring .= " VALUES ( '%d', '%d', '%s', '%d', '%s' )";
		
				$sql = sprintf($sqlstring,
					intval($_REQUEST['showid']),
					intval($_REQUEST['prio']),
					make_date($_REQUEST['date']),
					intval($_REQUEST['assign']),
					mysql_real_escape_string($_REQUEST['desc'])
				); break;
		}
		return $sql;
	}
	
	private function get_update_sql($type) {
		GLOBAL $MYSQL_PREFIX;
		
		$sql = "NOT FOUND";
		
		switch ( $type ) {
			case "todo":
				$sqlstring  = "UPDATE `{$MYSQL_PREFIX}todo` SET showid = '%d', priority = '%d', assigned = '%d',";
				$sqlstring .= " dscr = '%s', due = '%s', complete = '%d' WHERE id = '%d'";
			
				$sql = sprintf($sqlstring,
					intval($_REQUEST['showid']),
					intval($_REQUEST['prio']),
					intval($_REQUEST['assign']),
					mysql_real_escape_string($_REQUEST['desc']),
					make_date($_REQUEST['date']),
					intval($_REQUEST['complete']),
					intval($_REQUEST['id'])
				); break;
		}
		return $sql;
	}
}
?>
