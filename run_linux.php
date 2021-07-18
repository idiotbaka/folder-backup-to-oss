<?php
use OSS\OssClient;
use OSS\Core\OssException;
require_once __DIR__ . '/vendor/autoload.php';

date_default_timezone_set('Asia/Shanghai');

// 读取配置文件
$config_file = 'config.json';
$fopen_config = fopen($config_file, 'r');
$config = fread($fopen_config, filesize($config_file));
fclose($fopen_config);

// 判断配置文件格式
$config = json_decode($config, true);
if(!isset($config['backup_folder_path'])) {
	die('config.json文件格式错误，错误的json格式。');
}

// 操作系统判断
$is_windows = 0;
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	$is_windows = 1;
}

$wait_today_print = false;
$wait_tomorrow_print = false;
while(true) {
	sleep(5);
	// 判断是否到运行时间
	$today_date = date('Y-m-d');
	$run_time = $today_date.' '.$config['backup_time'].':00';
	if(time() < strtotime($run_time)) {
		if(!$wait_today_print) {
			echo '等待至'.$run_time.'运行备份...'.PHP_EOL;
			$wait_today_print = true;
		}
		continue;
	}
	// 读取运行日志
	$backup_file = 'backup.log';
	$fopen_backup = fopen($backup_file, 'r');
	$backup_log = fread($fopen_backup, filesize($backup_file));
	fclose($fopen_backup);
	$backup_log = json_decode($backup_log, true);
	if(in_array($today_date, $backup_log['backup_history'])) {
		if(!$wait_tomorrow_print) {
			echo '等待至'.date('Y-m-d H:i:s', strtotime($run_time) + 86400).'运行备份...'.PHP_EOL;
			$wait_tomorrow_print = true;
		}
		continue;
	}
	
	$object = $config['upload_filename_prefix'].'-'.date('Y-m-d_H_i_s').'.zip';
	// 压缩文件夹
	echo '正在压缩文件夹'.$config['backup_folder_path'].'...'.PHP_EOL;
	// windows
	if($is_windows) {
		$file_path = __dir__.'\\'.$object;
		$zip_folder = shell_exec('"'.__dir__.'\7z.exe" a -r "'.$file_path.'" "'.$config['backup_folder_path'].'\*"');
		echo $zip_folder;
	}
	// unix
	else {
		$file_path = __dir__.'/'.$object;
		$zip_folder = shell_exec('zip -r "'.$file_path.'" "'.$config['backup_folder_path'].'"');
		echo $zip_folder;
	}

	// 判断是否压缩成功	
	if(!file_exists($file_path)) {
		echo '文件夹'.$config['backup_folder_path'].'压缩失败，请查看上述错误日志。'.PHP_EOL;
		continue;
	}
	else {
		echo '文件夹'.$config['backup_folder_path'].'压缩成功。'.PHP_EOL;
	}

	// 上传到oss
	echo '正在上传至Oss...'.PHP_EOL;
	$upload_result = oss_uplaod($object, $file_path);
	if(!is_array($upload_result)) {
		echo '上传至Oss失败，请查看上述错误日志。'.PHP_EOL;
	}
	else {
		echo '备份上传成功。'.PHP_EOL;
		echo 'URL: '.$upload_result['info']['url'].PHP_EOL;
		echo '上传耗时: '.$upload_result['info']['total_time'].'秒'.PHP_EOL;
		echo '文件大小: '.$upload_result['info']['size_upload'].'字节'.PHP_EOL;
	}

	if($config['auto_delete_local']) {
		unlink($file_path);
		echo '本地文件'.$file_path.'已删除。'.PHP_EOL;
	}

	// 写入备份日志
	$backup_log['backup_history'][] = $today_date;
	$fopen_backup = fopen($backup_file, 'w');
	fwrite($fopen_backup, json_encode($backup_log));
	fclose($fopen_backup);
	echo '已写入备份日志'.PHP_EOL;
	$wait_today_print = false;
	$wait_tomorrow_print = false;
}

function oss_uplaod($object, $filePath) {
	global $config;
	$accessKeyId = $config['access_key_id'];
	$accessKeySecret = $config['access_key_secret'];
	$endpoint = $config['endpoint'];
	$bucket= $config['bucket'];
	// 如果有bucket路径，则设置object路径
	if($config['bukket_path']) {
		$object = $config['bukket_path'].'/'.$object;
	}

	try{
	    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
	    $result = $ossClient->uploadFile($bucket, $object, $filePath);
	    return $result;
	} catch(OssException $e) {
	    printf(__FUNCTION__ . ": FAILED\n");
	    printf($e->getMessage() . "\n");
	    return false;
	}
}