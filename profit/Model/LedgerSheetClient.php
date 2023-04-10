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
    class LedgerSheetClient extends BaseModel
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

            $sql  = ' SELECT distinct ';
            $sql .= ' cust_cd, ';
            $sql .= ' REPLACE(cust_nm, $$　$$, $$ $$) AS cust_nm, ';
            $sql .= ' REPLACE(addr1, $$　$$, $$ $$) AS addr1, ';
            $sql .= ' REPLACE(addr2, $$　$$, $$ $$) AS addr2, ';
            $sql .= ' REPLACE(addr3, $$　$$, $$ $$) AS addr3, ';
            $sql .= ' REPLACE((CASE WHEN tel = $$$$ OR tel IS NULL THEN (SELECT tel4) ELSE (SELECT tel) end), $$-$$, $$$$) as tel, ';
            $sql .= ' REPLACE(hphone, $$-$$, $$$$) AS hphone, ';
            $sql .= ' TO_CHAR(TO_DATE(birth, $$YYYYMMDD$$), $$YYYY/MM/DD$$) AS birth, ';
            $sql .= ' (CASE WHEN dissenddm = $$0$$ THEN $$要$$ ELSE $$不要$$ end) AS dissenddm, ';
            $sql .= ' TO_CHAR(insdatetime,$$YYYY/MM/DD$$) AS insdatetime, ';
            $sql .= ' TO_CHAR(upddatetime,$$YYYY/MM/DD$$) AS updatetime ';
            $sql .= ' FROM mst0101';
            $sql .= ' WHERE true ';
            if(!empty($postArray['start_code'])){
                $sql .= ' AND cust_cd >= :start_code ';
                $searchArray = array_merge($searchArray, array(':start_code' => $postArray['start_code'],));
            }
            if(!empty($postArray['end_code'])){
                $sql .= ' AND cust_cd <= :end_code ';
                $searchArray = array_merge($searchArray, array(':end_code' => $postArray['end_code'],));
            }
            if(!empty($postArray['start_date'])){
                $sql .= ' AND insdatetime >= :start_date ';
                $searchArray = array_merge($searchArray, array(':start_date' => $postArray['start_date'],));
            }
            if(!empty($postArray['end_date'])){
                $sql .= ' AND insdatetime <= :end_date ';
                $searchArray = array_merge($searchArray, array(':end_date' => $postArray['end_date'],));
            }
            if(!empty($postArray['dm_code'])){
                $sql .= ' AND dissenddm = :dm_code ';
                $searchArray = array_merge($searchArray, array(':dm_code' => $postArray['dm_code'],));
            }
            if(!empty($postArray['cust_name'])){
                $sql .= " AND cust_nm like :cust_name ";
                $searchArray = array_merge($searchArray, array(':cust_name' => '%'.$postArray['cust_name'].'%',));
            }
            if(!empty($postArray['addr'])){
                $sql .= " AND (addr1 like :addr OR addr2 like :addr) ";
                $searchArray = array_merge($searchArray, array(':addr' => '%'.$postArray['addr'].'%',));
            }
            $sql .= ' group by cust_cd, tel, birth, addr1, addr2, addr3, cust_nm, tel4, hphone, dissenddm, insdatetime, upddatetime ';
            $sql .= ' ORDER BY cust_cd asc ';
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
