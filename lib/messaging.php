<?php
/**
 * TDTrac Messaging Functions
 * Data hardened since 1.3.1
 * 
 * Contains all messaging framework
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 1.0.0beta1
 */

/**
 * MAIL Module
 *  Allows viewing of in-system messages.
 * 
 * @package tdtrac
 * @version 3.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_mail {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var bool Output format (TURE = json, FALSE = html) */
	private $output_json = false;
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Mail";
	
	/** @var array JSON Data */
	private $json = array();
	
	/** 
	 * Create a new instance of the TO-DO module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Mail Object
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
	 * @global bool App in test mode
	 */
	public function output() {
		global $TEST_MODE;
		if ( !$this->output_json ) { // HTML METHODS
			switch ( $this->action['action'] ) {
				case "inbox":
					$this->title .= "::Inbox";
					$this->html = $this->inbox();
					break;
				case "clear":
					$this->clear();
					break;
				default:
					$this->title .= "::Inbox";
					$this->html = $this->inbox();
					break;
			}
			makePage($this->html, $this->title);
		} else { 
			switch($this->action['action']) {
				case "delete":
					if ( $TEST_MODE ) {
						$this->json['success'] = true;
					} else {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->delete(intval($this->action['id']));
						} else {
							$this->json['success'] = false;
						}
					} break;
				default:
					$this->json['success'] = false;
					break;
			} echo json_encode($this->json);
		}
	} // END OUTPUT FUNCTION

	/** 
	 * View inbox
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @return array HTML Output
	 */
	private function inbox() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT id, fromid, body, DATE_FORMAT(stamp, '%m-%d-%Y') as wtime FROM {$MYSQL_PREFIX}msg WHERE toid = {$this->user->id} ORDER BY stamp DESC";
		$result = mysql_query($sql, $db);
		
		$list = new tdlist(array('id' => "mail", 'actions' => true, 'icon' => 'delete'));
		
		if ( mysql_num_rows($result) < 1) { 
			$list->setFormat("<h3>%s</h3>");
			$list->addRow(array("Inbox is Empty"));
			return $list->output();
		}
		
		$list->setFormat("<a href='#'></a><h3>%s</h3><p><strong>Sent By:</strong> %s</p><span class='ui-li-count'><strong>%s</strong></span>");
		$list->addAction("mdel");
		
		while ( $row = mysql_fetch_array($result) ) {
			$list->addRow(array($row['body'], $this->user->get_name($row['fromid']), $row['wtime']), $row);
		}
		return array_merge($list->output(), array("<br /><br /><a href='{$TDTRAC_SITE}mail/clear/' data-role='button' data-theme='f'>Clear Inbox</a>"));
	}

	/** 
	 * Remove a message form the datebase
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer Message ID to remove
	 * @return void
	 */
	private function delete($msgid) {
		GLOBAL $db, $MYSQL_PREFIX;
		
		$sql = "SELECT toid FROM `{$MYSQL_PREFIX}msg` WHERE id = {$msgid}";
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		if ( $row['toid'] == $this->user->id || $this->user->admin ) { 
			$dsql = "DELETE FROM `{$MYSQL_PREFIX}msg` WHERE id = {$msgid} LIMIT 1";
			$result = mysql_query($dsql, $db);
			if ( $result ) {
				$this->json['success'] = true;
			} else {
				$this->json['success'] = false;
			}
		} else {
			$this->json['success'] = false;
		}
	}

	/** 
	 * Clear inbox
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool App In Test Mode
	 * @return void
	 */
	private function clear() {
		GLOBAL $db, $MYSQL_PREFIX, $TEST_MODE;
		if ( $TEST_MODE ) {
			thrower("Inbox Cleared");
		} else {
			$sql = "DELETE FROM {$MYSQL_PREFIX}msg WHERE toid = {$this->user->id}";
			$result = mysql_query($sql, $db);
			if ( $result ) {
				thrower("Inbox Cleared");
			} else {
				thrower("Inbox Clear :: Operation Failed");
			}
		}
	}
	
}
?>
