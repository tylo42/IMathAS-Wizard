<?php
/*
 * Author:	Tyler Hyndman
 * Date:	March 13, 2010
 * TODO:
 * 	- Remove the _input from the class names
 * 	- Make all member data privite
 * 	- Split into 4 classes:
 * 		+ Hold data
 * 		+ Display Variable
 * 		+ Parse Variable
 * 		+ Submit Variable
 */

// The Variable class is the parent class for all other variables.
abstract class Variable {
	abstract public function display_variable($myq,&$line);
	abstract public function submit_variable(&$_POST);
	abstract public function parse_variable($control_line,&$line);
	
   // the protected variables should become private
	protected $title;
	protected $name;
	protected $pre;
	protected $myq;
	protected $default_value;
	protected $instructions;
	protected $id; // TO BE REMOVED
	private $required; // boolean value, true if it is a required variable, else false

	public function __construct($title, $instructions, $name, $pre, $default_value, $required) {
		$this->title=$title;
		$this->instructions=$instructions;
		$this->name=$name;
		$this->pre=$pre;
		$this->default_value=$default_value;
		$this->id=$pre.$name;
		$this->required=$required;
	}
	
	public function get_title() { return $this->title; }
	public function get_name() { return $this->name; }
	public function get_pre() { return $this->pre; }
	public function get_myq() { return $this->myq; }
	public function get_default_value() { return $this->default_value; }
	public function get_instructions() { return $this->instructions; }
	public function get_id() { return $this->pre.$this->name; }
	public function get_required() { return $this->required; }

   /** Display Function: used to print [+][-], grayed out if not my question
    * 
    *  @param[in] $jsadd      
    *    An onclick event to be called when [+] is clicked
    *  @param[in] $jsremove   
    *    An onclick event to be called when [-] is clicked
    */
	protected function add_remove_links($jsadd,$jsremove){
		if($this->get_myq()) {
			$ret.= "<span class=pointer onclick=\"$jsadd\">[+]</span><span class=pointer onclick=\"$jsremove\">[-]</span>";
		} else {
			$ret.="<span style='color: gray'>[+][-]</span>";
      }

		return $ret;
	}
	
   /** Display Function: print out a list of check boxes, one per line
    *  
    *  @param[in] $line
    *  @param[in] $name
    *  @param[in] $values
    * 
    */
	protected function display_check_selection_input(&$line,$name,$values){
		foreach($values as $display_value => $value) {
			$ret.=$this->display_single_selection_input("checkbox",$name.$value,"true",$line[$name.$value]).$display_value."<br />";
		}
		return $ret;
	}
	
	protected function display_menu_selection(&$line,$name,$values,$default_value,$onchange="") {
		if($line[$name]=="") {
			$line[$name]=$default_value;
      }
      
		if(!$this->get_myq()) {
			 $disabled="disabled='disabled'";
      }
		
      if($onchange!="") {
			$onchange="onchange=\"$onchange\"";
      }
			
		$ret.="<select name='$name' $disabled $onchange>";
		foreach($values as $display_value => $value) {
			$ret.=$this->display_menu_option($line, $name, $value, $display_value);
      }
		$ret.="</select>";
		return $ret;
	}
	
	protected function display_menu_option(&$line, $name, $value, $display_value) {
		$opt_value="value='$value'";
		if ($line[$name]==$value) {
			$selected.="selected";
      }
      
		if($value=="<disabled>") {
			$disabled="disabled='disabled'";
			$opt_value="";
		}
		return "<option $opt_value $selected $disabled >$display_value</option>";
	}
	
	protected function display_radio_selection_input(&$line, $name, $values) {
		foreach($values as $display_value => $value){
			$ret.=$this->display_single_selection_input("radio",$name,$value,$line[$name]).$display_value."<br />";
		}
		return $ret;
	}
	
	protected function display_single_selection_input($type,$name,$value,$input){
		if($value==$input) {
			$checked="checked";
      }
      
		if(!$this->get_myq()) {
			$disabled=" disabled";
      }
      
		return "<input type='$type' name='$name' value='$value' $checked $disabled />";
	}

	protected function display_text_input($name,$value,$size=60){
		if(!$this->myq) {
			$readonly="readonly='readonly'";
      }
      
		return "<input type='text' name='$name' value='$value' size='$size' $readonly />";
	}

	protected function display_text_table_input($myq, &$line, $values) {
		$ret.="<table>";
		foreach($values as $display_value => $value) {
			if(!isset($line[$this->id.$value])) {
				$line[$this->id.$value]=$this->default_value[$value];
         }
			$ret.="<tr><td>$display_value:</td><td>".$this->display_text_input($this->id.$value,$line[$this->id.$value],15)."</td></tr>";
		}
		$ret.="</table><br />";
		return $ret;
	}

	protected function display_textarea_input($name,$value,$min_rows=4,$max_rows=20) {
		if (!$this->myq) {
			$readonly="readonly='readonly'";
      }

		$rows=min($max_rows,max($min_rows,substr_count($value,"\n")+1));
		return "<textarea name='$name' id='$name' style='width: 100%' rows='$rows' $readonly >".$value."</textarea><br />";
	}

	protected function display_title($jsadd="",$jsremove=""){
		if($jsadd!=""&&$jsremove!="") {
			$javascript=$this->add_remove_links($jsadd,$jsremove);
      }
      
		if($this->instructions!="") {
			//$instructions.="($this->instructions)";
			$span_start="<span onmouseover=\"tipshow(this,'$this->instructions')\" onmouseout=\"tipout()\">";
			$span_end="</span>";
		}

		return "$span_start<b>$this->title</b>:$span_end $javascript<br />";
	}
	
	protected function get_index($control_line) { // Index starts at 1, this might be a problem
		$start_pos=strpos($control_line,"[")+1;
		$end_pos=strpos($control_line,"]");
		return substr($control_line,$start_pos,$end_pos-$start_pos)+1;
	}

