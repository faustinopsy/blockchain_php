<?php
/**
 * A very simple BlockChain implementation intended to illustrate the concept.
 *
 * Marty Anstey (https://marty.anstey.ca/)
 * August 2015
 *
 * The block index simply maps a block to a disk offset for convenience.
 * It's not necessary, but it makes it much easier to quickly locate any
 * block within the chain.
 *
 * ISAM index:
 * [4] count
 * -----------
 * [4] offset
 * [4] length
 * ...
 *
 * Block format:
 * [4] magic
 * [1] format (0x01)
 * [4] timestamp
 * [n] hash of previous block
 * [4] data length
 * [?] data
 *
 *
 */
define('_magic', 0xD5E8A97F);
define('_hashalg', 'sha256');
define('_hashlen', 32);
define('_blksize', (13 + _hashlen));

/*
$res = addblock('blockchain.dat','Some data');
if ($res!==TRUE) exit("ERROR: ".$res."\n");
*/

function addblock($fn,$data,$genesis=FALSE) {
	$indexfn = $fn.'.idx';
	if (!$genesis) {
		if (!file_exists($fn)) return('Missing blockchain data file!');
		if (!file_exists($indexfn)) return('Missing blockchain index file!');
		// get disk location of last block from index
		if (!$ix = fopen($indexfn, 'r+b')) return("Can't open ".$indexfn);
		$maxblock = unpack('V', fread($ix,4))[1];
		$zpos = (($maxblock*8)-4);
		fseek($ix, $zpos, SEEK_SET);
		$ofs = unpack('V', fread($ix, 4))[1];
		$len = unpack('V', fread($ix, 4))[1];
		// read last block and calculate hash
		if (!$bc = fopen($fn,'r+b')) return("Can't open ".$fn);
		fseek($bc, $ofs, SEEK_SET);
		$block = fread($bc, $len);
		$hash = hash(_hashalg, $block);
		// add new block to the end of the chain
		fseek($bc, 0, SEEK_END);
		$pos = ftell($bc);
		write_block($bc, $data, $hash);
		fclose($bc);
		// update index
		update_index($ix, $pos, strlen($data), ($maxblock+1));
		fclose($ix);
		return TRUE;
	}
	else
	{
		if (file_exists($fn)) return('Blockchain data file already exists!');
		if (file_exists($indexfn)) return('Blockchain index file already exists!');
		$bc = fopen($fn, 'wb');
		$ix = fopen($indexfn, 'wb');
		write_block($bc, $data, str_repeat('00', _hashlen));
		update_index($ix, 0, strlen($data), 1);
		fclose($bc);
		fclose($ix);
		return TRUE;
	}
}

function write_block(&$fp, $data, $prevhash) {
	fwrite($fp, pack('V', _magic), 4);                // Magic
	fwrite($fp, chr(1), 1);                           // Version
	fwrite($fp, pack('V', time()), 4);                // Timestamp
	fwrite($fp, hex2bin($prevhash), _hashlen);        // Previous Hash
	fwrite($fp, pack('V', strlen($data)), 4);         // Data Length
	fwrite($fp, $data, strlen($data));                // Data
}

function update_index(&$fp, $pos, $datalen, $count) {
	fseek($fp, 0, SEEK_SET);
	fwrite($fp, pack('V', $count), 4);                // Record count
	fseek($fp, 0, SEEK_END);
	fwrite($fp, pack('V', $pos), 4);                  // Offset
	fwrite($fp, pack('V', ($datalen+_blksize)), 4);		// Length
}
