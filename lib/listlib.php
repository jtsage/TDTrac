<?php
/**
 * TDTrac List Library
 * 
 * Contains the list library
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * TDList Class
 * @package tdtrac
 */
class tdlist {
	/**
	 * @var array List Member storage
	 */
	public $items = null;
	/**
	 * @var array Formated HTML
	 */
	private $html = "";
	/**
	 * @var array Various list options
	 */
	private $options = array();
	/**
	 * @var integer Current row index
	 */
	public $currentrow = 0;
	/**
	 * @var array Action list for view
	 */
	private $actionlist = null;
	/**
	 * @var string Row output, sprintf format.
	 */
	private $formatsting = false;
	
	/**
	 * Create a new list
	 * 
	 * Options:
	 * 	'id'      => List ID
	 * 	'actions' => Bool, use split list action
	 *  'icon'    => Icon for split list action
	 *  'inset'   => Inset mode
	 * 
	 * @param array Array of named options
	 * @return object
	 */
	public function __construct($passed) {
		$default = array( 'id' => 'td_list', 'actions' => false, 'icon' => 'delete', 'inset' => false );
		$this->options = merge_defaults($default, $passed);
		$this->options['id'] = preg_replace('/ /', '', $this->options['id']);
	}
	
	/**
	 * Add an action to each table row
	 * 
	 * @param mixed Action name / array of names
	 * @return void
	 */
	public function clearAction() {
		$this->actionlist = null;
	}
	public function addAction($name) {
		if ( is_array($name) ) {
			foreach ($name as $item) {
				$this->actionlist[] = $item;
			}
		} else {
			$this->actionlist[] = $name;
		}
	}
	
	/** 
	 * Set the format of each list row
	 * 
	 * @param string vsprintf Format
	 * @return null
	 */
	public function setFormat($format = "<p>%s</p>") {
		$this->formatstring = $format;
	}
	
	/**
	 * Add a row to the list
	 * 
	 * Options:
	 *  'noformat' => Bool, output row as-is, don't sprintf
	 *  'theme'    => Theme for row
	 *  'numbers'  => Presumably math related, does nothing right now.
	 * 
	 * @param array Array of values to fill in format
	 * @param array Raw SQL array of data (for actions)
	 * @param array Options array
	 * @return null
	 */
	public function addRow($text, $raw=null, $passed=null) {
		$default = array('numbers' => false, 'noformat' => false, 'theme' => 'a');
		$argus = merge_defaults($default, $passed);
		
		if ( $argus['noformat'] ) {
			$this->items[] = "\t".$text;
		} else {
			$this->items[] = "\t<li data-theme=\"{$argus['theme']}\">" . vsprintf($this->formatstring, $text) . (($this->options['actions'])?$this->do_actions($raw):"") ."</li>";
		}
		$this->currentrow++;
	}
	
	/**
	 * Add a list divider
	 * 
	 * @param string Text for divider
	 * @param string Text for count bubble, or false
	 * @return null
	 */
	public function addDivide($text, $count = false) {
		$this->addRow("<li data-role='list-divider'>{$text}".(($count!=false)?"<span class='ui-li-count'>{$count}</span>":"")."</li>", null, array('noformat' => true));
	}
	
	/**
	 * Output raw data as a row
	 * 
	 * @param string Text to add as a row
	 * @return null
	 */
	public function addRaw($row) {
		$this->addRow($row, null, array('noformat' => true));
	}
	
	/**
	 * Return the formatted list
	 * 
	 * @return array Formatted HTML
	 */
	public function output() {
		return array_merge(
			array("<ul data-divider-theme='b' id='list_{$this->options['id']}' ".($this->options['inset']?'data-inset="true" ':'').($this->options['actions']?"data-split-icon='{$this->options['icon']}' data-split-theme='a'":"")." data-role='listview'>"),
			$this->items,
			array("</ul>"));
	}
	
	/** 
	 * Run actions
	 * 
	 * @param array Raw SQL array
	 * @return string Formatted HTML
	 */
	private function do_actions($raw) {
		$rethtml = "";
		foreach ( $this->actionlist as $action ) {
			switch ($action) {
				case "badd":
					$rethtml .= $this->act_badd($raw);
					break;
				case "mdel":
					$rethtml .= $this->act_mdel($raw);
					break;
				case "tdone":
					$rethtml .= $this->act_tdone($raw);
					break;
				case "hclear":
					$rethtml .= $this->act_hclear($raw);
					break;
				case "hmark":
					$rethtml .= $this->act_hmark($raw);
					break;
			}
		}
		return $rethtml;
	}
	
	/**
	 * Action: Budget add item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_badd($raw) {
		return "<a class='budg-add' href='/budget/add/show:{$raw['showid']}/'>Add Budget Item</a>";
	}
	
	/**
	 * Action: Hours Clear User button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_hclear($raw) {
		return "<a class=\"hours-clear\" data-done=\"0\" data-recid=\"{$raw['userid']}\" href=\"#\">Mark Hours Submitted</a>";
	}
	
	/**
	 * Action: Hours Mark Done User button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_hmark($raw) {
		return "<a class=\"hours-mark\" data-done=\"0\" data-recid=\"{$raw['id']}\" href=\"#\">Mark Hours Submitted</a>";
	}
	
	/**
	 * Action: Todo delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_tdone($raw) {
		return "<a class=\"todo-done\" data-done=\"{$raw['complete']}\" data-recid=\"{$raw['id']}\" href=\"#\">Mark Item Finished</a>";
	}
	
	/**
	 * Action: Message delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_mdel($raw) {
		return "<a class=\"msg-delete\" data-done=\"0\" data-recid=\"{$raw['id']}\" href=\"#\">Delete Message</a>";
	}
	
}
