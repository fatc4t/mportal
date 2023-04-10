<?php
    /**
     * @file      請求書 [M]
     * @author    川橋
     * @date      2020/02/13
     * @version   1.00
     * @note      帳票 - 請求書の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetInvoicing extends BaseModel
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ModelBaseのデストラクタ
            parent::__destruct();
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getFormListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );
            
            //print_r( $sql);
            //print_r($searchArray);
            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getListData");
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            
            //print_r($postArray);
            
            $postArray['wellset_date'] = str_replace('/','',$postArray['wellset_date']);
            $postArray['sumday'] = $postArray['sumday'];

            $sql  = "";
            $sql .= " select ";
            $sql .= "   JSK4150.CUST_CD                                     ";
            $sql .= "  ,MST0101.CUST_NM                                     ";
            $sql .= "  ,MST0101.ZIP                                         ";
            $sql .= "  ,MST0101.ADDR1                                       ";
            $sql .= "  ,MST0101.ADDR2                                       ";
            $sql .= "  ,JSK4150.WELLSET_DATE                                ";
            // 単店指定の場合のみ請求Noが取得出来る
            if ($postArray['org_id'] !== 'false' && $postArray['org_select'] !== 'empty'){
                $sql .= "  ,JSK4150.BILL_NO                                     ";
            }
            $sql .= "  ,sum(JSK4150.BEF_BALANCE)    as BEF_BALANCE          ";
            $sql .= "  ,sum(JSK4150.SALE_TOTAL)     as SALE_TOTAL           ";
            $sql .= "  ,sum(JSK4150.SALE_TAX)       as SALE_TAX             ";
            $sql .= "  ,sum(JSK4150.RECE_TOTAL)     as RECE_TOTAL           ";
            $sql .= "  ,sum(JSK4150.RECE_DISC)      as RECE_DISC            ";
            $sql .= "  ,sum(JSK4150.NOW_BALANCE)    as NOW_BALANCE          ";
            $sql .= "  ,sum(JSK4150.SALE_CNT + JSK4150.RECE_CNT) as DEN_CNT ";
            $sql .= "  ,sum(itax.tax_total_i) as tax_total_i ";
            $sql .= " from JSK4150               ";
            $sql .= " inner join MST0101 on jsk4150.cust_cd = mst0101.cust_cd ";
            $sql .= " left join (select cust_cd, wellset_date, organization_id, sum(tax_total) as tax_total_i from JSK4170 where tax_type = '2' group by cust_cd, wellset_date,organization_id ) as itax ";
            $sql .= "        on jsk4150.cust_cd = itax.cust_cd and jsk4150.wellset_date = itax.wellset_date  and jsk4150.organization_id = itax.organization_id ";
            $sql .= " WHERE jsk4150.WELLSET_DATE = '". $postArray['wellset_date'] ."'";
            if ($postArray['action'] === 'pdfoutput'){
                // 顧客一覧でチェックした顧客
                $sql .= " and jsk4150.cust_cd = '".$postArray['cust_cd']."'";
            }
            else{
                // 顧客指定
                if($postArray['cust_cd'] !== 'false'){
                    if( $postArray['cust_select'] === 'empty'){
                        // 複数選択
                        $sql .= " and jsk4150.cust_cd in (".$postArray['cust_cd'].")";
                    }else{
                        // 単一選択
                        $sql .= " and jsk4150.cust_cd = '".$postArray['cust_select']."'";
                    }
                }
            }
            // 店舗指定
            if($postArray['org_id'] !== 'false'){
                if( $postArray['org_select'] === 'empty'){
                    // 複数選択
                    $sql .= " and jsk4150.organization_id in (".$postArray['org_id'].")";
                }else{
                    // 単一選択
                    $sql .= " and jsk4150.organization_id = ".$postArray['org_select'];
                }
            }

            if ($postArray['action'] !== 'pdfoutput'){
                // 締日指定
                if ($postArray['sumday'] !== ''){
                    $sql .= "   and MST0101.CUST_SUMDAY = '".$postArray['sumday']."'";
                }
            }
            
            $sql .= " group by JSK4150.CUST_CD, MST0101.CUST_NM, JSK4150.WELLSET_DATE ";
            $sql .= "  ,MST0101.ZIP              ";
            $sql .= "  ,MST0101.ADDR1            ";
            $sql .= "  ,MST0101.ADDR2            ";
            // 単店指定の場合のみ請求Noが取得出来る
            if ($postArray['org_id'] !== 'false' && $postArray['org_select'] !== 'empty'){
                $sql .= " , JSK4150.BILL_NO ";
            }
            
            if ($postArray['action'] !== 'pdfoutput'){
                If (isset($postArray['chk_Claimable']) === true && isset($postArray['chk_Purchase_payment']) === true){
                    // どちらの条件にも該当するもののみ
                    $sql .= " having ((sum(JSK4150.SALE_TOTAL) <> 0 or sum(JSK4150.RECE_TOTAL) <> 0 or sum(JSK4150.RECE_DISC) <> 0) Or (sum(JSK4150.NOW_BALANCE) <> 0)) ";
                }
                else if (isset($postArray['chk_Claimable']) === true && isset($postArray['chk_Purchase_payment']) === false){
                    // 請求額があるもののみ
                    $sql .= " having sum(JSK4150.NOW_BALANCE) <> 0 ";
                }
                else if (isset($postArray['chk_Claimable']) === false && isset($postArray['chk_Purchase_payment']) === true){
                    // 買上または入金(入金値引き)があるもののみ
                    $sql .= " having (sum(JSK4150.SALE_TOTAL) <> 0 or sum(JSK4150.RECE_TOTAL) <> 0 or sum(JSK4150.RECE_DISC) <> 0) ";
                }
            }
            $sql .= " order by JSK4150.CUST_CD ";
            
            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 締処理日取得
         * @param    
         * @return   締処理日配列
         */
        public function getWellsetDateList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getWellsetDateList");
            
            // 締処理日検索
            $sql  = "";
            $sql .= " select distinct WELLSET_DATE ";
            $sql .= " from jsk4150 ";
            $sql .= " order by wellset_date desc";
            
            $searchArray = array();     // 検索キー指定なし
            
            $result = $DBA->executeSQL($sql, $searchArray);

            $resultList = array();

            if( $result === false )
            {
                $Log->trace("END getWellsetDateList");
                return $resultList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $resultList, $data['wellset_date']);
            }

            $Log->trace("END getWellsetDateList");
            return $resultList;
        }

        /**
         * システムマスタ取得
         * @param    
         * @return   システムマスタ配列
         */
        public function getSysInfo()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSysInfo");
            
            // システムマスタ検索
            $sql  = "";
            $sql .= " select * ";
            $sql .= " from mst0010";
            $sql .= " where organization_id = -1";
            $sql .= " and   sys_cd = 1";
        
            $searchArray = array();     // 検索キー指定なし
            
            $result = $DBA->executeSQL($sql, $searchArray);

            $resultList = array();

            if( $result === false )
            {
                $Log->trace("END getSysInfo");
                return $resultList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $resultList, $data);
            }

            $Log->trace("END getSysInfo");
            return $resultList[0];
        }
        
        /**
         * 明細情報取得
         * @param    
         * @return   会社情報配列
         */
        public function getDetailDataList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPDFDataList");
            
            // 締処理日
            $postArray['wellset_date'] = str_replace('/','',$postArray['wellset_date']);
            
            // 明細検索
            $sql  = "";
            $sql .= " select * from";
            $sql .= " (select";
            $sql .= "     JSK4160.organization_id";
            $sql .= "    ,JSK4160.TRNDATE";
            $sql .= "    ,JSK4160.TRNTIME";
            $sql .= "    ,JSK4160.HIDESEQ";
            $sql .= "    ,JSK4160.ACCOUNT_NO";
            $sql .= "    ,JSK4160.ACCOUNT_KBN";
            $sql .= "    ,JSK4160.SECT_CD";
            $sql .= "    ,MST1201.SECT_NM";
            $sql .= "    ,MST1201.SECT_KN";
            $sql .= "    ,JSK4160.PROD_CD";
            $sql .= "    ,MST0201.PROD_NM";
            $sql .= "    ,MST0201.PROD_KN";
            $sql .= "    ,JSK4160.TAX_TYPE";
            $sql .= "    ,JSK4160.AMOUNT";
            $sql .= "    ,JSK4160.SALEPRICE";
            $sql .= "    ,JSK4160.DISCTOTAL";
            $sql .= "    ,JSK4160.PURE_TOTAL";
            $sql .= "    ,JSK4160.DEN_PURE_TOTAL";
            $sql .= "    ,JSK4160.DEN_TAX";
            $sql .= "    ,JSK4160.RECE_KBN";
            $sql .= "    ,JSK4160.RECE_TOTAL as  NYUKIN";
            $sql .= "    ,JSK4160.SWITCH_OTC_KBN";
            $sql .= "    ,JSK4160.TAX_RATE";
            $sql .= "    ,case when (JSK4160.DEL_CUST_CD is null or JSK4160.DEL_CUST_CD = '') then '".$postArray['cust_cd']."'";
            $sql .= "          else  JSK4160.DEL_CUST_CD";
            $sql .= "     end as DEL_CUST_CD";
