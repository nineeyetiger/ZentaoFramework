<?php

require("spidder.class.php");

class getGames extends spidder{
	
	var $leangueId  = 0;
	var $round      = 0;
	
	var $tournament = '';
	var $hostTeams  = array();
	var $guestTeams = array();
	var $dateTimes  = array();
	
	public function __construct(){
		parent::__construct();
	}
	
	function clear(){
		$this->leagueId   = 0;
		$this->round      = 0;
		$this->tournament = '';
		$this->hostTeams  = array();	//TODO
		$this->guestTeams = array();
		$this->dateTimes  = array();
	}
	
	function getRoundUrl($leagueId, $round){
		$this->leagueId = $leagueId;
		$this->round    = $round;
		$baseUrl="http://data.sports.sina.com.cn/yingchao/calendar/";
		$params="?action=round&league_id=".$leagueId."&round=".$round;

		return $baseUrl . $params;
	}
	
	function trimRoundString($string){
		$pat = '/.+?(第.+?轮)/';
		$ret = preg_match($pat, $string, $matches);
		if(! empty($ret)){
			$total = strlen($string);
			$len = strlen($matches[1]);
			return substr($string, 0, $total - $len);
		}		
		return false;
	}
	
	function getTournament($html){
		$pat = "/<font color=\"2677AF\"> *(.+?)<\/font>/";
		$ret = preg_match($pat, $html, $matches);
		if(! empty($ret)){
			$tournament = $matches[1];
			$tournament = iconv('GB2312', 'UTF-8', $tournament);
			$ret = $this->trimRoundString($tournament);
			if(! empty($ret)){
				$this->tournament = $ret;
			}
			return true;
		}		
		return false;
	}
	
	function getTeams($html){
		$pat = "/class=\"a02\" target=_blank>(.+?)<\/a>/";
		$ret = preg_match_all($pat, $html, $matches);
		if(! empty($ret)){
			for($i = 0; $i < $ret; $i ++){
				if($i % 2 == 0){
					$this->hostTeams[] = iconv('GB2312', 'UTF-8//IGNORE', $matches[1][$i]);
				}else{
					$this->guestTeams[] = iconv('GB2312', 'UTF-8//IGNORE', $matches[1][$i]);
				}
			}			
			return true;
		}		
		return false;
	}
	
	function getGameDates($html){
		$pat = '/<font color=\"#333333\">([0-9]+?[-:][0-9]+?[-:][0-9]+?)<\/font>/';
		$ret = preg_match_all($pat, $html, $matches);
		if(! empty($ret)){
			for($i = 0; $i < $ret; $i ++){
				if($i % 2 == 0){
					$date = $matches[1][$i];
				}else{
					$time = $date." ".$matches[1][$i];
					$this->dateTimes[] = $time;
				}
			}
			
			return true;
		}
		
		return false;
	}
	
	function getGames($url){
		if(! $this->beginFetch($url)){
			return false;
		}
		
		$contents = fread($this->fp, filesize($this->tmpFile));
		$ret = $this->getTournament($contents);
		if(! empty($ret)){
			$this->getTeams($contents);
			$this->getGameDates($contents);
		}
		
		$this->endFetch();
	}
	
	public function getRoundGames($leagueId, $round){
		$this->clear();
		$url = $this->getRoundUrl($leagueId, $round);
		
		$this->getGames($url);
	}
		
}