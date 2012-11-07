<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
</head>

<body>

<?php  if(empty($_POST)){?>

<form action="index.php?m=ring&f=add" enctype="multipart/form-data" method="POST">
<input type="file" name="ring" id="ring" accept="audio/mp3"></input>
<br/><br/>
球队专属闹铃:    <input type="radio" name="type" value="team" checked="checked"></input>
<br/><br/>
苹果系统闹铃:    <input type="radio" name="type" value="android" ></input>
<br/><br/>
安卓系统闹铃:    <input type="radio" name="type" value="ios"></input>
<br/><br/>
所属球队ＩＤ             <input type="text" name="teamId" value=""></input>
<br/><br/>
<input type="submit" value="添加"></input>
</form>

<?php }else{ 
	echo $filename."<br/>";
	echo $tmpDir."<br/>";
	echo $ringType."<br/>";
	echo $dstPath."<br/>";
}?>

</body>

</html>