//            $sql .= "    ,JSK4160.SALE_HIDESEQ"; viewpos同様にHIDESEQにしていたが、一高さんからの指摘によりレジの伝票番号に変更 20200313 oota
            $sql .= "    ,JSK4160.reji_no || '-' || JSK4160.account_no as SALE_HIDESEQ";
            $sql .= "    ,MST0201.CASE_PIECE"; 
            $sql .= "    ,trunc(nullif(JSK4160.AMOUNT,0)/ nullif(MST0201.CASE_PIECE,0),0)  as CASES";
            $sql .= "    ,case when MST0201.CASE_PIECE=0 or MST0201.CASE_PIECE is null then JSK4160.AMOUNT";
            $sql .= "          else nullif(JSK4160.AMOUNT, 0) % nullif(MST0201.CASE_PIECE, 0)";
            $sql .= "     end as FRACTION";
            $sql .= "    ,MST0010.shop_cd";
// 返品伝票にある元伝票と比較して同じ締日範囲かを確認
            $sql .= "    ,(select count('x') from trn0101";
            $sql .= "        where hideseq = trn0101J.return_hideseq and reji_no = trn0101J.reji_no and organization_id = trn0101J.organization_id and WELLSET_DATE = trn0101J.WELLSET_DATE";
            $sql .= "     ) as rCount1";
