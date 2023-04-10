<?php

    /**
     * @file      トップメッセージマスタ
     * @author    millionet oota
     * @date      2016/01/26
     * @version   1.00
     * @note      トップメッセージマスタテーブルの管理を行う
     */

    // DB情報を読み込み
    require_once("./common/DBAccess_Function.php");

    //企業マスタを読込み
    $sql = 'SELECT * FROM public.m_company_contract WHERE is_del = 0';
    $companyContractList = getList($sql);

    date_default_timezone_set('Asia/Tokyo');
    # ↑ タイムゾーンをセット

    $name = isset($_POST['name']) ? htmlspecialchars($_POST["name"], ENT_QUOTES) :  "no name posted";
    $uni = isset($_POST['uni']) ? htmlspecialchars($_POST["uni"], ENT_QUOTES) : "no uni name posted";
    # ↑ POST送信を処理。三項演算子を用いて、中身が入っていないときは入っていないことを明示的にしている。isset関数は、中身が入っているか判断している

    $json_array = array(
        'time' => date("Y-m-d H:i:s"),
        'name' => $name,
        'university' => $uni,
    );
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>