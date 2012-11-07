<?php

require('spidder.class.php');

class appleOffer extends spidder{
	
	public $allDays;	
	
	private $host;
	
	public function __construct(){
		parent::__construct();
		
		$this->allDays = array();
		
		$this->host = 'http://3.1415926.mobi/';
	}
	
	private function getTodayOfferUrl(){
		$arr = array();
		$nowTime = localTime(time(), true);
		
		foreach($this->allDays as $dayDesc){
			$arr = explode('_', $dayDesc->date);						
			$year = ltrim($arr[0], '0');
			$mon  = ltrim($arr[1], '0');
			$day  = ltrim($arr[2], '0');
				
			if($year == ($nowTime['tm_year'] + 1900) 
			&& $mon == ($nowTime['tm_mon'] + 1) 
			&& $day == $nowTime['tm_mday']){	
				return $dayDesc->url; 
			}
		}
		
		return false;
	}
	
	private function getOfferUrl($contents){
		$pat = '/<span id="thread_[0-9]+"><a href="(.+?)">([0-9]{4}.+?)<\/a><\/span>/';
		$ret = preg_match_all($pat, $contents, $matches);
		if(0 == $ret){
			return false;
		}
		
		$count = $ret;
		for($i = 0; $i < $count; $i ++){			
			$this->allDays[$i]->url  = $matches[1][$i];
			
			$pat = '/([0-9]{4}).+?([0-9]{2}).+?([0-9]{2}).*/';
			$ret = preg_match($pat, $matches[2][$i], $values);
			$this->allDays[$i]->date = $values[1]."_".$values[2]."_".$values[3];
		}
		
//		return $this->getTodayOfferUrl();
		return true;
	}
	
	// 获取报价页面中的所有子报价链接地址。
	public function getOfferDays($url){
		if(! $this->beginFetch($url)){
			return false;
		}
		
		$contents = fread($this->fp, filesize($this->tmpFile));
		$ret = $this->getOfferUrl($contents);
		
		$this->endFetch();	
		
		return $ret;
	}
	
	private function getAppleOffer($contents){
		// 必须将utf8编码的“苹果”转为gbk。
		$apple = '苹果';
		$apple = iconv('UTF-8', 'GB2312', $apple);
//		$pat = '/<a href="(attachment\.php\?aid=.+?)".*?target="_blank">'.$apple.'\.jpg<\/a>/';
		$pat = '/<img onclick="zoom\(this, \'(attachments.*?)\'\)".*?alt="'.$apple.'\.jpg" \/><\/a>/';
		$ret = preg_match($pat, $contents, $matches);

		return $ret == 0 ? false : $matches[1];
	}
	
	/**
	 * 获取所有产品的offer图片的，当然，内部只调用了获取苹果的。
	 */
	public function getProductsOffer($url, $date){
		if(! $this->beginFetch($url)){
			return false;
		}
		
		clearstatcache();
		$size = filesize($this->tmpFile);
		$contents = fread($this->fp, $size);
		$ret = $this->getAppleOffer($contents);
		if($ret){
			global $app;
			
			$target = $this->host . $ret;
			$localFile = "downloads/" . $date . "_apple.jpg";
			$this->download($target, $app->getBasePath().$localFile);
			
			$ret = $localFile;
		}
		
		$this->endFetch();
		
		return $ret;
	}
	
	public function getOffers(){		
		$url = $this->getOfferDays($this->host.'forum-3-1.html');		
	}
}