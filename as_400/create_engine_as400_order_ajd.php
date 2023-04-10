<?php
/*******************************************************************************
'*
'*  ユーザー名  :
'*
'*  システム名  :MPORTAL
'*
'*  処理名      :送信処理-発注データ（AJD版）
'*
'*  処理概要    :
'*
'*  開発言語    :PHP
'*
'*  作成日      :2021/02/20
'*  作成者      :バッタライ
'*
'*----------------------------------------------------------------------------
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
    $currentdatetime  = date('Y/m/d h:i:s');
    // 現在の年月日時分秒を取得(スラシュ無し)
    $currentdate      = date('ymdHis');
    // 発注データ集計フォーマット
    $format           = "AJD";
    // ログファイル名
    $logname          = $format."_".$currentdate;
    // ログファイルパス
    $log_path = "/var/www/as_400/log/".$logname.'.log';
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
    // 機能説明 :
    //
    // 備考     :
    //
    //**************************************************************************
    function CreateOrderRec_ajd($IN_strOnline_Cd, $IN_aryDataBuf, &$OUT_strOutBuf, 
                                $IN_blnSysFileHeader,$strBef_suppliers) {

        $strMe = 'CreateOrderRec_ajd';

        $OUT_strOutBuf = '';
        $strRecLine    = '';

        //発注データ出力していない
        if ($IN_blnSysFileHeader === false) {
            //システム・ファイル・ヘッダー
            //レコード区分(1)
            $strRecLine .= '%';
            //データ種別(2)
            $strRecLine .= '01';
            //データ処理日付(12)
            //データ作成時のマシン日付、マシン時刻
            $strRecLine .= date('ymdHis');
            //予備(6)
            $strRecLine .= str_pad('', 6);
            //データ送信元(8)
            //ミリオセンターコード「80630000」固定
            $strRecLine .= '80630000';
            //最終送信先(8)
            //取引先コード
            $strRecLine .= $strBef_suppliers;
            //予備(91)
            $strRecLine .= str_pad('', 91);
        }

        $intPos = 0;
        foreach ($IN_aryDataBuf as $strDataRow) {
            if (substr($strDataRow, 0, 1) === 'B') {
                if ($intPos > 0 && strlen($strRecLine) > 0 && strlen($strRecLine) < 256) {
                    $OUT_strOutBuf .= str_pad($strRecLine, 256);
                    $strRecLine = '';
                }
                //伝票ヘッダーレコード
                $strRecLine .= $strDataRow;
            } else if (substr($strDataRow, 0, 1) === 'D') {
                //伝票明細レコード
                $strRecLine .= $strDataRow;
            } else {
                //NOP
            }

            if (strlen($strRecLine) === 256) {
                $OUT_strOutBuf .= $strRecLine;
                $strRecLine = '';
            }
            $intPos ++;
        }
        if (strlen($strRecLine) === 128) {
            $OUT_strOutBuf .= str_pad($strRecLine, 256);
            $strRecLine = '';
        }
        
        if (strlen($strRecLine) > 1 && strlen($strRecLine) < 256) {
            $OUT_strOutBuf .= str_pad($strRecLine, 256);
        }
        return true;
    }

        /*企業名固定ニシザワ*/
        $schema = 'nishizawa';

        //**********************************************************************
        //
        // 機能     :VAN発注データ取得
        //
        // 機能説明 :
        //
        // 備考     :
        //
        //**********************************************************************		
        $arySupp_Cd = array();

        $sql = "";
        $sql .= "select ";
        $sql .= "        TRN1601.HIDESEQ             as HIDESEQ           "; /* レコード番号 */
        $sql .= "       ,TRN1601.ORDER_KBN           as ORDER_KBN         "; /* 発注形態区分 */
        $sql .= "       ,TRN1601.VAN_DATA_KBN        as VAN_DATA_KBN      "; /* VANデータ作成区分 */
        $sql .= "       ,TRN1601.CONSPROC_KBN        as CONSPROC_KBN      "; /* 集約処理区分 */
        $sql .= "       ,TRN1601.ORGANIZATION_ID     as ORGANIZATION_ID   "; /* organization_id */
        $sql .= "       ,TRN1601.SUPP_CD             as SUPP_CD           "; /* 仕入先コード */
        $sql .= "       ,TRN1601.ORD_DATE            as ORD_DATE          "; /* 発注日 */
        $sql .= "       ,TRN1601.ARR_DATE            as ARR_DATE          "; /* 入荷予定日 */
        $sql .= "       ,TRN1602.PROD_CD             as PROD_CD           "; /* 商品コード */
        $sql .= "       ,TRN1602.ORD_AMOUNT          as ORD_AMOUNT        "; /* 発注数量 */
        $sql .= "       ,TRN1602.COSTPRICE           as COSTPRICE         "; /* 商品原価 */
        $sql .= "       ,TRN1602.SALEPRICE           as SALEPRICE         "; /* 商品売価 */
        $sql .= "       ,TRN1602.LINE_NO             as LINE_NO           "; /* 行番号 */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','')      as PROD_KN "; /* 商品容量カナ */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(MST0201.PROD_CAPA_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as PROD_CAPA_KN "; /* 商品容量カナ */
        $sql .= "       ,MST0201.SECT_CD             as SECT_CD           "; /* 部門コード */
        $sql .= "       ,MST1101.KICS_SUPP_CD        as KICS_SUPP_CD      "; /* KICS仕入先コード */
        $sql .= "       ,M_CHOALL.ONLINE_CD          as ONLINE_CD         "; /* オンラインコード */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.ONLINE_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as ONLINE_KN "; /*取引先名*/   
        $sql .= "       ,'040801'                    as BASE_FIRM_CUST_CD "; /* 店舗コード =>固定 */
        $sql .= "       ,M_CHOALL.BASE_FIRM_CUST_CD  as BASE_FIRM_CUST_CDD "; /* 店舗コード伝票番号  */
        $sql .= "       ,regexp_replace (replace(replace(coalesce(M_CHOALL.BASE_FIRM_CUST_KN,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as BASE_FIRM_CUST_KN "; /* 企業名 */
        $sql .= "       ,0                           as EOS_SEQ           "; /**/
        $sql .= "       ,'(ﾕｳ)NPK'                   as COMP_NM           "; /* ※社名=>固定 */
        $sql .= "       ,DEL_CONF_CD                 as SUPPILER          "; /* AJD取引先コード */
        $sql .= "   from " . $schema . ".TRN1601";
        $sql .= "   inner join " . $schema . ".TRN1602 ";
        $sql .= "   on (TRN1602.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and TRN1602.HIDESEQ = TRN1601.HIDESEQ)";
        $sql .= "   left join " . $schema . ".MST1101 ";
        $sql .= "   on (MST1101.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID and MST1101.SUPP_CD = TRN1601.SUPP_CD)";
        $sql .= "   left join " . $schema . ".MST0201 ";
        $sql .= "   on (MST0201.ORGANIZATION_ID = TRN1602.ORGANIZATION_ID and MST0201.PROD_CD = TRN1602.PROD_CD)";
        $sql .= "   left join " . $schema . ".MST0010 ";
        $sql .= "   on (MST0010.ORGANIZATION_ID = TRN1601.ORGANIZATION_ID) ";
        $sql .= "   left join PUBLIC.M_CHOALL ";
        $sql .= "   on (M_CHOALL.SUPP_CD = TRN1601.SUPP_CD and M_CHOALL.BASE_FIRM_CUST_CD = MST0010.FIRM_CUST_CD)";
        $sql .= "   left join PUBLIC.M_CHANGE_SHOP ";
        $sql .= "   on (TRN1601.SUPP_CD = M_CHANGE_SHOP.POS_SUPP_CD and M_CHOALL.ONLINE_CD = M_CHANGE_SHOP.TOT_ORD_CD) ";
        $sql .= "   where TRN1601.ORDER_KBN          = '1' ";// EOS発注
        $sql .= "   and   TRN1601.CONNECT_KBN        = '0' ";// 未送信
        $sql .= "   and   TRN1601.VAN_DATA_KBN       = '1' ";// 未送信
        $sql .= "   and   TRN1601.CONSPROC_KBN       = '1' ";// 集約済データ
        $sql .= "   and   TRN1602.SUPP_STATE_KBN     <> '0'";// 未確定データ以外を対象す
        $sql .= "   and   M_CHOALL.ORDER_FORMAT_TYPE = '2' ";//ajd版
        $sql .= "  order by ";
        $sql .= "   SUPPILER      ";
        $sql .= "  ,TRN1601.ORGANIZATION_ID  ";
        $sql .= "  ,MST0201.SECT_CD          ";
        $sql .= "  ,TRN1601.HIDESEQ          ";
        // 発注データ取得SQL実行
        $rows_import = getList($sql);
        
        // 対象のデータがない場合、ログ出力、
        if (count($rows_import) <= 0) {
            
            error_log($currentdatetime.$space.
                     $format.$space.
                     $schema.$space.
                     "VAN発注データが存在しません"."\n",3,$log_path);
           // exit();
            
        }
        
        /*データ存在チェック*/
        if ($rows_import) {
            // オンラインコード初期化
            $strOnline_Cd                  = '';
            // オンライン名称初期化
            $strOnline_Nm                  = '';
            // 前処理オンラインコード初期化
            $strBef_Online_Cd              = '';
            // 商品部門コード初期化
            $strsect_Cd                    = '';
            // 前処理商品部門コード
            $strBef_Sect_Cd                = '';
            // 前処理取引コード初期化
            $strBef_strsuppliers           = '';
            // データ格納配列初期化
            $aryDataBuf                    = array();
            //
            $strOutBuf                     = '';
            // 処理明細数
            $intProcDetailCnt              = 0;
            // レコード番号
            $hideseq                       = ""; 
            // 行番号   
            $line_no                       = "";
            // MPORTAL店舗ID   
            $org_id                        = "";
            // 取引コード  
            $strsuppliers                  = "";

            /*ループ開始*/
            foreach ($rows_import as $row_import) {
                // 仕入先コード(4)
                $strSupp_Cd                = db2str($row_import, 'supp_cd', 4);
                // オンラインコード（6）
                $strOnline_Cd              = trim(db2str($row_import, 'online_cd', 8));
                // 取引先名(20
                $strOnline_Nm              = trim(db2str($row_import, 'online_kn', 20));
                // 店舗コード(6)
                $strBase_Firm_Cust_Cd      = db2str($row_import, 'base_firm_cust_cdd', 6);
                // 店舗名(20)
                $strBase_Firm_Cust_Nm      = db2str($row_import, 'base_firm_cust_kn', 20);
                // 商品部門コード(4)
                $strsect_cd                = db2str($row_import, 'sect_cd', 2);
                // レコード番(6)
                $hideseq                   = $row_import['hideseq']; 
                // 行番番号(2)
                $line_no                   = $row_import['line_no']; 
                // MPORTAL店舗ID
                $org_id                    = $row_import['organization_id'];
                //　取引先コード
                $strsuppliers              = db2str($row_import, 'suppiler', 8);
                
                // 条件オンラインコードが変わった時ログ出力
                if ($strOnline_Cd !== $strBef_Online_Cd){
                    
                    error_log($currentdatetime . $space . 
                              $format . $space . str_pad($strBase_Firm_Cust_Cd, 6) . 
                              $space . str_pad($strBase_Firm_Cust_Nm, 20) . $space . 
                              str_pad($strOnline_Cd, 6) . $space . str_pad($strOnline_Nm, 20) . 
                              $space . count($rows_import) . "件" . "\n", 3, $log_path);
                }
                
                // オンラインコードが変わった時、
                // またはループ初回処理時
                //　または部門コードが変わった時
                //  取引先コードが変わった時
                if ($strOnline_Cd     !== $strBef_Online_Cd     || 
                        $strsuppliers !== $strBef_strsuppliers  ||
                        $intProcDetailCnt >= 6                  || 
                        $strsect_cd   !== $strBef_sect_cd)
                    {          
                    // 前処理オンラインコードあり＝出力データあり
                    if ($strBef_Online_Cd !== '') {
                        // 発注データ出力済み取引先コードをチェック
                        $blnExists = in_array($strBef_strsuppliers, $arySupp_Cd, true);
                        // 発注データレコード生成
                        CreateOrderRec_ajd($strBef_Online_Cd, $aryDataBuf, $strOutBuf, $blnExists,$strBef_strsuppliers);
                        // 発注データファイル出力
                        PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
                        // 発注データ変換した取引先コードを保持(メール送信するため)
                        if (in_array($strBef_strsuppliers, $arySupp_Cd, true) === false) {
                            $arySupp_Cd[] = $strBef_strsuppliers;
                        }
                        // 明細行番号初期化
                        $intLineno = 0;
                    }
                    // データ格納配列初期化
                    $aryDataBuf       = array();
                    // 出力バッファ初期化
                    $strOutBuf        = '';
                    // ヘッダレコード
                    // レコード区分(1)
                    $strOutBuf       .= 'B';
                    // データ区分(2)
                    $strOutBuf       .= '01';
                    // 伝票番号(9)
                    $strSeq           = get_m_denno($strBase_Firm_Cust_Cd);
                    if ($strSeq === '') {
                        $strOutBuf   .= str_pad('', 9);
                    } else {
                        $strOutBuf   .= sprintf('%09s', $strSeq);
                    }
                    
                    // 小売店コード(8)
                    $strOutBuf       .= sprintf("%08d", intval($row_import['base_firm_cust_cd']));
                    // 予備(6)
                    $strOutBuf       .= str_pad('', 6);
                    // 伝票区分(2)※01固定
                    $strOutBuf       .= '01';
                    // 発注日(6)
                    $strOutBuf       .= substr(db2str($row_import, 'ord_date', 8), 2, 6);
                    // 納品日(6)
                    $strOutBuf       .= substr(db2str($row_import, 'arr_date', 8), 2, 6);
                    // 取引先(8)
                    $strOutBuf       .= $strsuppliers;
                    // 社名
                    $strOutBuf       .= db2str($row_import, 'comp_nm', 20);
                    // 店名
                    $strOutBuf       .= db2str($row_import, 'base_firm_cust_kn', 20);
                    // 取引先名
                    $strOutBuf       .= db2str($row_import, 'online_kn', 20);
                    // E欄(17)
                    $strOutBuf       .= str_pad('EOS', 17);
                    // 便区分(1)
                    $strOutBuf       .= str_pad('', 1);
                    // 予備(2)
                    $strOutBuf       .= str_pad('', 2);
                    //ヘッダバッファ設定
                    $aryDataBuf[]     = $strOutBuf;
                    //処理明細数クリア
                    $intProcDetailCnt = 0;
                    //伝票番号を更新
                    update_m_denno($strBase_Firm_Cust_Cd,$strSeq);                    
                }
                //出力バッファ初期化
                $strOutBuf            = '';
                //明細レコード
                //--------------------------------------------------------------
                //レコード区分(1)
                $strOutBuf           .= 'D';
                //データ区分(2)
                $strOutBuf           .= '01';
                //伝票行番号(2)
                $intProcDetailCnt ++;
                $strOutBuf           .= sprintf('%02d', $intProcDetailCnt);
                
                //商品コード(13)
                if (is_numeric($row_import['prod_cd'])) {
                  //数値化して不足桁数スペースを付加
                  $strProd_cd        = sprintf("%-13.0f", floor($row_import['prod_cd']));
                  $strprod_cdN       = preg_replace('/\s+/', '', $strProd_cd);
                  if(strlen($strprod_cdN) !== 8){
                      // 商品コードが8出ない場合はそのまま使用
                      $strProd_cd        = db2str($row_import, 'prod_cd', 13);
                  }else{
                      $strProd_cd        = sprintf("%-13.0f", floor($row_import['prod_cd']));
                  }
                } else {
                 // そのまま使用   
                  $strProd_cd        = db2str($row_import, 'prod_cd', 13);
                }
                $strOutBuf          .= $strProd_cd;
                //入数(4)
                $strOutBuf          .= sprintf('%04d', 1);
                //発注単位数(4)
                //　発注数(4)
                $ord_amount            = $row_import['ord_amount'];          

                $strOutBuf            .= sprintf("%04d", intval($ord_amount));
                //予備(2)
                $strOutBuf          .= str_pad('', 2);
                //数量(N5V1)
                $dblAmount           = doubleval($row_import['ord_amount']);
                try {
                    $strTmp          = sprintf("%07.1f", $dblAmount);
                    $strTmp          = str_replace('.', '', $strTmp);
                } catch (Exception $e) {
                    $strTmp          = str_pad('', 6);
                }
                $strOutBuf          .= $strTmp;
                //原単価(N7V2)
                $dblCostPrice        = doubleval($row_import['costprice']);
                try {
                    $strTmp          = sprintf('%010.2f', intval($dblCostPrice));
                    $strTmp          = str_replace('.', '', $strTmp);
                } catch (Exception $e) {
                    $strTmp          = str_pad('', 9);
                }
                $strOutBuf          .= $strTmp;
                //売単価(7)
                $dblSalePrice        = doubleval($row_import['saleprice']);
                try {
                    $strTmp          = sprintf('%07d', floor($dblSalePrice));
                } catch (Exception $e) {
                    $strTmp          = str_pad('', 7);
                }
                $strOutBuf          .= $strTmp;
                //原価金額(10)※数量×原単価
                $strOutBuf          .= sprintf('%010d', floor($dblAmount * $dblCostPrice));
                //売価金額(10)※数量×売単価
                $strOutBuf          .= sprintf('%010d', floor($dblAmount * $dblSalePrice));
                //予備１(9)
                $strOutBuf          .= str_pad('', 9);
                // 商品名カナ(15)
                if($row_import['prod_kn'] === ''){
                    // 商品カナ略
                    $strOutBuf      .= db2str($row_import, 'prod_kn_rk', 25);

                }else{
                    // 商品カナ
                    $strOutBuf      .= db2str($row_import, 'prod_kn', 25);

                }
                //予備２(6)
                $strOutBuf          .= str_pad('', 6);
                //引合(2)※部門コード
                $strOutBuf          .= $strsect_cd;
                //予備３(2)
                $strOutBuf          .= str_pad('', 2);
                //予備４(7)
                $strOutBuf          .= str_pad('', 7);
                //予備５(5)
                $strOutBuf          .= str_pad('', 5);
                //予備(2)
                $strOutBuf          .= str_pad(' 1', 2);
                //バッファ設定
                $aryDataBuf[]        = $strOutBuf;
                //オンラインコードを保持
                $strBef_Online_Cd    = $strOnline_Cd;
                //商品部門コードを保持
                $strBef_sect_cd      = $strsect_cd;
                // 取引コードを保持
                $strBef_strsuppliers = $strsuppliers;

                // 発注明細を更新
                update_trn1602($schema,$hideseq,$org_id,$line_no,$intProcDetailCnt,
                               $strSeq,$ord_amount);
                // 発注伝票を更新
                update_trn1601($schema, $hideseq, $org_id, $strSeq);
             
                // ループ終了          
            }
            //ループ脱出後
            //--------------------------------------
            //前処理KICS仕入先コードあり＝出力データあり
            if ($strBef_Online_Cd !== '' || $strBef_strsuppliers !== '') {
                //発注データ変換出力取引先コードをチェック
                $blnExists = in_array($strBef_strsuppliers, $arySupp_Cd, true);
                //発注データレコード生成
                CreateOrderRec_ajd($strBef_Online_Cd, $aryDataBuf, $strOutBuf, $blnExists,$strBef_strsuppliers);
                //発注データファイル出力
                PutFileOrderDat($strBef_Online_Cd, $strOutBuf);
            }
            //--------------------------------------
            //発注データ変換した仕入先コードを保持(メール送信するため)
            if (in_array($strBef_strsuppliers, $arySupp_Cd, true) === false) {
                $arySupp_Cd[] = $strBef_strsuppliers;
            }
        }
} catch (Exception $ex) {
    // 
    error_log($currentdatetime . $space . 
              $format . $space . 
              "処理中にエラーが発生しました" . "\n"."エラー内容：".$ex, 3, $log_path);
    
}
    // 処理完了ログ出力
    error_log($currentdatetime . $space .
              $format . $space .
              "完了" . "\n", 3, $log_path);
    error_log('END', 3, '/var/www/as_400/order/AJD.dat');
    @ob_flush();
    @flush();
?>
