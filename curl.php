<?php
/**
 * zhoumengkang < i@zhoumengkang.com >
 * NBA数据采集
 */


//数据库的创建
/*********************************************************************************************************************************

--
-- 数据库: `nba`
--
CREATE DATABASE IF NOT EXISTS `nba` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `nba`;

-- --------------------------------------------------------

--
-- 表的结构 `match`
--

DROP TABLE IF EXISTS `match`;
CREATE TABLE IF NOT EXISTS `match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `time` varchar(100) NOT NULL,
  `team1_id` varchar(20) NOT NULL,
  `team2_id` varchar(20) NOT NULL,
  `team1_name` varchar(100) NOT NULL,
  `team2_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `match_id` (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `players`
--

DROP TABLE IF EXISTS `players`;
CREATE TABLE IF NOT EXISTS `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `players_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `players_id` (`players_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `record`
--

DROP TABLE IF EXISTS `record`;
CREATE TABLE IF NOT EXISTS `record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `player_team_id` varchar(100) NOT NULL,
  `fgper` varchar(20) NOT NULL,
  `fg` int(11) NOT NULL,
  `fga` int(11) NOT NULL,
  `threepper` varchar(20) NOT NULL,
  `threep` int(11) NOT NULL,
  `threepa` int(11) NOT NULL,
  `ftper` varchar(20) NOT NULL,
  `ft` int(11) NOT NULL,
  `fta` int(11) NOT NULL,
  `trb` int(11) NOT NULL,
  `ast` int(11) NOT NULL,
  `stl` int(11) NOT NULL,
  `blk` int(11) NOT NULL,
  `tov` int(11) NOT NULL,
  `pf` int(11) NOT NULL,
  `pts` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

********************************************************************************************************************************/

header("Content-Type: text/html; charset=utf-8");
set_time_limit(0);

//定义数据库常量(换成你的数据库帐号密码)
/*define(HOST, 'localhost');
define(USER, 'root');
define(PASSWORD, 'zmkzmk');

//连接数据库
$link = mysql_connect(HOST, USER, PASSWORD);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
mysql_set_charset('utf8');
mysql_select_db('nba', $link);*/

$url = $_POST['url'];
$baseUrl = 'http://www.stat-nba.com/';


/*error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');*/

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');


require_once './Classes/PHPExcel.php';
$team1 = new PHPExcel();
$team2 = new PHPExcel();



include 'phpQuery.php';

$query = parse_url($url);
parse_str($query['query']); 
$matchId = $id;  ////////////////////////////////////////////////////////////////////////////////////matchId
phpQuery::newDocumentFile($url);
//获取比赛时间
$matchTime = pq('.title:eq(0)')->next('div')->html();
preg_match_all('/(\d{4}-\d{1,2}-\d{1,2})/', $matchTime, $matches);
$matchTime = $matches[1][0];///////////////////////////////////////////////////////////////////////matchTime

$team1->setActiveSheetIndex(0)
            ->setCellValue('A1', $matchTime)
            ->setCellValue('A2', '得分')
            ->setCellValue('A3', '投篮率')
            ->setCellValue('A4', '总命中')
            ->setCellValue('A5', '三分率')
            ->setCellValue('A6', '三分数')
            ->setCellValue('A7', '篮球(前)')
            ->setCellValue('A8', '助攻');
$team2->setActiveSheetIndex(0)
            ->setCellValue('A1', $matchTime)
            ->setCellValue('A2', '得分')
            ->setCellValue('A3', '投篮率')
            ->setCellValue('A4', '总命中')
            ->setCellValue('A5', '三分率')
            ->setCellValue('A6', '三分数')
            ->setCellValue('A7', '篮球(前)')
            ->setCellValue('A8', '助攻');





//获取两支球队信息
$team1id = pq('.title:eq(1) a')->attr('href');
preg_match('/.\/team\/(.*)\.html/', $team1id, $matches);
$team1id = $matches[1];
$team1name = pq('.title:eq(1) a')->html();

$team2id = pq('.title:eq(2) a')->attr('href');
preg_match('/.\/team\/(.*)\.html/', $team2id, $matches);
$team2id = $matches[1];
$team2name = pq('.title:eq(2) a')->html();






$time = time();
/*$sql = "insert into `match` (`match_id`,`time`,`team1_id`,`team2_id`,`team1_name`,`team2_name`) values ({$matchId},'{$matchTime}','{$team1id}','{$team2id}','{$team1name}','{$team2name}')";
echo '本场比赛信息写入数据库<br/>';
echo $sql.'<br/>';
if($res = mysql_query($sql) && mysql_affected_rows()){
  echo '写入成功<br/>';
}else{
  echo '写入失败<br/>';
  die('检查本场比赛是否已经记录过了');
}*/


