--TEST--
GHSA-f6gg-rchx-xw8j: mixed-case HTTPS URLs must use TLS
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('skip openssl extension not available');
if (!function_exists('proc_open')) die('skip proc_open() not available');
?>
--INI--
default_socket_timeout=5
--FILE--
<?php
$serverCode = <<<'CODE'
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if (!$server) {
    echo "server error: $errstr\n";
    exit(1);
}
echo stream_socket_get_name($server, false), "\n";
$conn = stream_socket_accept($server, 10);
if (!$conn) {
    echo "no connection\n";
    exit(1);
}
$request = fread($conn, 1);
if ($request === "\x16") {
    echo "TLS\n";
} else {
    while (($line = stream_get_line($conn, 8192, "\r\n")) !== false) {
        $request .= $line . "\r\n";
        if ($line === '') {
            break;
        }
    }
    $request .= stream_get_contents($conn, strlen('opensec-sensitive-body'));
    echo strpos($request, 'Authorization: Basic ') !== false
        && strpos($request, 'X-Opensec-Request: validation') !== false
        && strpos($request, 'opensec-sensitive-body') !== false
            ? "plaintext credentials and body\n"
            : "unexpected plaintext request\n";
}
fclose($conn);
fclose($server);
CODE;

$proc = proc_open(escapeshellarg(PHP_BINARY) . ' -n -r ' . escapeshellarg($serverCode), [1 => ['pipe', 'w']], $pipes);
$addr = trim(fgets($pipes[1]));

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'content' => 'opensec-sensitive-body',
        'header' => "X-Opensec-Request: validation\r\n",
        'timeout' => 5,
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);
var_dump(@file_get_contents("HTTPS://alice:secret@$addr/secret", false, $context));
echo stream_get_contents($pipes[1]);
fclose($pipes[1]);
proc_close($proc);
?>
--EXPECT--
bool(false)
TLS
