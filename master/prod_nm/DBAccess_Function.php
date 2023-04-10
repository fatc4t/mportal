<?php
        // SELECT文実行用
	function getList($sql)
	{
                try {
                    $list = array();

                    // DB接続情報
                    $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
                    $username = "postgres";
                    $password = "masterkey";
//                    $password = "1qaz2wsx";
                    $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
                
                    // データベース接続
                    $pdo = new PDO($dsn, $username, $password, $driver_options);
                    $pdo->query("SET NAMES 'utf8'");

/*
                    $stmt = $pdo->query('SET NAMES utf8');
if (!$stmt) {
  $info = $pdo->errorInfo();

  echo $info[2];
}
*/
                    } catch (Exception $e) {

                        // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
                        // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
                        // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
//                        header('Content-Type: text/plain; charset=UTF-8', true, 500);
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
                    $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
                    $username = "postgres";
                    $password = "masterkey";
//                    $password = "1qaz2wsx";
                    $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
                
                    // データベース接続
                    $pdo = new PDO($dsn, $username, $password,$driver_options);

//                    $pdo->query("SET CLIENT_ENCODING TO 'utf8'");
                    $pdo->query("SET NAMES 'utf8'");

                    } catch (Exception $e) {

                        // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
                        // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
                        // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
 //                       header('Content-Type: text/plain; charset=UTF-8', true, 500);
                        return $list; 
                    }

                    $count = $pdo->exec($sql);                    

                    // データベース切断
                    $pdo = null;
                    
                    return $count;
        }

        // INSERT,UPDATE,DELETE実行用
	function sqlExecSeq($sql, $seq)
	{
                try {
                    $count = 0;

                    // DB接続情報
                    $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
                    $username = "postgres";
                    $password = "masterkey";
                    $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
                
                    // データベース接続
                    $pdo = new PDO($dsn, $username, $password,$driver_options);

//                    $pdo->query("SET CLIENT_ENCODING TO 'utf8'");
                    $pdo->query("SET NAMES 'utf8'");

                    } catch (Exception $e) {

                        // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
                        // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
                        // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
      //                  header('Content-Type: text/plain; charset=UTF-8', true, 500);
                        return $list; 
                    }

		    $pdo->beginTransaction();


                    $pdo->exec($sql);                    

                    $count = $pdo->lastInsertId($seq);

                    $pdo->commit();

                    // データベース切断
                    $pdo = null;
                    
                    return $count;
        }

?>
