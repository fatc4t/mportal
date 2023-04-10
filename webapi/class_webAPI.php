<?php

    /**
     * @file      情報取得用WebApi
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      トップメッセージマスタテーブルの管理を行う
     */

    // DB情報を読み込み
    require_once("./common/DBAccess_Function3.php");

    //POSTからパラメータを取得
    $json_string = isset( $_POST['params'] )       === true ? $_POST['params']    : '';
    $json_obj = json_decode($json_string, true);

    //顧客IDを取得
    $cust_cd = $json_obj[data][0][rows][0][0][cust_cd];

    //企業マスタを読込み
    $sql = 'SELECT department_code::int,department_name FROM mandr.m_profit_department order by department_code';
    $companyContractList = getList($sql);

    $sFlg = 0;

    foreach($companyContractList as $data){

        $aryAddRow = array(
            'class_code' => $data['department_code'], // 分類コード
            'class_name' => $data['department_name'], // 部門分類名
        );
        $json_array[]=$aryAddRow;

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
