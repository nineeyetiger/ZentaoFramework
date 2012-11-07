<?php

if(empty($_GET)){
	$str = '上海自来水来自海上';
}else{
	$str = $_GET['str'];
}

$seg = new SaeSegment();
$ret = $seg->segment($str, 1);

print_r($ret);

if($ret === false){
	var_dump($seg->errno(), $seg->errmsg());
}


?>