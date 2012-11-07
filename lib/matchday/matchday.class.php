<?php

class matchDay {
	public static function getRecentCycle(){		
		$arr = array();
		$nowTime = localTime(time(), true);
		$nowSeconds = mktime();
		
		$elapsed = $nowTime['tm_hour']*3600 + $nowTime['tm_min']*60 + $nowTime['tm_sec'];
		$dayBeginSeconds = $nowSeconds - $elapsed;
		
//		$weekBeginSeconds = $dayBeginSeconds - $nowTime['tm_wday'] * 3600 * 24;
		$arr[]  = date('Y-m-d H:i:s', $nowSeconds);
		
		$weekEndSeconds   = $dayBeginSeconds + (7 - $nowTime['tm_wday']) * 3600 * 24;
		$weekEndSeconds  += 3600 * 6; // Week ending at 6 hours later.
		$arr[]  = date('Y-m-d H:i:s', $weekEndSeconds);
		
		return $arr;
	}
}