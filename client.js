var socket = new WebSocket('ws://127.0.0.1:8080'),
	createBtn = document.getElementById('create'),
	connectBtn = document.getElementById('connect'),
	sendBtn = document.getElementById('send'),
	textField = document.getElementById('textField'),
	passwordField = document.getElementById('passwordField'),
	clientsId;
socket.onopen = function(){
	console.log('open');
}
socket.onerror = function(){
	console.log('error');
}
socket.onclose = function(){
	console.log('close');
}
createBtn.onclick = function(e){
	e.preventDefault();
	clientsId = Math.random();
	socket.send(JSON.stringify({connectType: 'create', id: clientsId}));
}
connectBtn.onclick = function(e){
	e.preventDefault();
	socket.send(JSON.stringify({connectType: 'connect',password: textField.value}));
}
sendBtn.onclick = function(e){
	e.preventDefault();
	if(clientsId) socket.send(JSON.stringify({message: textField.value, id: clientsId}));
}
socket.onmessage = function(event) {
	var incomingMessage = JSON.parse(event.data);
	if(incomingMessage.hasOwnProperty('message')) showMessage(incomingMessage.message);
	else if(incomingMessage.hasOwnProperty('id')) clientsId = incomingMessage.id;
	else if(incomingMessage.hasOwnProperty('password')) passwordField.innerHTML = incomingMessage.password;
};
function showMessage(message) {
	var messageElem = document.createElement('div');
	messageElem.appendChild(document.createTextNode(message));
	document.getElementById('subscribe').appendChild(messageElem);
}