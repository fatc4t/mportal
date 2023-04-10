<?php
    /**
     * @file      帳票 - コスト
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
    class LedgerSheetDepartmentResults extends BaseModel
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
            
            $sql  = "";
            $sql .= " select j.proc_date " ;
            $sql .= " 	,j.organization_id  as org_id " ;
            $sql .= " 	,o.abbreviated_name as org_nm " ;
            $sql .= " 	,j.sect_cd " ;
            $sql .= " 	,m.sect_nm " ;
            $sql .= " 	,sum(j.sale_total) as sale_total " ;
            $sql .= " 	,sum(j.sale_profit) as sale_profit " ;
            $sql .= " 	,round(CASE sum(j.sale_total) " ;
            $sql .= " 		when 0 then 0 " ;
            $sql .= " 		else round(sum(j.sale_profit)/sum(j.sale_total)*100,2)  " ;
            $sql .= " 	end,2) as sale_per " ;
            $sql .= " 	,sum(j.sale_amount) as sale_amount " ;
            $sql .= " 	,sum(j.sale_cust_cnt+j.sale_not_cust_cnt) as cust_amount   " ;
            $sql .= " from jsk1130 j " ;
            $sql .= " left join m_organization_detail o on (o.organization_id = j.organization_id) " ;
            $sql .= " left join mst1201 m on (j.sect_cd = m.sect_cd and j.organization_id = m.organization_id) " ;
            $sql .= " where 1 = :org_id ";
            $sql .= "   and j.sect_cd ~ '^[0-9]*$' " ;
            $sql .= " group by j.proc_date, j.organization_id, o.abbreviated_name,j.sect_cd,m.sect_nm " ;
            $sql .= " order by proc_date,org_id,sect_cd " ;

            $searchArray[':org_id'] = 1 ;
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
