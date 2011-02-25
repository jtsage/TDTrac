<?php
/**
 * TDTrac Messaging Functions
 * Data hardened since 1.3.1
 * 
 * Contains all messaging framework
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 1.0.0beta1
 */

/**
 * MAIL Module
 *  Allows viewing of in-system messages.
 * 
 * @package tdtrac
 * @version 2.0.0
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
	private $title = "Shows";
	
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
	 */
	public function output() {
		if ( !$this->output_json ) { // HTML METHODS
			switch ( $this->action['action'] ) {
				case "outbox":
					$this->title .= " :: Outbox";
					$this->html = $this->outbox();
					break;
				case "inbox":
					$this->title .= " :: Inbox";
					$this->html = $this->inbox();
					break;
				case "clear":
					$this->clear();
					break;
				default:
					$this->html = $this->inbox();
					break;
			}
			makePage($this->html, $this->title);
		} else { 
			switch($this->action['action']) {
				case "delete":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
						$this->delete(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				default:
					$this->json['success'] = false;
					break;
			} echo json_encode($this->json);
		}
	} // END OUTPUT FUNCTION
	
	/** 
	 * View outbox
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @return array HTML Output
	 */
	private function outbox() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT id, toid, body, DATE_FORMAT(stamp, '%m-%d-%y %h:%i %p') as wtime FROM {$MYSQL_PREFIX}msg WHERE fromid = {$this->user->id} ORDER BY stamp DESC";
		$result = mysql_query($sql, $db);
		$html[]  = "<h3>Message Outbox</h3>";
		if ( mysql_num_rows($result) < 1 ) { return array_merge($html, array("<p>Outbox is empty</p>")); }
		$tabl = new tdtable("msgoutbox", 'datatable', $this->user->admin);
		$tabl->addHeader(array('Date', 'Recipient', 'Message'));
		if ( $this->user->admin ) { $tabl->addAction('mdel'); }
		while ( $row = mysql_fetch_array($result) ) {
			$tabl->addRow(array($row['wtime'], $this->user->get_name($row['toid']), $row['body']), $row);
		}
		return array_merge($html, $tabl->output(false));
	}

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
		$sql = "SELECT id, fromid, body, DATE_FORMAT(stamp, '%m-%d-%y %h:%i %p') as wtime FROM {$MYSQL_PREFIX}msg WHERE toid = {$this->user->id} ORDER BY stamp DESC";
		$result = mysql_query($sql, $db);
		$html[] = "<h3>Message Inbox</h3>";
		if ( mysql_num_rows($result) < 1 ) { return array_merge($html, array("<p>Inbox is empty</p>")); }
		$html[] = "<span class=\"upright\">[-<a href=\"{$TDTRAC_SITE}mail/clean/\">Clear Inbox</a>-]</span>";
		$tabl = new tdtable("msginbox");
		$tabl->addHeader(array('Date', 'Sender', 'Message'));
		$tabl->addAction('mdel');
		while ( $row = mysql_fetch_array($result) ) {
			$tabl->addRow(array($row['wtime'], $this->user->get_name($row['fromid']), $row['body']), $row);
		}
		return array_merge($html, $tabl->output(false));
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
	 * @return void
	 */
	private function clear() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "DELETE FROM {$MYSQL_PREFIX}msg WHERE toid = {$this->user->id}";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			thrower("Inbox Cleared");
		} else {
			thrower("Inbox Clear :: Operation Failed");
		}
	}
	
}
?>
