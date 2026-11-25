--TEST--
Bug #79797 (Use of freed hash key in the phar_parse_zipfile function)
--SKIPIF--
<?php
if (!extension_loaded('phar')) die('skip phar extension not available');
?>
--FILE--
<?php
/* upstream's bug79797.phar, inline: patches must be text only. phar.cache_list
 * is read at startup, so a child php loads it. */
$f = __DIR__ . '/bug79797.phar';
file_put_contents($f, base64_decode('UEsDBAAAAAAAAIFVmUd28UcF//8AAP//AAAEABIAYWFhYW51DgBXK0GEtgEAAAAAAAAAAFBLAQIAAAAAAAAAAIFVmUd28UcF//8AAP//AAAEABIAAAAAAAAAAAAAAAAAAABhYWFhbnUOAFcrQYS2AQAAAAAAAAAAAAAAAAEAAQBEAAAAMwABAAAAUEsFBgAAAAABAAEAY2NkZKAAAAAAAFBLAQJBQUJCAAAAAEVFRkZHR0dHSEhISAUAAAAPAAAAAABNTU5OT09PT+AAAAAucGhhci9hbGlhcy50eHQAAABBQUFBQkIAAAAARUVGRkdHR0dISEhIBQAAAA8AAAAAAE1NTk5PT09PEAEAAABBQUFBQQ=='));
passthru(escapeshellarg(getenv('TEST_PHP_EXECUTABLE')) . ' -n -d ' . escapeshellarg("phar.cache_list=$f")
    . ' -r ' . escapeshellarg('var_dump(extension_loaded("phar")); echo "done\n";'));
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/bug79797.phar');
?>
--EXPECT--
bool(true)
done