	protected function get_value($control_line,$ignore_quotes=false) {
		list($variable,$value)=explode("=",$control_line,2); // separate the variable and value
		$value=trim($value); // trim any excess whitespace
		if(!$ignore_quotes)
			$value = substr($value,1,-1); // remove first and last characters, the quotes
		return $value;
	}

	protected function parse_one_line_value($control_line,&$line,$ignore_quotes) {
		$line[$this->id]=$this->get_value($control_line,$ignore_quotes);
	}

	protected function submit_one_line_value(&$_POST,$ignore_quotes){//TODO: Create unit test
      // if it is the default value do not put in control
		if($_POST[$this->id]!=$this->default_value) { 
         // if we don't ignore quotes put quotes in
			if(!$ignore_quotes) {
				$_POST[$this->id] = '"'.$_POST[$this->id].'"';
         }
			$_POST['control'].="\$$this->name=".$_POST[$this->id]."\n\n";
		}		
		return $_POST;
	}
} // end abstract class Variable

///////////////////////////////////////////////////////////

class text_input extends Variable{
	private $ignore_quotes;

	function __construct($title,$instructions,$name,$pre,$default_value,$required,$ignore_quotes) {
		parent::__construct($title,$instructions,$name,$pre,$default_value,$required);
		$this->ignore_quotes=$ignore_quotes;
	}	

	public function display_variable($myq,&$line) { //TODO: Create unit test
		$this->myq=$myq;
		if(!isset($line[$this->pre.$this->name])) {
			$line[$this->pre.$this->name]=$this->default_value;
      }

		$ret.=$this->display_title();
		$ret.=$this->display_text_input($this->id, $line[$this->id], 60);
		$ret.="<br /><br />";	
		return $ret;
	}

	public function submit_variable(&$_POST) { 
		$this->submit_one_line_value($_POST,$this->ignore_quotes);
	}

	public function parse_variable($control_line, &$line) { 
		$this->parse_one_line_value($control_line, $line, $this->ignore_quotes);
	}
} // end class text_input

class radio_selection_input extends Variable{
	private $values;

	function __construct($title, $instructions, $name, $pre, $default_value, $required, $values){
		parent::__construct($title, $instructions, $name, $pre, $default_value, $required);
		$this->values=$values;
	}

	public function display_variable($myq,&$line){ //TODO: Create unit test
		$this->myq=$myq;
		if( !in_array($line[$this->id], $this->values) ) {
			$line[$this->id]=$this->default_value;
      }

		$ret.=$this->display_title();
		$ret.=$this->display_radio_selection_input($line,$this->id,$this->values)."<br />";
		return $ret;
	}

	public function submit_variable(&$_POST) {
		$this->submit_one_line_value(&$_POST,false);
	}

	public function parse_variable($control_line,&$line) {
		return $this->parse_one_line_value($control_line, $line, false);
	}
}

class check_selection_input extends Variable{
	private $values;

	function __construct($title, $instructions, $name, $pre, $default_value, $required, $values){
		parent::__construct($title, $instructions, $name, $pre, $default_value, $required);
		$this->values=$values;
	}

	public function display_variable($myq,&$line){ //TODO: Create unit test
		$this->myq=$myq;
		if( $this->empty_values($line) ) {
			$this->choose_defaults($line);
		}

		$ret.=$this->display_title();
		$ret.=$this->display_check_selection_input($line,$this->id,$this->values)."<br />";
		return $ret;
	}

	protected function empty_values(&$line){
		foreach($this->values as $value){
			if( isset($line[$this->id.$value]) ) {
				return false;
         }
		}
		return true;
	}

	protected function choose_defaults(&$line) {
		foreach ($this->default_value as $value) {
			$line[$this->id.$value]="true";
      }
	}

	public function submit_variable(&$_POST){ // unit test and refactor, do not put anything if defaults are choosen
		$control="\$".$this->name."=\"";
		foreach($this->values as $value) {
			if($_POST[$this->id.$value]==true) {
				$control.=$value.",";
         }
		}

		$control=substr($control,0,-1)."\"\n";

		if($control=="\$".$this->name."=\"\n")
			$control="\$".$this->name."=\"\"\n";
		$_POST['control'].=$control;
	}

	public function parse_variable($control_line, &$line) {  // unit test, refactor
		$input=$this->get_value($control_line);
		$inputs=explode(",",$input);

		foreach($this->values as $value) {
			$line[$this->id.$value]="false";
      }

		foreach($inputs as $value) {
			$value=trim($value);
			$line[$this->id.$value]="true";
		}
	}
}

class menu_selection_input extends Variable {
	private $values;

	function __construct($title, $instructions, $name, $pre, $default_value, $required, $values) {
		parent::__construct($title, $instructions, $name, $pre, $default_value, $required);
		$this->values=$values;
	}

	public function display_variable($myq, &$line) { //TODO: Create unit test
		$this->myq=$myq;
		
		// The following commented lines should be deleted soon
		
		//if(!in_array($line[$this->pre.$this->name],$this->values))
		//	$line[$this->pre.$this->name]=$this->default_value;

		$ret.=$this->display_title();
		$ret.=$this->display_menu_selection($line,$this->id,$this->values,$this->default_value)."<br /><br />";
		return $ret;
	}

	public function submit_variable(&$_POST) {
		$this->submit_one_line_value($_POST, false);
	}

	public function parse_variable($control_line, &$line) {
		$this->parse_one_line_value($control_line, $line, false);
	}
}

// Classes below here are a little less generic.
// It would be nice to try to make them a little more generic.
class multiple_answers_questions extends Variable{
	function __construct($title, $instructions, $name, $pre, $default_value, $required){
		parent::__construct($title, $instructions, $name, $pre, $default_value, $required);
	}
	
	public function display_variable($myq, &$line) {
		$this->myq=$myq;
		$ret.=$this->display_title();
		
		return $ret;
	}
	
