<?php
/**
 * TDTrac Messaging Functions
 * Data hardened since 1.3.1
 * 
 * Contains all messaging framework
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 1.0.0beta1
 */

/**
 * MAIL Module
 *  Allows viewing of in-system messages.
 * 
 * @package tdtrac
 * @version 4.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_mail {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Mail";
	
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
	}
	
	/**
	 * Output todo list operation
	 * 
	 * @return void
	 * @global bool App in test mode
	 */
	public function output() {
		global $TEST_MODE;
		switch ( $this->action['action'] ) {
			case "inbox":
				$this->title .= "::Inbox";
				$this->html = $this->inbox();
				break;
			default:
				$this->title .= "::Inbox";
				$this->html = $this->inbox();
				break;
		}
		makePage($this->html, $this->title);
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
		$result = mysqli_query($db, $sql);
		
		$list = new tdlist(array(
			'id' => "mail",
			'actions' => true,
			'icon' => 'delete'
		));
		
		if ( mysqli_num_rows($result) < 1) { 
			$list->setFormat("<h3>%s</h3>");
			$list->addRow(array("Inbox is Empty"));
			return $list->output();
		}
		
		$list->setFormat(
			"<a href='#'><h3>%s</h3>" . 
			"<p><strong>Sent By:</strong> %s</p>" . 
			"<span class='ui-li-count'><strong>%s</strong></span></a>"
		);
		$list->addAction("mdel");
		
		while ( $row = mysqli_fetch_array($result) ) {
			$list->addRow(
				array(
					$row['body'],
					$this->user->get_name($row['fromid']),
					$row['wtime']
				),
				$row
			);
		}
		return array_merge(
			$list->output(),
			array("<br /><br /><a href='#' id='mailClear' data-role='button' data-theme='d'>Clear Inbox</a>")
		);
	}
	
}
?>
