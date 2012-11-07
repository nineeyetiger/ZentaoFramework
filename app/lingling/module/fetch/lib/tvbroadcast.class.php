<?php

require("spidder.class.php");

class TvBroadcast extends spidder {
	
	var $date;
	var $time;
	var $available = array();
	
	public function __construct(){
		parent::__construct();
	}
	
	function storeTvb($team, $tvb){
		if($this->isExpired($this->date, $this->time)){
			return;
		}
		
		$team = iconv('GB2312', 'UTF-8', $team);
		$tvb  = iconv('GB2312', 'UTF-8', $tvb);	
		
		$this->available[] = array($team, $this->date, $this->time, $tvb);
	}
	
	function getDetail($desc){
		$desc = str_replace('<strong><font color="#ED1C24">CCTV-5</font></strong>', 'CCTV5', $desc);
		$pat = '/.+?VS +(.+?) +(.*)/';
		$ret = preg_match($pat, $desc, $matches);
		if(! empty($ret)){			
			$this->storeTvb($matches[1], $matches[2]);
		} 
	}
	
	function getDate($line){
		$pat = '/<td class="link04"><strong>([0-9]+).*?([0-9]+).*<\/strong>/';
		$ret = preg_match($pat, $line, $matches);
		if(! empty($ret)){
			// xx月xx日 星期x ——> xx-xx
			$date = $matches[1] . "-" . $matches[2];
			$now = localtime(time(), true);
			$year = $now['tm_year'] + 1900;
			$this->date = $year.'-'.$date;
			return true;				
		}
		
		return false;	
	}
	
	function getGame($line){
		$pat = '/<td class="link04">([0-9:]+) *(.+?)<\/td>/';
		$ret = preg_match($pat, $line, $matches);
		if(! empty($ret)){
			// 得到比赛时间。
			$this->time = $matches[1];
			$desc = $matches[2];
			$this->getDetail($desc);
		}
		return true;
	}
	
	public function getBroadcast($url){
		if(! $this->beginFetch($url)){
			return false;
		}
		
		$line = fgets($this->fp);
		while($line){			
			if(! $this->getDate($line)){
				$this->getGame($line);
			}
			$line = fgets($this->fp);
		}
		
		$this->endFetch();
	}
}