<?php
header('Access-Control-Allow-Origin: *');

mb_internal_encoding('UTF-8');

require_once('engine.php');

$link = connect($hostname, $username, $password, $database);

if(isset($request['FILES']['file']) && isset($request['FILES']['file']['tmp_name']) && !is_array($request['FILES']['file']['tmp_name'])) {
	if(!$request['FILES']['file']['error']) {
		if(!is_dir('content')) {
			mkdir('content');
		}
		
		if(isset($request['POST']['type']) && !is_array($request['POST']['type']) && $request['POST']['type'] == 'image') {
			if(mime_content_type($request['FILES']['file']['tmp_name']) == 'image/png' || mime_content_type($request['FILES']['file']['tmp_name']) == 'image/jpeg') {
				$address = 'content/' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16));
				$path = $address . '/' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16));
				
				mkdir($address);
				
				if(move_uploaded_file($request['FILES']['file']['tmp_name'], $path)) {
					$s = $address . '/s-' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$p = $address . '/p-' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$m = $address . '/m-' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$z = $address . '/z-' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$crop_s = $address . '/crop_s' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$crop_p = $address . '/crop_p' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$crop_m = $address . '/crop_m' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					$crop_z = $address . '/crop_z' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					
					$original = $address . '/' . mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16)) . '.jpg';
					
					img_resize_save($path, $s, 180, 180, mime_content_type($path));
					img_resize_save($path, $p, 360, 360, mime_content_type($path));
					img_resize_save($path, $m, 720, 720, mime_content_type($path));
					img_resize_save($path, $z, 1440, 1440, mime_content_type($path));
					img_resize_save($path, $crop_s, 180, 180, mime_content_type($path), true);
					img_resize_save($path, $crop_p, 360, 360, mime_content_type($path), true);
					img_resize_save($path, $crop_m, 720, 720, mime_content_type($path), true);
					img_resize_save($path, $crop_z, 1440, 1440, mime_content_type($path), true);
					
					$org = img_resize_save($path, $original, 3840, 3840, mime_content_type($path));
					
					unlink($path);
					
					if($org) {
						$query = query($link, "SELECT * FROM upload_servers WHERE server_id = '" . mysqli_real_escape_string($link, $server) . "'");
						if($query->num_rows) {
							$url = $query->row['server_url'];
						} else {
							$query = query($link, "SELECT * FROM upload_servers WHERE server_id = '" . mysqli_real_escape_string($link, config($link, 'upload')) . "'");
							if($query->num_rows) {
								$url = $query->row['server_url'];
							} else {
								$url = $server;
							}
						}
						
						$secret = mb_strtolower(mb_substr(md5(time() . mb_substr(str_shuffle('abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 16)), rand(1, 8), 16));
						$content = array(
							's' => $s,
							'p' => $p,
							'm' => $m,
							'z' => $z,
							'crop_s' => $crop_s,
							'crop_p' => $crop_p,
							'crop_m' => $crop_m,
							'crop_z' => $crop_z,
							'original' => $original
						);
						
						foreach($content as $key => $value) {
							$images[$key] = $url . '/' . $value;
						}
						
						query($link, "INSERT INTO upload_images SET image_server = '" . mysqli_real_escape_string($link, $server) . "', image_secret = '" . mysqli_real_escape_string($link, $secret) . "', image_content = '" . mysqli_real_escape_string($link, json_encode($content)) . "', image_date_add = NOW()");
						
						$data['status'] = 'success';
						$data['image'] = $secret;
						$data['images'] = $images;
					} else {
						rmdir($address);
						
						$data['status'] = 'error';
						$data['type'] = 6;
					}
				} else {
					rmdir($address);
					
					$data['status'] = 'error';
					$data['type'] = 5;
				}
			} else {
				$data['status'] = 'error';
				$data['type'] = 4;
			}
		} else {
			$data['status'] = 'error';
			$data['type'] = 3;
		}
	} else {
		$data['status'] = 'error';
		$data['type'] = 2;
	}
} else {
	$data['status'] = 'error';
	$data['type'] = 1;
}

mysqli_close($link);

exit(json_encode($data));
?>