	public function submit_variable(&$_POST){
		return $_POST;
	}
	
	public function parse_variable($control_line, &$line){
		return $line;
	}
}

class choices_answer extends Variable{
	function __construct($pre, $required) {
		parent::__construct("Choices", "", "choices", $pre, "", $required);
	}
	
	public function display_variable($myq, &$line){
		$this->myq=$myq;
		$ret.=$this->display_title("addRow('".$this->id."',1)","removeRow('".$this->id."',3)");

		if( !isset($line[$this->id.'size']) ) {
			$line[$this->id.'size']=4;
      }
      
		if( !isset($line[$this->id.'correct']) ) {
			$line[$this->id.'correct']=0;
      }
		
		$ret.="<table id='".$this->id."' valign='top'>";
		$ret.="<tr><td></td><td><u>Choice</u></td><td><u>Correct</u></td></tr>";
		for($x=0;$x<($line[$this->id.'size']);$x++) {
			$ret.="<tr>";
			$ret.="<td>".($x+1).":</td>";
			$ret.="<td>".$this->display_text_input($this->id.($x+1),$line[$this->id.($x+1)],60)."</td>";
			$ret.="<td>".$this->display_single_selection_input("radio",$this->id."correct",$x,$line[$this->id."correct"])."</td>";
			$ret.="</tr>";
		}
		$ret.="</table>";
		$ret.="<br>";
		
		return $ret;
	}
	
	public function submit_variable(&$_POST) {
		$size=1;
		while( isset($_POST[$this->id.$size]) ) {
			$_POST['control'].="\$choices[".($size-1)."]=".$_POST[$this->id.$size]."\n";
			$size++;
		}
	
		$_POST[$this->id.'size']=$size;
		
		if( !isset($_POST[$this->id.'correct']) ) { // if no radio button is selected
			$_POST[$this->id.'correct']=0;
      }
		$_POST['control'].="\n\$answer=".($_POST[$this->id.'correct'])."\n\n";
	}
	
	public function parse_variable($control_line,&$line){
		if( (strpos($control_line,"\$questions")!==false) || (strpos($control_line,"\$choices")!==false) ) {
			$this->parse_choices($control_line,$line);
		} else if(strpos($control_line,"\$answer")!==false) {
			$this->parse_answer($control_line,$line);
      }
	}
	
	protected function parse_choices($control_line,&$line){
		$index=$this->get_index($control_line);
		$line[$this->id.$index]=$this->get_value($control_line,true);

		if($index>$line[$this->id.'size']) {
			$line[$this->id.'size']=$index;
      }
	}
	
	protected function parse_answer($control_line,&$line) {
		$line[$this->id."correct"]=$this->get_value($control_line,true);
	}
}

class multans_answer extends Variable{
	function __construct($pre, $required) {
		parent::__construct("Choices","","choices",$pre,"", $required);
	}
	
	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		$ret.=$this->display_title("addRow('".$this->id."',1)","removeRow('".$this->id."',3)");

		if(!isset($line[$this->id.'size']))
			$line[$this->id.'size']=4;
		//if(!isset($line[$this->id.'correct']))
		//	$line[$this->id.'correct']=0;
		
		$ret.="<table id='".$this->id."' valign='top'>";
		$ret.="<tr><td></td><td><u>Choice</u></td><td><u>Correct</u></td></tr>";
		for($x=0;$x<($line[$this->id.'size']);$x++){
			$ret.="<tr>";
			$ret.="<td>".($x+1).":</td>";
			$ret.="<td>".$this->display_text_input($this->id.($x+1),$line[$this->id.($x+1)],60)."</td>";
			$ret.="<td>".$this->display_single_selection_input("checkbox",$this->id."correct".($x+1),"true",$line[$this->id."correct".($x+1)])."</td>";
			$ret.="</tr>";
		}
		$ret.="</table>";
		$ret.="<br>";
		
		return $ret;
	}
	
	public function submit_variable(&$_POST){
		$size=1;
		while( isset($_POST[$this->id.$size]) ) {
			$_POST['control'].="\$choices[".($size-1)."]=".$_POST[$this->id.$size]."\n";
			$size++;
		}
	
		$_POST[$this->id.'size']=$size;

		$answer="\$answers=\"";
		for($x=0;$x<$size;$x++) {
			if($_POST[$this->id."correct".($x+1)]=="true"){
				$checked=1;
				$answer.=$x.",";
			}
		}
		$answer=substr($answer,0,-1)."\"";
		
		if(!$checked) {
			$answer="\$answers=\"-1\"";
      }

		$_POST['control'].="\n".$answer."\n\n";
	}
	
	public function parse_variable($control_line,&$line){
		if( (strpos($control_line,"\$questions")!==false) || (strpos($control_line,"\$choices")!==false) ) {
			$this->parse_choices($control_line,$line);
		} else if(strpos($control_line,"\$answer")!==false) {
			$this->parse_answer($control_line,$line);
      }
	}
	
	protected function parse_choices($control_line,&$line) {
		$index=$this->get_index($control_line);

		$line[$this->id.$index]=$this->get_value($control_line,true);
		
		if($index>$line[$this->id.'size']) {
			$line[$this->id.'size']=$index;
      }
	}
	
	protected function parse_answer($control_line,&$line) {
		$correct=$this->get_value($control_line,false);
		
		foreach(explode(",",$correct) as $x) {
			$line[$this->id."correct".($x+1)]="true";
		}
	}
}

class matching_answer extends Variable{ // works
	private $questions;
	private $answers;
	private $correct;

	function __construct($pre, $required) {
		parent::__construct("Choices", "", "choices", $pre, "", $required);
		$this->questions=$pre."questions";
		$this->answers=$pre."answers";
		$this->correct=$pre."correct";
	}
	
	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		$ret.=$this->display_title();
		
