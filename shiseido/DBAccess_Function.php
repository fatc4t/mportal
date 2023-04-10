<?php
    //**************************************************************************
    //
    // 機能     :資生堂ファイル出力DBアクセス
    //
    // 機能説明 :共通関数
    //
    // 備考     :AS400ダウンサイジング
    //
    // 作成日　：2021/03/24　　　　　　 作成者　：バッタライ
    // 
    // 修正日　：　　　　　　　　　　　　修正者　：
    // 　　　　　　　　
    //************************************************************************** 
    
    //**************************************************************************
    // 機能　　　：SELECT文実行用
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function getList($sql){
        try {
            $list = array();
            // DB接続情報
            //本番サーバ-
            $dsn = "pgsql:dbname=m_portal host=133.242.228.107 port=5432";
            $username = "postgres";
            $password = "uBRagUjZwi";
            //テストサーバー
//            $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
//            $username = "postgres";
//            $password = "masterkey";
            $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];                
            // データベース接続
            $pdo = new PDO($dsn, $username, $password,$driver_options);
        } catch (Exception $e) {
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
    function sqlExec($sql){
        try {
            $count = 0;

            // DB接続情報
            //本番サーバ-
            $dsn = "pgsql:dbname=m_portal host=133.242.228.107 port=5432";
            $username = "postgres";
            $password = "uBRagUjZwi";
            //テストサーバー
//            $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
//            $username = "postgres";
//            $password = "masterkey";
            $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];               
            // データベース接続
            $pdo = new PDO($dsn, $username, $password,$driver_options);
            $pdo->query("SET NAMES 'utf8'");
        } catch (Exception $e) {
            // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
            // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
            // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
            return $list; 
        }
        $count = $pdo->exec($sql);
        $pdo = null;
        return $count;
    }
?>
