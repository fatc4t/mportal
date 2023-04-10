<?php
    /**
     * @file      帳票 - 注文書
     * @author    millionet oota
     * @date      2018/04/2
     * @version   1.00
     * @note      帳票 - 日次の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetOrder extends BaseModel
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
         * @param    $postArray                 入力パラメータ(supplierName/start_date/end_date)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT distinct
                      hed.hideseq,
                      hed.denno as denno,
                      mei.prod_cd,
                      MAX(pro.prod_kn) AS prod_kn,
                      MAX(pro.prod_nm) AS prod_nm,
                            CASE
                            WHEN pro.prod_capa_nm = $$$$
                            THEN (SELECT pro.prod_capa_kn)
                            ELSE (SELECT pro.prod_capa_nm)
                            END AS prod_capa,
                      mei.ord_amount,
                      to_char(date(hed.start_date), $$YYYY/MM/DD$$) AS start_date,
                      to_char(date(hed.end_date), $$YYYY/MM/DD$$) AS end_date,
                      v.abbreviated_name
                      FROM trn1601 hed 
                      INNER JOIN trn1602 mei on hed.hideseq = mei.hideseq 
                      INNER JOIN mst0201 pro on mei.prod_cd = pro.prod_cd 
                      JOIN v_organization v ON hed.organization_id = v.organization_id
                      WHERE true ';

            // AND条件
            if(!empty($postArray['supp_cd'])) {
              $sql .= ' AND hed.supp_cd  = :supp_cd';
              $isDelArray = array(':supp_cd' => $postArray['supp_cd']);
              $searchArray = array_merge($searchArray, $isDelArray);
            }

            if(!empty($postArray['start_date'])) {
              $sql .= ' AND hed.ord_date  >= :start_date';
              $isDelArray = array(':start_date' => str_replace("/","",$postArray['start_date']));
              $searchArray = array_merge($searchArray, $isDelArray);
            }

            if(!empty($postArray['end_date'])) {
              $sql .= ' AND hed.ord_date  <= :end_date';
              $isDelArray = array(':end_date' => str_replace("/","",$postArray['end_date']));
              $searchArray = array_merge($searchArray, $isDelArray);
            }
            $sql .= ' GROUP BY hed.hideseq, hed.denno, mei.prod_cd, prod_capa, ord_amount, start_date, end_date, v.abbreviated_name';

            $Log->debug($sql);

            $Log->trace("END creatSQL");
//            Edit 2019/11/29 kanderu
//            echo $sql;
//            print_r($searchArray);
            return $sql;
        }



        /**
         * 仕入先一覧取得
         * @return 仕入先一覧(配列)
         */
        public function getSuppliersList()
        {
          global $DBA,$Log;  // グローバル変数宣言
          $Log->trace("START getSuppliersList");

          $rtnArr = array();  //戻り値用配列型変数

          $sql  = 'SELECT distinct';
          $sql .= ' supp_cd, supp_nm, supp_kn ';
          $sql .= ' FROM mst1101 ';
          $sql .= ' ORDER BY supp_cd ';
          $parametersArray = array();
          $result = $DBA->executeSQL($sql, $parametersArray);
          if( $result === false )
          {
              $Log->trace("END getSuppliersList");
              return $rtnArr;
          }
          $rtnArr = $result->fetchAll(PDO::FETCH_ASSOC);
          $Log->trace("END getSuppliersList");

          return $rtnArr;
        }
        /**
         * 仕入先名取得
         * @return 仕入先名
         */
        public function getSupplierName($supp_cd)
        {
          global $DBA,$Log;  // グローバル変数宣言
          $Log->trace("START getSuppliersList");

          $supp_nm = '';

          $sql  = 'SELECT distinct';
          $sql .= ' MAX(supp_nm)';
          $sql .= ' FROM mst1101 ';
          $sql .= ' WHERE supp_cd = :supp_cd';
          $parametersArray = array(':supp_cd' => $supp_cd);
          $result = $DBA->executeSQL($sql, $parametersArray);
          if( $result === false )
          {
              $Log->trace("END getSuppliersList");
              return $supp_nm;
          }
          $row = $result->fetch(PDO::FETCH_ASSOC);
          $supp_nm = $row['supp_nm'];
          $Log->trace("END getSuppliersList");

          return $supp_nm;
        }
    }
?>