// 元の伝票情報にある返品元と比較して同じ締日範囲かを確認
            $sql .= "    ,(select count('x') from trn0101";
            $sql .= "         where hideseq = trn0101R.hideseq and reji_no = trn0101R.reji_no and organization_id = trn0101R.organization_id and WELLSET_DATE = trn0101R.WELLSET_DATE";
            $sql .= "     ) as rCount2";
            $sql .= "    from JSK4160";
            $sql .= "      left outer join MST0201 on (MST0201.PROD_CD = JSK4160.PROD_CD and MST0201.organization_id = JSK4160.organization_id)";
            $sql .= "      left outer join MST1201 on (MST1201.SECT_CD = JSK4160.SECT_CD and MST1201.organization_id = JSK4160.organization_id)";
            $sql .= "      left outer join MST0101 on (MST0101.CUST_CD = JSK4160.DEL_CUST_CD)";
            $sql .= "      left outer join MST0010 on (MST0010.organization_id = JSK4160.organization_id)";
// 返品が存在する返品伝票と結合
            $sql .= "      left outer join (select * from trn0101 where return_hideseq <> 0) trn0101J";
            $sql .= "                   on (trn0101J.organization_id = JSK4160.organization_id and trn0101J.hideseq = JSK4160.sale_hideseq and trn0101J.reji_no = JSK4160.reji_no  and trn0101J.WELLSET_DATE = JSK4160.WELLSET_DATE)";
