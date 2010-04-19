<?php
/* 
 * Title: 	test_variable.php
 * Author:	Tyler Hyndman
 * Date: 	September 4, 2009
 */ 

require_once("variable.php");

class Test_variable extends Variable {
	protected $failures=0;
	protected $passes=0;
	protected $total=0;
	
	function __construct(){ 
		parent::__construct("Title","","test","pre","");
	}

	public function display_variable($myq,&$line){
		$this->add_remove_links_test();
		// display_check_selection_input 			- NOT A UNIT
		// display_menu_selection 					- NOT A UNIT
		$this->display_menu_option_test();
		// display_radio_selection_input 			- NOT A UNIT
		$this->display_single_selection_input_test();
		$this->display_text_input_test();
		// display_text_table_input 				- NOT A UNIT
		$this->display_textarea_input_test(); // this function has a new option
		$this->display_title_test();
		$this->get_index_test();
		$this->get_value_test();
		$this->parse_one_line_value_test();
		$this->submit_one_line_value_test();
	}

	function add_remove_links_test(){
		$this->myq = true;
		$expected="<span class=pointer onclick=\"jsadd\">[+]</span><span class=pointer onclick=\"jsremove\">[-]</span>";
		$this->assert($this->add_remove_links("jsadd","jsremove"),$expected,"==== add_remove_links: myq = true ====");
		
		$this->myq = false;
		$expected="<span style='color: gray'>[+][-]</span>";
		$this->assert($this->add_remove_links("jsadd","jsremove"),$expected,"==== add_remove_links: myq = false ====");
	}
	
	function display_menu_option_test(){
		$line['name'] = "value";
		$expected="<option value='not_value'   >display_value</option>";
		$this->assert($this->display_menu_option($line,'name','not_value',"display_value"),$expected,"==== display_menu_option: value='not_value' ====");
		
		$expected="<option value='value' selected  >display_value</option>";
		$this->assert($this->display_menu_option($line,'name','value',"display_value"),$expected,"==== display_menu_option: value='value' ====");
		
		$expected="<option   disabled='disabled' >display_value</option>";
		$this->assert($this->display_menu_option($line,'name','<disabled>',"display_value"),$expected,"==== display_menu_option: value='<disabled>' ====");
		
		$line['name'] = "<disabled>";
		$expected="<option  selected disabled='disabled' >display_value</option>";
		$this->assert($this->display_menu_option($line,'name','<disabled>',"display_value"),$expected,"==== display_menu_option: value='<disabled>', \$line['name']='<disabled>' ====");
	}
	
	function display_single_selection_input_test(){
		$this->myq=true;
		$expected="<input type='radio' name='name' value='value'   />";
		$this->assert($this->display_single_selection_input("radio","name","value","input"),$expected,"==== display_single_selection_input myq=true, radio, input=input ====");
		
		$expected="<input type='radio' name='name' value='value' checked  />";
		$this->assert($this->display_single_selection_input("radio","name","value","value"),$expected,"==== display_single_selection_input myq=true, radio, input=value ====");
		
		$expected="<input type='checkbox' name='name' value='true'   />";
		$this->assert($this->display_single_selection_input("checkbox","name","true","input"),$expected,"==== display_single_selection_input myq=true, checkbox, input=input ====");
		
		$expected="<input type='checkbox' name='name' value='true' checked  />";
		$this->assert($this->display_single_selection_input("checkbox","name","true","true"),$expected,"==== display_single_selection_input myq=true, checkbox, input=true ====");
		
		$this->myq=false;
		$expected="<input type='radio' name='name' value='value'   disabled />";
		$this->assert($this->display_single_selection_input("radio","name","value","input"),$expected,"==== display_single_selection_input myq=false, radio, input=input ====");
		
		$expected="<input type='radio' name='name' value='value' checked  disabled />";
		$this->assert($this->display_single_selection_input("radio","name","value","value"),$expected,"==== display_single_selection_input myq=false, radio, input=value ====");
		
		$expected="<input type='checkbox' name='name' value='true'   disabled />";
		$this->assert($this->display_single_selection_input("checkbox","name","true","input"),$expected,"==== display_single_selection_input myq=false, checkbox, input=input ====");
		
		$expected="<input type='checkbox' name='name' value='true' checked  disabled />";
		$this->assert($this->display_single_selection_input("checkbox","name","true","true"),$expected,"==== display_single_selection_input myq=false, checkbox, input=true ====");
	}
		
	function display_text_input_test(){
		$this->myq=true;
		$expected="<input type='text' name='name' value='value' size='60'  />";
		$this->assert($this->display_text_input("name","value"),$expected,"=== display_test_input: myq = true, default size ====");

		$expected="<input type='text' name='name' value='value' size='50'  />";
		$this->assert($this->display_text_input("name","value",50),$expected,"=== display_test_input: myq = true, size=50 ====");

		$this->myq=false;
		$expected="<input type='text' name='name' value='value' size='60' readonly='readonly' />";
		$this->assert($this->display_text_input("name","value"),$expected,"=== display_test_input: myq = false, default size ====");	
	}

	function display_textarea_input_test(){
		$this->myq = true;
		$expected="<textarea name='name' id='name' style='width: 100%' rows='4'  >value</textarea><br />";
		$this->assert($this->display_textarea_input("name","value"),$expected,"==== display_textarea_input: myq = true, default rows ====");
		
		$expected="<textarea name='name' id='name' style='width: 100%' rows='2'  >value</textarea><br />";
		$this->assert($this->display_textarea_input("name","value",2),$expected,"==== display_textarea_input: myq = true, nondefault rows ====");
		
		$this->myq = false;
		$expected="<textarea name='name' id='name' style='width: 100%' rows='4' readonly='readonly' >value</textarea><br />";
		$this->assert($this->display_textarea_input("name","value"),$expected,"==== display_textarea_input: myq = false ====");
	}

