<?php
    /**
     * @file      帳票 - 在庫一覧表(合計表)
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 在庫一覧表(合計表)の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetStockTotal extends BaseModel
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

            $displayStockTotal = array();

            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayStockTotal;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayStockTotal, $data);
            }

            $Log->trace("END getListData");
            return $displayStockTotal;
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

            // 検索条件
            $where  = '';
            $where .= 'WHERE true  AND pro.organization_id <> - 1 ';


            if( $postArray['organization_id'] !== 'compile' && $postArray['organization_id'] !== 'all')
            {
                $where .= " AND pro.organization_id in (".$postArray['organization_id'].") ";
            }
            if( $postArray['prod_cd'] !== 'false' )
            {
                $where .= " AND pro.prod_cd in (".$postArray['prod_cd'].") ";
            }
            if( $postArray['sect_cd'] !== 'false' )
            {
                $where .= " AND pro.sect_cd in (".$postArray['sect_cd'].") ";
            }

            $sql  = 'SELECT ';
            $sql .= "  '001' gyokbn ";
            $sql .= ' ,sec.type_cd type_cd ';
            $sql .= ' ,MAX(bun.type_nm) type_nm ';
            $sql .= ' ,SUM(pro.saleprice) saleprice ';
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "   ,pro.organization_id    AS org_id ";
                $sql .= "   ,v.abbreviated_name     AS org_nm ";
            }

            // 現在在庫
            if ($postArray['amount_kbn'] === '0'){
                $sql .= ' ,SUM(sto.total_stock_amout)                       AS amount ';
                $sql .= ' ,SUM(pro.head_costprice * sto.total_stock_amout)  AS total1 ';
                $sql .= ' ,SUM(pro.saleprice * sto.total_stock_amout)       AS total2 ';
            }
            // 前月末在庫
            else if ($postArray['amount_kbn'] === '1'){
                $sql .= ' ,SUM(sto.endmon_amount)                            AS amount ';
                $sql .= ' ,SUM(pro.head_costprice * sto.endmon_amount)       AS total1 ';
                $sql .= ' ,SUM(pro.saleprice * sto.endmon_amount)            AS total2 ';
            }
            else{
                $sql .= ' ,0 AS amount ';
                $sql .= ' ,0 AS total1 ';
                $sql .= ' ,0 AS total2 ';
            }
            $sql .= ' FROM MST0201 pro ';
            $sql .= ' INNER JOIN mst0204 sto ON (pro.prod_cd=sto.prod_cd and pro.organization_id=sto.organization_id) ';
            $sql .= ' INNER JOIN mst1201 sec ON (pro.sect_cd=sec.sect_cd and pro.organization_id=sec.organization_id) ';
            $sql .= ' LEFT  JOIN mst1205 bun ON (sec.type_cd=bun.type_cd and sec.organization_id=bun.organization_id) ';
            $sql .= "   LEFT JOIN v_organization v ON v.organization_id = sto.organization_id  ";
            $sql .= $where;
            $sql .= ' GROUP BY sec.type_cd ';
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "  ,org_id, org_nm ";
            }
            // ゼロ在庫表示しない
            if ($postArray['zero_kbn'] === '1'){
                // 現在在庫
                if ($postArray['amount_kbn'] === '0'){
                    $sql .= " HAVING SUM(sto.total_stock_amout) <> 0 ";
                 }
                // 前月末在庫
                else if ($postArray['amount_kbn'] === '1'){
                    $sql .= " HAVING SUM(sto.endmon_amount) <> 0 ";
                }
            }
            // 現在在庫
            if ($postArray['amount_kbn'] === '0'){
                // 在庫数範囲指定
                if (!empty($postArray['amount_str'])){
                    $where .= " AND SUM(sto.total_stock_amout) >= :amount_str ";
                    $ledgerSheetFormIdArray = array(':amount_str' => $postArray['amount_str'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
                if (!empty($postArray['amount_end'])){
                    $where .= " AND SUM(sto.total_stock_amout) <= :amount_end ";
                    $ledgerSheetFormIdArray = array(':amount_end' => $postArray['amount_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
            }
            // 前月末在庫
            else if ($postArray['amount_kbn'] === '1'){
                // 在庫数範囲指定
                if (!empty($postArray['amount_str'])){
                    $where .= " AND SUM(sto.endmon_amount) >= :amount_str ";
                    $ledgerSheetFormIdArray = array(':amount_str' => $postArray['amount_str'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
                if (!empty($postArray['amount_end'])){
                    $where .= " AND SUM(sto.endmon_amount) <= :amount_end ";
                    $ledgerSheetFormIdArray = array(':amount_end' => $postArray['amount_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
            }

            $sql .= "  ORDER BY ";
            if ($postArray['organization_id'] !== 'compile'){
                $sql .= "  org_id, ";
            }
            $sql .= '  2, 1 ';

            $Log->trace("END creatSQL");
            return $sql;
        }
    }
?>
