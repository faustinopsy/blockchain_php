<?php
require 'blockchain.php';

// genesis block
if($_POST){
	$nome=$_POST['nome'];
	$email=$_POST['email'];
	$comment=$_POST['comment'];
	$valores=$nome.'|'.$email.'|'.$comment;
// add additional blocks
if (!$res = addblock('blockchain.dat',strtotime("now").'|'.$valores)) exit("Got error: ".$res."\n");
}

		
?>
