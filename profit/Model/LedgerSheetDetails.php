<?php
    /**
     * @file      帳票 - 日次
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 日次の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetDetails extends BaseModel
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
            //print_r($searchArray);
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
            //modal
            //print_r($postArray);
            if( $postArray['cust_cd'] !== 'false' )
            {
                $where .= " AND a.cust_cd in (".$postArray['cust_cd'].") ";
            }
            if( $postArray['organization_id'] !== 'false' )
            {
                $where .= " AND d.organization_id in (".$postArray['organization_id'].") ";
            }

            //sort
            $order = ' order by ';
            if($_POST["sort_table"]){
                if (strpos($_POST["sort_table"], 'trndate') !== false) {
                   $order .= $_POST['sort_table'].",org_id,cust_cd,account_no,prod_cd "; 
                }else if(strpos($_POST["sort_table"], 'org_nm') !== false){
                   $order .= 'trndate, '.$_POST["sort_table"].',cust_cd,account_no,prod_cd '; 
                }else if(strpos($_POST["sort_table"], 'cust_cd') !== false || strpos($_POST["sort_table"], 'supp_nm') !== false){
                   $order .= 'trndate,org_id, '.$_POST["sort_table"].',account_no,prod_cd '; 
                }else if(strpos($_POST["sort_table"], 'account_no') !== false || strpos($_POST["sort_table"], 'sect_nm') !== false){
                   $order .= 'trndate,org_id,cust_cd,'.$_POST["sort_table"].',prod_cd '; 
                }else{
                   $order .= 'trndate,org_id,cust_cd,account_no, '.$_POST["sort_table"].',prod_cd '; 
                }
            }else{
                $order .= 'trndate, org_id,cust_cd,account_no,prod_cd ';
            }
 
                     
            $sql  = "   select
                            to_char(date(d.trndate), 'YYYY/MM/DD') as trndate,
                            d.organization_id as org_id,
                            o.abbreviated_name as org_nm,
                            a.cust_cd as cust_cd,
                            a.cust_nm as cust_nm,
                            d.account_no as account_no,	
                            tr2.prod_cd as prod_cd,
                            coalesce(c.prod_nm,prod_kn) as prod_nm,
                            coalesce(nullif(c.prod_capa_nm,''),c.prod_capa_kn) as prod_capa,	
                            coalesce(ms.staff_cd,'') as staff_cd,
                            coalesce(ms1.staff_nm,ms1.staff_kn,'') as staff_nm,
                            sum(tr2.amount) as amount,
                            sum(nebiki+waribiki+mod_nebiki+s_mixmatch_nebiki+s_nebiki+s_waribiki+s_tyoseibiki) as disctotal,
                            sum(tr2.pure_total_i+tr2.itax) as saletotal,
                            tr2.saleprice as saleprice,
                            coalesce(d.point_detail01,0) as point,
                            coalesce(d.point_detail02+d.point_detail03+d.point_detail04+d.point_detail05,0) as point_r
                        from mst0101 a
                        left join trn0101 d on d.cust_cd = a.cust_cd and d.stop_kbn = '0'
                        left join trn0102 tr2 on d.organization_id = tr2.organization_id and tr2.hideseq = d.hideseq and tr2.trndate = d.trndate and tr2.trntime = d.trntime
                        left join v_organization as o on d.organization_id = o.organization_id
                        left join mst0201 c on tr2.prod_cd = c.prod_cd and tr2.organization_id = c.organization_id 
                        left join mst0101_staff ms on ms.organization_id = d.organization_id and ms.cust_cd = d.cust_cd
                        left join mst0601 ms1 on ms1.organization_id = ms.organization_id and ms1.staff_cd = ms.staff_cd
                        where
                                d.trndate >= substr(:start_date||'0000',1,8)	
                            and d.trndate <= substr(:end_date||'9999',1,8)
                            and tr2.cancel_kbn = '0'
                            and d.stop_kbn = '0'  
                            and tr2.prod_cd is not null "; 
            $sql .= $where;
            $sql .= "   group by 
                            d.trndate
                           ,d.organization_id
                           ,o.abbreviated_name
                           ,a.cust_cd
                           ,a.cust_nm
                           ,d.account_no
                           ,tr2.prod_cd
                           ,coalesce(c.prod_nm,prod_kn)
                           ,coalesce(nullif(c.prod_capa_nm,''),c.prod_capa_kn)
                           ,coalesce(ms.staff_cd,'')
                           ,coalesce(ms1.staff_nm,ms1.staff_kn,'')
                           ,coalesce(d.point_detail01,0)
                           ,coalesce(d.point_detail02+d.point_detail03+d.point_detail04+d.point_detail05,0)
                           ,tr2.saleprice ";
            $sql .= $order;
            $searchArray = array_merge($searchArray, array('start_date' => $postArray['start_date'],));
            $searchArray = array_merge($searchArray, array('end_date' => $postArray['end_date'],));
            $Log->trace("END creatSQL");
            //print_r($sql);
            //print_r($ledgerSheetFormIdArray);
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
    }
?>
