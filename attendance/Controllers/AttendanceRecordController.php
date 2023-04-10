<?php
    /**
     * @file      勤怠一覧コントローラ
     * @author    USE M.Higashihara
     * @date      2016/07/12
     * @version   1.00
     * @note      勤怠一覧の管理を行う。
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 勤怠一覧処理モデル
    require './Model/AttendanceRecord.php';

    /**
     * 勤怠一覧コントローラクラス
     * @note   勤怠一覧表示の処理を行う
     */
    class AttendanceRecordController extends BaseController
    {
        protected $attendance = null;   ///< 勤怠一覧モデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
            global $attendance;
            $attendance = new attendanceRecord();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // BaseControllerのデストラクタ
            parent::__destruct();
        }

        /**
         * 勤怠一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1410" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 勤怠一覧画面初期表示
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            $Log->info( "MSG_INFO_1411" );

            $printFlag = false;

            $this->initialList($printFlag);
            $Log->trace("END   searchAction");
        }

        /**
         * 勤怠一覧印刷画面表示
         * @return    なし
         */
        public function printAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START printAction");
            $Log->info( "MSG_INFO_1412" );

            $printFlag = true;

            $this->initialList($printFlag);
            $Log->trace("END   printAction");
        }

         /**
         * 勤怠一覧承認画面表示
         * @return    なし
         */
        public function lumpApprovalAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START printAction" );
            $Log->info( "MSG_INFO_1142" );

            if(isset($_POST['lumpDayApproval']))
            {
                require_once './View/AttendanceDailyApprovalPanel.html';
            }
            else
            {
                require_once './View/AttendanceMonthlyApprovalPanel.html';
            }
            $Log->trace("END printAction");
        }

        /**
         * 勤怠一覧画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $attendance;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            // 検索用プルダウン
            $abbreviatedNameList = $attendance->setPulldown->getSearchOrganizationList( 'reference', true );    // 組織略称名リスト
            $positionNameList = $attendance->setPulldown->getSearchPositionList( 'reference' );                 // 役職名リスト
            $employmentNameList = $attendance->setPulldown->getSearchEmploymentList( 'reference' );             // 雇用形態名リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            // ログインユーザ勤怠一覧閲覧権限宗取得
            $attendanceAccessId = $attendance->getAttendanceAccessId();

            // 昨日の年・月・日
            $yesterdayYear = date('Y', strtotime('-1 day') );
            $yesterdayMonth = date('m', strtotime('-1 day'));
            $yesterdayDay = date('d', strtotime('-1 day'));
            $nowTime = date('H');
            // 初期表示用の検索条件
            $searchArray =array(
                'attendanceAccessId'        => $attendanceAccessId,
                'dateTimeUnit'              => $Log->getMsgLog('MSG_CUMULATIVE_DAY'),
                'mobileUnit'                => $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL'),
                'minPeriodSpecifiedYear'    => $yesterdayYear,
                'minPeriodSpecifiedMonth'   => $yesterdayMonth,
                'minPeriodSpecifiedDay'     => $yesterdayDay,
                'minPeriodSpecifiedHour'    => $nowTime,
                'maxPeriodSpecifiedYear'    => $yesterdayYear,
                'maxPeriodSpecifiedMonth'   => $yesterdayMonth,
                'maxPeriodSpecifiedDay'     => $yesterdayDay,
                'maxPeriodSpecifiedHour'    => $nowTime,
                'organizationID'            => "",
                'position_name'             => "",
                'employment_name'           => "",
                'personal_name'             => "",
                'embossingOrganizationID'   => "",
                'embossingSituation'        => "",
                'minEstimationShift'        => "",
                'maxEstimationShift'        => "",
                'minEstimationPerfo'        => "",
                'maxEstimationPerfo'        => "",
                'minTotalWorkTime'          => "",
                'maxTotalWorkTime'          => "",
                'minOvertime'               => "",
                'maxOvertime'               => "",
                'minBreakTime'              => "",
                'maxBreakTime'              => "",
                'minAbsenteeismNo'          => "",
                'maxAbsenteeismNo'          => "",
                'minLateNo'                 => "",
                'maxLateNo'                 => "",
                'minLeaveNo'                => "",
                'maxLeaveNo'                => "",
                'minModifyNo'               => "",
                'maxModifyNo'               => "",
                'searchCondition'           => "",
            );
            if( !empty( $_SESSION['ATTENDANCE_RECORD'] ) )
            {
                $searchArray = $_SESSION['ATTENDANCE_RECORD'];
                // 累計単位・期間指定以外の検索条件がある場合、指定期間内の勤怠情報が存在しないデータを一覧で表示させない
                $searchArray = $this->setSearchCondition( $searchArray );
            }

            // 勤怠一覧リスト（個人日単位現在日の1日前の日付データ）
            $attendanceList = $attendance->getListData( $searchArray );
            // 手当等の除外対象のキーとなるリスト取得
            $exclusionKeyList = $this->getExclusionKeyList( $attendanceList );
            // 累計単位の内容によって非表示とするリスト
            $hideList = $this->setHideList( $searchArray );
            // 表示項目情報
            $displayItemData = $attendance->getDisplayItemData();
            // 表示項目リスト
            $displayItemList = $attendance->getDisplayItemList( $hideList, $searchArray['dateTimeUnit'] );
            // 手当取得情報
            $allowanceInfoList = $attendance->getAllowanceInfoList( $searchArray, $exclusionKeyList );
            // 休日取得情報
            $holidayInfoList = $attendance->getHolidayInfoList( $searchArray, $exclusionKeyList );
            // 休日名称取得情報
            $holidayInfoNameList = $attendance->getHolidayNameInfoList( $searchArray, $exclusionKeyList );
            // NO表示用
            $display_no = 0;
            $attNoSortFlag = false;

            // 表示行数を算出
            $viewCnt = count( $attendanceList );
            // ベースサイズ
            $baseSize = 80;
            $cnt = -1;
            // ベースサイズ計算
            foreach( $displayItemList as $displayItem )
            {
                if( $displayItem['output_type_id'] == 1 || ( $displayItem['output_type_id'] > 1 && $displayItem['output_item_branch'] != 0 ) )
                {
                    $baseSize += $displayItem['width'];
                }
                $cnt++;
            }

            $isScrollBar = false;
            $lastOutputTypeId = 0;
            $lastOutputItemId = 0;
            $heightSize = '';
            if( $viewCnt > 18 )
            {
                $baseHeadSize = 18;
                $isScrollBar = true;
                $lastOutputTypeId = $displayItemList[$cnt]['output_type_id'];
                $lastOutputItemId = $displayItemList[$cnt]['output_item_id'];
                $lastOutputItemBranch = $displayItemList[$cnt]['output_item_branch'];
                $heightSize  = 'height:450px;';
            }

            // div(表示サイズを設定) 18pxはスクロールバー分
            $viewSize = $baseSize + $baseHeadSize + 2;
            $dataTableSize = $viewSize;
            if( $viewSize > 1260 )
            {
                $viewSize = 1260;
            }

            $viewTable = $dataTableSize;
            if( $isScrollBar )
            {
                $viewTable = $dataTableSize - 18;
            }

            $Log->trace("END initialDisplay");

            require_once './View/AttendanceRecordPanel.html';
        }

        /**
         * 勤怠一覧画面
         * @note     パラメータは、View側で使用
         * @param    $printFlag
         * @return   無
         */
        private function initialList($printFlag)
        {
            global $Log, $attendance;  // グローバル変数宣言
            $Log->trace("START initialList");

            // 検索条件の労働時間・残業時間・休憩時間の分換算処理
            $minTotalWorkTime = $this->conversionSearchTime( parent::escStr( $_POST['searchMinTotalWorkTimeHour'] ), parent::escStr( $_POST['searchMinTotalWorkTimeMinute'] ) );
            $maxTotalWorkTime = $this->conversionSearchTime( parent::escStr( $_POST['searchMaxTotalWorkTimeHour'] ), parent::escStr( $_POST['searchMaxTotalWorkTimeMinute'] ) );
            $minOvertime = $this->conversionSearchTime( parent::escStr( $_POST['searchMinOvertimeHour'] ), parent::escStr( $_POST['searchMinOvertimeMinute'] ) );
            $maxOvertime = $this->conversionSearchTime( parent::escStr( $_POST['searchMaxOvertimeHour'] ), parent::escStr( $_POST['searchMaxOvertimeMinute'] ) );
            $minBreakTime = $this->conversionSearchTime( parent::escStr( $_POST['searchMinBreakTimeHour'] ), parent::escStr( $_POST['searchMinBreakTimeMinute'] ) );
            $maxBreakTime = $this->conversionSearchTime( parent::escStr( $_POST['searchMaxBreakTimeHour'] ), parent::escStr( $_POST['searchMaxBreakTimeMinute'] ) );

            // ログインユーザ勤怠一覧閲覧権限宗取得
            $attendanceAccessId = $attendance->getAttendanceAccessId();
            // 検索条件リスト
            $searchArray = array(
                'attendanceAccessId'        => $attendanceAccessId,
                'dateTimeUnit'              => parent::escStr( $_POST['searchDateTimeUnit'] ),
                'mobileUnit'                => parent::escStr( $_POST['searchMobileUnit'] ),
                'minPeriodSpecifiedYear'    => parent::escStr( $_POST['searchMinPeriodSpecifiedYear'] ),
                'minPeriodSpecifiedMonth'   => parent::escStr( $_POST['searchMinPeriodSpecifiedMonth'] ),
                'minPeriodSpecifiedDay'     => parent::escStr( $_POST['searchMinPeriodSpecifiedDay'] ),
                'minPeriodSpecifiedHour'    => parent::escStr( $_POST['searchMinPeriodSpecifiedHour'] ),
                'maxPeriodSpecifiedYear'    => parent::escStr( $_POST['searchMaxPeriodSpecifiedYear'] ),
                'maxPeriodSpecifiedMonth'   => parent::escStr( $_POST['searchMaxPeriodSpecifiedMonth'] ),
                'maxPeriodSpecifiedDay'     => parent::escStr( $_POST['searchMaxPeriodSpecifiedDay'] ),
                'maxPeriodSpecifiedHour'    => parent::escStr( $_POST['searchMaxPeriodSpecifiedHour'] ),
                'organizationID'            => parent::escStr( $_POST['searchOrganizatioID'] ),
                'position_name'             => parent::escStr( $_POST['searchPositionName'] ),
                'employment_name'           => parent::escStr( $_POST['searchEmploymentName'] ),
                'personal_name'             => parent::escStr( $_POST['searchPersonalName'] ),
                'embossingOrganizationID'   => parent::escStr( $_POST['searchEmbossingOrganizationID'] ),
                'embossingSituation'        => parent::escStr( $_POST['searchEmbossingSituation'] ),
                'minEstimationShift'        => parent::escStr( $_POST['searchMinEstimationShift'] ),
                'maxEstimationShift'        => parent::escStr( $_POST['searchMaxEstimationShift'] ),
                'minEstimationPerfo'        => parent::escStr( $_POST['searchMinEstimationPerfo'] ),
                'maxEstimationPerfo'        => parent::escStr( $_POST['searchMaxEstimationPerfo'] ),
                'minTotalWorkTime'          => $minTotalWorkTime,
                'maxTotalWorkTime'          => $maxTotalWorkTime,
                'minOvertime'               => $minOvertime,
                'maxOvertime'               => $maxOvertime,
                'minBreakTime'              => $minBreakTime,
                'maxBreakTime'              => $maxBreakTime,
                'minAbsenteeismNo'          => parent::escStr( $_POST['searchMinAbsenteeismNo'] ),
                'maxAbsenteeismNo'          => parent::escStr( $_POST['searchMaxAbsenteeismNo'] ),
                'minLateNo'                 => parent::escStr( $_POST['searchMinLateNo'] ),
                'maxLateNo'                 => parent::escStr( $_POST['searchMaxLateNo'] ),
                'minLeaveNo'                => parent::escStr( $_POST['searchMinLeaveNo'] ),
                'maxLeaveNo'                => parent::escStr( $_POST['searchMaxLeaveNo'] ),
                'minModifyNo'               => parent::escStr( $_POST['searchMinModifyNo'] ),
                'maxModifyNo'               => parent::escStr( $_POST['searchMaxModifyNo'] ),
                'searchCondition'           => "",
            );
            // 勤怠一覧の検索条件をセッション変数に挿入
            $_SESSION['ATTENDANCE_RECORD'] = $searchArray;
            // 累計単位・期間指定以外の検索条件がある場合、指定期間内の勤怠情報が存在しないデータを一覧で表示させない
            $searchArray = $this->setSearchCondition( $searchArray );
            // 表示項目情報
            $displayItemData = $attendance->getDisplayItemData();
            // 勤怠一覧リスト
            $attendanceList = $attendance->getListData( $searchArray );
            // 手当等の除外対象のキーとなるリスト取得
            $exclusionKeyList = $this->getExclusionKeyList( $attendanceList );
            // 累計単位の内容によって非表示とするリスト
            $hideList = $this->setHideList( $searchArray );
            // 表示項目リスト
            $displayItemList = $attendance->getDisplayItemList( $hideList, $searchArray['dateTimeUnit'] );
            // 手当取得取得
            $allowanceInfoList = $attendance->getAllowanceInfoList( $searchArray, $exclusionKeyList );
            // 休日取得情報
            $holidayInfoList = $attendance->getHolidayInfoList( $searchArray, $exclusionKeyList );
            // 休日名称取得情報
            $holidayInfoNameList = $attendance->getHolidayNameInfoList( $searchArray, $exclusionKeyList );
            // NO表示用
            $display_no = 0;
            $attNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            
            // 表示行数を算出
            $viewCnt = count( $attendanceList );
            // ベースサイズ
            $baseSize = 80;
            $cnt = -1;
            // ベースサイズ計算
            foreach( $displayItemList as $displayItem )
            {
                if( $displayItem['output_type_id'] == 1 || ( $displayItem['output_type_id'] > 1 && $displayItem['output_item_branch'] != 0 ) )
                {
                    $baseSize += $displayItem['width'];
                }
                $cnt++;
            }

            $isScrollBar = false;
            $lastOutputTypeId = 0;
            $lastOutputItemId = 0;
            $heightSize = '';
            if( $viewCnt > 18 )
            {
                $baseHeadSize = 18;
                $isScrollBar = true;
                $lastOutputTypeId = $displayItemList[$cnt]['output_type_id'];
                $lastOutputItemId = $displayItemList[$cnt]['output_item_id'];
                $lastOutputItemBranch = $displayItemList[$cnt]['output_item_branch'];
                $heightSize  = 'height:450px;';
            }

            // div(表示サイズを設定) 18pxはスクロールバー分
            $viewSize = $baseSize + $baseHeadSize + 2;
            $dataTableSize = $viewSize;
            if( $viewSize > 1260 )
            {
                $viewSize = 1260;
            }

            $viewTable = $dataTableSize;
            if( $isScrollBar && !$printFlag )
            {
                $viewTable = $dataTableSize - 18;
            }

            if($printFlag)
            {
                $displayList = $this->setPrintHeaderList($searchArray);
                require_once './View/AttendanceRecordPrintPanel.html';
            }
            else
            {
                require_once './View/AttendanceRecordTablePanel.html';
            }

            $Log->trace("END initialList");
        }

        /**
         * 手当等絞り込み条件キーリスト作成
         * @param    $attendanceList
         * @return   $exclusionKeyList
         */
        private function getExclusionKeyList( $attendanceList )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getExclusionKeyList");

            $exclusionKeyList = array();
            foreach( $attendanceList as $attendance )
            {
                if( !empty( $attendance['organization_id'] ) )
                {
                    $exclusion = array(
                        'organization_id' => $attendance['organization_id'],
                        'user_id'         => $attendance['user_id'],
                        'position_id'     => $attendance['position_id'],
                        'employment_id'   => $attendance['employment_id'],
                        'attendance_id'   => $attendance['attendance_id'],
                        'periodDate'      => $attendance['periodDate'],
                        'target_period'   => $attendance['target_period'],
                    );
                    array_push( $exclusionKeyList, $exclusion );
                }
            }

            $Log->trace("END getExclusionKeyList");

            return $exclusionKeyList;
        }

        /**
         * 絞り込み条件の判定値セット
         * @param    $searchArray
         * @return   $searchArray
         */
        private function setSearchCondition( $searchArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setSearchCondition");

            $conditionCnt = 1;
            foreach( $searchArray as $val )
            {
                if( $conditionCnt > 15 )
                {
                    if( !empty( $val ) )
                    {
                        $searchArray['searchCondition'] = 1;
                    }
                }
                $conditionCnt++;
            }

            $Log->trace("END setSearchCondition");

            return $searchArray;
        }

        /**
         * 非表示リストを設定する
         * @note     表示項目リスト取得時に検索条件の累計単位によって非表示とする項目をリスト化する
         * @param    $searchArray
         * @return   $hideList
         */
        private function setHideList( $searchArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setHideList");

            if( $searchArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_INDIVIDUAL') )
            {
                $hideList = $this->setHideIndividualList( $searchArray );
            }
            else
            {
                $hideList = $this->setHideUnitList( $searchArray );
            }

            $Log->trace("END setHideList");

            return $hideList;
        }

        /**
         * 個人用非表示リストを設定する
         * @note     累計単位個人月または年で検索する際に非表示とするリストを設定する
         * @param    $mobileUnit
         * @return   $hideList
         */
        private function setHideIndividualList( $searchArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setHideIndividualList");

            $hideList = array( 75 );

            if( $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') ||  $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR')  )
            {
                // 月年時に非表示とする項目を入れ替える
                $hideList = array( 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 87 );
                // 検索条件で打刻状況を設定した場合に打刻状況の非表示を解除
                if( !empty( $searchArray['embossingSituation'] ) )
                {
                    $hideList = array_diff( $hideList, array( 14 ) );
                    $hideList = array_values( $hideList );
                }
                // 検索条件で打刻場所を設定した場合に打刻場所情報の非表示を解除
                if( !empty( $searchArray['embossingOrganizationID'] ) )
                {
                    $hideList = array_diff( $hideList, array( 11, 12, 13 ) );
                    $hideList = array_values( $hideList );
                }
            }
            else if( $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_TIME') )
            {
                // 時間時に非表示とする項目を入れ替える
                $hideList = array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 78, 79, 80, 81, 82, 83, 84, 85, 86 );
            }

            $Log->trace("END setHideIndividualList");

            return $hideList;
        }

        /**
         * 個人以外用非表示リストを設定する
         * @note     累計単位組織で検索する際に非表示とするリストを設定する
         * @param    $searchArray
         * @return   $hideList
         */
        private function setHideUnitList( $searchArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setHideIndividualList");

            $hideList = array( 1, 2, 3, 4, 5, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 26, 33, 35, 37, 39, 41, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 75, 76, 77, 81, 82, 83, 84, 85, 87, 89, 90, 91 );

            // 検索項目で雇用形態が選択された場合に雇用形態名の非表示を解除する
            if( $searchArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_EMPLOYMENT') )
            {
                $hideList = array_diff( $hideList, array( 5 ) );
                $hideList = array_values( $hideList );
            }
            // 検索項目で役職が選択された場合に役職名の非表示を解除する
            if( !empty( $searchArray['mobileUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_POSITION') ) )
            {
                $hideList = array_diff( $hideList, array( 9 ) );
                $hideList = array_values( $hideList );
            }
            // 月または年での検索時に非表示となる項目の解除を行う
            if( $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') ||  $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR')  )
            {
                $hideList = array_diff( $hideList, array( 22, 33, 35, 37, 39, 41, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 76, 77, 91 ) );
                $hideList = array_values( $hideList );
            }
            // 検索条件で打刻状況を設定した場合に打刻状況の非表示を解除
            if( !empty( $searchArray['embossingSituation'] ) )
            {
                $hideList = array_diff( $hideList, array( 14 ) );
                $hideList = array_values( $hideList );
            }
            // 検索条件で打刻場所を設定した場合に打刻場所情報の非表示を解除
            if( !empty( $searchArray['embossingOrganizationID'] ) )
            {
                $hideList = array_diff( $hideList, array( 11, 12, 13 ) );
                $hideList = array_values( $hideList );
            }

            $Log->trace("END setHideIndividualList");

            return $hideList;
        }

        /**
         * 印刷画面ヘッダ部分用表示一覧
         * @note     印刷画面のヘッダ部分に表示させる文言一覧
         * @param    $searchArray
         * @return   $printHeaderList
         */
        private function setPrintHeaderList( $searchArray )
        {
            global $Log, $attendance;  // グローバル変数宣言
            $Log->trace("START setPrintHeaderList");

            $printHeaderList['dateTimeUnit'] = $searchArray['dateTimeUnit'];
            $printHeaderList['mobileUnit'] = $searchArray['mobileUnit'];
            $printHeaderList['periodRange'] = $this->setRangePeriodText( $searchArray );
            $printHeaderList['embossingOrganizationName'] = $attendance->getSearchTargetOrganizationName( $searchArray['embossingOrganizationID'] );
            $printHeaderList['embossingSituation'] = $searchArray['embossingSituation'];
            $printHeaderList['estimationShiftRange'] = $this->setRangeText( $searchArray['minEstimationShift'], $searchArray['maxEstimationShift'], "円" );
            $printHeaderList['estimationPerfoRange'] = $this->setRangeText( $searchArray['minEstimationPerfo'], $searchArray['maxEstimationPerfo'], "円" );
            $printHeaderList['absenteeismCntRange'] = $this->setRangeText( $searchArray['minAbsenteeismNo'], $searchArray['maxAbsenteeismNo'], "回" );
            $printHeaderList['lateCntRange'] = $this->setRangeText( $searchArray['minLateNo'], $searchArray['maxLateNo'], "回" );
            $printHeaderList['leaveCntRange'] = $this->setRangeText( $searchArray['minLeaveNo'], $searchArray['maxLeaveNo'], "回" );
            $printHeaderList['modifyCntRange'] = $this->setRangeText( $searchArray['minModifyNo'], $searchArray['maxModifyNo'], "回" );
            $printHeaderList['totalWorkTimeRange'] = $this->setRangeTimeText( $searchArray['minTotalWorkTime'], $searchArray['maxTotalWorkTime'] );
            $printHeaderList['overtimeRange'] = $this->setRangeTimeText( $searchArray['minOvertime'], $searchArray['maxOvertime'] );
            $printHeaderList['breakTimeRange'] = $this->setRangeTimeText( $searchArray['minBreakTime'], $searchArray['maxBreakTime'] );
            $printHeaderList['organization_name'] = $attendance->getSearchTargetOrganizationName( $searchArray['organizationID'] );
            $printHeaderList['position_name'] = $searchArray['position_name'];
            $printHeaderList['employment_name'] = $searchArray['employment_name'];
            $printHeaderList['personal_name'] = $searchArray['personal_name'];

            $Log->trace("END setPrintHeaderList");

            return $printHeaderList;
        }

        /**
         * 印刷画面ヘッダ部分用範囲表示
         * @note     印刷画面のヘッダ部分に表示させる文言
         * @param    $minVal
         * @param    $maxVal
         * @param    $unitCharacter
         * @return   $rangeText
         */
        private function setRangeText( $minVal, $maxVal, $unitCharacter )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setRangeText");

            $minText = "";
            $maxText = "";
            if( !empty( $minVal ))
            {
                $minText = $minVal. $unitCharacter . " 以上 ";
            }
            if( !empty( $maxVal ))
            {
                $maxText = $maxVal. $unitCharacter . " 以下";
            }

            $rangeText = $minText . $maxText;

            $Log->trace("END setRangeText");

            return $rangeText;
        }

        /**
         * 印刷画面ヘッダ部分用範囲表示(時間)
         * @note     印刷画面のヘッダ部分に表示させる文言
         * @param    $minTimeInt
         * @param    $maxTimeInt
         * @return   $rangeTimeText
         */
        private function setRangeTimeText( $minTimeInt, $maxTimeInt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setRangeTimeText");

            $minTimeText = "";
            $maxTimeText = "";
            if( !empty( $minTimeInt ) )
            {
                $minTime =  sprintf( '%02d', strval( ( $minTimeInt / 60 ) ) ) . ":" . sprintf( '%02d', strval( ( $minTimeInt % 60 ) ) );
                $minTimeText = $minTime . " 以上 ";
            }
            if( !empty( $maxTimeInt ) )
            {
                $maxTime =  sprintf( '%02d', strval( ( $maxTimeInt / 60 ) ) ) . ":" . sprintf( '%02d', strval( ( $maxTimeInt % 60 ) ) );
                $maxTimeText = $maxTime . " 以下 ";
            }

            $rangeTimeText = $minTimeText . $maxTimeText;

            $Log->trace("END setRangeTimeText");

            return $rangeTimeText;
        }

        /**
         * 印刷画面ヘッダ部分用範囲表示(期間)
         * @note     印刷画面のヘッダ部分に表示させる文言
         * @param    $searchArray
         * @return   $rangePeriodText
         */
        private function setRangePeriodText( $searchArray )
        {
            global $Log, $attendance;  // グローバル変数宣言
            $Log->trace("START setRangePeriodText");

            $startUnix = $attendance->setStartDateUnix( $searchArray['minPeriodSpecifiedYear'], $searchArray['minPeriodSpecifiedMonth'], $searchArray['minPeriodSpecifiedDay'] );
            $endUnix = $attendance->setEndDateUnix( $searchArray['maxPeriodSpecifiedYear'], $searchArray['maxPeriodSpecifiedMonth'], $searchArray['maxPeriodSpecifiedDay'] );
            // 指定期間開始日UNIXを年/月/日形式へ変更
            $startDate = $this->setPeriodText( $searchArray, $startUnix );
            // 指定期間終了日UNIXを年/月/日形式へ変更
            $endDate = $this->setPeriodText( $searchArray, $endUnix );

            $rangePeriodText = $startDate . " ～ " . $endDate;

            $Log->trace("END setRangePeriodText");

            return $rangePeriodText;
        }

        /**
         * 印刷画面ヘッダ部分用範囲表示(期間)
         * @note     印刷画面のヘッダ部分に表示させる文言
         * @param    $searchArray
         * @param    $dateUnix
         * @return   $periodText
         */
        private function setPeriodText( $searchArray, $dateUnix )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setPeriodText");

            if( $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_DAY') )
            {
                $dateText = date("Y年m月d日", $dateUnix);
            }
            else if( $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_MONTH') )
            {
                $dateText = date("Y年m月", $dateUnix);
            }
            else if( $searchArray['dateTimeUnit'] === $Log->getMsgLog('MSG_CUMULATIVE_YEAR') )
            {
                $dateText = date("Y年", $dateUnix);
            }
            else
            {
                $dateText = date("Y年m月d日", $dateUnix);
            }

            $Log->trace("END setPeriodText");

            return $dateText;
        }
        
        /**
         * 時間表示設定
         * @note     給与連携マスタの時間表示形式に合わせて、データを表示する
         * @param    $time          時間(分)
         * @param    $noDataFormat
         * @param    $displayFormat
         * @return   時間の表示形式
         */
        private function changeViewAttendanceTime( $time, $noDataFormat, $displayFormat )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeViewAttendanceTime");
            
            // 時間が0 かつ 時間なしの場合が空白の場合
            if( $time == 0 && $noDataFormat == 2 )
            {
                // ''を返す
                return '';
            }
            
            // 分を時刻形式に修正
            $ret = $this->changeTimeFromMinute( $time );
            
            // 時間の表示形式が、10進数の場合
            if( $displayFormat == 1 && $ret != '00:00' )
            {
                $list = explode( ":", $ret );
                $minute = $list[1] / 60;
                if( $minute == 0 )
                {
                    $ret = $list[0] . ".00";
                }
                else
                {
                    $ret = $list[0] + $minute;
                }
            }
            else if($displayFormat == 1 && $ret == '00:00')
            {
            	$ret = '0.00';
            }

            $Log->trace("END changeViewAttendanceTime");

            return $ret;
        }

    }
?>
