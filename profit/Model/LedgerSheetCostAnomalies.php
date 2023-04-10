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
    class LedgerSheetCostAnomalies extends BaseModel
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
            $sql .= " 	 distinct( m.organization_id ) as org_id ";
            $sql .= " 	,o.abbreviated_name as org_nm ";
            $sql .= " 	,m.prod_cd ";
            $sql .= " 	,regexp_replace (replace(replace(coalesce(m.prod_nm,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_nm ";
            $sql .= " 	,regexp_replace (replace(coalesce(m.prod_capa_nm,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_capa_nm ";
            $sql .= " 	,m.sect_cd ";
            $sql .= " 	,regexp_replace (replace(coalesce(mse.sect_nm,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as	sect_nm ";
            $sql .= " 	,coalesce(m.priv_class_cd,'') as priv_class_cd ";
            $sql .= " 	,regexp_replace (replace(coalesce(mc.priv_class_nm,mc.priv_class_kn,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as priv_class_nm ";
            $sql .= " 	,coalesce(m.maker_cd,'') as maker_cd ";
            $sql .= " 	,regexp_replace (replace(coalesce(mm.maker_nm,''),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as maker_nm ";
            $sql .= " 	,coalesce(m.saleprice_ex, 0) as saleprice ";
            $sql .= " 	,m.cust_saleprice ";
            $sql .= " 	,m.head_costprice ";
            $sql .= " 	,coalesce(m.head_supp_cd,'') as supp_cd ";
            $sql .= " 	,regexp_replace (replace(coalesce(msu.supp_nm,'なし'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as	supp_nm ";
            $sql .= " 	,m.order_lot ";
            $sql .= " 	,coalesce(m.return_lot,0) as return_lot ";
            $sql .= " 	,m.order_stop_kbn ";
            $sql .= " 	,m.noreturn_kbn ";
            $sql .= " from mst0201 m  ";
            $sql .= " left join m_organization_detail o on (o.organization_id = m.organization_id) ";
            $sql .= " left join mst1201 mse on (m.organization_id = mse.organization_id and m.sect_cd       = mse.sect_cd) ";
            $sql .= " left join mst1101 msu on (m.organization_id = msu.organization_id and m.head_supp_cd  = msu.supp_cd) ";
            $sql .= " left join mst1001 mm  on (m.organization_id = mm.organization_id  and m.maker_cd      = mm.maker_cd) ";
            $sql .= " left join mst5501 mc  on (m.organization_id = mc.organization_id  and m.priv_class_cd = mc.priv_class_cd) ";
            $sql .= " left join jsk5110 j on (m.organization_id = j.organization_id and m.prod_cd = j.prod_cd) ";
            $sql .= " where ";
            $sql .= "       1 = :val ";
            $sql .= " 	and ( m.saleprice_ex < m.head_costprice or m.head_costprice = 0 ) ";
            $sql .= " 	and ( j.prod_cd is not null ";
            $sql .= " 		  or m.insdatetime >= now() - interval '6 month' ";
            $sql .= " 	     ) ";
            $sql .= " order by org_id,m.prod_cd "; 
            
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
