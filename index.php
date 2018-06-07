<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
	      integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
	<title>Document</title>
</head>
<body>
<?php
function array_merge_time($time, $part) {
	foreach ($time as $key => $value) {
		if (!isset($part[$key])) {
			$part[$key] = $value;
		}
		else {
			$part[$key] += $value;
		}
	}
	return $part;
}

function format($time) {
	if (isset($time['недели'])) {
		if (isset($time['дни'])) {
			$time['дни'] += $time['недели'] * 5;
		}
		else {
			$time['дни'] = $time['недели'] * 5;
		}
		unset($time['недели']);
	}
	if (isset($time['дни'])) {
		if (isset($time['часы'])) {
			$time['часы'] += $time['дни'] * 8;
		}
		else {
			$time['часы'] = $time['дни'] * 8;
		}
		unset($time['дни']);
	}
	if (isset($time['минуты']) and $time['минуты'] >= 60) {
		if (isset($time['часы'])) {
			$time['часы'] += floor($time['минуты'] / 60);
		}
		else {
			$time['часы'] = floor($time['минуты'] / 60);
		}
		$time['минуты'] %= 60;
	}
	return $time;
}
$ch = curl_init();
$url = "http://gitlab.rosoperator.ru/api/v4/projects/15/labels?private_token=15jLVm73mpCEyEf8dv2-";
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$result1 = json_decode($result);
echo "<form method='get' role='form'>
	<select name='name' id='inputID' class='form-control'>";
foreach ($result1 as $value) {
	echo "<option>{$value->name}</option>";
};
echo "	</select>
	<button type='submit' class='btn btn-primary'>Submit</button>
</form>
</select>";
if (isset($_GET['name'])) {
	echo "<span class='name-label'>Метка: {$_GET['name']}</span><br>";
	$url = "http://gitlab.rosoperator.ru/api/v4/projects/15/issues?private_token=15jLVm73mpCEyEf8dv2-";
	curl_setopt($ch, CURLOPT_URL, $url);
	$issues = curl_exec($ch);
	$issues1 = json_decode($issues);
	$sum = 0;
	$part = array();
	foreach ($issues1 as $value) {
		foreach ($value->labels as $item) {
			if ($item == $_GET['name']) {
				$sum = $sum + $value->time_stats->total_time_spent;
				$url = "http://gitlab.rosoperator.ru/api/v4/projects/15/issues/{$value->iid}/notes?private_token=15jLVm73mpCEyEf8dv2-";
				curl_setopt($ch, CURLOPT_URL, $url);
				$notes = curl_exec($ch);
				$notes1 = json_decode($notes);
				if ($value->time_stats->total_time_spent != 0) {
					foreach ($notes1 as $str) {
						if (strpos($str->body, 'of time spent')) {
							$f = 0;
							$temptime = explode(" ", substr($str->body, 6,  strpos($str->body, 'of time spent') - 7));
							if (strpos($str->body, 'subtracted') !== FALSE) {
								$f = 1;
								$temptime = explode(" ", substr($str->body, 11,  strpos($str->body, 'of time spent') - 12));
							}
							$time = array();
							foreach ($temptime as $parttime) {
								if ($parttime[-1] == 'w') {
									$time['недели'] = (int)$parttime;
									if ($f == 1) {
										$time['недели'] = 0 - $time['недели'];
									}
									continue;
								}
								if ($parttime[-1] == 'd') {
									$time['дни'] = (int)$parttime;
									if ($f == 1) {
										$time['дни'] = 0 - $time['дни'];
									}
									continue;
								}
								if ($parttime[-1] == 'h') {
									$time['часы'] = (int)$parttime;
									if ($f == 1) {
										$time['часы'] = 0 - $time['часы'];
									}
									continue;
								}
								if ($parttime[-1] == 'm') {
									$time['минуты'] = (int)$parttime;
									if ($f == 1) {
										$time['минуты'] = 0 - $time['минуты'];
									}
									continue;
								}
							}
							$man = $str->author->name;
							if (isset($part[$man])) {
								$part[$man] = array_merge_time($time, $part[$man]);
							}
							else {
								$part[$man] = $time;
							}
						}
					}
				}
				break;
			}
		}
	}
	echo "<br><span class='name-label'>Общее время:</span>";
	$minutes = $sum / 60;
	$hours = floor($minutes / 60);
	$minutes %= 60;
	if ($hours != 0) {
		echo "<br> часов: " . $hours;
	}
	if ($minutes != 0) {
		echo "<br> минут: " . $minutes;
	}
	if ($sum == 0) {
		echo "<br> 0 ";
	}
	curl_close($ch);
	if ($sum != 0) {
		echo "<br><span class='name-label'>Участники:</span><br>";
		foreach ($part as $name => $value) {
			echo "{$name}<br>";
			$value = format($value);
			foreach ($value as $key => $item) {
				echo "{$key} - {$item}<br>";
			}
		}
	}
}
?>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
        integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T"
        crossorigin="anonymous"></script>
</html>