--TEST--
Bug #81726 (phar wrapper: DOS when using quine gzip file)
--SKIPIF--
<?php
if (!extension_loaded("phar")) die("skip phar extension not available");
if (!extension_loaded("zlib")) die("skip zlib extension not available");
?>
--FILE--
<?php
/* upstream's bug81726.gz (a gzip quine), inline: patches must be text only */
file_put_contents(__DIR__ . "/bug81726.gz", base64_decode('H4sICAAAAAAA/3F1aW5lLmd6AAAYAOf/H4sICAAAAAAA/3F1aW5lLmd6AAAYAOf/QhZHFgcABQD6/0IWRxYHAAUA+v8ABQD6/wAUAOv/QhZHFgcABQD6/wAFAPr/ABQA6/9CiCHEAAAUAOv/QoghxAAAFADr/0KIIcQAABQA6/9CiCHEAAAUAOv/QoghxAAAAAD//wAAAP//AA0A8v9CiCHEAAAAAP//AAAA//8ADQDy/4NwoBwA/3n/qcwAAACDcKAcAP95/6nMAAAA'));
var_dump(fopen("phar://" . __DIR__ . "/bug81726.gz", "r"));
?>
--CLEAN--
<?php
@unlink(__DIR__ . "/bug81726.gz");
?>
--EXPECTF--
Warning: fopen(phar://%s): failed to open stream: unable to decompress gzipped phar archive "%s" in %s on line %d
bool(false)
