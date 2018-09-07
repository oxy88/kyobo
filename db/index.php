<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>db</title>

<?
if ($_POST['pw'] != 'dododo') { ?>
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
</head>
<body>


<style>
	input.pw {
		display: block;
		margin: 80px auto;
		height: 40px;
		width: 190px;
	}
</style>
<form action="" method="post">
	<input type="password" name='pw' class="pw">
</form>

<?
exit;
}
$mbti = array("ESFP","ESTP","ISFP",	"ESFJ",	"ESTJ",	"ISFJ",	"ISTJ",	"ENFP",	"ENFJ",	"INFP",	"INFJ",	"ENTP",	"ENTJ",	"INTP",	"INTJ",	"ISTP");
$mbti_bible = array(
	"ESFP" => "자유로운 영혼의 연예인",
	"ESTP" => "모험을 즐기는 사업가",
	"ISFP" => "호기심 많은 예술가",
	"ESFJ" => "사교적인 외교관",
	"ESTJ" => "엄격한 관리자",
	"ISFJ" => "용감한 수호자",
	"ISTJ" => "청렴결백한 논리주의자",
	"ENFP" => "재기발랄한 활동가",
	"ENFJ" => "정의로운 사회운동가",
	"INFP" => "열정적인 중재자",
	"INFJ" => "선의의 옹호자",
	"ENTP" => "뜨거운 논쟁을 즐기는 변론가",
	"ENTJ" => "대담한 통솔자",
	"INTP" => "논리적인 사색가",
	"INTJ" => "용의주도한 전략가",
	"ISTP" => "만능 재주꾼"
);

$host = 'localhost';
$user = 'allhobybox';
$pw = 'hobybox7410!!';
$dbName = 'allhobybox';
$mysqli = new mysqli($host, $user, $pw, $dbName);




//$query = $mysqli->query("SELECT * FROM table WHERE condition");


function daily_db_insert($mysqli, $data) {
	$query = "INSERT into popup_kyobo_static (reg_date, count, data) VALUES (?,?,?)";
	$stmt = mysqli_prepare($mysqli, $query);
	mysqli_stmt_bind_param($stmt, 'sis', $data['reg_date'], $data['count'], json_encode($data));
	mysqli_stmt_execute($stmt);
}

function get_date_calc($calc, $edate) {
	$date_format = 'Y-m-d';
	return date($date_format, strtotime($calc, strtotime($edate)));
}


function analyize($mysqli, $pdate) {
	$date_start = $pdate." 00:00:00";
	$date_end = $pdate." 23:59:59";

	if ($query = $mysqli->query("SELECT * FROM popup_kyobo WHERE reg_date>'$date_start' and reg_date<'$date_end'")) {

		$seg_mbti = array();
		$seg_time = array();
		$count = 0;
		$count_t580 = 0;
		$count_kiosk = 0;

		$user_agent_t580 = "Mozilla/5.0 (Linux; Android 6.0.1; SAMSUNG SM-T580 Build/MMB29K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/4.0 Chrome/44.0.2403.133 Safari/537.36";
		$user_agent_kiosk = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36";

	    while ($row = $query->fetch_assoc()) {
	    	$seg_mbti[$row['mbti']]++;
	        $count++;
	        $hour = date('H', strtotime($row[reg_date]));
	        $seg_time[$hour]++;
	        if ($row['user_agent'] == $user_agent_kiosk) {
	        	$count_kiosk++;

	        } else if ($row['user_agent'] == $user_agent_t580) {
	        	$count_t580++;
	        }

	    }

	    // print_r($seg_mbti);
	    // echo "<br>";
	    // print_r($seg_time);
	    // echo "<br>";
	    // echo $count." ".$count_t580." ".$count_kiosk;
	    // echo "<br>";

	    $result = array('reg_date'=>$pdate, 'seg_mbti' => $seg_mbti, 'seg_time' => $seg_time, 'count' => $count, 'count_t580'=>$count_t580, 'count_kiosk' => $count_kiosk);

	    /* free result set */
	    daily_db_insert($mysqli, $result);
	    $query->free();
	    return $result;
	}
}

$date_format = 'Y-m-d';
$pdate = "2017-08-10";
$static_data =  array();

$total = array();

