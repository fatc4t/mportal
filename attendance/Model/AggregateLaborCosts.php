<?php
    /**
     * @file      売上管理向け、人件費/労働時間集計クラス
     * @author    USE Y.Sakata
     * @date      2016/08/18
     * @version   1.00
     * @note      売上管理向けに、期間指定の人件費/労働時間結果を返す
     */

    // BaseModel.phpを読み込む
    require_once './Model/Common/BaseModel.php';

    /**
     * 人件費/労働時間集計クラス
     * @note   人件費/労働時間集計に必要なDBアクセスの制御を行う
     */
    class AggregateLaborCosts extends BaseModel
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
         * ユーザ勤務単位の未集計情報
         * @param    $userIDList       取得対象のユーザID (array型)
         * @param    $orgIDList        取得対象の打刻場所の組織ID (array型)
         * @param    $startDate        集計開始日 (例：2016/05/01)
         * @param    $endDate          集計終了日 (例：2016/06/01)
         * @return   集計結果のArray
         */
        public function getUserLaborDailyData( $userIDList, $orgIDList, $startDate, $endDate )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getUserLaborDailyData");

            $sql  =  " SELECT "
                 .   "     va.user_id "
                 .   "   , va.user_name "
                 .   "   , va.employees_no "
                 .   "   , va.date "
                 .   "   , va.embossing_abbreviated_name "
                 .   "   , va.embossing_status "
                 .   "   , va.total_working_time_con_minute as total_working "
                 .   "   , va.shift_working_time_con_minute as shift_working "
                 .   "   , va.attendance_time as total_working_user_count "
                 .   "   , va.shift_attendance_time as shift_working_user_count "
                 .   "   , va.rough_estimate as rough_estimate "
                 .   "   , va.shift_rough_estimate as shift_rough_estimate "
                 .   " FROM v_attendance_record va "
                 .   " WHERE :startDate <= date AND date < :endDate AND is_del = 0 ";
            
            // ユーザ指定がある場合、条件文を追加する
            if( count( $userIDList ) > 0  )
            {
                $sql .= " AND va.user_id IN ( ";
                foreach( $userIDList as $userID )
                {
                    $sql .= $userID . ",";
                }
                
                // 最後のカンマを削除(最後のカンマ以外の文字列を再度入れる)
                $sql = substr($sql, 0, -1);
                
                $sql .= ") ";
            }

            // 組織指定がある場合、条件文を追加する
            if( count( $orgIDList ) > 0  )
            {
                $sql .= " AND va.embossing_organization_id IN ( ";
                foreach( $orgIDList as $orgID )
                {
                    $sql .= $orgID . ",";
                }
                
                // 最後のカンマを削除(最後のカンマ以外の文字列を再度入れる)
                $sql = substr($sql, 0, -1);
                
                $sql .= ") ";
            }
            
            // ORDER BYを指定
            $sql .=  " ORDER BY employees_no, date ";

            $parameters = array(
                                ':startDate'    =>  $startDate,
                                ':endDate'      =>  $endDate,
                                );
            $result = $DBA->executeSQL($sql, $parameters);

            $laborCosts = array();
            // SQLエラー
            if( $result === false )
            {
                $errMsg = "SQLエラー" . $sql;
                $Log->fatalDetail($errMsg);
                $Log->trace("END getUserLaborData");
                return $laborCosts;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $putData = array(
                                    'user_id'                    => $data['user_id'],
                                    'user_name'                  => $data['user_name'],
                                    'employees_no'               => $data['employees_no'],
                                    'date'                       => $data['date'],
                                    'embossing_abbreviated_name' => $data['embossing_abbreviated_name'],
                                    'embossing_status'           => $data['embossing_status'],
                                    'total_working'              => $this->changeTimeFromMinute( $data['total_working'] ),
                                    'shift_working'              => $this->changeTimeFromMinute( $data['shift_working'] ),
                                    'total_working_user_count'   => $this->isNum( $data['total_working_user_count'] ),
                                    'shift_working_user_count'   => $this->isNum( $data['shift_working_user_count'] ),
                                    'rough_estimate'             => $this->isNum( $data['rough_estimate'] ),
                                    'shift_rough_estimate'       => $this->isNum( $data['shift_rough_estimate'] ),
                                );
                array_push( $laborCosts, $putData );
            }

            $Log->trace("END   getUserLaborDailyData");

            return $laborCosts;
        }

        /**
         * ユーザの集計情報
         * @param    $userIDList       取得対象のユーザID (array型)
         * @param    $orgIDList        取得対象の打刻場所の組織ID (array型)
         * @param    $startDate        集計開始日 (例：2016/05/01)
         * @param    $endDate          集計終了日 (例：2016/06/01)
         * @param    $isUser           所属組織/全支店の合算フラグ  true：ユーザ単位   false：組織単位に出力
         * @param    $isMySelfStore    自店/支援フラグ  1：自店のみ   2：支援店舗のみ  3：全て
         * @param    $isDaily          日単位フラグ  true：日単位   false：期間単位
         * @return   集計結果のArray
         */
        public function getUserLaborAggregateData( $userIDList, $orgIDList, $startDate, $endDate, $isUser, $isMySelfStore, $isDaily )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getUserLaborAggregateData");

            $sql  =  " SELECT va.user_id ";
            if( $isDaily )
            {
                $sql .=  "   , va.date ";
            }
            $sql .=  "   , ( SELECT vu1.user_name    FROM v_user vu1 WHERE vu1.user_id = va.user_id AND vu1.eff_code = '適用中' ) "
                 .   "   , ( SELECT vu2.employees_no FROM v_user vu2 WHERE vu2.user_id = va.user_id AND vu2.eff_code = '適用中' ) "
                 .   "   , sum( va.total_working_time_con_minute ) as total_working "
                 .   "   , sum( va.shift_working_time_con_minute ) as shift_working "
                 .   "   , count( va.attendance_time ) as total_working_user_count "
                 .   "   , count( va.shift_attendance_time ) as shift_working_user_count "
                 .   "   , sum( va.rough_estimate ) as rough_estimate "
                 .   "   , sum( va.shift_rough_estimate ) as shift_rough_estimate ";
            if( !$isUser )
            {
                 $sql .=  "   , ( SELECT vo1.abbreviated_name as embossing_abbreviated_name  FROM v_organization vo1 WHERE vo1.organization_id = va.embossing_organization_id AND vo1.eff_code = '適用中' ) "
                      .   "   , CASE WHEN va.embossing_organization_id = va.organization_id THEN '所属' "
                      .   "          WHEN va.embossing_organization_id = 0 THEN '' "
                      .   "          WHEN va.embossing_organization_id <> va.organization_id THEN '支援' "
                      .   "     END embossing_status ";
            }
            else
            {
                $sql .=  "   , '全て' as embossing_abbreviated_name "
                     .   "   , '全て' as embossing_status ";
            }
            
            $sql .=   " FROM v_attendance_record va "
                 .    " WHERE :startDate <= date AND date < :endDate AND is_del = 0 ";
            
            if( $isMySelfStore == 1 )
            {
                $sql .=  "  AND embossing_status = '所属' ";
            }
            else if( $isMySelfStore == 2 )
            {
                $sql .=  "  AND embossing_status = '支援' AND embossing_abbreviated_name is NOT NULL ";
            }
            
            // ユーザ指定がある場合、条件文を追加する
            if( count( $userIDList ) > 0  )
            {
                $sql .= " AND va.user_id IN ( ";
                foreach( $userIDList as $userID )
                {
                    $sql .= $userID . ",";
                }
                
                // 最後のカンマを削除(最後のカンマ以外の文字列を再度入れる)
                $sql = substr($sql, 0, -1);
                
                $sql .= ") ";
            }

            // 組織指定がある場合、条件文を追加する
            if( count( $orgIDList ) > 0  )
            {
                $sql .= " AND va.embossing_organization_id IN ( ";
                foreach( $orgIDList as $orgID )
                {
                    $sql .= $orgID . ",";
                }
                
                // 最後のカンマを削除(最後のカンマ以外の文字列を再度入れる)
                $sql = substr($sql, 0, -1);
                
                $sql .= ") ";
            }
            
            $sql .=  " GROUP BY va.user_id ";
            if( $isDaily )
            {
                $sql .=  " , date ";
            }
            
            // 組織単位に出力する
            if( !$isUser )
            {
                $sql .=  " , va.organization_id, va.embossing_organization_id ";
            }
            
            // ORDER BYを指定
            $sql .=  " ORDER BY employees_no ";
            if( $isDaily )
            {
                $sql .=  " , date ";
            }
            
            $parameters = array(
                                ':startDate'    =>  $startDate,
                                ':endDate'      =>  $endDate,
                                );
            $result = $DBA->executeSQL($sql, $parameters);

            $laborCosts = array();
            // SQLエラー
            if( $result === false )
            {
                $errMsg = "SQLエラー" . $sql;
                $Log->fatalDetail($errMsg);
                $Log->trace("END getUserLaborData");
                return $laborCosts;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $putData = array(
                                    'user_id'                    => $data['user_id'],
                                    'user_name'                  => $data['user_name'],
                                    'employees_no'               => $data['employees_no'],
                                    'embossing_abbreviated_name' => $data['embossing_abbreviated_name'],
                                    'embossing_status'           => $data['embossing_status'],
                                    'total_working'              => $this->changeTimeFromMinute( $data['total_working'] ),
                                    'shift_working'              => $this->changeTimeFromMinute( $data['shift_working'] ),
                                    'total_working_user_count'   => $this->isNum( $data['total_working_user_count'] ),
                                    'shift_working_user_count'   => $this->isNum( $data['shift_working_user_count'] ),
                                    'rough_estimate'             => $this->isNum( $data['rough_estimate'] ),
                                    'shift_rough_estimate'       => $this->isNum( $data['shift_rough_estimate'] ),
                                );
                // 日単位の場合、日付も追加する
                if( $isDaily )
                {
                    $putData    =   array_merge( $putData, array( 'date' => $data['date'], ) );
                }
                
                array_push( $laborCosts, $putData );
            }

            $Log->trace("END   getUserLaborAggregateData");

            return $laborCosts;
        }

        /**
         * 組織の集計情報
         * @param    $orgIDList        取得対象の打刻場所の組織ID (array型)
         * @param    $startDate        集計開始日 (例：2016/05/01)
         * @param    $endDate          集計終了日 (例：2016/06/01)
         * @param    $isAggregate      集計単位         1：雇用形態   2：役職          3：全て
         * @param    $isMySelfStore    自店/支援フラグ  1：自店のみ   2：支援店舗のみ  3：全て
         * @param    $isDaily          日単位フラグ  true：日単位   false：期間単位
         * @return   集計結果のArray
         */
        public function getOrgLaborAggregateData( $orgIDList, $startDate, $endDate, $isAggregate, $isMySelfStore, $isDaily )
        {
            global $Log, $DBA; // グローバル変数宣言
            $Log->trace("START getOrgLaborAggregateData");

            $sql  =  " SELECT va.organization_id ";
            // 日単位
            if( $isDaily )
            {
                $sql .=  "   , va.date ";
            }
            
            // 役職 or 雇用形態
            if( $isAggregate == 1 )
            {
                $sql .=   "   , va.employment_id "
                     .    "   , ( SELECT me1.employment_name FROM m_employment me1 WHERE me1.employment_id = va.employment_id ) "
                     .    "   , ( SELECT me2.code FROM m_employment me2 WHERE me2.employment_id = va.employment_id ) ";
            }
            else if( $isAggregate == 2 )
            {
                $sql .=   "   , va.position_id "
                     .    "   , ( SELECT mp1.position_name FROM m_position mp1 WHERE mp1.position_id = va.position_id ) "
                     .    "   , ( SELECT mp2.code FROM m_position mp2 WHERE mp2.position_id = va.position_id ) ";
            }
            
            $sql .=  "   , ( SELECT vo1.organization_name FROM v_organization vo1 WHERE vo1.organization_id = va.organization_id AND vo1.eff_code = '適用中' ) "
                 .   "   , ( SELECT vo2.abbreviated_name  FROM v_organization vo2 WHERE vo2.organization_id = va.organization_id AND vo2.eff_code = '適用中' ) "
                 .   "   , ( SELECT vo3.department_code   FROM v_organization vo3 WHERE vo3.organization_id = va.organization_id AND vo3.eff_code = '適用中' ) "
                 .   "   , sum( va.total_working_time_con_minute ) as total_working "
                 .   "   , sum( va.shift_working_time_con_minute ) as shift_working "
                 .   "   , count( va.attendance_time ) as total_working_user_count "
                 .   "   , count( va.shift_attendance_time ) as shift_working_user_count "
                 .   "   , sum( va.rough_estimate ) as rough_estimate "
                 .   "   , sum( va.shift_rough_estimate ) as shift_rough_estimate ";

            if( $isMySelfStore != 3 )
            {
                 $sql .=  "   , CASE WHEN va.embossing_organization_id = va.organization_id THEN '所属' "
                      .   "          WHEN va.embossing_organization_id = 0 THEN '' "
                      .   "          WHEN va.embossing_organization_id <> va.organization_id THEN '支援' "
                      .   "     END embossing_status ";
            }
            else
            {
                $sql .=  "   , '全て' as embossing_status ";
            }
            
            $sql .=   " FROM v_attendance_record va "
                 .    " WHERE :startDate <= date AND date < :endDate AND is_del = 0 ";
            
            if( $isMySelfStore == 1 )
            {
                $sql .=  "  AND embossing_status = '所属' ";
            }
            else if( $isMySelfStore == 2 )
            {
                $sql .=  "  AND embossing_status = '支援' AND embossing_abbreviated_name is NOT NULL ";
            }

            // 組織指定がある場合、条件文を追加する
            if( count( $orgIDList ) > 0  )
            {
                $sql .= " AND va.organization_id IN ( ";
                foreach( $orgIDList as $orgID )
                {
                    $sql .= $orgID . ",";
                }
                
                // 最後のカンマを削除(最後のカンマ以外の文字列を再度入れる)
                $sql = substr($sql, 0, -1);
                
                $sql .= ") ";
            }
            
            $sql .=  " GROUP BY va.organization_id ";
            if( $isDaily )
            {
                $sql .=  " , date ";
            }
            
            // 単位に出力する
            if( $isMySelfStore != 3 )
            {
                $sql .=  " , va.embossing_organization_id ";
            }
            
            // 役職 or 雇用形態
            if( $isAggregate == 1 )
            {
                $sql .=   "   , va.employment_id ";
            }
            else if( $isAggregate == 2 )
            {
                $sql .=   "   , va.position_id ";
            }
            
            // ORDER BYを指定
            $sql .=  " ORDER BY department_code ";
            if( $isDaily )
            {
                $sql .=  " , date ";
            }
            
            $parameters = array(
                                ':startDate'    =>  $startDate,
                                ':endDate'      =>  $endDate,
                                );
            $result = $DBA->executeSQL($sql, $parameters);

            $laborCosts = array();
            // SQLエラー
            if( $result === false )
            {
                $errMsg = "SQLエラー" . $sql;
                $Log->fatalDetail($errMsg);
                $Log->trace("END getUserLaborData");
                return $laborCosts;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $putData = array(
                                    'organization_id'            => $data['organization_id'],
                                    'organization_name'          => $data['organization_name'],
                                    'abbreviated_name'           => $data['abbreviated_name'],
                                    'department_code'            => $data['department_code'],
                                    'embossing_status'           => $data['embossing_status'],
                                    'total_working'              => $this->changeTimeFromMinute( $data['total_working'] ),
                                    'shift_working'              => $this->changeTimeFromMinute( $data['shift_working'] ),
                                    'total_working_user_count'   => $this->isNum( $data['total_working_user_count'] ),
                                    'shift_working_user_count'   => $this->isNum( $data['shift_working_user_count'] ),
                                    'rough_estimate'             => $this->isNum( $data['rough_estimate'] ),
                                    'shift_rough_estimate'       => $this->isNum( $data['shift_rough_estimate'] ),
                                );
                // 日単位の場合、日付も追加する
                if( $isDaily )
                {
                    $putData    =   array_merge( $putData, array( 'date' => $data['date'], ) );
                }
                
                // 役職 or 雇用形態
                if( $isAggregate == 1 )
                {
                    $subArray = array(
                                        'employment_id'     => $data['employment_id'],
                                        'employment_name'   => $data['employment_name'],
                                        'code'              => $data['code'],
                                      );
                    $putData    =   array_merge( $putData, $subArray );
                }
                else if( $isAggregate == 2 )
                {
                    $subArray = array(
                                        'position_id'     => $data['position_id'],
                                        'position_name'   => $data['position_name'],
                                        'code'            => $data['code'],
                                      );
                    $putData    =   array_merge( $putData, $subArray );
                }
                
                array_push( $laborCosts, $putData );
            }

            $Log->trace("END   getOrgLaborAggregateData");

            return $laborCosts;
        }

        /**
         * 数値チェック
         * @param   $num      検証対象文字列又は数値
         * @return  対象文字列の数値又は、0
         */
        private function isNum( $num )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isNum");

            $ret = 0;
            if ( is_numeric( $num ) ) 
            {
                $ret = $num;
            }
            
            $Log->trace("END   isNum");

            return $ret;
        }

    }
?>
