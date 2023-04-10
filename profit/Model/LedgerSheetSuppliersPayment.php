<?php
    /**
     * @file      帳票 - 仕入先・店舗別支払一覧表
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 仕入先・店舗別支払一覧表の管理を行う
     */
//*----------------------------------------------------------------------------
//*   修正履歴
//*   修正日      :
//* @m4 2019.03.18 モンターニュ　クエリを再構築する
//*****************************************************************************   
//

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetSuppliersPayment extends BaseModel
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
            //print_r($displayItemDataList);
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            
            $where = '';
            if( !empty( $postArray['start_date'] ) ){
                $searchArray[':start_date'] = str_replace('/','',substr($postArray['start_date'],0,7));
                $searchArray[':shimebi'] = substr($postArray['start_date'],8,2);                
                $where .= " and jsk.proc_ym  = :start_date ";
                $where .= " and m.pay_close_day1 = (case when :shimebi > 27 then '99' else '".$searchArray[':shimebi']."' end) ";
            }
            //modal
            if( $postArray['supp_cd'] !== 'false' )
            {
                $where .= " AND t.supp_cd in (".$postArray['supp_cd'].") ";
            }
            if( $postArray['organization_id'] !== 'false' )
            {
                $where .= " AND t.organization_id in (".$postArray['organization_id'].") ";
            }
            $order = ' order by ';
            if($_POST["sort_table"]){
                if (strpos($_POST["sort_table"], 'proc_ym') !== false) {
                   $order .= $_POST['sort_table']." ,o.organization_id ,supp_cd "; 
                }else if(strpos($_POST["sort_table"], 'abbreviated_name') !== false){
                   $order .= 'proc_ym, '.$_POST["sort_table"].',supp_cd '; 
                }else{
                   $order .= 'proc_ym, organization_id, '.$_POST["sort_table"].',supp_cd '; 
                }
            }else{
                $order .= 'proc_ym, organization_id,supp_cd';
            }

        $sql = "    select
                        m.supp_cd ,
                        m.supp_nm ,
                        jsk.proc_ym as proc_ym,
                        o.organization_id ,
                        o.abbreviated_name ,
                        jsk.bef_balance,
                        jsk.mon_disc_total,
                        jsk.mon_supp_total as subtot1,
                        (jsk.mon_pay_bill_total + jsk.mon_pay_cash_total + jsk.mon_pay_check_total + jsk.mon_pay_coun_total + jsk.mon_pay_others_total + jsk.mon_pay_trans_total) as subtot2,
                        jsk.mon_balance as total,
                        sum(t.cost_money) as cost_money ,
                        sum(t.disc_money) as disc_money ,
                        count(t.hideseq) ,
                        sum(t.return_money) as return_money ,
                        sum(t.tax) as tax ,
                        sum(t.cost_money) - sum(t.disc_money) + sum(t.return_money) as sumcost_money
                    from jsk6510 jsk
                    left join v_organization as o on
                        (jsk.organization_id = o.organization_id)
                    left join mst1101 as m on
                        (jsk.supp_cd = m.supp_cd
                        and jsk.organization_id = m.organization_id)
                    left join trn1101 t on (t.supp_cd = jsk.supp_cd
                        and t.organization_id = jsk.organization_id
                        and jsk.proc_ym||m.pay_close_day1 >= t.stock_date 
                        and coalesce((select proc_ym from jsk6510 where proc_ym < jsk.proc_ym and supp_cd = jsk.supp_cd and  organization_id = jsk.organization_id order by proc_ym desc limit 1),'0') ||m.pay_close_day1 <= t.stock_date)	
                    where
                        1 = 1
                        and t.stock_date > '0'
                        and t.stock_date <=  '99999999' ";
        $sql .= $where;
        $sql .= "   group by
                        o.organization_id,
                        o.abbreviated_name,
                        m.supp_cd,
                        m.supp_nm,
                        jsk.bef_balance,
                        jsk.mon_supp_total,
                        jsk.mon_supp_tax ,
                        jsk.mon_pay_bill_total,
                        jsk.mon_pay_cash_total,
                        jsk.mon_pay_check_total,
                        jsk.mon_pay_coun_total,
                        jsk.mon_pay_others_total,
                        jsk.mon_pay_trans_total,
                        jsk.mon_disc_total,
                        jsk.proc_ym,
                        mon_balance
                        ";
            $sql .= $order;
            $Log->trace("END creatSQL");
            //echo $sql;
            //print_r($searchArray);
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getMcodeData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMcodeData");

            $searchArray = array();
            $sql = $this->creatMSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getMcodeData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getMcodeData");
            return $displayItemDataList;
        }

        /**
         * マッピング情報取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatMSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * '
               .   '   FROM m_pos_mapping'
               .   '  WHERE mapping_name_id in'
               .   '        (SELECT mapping_name_id FROM m_mapping_name WHERE mapping_code = :mcode)';

            // AND条件
            if( !empty( $postArray['mcode'] ) )
            {
                $ledgerSheetFormIdArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getReportDataDay( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getReportDataDay");

            $searchArray = array();
            $sql = $this->creatRdSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getReportDataDay");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getReportDataDay");
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatRdSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM t_report_data_day WHERE true ';

            // AND条件
            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $organizationIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $organizationIdArray);
            }

            if( !empty($postArray['target_date']) )
            {
                $sql .= ' AND target_date = :target_date ';
                $targetDateArray = array(':target_date' => $postArray['target_date'],);
                $searchArray = array_merge($searchArray, $targetDateArray);
            }

            if( !empty( $postArray['report_form_id'] ) )
            {
                $sql .= ' AND report_form_id = :report_form_id ';
                $reportFormIdArray = array(':report_form_id' => $postArray['report_form_id'],);
                $searchArray = array_merge($searchArray, $reportFormIdArray);
            }

            if( !empty( $postArray['report_form_detail_id'] ) )
            {
                $sql .= ' AND report_form_detail_id = :report_form_detail_id ';
                $reportFormDetailIdArray = array(':report_form_detail_id' => $postArray['report_form_detail_id'],);
                $searchArray = array_merge($searchArray, $reportFormDetailIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧画面一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getMcodeListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMcodeListData");

            $searchArray = array();
            $sql = $this->creatMLSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END getMcodeListData");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END getMcodeListData");
            return $displayItemDataList;
        }

        /**
         * 帳票フォーム一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatMLSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM t_mcode_data_day WHERE true ';

            // AND条件
            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $isDelArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty($postArray['target_date']) )
            {
                $sql .= ' AND target_date = :target_date ';
                $isDelArray = array(':target_date' => $postArray['target_date'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['mcode'] ) )
            {
                $sql .= ' AND mcode = :mcode ';
                $ledgerSheetFormIdArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * Mコードチェック
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$マッピング情報  失敗：無
         */
        public function checkMcode( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START checkMcode");

            $searchArray = array();
            $sql = $this->creatMCSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $displayItemDataList = array();

            if( $result === false )
            {
                $Log->trace("END checkMcode");
                return $displayItemDataList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }

            $Log->trace("END checkMcode");
            return $displayItemDataList;
        }

        /**
         * Mコードチェック用SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   帳票フォーム一覧取得SQL文
         */
        private function creatMCSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM m_mapping_name WHERE true ';

            // AND条件
            if( !empty( $postArray['mapping_code'] ) )
            {
                $sql .= ' AND mapping_code = :mapping_code ';
                $mappingCodeArray = array(':mapping_code' => $postArray['mapping_code'],);
                $searchArray = array_merge($searchArray, $mappingCodeArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }
        
        public function get_mst1101_data(){
            
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START get_mst1101_data");
            
            $sql  = '';
            $sql .= "select organization_id,supp_cd,supp_nm ";
            $sql .= " from mst1101 ";
            $sql .= " order by supp_cd,organization_id";
            $searchArray = []; 
            $result = $DBA->executeSQL($sql, $searchArray);

            $data_all = [];

            if( $result === false )
            {
                $Log->trace("END get_mst1101_data");
                return $data_all;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                 if(!$data_all[$data['supp_cd']]){
                     $data_all[$data['supp_cd']]=[];
                 }
                 $data_all[$data['supp_cd']][$data['organization_id']] = $data['supp_nm'];
            }

            $Log->trace("END get_mst1101_data");
            return $data_all;            
        }
// test all client
        public function test(){
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START test");            
            $sql = "    select
                            m.supp_cd ,
                            m.supp_nm ,
                            jsk.proc_ym as proc_ym_jsk,
                            o.organization_id ,
                            o.abbreviated_name ,
                            jsk.bef_balance,
                            jsk.mon_disc_total,
                            jsk.mon_supp_total as subtot1,
                            (jsk.mon_pay_bill_total + jsk.mon_pay_cash_total + jsk.mon_pay_check_total + jsk.mon_pay_coun_total + jsk.mon_pay_others_total + jsk.mon_pay_trans_total) as subtot2,
                            jsk.mon_balance as mon_balance,
                            sum(t.cost_money) as cost_money ,
                            sum(t.disc_money) as disc_money ,
                            count(t.hideseq) ,
                            sum(t.return_money) as return_money ,
                            sum(t.tax) as tax ,
                            sum(t.cost_money) - sum(t.disc_money) + sum(t.return_money) as sumcost_money
                        from jsk6510 jsk
                        left join v_organization as o on
                            (jsk.organization_id = o.organization_id)
                        left join mst1101 as m on
                            (jsk.supp_cd = m.supp_cd
                            and jsk.organization_id = m.organization_id)
                        left join trn1101 t on (t.supp_cd = jsk.supp_cd
                            and t.organization_id = jsk.organization_id
                            and jsk.proc_ym||m.pay_close_day1 >= t.stock_date 
                            and coalesce((select proc_ym from jsk6510 where proc_ym < jsk.proc_ym and supp_cd = jsk.supp_cd and  organization_id = jsk.organization_id order by proc_ym desc limit 1),'0') ||m.pay_close_day1 <= t.stock_date)	
                        where
                            1 = 1
                            and t.stock_date > '0'
                            and t.stock_date <=  '99999999' ";
            $sql .= "   group by
                            o.organization_id,
                            o.abbreviated_name,
                            m.supp_cd,
                            m.supp_nm,
                            jsk.bef_balance,
                            jsk.mon_supp_total,
                            jsk.mon_supp_tax ,
                            jsk.mon_pay_bill_total,
                            jsk.mon_pay_cash_total,
                            jsk.mon_pay_check_total,
                            jsk.mon_pay_coun_total,
                            jsk.mon_pay_others_total,
                            jsk.mon_pay_trans_total,
                            jsk.mon_disc_total,
                            jsk.proc_ym,
                            mon_balance
                        order by
                            m.supp_cd,
                            jsk.proc_ym,
                            o.organization_id"; 
            
            $searchArray = []; 
            $result = $DBA->executeSQL($sql, $searchArray);
            $data_all = [];

            if( $result === false )
            {
                $Log->trace("END test");
                return $data_all;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                 $data_all[] = $data;
            }

            $Log->trace("END test");
            return $data_all;             
        }
    }
?>
