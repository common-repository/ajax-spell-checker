function saveContent() {
	cleanUp();
	tinyMCE.setContent(sr.innerHTML);
	tinyMCE.closeWindow(window);
}

// Fixes some charcode issues
function fixContent(html) {
	html = html.replace(new RegExp('<(p|hr|table|tr|td|ol|ul|object|embed|li|blockquote)', 'gi'),'\n<$1');
	html = html.replace(new RegExp('<\/(p|ol|ul|li|table|tr|td|blockquote|object)>', 'gi'),'</$1>\n');
	html = tinyMCE.regexpReplace(html, '<br />','<br />\n','gi');
	html = tinyMCE.regexpReplace(html, '\n\n','\n','gi');
	return html;
}

function onLoadInit() {
//	tinyMCEPopup.resizeToInnerSize();
	sr = document.getElementById('spellResults');
	status = document.getElementById('status');

	sr.innerHTML = fixContent(tinyMCE.getContent(tinyMCE.getWindowArg('editor_id')));
	htmlSrc = sr.innerHTML;
	status.innerHTML = "idle";

	addEvent(document,"click", handleClicks);

	resizeInputs();
	checkSpelling();
}