		// Set defaults, should be in some kind of function
		if(!isset($line[$this->questions.'size'])) {
			$line[$this->questions.'size']=4;
      }
      
		if(!isset($line[$this->answers.'size'])) {
			$line[$this->answers.'size']=4;
      }
      
		if(!isset($line[$this->correct.'1'])) {
			for($x=1; $x<=4; $x++) {
				$line[$this->correct.$x]=$x;
			}
		}


		$ret.="<table id=\"".$this->id."question\" valign=\"top\">";
		$ret.="<tr align=\"center\"><td></td><td align=\"left\">Question Text: ".$this->add_remove_links("addRowMatQ('".$this->id."question')","removeRow('".$this->id."question',3)")."</td>";
		for($x=0;$x<$line[$this->answers.'size'];$x++) {
			$ret.="<td>".chr($x+97)."</td>";
      }
		$ret.="</tr>";
		for($x=0; $x<$line[$this->questions.'size']; $x++) {
			$ret.="<tr align='center'>";
			$ret.="<td>".($x+1).":</td>";
			$ret.="<td>".$this->display_text_input($this->questions.($x+1),$line[$this->questions.($x+1)])."</td>";

			// ==== Radio Buttons ====
			for($y=0; $y<$line[$this->answers.'size']; $y++) {
				$ret.="<td>".$this->display_single_selection_input("radio",$this->correct.($x+1),$y+1,$line[$this->correct.($x+1)])."</td>";
			}
			$ret.="</tr>";
		}
		$ret.="</table>";
		$ret.="<br>";

		// ==== Answer Table ====
		$ret.="<table id=\"".$this->id."answer\" valign=\"top\">";
		$ret.="<tr align=\"center\"><td></td><td align=\"left\">Answer Text: ".$this->add_remove_links("addRowMatA('".$this->id."answer','".$this->id."question')","removeRowMatA('".$this->id."answer','".$this->id."question',3)")."</td>";
		$ret.="</tr>";
		for($x=0;$x<$line[$this->answers.'size'];$x++) {
			$ret.="<tr align=\"center\">";
			$ret.="<td>".chr($x+97).":</td>";
			$ret.="<td>".$this->display_text_input($this->answers.($x+1),$line[$this->answers.($x+1)])."</td>";
			$ret.="</tr>";
		}
		$ret.="</table>";
		$ret.="<br>";
		
		return $ret;
	}
	
	public function submit_variable(&$_POST) {
		// get the number of questions
		$qsize=1;
		while(isset($_POST[$this->questions.$qsize])) {
			$qsize++;
      }
		$_POST[$this->questions.'size']=$qsize;

		// get the number of answers
		$asize=1;
		while(isset($_POST[$this->answers.$asize])) {
			$asize++;
      }
		$_POST[$this->answers.'size']=$asize;

		// ==== $questions ==== 
		for($x=1;$x<$qsize;$x++) {
			$_POST['control'].="\$questions[".($x-1)."]=".$_POST[$this->questions.$x]."\n";
      }

		// ==== $answers ==== 
		for($x=1;$x<$asize;$x++) {
			$_POST['control'].="\$answers[".($x-1)."]=".$_POST[$this->answers.$x]."\n";
      }

		// ==== $matchlist ==== 
		$_POST['control'].="\n\$matchlist=\"";
		for($x=1;$x<$qsize;$x++) {
			if(!isset($_POST[$this->correct.$x])) { // if there was no selection
				$_POST[$this->correct.$x]=1;
         }
			$_POST['control'].=($_POST[$this->correct.$x]-1).",";
		}
		$_POST['control'] = substr($_POST['control'],0,-1);
		$_POST['control'].="\"\n\n";
	}
	
	public function parse_variable($control_line,&$line) {
		if( (strpos($control_line,"\$questions")!==false) || (strpos($control_line,"\$choices")!==false) ) {
			$this->parse_choices($control_line,$line);
      } else if(strpos($control_line,"\$answers")!==false) {
			$this->parse_answers($control_line,$line);
		} else if(strpos($control_line,"\$matchlist")!==false) {
			$this->parse_matchlist($control_line,$line);
      }
	}
	
	protected function parse_choices($control_line,&$line) { // similar function to parse_answers
		$index=$this->get_index($control_line);

		$line[$this->questions.$index]=$this->get_value($control_line,true);
		
		if($index>$line[$this->questions.'size']) {
			$line[$this->questions.'size']=$index;
      }
	}
	
	protected function parse_answers($control_line,&$line) {
		$index=$this->get_index($control_line);

		$line[$this->answers.$index]=$this->get_value($control_line,true);
		
		if($index>$line[$this->answers.'size']) {
			$line[$this->answers.'size']=$index;
      }
	}
	
	protected function parse_matchlist($control_line,&$line) {
		$list=$this->get_value($control_line,false);
		
		foreach(explode(",",$list) as $x) {
			$line[$this->correct.($counter+1)]=$x+1;
			$counter++;
		}
	}
}

class matrix_answer extends Variable {  // works 
	function __construct($title, $instructions, $name, $pre, $default_value, $required){
		parent::__construct($title, $instructions, $name, $pre, $default_value, $required);
	}

