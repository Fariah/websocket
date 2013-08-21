#!/php -q
<?php
/*  >php -q server.php  */

error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();

$master = WebSocket("127.0.0.1", 1222);
$sockets = array($master);
$users = array();
$debug = false;

while (true) {
    $changed = $sockets;
//    var_dump($changed);
    socket_select($changed, $write = NULL, $except = NULL, 0);
    foreach ($changed as $socket) {
        if ($socket == $master) {
            $client = socket_accept($master);
            if ($client < 0) {
                console("socket_accept() failed");
                continue;
            } else {
                connect($client);
            }
        } else {
            $bytes = @socket_recv($socket, $buffer, 2048, 0);
            if ($bytes == 0) {
                disconnect($socket);
            } else {
                $user = getuserbysocket($socket);
                if (!$user->handshake) {
                    handshake($user, $buffer, $socket);
                } else {
//                    $action = unmask($buffer);
//                    socket_write($client, $action, strlen($action));
//                    console("Data from client: " . $action);
//                    socket_close($socket);
                    process($user, $buffer);
//                    disconnect($socket);
                }
            }
        }
    }
}

//---------------------------------------------------------------
function process($user, $msg) {
    $action = unmask($msg);
//    var_dump($decoded_data); die;
//    $action = unwrap($decoded_data);
    say("< " . $action);
    switch ($action) {
        case "hello" : send($user->socket, "hello human");
            break;
        case "hi" : send($user->socket, "zup human");
            break;
        case "name" : send($user->socket, "my name is Multivac, silly I know");
            break;
        case "age" : send($user->socket, "I am older than time itself");
            break;
        case "date" : send($user->socket, "today is " . date("Y.m.d"));
            break;
        case "time" : send($user->socket, "server time is " . date("H:i:s"));
            break;
        case "thanks": send($user->socket, "you're welcome");
            break;
        case "bye" : send($user->socket, "bye");
            break;
        default : send($user->socket, $action . " not understood");
            break;
    }
}

function send($client, $msg) {
    say("> " . $msg);
    $msg = wrap($msg);
    socket_write($client, $msg, strlen($msg));
}

function WebSocket($address, $port) {
    $master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket_create() failed");
    socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1) or die("socket_option() failed");
    socket_bind($master, $address, $port) or die("socket_bind() failed");
    socket_listen($master, 20) or die("socket_listen() failed");
    echo "Server Started : " . date('Y-m-d H:i:s') . "\n";
    echo "Master socket  : " . $master . "\n";
    echo "Listening on   : " . $address . " port " . $port . "\n\n";
    return $master;
}

function connect($socket) {
    global $sockets, $users;
    $user = new User();
    $user->id = uniqid();
    $user->socket = $socket;
    array_push($users, $user);
    array_push($sockets, $socket);
    console($socket . " CONNECTED!");
}

function disconnect($socket) {
    global $sockets, $users;
    $found = null;
    $n = count($users);
    for ($i = 0; $i < $n; $i++) {
        if ($users[$i]->socket == $socket) {
            $found = $i;
            break;
        }
    }
    if (!is_null($found)) {
        array_splice($users, $found, 1);
    }
    $index = array_search($socket, $sockets);
    socket_close($socket);
    console($socket . " DISCONNECTED!");
    if ($index >= 0) {
        array_splice($sockets, $index, 1);
    }
}

function handshake($user, $headers, $socket) {

    if (preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $headers, $match))
        $version = $match[1];
    else {
        console("The client doesn't support WebSocket");
        return false;
    }

    if ($version == 13) {
        // Extract header variables
        if (preg_match("/GET (.*) HTTP/", $headers, $match))
            $root = $match[1];
        if (preg_match("/Host: (.*)\r\n/", $headers, $match))
            $host = $match[1];
        if (preg_match("/Origin: (.*)\r\n/", $headers, $match))
            $origin = $match[1];
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match))
            $key = $match[1];

        $acceptKey = $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $acceptKey = base64_encode(sha1($acceptKey, true));

        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Accept: $acceptKey" .
                "\r\n\r\n";

//        socket_write($user, $upgrade);
        socket_write($socket, $upgrade . chr(0), strlen($upgrade . chr(0)));
        $user->handshake = true;
        console($upgrade);
        console("Done handshaking...");
        return true;
    }
    else {
        console("WebSocket version 13 required (the client supports version {$version})");
        return false;
    }
}

function getuserbysocket($socket) {
    global $users;
    $found = null;
    foreach ($users as $user) {
        if ($user->socket == $socket) {
            $found = $user;
            break;
        }
    }
    return $found;
}

function say($msg = "") {
    echo $msg . "\n";
}

function wrap($msg = "") {
    return chr(0) . $msg . chr(255);
}

function unwrap($msg = "") {
    return substr($msg, 1, strlen($msg) - 2);
}

function console($msg = "") {
    global $debug;
    if ($debug) {
        echo $msg . "\n";
    }
}

function unmask($payload) {
    $length = ord($payload[1]) & 127;

    if ($length == 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length == 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }

    $text = '';
    for ($i = 0; $i < strlen($data); ++$i) {
        $text .= $data[$i] ^ $masks[$i % 4];
    }
    return $text;
}

function encode($text) {
    // 0x1 text frame (FIN + opcode)
    $b1 = 0x80 | (0x1 & 0x0f);
    $length = strlen($text);

    if ($length <= 125)
        $header = pack('CC', $b1, $length);
    elseif ($length > 125 && $length < 65536)
        $header = pack('CCS', $b1, 126, $length);
    elseif ($length >= 65536)
        $header = pack('CCN', $b1, 127, $length);

    return $header . $text;
}

class User {

    var $id;
    var $socket;
    var $handshake;

}
?>
