<?php
//IMathAS:  Modify a question's code
//(c) 2006 David Lippman
	require("../validate.php");
	$pagetitle = "Question Wizard";
	require("../header.php");
	require("wizard/questionwizardhelper.php");

	//wizard
	if(isset($_POST['qtype'])){
		wizardpost($_POST);
	}
	

	if ($myrights<20) {
		echo "You need to log in as a teacher to access this page";
		require("../footer.php");
		exit;
	}
	$cid = $_GET['cid'];
	$isadmin = false;
	if (!isset($_GET['aid'])) {
		if ($_GET['cid']=="admin") {
			echo "<div class=breadcrumb><a href=\"../index.php\">Home</a> &gt; <a href=\"../admin/admin.php\">Admin</a>";
			echo "&gt; <a href=\"manageqset.php?cid=admin\">Manage Question Set</a> &gt; Modify Question</div>\n";
			if ($myrights == 100) {
				$isadmin = true;
			} else if ($myrights==75) {
				$isgrpadmin = true;
			}
		} else {
			echo "<div class=breadcrumb><a href=\"../index.php\">Home</a> ";
			if ($cid>0) {
				echo "&gt; <a href=\"course.php?cid=$cid\">$coursename</a>";
			}
			echo "&gt; <a href=\"manageqset.php?cid=$cid\">Manage Question Set</a> &gt; Modify Question</div>\n";
		}
		
	} else {
		echo "<div class=breadcrumb><a href=\"../index.php\">Home</a> ";
		if ($cid>0) {
			echo "&gt; <a href=\"course.php?cid=$cid\">$coursename</a> ";
		}
		echo "&gt; <a href=\"addquestions.php?aid={$_GET['aid']}&cid=$cid\">Add/Remove Questions</a> &gt; Modify Questions</div>";
	}
	
	if (isset($_GET['frompot'])) {
		$frompot = 1;
	} else {
		$frompot = 0;
	}
	
	if (isset($_POST['qtext'])) {
		$now = time();
		
		if (isset($_GET['id'])) { //modifying existing
			$qsetid = $_GET['id'];
			$_POST['qtext'] = preg_replace('/<([^<>]+?)>/',"&&&L$1&&&G",$_POST['qtext']);
			$_POST['qtext'] = str_replace(array("<",">"),array("&lt;","&gt;"),$_POST['qtext']);
			$_POST['qtext'] = str_replace(array("&&&L","&&&G"),array("<",">"),$_POST['qtext']);
			$_POST['description'] = str_replace(array("<",">"),array("&lt;","&gt;"),$_POST['description']);
			$isok = true;
			if ($isgrpadmin) {
				$query = "SELECT iq.id FROM imas_questionset AS iq,imas_users ";
				$query .= "WHERE iq.id='{$_GET['id']}' AND iq.ownerid=imas_users.id AND (imas_users.groupid='$groupid' OR iq.userights>2)";
				$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
				if (mysql_num_rows($result)==0) {
					$isok = false;
				}
				//$query = "UPDATE imas_questionset AS iq,imas_users SET iq.description='{$_POST['description']}',iq.author='{$_POST['author']}',iq.userights='{$_POST['userights']}',";
				//$query .= "iq.qtype='{$_POST['qtype']}',iq.control='{$_POST['control']}',iq.qcontrol='{$_POST['qcontrol']}',";
				//$query .= "iq.qtext='{$_POST['qtext']}',iq.answer='{$_POST['answer']}',iq.lastmoddate=$now ";
				//$query .= "WHERE iq.id='{$_GET['id']}' AND iq.ownerid=imas_users.id AND (imas_users.groupid='$groupid' OR iq.userights>2)";
			} 
			$query = "UPDATE imas_questionset SET description='{$_POST['description']}',author='{$_POST['author']}',userights='{$_POST['userights']}',";
			$query .= "qtype='{$_POST['qtype']}',control='{$_POST['control']}',qcontrol='{$_POST['qcontrol']}',";
			$query .= "qtext='{$_POST['qtext']}',answer='{$_POST['answer']}',lastmoddate=$now ";
			$query .= "WHERE id='{$_GET['id']}'";
			if (!$isadmin && !$isgrpadmin) { $query .= " AND (ownerid='$userid' OR userights>2);";}
			if ($isok) {
				$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
				if (mysql_affected_rows()>0) {
					echo "Question Updated. ";
				} else {
					echo "Library Assignments Updated. ";
				}
			} 
			$query = "SELECT id,filename,var FROM imas_qimages WHERE qsetid='{$_GET['id']}'";
			$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
			$imgcnt = mysql_num_rows($result);
			while ($row = mysql_fetch_row($result)) {
				if (isset($_POST['delimg-'.$row[0]])) {
					$query = "SELECT id FROM imas_qimages WHERE filename='{$row[1]}'";
					$r2 = mysql_query($query) or die("Query failed :$query " . mysql_error());
					if (mysql_num_rows($r2)==1) { //don't delete if file is used in other questions
						unlink(rtrim(dirname(__FILE__), '/\\') .'/../assessment/qimages/'.$row[1]);
					}
					$query = "DELETE FROM imas_qimages WHERE id='{$row[0]}'";
					mysql_query($query) or die("Query failed :$query " . mysql_error());
					$imgcnt--;
					if ($imgcnt==0) {
						$query = "UPDATE imas_questionset SET hasimg=0 WHERE id='{$_GET['id']}'";
						mysql_query($query) or die("Query failed :$query " . mysql_error());
					}
				} else if ($row[2]!=$_POST['imgvar-'.$row[0]]) {
					$newvar = str_replace('$','',$_POST['imgvar-'.$row[0]]);
					$disallowedvar = array('link','qidx','qnidx','seed','qdata','toevalqtxt','la','GLOBALS','laparts','anstype','kidx','iidx','tips','options','partla','partnum','score');
					if (in_array($newvar,$disallowedvar)) {
						echo "<p>$newvar is not an allowed variable name</p>";
					} else {
						$query = "UPDATE imas_qimages SET var='$newvar' WHERE id='{$row[0]}'";
						mysql_query($query) or die("Query failed :$query " . mysql_error());
					}
				}
			}
			
		} else { //adding new
			$_POST['qtext'] = preg_replace('/<([^<>]+?)>/',"&&&L$1&&&G",$_POST['qtext']);
			$_POST['qtext'] = str_replace(array("<",">"),array("&lt;","&gt;"),$_POST['qtext']);
			$_POST['qtext'] = str_replace(array("&&&L","&&&G"),array("<",">"),$_POST['qtext']);
			$_POST['description'] = str_replace(array("<",">"),array("&lt;","&gt;"),$_POST['description']);
			$mt = microtime();
			$uqid = substr($mt,11).substr($mt,2,6);
			$ancestors = '';
			if (isset($_GET['templateid'])) {
				$query = "SELECT ancestors FROM imas_questionset WHERE id='{$_GET['templateid']}'";
				$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
				$ancestors = mysql_result($result,0,0);
				if ($ancestors!='') {
					$ancestors = $_GET['templateid'] . ','. $ancestors;
				} else {
					$ancestors = $_GET['templateid'];
				}
			}
			$query = "INSERT INTO imas_questionset (uniqueid,adddate,lastmoddate,description,ownerid,author,userights,qtype,control,qcontrol,qtext,answer,hasimg,ancestors) VALUES ";
			$query .= "($uqid,$now,$now,'{$_POST['description']}','$userid','{$_POST['author']}','{$_POST['userights']}','{$_POST['qtype']}','{$_POST['control']}',";
			$query .= "'{$_POST['qcontrol']}','{$_POST['qtext']}','{$_POST['answer']}','{$_POST['hasimg']}','$ancestors');";
			$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
			$qsetid = mysql_insert_id();
			$_GET['id'] = $qsetid;			
			
			if (isset($_GET['templateid'])) {
				$query = "SELECT var,filename,alttext FROM imas_qimages WHERE qsetid='{$_GET['templateid']}'";
				$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
				while ($row = mysql_fetch_row($result)) {
					$query = "INSERT INTO imas_qimages (qsetid,var,filename,alttext) VALUES ('$qsetid','{$row[0]}','{$row[1]}','{$row[2]}')";
					mysql_query($query) or die("Query failed :$query " . mysql_error());
				}
			}
			
			if (isset($_GET['makelocal'])) {
				$query = "UPDATE imas_questions SET questionsetid='$qsetid' WHERE id='{$_GET['makelocal']}'";
				mysql_query($query) or die("Query failed :$query " . mysql_error());
				echo " Local copy of Question Created ";
				$frompot = 0;
			} else {
				echo " Question Added to QuestionSet. ";
				$frompot = 1;
			}
			
		}
		if (!isset($_GET['aid'])) {
			echo "<a href=\"manageqset.php?cid=$cid\">Return to Question Set Management</a>\n";
		} else {
			if ($frompot==1) {
				echo "<a href=\"modquestion.php?qsetid=$qsetid&cid=$cid&aid={$_GET['aid']}\">Add Question to Assessment</a>  \n";
			}
			echo "<a href=\"addquestions.php?cid=$cid&aid={$_GET['aid']}\">Return to Assessment</a>\n";
		}
		//upload image files if attached
		if ($_FILES['imgfile']['name']!='') {
			$disallowedvar = array('link','qidx','qnidx','seed','qdata','toevalqtxt','la','GLOBALS','laparts','anstype','kidx','iidx','tips','options','partla','partnum','score');
			if (trim($_POST['newimgvar'])=='') {
				echo "<p>Need to specify variable for image to be referenced by</p>";
			} else if (in_array($_POST['newimgvar'],$disallowedvar)) {
				echo "<p>$newvar is not an allowed variable name</p>";
			} else {
				$uploaddir = rtrim(dirname(__FILE__), '/\\') .'/../assessment/qimages/';
				//$filename = basename($_FILES['imgfile']['name']);
				$userfilename = preg_replace('/[^\w\.]/','',basename($_FILES['imgfile']['name']));
				$filename = $userfilename;
			
				$uploadfile = $uploaddir . $filename;
				$t=0;
				while(file_exists($uploadfile)){
					$filename = substr($filename,0,strpos($userfilename,"."))."_$t".strstr($userfilename,".");
					$uploadfile=$uploaddir.$filename;
					$t++;
				}
				$result_array = getimagesize($_FILES['imgfile']['tmp_name']); 
				if ($result_array === false) {
					echo "<p>File is not image file</p>";
				} else {
					if (move_uploaded_file($_FILES['imgfile']['tmp_name'], $uploadfile)) {
						//echo "<p>File is valid, and was successfully uploaded</p>\n";
						$_POST['newimgvar'] = str_replace('$','',$_POST['newimgvar']);
						$query = "INSERT INTO imas_qimages (var,qsetid,filename) VALUES ('{$_POST['newimgvar']}','$qsetid','$filename')";
						mysql_query($query) or die("Query failed :$query " . mysql_error());
						$query = "UPDATE imas_questionset SET hasimg=1 WHERE id='$qsetid'";
						mysql_query($query) or die("Query failed :$query " . mysql_error());
					} else {
						echo "<p>Error uploading image file!</p>\n";
						exit;
					}
				}
			}
		}
		
		//update libraries
		$newlibs = explode(",",$_POST['libs']);
				
		if (in_array('0',$newlibs) && count($newlibs)>1) {
			array_shift($newlibs);
		}
		
		if ($_POST['libs']=='') {
			$newlibs = array();
		}
		if ($isgrpadmin) {
			$query = "SELECT ili.libid FROM imas_library_items AS ili,imas_users WHERE ili.ownerid=imas_users.id ";
			$query .= "AND (imas_users.groupid='$groupid' OR ili.libid=0) AND ili.qsetid='$qsetid'";
		} else {
			$query = "SELECT libid FROM imas_library_items WHERE qsetid='$qsetid'";
			if (!$isadmin) {
				$query .= " AND (ownerid='$userid' OR libid=0)";
			}
		}
		$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
		$existing = array();
		while($row = mysql_fetch_row($result)) { 
			$existing[] = $row[0]; 
		}  
		
		$toadd = array_values(array_diff($newlibs,$existing)); 
		$toremove = array_values(array_diff($existing,$newlibs));
		
		
		
		while(count($toremove)>0 && count($toadd)>0) { 
			$tochange = array_shift($toremove); 
			$torep = array_shift($toadd); 
			$query = "UPDATE imas_library_items SET libid='$torep' WHERE qsetid='$qsetid' AND libid='$tochange'";
			mysql_query($query) or die("Query failed :$query " . mysql_error());
		} 
		if (count($toadd)>0) { 
			foreach($toadd as $libid) { 
				$query = "INSERT INTO imas_library_items (libid,qsetid,ownerid) VALUES ('$libid','$qsetid','$userid')";
				mysql_query($query) or die("Query failed :$query " . mysql_error());
			} 
		} else if (count($toremove)>0) { 
			foreach($toremove as $libid) { 
				$query = "DELETE FROM imas_library_items WHERE libid='$libid' AND qsetid='$qsetid'";
				mysql_query($query) or die("Query failed :$query " . mysql_error());
			} 
		} 
		if (count($newlibs)==0) {
			$query = "SELECT id FROM imas_library_items WHERE qsetid='$qsetid'";
			$result = mysql_query($query) or die("Query failed :$query " . mysql_error());
			if (mysql_num_rows($result)==0) {
				$query = "INSERT INTO imas_library_items (libid,qsetid,ownerid) VALUES (0,'$qsetid','$userid')";
				mysql_query($query) or die("Query failed :$query " . mysql_error());
			}
		}
		
		if ($_POST['test']=="Save and Test Question") {
			echo "<script>addr = '$imasroot/course/testquestion.php?cid=$cid&qsetid={$_GET['id']}';";
			//echo "function previewit() {";
			echo "previewpop = window.open(addr,'Testing','width='+(.4*screen.width)+',height='+(.8*screen.height)+',scrollbars=1,resizable=1,status=1,top=20,left='+(.6*screen.width-20));\n";
			echo "previewpop.focus();";
			echo "</script>";
			//echo "}";
			//echo "window.onload = previewit;";

		} else {
			require("../footer.php");
			exit;
		}
	}
	$query = "SELECT firstName,lastName FROM imas_users WHERE id='$userid'";
	$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
	$row = mysql_fetch_row($result);
	$myname = $row[1].','.$row[0];
	if (isset($_GET['id'])) {
			$query = "SELECT imas_questionset.*,imas_users.groupid FROM imas_questionset,imas_users WHERE ";
			$query .= "imas_questionset.ownerid=imas_users.id AND imas_questionset.id='{$_GET['id']}'";
			$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
			$line = mysql_fetch_array($result, MYSQL_ASSOC);

			parseText($line); //added for wizard
			
			$myq = ($line['ownerid']==$userid);
			if ($isadmin || ($isgrpadmin && $line['groupid']==$groupid) || $line['userights']==3) {
				$myq = true;
			}
			$namelist = explode(", mb ",$line['author']);
			if ($myq && !in_array($myname,$namelist)) {
				$namelist[] = $myname;
			}
			if (isset($_GET['template'])) {
				$author = $myname;
				$myq = true;
			} else {
				$author = implode(", mb ",$namelist);
			}
			foreach ($line as $k=>$v) {
				$line[$k] = str_replace('&','&amp;',$v);
			}
			
			$inlibs = array();
			
			$images = array();
			if ($line['hasimg']>0) {
				$query = "SELECT id,var,filename FROM imas_qimages WHERE qsetid='{$_GET['id']}'";
				$result = mysql_query($query) or die("Query failed : $query " . mysql_error());
				while ($row = mysql_fetch_row($result)) {
					$images['vars'][$row[0]] = $row[1];
					$images['files'][$row[0]] = $row[2];
				}
			}
			if (isset($_GET['template'])) {
				$query = "SELECT deflib,usedeflib FROM imas_users WHERE id='$userid'";
				$result = mysql_query($query) or die("Query failed : $query " . mysql_error());
				list($deflib,$usedeflib) = mysql_fetch_row($result);
				
				if (isset($_GET['makelocal'])) {
					$inlibs[] = $deflib;
					$line['description'] .= " (local for $userfullname)";
				} else {
					$line['description'] .= " (copy by $userfullname)";
					if ($usedeflib==1) {
						$inlibs[] = $deflib;
					} else {
						$query = "SELECT imas_libraries.id,imas_libraries.ownerid,imas_libraries.userights,imas_libraries.groupid ";
						$query .= "FROM imas_libraries,imas_library_items WHERE imas_library_items.libid=imas_libraries.id ";
						$query .= "AND imas_library_items.qsetid='{$_GET['id']}'";
						$result = mysql_query($query) or die("Query failed : $query " . mysql_error());
						while ($row = mysql_fetch_row($result)) {
							if ($row[2] == 8 || ($row[3]==$groupid && ($row[2]%3==2)) || $row[1]==$userid) {
								$inlibs[] = $row[0];
							}
						}
					}
				}
				/*$query = "SELECT imas_library_items.libid FROM imas_library_items,imas_libraries WHERE ";
				$query .= "imas_library_items.libid=imas_libraries.id AND (imas_libraries.ownerid=$userid OR imas_libraries.userights=2) ";
				$query .= "AND qsetid='{$_GET['id']}'";
				$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
				while ($row = mysql_fetch_row($result)) {
					$inlibs[] = $row[0];	
				}*/
				$locklibs = array();
				$addmod = "Add";
				
				$query = "SELECT qrightsdef FROM imas_users WHERE id='$userid'";
				$result = mysql_query($query) or die("Query failed : " . mysql_error());
				$line['userights'] = mysql_result($result,0,0);
			
			} else {
				if ($isgrpadmin) {
					$query = "SELECT ili.libid FROM imas_library_items AS ili,imas_users WHERE ili.ownerid=imas_users.id ";
					$query .= "AND imas_users.groupid='$groupid' AND ili.qsetid='{$_GET['id']}'";
				} else {
					$query = "SELECT libid FROM imas_library_items WHERE qsetid='{$_GET['id']}'";
					if (!$isadmin) {
						$query .= " AND ownerid='$userid'";
					}
				}
				//$query = "SELECT libid FROM imas_library_items WHERE qsetid='{$_GET['id']}' AND imas_library_items.ownerid='$userid'";
				$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
				while ($row = mysql_fetch_row($result)) {
					$inlibs[] = $row[0];	
				}
				
				$locklibs = array();
				if (!$isadmin) {
					if ($isgrpadmin) {
						$query = "SELECT ili.libid FROM imas_library_items AS ili,imas_users WHERE ili.ownerid=imas_users.id ";
						$query .= "AND imas_users.groupid!='$groupid' AND ili.qsetid='{$_GET['id']}'";
					} else if (!$isadmin) {
						$query = "SELECT libid FROM imas_library_items WHERE qsetid='{$_GET['id']}' AND imas_library_items.ownerid!='$userid'";
					}
					//$query = "SELECT libid FROM imas_library_items WHERE qsetid='{$_GET['id']}' AND imas_library_items.ownerid!='$userid'";
					$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
					while ($row = mysql_fetch_row($result)) {
						$locklibs[] = $row[0];	
					}
				}
				$addmod = "Modify";
				
				$query = "SELECT count(imas_questions.id) FROM imas_questions,imas_assessments,imas_courses WHERE imas_assessments.id=imas_questions.assessmentid ";
				$query .= "AND imas_assessments.courseid=imas_courses.id AND imas_questions.questionsetid='{$_GET['id']}' AND imas_courses.ownerid<>'$userid'";
				$result = mysql_query($query) or die("Query failed : $query" . mysql_error());
				$inusecnt = mysql_result($result,0,0);
			}
			
			if (count($inlibs)==0 && count($locklibs)==0) {
				$inlibs = array(0);
			}
			$inlibs = implode(",",$inlibs);
			$locklibs = implode(",",$locklibs);
			
			$twobx = ($line['qcontrol']=='' && $line['answer']=='');
			
			
	} else {
			$myq = true;
			$twobx = true;
			$line['description'] = "Enter description here";
			$query = "SELECT qrightsdef FROM imas_users WHERE id='$userid'";
			$result = mysql_query($query) or die("Query failed : " . mysql_error());
			$line['userights'] = mysql_result($result,0,0);
			
			//changed for proof of concept
			$line['qtype'] = "none";
			$line['control'] = '';
			$line['qcontrol'] = '';
			$line['qtext'] = '';
			$line['answer'] = '';
			$line['hasimg'] = 0;
			if (isset($sessiondata['lastsearchlibs'.$cid])) {
				//$searchlibs = explode(",",$sessiondata['lastsearchlibs']);
				$inlibs = $sessiondata['lastsearchlibs'.$cid];
			} else {
				$inlibs = '0';
			}
			$locklibs='';
			$images = array();
			$author = $myname;
			
			
			$inlibssafe = "'".implode("','",explode(',',$inlibs))."'";
			if (!isset($_GET['id']) || isset($_GET['template'])) {
				$query = "SELECT id,ownerid,userights,groupid FROM imas_libraries WHERE id IN ($inlibssafe)";
				$result = mysql_query($query) or die("Query failed : " . mysql_error());
				while ($row = mysql_fetch_row($result)) {
					if ($row[2] == 8 || ($row[3]==$groupid && ($row[2]%3==2)) || $row[1]==$userid) {
						$oklibs[] = $row[0];
					}
				}
				if (count($oklibs)>0) {
					$inlibs = implode(",",$oklibs);
				} else {$inlibs = '0';}
			}	
			
			$addmod = "Add";
	}
	$inlibssafe = "'".implode("','",explode(',',$inlibs))."'";
	
	$lnames = array();
	if (substr($inlibs,0,1)==='0') {
		$lnames[] = "Unassigned";
	} 
	$inlibssafe = "'".implode("','",explode(',',$inlibs))."'";
	$query = "SELECT name FROM imas_libraries WHERE id IN ($inlibssafe)";
	$result = mysql_query($query) or die("Query failed : " . mysql_error());
	while ($row = mysql_fetch_row($result)) {
		$lnames[] = $row[0];
	}
	$lnames = implode(", ",$lnames);

	// Added Wizard in the next line
	echo "<h2>$addmod QuestionSet Question Wizard</h2>\n";

	if (isset($inusecnt) && $inusecnt>0) {
		echo '<p style="color:red;">This question is currently being used in ';
		if ($inusecnt>1) {
			echo $inusecnt.' assessments that are not yours.  ';
		} else {
			echo 'one assessment that is not yours.  ';
		}
		echo 'In consideration of the other users, if you want to make changes other than minor fixes to this question, consider creating a new version of this question instead.  </p>';
		if (isset($_GET['qid'])) {
			echo "<p><a href=\"moddataset.php?id={$_GET['id']}&cid=$cid&aid={$_GET['aid']}&template=true&makelocal={$_GET['qid']}\">Template this question</a> for use in this assessment.  ";
			echo "This will let you modify the question for this assessment only without affecting the library version being used in other assessments.</p>";
		}
	}
	if (!$myq) {
		echo "<p>This question is not set to allow you to modify the code.  You can only view the code and make additional library assignments</p>";
	}
