var wHeight = 0, wWidth = 0, owHeight = 0, owWidth = 0, sr = null, status = null, suggestions = null, htmlSrc = null;
var serviceLocation = 'spell-check-service'
comment = false;

function cleanUp() {
	for(var i = 0; i < suggestions.length; i++){
		var a = document.getElementById('w'+i);
		var m = document.getElementById('m'+i);

		if(a){
			var t = document.createTextNode(a.innerHTML);
			a.parentNode.replaceChild(t,a);
		}
		if(m)
			m.parentNode.removeChild(m);
	}
}

function resizeInputs() {
	if (self.innerHeight) {
		 wHeight = self.innerHeight - 80;
		 wWidth = self.innerWidth - 16;
	} else {
		 wHeight = document.body.clientHeight - 80;
		 wWidth = document.body.clientWidth - 16;
	}

	sr.style.height = Math.abs(wHeight) + 'px';
	sr.style.width  = Math.abs(wWidth) + 'px';
}

function ajaxOnLoading() {
	status.innerHTML = 'Sending ...';
}

function ajaxOnLoaded() {
	status.innerHTML = 'Sending ... done';
}

function ajaxOnInteractive() {
	status.innerHTML = 'Receiving ...';
}

function ajaxOnCompletion() {
	status.innerHTML = 'Receiving ... done';
	timer = window.setTimeout('clearStatus()', 1000);
}

function clearStatus() {
	status.innerHTML = 'idle';
}

function checkSpelling() {
	htmlSrc = sr.innerHTML.replace(/<span class="error" id="w[0-9]+">([^<]*)<\/span>/g,"$1");

	ajax = new sack(serviceLocation)
	ajax.setVar('do', 'check');
	ajax.setVar('content', escape(htmlSrc));

	ajax.method = 'POST';
	ajax.onLoading = ajaxOnLoading;
	ajax.onLoaded = ajaxOnLoaded;
	ajax.onInteractive = ajaxOnInteractive;
	ajax.onCompletion = ajaxOnCompletion;
	ajax.execute = true;

	ajax.runAJAX();
}

function addToPersonal(word) {
	var ajax = new sack(serviceLocation);
	ajax.setVar('do', 'add');
	ajax.setVar('content', escape(word));

	ajax.method = 'POST';
	ajax.onLoading = ajaxOnLoading;
	ajax.onLoaded = ajaxOnLoaded;
	ajax.onInteractive = ajaxOnInteractive;
	ajax.onCompletion = ajaxOnCompletion;
	ajax.execute = true;

	ajax.runAJAX();
}

function storeReplacement(bad, good) {
	var ajax = new sack(serviceLocation);
	ajax.setVar('do', 'store');
	ajax.setVar('content', escape(bad+':'+good));

	ajax.method = 'POST';
	ajax.onLoading = ajaxOnLoading;
	ajax.onLoaded = ajaxOnLoaded;
	ajax.onInteractive = ajaxOnInteractive;
	ajax.onCompletion = ajaxOnCompletion;
	ajax.execute = true;

	ajax.runAJAX();
}

function updateDisplay(data) {
	suggestions = data;
	html = '';
	offset = 0;
	for(i = 0; i < data.length; i++) {
		var wOffset = data[i]['o'];
		var wLength = data[i]['l'];
		var wSug = data[i]['value'];
		var contentBefore = htmlSrc.substring(offset, wOffset);
		var word = '<span class="error" id="w' + i + '">' + htmlSrc.substring(wOffset, wOffset + wLength) + '</span>';

		html += (contentBefore + word);
		offset = wOffset + wLength;
	}
	html += htmlSrc.substring(offset);
	sr.innerHTML = html;
}

function toggle(obj){
	var menu = document.getElementById(obj.id.replace("w","m"));
	if(!menu){
		left = obj.offsetLeft - sr.scrollLeft;
		top = obj.offsetTop + obj.offsetHeight - sr.scrollTop;
		id = obj.id.replace('w', '');

		menu = document.createElement('ul');
		menu.innerHTML = renderSuggestions(id);
		menu.className = 'menu';
		menu.style.left = left + 'px';
		menu.style.top = top + 'px';
		menu.style.display = 'block';
		menu.id = 'm'+id;

		sr.appendChild(menu);
	} else {
		menu.parentNode.removeChild(menu);
	}
}

