<?php
/**
 * TDTrac Show Functions
 * 
 * Contains all show related functions. 
 * Data hardened
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/**
 * SHOWS Module
 *  Allows configuration of shows
 * 
 * @package tdtrac
 * @version 4.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_shows {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Shows";
	
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
					$HEAD_LINK = array('shows/add/', 'plus', 'Add Show'); 
					$this->title .= "::View";
					$this->html = $this->view();
				} else {
					$this->html = error_page("Access Denied :: You Cannot View Shows");
				} break;
		}
		makePage($this->html, $this->title, $this->sidebar());
	} // END OUTPUT FUNCTION

	/**
	 * Show the show add form
	 * 
	 * @global string Site address for links
	 * @return array HTML output
	 */
	private function add_form() {
		GLOBAL $TDTRAC_SITE;
		$form = new tdform(array(
			'action' => "{$TDTRAC_SITE}json/save/base:show/id:0/",
			'id' => 'show-add-form'
		));
		
		$result = $form->addText(array(
			'name' => 'showname',
			'label' => 'Show Name',
			'placeholder' => 'Title of the Show'
		));
		$result = $form->addText(array(
			'name' => 'company',
			'label' => 'Show Company',
			'placeholder' => 'Company or Division Producing Show'
		));
		$result = $form->addText(array(
			'name' => 'venue',
			'label' => 'Show Venue',
			'placeholder' => 'Location of Show'
		));
		$result = $form->addDate(array(
			'name' => 'dates',
			'label' => 'Show Opening',
			'placeholder' => "Opening Date",
			'options' => '{"mode":"calbox"}'
		));
		
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
	
		$sqlstring  = "SELECT `showname`, `company`, `venue`, `dates`, `closed`" .
			" FROM `{$MYSQL_PREFIX}shows`" .
			" WHERE `showid` = %d LIMIT 1";
	
		$sql = sprintf($sqlstring,
			intval($id)
		);
	
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		$form = new tdform(array(
			'action' => "{$TDTRAC_SITE}json/save/base:show/id:{$id}/",
			'id' => "showedit"
		));
		
		$fesult = $form->addText(array(
			'name' => 'showname',
			'label' => 'Show Name',
			'preset' => $row['showname']
		));
		$result = $form->addText(array(
			'name' => 'company',
			'label' => 'Show Company',
			'preset' => $row['company']
		));
		$result = $form->addText(array(
			'name' => 'venue',
			'label' => 'Show Venue',
			'preset' => $row['venue']
		));
		$result = $form->addDate(array(
			'name' => 'dates',
			'label' => 'Show Dates',
			'placeholder' => 'Opening Date',
			'preset' => $row['dates']
		));
		$result = $form->addToggle(array(
			'name' => 'closed',
			'label' => 'Show Record Open',
			'preset' => $row['closed'],
			'options' => array(
				array(1,'Closed'),
				array(0,'Open')
			)
		));
		$result = $form->addHidden('id', $id);
		return array_merge($form->output('Commit'));
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
		$list = new tdlist(array('id' => 'show_view', 'actions' => false, 'inset' => true));
		$showsopen = true;
		
		$list->setFormat(
			"<a data-recid='%d' data-admin='" . ( ( $this->user->admin) ? 1 : 0 ) . 
			"' class='show-menu' href='#'><h3>%s</h3>" .
			"<p><strong>Company:</strong> %s<br /><strong>Venue:</strong> %s<br />" . 
			"<strong>Dates:</strong> %s</p></a>"
		);
		
		$list->addDivide('Open Shows');
		
		while ( $row = mysql_fetch_array($result) ) {
			if ( $showsopen && $row['closed'] == 1 ) {
				$list->addDivide('Closed Shows');
				$showsopen = false;
			}
			$list->addRow(
				array(
					$row['showid'],
					$row['showname'],
					$row['company'],
					$row['venue'],
					$row['dates']
				),
				$row
			);
		}
		return $list->output();
	}
	
	/**
	 * View sidebar of shows
	 * 
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function sidebar() {
		GLOBAL $MYSQL_PREFIX, $TDTRAC_SITE;
		
		$shows_open = get_single(
			"SELECT COUNT(showid) as num FROM `{$MYSQL_PREFIX}shows` WHERE closed = 0"
		);
		$shows_clsd = get_single(
			"SELECT COUNT(showid) as num FROM `{$MYSQL_PREFIX}shows` WHERE closed = 1"
		);
		
		$list = new tdlist(array(
			'id' => 'show_sidebar',
			'actions' => false,
			'inset' => true
		));
		$showsopen = true;
		
		$html = array('<h4 class="intro">Manage Shows and Jobs</h4>');
		
		$list->setFormat("%s");
		$list->addRow(
			"<h3>Open Shows</h3><p>Shows available for new items</p>" .
			"<p class='ui-li-count'>{$shows_open}</p></h3>"
		);
		$list->addRow(
			"<h3>Closed Shows</h3><p>Shows from the past</p>" .
			"<p class='ui-li-count'>{$shows_clsd}</p></h3>"
		);
		if ( $this->action['action'] <> 'add' ) {
			$list->addRaw(
				"<li data-icon='plus'><a href='{$TDTRAC_SITE}shows/add/'>" .
				"<h3>Add Show</h3></a></li>"
			);
		}
		
		return array_merge($html,$list->output());
	}
}
?>
