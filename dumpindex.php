<?php
/**
 * dumpindex
 *
 * Dumps all of the records in a blockchain index.
 *
 * Marty Anstey (https://marty.anstey.ca/)
 * August 2015
 *
 */
define('_fn', 'blockchain.dat.idx');

if (!file_exists(_fn)) exit("Can't open "._fn);
$fp = fopen(_fn,'rb');
$records = unpack('V', fread($fp, 4))[1];

for ($i=0;$i<$records;$i++) {
	$ofs = unpack('V', fread($fp, 4))[1];
	$len = unpack('V', fread($fp, 4))[1];
	print str_pad($i,5,' ',STR_PAD_RIGHT)."OFS: ".str_pad($ofs,8,' ',STR_PAD_RIGHT)." LEN: $len\n";
}

fclose($fp);

function unpack32($data,$ofs) {
	return unpack('V', substr($data,$ofs,4))[1];
}
