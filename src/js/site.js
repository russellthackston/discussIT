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

	// Get the rollcall button element
	var messageElem = document.getElementById("navRollCall");
	var srcElem = document.getElementById("present");
	
	// Get the rollcall URL
	var href = replacePage(window.location.href, "rollcall.php");

	//Get current button label and color
	var buttonLabel = messageElem.innerHTML;
	var messageElemColor = messageElem.style.color;

	//Send Rollcall to server
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			messageElem.innerHTML = this.responseText;
			messageElem.classList.add("here");
			srcElem.classList.add("here");
			console.log(this.responseText);
			var timer = setTimeout(function () {
		        messageElem.innerHTML = buttonLabel;
   				messageElem.classList.remove("here");
				srcElem.classList.remove("here");
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
	var studentidelem = document.getElementById('newstudentid');
	var studentname = document.getElementById('newstudentname');
	var regcode = document.getElementById('newregcode');
	studentid = studentidelem.value;
	var data = {
		"studentid" : studentid,
		"studentname" : studentname.value,
		"regcode" : regcode.value
	};
	studentidelem.value = "";
	studentname.value = "";
	regcode.value = "";

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			obj = JSON.parse(this.responseText);
			var formrow = document.getElementById('newstudent');

			var newstudentrow = document.createElement('tr');
			newstudentrow.id = 'student-row-' + obj.studentid;
			var deletecell = document.createElement('td');
			var namecell = document.createElement('td');
			var isregcell = document.createElement('td');
			var idcell = document.createElement('td');
			var regcell = document.createElement('td');

			//<input onclick="deleteStudent(this)">
			var deletebutton = document.createElement('input');
			deletebutton.type = 'button';
			deletebutton.id = 'student-del-button-' + obj.studentid;
			deletebutton.setAttribute('data-studentid', obj.studentid);
			deletebutton.value = 'Delete';

			deletecell.appendChild(deletebutton);
			namecell.appendChild(document.createTextNode(obj.studentname));
			namecell.id = 'student-name-' + obj.studentid;
			isregcell.appendChild(document.createTextNode(obj.isreg));
			idcell.appendChild(document.createTextNode(obj.studentid));
			regcell.appendChild(document.createTextNode(obj.regcode));

			newstudentrow.appendChild(deletecell);
			newstudentrow.appendChild(namecell);
			newstudentrow.appendChild(isregcell);
			newstudentrow.appendChild(idcell);
			newstudentrow.appendChild(regcell);

			formrow.parentNode.insertBefore(newstudentrow, formrow.nextSibling);

			deletebutton.addEventListener("click", deleteStudent, false);

		} else {
			console.log(this);
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send(JSON.stringify(data));

}

function deleteStudent(evt) {
	var elem = evt;
	if (evt.target) {
		elem = evt.target;
	}
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


function toggleHotdog(){
	var items = document.getElementsByClassName("phoneMenu");
	var i;
	for (i = 0; i < items.length; i++) { 
		console.log(items[i]);
	    if (items[i].classList.contains("menuHidden")){
		    items[i].classList.add("menuVisable");
   		    items[i].classList.remove("menuHidden");

		} else if (items[i].classList.contains("menuVisable")){
		    items[i].classList.remove("menuVisable");
   		    items[i].classList.add("menuHidden");
		}		
	}
}

function override(btn) {
	var action = (btn.getAttribute("data-state") == "overridden") ? "undo" : "override";
	var href = replacePage(window.location.href, "overridecritique.php") + 
		"?action="+action+"&id=" + btn.getAttribute('data-critique-id');
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200 && this.responseText == "Success") {
				if (action == "undo") {
					btn.parentElement.parentElement.getElementsByTagName("span")[0].classList.remove('overridden')
					btn.setAttribute("data-state", "override");
					btn.value = "Override";
				} else {
					btn.parentElement.parentElement.getElementsByTagName("span")[0].classList.add('overridden')
					btn.setAttribute("data-state", "overridden");
					btn.value = "Undo Override";
				}
				console.log(this);
		} else {
			console.log(this);
		}
	};
	xhttp.open("get", href, true);
	xhttp.send();

}

function doCopyToClipboard(elem) {
	copyToClipboard(elem.getAttribute("data-text"));
}

// https://stackoverflow.com/a/33928558/1038760
// Copies a string to the clipboard. Must be called from within an 
// event handler such as click. May return false if it failed, but
// this is not always possible. Browser support for Chrome 43+, 
// Firefox 42+, Safari 10+, Edge and IE 10+.
// IE: The clipboard feature may be disabled by an administrator. By
// default a prompt is shown the first time the clipboard is 
// used (per session).
function copyToClipboard(text) {
    if (window.clipboardData && window.clipboardData.setData) {
        // IE specific code path to prevent textarea being shown while dialog is visible.
        return clipboardData.setData("Text", text); 

    } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
        var textarea = document.createElement("textarea");
        textarea.textContent = text;
        textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.
        document.body.appendChild(textarea);
        textarea.select();
        try {
            return document.execCommand("copy");  // Security exception may be thrown by some browsers.
        } catch (ex) {
            console.warn("Copy to clipboard failed.", ex);
            return false;
        } finally {
            document.body.removeChild(textarea);
        }
    }
}

function addNote(btn) {
	var text = document.getElementById("notetext").value;
	var order = document.getElementById("addnote").getAttribute("data-order");
	var href = replacePage(window.location.href, "addnote.php");
	var data = {
		"text" : text,
		"order" : order
	};

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById('notes').innerHTML = this.responseText;
			document.getElementById("addnote").setAttribute("data-order", parseInt(order) + 1);
			document.getElementById("notetext").value = "";
		}
	};
	xhttp.open("post", href, true);
	xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
	xhttp.send(JSON.stringify(data));
}
