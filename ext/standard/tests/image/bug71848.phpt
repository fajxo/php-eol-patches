--TEST--
Bug #71848 (getimagesize with $imageinfo returns false)
--FILE--
<?php
// the upstream test image, inlined: the patch must stay text-only
$jpg =
    '/9j/4AAQSkZJRgABAQAAAQABAAD/5QAC//4APkNSRUFUT1I6IGdkLWpwZWcgdjEuMCAodXNpbmcg' .
    'SUpHIEpQRUcgdjkwKSwgZGVmYXVsdCBxdWFsaXR5Cv/bAEMACAYGBwYFCAcHBwkJCAoMFA0MCwsM' .
    'GRITDxQdGh8eHRocHCAkLicgIiwjHBwoNyksMDE0NDQfJzk9ODI8LjM0Mv/bAEMBCQkJDAsMGA0N' .
    'GDIhHCEyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMv/A' .
    'ABEIAAgACAMBIgACEQEDEQH/xAAfAAABBQEBAQEBAQAAAAAAAAAAAQIDBAUGBwgJCgv/xAC1EAAC' .
    'AQMDAgQDBQUEBAAAAX0BAgMABBEFEiExQQYTUWEHInEUMoGRoQgjQrHBFVLR8CQzYnKCCQoWFxgZ' .
    'GiUmJygpKjQ1Njc4OTpDREVGR0hJSlNUVVZXWFlaY2RlZmdoaWpzdHV2d3h5eoOEhYaHiImKkpOU' .
    'lZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4eLj5OXm5+jp6vHy8/T1' .
    '9vf4+fr/xAAfAQADAQEBAQEBAQEBAAAAAAAAAQIDBAUGBwgJCgv/xAC1EQACAQIEBAMEBwUEBAAB' .
    'AncAAQIDEQQFITEGEkFRB2FxEyIygQgUQpGhscEJIzNS8BVictEKFiQ04SXxFxgZGiYnKCkqNTY3' .
    'ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqCg4SFhoeIiYqSk5SVlpeYmZqio6Sl' .
    'pqeoqaqys7S1tre4ubrCw8TFxsfIycrS09TV1tfY2dri4+Tl5ufo6ery8/T19vf4+fr/2gAMAwEA' .
    'AhEDEQA/APf6KKKAP//Z';
$f = __DIR__ . '/bug71848_tmp.jpg';
file_put_contents($f, base64_decode($jpg));
var_dump(getimagesize($f, $info));
var_dump(array_keys($info));
?>
===DONE===
--CLEAN--
<?php @unlink(__DIR__ . '/bug71848_tmp.jpg'); ?>
--EXPECT--
array(7) {
  [0]=>
  int(8)
  [1]=>
  int(8)
  [2]=>
  int(2)
  [3]=>
  string(20) "width="8" height="8""
  ["bits"]=>
  int(8)
  ["channels"]=>
  int(3)
  ["mime"]=>
  string(10) "image/jpeg"
}
array(2) {
  [0]=>
  string(4) "APP0"
  [1]=>
  string(4) "APP5"
}
===DONE===