	public function display_variable($myq,&$line){
		$this->myq=$myq;
		$ret.=$this->display_title();


		$ret.="<table>";
		$ret.="<tr><td>Rows: </td><td>".$this->add_remove_links("addRow('".$this->id."',0,false)","removeRow('".$this->id."',1)")."</td></tr>";
		$ret.="<tr><td>Columns: </td><td>".$this->add_remove_links("addCol('".$this->id."')","removeCol('".$this->id."',1)")."</td></tr>";
		$ret.="</table>";

		//answer grid
		if($line[$this->id.'rowsize']==0) {
			$line[$this->id.'rowsize']=3;
      }
      
		if($line[$this->id.'colsize']==0) {
			$line[$this->id.'colsize']=3;
      }

		// This is needed for the big brackets, probably should be moved later
		$ret.="<link rel=\"stylesheet\" href=\"/imathas-development/assessment/mathtest.css\" type=\"text/css\"/>";

		$ret.="<table><tr><td class='matrixleft'>&nbsp;</td><td>";
		$ret.="<table id='".$this->id."'>";
		for($row=0;$row<$line[$this->id.'rowsize'];$row++) {
			$ret.="<tr>";
			for($col=0;$col<$line[$this->id.'colsize'];$col++) {
				$ret.="<td>".$this->display_text_input($this->id.($col+1)."_".($row+1),$line[$this->id.($col+1).'_'.($row+1)],3)."</td>";
			}
         $ret.="</tr>";
		}
		$ret.="</table>";
		$ret.="</td><td class=\"matrixright\">&nbsp;</td></tr></table>";

		$ret.=$this->display_check_selection_input($line,$this->id,array("Display answer size" => "answersize"),null)."<br />";
		
		return $ret;
	}

	public function submit_variable(&$_POST){
		$answer="\$answer=\"[";
		
		$col=1;
		$row=1;
		while(isset($_POST[$this->id.'1_'.$row])) {
			$col=1;
			$answer.="(";
			while(isset($_POST[$this->id.$col.'_'.$row])) {
				$answer.=$_POST[$this->id.$col.'_'.$row].",";
				$col++;
			}
			$answer=substr_replace($answer,"",-1)."),";  // Change this to substr
			$row++;
		}
		$answer=substr($answer,0,-1)."]\"";
		
		if($_POST[$this->id.'answersize']=="true") {
			$answersize="\$answersize=\"".($row-1).",".($col-1)."\"";
		}
		
		$_POST['control'].=$answer."\n";
		$_POST['control'].=$answersize."\n\n";
	}

	public function parse_variable($control_line,&$line){ // NOTE: "$answer" is a substring of "$answersize" 
		if(strpos($control_line,"\$answersize")!==false) {
			$this->parse_answersize_input($control_line,$line);
		} else if(strpos($control_line,"\$answer")!==false) {
			$this->parse_answer_input($control_line,$line);
      }
	}
	
	protected function parse_answersize_input($control_line,&$line){
		$string=$this->get_value($control_line);
		list($rows,$cols)=explode(",",$string);
		
		$line[$this->id.'answersize']="true";
		$this->set_rows_cols($rows,$cols,$line);
	}
	
	protected function parse_answer_input($control_line,&$line){ // this makes assumptions, should check for them in submit
		$string=$this->get_value($control_line);
		
		$string=substr(trim($string),1,-1); // remove first and last parenthesis or brackets
		
		$string = str_replace(" ","",$string); // remove all whitespace, may need some modification
		$values=explode(",",$string);

		$rows=0;
		$cols=0;
		foreach($values as $value) {
			if(strpos($value,"(")===0) { // if the string starts with a (
				$rows++;
				$cols=0;
				$value=substr_replace($value,"",0,1); // remove first character
			}
			if( substr_count($value,")") > substr_count($value,"(") ) { // If there are more ) than (
				$value=substr($value,0,-1); // remove last character, ")"
			}
			
			$cols++;
			$line[$this->id.$cols.'_'.$rows]=$value;
		}
		
		$this->set_rows_cols($rows,$cols,$line);
	}
	
	protected function set_rows_cols($rows,$cols,&$line) {
		$line[$this->id.'rowsize']=trim($rows);
		$line[$this->id.'colsize']=trim($cols);
	}
}

class draw_answer_input extends Variable { // works, could use some modification
	protected $function;
	protected $type;

	function __construct($pre, $required) {
		parent::__construct("Answers", "", "answers", $pre, "", $required);
		$this->function=$pre."function";
		$this->type=$pre."type";
	}

	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		$ret.=$this->display_title("addRow('".$this->id."',1,true,true)","removeRow('".$this->id."',2)");
		
		if(!isset($line[$this->id.'size'])) {
			$line[$this->id.'size']=1;
      }
		
		$ret.="<table id=".$this->id.">";
		$ret.="<tr><td></td><td>Draw Type</td><td>Parameters</td><td>Weight</td></tr>";
		for($x=0;$x<$line[$this->id.'size'];$x++) {
			$ret.="<tr>";
			$ret.="<td>".($x+1).":</td>";
			$values=array("--- Select Answer Type ---" => "<disabled>", "Function" => "func".($x+1), "Dot" => "dot".($x+1));
			$ret.="<td>".$this->display_menu_selection($line,$this->type.($x+1),$values,"<disabled>","javascript:select_div(this.value,['func', 'dot'],".($x+1).")")."</td>";

			$ret.="<td>";
			$display="display:none";
			if($line[$this->type.($x+1)]=="func".($x+1)) {
				$display="display:block";
         }
			$ret.="<div id='func".($x+1)."' style='$display'>";
			$ret.="f(x)= ".$this->display_text_input($this->function.($x+1),$line[$this->function.($x+1)],10);
			$ret.=" Min: ".$this->display_text_input($this->function."min".($x+1),$line[$this->function."min".($x+1)],5);
			$ret.=" Max: ".$this->display_text_input($this->function."max".($x+1),$line[$this->function."max".($x+1)],5);
			$ret.="</div>";
			
			$display="display:none";
			if($line[$this->type.($x+1)]=="dot".($x+1)) {
				$display="display:block";
         }
			$ret.="<div id='dot".($x+1)."' style='$display'>";
			$ret.=" x: ".$this->display_text_input($this->dot."x".($x+1),$line[$this->dot."x".($x+1)],5);
			$ret.=" y: ".$this->display_text_input($this->dot."y".($x+1),$line[$this->dot."y".($x+1)],5);
			$values=array("Closed" => "closed", "Open" => "open");
			$ret.=" ".$this->display_menu_selection($line,"open-closed".($x+1),$values,"closed");
			$ret.="</div>";
			$ret.="</td>";
			
			$ret.="<td>";
			$ret.=$this->display_text_input($this->id."weight".($x+1),$line[$this->id."weight".($x+1)],5);
			$ret.="</td>";
			
			$ret.="</tr>";
		}
		$ret.="</table>";
		$ret.="<br />";
		
