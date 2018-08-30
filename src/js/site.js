function checkLength10(elem){
	if (elem.value.length > 10){
		elem.value = elem.value.substring(0,10);
	}
}

function activateNewCommentForm() {
	var form = document.getElementById('newcommentform');
	if (!form) {
		return;
	}
  // Check for critiques on all existing comments before allowing submit
  var allHidden = true;
  var forms = document.getElementsByClassName("votingform");
  for(var i=0; i<forms.length; i++) {
    // If there are any visible critique forms, prevent submit
      if (forms[i].style.display != "none") {
        allHidden = false;
        break;
      }
  }
  if (allHidden) {
    document.getElementById("comment").placeholder = "Add a comment";
    document.getElementById("submitcomment").disabled = false;
  } else {
    document.getElementById("comment").placeholder = "Critique all existing comments before adding your own";
    document.getElementById("submitcomment").disabled = true;
  }
}

function processNewCommentForm(e) {
    // Check for critiques on all existing comments before allowing submit
    var forms = document.getElementsByClassName("votingform");
    for(var i=0; i<forms.length; i++) {
	    // If there are any visible critique forms, prevent submit
        if (forms[i].style.display != "none") {
	        console.log("Blocking comment");
	        if (e.preventDefault) e.preventDefault();
	        return false;
        }
    }

    // Return false to prevent the default form behavior
    console.log("Allowing comment");
    return true;
}

function setupCommentForm() {
	var form = document.getElementById('newcommentform');
	if (form) {
		if (form.attachEvent) {
		    form.attachEvent("submit", processNewCommentForm);
		} else {
		    form.addEventListener("submit", processNewCommentForm);
		}
		console.log("Added listener");
	}
}

function replacePage(href, target) {
	// Remove the query string parameters
	href = href.replace(/\?\S+/g, "");

	// Replace the page name with the new one
	href = href.replace(/\w+\.php/g, target);

	// If there was not page name to replace (e.g. "http://example.com/"), add the new one
	if (!href.endsWith(target)) {
		href = href + target;
	}
	return href;
}

function showup(elem) {
	var commentid = elem.getAttribute('data-commentid');
	var upvotediv = document.getElementById("upvotediv-" + commentid);
	var downvotediv = document.getElementById("downvotediv-" + commentid);
	var cancelbutton = document.getElementById("hideupdown-" + commentid);
	cancelbutton.style.display = "inline";
	upvotediv.style.display = "block";
	downvotediv.style.display = "none";
}

function hideupdown(elem) {
	var commentid = elem.getAttribute('data-commentid');
	var upvotediv = document.getElementById("upvotediv-" + commentid);
	var downvotediv = document.getElementById("downvotediv-" + commentid);
	var cancelbutton = document.getElementById("hideupdown-" + commentid);
	cancelbutton.style.display = "none";
	upvotediv.style.display = "none";
	downvotediv.style.display = "none";
}

function up(elem) {
	// Get the vote details and display a processing message
	var commentid = elem.getAttribute('data-commentid');
	var upvotetext = document.getElementById("upvotetext-" + commentid);
	var upvotediv = document.getElementById("upvotediv-" + commentid);
	var processingElement = document.getElementById("voteprocessing-" + commentid);
	var processedElement = document.getElementById("voteprocessed-" + commentid);
	upvotediv.style.display = "none";
	processingElement.style.display = "block";

	// Send the vote to the server
	var href = replacePage(window.location.href, "vote.php");
	console.log(href);
	var data = {
		"commentid" : commentid,
		"vote" : "up",
		"text" : upvotetext.value
	};

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			processingElement.style.display = "none";
			processedElement.style.display = "block";
			var critiques = this.responseText;
			displayCritiques(critiques, commentid, "up");
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send(JSON.stringify(data));
}

function showdown(elem) {
	var commentid = elem.getAttribute('data-commentid');
	var upvotediv = document.getElementById("upvotediv-" + commentid);
	var downvotediv = document.getElementById("downvotediv-" + commentid);
	var cancelbutton = document.getElementById("hideupdown-" + commentid);
	cancelbutton.style.display = "inline";
	upvotediv.style.display = "none";
	downvotediv.style.display = "block";
}

