<?php
/**
 * TDTrac Table Library
 * 
 * Contains the table library
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * TDTable Class
 * @package tdtrac
 */
class tdtable {
	/**
	 * @var array Form Member storage
	 */
	public $members = null;
	/**
	 * @var array Formated HTML
	 */
	private $html = "";
	/**
	 * @var string Name of Form
	 */
	private $tablename = "";
	/**
	 * @var integer Current row index
	 */
	private $currentrow = 0;
	/**
	 * @var bool Use actions
	 */
	private $actions = true;
	/**
	 * @var integer Subtotal on this table element
	 */
	private $subidx = null;
	/**
	 * @var array Running Subtotal (array of doubles.  Total all currency values)
	 */
	private $runsub = null;
	/**
	 * @var array Running Total (array of doubles. Total all currency values)
	 */
	private $runtot = null;
	/**
	 * @var array Header Names
	 */
	private $headers = null;
	/**
	 * @var array Alignment of elements
	 */
	private $align = null;
	/**
	 * @var array Currency Indexes
	 */
	private $currencyidx = null;
	/**
	 * @var bool Need final subtotal
	 */
	private $finalsub = false;
	/**
	 * @var array List of all actions
	 */
	private $actionlist = null;
	/**
	 * @var array List of number only currencies
	 */
	private $numberonly = null;
	/**
	 * @var string Where we come from, for message redirect
	 */
	private $fromlink = false;
	
	/**
	 * Create a new table
	 * 
	 * @param string ID of the table
	 * @param string Class type for the table
	 * @param bool Use action type items
	 */
	public function __construct($id = 'tdtable', $class = 'datatable', $actions = true, $from = false) {
		$this->html[] = "<div id=\"{$id}\">\n";
		$this->html[] = "  <table id=\"{$id}-table\" class=\"{$class}\">\n";
		$this->tablename = $id;
		$this->actions = $actions;
		$this->fromlink = $from;
	}
	
	/**
	 * Ouput the finished table
	 * 
	 * @return string Formatted HTML
	 */
	public function output($string = true) {
		$rhtml = "";
		if ( $this->finalsub ) {
			$this->doSubtotal();
		}
		if ( ! is_null($this->currencyidx) ) {
			$this->doTotal();
		}
		$this->html[] = "</table></div>";

		if ( !$string ) { return $this->html; }

		foreach ( $this->html as $line ) {
			$rhtml .= $line;
		}
		return $rhtml;
	}
	
	/**
	 * Compute and add a subtotal row
	 * 
	 * @return bool True on success
	 */
	private function doSubtotal() {
		$elements = count($this->headers);
		if ( $this->actions ) { $elements++; }
		$thisrow = array_fill(0, $elements, '<span style="display: block; text-align: center">-=-</span>');
		foreach ( $this->currencyidx as $cidx ) {
			$thisrow[$cidx] = "$" . number_format($this->runsub[$cidx], 2);
			if ( $this->numberonly[$cidx] ) { $thisrow[$cidx] = $this->runsub[$cidx]; }
			$this->runsub[$cidx] = 0;
			$thisrow[$cidx] = "<span style=\"display: block; text-align: {$this->align[$cidx]}\">{$thisrow[$cidx]}</span>";
		}
		$thisrow[$this->subidx] = $this->members[$this->currentrow-1][$this->subidx];
		if ( $this->actions ) { $thisrow[$elements-1] = ''; }
		foreach ( $thisrow as $item ) {
			$rhtml .= "<td>{$item}</td>";
		}
		$this->html[] = "<tr class=\"datasubtotal\">{$rhtml}</tr>\n";
		return true;
	}
	
