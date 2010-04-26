<?php
/*
	$myform = new tdform("./", "form1"); // (ACTION, FORM ID, TABINDEX START, ENCASING DIV)
	 
	// General Type:
		bool = $myform->add<Type>(NAME, TEXT, TITLE, VALUE);
	// Drop Type:
		bool = $myform->addDrop(NAME, TEXT, TITLE, CONTENTS, bool AllowNew, SELECTED);
	$result = $myform->addDate('date', 'Date', 'Date of Charge');
	$result = $myform->addDrop('vendor', 'Vendor', 'Name of Vendor', array('Lowes', 'Home Depot', 'Radio Shack'));
	$result = $myform->addText('desc', 'Description');
	$result = $myform->addMoney('price', 'Price', 'Total Amount');
	$result = $myform->addHidden('howdy', 'ho');
	$result = $myform->addCheck('checker', 'Pending Payment', null, True);
	$result = $myform->addCheck('checkera', 'Not Pending Payment');
	
	echo $myform->output("Add Expense"); // Submit Button Name
	echo $myform->getmembers();
*/

class tdform {
	public $members = "";
	private $html = "";
	private $formname = "";
	private $tabindex = 0;
	private $hidden = "";
	
	public function __construct($action = null, $id = 'genform', $tab = 1, $div = 'genform') {
		$this->html[] = "<div id=\"{$div}\"><form method=\"POST\" action=\"{$action}\" name=\"{$id}\">\n";
		$this->formname = $id;
		$this->tabindex = $tab;
	}
	
	public function output($actioname = 'Submit') {
		$output = "";
		foreach ($this->html as $line) {
			$output .= $line;
		}
		$output .= "  <div class=\"frmele\">";
		if ( is_array($this->hidden) ) {
			foreach( $this->hidden as $hide) {
				$output .= "<input type=\"hidden\" name=\"{$hide[0]}\" value=\"{$hide[1]}\" />";
			}
		}
		$output .= "<input type=\"submit\" value=\"{$actioname}\" title=\"{$actioname}\"></div>\n";
		$output .= "</form></div>\n";
		return $output;
	}
	
	public function getmembers() {
		$returner = "<table><tr><th>Type</th><th>Name</th><th>Text</th><th>Title</th><th>Data</th></tr>";
		foreach ($this->members as $member) {
			$returner .= "<tr><td>{$member[0]}</td><td>{$member[1]}</td><td>{$member[2]}</td><td>{$member[3]}</td><td>{$member[4]}</td></tr>";
		}
		$returner .= "</table>";
		return $returner;
	}
	
	public function getlasttab() {
		return $this->tabindex + 1;
	}

	public function addDate($name = 'date', $text = null, $title = null, $preset = null, $enabled = True) {
		$this->members[] = array('date', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$temp  = "  <div class=\"frmele\" title=\"{$title}\">{$text}: <input tabindex=\"{$this->tabindex}\" type=\"text\" size=\"22\" name=\"{$name}\" id=\"{$name}\" class=\"tdformdate\" ";
		if ( $preset != null ) { $temp .= "value=\"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/>";
		if ( $enabled ) {
			$temp .= "/><a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal{$name}','{$name}')\">[cal]</a>";
			$temp .= " <a href=\"#\" onClick=\"document.forms['{$this->formname}'].{$name}.value='".date("Y-m-d")."'\">[today]</a></div>";
			$temp .= "<div class=\"frmele\" id=\"pickcal{$name}\"></div>\n";
		} else { $temp .= "\n"; }
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
	
	public function addDrop($name = 'drop', $text = null, $title = null, $preset = null, $new = True, $selected = False, $enabled = True) {
		$this->members[] = array('dropdown', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$temp  = "  <div class=\"frmele\" title=\"{$title}\">{$text}: <select onchange=\"tdt_get_option(this[selectedIndex].value, '{$name}', '{$text}')\" class=\"tdformdrop\" name=\"{$name}\" id=\"{$name}\" tabindex=\"{$this->tabindex}\"".(!$enabled ? " disabled=\"disabled\"":"").">";
		if ( $new && $enabled) { $temp .= "<option value=\"0\">-- Please Choose --</option><option value=\"--new--\">-- Add New --</option>"; }
		if ( $preset != null ) {
			foreach ( $preset as $option ) {
				if ( is_array($option) ) {
					$temp .= "<option value=\"{$option[0]}\"".(($selected == $option[0]) ? " selected=\"selected\"":"").">{$option[1]}</option>";
				} else {
					$temp .= "<option value=\"{$option}\"".(($selected == $option) ? " selected=\"selected\"":"").">{$option}</option>";
				}
			}
		}
		$temp .= "</select></div>\n";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
	
	public function addText($name = 'text', $text = null, $title = null, $preset = null, $enabled = True, $money = False) {
		$temptype = ($money) ? "textMoney" : "text";
		$this->members[] = array($temptype, $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$temp  = "  <div class=\"frmele\" title=\"{$title}\">{$text}: ".(($money) ? "$" : "")."<input tabindex=\"{$this->tabindex}\" type=\"text\" class=\"td{$temptype}\" size=\"".($money ? "34" : "35")."\" name=\"{$name}\" ";
		if ( $preset != null ) { $temp .= "value = \"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/></div>\n";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
	
	public function addPass($name = 'password', $text = null, $title = null, $preset = null, $enabled = True ) {
		$this->members[] = array('password', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$temp  = "  <div class=\"frmele\" title=\"{$title}\">{$text}: <input tabindex=\"{$this->tabindex}\" type=\"password\" class=\"tdpassword\" size=\"35\" name=\"{$name}\" ";
		if ( $preset != null ) { $temp .= "value = \"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/></div>\n";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
	
	public function addMoney($name = 'money', $text = null, $title = null, $preset = null, $enabled = True) {
		$this->addText($name, $text, $title, $preset, $enabled, True);
		return True;
	}
	
	public function addHidden($name = null, $value = null) {
		if ( $value == null || $name == null ) { return False; }
		else {
			$this->members[] = array('hidden', $name, null, null, $value);
			$this->hidden[] = array($name, $value);
			return true;
		}
	}
	
	public function addCheck($name = 'check', $text = null, $title = null, $preset = False, $enabled = True) {
		$this->members[] = array('checkbox', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$this->html[] = "  <div class=\"frmele\" title=\"{$title}\">{$text}: <input type=\"checkbox\" name=\"{$name}\" value=\"y\" tabindex=\"{$this->tabindex}\" ".($preset ? "checked=\"checked\"":"").(!$enabled ? "disabled=\"disabled\" ":"")." /></div>\n";
		$this->tabindex++;
		return true;
	}
}

?>
