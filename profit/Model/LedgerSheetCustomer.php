<?php
    /**
     * @file      帳票 - 顧客台帳
     * @author    millionet oota
     * @date      2018/06/04
     * @version   1.00
     * @note      帳票 - 顧客台帳の管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';
    require '../modal/Model/Modal.php';    

    /**
     * 表示項目設定クラス
     * @note   表示項目設定マスタテーブルの管理を行う
     */
    class LedgerSheetCustomer extends BaseModel
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
            
            //print_r("$sql \n");
            //print_r($searchArray);
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

            $where = '';
            $where2 = 'where 1 = 1 ';
            $where3 = 'where 1 = 1 ';
            $limit = '';
            $order = 'order by m.cust_cd';
            
            // 期間
            if( !empty( $postArray['start_date'] ) )
            {
                $where .= ' where proc_ym >= :start_date ';
                $ledgerSheetFormIdArray = array(':start_date' => $postArray['start_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            if( !empty( $postArray['end_date'] ) )
            {
                $where .= ' AND proc_ym <= :end_date ';
                $ledgerSheetFormIdArray = array(':end_date' => $postArray['end_date'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }
            
            // 全てに該当するの場合
            if ($postArray['conditions'] == '1'){
                // お買上金額
                if( !empty( $postArray['total_st'] ) )
                {
                    $where2 .= ' AND j4.total >= :total_st ';
                    $ledgerSheetFormIdArray = array(':total_st' => $postArray['total_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['total_end'] ) )
                {
                    $where2 .= ' AND j4.total <= :total_end ';
                    $ledgerSheetFormIdArray = array(':total_end' => $postArray['total_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 粗利金額
                if( !empty( $postArray['profit_st'] ) )
                {
                    $where2 .= ' AND j4.profit >= :profit_st ';
                    $ledgerSheetFormIdArray = array(':profit_st' => $postArray['profit_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['profit_end'] ) )
                {
                    $where2 .= ' AND j4.profit <= :profit_end ';
                    $ledgerSheetFormIdArray = array(':profit_end' => $postArray['profit_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 得点
                if( !empty( $postArray['cust_point_st'] ) )
                {
                    $where2 .= ' AND j4.cust_point >= :cust_point_st ';
                    $ledgerSheetFormIdArray = array(':cust_point_st' => $postArray['cust_point_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['cust_point_end'] ) )
                {
                    $where2 .= ' AND j4.cust_point <= :cust_point_end ';
                    $ledgerSheetFormIdArray = array(':cust_point_end' => $postArray['cust_point_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 来店回数
                if( !empty( $postArray['visit_cnt_st'] ) )
                {
                    $where2 .= ' AND j4.visit_cnt >= :visit_cnt_st ';
                    $ledgerSheetFormIdArray = array(':visit_cnt_st' => $postArray['visit_cnt_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['visit_cnt_end'] ) )
                {
                    $where2 .= ' AND j4.visit_cnt <= :visit_cnt_end ';
                    $ledgerSheetFormIdArray = array(':visit_cnt_end' => $postArray['visit_cnt_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 最終来店日
                if( !empty( $postArray['start_last_date'] ) )
                {
                    $where2 .= ' AND j3.last_date >= :start_last_date ';
                    $ledgerSheetFormIdArray = array(':start_last_date' => $postArray['start_last_date'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_last_date'] ) )
                {
                    $where2 .= ' AND j3.last_date <= :end_last_date ';
                    $ledgerSheetFormIdArray = array(':end_last_date' => $postArray['end_last_date'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 最終来店
                if( !empty( $postArray['start_last_dateM'] ) )
                {
                    $where2 .= ' AND j3.last_date <= :start_last_dateM ';
                    $ledgerSheetFormIdArray = array(':start_last_dateM' => date('Ymd',strtotime('-'.$postArray['start_last_dateM'].' month')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_last_dateM'] ) )
                {
                    $where2 .= ' AND j3.last_date >= :end_last_dateM ';
                    $ledgerSheetFormIdArray = array(':end_last_dateM' => date('Ymd',strtotime('-'.$postArray['end_last_dateM'].' month')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 誕生日
                if( !empty( $postArray['start_lbirth'] ) )
                {
                    $where2 .= ' AND m.birth >= :start_lbirth ';
                    $ledgerSheetFormIdArray = array(':start_lbirth' => $postArray['start_lbirth'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_lbirth'] ) )
                {
                    $where2 .= ' AND m.birth <= :end_lbirth ';
                    $ledgerSheetFormIdArray = array(':end_lbirth' => $postArray['end_lbirth'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 誕生月
                if( !empty( $postArray['birth_month'] ) )
                {
                    $where2 .= ' AND m.birth_month in ('.$postArray['birth_month'].') ';
                }

                // 年齢
                if( !empty( $postArray['start_age'] ) )
                {
                    $where2 .= " AND m.birth !=''  AND m.birth is not null AND m.birth <= :start_age ";
                    $ledgerSheetFormIdArray = array(':start_age' => date('Ymd',strtotime('-'.$postArray['start_age'].' year')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_age'] ) )
                {
                    $where2 .= " AND m.birth !=''  AND m.birth is not null AND m.birth >= :end_age ";
                    $ledgerSheetFormIdArray = array(':end_age' => date('Ymd',strtotime('-'.$postArray['end_age'].' year')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 性別
                if( !empty( $postArray['sex'] ) )
                {
                    $where2 .= " AND m.sex in (".$postArray['sex'].') ';

                }

                // 顧客コード
                if( !empty( $postArray['start_cust_cd'] ) )
                {
                    $where2 .= ' AND m.cust_cd >= :start_cust_cd ';
                    $ledgerSheetFormIdArray = array(':start_cust_cd' => $postArray['start_cust_cd'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_cust_cd'] ) )
                {
                    $where2 .= ' AND m.cust_cd <= :end_cust_cd ';
                    $ledgerSheetFormIdArray = array(':end_cust_cd' => $postArray['end_cust_cd'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 顧客名
                if( !empty( $postArray['cust_nm'] ) )
                {
                    $where2 .= " AND m.cust_nm like :cust_nm ";
                    $ledgerSheetFormIdArray = array(':cust_nm' => '%'.$postArray['cust_nm'].'%',);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 住所指定
                if( !empty( $postArray['addr1'] ) )
                {
                    $where2 .= " AND m.addr1 like :addr1 ";
                    $ledgerSheetFormIdArray = array(':addr1' => '%'.$postArray['addr1'].'%',);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
    // modal       
                // 店舗
                if(  $postArray['organization_id'] !== 'false' )
                {
                    $where2 .= ' AND j2.organization_id in ( '.$postArray['organization_id'].' )';
                }
                //商品
                if( $postArray['prod_cd'] !== 'false' )
                {
                    $where2 .= ' AND j2.prod_cd in ( '.$postArray['prod_cd'].' )';
                }                
            }else{
                // お買上金額
                if( !empty( $postArray['total_st'] ) )
                {
                    $where2 .= ' OR j4.total >= :total_st ';
                    $ledgerSheetFormIdArray = array(':total_st' => $postArray['total_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['total_end'] ) )
                {
                    $where2 .= ' OR j4.total <= :total_end ';
                    $ledgerSheetFormIdArray = array(':total_end' => $postArray['total_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 粗利金額
                if( !empty( $postArray['profit_st'] ) )
                {
                    $where2 .= ' OR j4.profit >= :profit_st ';
                    $ledgerSheetFormIdArray = array(':profit_st' => $postArray['profit_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['profit_end'] ) )
                {
                    $where2 .= ' OR j4.profit <= :profit_end ';
                    $ledgerSheetFormIdArray = array(':profit_end' => $postArray['profit_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 得点
                if( !empty( $postArray['cust_point_st'] ) )
                {
                    $where2 .= ' OR j4.cust_point >= :cust_point_st ';
                    $ledgerSheetFormIdArray = array(':cust_point_st' => $postArray['cust_point_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['cust_point_end'] ) )
                {
                    $where2 .= ' OR j4.cust_point <= :cust_point_end ';
                    $ledgerSheetFormIdArray = array(':cust_point_end' => $postArray['cust_point_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 来店回数
                if( !empty( $postArray['visit_cnt_st'] ) )
                {
                    $where2 .= ' OR j4.visit_cnt >= :visit_cnt_st ';
                    $ledgerSheetFormIdArray = array(':visit_cnt_st' => $postArray['visit_cnt_st'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['visit_cnt_end'] ) )
                {
                    $where2 .= ' OR j4.visit_cnt <= :visit_cnt_end ';
                    $ledgerSheetFormIdArray = array(':visit_cnt_end' => $postArray['visit_cnt_end'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 最終来店日
                if( !empty( $postArray['start_last_date'] ) )
                {
                    $where2 .= ' OR j3.last_date >= :start_last_date ';
                    $ledgerSheetFormIdArray = array(':start_last_date' => $postArray['start_last_date'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_last_date'] ) )
                {
                    $where2 .= ' OR j3.last_date <= :end_last_date ';
                    $ledgerSheetFormIdArray = array(':end_last_date' => $postArray['end_last_date'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 最終来店
                if( !empty( $postArray['start_last_dateM'] ) )
                {
                    $where2 .= ' OR j3.last_date <= :start_last_dateM ';
                    $ledgerSheetFormIdArray = array(':start_last_dateM' => date('Ymd',strtotime('-'.$postArray['start_last_dateM'].' month')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_last_dateM'] ) )
                {
                    $where2 .= ' OR j3.last_date >= :end_last_dateM ';
                    $ledgerSheetFormIdArray = array(':end_last_dateM' => date('Ymd',strtotime('-'.$postArray['end_last_dateM'].' month')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 誕生日
                if( !empty( $postArray['start_lbirth'] ) )
                {
                    $where2 .= ' OR m.birth >= :start_lbirth ';
                    $ledgerSheetFormIdArray = array(':start_lbirth' => $postArray['start_lbirth'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_lbirth'] ) )
                {
                    $where2 .= ' OR m.birth <= :end_lbirth ';
                    $ledgerSheetFormIdArray = array(':end_lbirth' => $postArray['end_lbirth'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 誕生月
                if( !empty( $postArray['birth_month'] ) )
                {
                    $where2 .= ' OR m.birth_month in ('.$postArray['birth_month'].') ';
                }

                // 年齢
                if( !empty( $postArray['start_age'] ) )
                {
                    $where2 .= " OR m.birth !=''  AND m.birth is not null AND m.birth <= :start_age ";
                    $ledgerSheetFormIdArray = array(':start_age' => date('Ymd',strtotime('-'.$postArray['start_age'].' year')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_age'] ) )
                {
                    $where2 .= " OR m.birth !=''  AND m.birth is not null AND m.birth >= :end_age ";
                    $ledgerSheetFormIdArray = array(':end_age' => date('Ymd',strtotime('-'.$postArray['end_age'].' year')) ,);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 性別
                if( !empty( $postArray['sex'] ) )
                {
                    $where2 .= ' OR m.sex in ('.$postArray['sex'].') ';
                }

                // 顧客コード
                if( !empty( $postArray['start_cust_cd'] ) )
                {
                    $where2 .= ' OR m.birth >= :start_cust_cd ';
                    $ledgerSheetFormIdArray = array(':start_cust_cd' => $postArray['start_cust_cd'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                if( !empty( $postArray['end_cust_cd'] ) )
                {
                    $where2 .= ' OR m.birth <= :end_cust_cd ';
                    $ledgerSheetFormIdArray = array(':end_cust_cd' => $postArray['end_cust_cd'],);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 顧客名
                if( !empty( $postArray['cust_nm'] ) )
                {
                    $where2 .= " OR m.cust_nm like :cust_nm ";
                    $ledgerSheetFormIdArray = array(':cust_nm' => '%'.$postArray['cust_nm'].'%',);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }

                // 住所指定
                if( !empty( $postArray['addr1'] ) )
                {
                    $where2 .= " OR m.addr1 like :addr1 ";
                    $ledgerSheetFormIdArray = array(':addr1' => '%'.$postArray['addr1'].'%',);
                    $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
                }
    // modal       
                // 店舗
                if(  $postArray['organization_id'] !== 'false' )
                {
                    $where2 .= ' OR j2.organization_id in ( '.$postArray['organization_id'].' )';
                }
                //商品
                if( $postArray['prod_cd'] !== 'false' )
                {
                    $where2 .= ' OR j2.prod_cd in ( '.$postArray['prod_cd'].' )';
                }                
            }

            // 並び順
            if( !empty( $postArray['sort'] )){
                if($postArray['sort'] == '1'){
                    $order = ' order by m.cust_cd desc';
                }else if ($postArray['sort'] == '2'){
                    $order = ' order by m.cust_cd';
                }else if ($postArray['sort'] == '3'){
                    $order = ' order by m.cust_nm desc';
                }else if ($postArray['sort'] == '4'){
                    $order = ' order by m.cust_nm';
                }else if ($postArray['sort'] == '5'){
                    $order = ' order by m.zip desc';
                }else if ($postArray['sort'] == '6'){
                    $order = ' order by m.zip';
                }else if ($postArray['sort'] == '7'){
                    $order = ' order by m.addr1 desc';
                }else if ($postArray['sort'] == '8'){
                    $order = ' order by m.addr1';
                }else if ($postArray['sort'] == '9'){
                    $order = ' order by m.addr2 desc';
                }else if ($postArray['sort'] == '10'){
                    $order = ' order by m.addr2';
                }else if ($postArray['sort'] == '11'){
                    $order = ' order by m.tel desc';
                }else if ($postArray['sort'] == '12'){
                    $order = ' order by m.tel';
                }else if ($postArray['sort'] == '13'){
                    $order = ' order by insdatetime desc';
                }else if ($postArray['sort'] == '14'){
                    $order = ' order by insdatetime';
                }else if ($postArray['sort'] == '15'){
                    $order = ' order by j4.total desc';
                }else if ($postArray['sort'] == '16'){
                    $order = ' order by j4.total';
                }else if ($postArray['sort'] == '17'){
                    $order = ' order by j4.profit desc';
                }else if ($postArray['sort'] == '18'){
                    $order = ' order by j4.profit';
                }else if ($postArray['sort'] == '19'){
                    $order = ' order by j4.cust_point desc';
                }else if ($postArray['sort'] == '20'){
                    $order = ' order by j4.cust_point';
                }else if ($postArray['sort'] == '21'){
                    $order = ' order by j4.visit_cnt desc';
                }else if ($postArray['sort'] == '22'){
                    $order = ' order by j4.visit_cnt';
                }else if ($postArray['sort'] == '23'){
                    $order = ' order by j3.last_date desc';
                }else if ($postArray['sort'] == '24'){
                    $order = ' order by j3.last_date';
                }
            }
            
            // 抽出件数
            if( !empty( $postArray['line_cnt'] ) )
            {
                $limit .= ' limit :line_cnt ';
                $ledgerSheetFormIdArray = array(':line_cnt' => $postArray['line_cnt'],);
                $searchArray = array_merge($searchArray, $ledgerSheetFormIdArray);
            }

            // 顧客名未入力
            if( !empty( $postArray['client_no'] ) )
            {
                if($postArray['client_no'] == 2){
                    $where2 .= " AND m.cust_nm <> '' ";
                }
            }

            // 住所未入力
            if( !empty( $postArray['addr_no'] ) )
            {
                if($postArray['addr_no'] == 2){
                    $where2 .= " AND m.addr1 <> '' ";
                }
            }

            // DM発送区分
            if( !empty( $postArray['dm_no'] ) )
            {
                if($postArray['dm_no'] == 2){
                    $where2 .= " AND m.dissenddm = '1' ";
                }else if($postArray['dm_no'] == 1){
                    $where2 .= " AND m.dissenddm = '0' ";
                }
            }

            $sql  = " SELECT  distinct(m.cust_cd) ";
            $sql .= "        ,m.cust_nm ";
            $sql .= "        ,m.zip ";
            $sql .= "        ,m.addr1 ";
            $sql .= "        ,m.addr2 ";
            $sql .= "        ,m.tel ";
            $sql .= "        ,to_char(m.insdatetime ,'yyyy-mm-dd' ) as insdatetime ";
            $sql .= "        ,j4.total ";
            $sql .= "        ,j4.profit ";
            $sql .= "        ,j4.cust_point ";
            $sql .= "        ,j4.visit_cnt ";
            $sql .= "        ,j3.last_date ";
            $sql .= "     FROM (select cust_cd, sum(total) as total, sum(profit) as profit, sum(cust_point) as cust_point, sum(visit_cnt) as visit_cnt from jsk4140 ";
            $sql .= $where;
            $sql .= "   group by cust_cd ) as j4 ";
            $sql .= "   left join jsk4130 j3 on ( j4.cust_cd = j3.cust_cd ) ";
            $sql .= "   left join mst0101 m on ( j4.cust_cd = m.cust_cd ) ";
            $sql .= "   left join jsk4120 j2 on (j2.cust_cd = j4.cust_cd ) ";
            $sql .= $where2;
            $sql .= $order;
            $sql .= $limit;
            
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
