<?php

class fetch extends control{
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 更新所有赛事中的球队排名。
	 */
	public function updateRank(){
		$teamModel = $this->loadModel('team');
		$tournamentsId = $teamModel->getTournamentsId();
		if(empty($tournamentsId)){
			die("no tournaments");
		}
		
		$total = 0;
		
		require('lib/getranks.class.php');
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
				$teamRecord = $teamModel->queryByName($teamName);
				if(empty($teamRecord)){
					die("query team $teamName error");
				}
					
				$teamId = $teamRecord[0]->id;
				$ids [] = $teamId;
				
				$total += 1;
			}
			
			// 更新tournament表中的teamsId字段，id的先后顺序代表了球队排名。
			$teamsId = join(',', $ids);
			$teamModel->updateTeams($tournamentId->id, $teamsId);
		}
		
		$this->assignData($total);
		$this->display();
	}
	
/**
	 * 更新“最近”一个时期内的足球比赛时间。用于定时任务中。
	 */
	public function updateGameTime(){
		require('lib/recentRounds.class.php');
		$rounds = new recentRounds();
		$rounds->beginGetRecentRounds();
		$games = $this->fetch->getTimedGameTips($rounds->weekBegin, $rounds->weekEnd);
		$rounds->endGetRecentRounds($games);
		
		require('lib/getgames.class.php');
		$handler = new getGames();
		
		$gameModel = $this->loadModel('game');
		$total = 0;
		foreach($rounds->rounds as $leagueId=>$rounds){
			foreach ($rounds as $round){
				// 获得一轮中所有比赛。
				$handler->getRoundGames($leagueId, $round);
				// 更新比赛时间。	
				$len = count($handler->hostTeams);
				for($i = 0; $i < $len; $i ++){
					$ret = $gameModel->addGame(
								$handler->tournament,
								$handler->hostTeams[$i],
								$handler->guestTeams[$i],
								$handler->dateTimes[$i],
								$handler->round);
					if($ret){
						$total ++;
					}		
				}	
			}
		}
		
		$this->assignData($total);
		$this->display();
	}	
	
	/**
	 * 更新足球比赛直播信息。用于定时任务中。
	 */
	public function getTvb(){
		require('lib/tvbroadcast.class.php');
		$tvb = new TvBroadcast();
		$tvb->getBroadcast('http://sports.sina.com.cn/global/tvguide/');
		$count = 0;
		foreach ($tvb->available as $broadcast){
			if(true == $this->fetch->addFootballTvb($broadcast)){
				$count += 1;
			}
		}
		
		$this->assignData($count);
		$this->display();
	}
	
	/**
	 * 更新NBA比赛直播信息。用于定时任务中。
	 */
	public function getNbaTvb(){
		require('lib/tvbroadcast4nba.class.php');
		$tvb = new TvBroadcast4NBA();
		$tvb->getBroadcast('http://sports.sohu.com/s2010/nbadianshizhuanbobiao/');
		$count = 0;
		foreach($tvb->available as $broadcast){
			if(true == $this->fetch->addNbaTvb($broadcast)){
				$count += 1;
			}
		}
		
		$this->assignData($count);
		$this->display();
	}
	
	public function getAppleProducts(){
		require('lib/appleOffer.class.php');
		$offer = new appleOffer();
		$ret = $offer->getOfferDays('http://3.1415926.mobi/forum-3-1.html');
		if($ret){
			foreach($offer->allDays as $dayInfo){
				if(! $this->fetch->isOfferExist($dayInfo->date)){
					$ret = $offer->getProductsOffer('http://3.1415926.mobi/'.$dayInfo->url, $dayInfo->date);
					$this->fetch->saveOffer($dayInfo->date, $ret);
				}
			}
		}
	}
}