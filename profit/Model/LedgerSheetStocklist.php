<?php
    /**
     * @file      帳票 - 在庫一覧表(明細一覧表)
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 在庫一覧表(明細一覧表)の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetStocklist extends BaseModel
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
         * @return   成功時：$displayStockList(帳票フォームデータ)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayStockList = array();

            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayStockList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayStockList, $data);
            }

            $Log->trace("END getListData");
            return $displayStockList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            //print_r($_POST);
            // 検索条件
            $where  = "";
            //EDITSTR 2020/02/04 kanderu
            //organization_id =-1のデータを表示しない
            $where .= " WHERE mst0204.location_no = '' and  mst0204.organization_id  <> -1";
            //EDITEND 2020/02/04 kanderu
//            if ($postArray['bb_date_kbn'] === '0'){
//                $where .= " AND   mst0204.bb_date <> ''";
//            }
//            else if ($postArray['bb_date_kbn'] === '1'){
//                $where .= " AND   mst0204.bb_date = ''";
//            }

            if( $postArray['organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $where .= " AND mst0204.organization_id in (".$postArray['organization_id'].") ";
            }
            if( $postArray['prod_cd'] !== 'false' )
            {
                $where .= " AND mst0204.prod_cd in (".$postArray['prod_cd'].") ";
            }
            if( $postArray['sect_cd'] !== 'false' )
            {
                $where .= " AND mst1201.sect_cd in (".$postArray['sect_cd'].") ";
            }
            // 現在在庫
            if ($postArray['amount_kbn'] === '0'){
                // 在庫数範囲指定
                if (!empty($postArray['amount_str'])){
                    $where .= " AND mst0204.total_stock_amout >= :amount_str ";
                    $ledgerSheetFormIdArray = array(':amount_str' => $postArray['amount_str'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
                if (!empty($postArray['amount_end'])){
                    $where .= " AND mst0204.total_stock_amout <= :amount_end ";
                    $ledgerSheetFormIdArray = array(':amount_end' => $postArray['amount_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
            }
            // 前月末在庫
            else if ($postArray['amount_kbn'] === '1'){
                // 在庫数範囲指定
                if (!empty($postArray['amount_str'])){
                    $where .= " AND mst0204.endmon_amount >= :amount_str ";
                    $ledgerSheetFormIdArray = array(':amount_str' => $postArray['amount_str'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
                if (!empty($postArray['amount_end'])){
                    $where .= " AND mst0204.endmon_amount <= :amount_end ";
                    $ledgerSheetFormIdArray = array(':amount_end' => $postArray['amount_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
            }

            $sql  = " SELECT ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "    mst0204.organization_id ";
                $sql .= "   ,v.abbreviated_name, ";
            }
            $sql .= "    mst0204.prod_cd ";
            $sql .= "   ,mst0201.prod_nm ";
            $sql .= "   ,mst0201.prod_tax ";
            if ($postArray['bb_date_kbn'] === '0' && $postArray['bb_date_dspkbn'] === '0'){
                $sql .= "   ,CASE WHEN COALESCE(mst0204.bb_date, '') = '' THEN '' ";
                $sql .= "         ELSE TO_CHAR(TO_DATE(mst0204.bb_date, 'YYYYMMDD'), 'YYYY/MM/DD') ";
                $sql .= "    END AS bb_date ";
            }
            $sql .= "   ,mst0201.prod_capa_nm ";
            $sql .= "   ,mst0201.head_costprice ";
            $sql .= "   ,mst0201.saleprice ";
            $sql .= "   ,mst0201.saleprice_ex ";
            $sql .= "   ,SUM(mst0204.total_stock_amout)     AS total_stock_amout ";
            $sql .= "   ,SUM(mst0204.endmon_amount)         AS endmon_amount ";
            $sql .= "   FROM mst0204 ";
            $sql .= "   INNER JOIN mst0201 ON ( mst0201.prod_cd = mst0204.prod_cd and mst0201.organization_id = mst0204.organization_id ) ";
            $sql .= "   INNER JOIN mst1201 ON ( mst1201.sect_cd = mst0201.sect_cd and mst1201.organization_id = mst0201.organization_id ) ";
            $sql .= "   LEFT JOIN v_organization v ON v.organization_id = mst0204.organization_id  ";
            $sql .= $where;
            $sql .= "   GROUP BY ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "    mst0204.organization_id ";
                $sql .= "   ,v.abbreviated_name, ";
            }
            $sql .= "    mst0204.prod_cd ";
            $sql .= "   ,prod_nm ";
            $sql .= "   ,prod_tax ";
            if ($postArray['bb_date_kbn'] === '0' && $postArray['bb_date_dspkbn'] === '0'){
               $sql .= "   ,bb_date ";
            }
            $sql .= "   ,prod_capa_nm ";
            $sql .= "   ,head_costprice ";
            $sql .= "   ,saleprice ";
            $sql .= "   ,saleprice_ex ";
            
            // ゼロ在庫表示しない
            if ($postArray['zero_kbn'] === '1'){
                // 現在在庫
                if ($postArray['amount_kbn'] === '0'){
                    $sql .= " HAVING SUM(mst0204.total_stock_amout) <> 0 ";
                 }
                // 前月末在庫
                else if ($postArray['amount_kbn'] === '1'){
                    $sql .= " HAVING SUM(mst0204.endmon_amount) <> 0 ";
                }
            }
            
            $sql .= "   ORDER BY ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "    mst0204.organization_id, ";
            }
            $sql .= "    mst0204.prod_cd ";
            if ($postArray['bb_date_kbn'] === '0' && $postArray['bb_date_dspkbn'] === '0'){
               $sql .= "   ,bb_date ";
            }

            $Log->trace("END creatSQL");
           // echo $sql;
		   //print_r($sql);
            return $sql;
        }
        
        
        /**
         * システムマスタ：賞味期限利用区分判定
         * @param    なし
         * @return   0:利用する / 1:利用しない
         */
        public function chk_BB_DATE_KBN()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START chk_BB_DATE_KBN");

            $searchArray = array();
            $sql  = "";
            $sql .= " SELECT DISTINCT bb_date_kbn FROM mst0010 WHERE disabled = '0' ";

            $result = $DBA->executeSQL($sql, $searchArray);

            //$getDataList = array();

            if( $result === false )
            {
                $Log->trace("END chk_BB_DATE_KBN");
                //return $getDataList;
                return '1';     // 利用しない
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //array_push( $getDataList, $data);
                if ($data['bb_date_kbn'] === '0'){
                    $Log->trace("END chk_BB_DATE_KBN");
                    return '0';     // 利用する
                }
            }

            $Log->trace("END chk_BB_DATE_KBN");
            //return $getDataList;
            return '1';     // 利用しない
        }
    }
?>
