<?php
class gameModel extends model{		

	public function __construct(){
		parent::__construct();
		
		$this->gameFields = 'id, hostTeamId, guestTeamId, dateTime, tournamentId, tvb';
	}
	
	/**
	 * 判断日期是否合法：xxxx-xx-xx xx:xx:xx
	 */
	public function isDateValid($date){
		$pattern = '/^ *[0-9]{2,4}(-[0-9]{2}){1,2} [0-9]{2}(:[0-9]{2}){1,2} *$/';
		$ret = preg_match($pattern, $date, $matches);
		return $ret == 0 ? false : true;			
	}
	
	/**
	 * 添加一场比赛信息。加入比赛所属赛事、球队未添加，会自动添加。
	 * @param $tournament	string	赛事名
	 * @param $hostTeam		string	主队名
	 * @param $guestTeam	string	客队名
	 * @param $dateTime		date	比赛时间
	 * @return int	比赛ID
	 */
	public function addGame($tournament, $hostTeam, $guestTeam, $dateTime, $round){
		if(! $this->isDateValid($dateTime)){
			return false;
		}
		
		// 插入赛事信息。
		$tournamentId = $this->addTournament($tournament);
		if(! $tournamentId){
			return false;
		}		

		// 插入主队信息。
		$hostId = $this->addTeam($hostTeam, $tournamentId); 
		if(! $hostId){
			return false;
		}
		
		// 插入客队信息。
		$guestId = $this->addTeam($guestTeam, $tournamentId);			
		if(! $guestId){
			return false;
		}
			
		// 插入比赛信息。
		$game->hostTeamId   = $hostId;
		$game->guestTeamId  = $guestId;
		$game->tournamentId = $tournamentId;
		$game->dateTime     = $dateTime;
		$game->description	= $round;

		$ret = $this->dao->select('id')->from(TABLE_GAME)->where('hostTeamId')->eq($hostId)
			->andWhere('guestTeamId')->eq($guestId)
			->andWhere('tournamentId')->eq($tournamentId)
			->andWhere('description')->eq($round)
			->fetch('id');
		if(! $ret){
			$this->dao->insert(TABLE_GAME)->data($game)->exec();
			return $this->dao->lastInsertID();
		}else{
			$this->dao->update(TABLE_GAME)->data($game)->where('id')->eq($ret)->exec();
			return $ret;
		}		
	}
	
	/**
	 * add tournament to db
	 * @param string $name	tournament name, such as "Olympic Games".
	 * @return int tournament id.
	 */
	private function addTournament($name){
		$ret=$this->dao->select('id')->from(TABLE_TOURNAMENT)
				  ->where('name')->eq($name)
				  ->fetch('id');
		if(! $ret){		
			$this->dao->insert(TABLE_TOURNAMENT)->set('name')->eq($name)->exec();
			return $this->dao->lastInsertID();			
		}else{
			return $ret;
		}
	}
	
	/**
	 * @param string $name
	 * @param int $tournamentId
	 * @return int team id.
	 */
	private function addTeam($name, $tournamentId){
		$ret=$this->dao->select('id')->from(TABLE_TEAM)
		->where('name')->eq($name)
		->orWhere('alias')->eq($name)
		->fetch('id');
		if(! $ret){
			$team->name = $name;
			$this->dao->insert(TABLE_TEAM)->data($team)->exec();
			$teamId = $this->dao->lastInsertID();
			
			// 将新球队id添加到赛事teamsId字段中
			$teamsId = $this->dao->select('teamsId')->from(TABLE_TEAM)
							->where('id')->eq($tournamentId)->fetch('teamsId');
			$found = false;
			$ids = explode(',', $teamsId);
			foreach($ids as $id){
				if($id == $teamId){
					$found = true;
					break;
				}
			}
			if(false == $found){
				$ids[] = $teamId;
				$teamsId = join(',', $ids);
				$data['teamsId'] = $teamsId;
				$this->dao->update(TABLE_TOURNAMENT)
					 ->data($data)
					 ->where('id')->eq($tournamentId)
					 ->exec();
			}
			
			return $teamId;
		}else{
			return $ret;
		}
	}
	
	/**
	 * 查询在某个时间点之后结束的所有赛事信息
	 * @param time $endBeforeThisTime
	 */
	public function getTournaments($endBeforeThisTime = ''){
		if(empty($endBeforeThisTime)){
			$endBeforeThisTime= date("Y-m-d H:i:s");
		}
		$ret=$this->dao->select('*')->from(TABLE_TOURNAMENT)
			      ->where('end')->gt($endBeforeThisTime)->fetchAll();
		return $ret;
	}
	
