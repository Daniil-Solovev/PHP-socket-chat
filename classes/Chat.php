<?php

class Chat
{
    const RFC_SOCKET_KEY = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    /**
     * @param $headers
     * @param $new_socket
     * @param $host
     * @param $port
     */
    public function sendHeaders($headers, $new_socket, $host, $port)
    {
        $headers = self::http_parse_headers($headers);
        if (empty($headers)) die('Key is not defined!');

        $key     = $headers['Sec-WebSocket-Key'];
        $bin_key = pack('H*', sha1($key.self::RFC_SOCKET_KEY));
        $s_key   = base64_encode($bin_key);

        $resp_headers =
            "HTTP/1.1 101 Switching Protocols \r\n" .
            "Upgrade: WebSocket \r\n" .
            "Connection: Upgrade \r\n" .
            "WebSocket-Origin: $host \r\n" .
            "WebSocket-Location: ws://$host':'$port/chat/server.php \r\n" .
            "Sec-WebSocket-Accept: $s_key \r\n\r\n";

        socket_write($new_socket, $resp_headers, strlen($resp_headers));
    }

    /**
     * @param bool $headers
     * @return array
     */
    private function http_parse_headers($headers = false)
    {
        $sock_headers = [];
        $lines = preg_split("/\r\n/", $headers);
        foreach($lines as $line)
        {
            $line = rtrim($line);
            if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
            {
                @$sock_headers[$matches[1]] = $matches[2];
            }
        }
        return $sock_headers;
    }

    /**
     * @param $client_ip
     * @return string
     */
    public function newConnectionACK($client_ip)
    {
        $message = "Пользователь " . $client_ip . " в сети";
        $message_array = [
            "message" => $message,
            "type"    => 'new connection',
        ];
        return $this->seal(json_encode($message_array));
    }

    /**
     * @param $client_ip
     * @return string
     */
    public function newDisconnectedACK($client_ip)
    {
        $message = "Пользователь " . $client_ip . " покинул беседу!";
        $message_array = [
            "message" => $message,
            "type"    => 'new connection',
        ];
        return $this->seal(json_encode($message_array));
    }

    /**
     * @param $socket_data
     * @return string
     */
    public function seal($socket_data)
    {
        $b1     = 0x81;
        $length = strlen($socket_data);
        $header = "";

        if ($length <= 125) {
            $header = pack("CC", $b1, $length);
        }
        elseif ($length > 125 && $length < 65536) {
            $header = pack("CCn", $b1, 126, $length);
        }
        elseif ($length > 65536) {
            $header = pack("CCNN", $b1, 127, $length);
        }

        return $header.$socket_data;
    }

    /**
     * @param $socket_data
     * @return string
     */
    public function unseal($socket_data)
    {
        $length = ord($socket_data[1]) & 127;

        if ($length == 126) {
            $mask = substr($socket_data, 4, 4);
            $data = substr($socket_data, 8);
        }
        elseif ($length == 127) {
            $mask = substr($socket_data, 10, 4);
            $data = substr($socket_data, 14);
        }
        else {
            $mask = substr($socket_data, 2, 4);
            $data = substr($socket_data, 6);
        }

        $socket_msg = "";
        for ($i = 0; $i < strlen($data); ++$i) {
            $socket_msg .= $data[$i] ^ $mask[$i%4];
        }

        return $socket_msg;
    }

    /**
     * @param $message
     * @param $client_socket_array
     * @return bool
     */
    public function send($message, $client_socket_array)
    {
        $message_length = strlen($message);

        foreach ($client_socket_array as $item) {
            @socket_write($item, $message, $message_length);
        }

        return true;
    }

    /**
     * @param $username
     * @param $message_str
     * @param bool $sender
     * @return string
     */
    public function createChatMessage($username, $message_str, $sender = false)
    {
        $message_array = [
            "type" => 'chat_message',
            "message" => $message_str,
            "sender" => $sender
        ];

        return $this->seal(json_encode($message_array));
    }
}