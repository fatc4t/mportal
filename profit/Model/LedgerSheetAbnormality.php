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
    class LedgerSheetAbnormality extends BaseModel
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

            $sql  = ' SELECT ';
            $sql .= ' a.prod_cd AS prod_cd, ';
            $sql .= ' replace(CASE
                            WHEN b.prod_nm = $$$$
                            THEN (SELECT b.prod_kn)
                            ELSE (SELECT b.prod_nm)
                            END, $$　$$,$$ $$) AS prod_kn, ';
            $sql .= '   CASE
                            WHEN b.prod_capa_nm = $$$$ 
                            THEN (SELECT b.prod_capa_kn)
                            ELSE (SELECT b.prod_capa_nm)
                        END AS prod_capa_kn, ';
            $sql .= ' a.saleprice AS saleprice, ';
            $sql .= ' a.day_costprice AS day_costprice, ';
            $sql .= ' a.trndate AS trndate, ';
            $sql .= ' v.abbreviated_name AS abbreviated_name';
            $sql .= ' FROM trn0102 a ';
            $sql .= ' LEFT JOIN mst0201 b ON (a.prod_cd = b.prod_cd and a.organization_id = b.organization_id )';
            $sql .= ' JOIN v_organization v ON v.organization_id = a.organization_id ';
            $sql .= '  WHERE a.saleprice < day_costprice ';
            $sql .= $where;
           
            if(!empty($postArray['start_date'])){
                $sql .= ' AND a.trndate >= :start_date ';
                $searchArray = array_merge($searchArray, array(':start_date' => $postArray['start_date'],));
            }
            if(!empty($postArray['end_date'])){
                $sql .= ' AND a.trndate <= :end_date ';
                $searchArray = array_merge($searchArray, array(':end_date' => $postArray['end_date'],));
            }
            
            $sql .= ' ORDER BY a.hideseq,a.line_no ';

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
