<?php
        // SELECT文実行用
	function getList($sql)
	{
                try {
                    $list = array();

                    // DB接続情報
                    $dsn = "pgsql:dbname=m_portal host=133.242.228.107 port=5432";
                    $username = "postgres";
//                    $password = "postgres";
                    $password = "uBRagUjZwi";
                    $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
                
                    // データベース接続
                    $pdo = new PDO($dsn, $username, $password,$driver_options);

                    } catch (Exception $e) {

                        // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
                        // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
                        // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
                        header('Content-Type: text/plain; charset=UTF-8', true, 500);
                        return $list; 
                    }

                    $result = $pdo->query($sql);
                    
                    while ($row = $result->fetch()) {
                        array_push($list, $row);
                    }
                    
                    // データベース切断
                    $pdo = null;
                    
                    return $list;
        }

        // INSERT,UPDATE,DELETE実行用
	function sqlExec($sql)
	{
                try {
                    $count = 0;

                    // DB接続情報
                    $dsn = "pgsql:dbname=m_portal host=133.242.228.107 port=5432";
                    $username = "postgres";
//                    $password = "postgres";
                    $password = "uBRagUjZwi";
                    $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
                
                    // データベース接続
                    $pdo = new PDO($dsn, $username, $password,$driver_options);

                    } catch (Exception $e) {

                        // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
                        // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
                        // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
                        header('Content-Type: text/plain; charset=UTF-8', true, 500);
                        return $list; 
                    }

                    $count = $pdo->exec($sql);                    
                    
                    // データベース切断
                    $pdo = null;
                    
                    return $count;
        }
        
//文字列変換
//$chg:"to_db":DBへ書き込む時, "to_dsp":画面に表示する時
//$cr:変換する文字列
function char_chg($chg, $cr)
{
	$cr_after = "";

	switch ($chg)
	{
		case "to_db":
			$cr_after = mb_convert_encoding($cr,"UTF-8","SJIS"); 
			break;
		case "to_dsp":
			$cr_after = mb_convert_encoding($cr,"SJIS","UTF-8"); 
			break;
		case "to_utf":
			$cr_after = mb_convert_encoding($cr,"UTF-8","UTF-8"); 
			break;
		default:
			$cr_after = "no change"; 
			break;
	}

	return $cr_after;
}

//POSコードからorg_idを取得
function f_get_pos_org_code($comp_id, $pos_code)
{
	$org_id = "";
	$sql = "select org_id from t_org where comp_id = '".$comp_id."' and pos_code = '".$pos_code."'";
	getList($sql);
	if($rows){
		$row = mysql_fetch_array($result);
		$org_id = $row["org_id"]; 
	}

	mysql_free_result($result);

	return $org_id;
}

//指定日後の年月日を取得
function computeDate($year, $month, $day, $addDays) {

    	$baseSec = mktime(0, 0, 0, $month, $day, $year);//基準日を秒で取得
    	$addSec = $addDays * 86400;//日数×１日の秒数
    	$targetSec = $baseSec + $addSec;

	$year_r = date("Y", $targetSec);
	$month_r = date("m", $targetSec);
	$day_r = date("d", $targetSec);

    return array ($year_r, $month_r, $day_r);
}

?>
