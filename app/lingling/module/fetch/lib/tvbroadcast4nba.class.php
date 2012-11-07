<?php

require('spidder.class.php');

class TvBroadcast4NBA extends spidder {
	
	var $available = array();
	
	function __construct(){
		parent::__construct();
	}

	private function getColumns($url){
		if(! $this->beginFetch($url)){
			return false;
		}
		
		$contents = fread($this->fp, filesize($this->tmpFile));
		$pat = '/<td>(.*?)<\/td>/';
		$ret = preg_match_all($pat, $contents, $matches);
		
		$this->endFetch();
		
		if(empty($ret)){
			return false;
		}else{
			return $matches[1];
		}	
	}
	
	private function getDate($subject){
		$pat = '/([0-9-]+).*/';
		$ret = preg_match($pat, $subject, $matches);
		if($ret == 0){
			return false;
		}else{
			return $matches[1];			
		}
	}
	
	private function getTime($subject){
		$pat = '/([0-9:]+).*/';
		$ret = preg_match($pat, $subject, $matches);
		if($ret == 0){
			return false;
		}else{
			return $matches[1];
		}
	}
	
	private function getTeam($subject){
		$pat = '/target="_blank">(.+?)<\/A>/';
		$ret = preg_match($pat, $subject, $matches);
		if($ret == 0){
			return false;
		}else{
			return iconv('GB2312', 'UTF-8', $matches[1]);
		}
	}
	
	private function getTvb($subject){
		return iconv('GB2312', 'UTF-8', $subject);
	}
	
	private function addTvb($guest, $host, $date, $time, $tvb){
		if($this->isExpired($date, $time)){
			return;
		}
		
		$dateTime = $date." ".$time;
		$tvb = trim($tvb);
		$tvChannel = str_replace(' ', ',', $tvb);
		$this->available[] = array($guest, $host, $dateTime, $tvChannel);
	}
	
	public function getBroadcast($url){
		$columns = $this->getColumns($url);
		if(empty($columns)){
			return false;
		}
		
		$date = '';
		$time = '';
		$host = '';
		$guest = '';
		$tvb = '';	
		$count = count($columns);
		for($i = 0; $i < $count; $i++){
			$j = $i % 5;
			switch($j){
				case 0:
					$ret = $this->getDate($columns[$i]);
					if(true == $ret){
						$date = $ret;
					}
					break;
				case 1:
					$time = $this->getTime($columns[$i]);
					break;
				case 2:
					$guest = $this->getTeam($columns[$i]);
					break;
				case 3:
					$host = $this->getTeam($columns[$i]);
					break;
				case 4:
					$tvb = $this->getTvb($columns[$i]);
					$this->addTvb($guest, $host, $date, $time, $tvb);
					break;		
			}
		}
	}
}