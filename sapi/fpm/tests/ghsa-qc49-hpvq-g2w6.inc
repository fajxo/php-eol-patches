<?php
/* Raw FastCGI helpers for the GHSA-qc49-hpvq-g2w6 tests (5.6 port of the
 * helpers in upstream unread-body-*.phpt). */

function ghsa_record($type, $content, $id)
{
	return pack('CCnnCC', 1, $type, $id, strlen($content), 0, 0) . $content;
}

function ghsa_encode_length($length)
{
	return $length < 128 ? chr($length) : pack('N', $length | 0x80000000);
}

function ghsa_params($params)
{
	$encoded = '';
	foreach ($params as $name => $value) {
		$encoded .= ghsa_encode_length(strlen($name)) . ghsa_encode_length(strlen($value)) . $name . $value;
	}
	return $encoded;
}

function ghsa_request($id, $keep, $params, $stdin)
{
	return ghsa_record(1, pack('nCxxxxx', 1, $keep ? 1 : 0), $id)
		. ghsa_record(4, ghsa_params($params), $id)
		. ghsa_record(4, '', $id)
		. ghsa_record(5, $stdin, $id)
		. ghsa_record(5, '', $id);
}

function ghsa_transact($host, $port, $request)
{
	$socket = fsockopen($host, $port);
	stream_set_timeout($socket, 3);
	$written = 0;
	while ($written < strlen($request)) {
		$written += fwrite($socket, substr($request, $written));
	}
	stream_socket_shutdown($socket, STREAM_SHUT_WR);
	$response = stream_get_contents($socket);
	fclose($socket);
	return $response;
}

function ghsa_has($haystack, $needle)
{
	return strpos($haystack, $needle) !== false;
}
