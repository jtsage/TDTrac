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
	private $listname = "";
	/**
	 * @var integer Current row index
	 */
	public $currentrow = 0;
	/**
	 * @var bool Use actions
	 */
	private $actions = true;
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
	 * @var string Icon for single action
	 */
	private $iconname = 'gear';
	
	/**
	 * Create a new table
	 * 
	 * @param string ID of the table
	 * @param string Class type for the table
	 * @param bool Use action type items
	 * @return object
	 */
	public function __construct($id = 'td_list', $actions = true, $icon = 'delete') {
		$this->listname = $id;
		$this->actions = $actions;
		$this->iconname = $icon;
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
	
	public function addRow($row, $raw = false, $numbers = false, $noformat = false, $style = 'c') {
		if ( $noformat ) {
			$this->items[] = "\t".$row;
		} else {
			$this->items[] = "\t<li data-theme=\"{$style}\">" . vsprintf($this->formatstring, $row) . (($this->actions)?$this->do_actions($raw):"") ."</li>";
		}
		$this->currentrow++;
	}
	
	public function addDivide($text, $theme='c') {
		$this->addRow("<li data-role='list-divider' data-theme='{$theme}'>{$text}</li>", null, null, true);
	}
	
	public function addRaw($row) {
		$this->addRow($row, null, null, true);
	}
	
	public function output() {
		return array_merge(
			array("<ul id=\"list_{$this->listname}\" data-split-icon=\"{$this->iconname}\" data-split-theme=\"d\" data-role=\"listview\">"),
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
				case "sdel":
					$rethtml .= $this->act_sdel($raw);
					break;
				case "mdel":
					$rethtml .= $this->act_mdel($raw);
					break;
				case "tdel":
					$rethtml .= $this->act_tdel($raw);
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
	 * Action: Show delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_sdel($raw) {
		return "<a class=\"show-delete\" data-done=\"0\" data-recid=\"{$raw['showid']}\" href=\"#\">Delete Show</a>";
	}
	
	/**
	 * Action: Todo delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_tdel($raw) {
		return "<a class=\"todo-delete\" data-done=\"0\" data-recid=\"{$raw['id']}\" href=\"#\">Delete Item</a>";
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
