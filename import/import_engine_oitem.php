<?php
// キーファイル名の設定があるファイルを全て読み込む
//key_type が 2のみ
require_once("DBAccess_Function.php");

        // POSファイル保存パス
	$path = "../profit/import/ozaki_shoji/read/";

        // 対象パスにあるファイルを読み込んでリスト化する
	function serchDir($comp_code_in, $path)
	{
		$comp_code = $comp_code_in;
		if ($handle = opendir($path))
		{

                    // ファイルリスト用変数
                    $queue = array();
                        
                        //　フォルダ内のチェック
			while (false !== ($file = readdir($handle)))
			{
                                // サブディレクトリのチェック
				if (is_dir($path.$file) && $file != '.' && $file !='..') {
                                    // ディレクトリの場合は下層から実施する
					$comp_code = $file;
					serchSubDir($comp_code, $file, $path);
				} else if ($file != '.' && $file !='..') {
                                    // ファイルリストに入れる
					$queue[] = $file;
				}
			}

                        // 読み込みを実行
			setQueue($comp_code, $queue, $path);

		}

	}

        // サブディレクトリを再帰的に呼び出す
	function serchSubDir($comp_code, $dir, $path)
	{
		serchDir($comp_code, $path.$dir."/");
	}

        // ファイル数、読み込みを実施
	function setQueue($comp_code, $queue, $path)
	{
		foreach ($queue as $file)
		{
			read_file($comp_code, $file, $path.$file);
		}
	}

        //ファイルを読み込んで書き込み
	function read_file($comp_code, $filename, $filefull)
	{
		$time_start = microtime(true);

                //企業マスタを読込み
                $sql = 'SELECT * FROM public.m_company_contract WHERE is_del = 0';
                $companyContractList = getList($sql);

                $key_file_name = "";

                // スキーマ名
                $schema = "ozaki_shoji";

                // $filename からPOS種別を判断して、日付を取り出す yyyymmdd
		$sql = "select * from ".$schema.".m_pos_key_file where is_del = 0 and pos_key_type = 2";

                $key_file = array();

                $rows = getList($sql);
		$key_file_cnt = count($key_file);
               
                if($rows){
			while($row = $rows[$key_file_cnt]) {
				$key_file[$key_file_cnt] = $row["pos_key_file_name"];
				$key_file_cnt += 1;
			}
		}

                //ファイル名の中にキーファイル名があるかチェック
		$key_file_ok = 0;
		for ($jj = 0; $jj < $key_file_cnt; $jj++){
			if (strstr($filename, $key_file[$jj].'.csv')){

                                //ファイル名から日付取り出し
				$tenpo_code = substr($filename, 1, 4);	//店舗コード
				$yyyy = substr($filename, 6, 4);	//yyyy
				$mm = substr($filename, 10, 2);		//mm
				$dd = substr($filename, 12, 2);		//dd
				$key_file_name = $key_file[$jj];	//キーファイル名
				$key_file_ok = 1;
				break;
			}
		}

		if ($key_file_ok == 0){
                //キーファイルの指定なし　処理スキップ
                //			echo "(".$filename.")POSキーファイルの指定が無いファイルを検出しました<br />\n";
                //			log_insert("", "", "", "POSキーファイルの指定が無いファイルを検出しました", "POSインポート", "", "");
			goto skip;
		}

		$table_name = $schema.".t_import_data_item";
		$num = 1;
		$get_ok = 0;

		$fp = fopen($filefull, 'r');

		if ($fp){
			if (flock($fp, LOCK_SH)){
                                // 取込み前に対象日の同じキーファイルタイプで該当組織分を上書きにするため削除
			  	$sql = "DELETE FROM ".$table_name." WHERE target_date = '".$yyyy.$mm.$dd."' and pos_key_file_id = '".$key_file_name."' and pos_code = '".$tenpo_code."'";

                                sqlExec($sql);
                                
				while (!feof($fp)) {
					$buffer = fgets($fp);

                                        //デリミタを除外
                                        $str =  str_replace('"','',char_chg("to_db", $buffer));

					$sql = "INSERT INTO ".$table_name." (";
						$sql .= "num, ";
						$sql .= "pos_code, ";
						$sql .= "target_date, ";
						$sql .= "file_name, ";
						$sql .= "data_line, ";
						$sql .= "pos_key_file_id, ";
						$sql .= "registration_time ";
						$sql .= ") ";
						$sql .= "VALUES(";
						$sql .= $num.", ";
						$sql .= "'".$tenpo_code."', ";
						$sql .= "'".$yyyy.$mm.$dd."', ";
						$sql .= "'".$filename."', ";
						$sql .= "'".$str."', ";
						$sql .= "'".$key_file_name."', ";
						$sql .= "CURRENT_TIMESTAMP)";


					sqlExec($sql);
					$num ++;
				}

                                // ファイルのロックを解除
				flock($fp, LOCK_UN);
                                
                                // mcode取込みを呼び出し
                                require("create_engine_oitem.php");
                                
			}else{
				echo "(".$filename.") ファイルのロックに失敗しました<br />\n";
				//log_insert("", "", "", "(".$filename.") ファイルのロックに失敗しました", "POSインポート", "", "");
			}
		}

		echo "(".$filename.") ファイルの取り込みに成功しました<br />\n";
		//log_insert("", "", "", "(".$filename.") ファイルの取り込みに成功しました", "POSインポート", "", "");

		$timelimit = microtime(true) - $time_start;
		echo $timelimit." seconds<br />\n";

		echo "-----<br />\n";

		@ob_flush();
		@flush();

skip:

	}

serchDir("", $path);

?>
