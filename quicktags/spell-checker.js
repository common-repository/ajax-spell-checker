var edCanvas = null;
serviceLocation = '../service/spell-check-service.php';

function saveContent() {
	cleanUp();
	edCanvas.value = sr.innerHTML;
	window.close();
}

function onLoadInit() {
	edCanvas = window.opener.document.getElementById("content");
	sr = document.getElementById('spellResults');
	status = document.getElementById('status');

	sr.innerHTML = edCanvas.value;
	htmlSrc = sr.innerHTML;
	status.innerHTML = "idle";

	addEvent(document,"click", handleClicks);

	resizeInputs();
	checkSpelling();
}
