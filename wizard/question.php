<?php
/*
 * Author:	Tyler Hyndman
 * Date:	August 21, 2009
 */

require_once("variable.php");

class Question {	
	private $pre;
	private $myq;
	private $qtype;
	private $control_lines;
	private $required_variables;
	private $optional_variables;

	function __construct($qtype) {
		$this->pre=$qtype."_";
		$this->qtype=$qtype;
		$this->myq=$myq;

		$this->required_variables=array();
		$this->optional_variables=array();
		$this->no_display_variables=array();
	}
   
   public function get_pre() { return $this->pre; }
   public function get_myq() { return $this->myq; }
   public function get_qtype() { return $this->qtype; }
   
	public function add_variable($variable) {
		if($variable->required()==true) {
			$this->required_variables[$variable->get_name()]=$variable;
		} else { // default to not required
			$this->optional_variables[$variable->get_name()]=$variable;
		}
	}

	public function display_html($myq, $line) {
		$this->line=$line;
		$this->myq=$myq;
		//$this->myq=false; // uncomment this to test all questions with myq=false

		$this->create_default_variables();

		$ret.=$this->head();
		foreach($this->required_variables as $required_variable) {
			$ret.=$required_variable->display_variable($this->myq,$this->line);
      }

		$ret.=$this->optional_variables_heading();
		foreach($this->optional_variables as $optional_variable) {
			$ret.=$optional_variable->display_variable($this->myq,$this->line);
      }

		$ret.=$this->foot();

		return $ret;
	}

	private function create_default_variables() {
		// Top Variables
		$top_variables['qtext']=new qtext_input($this->pre, true);
		$top_variables['uvariables']=new uvariables_input($this->pre, true);
		$this->required_variables=array_merge($top_variables, $this->required_variables);

		// Bottom Variables
		$this->create_text_input(false,"Show Answer","Leave black to give just the correct answer, fill in to give a detailed explanation","showanswer","",false,5);

		$values=array("False" => "default","True" => "true");
		$this->create_radio_selection_input(false,"Hide Tips","","hidetips","default",$values);

		$this->optional_variables['hints']=new hint_text_input($this->pre, $required);
	}

	public function submit_question(&$_POST) {
		$this->create_default_variables();

		$variables=array_merge($this->required_variables, $this->optional_variables);

		foreach($variables as $variable)
			$variable->submit_variable($_POST);

		//print_r($_POST);
	}

	public function parse_question(&$line) {
		$this->line=& $line;
		$this->create_default_variables();
		$this->parse_control_lines();

		$variables=array_merge($this->required_variables, $this->optional_variables, $this->no_display_variables);

		$variables['qtext']->parse_variable($control_line,$this->line);

		foreach($this->control_lines as $control_line) {
			$control_line=$this->parse_comments($control_line);

			$var_name=$this->get_variable_name($control_line);

			//echo $var_name."<br>";

			if(isset($variables[$var_name])) {
				$variables[$var_name]->parse_variable($control_line,$this->line);
         } else if($control_line!="") {
				$variables['uvariables']->parse_variable($control_line,$this->line);
         }
		}
		//print_r($this->line);
	}

	private function get_variable_name($control_line){
		preg_match('/\$(\w+\b)/',$control_line,$match); // find the variable name
		return $match[1];
	}

	public function insert_required($variable) {
		$this->required_variables[$variable->get_name()] = $variable;
	}
   
	public function insert_optional($variable) {
		$this->optional_variables[$variable->get_name()] = $variable;
	}

	// These functions will be replaced by the questions_factory.php and questions.xml
	public function create_text_input($required, $title, $instructions, $name, $default_value, $ignore_quotes) {
		$variable=new text_input($title, $instructions, $name, $this->pre, $default_value, $ignore_quotes);
		if($required) {
			$this->required_variables[$name]=$variable;
		} else {
			$this->optional_variables[$name]=$variable;
      }
	}

	public function create_radio_selection_input($required,$title,$instructions,$name,$default_value,$values) {
		$variable=new radio_selection_input($title, $instructions, $name, $this->pre, $default_value, $values);
		if($required) {
			$this->required_variables[$name]=$variable;
		} else {
			$this->optional_variables[$name]=$variable;
      }
	}

	public function create_menu_selection_input($required,$title,$instructions,$name,$default_value,$values) {
		$variable=new menu_selection_input($title, $instructions, $name, $this->pre, $default_value, $values);
		if($required) {
			$this->required_variables[$name]=$variable;
		} else {
			$this->optional_variables[$name]=$variable;
      }
	}

	public function create_check_selection_input($required,$title,$instructions,$name,$default_value,$values) {
		$variable=new check_selection_input($title,$instructions,$name,$this->pre,$default_value,$values);
		if($required) {
			$this->required_variables[$name]=$variable;
		} else {
			$this->optional_variables[$name]=$variable;
      }
	}

	public function create_multiple_text_input($required,$title,$instructions,$name,$default_value,$values) {
		$variable=new multiple_text_input($title, $instructions, $name, $this->pre, $default_value, $values);
		if($required) {
			$this->required_variables[$name]=$variable;
		} else {
			$this->optional_variables[$name]=$variable;
      }
	}

	public function create_required_times_input($required){
		$variable=new required_times_input($this->pre);
		if($required) {
			$this->required_variables["requiretimes"]=$variable;
		} else {
			$this->optional_variables["requiretimes"]=$variable;
      }
	}

	public function create_draw_background_input($required) {
		$variable=new draw_background_input($this->pre);
		if($required) {
			$this->required_variables["background"]=$variable;
		} else {
			$this->optional_variables["background"]=$variable;
      }
	}

