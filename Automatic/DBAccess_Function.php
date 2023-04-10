<?php
    //**************************************************************************
    //
    // 機能     :自動化（DBアクセス）
    //
    // 機能説明 :共通関数
    //
    // 備考     :
    //
    // 作成日　：2022/08/26　　　　　　 作成者　：バッタライ
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
            $dsn = "pgsql:dbname=m_portal host=localhost port=5432";
            $username = "postgres";
            $password = "uBRagUjZwi";

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
    
    //**************************************************************************
    // 機能　　　：INSERT,UPDATE,DELETE実行用
    //
    // 引数　　　：SQL文
    //
    // 備考　　　：
    //
    //**************************************************************************
    function sqlExec($sql) {
        try {
            $count = 0;
            $dsn   = "pgsql:dbname=m_portal host=localhost port=5432";
            $username = "postgres";
            $password = "uBRagUjZwi";
       //     $password = "masterkey";

            $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
            // データベース接続
            $pdo = new PDO($dsn, $username, $password, $driver_options);
            // トランジション開始
            $pdo->beginTransaction();
            $pdo->query("SET NAMES 'utf8'");
            $count = $pdo->exec($sql);
            
        } catch (Exception $e) {
            // エラーが発生した場合は「500 Internal Server Error」でテキストとして表示して終了する
            // - もし手抜きしたくない場合は普通にHTMLの表示を継続する
            // - ここではエラー内容を表示しているが， 実際の商用環境ではログファイルに記録して， Webブラウザには出さないほうが望ましい
            header('Content-Type: text/plain; charset=UTF-8', true, 500);
            $count = $pdo->rollBack();
            echo $e;
            return $count;
        }
        $count = $pdo->commit();
        $pdo = null;
        return $count;
    }
?>
