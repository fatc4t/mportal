<?php
    /**
     * @file      帳票 - 商品別
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 商品別の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetItem extends BaseModel
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
        public function getItemData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getMcodeListData");
           
            $searchArray = array();
            $sql = $this->creatISQL( $postArray, $searchArray );

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
        private function creatISQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  plu_code"
                    . "     ,plu_name"
                    . "     ,MAX(price) as price"
                    . "     ,MAX(cost) as cost"
                    . "     ,SUM(to_number(count,'9999999')) as count"
                    . "     ,SUM(to_number(count,'9999999') * price) as sales"
                    . "   FROM t_mcode_data_item"
                    . "  WHERE true ";

            // AND条件
            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $isDelArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty($postArray['target_date_start']) )
            {
                $sql .= ' AND target_date >= :target_date_start ';
                $isDelArray = array(':target_date_start' => $postArray['target_date_start'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty($postArray['target_date_end']) )
            {
                $sql .= ' AND target_date <= :target_date_end ';
                $isDelArray = array(':target_date_end' => $postArray['target_date_end'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['plu_code'] ) )
            {
                $sql .= ' AND plu_code = :plu_code ';
                $ledgerSheetFormIdArray = array(':plu_code' => $postArray['plu_code'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            // 商品コードなしを追加するための条件
            if( !empty( $postArray['plu_name_flg'] ) )
            {
                $sql .= " AND (plu_name = '' or plu_name is null)";
            }
            
            // GROPU BY句を追加
            $sql .= " GROUP BY plu_code,plu_name,price,cost";
            
            // ORDER BY句を追加
            $sql .= " ORDER BY plu_code";

            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 表示項目設定マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   表示項目設定マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = '';

            // ソート条件作成
            $sortSqlList = array(
                                0       =>  ' ORDER BY di.organization_id, di.disp_order, di.display_item_id',                              // 初期表示
                                3       =>  ' ORDER BY di.organization_id DESC, di.disp_order, di.display_item_id',   // 組織名の降順
                                4       =>  ' ORDER BY di.organization_id, di.disp_order, di.display_item_id',        // 組織名の昇順
                                5       =>  ' ORDER BY di.display_item_id DESC, di.organization_id, di.disp_order, di.display_item_id',     // 設定名の降順
                                6       =>  ' ORDER BY di.display_item_id, di.organization_id, di.disp_order, di.display_item_id',          // 設定名の昇順
                                7       =>  ' ORDER BY di.display_format DESC, di.organization_id, di.disp_order, di.display_item_id',      // 時間の表示形式の降順
                                8       =>  ' ORDER BY di.display_format, di.organization_id, di.disp_order, di.display_item_id',           // 時間の表示形式の昇順
                                9       =>  ' ORDER BY di.no_data_format DESC, di.organization_id, di.disp_order, di.display_item_id',      // 時間データなしの降順
                               10       =>  ' ORDER BY di.no_data_format, di.organization_id, di.disp_order, di.display_item_id',           // 時間データなしの昇順
                               11       =>  ' ORDER BY count DESC, di.organization_id, di.disp_order,  di.display_item_id',                 // 表示項目数の降順
                               12       =>  ' ORDER BY count, di.organization_id, di.disp_order,  di.display_item_id',                      // 表示項目数の昇順
                               13       =>  ' ORDER BY di.comment DESC, di.organization_id,  di.display_item_id',                           // コメントの降順
                               14       =>  ' ORDER BY di.comment, di.organization_id,  di.display_item_id',                                // コメントの昇順
                               15       =>  ' ORDER BY di.disp_order DESC, di.organization_id,  di.display_item_id',                        // 表示順の降順
                               16       =>  ' ORDER BY di.disp_order, di.organization_id,  di.display_item_id',                             // 表示順の昇順
                            );
            // ソート条件
            if( array_key_exists( $sortNo, $sortSqlList ) )
            {
                $sql = $sortSqlList[$sortNo];
            }

            $Log->trace("END creatSortSQL");
            return $sql;
        }

    }
?>
