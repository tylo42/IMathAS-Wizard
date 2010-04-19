<?php
/*
 * Author:	Tyler Hyndman
 * Date:	August 31, 2009
 */

require_once("question.php");

function create_number_variables(){
	$number = new question("number");

	$number->create_text_input(true,"Answer","","answer","",true);

	$number->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$number->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$number->create_text_input(false,"Required Decimals","","reqdecimals","",true);

	$values=array("Default" => "default","List" => "list","Exact List" => "exactlist","Ordered List" => "orderedlist");
	$number->create_radio_selection_input(false,"Answer Format","","answerformat","default",$values);
	
	$number->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	
	$values=array("Number" => "default","Point" => "point","Vector" => "vector");
	$number->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	$number->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	return $number;
}

function create_calculated_variables() {
	$calculated = new question("calculated");

	$calculated->create_text_input(true,"Answer","","answer","",true);

	$values=array("Default" => "default", "Fraction" => "fraction","Reduced Fraction" => "reducedfraction","Mixed Number" => "mixednumber","Fraction or Decimal" => "fracordec","No Decimal" => "nodecimal","No Trig" => "notrig", "List" => "list","Exact List" => "exactlist","Ordered List" => "orderedlist");
	$calculated->create_radio_selection_input(false,"Answer Format","","answerformat","default",$values);

	$calculated->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$calculated->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$calculated->create_text_input(false,"Required Decimals","","reqdecimals","",true);

	$calculated->create_required_times_input(false);

	$calculated->create_text_input(false,"Answer Prompt","","ansprompt","",false);

	$values=array("Number" => "default","Point" => "point","Vector" => "vector");
	$calculated->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	$calculated->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	$values=array("False" => "default","True" => "true");
	$calculated->create_radio_selection_input(false,"Hide Preview","","hidepreview","default",$values);

	return $calculated;
}

function create_choices_variables() {
	$choices = new question("choices");

	$choices->create_choices_answer(true);

	$values=array("Vertical" => "default","Horizontal" => "horiz", "Select" => "select", "Inline" => "inline");
	$choices->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	$values=array("Shuffle" => "default", "Do not shuffle" => "all", "Shuffle all but last" => "last");
	$choices->create_radio_selection_input(false,"Shuffle","","noshuffle","default",$values);

	return $choices;
}

function create_multans_variables() {
	$multans = new question("multans");

	$multans->create_multans_answer(true);

	$values=array("Default" => "default", "Answers" => "answers");
	$multans->create_radio_selection_input(false,"Score Method","","scoremethod","default",$values);

	$values=array("Shuffle" => "default", "Do not shuffle" => "all");
	$multans->create_radio_selection_input(false,"Shuffle","","noshuffle","default",$values);

	return $multans;
}

function create_matching_variables() {
	$matching = new question("matching");

	$matching->create_matching_answer(true);
	
	$matching->create_text_input(false,"Question Title","","questiontitle","",false);
	$matching->create_text_input(false,"Answer Title","","answertitle","",false);

	$values=array("Shuffle both" => "default", "Just questions" => "questions", "Just answers" => "answers");
	$matching->create_radio_selection_input(false,"Shuffle","","noshuffle","default",$values);

	$values=array("Default" => "default", "Select boxes" => "select");
	$matching->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	return $matching;
}

function create_numfunc_variables() {
	$numfunc = new question("numfunc");

	$numfunc->create_text_input(true,"Answer","","answer","",false);

	$numfunc->create_numfunc_variables_domain_input(false); 

	$numfunc->create_required_times_input(false);

	$values=array("Default" => "default", "Equation" => "equation", "Constant" => "toconst");
	$numfunc->create_radio_selection_input(false,"Answer Format","","answerformat","default",$values);

	$numfunc->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$numfunc->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$numfunc->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	$numfunc->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	$values=array("False" => "default","True" => "true");
	$numfunc->create_radio_selection_input(false,"Hide Preview","","hidepreview","default",$values);
	return $numfunc;
}

function create_string_variables() {
	$string = new question("string");

	$string->create_text_input(true,"Answer","","answer","",false);

	$string->create_string_flags_input(false);
	$string->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	$string->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	return $string;
}

function create_essay_variables() {
	$essay = new question("essay");

	$default_value=array("rows" => 5, "columns" => 50);
	$values=array("Rows" => "rows", "Columns" => "columns");
	$essay->create_multiple_text_input(false,"Answer Box Size","","answerboxsize",$default_value,$values);

	$values=array("Basic editor" => "default", "Rich text editor" => "editor");
	$essay->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	return $essay;
}

function create_draw_variables() {
	$draw = new question("draw");

	$draw->create_draw_answer_input(true);  // This is not done, add in $partweights

	$default_value=array("xmin" => -5, "xmax" => 5, "ymin" => -5, "ymax" => 5, "xscale" => 1, "yscale" => 1, "imagewidth" => 300, "imageheight" => 300);
	$values=array("X-min" => "xmin", "X-max" => "xmax", "Y-min" => "ymin", "Y-max" => "ymax", "X-scale" => "xscale", "Y-scale" => "yscale", "Image width" => "imagewidth", "Image height" => "imageheight");
	$draw->create_multiple_text_input(false,"Grid","","grid",$default_value,$values);
	$draw->create_draw_background_input(false);

	$default_value = array("line","dot","opendot");
	$values = array("Line" => "line", "Dot" => "dot", "Open dot" => "opendot", "Polygon" => "polygon");
	$draw->create_check_selection_input(false,"Answer Format","","answerformat",$default_value,$values);

	$draw->create_text_input(false,"Relative Tolerance","","reltolerance","1",true);
	$draw->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);

	return $draw;
}