do {
	$pdate = get_date_calc('+1 day', $pdate);
	if ($query = $mysqli->query("SELECT * FROM popup_kyobo_static WHERE reg_date='$pdate'")) {
		if ($query->num_rows > 0) {
			while ($row = $query->fetch_assoc()) {
				$static_data[$row['reg_date']] = json_decode($row['data'], true);
			}
		} else {
			$static_data[$pdate] = analyize($mysqli, $pdate);
		}
		$day_count = $static_data[$pdate]['count'];
		$total['count'] += $day_count;
		$total['count_int'] += $static_data[$pdate]['count_t580'] + $static_data[$pdate]['count_kiosk'];
		$total['seg_day'][date('m-d', strtotime($pdate))] = $day_count;
		if ($total['seg_day_max'] < $day_count) {
			$total['seg_day_max'] = $day_count;
		}
		foreach ($mbti as $value) {
			$mbti_num = $static_data[$pdate]['seg_mbti'][$value];
			if ($static_data[$pdate]['seg_mbti_max'] < $mbti_num) {
				$static_data[$pdate]['seg_mbti_max'] = $mbti_num;
			}
			$total['seg_mbti'][$value] += $mbti_num;
			if (strpos($value, 'E') !== false) {
				$total['mbti_dist']['E'] += $mbti_num;
			} else {
				$total['mbti_dist']['I'] += $mbti_num;
			}
			if (strpos($value, 'S') !== false) {
				$total['mbti_dist']['S'] += $mbti_num;
			} else {
				$total['mbti_dist']['N'] += $mbti_num;
			}
			if (strpos($value, 'F') !== false) {
				$total['mbti_dist']['F'] += $mbti_num;
			} else {
				$total['mbti_dist']['T'] += $mbti_num;
			}
			if (strpos($value, 'P') !== false) {
				$total['mbti_dist']['P'] += $mbti_num;
			} else {
				$total['mbti_dist']['J'] += $mbti_num;
			}
		}
		for ($i=0; $i < 24; $i++) { 
			$zi = ($i<10) ? '0'.$i : $i;
			$time_num = $static_data[$pdate]['seg_time'][$zi];
			if (!$time_num) {
				$static_data[$pdate]['seg_time'][$zi] = 0;
			}
			if ($static_data[$pdate]['seg_time_max'] < $time_num) {
				$static_data[$pdate]['seg_time_max'] = $time_num;
			}
			$total['seg_time'][$i] += $time_num;
		}
	}
	//echo get_date_calc('+1 day', $pdate).'----'.date($date_format);
} while (get_date_calc('+1 day', $pdate) != date($date_format));


foreach ($total['seg_mbti'] as $key => $value) {
	if ($total['seg_mbti_max'] < $value) {
		$total['seg_mbti_max'] = $value;
	}
}
foreach ($total['seg_time'] as $key => $value) {
	if ($total['seg_time_max'] < $value) {
		$total['seg_time_max'] = $value;
	}
}

// echo '<pre>';
// print_r($static_data);
// print_r($total);
// echo '</pre>';
mysqli_close($mysqli);
?>


<style>
	.clear {
		clear: both;
	}
	.info {
		padding: 20px;
	    background-color: #eee;
	    margin: 20px;
	}
	.total_count {
		font-size: 1.6em;
	    color: #555;
	}
	.total_count .ext {
		font-size: 0.6em;
	}
	.sub_title {
		background-color: #eee;
	    padding: 10px;
	}
 	.chart {
 		float:left;
		padding-top: 60px;
	    margin-right: 10px;
	}
	.chart .box {
		float: left;
		margin-right: 5px;
	}
	.chart .graph {
		height: 100px;
	    position: relative;
	    width: 35px;
	}
	.chart .stick {
		background-color: #f3afcb;
		position: absolute;
		bottom: 0;
		left: 0;
		width: 100%;
	}
	.chart .avarage {
		position: absolute;
		width: 100%;
		bottom: 0;
		left: 0;
		border-top: 2px solid #d80000;
	}
	.chart .txt {
		text-align: center;
		font-size: 0.9em;
		color: gray;
	}
	.chart .txt .num {
		font-size: 0.8em;
	}
	.chart.time .box {
		margin-right: 3px;
	}
	.chart.time .graph {
		width: 24px;
	}
	.chart.time .stick {
		background-color: #cecece;
	}
	.chart.time .avarage {
		border-top: 2px solid #7b7b7b;
	}
	.chart.day .stick {
		background-color: #cecece;
	}

	.chart .mbti_title {
		z-index: 400;
		position: absolute;
		font-size: 0.6em;
		color: rgb(224, 105, 105);
		transform: rotate(-90deg);
		width: 100px;
		left: -38px;
		bottom: 50px;
	}
	.chart .box.max .mbti_title {
		color:rgb(134, 35, 35);
	}
	.chart .box.max .txt {
		font-weight: 900;
		color:black;
	}
	.chart .box.max .stick {
		background-color: #e66f96;
	}
	.chart.time .box.max .stick, .chart.day .box.max .stick {
		background-color: #a0a0a0;
	}

	.daily_title {
		color: gray;
		font-size: 1.2em;
		background-color: #eee;
		padding: 6px;
	}
	.daily_title .count {
		font-weight: 600;
		color:black;
	}

	.mbti_dist {
		margin-top: 60px;
	}
	.mbti_dist .bar_wrap {
		width: 308px;
		position: relative;
		font-size: 1.3em;
		height:50px;
	}
	.mbti_dist .left {
		position: absolute;
		left: 0;
	}
	.mbti_dist .right {
		position: absolute;
		right: 0;
	}
	.mbti_dist .bar_back {
		position: absolute;
		left: 17px;
		width: 260px;
		background: #a06969;
		height: 20px;
		color: white;
		font-weight: 200;
		text-align: right;
		font-size: 0.8em;
		padding: 5px;
		letter-spacing: 0.05em;
	}
	.mbti_dist .bar_left {
		background-color: #981c1c;
		height: 100%;
		color: white;
		position: absolute;
		top: 0;
		left: 0;
		text-align: left;
		box-sizing: border-box;
		padding: 5px;
	}

	.day_combo_wrap {
		clear: both;
		margin-top: 30px;
		overflow: hidden;
	}
	.charts_wrap {}

