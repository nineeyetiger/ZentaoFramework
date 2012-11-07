<?php include "../../common/view/header.html.php"?>
<table>
	<tr>
		<th>功能</th>
		<th>条件一</th>
		<th>条件二</th>
		<th>操作</th>
	</tr>
	<tr>
		<td>查询所有赛事信息</td>
		<td></td>		
		<td></td>
		<td>
			<form action="index.php" method="GET">
				<input type="hidden" name="m" value="game"/>
				<input type="hidden" name="f" value="getTournaments"/>
				<input type="hidden" name="t" value="json"/>
				<input type="submit" value="查询"/>
			</form>
		</td>
	</tr>
	<tr>
		<form action="index.php" method="GET" target="_blank" onSubmit="return checkInput(537)">
			<input type="hidden" name="m" value="team"/>
			<input type="hidden" name="f" value="getTeams"/>
			<td>查询赛事中的参赛队</td>
			<td><input type="text" id="537" name="tournamentId" value="请输入赛事id" onmousedown="onMouseDown()"/></td>
			<td></td>
			<td>												
				<input type="submit" value="查询"/>								
			</td>
		</form>
	</tr>
	<tr>
		<form action="index.php" method="GET" onSubmit="return (checkInput(538) && checkInput(539))">
			<input type="hidden" name="m" value="game"/>
			<input type="hidden" name="f" value="getGames"/>
			<td>查询球队的比赛</td>					
			<td><input type="text" id="539" name="tournamentId" value="请输入赛事id" onmousedown="onMouseDown(539)"/></td>
			<td><input type="text" id="538" name="teamId" value="请输入队伍id" onmousedown="onMouseDown(538)"/></td>
			<td><input type="submit" value="查询"/></td>			
			<input type="hidden" name="t" value="json"/>
		</form>
	</tr>
	<tr>
		<form action="index.php" method="GET">
			<input type="hidden" name="m" value="ring"/>
			<input type="hidden" name="f" value="getAll"/>
			<input type="hidden" name="t" value="json"/>
			<td>查询所有铃声</td>
			<td></td>
			<td></td>
			<td><input type="submit" value="查询"/></td>
		</form>
	</tr>
	<tr>
		<form action="index.php?m=game&f=getSpecificGames&t=json" method="POST">
			<td>查询特定比赛</td>
			<td></td>
			<td><input type="text" name="gamesId"/></td>
			<td><input type="submit" value="查询"/></td>
		</form>
	</tr>	
	<tr>
		<form action="index.php" method="GET">
			<td>查询球队id</td>
			<td></td>
			<input type="hidden" name="m" value="team"/>
			<input type="hidden" name="f" value="queryByName"/>
			<input type="hidden" name="t" value="json"/>
			<td><input type="text" name="teamName"/></td>			
			<td><input type="submit" value="查询"/></td>
		</form>
	</tr>
	<tr>
		<form action="index.php" method="GET">
			<td>添加球队</td>
			<td></td>
			<input type="hidden" name="m" value="team"/>
			<input type="hidden" name="f" value="add"/>
			<input type="hidden" name="t" value="json"/>
			<td><input type="text" name="teamName"/></td>			
			<td><input type="submit" value="添加"/></td>
		</form>
	</tr>	
	<tr>
		<td>添加球队铃声</td>
		<td></td>
		<td></td>
		<td><a href="index.php?m=ring&f=add">添加</a></td>
	</tr>
	<tr>
		<form action="index.php?m=game&f=checkGames&t=json" method="POST">
			<td>查询新比赛项</td>
			<td></td>
			<td><input type="text" name="games"/></td>
			<td><input type="submit" value="查询"/></td>
		</form>
	</tr>
</table>

<a href="index.php?m=game&f=modify">高级操作</a>

<script type="text/javascript">

function onMouseDown(id){
	var obj=document.getElementById(id);
	obj.value='';
}

function checkInput(id){
	var obj=document.getElementById(id);
	id=obj.value;
	if(id == ''){
		alert("请输入id");
		return false;
	}
	for(var i=0; i<id.length; i++){
		char=id.charAt(i);
		if(char<'0' || char>'9'){
			alert("id必须是数字");
			return false;
		}
	}

	return true;
}

</script>
<?php include "../../common/view/footer.html.php"?>
