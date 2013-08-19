<!DOCTYPE HTML>
<html>
<head>
<script type="text/javascript">
function WebSocketTest()
{
  if ("WebSocket" in window)
  {
     alert("WebSocket is supported by your Browser!");
     // Let us open a web socket
     var ws = new WebSocket("ws://localhost:9998/echo");
     ws.onopen = function()
     {
        // Web Socket is connected, send data using send()
        ws.send("Message to send");
        alert("Message is sent...");
     };
     ws.onmessage = function (evt) 
     { 
        var received_msg = evt.data;
        alert("Message is received...");
     };
     ws.onclose = function()
     { 
        // websocket is closed.
        alert("Connection is closed..."); 
     };
  }
  else
  {
     // The browser doesn't support WebSocket
     alert("WebSocket NOT supported by your Browser!");
  }
}
</script>
</head>
<body>
<div id="sse">
   <a href="javascript:WebSocketTest()">Run WebSocket</a>
</div>
</body>
</html>
<!--<html>
    <head>
        <title>WebSocket</title>

        <style>
            html,body{font:normal 0.9em arial,helvetica;}
            #log {width:440px; height:200px; border:1px solid #7F9DB9; overflow:auto;}
            #msg {width:330px;}
        </style>

        <script>
            var socket;

            function init() {
                var host = "ws://websocket.local/server.php";
                try {
                    socket = new WebSocket(host);
                    log('WebSocket - status ' + socket.readyState);
                    socket.onopen = function(msg) {
                        log("Welcome - status " + this.readyState);
                    };
                    socket.onmessage = function(msg) {
                        log("Received: " + msg.data);
                    };
                    socket.onclose = function(msg) {
                        log("Disconnected - status " + this.readyState);
                    };
                }
                catch (ex) {
                    log(ex);
                }
                $("msg").focus();
            }

            function send() {
                var txt, msg;
                txt = $("msg");
                msg = txt.value;
                if (!msg) {
                    alert("Message can not be empty");
                    return;
                }
                txt.value = "";
                txt.focus();
                try {
                    socket.send(msg);
                    log('Sent: ' + msg);
                } catch (ex) {
                    log(ex);
                }
            }
            function quit() {
                log("Goodbye!");
                socket.close();
                socket = null;
            }

            // Utilities
            function $(id) {
                return document.getElementById(id);
            }
            function log(msg) {
                $("log").innerHTML += "<br>" + msg;
            }
            function onkey(event) {
                if (event.keyCode == 13) {
                    send();
                }
            }
        </script>

    </head>
    <body onload="init()">
        <h3>WebSocket v2.00</h3>
        <div id="log"></div>
        <input id="msg" type="textbox" onkeypress="onkey(event)"/>
        <button onclick="send()">Send</button>
        <button onclick="quit()">Quit</button>
        <div>Commands: hello, hi, name, age, date, time, thanks, bye</div>
    </body>
</html>-->
<?php
//error_reporting(E_ALL);
//
//echo "<h2>TCP/IP Connection</h2>\n";
//
///* Get the port for the WWW service. */
//$service_port = getservbyname('www', 'tcp');
//
///* Get the IP address for the target host. */
//$address = gethostbyname('www.example.com');
//
///* Create a TCP/IP socket. */
//$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
//if ($socket < 0) {
//    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
//} else {
//    echo "OK.<br />";
//}
//
//echo "Attempting to connect to '$address' on port '$service_port'...";
//$result = socket_connect($socket, $address, $service_port);
//if ($result < 0) {
//    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
//} else {
//    echo "OK.<br />";
//}
//
//$in = "HEAD / HTTP/1.1\r\n";
//$in .= "Host: www.example.com\r\n";
//$in .= "Connection: Close\r\n\r\n";
//$out = '';
//
//echo "Sending HTTP HEAD request...";
//socket_write($socket, $in, strlen($in));
//echo "OK.<br />";
//
//echo "Reading response:<br /><br />";
//while ($out = socket_read($socket, 2048)) {
//    echo $out;
//}
//
//echo "Closing socket...";
//socket_close($socket);
//echo "OK.<br /><br />";