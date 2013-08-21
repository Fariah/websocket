var WebSocketServer = new require('ws'),
	webSocketServer = new WebSocketServer.Server({port: 8080}),
	chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz',
	charsSize = chars.length,
	passwordLength = 8,
	clients = {};
webSocketServer.on('connection', function(ws) {
	ws.on('message', function(message) {
		var message = JSON.parse(message);
		console.log(message)
		if(message.connectType === 'create'){
			var password = generatePassword(),
				id = message.id;
			clients[id] = {};
			clients[id].id = id;
			clients[id].password = password;
			clients[id].mainUser = ws;
			clients[id].mainUser.send(JSON.stringify({password: password}));
			ws.on('close', function() {
				console.log('соединение закрыто ' + id);
				delete clients[id];
			});
		}else if(message.connectType === 'connect'){
			for(var i in clients){
				if(clients[i].password === message.password){
					clients[i].user = ws;
					console.log('send id ', clients[i].id);
					clients[i].user.send(JSON.stringify({id: clients[i].id}));
				}
			}
		}else{
			var id = message.id;
			clients[id].mainUser.send(JSON.stringify({message: message.message}));
			clients[id].user.send(JSON.stringify({message: message.message}));
		}
	});
});
function generatePassword(){
	var randomstring = '';
	for (var i = 0; i < passwordLength; i++) {
		var rnum = Math.floor(Math.random()*charsSize);
		randomstring += chars.substring(rnum, rnum + 1);
	}
	return randomstring;
}