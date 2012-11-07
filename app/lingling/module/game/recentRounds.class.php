<?php

/**
 * 1，beginGetRecentRounds：获取“本周”的具体定义：开始时间、结束时间。。
 * 2，外部函数使用这两个时间查询该段内的所有比赛。
 * 3，endGetRecentRounds：将所有比赛信息作为输入参数，可以得到所有该范围内的足球赛事轮次信息。
 * @author Liu WanWei
 *
 */

class recentRounds{
	
	// 查询结果.
	var $rounds;
	var $weekBegin;
	var $weekEnd;		
	
	public function __construct(){
	}
	
	private function saveRound($tournamentId, $round){
		$mapping = array('17'=>'327', '18'=>'326', '20'=>'329', '21'=>'325');
		
		$leagueId = '';
		if(isset($mapping[$tournamentId])){			
			$leagueId = $mapping[$tournamentId];
		} 
		
		if(empty($leagueId)){
			return false;
		}
		
		if(empty($this->rounds[$leagueId])){
			$this->rounds[$leagueId] = array($round,);
		}else{
			foreach ($this->rounds[$leagueId] as $rd){
				if($round == $rd){
					return false;	
				}			
			}
			
			$this->rounds[$leagueId][] = $round;
		}
	}

	/**
	 * 获取“最近”的起始日期和结束日期。本周一的零点零分，到下周一的六点零分。
	 */
	public function beginGetRecentRounds(){
		$this->app->loadClass('matchDay', true);
		$arr = matchDay::getRecentCycle();
		$this->weekBegin = $arr[0];
		$this->weekEnd = $arr[1];
	}
	
	public function endGetRecentRounds($games){
		foreach ($games as $info){
			$this->saveRound($info->tournamentId, $info->description);
		}		
	}
}