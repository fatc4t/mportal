<?php

    /**
     * @file      クーポン情報取得用WebApi
     * @author    millionet oota
     * @date      2019/10/22
     * @version   1.00
     * @note      クーポン情報を取得する
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
    $sql  = "select * from coupon";
    $sql .= " where start_date <= now()";
    $sql .= "   and expire_date >= now()";

    $companyContractList = getList($sql);

    foreach($companyContractList as $data){

        $aryAddRow = array(
            'coupon_jan_code'                => $data['coupon_jan_code'],               // クーポンコード
            'display_condition'              => $data['display_condition'],             // 提示条件 01予約時～21受付時＆会計時まで画面リストと連動
            'start_date'                     => $data['start_date'],                    // 表示開始日
            'expire_date'                    => $data['expire_date'],                   // 有効期限
            'expire_auto_set'                => $data['expire_auto_set'],               // 有効期限自動設定フラグ 0:チェックなし, 1 : チェックあり
            'show_coupon'                    => $data['show_coupon'],                   // クーポン表示対象 00:予約サイト・会員アプリ, 01: レシート出力
            'store_id'                       => $data['store_id'],                      // 配信店舗
            'benefits_content'               => $data['benefits_content'],              // 特典内容 00': 値引, '01': 割引, '02': セット価格, '03':食べ飲み放題
            'discount_yen'                   => $data['discount_yen'],                  // 値引き
            'discount_percent'               => $data['discount_percent'],              // 割引
            'discount_price_set'             => $data['discount_price_set'],            // セット価格
            'discount_price_set_tax_type'    => $data['discount_price_set_tax_type'],   // セット価格消費税 00 : 内税 01:外税 02:非課税
            'discount_drink_eat'             => $data['discount_drink_eat'],            // 食べ飲み放題
            'discount_drink_eat_tax_type'    => $data['discount_drink_eat_tax_type'],   // 食べ飲み放題消費税 00 : 内税 01:外税 02:非課税
            'image'                          => $data['image'],                         // 画像
            'grant_point'                    => $data['grant_point'],                   // 付与ポイント
            'title'                          => $data['title'],                         // タイトル
            'title_font_size'                => $data['title_font_size'],               // フォントサイズ 0:大 Large ,1:中 Middle, 2:小 Small
            'title_postion'                  => $data['title_postion'],                 // 表示場所 0:画像上 Before image , 1:画像 下 After image
            'introduction_1'                 => $data['introduction_1'],                // クーポン紹介文1
            'introduction_font_size_1'       => $data['introduction_font_size_1'],      // クーポン紹介文1フォントサイズ
            'introduction_postion_1'         => $data['introduction_postion_1'],        // クーポン紹介文1表示場所
            'introduction_2'                 => $data['introduction_2'],                // クーポン紹介文2
            'introduction_font_size_2'       => $data['introduction_font_size_2'],      // クーポン紹介文2フォントサイズ
            'introduction_postion_2'         => $data['introduction_postion_2'],        // クーポン紹介文2表示場所
            'introduction_3'                 => $data['introduction_3'],                // クーポン紹介文3
            'introduction_font_size_3'       => $data['introduction_font_size_3'],      // クーポン紹介文3フォントサイズ
            'introduction_postion_3'         => $data['introduction_postion_3'],        // クーポン紹介文3表示場所
            'coupon_details_description'     => $data['coupon_details_description'],    // クーポン詳細説明
            'combine_with_other_coupon_flg'  => $data['combine_with_other_coupon_flg'], // 他のクーポンとの併用可
            'display_barcode_flg'            => $data['display_barcode_flg'],           // バーコードの表示
        );

        $json_array[] = $aryAddRow;
    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array, JSON_UNESCAPED_UNICODE);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
