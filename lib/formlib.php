<?php
/**
 * TDTrac Form Library
 * 
 * Contains the form library
 * @package tdtrac
 * @version 1.3.0
 */

/**
 * TDForm Class
 * @package tdtrac
 */
class tdform {
	/**
	 * @var array Form Member storage
	 */
	public $members = "";
	/**
	 * @var array Formated HTML
	 */
	private $html = "";
	/**
	 * @var string Name of Form
	 */
	private $formname = "";
	/**
	 * @var integer Current Tab Index
	 */
	private $tabindex = 0;
	/**
	 * @var array Hidden Element Storage
	 */
	private $hidden = "";
	
	/**
	 * Create a new form
	 * 
	 * @param string HTTP location for action
	 * @param string ID of form
	 * @param integer First tab index for form
	 * @param string ID for enclosing div
	 */
	public function __construct($action = null, $id = 'genform', $tab = 1, $div = 'genform') {
		$this->html[] = "<div id=\"{$div}\" class=\"genform\"><form method=\"post\" action=\"{$action}\" name=\"{$id}\">\n";
		$this->formname = $id;
		$this->tabindex = $tab;
	}
	
	/**
	 * Output the form to a single string
	 * 
	 * @param string Name of submit button
	 * @param string Extra text beform submit button
	 * @return string HTML Formatted output
	 */
	public function output($actioname = 'Submit', $extra = null, $nobutton = False) {
		$output = "";
		foreach ($this->html as $line) {
			$output .= $line;
		}
		if ( !$nobutton ) {
			$output .= "  <div class=\"frmele\">";
			if ( is_array($this->hidden) ) {
				foreach( $this->hidden as $hide) {
					$output .= "<input type=\"hidden\" name=\"{$hide[0]}\" value=\"{$hide[1]}\" />";
				}
			}
			$output .= ((!is_null($extra)) ? $extra : "");
			$output .= "<input type=\"submit\" class=\"subbie\" tabindex=\"{$this->tabindex}\" value=\"{$actioname}\" title=\"{$actioname}\" /></div>\n";
		}
		$output .= "</form></div>\n";
		$this->tabindex++;
		return $output;
	}
	
	/**
	 * Show a table of current members
	 * 
	 * @return string HTML Formatted output
	 */
	public function getmembers() {
		$returner = "<table><tr><th>Type</th><th>Name</th><th>Text</th><th>Title</th><th>Data</th></tr>";
		foreach ($this->members as $member) {
			$returner .= "<tr><td>{$member[0]}</td><td>{$member[1]}</td><td>{$member[2]}</td><td>{$member[3]}</td><td>{$member[4]}</td></tr>";
		}
		$returner .= "</table>";
		return $returner;
	}
	
	/**
	 * Return the next tab index for document
	 * 
	 * @return integer Next Tab Element
	 */
	public function getlasttab() {
		return $this->tabindex + 1;
	}
	