	public function getTeams($tournamentId){
		$ret=$this->dao->select('id, name')->from(TABLE_TEAM)
				  ->where('tournamentId')->eq($tournamentId)
				  ->orderBy('rank')
				  ->fetchAll();
		return $ret;
	}
	
	public function getGames($teamId, $tournamentId){
		$date= date("Y-m-d H:i:s");
		$ret=$this->dao->select($this->gameFields)->from(TABLE_GAME)				  
				  ->WhereBeginBracket('hostTeamId')->eq($teamId)
				  ->orWhere('guestTeamId')->eqEndBracket($teamId)
				  ->andWhere('tournamentId')->eq($tournamentId)
				  ->andWhere('dateTime')->gt($date)
				  ->orderBy('dateTime')
				  ->fetchAll();
		return $ret;		
	}
	
	/**
	 * 更新一场比赛的时间
	 * @param int $tournamentId
	 * @param int $hostTeamId
	 * @param int $guestTeamId
	 * @param int $round
	 * @param datetime $dateTime
	 */
	public function updateGame($tournamentId, $hostTeamId, $guestTeamId, $round, $dateTime){
		if(! $this->isDateValid($dateTime)){
			die(js::error("invalid datetime format"));
		}
		
		$data['dateTime'] = $dateTime;
		$this->dao->update(TABLE_GAME)->data($data)
			 ->where('tournamentId')->eq($tournamentId)
			 ->andWhere('hostTeamId')->eq($hostTeamId)
			 ->andWhere('guestTeamId')->eq($guestTeamId)
			 ->andWhere('description')->eq($round)
			 ->exec();
		if(dao::isError())	die(js::error(dao::getError()));
		return true;
	}
	
	/**
	 * 根据一组比赛id，查找符合条件的比赛记录。
	 * @param string $gamesId
	 */
	public function getSpecificGames($gamesId){
		$ids = explode(',', $gamesId, 500);
		if(empty($ids)){
			die(js::error('invalid parameters'));
		}
		
		$ret = $this->dao->select($this->gameFields)->from(TABLE_GAME)
					->where('id')->in($ids)
					->fetchAll();
		return $ret;
	}
	
	/**
	 * 根据一组球队id，查找符合条件的球队记录。
	 * @param string $teamsId
	 */
	public function getSpecificTeams($teamsId){
		$ids = explode(',', $teamsId, 500);
		if(empty($ids)){
			die(js::error('invalid parameters'));
		}
		
		$rets = $this->dao->select('*')->from(TABLE_TEAM)
					->where('id')->in($ids)
					->fetchAll();

		$teams = array();
		foreach($rets as $ret){
			$teams[$ret->id] = $ret->name;
		}
		return $teams;
	}
	
	/**
	 * 将形如“021”或“003301”头部的所有‘0’去掉。
	 * @param unknown_type $string
	 */
	private function trimZero($string){
		if(empty($string)){
			return '';
		}
		
		$len = strlen($string);		
		while($string[0] == '0' && $len > 1){
			$string = substr($string, 1);
			$len = strlen($string);
		}
		
		return $string;
	} 
	
	/**
	 * 检查某球队在该项赛事中，某个日期之后是否还有比赛。
	 */
	public function checkGame($teamId, $tournamentId, $fromDateTime){
		$teamId = $this->trimZero($teamId);
		$tournamentId = $this->trimZero($tournamentId);
		$ret = $this->dao->select($this->gameFields)->from(TABLE_GAME)
					->WhereBeginBracket('hostTeamId')->eq($teamId)
					->orWhere('guestTeamId')->eq($teamId)->endBracket()
					->andWhere('tournamentId')->eq($tournamentId)
					->andWhere('dateTime')->gt($fromDateTime)
					->fetchAll();
		if(dao::isError())	die(js::error(dao::getError()));
		return $ret;
	}
	
	/**
	 * 获取一个时间段内某项赛事的所有直播比赛信息。
	 * @param datetime $begin
	 * @param datetime $end
	 * @param int $tournamentId
	 * @param boolean $broadcast. default false.
	 */
	public function getTimedGames($begin, $end, $tournamentId, $broadcast = false){
		if($broadcast == true){
			$ret = $this->dao->select($this->gameFields)->from(TABLE_GAME)
						->where('tournamentId')->in($tournamentId)						
						->andWhere('dateTime')->between($begin, $end)
						->andWhere('tvb')->noteq('')						
						->fetchAll();
		}else{
			$ret = $this->dao->select($this->gameFields)->from(TABLE_GAME)
						->where('tournamentId')->eq($tournamentId)
				  		->andWhere('dateTime')->between($begin, $end)				  		
				  		->fetchAll();	
		}
		
		return $ret;
	} 
}