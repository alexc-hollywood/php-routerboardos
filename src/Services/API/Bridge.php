<?php 

namespace RouterboardOS\Services\API;

class Bridge 
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private $sock;

    public function __construct (string $host, int $port, string $username, string $password) 
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect (): self 
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        socket_connect($this->sock, $this->host, $this->port);

        return $this;
    }

    public function disconnect (): self 
    {
        if ($this->sock)
        {
            socket_close ($this->sock);
        }
        
        return $this;
    }

    private function encode_length (int $length): string 
    {
        if ($length < 0x80) 
        {
            return chr($length);
        } 
        elseif ($length < 0x4000) 
        {
            $length |= 0x8000;
            return pack("n", $length);
        } 
        elseif ($length < 0x200000) 
        {
            $length |= 0xC00000;
            return substr(pack("N", $length), 1);
        } 
        elseif ($length < 0x10000000) 
        {
            $length |= 0xE0000000;
            return pack("N", $length);
        } 
        else 
        {
            return chr(0xF0) . pack("N", $length);
        }
    }

    private function decode_length (string $data): array 
    {
        $length = ord($data[0]);

        if (($length & 0x80) == 0x00) 
        {
            return [$length, 1];
        } 
        elseif (($length & 0xC0) == 0x80) 
        {
            return [(($length & 0x3F) << 8) + ord($data[1]), 2];
        } 
        elseif (($length & 0xE0) == 0xC0) 
        {
            return [(($length & 0x1F) << 16) + unpack("n", substr($data, 1, 2))[1], 3];
        } 
        elseif (($length & 0xF0) == 0xE0) 
        {
            return [(($length & 0x0F) << 24) + unpack("N", "\x00" . substr($data, 1, 3))[1], 4];
        } 
        else 
        {
            return [unpack("N", substr($data, 1, 4))[1], 5];
        }
    }

    private function write_sentence (array $words = []): self 
    {
        foreach ($words as $word) 
        {
            socket_write ($this->sock, $this->encode_length(strlen($word)) . $word);
        }

        socket_write ($this->sock, chr(0));

        return $this;
    }

    private function read_sentence(): array 
    {
        $sentence = [];

        while (true) 
        {
            [$word_length, $length_bytes] = $this->decode_length (socket_read($this->sock, 1));

            if ($word_length == 0) 
            {
                break;
            }

            $word = socket_read($this->sock, $word_length);

            $sentence[] = $word;
        }

        return $sentence;
    }

    public function login(): bool 
    {
        $response = $this->read_sentence();

        if ($response[0] !== '!done' || count($response) !== 2) 
        {
            return false;
        }

        $challenge = substr($response[1], strlen('=ret='));

        $hash = hash('md5', "\x00" . $this->password . $challenge);

        $response = "00" . $hash;

        $this->write_sentence(['/login', "=name={$this->username}", "=response={$response}"]);

        $response = $this->read_sentence();

        return $response[0] === '!done';
    }
    

}