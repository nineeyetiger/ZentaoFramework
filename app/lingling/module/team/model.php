<?php

class teamModel extends  model{
	public function __construct(){
		parent::__construct();
	}
	
	function getTeams($tournamentId, $pager = null){
		$ids = $this->dao->select('teamsId')
					->from(TABLE_TOURNAMENT)
					->where('id')->eq($tournamentId)
					->fetch('teamsId');
		if(empty($ids)){
			return false;
		}
		
		$ret = $this->dao->select('id, name')
					->from(TABLE_TEAM)
					->where('id')->in($ids)
					->orderBy('id')
					->page($pager)
					->fetchAll();
					
		if(dao::isError())	die(js::error(dao::getError()));
		
		return $ret;
	}
	
	function queryByName($teamName){
		$ret = $this->dao->select('id')
				  	->from(TABLE_TEAM)
				  	->where('name')->eq($teamName)
				  	->orWhere('alias')->eq($teamName)
				  	->fetchAll();
		return $ret;
	}
	
	function add($teamName){
		$data['name'] = $teamName;
		$ret = $this->dao->insert(TABLE_TEAM)->data($data)->exec();
		if(dao::isError())	die(js::error(dao::getError()));
		
		return $this->dao->lastInsertID();
	}
	
	function getTournamentsId(){
		return $this->dao->select('id')->from(TABLE_TOURNAMENT)->fetchAll('id');
	}
	
	function updateTeams($tournamentId, $teamsId){
		$data['teamsId'] = $teamsId;
		$this->dao->update(TABLE_TOURNAMENT)->data($data)->where('id')->eq($tournamentId)->exec();
		if(dao::isError())	die(js::error(dao::getError()));
	}
}