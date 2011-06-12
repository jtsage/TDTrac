<?php
/**
 * TDTrac List Library
 * 
 * Contains the table library
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * TDTable Class
 * @package tdtrac
 */
class tdlist {
	/**
	 * @var array Form Member storage
	 */
	public $items = null;
	/**
	 * @var array Formated HTML
	 */
	private $html = "";
	/**
	 * @var string Name of Form
	 */
	private $options = array();
	/**
	 * @var integer Current row index
	 */
	public $currentrow = 0;
	/**
	 * @var integer Subtotal on this table element
	 */
	private $totals = null;
	/**
	 * @var array Running Subtotal (array of doubles.  Total all currency values)
	 */
	private $actionlist = null;
	/**
	 * @var string Where we come from, for message redirect
	 */
	private $formatsting = false;
	
	/**
	 * Create a new table
	 * 
	 * @param array Passed variables - id, actions, icon, inset
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
	 * @param string Action name (or array of names)
	 * @return void
	 */
	public function addAction($name) {
		if ( is_array($name) ) {
			foreach ($name as $item) {
				$this->actionlist[] = $item;
			}
		} else {
			$this->actionlist[] = $name;
		}
	}
	
	public function setFormat($format = "<p>%s</p>") {
		$this->formatstring = $format;
	}
	
	public function addRow($text, $raw=null, $passed=null) {
		$default = array('numbers' => false, 'noformat' => false, 'theme' => 'c');
		$argus = merge_defaults($default, $passed);
		
		if ( $argus['noformat'] ) {
			$this->items[] = "\t".$text;
		} else {
			$this->items[] = "\t<li data-theme=\"{$argus['theme']}\">" . vsprintf($this->formatstring, $text) . (($this->options['actions'])?$this->do_actions($raw):"") ."</li>";
		}
		$this->currentrow++;
	}
	
	public function addDivide($text, $count = false) {
		$this->addRow("<li data-role='list-divider'>{$text}".(($count!=false)?"<span class='ui-li-count'>{$count}</span>":"")."</li>", null, array('noformat' => true));
	}
	
	public function addRaw($row) {
		$this->addRow($row, null, array('noformat' => true));
	}
	
	public function output() {
		return array_merge(
			array("<ul id='list_{$this->options['id']}' ".($this->options['inset']?'data-inset="true" ':'').($this->options['actions']?"data-split-icon='{$this->options['icon']}' data-split-theme='d'":"")." data-role='listview'>"),
			$this->items,
			array("</ul>"));
	}
	
	private function do_actions($raw) {
		$rethtml = "";
		foreach ( $this->actionlist as $action ) {
			switch ($action) {
				case "bdel":
					$rethtml .= $this->act_bdel($raw);
					break;
				case "badd":
					$rethtml .= $this->act_badd($raw);
					break;
				case "mdel":
					$rethtml .= $this->act_mdel($raw);
					break;
				case "tdone":
					$rethtml .= $this->act_tdone($raw);
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
	 * Action: Budget delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_bdel($raw) {
		return "<a class=\"budg-delete\" data-done=\"0\" data-recid=\"{$raw['showid']}\" href=\"#\">Delete Item</a>";
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
