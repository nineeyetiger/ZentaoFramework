<?php include '../../common/view/header.html.php';?>

<?php 

foreach ($offers as $offer){
	echo $offer->keyword.'( below )</br>';
	echo "<img src=\"$offer->value\" /></br>";
}

?>

<?php include '../../common/view/footer.html.php';?>