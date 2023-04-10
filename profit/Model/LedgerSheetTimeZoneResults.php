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
    class LedgerSheetTimeZoneResults extends BaseModel
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
            $sql .= " select ";
            $sql .= " 	substring(j.proc_date,0,7) as proc_date , ";
            $sql .= " 	j.organization_id as org_id, ";
            $sql .= " 	o.abbreviated_name as org_nm , ";
            $sql .= " 	j.tmzone_cd, ";
            $sql .= " 	(to_char(m.tmzone_str::time,'HH24:MI') || ' ~ ' || to_char(m.tmzone_end::time,'HH24:MI') ) as time_period, ";
            $sql .= " 	sum(j.sale_total) as sale_total , ";
            $sql .= " 	sum(j.sale_total)-coalesce(sum(t.tax),0) as pure_total, ";
            $sql .= " 	sum(j.sale_profit) as sale_profit, ";
            $sql .= " 	sum(j.sale_amount) as sale_amount, ";
            $sql .= " 	coalesce(sum(t.return_amount),0) as return_amount, ";
            $sql .= " 	coalesce(sum(t.return_total),0) as return_total, ";
            $sql .= " 	coalesce(sum(t.tax),0) as tax ";
            $sql .= " from ";
            $sql .= " 	jsk1190 j ";
            $sql .= " left join m_organization_detail o on (o.organization_id = j.organization_id) ";
            $sql .= " left join mst8101 m on (m.organization_id = j.organization_id and m.tmzone_cd = j.tmzone_cd) ";
            $sql .= " left join (	select ";
            $sql .= " 				distinct(mt.organization_id), ";
            $sql .= " 				tt.trndate as proc_date, ";
            $sql .= " 				mt.tmzone_cd, ";
            $sql .= " 				sum(tt.return_amount) as return_amount, ";
            $sql .= " 				sum(tt.return_total) as return_total, ";
            $sql .= " 				sum(tt.itax + tt.utax) as tax, ";
            $sql .= " 				mt.tmzone_str, ";
            $sql .= " 				mt.tmzone_end ";
            $sql .= " 			from ";
            $sql .= " 				trn0101 tt, ";
            $sql .= " 				mst8101 mt ";
            $sql .= " 			where ";
            $sql .= "                               tt.organization_id = mt.organization_id ";
            $sql .= " 				and tt.trntime >= mt.tmzone_str ";
            $sql .= " 				and tt.trntime <= mt.tmzone_end ";
            $sql .= " 				and tt.trndate != '' ";
            $sql .= " 			group by ";
            $sql .= " 				mt.organization_id, ";
            $sql .= " 				mt.tmzone_cd, ";
            $sql .= " 				tt.trndate, ";
            $sql .= " 				mt.tmzone_str, ";
            $sql .= " 				mt.tmzone_end ";
            $sql .= " 			order by  ";
            $sql .= " 				organization_id,trndate,tmzone_cd ";
            $sql .= " 		) t on (t.organization_id = j.organization_id and t.tmzone_cd = j.tmzone_cd and j.proc_date = t.proc_date) ";
            $sql .= " where ";
            $sql .= " 		1 = :val ";
            $sql .= " 	and j.tmzone_cd ~ '^[0-9]*$' ";
            $sql .= " group by ";
            $sql .= " 	substring(j.proc_date,0,7), ";
            $sql .= " 	j.organization_id, ";
            $sql .= " 	o.abbreviated_name, ";
            $sql .= " 	j.tmzone_cd, ";
            $sql .= " 	m.tmzone_str,m.tmzone_end ";
            $sql .= " order by  proc_date ";
            $sql .= " 		, org_id ";
            $sql .= " 		, tmzone_cd ";
           // print_r($sql);
            $searchArray[':val'] = 1 ;
            //print_r($sql);
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