function down(elem) {

	// Validate input
	var commentid = elem.getAttribute('data-commentid');
	var downvotetext = document.getElementById("downvotetext-" + commentid);
	if (!downvotetext.value) {

		alert("Please provide an explanation.");

	} else {

		// Get the vote details and display a processing message
		var upvotediv = document.getElementById("upvotediv-" + commentid);
		var processingElement = document.getElementById("voteprocessing-" + commentid);
		var processedElement = document.getElementById("voteprocessed-" + commentid);
		upvotediv.style.display = "none";
		processingElement.style.display = "block";

		// Send the vote to the server
		var href = replacePage(window.location.href, "vote.php");
		console.log(href);
		var data = {
			"commentid" : commentid,
			"vote" : "down",
			"text" : downvotetext.value
		};

		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				processingElement.style.display = "none";
				processedElement.style.display = "block";
				var critiques = this.responseText;
				displayCritiques(critiques, commentid, "down");
			}
		};
		xhttp.open("post", href, true);
		xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
		xhttp.send(JSON.stringify(data));

	}

}

function displayCritiques(critiques, commentid, vote) {

	// Display the critique list
	var critiquesElement = document.getElementById("critiques-" + commentid);
	critiquesElement.style.display = "block";

	// Fix and display the votes summary
	var statsElement = document.getElementById("stats-container-" + commentid);
	var ups = statsElement.getElementsByClassName("up");
	var downs = statsElement.getElementsByClassName("down");
	var totals = statsElement.getElementsByClassName("total");
	var novote = statsElement.getElementsByClassName("novote");
	if (vote == "up") {
		for (var i = 0; i < ups.length; i++) {
			var num = parseInt(ups[i].innerHTML);
			num++;
			ups[i].innerHTML = "" + num;
		}
	}
	if (vote == "down") {
		for (var i = 0; i < downs.length; i++) {
			var num = parseInt(downs[i].innerHTML);
			num--;
			downs[i].innerHTML = "" + num;
		}
	}
	for (var i = 0; i < totals.length; i++) {
		var num = parseInt(totals[i].innerHTML);
		num++;
		totals[i].innerHTML = "" + num;
	}
	for (var i = 0; i < novote.length; i++) {
		novote[i].innerHTML = "";
	}
	statsElement.style.display = "inline";

	// Remove the voting forms
	var votingformdiv = document.getElementById("votingform-" + commentid);
	var downvotediv = document.getElementById("downvotediv-" + commentid);
	var upvotediv = document.getElementById("upvotediv-" + commentid);
	votingformdiv.remove();
	upvotediv.remove();
	downvotediv.remove();

	// Display the critiques
	critiquesElement.innerHTML = critiques;
	activateNewCommentForm();

}

function setupMoreInfo() {
	// Add a listener to all radio buttons to enable/disable text boxes
	var radios = document.getElementsByTagName("input");
	var radioList = Array.prototype.slice.call(radios);
	radioList.forEach(function(element) {
		if (element.type == "radio") {
			element.onclick = function() {
				toggleMoreInfo(element);
			};
		}
	});
}

function toggleMoreInfo(elem) {
	// Disable all text boxes
	var moreinfos = document.getElementsByTagName("input");
	var moreinfoList = Array.prototype.slice.call(moreinfos);
	moreinfoList.forEach(function(element) {
		if (element.type == "text") {
			element.disabled = "disabled";
		}
	});

	// Enable this text box
	if(elem.getAttribute("data-moreinfoneeded") == "1") {
		var tbox = document.getElementById("moreinfoneeded-" + elem.value);
		console.log("moreinfoneeded-" + elem.value);
		console.log(tbox);
		tbox.disabled = "";
	}
}

function rollcall(elem) {

	// Get the vote details and display a processing message
	var messageElem = document.getElementById("rollcallresult");

	// Send the vote to the server
	var href = replacePage(window.location.href, "rollcall.php");

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			messageElem.innerHTML = this.responseText;
			console.log(this.responseText);
			var timer = setInterval(function () {
		        messageElem.innerHTML = "";
		    }, 5000);
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send();

}

function filterByRegCode() {
	var filter = document.getElementById('regcodefilter');
	var option = filter.value;
	var users = document.getElementsByClassName('user');
	for(var i = 0; i < users.length; i++) {
		var link = users[i].getElementsByTagName('a');
		if (link != null && link.length > 0) {
			var regcodes = link[0].getAttribute('data-regcodes');
			if (regcodes.includes(option)) {
				users[i].style.display = "list-item";
			} else {
				users[i].style.display = "none";
			}
		}
	}
}

