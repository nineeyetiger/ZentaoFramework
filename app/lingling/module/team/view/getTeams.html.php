<?php include '../../common/view/header.html.php';?>

<div style="align:center">
<table>
	<tr>
		<th>球队名称</th>
		<th>球队ID</th>
	</tr>
	<?php 
	if(! empty($teams)){
		foreach($teams as $team){
			echo "<tr>";
			echo "<td>$team->name</td>";
			echo "<td>$team->id</td>";
			echo "</tr>";
		}
	}
	?>
</table>
</div>
<?php include '../../common/view/footer.html.php';?>