	/**
	 * Compute and add a total row
	 * 
	 * @return bool True on success
	 */
	private function doTotal() {
		$elements = count($this->headers);
		if ( $this->actions ) { $elements++; }
		$thisrow = array_fill(0, $elements, '<span style="display: block; text-align: center">-=-=-</span>');
		foreach ( $this->currencyidx as $cidx ) {
			$thisrow[$cidx] = "$" . number_format($this->runtot[$cidx], 2);
			if ( $this->numberonly[$cidx] ) { $thisrow[$cidx] = $this->runtot[$cidx]; }
			$thisrow[$cidx] = "<span style=\"display: block; text-align: {$this->align[$cidx]}\">{$thisrow[$cidx]}</span>";
		}
		if ( $this->actions ) { $thisrow[$elements-1] = ''; }
		foreach ( $thisrow as $item ) {
			$rhtml .= "<td>{$item}</td>";
		}
		$this->html[] = "<tr class=\"datatotal\">{$rhtml}</tr>\n";
		return true;
	}
	
	/**
	 * Add a list of header names to the table
	 * 
	 * @param array List of strings for headers
	 * @return bool True on success
	 */
	public function addHeader($items = null) {
		$this->headers = $items;
		$this->align = array_fill(0, count($this->headers), "left");
		foreach ( $items as $item ) {
			$thtml .= "<th>{$item}</th>";
		}
		if ( $this->actions ) { $thtml .= "<th>Action</th>"; }
		$this->html[] = "  <tr>{$thtml}</tr>\n";
		return true;
	}
	
	/**
	 * Denote column name for subtotal generation
	 * 
	 * @param string Name of column
	 * @return bool True on success
	 */
	public function addSubtotal($headername) {
		$currentindex = 0;
		foreach ( $this->headers as $testname ) {
			if ( $testname == $headername ) {
				$this->subidx = $currentindex;
				return true;
			} $currentindex++;
		}
		return false;
	}
	
	/**
	 * Denote column contains currency (total it)
	 * 
	 * @param string Name of column
	 * @return int Column number, or false on failure
	 */
	public function addCurrency($headername) {
		$currentindex = 0;
		foreach ( $this->headers as $testname ) {
			if ( $testname == $headername ) {
				$this->currencyidx[] = $currentindex;
				$this->runsub[$currentindex] = 0;
				$this->runtot[$currentindex] = 0;
				$this->align[$currentindex] = "right";
				return $currentindex;
			} $currentindex++;
		}
		return false;
	}
	
	/**
	 * Denote column contains a numbner (total it)
	 * 
	 * @param string Name of column
	 * @return bool True on sucess
	 */
	public function addNumber($headername) {
		$numonly = $this->addCurrency($headername);
		$this->numberonly[$numonly] = True;
		return true;
	}
	
	/**
	 * Change the alignment of a column
	 * 
	 * @param string Name of column
	 * @param string New alignment
	 * @return int Index of column or False on failure
	 */
	public function setAlign($headername, $alignment) {
		$currentindex = 0;
		foreach ( $this->headers as $testname ) {
			if ( $testname == $headername ) {
				$this->align[$currentindex] = $alignment;
				return $currentindex;
			} $currentindex++;
		}
		return false;
	}
	
	/**
	 * Add a row to the table
	 * 
	 * @param array List of items in the row
	 * @param array Raw SQL returned array
	 * @return bool True on sucess
	 */
	public function addRow($row = null, $raw = null, $rowclass = null) {
		if ( is_null($row) ) { return false; }
		if ( ! is_null($this->subidx) ) {
			if ( $this->currentrow > 0 ) {
				if ( $row[$this->subidx] <> $this->members[$this->currentrow-1][$this->subidx] ) {
					$this->finalsub = true;
					$this->doSubtotal();
				}
			}
		}
		$this->members[] = $row;
		$drow = $row;
		if ( !is_null($this->currencyidx) ) {
			foreach ( $this->currencyidx as $cidx ) {
				$this->runsub[$cidx] += $row[$cidx];
				$this->runtot[$cidx] += $row[$cidx];
				if ( ! $this->numberonly[$cidx] ) {
					$drow[$cidx] = number_format($drow[$cidx], 2); 
				}
			}
		}
		foreach ( array_keys($drow) as $item ) {
			$thtml .= "<td style=\"text-align: {$this->align[$item]}\">{$drow[$item]}</td>";
		}
		if ( $this->actions ) { $thtml .= "<td style=\"text-align: right\">" . $this->do_actions($raw) . "</td>"; }
		if ( is_null($rowclass) ) {
			if ( $this->currentrow % 2 == 0 ) {
				$this->html[] = "   <tr class=\"tdtabevn\">{$thtml}</tr>\n";
			} else {
				$this->html[] = "   <tr class=\"tdtabodd\">{$thtml}</tr>\n";
			}
		} else {
			$this->html[] = "   <tr class=\"{$rowclass}\">{$thtml}</tr>\n";
		}
		$this->currentrow++;
		return true;
			
	}
	
