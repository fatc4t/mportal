<?php
    /**
     * @file      商品実績 [M]
     * @author    vergara miguel
     * @date      2019/02/13
     * @version   1.00
     * @note      帳票
     */

    // BaseModel.phpを読み込む

    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class ledgerSheetProductSales extends BaseModel
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

            $where = ' WHERE a.cancel_kbn != $$1$$ AND c.stop_kbn != $$1$$ 
            AND c.lump_return_kbn != $$1$$ AND c.return_hideseq != a.hideseq ';
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' AND date(a.trndate) >= :start_date ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
           
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND date(a.trndate) <= :end_date ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['organization_id'] ) )
            {
                $where .= ' AND b.organization_id = :organization_id ';
                $ledgerSheetFormIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            if( !empty( $postArray['sect_k'] ) )
            {
                $where .= ' AND b.sect_cd >= :sect_k ';
                $ledgerSheetFormIdArray = array(':sect_k' => $postArray['sect_k'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            if( !empty( $postArray['sect_s'] ) )
            {
                $where .= ' AND b.sect_cd <= :sect_s ';
                $ledgerSheetFormIdArray = array(':sect_s' => $postArray['sect_s'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['prod_k'] ) )
            {
                $where .= ' AND a.prod_cd >= :prod_k';
                $ledgerSheetFormIdArray = array(':prod_k' => $postArray['prod_k'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['prod_s'] ) )
            {
                $where .= ' AND a.prod_cd <= :prod_s';
                $ledgerSheetFormIdArray = array(':prod_s' => $postArray['prod_s'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['prod_nm'] ))
            {
                $where .= ' AND b.prod_nm ~ :prod_nm';
                $ledgerSheetFormIdArray = array(':prod_nm' => $postArray['prod_nm'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['maker_cd'] ))
            {
                $where .= ' AND b.maker_cd = :maker_cd';
                $ledgerSheetFormIdArray = array(':maker_cd' => $postArray['maker_cd'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['supp_cd_k'] ) )
            {
                $where .= ' AND b.head_supp_cd >= :supp_cd_k ';
                $ledgerSheetFormIdArray = array(':supp_cd_k' => $postArray['supp_cd_k'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            if( !empty( $postArray['supp_cd_s'] ) )
            {
                $where .= ' AND b.head_supp_cd <= :supp_cd_s ';
                $ledgerSheetFormIdArray = array(':supp_cd_s' => $postArray['supp_cd_s'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            $list_appro="";
            if($_POST['checkbox0'] == '0'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'0'";
            }
            

            if($_POST['checkbox1'] == '1'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'1'";
            }

            if($_POST['checkbox2'] == '2'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'2'";
            }

            if($_POST['checkbox3'] == '3'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'3'";
            }
            
            if($_POST['checkbox4'] == '4'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'4'";
            }

            if($_POST['checkbox5'] == '5'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'5'";
            }

            if($_POST['checkbox6'] == '6'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'6'";
            }

            if($_POST['checkbox7'] == '7'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'7'";
            }

            if($_POST['checkbox8'] == '8'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'8'";
            }

            if($_POST['checkbox9'] == '9'){
                if($list_appro !== ""){$list_appro.=",";}
                $list_appro.="'9'";
            }
            if($list_appro !== "" ){
                $where .= " AND b.appo_prod_kbn  in ($list_appro)"; 
            }

            $sql  = " SELECT 
                    b.sect_cd,
                    a.prod_cd,
                    b.maker_cd AS maker_cd,
                    MAX(CASE WHEN
                    b.prod_nm = ''
                    THEN (SELECT b.prod_kn)
                    ELSE (SELECT b.prod_nm)
                    END) AS prod_nm,
                    MAX(CASE WHEN
                    b.prod_capa_nm = ''
                    THEN (SELECT b.prod_capa_kn)
                    ELSE (SELECT b.prod_capa_nm)
                    END) AS prod_capa,
                    SUM(a.amount) AS amount,
                    SUM(a.subtotal) AS subtotal,
                    AVG(CASE WHEN 
                    a.day_costprice = '0'
                    THEN CAST(b.head_costprice AS INT)
                    ELSE (SELECT a.day_costprice)
                    END) AS avgcostprice,
                    AVG(a.saleprice) AS avgsaleprice,
                    SUM(CASE WHEN
                    a.day_costprice = '0'
                    THEN (SELECT a.subtotal - CAST(b.head_costprice AS INT)) 
                    ELSE (SELECT a.subtotal - a.day_costprice)
                    END) AS arari

            
                    FROM trn0102 a
                    JOIN mst0201 b on a.prod_cd = b.prod_cd AND a.organization_id = b.organization_id 
                    JOIN trn0101 c on a.hideseq = c.hideseq AND a.organization_id = c.organization_id ";
                    $sql .= $where; 
                    $sql .= " GROUP BY a.prod_cd, b.sect_cd, b.maker_cd"; 
                    $sql .= " ORDER BY b.sect_cd, a.prod_cd  ";


                    $Log->trace("END creatSQL");
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
