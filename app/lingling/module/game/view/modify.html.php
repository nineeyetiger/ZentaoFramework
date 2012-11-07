<?php include '../../common/view/header.html.php';?>

<table>
  <tr>
    <th>名称</th>
    <th>操作</th>
  </tr>
  <tr>  	
  	<td>德甲</td>
    <td><a href="http://data.sports.sina.com.cn/yingchao/calendar/?action=round&league_id=327&round=1" target="blank">查看</a></td>
  </tr>
  <tr>
    <td>意甲</td>
    <td><a href="http://data.sports.sina.com.cn/yingchao/calendar/?action=round&league_id=326&round=1" target="blank">查看</a></td>
  </tr>
  <tr>
    <td>西甲</td>
    <td><a href="http://data.sports.sina.com.cn/yingchao/calendar/?action=round&league_id=329&round=1" target="blank">查看</a></td>
  </tr>
  <tr>
    <td>英超</td>
    <td><a href="http://data.sports.sina.com.cn/yingchao/calendar/?action=round&league_id=325&round=1" target="blank">查看</a></td>
  </tr>
  <tr>
  	<td>电视转播</td>
  	<td><a href="http://sports.sina.com.cn/global/tvguide/" target="blank">查看</a></td>
  </tr>
</table>

<p style="text-align:center">
<form method="POST">
	<select name="tournament" onchange="tournamentChange()">
	<?php  
		foreach($tournaments as $id => $tournament){
			echo "<option value=$id>$tournament[0]</option>";
		}
	?>
	</select>

	<select id="round" name="round"></select>
	<select id="hostTeam" name="hostTeam"></select>
	<select id="guestTeam" name="guestTeam"></select>	
	<input type="text" name="dateTime"></input>

	<input type="submit" name="修改"></input>
</form>
</p>

<script language="Javascript">
var tournaments = new Array(
		<?php 
		$i = 0;
		$tourNum = count($tournaments);
		foreach($tournaments as $id => $tournament){
			$i ++;			
			echo "\n['$id', '$tournament[1]', [";
			
			$j = 0;
			$teamNum = count($tournament[2]);
			foreach($tournament[2] as $teamId => $teamName){
				$j ++;
				if($j != $teamNum){
					echo "\n['$teamId', '$teamName'],";
				}else{
					echo "\n['$teamId', '$teamName']";
				}
			}
			
			if($i != $tourNum){
				echo "]],\n";
			}else{
				echo "]]";
			}
		}
		?>);

function refreshTeams(ctrlId, teams){
	var ctrl = document.getElementById(ctrlId);
	// 清空所有选项
	document.all[ctrlId].options.length = 0;
	var total = teams.length;
	var i = 0;
	for(i = 0; i < total; i ++){
		ctrl.options.add(new Option(teams[i][1], teams[i][0]));
	}	
}

function refreshRounds(rounds){
	var ctrl = document.getElementById("round");
	document.all['round'].options.length = 0;
	var i = 0;
	for(i = 0; i < rounds; i ++){
		ctrl.options.add(new Option(i + 1, i + 1));
	}
}

function refresh(tournamentId){
	var len = tournaments.length;
	var i = 0;
	for(i = 0; i < len; i ++){
		if(tournaments[i][0] == tournamentId){
			refreshTeams('hostTeam', tournaments[i][2]);
			refreshTeams('guestTeam', tournaments[i][2]);
			refreshRounds(tournaments[i][1]);
			break;			
		}
	}	
}

function tournamentChange(){
	var tournamentId = document.all['tournament'].value;
//	alert(tournamentId);
	refresh(tournamentId);
}

refresh('17');

</script>


<?php include '../../common/view/footer.html.php';?>