	function display_title_test(){
		$this->myq=true;
		$this->instructions="";
		$expected="<b>Title</b>: <br />";
		$this->assert($this->display_title(),$expected,"=== display_title: myq = true, no instructions ====");
		
		$this->instructions="instructions";
		$expected="<span onmouseover=\"tipshow(this,'instructions')\" onmouseout=\"tipout()\"><b>Title</b>:</span> <br />";
		$this->assert($this->display_title(),$expected,"=== display_title: myq = true, with instructions ====");
		
		$this->myq=false;
		$this->instructions="";
		$expected="<b>Title</b>: <br />";
		$this->assert($this->display_title(),$expected,"=== display_title: myq = false, no instructions ====");
		
		$expected="<b>Title</b>: <br />";
		$this->assert($this->display_title("jsadd"),$expected,"=== display_title: myq = false, just jsadd ====");
		
		$expected="<b>Title</b>: <span style='color: gray'>[+][-]</span><br />";
		$this->assert($this->display_title("jsadd","jsremove"),$expected,"=== display_title: myq = false, just both js ====");
		
		$this->myq=true;
		$expected="<b>Title</b>: <span class=pointer onclick=\"jsadd\">[+]</span><span class=pointer onclick=\"jsremove\">[-]</span><br />";
		$this->assert($this->display_title("jsadd","jsremove"),$expected,"=== display_title: myq = true, just both js ====");
	}
	
	function get_index_test(){
		$this->assert($this->get_index("something[0]=\"test\""),1,"==== get_index: something[0]=\"test\"");
		$this->assert($this->get_index("something[0]=\"[test]\""),1,"==== get_index: something[0]=\"[test]\"");
		$this->assert($this->get_index("something[15]=\"test\""),16,"==== get_index: something[15]=\"test\"");
		$this->assert($this->get_index("something[ 0]=\"test\""),1,"==== get_index: something[ 0]=\"test\"");
		$this->assert($this->get_index("something[0 ]=\"test\""),1,"==== get_index: something[0 ]=\"test\"");
		$this->assert($this->get_index("something[ 101 ]=\"test\""),102,"==== get_index: something[ 101 ]=\"test\"");
		$this->assert($this->get_index("something[-1]=\"test\""),0,"==== get_index: something[-1]=\"test\"");
		$this->assert($this->get_index("something[-255]=\"test\""),-254,"==== get_index: something[-255]=\"test\"");
	}
	
	function get_value_test(){
		$this->assert($this->get_value("something[0]=\"test\""),"test","==== get_value: something[0]=\"test\"");
		$this->assert($this->get_value("something[0]=\"[test]\""),"[test]","==== get_value: something[0]=\"[test]\"");
		$this->assert($this->get_value("something=\"test\""),"test","==== get_value: something=\"test\"");
		$this->assert($this->get_value("something =\"test\""),"test","==== get_value: something =\"test\"");
		$this->assert($this->get_value("something= \"test\""),"test","==== get_value: something= \"test\"");
		$this->assert($this->get_value("something = \"test\""),"test","==== get_value: something = \"test\"");
		$this->assert($this->get_value("something = \"test\"  "),"test","==== get_value: something = \"test\"  ");
		
		$this->assert($this->get_value("something[0]=\"test\"",true),'"test"',"==== get_value: something[0]=\"test\", ignore_quotes=true");
		$this->assert($this->get_value("something[0]=\"[test]\"",true),'"[test]"',"==== get_value: something[0]=\"[test]\", ignore_quotes=true");
		$this->assert($this->get_value("something=\"test\"",true),'"test"',"==== get_value: something=\"test\", ignore_quotes=true");
		$this->assert($this->get_value("something =\"test\"",true),'"test"',"==== get_value: something =\"test\", ignore_quotes=true");
		$this->assert($this->get_value("something= \"test\"",true),'"test"',"==== get_value: something= \"test\", ignore_quotes=true");
		$this->assert($this->get_value("something = \"test\"",true),'"test"',"==== get_value: something = \"test\", ignore_quotes=true");
		$this->assert($this->get_value("something = \"test\"  ",true),'"test"',"==== get_value: something = \"test\"  , ignore_quotes=true");
		
		$this->assert($this->get_value("something = 5",true),"5","==== get_value: something = 5  , ignore_quotes=true");
		$this->assert($this->get_value("something =5",true),"5","==== get_value: something =5  , ignore_quotes=true");
		$this->assert($this->get_value("something= 5",true),"5","==== get_value: something= 5  , ignore_quotes=true");
		$this->assert($this->get_value("something=5",true),"5","==== get_value: something=5  , ignore_quotes=true");
		$this->assert($this->get_value("something = 5      ",true),"5","==== get_value: something = 5        , ignore_quotes=true");
	}
	
	function parse_one_line_value_test(){
		
	}
	
	function submit_one_line_value_test(){
		
	}
	
	public function submit_variable(&$_POST){
		return $_POST;
	}
	
	public function parse_variable($control_line,&$line){
		return $line;
	}

	function assert($actual,$expected,$message=""){
		if($actual!==$expected){
			if($message!="")
				echo "$message\n";
			echo "Actual:   $actual\n\nExpected: $expected\n";
			$this->failures++;
		}
		$this->total++;
		$this->passes=$this->total-$this->failures;
	}
	
	function results(){
		echo $this->failures." FAILURES\t".$this->passes." PASSES\n";
	}
}

$test = new test_variable();
$line=null;
$test->display_variable(null,$line);
$test->results();

?>