?>

<!-- action="..." was modified for wizard -->
<form enctype="multipart/form-data" method=post action="questionwizard.php?process=true<?php 
	if (isset($_GET['cid'])) {
		echo "&cid=$cid";
	} 
	if (isset($_GET['aid'])) {
		echo "&aid={$_GET['aid']}";
	}
	if (isset($_GET['id']) && !isset($_GET['template'])) {
		echo "&id={$_GET['id']}";
	}
	if (isset($_GET['template'])) {
		echo "&templateid={$_GET['id']}";
	}
	if (isset($_GET['makelocal'])) {
		echo "&makelocal={$_GET['makelocal']}";
	}
	if ($frompot==1) {
		echo "&frompot=1";
	}
?>">



<input type="hidden" name="hasimg" value="<?php echo $line['hasimg'];?>"/>
<p>
Description:<BR> 
<textarea cols=60 rows=4 name=description <?php if (!$myq) echo "readonly=\"readonly\"";?>><?php echo $line['description'];?></textarea>
</p>
<p>
Author: <?php echo $line['author']; ?> <input type="hidden" name="author" value="<?php echo $author; ?>">
</p>
<p>
<?php
if (!isset($line['ownerid']) || isset($_GET['template']) || $line['ownerid']==$userid || $isadmin || ($isgrpadmin && $line['groupid']==$groupid)) {
	echo "Use Rights <select name=userights>\n";
	echo "<option value=\"0\" ";
	if ($line['userights']==0) {echo "SELECTED";}
	echo ">Private</option>\n";
	echo "<option value=\"2\" ";
	if ($line['userights']==2) {echo "SELECTED";}
	echo ">Allow use, use as template, no modifications</option>\n";
	echo "<option value=\"3\" ";
	if ($line['userights']==3) {echo "SELECTED";}
	echo ">Allow use and modifications</option>\n";
}
?>
</select>
</p>
<script>
var curlibs = '<?php echo $inlibs;?>';
var locklibs = '<?php echo $locklibs;?>';
function libselect() {
	window.open('libtree.php?libtree=popup&cid=<?php echo $cid;?>&selectrights=1&libs='+curlibs+'&locklibs='+locklibs,'libtree','width=400,height='+(.7*screen.height)+',scrollbars=1,resizable=1,status=1,top=20,left='+(screen.width-420));
}
function setlib(libs) {
	if (libs.charAt(0)=='0' && libs.indexOf(',')>-1) {
		libs = libs.substring(2);
	}
	document.getElementById("libs").value = libs;
	curlibs = libs;
}
function setlibnames(libn) {
	if (libn.indexOf('Unassigned')>-1 && libn.indexOf(',')>-1) {
		libn = libn.substring(11);
	}
	document.getElementById("libnames").innerHTML = libn;
}
function swapentrymode() {
	var butn = document.getElementById("entrymode");
	if (butn.value=="2-box entry") {
		document.getElementById("qcbox").style.display = "none";
		document.getElementById("abox").style.display = "none";
		document.getElementById("control").rows = 20;
		butn.value = "4-box entry";
	} else {
		document.getElementById("qcbox").style.display = "block";
		document.getElementById("abox").style.display = "block";
		document.getElementById("control").rows = 10;
		butn.value = "2-box entry";
	}
}
function incboxsize(box) {
	document.getElementById(box).rows += 1;
}
function decboxsize(box) {
	if (document.getElementById(box).rows > 1) 
		document.getElementById(box).rows -= 1;
}
</script>
<p>
My library assignments: <span id="libnames"><?php echo $lnames;?></span><input type=hidden name="libs" id="libs" size="10" value="<?php echo $inlibs;?>">
<input type=button value="Select Libraries" onClick="libselect()">
</p>



