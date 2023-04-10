<?php

    /**
     * @file      顧客情報取得用WebApi
     * @author    millionet oota
     * @date      2019/10/22
     * @version   1.00
     * @note      顧客情報を取得する
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

    //企業マスタを読込み
    $sql = 'SELECT * FROM mst_customer where customer_jan_code = '.$cust_cd;
    $companyContractList = getList($sql);

    foreach($companyContractList as $data){
        $aryAddRow =  array(
            'first_name'        => $data['first_name'],        // 顧客名.姓
            'first_name_kana'   => $data['first_name_kana'],   // 顧客名.姓カナ
            'last_name'         => $data['last_name'],         // 顧客名.名
            'last_name_kana'    => $data['last_name_kana'],    // 顧客名.名カナ
            'birth_date'        => $data['birth_date'],        // 生年月日
            'sex'               => $data['sex'],               // 性別
            'address'           => $data['address'],           // 住所
            'address2'          => $data['address2'],          // 住所2
            'address_map'       => $data['address_map'],       // 住所図
            'post_code'         => $data['post_code'],         // 郵便番号
            'tel'               => $data['tel'],               // 電話番号
            'mobile'            => $data['mobile'],            // 携帯番号
            'email'             => $data['email'],             // メールアドレス
            'memo'              => $data['memo'],              // メモ
            'referral_person'   => $data['referral_person'],   // 紹介者
            'register_store_id' => $data['register_store_id'], // 登録店舗コード
            'register_staff_id' => $data['register_staff_id'], // 登録スタッフコード
            'prefecture'        => $data['prefecture'],        // 都道府県
            'user_id'           => $data['user_id'],           // ユーザーID
        );

        $json_array[] = $aryAddRow;

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
