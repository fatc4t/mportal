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
    class LedgerSheetProduct extends BaseModel
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
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getSectListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL_Master( $postArray, $searchArray );

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
                      a.prod_nm AS prod_nm, 
                      (CASE WHEN a.tax_type IS NOT NULL OR a.tax_type != $$$$ THEN
                                    (CASE WHEN a.tax_type = $$2$$ THEN (SELECT a.saleprice)
                                    ELSE (SELECT a.saleprice_ex)
                                    END)
                            ELSE 
                                    (CASE WHEN c.tax_type = $$2$$
                                     THEN (SELECT a.saleprice)
                                      ELSE (SELECT a.saleprice_ex) 
                                      END) 
                       END) AS saleprice, 
                      a.cust_saleprice as cust_saleprice, 
                 --   a.maker_cd as maker_cd, 
                      a.head_supp_cd AS head_supp_cd, 
                      (CASE WHEN a.prod_capa_nm = $$$$ OR a.prod_capa_nm IS NULL THEN (SELECT a.prod_capa_kn)
                      ELSE (SELECT a.prod_capa_nm) END) AS prod_capa,
                      (CASE WHEN a.tax_type = $$1$$ THEN $$税抜$$
                      WHEN a.tax_type = $$2$$ THEN $$税込$$
                      WHEN a.tax_type = $$9$$ THEN $$非課税$$
                      WHEN a.tax_type = $$$$ or a.tax_type IS NULL
                      THEN (SELECT CASE WHEN c.tax_type = $$1$$ THEN $$税抜$$
                      WHEN c.tax_type = $$2$$ THEN $$税込$$
                      WHEN c.tax_type = $$9$$ THEN $$非課税$$
                      END)
                      ELSE $$$$
                      END) AS tax_type, 
                      a.prod_cd AS prod_cd, 
                      a.prod_kn AS prod_kn, 
                      a.sect_cd AS sect_cd, 
                      c.sect_nm AS sect_nm, 
                      a.organization_id AS organization_id, 
                      a.saleprice AS saleprice, 
                      a.saleprice_ex AS saleprice_ex, 
                      a.head_costprice AS head_costprice, 
                      d.total_stock_amout AS total_stock_amount, 
                      d.endmon_amount AS endmon_amount, 
                  --  b.prod_sale_amount AS prod_sale_amount, 
                      e.supp_nm AS supp_nm, 
                      v.abbreviated_name AS abbreviated_name 
                      FROM mst0201 a 
                  --  LEFT JOIN jsk5110 b ON a.prod_cd = b.prod_cd and a.organization_id = b.organization_id 
                      LEFT JOIN mst1201 c ON a.sect_cd = c.sect_cd and a.organization_id = c.organization_id 
                      LEFT JOIN mst1101 e on e.supp_cd = a.head_supp_cd and a.organization_id = e.organization_id 
                      LEFT JOIN mst0204 d ON a.prod_cd = d.prod_cd and a.organization_id = d.organization_id 
                      LEFT JOIN v_organization v ON v.organization_id = a.organization_id 
                      WHERE true ';

            //$searchArray = array_merge($searchArray, array(':loca' => '',));
            if(!empty($postArray['start_code'])){
                $sql .= ' AND a.prod_cd >= :start_cd ';
                $searchArray = array_merge($searchArray, array(':start_cd' => $postArray['start_code'],));
            }
            if(!empty($postArray['end_code'])){
                $sql .= ' AND a.prod_cd <= :end_cd ';
                $searchArray = array_merge($searchArray, array(':end_cd' => $postArray['end_code'],));
            }
            if(!empty($postArray['prod_kn'])){
                $sql .= " AND a.prod_nm like :prod_kn ";
                $searchArray = array_merge($searchArray, array(':prod_kn' => '%'.$postArray['prod_kn'].'%',));
            }
            if(!empty($postArray['sect_cd']) && $postArray['sect_cd'] != '00'){
                $sql .= ' AND a.sect_cd = :sect_cd  ';
                $searchArray = array_merge($searchArray, array(':sect_cd' => $postArray['sect_cd'],));
            }
            if($postArray['start_tsa'] != ''){
                $sql .= ' AND d.total_stock_amout >= :start_tsa ';
                $searchArray = array_merge($searchArray, array(':start_tsa' => $postArray['start_tsa'],));
            }
            if($postArray['end_tsa'] != ''){
                $sql .= ' AND d.total_stock_amout <= :end_tsa ';
                $searchArray = array_merge($searchArray, array(':end_tsa' => $postArray['end_tsa'],));
            }
            if(!empty($postArray['start_ea'])){
                $sql .= ' AND d.endmon_amount >= :start_ea ';
                $searchArray = array_merge($searchArray, array(':start_ea' => $postArray['start_ea'],));
            }
            if(!empty($postArray['end_ea'])){
                $sql .= ' AND d.endmon_amount <= :end_ea ';
                $searchArray = array_merge($searchArray, array(':end_ea' => $postArray['end_ea'],));
            }
            

            $sql .= ' ORDER BY  a.prod_cd  asc ';

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

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL_Master( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT distinct ';
            $sql .= ' sect_cd, ';
            $sql .= ' sect_nm ';
            $sql .= ' FROM mst1201 ';
            $sql .= ' WHERE true ';
            $sql .= ' ORDER BY sect_cd ';

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