	/**
	 * Add an action to each table row
	 * 
	 * @param string Action name (or array of names)
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
	
	/** 
	 * Add the actions to the row (logic)
	 * 
	 * @param array Raw SQL array
	 * @return string Formatted HTML
	 */
	private function do_actions($raw) {
		$rethtml = "";
		foreach ( $this->actionlist as $action ) {
			switch ($action) {
				case "bpend":
					$rethtml .= $this->act_bpend($raw);
					break;
				case "breim":
					$rethtml .= $this->act_breim($raw);
					break;
				case "rview":
					$rethtml .= $this->act_rview($raw);
					break;
				case "bedit":
					$rethtml .= $this->act_bedit($raw);
					break;
				case "bview":
					$rethtml .= $this->act_bview($raw);
					break;
				case "bdel":
					$rethtml .= $this->act_bdel($raw);
					break;
				case "pedit":
					$rethtml .= $this->act_pedit($raw);
					break;
				case "pdel":
					$rethtml .= $this->act_pdel($raw);
					break;
				case "mdel":
					$rethtml .= $this->act_mdel($raw);
					break;
				case "tdone":
					$rethtml .= $this->act_tdone($raw);
					break;
				case "tedit":
					$rethtml .= $this->act_tedit($raw);
					break;
				case "tdel":
					$rethtml .= $this->act_tdel($raw);
					break;
			}
		}
		return $rethtml;
	}
	
