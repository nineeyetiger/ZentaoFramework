<?php

class fetchModel extends model{
	public function __construct(){
		parent::__construct();
	}
	
	
	/*
	 * 根据时间跨度来查询比赛轮次简要信息。
	 */
	public function getTimedGameTips($begin, $end){
		$ret = $this->dao->select('tournamentId,description')->from(TABLE_GAME)
			 	    ->where('dateTime')->between($begin, $end)
			 		->fetchAll();
		return $ret;
	}
	
	private function finalAddTvb($teamName, $dateTime, $tvb){
		if(empty($tvb)){
			return false;
		}
		
		// 查找球队是否在覆盖的赛事范围内。
		$rets = $this->dao->select('id')
					->from(TABLE_TEAM)
					->whereBeginBracket('name')->eq($teamName)
					->orWhere('alias')->eq($teamName)->endBracket()
					->fetchAll();
		// 参数错误：没有找到以该名字命名的球队。
		if(empty($rets)){
			return false;
		}
					
		$ids = array();
		foreach ($rets as $ret){
			$ids[] = $ret->id;
		}
		
		// 查找球队是否有在直播时间点上的赛事。
		$ids = join(',', $ids);
		$ret = $this->dao->select('id')
					->from(TABLE_GAME)
					->WhereBeginBracket('hostTeamId')->in($ids)
				  	->orWhere('guestTeamId')->in($ids)->endBracket()
					->andWhere('dateTime')->eq($dateTime)
					->fetch('id');
		
		// 参数错误：该球队在该时间点上没有比赛。
		if(empty($ret)){
			return false;
		}
		
		// 更新电视转播频道信息。
		$this->loadModel('param')->updateTvChannels($tvb);
		
		$data['tvb'] = $tvb;
		$this->dao->update(TABLE_GAME)->data($data)->where('id')->eq($ret)->exec();

		if(dao::isError())	die(js::error(dao::getError()));
		
		return true;
	}
	
	// 多个转播频道时，剪切频道名前后的空格，在频道名中间用顿号隔开。
	private function formatTvb($tvb, $seporator){
		$channelArray = array();
		
		$channels = explode($seporator, $tvb);		
		foreach($channels as $channel){
			$channel = trim($channel);
			if(strtolower($channel) == 'cctv-5'){
				// 对于CCTV5，有的网站写成cctv-5。转换成一致内容。
				$channel = 'CCTV5';
			}else if($channel == '直播待定'){
				continue;
			}else{
				// 直播表出现解说员名字时，刨去解说员，如：劲爆体育/上海新视觉（唐晖 李翔）。
				$pat = '/(.*?)[\(（].*[\)）]/';
				$ret = preg_match($pat, $channel, $matches);
				if(0 != $ret){
					$channel = $matches[1];
				}
			}
			$channelArray[] = $channel;
		}
		return implode(',', $channelArray);
	}
	
	public function addFootballTvb($broadcast){
		$team = $broadcast[0];
		$date = $broadcast[1];
		$time = $broadcast[2];
		$tvb  = $this->formatTvb($broadcast[3], '、');				
		
		$dateTime = $date . " " . $time;
		
		return $this->finalAddTvb($team, $dateTime, $tvb);
	}
	
	public function addNbaTvb($broadcast){
		$team = $broadcast[1];
		$dateTime = $broadcast[2];
		$tvb = $this->formatTvb($broadcast[3], ',');
		return $this->finalAddTvb($team, $dateTime, $tvb);
	}
	
	public function isOfferExist($date){
		$ret = $this->loadModel('param')->get($date);
		return empty($ret) ? false : true;
	}
	
	public function saveOffer($date, $localFile){
		$this->loadModel('param')->update($date, $localFile);
	}
}