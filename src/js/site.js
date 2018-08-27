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
			console.log(critiques);
			displayCritiques(critiques, commentid);
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
				console.log(critiques);
				displayCritiques(critiques, commentid);
			}
		};
		xhttp.open("post", href, true);
		xhttp.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
		xhttp.send(JSON.stringify(data));

	}

}

function displayCritiques(critiques, commentid) {

	// Display the critique list
	var critiquesElement = document.getElementById("critiques-" + commentid);
	critiquesElement.style.display = "block";

	// Display the votes summary
	var votesElement = document.getElementById("votes-" + commentid);
	votesElement.style.display = "block";

	// Remove the voting forms
	var votingformdiv = document.getElementById("votingform-" + commentid);
	var downvotediv = document.getElementById("downvotediv-" + commentid);
	var upvotediv = document.getElementById("upvotediv-" + commentid);
	votingformdiv.remove();
	upvotediv.remove();
	downvotediv.remove();

	// Display the comments
	var up = 0;
	var down = 0;
	obj = JSON.parse(critiques);
	critiquesElement.innerHTML = "";
	var critiquesList = document.createElement("ul");
	critiquesList.setAttribute("class", "critiques");
	critiquesElement.appendChild(critiquesList);
	obj.forEach(function(element) {
		if (element.addstodiscussion == "0") {
			down += 1;
		} else {
			up += 1;
		}
		if (element.critiquetext) {
			var cElem = document.createElement("li");
			var classes = "critique ";
			if (element.addstodiscussion == "0") {
				classes += "down ";
			} else {
				classes += "up ";
			}
			cElem.setAttribute("class", classes);
			cElem.appendChild(document.createTextNode(element.critiquetext + " -- " + element.username));
			critiquesList.appendChild(cElem);
		}
	});

	// Update the counters
	var total = up + down;
	votesElement.innerHTML = (up + " out of " + total +
		" users thought this comment contributed to the discussion.");

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

function fadeOutWrapper() {
	var footer = document.getElementById("sitefooter");
	var oldWrapper = document.getElementById('wrapper');
	oldWrapper.setAttribute("id", "oldwrapper");
	var op = 1;  // initial opacity
    var timer = setInterval(function () {
        if (op <= 0.1) {
            clearInterval(timer);
            oldWrapper.remove();
			fadeInWrapper();
        }
        oldWrapper.style.opacity = op;
        oldWrapper.style.filter = 'alpha(opacity=' + op * 100 + ")";
        footer.style.opacity = op;
        footer.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= op * 0.1;
    }, 20);
}

function fadeInWrapper() {
	var footer = document.getElementById("sitefooter");
	var newWrapper = document.getElementById('newwrapper');
	var op = 0.1;  // initial opacity
    newWrapper.style.display = 'block';
    var timer = setInterval(function () {
        if (op >= 1){
            clearInterval(timer);
            newWrapper.setAttribute("id", "wrapper");
        }
        newWrapper.style.opacity = op;
        newWrapper.style.filter = 'alpha(opacity=' + op * 100 + ")";
        footer.style.opacity = op;
        footer.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op += op * 0.1;
    }, 20);
}

function reloadWrapper(url) {
	fadeOutWrapper();

	// Load the new page in a new div off the right side of the screen
	var newWrapper = document.createElement('div');
	newWrapper.setAttribute('id', 'newwrapper');
	newWrapper.style.opacity = 0.0;
	newWrapper.display = "none";
	document.body.insertBefore(newWrapper, document.getElementById('sitefooter'));

	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			parser=new DOMParser();
			htmlDoc=parser.parseFromString(this.responseText, "text/html");
			newWrapper.innerHTML = htmlDoc.getElementById('wrapper').innerHTML;
		}
	};
	xhttp.open("get", url, true);
	xhttp.send();
	//window.history.pushState(document.location.href, document.title, url);
	//window.history.replaceState(document.location.href, document.title, url);
}

function showThing(elem) {
	var thingid = elem.getAttribute('data-thingid');

	var href = replacePage(window.location.href, "thing.php") + "?thingid=" + thingid;
	console.log(href);
	reloadWrapper(href);
}

function showPage(target) {
	var href = replacePage(window.location.href, target)
 	reloadWrapper(href);
}

/*
	window.onpopstate = function (event) {
    var propValue;
	for(var propName in event) {
	    propValue = event[propName]

	    console.log(propName,propValue);
	}
    reloadWrapper(event.state);
};
*/

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

}