	/**
	 * Action: Budget pending items notification
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_bpend($raw) {
		if ( $raw['pending'] ) {
			return "<img class=\"ticon\" src=\"/images/pending.png\" alt=\"Payment Pending\" title=\"Payment Pending\" />";
		} else {
			return "<img class=\"ticon\" src=\"/images/blank.png\" alt=\"Spacer\" />";
		}
	}
	
	/**
	 * Action: Budget reimbursment items notification
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_breim($raw) {
		if ( $raw['needrepay'] ) {
			if ( $raw['gotrepay'] ) {
				return "<img class=\"ticon\" src=\"/images/reim-yes.png\" title=\"Reimbursment Recieved\" alt=\"Reimbursment Recieved\" />";
			} else {
				return "<img class=\"ticon\" src=\"/images/reim-no.png\" title=\"Reimbursment Needed\" alt=\"Reimbursment Needed\" />";
			}
		} else { 
			return "<img class=\"ticon\" src=\"/images/blank.png\" alt=\"Spacer\" />";
		}
	}
	
	/**
	 * Action: Reciept view button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_rview($raw) {
		if ( $raw['imgid'] > 0 ) {
			return "<a href=\"/rcpt.php?imgid={$raw['imgid']}&amp;hires\" target=\"_blank\"><img class=\"ticon\" src=\"/images/rcptview.png\" title=\"View Reciept (new window)\" alt=\"Show Reciept\" /></a>";
		} else { 
			return "<img class=\"ticon\" src=\"/images/blank.png\" alt=\"Spacer\" />";
		}
	}
	
	/**
	 * Action: Budget view item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_bview($raw) {
		global $TDTRAC_SITE;
		
		return "<a href=\"{$TDTRAC_SITE}budget/item/{$raw['id']}/\"><img class=\"ticon\" src=\"/images/view.png\" title=\"View Budget Item Detail\" alt=\"View Item\" /></a>";
	}
	
	/**
	 * Action: Budget edit item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_bedit($raw) {
		global $TDTRAC_SITE;
		if ( $this->fromlink ) { $extra = "&redir-to={$this->fromlink}"; }
		return "<a href=\"{$TDTRAC_SITE}budget/edit/{$raw['id']}/{$extra}\"><img class=\"ticon\" src=\"/images/edit.png\" title=\"Edit Budget Item\" alt=\"Edit Item\" /></a>";
	}
	
	/**
	 * Action: Budget delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_bdel($raw) {
		global $TDTRAC_SITE;
		if ( $this->fromlink ) { $extra = "&redir-to={$this->fromlink}"; }
		return "<a href=\"{$TDTRAC_SITE}/budget/del/{$raw['id']}/{$extra}\"><img class=\"ticon\" src=\"/images/delete.png\" title=\"Delete Budget Item\" alt=\"Delete Item\" /></a>";
	}
	
	/**
	 * Action: Payroll edit item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_pedit($raw) {
		global $TDTRAC_SITE;
		return "<a title=\"Edit Payroll Item\" href=\"{$TDTRAC_SITE}/hours/edit/{$raw['hid']}/\"><img class=\"ticon\" src=\"images/edit.png\" alt=\"Edit\" /></a> ";
	}
	
	/**
	 * Action: Payroll delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_pdel($raw) {
		global $TDTRAC_SITE;
		return "<a title=\"Delete Payroll Item\" href=\"{$TDTRAC_SITE}/hours/del/{$raw['hid']}/\"><img class=\"ticon\" src=\"images/delete.png\" alt=\"Delete\" /></a>";
	}
	
	/**
	 * Action: Message delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_mdel($raw) {
		global $TDTRAC_SITE;
		return "<a title=\"Delete Message\" href=\"{$TDTRAC_SITE}/mail/delete/{$raw['id']}/\"><img class=\"ticon\"  alt=\"Delete\" src=\"/images/delete.png\" /></a>";
	}
	
	/**
	 * Action: Todo delete item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_tdel($raw) {
		global $TDTRAC_SITE;
		if ( $this->fromlink ) { $extra = "&redir-to={$this->fromlink}"; }
		return "<a href=\"{$TDTRAC_SITE}todo/del/{$raw['id']}/{$extra}\"><img class=\"ticon\" src=\"/images/delete.png\" title=\"Delete Todo Item\" alt=\"Delete Item\" /></a>";
	}
	
	/**
	 * Action: Todo edit item button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_tedit($raw) {
		global $TDTRAC_SITE;
		if ( $this->fromlink ) { $extra = "&redir-to={$this->fromlink}"; }
		return "<a href=\"{$TDTRAC_SITE}todo/edit/{$raw['id']}/{$extra}\"><img class=\"ticon\" src=\"/images/edit.png\" title=\"Edit Budget Item\" alt=\"Edit Item\" /></a>";
	}
	
	/**
	 * Action: Todo mark item done button
	 * 
	 * @param array Raw SQL Array
	 * @return string Formatted HTML
	 */
	private function act_tdone($raw) {
		global $TDTRAC_SITE;
		if ( $this->fromlink ) { $extra = "&redir-to={$this->fromlink}"; }
		if ( ! $raw['complete'] ) { return "<a href=\"{$TDTRAC_SITE}todo/done/{$raw['id']}/{$extra}\"><img class=\"ticon\" src=\"/images/check-no.png\" title=\"Mark Todo Item Done\" alt=\"Mark Item\" /></a>"; }
		else { return "<img class=\"ticon\" src=\"/images/check-yes.png\" title=\"Todo Item Done\" alt=\"Item Done\" />"; }
	}
}

?>
