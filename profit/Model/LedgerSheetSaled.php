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
    class LedgerSheetSaled extends BaseModel
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
            if( !empty( $postArray['saled_date'] ) )
            {
                $where .= ' where date(a.proc_date) = date(:saled_date) ';
                $ledgerSheetFormIdArray = array(':saled_date' => $postArray['saled_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['organization_id'] ) )
            {
                $where .= ' AND a.organization_id = :organization_id ';
                $ledgerSheetFormIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }


            if(isset($_POST['prod_capa'])){
                $capa = '';
                $capa = ' (CASE WHEN b.prod_capa_nm = $$$$ or b.prod_capa_nm IS NULL THEN (select          b.prod_capa_kn)
                ELSE (select b.prod_capa_nm) END) AS prod_capa, ';}
            else{$capa = '';}

            $sql  = ' SELECT  ';
            $sql .= ' a.proc_date AS proc_date, ';
            $sql .= ' b.shop_supp_cd AS shop_supp_cd, ';
            $sql .= ' c.supp_nm AS supp_nm, ';
            $sql .= ' b.prod_cd AS prod_cd, ';
            $sql .= ' (CASE WHEN b.prod_nm = $$$$ OR b.prod_nm IS NULL THEN (SELECT b.prod_kn)
                      ELSE (SELECT b.prod_nm) END) AS prod_nm, ';
            $sql .= $capa;
            $sql .= ' CAST(SUM(a.prod_sale_amount) AS INT) AS prod_sale_amount, ';
            $sql .= ' CAST(SUM(a.prod_profit) AS INT) AS prod_profit, ';
            $sql .= ' CAST(SUM(a.prod_pure_total) AS INT) AS prod_pure_total, ';
            $sql .= ' (CASE WHEN b.head_costprice != 0 and a.prod_pure_total != 0 then 
                      (SELECT CAST((select a.prod_profit * 100/a.prod_pure_total)as decimal(5,2))) 
                      else $$0$$ end) 
                      as arariritsu ';
            $sql .= ' FROM jsk5110 a ';
            $sql .= ' LEFT JOIN mst0201 b ON a.prod_cd = b.prod_cd and a.organization_id = b.organization_id ';
            $sql .= ' LEFT JOIN mst1101 c ON b.shop_supp_cd = c.supp_cd and b.organization_id = c.organization_id ';
            $sql .= $where;
            //$searchArray = array_merge($searchArray, array(':loca' => '',));
            $sql .= ' GROUP BY b.organization_id, a.proc_date, b.shop_supp_cd, c.supp_nm, b.prod_cd, b.prod_nm, prod_disc_total, prod_pure_total, prod_profit ';
            $sql .= ' ORDER BY b.shop_supp_cd ';

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