	/**
	 * Add a DATE input to the form
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param string Preset value of element
	 * @param bool Element is enabled
	 * @param string ID for div if needed
	 * @return bool True on success
	 */
	public function addDate($name = 'date', $text = null, $title = null, $preset = null, $enabled = True, $id = null) {
		$this->members[] = array('date', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		if ( $id == null ) { $id = $name; }
		$temp  = "  <div class=\"frmele\" title=\"{$title}\">{$text}: <input tabindex=\"{$this->tabindex}\" type=\"text\" size=\"21\" name=\"{$name}\" id=\"{$id}\" class=\"tdformdate\" ";
		if ( $preset != null ) { $temp .= "value=\"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/>";
		if ( $enabled ) {
			$temp .= "<a href=\"#\" onclick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal{$id}','{$id}')\">[cal]</a>";
			$temp .= " <a href=\"#\" onclick=\"document.forms['{$this->formname}'].{$id}.value='".date("Y-m-d")."'\">[today]</a></div>";
		} else { $temp .= "</div>"; }
		$temp .= "<div class=\"frmele\" id=\"pickcal{$id}\"></div>\n";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
	
	/**
	 * Add a SELECT method to from
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param array Preset values for dropdown
	 * @param bool Allow user defined values
	 * @param string Selected element
	 * @param bool Element is enabled
	 * @return bool True on success
	 */
	public function addDrop($name = 'drop', $text = null, $title = null, $preset = null, $new = True, $selected = False, $enabled = True) {
		$this->members[] = array('dropdown', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$temp  = "  <div class=\"frmeled\" title=\"{$title}\">{$text}: <div class=\"frmeledrop\"><select onchange=\"tdt_get_option(this[selectedIndex].value, '{$name}', '{$text}')\" class=\"tdformdrop\" name=\"{$name}\" id=\"{$name}\" tabindex=\"{$this->tabindex}\"".(!$enabled ? " disabled=\"disabled\"":"").">";
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
		$temp .= "</select></div></div>\n";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
	
	/**
	 * Add a TEXT or MONEY input to the form
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param string Preset value of element
	 * @param bool Element is enabled
	 * @param bool True if MONEY input
	 * @return bool True on success
	 */
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
	
	/**
	 * Add a PASSWORD input to the form
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param string Preset value of element
	 * @param bool Element is enabled
	 * @return bool True on success
	 */
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
	
	/**
	 * Add a MONEY input to the form
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param string Preset value of element
	 * @param bool Element is enabled
	 * @return bool True on success
	 */
	public function addMoney($name = 'money', $text = null, $title = null, $preset = null, $enabled = True) {
		$result = $this->addText($name, $text, $title, $preset, $enabled, True);
		return $result;
	}
	
	/**
	 * Add INFORMATION to form
	 * 
	 * @param string Text to add
	 * @return True on success
	 */
	public function addInfo($text) {
		$this->members[] = array('info', null, $text, null, null);
		$this->html[] = "  <div class=\"frmele\">{$text}</div>\n";
		return true;
	}
	
	/**
	 * Add a HIDDEN field to the form
	 * 
	 * @param string Name of field
	 * @param string Value of the field
	 * @return bool True on success
	 */
	public function addHidden($name = null, $value = null) {
		if ( $value == null || $name == null ) { return False; }
		else {
			$this->members[] = array('hidden', $name, null, null, $value);
			$this->hidden[] = array($name, $value);
			return true;
		}
	}
	
	/**
	 * Add a CHECKBOX input to the form
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param string Preset value of element
	 * @param bool Element is enabled
	 * @param string Value on true
	 * @return bool True on success
	 */
	public function addCheck($name = 'check', $text = null, $title = null, $preset = False, $enabled = True, $value = 'y') {
		$this->members[] = array('checkbox', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$this->html[] = "  <div class=\"frmele\" title=\"{$title}\">{$text}: <input class=\"tdformcheck\" type=\"checkbox\" name=\"{$name}\" value=\"{$value}\" tabindex=\"{$this->tabindex}\" ".($preset ? "checked=\"checked\"":"").(!$enabled ? "disabled=\"disabled\" ":"")." /></div>\n";
		$this->tabindex++;
		return true;
	}
	
	/**
	 * Add a RADIO input to the form
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param string Preset value of element
	 * @param bool Element is enabled
	 * @return bool True on success
	 */
	public function addRadio($name = 'radio', $text = null, $title = null, $preset = False, $enabled = True ) {
		$this->members[] = array('radio', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$temp  = "  <div class=\"frmele\" title=\"{$title}\">{$text}";
		$temp .= "<input type=\"radio\" name=\"{$name}\" tabindex=\"{$this->tabindex}\" value=\"1\" ".($preset?"checked=\"checked\"":"")." />";
		$this->tabindex++;
		$temp .= "<input type=\"radio\" name=\"{$name}\" tabindex=\"{$this->tabindex}\" value=\"0\" ".($preset?"":"checked=\"checked\"")."/></div>\n";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
}

?>
