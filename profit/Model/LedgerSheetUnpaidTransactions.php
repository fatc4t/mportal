<?php
    /**
     * @file      帳票 - 掛売明細一覧
     * @author    川橋
     * @date      2019/06/20
     * @version   1.00
     * @note      帳票 - 掛売明細一覧の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetUnpaidTransactions extends BaseModel
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
            $sql .= "    trn0101.proc_date ";
            $sql .= " 	,trn0101.organization_id    as org_id ";
            $sql .= " 	,v.abbreviated_name         as org_nm ";
            $sql .= "   ,trn0101.reji_no ";
            $sql .= "   ,trn0101.cust_cd ";
            $sql .= "   ,trn0101.cust_nm ";
            //$sql .= "   ,trn0101.account_no ";
            $sql .= "   ,trn0102.prod_cd ";
            $sql .= "   ,regexp_replace (replace(replace(coalesce(trn0102.prod_nm_prn,''),'\"','\\\"'),'''',' '),'/  |\r\n|\n|\r|\t|\&|\b|\f/gm','') as prod_nm_prn ";
            //$sql .= "   ,trn0102.sect_cd ";
            $sql .= "   ,SUM(trn0102.amount)        AS amount ";
            $sql .= "   ,SUM(trn0102.subtotal)      AS subtotal ";
            $sql .= "   ,SUM(trn0102.pure_total)    AS pure_total ";
            //$sql .= "   ,trn0102.sect_sale_kbn ";
            $sql .= "   FROM trn0101 ";
            $sql .= "   JOIN v_organization v ON v.organization_id = trn0101.organization_id ";
            $sql .= "   JOIN trn0102 ON trn0102.hideseq = trn0101.hideseq AND trn0102.organization_id = trn0101.organization_id ";
            $sql .= "   JOIN trn0201 ON trn0201.hideseq = trn0102.hideseq AND trn0201.organization_id = trn0102.organization_id ";
            $sql .= "   WHERE trn0201.credit_kbn = '3' ";
            $sql .= "   AND   trn0101.stop_kbn <> '1' ";
            $sql .= "   AND   trn0102.cancel_kbn <> '1' ";
            $sql .= "   AND   trn0101.return_hideseq <> trn0101.hideseq ";
            $sql .= "   AND   trn0101.lump_return_kbn != '1' ";
            $sql .= "   GROUP BY ";
            $sql .= "    trn0101.proc_date ";
            $sql .= "   ,trn0101.organization_id ";
            $sql .= " 	,v.abbreviated_name ";
            $sql .= "   ,trn0101.reji_no ";
            $sql .= "   ,trn0101.cust_cd ";
            $sql .= "   ,trn0101.cust_nm ";
            $sql .= "   ,trn0102.prod_cd ";
            $sql .= "   ,trn0102.prod_nm_prn ";
            $sql .= "   ORDER BY ";
            $sql .= "    trn0101.proc_date ";
            $sql .= "   ,trn0101.organization_id ";
            $sql .= "   ,trn0101.reji_no ";
            $sql .= "   ,trn0101.cust_cd ";
            $sql .= "   ,trn0102.prod_cd ";
            
           //$searchArray[':val'] = 1 ;
           $searchArray = array();
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
