<?php
$hostname = 'hostname';
$username = 'username';
$password = 'password';
$database = 'database';

$server = 1;

$request = array(
	'GET' => clean($_GET),
	'POST' => clean($_POST),
	'REQUEST' => clean($_REQUEST),
	'COOKIE' => clean($_COOKIE),
	'FILES' => clean($_FILES),
	'SERVER' => clean($_SERVER),
);

function img_resize_save($target, $newcopy, $w, $h, $type, $crop = false) {
	list($w_orig, $h_orig) = getimagesize($target);
		
	if($type == 'image/gif') {
		$img = @imagecreatefromgif($target);
	} elseif($type == 'image/png') {
		$img = @imagecreatefrompng($target);
	} else {
		$img = @imagecreatefromjpeg($target);
	}
	
	if($img) {
		if($crop) {
			if($w_orig > $h_orig) {
				$width = floor($w_orig * ($h / $h_orig));
				$height = $h;
				$x = ceil(($w_orig - $h_orig) / 2);
				$y = 0;
			} else {
				$width = $w;
				$height = floor($h_orig * ($w / $w_orig));
				$x = 0;
				$y = ceil(($h_orig - $w_orig) / 2);
			}
			
			$tci = imagecreatetruecolor($w, $h);
			$white = imagecolorallocate($tci, 255, 255, 255);
			imagefill($tci, 0, 0, $white);
			imagecopyresampled($tci, $img, 0, 0, $x, $y, $width, $height, $w_orig, $h_orig);
			imagejpeg($tci, $newcopy, 80);
			imagedestroy($img);
			imagedestroy($tci);
		} else {
			$scale_ratio = $w_orig / $h_orig;
			if(($w / $h) > $scale_ratio) {
				$w = $h * $scale_ratio;
			} else {
				$h = $w / $scale_ratio;
			}
			
			$tci = imagecreatetruecolor($w, $h);
			$white = imagecolorallocate($tci, 255, 255, 255);
			imagefill($tci, 0, 0, $white);
			imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
			imagejpeg($tci, $newcopy, 80);
			imagedestroy($img);
			imagedestroy($tci);
		}
		
		return true;
	} else {
		return false;
	}
}

function config($link, $key = null) {
	$query = query($link, "SELECT * FROM system_configs WHERE config_key = '" . mysqli_real_escape_string($link, $key) . "'");
	if($query->num_rows) {
		return htmlspecialchars_decode($query->row['config_value']);
	} else {
		return $key;
	}
}

function query($link, $sql) {
	$result = mysqli_query($link, $sql);
	
	if($result) {
		if($result instanceof mysqli_result) {
			$i = 0;
			$data = array();
			
			while($row = mysqli_fetch_assoc($result)) {
				$data[$i] = $row;
				$i++;
			}
			
			mysqli_free_result($result);
			
			$query = new stdClass();
			$query->row = isset($data[0]) ? $data[0] : array();
			$query->rows = $data;
			$query->num_rows = $i;
			
			unset($data);
			
			return $query;
		} else {
			return true;
		}
	} else {
		exit('Error: ' . mysqli_error($link) . '<br>Error number: ' . mysqli_errno($link) . '<br>' . $sql);
	}
}

function connect($hostname, $username, $password, $database) {
	$link = mysqli_connect($hostname, $username, $password);
	
	if(!$link) {
		exit('Error: Failed to connect to the database server!');
	}
	
	if(!mysqli_select_db($link, $database)) {
		exit('Error: Failed to connect to ' . $database . ' database!');
	}
	
	mysqli_query($link, "SET NAMES 'utf8'");
	mysqli_query($link, "SET CHARACTER SET utf8");
	mysqli_query($link, "SET CHARACTER_SET_CONNECTION=utf8");
	mysqli_query($link, "SET SQL_MODE = ''");
	
	return $link;
}

function clean($data) {
	if(is_array($data)) {
		foreach($data as $key => $value) {
			unset($data[$key]);
			$data[clean($key)] = clean($value);
		}
	} else {
		$data = htmlspecialchars($data, ENT_COMPAT);
	}
	return $data;
}
?>