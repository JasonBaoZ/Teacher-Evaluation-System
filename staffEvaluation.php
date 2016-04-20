<?php
include("checkCredentials.php");
$instrumentid = mysql_fetch_array(mysql_query("SELECT * FROM district WHERE id='$districtid'"))['instrumentid'];
$observationid = $_POST['nObservationId'];
$observation = mysql_fetch_array(mysql_query("SELECT * FROM observation WHERE id = '$observationid'"));
$classification = $observation['q6'];
$evalType = $observation['q7'];
$idoftable = mysql_fetch_array(mysql_query("SELECT * FROM surveys WHERE instrumentid='$instrumentid' AND classification = '$classification' AND evalType='$evalType'"))['id']; //to be dynamic later
$surveyData = mysql_fetch_array(mysql_query("SELECT * FROM surveys WHERE id='$idoftable'"));
function changeIdToName($id, $tbl_name){
	$temp = mysql_fetch_array(mysql_query("SELECT * FROM $tbl_name WHERE id='$id'"));
	return strtoupper($temp['last']).",".strtoupper($temp['first']);
}
function changeNameToId($name, $tbl_name){
	$last = substr($name, 0, strpos($name, ","));
	$first = substr($name, strpos($name,",")+1);
	return mysql_fetch_array(mysql_query("SELECT * FROM $tbl_name WHERE UPPER(first) = UPPER('$first') AND UPPER(last) = UPPER('$last')"))['id'];
}
$evaluatorid = $observation['evaluatorSelect'];
$evalueeid = $observation['evalueeid'];
$JSONObservation =$observation['surveyJSON'];
if($JSONObservation == ""){
	$JSONObservation=0;
}
?>
<html>
<head>
<title><?php echo $surveyData['title']; ?></title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<style>
.question-title{
	font-weight: bold;
}
body{
	font-family: "Segoe UI","Trebuchet MS",Helvetica,Arial,sans-serif;
	font-size:14px;
}
h2{
	font-size: 20px;
}
img{
	max-width: 100%;
}
.question{
	background-color: #f9f9f6;
	margin:10px;
}
.questions{
	width:70%;
	margin:0 auto;
}
.button{
	display: inline-block;
	text-decoration: none;
	border: 1px solid #dedede;
	border-color: #dedede #d8d8d8 #d3d3d3;
	color: #555;
	box-shadow: 1px 1px 1px rgba(0,0,0,0.1);
	padding: 8px 11px;
	overflow: hidden;
	vertical-align: middle;
	line-height: 16px;
	font-family: "Open Sans","Segoe UI","FreeSans",Helvetica,Arial,sans-serif;
	font-size: 14px;
	font-weight: 400;
	background: -webkit-gradient(linear,left top,left bottom,from(#f9f9f9),to(#f0f0f0));
	cursor: pointer;
}
.footer{
  overflow-x: visible;
  background-color: rgba(255,255,255,0.95);
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  margin: 0;
  text-align: center;
  padding: 1em;
  border-top: 1px solid #eee;
  z-index: 100;
}
.first{
	width:70%;
}

</style>
<script>
var alphabet = "abcdefghijklmnopqrstuvwxyz".toUpperCase().split("");
function showRubric(thisOne){
	if(thisOne.className=="showRubric"){
		var allImages = document.getElementsByName(thisOne.name);
		for(i=0; i<allImages.length;i++){
			document.getElementById("img"+allImages[i].value).style.display="none";
		}
		document.getElementById("img"+thisOne.value).style.display="block";
	}
}
function loadSurvey(){
	everything = <?php echo $surveyData['surveystring'];?>;
	var counter =1;
	var SOEcounter=1;
	std = [];
	for(var i=1; i<=everything.length;i++){
		//For mean Scores:
		std[i]=new Array();

		var newViewRubricHeader = document.getElementById("tobeclonedRubricHeader").cloneNode(true);
		newViewRubricHeader.id="viewRubric"+i;
		newViewRubricHeader.childNodes[1].childNodes[1].innerHTML=everything[i-1].standardName;
		newViewRubricHeader.childNodes[3].id="appendRubricChoices"+i;
		newViewRubricHeader.childNodes[5].id="appendClonedImages"+i;
		document.getElementById("mainAppend").appendChild(newViewRubricHeader);
		var newTable = document.getElementById("tobeclonedsurvey").cloneNode(true);
		newTable.id="standardTableDiv"+i;
		newTable.childNodes[1].id="standardTable"+i;
		newTable.childNodes[1].childNodes[3].id="appendsubstandard"+i;
		document.getElementById("mainAppend").appendChild(newTable);
		for(var j=0;j<everything[i-1].substandards.length;j++){
			std[i][(j+1)]=false;
			var newShowRubric = document.getElementById("tobeclonedshowrubricid").cloneNode(true);
			newShowRubric.id="clonedshowrubric"+i+alphabet[j];
			newShowRubric.childNodes[1].name="SR"+i;
			newShowRubric.childNodes[1].value=i+alphabet[j];
			newShowRubric.childNodes[2].textContent = " "+ everything[i-1].substandards[j].substandardID + " Rubric";
			document.getElementById("appendRubricChoices"+i).appendChild(newShowRubric);
			var newRubricImage = document.getElementById("tobeclonedImages").cloneNode(true);
			newRubricImage.id="clonedImages"+i+alphabet[j];
			newRubricImage.childNodes[1].id="img"+i+alphabet[j];
			newRubricImage.childNodes[1].src="./rubrics/"+everything[i-1].substandards[j].imagefile;
			document.getElementById("appendClonedImages"+i).appendChild(newRubricImage);
			var row = document.getElementById("standardTable"+i).insertRow();
			var cell1 = row.insertCell();
			var cell2 = row.insertCell();
			var cell3 = row.insertCell();
			var cell4 = row.insertCell();
			var cell5 = row.insertCell();
			cell1.innerHTML = everything[i-1].substandards[j].substandardtext;
			cell2.innerHTML = '<input type="radio" name="'+"q"+counter+ '" value="'+""+i+""+padTwoDigits(j+1)+""+4+ '"style="display:block; margin:auto;" onclick="changeScore(this);">';
			cell3.innerHTML = '<input type="radio" name="'+"q"+counter+ '" value="'+""+i+""+padTwoDigits(j+1)+""+3+ '"style="display:block; margin:auto;" onclick="changeScore(this);">';
			cell4.innerHTML = '<input type="radio" name="'+"q"+counter+ '" value="'+""+i+""+padTwoDigits(j+1)+""+2+ '"style="display:block; margin:auto;" onclick="changeScore(this);">';
			cell5.innerHTML = '<input type="radio" name="'+"q"+counter+ '" value="'+""+i+""+padTwoDigits(j+1)+""+1+ '"style="display:block; margin:auto;" onclick="changeScore(this);">';
			counter++;
		}
		var newScores= document.getElementById("tobeclonedscores").cloneNode(true);
		newScores.id="standardScore"+i;
		newScores.childNodes[1].innerHTML = "Standard "+i+" Total Score:";
		newScores.childNodes[2].id="ts"+i;
		newScores.childNodes[4].innerHTML="Standard "+i+" Mean Score:";
		newScores.childNodes[5].id="ms"+i;
		newScores.childNodes[6].id="tn"+i;
		document.getElementById("mainAppend").appendChild(newScores);
		if(everything[i-1].SOE){
			var newSOE = document.getElementById("tobeclonedSOE").cloneNode(true);
			newSOE.id="clonedSOE"+i;
			newSOE.childNodes[1].childNodes[1].innerHTML = "Standard "+i+": Possible sources of evidence for this domain (in addition to direct observation)."
			newSOE.childNodes[3].childNodes[1].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[3].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[5].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[7].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[9].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[11].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[13].childNodes[0].name="qc"+SOEcounter;
			newSOE.childNodes[3].childNodes[15].childNodes[0].name="qc"+SOEcounter;
			document.getElementById("mainAppend").appendChild(newSOE);
		}
		if(everything[i-1].comments){
			var newComment = document.getElementById("tobeclonedComments").cloneNode(true);
			newComment.id="clonedComment"+i;
			newComment.childNodes[1].innerHTML = "Standard "+i+" Comments:";
			newComment.childNodes[3].id="qi"+i;
			document.getElementById("mainAppend").appendChild(newComment);
		}
	}
}

function padTwoDigits(str){
	str = "" + str;
	var pad = "00";
	return pad.substring(0, pad.length - str.length) + str;
}
function init(){
	loadSurvey();
	$(':radio').mousedown(function(e){
  		var $self = $(this);
  		if( $self.is(':checked') ){
    		var uncheck = function(){
      			setTimeout(function(){$self.removeAttr('checked');},0);
			};
    		var unbind = function(){
      			$self.unbind('mouseup',up);
    		};
    		var up = function(){
      			uncheck();
      			document.getElementById("img"+this.value).style.display="none";
      			unbind();
    		};
    		$self.bind('mouseup',up);
    		$self.one('mouseout', unbind);
  		}else{
  			showRubric(this);
  		}
	});
	var JSONObservation = <?php echo $JSONObservation; ?>;
	console.log(JSONObservation);
	if(JSONObservation!=0){
		if(JSONObservation.surveyID==<?php echo $idoftable;?>){
			var counter =1;
			for(var i=0; i<everything.length;i++){
				for(var j=0;j<everything[i].substandards.length;j++){
					document.getElementsByName("q"+counter)[JSONObservation.mostStuff[i].scores[j]].checked=true;
					changeScore(document.getElementsByName("q"+counter)[JSONObservation.mostStuff[i].scores[j]]);
					counter++;
				}
				document.getElementById("qi"+(i+1)).value=JSONObservation.mostStuff[i].comments;
			}	
			document.getElementById("formativeCommentsText").value = JSONObservation.comments;
		}
	}
	
}
function changeScore(value){
	var standardNumber = parseInt(value.value.charAt(0));
	var questionNumber = parseInt(value.value.substring(1,3));
	var valueNumber = parseInt(value.value.charAt(3));
	if(std[standardNumber][questionNumber]==false){
		document.getElementById("formativets").innerHTML = (parseInt(document.getElementById("formativets").innerHTML)+ valueNumber).toFixed(3);
		document.getElementById("formativetn").innerHTML = parseInt(document.getElementById("formativetn").innerHTML) + 1;
		document.getElementById("formativems").innerHTML = (parseInt(document.getElementById("formativets").innerHTML) / parseInt(document.getElementById("formativetn").innerHTML)).toFixed(3);
		document.getElementById("ts"+standardNumber).innerHTML = (parseInt(document.getElementById("ts"+standardNumber).innerHTML)+ valueNumber).toFixed(3);
		document.getElementById("tn"+standardNumber).innerHTML = parseInt(document.getElementById("tn"+standardNumber).innerHTML) + 1;
		document.getElementById("ms"+standardNumber).innerHTML = (parseInt(document.getElementById("ts"+standardNumber).innerHTML)/ parseInt(document.getElementById("tn"+standardNumber).innerHTML)).toFixed(3);
		std[standardNumber][questionNumber] = valueNumber;
	}else{
		document.getElementById("formativets").innerHTML = (parseInt(document.getElementById("formativets").innerHTML)-parseInt(std[standardNumber][questionNumber])+valueNumber).toFixed(3);
		document.getElementById("formativems").innerHTML = (parseInt(document.getElementById("formativets").innerHTML)/ parseInt(document.getElementById("formativetn").innerHTML)).toFixed(3);
		document.getElementById("ts"+standardNumber).innerHTML = (parseInt(document.getElementById("ts"+standardNumber).innerHTML)-parseInt(std[standardNumber][questionNumber])+valueNumber).toFixed(3);
		document.getElementById("ms"+standardNumber).innerHTML = (parseInt(document.getElementById("ts"+standardNumber).innerHTML)/ parseInt(document.getElementById("tn"+standardNumber).innerHTML)).toFixed(3);
		std[standardNumber][questionNumber] = valueNumber;
	}
}
function saveSurvey(alertYesOrNo){
	var realObject = {};
	var JSONObject = [];
	var counter =1;
	for(var i=0; i<everything.length;i++){
		var stdObject = {};
		stdObject.scores = [];
		for(var j=0;j<everything[i].substandards.length;j++){
			for(var k =0; k<document.getElementsByName("q"+counter).length;k++){
				if(document.getElementsByName("q"+counter)[k].checked){
					stdObject.scores[j]=k;
				}
			}
			counter++;
		}
		if(everything[i].comments){
			stdObject.comments=document.getElementById("qi"+(i+1)).value;
		}
		JSONObject.push(stdObject);
	}
	realObject.mostStuff = JSONObject;
	realObject.comments = document.getElementById("formativeCommentsText").value;
	realObject.surveyID = <?php echo $idoftable;?>;
	console.log(realObject);
	alert("e");
	$.ajax({
        type: "POST",
        url:"saveData.php",
        data:{
            data: JSON.stringify(realObject),
            observationid: <?php echo $observationid ?>
        },
        complete: function(response){
        	console.log(response['responseText']);
        }
    });
    if(alertYesOrNo){
    	alert("Done!");
    }else{
    	$.ajax({
	        type: "POST",
	        url:"finishSurvey.php",
	        data:{
	            observationid:  <?php echo $observationid?>
	        },
	        complete: function(response){
	        	window.location.replace("evaluator.php");
    			alert("Your survey is done!");
	        }
    	});
    	
    }
}
</script>
</head>
<body onload="init();">
<div class="questions" id="mainAppend">
	<div class="question" >
		<div class="question-header">
			<h2 class="question-title">Validate WHO IS THE EVALUEE</h2>
		</div>
		<div class="question-body">
			<p><?php echo changeIdToName($evalueeid,"evaluees");?></p>
		</div>
	</div>

</div>
<br>
<div class="questions">
	<div class="question" style="width:50%; display:inline;">
		<h3 class="question-title" style="display:inline;">Formative Total Score:</h3><h3 class="question-title" style="display:inline; padding-right:5em;" id="formativets">0</h3>
		<h3 class="question-title" style="display:inline;">Formative Mean Score:</h3><h3 class="question-title" style="display:inline;" id="formativems">0.00</h3><p id="formativetn" style="display:none" >0</p>
	</div>
	<div class="question-header">
		<h2 class="question-title" >Formative Comments:</h2>
		<textarea cols="50" id="formativeCommentsText" rows="2"></textarea>
		<br>
	</div>
</div>

<div class="questions" style="display:none">
	
	<div id="tobeclonedRubricHeader" class="question">
		<div class="question-header">
			<h2 id="viewRubricNumber" class="question-title">View Standard i Rubrics</h2>
		</div>
		<div id="appendRubricChoicesID" class="question-body">

		</div>
		<div id="appendClonedImages" class="question">

		</div>
	</div>
	<!--Show Rubric Item to be appended-->
	<span id="tobeclonedshowrubricid"> <input  type="radio" class="showRubric" name="SR1" value="1A"> i Rubric &nbsp;</span>
	<!-- -->
	<!--Cloned Images to be appended-->
	<div id="tobeclonedImages" class="question">
		<img id="imgid" style="display:none" />
	</div>
	<!-- -->
	<div id="tobeclonedsurvey" class="question">
		<table class="table table-bordered">
		    <thead>
		      	<tr>
		        	<th class="first"></th>
		        	<th>4</th>
		        	<th>3</th>
		        	<th>2</th>
		        	<th>1</th>
		      	</tr>
		    </thead>
		    <tbody id="appendsubstandardID">
		    </tbody>
	  	</table>
	</div>

	<div id="tobeclonedscores" class="question" style="width:50%; display:inline;">
		<h4 class="question-title" style="display:inline;">Std. i Total Score:</h4><h4 class="question-title" style="display:inline; padding-right:5em;" id="tsid">0</h4>
		<h4 class="question-title" style="display:inline;">Std. i Mean Score:</h4><h4 class="question-title" style="display:inline;" id="msid">0.00</h4><p id="tnid" style="display:none" >0</p>
	</div>

	<div id="tobeclonedSOE" class="question">
		<div class="question-header">
			<h2 class="question-title">Standard i: Possible sources of evidence for this domain (in addition to direct observation).</h2>
		</div>
		<div class="question-body">
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="0"> Assessments &nbsp;</span>
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="1"> Student Work &nbsp;</span>	
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="2"> Documentation &nbsp;</span>	
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="3"> Relevant Data &nbsp;</span>	
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="4"> Student Records &nbsp;</span>	
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="5"> Professional Development Materials and Reflections &nbsp;</span>	
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="6"> Journals &nbsp;</span>	
			<span style="white-space: nowrap;"><input type="checkbox" name="qcid" value="7"> Other, please specify... &nbsp;	
			<input type="text" name="qcid"></span>
		</div>
	</div>
	<div id="tobeclonedComments" class="question-header">
		<h2 class="question-title" id="stdidcomments">Standard i: Comments:</h2>
		<textarea cols="50" id="qiid" rows="2"></textarea>
		<br>
	</div>
</div>

<br><br><br><br><br>
<div id="footer" class="footer">
	<div id="button-group" style="width:50%;margin:0 auto;">
		<?php echo "<a href='index.php?observationid=".$observationid."'><input action='action' class='button' type='button' value='Back'></a>";?>
		<input type="submit" class="button" name="save-changes" onclick="saveSurvey(true);" value="Save &amp; Continue Editing">
		<input id="next-submit-button" type="submit" class="button" onclick="saveSurvey(false);" name="next" value="Finish Evaluation">
	</div>
</div>
</body>
</html>
