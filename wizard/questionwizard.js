/*
 * Title: 	questionwizard.js
 * Author:	Tyler Hyndman
 * Date: 	August 31, 2009
 * Description:	This file contains a collection of javascript functions used in the question wizzard.  
 * Each function adds or removes columns or rows from a table.
 */

// ==== Number ====
// ==== Calculated ====
// ==== Choices ====
// ==== Multans ====
// ==== Matching ====
function addRowMatQ(id){
	var tbl = document.getElementById(id);
	var curRow = tbl.rows.length;
	if(curRow<20){
		var curCol = tbl.getElementsByTagName('tr')[0].getElementsByTagName('td').length;
		//var curCol = 5;
		var tbody = document.getElementById

		(id).getElementsByTagName("TBODY")[0];

		var row = document.createElement("TR")
		var td1 = document.createElement("TD")
		td1.appendChild (document.createTextNode(curRow+":"))

		var td2 = document.createElement("TD")
		var inputT = document.createElement('input');
		inputT.type = 'text';
		inputT.size = 60;
		inputT.name = 'matching_questions' + curRow;  ///////
		td2.appendChild(inputT);

		row.appendChild(td1);
		row.appendChild(td2);

		for(var x=1; x<curCol-1; x++){
			var td3 = document.createElement("TD");
			var inputR = document.createElement('input');
			inputR.type = 'radio';
			inputR.name = 'matching_correct' + curRow;
			inputR.value = x;
			if(x==1)
				inputR.checked=true;
			//inputR.value = curRow;
			td3.appendChild(inputR);
			row.appendChild(td3);
		}
		tbody.appendChild(row);
	}
}

function addRowMatA(ida,idq){
	var tbla = document.getElementById(ida);
	var tblq = document.getElementById(idq);
	var curRow = tbla.rows.length;
	if(curRow<27){
		var curCol = tblq.getElementsByTagName('tr')[0].getElementsByTagName('td').length;
		//var curCol = 5;
		var tbody = document.getElementById

		(ida).getElementsByTagName("TBODY")[0];

		var row = document.createElement("TR")
		var td1 = document.createElement("TD")
		td1.appendChild (document.createTextNode(String.fromCharCode(curRow+96)+":"))

		var td2 = document.createElement("TD")
		var inputT = document.createElement('input');
		inputT.type = 'text'
		inputT.size = 60;
		inputT.name = 'matching_answers' + curRow;
		td2.appendChild(inputT);

		row.appendChild(td1);
		row.appendChild(td2);
		tbody.appendChild(row);

		// Add column to question
		var tblBodyObj = document.getElementById(idq).tBodies[0];
		for (var x=0; x<tblBodyObj.rows.length; x++) {
			var newCell = tblBodyObj.rows[x].insertCell(-1);
			if(x==0){
				newCell.appendChild (document.createTextNode(String.fromCharCode(curRow+96)))
			} else {
				var inputR = document.createElement('input');
				inputR.type = 'radio';
				inputR.name = 'matching_correct' + x;
				inputR.value = curRow;
				newCell.appendChild(inputR);
			}
		}
	}
}

function removeRowMatA(ida,idq,num){
	var tbl = document.getElementById(ida);
	var lastRow = tbl.rows.length;
	var allRows = document.getElementById(idq).rows;
	if (lastRow > num){
		for (var i=0; i<allRows.length; i++) {
			allRows[i].deleteCell(-1);
		}

		tbl.deleteRow(lastRow - 1);
	}
}
// ==== Numfunc ====
// ==== String ====
function addRowStrAns(id){
	var tbl = document.getElementById(id);
	var curRow = tbl.rows.length;
	if(curRow<20){
		var tbody = document.getElementById

		(id).getElementsByTagName("TBODY")[0];

		var row = document.createElement("TR")
		var td1 = document.createElement("TD")
		td1.appendChild (document.createTextNode(curRow+":"))

		var td2 = document.createElement("TD")
		var inputT = document.createElement('textarea');
		inputT.cols = 60;
		inputT.rows = 1;
		inputT.name = 'stranswer' + curRow;
		td2.appendChild(inputT);

		row.appendChild(td1);
		row.appendChild(td2);
		tbody.appendChild(row);
	}
}

