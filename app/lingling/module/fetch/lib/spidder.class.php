<?php

class spidder {
	
	var $fp      = 0;
	var $url     = '';
	var $tmpFile = '';
	
	public function __construct(){
		$this->tmpFile =  SAE_TMP_PATH . "/tmpHtml.txt";	
	}
	
	/**
	 * 检查该比赛是否已经过期，过期返回true，未过期返回false。
	 * @param 2011-11-12 $date
	 * @param 19:00:00   $time
	 */
	protected function isExpired($date, $time){
		$pat = '/^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$/';
		$ret = preg_match($pat, $date, $matches);
		if(0 == $ret){
			return true;
		}
		$year = ltrim($matches[1], '0');
		$mon  = ltrim($matches[2], '0');
		$day  = ltrim($matches[3], '0');
		
		$pat ='/^([0-9]{1,2}):([0-9]{1,2}):?([0-9]{1,2})?$/';
		$ret = preg_match($pat, $time, $matches);
		if(0 == $ret){
			return true;
		}
		$hour = $matches[1];
		$min  = $matches[2];
		$second = 0;
		if(!empty($matches[3])){
			$second = $matches[3];
		}
		
		$now = time();
		$gameTime = mktime($hour, $min, $second, $mon, $day, $year);
		
		return ($gameTime < $now ? true : false);
	}
	
	public function setUrl($url){
		$tis->url = $url;
	} 
	
	public function download($url, $localPath){
		$this->url = $url;
		$ch = curl_init($this->url);
		$fp = fopen($localPath, "w+");
		
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko)');
		curl_setopt($ch, CURLOPT_HEADER, 0);		

		curl_exec($ch);
		curl_close($ch);
		
		fclose($fp);
	}
	
	public function beginFetch($url = ''){
		if($url != ''){
			$this->url = $url;
		}
		
		if(empty($this->url) || $this->url == ''){
			return false;
		}
		
		$ch = curl_init($this->url);
		if(!($fp = fopen($this->tmpFile, "w+"))){
			return false;
		}

		if(! curl_setopt($ch, CURLOPT_FILE, $fp)){
			return false;
		}
		if(! curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko)')){
			return false;
		}
		if(! curl_setopt($ch, CURLOPT_HEADER, 0)){
			return false;
		}		

		if(! curl_exec($ch)){
			return false;
		}
		curl_close($ch);
		
		fseek($fp, 0, SEEK_SET);
		
		$this->fp = $fp;				
		return true;
		
//		while(true == ($line=fgets($fp))){
//			echo $line;
//		}
//		fclose($fp);
	}
	
	function endFetch(){
		fclose($this->fp);
	}	
}