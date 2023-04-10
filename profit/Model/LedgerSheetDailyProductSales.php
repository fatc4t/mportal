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
    class LedgerSheetDailyProductSales extends BaseModel
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
            $sql .= " 	j.proc_date as proc_date, ";
            $sql .= " 	t.organization_id as org_id , ";
            $sql .= " 	o.abbreviated_name as org_nm , ";
            $sql .= " 	t.sect_cd, ";
            $sql .= " 	j.sale_plan_cd as splan_cd , ";
            $sql .= " 	coalesce(mt1.sale_plan_nm,'簡易特売') as splan_nm , ";
            $sql .= " 	j.prod_cd , ";
            $sql .= " 	regexp_replace (replace(coalesce(t.prod_nm_dsp, ''), '''', ' '),'/ | | | |	|\&|\b|/gm','') as prod_nm, ";            
            $sql .= " 	t.day_costprice as costprice , ";
            $sql .= " 	case  ";
            $sql .= " 		when t.day_saleprice <> 0 then t.day_saleprice ";
            $sql .= " 		else t.saleprice ";
            $sql .= " 	end as saleprice , ";
            $sql .= " 	sum(t.amount)   as sale_amount, ";
            $sql .= " 	sum(t.subtotal) as sale_total, ";
            $sql .= " 	sum(t.profit_i) as sale_profit ";
            $sql .= " from ";
            $sql .= " 	jsk1140 j ";
            $sql .= " left join trn0102 t on  ";
            $sql .= " 	(		j.organization_id = t.organization_id  ";
            $sql .= " 		and t.prod_cd = j.prod_cd  ";
            $sql .= " 		and j.sale_plan_cd = t.sale_plan_cd ";
            $sql .= " 	) ";
            $sql .= " right join trn0101 t1 on (j.organization_id = t1.organization_id and t.hideseq = t1.hideseq and t1.proc_date = j.proc_date) ";
            $sql .= " left join mst1301 mt1 on ";
            $sql .= " 	(		mt1.organization_id = t.organization_id ";
            $sql .= " 		and mt1.sale_plan_cd = t.sale_plan_cd ";
            $sql .= " 	) ";
            $sql .= " left join m_organization_detail o on ";
            $sql .= " 	(o.organization_id = t.organization_id) ";
            $sql .= " where ";
            $sql .= " 	1 = :val ";
            $sql .= " 	and j.sale_plan_cd ~ '^[0-9]*$' ";
            $sql .= " 	and j.prod_cd ~ '^[0-9]*$' ";
            $sql .= " 	and j.sale_plan_cd is not null ";
            $sql .= " 	and j.sale_plan_cd <> '' ";
            $sql .= " 	and day_saleprice is not null ";
            $sql .= " 	and t1.stop_kbn = '0' ";
            $sql .= " 	and t1.cancel_cnt = '0' ";
            $sql .= " group by  ";
            $sql .= " 	 j.proc_date ";
            $sql .= " 	,org_id ";
            $sql .= " 	,org_nm ";
            $sql .= " 	,sect_cd ";
            $sql .= " 	,splan_cd ";
            $sql .= " 	,splan_nm ";
            $sql .= " 	,j.prod_cd ";
            $sql .= " 	,prod_nm ";
            $sql .= " 	,costprice ";
            $sql .= " 	,saleprice ";
            $sql .= " 	,t.day_saleprice ";
            $sql .= " order by ";
            $sql .= " 	proc_date, ";
            $sql .= " 	splan_cd, ";
            $sql .= " 	sect_cd, ";
            $sql .= " 	prod_cd ";
            
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