		return $ret;
	}

	public function submit_variable(&$_POST) {
		$size=1;
		while(isset($_POST[$this->type.$size])) {
			$size++;
      }
		$_POST[$this->id.'size']=$size;
		
		$weight="\$partweights=\"";
		$empty_weight="\$partweights=\"";
		for($i=0;$i<$size-1;$i++) {
				$control.="\$answers[$i]=\"";
			if($_POST[$this->type.($i+1)]=="func".($i+1)) {
				$control.=$_POST[$this->function.($i+1)].",".$_POST[$this->function."min".($i+1)].",".$_POST[$this->function."max".($i+1)]."\"\n";
			} else if($_POST[$this->type.($i+1)]=="dot".($i+1)) {
				$control.=$_POST[$this->dot."x".($i+1)].",".$_POST[$this->dot."y".($i+1)];
				if($_POST["open-closed".($i+1)]=="open") {
					$control.=",open";
            }
				$control.="\"\n";
			}
			$weight.=$_POST[$this->id."weight".($i+1)].",";
			$empty_weight.=",";
		}
		$weight=substr($weight,0,-1)."\"\n";
		$empty_weight=substr($empty_weight,0,-1)."\"\n";
		
		if($weight==$empty_weight) {
			$weight="";
      }
		
		$_POST['control'].=$control."\n".$weight."\n";
	}

	public function parse_variable($control_line,&$line){
		if(strpos($control_line,"\$answers")!==false) {
			$this->parse_answers($control_line,$line);
		} else if(strpos($control_line,"\$partweights")!==false) {
			$this->parse_partweights($control_line,$line);
      }
	}
	
	protected function parse_answers($control_line,&$line){
		$index=$this->get_index($control_line);
		$value=$this->get_value($control_line);
		
		$values=explode(",",$value);
		if(strpos($values[0],"x")===false) { // if the first value does not contain an x it is a dot, need to check other stuff
			$line[$this->type.$index]="dot".$index;
			$line[$this->dot."x".$index]=$values[0];
			$line[$this->dot."y".$index]=$values[1];
			$line["open-closed".$index]=$values[2];
		} else {
			$line[$this->type.$index]="func".$index;
			$line[$this->function.$index]=$values[0];
			$line[$this->function."min".$index]=$values[1];
			$line[$this->function."max".$index]=$values[2];
		}
		
		if($index>$line[$this->id.'size']) {
			$line[$this->id.'size']=$index;
      }
	}
	
	protected function parse_partweights($control_line,&$line){
		$value=$this->get_value($control_line);
		$values=explode(",",$value);
		
		for($x=0;$x<sizeof($values);$x++) {
			$line[$this->id."weight".($x+1)]=$values[$x];
		}
	}
}

class numfunc_variables_domain_input extends Variable {
	private $domain;

	function __construct($pre, $required) {
		parent::__construct("Variables", "", "variables", $pre, "", $required);
		$this->domain_min=$pre."domain_min";
		$this->domain_max=$pre."domain_max";
		$this->integers=$pre."integers";
	}

	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		$ret.=$this->display_title("addRow('".$this->id."','1')","removeRow('".$this->id."',2)");

		$ret.="<table id='".$this->id."'>";
		$ret.="<tr><td></td><td><u>Variable</u></td><td><u>Min</u></td><td><u>Max</u></u></td></td><td><u>Integers</u></td></tr>";

		if(!isset($line[$this->id.'size'])||$line[$this->id.'size']==0) {
			$line[$this->id.'size']=1;
      }

		for($x=0;$x<$line[$this->id.'size'];$x++) {
			$ret.="<tr>";
			$ret.="<td>".($x+1).": </td>";
			$ret.="<td>".$this->display_text_input($this->id.($x+1),$line[$this->id.($x+1)],10)."</td>";
			$ret.="<td>".$this->display_text_input($this->domain_min.($x+1),$line[$this->domain_min.($x+1)],5)."</td>";
			$ret.="<td>".$this->display_text_input($this->domain_max.($x+1),$line[$this->domain_max.($x+1)],5)."</td>";
			$ret.="<td>".$this->display_single_selection_input("checkbox",$this->integers.($x+1),"true",$line[$this->integers.($x+1)])."</td>";
			$ret.="</tr>";
		}
		$ret.="</table><br \>";

		return $ret;
	}

	public function submit_variable(&$_POST) {
		$variables="\$variables=\"";
		$domain="\$domain=\"";

		$size=1;
		while(isset($_POST[$this->id.$size])) {
			$size++;
      }
		$_POST[$this->id.'size']=$size;
	
		for($x=1;$x<$size;$x++) { // need some error checking for blank values in domain
			if($x!=1) { // fix this
				$variables.=",";
				$domain.=",";
			}
			$variables.=$_POST[$this->id.$x];
			$domain.=$_POST[$this->domain_min.$x].",".$_POST[$this->domain_max.$x];
			if($_POST[$this->integers.$x]=="true")
				$domain.=",integers";
		}
		$variables.="\"";
		$domain.="\"";

		if($variables!="\$variables=\"\"") {
			$_POST['control'].=$variables."\n";
      }
      
		if($variables!="\$domain=\"\"") {
			$_POST['control'].=$domain."\n\n";
      }
	}

   public function parse_variable($control_line,&$line){
      if(strpos($control_line,"\$variables")!==false) {
         $this->parse_variables_input($control_line,$line);
      } else if(strpos($control_line,"\$domain")!==false) {
			$this->parse_domain_input($control_line,$line);
      }
   }

	protected function parse_variables_input($control_line,&$line) {
		$string=$this->get_value($control_line);
		$variables=explode(",",$string);

		$counter=1;
		foreach($variables as $variable) {
			$line[$this->id.$counter++]=$variable;
      }

		$line[$this->id.'size']=$counter-1;
	}

	protected function parse_domain_input($control_line,&$line) {
		$string=$this->get_value($control_line);
		$variables=explode(",",$string);

		$cur=1;
		for($x=0;$x<count($variables);) {
			$line[$this->domain_min.$cur]=$variables[$x++];
			$line[$this->domain_max.$cur]=$variables[$x++];
			if($variables[$x]=="integers") {
				$line[$this->integers.$cur]="true";
				$x++;
			}
			$cur++;
		}
	}
}