	public function create_string_flags_input($required) {
		$variable=new string_flags_input($this->pre);
		if($required) {
			$this->required_variables["strflags"]=$variable;
		} else {
			$this->optional_variables["strflags"]=$variable;
      }
	}

	public function create_numfunc_variables_domain_input($required) {
		$variable=new numfunc_variables_domain_input($this->pre);
		if($required) {
			$this->required_variables["variables"]=$variable;
			$this->no_display_variables["domain"]= & $this->required_variables["variables"];
		} else {
			$this->optional_variables["variables"]=$variable;
			$this->no_display_variables["domain"]= & $this->optional_variables["variables"];
		}
	}

	public function create_draw_answer_input($required) {
		$variable=new draw_answer_input($this->pre);
		if($required) {
			$this->required_variables["answers"]=$variable;
			$this->no_display_variables["partweights"]= & $this->required_variables["answers"];
		} else {
			$this->optional_variables["answers"]=$variable;
			$this->no_display_variables["partweights"]= & $this->optional_variables["answers"];
		}
	}

	public function create_matrix_answer($required,$title,$instructions,$name,$default_value) {
		$variable=new matrix_answer($title,$instructions,$name,$this->pre,$default_value);
		if($required) {
			$this->required_variables[$name]=$variable;
			$this->no_display_variables['answersize']= & $this->required_variables[$name];
		} else {
			$this->optional_variables[$name]=$variable;
			$this->no_display_variables['answersize']= & $this->optional_variables[$name];
		}
	}

	public function create_choices_answer($required) {
		$variable=new choices_answer($this->pre);
		if($required) {
			$this->required_variables['questions']=$variable;
			$this->no_display_variables['choices']= & $this->required_variables['questions'];
			$this->no_display_variables['answer']= & $this->required_variables['questions'];			
		} else {
			$this->optional_variables['questions']=$variable;
			$this->no_display_variables['choices']= & $this->optional_variables['questions'];
			$this->no_display_variables['answer']= & $this->optional_variables['questions'];
		}
	}
	
	public function create_multans_answer($required) {
		$variable=new multans_answer($this->pre);
		if($required) {
			$this->required_variables['questions']=$variable;
			$this->no_display_variables['choices']= & $this->required_variables['questions'];
			$this->no_display_variables['answers']= & $this->required_variables['questions'];
		} else {
			$this->optional_variables['questions']=$variable;
			$this->no_display_variables['choices']= & $this->optional_variables['questions'];
			$this->no_display_variables['answers']= & $this->optional_variables['questions'];
		}
	}
	
	public function create_matching_answer($required) {
		$variable=new matching_answer($this->pre);
		if($required) {
			$this->required_variables['questions']=$variable;
			$this->no_display_variables['choices']= & $this->required_variables['questions'];
			$this->no_display_variables['answers']= & $this->required_variables['questions'];
			$this->no_display_variables['matchlist']= & $this->required_variables['questions'];			
		} else {
			$this->optional_variables['questions']=$variable;
			$this->no_display_variables['choices']= & $this->optional_variables['questions'];
			$this->no_display_variables['answers']= & $this->optional_variables['questions'];
			$this->no_display_variables['matchlist']= & $this->optional_variables['questions'];
		}
	}


	/// HELPER FUNCTIONS
	private function head() {
		$ret.=$this->head_div();
		$ret.="<h3>Required Variables</h3>";
		return $ret;
	}

	private function foot() {
		$ret.=$this->save();
		$ret.="</div>";
		$ret.="</div>";
		$ret.="\n";
		return $ret;
	}

	private function head_div() {
		$display="none";
		if($this->qtype==$this->line['qtype']) {
			$display="block";
      }
		return "<div id=\"".$this->qtype."\" style=\"display:$display\"><br>";
	}

	private function optional_variables_heading() {
		$ret.=$this->save();

		$ret.="<table><tr><td>";
		$ret.="<h3>Optional Variables</h3>";
		$ret.="</td><td>";
		$ret.="&nbsp - &nbsp<a href=\"javascript:showhide('".$this->pre."optionalvariables')\">Show/Hide</a>";
		$ret.="</td></tr>";
		$ret.="</table>";

		$ret.="<div id=\"".$this->pre."optionalvariables\" style=\"display:none\">";
		$ret.="Default values are automatically selected. <br><br>";
		return $ret;
	}

	private function save() {
		$ret.="<input type=submit value=\"Save\">&nbsp";
		$ret.="<input type=submit name=test value=\"Save and Test Question\"><br><br>";
		return $ret;
	}

	private function parse_control_lines() { // possibly remove this function
		// concatinate control, question control, and answer control
		$this->line['control'].="\n".$this->line['qcontrol']."\n".$this->line['answer'];
		$this->line['qcontrol']="";
		$this->line['answer']="";
		
		// separate each line in control into an array
		$this->control_lines=explode("\n",$this->line['control']);
	
		// trim excess white space in each line   TODO: use array_walk
		for($x=0;$x<count($control_lines);$x++) {
			$this->control_lines[$x]=trim($this->control_lines[$x]);
      }
	}

	private function parse_comments($control_line) {
		if(strpos($control_line,"//")!==false&&strpos($control_line,"//")!=0) {
			$split=explode("//",$control_line);
			$ret=trim($split[0]);
			$this->line[$this->pre.'uvariables'].="//".$split[1]."\n";
		} else if(strpos($control_line,"//")!==false) {
			$this->line[$this->pre.'uvariables'].=$control_line."\n";
			$ret="";
		} else {
			$ret=$control_line;
      }
		return $ret;
	}
}
?>
