<?php

    /**
     * @file      お知らせ情報取得用WebApi
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      お知らせ情報を取得する
     */

    // DB情報を読み込み
    require_once("./common/DBAccess_Function2.php");

    //POSTからパラメータを取得
    $json_string = isset( $_POST['params'] )       === true ? $_POST['params']    : '';
    $json_obj = json_decode($json_string, true);


    //顧客IDを取得
    $cust_cd = $json_obj[data][0][rows][0][0][cust_cd];

    $cust_cd = $_GET['cust_cd'];
        if(!$cust_cd){$cust_cd = '2000120000018';}

    //お知らせ情報を読込み
    $sql  = "SELECT * FROM mst_notice where id in (select notice_id from notice_customer where customer_id = (select id from mst_customer where customer_jan_code = '";
    $sql .= $cust_cd;
    $sql .= "' )) and auto_push_flg = '0'";

    $companyContractList = getList($sql);

    foreach($companyContractList as $data){

        $aryAddRow = array(
            'notice_id'    => $data['id'],
            'notice_title' => $data['title'],
            'notice_info'  => $data['content'],
        );

        $json_array[] = $aryAddRow;
    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
