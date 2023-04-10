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
    class LedgerSheetStockpile extends BaseModel
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
            $where = '';
            if( !empty( $postArray['organization_id'] ) )
            {
                $where .= ' AND a.organization_id = :organization_id ';
                $ledgerSheetFormIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if(!isset($_POST['zero']))
            {$where .= " AND c.total_stock_amout >= '1' ";}
            else{$where .= '';}

            $sql  = ' SELECT
                     a.prod_cd AS prod_cd,
                     CASE 
                        WHEN a.prod_nm = $$$$ 
                        THEN (select a.prod_kn)
                        ELSE (select a.prod_nm)
                        END AS prod_kn,                     
                     CASE
                         WHEN a.prod_capa_nm = $$$$
                         THEN (SELECT a.prod_capa_kn)
                         ELSE (SELECT a.prod_capa_nm)
                     END AS prod_capa_kn,
                     SUM(a.saleprice) / NULLIF(count(a.prod_cd),0) AS saleprice,
                     b.jicfs_class_cd AS jicfs_class_cd,
                     b.jicfs_class_nm AS jicfs_class_nm,
                     sum(c.total_stock_amout) AS total_stock_amout,
                     CASE
                          WHEN d.dead_months != $$0$$ or d.dead_months IS NOT NULL
                          THEN (select d.dead_months)
                          ELSE $$0$$
                     END AS dead_months,
                     v.abbreviated_name as abbreviated_name
                     FROM mst0201 a 
                     LEFT JOIN mst5401 b ON (a.jicfs_class_cd = b.jicfs_class_cd and a.organization_id = b.organization_id) 
                     LEFT JOIN mst0204 c ON (a.prod_cd = c.prod_cd AND c.location_no = :loca and a.organization_id = c.organization_id) 
                     LEFT JOIN jsk5100 d ON (a.prod_cd = d.prod_cd and a.organization_id = d.organization_id) 
                     LEFT JOIN v_organization v ON a.organization_id = v.organization_id
                     WHERE true ';
            $sql .= $where;
            $sql .= ' group by a.prod_cd,a.prod_kn,a.prod_capa_kn,b.jicfs_class_cd,b.jicfs_class_nm,d.dead_months, abbreviated_name, a.prod_capa_nm, a.prod_nm';
            $sql .= ' ORDER BY a.prod_cd ';

            // AND条件
            // if( !empty($postArray['is_del']) )
            // {
            //     $sql .= ' AND is_del = :is_del ';
            //     $isDelArray = array(':is_del' => $postArray['is_del'],);
            //     $searchArray = array_merge($searchArray, $isDelArray);
            // }
            $searchArray = array_merge($searchArray, array(':loca' => ''));

            $Log->trace("END creatSQL");
            return $sql;
        }
    }
?>