function renderSuggestions(index) {
	var menu = '';

	if(suggestions){
		if(suggestions[index]['value'][0].length == 0) {
			menu += "<li>(No&nbsp;suggestions)</li>";
		} else {
			for(var i = 0; i < suggestions[index]['value'].length; i++) {
				menu += ('<li><a class="menuitem" id="m' + index + 's' + i + '">');
				menu += (suggestions[index]['value'][i] + '</a></li>');
			}
		}
	}

	menu += '<li><hr /><a class="menuitem" id="m' + index + 'custom">Enter&nbsp;word</a></li>';
	if(!comment)
		menu += '<li><hr /><a class="menuitem" id="m' + index + 'add">Add&nbsp;to&nbsp;dictionary</a></li>';
	return menu;
}

function handleClicks(e) {
	var src;
	if(e.target){
		src = e.target;
	} else if(e.srcElement) {
		src = e.srcElement;
	}

	if(src.className == 'error') {
		if(e.preventDefault) {
			e.preventDefault();
		} else {
			e.returnValue = false;
		}
		toggle(src);
	} else if(src.className == 'menuitem') {
		if(src.id.indexOf('custom') >= 0)
			replaceCustom(src.id.replace(/m([0-9]+)custom/,'$1'));
		else if(src.id.indexOf('add') >= 0)
			addWord(src.id.replace(/m([0-9]+)add/,'$1'));
		else
			replaceWord(src.id.replace(/m([0-9]+)s([0-9]+)/, '$1'), src.id.replace(/m([0-9]+)s([0-9]+)/, '$2'));
	} else {

		for(var i = 0; i < suggestions.length; i++) {
			var menu = document.getElementById('m' + i);
			if(menu && menu != src)
				menu.style.display = 'none';
		}
	}
}

function replaceWord(wid, sid) {
	var contentBefore = htmlSrc.substring(0, suggestions[wid]['o']);
	var newWord = suggestions[wid]['value'][sid];
	var contentAfter = htmlSrc.substring(suggestions[wid]['o'] + suggestions[wid]['l']);
	var word = document.getElementById('w'+wid);
	var menu = document.getElementById('m'+wid);

	htmlSrc = contentBefore + newWord + contentAfter;
	nwe = document.createTextNode(newWord);
	word.parentNode.replaceChild(nwe, word);
	menu.parentNode.removeChild(menu);
}

function addWord(wid) {
	var wLength = suggestions[wid]['l'];
	var wOffset = suggestions[wid]['o'];
	var newWord = htmlSrc.substring(wOffset, wOffset + wLength);
	var word = document.getElementById('w' + wid);
	var menu = document.getElementById('m' + wid);

	if(!comment)
		addToPersonal(newWord);

	nwe = document.createTextNode(newWord);
	word.parentNode.replaceChild(nwe, word);
	menu.parentNode.removeChild(menu);
}

function replaceCustom(wid) {
	var wLength = suggestions[wid]['l'];
	var wOffset = suggestions[wid]['o'];
	var contentBefore = htmlSrc.substring(0, wOffset);
	var oldWord = htmlSrc.substring(wOffset, wOffset + wLength);
	var newWord = prompt("Enter replacement");
	var contentAfter = htmlSrc.substring(wOffset + wLength);
	var span = document.getElementById('w' + wid);
	var menu = document.getElementById('m' + wid);

	if(newWord == '' || newWord == null)
		return;

	htmlSrc = contentBefore + newWord + contentAfter;

	if(!comment)
		storeReplacement(oldWord, newWord);

	nwe = document.createTextNode(newWord);
	span.parentNode.replaceChild(nwe,span);
	menu.parentNode.removeChild(menu);
}

function addEvent(obj, evt, handler) {
	if(obj.attachEvent)
		obj.attachEvent("on" + evt, handler);
	else if(obj.addEventListener)
		obj.addEventListener(evt,handler,false);
}