function create_ntuple_variables() {
	$ntuple = new question("ntuple");

	$ntuple->create_text_input(true,"Answer","","answer","",false); // modify this at a later point, read help page

	$values=array("Default" => "default", "Point" => "point", "Point list" => "pointlist", "Vector" => "vector", "Vector list" => "vectorlist", "List" => "list");
	$ntuple->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	$ntuple->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$ntuple->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);

	return $ntuple;
}

function create_calcntuple_variables() {
	$calcntuple = new question("calcntuple");

	$calcntuple->create_text_input(true,"Answer","","answer","",false); // modify this at a later point, read help page

	$values=array("Default" => "default", "Point" => "point", "Point list" => "pointlist", "Vector" => "vector", "Vector list" => "vectorlist", "List" => "list",);
	$calcntuple->create_radio_selection_input(false,"Display Format","","displayformat","default",$values);

	$default_value=array();
	$values=array("Fraction" => "fraction", "Reduced fraction" => "reducedfraction", "Mixed number" => "mixednumber", "Scientific notation" => "scinot", "Fraction or decimal" => "fracordec", "No decimal" => "nodecimal", "No trig" => "notrig");
	$calcntuple->create_check_selection_input(false,"Answer Format","","answerformat",$default_value,$values);

	$calcntuple->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$calcntuple->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);

	return $calcntuple;
}

function create_matrix_variables() {
	$matrix = new question("matrix");

	$matrix->create_matrix_answer(true,"Answer","","answer","");
	
	$matrix->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$matrix->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$matrix->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	$matrix->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	return $matrix;
}

function create_calcmatrix_variables() {
	$calcmatrix = new question("calcmatrix");

	$calcmatrix->create_matrix_answer(true,"Answer","","answer","");

	$default_value=array();
	$values=array("Fraction" => "fraction", "Reduced fraction" => "reducedfraction", "Mixed number" => "mixednumber", "Scientific notation" => "scinot", "Fraction or decimal" => "fracordec", "No decimal" => "nodecimal", "No trig" => "notrig");
	$calcmatrix->create_check_selection_input(false,"Answer Format","","answerformat",$default_value,$values);

	$calcmatrix->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$calcmatrix->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$calcmatrix->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	$calcmatrix->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	$values=array("False" => "default","True" => "true");
	$calcmatrix->create_radio_selection_input(false,"Hide Preview","","hidepreview","default",$values);

	return $calcmatrix;
}

function create_interval_variables() {
	$interval = new question("interval");

	$interval->create_text_input(true,"Answer","","answer","",false); // May want to change later

	$interval->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$interval->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$interval->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	$interval->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	return $interval;
}

function create_calcinterval_variables() {
	$calcinterval = new question("calcinterval");

	$calcinterval->create_text_input(true,"Answer","","answer","",false); // May want to change later

	$default_value=array();
	$values=array("Fraction" => "fraction", "Reduced fraction" => "reducedfraction", "Mixed number" => "mixednumber", "Scientific notation" => "scinot", "Fraction or decimal" => "fracordec", "No decimal" => "nodecimal", "No trig" => "notrig");
	$calcinterval->create_check_selection_input(false,"Answer Format","","answerformat",$default_value,$values);

	$calcinterval->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$calcinterval->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);
	$calcinterval->create_text_input(false,"Required Decimals","","reqdecimals","",true);
	$calcinterval->create_text_input(false,"Answer Prompt","","ansprompt","",false);
	$calcinterval->create_text_input(false,"Answer Box Size","","answerboxsize","20",true);

	return $calcinterval;
}

function create_complex_variables() {
	$complex = new question("complex");

	$complex->create_text_input(true,"Answer","","answer","",false);

	$values=array("Default" => "default", "List" => "list");
	$complex->create_radio_selection_input(false,"Answer Format","","answerformat","default",$values);

	$complex->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$complex->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);

	return $complex;
}

function create_calccomplex_variables() {
	$calccomplex = new question("calccomplex");

	$calccomplex->create_text_input(true,"Answer","","answer","",false);

	$default_value=array();
	$values=array("Fraction" => "fraction", "Reduced fraction" => "reducedfraction", "Mixed number" => "mixednumber", "Scientific notation" => "scinot", "Fraction or decimal" => "fracordec", "No decimal" => "nodecimal", "No trig" => "notrig");
	$calccomplex->create_check_selection_input(false,"Answer Format","","answerformat",$default_value,$values);

	$calccomplex->create_text_input(false,"Relative Tolerance","","reltolerance",".001",true);
	$calccomplex->create_text_input(false,"Absolute Tolerance","","abstolerance","",true);

	return $calccomplex;
}

function create_multipart_variables() {
	$multipart = new question("multipart");

	return $multipart;
}
?>