<p>
Question type: <select name=qtype onchange="javascript:showqtype(this.value)" <?php if (!$myq) echo "disabled=\"disabled\"";?>>
	<option value="none" <?php if ($line['qtype']=="none") {echo "SELECTED";} ?> disabled="disabled"> --- Select a Question Type --- </option>
	<option value="number" <?php if ($line['qtype']=="number") {echo "SELECTED";} ?>>Number</option>
	<option value="calculated" <?php if ($line['qtype']=="calculated") {echo "SELECTED";} ?>>Calculated Number</option>
	<option value="choices" <?php if ($line['qtype']=="choices") {echo "SELECTED";} ?>>Multiple-Choice</option>
	<option value="multans" <?php if ($line['qtype']=="multans") {echo "SELECTED";} ?>>Multiple-Answer</option>
	<option value="matching" <?php if ($line['qtype']=="matching") {echo "SELECTED";} ?>>Matching</option>
	<option value="numfunc" <?php if ($line['qtype']=="numfunc") {echo "SELECTED";} ?>>Function</option>
	<option value="string" <?php if ($line['qtype']=="string") {echo "SELECTED";} ?>>String</option>
	<option value="essay" <?php if ($line['qtype']=="essay") {echo "SELECTED";} ?>>Essay</option>
	<option value="draw" <?php if ($line['qtype']=="draw") {echo "SELECTED";} ?>>Drawing</option>
	<option value="ntuple" <?php if ($line['qtype']=="ntuple") {echo "SELECTED";} ?>>N-Tuple</option>
	<option value="calcntuple" <?php if ($line['qtype']=="calcntuple") {echo "SELECTED";} ?>>Calculated N-Tuple</option>
	<option value="matrix" <?php if ($line['qtype']=="matrix") {echo "SELECTED";} ?>>Numerical Matrix</option>
	<option value="calcmatrix" <?php if ($line['qtype']=="calcmatrix") {echo "SELECTED";} ?>>Calculated Matrix</option>
	<option value="interval" <?php if ($line['qtype']=="interval") {echo "SELECTED";} ?>>Interval</option>
	<option value="calcinterval" <?php if ($line['qtype']=="calcinterval") {echo "SELECTED";} ?>>Calculated Interval</option>
	<option value="complex" <?php if ($line['qtype']=="complex") {echo "SELECTED";} ?>>Complex</option>
	<option value="calccomplex" <?php if ($line['qtype']=="calccomplex") {echo "SELECTED";} ?>>Calculated Complex</option>
	<option value="multipart" <?php if ($line['qtype']=="multipart") {echo "SELECTED";} ?>>Multipart</option>
