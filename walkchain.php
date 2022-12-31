<?php
header("Content-Type: application/json; charset=UTF-8");
/**
 * walkchain
 *
 * Walks through the blockchain and prints out the contents of each record.
 *
 * No verification of the blockchain validity is performed; this tool simply reads
 * each of the records from the blockchain ordered as they are stored on disk.
 *
 * Marty Anstey (https://marty.anstey.ca/)
 * August 2015
 *
 */
date_default_timezone_set('UTC');
define('_magic', 0xD5E8A97F);
define('_hashalg', 'sha256');
define('_hashlen', 32);

define('_fn', 'blockchain.dat');

if (!file_exists(_fn)) exit("Can't open "._fn);
$size = filesize(_fn);
$fp = fopen(_fn,'rb');

$height = 0;

 $response= '{"blocos": 
 [';
while (ftell($fp) < $size) {

	$header = fread($fp, (13+_hashlen));

	$magic = unpack32($header,0);
	$version = ord($header[4]);
	$timestamp = unpack32($header,5);
	$prevhash = bin2hex(substr($header,9,_hashlen));
	$datalen = unpack32($header,-4);
	$data = fread($fp, $datalen);
	$hash = hash(_hashalg, $header.$data);
	$arraytratado=explode("|",$data);
	
	$response.='{
	"ID":"'.$height.'",
	"magic":"'.dechex($magic).'",
	"Versão":"'.$version.'",
	"timestamp":"'.date("H:i:s m/d/Y",$timestamp).'",
	"BlocoAnterior":"'.$prevhash.'",
	"blocoAtual":"'.$hash.'",
	"Tamanho":"'.$datalen.'",
	"Dados":"'.wordwrap($data, 100).'",
	"Nome":"'.wordwrap($arraytratado[1], 100).'",
	"Email":"'.wordwrap($arraytratado[2], 100).'",
	"Comentário":"'.wordwrap($arraytratado[3], 100).'"
	},'; 
	
}
$response.= ']}';
echo substr_replace($response, "",-3,1);
fclose($fp);

function unpack32($data,$ofs) {
	return unpack('V', substr($data,$ofs,4))[1];
}
