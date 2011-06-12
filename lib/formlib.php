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
	public function __construct($passed) {
		$default = array( 'action' => '/', 'theme' => 'c', 'id' => 'tdform' );
		$options = merge_defaults($default, $passed);
		$this->html[] = "<form method='post' data-ajax='false' data-theme='{$options['theme']}' action='{$options['action']}'>";
		$this->formname = $options['id'];
	}
	
	/**
	 * Add a section (collapsible div)
	 * 
	 * @param string Type, either open or closed
	 * @param string Text for heading
	 * @param bool Collapse or not (false = collapse)
	 * @return bool Always true
	 */
	public function addSection($type = 'open', $text = '', $open = False) {
		if ( $type == 'open' ) {
			$this->html[] = " <div data-role='collapsible'".(!$open?" data-collapsed='true'":"")."><h3>{$text}</h3>";
		} else {
			$this->html[] = " </div>";
		}
		return true;
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
			$temp = "  <div data-role='fieldcontain'>";
			if ( is_array($this->hidden) ) {
				foreach( $this->hidden as $hide) {
					$temp .= "<input type='hidden' name='{$hide[0]}' value='{$hide[1]}' />";
				}
			}
			$temp .= "<input type='submit' class='subbie' value='{$actioname}' title='{$actioname}' /></div>";
			$output[] = $temp;
		}
		$output[] = "</form>";
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
	 * Add a DATE input to the form
	 * 
	 * Options:
	 * 	'id' => Label ID
	 * 	'name' => Input Name
	 * 	'label' => Label Text
	 * 	'title' => Mouseover Text
	 * 	'preset' => Preset Value
	 * 	'enabled' => Field Enabled
	 * 	'type' => Type of field
	 * 	'role' => data-role of field
	 * 
	 * @param array Array of named options
	 * @return bool True on success
	 */
	public function addDate($passed) {
		$default = array(
			'name' 		=> 'date',
			'label'		=> 'Date Field',
			'title'		=> null,
			'preset'	=> null,
			'enabled'	=> True,
			'id'		=> null,
			'type'		=> 'date',
			'role'		=> 'datebox',
			'options'	=> '{"pickPageButtonTheme":"c", "mode": "calbox", "useModal": true}'
			);
		$options = merge_defaults($default, $passed);
		return $this->addText($options);
	}

	/**
	 * Add a PASSWORD input to the form
	 * 
	 * Options:
	 * 	'id' => Label ID
	 * 	'name' => Input Name
	 * 	'label' => Label Text
	 * 	'title' => Mouseover Text
	 * 	'preset' => Preset Value
	 * 	'enabled' => Field Enabled
	 * 	'type' => Type of field
	 * 	'role' => data-role of field
	 * 
	 * @param array Array of named options
	 * @return bool True on success
	 */
	public function addPass($passed) {
		$default = array(
			'name' 		=> 'password',
			'label'		=> 'Password Field',
			'title'		=> null,
			'preset'	=> null,
			'enabled'	=> True,
			'id'		=> null,
			'type'		=> 'password',
			'role'		=> null
			);
		$options = merge_defaults($default, $passed);
		return $this->addText($options);
	}
	
	/**
	 * Add a TEXT input to the form
	 * 
	 * Options:
	 * 	'id' => Label ID
	 * 	'name' => Input Name
	 * 	'label' => Label Text
	 * 	'title' => Mouseover Text
	 * 	'preset' => Preset Value
	 * 	'enabled' => Field Enabled
	 * 	'type' => Type of field
	 * 	'role' => data-role of field
	 * 
	 * @param array Array of named options
	 * @return bool True on success
	 */
	public function addText($passed) {
		$default = array(
			'name' 		=> 'text',
			'label'		=> 'Text Field',
			'title'		=> null,
			'preset'	=> null,
			'enabled'	=> True,
			'id'		=> null,
			'type'		=> 'text',
			'role'		=> null,
			'options'	=> null
			);
		$options = merge_defaults($default, $passed);

		if ( $options['id'] == null )		{ $options['id'] = $options['name']; }
		if ( $options['title'] == null )	{ $options['title'] = $options['label']; }
		
		$this->members[] = array($options['type'], $options['name'], $options['label'], $options['title'], $options['preset']);
		
		$temp  = "  <div data-role='fieldcontain' title='{$options['title']}'><label for='{$options['id']}'>{$options['label']}</label><input ".(($options['role'] != null)?"data-role='{$options['role']}' ":"")."type='{$options['type']}' name='{$options['name']}' id='{$options['id']}' ";
		if ( $options['preset'] != null )	{ $temp .= "value='{$options['preset']}' "; }
		if ( $options['options'] != null ) 	{ $temp .= "data-options='{$options['options']}' "; }
		if ( ! $options['enabled'] )		{ $temp .= "disabled='disabled' "; }
		$temp .= "/></div>";
		
		$this->html[] = $temp;
		return true;
	}
	
	/**
	 * Add a TEXT input to the form
	 * 
	 * Options:
	 * 	'id' => Label ID
	 * 	'name' => Input Name
	 * 	'label' => Label Text
	 * 	'title' => Mouseover Text
	 * 	'preset' => Preset Value
	 * 	'enabled' => Field Enabled
	 * 	'options' => Button Options, as an array
	 * 
	 * @param array Array of named options
	 * @return bool True on success
	 */
	public function addHRadio($passed) {
		$default = array(
			'name' 		=> 'text',
			'label'		=> 'Text Field',
			'title'		=> null,
			'preset'	=> null,
			'enabled'	=> True,
			'id'		=> null,
			'options'	=> array(),
			'hide'		=> False
			);
		$options = merge_defaults($default, $passed);
		
		if ( $options['id'] == null )		{ $options['id'] = $options['name']; }
		if ( $options['title'] == null )	{ $options['title'] = $options['label']; }
		
		$this->members[] = array('radio', $options['name'], $options['label'], $options['title'], $options['options']);

		$temp  = "<div data-role='fieldcontain'>";
		$temp .= "<fieldset data-role='controlgroup' data-type='horizontal'>";
		$temp .= "<legend>{$options['label']}</legend>"; 
		$ident = "a";
		foreach( $options['options'] as $option ) {
			$temp .= "<input type='radio' name='{$options['name']}' id='{$options['name']}-{$ident}' value='{$option[0]}' ".(($options['preset'] == $option[0])?"checked='checked' ":"")."/>";
			$temp .= "<label for='{$options['name']}-{$ident}'>{$option[1]}</label>";
			$ident++;
		}
		$temp .= "</fieldset></div>";
		$this->html[] = $temp;
	}
	
	/**
	 * Add a SELECT method to from
	 * 
	 * Options:
	 * 	id => Element ID
	 * 	name => Submitted Value Key
	 * 	label => Visible Label
	 * 	title => Mouseover Title
	 * 	options => Array of options
	 * 	selected => Selected element, if any (key/index is options is keyed)
	 * 	enabled => Element enabled
	 * 	allownew => Allow new elements to be added
	 * 
	 * @param array Options
	 * @return bool True on success
	 */
	public function addDrop($passed) {
		$default = array(
			'id' => null,
			'name' => 'drop',
			'title' => null,
			'label' => 'Drop List',
			'options' => null,
			'selected' => 0,
			'enabled' => True,
			'allownew' => False,
			'header' => True,
			'add' => False,
		);
		$options = merge_defaults($default, $passed);
		$this->members[] = array('dropdown', $option['name'], $option['label'], $option['title'], $option['options']);
		
		if ( $options['id'] == null )		{ $options['id'] = $options['name']; }
		if ( $options['title'] == null ) { $options['title'] = $options['label']; }

		$temp  = "  <div data-role='fieldcontain'><label for='{$options['id']}'>{$options['label']}</label><select data-native-menu='false' name='{$options['name']}' id='{$options['id']}' ".(!$options['enabled'] ? " disabled='disabled'":"").">";
		if ( $options['header'] ) {
			$temp .= "<option data-placeholder='true'>Choose one...</option>";
		}
		if ( $options['add'] ) {
			$temp .= "<option value='none' data-addoption='true'>Add New...</option>";
		}
		foreach ( $options['options'] as $option ) {
			if ( is_array($option) ) {
				$temp .= "<option value='{$option[0]}'".(($options['selected'] == $option[0]) ? " selected='selected'":"").">{$option[1]}</option>";
			} else {
				$temp .= "<option value='{$option}'".(($options['selected'] == $option) ? " selected='selected'":"").">{$option}</option>";
			}
		}
		
		$temp .= "</select></div>";
		$this->html[] = $temp;
		return true;
	}
	
	/**
	 * Add a MONEY input to the form
	 * 
	 * Options:
	 * 	'id' => Label ID
	 * 	'name' => Input Name
	 * 	'label' => Label Text
	 * 	'title' => Mouseover Text
	 * 	'preset' => Preset Value
	 * 	'enabled' => Field Enabled
	 * 	'type' => Type of field
	 * 	'role' => data-role of field
	 * 
	 * @param array Array of named options
	 * @return bool True on success
	 */
	public function addMoney($passed) {
		$default = array(
			'name' 		=> 'money',
			'label'		=> 'Money Field',
			'title'		=> null,
			'preset'	=> null,
			'enabled'	=> True,
			'id'		=> null,
			'type'		=> 'text',
			'role'		=> null
			);
		$options = merge_defaults($default, $passed);
		return $this->addText($options);
	}
	
	/**
	 * Add INFORMATION to form
	 * 
	 * @param string Text to add
	 * @return True on success
	 */
	public function addInfo($text) {
		$this->members[] = array('info', null, $text, null, null);
		$this->html[] = "  <div data-role='fieldcontain'>{$text}</div>";
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
	 * @param array Options
	 * @return bool True on success
	 */
	public function addCheck($passed) {
		$default = array(
			'id' => null,
			'name' => 'check',
			'label' => 'Checkmark Input',
			'text' => 'Yes',
			'preset' => False,
			'title' => null,
			'enabled' => True,
			'value' => 'y'
		);
		
		$options = merge_defaults($default, $passed);
		
		if ( $options['id'] == null )		{ $options['id'] = $options['name']; }
		if ( $options['title'] == null )	{ $options['title'] = $options['label']; }
		
		$this->members[] = array('checkbox', $options['name'], $options['label'], $options['title'], $options['preset']);
		
		$this->html[] = "  <div data-role='fieldcontain'><fieldset data-role='controlgroup'><legend>{$options['label']}</legend><input type='checkbox' name='{$options['name']}' id='{$options['id']}' class='custom' value='{$options['value']}'".($options['preset']?"checked='checked' ":"").(!$options['enabled']?"disabled='disabled' ":"")." /><label for='{$options['id']}'>{$options['text']}</label></fieldset></div>";
		return true;
	}
	
	/**
	 * Add a CHECKBOX input to the form
	 * 
	 * @param array Options
	 * @return bool True on success
	 */
	public function addToggle($passed) {
		$default = array(
			'id' => null,
			'name' => 'slider',
			'label' => 'Toggle Input',
			'options' => array(array(0,'False'),array(1,'True' )),
			'preset' => False,
			'title' => null,
			'enabled' => True,
		);
		
		$options = merge_defaults($default, $passed);
		
		if ( $options['id'] == null )		{ $options['id'] = $options['name']; }
		if ( $options['title'] == null )	{ $options['title'] = $options['label']; }
		
		$this->members[] = array('toggle', $options['name'], $options['label'], $options['title'], $options['preset']);
		
		$temp[] = "  <div data-role='fieldcontain'>";
		$temp[] = "<label for='{$options['id']}'>{$options['label']}</label>";
		$temp[] = "<select name='{$options['name']}' id='{$options['id']}' data-role='slider'>";
		foreach ( $options['options'] as $opt ) {
			$temp[] = "<option value='{$opt[0]}'".(($options['preset'] == $opt[0])?" selected='selected'":"").">{$opt[1]}</option>";
		}
		$temp[] = "</select></div>";
		
		$this->html[] = join($temp);
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
