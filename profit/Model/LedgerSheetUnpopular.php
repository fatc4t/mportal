<?php
    /**
     * @file      帳票 - 在庫調査表
     * @author    media craft
     * @date      2018/03/22
     * @version   1.00
     * @note      帳票 - 在庫調査表の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';


    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetUnpopular extends BaseModel
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

            $sql  = ' SELECT distinct 
                     a.sect_cd AS sect_cd,
                     m.organization_name AS organization_name,
                     a.prod_cd AS prod_cd,
                     (CASE WHEN a.shop_supp_cd = $$$$ OR a.shop_supp_cd IS NULL
                     THEN (SELECT a.head_supp_cd)
                     ELSE (SELECT a.shop_supp_cd) END) as supp_cd,
                     b.supp_nm AS supp_nm,
                     (CASE WHEN a.prod_nm = $$$$ THEN (SELECT a.prod_kn)
                     ELSE (SELECT a.prod_nm) END) AS prod_kn, 
                     e.sect_nm AS sect_nm,
                     c.dead_months AS dead_months,
                     d.total_stock_amout AS total_stock_amount
                     FROM mst0201 a
                     LEFT JOIN mst1101 b ON a.head_supp_cd = b.supp_cd AND a.organization_id = b.organization_id 
                     LEFT JOIN jsk5100 c ON a.prod_cd = c.prod_cd AND a.organization_id = c.organization_id 
                     LEFT JOIN mst0204 d ON a.prod_cd = d.prod_cd AND a.organization_id = d.organization_id AND d.location_no = :loca  
                     LEFT JOIN m_organization_detail m ON a.organization_id = m.organization_id 
                     LEFT JOIN mst1201 e ON a.sect_cd = e.sect_cd 
                     WHERE true ';
            $searchArray = array_merge($searchArray, array(':loca' => '',));
            if(!empty($postArray['supp_cd'])){
                $sql .= ' AND supp_cd = :supp_cd ';
                $searchArray = array_merge($searchArray, array(':supp_cd' => $postArray['supp_cd'],));
            }
            if(!empty($postArray['months'])){
                $sql .= ' AND c.dead_months > :months ';
                $searchArray = array_merge($searchArray, array(':months' => $postArray['months'],));
            }
            if(!empty($postArray['counts'])){
                $sql .= ' AND d.total_stock_amout > :counts ';
                $searchArray = array_merge($searchArray, array(':counts' => $postArray['counts'],));
            }
            $sql .= ' AND dead_months != $$0$$ AND dead_months IS NOT NULL ';
            $sql .= ' ORDER BY a.prod_cd asc ';

            // AND条件
            // if( !empty($postArray['is_del']) )
            // {
            //     $sql .= ' AND is_del = :is_del ';
            //     $isDelArray = array(':is_del' => $postArray['is_del'],);
            //     $searchArray = array_merge($searchArray, $isDelArray);
            // }

            $Log->trace("END creatSQL");
            return $sql;
        }
    }
?>
