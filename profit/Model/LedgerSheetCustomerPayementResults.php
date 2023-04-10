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
    class LedgerSheetCustomerPayementResults extends BaseModel
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
            $sql .= " 	 proc_date ";
            $sql .= " 	,j.organization_id as org_id ";
            $sql .= " 	,o.abbreviated_name as org_nm  ";
            $sql .= " 	,reji_no ";
            $sql .= " 	,sum(gift_cnt) as gift_cnt ";
            $sql .= " 	,sum(gift_total) as gift_total ";
            $sql .= " 	,sum(gift_profit) as gift_profit ";
            $sql .= " 	,sum(credit_cnt) as credit_cnt ";
            $sql .= " 	,sum(credit_total) as credit_total ";
            $sql .= " 	,sum(credit_profit) as credit_profit ";
            $sql .= " 	,sum(house_cnt) as house_cnt ";
            $sql .= " 	,sum(house_total) as house_total ";
            $sql .= " 	,sum(house_profit) as house_profit ";
            $sql .= " 	,sum(electr_cnt) as electr_cnt ";
            $sql .= " 	,sum(electr_total) as electr_total ";
            $sql .= " 	,sum(electr_profit) as electr_profit ";
            $sql .= " 	,sum(kake_cnt) as kake_cnt ";
            $sql .= " 	,sum(kake_total) as kake_total ";
            $sql .= " 	,sum(kake_profit) as kake_profit ";
            $sql .= " 	,sum(point_cnt) as point_cnt ";
            $sql .= " 	,sum(point_total) as point_total ";
            $sql .= " 	,sum(transport_cnt) as transport_cnt ";
            $sql .= " 	,sum(transport_total) as transport_total ";
            $sql .= " 	,sum(transport_profit) as transport_profit ";
            $sql .= " 	,sum(id_cnt) as id_cnt ";
            $sql .= " 	,sum(id_total) as id_total ";
            $sql .= " 	,sum(id_profit) as id_profit ";
            $sql .= " 	,sum(edy_cnt) as edy_cnt ";
            $sql .= " 	,sum(edy_total) as edy_total ";
            $sql .= " 	,sum(edy_profit) as edy_profit ";
            $sql .= " 	,sum(nanaco_cnt) as nanaco_cnt ";
            $sql .= " 	,sum(nanaco_total) as nanaco_total ";
            $sql .= " 	,sum(nanaco_profit) as nanaco_profit ";
            $sql .= " 	,sum(waon_cnt) as waon_cnt ";
            $sql .= " 	,sum(waon_total) as waon_total ";
            $sql .= " 	,sum(waon_profit) as waon_profit ";
            $sql .= " 	,sum(quicpay_cnt) as quicpay_cnt ";
            $sql .= " 	,sum(quicpay_total) as quicpay_total ";
            $sql .= " 	,sum(quicpay_profit) as quicpay_profit ";
            $sql .= " from jsk1010 j ";
            $sql .= " left join v_organization as o on (j.organization_id = o.organization_id) ";
            $sql .= " where 1 = :val  ";
            $sql .= " group by  ";
            $sql .= " 	 proc_date ";
            $sql .= " 	,j.organization_id ";
            $sql .= " 	,o.abbreviated_name ";
            $sql .= " 	,reji_no ";
            $sql .= " order by proc_date,org_id,reji_no ";

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
