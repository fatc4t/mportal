<?php
/******************************************************************************-*
  '*
  '*  ユーザー名  :
  '*
  '*  システム名  :MPORTAL
  '*
  '*  処理名      :送信処理-発注データ（KICS版）
  '*
  '*  処理概要    :
  '*
  '*  開発言語    :PHP
  '*
  '*  作成日      :2021/02/20
  '*  作成者      :バッタライ
  '*
  '*------------------------------------------------------------------------------
  '*  修正履歴
  '*  修正日      修正者  修正内容
  '*
  '*
  '******************************************************************************/

try {
    
    require_once("create_engine_as400_order_Function.php");
    
    //日本の時間を設定
    date_default_timezone_set('Asia/Tokyo');
    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');
    // 発注データ集計フォーマット
    $format = "KICS";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path     = "/var/www/as_400/log/".$logname.'.log';
    //$log_path     = "E:/My Desktop/as_400/log/".$logname.'.log';
    // 空白
    $space = str_pad('',5);

    // 処理開始ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "開始" . "\n", 3, $log_path);

    //**************************************************
    //
    // 機能     :発注データレコード生成
    //
    // 機能説明 :
    //
    // 備考     :as_ダウンサイジング
    //
    //**************************************************

    function CreateOrderRec_std($IN_aryDataBuf, &$OUT_strOutBuf) {
        $strMe         = 'CreateOrderRec_std';
        $OUT_strOutBuf = '';
        $strRecLine    = '';
        $intPos        = 0;
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
                //
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

    }

    //**************************************************
    //
    // 機能     :EOS発注使用しているスキーマのみ取得 
    //
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************

    $sql_schema = "";
    $sql_schema .= "SELECT                          ";
    $sql_schema .= "     COMPANY_CODE               ";
    $sql_schema .= "FROM PUBLIC.M_COMPANY_CONTRACT  ";
    $sql_schema .= "WHERE EOS_KBN = '1'             ";

    $rows_schemas = getList($sql_schema);

    // 企業名初期化
    $schema = "";
    
    // 企業ループ開始
    foreach ($rows_schemas as $schemas) {
        // DBスキーマ名
        $schema = $schemas['company_code'];
        
        $arySupp_Cd = array();

        // 対象データの取得SQL
        $sql = "";
        $sql .= " select ";
        $sql .= "        TRN1601.HIDESEQ             as HIDESEQ                    "; /* レコード番号 */
        $sql .= "       ,TRN1601.contract_month      as contract_month             "; /* 限月*/
        $sql .= "       ,TRN1601.ORDER_KBN           as ORDER_KBN                  "; /* 発注形態区分 */
        $sql .= "       ,TRN1601.VAN_DATA_KBN        as VAN_DATA_KBN               "; /* VANデータ作成区分*/
        $sql .= "       ,TRN1601.CONSPROC_KBN        as CONSPROC_KBN               "; /* 集約処理区分*/
        $sql .= "       ,TRN1601.ORGANIZATION_ID     as ORGANIZATION_ID            "; /* mportal店舗コード */
        $sql .= "       ,TRN1601.SUPP_CD             as SUPP_CD                    "; /* 仕入先コード*/
        $sql .= "       ,TRN1601.ORD_DATE            as ORD_DATE                   "; /* 発注日*/
        $sql .= "       ,TRN1601.ARR_DATE            as ARR_DATE                   "; /* 入荷予定日*/
        $sql .= "       ,TRN1602.PROD_CD             as PROD_CD                    "; /* 商品コード*/
        $sql .= "       ,TRN1602.ORD_AMOUNT          as ORD_AMOUNT                 "; /* 発注数量*/
        $sql .= "       ,TRN1602.COSTPRICE           as COSTPRICE                  "; /* 商品原価*/
        $sql .= "       ,TRN1602.SALEPRICE           as SALEPRICE                  "; /* 商品売価*/
        $sql .= "       ,TRN1602.LINE_NO             as LINE_NO                    "; /* 行番号*/
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')      as PROD_KN      "; /* 商品容量カナ */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_KN_RK,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')   as PROD_KN_RK   "; /* 商品カナ略 */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_CAPA_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as PROD_CAPA_KN "; /* 商品容量カナ */
        $sql .= "       ,MST0201.PRIV_CLASS_CD       as CLASS_CD                   "; /* 自社分類コード */
        $sql .= "       ,MST0201.BASE_SALEPRICE      as BASE_SALEPRICE             "; /* 基準売価 */
        $sql .= "       ,MST0201.SECT_CD             as SECT_CD                    "; /* 部門コード */
        $sql .= "       ,M_CHOALL.ONLINE_CD          as ONLINE_CD                  "; /* オンラインコード */
        $sql .= "       ,M_CHOALL.BASE_FIRM_CUST_CD  as BASE_FIRM_CUST_CD          "; /* AS店舗コード */
        $sql .= "       ,M_CHOALL.DISTRICT_CODE      as DISTRICT_CODE              "; /* 地区コード */
        $sql .= "       ,M_CHOALL.SEND_COST_KBN      as SEND_COST_KBN              "; /* 原価送信区分 */
        $sql .= "       ,M_CHOALL.SEND_SALE_KBN      as SEND_SALE_KBN              "; /* 売価送信区分 */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.BASE_FIRM_CUST_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as BASE_FIRM_CUST_KN "; /* AS店舗名 */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.ONLINE_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as ONLINE_KN                 "; /* 取引先名 */
        $sql .= "       ,0                           as EOS_SEQ                    ";
        $sql .= " from " . $schema . ".TRN1601                                                                   ";
        $sql .= "   inner join " . $schema . ".TRN1602                                                           ";
        $sql .= "   on (TRN1602.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and TRN1602.HIDESEQ = TRN1601.HIDESEQ) ";
        $sql .= "   left join " . $schema . ".MST0201                                                            ";
        $sql .= "   on (MST0201.ORGANIZATION_ID = TRN1602.ORGANIZATION_ID and MST0201.PROD_CD = TRN1602.PROD_CD) ";
        $sql .= "   left join " . $schema . ".MST1101                                                            ";
        $sql .= "   on (MST1101.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and MST1101.SUPP_CD = TRN1601.SUPP_CD) ";
        $sql .= "   left join " . $schema . ".MST0010                                                            ";
        $sql .= "   on(TRN1601.ORGANIZATION_ID  = MST0010.ORGANIZATION_ID)                                       ";
        $sql .= "   left join PUBLIC.M_CHOALL                                                                    ";
        $sql .= "   on (M_CHOALL.SUPP_CD = TRN1601.SUPP_CD and M_CHOALL.BASE_FIRM_CUST_CD = MST0010.FIRM_CUST_CD)";
        $sql .= "where TRN1601.ORDER_KBN             = '1' ";// EOS発注
        $sql .= "   and   TRN1601.VAN_DATA_KBN       = '1' ";// 送信済
        $sql .= "   and   TRN1601.CONNECT_KBN        = '0' ";// 未送信
        $sql .= "   and   TRN1601.CONSPROC_KBN       = '1' ";// 集約済データ
        $sql .= "   and   TRN1602.SUPP_STATE_KBN    <> '0' ";// 未確定データ以外を対象す
        $sql .= "   and   M_CHOALL.ORDER_FORMAT_TYPE = '3' ";// 発注データ形式 = :KICS/（標準）版
        $sql .= "order by  ";
        $sql .= "        TRN1601.ORGANIZATION_ID  ";
        $sql .= "        ,TRN1601.ORD_DATE        ";
        $sql .= "        ,TRN1601.HIDESEQ         ";
        $sql .= "        ,TRN1601.ARR_DATE        ";
        $sql .= "        ,TRN1602.PROD_CD         ";

        // 発注データ取得SQL実行
        $rows_import = getList($sql);

        // 対象のデータがない場合、ログ出力、
        if (count($rows_import) <= 0) {

            error_log($currentdatetime . $space .
                    $format . $space . $schema .
                    $space . "VAN発注データが存在しません" . "\n", 3, $log_path);
            continue;
            
        }

        /* データ存在チェック */
        if ($rows_import) {
            // オンラインコード初期化
            $strOnline_Cd             = "";
            // オンライン名初期化
            $strOnline_Kn             = "";
            // 店舗コード初期化
            $strBase_Firm_Cust_cd     = "";
            // 前処理店舗コード
            $strBef_Base_Firm_Cust_cd = "";
            // 前処理オンラインコード初期化
            $strBef_Online_Cd         = "";
            // 処理明細数
            $intProcDetailCnt         = 0;
            // 曜日マスタ初期化
            $m_youbi                  = "";
            // 発注日初期化
            $ord_date                 = "";
            // 納品日
            $arr_date                 = "";
            // レコード番号
            $hideseq                  = "";
            // 行番号
            $line_no                  = "";
            // MPORTAL店舗ID
            $org_id                   = "";
            // 発注-原価送信対象区分初期化
            $strcost_send_kbn         = "";
            // 発注-売価送信取扱区分
            $strsale_send_kbn         = "";
            // 発注-HT発注区分
            $strdata_set_kbn          = "";
            // 原価送信区分
            $strSend_cost_kbn         = "";
            // 売価送信区分
            $str_send_sale_kbn        = "";
            // データ格納配列初期化
            $aryDataBuf               = array();
            /* ループ開始 */
            foreach ($rows_import as $row_import) {
                // 店舗コード(6)
                $strBase_Firm_Cust_Cd   = db2str($row_import, 'base_firm_cust_cd', 6);
                // 店舗名(20)
                $strBase_Firm_Cust_Nm   = db2str($row_import, 'base_firm_cust_kn', 20);
                // POS仕入先コード(4)
                $strSupp_Cd             = db2str($row_import, 'supp_cd', 4);
                // 取引先コード(6)
                $strOnline_Cd           = trim(db2str($row_import, 'online_cd', 8));
                // 取引先名(20)
                $strOnline_Kn           = trim(db2str($row_import, 'online_kn', 20));
                // 直送区分
                $strDIRECT_DELIVERY_KBN = db2str($row_import, 'direct_delivery_kbn', 1);
                // 発注日
                $ord_date               = db2str($row_import, 'ord_date', 8);
                // 納品日
                $arr_date               = db2str($row_import, 'arr_date', 8);
                // レコード番号
                $hideseq                = $row_import['hideseq'];
                // 行番号
                $line_no                = $row_import['line_no'];
                // MPORTAL店舗ID
                $org_id                 = $row_import['organization_id'];
                // システム詳細マスタ取得
                $m_system               = get_m_system($schema, $org_id);
                // 原価送信区分
                $strSend_cost_kbn       = $row_import['send_cost_kbn'];
                // 売価送信区分
                $str_send_sale_kbn      = $row_import['send_sale_kbn'];
                // 商品原価初期化
                $strCostprice           = 0;
                // 商品売価初期化
                $strSaleprice           = 0;
                // システム詳細マスタループ開始
                foreach ($m_system as $sys) {
                    //　データ設定区分
                    if ($sys['detail_cd'] === '107100040S') {
                        $strdata_set_kbn = $sys['strvalue'];
                    }
                    // データ設定区分1の場合設定する
                    if ($strdata_set_kbn === '1') {
                        // 原価送信対象区分
                        if ($sys['detail_cd'] === '107100010S') {
                            $strcost_send_kbn = $sys['strvalue'];
                        }
                        // 原価送信区分
                        if ($strcost_send_kbn === '1') {
                            // 送信する
                            if ($row_import['costprice'] === 0) {
                                // 0の場合6桁0詰める
                                $strCostprice = str_pad('000000', 6);
                            } else {

                                $strCostprice = sprintf("%06d", intval($row_import['costprice']));
                            }
                        } else {
                            //送信しない
                            $strCostprice = str_pad('000000', 6);
                        }
                        // 売価送信取扱区分
                        if ($sys['detail_cd'] === '107100020S') {
                            $strsale_send_kbn = $sys['strvalue'];
                        }
                        // 売価送信区分
                        if ($strsale_send_kbn === '0') {
                            // 送信しない
                            $strSaleprice = str_pad('', 6);
                        } else if ($strsale_send_kbn === '1') {
                            //　送信する：商品売価
                            if ($row_import['saleprice'] === 0) {
                                //0の場合はブランク
                                $strSaleprice = str_pad('', 6);
                            } else {
                                // 入力ありの場合はゼロ拡張
                                $strSaleprice = sprintf("%06d", intval($row_import['saleprice']));
                            }
                        } else if ($strsale_send_kbn === '2') {
                            //送信する:基準売価→商品売価
                            if ($row_import['base_saleprice'] === 0) {
                                //0の場合はブランク
                                $strSaleprice = str_pad('', 6);
                            } else {
                                // 入力ありの場合はゼロ拡張
                                $strSaleprice = sprintf("%06d", intval($row_import['base_saleprice']));
                            }
                        } else {
                            // 送信しない
                            $strSaleprice = str_pad('', 6);
                        }
                    } else {
                        // 帳合先マスタ原価送信区分
                        if ($strSend_cost_kbn === '1') {
                            // 送信する
                            if ($row_import['costprice'] === 0) {
                                // 0の場合6桁0詰める
                                $strCostprice = str_pad('000000', 6);
                            } else {
                                // 入力ありの場合はゼロ拡張
                                $strCostprice = sprintf("%06d", intval($row_import['costprice']));
                            }
                        } else {
                            // 送信しない
                            $strCostprice = str_pad('000000', 6);
                        }
                        // 帳合先マスタ売価送信区分
                        if ($str_send_sale_kbn === '1') {
                            // 送信する
                            if ($row_import['saleprice'] === 0) {
                                // 0の場合はブランク
                                $strSaleprice = str_pad('', 6);
                            } else {
                                // 入力ありの場合はゼロ拡張
                                $strSaleprice = sprintf("%06d", intval($row_import['saleprice']));
                            }
                        } else {
                            //　送信しない
                            $strSaleprice = str_pad('', 6);
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

                // 条件オンラインコードが変わった時、またはループ初回処理時
                // または小売店コードが変わった時
                // 納品日が変わった時 add millionet oota 20210929
                // または6明細に達した時（どうやら1伝票につき最大6明細の模様）

                if ($strOnline_Cd !== $strBef_Online_Cd ||
                        $strBase_Firm_Cust_Cd !== $strBef_Base_Firm_Cust_cd ||
                        // add millionet oota 20210929 START
                        $strBef_arr_date      !== $arr_date                 ||
                        // add millionet oota 20210929 END
                        $intProcDetailCnt     >= 6
                        
                ) {
                    
                    // 前処理オンラインコードあり＝出力データあり
                    if ($strBef_Online_Cd !== '') {
                        // 発注データレコード生成
                        CreateOrderRec_std($aryDataBuf, $strOutBuf);
                        // 発注データファイル出力
                        PutFileOrderDat($strBef_Online_Cd, $strOutBuf);

                        // 発注データ変換した仕入先コードを保持(メール送信するため)
                        if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                            $arySupp_Cd[] = $strBef_Online_Cd;
                        }
                        // 明細行番号初期化
                        $intLineno = 0;
                    }
                    // データ格納配列初期化
                    $aryDataBuf = array();
                    // 出力バッファ初期化
                    $strOutBuf  = '';
                    // ヘッダ-レコード
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

                    // 小売店コード(6)/POSの取引先コード
                    $strOutBuf .= $strBase_Firm_Cust_Cd;
                    // 予備(2)※'[０４：１号機,０５：２号機]のいずれかの値が入る(KICS任意項目として使用)
                    $strOutBuf .= sprintf("%02d", intval($row_import['district_code']));
                    // 分類コード(2)※構成01～05//現在使用されてない為、スペース
                    $strOutBuf .= str_pad('', 2);
                    // 予備(2)スペース
                    $strOutBuf .= str_pad('', 2);
                    // 限(2)　使用されてない為　（00）
                    $strOutBuf .= str_pad('00', 2);
                    // 発注日(6)
                    if(strlen($ord_date) == 8){
                        $strOutBuf .= substr($ord_date, 2, 6);
                    }else{
                        $strOutBuf .= str_pad($ord_date, 6);
                    }
                    // 納品日(6)                    
                    if(strlen($arr_date) == 8){
                        $strOutBuf .= substr($arr_date, 2, 6);
                    }else{
                        $strOutBuf .= str_pad($arr_date, 6);
                    }
                    // 仕入先コード(6)
                    $strOutBuf .= $strOnline_Cd;
                    // 予備(2)
                    $strOutBuf .= str_pad('', 2);
                    // 便区分(1)
                    $strOutBuf .= str_pad('', 1);
                    // 予備(81)
                    $strOutBuf .= str_pad('', 81);
                    // ヘッダバッファ設定
                    $aryDataBuf[] = $strOutBuf;
                    // 処理明細数クリア
                    $intProcDetailCnt = 0;
                    // 伝票番号を更新
                    update_m_denno($strBase_Firm_Cust_Cd, $strSeq);

                }

                // 出力バッファ初期化
                $strOutBuf = '';
                // レコード区分(1)
                $strOutBuf .= 'D';
                // データ区分(2)
                $strOutBuf .= '11';
                // 商品コード(13)
                $strOutBuf .= db2str($row_import, 'prod_cd', 13);
                // 発注数(4)
                $ord_amount  = $row_import['ord_amount'];             
                $strOutBuf  .= sprintf("%04d", intval($ord_amount));
                // 商品名カナ(15)
                if ($row_import['prod_kn'] === '') {
                    // 商品カナ略
                    $strOutBuf .= db2str($row_import, 'prod_kn_rk', 15);
                } else {
                    // 商品カナ
                    $strOutBuf .= db2str($row_import, 'prod_kn', 15);
                }

                // 予備(4)
                $strOutBuf .= str_pad('', 4);
                // 規格容量(6)
                $strOutBuf .= db2str($row_import, 'prod_capa_kn', 6);
                // 仕入単価(6)
                $strOutBuf .= $strCostprice;
                // 直送区分(1)
                $strOutBuf .= str_pad('', 1);
                // 売価 (6)
                $strOutBuf .= $strSaleprice;
                // 予備(6)
                $strOutBuf .= str_pad('', 6);
                // 明細バッファ設定
                $aryDataBuf[] = $strOutBuf;
                // 処理明細数インクリメント
                $intProcDetailCnt ++;
                // オンラインコードを保持
                $strBef_Online_Cd = $strOnline_Cd;
                // 店舗コードを保持
                $strBef_Base_Firm_Cust_cd = $strBase_Firm_Cust_Cd;
                // add millionet oota 20210929 START
                // 納品日を保持
                $strBef_arr_date  = $arr_date;
                // add millionet oota 20210929 END

                // 発注明細を更新
                update_trn1602($schema,$hideseq,$org_id,$line_no,$intProcDetailCnt,
                              $strSeq,$ord_amount);
                // 発注伝票を更新
                update_trn1601($schema, $hideseq, $org_id, $strSeq);

            }

            // ループ終了
            //前処理KICS仕入先コードあり＝出力データあり
            if ($strBef_Online_Cd !== '') {
                //発注データレコード生成
                CreateOrderRec_std($aryDataBuf, $strOutBuf);
                //発注データファイル出力
                PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
            }

            //発注データ変換した仕入先コードを保持(メール送信するため)
            if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                $arySupp_Cd[] = $strBef_Online_Cd;
            }

        }

    }

} catch (Exception $ex) {
    // 処理中にエラーが発生した場合、ログファイル出力
    error_log($currentdatetime . $space . 
              $format . $space . 
              "処理中にエラーが発生しました" . "\n"."エラー内容：".$ex, 3, $log_path);
}
    // 処理完了ログ出力
    error_log($currentdatetime . $space .
            $format . $space .
            "完了" . "\n", 3, $log_path);
    
    error_log('END', 3, '/var/www/as_400/order/KICS.dat');

    @ob_flush();
    @flush();
?>
