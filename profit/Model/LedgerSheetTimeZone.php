<?php
    /**
     * @file      帳票 - 時間帯別
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票 - 時間別の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 時間帯別帳票クラス
     * @note   時間帯別帳票の管理を行う
     */
    class LedgerSheetTimeZone extends BaseModel
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
         * 時間帯別帳票一覧画面一覧表
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
         * 時間帯マスタ取得
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(時間帯マスタデータ)  失敗：無
         */
        public function getTimeZoneListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getTimeZoneListData");
           
            $searchArray = array();
            $sql = $this->creatZSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);
            
            $displayItemDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getTimeZoneListData");
                return $displayItemDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $displayItemDataList, $data);
            }
            
            $Log->trace("END getTimeZoneListData");
            return $displayItemDataList;
        }
        
        /**
         * 時間帯マスタ取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   時間帯マスタ取得SQL文
         */
        private function creatZSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = ' SELECT * FROM m_profit_time_zone WHERE true ';
                
            // AND条件
            if( !empty($postArray['is_del']) )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            if( !empty( $postArray['organization_id'] ) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $organizationIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $organizationIdArray);
            }

            $sql .= ' ORDER BY time_zone_start;';
            
            $Log->trace("END creatSQL");
            return $sql;
        }
        
        /**
         * 時間別帳票一覧表
         * @param    $postArray   入力パラメータ(ledger_sheet_form_id/start_date/end_date/sort)
         * @return   成功時：$displayItemList(帳票フォームデータ)  失敗：無
         */
        public function getTimeData( $postArray )
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
         時間別データ取得SQL文作成
         * @param    $postArray                 入力パラメータ(is_del/ledger_sheet_form_id)
         * @param    $searchArray               検索条件用パラメータ
         * @return   時間別データ取得SQL文
         */
        private function creatMSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  substr(MAX(start_time),1,2) as key"
                    . "     ,SUM(to_number(count,'9999999')) as count"
                    . "     ,SUM(to_number(sales,'9999999')) as sales"
                    . "     ,SUM(to_number(sales_tax,'9999999')) as sales_tax"
                    . "     ,SUM(to_number(guest_count,'9999999')) as guest_count"
                    . "     ,SUM(to_number(group_count,'9999999')) as group_count"
                    . "     ,SUM(to_number(coupon,'9999999')) as coupon"
                    . "     ,SUM(to_number(coupon_tax,'9999999')) as coupon_tax"
                    . "     ,SUM(to_number(coupon_count,'9999999')) as coupon_count"
                    . "   FROM t_mcode_data_time"
                    . "  WHERE true ";

            // AND条件
            if( !empty($postArray['start_time']) )
            {
                $sql .= ' AND substr(start_time,1,2) >= :start_time ';
                $startTimeArray = array(':start_time' => $postArray['start_time'],);
                $searchArray = array_merge($searchArray, $startTimeArray);
            }

            if( !empty($postArray['end_time']) )
            {
                $sql .= ' AND substr(start_time,1,2) < :end_time ';
                $endTimeArray = array(':end_time' => $postArray['end_time'],);
                $searchArray = array_merge($searchArray, $endTimeArray);
            }

            if( !empty($postArray['organization_id']) )
            {
                $sql .= ' AND organization_id = :organization_id ';
                $organizationIdArray = array(':organization_id' => $postArray['organization_id'],);
                $searchArray = array_merge($searchArray, $organizationIdArray);
            }

            if( !empty($postArray['target_date_start']) )
            {
                $sql .= ' AND target_date >= :target_date_start ';
                $targetDateStartArray = array(':target_date_start' => $postArray['target_date_start'],);
                $searchArray = array_merge($searchArray, $targetDateStartArray);
            }

            if( !empty($postArray['target_date_end']) )
            {
                $sql .= ' AND target_date <= :target_date_end ';
                $targetDateEndArray = array(':target_date_end' => $postArray['target_date_end'],);
                $searchArray = array_merge($searchArray, $targetDateEndArray);
            }

            $Log->trace("END creatSQL");
            return $sql;
        }

    }

?>
