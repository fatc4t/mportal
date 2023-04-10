<?php
    /**
     * @file      シフト一覧画面(Model)
     * @author    USE K.narita
     * @date      2016/07/22
     * @version   1.00
     * @note      シフト一覧テーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * シフト一覧画面クラス
     * @note   シフト一覧テーブルの管理を行う
     */
    class ShiftList extends BaseModel
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
         * シフト一覧テーブル一覧表(各セクション用)
         * @param    $postArray(organizationID/sectionName/positionName/employmentName/minDay/maxDay)
         * @return   成功時：$shiftDataList(shift_id/user_id/user_name/section_name/color/day/is_holiday/attendance/taikin)  失敗：無
         */
        public function getListData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getListData");
            
            $searchArray = array();
            $sql = $this->creatSQL($postArray, $searchArray);

            $result = $DBA->executeSQL( $sql, $searchArray);
            
            $shiftDataList = array();
            if( $result === false )
            {
                $Log->trace("END getListData");
                return $shiftDataList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( !isset($shiftDataList[$data['section_name']]) )
                {
                    $shiftDataList[$data['section_name']] = array();
                }
                
                if( !isset($shiftDataList[$data['section_name']][$data['user_name']]) )
                {
                    $shiftDataList[$data['section_name']][$data['user_name']] = array();
                }
                
                array_push( $shiftDataList[$data['section_name']][$data['user_name']],  $data);
            }
            
            $Log->trace("END getListData");
            return $shiftDataList;
        }

        /**
         * シフト一覧テーブル一覧表(概要用)
         * @param    $postArray(organizationID/sectionName/positionName/employmentName/minDay/maxDay)
         * @return   成功時：$shiftList(section_name/day/count)  失敗：無
         */
        public function getShiftList($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftList");
            
            $searchArray = array();
            $sql = "SELECT   s.section_name  , ts.day  ,count(CASE WHEN ts.attendance <> ts.taikin THEN TRUE ELSE NULL END) "
                .  "FROM t_shift ts INNER JOIN v_user vu ON ts.user_id = vu.user_id AND vu.eff_code = '適用中' "
                .  "LEFT OUTER JOIN m_section s ON ts.section_id = s.section_id ";
            
            $sqlAnd = $this->creatSqlAnd($postArray,$searchArray);
            $sql .= $sqlAnd;
            
            $sql .= "GROUP BY  s.section_name  , ts.day "
                 .  "ORDER BY day,s.section_name ";

            $result = $DBA->executeSQL( $sql, $searchArray);
            
            $shiftList = array();
            if( $result === false )
            {
                $Log->trace("END getShiftList");
                return $shiftList;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( !isset($shiftList[$data['section_name']]) )
                {
                    $shiftList[$data['section_name']] = array();
                }
                
                array_push( $shiftList[$data['section_name']],  $data);
            }
            
            $Log->trace("END getShiftList");
            return $shiftList;
        }

        /**
         * 休日一覧を取得
         * @return   休日一覧
         */
        public function getHolidayList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHolidayList");
            
            // 入力された組織で、表示するマスタの上限を設定
            $searchArray = array();
            $sql = "SELECT holiday_id, holiday "
                .  "FROM m_holiday "
                .  "WHERE is_del = 0";

            $result = $DBA->executeSQL( $sql, $searchArray);
            
            $list = array(
                            SystemParameters::$PUBLIC_HOLIDAY       =>  '公休',
                            SystemParameters::$STATUTORY_HOLIDAY    =>  '法定休',
                         );
            if( $result === false )
            {
                $Log->trace("END getHolidayList");
                return $list;
            }
            // 取得したデータを配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $list[$data['holiday_id']] = $data['holiday'];
            }

            $Log->trace("END getHolidayList");
            return $list;
        }

        /**
         * シフト一覧テーブル(各セクション用)取得SQL文作成
         * @param    $postArray(organizationID/sectionName/positionName/employmentName/minDay/maxDay)
         * @param    $searchArray(空の配列)
         * @return   シフト一覧マスタ一覧取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            
            $sql = ' SELECT ts.shift_id , vu.user_name , s.section_name , ts.color , ts.day , ts.is_holiday  , ts.attendance , ts.taikin  '
                 . " FROM t_shift ts INNER JOIN v_user vu ON ts.user_id = vu.user_id AND vu.eff_code = '適用中' "
                 . " LEFT OUTER JOIN m_section s ON ts.section_id = s.section_id ";
            
            $sqlAnd = $this->creatSqlAnd($postArray,$searchArray);
            $sql .= $sqlAnd;
            
            $sql .= "GROUP BY ts.shift_id , vu.user_name , s.section_name , ts.color , ts.day , ts.is_holiday  , ts.attendance , ts.taikin "
                 .  "ORDER BY day,s.section_name ";
            
            $Log->trace("END creatSQL");
            return $sql;
        }
        
        /**
         * AND用のSQL文作成
         * @param    $postArray(organizationID/sectionName/positionName/employmentName/minDay/maxDay)
         * @param    $searchArray   検索条件用パラメータ
         * @return   $sqlAnd
         */
        private function creatSqlAnd($postArray,&$searchArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSqlAnd");
            
            $sqlAnd = "WHERE day BETWEEN :minDay AND :maxDay ";
            
            $minDayArray = array(':minDay' => $postArray['minDay'],);
            $searchArray = array_merge($searchArray, $minDayArray);
            
            $maxDayArray = array(':maxDay' => $postArray['maxDay'],);
            $searchArray = array_merge($searchArray, $maxDayArray);
            
            if( !empty( $postArray['organizationID'] ) )
            {
                $sqlAnd .= ' AND ts.organization_id = :organizationID ';
                $organizationIDArray = array(':organizationID' => $postArray['organizationID'],);
                $searchArray = array_merge($searchArray, $organizationIDArray);
            }
            if( !empty( $postArray['sectionName'] ) )
            {
                $sqlAnd .= ' AND s.section_name = :section_name ';
                $sectionArray = array(':section_name' => $postArray['sectionName'],);
                $searchArray = array_merge($searchArray, $sectionArray);
            }
            if( !empty( $postArray['positionName'] ) )
            {
                $sqlAnd .= ' AND vu.position_name = :position_name ';
                $positionNameArray = array(':position_name' => $postArray['positionName'],);
                $searchArray = array_merge($searchArray, $positionNameArray);
            }
            if( !empty( $postArray['employmentName'] ) )
            {
                $sqlAnd .= ' AND vu.employment_name = :employment_name ';
                $employmentNameArray = array(':employment_name' => $postArray['employmentName'],);
                $searchArray = array_merge($searchArray, $employmentNameArray);
            }
            
            $searchedColumn = ' AND ts.';
            $sqlWhereInCond = $this->creatSqlWhereInConditions($searchedColumn);
            
            // 自分のみアクセス権限の場合、参照可能な組織IDがない為、自分のユーザIDで表示条件を絞る
            if( $sqlWhereInCond == '' )
            {
                $sqlAnd .= ' AND vu.user_id = :user_id ';
                $searchArray = array_merge($searchArray, array(':user_id' => $_SESSION["USER_ID"],));
            }
            else
            {
                $sqlAnd .= $sqlWhereInCond;
            }
            
            $Log->trace("END creatSqlAnd");
            return $sqlAnd;
        }
    }
?>
