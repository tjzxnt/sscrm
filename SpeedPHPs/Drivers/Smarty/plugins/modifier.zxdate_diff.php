<?php

function smarty_modifier_zxdate_diff($starttime, $endtime = "")
{
	if(!$endtime)
		$endtime = time();
    $difftime = abs($endtime - $starttime);
    $timeminute = 60;
    $timehour = $timeminute * 60;
    $timedate = $timehour * 24;
    $timemonth = $timedate * 30;
    $timeyear = $timedate * 365;
    if($diffval = floor($difftime / $timeyear))
    	return strval($diffval)."年";
    if($diffval = floor($difftime / $timemonth))
    	return strval($diffval)."月";
    if($diffval = floor($difftime / $timedate))
    	return strval($diffval)."天";
    if($diffval = floor($difftime / $timehour))
    	return strval($diffval)."小时";
    if($diffval = floor($difftime / $timeminute))
    	return strval($diffval)."分钟";
    return $difftime."秒";
}
?>