class multiple_text_input extends Variable {
	private $values;

	function __construct($title, $instructions, $name, $pre, $default_value, $required, $values) {
		parent::__construct($title, $instructions, $name, $pre, $default_value, $required);
		$this->values=$values;
	}

	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		$ret.=$this->display_title();
		$ret.=$this->display_text_table_input($myq, $line, $this->values);

		return $ret;
	}

	public function submit_variable(&$_POST) { // TODO: Do not submit if defaults are given
		$control="\$".$this->name."=\"";

		foreach($this->values as $value) {
			if($_POST[$this->id.$value]=="") {
				$_POST[$this->id.$value]=$this->default_value[$value];
         }
			$control.=$_POST[$this->id.$value].",";
		}
		
		$_POST['control'].=substr($control,0,-1)."\"\n\n";
	}

	public function parse_variable($control_line,&$line) {
		$string=$this->get_value($control_line);
		$grid=explode(",",$string);
		$counter=0;
		foreach($this->values as $value) {// assumes that the order is maintained
			$line[$this->id.$value]=trim($grid[$counter++]);
		}
	}

}

class string_flags_input extends check_selection_input {  // INFO: This class uses that same display_variable function as check_selection_input
	function __construct($pre, $required) {
		$default_value=array("compress_whitespace","ignore_case");
		$values=array("Ignore case" => "ignore_case", "Trim whitespace" => "trim_whitespace", "Compress whitespace" => "compress_whitespace", "Remove whitespace" => "remove_whitespace", "Ignore order" => "ignore_order", "Ignore commas" => "ignore_commas", "Special or" => "special_or");
		parent::__construct("String Flags", "", "strflags", $pre, $default_value, $required, $values);
	}

	public function submit_variable(&$_POST) {
		$control="\$".$this->name."=\"";
		foreach($this->values as $value) {
			if($_POST[$this->id.$value]==true) {
				$control.=$value."=1,";
			} else {
				$control.=$value."=0,";
         }
		}

		$control=substr($control,0,-1)."\"\n";
		$_POST['control'].=$control;
	}

	public function parse_variable($control_line,&$line){
		$input=$this->get_value($control_line);
		$inputs=explode(",",$input);

		foreach($inputs as $value){
			$flag=explode("=",$value);
			if($flag[1]==1) {
				$line[$this->id.trim($flag[0])]="true";
			} else {
				$line[$this->id.trim($flag[0])]="false";
         }
		}
	}
}

class draw_background_input extends Variable{
	private $values;
	private $color;
	private $equation;

	function __construct($pre, $required){
		parent::__construct("Background", "", "background", $pre, "", $required);
		$this->values=array("Black" => "black", "Red" => "red", "Blue" => "blue", "Yellow" => "yellow", "Green" => "green", "Orange" => "orange", "Purple" => "purple", "Cyan" => "cyan", "Gray" => "gray", "White" => "white");
		$this->color=$pre."color";
		$this->equation=$pre."equation";
	}

	public function display_variable($myq,&$line){
		$this->myq=$myq;
		$ret.=$this->display_title("addRow('".$this->id."','1')","removeRow('".$this->id."',2)");
		$ret.="<table id='".$this->id."'>";
		$ret.="<tr><td></td><td></td><td>Equation</td><td>Color</td></tr>";

		if(!isset($line[$this->id.'size'])||$line[$this->id.'size']==0) {
			$line[$this->id.'size']=1;
      }

		for($x=0;$x<$line[$this->id.'size'];$x++) {
			$ret.="<tr>";
			$ret.="<td>".($x+1).": </td>";
			$ret.="<td>y=</td>";
			$ret.="<td>".$this->display_text_input($this->equation.($x+1),$line[$this->equation.($x+1)],60)."</td>";
			$ret.="<td>".$this->display_menu_selection($line,$this->color.($x+1),$this->values,"")."</td>";
			$ret.="</tr>";
		}
		$ret.="</table><br>";
		return $ret;
	}

	public function submit_variable(&$_POST){
		$size=1;
		while(isset($_POST[$this->equation.$size])) {
			$size++;
		}

		$_POST[$this->id.'size']=$size;
	
		for($x=1;$x<$size;$x++) {
			if($_POST[$this->equation.$x]!="") {
				$_POST['control'].="\$".$this->name."[".($x-1)."]=\"".$_POST[$this->equation.$x].",".$_POST[$this->color.$x]."\"\n";
         }
		}
		$_POST['control'].="\n";
	}

	public function parse_variable($control_line,&$line){
		$index=$this->get_index($control_line);

		if($index>$line[$this->id.'size']) {
			$line[$this->id.'size']=$index;
      }

		// get the equation and color
		$string=$this->get_value($control_line);
		$string=explode(",",$string);
		$line[$this->equation.$index]=trim($string[0]);
		$line[$this->color.$index]=trim($string[1]);
	}
}

class required_times_input extends Variable { // TODO: Bug: has to do with the javascript
	private $symbol;
	private $equal;
	private $num;
	private $values;

