<?php


class team extends control{
	public function __construct(){
		parent::__construct();
	}
	
	public function getTeams($tournamentId){
		if($this->app->getViewType() == 'json'){
			$ret = $this->team->getTeams($tournamentId);
			$this->assignData($ret);
		}else{			
			$this->app->loadClass('pager');
			$pager = new pager(100, 100, 1);
			$teams = $this->team->getTeams($tournamentId, $pager);
			$this->view->teams = $teams;
		}
		$this->display();
	}
	
	public function queryByName($teamName){
		$ret = $this->team->queryByName($teamName);
		$this->assignData($ret);
		$this->display();
	}
	
	public function add($teamName){
		$ret = $this->team->queryByName($teamName);
		if(empty($ret)){
			$ret = $this->team->add($teamName);
			$this->assignData($ret);
		}else{
			$this->assignData("exists");
		}
		
		$this->display();
	}
	
	/**
	 * 更新所有赛事中的球队排名。
	 */
	public function updateRank(){
		$tournamentsId = $this->team->getTournamentsId();
		if(empty($tournamentsId)){
			die("no tournaments");
		}
		
		$this->app->loadClass('getranks');
		$handler = new getRanks();
		foreach($tournamentsId as $tournamentId){
			$ids = array();
			$rankedTeams = $handler->getRanks($tournamentId->id);
			if(empty($rankedTeams)){
				echo "get $tournamentId->id 's ranks failed<br/>";
				continue;
			}
			
			foreach($rankedTeams as $teamRank=>$teamName){
				$teamRank += 1;
				$teamName = iconv('GB2312', 'UTF-8', $teamName);
				$teamRecord = $this->team->queryByName($teamName);
				if(empty($teamRecord)){
					die("query team $teamName error");
				}
					
				$teamId = $teamRecord[0]->id;
				$ids [] = $teamId;
			}
			
			$teamsId = join(',', $ids);
			$this->team->updateTeams($tournamentId->id, $teamsId);
		}
	}
}