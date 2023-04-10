<?php
/* * *****************************************************************************
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :送信処理-発注データ（SGM版）
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/11/25
  '*  作成者      :バッタライ
  '*
  '*----------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日       修正者         修正内容
  '*
  '***************************************************************************** */

try {

    require_once("create_engine_as400_order_Function.php");
    //日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');
    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');
    // 発注データ集計フォーマット
    $format = "SGM";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path = "/var/www/as_400/log/" . $logname . '.log';
    // 商品売価0の場合のログパス設定
    $log_path_baika = "/var/www/as_400/log/baika/". $currentdate."baika_0_.log";
    // 空白
    $space = str_pad('', 5);
    // 処理開始ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "開始" . "\n", 3, $log_path);

    //**************************************************************************
    //
    // 機能     :発注データレコード生成
    //
    // 引数　　 :$IN_aryDataBuf、$OUT_strOutBuf
    //
    // 備考     :
    //
    //**************************************************************************

    function CreateOrderRec_sgm($IN_aryDataBuf, &$OUT_strOutBuf) {

        $strMe = 'CreateOrderRec_sgm';

        $OUT_strOutBuf = '';
        $strRecLine = '';
        $intPos = 0;

        foreach ($IN_aryDataBuf as $strDataRow) {
            if (substr($strDataRow, 0, 1) === 'B') {
                //伝票ヘッダーレコード
                $strRecLine .= $strDataRow;
            } else if (substr($strDataRow, 0, 1) === 'D') {
                //伝票明細レコード
                if (($intPos % 2) === 0) {
                    // 偶数行の明細はレコード頭の"D11"を省く
                    $strRecLine .= substr($strDataRow, 3);
                    // ("D11"を省いた分)末尾に予備(3)を付加する
                    $strRecLine .= str_pad('', 3);
                } else {
                    $strRecLine .= $strDataRow;
                }
            } else {
                //NOP
            }
            if (strlen($strRecLine) === 256) {
                $OUT_strOutBuf .= $strRecLine;
                $strRecLine = '';
            }
            $intPos ++;
        }
        //明細数が1または3～5の場合は256桁に満たないのでスペース埋め
        if (strlen($strRecLine) > 1 && strlen($strRecLine) < 256) {
            $strRecLine = str_pad($strRecLine, 256);
            $OUT_strOutBuf .= $strRecLine;
        }

        return true;
    }

    $order_shop = "";
    // 本日を取得
    $date = date('Y-m-d');
    // 1日前の日付に変換する
    $date_today = date('Y-m-d', strtotime($date . '-1 day'));
    // 本日の曜日を取得
    $Dt_today  = date("D", strtotime($date . '-1 day'));
    // 発注日マスタより当日発注可能な店舗を取得
    $mht_youbis = get_mht_youbi($Dt_today);
    // 当日発注できる店舗コードでループを回る

    foreach ($mht_youbis as $mht_youbi) {
        // 当日発注可能な店舗
        $order_shop = $mht_youbi['base_firm_cust_cd'];
        //**************************************************************************
        //
        // 機能     :VAN発注送信対象データ取得
        //
        // 引数　　 :店舗コード
        //
        // 備考     :
        //
        //**************************************************************************

        $arySupp_Cd = array();
        /* 企業名固定シグマ */
        $schema = "shiguma";

        $sql = "";
        $sql .= "select ";
        $sql .= "        TRN1601.HIDESEQ             as HIDESEQ           "; // レコード番号
        $sql .= "       ,TRN1601.ORDER_KBN           as ORDER_KBN         "; // 発注形態区分
        $sql .= "       ,TRN1601.VAN_DATA_KBN        as VAN_DATA_KBN      "; // VANデータ作成区分
        $sql .= "       ,TRN1601.CONSPROC_KBN        as CONSPROC_KBN      "; // 集約処理区分
        $sql .= "       ,TRN1601.ORGANIZATION_ID     as ORGANIZATION_ID   "; // 組織ID
        $sql .= "       ,TRN1601.SUPP_CD             as SUPP_CD           "; // POS仕入先コード
        $sql .= "       ,to_char(date(TRN1601.ORD_DATE), 'YYYY/MM/DD') as ORD_DATE "; // 発注日
        $sql .= "       ,to_char(date(TRN1601.ARR_DATE), 'YYYY/MM/DD') as ARR_DATE "; // 入荷予定日
        $sql .= "       ,TRN1602.PROD_CD             as PROD_CD           "; // 商品コード
        $sql .= "       ,CASE                                             ";
        $sql .= "           WHEN TRN1602.ORD_AMOUNT = NULL                "; // 発注数量が空白の場合に0を取得
        $sql .= "               THEN 0                                    ";
        $sql .= "                   ELSE TRN1602.ORD_AMOUNT               "; // 空白でない場合そのまま発注数量
        $sql .= "               END                  as ORD_AMOUNT        ";
        $sql .= "        ,CASE                                            ";
        $sql .= "             WHEN MST0201.ORDER_LOT  = NULL              "; // 発注ロットが空白の場合0を取得す
        $sql .= "                THEN 0                                   ";
        $sql .= "                    ELSE MST0201.ORDER_LOT               "; // 発注ロット空白出ない場合はそのまま 
        $sql .= "                END                 as ORDER_LOT         ";
        $sql .= "       ,TRN1602.COSTPRICE           as COSTPRICE         "; // TRN原価
        $sql .= "       ,TRN1602.SALEPRICE           as SALEPRICE         "; // TRN売価
        // EDTSTR bhattarai 2021/11/11 商品マスタの原価と売価を取得
        $sql .= "       ,MST0201.HEAD_COSTPRICE      as HEAD_COSTPRICE    "; // 商品マスタ原価
        $sql .= "       ,MST0201.SALEPRICE           as HEAD_SALEPRICE    "; // 商品マスタ売価
        // EDTEND bhattarai 2021/11/11 商品マスタの原価と売価を取得
        $sql .= "       ,TRN1602.LINE_NO             as LINE_NO           "; // 行番号
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')      as PROD_KN      "; // 商品名カナ
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_CAPA_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as PROD_CAPA_KN "; // 商品容量名
        $sql .= "       ,CASE                                             ";
        $sql .= "            WHEN M_CH_BMN.AGG_CD = ''                    "; // 変更前の分類コード
        $sql .= "               THEN '03'                                 "; // 空白の場合03にする
        $sql .= "            ELSE M_CH_BMN.AGG_CD                         "; // 空白でない場合そのまま仕様
        $sql .= "            END AS CLASS_CD                              ";
        $sql .= "       , MST0201.RESAlE_KBN                              "; // 商品マスタを参照する
        $sql .= "               AS DIRECT_KBN                             "; // TRN1602を採用する  
        $sql .= "       ,M_CHOALL.ONLINE_CD          as ONLINE_CD         "; // 卸コード
        $sql .= "       ,M_CHOALL.BASE_FIRM_CUST_CD  as BASE_FIRM_CUST_CD "; // KICS店舗コード
        $sql .= "       ,M_CHOALL.DISTRICT_CODE      as DISTRICT_CODE     "; // 地区/県コード
        $sql .= "       ,M_CHOALL.SEND_COST_KBN      as SEND_COST_KBN     "; /* 原価送信区分 */
        $sql .= "       ,M_CHOALL.SEND_SALE_KBN      as SEND_SALE_KBN     "; /* 売価送信区分 */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.BASE_FIRM_CUST_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as BASE_FIRM_CUST_KN "; // KICS店舗名カナ
        $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.ONLINE_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')         as ONLINE_KN         "; // 卸コードカナ
        $sql .= "       ,0                           as EOS_SEQ           "; // EOSsequence番号を0取得
        $sql .= "from " . $schema . ".TRN1601                             ";
        $sql .= "   inner join " . $schema . ".TRN1602                    ";
        $sql .= "       on (TRN1602.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and TRN1602.HIDESEQ = TRN1601.HIDESEQ) ";
        $sql .= "   left join " . $schema . ".MST1101                                                                ";
        $sql .= "       on (MST1101.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and MST1101.SUPP_CD = TRN1601.SUPP_CD) ";
        $sql .= "   left join " . $schema . ".MST0201                                                                ";
        $sql .= "       on (MST0201.ORGANIZATION_ID = TRN1602.ORGANIZATION_ID and MST0201.PROD_CD = TRN1602.PROD_CD) ";
        $sql .= "   left join " . $schema . ".MST0010                                                                ";
        $sql .= "       on (MST0010.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID)                                       ";
        $sql .= "   left join PUBLIC.M_CHOALL                                                                        ";
        $sql .= "       on (M_CHOALL.SUPP_CD = TRN1601.SUPP_CD and M_CHOALL.BASE_FIRM_CUST_CD = MST0010.FIRM_CUST_CD)";
        $sql .= "   left join PUBLIC.M_CH_BMN              ";
        $sql .= "       on (M_CH_BMN.BMN_CD = MST0201.SECT_CD) ";
        $sql .= "where TRN1601.ORDER_KBN           = '1'                  "; // EOS発注
        $sql .= "   and TRN1601.CONNECT_KBN        = '0'                  "; // 未送信
        $sql .= "   and TRN1601.VAN_DATA_KBN       = '1'                  "; // POS送信済
        $sql .= "   and TRN1601.CONSPROC_KBN       = '1'                  "; // 集約済データ
        $sql .= "   and TRN1602.SUPP_STATE_KBN     <> '0'                 "; // 未確定データ以外を対象する
        $sql .= "   and M_CHOALL.ORDER_FORMAT_TYPE = '1'                  "; // 発注データ形式 = :シグマ版
        $sql .= "   and MST0010.FIRM_CUST_CD       = " . "'" . $order_shop . "' "; // 発注データ形式 = :シグマ版
        $sql .= "order by                                                 ";
        $sql .= "    ORD_DATE                                             "; // 発注日
        $sql .= "   ,M_CHOALL.ONLINE_CD                                   "; // KICS卸コード
        $sql .= "   ,M_CHOALL.BASE_FIRM_CUST_CD                           "; // KICS店舗コード
        $sql .= "   ,CLASS_CD                                             "; // 分類コード
        $sql .= "   ,TRN1602.PROD_CD                                      "; // 商品コード 
        // 発注データ取得SQL実行
        $rows_import = getList($sql);

        // 対象のデータがない場合、ログ出力し終了
        if (count($rows_import) <= 0) {

            error_log($currentdatetime . $space .
                    $format . $space .
                    $order_shop . $space .
                    "VAN発注データが存在しません" . "\n", 3, $log_path);
            continue;
        }

        /* データ存在チェック */
        if ($rows_import) {
            $strBef_Online_Cd = "";
            // 商品分類コード初期化
            $strProd_Class_Cd = "";
            // 前処理商品分類コード初期化
            $strBef_Prod_Class_Cd = "";
            // 直送区分初期化
            $strBef_Direct_kbn = "";
            // 前処理直送区分初期化
            $strDirect_kbn = "";
            // 特売区分初期化
            $strSale_plan_kbn = "";
            // 前処理特売区分初期化
            $strBef_Sale_plan_kbn = "";
            // オンラインコード初期化
            $strOnline_Cd = "";
            // オンライン名初期化
            $strOnline_Kn = "";
            // 店舗コード初期化
            $strBase_Firm_Cust_cd = "";
            // 前処理店舗コード
            $strBef_Base_Firm_Cust_cd = "";
            // 前処理納品日
            $strBef_arr_date = "";
            // 出力バファ初期化 
            $strOutBuf = "";
            // 処理明細数
            $intProcDetailCnt = 0;
            // 曜日マスタ初期化
            $m_youbi = "";
            // 発注日初期化
            $ord_date = "";
            // 納品日
            $arr_date = "";
            // 計算後の納品日
            $A_arr_date1 = "";
            // レコード番号
            $hideseq = "";
            // 行番号
            $line_no = "";
            // MPORTAL店舗ID
            $org_id = "";
            // 発注-原価送信対象区分初期化
            $strcost_send_kbn = "";
            // 発注-売価送信取扱区分
            $strsale_send_kbn = "";
            // 発注-HT発注区分
            $strdata_set_kbn = "";
            // 原価送信区
            $strSend_cost_kbn = "";
            // 売価送信区分
            $str_send_sale_kbn = "";
            // データ格納配列初期化
            $aryDataBuf = array();
            // 商品コード
            $prod_cd  = "";
            // 伝票番号初期化
            $strSeq  = 0;
            //　組織コード取得
            foreach ($rows_import as $row_import) {
                //　店舗コード(6)
                $strBase_Firm_Cust_Cd = db2str($row_import, 'base_firm_cust_cd', 6);
                // 店舗名(20)
                $strBase_Firm_Cust_Nm = db2str($row_import, 'base_firm_cust_kn', 20);
                //　部門コード(02)
                // EDTSTR 2021/11/11 bhattarai 部門コードが空白の場合は　03　にする
                $strProd_Class_Cd = $row_import['class_cd'];
                // 部門コードが空白の場合は03にする
                if($strProd_Class_Cd == '' || NULL){
                    // 部門コード03
                    $strProd_Class_Cd = '03';
                }
                // EDTEND 2021/11/11 bhattarai 部門コードが空白の場合は　03　にする
                //　直送区分(1)
                $strDirect_kbn = db2str($row_import, 'direct_kbn', 1);
                //　特売区分(1)
                $strSale_plan_kbn = db2str($row_import, 'sale_plan_kbn', 1);
                //　オンラインコード
                $strOnline_Cd = trim(db2str($row_import, 'online_cd', 8));
                // 取引先名(20)
                $strOnline_Kn = trim(db2str($row_import, 'online_kn', 20));
                // 発注日
                $ord_date = db2str($row_import, 'ord_date', 10);
                // 納品日
                $arr_date = db2str($row_import, 'arr_date', 10);
                // 発注日を日付型に変換
                $A_ord_date = date("y-m-d", strtotime($ord_date));
                // 納品日を日付型に変換
                $A_arr_date = date("y-m-d", strtotime($arr_date));
                // 商品コード
                $prod_cd = db2str($row_import, 'prod_cd', 13);
                // 商品原価初期化
                $strCostprice = 0;
                // 商品売価初期化
                $strSaleprice = 0;
                // レコード番号
                $hideseq = $row_import['hideseq'];
                // 行番号
                $line_no = $row_import['line_no'];
                // MPORTAL店舗ID
                $org_id = $row_import['organization_id'];
                // 原価送信区分
                $strSend_cost_kbn = $row_import['send_cost_kbn'];
                // 売価送信区分
                $str_send_sale_kbn = $row_import['send_sale_kbn'];
                // 曜日マスタを取得
                $m_youbi       = get_m_youbi($strBase_Firm_Cust_Cd);
                // 特売マスタを参照
                $m_tokubai     = get_m_tokubai($schema, $prod_cd, $org_id);
                // システム詳細マスタのデータ設定区分を取得
                $strdata_set_kbn = strdata_set_kbn($schema, $org_id);
                // データ設定区分1の場合設定する
                if ($strdata_set_kbn === '1') {
                    // 原価送信対象区分を取得
                    $strcost_send_kbn = strcost_send_kbn($schema, $org_id);
                    // 原価送信区分1の場合
                    if ($strcost_send_kbn === '1') {
                        // 特売マスタが存在しない場合
                        if ($m_tokubai['costprice1'] <= 0) {
                            // TRNデータの原価
                            // TRNデータの原価0の場合
                            if($row_import['costprice'] <= 0){
                                //商品マスタの原価を採用
                                $strCostprice = sprintf("%06d", intval($row_import['head_costprice']));
                            }else{
                                // TRNデータの原価を採用する
                                $strCostprice = sprintf("%06d", intval($row_import['costprice']));
                            }
                        } else {
                            // 特売マスタの原価採用する
                            $strCostprice = sprintf("%06d", intval($m_tokubai['costprice1']));
                        }
                        // 仕入単価が0の場合はその商品がベンダーに届かない様にし、ログファイル出力
                        if($strCostprice <= 0){
                            // ログファイル出力し、つぎへ
                            error_log($currentdatetime . $space .
                                      "商品コード　:".$space.db2str($row_import, 'prod_cd', 13) . "\n", 3, $log_path_baika);
                            continue;
                        }
                        
                    } else {
                        //送信しない
                        $strCostprice = str_pad('000000', 6);
                    }
                    // 売価送信取扱区分を取得
                    $strsale_send_kbn = strsale_send_kbn($schema, $org_id);
                    // 売価送信区分
                    if ($strsale_send_kbn === '0') {
                        // 送信しない
                        $strSaleprice = str_pad('', 6);
                    } else if ($strsale_send_kbn === '1') {
                        //　送信する：商品売価
                        if ($row_import['saleprice'] <= 0) {
                            //0の場合はブランク
                            $strSaleprice = str_pad('', 6);
                        } else {
                            // 入力ありの場合はゼロ拡張
                            $strSaleprice = sprintf("%06d", intval($row_import['saleprice']));
                        }
                    } else if ($strsale_send_kbn === '2') {
                        //送信する:基準売価→商品売価
                        if ($row_import['base_saleprice'] <= 0) {
                            //0の場合はブランク
                            $strSaleprice = str_pad('', 6);
                        } else {
                            // 入力ありの場合はゼロ拡張
                            $strSaleprice = sprintf("%06d", intval($row_import['base_saleprice']));
                        }
                        // EDTSTR bhattarai 2021/11/11 商品マスタの売価を採用する
                    } else if($strSaleprice <= 0){
                        // 商品マスタ売価0
                        if($row_import['head_saleprice'] <= 0){
                            //0の場合はブランク
                            $strSaleprice = str_pad('', 6); 
                        }else{
                            // 商品マスタの売価を採用する
                            $strSaleprice = sprintf("%06d", intval($row_import['head_saleprice']));
                        }
                        // EDTSTR bhattarai 2021/11/11 商品マスタの売価を採用する
                    } else if ($m_tokubai['saleprice1'] >= 0) {
                            // TRNデータの原価を採用する
                           $strSaleprice = sprintf("%06d", intval($m_tokubai['saleprice1']));
                    } else {
                        // 送信しない
                        $strSaleprice = str_pad('', 6);
                    }
                } else {
                    // 帳合先マスタ原価送信区分
                    // 原価送信区分1の場合
                    if ($strcost_send_kbn === '1') {
                        // 特売マスタが存在しない場合
                        if ($m_tokubai['costprice1'] <= 0) {
                            // TRNデータの原価
                            // TRNデータの原価0の場合
                            if($row_import['costprice'] <= 0){
                                //商品マスタの原価を採用
                            }else{
                                // TRNデータの原価を採用する
                                $strCostprice = sprintf("%06d", intval($row_import['costprice']));
                            }
                        } else {
                            // 特売マスタの原価採用する
                            $strCostprice = sprintf("%06d", intval($m_tokubai['costprice1']));
                        }
                        // 仕入単価が0の場合はその商品がベンダーに届かない様にし、ログファイル出力
                        if($strCostprice <= 0){
                            // ログファイル出力し、つぎへ
                            error_log($currentdatetime . $space .
                                      "商品コード　:".$space.db2str($row_import, 'prod_cd', 13) . "\n", 3, $log_path_baika);
                            continue;
                        }
                        
                    } else {
                        //送信しない
                        $strCostprice = str_pad('000000', 6);
                    }
                    // 帳合先マスタ売価送信区分
                    if ($str_send_sale_kbn === '1') {
                        // 送信する
                        // 特売マスタが存在しない場合
                        if ($m_tokubai['saleprice1'] <= 0) {
                            // trn売価を採用する
                            // trn売価が0の場合
                            if ($row_import['saleprice'] <= 0) {
                                //商品マスタの売価を採用する
                                //商品マスタが0の場合
                                if($row_import['head_saleprice'] <= 0){
                                    // 0の場合はブランク
                                    $strSaleprice = str_pad('', 6);
                                }else{
                                    // 商品マスタの売価を採用する
                                    $strSaleprice = sprintf("%06d", intval($row_import['head_saleprice']));
                                }
                            }
                        }else{
                            //特売商品売価を採用する
                            $strSaleprice = sprintf("%06d", intval($m_tokubai['saleprice']));
                        }    
                    } else {
                        //　送信しない
                        $strSaleprice = str_pad('', 6);
                    }
                }
                // 本日を納品日と同じ形にする(yy-mm-dd)
                $today = substr($date_today, 2, 8);
                // 過去の納品日と本日納品日の場合、+2日する
                if($A_arr_date <= $today){
                     $A_arr_date = date('Y-m-d', strtotime($date_today .'+2 day'));
                }
                // 過去の発注日の場合、発注日を本日に変更する
                if($A_ord_date < $today){
                    $A_ord_date = $today;
                }
                // 本日の曜日を取得
                $weekday = get_weekday($A_arr_date);
                // 曜日マスタが存在した場合
                if($m_youbi){
                    foreach($m_youbi as $val){
                        $i = 0;
                        switch($weekday){
                            case "mon":
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}
                            case "tue":
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}
                            case "wed":
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}
                            case "thu":
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}
                            case "fri":
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                            case "sat":
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
                            case "sun":
                                if($val['sun'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['mon'] == 1){$i = $i + 0; break;}else{$i += 1;}  
                                if($val['tue'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['wed'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                if($val['sat'] == 1){$i = $i + 0; break;}else{$i += 1;}
                        }
                        
                        $A_arr_date = date('Y-m-d', strtotime($A_arr_date .$i. ' day'));
                        //　納品日が日曜日の場合
                        if ($week === 'sun') {
                            if ($A['mon'] === 0) {   //Bgroup
                                $A_arr_date = date('Y-m-d', strtotime($A_arr_date . '+2 day'));
                            } else { //Agroup
                                $A_arr_date = date('Y-m-d', strtotime($A_arr_date . '+1 day'));
                            }
                        }
                    }
                }
                // 条件オンラインコードが変わった時ログ出力
                if ($strOnline_Cd !== $strBef_Online_Cd) {

                    error_log($currentdatetime . $space .
                            $format . $space . str_pad($strBase_Firm_Cust_Cd, 6) .
                            $space . str_pad($strBase_Firm_Cust_Nm, 20) . $space .
                            str_pad($strOnline_Cd, 6) . $space . str_pad($strOnline_Kn, 20) .
                            $space . count($rows_import) . "件" . "\n", 3, $log_path);
                }

                //　オンラインコードが変わった時、またはループ初回処理時
                //  または店舗コードが変わった時
                //　または商品分類コードが変わった時
                //　または特売区分が変わった時
                //　または6明細に達した時（どうやら1伝票につき最大6明細の模様）
                if ($strOnline_Cd             !== $strBef_Online_Cd         ||
                        $strBase_Firm_Cust_Cd !== $strBef_Base_Firm_Cust_cd ||
                        $strProd_Class_Cd     !== $strBef_Prod_Class_Cd     ||
                        $strSale_plan_kbn     !== $strBef_Sale_plan_kbn     ||
                        $strDirect_kbn        !== $strBef_Direct_kbn        ||
                        $strBef_arr_date      !== $A_arr_date               ||
                        $intProcDetailCnt     >= 6
                ) {
                    //--------------------------------------
                    //　前処理オンラインコードあり＝出力データあり
                    if ($strBef_Online_Cd !== '') {
                        //　発注データレコード生成
                        CreateOrderRec_sgm($aryDataBuf, $strOutBuf);
                        //　発注データファイル出力
                        PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
                        //　発注データ変換した仕入先コードを保持(メール送信するため)
                        if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                            $arySupp_Cd[] = $strBef_Online_Cd;
                        }
                        //　明細行番号初期化
                        $intLineno = 0;
                    }
                    // データ格納配列初期化
                    $aryDataBuf = array();
                    // 出力バッファ初期化
                    $strOutBuf = '';
                    // 伝票番号初期化
		    $strSeq = 0;
                    // 処理行NO初期化
                    $intLineno = 0;
                    // ヘッダレコード
                    //----------------------------------------------------------
                    // レコード区分(1)
                    $strOutBuf .= 'B';
                    // データ区分(2)
                    $strOutBuf .= '11';
                    // 伝票番号(9)
                    $strSeq = get_m_denno($strBase_Firm_Cust_Cd);

                    if ($strSeq === '') {
                        $strOutBuf .= str_pad('', 9);
                    } else {
                        $strOutBuf .= sprintf('%09s', $strSeq);
                    }
                    // 伝票番号を更新
                    update_m_denno($strBase_Firm_Cust_Cd, $strSeq);

                    // 小売店コード(6)
                    $strOutBuf .= $strBase_Firm_Cust_Cd;
                    // 地区コード(2)※'03','04','05'のいずれかの値が入る(KICS任意項目として使用)
                    $strOutBuf .= sprintf("%02d", intval($row_import['district_code']));
                    // 分類コード(2)※構成01～09
                    $strOutBuf .= sprintf('%02s', $strProd_Class_Cd);
                    // 直送区分(1)※直送時「1」、直送でない場合は(0)
                    $strOutBuf .= $strDirect_kbn;
                    // 特売区分(1)※特売時「1」、特売でない場合は(0)
                    $strOutBuf .= $strSale_plan_kbn;
                    // 限(2)
                    $strOutBuf .= str_pad('00', 2);
                    // 発注日(6)
                    $order_date = str_replace("-", "", $A_ord_date);
                    if (strlen($order_date) == 8) {
                        $strOutBuf .= substr($order_date, 2, 6);
                    } else {
                        $strOutBuf .= substr($order_date, 0, 6);
                    }
                    // 納品日(6)
                    $del_date = str_replace("-", "", $A_arr_date);
                    if (strlen($del_date) == 8) {
                        $strOutBuf .= substr($del_date, 2, 6);
                    } else {
                        $strOutBuf .= substr($del_date, 0, 6);
                    }
                    // 仕入先コード(8)
                    $strOutBuf .= sprintf('%-8s', $strOnline_Cd);
                    // 便区分(1)
                    $strOutBuf .= str_pad('', 1);
                    // 予備(81)
                    $strOutBuf .= str_pad('', 81);
                    // ヘッダバッファ設定
                    $aryDataBuf[] = $strOutBuf;
                    // 処理明細数クリア
                    $intProcDetailCnt = 0;
                }

                //　出力バッファ初期化
                $strOutBuf = '';
                //　レコード区分(1)
                $strOutBuf .= 'D';
                //　データ区分(2)
                $strOutBuf .= '11';
                //　商品コード(13)
                $strOutBuf .= db2str($row_import, 'prod_cd', 13);
                //　発注数(4)
                $ord_amount = $row_import['ord_amount'];

                $strOutBuf .= sprintf("%04d", intval($ord_amount));
                // 商品名カナ(15)
                if ($row_import['prod_kn'] === '') {
                    // 商品カナ略
                    // $strOutBuf .= db2str($row_import, 'prod_kn_rk', 15);
                    // EDTSTR bhattarai 2021/11/10 全角対応 
                    if (mb_strlen($row_import['prod_kn_rk'],"UTF-8") == mb_strwidth($row_import['prod_kn_rk'],"UTF-8")) {
                            $strOutBuf .= db2str($row_import, 'prod_kn_rk', 15);
                    } else {
                            $strOutBuf .= str_pad(' ', 15);
                    }

                } else {
                    // 商品カナ
                    // $strOutBuf .= db2str($row_import, 'prod_kn', 15);
                    if (mb_strlen($row_import['prod_kn'],"UTF-8") == mb_strwidth($row_import['prod_kn'],"UTF-8")) {
                            $strOutBuf .= db2str($row_import, 'prod_kn', 15);
                    } else {
                            $strOutBuf .= str_pad(' ', 15);
                    }
                }
                //　予備(4)
                $strOutBuf .= str_pad('', 4);
                // 規格容量(6)
                // EDTSTR bhattarai 2021/11/10 全角対応
                // $strOutBuf .= db2str($row_import, 'prod_capa_kn', 6);
                if (mb_strlen($row_import['prod_capa_kn'],"UTF-8") == mb_strwidth($row_import['prod_capa_kn'],"UTF-8")) {
                        $strOutBuf .= db2str($row_import, 'prod_capa_kn', 6);
                } else {
                        $strOutBuf .= str_pad(' ', 15);
                }
                // 仕入単価(6)
                $strOutBuf .= sprintf('%06d', $strCostprice);
                // 予備(1)
                $strOutBuf .= str_pad('', 1);
                // 売価(6)
                $strOutBuf .= sprintf('%06d', $strSaleprice);
                // 行番号(2)
                $intLineno = (( ++$intLineno) % 100);
                if ($intLineno === 0) {
                    $intLineno ++;
                }
                $strOutBuf .= sprintf('%02d', $intLineno);
                // 予備(4)
                $strOutBuf .= str_pad('', 4);
                // 明細バッファ設定
                $aryDataBuf[] = $strOutBuf;
                // 処理明細数インクリメント
                $intProcDetailCnt ++;
                // オンラインコードを保持
                $strBef_Online_Cd = $strOnline_Cd;
                // 店舗コードを保持
                $strBef_Base_Firm_Cust_cd = $strBase_Firm_Cust_Cd;
                // 商品分類コードを保持
                $strBef_Prod_Class_Cd = $strProd_Class_Cd;
                // 直送区分を保持
                $strBef_Direct_kbn = $strDirect_kbn;
                // 特売区分を保持
                $strBef_Sale_plan_kbn = $strSale_plan_kbn;
                // 納品日を保持
                $strBef_arr_date  = $A_arr_date;
                // 発注明細を更新
                update_trn1602($schema,$hideseq,$org_id,$line_no,$intProcDetailCnt,
                                  $strSeq,$ord_amount);
                // 発注伝票を更新
                update_trn1601($schema,$hideseq,$org_id,$strSeq,$del_date);
                // ループ終了
            }

            //　ループ脱出後
            //　----------------------------------------------------------------
            //　前処理KICS仕入先コードあり＝出力データあり
            if ($strBef_Online_Cd !== '') {
                //　発注データレコード生成
                CreateOrderRec_sgm($aryDataBuf, $strOutBuf);
                //　発注データファイル出力
                PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
            }
            //------------------------------------------------------------------
            //　発注データ変換した仕入先コードを保持(メール送信するため)
            if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                $arySupp_Cd[] = $strBef_Online_Cd;
            }
        }
    }
} catch (Exception $ex) {
    // 処理中にエラーが発生した場合はログファイル出力し、終了
    error_log($currentdatetime . $space .
            $format . $space .
            "処理中にエラーが発生しました" . "\n" . "エラー内容：" . $ex, 3, $log_path);
}
// 処理完了ログ出力
error_log($currentdatetime . $space .
        $format . $space .
        "完了" . "\n", 3, $log_path);

error_log('END', 3, '/var/www/as_400/order/SGM_MRNG.dat');

@ob_flush();
@flush();
?>