// 返品が存在する元の伝票同士を結合
            $sql .= "      left outer join (select * from trn0101 where return_hideseq <> 0) trn0101R";
            $sql .= "                   on (trn0101R.organization_id = JSK4160.organization_id and trn0101R.return_hideseq = JSK4160.sale_hideseq and trn0101R.reji_no = JSK4160.reji_no and trn0101R.WELLSET_DATE = JSK4160.WELLSET_DATE)";
            $sql .= "    where JSK4160.WELLSET_DATE = '".$postArray['wellset_date']."'";
            $sql .= "    and   JSK4160.CUST_CD      = '".$postArray['cust_cd']."'";
            // 店舗指定
            if($postArray['org_id'] !== 'false'){
                if( $postArray['org_select'] === 'empty'){
                    // 複数選択
                    $sql .= " and jsk4160.organization_id in (".$postArray['org_id'].")";
                }else{
                    // 単一選択
                    $sql .= " and jsk4160.organization_id = ".$postArray['org_select'];
                }
            }
            $sql .= "    and   ((JSK4160.account_kbn in ('1','2') and   JSK4160.RECE_TOTAL != 0)";
            $sql .= "            or (JSK4160.account_kbn not in ('1','2') ))";
            $sql .= "    ) as a";
            $sql .= "    where rCount1 = 0";
            $sql .= "    and rCount2 = 0";
            $sql .= "    order by";
            $sql .= "             DEL_CUST_CD";
            $sql .= "            ,TRNDATE";
            $sql .= "            ,TRNTIME";
            $sql .= "            ,SALE_HIDESEQ";
            $sql .= "            ,HIDESEQ";

            $searchArray = array();     // 検索キー指定なし
            
            $result = $DBA->executeSQL($sql, $searchArray);

            $resultList = array();

            if( $result === false )
            {
                $Log->trace("END getDetailDataList");
                return $resultList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $resultList, $data);
            }

            $Log->trace("END getDetailDataList");
            return $resultList;
        }
        
        /**
         * 顧客掛売取引税別明細取得
         * @param    
         * @return   顧客掛売取引税別明細配列
         */
        public function getJSK4170DataList($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getJSK4170DataList");
 
            // 締処理日
            $postArray['wellset_date'] = str_replace('/','',$postArray['wellset_date']);

            // 顧客売掛取引税別明細検索
            $sql  = "";
            $sql .= "  select";
            $sql .= "     TAX_RATE";
            $sql .= "    ,sum(TAX_TOTAL) as SALES_TAX";
            $sql .= "   from JSK4170";
            $sql .= "   where CUST_CD      =  '".$postArray['cust_cd']."'";
            $sql .= "     and WELLSET_DATE =  '".$postArray['wellset_date']."'";
            // 店舗指定
            if($postArray['org_id'] !== 'false'){
                if( $postArray['org_select'] === 'empty'){
                    // 複数選択
                    $sql .= " and organization_id in (".$postArray['org_id'].")";
                }else{
                    // 単一選択
                    $sql .= " and organization_id = ".$postArray['org_select'];
                }
            }
//            $sql .= "     and TAX_TYPE     <> '9'";		// 非課税以外
            $sql .= "     and TAX_TYPE     = '1'";		// 外税だけに変更 20200313 oota
            $sql .= "   group by TAX_RATE";
            $sql .= "   order by TAX_RATE desc";
        
            $searchArray = array();     // 検索キー指定なし
            
            $result = $DBA->executeSQL($sql, $searchArray);

            $resultList = array();

            if( $result === false )
            {
                $Log->trace("END getJSK4170DataList");
                return $resultList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $resultList, $data);
            }

            $Log->trace("END getJSK4170DataList");
            return $resultList;
        }
        
        /**
         * 伝票別税区分別税額取得
         * @param    $postArray
         * @return   顧客掛売取引税別明細配列
         */
        public function getDetailTax($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDetailTax");
 
            // 締処理日
            $postArray['wellset_date'] = str_replace('/','',$postArray['wellset_date']);

            $sql  = "";
            $sql .= "  select";
            $sql .= "     TAX_TYPE";
            $sql .= "    ,sum(PURE_TOTAL)   as SALES_TOTAL";
            $sql .= "    ,sum(TAX_TOTAL)    as SALES_TAX";
            $sql .= "   from JSK4170";
            $sql .= "   where ORGANIZATION_ID = ".$postArray['organization_id'];
            $sql .= "     and CUST_CD      =  '".$postArray['cust_cd']."'";
            $sql .= "     and WELLSET_DATE =  '".$postArray['wellset_date']."'";
            $sql .= "     and ACCOUNT_NO =  ".$postArray['account_no'];
            $sql .= "     and TAX_TYPE     <> '9'";		// 非課税以外
            $sql .= "   group by TAX_TYPE";
            $sql .= "   order by TAX_TYPE";

            $searchArray = array();     // 検索キー指定なし
            
            $result = $DBA->executeSQL($sql, $searchArray);

            $resultList = array();

            if( $result === false )
            {
                $Log->trace("END getDetailTax");
                return $resultList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $resultList, $data);
            }

            $Log->trace("END getDetailTax");
            return $resultList;
        }
    }
    
?>
