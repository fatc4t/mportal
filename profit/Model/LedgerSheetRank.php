<?php
    /**
     * @file      帳票 - 商品別
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 商品別の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetRank extends BaseModel
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
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT DISTINCT
                    a.prod_cd AS prod_cd, 
                    (CASE
                        WHEN b.prod_nm = $$$$ OR b.prod_nm IS NULL
                        THEN (SELECT b.prod_kn)
                        ELSE (SELECT b.prod_nm)
                    END) AS prod_kn, 
                    (CASE
                        WHEN b.prod_capa_nm = $$$$ OR b.prod_capa_nm IS NULL
                        THEN (SELECT b.prod_capa_kn)
                        ELSE (SELECT b.prod_capa_nm) 
                    END) AS prod_capa_nm, 
                    c.sect_cd AS sect_cd,
                    c.sect_nm AS sect_nm, 
                    (CASE  
                        WHEN b.shop_supp_cd != $$0$$ OR b.shop_supp_cd IS NOT NULL 
                        THEN (SELECT b.shop_supp_cd)
                        ELSE (SELECT b.head_supp_cd)
                    END) AS supp_cd,
                    d.supp_nm AS supp_nm, 
                    SUM(a.prod_sale_amount) AS prod_sale_amount, 
                    SUM(a.prod_sale_total) AS prod_sale_total,
                    SUM(a.prod_profit) AS prod_profit, 
                    (CASE 
                        WHEN a.prod_sale_total != $$0$$ 
                        THEN ROUND(SUM(a.prod_profit) / SUM(a.prod_sale_total),4) * 100 
                    END) AS cost_rate
                    FROM jsk5110 a 
                    INNER JOIN mst0201 b ON a.prod_cd = b.prod_cd 
                    INNER JOIN mst1201 c ON b.sect_cd = c.sect_cd 
                    INNER JOIN mst1101 d ON 
                    (CASE
                        WHEN b.shop_supp_cd != $$0$$ or b.shop_supp_cd IS NOT NULL 
                        THEN (SELECT b.shop_supp_cd)
                        ELSE (SELECT b.head_supp_cd)
                    END) = d.supp_cd ';
            $sql .= ' WHERE true AND a.prod_sale_amount != $$0$$ ';
            // AND条件
            //商品コード(開始)
            if( !empty( $postArray['prod_cd1'] ) )
            {
                $sql .= ' AND a.prod_cd >= :prod_cd1 ';
                $ledgerSheetFormIdArray = array(':prod_cd1' => $postArray['prod_cd1'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //商品コード(終了)
            if( !empty( $postArray['prod_cd2'] ) )
            {
                $sql .= ' AND a.prod_cd <= :prod_cd2 ';
                $ledgerSheetFormIdArray = array(':prod_cd2' => $postArray['prod_cd2'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //品名
            if( !empty( $postArray['prod_kn'] ) )
            {
                $sql .= " AND b.prod_nm LIKE :prod_kn ";
                $ledgerSheetFormIdArray = array(':prod_kn' => "%".$postArray['prod_kn']."%",);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //部門名
            if( !empty( $postArray['sect_cd'] ) )
            {
                $sql .= ' AND c.sect_cd = :sect_cd ';
                $ledgerSheetFormIdArray = array(':sect_cd' => $postArray['sect_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //仕入先
            if( !empty( $postArray['supp_cd'] ) )
            {
                $sql .= ' AND b.supp_cd = :supp_cd ';
                $ledgerSheetFormIdArray = array(':supp_cd' => $postArray['supp_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //JICFS分類
            if( !empty( $postArray['jicfs_class_cd'] ) )
            {
                $sql .= ' AND b.jicfs_class_cd = :jicfs_class_cd ';
                $ledgerSheetFormIdArray = array(':jicfs_class_cd' => $postArray['jicfs_class_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //自社分類
            if( !empty( $postArray['priv_class_cd'] ) )
            {
                $sql .= ' AND b.priv_class_cd = :priv_class_cd ';
                $ledgerSheetFormIdArray = array(':priv_class_cd' => $postArray['priv_class_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //売上期間開始
            if( !empty( $postArray['prod_date1'] ) )
            {
                $sql .= ' AND a.proc_date >= :prod_date1 ';
                $ledgerSheetFormIdArray = array(':prod_date1' => $postArray['prod_date1'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //売上期間終了
            if( !empty( $postArray['prod_date2'] ) )
            {
                $sql .= ' AND a.proc_date <= :prod_date2 ';
                $ledgerSheetFormIdArray = array(':prod_date2' => $postArray['prod_date2'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }


            $sql .= ' GROUP BY b.prod_nm, a.prod_cd, a.prod_sale_total, b.prod_kn, b.prod_capa_nm, c.sect_nm, d.supp_nm, b.head_supp_cd, b.shop_supp_cd, b.prod_capa_kn, c.sect_cd  ';
            $sql .= ' ORDER BY prod_sale_total DESC';

            $Log->trace("END creatSQL");
            return $sql;
        }
             /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            //合計の取得
            $totalData   = $this->getTotalData($postArray);
            $saleTotal   = $totalData['sale_total'];  //金額合計
            $profitTotal = $totalData['profit_total'];  //粗利合計

            //$Log->debug("金額合計",$totalData['sale_total']);
            //$Log->debug("粗利合計",$totalData['profit_total']);

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayRankDataList = array();

            if( $result === false )
            {
                $Log->trace("END getListData");
                return $displayRankDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                //構成比の計算
                $per1 = round($data['prod_sale_total'] / $saleTotal,4) * 100;
                $per2 = round($data['prod_profit'] / $profitTotal,4) * 100;

                $data["per1"]      = $per1;
                $data["per2"]      = $per2;
                array_push( $displayRankDataList, $data);
            }

            $Log->trace("END getListData");
            return $displayRankDataList;
        }

        /**
         * 帳票合計取得
         * @param    $postArray                 入力パラメータ
         * @return   合計取得
         */
        private function getTotalData( $postArray)
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getTotalData");

          $searchArray = array();
          $sql = $this->creatTotalSQL( $postArray, $searchArray );

          $result = $DBA->executeSQL($sql, $searchArray);
          $totalData = array();
          $totalData["sale_total"] = 0;
          $totalData["profit_total"] = 0;

          if( $result === false )
          {
              $Log->trace("END getTotalData");
              return $totalData;
          }

          $totalData = $result->fetch(PDO::FETCH_ASSOC);


          $Log->trace("END getTotalData");
          return $totalData;
        }

        /**
         * 合計計算用SQL作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   合計計算SQL文
         */private function creatTotalSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT
                      SUM(a.prod_sale_total) AS sale_total,
                      SUM(a.prod_profit) AS profit_total
                      FROM jsk5110 a
                      INNER JOIN mst0201 b ON a.prod_cd = b.prod_cd
                      INNER JOIN mst1201 c ON b.sect_cd = c.sect_cd 
                      INNER JOIN mst1101 d ON 
                      (CASE
                            WHEN b.shop_supp_cd != $$0$$ or b.shop_supp_cd IS NOT NULL 
                            THEN (select b.shop_supp_cd)
                            ELSE (select b.head_supp_cd)
                        END) = d.supp_cd ';
            $sql .= ' WHERE true ';
            // AND条件
            //商品コード(開始)
            if( !empty( $postArray['prod_cd1'] ) )
            {
                $sql .= ' AND a.prod_cd >= :prod_cd1 ';
                $ledgerSheetFormIdArray = array(':prod_cd1' => $postArray['prod_cd1'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //商品コード(終了)
            if( !empty( $postArray['prod_cd2'] ) )
            {
                $sql .= ' AND a.prod_cd <= :prod_cd2 ';
                $ledgerSheetFormIdArray = array(':prod_cd2' => $postArray['prod_cd2'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //品名
            if( !empty( $postArray['prod_kn'] ) )
            {
                $sql .= " AND b.prod_nm LIKE :prod_kn";
                $ledgerSheetFormIdArray = array(':prod_kn' => "%".$postArray['prod_kn']."%",);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //部門名
            if( !empty( $postArray['sect_cd'] ) )
            {
                $sql .= ' AND b.sect_cd = :sect_cd ';
                $ledgerSheetFormIdArray = array(':sect_cd' => $postArray['sect_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //仕入先
            if( !empty( $postArray['supp_cd'] ) )
            {
                $sql .= ' AND b.head_supp_cd = :supp_cd ';
                $ledgerSheetFormIdArray = array(':supp_cd' => $postArray['supp_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //JICFS分類
            if( !empty( $postArray['jicfs_class_cd'] ) )
            {
                $sql .= ' AND b.jicfs_class_cd = :jicfs_class_cd ';
                $ledgerSheetFormIdArray = array(':jicfs_class_cd' => $postArray['jicfs_class_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //自社分類
            if( !empty( $postArray['priv_class_cd'] ) )
            {
                $sql .= ' AND b.priv_class_cd = :priv_class_cd ';
                $ledgerSheetFormIdArray = array(':priv_class_cd' => $postArray['priv_class_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //売上期間開始
            if( !empty( $postArray['prod_date1'] ) )
            {
                $sql .= ' AND a.proc_date >= :prod_date1 ';
                $ledgerSheetFormIdArray = array(':prod_date1' => $postArray['prod_date1'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            //売上期間終了
            if( !empty( $postArray['prod_date2'] ) )
            {
                $sql .= ' AND a.proc_date <= :prod_date2 ';
                $ledgerSheetFormIdArray = array(':prod_date2' => $postArray['prod_date2'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 検索条件:部門名の取得
         * @param    なし
         * @return   成功時：$displaySectionList(部門名一覧)  失敗：無
         */
        public function getSectionList()
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getSectionList");

          $displaySectionList = array();

          $sql  = " SELECT distinct ";
          $sql .= "  sect_cd";
          $sql .= " ,sect_nm";
          $sql .= " FROM mst1201";
          $sql .= " ORDER BY sect_cd";

          $searchArray = array();
          $result = $DBA->executeSQL($sql, $searchArray);
          if( $result === false )
          {
              $Log->trace("END getSeciontList");
              return $displaySectionList;
          }

          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $displaySectionList, $data);
          }

          $Log->trace("END getSeciontList");
          return $displaySectionList;
        }
        /**
         * 検索条件:JICFS分類の取得
         * @param    なし
         * @return   成功時：$displayJicfsList(JICFS分類一覧)  失敗：無
         */
        public function getJicfsList()
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getJicfsList");

          $displayJicfsList = array();

          $sql  = " SELECT";
          $sql .= "  jicfs_class_cd";
          $sql .= " ,jicfs_class_nm";
          $sql .= " FROM mst5401";
          $sql .= " ORDER BY jicfs_class_cd";

          $searchArray = array();
          $result = $DBA->executeSQL($sql, $searchArray);
          if( $result === false )
          {
              $Log->trace("END getJicfsList");
              return $displayJicfsList;
          }

          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $displayJicfsList, $data);
          }

          $Log->trace("END getJicfsList");
          return $displayJicfsList;
        }
        /**
         * 検索条件:自社分類の取得
         * @param    なし
         * @return   成功時：$displayPrivateList(自社分類一覧)  失敗：無
         */
        public function getPrivList()
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getPrivList");

          $getPrivateList = array();

          $sql  = " SELECT distinct ";
          $sql .= "  priv_class_cd";
          $sql .= " ,priv_class_nm";
          $sql .= " FROM mst5501";
          $sql .= " ORDER BY priv_class_cd";

          $searchArray = array();
          $result = $DBA->executeSQL($sql, $searchArray);
          if( $result === false )
          {
              $Log->trace("END getPrivList");
              return $getPrivateList;
          }

          while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
          {
              array_push( $getPrivateList, $data);
          }

          $Log->trace("END getPrivList");
          return $getPrivateList;
        }
        /**
         * 検索条件:自社分類名の取得
         * @param    priv_class_cd
         * @return   成功時：$displayPrivateData(自社分類名)  失敗：無
         */
        public function getPrivData($priv_class_cd)
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getPrivData");

          $getPrivateData = '';
          $searchArray = array(
             "priv_class_cd"=>$priv_class_cd
          );
          $sql = $this->creatPrivSQL( $priv_class_cd, $searchArray );

          $result = $DBA->executeSQL($sql, $searchArray);
          if( $result === false )
          {
              $Log->trace("END getPrivData");
              return $getPrivateData;
          }

          $data = $result->fetch(PDO::FETCH_ASSOC);
          $getPrivateData = $data['priv_class_nm'];

          $Log->trace("END getPrivData");
          return $getPrivateData;
        }
        /**
         * 自社分類名取得SQL作成
         * @param    $postArray                 入力パラメータ
         * @param    $searchArray               検索条件用パラメータ
         * @return   自社分類名取得SQL文
         */
        private function creatPrivSQL( $priv_class_cd, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT distinct ';
            $sql .= '  priv_class_nm';
            $sql .= ' FROM mst5501';
            $sql .= ' WHERE priv_class_cd = :priv_class_cd ';
            $ledgerSheetFormIdArray = array(':priv_class_cd' => $priv_class_cd);
            $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);

            $Log->trace("END creatSQL");
            return $sql;
        }
        /**
         * 検索条件:部門名の取得
         * @param    sect_cd
         * @return   成功時：$displaySectionData(部門名)  失敗：無
         */
        public function getSectData($sect_cd)
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getSectData");

          $getSectionData = '';
          $searchArray = array(
             "sect_cd"=>$sect_cd
          );
          $sql = $this->creatSectSQL( $sect_cd, $searchArray );

          $result = $DBA->executeSQL($sql, $searchArray);
          if( $result === false )
          {
              $Log->trace("END getSectData");
              return $getSectionData;
          }

          $data = $result->fetch(PDO::FETCH_ASSOC);
          $getSectionData = $data['sect_nm'];

          $Log->trace("END getSectData");
          return $getSectionData;
        }
        /**
         * 部門名取得SQL作成
         * @param    $sect_cd                 部門コード
         * @param    $searchArray               検索条件用パラメータ
         * @return   部門名取得SQL文
         */
        private function creatSectSQL( $sect_cd, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT distinct ';
            $sql .= '  sect_nm';
            $sql .= ' FROM mst1201';
            $sql .= ' WHERE sect_cd = :sect_cd ';
            $ledgerSheetFormIdArray = array(':sect_cd' => $sect_cd);
            $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);

            $Log->trace("END creatSQL");
            return $sql;
        }
        /**
         * 検索条件:JICFS分類名の取得
         * @param    jicfs_class_cd
         * @return   成功時：$displayJicjsData(部門名)  失敗：無
         */
        public function getJicfsData($jicfs_class_cd)
        {
          global $DBA, $Log; // グローバル変数宣言
          $Log->trace("START getJicfsData");

          $getJicfsData = '';
          $searchArray = array(
             "jicfs_class_cd"=>$jicfs_class_cd
          );
          $sql = $this->creatJicfsSQL( $jicfs_class_cd, $searchArray );

          $result = $DBA->executeSQL($sql, $searchArray);
          if( $result === false )
          {
              $Log->trace("END getJicfsData");
              return $getJicfsData;
          }

          $data = $result->fetch(PDO::FETCH_ASSOC);
          $getJicfsData = $data['jicfs_class_nm'];

          $Log->trace("END getJicfsData");
          return $getJicfsData;
        }
        /**
         * JICFS分類名取得SQL作成
         * @param    $jicfs_class_cd                 JICFS分類コード
         * @param    $searchArray               検索条件用パラメータ
         * @return  JICFS分類名取得SQL文
         */
        private function creatJicfsSQL( $jicfs_class_cd, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql  = ' SELECT distinct ';
            $sql .= '  jicfs_class_nm';
            $sql .= ' FROM mst5401';
            $sql .= ' WHERE jicfs_class_cd = :jicfs_class_cd ';
            $ledgerSheetFormIdArray = array(':jicfs_class_cd' => $jicfs_class_cd);
            $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);

            $Log->trace("END creatSQL");
            return $sql;
        }
    }
?>