function switchregcode() {
	var filter = document.getElementById('switchregcode');
	var option = filter.value;
	var href = replacePage(window.location.href, "switchregcode.php") + "?regcode=" + option;
	window.location.href = href;
}

function toggleVoteSummary(elem) {
	var commentid = elem.getAttribute('data-commentid');
	var short = document.getElementById('crit-stats-short-' + commentid);
	var long = document.getElementById('crit-stats-long-' + commentid);
	var disp = short.style.display;
	short.style.display = long.style.display;
	long.style.display = disp;
}

function showAdminTab(elem) {
	document.getElementById('userreports').style.display = "none";
	document.getElementById('rollcall').style.display = "none";
	document.getElementById('progressreports').style.display = "none";
	document.getElementById('userlist').style.display = "none";
	document.getElementById('studentlist').style.display = "none";
	document.getElementById('attachmenttypes').style.display = "none";
	document.getElementById(elem.getAttribute('data-tab')).style.display = "block";
}

function showEditStudent(elem) {
	var studentid = elem.getAttribute('data-studentid');
	document.getElementById('student-name-textfield-' + studentid).style.display = "inline";
	document.getElementById('student-edit-button-' + studentid).style.display = "inline";
	document.getElementById('student-name-' + studentid).style.display = "none";
}

function saveStudent(elem) {
	var href = replacePage(window.location.href, "editstudent.php");
	var studentid = elem.getAttribute('data-studentid');
	var studentname = document.getElementById('student-name-textfield-' + studentid).value;
	document.getElementById('student-name-' + studentid).style.display = "inline";
	document.getElementById('student-name-textfield-' + studentid).style.display = "none";
	document.getElementById('student-edit-button-' + studentid).style.display = "none";
	var data = {
		"studentid" : studentid,
		"studentname" : studentname
	};

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			console.log(this.responseText);
			obj = JSON.parse(this.responseText);
			document.getElementById('student-name-' + studentid).innerHTML = obj.studentname;
		} else {
			console.log(this);
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send(JSON.stringify(data));

}

function addStudent() {
	var href = replacePage(window.location.href, "addstudent.php");
	var studentid = document.getElementById('newstudentid');
	var studentname = document.getElementById('newstudentname');
	var regcode = document.getElementById('newregcode');
	var data = {
		"studentid" : studentid.value,
		"studentname" : studentname.value,
		"regcode" : regcode.value
	};
	studentid.value = "";
	studentname.value = "";
	regcode.value = "";

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			obj = JSON.parse(this.responseText);
			var formrow = document.getElementById('newstudent');

			var newstudentrow = document.createElement('tr');
			var deletecell = document.createElement('td');
			var namecell = document.createElement('td');
			var isregcell = document.createElement('td');
			var idcell = document.createElement('td');
			var regcell = document.createElement('td');

			//<input onclick="deleteStudent(this)">
			var deletebutton = document.createElement('input');
			deletebutton.type = 'button';
			deletebutton.id = 'student-del-button-' + studentid.value;
			deletebutton.setAttribute('data-studentid', studentid.value);
			deletebutton.value = 'Delete';

			deletecell.appendChild(deletebutton);
			namecell.appendChild(document.createTextNode(obj.studentname));
			isregcell.appendChild(document.createTextNode(obj.isreg));
			idcell.appendChild(document.createTextNode(obj.studentid));
			regcell.appendChild(document.createTextNode(obj.regcode));

			newstudentrow.appendChild(deletecell);
			newstudentrow.appendChild(namecell);
			newstudentrow.appendChild(isregcell);
			newstudentrow.appendChild(idcell);
			newstudentrow.appendChild(regcell);

			formrow.parentNode.insertBefore(newstudentrow, formrow.nextSibling);

			deletebutton.addEventListener("click", function(){
				var elem = document.getElementById('student-del-button-' + studentid.value);
				deleteStudent(elem);
			});

		} else {
			console.log(this);
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send(JSON.stringify(data));

}

function deleteStudent(elem) {
	var href = replacePage(window.location.href, "deletestudent.php");
	var studentid = elem.getAttribute('data-studentid');
	var studentname = document.getElementById('student-name-' + studentid);
	var row = document.getElementById('student-row-' + studentid);
	var data = {
		"studentid" : studentid
	};

	var r = confirm("Are you sure you want to delete '" + studentname.innerHTML.trim() + "'");
	if (r != true) {
	    return;
	}

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			row.remove();
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send(JSON.stringify(data));

}