// ==== Essay ====
// ==== Draw ====
// ==== Ntuple ====
// ==== Calcntuple ====
// ==== Matrix ====
// ==== Calcmatrix ====
// ==== Interval ====
// ==== Calcinterval ====
// ==== Complex ====
// ==== CalcComplex ====
// ==== Multipart ====

// ==== General ====
function addCol(id){
	var tblBodyObj = document.getElementById(id).tBodies[0];
	
	var table = document.getElementById(id);
	var first_row = table.getElementsByTagName('tr')[0];
	var td = first_row.getElementsByTagName('td');
	var first_col = td[0];
	var clone_col = first_col.cloneNode(true);
	
	var input = clone_col.getElementsByTagName('input')[0].cloneNode(true);
	
	
	for (var x=0; x<table.getElementsByTagName('tr').length; x++) {
		var newCell = table.getElementsByTagName('tr')[x].insertCell(-1);
		var new_input = input.cloneNode(true);
		
		var attribute_value = new_input.getAttribute('name');
		new_attribute_value = attribute_value.substring(0,attribute_value.length-3)+td.length+"_"+(x+1);
		new_input.setAttribute('name',new_attribute_value);
		
		new_input.setAttribute('value','');
		
		newCell.appendChild(new_input);
	}
}

function removeCol(id,min){
	var table = document.getElementById(id);

	var allRows = table.rows;
	var numCols = allRows[0].getElementsByTagName('td').length; // get the number of columns

	if (numCols > min){
		for (var i=0; i<allRows.length; i++)
			allRows[i].deleteCell(-1);
	}
}

function incboxsize(box) {
	document.getElementById(box).rows += 1;
}
function decboxsize(box) {
	if (document.getElementById(box).rows > 1) 
		document.getElementById(box).rows -= 1;
}

// add the attribute_index() function throughout
function addRow(id,clone_row,display_index,modify_options){  // This works for now but it might be good to refactor it later.
	if(clone_row==null)
		clone_row=0;
	if(display_index==null)
		display_index=true;
	if(modify_options==null)
		modify_options=false;

	var table = document.getElementById(id);
	var allRows = table.getElementsByTagName('tr');
	var cRow = allRows[clone_row].cloneNode(true);
	
	//alert(allRows.length);

	if(display_index)
		cRow.getElementsByTagName('td')[0].innerHTML = (allRows.length+1-clone_row)+":";

	// Rename the textareas --- Should be removed because moving to <input type='text' ... >
	// Commentted out: 10/5/09
	/*var clone_tag = cRow.getElementsByTagName('textarea');
	for(var i=0;i<clone_tag.length;i++){
		var attr_name = clone_tag[i].getAttribute('name');
		attr_name = attr_name.substring(0,attr_name.length-1);
		clone_tag[i].setAttribute('name',attr_name+(allRows.length+1-clone_row));
		clone_tag[i].innerHTML = "";
	}*/
	


	// Rename the inputs
	var clone_tag = cRow.getElementsByTagName('input');
	for(var i=0;i<clone_tag.length;i++){
		if(clone_tag[i].getAttribute('type')=='radio'){
			clone_tag[i].checked = false;
			clone_tag[i].setAttribute('value',allRows.length-clone_row);
		} else {
			var attr_name = clone_tag[i].getAttribute('name');
			attr_name = attr_name.substring(0,attr_name.length-1);
			clone_tag[i].setAttribute('name',attr_name+(allRows.length+1-clone_row));
			if(clone_tag[i].getAttribute('type')=='text') // Needs some reworking to make it how I like it
				clone_tag[i].setAttribute('value','');
		}
	}
	


	// Rename the menu selections
	var clone_tag = cRow.getElementsByTagName('select');
	for(var i=0;i<clone_tag.length;i++){
		attribute_index(clone_tag[i],'name',allRows.length+1-clone_row);
		if(clone_tag[i].getAttribute('onchange')!=null){
			var attr_onchange = clone_tag[i].getAttribute('onchange');
			var attr_onchange = attr_onchange.substring(0,attr_onchange.length-2);
			clone_tag[i].setAttribute('onchange',attr_onchange+(allRows.length+1-clone_row)+")");
		}
		
		if(modify_options){
			var options = clone_tag[i].getElementsByTagName('option');
			for(var j=0; j<options.length; j++){
				if(options[j].getAttribute('value')!=null)
					attribute_index(options[j],'value',allRows.length+1-clone_row);
				if(j==0) // This might need some modification
					options[j].setAttribute('selected','selected');
			}
		}

	}
	
	var clone_tag = cRow.getElementsByTagName('div');
	for(var i=0;i<clone_tag.length;i++){
		var attr_id = clone_tag[i].getAttribute('id');
		attr_id = attr_id.substring(0,attr_id.length-1);
		clone_tag[i].setAttribute('id',attr_id+(allRows.length+1-clone_row));
		clone_tag[i].setAttribute('style',"display:none");
	}
	
	table.appendChild(cRow);
}

