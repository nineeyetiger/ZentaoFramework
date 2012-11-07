<?php
class game extends control{
	var $mmc;

	public function __contruct(){
		parent::__contruct();
	}
	
	/**
	 * 添加一场比赛（赛事、主队、客队如果不存在，会自动创建和添加到数据库中）
	 * @param string $tournament
	 * @param string $hostTeam
	 * @param string $guestTeam
	 * @param string $dateTime
	 * @param string $round
	 */
	public function addGame($tournament, $hostTeam, $guestTeam, $dateTime, $round){
		$ret=$this->game->addGame($tournament, $hostTeam, $guestTeam, $dateTime, $round);
		$this->assign('id', $ret);				
		$this->display();
	}
	
	/**
	 * 获取所有赛事信息(未结束的)
	 */
	public function getTournaments(){        
		$ret=$this->game->getTournaments();
		if(false != $ret){
			$this->assignData($ret);
		}
		$this->display();
	}
	
	/**
	 * 获取某项赛事某个球队未结束的赛事信息
	 * @param int $teamId
	 * @param int $tournamentId
	 */
	public function getGames($teamId, $tournamentId){
		$ret=$this->game->getGames($teamId, $tournamentId);
		if(! empty($ret)){
			$this->assignData($ret);
		}
		$this->display();
	}
	
	/**
	 * 获取特定比赛的更新信息（通过POST中传递比赛id作为查询参数），
	 * 当比赛时间更新，比赛电视直播更新时，要用到该接口。
	 */
	public function getSpecificGames(){        
		if(! empty($_POST)){
			$gamesId = $_POST['gamesId'];
			if(! empty($gamesId)){
				$ret = $this->game->getSpecificGames($gamesId);
				if(!empty($ret)){
					$this->assignData($ret);
					$this->display();
				}				
			}
		}			
	}
	
	/**
	 * 获取一项赛事最近的所有有电视直播的比赛，用于关注某个赛事的所有直播信息。
	 * 查询时并没有使用description字段作为删选条件，因为电视直播信息更新时间不定。
	 * @param int $tournamentId
	 */
	public function getRecentGames($tournamentId){
		$this->app->loadClass('matchday');
		$cycle = matchday::getRecentCycle();
		$games = $this->game->getTimedGames($cycle[0], $cycle[1], $tournamentId, true);
		$this->assignData($games);
		$this->display();
	}
	
	/**
	 * 检查关注的球队在赛事中有无新比赛，比如新的淘汰赛对阵表。
	 * 通信协议：在POST的games变量中，传入形如{0001002120109[,0002002120109]}格式的数据
	 * 在以‘，’隔开的每个区域中存有固定的13字节数据：
	 * 0-3字节：  球队ID
	 * 4-6字节：  赛事ID
	 * 7-12字节：比赛日期，xx年xx月xx日。
	 */
	public function checkGames(){
		if(! empty($_POST)){
			$games = $_POST['games'];
			$games = explode(',', $games);
			
			$allTheGames = array();
			foreach ($games as $game){
				if(strlen($game) != 13){
					continue;
				}
				$teamId = substr($game, 0, 4);
				$tournamentId = substr($game, 4, 3);
				$dateTime = (substr($game, 7, 2) + 2000)
								."-".substr($game, 9, 2)
								."-".substr($game, 11)
								." 00:00:00";
				$thisGames = $this->game->checkGame($teamId, $tournamentId, $dateTime);
				if(! empty($thisGames)){
					$allTheGames = array_merge($allTheGames, $thisGames);
				}
			}
			
			$this->assignData($allTheGames);
			$this->display();			
		}
	}
	
	/**
	 * 修改比赛时间。
	 */
	public function modify(){
		if(! empty($_POST)){
			$tournamentId = $_POST['tournament'];
			$hostTeamId   = $_POST['hostTeam'];
			$guestTeamId  = $_POST['guestTeam'];
			$round        = $_POST['round'];
			$dateTime     = $_POST['dateTime'];
			if($hostTeamId == $guestTeamId){
				die("hostTeam and guestTeam can't be equal!");
			}
			$this->game->updateGame($tournamentId, $hostTeamId, $guestTeamId, $round, $dateTime);
			die(js::locate($this->createLink($this->moduleName, $this->methodName)));
		}else{			
			$rets = $this->game->getTournaments();
			foreach ($rets as $ret){
				$teams = $this->game->getSpecificTeams($ret->teamsId);
				$this->view->tournaments[$ret->id] = array($ret->name, $ret->round, $teams);
			}
		}
		
		$this->view->header = "Modify game dataes";
		$this->display();
	}	
	
	public function test(){		
		$str = '北京体育,广州电视台';
		$arr = explode(',', $str);
		$ret = $this->game->isDateValid('11-12-20 22:22:12');
		echo $ret;
	}
}