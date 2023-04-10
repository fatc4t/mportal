<?php

    /**
     * @file      お店情報取得用WebApi
     * @author    millionet oota
     * @date      2019/10/22
     * @version   1.00
     * @note      お店情報を取得する
     */

    // DB情報を読み込み
    require_once("./common/DBAccess_Function2.php");

    //POSTからパラメータを取得
    $json_string = isset( $_POST['params'] )       === true ? $_POST['params']    : '';
    $json_obj = json_decode($json_string, true);

    //お店IDを取得
//    $store_cd = $json_obj[data][0][rows][0][0][store_cd];

//    $store_cd = $_GET['store_cd'];
//        if(!$store_cd){$store_cd = '2000120000018';}

    //企業マスタを読込み
    $sql = 'SELECT * FROM mst_store';
    $companyContractList = getList($sql);

    foreach($companyContractList as $data){
        $aryAddRow =  array(
            'store_code'                   => $data['store_code'],                   // 店舗コード
            'pos_use'                      => $data['pos_use'],                      // POS使用
            'temporary_stop'               => $data['temporary_stop'],               // 一時停止
            'name'                         => $data['name'],                         // 店舗名
            'name_kana'                    => $data['name_kana'],                    // 店舗名カナ
            'post_code'                    => $data['post_code'],                    // 郵便番号
            'address'                      => $data['address'],                      // 住所
            'directions'                   => $data['directions'],                   // 道案内
            'latitude'                     => $data['latitude'],                     // 緯度
            'longitude'                    => $data['longitude'],                    // 経度
            'website'                      => $data['website'],                      // ホームページ
            'time_open'                    => $data['time_open'],                    // 開店時間
            'time_close'                   => $data['time_close'],                   // 閉店時間
            'regular_holiday'              => $data['regular_holiday'],              // 定休日
            'tel'                          => $data['tel'],                          // 電話番号
            'introduce'                    => $data['introduce'],                    // 店舗紹介
            'image1'                       => $data['image1'],                       // 店舗写真1
            'image2'                       => $data['image2'],                       // 店舗写真2
            'image3'                       => $data['image3'],                       // 店舗写真3
            'image4'                       => $data['image4'],                       // 店舗写真4
            'image5'                       => $data['image5'],                       // 店舗写真5
            'show_flg'                     => $data['show_flg'],                     // 表示
            'reservations_possible_time'   => $data['reservations_possible_time'],   // 予約可能時間
            'cancel_possible_time'         => $data['cancel_possible_time'],         // キャンセル可能時間
            'booking_resources'            => $data['booking_resources'],            // 予約時リソース選択
            'link_title_1'                 => $data['link_title_1'],                 // リンクタイトル1
            'link_url_1'                   => $data['link_url_1'],                   // リンクURL1
            'link_icon_1'                  => $data['link_icon_1'],                  // リンクアイコン1
            'link_title_2'                 => $data['link_title_2'],                 // リンクタイトル2
            'link_url_2'                   => $data['link_url_2'],                   // リンクURL2
            'link_icon_2'                  => $data['link_icon_2'],                  // リンクアイコン2
            'link_title_3'                 => $data['link_title_3'],                 // リンクタイトル3
            'link_url_3'                   => $data['link_url_3'],                   // リンクURL3
            'link_icon_3'                  => $data['link_icon_3'],                  // リンクアイコン3
            'store_item_1_title'           => $data['store_item_1_title'],           // 店舗任意項目・名称1
            'store_item_1'                 => $data['store_item_1'],                 // 店舗任意項目1
            'store_item_2_title'           => $data['store_item_2_title'],           // 店舗任意項目・名称2
            'store_item_2'                 => $data['store_item_2'],                 // 店舗任意項目2
            'store_item_3_title'           => $data['store_item_3_title'],           // 店舗任意項目・名称3
            'store_item_3'                 => $data['store_item_3'],                 // 店舗任意項目3
            'store_item_4_title'           => $data['store_item_4_title'],           // 店舗任意項目・名称4
            'store_item_4'                 => $data['store_item_4'],                 // 店舗任意項目4
            'store_item_5_title'           => $data['store_item_5_title'],           // 店舗任意項目・名称5
            'store_item_5'                 => $data['store_item_5'],                 // 店舗任意項目5
            'store_item_6_title'           => $data['store_item_6_title'],           // 店舗任意項目・名称6
            'store_item_6'                 => $data['store_item_6'],                 // 店舗任意項目6
            'store_item_7_title'           => $data['store_item_7_title'],           // 店舗任意項目・名称7
            'store_item_7'                 => $data['store_item_7'],                 // 店舗任意項目7
            'store_item_8_title'           => $data['store_item_8_title'],           // 店舗任意項目・名称8
            'store_item_8'                 => $data['store_item_8'],                 // 店舗任意項目8
            'store_item_9_title'           => $data['store_item_9_title'],           // 店舗任意項目・名称9
            'store_item_9'                 => $data['store_item_9'],                 // 店舗任意項目9
            'store_item_10_title'          => $data['store_item_10_title'],          // 店舗任意項目・名称10
            'store_item_10'                => $data['store_item_10'],                // 店舗任意項目10
            'staff_item_1'                 => $data['staff_item_1'],                 // スタッフ任意項目1
            'staff_item_2'                 => $data['staff_item_2'],                 // スタッフ任意項目2
            'staff_item_3'                 => $data['staff_item_3'],                 // スタッフ任意項目3
            'staff_item_4'                 => $data['staff_item_4'],                 // スタッフ任意項目4
            'staff_item_5'                 => $data['staff_item_5'],                 // スタッフ任意項目5
            'show_in_booking_flg'          => $data['show_in_booking_flg'],          // 予約利用フラグ
            'show_in_notice_flg'           => $data['show_in_notice_flg'],           // お知らせ利用フラグ
            'show_in_coupon_flg'           => $data['show_in_coupon_flg'],           // クーポンフラグ
            'show_in_product_category_flg' => $data['show_in_product_category_flg'], // 商品カタログフラグ
            'show_in_my_page_flg'          => $data['show_in_my_page_flg'],          // マイページフラグ
            'show_in_staff_flg'            => $data['show_in_staff_flg'],            // スタッフ情報閲覧フラグ
            'app_logo_img'                 => $data['app_logo_img'],                 // 店舗ロゴ(アプリ)
            'receipt_logo_img'             => $data['receipt_logo_img'],             // 店舗ロゴ(レシート)
            'round_down_flg'               => $data['round_down_flg'],               // 端数切捨て
            'round_process_flg'            => $data['round_process_flg'],            // 端数処理
            'gold_ticket_supply_flg'       => $data['gold_ticket_supply_flg'],       // 金券発行
            'gold_ticket_supply_method'    => $data['gold_ticket_supply_method'],    // 発行方法
            'supply_condition_point'       => $data['supply_condition_point'],       // 発行条件ポイント
            'supply_condition_yen'         => $data['supply_condition_yen'],         // 発行条件円
            'expiration_date_flg'          => $data['expiration_date_flg'],          // 有効期限
            'supply_year'                  => $data['supply_year'],                  // 発行日より
            'gold_ticket_image'            => $data['gold_ticket_image'],            // 金券画像
            'gold_ticket_description1'     => $data['gold_ticket_description1'],     // 説明1
            'gold_ticket_description2'     => $data['gold_ticket_description2'],     // 説明2
            'away_dates'                   => $data['away_dates'],                   // 離反日数
            'away_gimmick_dates'           => $data['away_gimmick_dates'],           // 離反しかけ日数
            'company_id'                   => $data['company_id'],                   // 企業コード
            'address_map'                  => $data['address_map'],                  // 地図情報
            'booking_reception'            => $data['booking_reception'],            // 予約受信
        );

        $json_array[] = $aryAddRow;

    }
    # ↑ JSON 形式にする

    header("Content-Type: text/javascript; charset=utf-8");
    # ↑ 半分おまじない。JSONで送りますよという合図

    echo json_encode($json_array);
    # ↑ JSON 形式にエンコードしてechoでPOST送信
?>
