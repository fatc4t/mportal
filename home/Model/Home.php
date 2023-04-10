<?php
    /**
     * @file    トップモデル
     * @author  スクリプト作者
     * @date    日付
     * @version バージョン
     * @note    注釈を記述
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * トップモデルクラス
     * @note   注釈を記述
     */
    class Home extends BaseModel
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
         * HOGE検索
         * @param    $postArray   検索パラメータ
         * @return   検索結果(Array)
         */
        public function searchHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchHoge");
            
            $sql = "";
            $sql = $this->getSearchSql($postArray);
            $retArray = array();
            
            $Log->trace("END   searchHoge");
            return $retArray;
        }

        /**
         * HOGE登録
         * @param    $postArray   登録パラメータ
         * @return   成功時：true  失敗：false
         */
        public function addHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START addHoge");
            
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   addHoge");
            return true;
        }

        /**
         * HOGE修正
         * @param    $postArray   修正パラメータ
         * @return   成功時：true  失敗：false
         */
        public function modHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START modHoge");
            
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   modHoge");
            return true;
        }

        /**
         * HOGE削除
         * @param    $postArray   削除パラメータ
         * @return   成功時：true  失敗：false
         */
        public function delHoge( $postArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START delHoge");
            
            $retArray = $postArray;
            $retArray = array();
            
            $Log->trace("END   delHoge");
            return true;
        }
        
        //kanderu
         public function unregprodlist($postArray)
        {
         global $DBA, $Log; // グローバル変数宣言
         $Log->trace("START unregprodlist"); 
         $sql  = " select ";
         $sql .= "    to_char (insdatetime,'yyyy-mm-dd') as date  ";
         $sql .= "    ,o.abbreviated_name  as orgn_nm ";
         $sql .= "    ,m.prod_cd  ";
         $sql .= "    ,m.prod_nm  ";
         $sql .= "    from mst0201 m  ";
         $sql .= "    left join m_organization_detail o  ";
         $sql .= "    on (m.organization_id=o.organization_id)  "; 
         $sql .= "    where m.prod_nm =''  ";
         $sql .= "    and m.organization_id <> -1  ";
         $sql .= "    order by m.organization_id,date,m.prod_cd  ";
      //  print_r($sql);
                  
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END unregprodlist");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END unregprodlist");
            return $ret;
        }
        
        

        /**
         * 売上履歴一覧を取得
         * @param    $postArray                 入力パラメータ(mcode/limit)
         * @return   POS種別マスタ一覧取得SQL文
         */
        public function getProfitHistoryList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProfitHistoryList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT   to_char(m.target_date, 'yyyy/mm/dd') as target_date "
                  . "         ,o.abbreviated_name as organization_name "
                  . "         ,m.data "
                  . "   FROM  t_mcode_data_day m "
                  . "         LEFT JOIN (SELECT * FROM v_organization WHERE eff_code = '適用中') o "
                  . "              ON (to_number(m.organization_id, '000000000000') = o.organization_id) "
                  . "  WHERE mcode = :mcode"
                  . "  ORDER BY target_date desc"
                  . "  limit 10";

            $parameters = array( ':mcode'  => $postArray['mcode'],
                               );
            
            $result = $DBA->executeSQL($sql, $parameters);
            
            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProfitHistoryList");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProfitHistoryList");
            return $ret;
        }

        /**
         * 売上履歴一覧を取得
         * @param    $postArray                 入力パラメータ(mcode/limit)
         * @return   POS種別マスタ一覧取得SQL文
         */
        public function getProfitHistoryList2( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProfitHistoryList2");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT   to_date(MAX(m.proc_date), 'yyyymmdd') as proc_date "
                  . "         ,MAX(o.abbreviated_name) as organization_name "
                  . "         ,SUM(m.total_total) as total_total "
                  . "         ,SUM(m.total_utax) as total_utax "
                  . "         ,SUM(m.total_profit) as total_profit "
                  . "   FROM  jsk1010 m "
                  . "         LEFT JOIN (SELECT * FROM v_organization WHERE eff_code = '適用中') o "
                  . "              ON (m.organization_id = o.organization_id) "
//                  . "  WHERE m.organization_id = :organization_id"
                  . "  GROUP BY m.organization_id,m.proc_date "
                  . "  ORDER BY m.proc_date desc"
                  . "  limit 10";

//            $parameters = array( ':organization_id'  => $postArray['organization_id'],
//                               );
            
            $result = $DBA->executeSQL($sql, $parameters);
            
            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProfitHistoryList2");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProfitHistoryList");
            return $ret;
        }

        /**
         * 全店売上詳細一覧を取得
         * @param    $postArray                 入力パラメータ(mcode/limit)
         * @return   POS種別マスタ一覧取得SQL文
         */
        public function getProfitSummaryList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProfitHistoryList");
            
            // 全店の売上詳細一覧を取得
            $sql  = " SELECT   to_char(m1.target_date, 'yyyy/mm/dd') as target_date "
                  . "         ,o.abbreviated_name as organization_name "
                  . "         ,m1.data as data1"
                  . "         ,m2.data as data2"
                  . "         ,m3.data as data3"
                  . "         ,m4.data as data4"
                  . "         ,m5.data as data5"
                  . "         ,m6.data as data6"
                  . "   FROM  t_mcode_data_day m1 "
                  . "         LEFT JOIN (SELECT * FROM t_mcode_data_day WHERE mcode = :mcode2) m2 ON (m1.target_date = m2.target_date AND m1.organization_id = m2.organization_id) "
                  . "         LEFT JOIN (SELECT * FROM t_mcode_data_day WHERE mcode = :mcode3) m3 ON (m1.target_date = m3.target_date AND m1.organization_id = m3.organization_id) "
                  . "         LEFT JOIN (SELECT * FROM t_mcode_data_day WHERE mcode = :mcode4) m4 ON (m1.target_date = m4.target_date AND m1.organization_id = m4.organization_id) "
                  . "         LEFT JOIN (SELECT * FROM t_mcode_data_day WHERE mcode = :mcode5) m5 ON (m1.target_date = m5.target_date AND m1.organization_id = m5.organization_id) "
                  . "         LEFT JOIN (SELECT * FROM t_mcode_data_day WHERE mcode = :mcode6) m6 ON (m1.target_date = m6.target_date AND m1.organization_id = m6.organization_id) "
                  . "         LEFT JOIN (SELECT * FROM v_organization WHERE eff_code = '適用中') o "
                  . "              ON (to_number(m1.organization_id, '000000000000') = o.organization_id) "
                  . "  WHERE m1.mcode = :mcode1"
                  . "  ORDER BY m1.target_date desc"
                  . "    limit 5 ";

            $parameters = array(
                                ':mcode1'    =>  $postArray['mcode1'],
                                ':mcode2'    =>  $postArray['mcode2'],
                                ':mcode3'    =>  $postArray['mcode3'],
                                ':mcode4'    =>  $postArray['mcode4'],
                                ':mcode5'    =>  $postArray['mcode5'],
                                ':mcode6'    =>  $postArray['mcode6'],
                               );

            
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProfitHistoryList");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProfitHistoryList");
            return $ret;
        }

        /**
         * 全店売上詳細一覧を取得
         * @param    $postArray                 入力パラメータ(mcode/limit)
         * @return   POS種別マスタ一覧取得SQL文
         */
        public function getProfitSummaryList2( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProfitHistoryList2");
            
            // 全店の売上詳細一覧を取得
            $sql  = " SELECT   MAX(m.proc_date) as proc_date "
                  . "         ,MAX(o.abbreviated_name) as organization_name "
                  . "         ,SUM(m.cash_total) as cash_total "
                  . "         ,SUM(m.credit_total) as credit_total "
                  . "         ,SUM(m.kake_total) as kake_total "
                  . "         ,SUM(m.total_profit) as total_profit "
                  . "         ,SUM(m.return_total) as return_total "
                  . "         ,SUM(m.total_cnt) as total_cnt "
                  . "   FROM  jsk1010 m "
                  . "         LEFT JOIN (SELECT * FROM v_organization WHERE eff_code = '適用中') o "
                  . "              ON (m.organization_id = o.organization_id) "
                  . "  WHERE date(m.proc_date) = date(now() + '-1 day') "
//                  . "  WHERE date(m.proc_date) = date('2019/01/05') "
                  . "  GROUP BY m.organization_id,m.proc_date "
                  . "  ORDER BY m.organization_id desc "
                  . "    limit 5 ";

//            $parameters = array(
//                                ':proc_date'    =>  $postArray['proc_date'],
//                               );

            
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProfitHistoryList2");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProfitHistoryList");
            return $ret;
        }

        /**
         * 全店売上推移用一覧を取得
         * @param    $postArray                 入力パラメータ(mcode/limit)
         * @return   POS種別マスタ一覧取得SQL文
         */
        public function getProfitList( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProfitHistoryList");
            
            // 全店の月初から当日までの売上一覧を取得
            $sql  = " SELECT   t.target_date "
                  . "         ,o.organization_name "
                  . "         ,COALESCE(m.data,'0') as data"
                  . "   FROM  (SELECT * FROM generate_series( cast(DATE_TRUNC('month', date(now())) as timestamp) ,DATE_TRUNC('month', cast(date(now()) as timestamp) + '1 months') + '-1 days','1 days') as target_date) as t "
                  . "         LEFT JOIN (SELECT * FROM t_mcode_data_day WHERE mcode = :mcode) m on (t.target_date = m.target_date) "
                  . "         LEFT JOIN (SELECT * FROM v_organization WHERE eff_code = '適用中' AND organization_id = :organization_id) o "
                  . "              ON (to_number(m.organization_id, '000000000000') = o.organization_id) "
                  . "  WHERE t.target_date >= DATE_TRUNC('month', date(now())) "
                  . "    AND t.target_date <= date(now()) "
                  . "  ORDER BY t.target_date ";

            $parameters = array( ':mcode'  => $postArray['mcode'],
                                 ':organization_id' => $postArray['organization_id'],
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProfitHistoryList");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProfitHistoryList");
            return $ret;
        }

        /**
         * 全店売上推移用一覧を取得
         * @param    $postArray                 入力パラメータ(mcode/limit)
         * @return   POS種別マスタ一覧取得SQL文
         */
        public function getProfitList2( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProfitHistoryList2");
            
            // 全店の月初から当日までの売上一覧を取得
            $sql  = " SELECT   m.proc_date,SUBSTR(m.proc_date,7,2)::int As day "
                  . "         ,'全店' as organization_name "
                  . "         ,sum(m.total_total) as total_total "
                  . "   FROM  jsk1010 m "
                  . "         LEFT JOIN (SELECT * FROM v_organization WHERE eff_code = '適用中') o "
                  . "              ON (m.organization_id = o.organization_id) "
                  . "  WHERE date(m.proc_date) >= DATE_TRUNC('month', date(now())) "
                  . "    AND date(m.proc_date) <= date(now()) "
                  . "    group by m.proc_date "
                  . "    order by m.proc_date";                
            
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProfitHistoryList2");
                return $ret;
            }

            $a = count($result);
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProfitHistoryList2");
            return $ret;
        }

        /**
         * 本日の出勤中を取得
         * @return   打刻情報
         */
        public function getEmbossingNowList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEmbossingMissList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  date"
                  . "        ,v.user_name"
                  . "   FROM  t_attendance as te LEFT JOIN v_user as v ON (te.user_id = v.user_id)"
                  . "  WHERE te.organization_id = :organization_id"
                  . "    AND te.clock_out_time_id is null"
                  . "    AND date = date(now())"
                  . "  ORDER BY date desc"
                  . "  limit 10";

            $parameters = array( ':organization_id'  => $organizationId,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getEmbossingMissList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getEmbossingMissList");
            return $ret;
            
        }
        
        /**
         * 商品粗利のTOP10
         * @return   商品情報
         */
        public function getProductList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getProductList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  CASE WHEN LENGTH(m.prod_nm) >= 15 THEN substr(m.prod_nm,0,15) || '...' "
                  . "              ELSE m.prod_nm "
                  . "          END as prod_nm"
                  . "        ,sum(j.prod_sale_amount) as prod_sale_amount "
                  . "        ,sum(j.prod_profit) as prod_profit "
                  . "   FROM  jsk5110 j"
                  . "         JOIN mst0201 as m ON (j.organization_id = m.organization_id and j.prod_cd = m.prod_cd)"
                  . "  WHERE date(proc_date) >= DATE_TRUNC('month', now()) "
                  . "    AND date(proc_date) <= DATE_TRUNC('month', now()) + '1 month' +'-1 Day' "
                  . "  GROUP BY m.prod_nm "
                  . "  ORDER BY prod_profit desc"
                  . "  limit 10";

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getProductList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getProductList");
            return $ret;
            
        }

        /**
         * 発注状況
         * @return   商品情報
         */
        public function getOrderList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOrderList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  CASE WHEN LENGTH(m.prod_nm) >= 10 THEN substr(m.prod_nm,0,10) || '...'  "
                  . "              ELSE m.prod_nm "
                  . "          END as prod_nm "
                  . "        ,CASE WHEN LENGTH(m2.supp_nm) >= 10 THEN substr(m2.supp_nm,0,10) || '...' "
                  . "              ELSE m2.supp_nm  "
                  . "          END as supp_nm "
                  . "         ,sum(t.ord_amount) as ord_amount "
                  . "   FROM  trn1602 t"
                  . "         left JOIN mst0201 as m ON (t.organization_id = m.organization_id and t.prod_cd = m.prod_cd) "
                  . "         left JOIN mst1101 as m2 ON (m.organization_id = m2.organization_id and m.head_supp_cd = m2.supp_cd) "
                  . "         left join trn1601 as t1 ON(t.organization_id = t1.organization_id and t.hideseq = t1.hideseq) "
                  . "  WHERE  date(ord_date) >= DATE_TRUNC('month', now()) AND date(ord_date) <= DATE_TRUNC('month', now()) + '1 month' +'-1 Day' "
                  . "  GROUP BY m2.supp_nm, m.prod_nm, t1.ord_date "
                  . "  ORDER BY t1.ord_date desc "
                  . "  limit 5";

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getOrderList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getOrderList");
            return $ret;
            
        }

        /**
         * 仕入状況
         * @return   商品情報
         */
        public function getPurchaseList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPurchaseList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  CASE WHEN LENGTH(m.prod_nm) >= 10 THEN substr(m.prod_nm,0,10) || '...'  "
                  . "              ELSE m.prod_nm "
                  . "          END as prod_nm "
                  . "        ,CASE WHEN LENGTH(m2.supp_nm) >= 10 THEN substr(m2.supp_nm,0,10) || '...' "
                  . "              ELSE m2.supp_nm  "
                  . "          END as supp_nm "
                  . "         ,sum(t.amount) as amount "
                  . "   FROM  trn1102 t"
                  . "         left JOIN mst0201 as m ON (t.organization_id = m.organization_id and t.prod_cd = m.prod_cd) "
                  . "         left JOIN mst1101 as m2 ON (m.organization_id = m2.organization_id and m.head_supp_cd = m2.supp_cd) "
                  . "         left join trn1101 as t1 ON(t.organization_id = t1.organization_id and t.hideseq = t1.hideseq) "
                  . "  WHERE  date(stock_date) >= DATE_TRUNC('month', now()) AND date(stock_date) <= DATE_TRUNC('month', now()) + '1 month' +'-1 Day' "
                  . "  GROUP BY m2.supp_nm, m.prod_nm, t1.stock_date "
                  . "  ORDER BY t1.stock_date desc "
                  . "  limit 5";

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getPurchaseList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getPurchaseList");
            return $ret;
            
        }

        /**
         * 打刻忘れの可能性アリを取得
         * @return   打刻情報
         */
        public function getEmbossingMissList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEmbossingMissList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  date"
                  . "        ,o.organization_name"
                  . "        ,v.user_name"
                  . "   FROM  t_attendance as te"
                  . "         LEFT JOIN v_user as v ON (te.user_id = v.user_id)"
                  . "         LEFT JOIN v_organization as o ON (te.organization_id = o.organization_id)"
                  . "  WHERE te.organization_id = :organization_id"
                  . "    AND te.clock_out_time_id is null"
                  . "    AND date < date(now())"
                  . "  ORDER BY date desc"
                  . "  limit 10";

            $parameters = array( ':organization_id'  => $organizationId,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getEmbossingMissList");
                return $ret;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getEmbossingMissList");
            return $ret;
            
        }
        
        /**
         * 新規顧客リストを取得
         * @return   打刻情報
         */
        public function getCustomerList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCustomerList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  CASE WHEN LENGTH(cust_nm) >= 12 THEN substr(cust_nm,0,12) || '...' "
                  . "              ELSE cust_nm "
                  . "          END as cust_nm"
                  . "        , to_char(insdatetime, 'yyyy-mm-dd HH12:MI:SS') as insdatetime"
                  . "   FROM mst0101"
                  . "  ORDER BY insdatetime desc"
                  . "  limit 10";

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getCustomerList");
                return $ret;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getCustomerList");
            return $ret;
            
        }
        
        /**
         * 打刻履歴を取得
         * @return   打刻情報
         */
        public function getEmbossingList($organizationId)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getEmbossingList");
            
            // 認証IDから、履歴一覧を取得
            $sql  = " SELECT  to_char(date_time, 'yyyy/mm/dd') as date"
                  . "        ,to_char(date_time, 'HH24:MI:SS') as time"
                  . "        ,embossing_type"
                  . "        ,v.user_name"
                  . "   FROM  t_embossing as te LEFT JOIN (select d.* from m_user_detail as d"
                  . "                                        join (select user_id,max(application_date_start) as application_date_start"
                  . "                                                from m_user_detail"
                  . "                                               where application_date_start <= now()"
                  . "                                               group by user_id"
                  . "                                             ) as ud"
                  . "                                          on (d.user_id = ud.user_id and d.application_date_start = ud.application_date_start)"
                  . "                                      ) as v ON (te.user_id = v.user_id)"
                  . "  WHERE te.organization_id = :organization_id"
                  . "  ORDER BY date_time desc"
                  . "  limit 14";

            $parameters = array( ':organization_id'  => $organizationId,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getEmbossingList");
                return $ret;
            }
                        
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getEmbossingList");
            return $ret;
            
        }

        /**
         * ワークフロー承認待ち件数を取得
         * @return   件数
         */
        public function getApprovalList($user_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getApprovalList");
            
            // スタッフIDから、承認待ち件数を取得
            $sql  = " SELECT count(*)"
                  . "   FROM t_workflow"
                  . "  WHERE (   next1_data LIKE  :staff_code"
                  . "         or next2_data LIKE  :staff_code"
                  . "         or next3_data LIKE  :staff_code"
                  . "         or next4_data LIKE  :staff_code"
                  . "         or next5_data LIKE  :staff_code"
                  . "         or next6_data LIKE  :staff_code"
                  . "         or next7_data LIKE  :staff_code"
                  . "         or next8_data LIKE  :staff_code"
                  . "         or next9_data LIKE  :staff_code"
                  . "         or next10_data LIKE  :staff_code"
                  . "    ) and stat = '1'";

            $parameters = array( ':staff_code'  => "%*" . $user_id . "-2%",
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getApprovalList");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getApprovalList");
            return $ret;
            
        }
        
        /**
         * ワークフロー参照待ち件数を取得
         * @return   件数
         */
        public function getReferenceList($user_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getReferenceList");
            
            // スタッフIDから、参照待ち件数を取得
            $sql  = " SELECT count(*)"
                  . "   FROM t_workflow"
                  . "  WHERE  next1_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next1_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next1_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next1_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next1_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next2_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next2_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next2_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next2_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next2_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next3_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next3_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next3_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next3_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next3_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next4_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next4_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next4_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next4_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next4_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next5_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next5_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next5_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next5_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next5_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next6_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next6_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next6_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next6_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next6_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next7_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next7_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next7_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next7_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next7_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next8_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next8_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next8_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next8_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next8_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next9_cc1_data   = '*' || :staff_code || '-2' or"
                  . "         next9_cc2_data   = '*' || :staff_code || '-2' or"
                  . "         next9_cc3_data   = '*' || :staff_code || '-2' or"
                  . "         next9_cc4_data   = '*' || :staff_code || '-2' or"
                  . "         next9_cc5_data   = '*' || :staff_code || '-2' or"
                  . "         next10_cc1_data  = '*' || :staff_code || '-2' or"
                  . "         next10_cc2_data  = '*' || :staff_code || '-2' or"
                  . "         next10_cc3_data  = '*' || :staff_code || '-2' or"
                  . "         next10_cc4_data  = '*' || :staff_code || '-2' or"
                  . "         next10_cc5_data  = '*' || :staff_code || '-2' or"
                  . "         cc1  = :staff_code || '-2' or"
                  . "         cc2  = :staff_code || '-2' or"
                  . "         cc3  = :staff_code || '-2' or"
                  . "         cc4  = :staff_code || '-2' or"
                  . "         cc5  = :staff_code || '-2' ";

            $parameters = array( ':staff_code'  => $user_id,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getReferenceList");
                return $ret;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getReferenceList");
            return $ret;
            
        }
        
        /**
         * ワークフロー申請中件数を取得
         * @return   件数
         */
        public function getApplyingList($user_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getApplyingList");
            
            // スタッフIDから、申請中件数を取得
            $sql  = " SELECT count(*)"
                  . "   FROM t_workflow"
                  . "  WHERE  user_id  = :staff_code "
                  . "    AND  stat = '1'";

            $parameters = array( ':staff_code'  => $user_id,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getApplyingList");
                return $ret;
            }
                        
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getApplyingList");
            return $ret;
            
        }

        /**
         * トップメッセージ最新を取得
         * @return   件数
         */
        public function getTopMessage()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTopMessage");
            
            // スタッフIDから、申請中件数を取得
            $sql  = " SELECT *"
                  . "   FROM t_top_message"
                  . "  order by update_time DESC "
                  . "  limit 1";

            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getTopMessage");
                return $ret;
            }
                        
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getTopMessage");
            return $ret;
            
        }

        /**
         * 日報の件数を取得
         * @return   件数
         */
        public function getDailyReportList($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getDailyReportList");

            $parameters = array();
            
            // 日報の件数を取得
            $sql = '   SELECT  dr.daily_report_id'
                    . '       ,dr.data'
                    . "       ,to_char(dr.target_date,'YYYY/MM/DD') as target_date"
                    . '       ,dr.form_id'
                    . '       ,dr.user_id'
                    . '       ,v.user_name'
                    . '       ,v.organization_name'
                    . '  FROM t_daily_report as dr'
                    . "  LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (dr.user_id = v.user_id)"
                    . ' WHERE daily_report_id is not null';

            // 組織
            if( !empty( $postArray['organization_id'] ) )
            {
                $sql .= " AND dr.organization_id = :organization_id ";
                $parameters = array_merge($parameters, array(':organization_id' => $postArray['organization_id'],));
            }
            
            // スタッフ
            if( !empty( $postArray['user_id'] ) )
            {
                $sql .= " AND dr.user_id = :user_id ";
                $parameters = array_merge($parameters, array(':user_id' => $postArray['user_id'],));
            }
            
            // 件数制限
            $sql .= " ORDER BY registration_time DESC "
                 .  " limit 25 ";
            
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getDailyReportList");
                return $ret;
            }
                        
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getDailyReportList");
            return $ret;
            
        }

        /**
         * 通達連絡最新を取得
         * @return   件数
         */
        public function getNoticeContactList($user_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getnoticeContactList");
            
            // スタッフIDから、自分に関する通達連絡を取得
            $sql  = " SELECT nc.notice_contact_id"
                  . "       ,CASE WHEN LENGTH(nc.title) > 15 THEN SUBSTR(nc.title, 0,  15) || ' ...' "
                  . "             ELSE nc.title "
                  . "        END as title"
                  . "       ,CASE WHEN LENGTH(nc.contents) > 10 THEN SUBSTR(nc.contents, 0,  35) || ' ...' "
                  . "             ELSE nc.title "
                  . "        END as contents"
                  . "       ,CASE WHEN ncd.state is null THEN 1"
                  . "             WHEN cast(nc.registration_user_id as integer) = :user_id THEN 1"
                  . "             ELSE ncd.state"
                  . "        END as state"
                  . "       ,CASE WHEN nc.registration_time > current_timestamp + '-3 days' THEN 1"
                  . "             ELSE 0"
                  . "        END as is_new"
                  . "       ,v.organization_name"
                  . "       ,v.user_name"
                  . "       ,nc.update_time"
                 . "        ,to_char(nc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time"
                  . "   FROM t_notice_contact as nc"
                  . "        JOIN (SELECT notice_contact_id,state"
                  . "                FROM t_notice_contact_details"
                  . "               WHERE user_id = :user_id"
                  . "             ) as ncd on (nc.notice_contact_id =  ncd.notice_contact_id) "
                  . "        LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (cast(nc.registration_user_id as integer) = v.user_id)"
                  . " UNION"
                  . " SELECT nc.notice_contact_id"
                  . "       ,CASE WHEN LENGTH(nc.title) > 15 THEN SUBSTR(nc.title, 0,  15) || ' ...' "
                  . "             ELSE nc.title "
                  . "        END as title"
                  . "       ,CASE WHEN LENGTH(nc.contents) > 10 THEN SUBSTR(nc.contents, 0,  35) || ' ...' "
                  . "             ELSE nc.title "
                  . "        END as contents"
                  . "       ,1 as state"
                  . "       ,CASE WHEN nc.registration_time > current_timestamp + '-3 days' THEN 1"
                  . "             ELSE 0"
                  . "        END as is_new"
                  . "       ,v.organization_name"
                  . "       ,v.user_name"
                  . "       ,nc.update_time"
                 . "        ,to_char(nc.registration_time,'YYYY/MM/DD HH24時MI分SS秒') as registration_time"
                  . "   FROM t_notice_contact as nc"
                  . "        LEFT JOIN (SELECT * FROM v_user WHERE eff_code = '適用中') v on (cast(nc.registration_user_id as integer) = v.user_id)"
                  . "   WHERE cast(registration_user_id as integer) = :user_id"
                  . "   ORDER BY update_time DESC "
                  . "   limit 20 ";

            $parameters = array( ':user_id'  => $user_id,
                               );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getnoticeContactList");
                return $ret;
            }
                        
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getnoticeContactList");
            return $ret;
            
        }

        /**
         * テロップ最新を取得
         * @return   件数
         */
        public function getTelopList($user_id)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTelopList");
            
            // スタッフIDから、申請中件数を取得
            $sql  = " select *"
                  . "   from m_telop"
                  . "  where application_start_date <= current_timestamp"
                  . "    and application_end_date >= current_timestamp"
                  . "   order by registration_time DESC ";

            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            
            if( $result === false )
            {
                $Log->trace("END getTelopList");
                return $ret;
            }
                        
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($ret,$data);
            }

            $Log->trace("END getTelopList");
            return $ret;
            
        }
        

        /**
       * 発注先一覧の取得
       * @param    $where 発注先名
       * @return   SQLの実行結果
       */
      	public function getMakerList($pha_id) {
      		global $DBA, $Log; // グローバル変数宣言
                $Log->trace("START getMakerList");

          			$params = array();

          			$sql  = " SELECT mak.ma_cd,ma_name,rse.postage,rse.free_kin,mak.mail_addr,mak.url1,mak.url2,mak.description From ord_m_maker mak";
          			$sql .= " INNER JOIN ord_m_makerset rse on(mak.ma_cd=rse.ma_cd and rse.pha_id=:pha_id)";
          			$sql .= " where mak.is_del=0";
          			$sql .= " ORDER BY mak.registration_time DESC";
                $sql .= " LIMIT 3 ";

          			$params = array("pha_id"=>$pha_id);
          			$result = $DBA->executeSQL($sql, $params);

                $ret = array();

                if( $result === false )
                {
                    $Log->trace("END getMakerList");
                    return $ret;
                }
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    array_push( $ret, $data);
                }

                $Log->trace("END getMakerList");

                return $ret;
      	}
        /**
       * 商品一覧の取得
       * @param    $where 発注先名
       * @return   SQLの実行結果
       */
      	public function getProductsList($pha_id) {
      		global $DBA, $Log; // グローバル変数宣言
                $Log->trace("START getProductsList");

          			$params = array();
                $sql  = " SELECT ";
                $sql .= " pro.*";
                $sql .= " ,mak.ma_name";
                $sql .= " FROM ord_m_products pro ";
                $sql .= " INNER JOIN ord_m_maker mak on (pro.ma_cd=mak.ma_cd)";
                $sql .= " WHERE pro.is_del=0";
                /*
                $sql .= " AND ma_cd IN (";
          			$sql  = " SELECT mak.ma_cd From ord_m_maker mak";
          			$sql .= " INNER JOIN ord_m_makerset rse on(mak.ma_cd=rse.ma_cd and rse.pha_id=:pha_id)";
          			$sql .= " where mak.is_del=0";
                $sql .= " )";
                */
                $sql .= " AND pro.pha_id=:pha_id";
          			$sql .= " ORDER BY pro.registration_time DESC";
                $sql .= " LIMIT 5 ";

          			$params = array("pha_id"=>$pha_id);
          			$result = $DBA->executeSQL($sql, $params);

                $ret = array();

                if( $result === false )
                {
                    $Log->trace("END getProductsList");
                    return $ret;
                }
                while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
                {
                    array_push( $ret, $data);
                }

                $Log->trace("END getProductsList");

                return $ret;
      	}
    }
?>
