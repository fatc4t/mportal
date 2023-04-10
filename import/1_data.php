<?php
// Output a 'waiting message'
//echo 'Please wait while this task completes';
try{
    /**
     * @file      -1
     * @author    office CRM kanderu
     * @date     2021/12/08
     * @version   2.00
     * @note      -1
     */

    //DBアクセス
    require_once("DBAccess_Function.php");
    // ログファイル名
    $currentdate = date('ymdHis');
    $logname =  $currentdate;
    $log_path = "/var/www/mportal/import/log/" . $logname . '.log';

    //商品マスタデータ
    $sql = "SELECT distinct(prod_cd) prod_cd FROM  withs.mst0201 where organization_id  <> -1 order by prod_cd "; 
        
    // SQLの実行
    $result =  getList($sql);
    
    //count
    $arr_length = count($result);
    //商品マスタデータループ
    foreach ($result as $key => $value) {        
    $prod_cd = $value['prod_cd'];
    
    //delete
    $sql1  =""; 
    $sql1 .= " delete from withs.mst0201 where organization_id = -1 and prod_cd = '".$prod_cd."' ";
    // SQLの実行
    $data_del = getList($sql1);
    
    //sql
    $sql  =""; 
    $sql .= " INSERT ";
    $sql .= "INTO withs.mst0201( ";
    $sql .= "insuser_cd             ";                     // 登録者コード
    $sql .= ", insdatetime          ";                     // 登録日時
    $sql .= ", upduser_cd           ";                     // 更新者コード
    $sql .= ", upddatetime          ";                     // 更新日時
    $sql .= ", disabled             ";                     // データ区分
    $sql .= ", lan_kbn              ";                     // LAN区分
    $sql .= ", connect_kbn          ";                     // 送信区分
    $sql .= ", organization_id      ";                     // 組織ID
    $sql .= ", prod_cd              ";                     // 商品コード
    $sql .= ", jan_cd               ";                     // 商品JANコード
    $sql .= ", itf_cd               ";                     // 商品ITFコード
    $sql .= ", prod_nm              ";                     // 商品名
    $sql .= ", prod_kn              ";                     // 商品カナ
    $sql .= ", prod_kn_rk           ";                     // 商品カナ略
    $sql .= ", prod_capa_kn         ";                     // 商品容量カナ
    $sql .= ", prod_capa_nm         ";                     // 商品容量名
    $sql .= ", disp_prod_nm1        ";                     // 表示用品名_上段
    $sql .= ", disp_prod_nm2        ";                     // 表示用品名_下段
    $sql .= ", case_piece           ";                     // ケース入数
    $sql .= ", case_bowl            ";                     // ケース内ボール数
    $sql .= ", bowl_piece           ";                     // ボール入数
    $sql .= ", sect_cd              ";                     // 部門コード
    $sql .= ", priv_class_cd        ";                     // 自社分類コード
    $sql .= ", jicfs_class_cd       ";                     // JICFS分類コード
    $sql .= ", maker_cd             ";                     // メーカーコード
    $sql .= ", head_supp_cd         ";                     // 本部仕入先コード
    $sql .= ", shop_supp_cd         ";                     // 店舗仕入先コード
    $sql .= ", serial_no            ";                     // 商品シリアルNO
    $sql .= ", prod_note            ";                     // 商品適用
    $sql .= ", priv_prod_cd         ";                     // 自社用商品コード
    $sql .= ", alcohol_cd           ";                     // 酒類コード
    $sql .= ", alcohol_capa_amount  ";                     // 酒類内容量
    $sql .= ", order_lot            ";                     // 発注ロット
    $sql .= ", return_lot           ";                     // 返品ロット
    $sql .= ", just_stock_amout     ";                     // 発注点
    $sql .= ", appo_prod_kbn        ";                     // 指定商品区分
    $sql .= ", appo_medi_kbn        ";                     // 指定医薬品区分
    $sql .= ", tax_type             ";                     // 税種別区分
    $sql .= ", order_stop_kbn       ";                     // 発注停止区分
    $sql .= ", noreturn_kbn         ";                     // 返品不可区分
    $sql .= ", point_kbn            ";                     // 得点区分
    $sql .= ", point_magni          ";                     // 得点倍率
    $sql .= ", p_calc_prod_point    ";                     // 付与得点
    $sql .= ", p_calc_prod_money    ";                     // 付与金額
    $sql .= ", auto_order_kbn       ";                     // 自動発注区分
    $sql .= ", risk_type_kbn        ";                     // リスク分類区分
    $sql .= ", order_fin_kbn        ";                     // 発注済区分
    $sql .= ", resale_kbn           ";                     // 再販対象識別区分
    $sql .= ", investiga_kbn        ";                     // 調査種別区分
    $sql .= ", disc_not_kbn         ";                     // 値引不可区分
    $sql .= ", regi_duty_kbn        ";                     // 記帳義務区分
    $sql .= ", set_prod_kbn         ";                     // セット商品区分
    $sql .= ", bundle_kbn           ";                     // バンドル対象区分
    $sql .= ", mixture_kbn          ";                     // ミックスマッチ区分
    $sql .= ", sale_period_kbn      ";                     // 特売期間区分
    $sql .= ", delete_kbn           ";                     // 削除済区分
    $sql .= ", order_can_kbn        ";                     // 発注取消区分
    $sql .= ", order_can_date       ";                     // 発注取消日
    $sql .= ", prod_t_cd1           ";                     // 商品大分類コード
    $sql .= ", prod_t_cd2           ";                     // 商品中分類コード
    $sql .= ", prod_t_cd3           ";                     // 商品小分類コード
    $sql .= ", prod_t_cd4           ";                     // 商品クラスコード
    $sql .= ", prod_k_cd1           ";                     // 商品区分1
    $sql .= ", prod_k_cd2           ";                     // 商品区分2
    $sql .= ", prod_k_cd3           ";                     // 商品区分3
    $sql .= ", prod_k_cd4           ";                     // 商品区分4
    $sql .= ", prod_k_cd5           ";                     // 商品区分5
    $sql .= ", prod_k_cd6           ";                     // 商品区分6
    $sql .= ", prod_k_cd7           ";                     // 商品区分7
    $sql .= ", prod_k_cd8           ";                     // 商品区分8
    $sql .= ", prod_k_cd9           ";                     // 商品区分9
    $sql .= ", prod_k_cd10          ";                     // 商品区分10
    $sql .= ", prod_res_cd1         ";                     // 商品予備コード1
    $sql .= ", prod_res_cd2         ";                     // 商品予備コード2
    $sql .= ", prod_res_cd3         ";                     // 商品予備コード3
    $sql .= ", prod_res_cd4         ";                     // 商品予備コード4
    $sql .= ", prod_res_cd5         ";                     // 商品予備コード5
    $sql .= ", prod_res_cd6         ";                     // 商品予備コード6
    $sql .= ", prod_res_cd7         ";                     // 商品予備コード7
    $sql .= ", prod_res_cd8         ";                     // 商品予備コード8
    $sql .= ", prod_res_cd9         ";                     // 商品予備コード9
    $sql .= ", prod_res_cd10        ";                     // 商品予備コード10
    $sql .= ", prod_comment         ";                     // 商品コメント
    $sql .= ", shelf_block1         ";                     // ブロック1
    $sql .= ", shelf_block2         ";                     // ブロック2
    $sql .= ", fixeprice            ";                     // 商品定価
    $sql .= ", saleprice            ";                     // 商品売価(税込)
    $sql .= ", saleprice_ex         ";                     // 商品売価(税抜)
    $sql .= ", base_saleprice       ";                     // 基準売価(税込)
    $sql .= ", base_saleprice_ex    ";                     // 基準売価(税抜)
    $sql .= ", cust_saleprice       ";                     // 会員売価
    $sql .= ", head_costprice       ";                     // 商品原価
    $sql .= ", shop_costprice       ";                     // 転送価格
    $sql .= ", contract_price       ";                     // 契約単価
    $sql .= ", empl_saleprice       ";                     // 社販売価
    $sql .= ", spcl_saleprice1      ";                     // 特殊売価1
    $sql .= ", spcl_saleprice2      ";                     // 特殊売価2
    $sql .= ", saleprice_limit      ";                     // 金額下限値(税込)
    $sql .= ", saleprice_ex_limit   ";                     // 金額下限値(税抜)
    $sql .= ", time_saleprice       ";                     // タイムセール
    $sql .= ", time_saleamount      ";                     // タイムセール個数設定
    $sql .= ", smp1_str_dt          ";                     // 簡易特売1-開始日
    $sql .= ", smp1_end_dt          ";                     // 簡易特売1-終了日
    $sql .= ", smp1_saleprice       ";                     // 簡易特売1-商品売価
    $sql .= ", smp1_cust_saleprice  ";                     // 簡易特売1-会員売価
    $sql .= ", smp1_costprice       ";                     // 簡易特売1-商品原価
    $sql .= ", smp1_point_kbn       ";                     // 簡易特売1-得点区分
    $sql .= ", smp2_str_dt          ";                     // 簡易特売2-開始日
    $sql .= ", smp2_end_dt          ";                     // 簡易特売2-終了日
    $sql .= ", smp2_saleprice       ";                     // 簡易特売2-商品売価
    $sql .= ", smp2_cust_saleprice  ";                     // 簡易特売2-会員売価
    $sql .= ", smp2_costprice       ";                     // 簡易特売2-商品原価
    $sql .= ", smp2_point_kbn       ";                     // 簡易特売2-得点区分
    $sql .= ", sales_talk_kbn       ";                     // 販売トーク区分
    $sql .= ", sales_talk           ";                     // 販売トーク
    $sql .= ", switch_otc_kbn       ";                     // スイッチOTC薬控除
    $sql .= ", prod_tax             ";                     // 税率
    $sql .= ", bb_date_kbn          ";                     // 賞味期限利用区分
    $sql .= ", cartone              ";                     // 入数
    $sql .= ", bb_days              ";                     // 賞味期限日数
    $sql .= ", order_prod_cd1       ";                     // 発注書印字商品コード1
    $sql .= ", order_prod_cd2       ";                     // 発注書印字商品コード2
    $sql .= ", order_prod_cd3       ";                     // 発注書印字商品コード3
    $sql .= ", case_costprice       ";                     // ケース原価
    $sql .= ", specific_nm";
    $sql .= " ) ";
    $sql .= "VALUES ( ";
    $sql .= "'mportal'"; // 登録者コード
    $sql .= ",now()";                       // 登録日時
    $sql .= ",'mportal'";                  // 更新者コード
    $sql .= ",now()";                    // 更新日時
    $sql .= ",'0'";                     // データ区分
    $sql .= ",'0'";                     // LAN区分
    $sql .= ",'0'";                     // 送信区分    
    $sql .= ",'-1' ";                   // 組織ID
    $sql .= ",'".$value['prod_cd']."'"; // 商品コード
    $sql .= ",'0'";                // 商品JANコード
    $sql .= ",'0'";                // 商品ITFコード
    $sql .= ",'0'";                // 商品名
    $sql .= ",'0'";                // 商品カナ
    $sql .= ",'0'";                // 商品カナ略
    $sql .= ",'0'";                // 商品容量カナ
    $sql .= ",'0'";                // 商品容量名
    $sql .= ",'0'";                // 表示用品名_上段
    $sql .= ",'0'";                // 表示用品名_下段
    $sql .= ",'0'";                // ケース入数
    $sql .= ",'0'";                // ケース内ボール数
    $sql .= ",'0'";                // ボール入数
    $sql .= ",'0'";                // 部門コード
    $sql .= ",'0'";                // 自社分類コード
    $sql .= ",'0'";                // JICFS分類コード
    $sql .= ",'0'";                // メーカーコード
    $sql .= ",'0'";                // 本部仕入先コード
    $sql .= ",'0'";                // 店舗仕入先コード
    $sql .= ",'0'";                // 商品シリアルNO
    $sql .= ",'0'";                // 商品適用
    $sql .= ",'0'";                // 自社用商品コード
    $sql .= ",'0'";                // 酒類コード
    $sql .= ",'0'";                // 酒類内容量
    $sql .= ",'0'";                // 発注ロット
    $sql .= ",'0'";                // 返品ロット
    $sql .= ",'0'";                // 発注点
    $sql .= ",'0'";                // 指定商品区分
    $sql .= ",'0'";                // 指定医薬品区分
    $sql .= ",'0'";                // 税種別区分
    $sql .= ",'0'";                // 発注停止区分
    $sql .= ",'0'";                // 返品不可区分
    $sql .= ",'0'";                // 得点区分
    $sql .= ",'0'";                // 得点倍率
    $sql .= ",'0'";                // 付与得点
    $sql .= ",'0'";                // 付与金額
    $sql .= ",'0'";                // 自動発注区分
    $sql .= ",'0'";                // リスク分類区分
    $sql .= ",'0'";                // 発注済区分
    $sql .= ",'0'";                // 再販対象識別区分
    $sql .= ",'0'";                // 調査種別区分
    $sql .= ",'0'";                // 値引不可区分
    $sql .= ",'0'";                // 記帳義務区分
    $sql .= ",'0'";                // セット商品区分
    $sql .= ",'0'";                // バンドル対象区分
    $sql .= ",'0'";                // ミックスマッチ区分
    $sql .= ",'0'";                // 特売期間区分
    $sql .= ",'0'";                // 削除済区分
    $sql .= ",'0'";                // 発注取消区分
    $sql .= ",'0'";                // 発注取消日
    $sql .= ",'0'";                // 商品大分類コード
    $sql .= ",'0'";                // 商品中分類コード
    $sql .= ",'0'";                // 商品小分類コード
    $sql .= ",'0'";                // 商品クラスコード
    $sql .= ",'0'";                // 商品区分1
    $sql .= ",'0'";                // 商品区分2
    $sql .= ",'0'";                // 商品区分3
    $sql .= ",'0'";                // 商品区分4
    $sql .= ",'0'";                // 商品区分5
    $sql .= ",'0'";                // 商品区分6
    $sql .= ",'0'";                // 商品区分7
    $sql .= ",'0'";                // 商品区分8
    $sql .= ",'0'";                // 商品区分9
    $sql .= ",'0'";                // 商品区分10
    $sql .= ",'0'";                // 商品予備コード1
    $sql .= ",'0'";                // 商品予備コード2
    $sql .= ",'0'";                // 商品予備コード3
    $sql .= ",'0'";                // 商品予備コード4
    $sql .= ",'0'";                // 商品予備コード5
    $sql .= ",'0'";                // 商品予備コード6
    $sql .= ",'0'";                // 商品予備コード7
    $sql .= ",'0'";                // 商品予備コード8
    $sql .= ",'0'";                // 商品予備コード9
    $sql .= ",'0'";                // 商品予備コード10
    $sql .= ",'0'";                // 商品コメント
    $sql .= ",'0'";                // ブロック1
    $sql .= ",'0'";                // ブロック2
    $sql .= ",'0'";                // 商品定価
    $sql .= ",'0'";                // 商品売価(税込)
    $sql .= ",'0'";                // 商品売価(税抜)
    $sql .= ",'0'";                // 基準売価(税込)
    $sql .= ",'0'";                // 基準売価(税抜)
    $sql .= ",'0'";                // 会員売価
    $sql .= ",'0'";                // 商品原価
    $sql .= ",'0'";                // 転送価格
    $sql .= ",'0'";                // 契約単価
    $sql .= ",'0'";                // 社販売価
    $sql .= ",'0'";                // 特殊売価1
    $sql .= ",'0'";                // 特殊売価2
    $sql .= ",'0'";                // 金額下限値(税込)
    $sql .= ",'0'";                // 金額下限値(税抜)
    $sql .= ",'0'";                // タイムセール
    $sql .= ",'0'";                // タイムセール個数設定
    $sql .= ",'0'";                // 簡易特売1-開始日
    $sql .= ",'0'";                // 簡易特売1-終了日
    $sql .= ",'0'";                // 簡易特売1-商品売価
    $sql .= ",'0'";                // 簡易特売1-会員売価
    $sql .= ",'0'";                // 簡易特売1-商品原価
    $sql .= ",'0'";                // 簡易特売1-得点区分
    $sql .= ",'0'";                // 簡易特売2-開始日
    $sql .= ",'0'";                // 簡易特売2-終了日
    $sql .= ",'0'";                // 簡易特売2-商品売価
    $sql .= ",'0'";                // 簡易特売2-会員売価
    $sql .= ",'0'";                // 簡易特売2-商品原価
    $sql .= ",'0'";                // 簡易特売2-得点区分
    $sql .= ",'0'";                // 販売トーク区分
    $sql .= ",'0'";                // 販売トーク
    $sql .= ",'0'";                // スイッチOTC薬控除
    $sql .= ",'0'";                // 税率
    $sql .= ",'0'";                // 賞味期限利用区分
    $sql .= ",'0'";                // 入数
    $sql .= ",'0'";                // 賞味期限日数
    $sql .= ",'0'";                // 発注書印字商品コード1
    $sql .= ",'0'";                // 発注書印字商品コード2
    $sql .= ",'0'";                // 発注書印字商品コード3
    $sql .= ",'0'";                // ケース原価
    $sql .= ",'0'"; 
    $sql .= " )";
    
    // SQLの実行
    $data = getList($sql);
    }
    } catch (Exception $ex) {
    $msg = "※SQLエラー";
    echo   $msg; 
    error_log("処理中にエラーが発生しました" . "\n" . "エラー内容：". "\n".$x . $ex, 3, $log_path);
    exit;
   }    
   $msg = "※完了";
   echo   $msg; 
   echo ($arr_length)."レコード";
   // 処理完了ログ出力
   error_log("完了" . "\n", 3, $log_path,$arr_length."レコード");                     
?>