	function __construct($pre, $required){
		parent::__construct("Require Times", "", "requiretimes", $pre, "", $required);
		$this->symbol=$this->pre."symbol";
		$this->equal=$this->pre."equal";
		$this->num=$this->pre."num";
		$this->values=array("=" => "=", "&lt" => "<", "&gt" => ">", "&lt=" => "<=", "&gt=" => ">=");
	}

	function display_variable($myq,&$line) {
		$this->myq=$myq;
		$ret.=$this->display_title("addRow('".$this->id."','1')","removeRow('".$this->id."',2)");
		$ret.="<table id=\"".$this->id."\" valign='top'>";
		$ret.="<tr><td></td><td>Symbol</td><td></td><td>Number</td></tr>";
	
		if(!isset($line[$this->id.'size'])) {
			$line[$this->id.'size']=1;
      }

		for($x=0;$x<($line[$this->id.'size']);$x++) {
			$ret.="<tr>";
			$ret.="<td>".($x+1).":</td>";
			$ret.="<td>".$this->display_text_input($this->symbol.($x+1),$line[$this->symbol.($x+1)],15)."</td>";
			$ret.="<td>".$this->display_menu_selection($line,$this->equal.($x+1),$this->values,"eq")."</td>";
			$ret.="<td>".$this->display_text_input($this->num.($x+1),$line[$this->num.($x+1)],15)."</td>";
			$ret.="</tr>";
		}
		$ret.="</table>";
		return $ret;
	}

	public function submit_variable(&$_POST) {
		$size=1;
		$_POST['control'].="\$".$this->name."=\"";
		while(isset($_POST[$this->equal.$size])&&$_POST[$this->symbol.$size]!=""&&$eq.$_POST[$this->num.$size]!=""){
			if($size!=1) {
				$_POST['control'].=",";
         }
			$_POST['control'].=$_POST[$this->symbol.$size].",".$_POST[$this->equal.$size].$_POST[$this->num.$size];
			$size++;
		}
		$_POST['control'].="\"\n\n";
	}

	public function parse_variable($control_line,&$line){
		$require_times=$this->parse_require_times($control_line);

		$counter=1;
		foreach($require_times as $symbol => $times) {
			$line[$this->symbol.$counter]=trim($symbol);
			foreach(array_reverse($this->values) as $value) {
				$value = str_replace(" ","",$value); // remove any white space
				if(strpos($times,$value)!==false) {
					$line[$this->equal.$counter]=$value;
					$line[$this->num.$counter]=str_replace($value,"",$times);
					break;
				}
			}
			$counter++;
		}
		$line[$this->id.'size']=$counter-1;
	}

	protected function parse_require_times($control_line) {
		$string=$this->get_value($control_line);
		$values=explode(",",$string);

		// foreach(range(0,count($values),2) as $i) // This might be a little better --- ?

		for($value=0;$value<count($values);$value=$value+2) {// change to $value+=2, and check
			$require_times[$values[$value]]=$values[$value+1];
		}
		return $require_times;
	}
}

class hint_text_input extends Variable {
	private $minimum_rows;

	function __construct($pre, $required){
		parent::__construct("Hint Text", "", "hints", $pre, "", $required);
		$this->minimum_rows=1;
	}

	public function display_variable($myq,&$line){ //TODO: Create unit test
		$this->myq=$myq;
		if($line[$this->id.'size']==0) {
			$line[$this->id.'size']=1;
      }

		$ret.=$this->display_title("addRow('".$this->id."')","removeRow('".$this->id."',$this->minimum_rows)");
		$ret.="<table id=\"".$this->id."\">";
		for($row=0;$row<$line[$this->id.'size'];$row++) {
			$ret.="<tr>";
			$ret.="<td>".($row+1).":</td>";
			$ret.="<td>".$this->display_text_input($this->id.($row+1),$line[$this->id.($row+1)],60)."</td>";
			$ret.="</tr>";
		}
		$ret.="</table>";
		return $ret;
	}

	public function submit_variable(&$_POST) {
		// calulate the hint size
		$size=1;
		while(isset($_POST[$this->id.$size]) /*&&($_POST[$this->id.$size]!=""||$size!=1)*/) {
			$size++;
      }
		$_POST[$this->id.'size']=$size;
	
		for($x=1;$x<$size;$x++) {
			$_POST['control'].="\$".$this->name."[".($x-1)."]=\"".$_POST[$this->id.$x]."\"\n";
      }
	}

	public function parse_variable($control_line,&$line) {
		$index=$this->get_index($control_line);

		$line[$this->id.$index]=$this->get_value($control_line);

		if($index>$line[$this->id.'size']) {
			$line[$this->id.'size']=$index;
      }
	}
}

class qtext_input extends Variable {
	function __construct($pre, $required) {
		parent::__construct("Question", "", "qtext", $pre, "", $required);
	}

	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		
		$ret.=$this->display_title("incboxsize('".$this->id."')","decboxsize('".$this->id."')");
		$ret.=$this->display_textarea_input($this->id,$line[$this->id]);
		return $ret;
	}

	public function submit_variable(&$_POST) {
		$_POST['qtext']=$_POST[$this->pre.'qtext'];
	}

	public function parse_variable($control_line,&$line) {
		$line[$this->pre.'qtext']=$line['qtext'];
	}
}

class uvariables_input extends Variable {
	function __construct($pre, $required) {
		parent::__construct("User Defined Variables", "", "uvariables", $pre, "", $required);
	}

	public function display_variable($myq,&$line) {
		$this->myq=$myq;
		
		$ret.=$this->display_title("incboxsize('".$this->id."')","decboxsize('".$this->id."')");
		$ret.=$this->display_textarea_input($this->id,$line[$this->id]);
		return $ret;
	}

	public function submit_variable(&$_POST) {
		$_POST['control']=$_POST[$this->pre.'uvariables']."\n\n";
	}

	public function parse_variable($control_line,&$line) {
		$line[$this->pre.'uvariables'].=$control_line."\n";
	}
}

?>
