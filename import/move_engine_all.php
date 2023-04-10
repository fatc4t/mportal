<?php
// 読み終わったファイルを保存フォルダに移動

	$path = "../import/read";
	$save_path = "../import/save";

	function serchDir($comp_code_in, $path, $save_path)
	{
		$comp_code = $comp_code_in;
		if ($handle = opendir($path))
		{
			$queue = array();
			while (false !== ($file = readdir($handle)))
			{
				if (is_dir($path.$file) && $file != '.' && $file !='..') {
					$comp_code = $file;
					serchSubDir($comp_code, $file, $path, $save_path);
				} else if ($file != '.' && $file !='..') {
					$queue[] = $file;
				}
			}

			setQueue($comp_code, $queue, $path, $save_path);

		}

	}

	function setQueue($comp_code, $queue, $path, $save_path)
	{
		foreach ($queue as $file)
		{
			read_file($comp_code, $file, $path.$file, $save_path.$file);
		}
	}

	function serchSubDir($comp_code, $dir, $path, $save_path)
	{
		serchDir($comp_code, $path.$dir."/", $save_path.$dir."/");
	}

	function read_file($comp_code, $filename, $filefull, $filefull_save)
	{
		$time_start = microtime(true);

		rename($filefull, $filefull_save);

		echo "(".$filename.") ファイルを保存しました<br />\n";
		log_insert("", "", "", "(".$filename.") ファイル保存に成功しました", "POSインポート", "", "");

		$timelimit = microtime(true) - $time_start;
		echo $timelimit." seconds<br />\n";

		echo "-----<br />\n";

		@ob_flush();
		@flush();

skip:

	}

	serchDir("", $path, $save_path);

?>
