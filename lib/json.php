<?php
/**
 * TDTrac JSON Functions
 * 
 * Contains the json functions (all)
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 3.0.0
 */

/**
 * JSON Module
 *  Allows per-user and per-show task lists
 * 
 * @package tdtrac
 * @version 4.0.0
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
		global $HEAD_LINK, $CANCEL, $TEST_MODE, $MYSQL_PREFIX, $db, $TDTRAC_SITE;
		$this->json['success'] = false; $this->json['msg'] = "Unknown Error";
		
		if ( !isset($this->action['base']) || !isset($this->action['action']) || ! is_numeric($this->action['id']) ) {
			$this->json['success'] = false;
			$this->json['msg'] = "Poorly Formed Request";
		} elseif ( ( ! in_array($this->action['base'], array('todo','show','hours','budget','msg','admin')) ) && $this->action['action'] != 'help' ) {
			$this->json['success'] = false;
			$this->json['msg'] = "Bad Base Module Name";
		} else {
			switch($this->action['action']) {
				case "delete":
					if ( ! $this->user->can("edit{$this->action['base']}") && ! $this->action['base'] == 'msg' ) {
						$this->json['success'] = false;
						$this->json['msg'] = "Permission Denied!";
					} else {
						switch($this->action['base']) {
							case "todo":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}todo` WHERE id = ".intval($this->action['id'])." LIMIT 1");
								break;
							case "hours":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}hours` WHERE id = ".intval($this->action['id'])." LIMIT 1");
								if ( isset($_SESSION['tdtrac']['one']) ) {
									$this->json['location'] = $_SESSION['tdtrac']['one'];
								} else {
									$this->json['location'] = "{$TDTRAC_SITE}{$this->action['base']}/";
								} break;
							case "budget":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}budget` WHERE id = ".intval($this->action['id'])." LIMIT 1");
								break;
							case "msg":
								$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}msg` WHERE id = ".intval($this->action['id'])." AND toid = {$this->user->id} LIMIT 1");
								break;
							case "show":
								if ( $this->user->admin ) {
									$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}todo` WHERE showid = ".intval($this->action['id']));
									$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}hours` WHERE showid = ".intval($this->action['id']));
									$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}budget` WHERE showid = ".intval($this->action['id']));
									$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}shows` WHERE showid = ".intval($this->action['id']));
									$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}rcpts` WHERE imgid NOT IN (SELECT imgid FROM `{$MYSQL_PREFIX}budget`) AND handled = 1");
									$this->json['success'] = true;
									$this->json['msg'] = 'Show Deleted';
								} else {
									$this->json['success'] = false;
									$this->json['msg'] = "Permission Denied!";
								}
								break;
							default:
								$this->json['msg'] = "Invalid Action";
								break;
						}
					} break;
				case "mark":
					if ( $this->user->can("edittodo") && $this->action['base'] == 'todo' ) {
						$this->do_sql("UPDATE `{$MYSQL_PREFIX}todo` SET complete = 1 WHERE id = ".intval($this->action['id'])." LIMIT 1");
					} elseif ( $this->user->can("edithours") && $this->action['base'] == 'hours' ) {
						$this->do_sql("UPDATE `{$MYSQL_PREFIX}hours` SET submitted = 1 WHERE id = ".intval($this->action['id'])." LIMIT 1");
					} else {
						$this->json['success'] = false;
						$this->json['msg'] = "Permission Denied!";
					} break;
				case "email":
					if ( ! $this->user->can("view{$this->action['base']}") ) {
						$this->json['success'] = false;
						$this->json['msg'] = "Permission Denied!";
					} else {
						switch($this->action['base']) {
							case "todo":
								$mod = new tdtrac_todo($this->user, $this->action);
								$this->json['success'] = $mod->email();
								break;
							case "budget":
								$mod = new tdtrac_budget($this->user, $this->action);
								$this->json['success'] = $mod->email($this->action['id']);
								break;
							case "hours":
								$mod = new tdtrac_hours($this->user, $this->action);
								if ( $this->action['type'] == 'remind' ) {
									$this->json['location'] = $TDTRAC_SITE.'hours/';
									$this->json['success'] = $mod->remind_send();
								} elseif ( $this->action['type'] == 'unpaid' ) {
									$this->json['success'] = $mod->email();
								} else {
									$this->json['msg'] = "Report not found";
								} break;
							default:
								$this->json['msg'] = "Report not found";
								break;
						}
						if ( $this->json['success'] == true ) { 
							$this->json['msg'] = "E-Mail Sent";
						} else {
							$this->json['msg'] = "E-Mail Failed to Send";
						}
					} break;
				case "save":
					$done = false;
					if ( $this->action['id'] == 0 && $this->user->can("add{$this->action['base']}") ) {
						$done = true;
						$this->do_sql($this->get_insert_sql($this->action['base']), true);
						if ( $this->action['base'] == 'hours' ) {
							$this->send_hours_add();
						}
					}
					if ( $this->action['id'] > 0 && $this->user->can("edit{$this->action['base']}") ) {
						$this->do_sql($this->get_update_sql($this->action['base']), false);
					}
					if ( $this->action['id'] == 0 && $this->user->isemp && $this->action['base'] == 'hours' && !$done ) {
						$this->do_sql($this->get_insert_sql('hours'), true);
						$this->send_hours_add();
					}
					if ( isset($_SESSION['tdtrac']['one']) ) {
						$this->json['location'] = $_SESSION['tdtrac']['one'];
					} else {
						$this->json['location'] = $TDTRAC_SITE."{$this->action['base']}/";
					} break;
				case "clear":
					switch ( $this->action['base'] ) {
						case 'msg':
							$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}msg` WHERE toid = {$this->user->id}");
							$this->json['location'] = $TDTRAC_SITE;
							break;
						case 'hours':
							$this->do_sql("UPDATE `{$MYSQL_PREFIX}hours` SET submitted = 1 WHERE userid = ".intval($this->action['id']));
							break;
					} break;
				case "nav":
					switch ( $this->action['base'] ) {
						case 'hours':
							$this->json['success'] = true;
							$this->json['msg'] = "Data loaded";
							$this->json['location'] = $TDTRAC_SITE."hours/view/type:search/listtype:" . $_POST['listtype'] . "/start:" . $_POST['start'] . "/end:" . $_POST['end'] . "/";
							break;
					} break;
				case "adm":
					if ( ! $this->user->admin || ! isset($this->action['sub']) ) {
						$this->json['msg'] = "Permission Denied";
					} else {
						switch ( $this->action['sub'] ) {
							case "saveuser":
								$this->json['location'] = $TDTRAC_SITE."admin/users/";
								if ( $this->action['id'] == 0 ) {
									$this->do_sql($this->get_insert_sql('user'), true);
									$sql = sprintf("INSERT INTO `{$MYSQL_PREFIX}usergroups` ( `userid`, `groupid` ) VALUES ( %d, %d )",
										mysql_insert_id($db),
										intval($_REQUEST['groupid'])
									);
									$this->do_sql($sql, true);
								} else {
									$this->do_sql($this->get_update_sql('user'));
								} break;
							case "deletegroup":
								$this->json['location'] = $TDTRAC_SITE."admin/groups/";
								if ( $this->action['id'] > 99 ) {
									$this->do_sql("DELETE FROM `{$MYSQL_PREFIX}groupnames` WHERE groupid = ".intval($this->action['id'])." LIMIT 1");
								} else {
									$this->json['msg'] = "Invalid group to delete";
								} break;
							case "savegroup":
								$this->json['location'] = $TDTRAC_SITE."admin/groups/";
								if ( $this->action['id'] == 0 ) {
									$this->do_sql($this->get_insert_sql('group'), true);
								} elseif ( $this->action['id'] == 1 ) {
									$this->json['msg'] = "Cannot rename special group 1 (admin)";
								} else {
									$this->do_sql($this->get_update_sql('group'));
								} break;
							case "saveperms":
								$this->json['location'] = $TDTRAC_SITE."admin/groups/";
								$this->do_sql($this->get_update_sql('perm'),true);
								break;
							case "savemailcode":
								$this->json['location'] = $TDTRAC_SITE."admin/";
								$this->do_sql($this->get_update_sql('mailcode'));
								break;
							case "toggle":
								if ( !isset($this->action['switch']) || empty($this->action['switch']) ) {
									$this->json['msg'] = "No switch found";
								} elseif ( in_array($this->action['switch'], array('limithours','notify','payroll','active') ) ) {
									$current = get_single("SELECT {$this->action['switch']} AS num FROM `{$MYSQL_PREFIX}users` WHERE userid = ".intval($this->action['id']));
									$this->json['newval'] = (($current==1)?0:1);
									$this->do_sql("UPDATE {$MYSQL_PREFIX}users SET {$this->action['switch']} = ".(($current==1)?0:1)." WHERE userid = ".intval($this->action['id'])." LIMIT 1", true);
									break;
								} else {
									$this->json['msg'] = "Invalid Switch";
								} break;
							}
					} break;
				case "help":
					/** Library: Help Text */
					require_once("helpnodes.php");
					$this->json['success'] = true;
					if ( ! isset($helpnode[$this->action['base']][$this->action['sub']]) ) {
						$this->json['helptitle'] = $helpnode['error']['title'];
						$this->json['helpbody'] = $helpnode['error']['data'];
						$this->json['msg'] = "Help Not Found";
					} else {
						$this->json['helptitle'] = $helpnode[$this->action['base']][$this->action['sub']]['title'];
						$this->json['helpbody'] = $helpnode[$this->action['base']][$this->action['sub']]['data'];
						$this->json['msg'] = "Help Found";
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
	 * @global bool MySQL DEBUG Status
	 * @param string SQL to execute
	 * @param bool Ignore DEBUG flag (i.e. add methods)
	 * @return void Noting
	 */
	private function do_sql($sql, $doalways = false) {
		GLOBAL $db, $TEST_MODE, $TEST_MODE_STOPISQL, $TEST_MODE_STOPUDSQL;
		
		$results = array();
		$errors = array();
		
		if ( !is_array($sql) ) { $sql = array($sql); }
		
		if ( $TEST_MODE ) {
			if ( is_array($this->json['sqlquery']) ) {
				$this->json['sqlquery'][] = $sql;
			} else {
				$this->json['sqlquery'] = array($sql);
			}
		}
		
		if ( $TEST_MODE && ( $TEST_MODE_STOPISQL || ( $TEST_MODE_STOPUDSQL && !$doalways ) ) ) { 
			$this->json['success'] = true;
			$this->json['msg'] = 'TEST MODE - Nothing Done';
		} else {
			foreach ( $sql as $eachsql ) {
				if ( ! (mysql_query($eachsql, $db)) ) {
					$errors[] = mysql_error();
					$results[] = false;
				} else {
					$errors[] = '';
					$results[] = true;
				}
			} 
			
			$this->json['msg'] = 'Success';
			$this->json['success'] = true;
			
			if ( $TEST_MODE ) {
				$this->json['sqlerrors'] = $errors;
				$this->json['sqlresults'] = $results;
			}
			
			foreach ( $results as $result ) {
				if ( ! $result ) {
					$this->json['success'] = false;
					$this->json['msg'] = 'Oh oh, Something went wrong!';
				}
			}
		}
	}
	
	/**
	 * Logic to generate INSERT queries
	 * 
	 * @global string MySQL Table Prefix
	 * @params string Type of SQL Query
	 * @return mixed One of more SQL Queries
	 */
	private function get_insert_sql($type) {
		GLOBAL $MYSQL_PREFIX, $TDTRAC_PAYRATE;
		
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
			case "show":
				$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}shows` ( showname, company, venue, dates )";
				$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s' )";
				
				$sql = sprintf($sqlstring,
					mysql_real_escape_string($_REQUEST['showname']),
					mysql_real_escape_string($_REQUEST['company']),
					mysql_real_escape_string($_REQUEST['venue']),
					mysql_real_escape_string($_REQUEST['dates'])
				); break;
			case "group":
				$sql = sprintf("INSERT INTO {$MYSQL_PREFIX}groupnames (groupname) VALUES ('%s')",
					mysql_real_escape_string($this->action['newname'])
				); break;
			case "user":
				$sql = array();
				$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}users` ( `username`, `first`, `last`, `password`, `phone`, `email`, `payrate` )";
				$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s', '%d', '%s', '%f' )";
				
				$sql = sprintf($sqlstring,
					mysql_real_escape_string($_REQUEST['username']),
					mysql_real_escape_string($_REQUEST['first']),
					mysql_real_escape_string($_REQUEST['last']),
					mysql_real_escape_string($_REQUEST['password']),
					intval($_REQUEST['phone']),
					mysql_real_escape_string($_REQUEST['email']),
					$TDTRAC_PAYRATE
				); break;
			case "budget":
				$sql = array();
				$rcptid = ( $_REQUEST['rcptid'] > 0 && is_numeric($_REQUEST['rcptid'])) ? $_REQUEST['rcptid'] : 0;
				$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}budget` ";
				$sqlstring .= "( showid, price, tax, imgid, vendor, category, dscr, date, pending, needrepay, gotrepay, payto )";
				$sqlstring .= " VALUES ( '%d','%f','%f','%d','%s','%s','%s','%s','%d','%d','%d', '%d' )";
			
				$sql = sprintf($sqlstring,
					intval($_REQUEST['showid']),
					floatval($_REQUEST['price']),
					(($_REQUEST['tax'] > 0 && is_numeric($_REQUEST['tax'])) ? $_REQUEST['tax'] : 0 ),
					intval($rcptid),
					mysql_real_escape_string($_REQUEST['vendor']),
					mysql_real_escape_string($_REQUEST['category']),
					mysql_real_escape_string($_REQUEST['dscr']),
					make_date($_REQUEST['date']),
					intval($_REQUEST['pending']),
					(($_REQUEST['repay'] == "yes" || $_REQUEST['repay'] == 'paid' ) ? "1" : "0"),
					(($_REQUEST['repay'] == "paid") ? "1" : "0"),
					intval($_REQUEST['payto'])
				);
				
				if ( $rcptid > 0 ) {
					$sql[] = "UPDATE {$MYSQL_PREFIX}rcpts SET handled = '1' WHERE imgid = '{$rcptid}'";
				} break;
			case "hours":
				$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}hours` ( `userid`, `showid`, `date`, `worked`, `note` )";
				$sqlstring .= " VALUES ( %d, %d, '%s', '%f', '%s' )";
				
				$sql = sprintf($sqlstring,
					intval($_REQUEST['userid']),
					intval($_REQUEST['showid']),
					make_date($_REQUEST['date']),
					floatval($_REQUEST['worked']),
					mysql_real_escape_string($_REQUEST['note'])
				); break;
		}
		return $sql;
	}
	
	/**
	 * Logic to generate UPDATE queries
	 * 
	 * @global string MySQL Table Prefix
	 * @params string Type of SQL Query
	 * @return mixed One of more SQL Queries
	 */
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
			case "show":
				$sqlstring  = "UPDATE `{$MYSQL_PREFIX}shows` SET showname='%s', company='%s', venue='%s', dates='%s',";
				$sqlstring .= " closed=%d WHERE showid = %d";
			
				$sql = sprintf($sqlstring,
					mysql_real_escape_string($_REQUEST['showname']),
					mysql_real_escape_string($_REQUEST['company']),
					mysql_real_escape_string($_REQUEST['venue']),
					mysql_real_escape_string($_REQUEST['dates']),
					intval($_REQUEST['closed']),
					intval($_REQUEST['id'])
				); break;
			case "group":
				$sql = sprintf("UPDATE `{$MYSQL_PREFIX}groupnames` SET groupname = '%s' WHERE groupid = %d",
					mysql_real_escape_string($this->action['newname']),
					intval($this->action['id'])
				); break;
			case "mailcode":
				$sql = sprintf("UPDATE tdtracmail SET code = '%s', email = '%s' WHERE prefix = '{$MYSQL_PREFIX}'",
					mysql_real_escape_string($_REQUEST['code']),
					mysql_real_escape_string($_REQUEST['email'])
				); break;
			case "perm":
				$sql = array();
				$sql[] = "DELETE FROM `{$MYSQL_PREFIX}permissions` WHERE groupid = ".intval($this->action['id']);
				foreach ( array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "addtodo", "edittodo", "viewtodo") as $perm ) {
					$sql[] = sprintf("INSERT INTO `{$MYSQL_PREFIX}permissions` (groupid, permid, permcan) VALUES (%d, '%s', %d)",
						intval($this->action['id']),
						$perm,
						(($_REQUEST[$perm]) ? "1" : "0")
					);
				} break;
			case "user":
				$sql = array();
				$sqlstring  = "UPDATE `{$MYSQL_PREFIX}users` SET `password` = '%s', `username` = '%s', `last` = '%s', `first` = '%s',";
				$sqlstring .= " `phone` = '%d', `email` = '%s', `payrate` = '%f'  WHERE `userid` = %d LIMIT 1";
				
				$sql[] = sprintf($sqlstring,
					mysql_real_escape_string($_REQUEST['password']),
					mysql_real_escape_string($_REQUEST['username']),
					mysql_real_escape_string($_REQUEST['last']),
					mysql_real_escape_string($_REQUEST['first']),
					intval($_REQUEST['phone']),
					mysql_real_escape_string($_REQUEST['email']),
					floatval($_REQUEST['payrate']),
					intval($_REQUEST['id'])
				);
				
				$sql[] = sprintf("UPDATE `{$MYSQL_PREFIX}usergroups` SET groupid = %d WHERE userid = %d",
					intval($_REQUEST['groupid']),
					intval($_REQUEST['id'])
				); break;
			case "budget":
				$sqlstring  = "UPDATE `{$MYSQL_PREFIX}budget` SET showid = '%d', price = '%f', tax = '%f' , vendor = '%s', ";
				$sqlstring .= "category = '%s', dscr = '%s' , date = '%s', pending = '%d', needrepay = '%d', gotrepay = '%d', payto = '%d'";
				$sqlstring .= " WHERE id = %d";
				
				$sql = sprintf($sqlstring,
					intval($_REQUEST['showid']),
					floatval($_REQUEST['price']),
					floatval($_REQUEST['tax']),
					mysql_real_escape_string($_REQUEST['vendor']),
					mysql_real_escape_string($_REQUEST['category']),
					mysql_real_escape_string($_REQUEST['dscr']),
					make_date($_REQUEST['date']),
					intval($_REQUEST['pending']),
					(($_REQUEST['repay'] == "yes" || $_REQUEST['repay'] == 'paid' ) ? "1" : "0"),
					(($_REQUEST['repay'] == "paid") ? "1" : "0"),
					intval($_REQUEST['payto']),
					intval($_REQUEST['id'])
				); break;
			case "hours":
				$sqlstring  = "UPDATE `{$MYSQL_PREFIX}hours` SET `showid` = %d, `date` = '%s', `worked` = '%f',";
				$sqlstring .= " submitted = %d, `note` = '%s' WHERE id = %d";
				
				$sql = sprintf($sqlstring,
					intval($_REQUEST['showid']),
					make_date($_REQUEST['date']),
					floatval($_REQUEST['worked']),
					intval($_REQUEST['submitted']),
					mysql_real_escape_string($_REQUEST['note']),
					intval($_REQUEST['id'])
				); break;
		}
		return $sql;
	}
	
	/**
	 * Logic to send payroll msg reports
	 * 
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function send_hours_add() {
		GLOBAL $MYSQL_PREFIX;
		
		$sql = array();
		
		$mailmessage = sprintf("%s Added Payroll: %f for %s (%s)",
			$this->user->name,
			number_format(floatval($_REQUEST['worked']),2),
			mysql_real_escape_string($_REQUEST['date']),
			$this->user->get_name(intval($_REQUEST['userid']))
		);
		
		$mail_sql_str  = "INSERT INTO `{$MYSQL_PREFIX}msg` ( toid, fromid, body ) VALUES ( %d, %d, '%s' )";
				
		if ( $this->user->id == intval($_REQUEST['userid']) ) { // ADDING FOR SELF, NOTIFY WHERE `notify`
			if ( $this->user->isemp ) { // BUT ONLY FOR LIMITED ACCOUNTS
				$users_to_notify_sql = "SELECT userid FROM `{$MYSQL_PREFIX}users` WHERE notify = 1";
				$users_to_notify_res = mysql_query($users_to_notify_sql, $db);
				while ( $row = mysql_fetch_array($users_to_notify_res) ) {
					$sql[] = sprintf($mail_sql_str,	$row['userid'], $this->user->id, $mailmessage );
				}
			}
		} else { // ADDING FOR OTHERS, NOTIFY RECIPIENT ONLY
			$sql[] = sprintf($mail_sql_str,
				intval($_REQUEST['userid']),
				$this->user->id,
				$mailmessage );
		}
		
		if ( !empty($sql) ) {
			$this->do_sql($sql, true);
		}
	}
	
}
?>
