<?php
    /**
     * @file      仕入先別実績 [v]
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - コストの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetSalesByProvider extends BaseModel
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

        public function get_data()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_data");
            

           $sql = " SELECT         
            a.organization_id org_id,
            v.abbreviated_name org_nm,
            a.proc_date,
            a.supp_cd,
            b.supp_nm,
            SUM(a.sale_total) sale_total,
            SUM(a.sale_profit) sale_profit,
            SUM(a.sale_amount) sale_amount,
            SUM(a.sale_cust_cnt + a.sale_not_cust_cnt) as total_cnt
        FROM jsk1120 a
        JOIN mst1101 b ON (a.supp_cd = b.supp_cd and a.organization_id = b.organization_id)
        JOIN m_organization_detail v ON a.organization_id = v.organization_id ";
                $sql .= " where 1 = :val " ; 
                $sql .= "  and a.supp_cd ~ '^[0-9]*$' " ; 
                $sql .= " GROUP BY org_nm, org_id, a.proc_date, b.supp_nm, a.supp_cd";
                $sql .= " order by a.proc_date, org_id, a.supp_cd";

            $searchArray[':val'] = 1 ;
            //print_r('area: '.$sql);
            //print_r($searchArray);
            // SQLの実行
            $result = $DBA->executeSQL($sql, $searchArray);
            
            // 一覧表を格納する空の配列宣言
            $Datas = [];
            //print_r($result);
            // データ取得ができなかった場合、空の配列を返す
            if( $result === false )
            {
                
                $Log->trace("END get_data");
                return $Datas;
            }

            // 取得したデータ群を配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                    //print_r($data);
                    $Datas[] = $data;
            }

            $Log->trace("END get_data");

            return $Datas;
        }
        
    }
?>
