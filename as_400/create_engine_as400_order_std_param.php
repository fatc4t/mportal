<?php

    //**************************************************

    // 機能     :VAN発注データ変換ファイル出力

    // 機能説明 :

    // 備考     :標準フォーマット

    //**************************************************
try {
    require_once("create_engine_as400_order_Function.php");
    
    // 取得パラメータを設定する oota add 2021/06/25
    $runVendors     = $_GET['runVendors'];      // 実行するベンダーを指定する(未指定の場合はすべて)  '',''のIN形式 SUPP_CD 指定
    $runSchema      = $_GET['runSchema'];       // 実行するスキーマを指定する(未指定の場合はすべて) '',''のIN形式 スキーマ名 指定
    $runStore       = $_GET['runStore'];        // 実行する店舗を指定する(未指定の場合はすべて) '',''のIN形式 ORGANIZATION_ID 指定
    $datePeriodFrom = $_GET['datePeriodFrom'];  // 実行する期間の開始を指定する(未指定の場合は開始期限なし) YY/MM/DD
    $datePeriodTo   = $_GET['datePeriodTo'];   // 実行する期間の終了を指定する(未指定の場合は終了期間なし) YY/MM/DD
    $run_kbn        = $_GET['run_kbn'];         // 再実行フラグ 0:通常実行 1:過去分に対して指定期間のデータをフラグ調整して再実行する

    // 現在の年月日時分秒を取得(スラシュあり)
    $currentdatetime = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate = date('ymdHis');
    // 発注データ集計フォーマット
    $format = "KICS";
    // ログファイル名
    $logname = $format . "_" . $currentdate;
    // ログファイルパス
    $log_path = "/var/www/as_400/log/" . $logname . '.log';
    
    //$log_path     = "E:/My Desktop/as_400/log/".$logname.'.log';
    // 空白
    $space = "　　　";
    // 処理開始ログ出力
    error_log($currentdatetime . $space . $format . $space . "開始" . "\n", 3, $log_path);

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
        $strMe = 'CreateOrderRec_std';
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
                //
            }
            if (strlen($strRecLine) === 256) {
                $OUT_strOutBuf .= $strRecLine;
                      $OUT_strOutBuf .= PHP_EOL;
                $strRecLine = '';
            }
            $intPos ++;
        }
        //明細数が1または3～5の場合は256桁に満たないのでスペース埋め
        if (strlen($strRecLine) > 1 && strlen($strRecLine) < 256) {
            $strRecLine = str_pad($strRecLine, 256);
            $OUT_strOutBuf .= $strRecLine;
                $OUT_strOutBuf .= PHP_EOL;
        }
        return true;
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

    // 実行時間の設定
    $runtime = array("0015","0230","0700","1040","1230","1530","1730","1840");
    
    // 指定範囲分を1日ごとに繰り返す
    $periodFrom = "";
    $periodTo = "";
    
    // 指定期間を1日ごとに実施していく
    
    // ターゲット時間を生成
    $periodFrom = date('Y/m/d H:i',strtotime($datePeriodFrom . "0015"));
    $periodTo = date('Y/m/d H:i',strtotime($datePeriodFrom . "0230"));
            
    // 時間ごとに実行していく
    for($i = 0; $i < 8; $i++){

        if($i == 7 ){
            $periodFrom   = date('Y/m/d H:i',strtotime($datePeriodFrom . $runtime[$i]));
            $periodTo   = date('Y/m/d H:i',strtotime($datePeriodTo . $runtime[$i+1]));
        }else{
            // 時間範囲を決定
            $periodFrom   = date('Y/m/d H:i',strtotime($datePeriodFrom . $runtime[$i]));
            $periodTo   = date('Y/m/d H:i',strtotime($datePeriodFrom . $runtime[$i+1]));
        }

        // 企業ループ開始
        foreach ($rows_schemas as $schemas) {
            // DBスキーマ名
            $schema = $schemas['company_code'];

            // パラメータに指定されたスキーマの分だけ実行する 2021/06/25 oota add
            if($runSchema == null || strpos($schema, $runSchema) !== false) {

                // 再実行フラグに合わせて実行前に対象データのフラグを変更する
                if($run_kbn = "1"){
                    $query  = "";
                    $query .= " update ".$schema.".TRN1601 set ";
                    $query .= "       VAN_DATA_KBN   = '1' ";  // VANデータ作成区分をもとに戻す
                    $query .= " where  1 = 1 ";

                    // パラメータに指定されたベンダーの分だけ実行する 2021/06/25 oota add
                    if($runStore !== null){
                        $query .= "   and TRN1601.SUPP_CD in ( " . $runVendors . " )" ; // '',''のIN形式 SUPP_CD 指定
                    }

                    // パラメータに指定された店舗の分だけ実行する 2021/06/25 oota add
                    if($runStore !== null){
                        $query .= "   and TRN1601.ORGANIZATION_ID in ( " . $runStore . " )" ; // '',''のIN形式 ORGANIZATION_ID 指定
                    }

                    // 日付の指定範囲のみ
                    if($periodFrom !== null){
                        $query .= "   and TRN1601.insdatetime >= '" . $periodFrom . "'" ; // YYYYMMDD hh:mm:ss の形式
                    }

                    // 日付の指定範囲のみ
                    if($periodTo !== null){
                        $query .= "   and TRN1601.insdatetime <= '" . $periodTo . "'" ; // YYYYMMDD hh:mm:ss の形式
                    }

                    $result = sqlExec($query);
                }

                $arySupp_Cd = array();

                // 対象データの取得SQL
                $sql = "";
                $sql .= "select ";
                $sql .= "        TRN1601.HIDESEQ             as HIDESEQ                    "; /* レコード番号 */
                $sql .= "       ,TRN1601.contract_month      as contract_month             "; /* 限月 */
                $sql .= "       ,TRN1601.ORDER_KBN           as ORDER_KBN                  "; /* 発注形態区分 */
                $sql .= "       ,TRN1601.VAN_DATA_KBN        as VAN_DATA_KBN               "; /* VANデータ作成区分 */
                $sql .= "       ,TRN1601.CONSPROC_KBN        as CONSPROC_KBN               "; /* 集約処理区分 */
                $sql .= "       ,TRN1601.ORGANIZATION_ID     as ORGANIZATION_ID            "; /* mportal店舗コード */
                $sql .= "       ,TRN1601.SUPP_CD             as SUPP_CD                    "; /* 仕入先コード */
                $sql .= "       ,to_char(date(TRN1601.ORD_DATE), 'YYYY/MM/DD') as ORD_DATE "; /* 発注日 */
                $sql .= "       ,to_char(date(TRN1601.ARR_DATE), 'YYYY/MM/DD') as ARR_DATE "; /* 入荷予定日 */
                $sql .= "       ,TRN1602.PROD_CD             as PROD_CD                    "; /* 商品コード */
                $sql .= "       ,TRN1602.ORD_AMOUNT          as ORD_AMOUNT                 "; /* 発注数量 */
                $sql .= "       ,TRN1602.COSTPRICE           as COSTPRICE                  "; /* 商品原価 */
                $sql .= "       ,TRN1602.SALEPRICE           as SALEPRICE                  "; /* 商品売価 */
                $sql .= "       ,TRN1602.LINE_NO             as LINE_NO                    "; /* 行番号 */
                $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')      as PROD_KN     "; /* 商品容量カナ */
                $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_CAPA_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as PROD_CAPA_KN "; /* 商品容量カナ */
                $sql .= "       ,MST0201.PRIV_CLASS_CD       as CLASS_CD                   "; /* 自社分類コード */
                $sql .= "       ,MST0201.SECT_CD             as SECT_CD                    "; /* 部門コード */
                $sql .= "       ,M_CHOALL.ONLINE_CD          as ONLINE_CD                  "; /* オンラインコード */
                $sql .= "       ,M_CHOALL.BASE_FIRM_CUST_CD  as BASE_FIRM_CUST_CD          "; /* AS店舗コード */
                $sql .= "       ,M_CHOALL.DISTRICT_CODE      as DISTRICT_CODE              "; /*  */
                $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.BASE_FIRM_CUST_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as BASE_FIRM_CUST_KN "; /* AS店舗名 */
                $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.ONLINE_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as ONLINE_KN "; /* 取引先名 */
                $sql .= "       ,0                           as EOS_SEQ                    ";
                $sql .= "       ,MST1101.SUPP_DETA_KBN       as SUPP_DETA_KBN              ";
                $sql .= "   from " . $schema . ".TRN1601";
                $sql .= "   inner join " . $schema . ".TRN1602";
                $sql .= "   on (TRN1602.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and TRN1602.HIDESEQ = TRN1601.HIDESEQ)";
                $sql .= "   left join " . $schema . ".MST0201";
                $sql .= "   on (MST0201.ORGANIZATION_ID = TRN1602.ORGANIZATION_ID and MST0201.PROD_CD = TRN1602.PROD_CD)";
                $sql .= "   left join " . $schema . ".MST1101";
                $sql .= "   on (MST1101.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and MST1101.SUPP_CD = TRN1601.SUPP_CD)";                
                $sql .= "   left join " . $schema . ".MST0010";
                $sql .= "   on(TRN1601.ORGANIZATION_ID  = MST0010.ORGANIZATION_ID)";
                $sql .= "   left join PUBLIC.M_CHOALL ";
                $sql .= "   on (M_CHOALL.SUPP_CD = TRN1601.SUPP_CD and M_CHOALL.BASE_FIRM_CUST_CD = MST0010.FIRM_CUST_CD)";
                $sql .= "   where TRN1601.ORDER_KBN          = '1'";      // EOS発注
                $sql .= "   and   TRN1601.VAN_DATA_KBN       = '1'";      // 未送信
                $sql .= "   and   TRN1601.CONSPROC_KBN       = '1'";      // 集約済データ
                $sql .= "   and   M_CHOALL.ORDER_FORMAT_TYPE = '3'";      // 発注データ形式 = :KICS/（標準）版

                // パラメータに指定されたベンダーの分だけ実行する 2021/06/25 oota add
                if($runStore !== null){
                    $sql .= "   and M_CHOALL.SUPP_CD in ( " + $runVendors + " )" ; // '',''のIN形式 SUPP_CD 指定
                }

                // パラメータに指定された店舗の分だけ実行する 2021/06/25 oota add
                if($runStore !== null){
                    $sql .= "   and TRN1601.ORGANIZATION_ID in ( " + $runStore + " )" ; // '',''のIN形式 ORGANIZATION_ID 指定
                }
                
                $sql .= "  order by ";
                $sql .= "         TRN1601.insdatetime ";
                $sql .= "        ,M_CHOALL.ONLINE_CD ";      
                $sql .= "        ,M_CHOALL.BASE_FIRM_CUST_CD  ";
                $sql .= "        ,TRN1602.PROD_CD  ";
//print_r($sql);
                // 発注データ取得SQL実行
                $rows_import = getList($sql);
                // 対象のデータがない場合、ログ出力、
                if (count($rows_import) === 0) {

                    error_log($currentdatetime . $space . 
                              $format . $space . $schema . 
                              $space . "VAN発注データが存在しません" . "\n", 3, $log_path);
                    continue;

                }

                /* データ存在チェック */
                if ($rows_import) {
                    // オンラインコード初期化
                    $strOnline_Cd             = '';
                    // オンライン名初期化
                    $strOnline_Kn             = '';
                    // 店舗コード初期化
                    $strBase_Firm_Cust_cd     = '';
                    // 前処理店舗コード
                    $strBef_Base_Firm_Cust_cd = '';
                    // 前処理オンラインコード初期化
                    $strBef_Online_Cd         = '';
                    // 出力バファ初期化 
                    $strOutBuf                = '';
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
                        $ord_date               = db2str($row_import, 'ord_date', 10);
                        // 納品日
                        $arr_date               = db2str($row_import, 'arr_date', 10);
                        // 発注日を日付型に変換
                        $A_ord_date             = date("y-m-d", strtotime($ord_date));
                        // 納品日を日付型に変換
                        $A_arr_date             = date("y-m-d", strtotime($arr_date));
                        // レコード番号
                        $hideseq                = $row_import['hideseq'];
                        // 行番号
                        $line_no                = $row_import['line_no'];
                        // MPORTAL店舗ID
                        $org_id                 = $row_import['organization_id'];
                        // 曜日マスタを取得
                        $m_youbi                = get_m_youbi($strBase_Firm_Cust_Cd, $strSupp_Cd);
                        // 本日の曜日を取得
                        $weekday                = get_weekday($A_ord_date);

                        if ($m_youbi) {
                            foreach ($m_youbi as $m_you => $val) {
                                // 曜日マスタにデータが存在しない場合調査不要
                                if ($val) {
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
                                        if($val['thu'] == 1){$i = $i + 0; break;}else{$i += 1;}
                                        if($val['fri'] == 1){$i = $i + 0; break;}else{$i += 1;}
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
                                    $A_ord_date = date('Y-m-d', strtotime($A_ord_date . $i . ' day'));
                                    $A_arr_date = date('Y-m-d', strtotime($A_arr_date . $i . ' day'));
                                } else {
                                    $A_ord_date = $A_ord_date;
                                    $A_arr_date = $A_arr_date;
                                }
                            }
                        }

                        // 条件オンラインコードが変わった時ログ出力
                        if ($strOnline_Cd !== $strBef_Online_Cd) {

                            error_log($currentdatetime . $space . 
                                      $format . $space . $strBase_Firm_Cust_Cd . 
                                      $space . $strBase_Firm_Cust_Nm . $space . 
                                      $strOnline_Cd . $space . $strOnline_Kn . 
                                      $space . count($rows_import) . "件" . "\n", 3, $log_path);

                        }

                        // 条件オンラインコードが変わった時、またはループ初回処理時
                        // または6明細に達した時（どうやら1伝票につき最大6明細の模様）
                        // または小売店コードが変わった時
                        //　または部門コードが変わった時
                        if ($strOnline_Cd         !== $strBef_Online_Cd          ||
                            $strBase_Firm_Cust_Cd !== $strBef_Base_Firm_Cust_cd  ||
                            $intProcDetailCnt >= 6
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
                            $strOutBuf = '';
                            // ヘッダ-レコード
                            // レコード区分(1)
                            $strOutBuf .= 'B';
                            // データ区分(2)
                            $strOutBuf .= '11';
                            // 伝票番号(9)
                            $strSeq     = get_m_denno($strBase_Firm_Cust_Cd);
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
                            if ($A_ord_date) {
                                $order_date = str_replace("-", "", $A_ord_date);
                            }
                            $strOutBuf .= str_pad($order_date, 6);
                            // 納品日(6)
                            if ($A_arr_date) {
                                $del_date = str_replace("-", "", $A_arr_date);
                            }
                            $strOutBuf .= str_pad($del_date, 6);
                            // 仕入先コード(6)
                            $strOutBuf .= $strOnline_Cd;
                            // 予備(2)
                            $strOutBuf .= str_pad('', 2);
                            // 便区分(1)
                            $strOutBuf .= str_pad('', 1);
                            // 予備(81)
                            $strOutBuf .= str_pad('', 81);
                            // ヘッダバッファ設定
                            $aryDataBuf[]     = $strOutBuf;
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
                        $strOutBuf .= sprintf("%04d", intval($row_import['ord_amount']));
                        // 商品名カナ(15)
                        $strOutBuf .= db2str($row_import, 'prod_kn', 15);
                        // 予備(4)
                        $strOutBuf .= str_pad('', 4);
                        // 規格容量(6)
                        $strOutBuf .= db2str($row_import, 'prod_capa_kn', 6);
                        //仕入先マスタ　→　仕入明細入力区分で仕入単価・売価表示0：無し/1：あり
                        $supp_deta_kbn = db2str($row_import, 'supp_deta_kbn', 1);
                        if($supp_deta_kbn === '1'){
                            $strTmp = sprintf("%06d", intval($row_import['costprice']));
                        }else{
                            $strTmp = str_pad('000000', 6);
                        }
                        // 仕入単価(6)
                        $strOutBuf .= $strTmp;
                        // 直送区分(1)
                        $strOutBuf .= str_pad('', 1);
                        // 売価(6)
                        if($supp_deta_kbn === '1'){
                            $strTmp = sprintf("%06d", intval($row_import['saleprice']));
                        }else{
                            $strTmp = str_pad('', 6);
                        }
                       // $strOutBuf .= str_pad('', 6);
                        $strOutBuf .= $strTmp;
                        // 予備(6)
                        $strOutBuf .= str_pad('', 6);
                        // 明細バッファ設定
                        $aryDataBuf[]             = $strOutBuf;
                        // 処理明細数インクリメント
                        $intProcDetailCnt ++;
                        // オンラインコードを保持
                        $strBef_Online_Cd         = $strOnline_Cd;
                        // 店舗コードを保持
                        $strBef_Base_Firm_Cust_cd = $strBase_Firm_Cust_Cd;
                        // 部門コードを保持
                        $strBef_sect_cd           = $str_sect_cd;

                        // 発注明細を更新
                        update_trn1602($schema,$hideseq,$org_id,$line_no,$intProcDetailCnt,$strSeq);
                        // 発注伝票を更新
                        update_trn1601($schema, $hideseq, $org_id, $strSeq);
                        // ループ終了
                    }
                    //前処理KICS仕入先コードあり＝出力データあり
                    if ($strBef_Online_Cd !== '') {
                        //発注データレコード生成
                        CreateOrderRec_std($aryDataBuf, $strOutBuf);
                        //発注データファイル出力
                        PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
                    }
                    //--------------------------------------
                    //発注データ変換した仕入先コードを保持(メール送信するため)
                    if (in_array($strBef_Online_Cd, $arySupp_Cd, true) === false) {
                        $arySupp_Cd[] = $strBef_Online_Cd;
                    }
                }
            }
        }
    }
    // 処理完了ログ出力
    error_log($currentdatetime . $space . $format . $space . "完了" . "\n", 3, $log_path);
} catch (Exception $ex) {
    //
}
@ob_flush();
@flush();
?>
