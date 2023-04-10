<?php
    /**
     * @file      帳票設定マスタ
     * @author    millionet oota
     * @date      2016/06/30
     * @version   1.00
     * @note      帳票設定マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 帳票設定クラス
     * @note   帳票設定マスタテーブルの管理を行う
     */
    class LedgerSheetForm extends BaseModel
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
         * 帳票設定一覧画面一覧表
         * @param    $postArray   入力パラメータ(is_del/sort)
         * @return   成功時：$ledgerSheetFormList  失敗：無
         */
        public function getListData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");

            $searchArray = array();
            $sql = $this->creatSQL( $postArray, $searchArray );

            $result = $DBA->executeSQL($sql, $searchArray);

            $ledgerSheetFormDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $ledgerSheetFormDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ledgerSheetFormDataList, $data);
            }
            
            // No以外でソート実施
            if( $postArray['sort'] >= 3 )
            {
                $ledgerSheetFormList = $ledgerSheetFormDataList;
            }
            else
            {
               // $posBrandList = $this->creatAccessControlledList($_SESSION["REFERENCE"], $posBrandDataList);
                $ledgerSheetFormList = $ledgerSheetFormDataList;
                if( $postArray['sort'] == 1 )
                {
                    $ledgerSheetFormList = array_reverse($ledgerSheetFormList);
                }
            }

            $Log->trace("END getListData");

            return $ledgerSheetFormList;
        }

        /**
         * 帳票設定マスタ新規データ登録
         * @param    $postArray   入力パラメータ(コード/マッピング名/削除フラグ0/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function addNewData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO m_profit_ledger_sheet_form( pos_brand_id'
                . '                      , ledger_sheet_form_code'
                . '                      , ledger_sheet_form_name'
                . '                      , ledger_sheet_form_name_kana'
                . '                      , header'
                . '                      , logic'
                . '                      , c_year'
                . '                      , c_month'
                . '                      , c_day'
                . '                      , c_kikan'
                . '                      , c_staff'
                . '                      , c_staff_bumon'
                . '                      , c_menu'
                . '                      , c_menu_bumon'
                . '                      , c_jikan'
                . '                      , c_jikantai'
                . '                      , c_time1'
                . '                      , c_time2'
                . '                      , c_time3'
                . '                      , c_time4'
                . '                      , c_time5'
                . '                      , c_time6'
                . '                      , c_time7'
                . '                      , c_time8'
                . '                      , c_time9'
                . '                      , c_time10'
                . '                      , c_time11'
                . '                      , c_time12'
                . '                      , c_time13'
                . '                      , c_time14'
                . '                      , c_time15'
                . '                      , c_time16'
                . '                      , c_time17'
                . '                      , c_time18'
                . '                      , c_time19'
                . '                      , c_time20'
                . '                      , c_time21'
                . '                      , c_time22'
                . '                      , c_time23'
                . '                      , c_time24'
                . '                      , disp_order'
                . '                      , is_del'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                        :ledger_sheet_form_code'
                . '                      , :ledger_sheet_form_name'
                . '                      , :ledger_sheet_form_name_kana'
                . '                      , :header'
                . '                      , :logic'
                . '                      , :c_year'
                . '                      , :c_month'
                . '                      , :c_day'
                . '                      , :c_kikan'
                . '                      , :c_staff'
                . '                      , :c_staff_bumon'
                . '                      , :c_menu'
                . '                      , :c_menu_bumon'
                . '                      , :c_jikan'
                . '                      , :c_jikantai'
                . '                      , :c_time1'
                . '                      , :c_time2'
                . '                      , :c_time3'
                . '                      , :c_time4'
                . '                      , :c_time5'
                . '                      , :c_time6'
                . '                      , :c_time7'
                . '                      , :c_time8'
                . '                      , :c_time9'
                . '                      , :c_time10'
                . '                      , :c_time11'
                . '                      , :c_time12'
                . '                      , :c_time13'
                . '                      , :c_time14'
                . '                      , :c_time15'
                . '                      , :c_time16'
                . '                      , :c_time17'
                . '                      , :c_time18'
                . '                      , :c_time19'
                . '                      , :c_time20'
                . '                      , :c_time21'
                . '                      , :c_time22'
                . '                      , :c_time23'
                . '                      , :c_time24'
                . '                      , :disp_order'
                . '                      , :is_del'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :update_user_id'
                . '                      , :update_organization)';

            $parameters = array(
                ':ledger_sheet_form_code'           => $postArray['posBrandId'],
                ':ledger_sheet_form_name'           => $postArray['mappingNameId'],
                ':ledger_sheet_form_name_kana'      => $postArray['logicType'],
                ':header'                           => $postArray['logic'],
                ':logic'                            => $postArray['keta'],
                ':c_year'                           => $postArray['symbol'],
                ':c_month'                          => $postArray['roundType'],
                ':c_day'                            => $postArray['posKeyFileId'],
                ':c_kikan'                          => $postArray['posKeyFileId'],
                ':c_staff'                          => $postArray['posKeyFileId'],
                ':c_staff_bumon'                    => $postArray['posKeyFileId'],
                ':c_menu'                           => $postArray['posKeyFileId'],
                ':c_menu_bumon'                     => $postArray['posKeyFileId'],
                ':c_jikan'                          => $postArray['posKeyFileId'],
                ':c_jikantai'                       => $postArray['posKeyFileId'],
                ':c_time1'                          => $postArray['cTime1'],
                ':c_time2'                          => $postArray['cTime2'],
                ':c_time3'                          => $postArray['cTime3'],
                ':c_time4'                          => $postArray['cTime4'],
                ':c_time5'                          => $postArray['cTime5'],
                ':c_time6'                          => $postArray['cTime6'],
                ':c_time7'                          => $postArray['cTime7'],
                ':c_time8'                          => $postArray['cTime8'],
                ':c_time9'                          => $postArray['cTime9'],
                ':c_time10'                         => $postArray['cTime10'],
                ':c_time11'                         => $postArray['cTime11'],
                ':c_time12'                         => $postArray['cTime12'],
                ':c_time13'                         => $postArray['cTime13'],
                ':c_time14'                         => $postArray['cTime14'],
                ':c_time15'                         => $postArray['cTime15'],
                ':c_time16'                         => $postArray['cTime16'],
                ':c_time17'                         => $postArray['cTime17'],
                ':c_time18'                         => $postArray['cTime18'],
                ':c_time19'                         => $postArray['cTime19'],
                ':c_time20'                         => $postArray['cTime20'],
                ':c_time21'                         => $postArray['cTime21'],
                ':c_time22'                         => $postArray['cTime22'],
                ':c_time23'                         => $postArray['cTime23'],
                ':c_time24'                         => $postArray['cTime24'],
                ':disp_order'                       => $postArray['dispOrder'],
                ':is_del'                           => $postArray['is_del'],
                ':registration_user_id'             => $postArray['user_id'],
                ':registration_organization'        => $postArray['organization'],
                ':update_user_id'                   => $postArray['user_id'],
                ':update_organization'              => $postArray['organization'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "m_pos_mapping" );

            $Log->trace("END addNewData");

            return $ret;
        }

        /**
         * 帳票設定マスタ登録データ修正
         * @param    $postArray   入力パラメータ(マッピング名ID/コード/組織ID/マッピング名/更新順/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function modUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START modUpdateData");

            $sql = 'UPDATE m_profit_ledger_sheet_form SET'
                . '   ledger_sheet_form_code = :ledger_sheet_form_code'
                . ' , ledger_sheet_form_name = :ledger_sheet_form_name'
                . ' , ledger_sheet_form_name_kana = :ledger_sheet_form_name_kana'
                . ' , header = :header'
                . ' , logic = :logic'
                . ' , c_year = :c_year'
                . ' , c_month = :c_month'
                . ' , c_day = :c_day'
                . ' , c_kikan = :c_kikan'
                . ' , c_staff = :c_staff'
                . ' , c_staff_bumon = :c_staff_bumon'
                . ' , c_menu = :c_menu'
                . ' , c_menu_bumon = :c_menu_bumon'
                . ' , c_jikan = :c_jikan'
                . ' , c_jikantai = :c_jikantai'
                . ' , c_time1 = :c_time1'
                . ' , c_time2 = :c_time2'
                . ' , c_time3 = :c_time3'
                . ' , c_time4 = :c_time4'
                . ' , c_time5 = :c_time5'
                . ' , c_time6 = :c_time6'
                . ' , c_time7 = :c_time7'
                . ' , c_time8 = :c_time8'
                . ' , c_time9 = :c_time9'
                . ' , c_time10 = :c_time10'
                . ' , c_time11 = :c_time11'
                . ' , c_time12 = :c_time12'
                . ' , c_time13 = :c_time13'
                . ' , c_time14 = :c_time14'
                . ' , c_time15 = :c_time15'
                . ' , c_time16 = :c_time16'
                . ' , c_time17 = :c_time17'
                . ' , c_time18 = :c_time18'
                . ' , c_time19 = :c_time19'
                . ' , c_time20 = :c_time20'
                . ' , c_time21 = :c_time21'
                . ' , c_time22 = :c_time22'
                . ' , c_time23 = :c_time23'
                . ' , c_time24 = :c_time24'
                . ' , disp_order = :disp_order'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE ledger_sheet_form_id = :ledger_sheet_form_id AND update_time = :update_time ';

            $parameters = array(
                ':ledger_sheet_form_id'            => $postArray['ledgerSheetFormId'],
                ':ledger_sheet_form_code'          => $postArray['ledgerSheetFormCode'],
                ':ledger_sheet_form_name'          => $postArray['ledgerSheetFormName'],
                ':ledger_sheet_form_name_kana'     => $postArray['ledgerSheetFormNameKana'],
                ':header'                          => $postArray['header'],
                ':logic'                           => $postArray['logic'],
                ':c_year'                          => $postArray['cYear'],
                ':c_month'                         => $postArray['cMonth'],
                ':c_day'                           => $postArray['cDay'],
                ':c_kikan'                         => $postArray['cKikan'],
                ':c_staff'                         => $postArray['cStaff'],
                ':c_staff_bumon'                   => $postArray['cStaffBumon'],
                ':c_menu'                          => $postArray['cMenu'],
                ':c_menu_bumon'                    => $postArray['cMenuBumon'],
                ':c_jikan'                         => $postArray['cJikan'],
                ':c_jikantai'                      => $postArray['cJikantai'],
                ':c_time1'                         => $postArray['cTime1'],
                ':c_time2'                         => $postArray['cTime2'],
                ':c_time3'                         => $postArray['cTime3'],
                ':c_time4'                         => $postArray['cTime4'],
                ':c_time5'                         => $postArray['cTime5'],
                ':c_time6'                         => $postArray['cTime6'],
                ':c_time7'                         => $postArray['cTime7'],
                ':c_time8'                         => $postArray['cTime8'],
                ':c_time9'                         => $postArray['cTime9'],
                ':c_time10'                        => $postArray['cTime10'],
                ':c_time11'                        => $postArray['cTime11'],
                ':c_time12'                        => $postArray['cTime12'],
                ':c_time13'                        => $postArray['cTime13'],
                ':c_time14'                        => $postArray['cTime14'],
                ':c_time15'                        => $postArray['cTime15'],
                ':c_time16'                        => $postArray['cTime16'],
                ':c_time17'                        => $postArray['cTime17'],
                ':c_time18'                        => $postArray['cTime18'],
                ':c_time19'                        => $postArray['cTime19'],
                ':c_time20'                        => $postArray['cTime20'],
                ':c_time21'                        => $postArray['cTime21'],
                ':c_time22'                        => $postArray['cTime22'],
                ':c_time23'                        => $postArray['cTime23'],
                ':c_time24'                        => $postArray['cTime24'],
                ':disp_order'                      => $postArray['dispOrder'],
                ':update_user_id'                  => $postArray['user_id'],
                ':update_organization'             => $postArray['organization'],
                ':update_time'                     => $postArray['updateTime'],
            );

            // 更新SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END modUpdateData");

            return $ret;
        }

        /**
         * 帳票設定マスタ登録データ削除
         * @param    $postArray   入力パラメータ(マッピング名ID/削除フラグ1/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        public function delUpdateData( $postArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START delUpdateData");

/* 使用中のチェック後で追加予定 2016/07/19 
            $id_name = "pos_brand_id";
            $inUseArray = $this->getInUseCheckList($id_name);
            $intPosBrandId = intval($postArray['posBrandID']);
            if(in_array($intPosBrandId, $inUseArray))
            {
                return "MSG_WAR_2101";
            }
*/
            $sql = 'UPDATE m_profit_ledger_sheet_form SET'
                . '   is_del = :is_del'
                . ' , update_time = current_timestamp'
                . ' , update_user_id = :update_user_id'
                . ' , update_organization = :update_organization'
                . ' WHERE ledger_sheet_form_id = :ledger_sheet_form_id AND update_time = :update_time ';

            $parameters = array(
                ':ledger_sheet_form_id'      => $postArray['ledgerSheetFormId'],
                ':is_del'                    => $postArray['is_del'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization'],
                ':update_time'               => $postArray['updateTime'],
            );

            // 削除SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters );

            $Log->trace("END delUpdateData");

            return $ret;
        }

        /**
         * 帳票設定一覧取得SQL文作成
         * @param    $postArray                 入力パラメータ(organization_id/is_del/organizationID/sectionName/sort)
         * @param    $searchArray               検索条件用パラメータ
         * @return   マッピング名マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");

            $sql = " SELECT  *"
                 . "   FROM m_profit_ledger_sheet_form"
                 . "  WHERE true"  ;
            
            if( !empty( $postArray['ledgerSheetFormCode'] ) )
            {
                $sql .= " AND ledger_sheet_form_code LIKE :ledgerSheetFormCode";
                $ledgerSheetFormArray = array(':ledgerSheetFormCode' => '%'.$postArray['ledgerSheetFormCode'].'%',);
                $searchArray = array_merge($searchArray, $ledgerSheetFormArray);
            }
            
            if( !empty( $postArray['ledgerSheetFormName'] ) )
            {
                $sql .= ' AND ledger_sheet_form_name LIKE :ledgerSheetFormName ';
                $ledgerSheetFormArray = array(':ledgerSheetFormName' => '%'.$postArray['ledgerSheetFormName'].'%',);
                $searchArray = array_merge($searchArray, $ledgerSheetFormArray);
            }

            if( $postArray['is_del'] == 0 )
            {
                $sql .= ' AND is_del = :is_del ';
                $isDelArray = array(':is_del' => $postArray['is_del'],);
                $searchArray = array_merge($searchArray, $isDelArray);
            }

            $sql .= $this->creatSortSQL( $postArray['sort'] );

            $Log->trace("END creatSQL");
            
            return $sql;
        }

        /**
         * 帳票設定マスタ一覧の表示順のSQL文
         * @param    $sortNo             ソートNo
         * @return   帳票設定マスタ一覧取得SQL文（ORDER BY）
         */
        private function creatSortSQL( $sortNo )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSortSQL");

            $sql = ' ORDER BY disp_order';

            // ソート条件作成
            $sortSqlList = array(
                                3       =>  ' ORDER BY disp_order DESC',                   // 表示順の降順
                                4       =>  ' ORDER BY disp_order',                        // 表示順の昇順
                                5       =>  ' ORDER BY ledger_sheet_form_code DESC',       // 帳票設定コードの降順
                                6       =>  ' ORDER BY ledger_sheet_form_code',            // 帳票設定コードの昇順
                                7       =>  ' ORDER BY ledger_sheet_form_name DESC',       // 帳票設定名の降順
                                8       =>  ' ORDER BY ledger_sheet_form_name',            // 帳票設定名の昇順
                                9       =>  ' ORDER BY ledger_sheet_form_name_kana DESC',  // 帳票設定名かなの降順
                               10       =>  ' ORDER BY ledger_sheet_form_name_kana',       // 帳票設定名かなの昇順
                               11       =>  ' ORDER BY header DESC',                       // ヘッダー情報の降順
                               12       =>  ' ORDER BY header',                            // ヘッダー情報の昇順
                               13       =>  ' ORDER BY logic DESC',                        // 表示ロジックの降順
                               14       =>  ' ORDER BY logic',                             // 表示ロジックの昇順
                               15       =>  ' ORDER BY c_year DESC',                       // 集計種別-年の降順
                               16       =>  ' ORDER BY c_year',                            // 集計種別-年の昇順
                               17       =>  ' ORDER BY c_month DESC ',                     // 集計種別-月の降順
                               18       =>  ' ORDER BY c_month',                           // 集計種別-月の昇順
                               19       =>  ' ORDER BY c_day DESC',                        // 集計種別-日の降順
                               20       =>  ' ORDER BY c_day',                             // 集計種別-日の昇順
                               21       =>  ' ORDER BY c_kikan DESC',                      // 集計種別-期間の降順
                               22       =>  ' ORDER BY c_kikan',                           // 集計種別-期間の昇順
                               23       =>  ' ORDER BY c_staff DESC',                      // 集計種別-スタッフの降順
                               24       =>  ' ORDER BY c_staff',                           // 集計種別-スタッフの昇順
                               25       =>  ' ORDER BY c_staff_bumon DESC',                // 集計種別-スタッフ部門の降順
                               26       =>  ' ORDER BY c_staff_bumon',                     // 集計種別-スタッフ部門の昇順
                               27       =>  ' ORDER BY c_menu DESC',                       // 集計種別-メニューの降順
                               28       =>  ' ORDER BY c_menu',                            // 集計種別-メニューの昇順
                               29       =>  ' ORDER BY c_menu_bumon DESC',                 // 集計種別-メニュー部門の降順
                               30       =>  ' ORDER BY c_menu_bumon',                      // 集計種別-メニュー部門の昇順
                               31       =>  ' ORDER BY c_jikan DESC',                      // 集計種別-時間の降順
                               32       =>  ' ORDER BY c_jikan',                           // 集計種別-時間の昇順
                               33       =>  ' ORDER BY c_jikantai DESC',                   // 集計種別-時間帯の降順
                               34       =>  ' ORDER BY c_jikantai',                        // 集計種別-時間帯の昇順
                               35       =>  ' ORDER BY c_time1 DESC',                      // 表示時間キー1の降順
                               36       =>  ' ORDER BY c_time1',                           // 表示時間キー1の昇順
                               37       =>  ' ORDER BY c_time2 DESC',                      // 表示時間キー2の降順
                               38       =>  ' ORDER BY c_time2',                           // 表示時間キー2の昇順
                               39       =>  ' ORDER BY c_time3 DESC',                      // 表示時間キー3の降順
                               40       =>  ' ORDER BY c_time3',                           // 表示時間キー3の昇順
                               41       =>  ' ORDER BY c_time4 DESC',                      // 表示時間キー4の降順
                               42       =>  ' ORDER BY c_time4',                           // 表示時間キー4の昇順
                               43       =>  ' ORDER BY c_time5 DESC',                      // 表示時間キー5の降順
                               44       =>  ' ORDER BY c_time5',                           // 表示時間キー5の昇順
                               45       =>  ' ORDER BY c_time6 DESC',                      // 表示時間キー6の降順
                               46       =>  ' ORDER BY c_time6',                           // 表示時間キー6の昇順
                               47       =>  ' ORDER BY c_time7 DESC',                      // 表示時間キー7の降順
                               48       =>  ' ORDER BY c_time7',                           // 表示時間キー7の昇順
                               49       =>  ' ORDER BY c_time8 DESC',                      // 表示時間キー8の降順
                               50       =>  ' ORDER BY c_time8',                           // 表示時間キー8の昇順
                               51       =>  ' ORDER BY c_time9 DESC',                      // 表示時間キー9の降順
                               52       =>  ' ORDER BY c_time9',                           // 表示時間キー9の昇順
                               53       =>  ' ORDER BY c_time10 DESC',                     // 表示時間キー10の降順
                               54       =>  ' ORDER BY c_time10',                          // 表示時間キー10の昇順
                               55       =>  ' ORDER BY c_time11 DESC',                     // 表示時間キー11の降順
                               56       =>  ' ORDER BY c_time11',                          // 表示時間キー11の昇順
                               57       =>  ' ORDER BY c_time12 DESC',                     // 表示時間キー12の降順
                               58       =>  ' ORDER BY c_time12',                          // 表示時間キー12の昇順
                               59       =>  ' ORDER BY c_time13 DESC',                     // 表示時間キー13の降順
                               60       =>  ' ORDER BY c_time13',                          // 表示時間キー13の昇順
                               61       =>  ' ORDER BY c_time14 DESC',                     // 表示時間キー14の降順
                               62       =>  ' ORDER BY c_time14',                          // 表示時間キー14の昇順
                               63       =>  ' ORDER BY c_time15 DESC',                     // 表示時間キー15の降順
                               64       =>  ' ORDER BY c_time15',                          // 表示時間キー15の昇順
                               65       =>  ' ORDER BY c_time16 DESC',                     // 表示時間キー16の降順
                               66       =>  ' ORDER BY c_time16',                          // 表示時間キー16の昇順
                               67       =>  ' ORDER BY c_time17 DESC',                     // 表示時間キー17の降順
                               68       =>  ' ORDER BY c_time17',                          // 表示時間キー17の昇順
                               69       =>  ' ORDER BY c_time18 DESC',                     // 表示時間キー18の降順
                               70       =>  ' ORDER BY c_time18',                          // 表示時間キー18の昇順
                               71       =>  ' ORDER BY c_time19 DESC',                     // 表示時間キー19の降順
                               72       =>  ' ORDER BY c_time19',                          // 表示時間キー19の昇順
                               73       =>  ' ORDER BY c_time20 DESC',                     // 表示時間キー20の降順
                               74       =>  ' ORDER BY c_time20',                          // 表示時間キー20の昇順
                               75       =>  ' ORDER BY c_time21 DESC',                     // 表示時間キー21の降順
                               76       =>  ' ORDER BY c_time21',                          // 表示時間キー21の昇順
                               77       =>  ' ORDER BY c_time22 DESC',                     // 表示時間キー22の降順
                               78       =>  ' ORDER BY c_time22',                          // 表示時間キー22の昇順
                               79       =>  ' ORDER BY c_time23 DESC',                     // 表示時間キー23の降順
                               80       =>  ' ORDER BY c_time23',                          // 表示時間キー23の昇順
                               81       =>  ' ORDER BY c_time24 DESC',                     // 表示時間キー24の降順
                               82       =>  ' ORDER BY c_time24',                          // 表示時間キー24の昇順
                               83       =>  ' ORDER BY registration_time DESC',            // 初回登録日の降順
                               84       =>  ' ORDER BY registration_time',                 // 初回登録日の昇順
                               85       =>  ' ORDER BY update_time DESC',                  // 更新日の降順
                               86       =>  ' ORDER BY update_time',                       // 更新日の昇順
                               87       =>  ' ORDER BY is_del DESC',                       // 状態の降順
                               88       =>  ' ORDER BY is_del',                            // 状態の昇順
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
