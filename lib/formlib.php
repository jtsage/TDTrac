<?php
/**
 * TDTrac Form Library
 * 
 * Contains the form library
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
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
	 * @return object Form Object
	 */
	public function __construct($action = null, $id = 'genform', $tab = 1, $div = 'genform', $legend = 'Form', $theme = 'c') {
		$this->html[] = "<form method=\"post\" data-theme=\"{$theme}\" action=\"{$action}\">";
		$this->formname = $id;
		$this->tabindex = $tab;
	}
	
	/**
	 * Output the form to a single string
	 * 
	 * @param string Name of submit button
	 * @param string Extra text beform submit button
	 * @param bool Suppress submit button on true
	 * @return array HTML Formatted output
	 */
	public function output($actioname = 'Submit', $extra = null, $nobutton = False) {
		$output = $this->html;
		if ( !$nobutton ) {
			$temp = "  <div data-role=\"fieldcontain\">";
			if ( is_array($this->hidden) ) {
				foreach( $this->hidden as $hide) {
					$temp .= "<input type=\"hidden\" name=\"{$hide[0]}\" value=\"{$hide[1]}\" />";
				}
			}
			//$temp .= ((!is_null($extra)) ? "{$extra}&nbsp;&nbsp;&nbsp;" : "");
			$temp .= "<input type=\"submit\" class=\"subbie\" value=\"{$actioname}\" title=\"{$actioname}\" /></div>";
			$output[] = $temp;
		}
		$output[] = "</form>";
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
	 * @param string ID for element
	 * @return bool True on success
	 */
	public function addDate($name = 'date', $text = null, $title = null, $preset = null, $enabled = True, $id = null ) {
		global $SITE_SCRIPT;
		if ( $id == null ) { $id = $name; }
		$this->members[] = array('date', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$SITE_SCRIPT[] = "\t$(function() {";
		$SITE_SCRIPT[] = "\t\t$( \"#{$id}\" ).datebox();";
		$SITE_SCRIPT[] = "\t});";
		$temp  = "  <div data-role=\"fieldcontain\" title=\"{$title}\"><label for=\"{$name}\">{$text}</label><input data-role=\"datebox\" tabindex=\"{$this->tabindex}\" type=\"date\" name=\"{$name}\" id=\"{$id}\" ";
		if ( $preset != null ) { $temp .= "value=\"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/></div>";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}

	
	/**
	 * Add a AUTOCOMPLETE TEXT method to from
	 * 
	 * @param string Name of input field
	 * @param string Text to display before input
	 * @param string Hover text for element
	 * @param array Preset values for autocomplete
	 * @param string Selected element
	 * @param bool Element is enabled
	 * @return bool True on success
	*/
	public function addACText($name = 'actext', $text = null, $title = null, $preset = null, $selected = False, $enabled = True) {
		global $SITE_SCRIPT;
		$this->members[] = array('autocomplete', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }
		$SITE_SCRIPT[] = "\t$(function() {";
		$SITE_SCRIPT[] = "\t\tvar available{$name} = [";
		foreach ( $preset as $option ) {
			$SITE_SCRIPT[] = "\t\t\t\"{$option}\",";
		}
		$SITE_SCRIPT[] = "\t\t];";
		$SITE_SCRIPT[] = "\t\t$( \"#{$name}\" ).autocomplete({";
		$SITE_SCRIPT[] = "\t\t\tsource: available{$name}";
		$SITE_SCRIPT[] = "\t\t});";
		$SITE_SCRIPT[] = "\t});";
		$temp  = "  <div data-role=\"fieldcontain\" title=\"{$title}\"><label".(($money)?" class=\"money\"":"")." for=\"{$name}\">{$text}</label><input tabindex=\"{$this->tabindex}\" type=\"text\" class=\"td{$temptype}\" name=\"{$name}\" id=\"{$name}\" ";
		if ( $selected != false ) { $temp .= "value = \"{$selected}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/></div>";
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
		global $SITE_SCRIPT;
		$this->members[] = array('dropdown', $name, $text, $title, $preset);
		if ( $title == null ) { $title = $text; }

		$temp  = "  <div data-role=\"fieldcontain\"><label for=\"{$name}\">{$text}</label><select name=\"{$name}\" id=\"{$name}\" ".(!$enabled ? " disabled=\"disabled\"":"").">";
		if ( $preset != null ) {
			foreach ( $preset as $option ) {
				if ( is_array($option) ) {
					$temp .= "<option value=\"{$option[0]}\"".(($selected == $option[0]) ? " selected=\"selected\"":"").">{$option[1]}</option>";
				} else {
					$temp .= "<option value=\"{$option}\"".(($selected == $option) ? " selected=\"selected\"":"").">{$option}</option>";
				}
			}
		}
		$temp .= "</select></div>";
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
		$temp  = "  <div data-role=\"fieldcontain\"><label for=\"{$name}\">{$text}</label> <input type=\"text\" id=\"{$name}\" name=\"{$name}\" ";
		if ( $preset != null ) { $temp .= "value = \"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/></div>";
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
		$temp  = "  <div data-role=\"fieldcontain\"><label for=\"{$name}\">{$text}</label> <input type=\"password\" id=\"{$name}\" name=\"{$name}\" ";
		if ( $preset != null ) { $temp .= "value = \"{$preset}\" "; }
		if ( !$enabled ) { $temp .= "disabled=\"disabled\" "; }
		$temp .= "/></div>";
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
		$this->html[] = "  <div data-role=\"fieldcontain\">{$text}</div>";
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
		$this->html[] = "  <div class=\"frmele check-{$name}\" title=\"{$title}\"><label for=\"{$name}\">{$text}</label><input class=\"tdformcheck\" type=\"checkbox\" name=\"{$name}\" value=\"{$value}\" tabindex=\"{$this->tabindex}\" ".($preset ? "checked=\"checked\"":"").(!$enabled ? "disabled=\"disabled\" ":"")." /></div>";
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
		$temp  = "  <div data-role=\"fieldcontain\" title=\"{$title}\"><label for=\"{$name}\">{$text}</label>";
		$temp .= "<input type=\"radio\" name=\"{$name}\" tabindex=\"{$this->tabindex}\" value=\"1\" ".($preset?"checked=\"checked\"":"")." />";
		$this->tabindex++;
		$temp .= "<input type=\"radio\" name=\"{$name}\" tabindex=\"{$this->tabindex}\" value=\"0\" ".($preset?"":"checked=\"checked\"")."/></div>";
		$this->html[] = $temp;
		$this->tabindex++;
		return true;
	}
}

?>
