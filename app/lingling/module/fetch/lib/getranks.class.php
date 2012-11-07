<?php

require('spidder.class.php');

class getRanks extends spidder{
	
	var $idToUrlMapping = array();
	
	function __construct(){
		parent::__construct();
		
		$this->idToUrlMapping['17'] = 'http://sports.sina.com.cn/global/score/Germany/index.shtml';
		$this->idToUrlMapping['18'] = 'http://sports.sina.com.cn/global/score/Italy/index.shtml';
		$this->idToUrlMapping['20'] = 'http://sports.sina.com.cn/global/score/Spain/index.shtml';
		$this->idToUrlMapping['21'] = 'http://sports.sina.com.cn/global/score/England/index.shtml';
	}	
	
	function getRankedTeams($url){
		if(! $this->beginFetch($url)){
			return false;
		}
		
		$contents = fread($this->fp, filesize($this->tmpFile));
		$pat = '/ target="_blank">(.*?)<\/a><\/font>/';
		$ret = preg_match_all($pat, $contents, $matches);
		
		$this->endFetch();
		
		if(empty($ret)){
			return false;
		}else{
			return $matches[1];
		}
	}
	
	function getRanks($tournamentId){
		if(! isset($this->idToUrlMapping[$tournamentId])){
			return false;
		}
		
		$url = $this->idToUrlMapping[$tournamentId];
		
		return $this->getRankedTeams($url);
	}
}