$teamTitle = pq('.title a');

$rowArr = array(
    0=>'B',
    1=>'C',
    2=>'D',
    3=>'E',
    4=>'F',
    5=>'G',
    6=>'H',
    7=>'I',
    8=>'J',
    9=>'K',
    10=>'L',
    11=>'M',
    12=>'N',
  );
foreach ($teamTitle as $k => $v) {
        $temaid = pq($v)->attr('href');
        $teamName = pq($v)->html();
        preg_match('/.\/team\/(.*)\.html/', $temaid, $matches);
        $teamid = $matches[1];
        echo '记录'.$teamName.'队本场比赛信息开始-----------------------------------<br/>';
        //整队数据表
        $infoTable = pq($v)->parent('.title')->next('div')->next('div')->find('.stat_box')->parent()->html();
        //队员的列表数组
        $playersArr = pq($infoTable)->find(".sort");
        foreach ($playersArr as $kk => $vv) {
            //pq($vv)->html();//注释1
            /*
            <td style="border:0px;"></td>
            <td class="normal player_name_out change_color col0 row0"><a href="./player/1342.html" target="_blank">保罗-乔治</a></td>
            <td class="current gs change_color col1 row0" rank="1">1</td>
            <td class="normal mp change_color col2 row0" rank="39">39</td>
            <td class="normal fgper change_color col3 row0" rank="0.473684210526316">47.4%</td>
            <td class="normal fg change_color col4 row0" rank="9">9</td>
            <td class="normal fga change_color col5 row0" rank="19">19</td>
            <td class="normal threepper change_color col6 row0" rank="0.363636363636364">36.4%</td>
            <td class="normal threep change_color col7 row0" rank="4">4</td>
            <td class="normal threepa change_color col8 row0" rank="11">11</td>
            <td class="normal ftper change_color col9 row0" rank="0.769230769230769">76.9%</td>
            <td class="normal ft change_color col10 row0" rank="10">10</td>
            <td class="normal fta change_color col11 row0" rank="13">13</td>
            <td class="normal trb change_color col12 row0" rank="6">6</td>篮板
            <td class="normal orb change_color col13 row0" rank="0">0</td>
            <td class="normal drb change_color col14 row0" rank="6">6</td>
            <td class="normal ast change_color col15 row0" rank="5">5</td>助攻
            <td class="normal stl change_color col16 row0" rank="2">2</td>抢断
            <td class="normal blk change_color col17 row0" rank="0">0</td>盖帽
            <td class="normal tov change_color col18 row0" rank="2">2</td>失误
            <td class="normal pf change_color col19 row0" rank="2">2</td>犯规
            <td class="normal pts change_color col20 row0" rank="32">32</td>得分          
            */

            $playerId = pq($vv)->find('.player_name_out a')->attr('href');
            $playerId = explode('/', $playerId);
            $playerId = trim($playerId[2],'.html');////////////////////////////////////////////////////////////////////////playerId

            $playerName = pq($vv)->find('.player_name_out a')->html();///////////////////////////////////////////////////playerName
            
            /*$sql = "SELECT `players_id` FROM `players` WHERE `players_id` = ".$playerId;

            echo '检测该球员信息是否记录过<br/>'.$sql.'<br/>';
            if ($res = mysql_query($sql) && mysql_affected_rows()) {
              echo '已存在<br/>';
            }else{
              echo '球员信息写入数据库<br/>';
              $sql = "INSERT INTO `players`(`players_id`, `name`) VALUES ({$playerId},'{$playerName}')";
              echo $sql.'<br>';
              if (mysql_query($sql)) {
                echo '写入成功<br/>';
              }else{
                echo '写入失败';
              }
            }*/
                        
            //投篮命中率
            $fgper = pq($vv)->find('.fgper')->html();/////////////////////////////////////////////////////////////////////////fgper
            //命中次数
            $fg = pq($vv)->find('.fg')->html();//////////////////////////////////////////////////////////////////////////////////fg
            //出手次数
            $fga = pq($vv)->find('.fga')->html();///////////////////////////////////////////////////////////////////////////////fga
            //三分命中率
            $threepper = pq($vv)->find('.threepper')->html();/////////////////////////////////////////////////////////////threepper
            //三分命中数
            $threep = pq($vv)->find('.threep')->html();//////////////////////////////////////////////////////////////////////threep
            //三分出手次数
            $threepa = pq($vv)->find('.threepa')->html();///////////////////////////////////////////////////////////////////threepa
            //罚球命中率
            $ftper = pq($vv)->find('.ftper')->html();/////////////////////////////////////////////////////////////////////////ftper
            //罚球命中数
            $ft = pq($vv)->find('.ft')->html();//////////////////////////////////////////////////////////////////////////////////ft
            //罚球数
            $fta = pq($vv)->find('.fta')->html();///////////////////////////////////////////////////////////////////////////////fta
            //篮板
            $trb = pq($vv)->find('.trb')->html();///////////////////////////////////////////////////////////////////////////////trb
            //前场篮板
            $orb = pq($vv)->find('.orb')->html();///////////////////////////////////////////////////////////////////////////////trb
            //助攻
            $ast = pq($vv)->find('.ast')->html();///////////////////////////////////////////////////////////////////////////////ast
            //抢断
            $stl = pq($vv)->find('.stl')->html();///////////////////////////////////////////////////////////////////////////////stl
            //盖帽
            $blk = pq($vv)->find('.blk')->html();///////////////////////////////////////////////////////////////////////////////blk
            //失误
            $tov = pq($vv)->find('.tov')->html();///////////////////////////////////////////////////////////////////////////////tov
            //犯规
            $pf = pq($vv)->find('.pf')->html();//////////////////////////////////////////////////////////////////////////////////pf
            //得分
            $pts = pq($vv)->find('.pts')->html();///////////////////////////////////////////////////////////////////////////////pts
            if ($k ==0) {
              $team1->setActiveSheetIndex(0)
                      ->setCellValue($rowArr[$kk].'1', $playerName)
                      ->setCellValue($rowArr[$kk].'2', $pts)
                      ->setCellValue($rowArr[$kk].'3', $fgper)
                      ->setCellValue($rowArr[$kk].'4', $fg)
                      ->setCellValue($rowArr[$kk].'5', $threepper)
                      ->setCellValue($rowArr[$kk].'6', $threep)
                      ->setCellValue($rowArr[$kk].'7', $orb)
                      ->setCellValue($rowArr[$kk].'8', $ast);
            }else{
              $team2->setActiveSheetIndex(0)
                      ->setCellValue($rowArr[$kk].'1', $playerName)
                      ->setCellValue($rowArr[$kk].'2', $pts)
                      ->setCellValue($rowArr[$kk].'3', $fgper)
                      ->setCellValue($rowArr[$kk].'4', $fg)
                      ->setCellValue($rowArr[$kk].'5', $threepper)
                      ->setCellValue($rowArr[$kk].'6', $threep)
                      ->setCellValue($rowArr[$kk].'7', $orb)
                      ->setCellValue($rowArr[$kk].'8', $ast);
            }
            
            


            /*$sql = "INSERT INTO `record`( `match_id`, `player_id`, `player_team_id`, `fgper`, `fg`, `fga`, `threepper`, `threep`, `threepa`, `ftper`, `ft`, `fta`, `trb`, `ast`, `stl`, `blk`, `tov`, `pf`, `pts`) VALUES ({$matchId},{$playerId},'{$teamid}','{$fgper}',{$fg}, {$fga}, '{$threepper}', {$threep}, {$threepa}, '{$ftper}', {$ft}, {$fta}, {$trb}, {$ast}, {$stl}, {$blk}, {$tov}, {$pf}, {$pts})";
            echo $sql.'<br/>';
            if (mysql_query($sql)) {
              echo '该球员本场比赛信息写入成功<br/>';
            }else{
              echo '该球员本场比赛信息写入失败<br/>';
            }*/
        }

}
$team1->getActiveSheet()->setTitle('nba'.$matchId.'_1');
$team2->getActiveSheet()->setTitle('nba'.$matchId.'_1');
$team1->setActiveSheetIndex(0);
$team2->setActiveSheetIndex(0);

$objWriter1 = PHPExcel_IOFactory::createWriter($team1, 'Excel2007');
$objWriter2 = PHPExcel_IOFactory::createWriter($team2, 'Excel2007');
$objWriter1->save('nba'.$matchId.'_1.xlsx');
$objWriter2->save('nba'.$matchId.'_2.xlsx');


$objWriter1 = PHPExcel_IOFactory::createWriter($team1, 'Excel5');
$objWriter2 = PHPExcel_IOFactory::createWriter($team2, 'Excel5');
$objWriter1->save('nba'.$matchId.'_1.xls');
$objWriter2->save('nba'.$matchId.'_2.xls');

function getContent($url ){
    if(!$url){
        die('未给定URL参数.');
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; KB974487)');
    curl_setopt($ch, CURLOPT_HEADER,0);
    curl_setopt($ch, CURLOPT_AUTOREFERER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_ENCODING, ' gzip, deflate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    $cons = curl_exec($ch);
    curl_close($ch);
    return $cons;
}

//mysql_close($link);



