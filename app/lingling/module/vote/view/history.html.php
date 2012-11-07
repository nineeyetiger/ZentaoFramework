<br/>
<br/>
<table>
<th>活动时间</td>
<th>最终选择</td>
<th>篮:足:忙</td>
<?php foreach($this->historys as $history):?>
<tr>
	<td><?php echo $history->date;?></td>
	<td><?php echo $history->game_name;?>
	<td><?php echo $history->vote_ratio;?></td>
</tr>
<?php endforeach;?>
</table>