</select>
</p>





<p>
<a href="#" onclick="window.open('<?php echo $imasroot;?>/help.php?section=writingquestions','Help','width=400,height=300,toolbar=1,scrollbars=1,resizable=1,status=1,top=20,left='+(screen.width-420))">Writing Questions Help</a> 
<a href="#" onclick="window.open('<?php echo $imasroot;?>/assessment/libs/libhelp.php','Help','width=400,height=300,toolbar=1,scrollbars=1,resizable=1,status=1,top=20,left='+(screen.width-420))">Macro Library Help</a> 




<!-- Begin: Added for wizard -->

<script LANGUAGE="JavaScript" src="wizard/questionwizard.js"></script>

<?php //$qtype=$line['qtype']; // do not think necessary ?>

<?php echo createDivs($myq,$line); ?>

<!-- End: Added for wizard -->

<div id=imgbox>
<input type="hidden" name="MAX_FILE_SIZE" value="500000" /> 
Image file: <input type="file" name="imgfile"/> assign to variable: <input type="text" name="newimgvar" size="10"/><br/>
<?php
if (isset($images['vars']) && count($images['vars'])>0) {
	echo "Images:<br/>\n";
	foreach ($images['vars'] as $id=>$var) {
		echo "Variable: <input type=\"text\" name=\"imgvar-$id\" value=\"\$$var\" size=\"10\"/> <a href=\"$imasroot/assessment/qimages/{$images['files'][$id]}\" target=\"_blank\">View</a> Delete? <input type=checkbox name=\"delimg-$id\"/><br/>";	
	}
	
}
?>
</div>
<p>
<input type=submit value="Save">
<input type=submit name=test value="Save and Test Question">
</p>
</form>


<?php
	require("../footer.php");
?>