function attribute_index(element,attribute,index){ //possible put a null check
	var attribute_value = element.getAttribute(attribute);
	new_attribute_value = attribute_value.substring(0,attribute_value.length-1)+index;
	element.setAttribute(attribute,new_attribute_value);
}

function removeRow(id,num){
	var tbl = document.getElementById(id);
	var lastRow = tbl.rows.length;
	if (lastRow > num) 
		tbl.deleteRow(lastRow - 1);
}

// Show/hide the optional variables
var state = 'none';

function showhide(id) {
	if (state == 'block') {
		state = 'none';
	}
	else {
		state = 'block';
	}
	if(document.all) { //IS IE 4 or 5 (or 6 beta)
		eval( "document.all." + id + ".style.display = state");
	}
	if (document.layers) { //IS NETSCAPE 4 or below
		document.layers[id].display = state;
	}
	if (document.getElementById && !document.all) {
		maxwell_smart = document.getElementById(id);
		maxwell_smart.style.display = state;
	}
}

// show that question types
function showqtype(id){
	var qtypes = new Array("number", "calculated", "choices", "multans", "matching", "numfunc", "string", "essay", "draw", "ntuple", "calcntuple", "matrix", "calcmatrix", "interval", "calcinterval", "complex", "calccomplex", "multipart");
	for (var x in qtypes){
		showhidediv(qtypes[x],"none");
	}
	//document.write(id);
	showhidediv(id,"block");
	state = 'none'; // temporary solution
}

// Show/hide the random input values
function showrand(id){
	var random = new Array("rand", "rrand", "nonzerorand", "nonzerorrand", "randfrom", "randname", "randmalename", "randfemalename", "rands", "rrands", "nonzerorands", "nonzerorrands", "randsfrom", "jointrandfrom", "diffrands", "diffrrands", "diffrandsfrom", "nonzerodiffrands", "jointshuffle", "singleshuffle", "randnames", "randmalenames", "randfemalenames");
	for (var x in random){
		showhidediv(random[x],"none");
	}
	showhidediv(id,"block");
}

function select_div(id,div_array,num){ 				// this is the generic function
	if(num==null)
		num="";
	for (var x in div_array){
		showhidediv(div_array[x]+num,"none");
	}
	showhidediv(id,"block");
}

function showhidediv(id,state){
	if(document.all) { //IS IE 4 or 5 (or 6 beta)
		eval( "document.all." + id + ".style.display = state");
	}
	if (document.layers) { //IS NETSCAPE 4 or below
		document.layers[id].display = state;
	}
	if (document.getElementById && !document.all) {
		maxwell_smart = document.getElementById(id);
		maxwell_smart.style.display = state;
	}
}
