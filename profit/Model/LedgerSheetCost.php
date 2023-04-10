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

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetCost extends BaseModel
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
            $Log->trace("START getFormListData");
           
            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getFormListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getFormListData");
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

            $sql = ' SELECT * FROM m_ledger_sheet_form WHERE true ';
                
            // AND条件
            if( !empty($postArray['is_del']) )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['ledger_sheet_form_id'] ) )
            {
                $sql .= ' AND ledger_sheet_form_id = :ledger_sheet_form_id ';
                $ledgerSheetFormIdArray = array(':ledger_sheet_form_id' => $postArray['ledger_sheet_form_id'],);
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
        public function getMcodeListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMcodeListData");
           
            $searchArray = array();
            $sql = $this->creatMSQL( $postArray, $searchArray );

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
        private function creatMSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  SUM(to_number(data,'999999999')) as data FROM t_mcode_data_day WHERE true ";
                
            // AND条件
            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $isDelArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty($postArray['startDate']) )
            {
                $sql .= ' AND target_date >= :startDate ';
                $isDelArray = array(':startDate' => $postArray['startDate'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty($postArray['endDate']) )
            {
                $sql .= ' AND target_date <= :endDate ';
                $isDelArray = array(':endDate' => $postArray['endDate'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['mcode'] ) )
            {
                $sql .= ' AND mcode = :mcode ';
                $ledgerSheetFormIdArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            $sql .= ' group by mcode ';
            
            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 全組織一覧取得
         * @param    $authenKey   認証キー
         * @return   組織名リスト
         */
        public function getAllOrganizationList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getAllOrganizationList");
            
            $sql =  '  SELECT organization_id, organization_name '
                 .  "    FROM v_organization WHERE eff_code = '適用中' "
                 .  "   ORDER BY department_code ";

            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);

            $organizationList = array();
            if( $result === false )
            {
                $Log->trace("END getAllOrganizationList");
                return $organizationList;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($organizationList, $data);
            }

            $Log->trace("END getAllOrganizationList");
            return $organizationList;
        }        

        /**
         * マッピング情報取得
         * @param    $postArray   入力パラメータ(is_del/sort)
         * @return   成功時：$mappingNameList(mapping_name_id/mapping_code/mapping_type/mapping_name/mapping_name_kana/link/list_f/disp_order/is_del)  失敗：無
         */
        public function getMListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatMNSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $mappingNameDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $mappingNameDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $mappingNameDataList, $data);
            }
            
            $Log->trace("END getListData");

            return $mappingNameDataList;
        }

        /**
         * マッピング名一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   マッピング名マスタ一覧取得SQL文
         */
        private function creatMNSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  * "
                 . "        , CASE WHEN mapping_type = 11 THEN '売上'"
                 . "               WHEN mapping_type = 12 THEN 'コスト(日)'"
                 . "               WHEN mapping_type = 13 THEN 'コスト(月)'"
                 . "               WHEN mapping_type = 14 THEN '目標(日)'"
                 . "               WHEN mapping_type = 15 THEN '目標(月)'"
                 . "               WHEN mapping_type = 16 THEN '仕入'"
                 . "               WHEN mapping_type = 17 THEN '勤怠'"
                 . "               WHEN mapping_type = 18 THEN 'その他'"
                 . "               WHEN mapping_type = 19 THEN '商品別'"
                 . "               WHEN mapping_type = 20 THEN '時間別'"
                 . "           END as mapping_type_name"
                 . "        , CASE WHEN link = 0 THEN '自動'"
                 . "               WHEN link = 1 THEN '手動'"
                 . "               WHEN link = 2 THEN '入力'"
                 . "           END as link_name"
                 . "        , CASE WHEN list_f = 0 THEN '表示しない'"
                 . "               WHEN list_f = 1 THEN '表示する'"
                 . "           END as list_f_name"
                 . "   FROM m_mapping_name"
                 . "  WHERE true"  ;
            
            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( $postArray['mcode'] != 0 )
            {
                $sql .= ' AND mapping_code = :mcode ';
                $isDelArray = array(':mcode' => $postArray['mcode'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            $Log->trace("END creatSQL");
            
            return $sql;
        }
        
    }
?>
