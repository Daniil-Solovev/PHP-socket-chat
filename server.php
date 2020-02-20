<?php

require_once ("classes/Chat.php");

const SOCKET_HOST = '';
const SOCKET_PORT = 8090;

$chat   = new Chat();
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, SOCKET_PORT);

socket_listen($socket);
$client_socket_array = [$socket];

while (true) {
    $new_socket_array = $client_socket_array;
    $null = [];
    socket_select($new_socket_array, $null, $null, 0 , 10);

    if (in_array($socket, $new_socket_array)) {
        $new_socket = socket_accept($socket);
        $client_socket_array[] = $new_socket;
        $header     = socket_read($new_socket, 1024);
        $chat->sendHeaders($header, $new_socket, SOCKET_HOST, SOCKET_PORT);

        socket_getpeername($new_socket, $client_ip_address);
        $connectionACK = $chat->newConnectionACK($client_ip_address);
        $chat->send($connectionACK, $client_socket_array);

        $new_socket_array_idx = array_search($socket, $new_socket_array);
        unset($new_socket_array[$new_socket_array_idx]);
    }

    foreach ($new_socket_array as $resource) {

        while (socket_recv($resource, $socket_data, 1024, 0) >= 1) {
            $socket_message = $chat->unseal($socket_data);
            $message_obj    = json_decode($socket_message);

            if (!isset($message_obj->chat_message)) continue;
            @$chat_message  = $chat->createChatMessage($message_obj->chat_user, $message_obj->chat_message, $message_obj->sender);

            $chat->send($chat_message, $client_socket_array);

            break 2;
        }

        $socket_data = @socket_read($resource, 1024, PHP_NORMAL_READ);
        if ($socket_data === false) {
            socket_getpeername($resource, $client_ip_address);
            $connectionACK = $chat->newDisconnectedACK($client_ip_address);
            $chat->send($connectionACK, $client_socket_array);

            $new_socket_array_idx = array_search($resource, $client_socket_array);
            unset($client_socket_array[$new_socket_array_idx]);
        }
    }
}

socket_close($socket);