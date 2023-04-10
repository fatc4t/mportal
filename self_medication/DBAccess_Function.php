<?php
/* * ****************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :DBアクセス共通関数
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/11/08
  '*  作成者      :
  '*
  '*----------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日      修正者  修正内容
  '*
  '*
  '******************************************************************************/

//**************************************************************************
// 機能　　　：SELECT文実行用
//
// 引数　　　：SQL文
//
// 備考　　　：
//
//**************************************************************************
function getList($sql) {
    try {
        $list = array();
        // DB接続情報

        $dsn = "pgsql:dbname=m_portal host=133.242.228.107 port=5432";
        $username = "postgres";
        $password = "uBRagUjZwi";
        
//        $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
//        $username = "postgres";
//        $password = "masterkey";
        
        $driver_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false,];
        // データベース接続
        $pdo = new PDO($dsn, $username, $password, $driver_options);
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
        // DB接続情報
        $dsn = "pgsql:dbname=m_portal host=133.242.228.107 port=5432";
        $username = "postgres";
        $password = "uBRagUjZwi";
        
//        $dsn = "pgsql:dbname=m_portal_test host=133.242.50.64 port=5432";
//        $username = "postgres";
//        $password = "masterkey";
        
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
//**************************************************************************
// 機能　　　：追加商品取得
//
// 引数　　　：なし
//
// 備考　　　：
//
//**************************************************************************
function get_add_prod_cd(){

    $sql_select   = "";
    $sql_select  .= " select prod_cd ";
    $sql_select  .= " from public.m_selfmedi ";
    $sql_select  .= " where kubun = '0' ";
    $sql_select  .= " order by prod_cd ";

    $result = getList($sql_select);
    
    foreach($result as $val){
        $rows_add_jan .= "'".$val['prod_cd']."'".',';
    }
  
    return substr($rows_add_jan,0,-1);
}

//**************************************************************************
// 機能　　　：削除商品取得
//
// 引数　　　：なし
//
// 備考　　　：
//
//**************************************************************************
function get_del_prod_cd(){

    $sql_select   = "";
    $sql_select  .= " select prod_cd ";
    $sql_select  .= " from public.m_selfmedi ";
    $sql_select  .= " where kubun = '1' ";
    $sql_select  .= " order by prod_cd ";

    $result = getList($sql_select);
    
    foreach($result as $val){
        $rows_del_jan .= "'".$val['prod_cd']."'".',';
    }
    
    return substr($rows_del_jan,0,-1);
}

//**************************************************************************
// 機能　　　：商品マスタが存在するスキーマを取得
//
// 引数　　　：なし
//
// 備考　　　：
//
//**************************************************************************
function get_schema(){

    $sql_schema  = " SELECT ";
    $sql_schema .= " schemaname ";
    $sql_schema .= " FROM pg_tables  ";
    $sql_schema .= " where tablename = 'mst0201' ";
    $sql_schema .= " order by schemaname  ";

    $result = getList($sql_schema);
    
    return $result;
}
