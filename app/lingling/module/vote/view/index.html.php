<?php include '../../common/view/header.html.php';?>

<form method="GET" action>
本轮活动时间：<?php echo $this->date; ?><br/>
<input type="hidden" name="m" value="vote"/>
<input type="hidden" name="f" value="vote"/>
<input type="radio" name="game" value="basketball"/>打篮球<?php echo "($this->basketball)"?><br/>
<input type="radio" name="game" value="football" />踢足球<?php echo "($this->football)"?><br/>
<input type="radio" name="game" value="busy" />忙，你们玩吧<?php echo "($this->busy)"?><br/>
<input type="hidden" name="date" value="<?php echo $this->date;?>"/>
验证码(请检查sungeo邮箱)：<input type="text" name="checkcode"/>
<input type="submit" value="投票"/>
</form>

<?php if(count($this->historys) > 0) include 'history.html.php';?>

<?php include '../../common/view/footer.html.php';?>