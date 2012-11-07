<?php 
class voteModel extends model{
	function __construct(){
		parent::__construct();
		
		$this->basketball="basketball";
		$this->football="football";
		$this->busy="busy";
	}
	
	private function generateCheckcode(){
		return uniqid();
	}
	
	private function sendMail($address, $checkcode){
		if (empty($this->mailSender)) {				
			$this->mailSender = new SaeMail();
		}
		$header = $this->lang->checkcodeHeader;
		$content = $this->lang->voteUrl . "  验证码：   ". $checkcode ;
		$account = $this->config->myMailAccount;
		$password = $this->config->myMailPassword;
		/*
		echo $header;
		echo $content;
		echo $account;
		echo $password;
		*/
		$this->mailSender->quickSend($address, $header, $content, $account, $password);
		$ret=$this->mailSender->clean();
		if ($ret === false) {
				var_dump($this->mailSender->errno(), $this->mailSender->errmsg());
		}else{
			echo "邮件已发送给 ".$address."<br/>";
		}
	}
	
	// 为每个person生成空的下轮投票记录。
	private function generateEmptyVoteRecord($roundId){
		$persons = $this->dao->select('*')->from(TABLE_VOTE_PERSON)->fetchAll();
		foreach($persons as $person){
			$data->person_id = $person->id;
			$data->round_id  = $roundId;
			$data->checkcode = $this->generateCheckcode();
			$this->dao->insert(TABLE_VOTE_VOTE)->data($data)->exec();
			if(dao::isError())	die(js::error(dao::getError));
			
			// 向所有相关同事发送邀请邮件，邮件中包含本轮的验证码。
			$this->sendMail($person->email, $data->checkcode);			
		}
	}
	
	private function toStringDate($date){
		return strftime("%Y-%m-%d %H:%M:%S", $date);
	}
	
	private function getThisRoundDate(){
		$arr = array();
		$nowTime = localTime(time(), true);
		$nowSeconds = mktime();
		
		$elapsed = $nowTime['tm_hour']*3600 + $nowTime['tm_min']*60 + $nowTime['tm_sec'];
		$dayBeginSeconds = $nowSeconds - $elapsed;
		
		$weekBeginSeconds = $dayBeginSeconds - ($nowTime['tm_wday'] - 1) * 3600 * 24;		
		$tuesdaySeconds = $weekBeginSeconds + (24+17)*3600;
		$fridaySeconds = $weekBeginSeconds + (24*4+17)*3600;
		
		if ($nowSeconds < $tuesdaySeconds) {
			// 本周二。
			$date=$tuesdaySeconds;
		}else if ($nowSeconds >= $tuesdaySeconds && $nowSeconds <= $fridaySeconds) {
			// 本周五。
			$date=$fridaySeconds;
		}else{
			// 下周二。
			$date=$tuesdaySeconds + (24*7)*3600;
		}
		
		return $this->toStringDate($date);
	}
	
	function determineGame(){
		// 获取上轮投票结果，根据结果决定玩啥。
		// 获取最大的roundId
		$rounds = $this->dao->select('id')->from(TABLE_VOTE_ROUND)
				->orderBy('id DESC')->fetchAll();
		if (! empty($rounds)) {
			$roundId = $rounds[0]->id;
			$count1 = $this->voteForGame($this->basketball, $roundId);
			$count2 = $this->voteForGame($this->football, $roundId);
			$count3 = $this->voteForGame($this->busy, $roundId);
		
			$bigger = 0;
			if ($count1 > $count2) {
				$bigger = $count1;
				$gameName = $this->basketball;
			}else{
				$bigger = $count2;
				$gameName = $this->football;
			}
		
			if ($count3 > $bigger) {
				$bigger = $count3;
				$gameName = $this->busy;
			}

			if (0 == $bigger) {
				$gameName = $this->busy;
			}
			
			$data->game_name = $gameName;
			$data->vote_ratio = $count1 . ":" . $count2 . ":" . $count3;
			$this->dao->update(TABLE_VOTE_ROUND)->data($data)->where('id')->eq($roundId)->exec();
			if(dao::isError())	die(js::error(dao::getError()));	
		}						
	}
	
	function createNextRoundVoteRecord(){
		$date = $this->getThisRoundDate();
		$ret=$this->dao->select("*")->from(TABLE_VOTE_ROUND)->where('date')->eq($date)->fetch();
		if (empty($ret)) {
			// 给上一轮盖棺论定。
			$this->DetermineGame();
			
			$round->date = $date;
			$this->dao->insert(TABLE_VOTE_ROUND)->data($round)->exec();
			if(dao::isError())	die(js::error(dao::getError()));
			$roundId = $this->dao->lastInsertID();
			
			// 生成下轮投票记录，记录内容为空，每条记录对应一个person，并包含验证身份用的checkcode。
			$this->generateEmptyVoteRecord($roundId);					
		}
		
		return $date;
	}
	
	private function voteForGame($gameName, $roundId){
		$gameId = $this->gameIdByName($gameName);
		$ret = $this->dao->select("*")->from(TABLE_VOTE_VOTE)
				->where('round_id')->eq($roundId)->andWhere('game_id')->eq($gameId)->fetchAll();
		return count($ret);
	}
	
	function getCurrentVote($date){
		$roundId = $this->roundIdByDate($date);
		
		$ret[$this->basketball] = $this->voteForGame($this->basketball, $roundId);
		$ret[$this->football] = $this->voteForGame($this->football, $roundId);
		$ret[$this->busy] = $this->voteForGame($this->busy, $roundId);
		
		return $ret;
	}
	
	private function roundIdByDate($date){
		return $this->dao->select('id')->from(TABLE_VOTE_ROUND)
				->where('date')->eq($date)->fetch('id');
	}
	
	private function gameIdByName($gameName){
		return $this->dao->select('id')->from(TABLE_VOTE_GAME)
				->where('name')->eq($gameName)->fetch('id');
	}
	
	// 投票。
	function vote($game, $date, $checkcode){
		$roundId = $this->roundIdByDate($date);
		$gameId = $this->gameIdByName($game);
		
		$vote->round_id = $roundId;
		$vote->game_id = $gameId;
		$vote->checkcode = $checkcode;
		
		$ret = $this->dao->update(TABLE_VOTE_VOTE)->data($vote)->where('checkcode')->eq($checkcode)->exec();
		if(dao::isError())	die(js::error(dao::getError()));
		return empty($ret);
	}
	
	// 获取过往投票历史。
	function getHistorys(){
		return $this->dao->select("*")->from(TABLE_VOTE_ROUND)
				->where('game_name')->notEq('')->orderBy('id DESC')->fetchAll();
	}
}