</style>
</head>
<body>

<?
function prt_daytitle($static_today) {

	$daily = array('일','월','화','수','목','금','토');
	$today = date('w', strtotime($static_today['reg_date']));
	
	echo '<div class="daily_title">▼'.$static_today['reg_date'].'('.$daily[$today].') <span class="count">'.$static_today['count'].'명</span></div>';
}
function prt_chart($static, $type, $is_sort = true) {
	global $total, $static_data, $mbti_bible;
	$static_key = $static['seg_'.$type];
	$max = $static['seg_'.$type.'_max'];
	$avarage_data = $total['seg_'.$type];
	if ($is_sort) ksort($static_key);
	echo '<div class="chart '.$type.'">';

	$is_total = ($static_key == $avarage_data) ? true : false;

	foreach ($static_key as $key => $value) {
		//echo $total['mbti'][$value];
		$avarage = $avarage_data[$key] / count($static_data);
		$is_max = ($value == $max) ? " max" : "";
		echo '<div class="box'.$is_max.'">';
			echo '<div class="graph">';
				echo '<div class="stick" style="height:'.($value/$max*100).'%"></div>';
				if (!$is_total) echo '<div class="avarage" style="height:'.($avarage/$max*100).'%"></div>';
				if ($type == 'mbti' && $is_total) echo '<div class="mbti_title">'.$mbti_bible[$key].'</div>';
			echo '</div>';
			echo '<div class="txt">'.$key.'<br><span class="num">'.$value.'</span></div>';
		echo '</div>';
		
	}
	echo '</div>';
	
}

function prt_mbti_dist() {
	echo '<div class="mbti_dist">';
	prt_mbti_dist_bar('E', 'I');
	prt_mbti_dist_bar('S', 'N');
	prt_mbti_dist_bar('T', 'F');
	prt_mbti_dist_bar('J', 'P');
	echo '</div>';
}

function prt_mbti_dist_bar($left, $right) {
	global $total;
	$total_num = $total['mbti_dist'][$left]+$total['mbti_dist'][$right];
	$left_percent = intval($total['mbti_dist'][$left]/$total_num*1000)/10;
	$right_percent = intval($total['mbti_dist'][$right]/$total_num*1000)/10;
	echo '<div class="bar_wrap">';
		echo '<div class="left">'.$left.'</div>';
		echo '<div class="right">'.$right.'</div>';
		echo '<div class="bar_back">';
			echo $right_percent.'%';
			echo '<div class="bar_left" style="width:'.$left_percent.'%">'.$left_percent.'%</div>';
		echo '</div>';
	echo '</div>';
}

function prt_total_count() {
	global $total;
	$ext_num = $total['count']-$total['count_int'];
	$ext_percent = intval($ext_num/$total['count']*1000)/10;
	echo '<div class="total_count">총 접속 ';
	echo $total['count'].' <span class="ext">외부 접속 '.$ext_num.' ('.$ext_percent.'%)</span>';
	echo '</div>';
}

function prt_subtitle($txt) {
	echo '<div class="sub_title">'.$txt.'</div>';
}
function clear(){
	echo'<div class="clear"></div>';
}
function prt_combo($edata, $title="") {
	echo '<div class="day_combo_wrap">';
	if ($title != "") prt_subtitle($title);
	else {
		prt_daytitle($edata);
	}
	echo '<div class="charts_wrap">';
	prt_chart($edata, 'mbti');
	prt_chart($edata, 'time');
	echo '</div></div>';
}
?>


<div class="info">
	- 당일 데이터는 적용되지 않고, 매일 0시에 업데이트 됩니다.<br>
	- 일일 그래프에서 실선은 평균치 입니다.<br>
	- 외부 접속 통계는 정확하지 않습니다. 실제보다 약간 적게 나옴.<br>
	- 현장의 기기들이 인터넷 접속을 유지해야 데이터가 집계됩니다.<br>
	- INFP, ESTJ는 한쪽 버튼만 누른결과로 테스트 등에 의해 허수가 포함되어 있습니다.<br>
</div>

<?
prt_total_count();
prt_chart($total, 'day');
clear();

$total_sort = $total;
arsort($total_sort['seg_mbti']);
prt_chart($total_sort, 'mbti', false);
clear();
prt_mbti_dist();


prt_combo($total, '총접속');

krsort($static_data);
foreach ($static_data as $key => $value) {
	prt_combo($value);
}

?>





</body>
</html>