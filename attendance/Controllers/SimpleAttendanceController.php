<?php
    /**
     * @file      簡易勤怠画面(コントローラ)
     * @author    USE Y.Sakata
     * @date      2016/09/30
     * @version   1.00
     * @note      簡易勤怠画面
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 簡易勤怠モデル
    require './Model/SimpleAttendance.php';

    /**
     * 簡易勤怠コントローラクラス
     * @note    勤怠修正の内容表示/修正を行う
     */
    class SimpleAttendanceController extends BaseController
    {
        protected $simpleAttendance = null;     ///< 簡易勤怠モデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
            global $simpleAttendance;
            $simpleAttendance = new SimpleAttendance();
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
         * 勤怠確認画面 初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1440" );

            // ログインユーザのIDと当日を設定
            $selectUserID = $_SESSION["USER_ID"];
            $selectDate = date('Y/m/d');


            $this->initialDisplay( true, $selectUserID ,$selectDate );

            $Log->trace("END   showAction");
        }

        /**
         * 勤怠確認画面 印刷ページからの戻り表示
         * @return    なし
         */
        public function printReturnAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1443" );

            // ログインユーザのIDと当日を設定
            $selectUserID = '';
            $selectDate = '';

            $selectUserID = parent::escStr( $_POST['userId']);
            $selectDate = parent::escStr( $_POST['date']);
            $selectDate .= "/01";

            $this->initialDisplay( true, $selectUserID ,$selectDate );

            $Log->trace("END   showAction");
        }

        /**
         * 勤怠確認画面 検索処理
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            $Log->info( "MSG_INFO_1441" );

            $selectUserID = '';
            $selectDate = '';

            $selectUserID = parent::escStr( $_POST['userId']);
            $selectDate = parent::escStr( $_POST['date']);
            $selectDate .= "/01";

            $this->initialDisplay( false, $selectUserID ,$selectDate);
            $Log->trace("END   searchAction");
        }

        /**
         * 勤怠確認画面 印刷処理
         * @return    なし
         */
        public function printAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START printAction");
            $Log->info( "MSG_INFO_1442" );

            $selectUserID = '';
            $selectDate = '';

            $selectUserID = parent::escStr( $_POST['userId']);
            $selectDate = parent::escStr( $_POST['date']);
            $selectDate .= "/01";

            $this->initialDisplay( false, $selectUserID ,$selectDate, true);
            $Log->trace("END   printAction");
        }

        /**
         * POSTされた値のエスケープ処理を行う
         * @note     追加 / 修正 /削除のPOST情報をエスケープ
         * @return   画面からの入力データ
         */
        private function creatPostArrayForBulk( $index )
        {
            global $Log, $simpleAttendance;  // グローバル変数宣言
            $Log->trace("START creatPostArrayForBulk");

            $date = parent::escStr( $_POST['date'][$index] );

            // 出勤/欠勤のステータス時、isholidayは設定しない
            $isholiday = parent::escStr( $_POST['isholiday'][$index] );
            if( $isholiday == SystemParameters::$ATTENDANCE || $isholiday == SystemParameters::$ABSENCE )
            {
                $isholiday = 0;
            }

            $postArray = array(
                'attendanceId'      => parent::escStr( $_POST['attendanceId'][$index] ),
                'date'              => $date,
                'userId'            => parent::escStr( $_POST['userId'][$index] ),
                'approval'          => parent::escStr( $_POST['approval'][$index] ),
                'organizationID'    => parent::escStr( $_POST['organizationId'][$index] ),
                'isholiday'         => $isholiday,
                'attendance_time'   => $this->escapeDate( $date, $_POST['attendance_time'][$index] ),
                'clock_out_time'    => $this->escapeDate( $date, $_POST['clock_out_time'][$index] ),
                's_break_time_1'    => $this->escapeDate( $date, $_POST['s_break_time_1'][$index] ),
                'e_break_time_1'    => $this->escapeDate( $date, $_POST['e_break_time_1'][$index] ),
                's_break_time_2'    => $this->escapeDate( $date, $_POST['s_break_time_2'][$index] ),
                'e_break_time_2'    => $this->escapeDate( $date, $_POST['e_break_time_2'][$index] ),
                's_break_time_3'    => $this->escapeDate( $date, $_POST['s_break_time_3'][$index] ),
                'e_break_time_3'    => $this->escapeDate( $date, $_POST['e_break_time_3'][$index] ),
                'updateTime'        => parent::escStr( $_POST['updateTime'][$index] ),
                'user_id'           => $_SESSION["USER_ID"],
                'organization'      => $_SESSION["ORGANIZATION_ID"],
            );
            
            // 手当分のリストを作成する
            $allowanceList = array();
            $manualList = $simpleAttendance->setPulldown->getSearchManualAllowanceList( 'registration' );
            foreach( $manualList as $manual )
            {
                $key = "allowanceId_" . $manual['allowance_id'];
                if( !empty( $_POST[$key][$index] ) )
                {
                    $allowance = array( $manual['allowance_id'] => $this->escStr( $_POST[$key][$index] ) );
                    array_push( $allowanceList, $allowance );
                }
            }
            
            $postArray['allowanceList'] = $allowanceList;

            $Log->trace("END   creatPostArrayForBulk");
            
            return $postArray;
        }

        /**
         * 個人日別勤怠修正一覧画面
         * @note     勤怠修正画面全てを更新
         * @return   なし
         */
        private function initialDisplay( $isFullScreen, $selectUserID, $selectDate, $isPrint = false )
        {
            global $Log, $TokenID, $simpleAttendance;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            // 勤怠一覧対象ユーザからの遷移時、ユーザのIDを代入
            $sendingUserID = $selectUserID;
            // 勤怠一覧対象ユーザからの遷移時、対象日を代入
            $sendingDate = $selectDate;

            // 表示画面を選択
            $isScreen = 2;

            $searchOrganizatioID = 0;
            $targetDayBulk = 0;

            // 勤怠修正一覧データ取得
            $attendanceCtAllList = $simpleAttendance->getAttendanceData($sendingUserID, $sendingDate, $isScreen, $isEnrollment);

            $allowanceAllList = array();
            // 手当テーブル一覧データ取得
            foreach( $attendanceCtAllList as $attendanceCtAll )
            {
                $allowanceAllList[$attendanceCtAll['attendance_id']] = $simpleAttendance->getAllowanceTableList( $sendingUserID, $attendanceCtAll['attendance_id'] );
            }

            // 勤怠一覧にデータがあった場合に対象従業員名をセットする
            $sendingName = $attendanceCtAllList[0]['user_name'];
            
            // 同日の勤怠データ数のカウント
            $dateCountList = $this->getDateCountList($attendanceCtAllList);
            
            $searchUserID = $selectUserID;

            // 集計行を追加する
            $aggregateList = array(
                                        'date'                      => '-',
                                        'shift_attendance_time'     => '-',
                                        'shift_taikin_time'         => '-',
                                        'shift_working_time'        => 0,
                                        'shift_night_working_time'  => 0,
                                        'shift_break_time'          => 0,
                                        'shift_overtime'            => 0,
                                        'embossing_attendance_time' => '-',
                                        'embossing_clock_out_time'  => '-',
                                        'attendance_time'           => '-',
                                        'clock_out_time'            => '-',
                                        's_break_time_1'            => '-',
                                        'e_break_time_1'            => '-',
                                        's_break_time_2'            => '-',
                                        'e_break_time_2'            => '-',
                                        's_break_time_3'            => '-',
                                        'e_break_time_3'            => '-',
                                        'total_working_time'        => 0,
                                        'night_working_time'        => 0,
                                        'night_overtime'            => 0,
                                        'break_time'                => 0,
                                        'overtime'                  => 0,
                                        'rough_estimate'            => 0,
                                        'shift_rough_estimate'      => 0,
                                        'user_name'                 => '-',
                                    );
            foreach($attendanceCtAllList as &$attendanceCt)
            {
                // シフトの労働時間
                $aggregateList['shift_working_time'] += $this->changeMinuteFromTime( $attendanceCt['shift_working_time'] );
                // シフトの深夜労働時間
                $aggregateList['shift_night_working_time'] += $this->changeMinuteFromTime( $attendanceCt['shift_night_working_time'] );
                // シフトの休憩時間
                $aggregateList['shift_break_time'] += $this->changeMinuteFromTime( $attendanceCt['shift_break_time'] );
                // シフトの残業時間
                $aggregateList['shift_overtime'] += $this->changeMinuteFromTime( $attendanceCt['shift_overtime'] );
                // 実労働時間
                $aggregateList['total_working_time'] += $this->changeMinuteFromTime( $attendanceCt['total_working_time'] );
                // 実深夜労働時間
                $aggregateList['night_working_time'] += $this->changeMinuteFromTime( $attendanceCt['night_working_time'] );
                // 実休憩時間
                $aggregateList['break_time'] += $this->changeMinuteFromTime( $attendanceCt['break_time'] );
                // 実残業時間
                $aggregateList['overtime'] += $this->changeMinuteFromTime( $attendanceCt['overtime'] );
                // 深夜残業時間
                $aggregateList['night_overtime'] += $this->changeMinuteFromTime( $attendanceCt['night_overtime'] );
                // 概算実績
                $aggregateList['rough_estimate'] += $attendanceCt['rough_estimate'];
                // 概算シフト
                $aggregateList['shift_rough_estimate'] += $attendanceCt['shift_rough_estimate'];
            }
            unset($attendanceCt);
            
            // 集計行の時間(分)を時間に戻す
            $aggregateList['shift_working_time'] = $this->changeTimeFromMinute( $aggregateList['shift_working_time'] );
            $aggregateList['shift_night_working_time'] = $this->changeTimeFromMinute( $aggregateList['shift_night_working_time'] );
            $aggregateList['shift_break_time'] = $this->changeTimeFromMinute( $aggregateList['shift_break_time'] );
            $aggregateList['shift_overtime'] = $this->changeTimeFromMinute( $aggregateList['shift_overtime'] );
            $aggregateList['total_working_time'] = $this->changeTimeFromMinute( $aggregateList['total_working_time'] );
            $aggregateList['night_working_time'] = $this->changeTimeFromMinute( $aggregateList['night_working_time'] );
            $aggregateList['night_overtime'] = $this->changeTimeFromMinute( $aggregateList['night_overtime'] );
            $aggregateList['break_time'] = $this->changeTimeFromMinute( $aggregateList['break_time'] );
            $aggregateList['overtime'] = $this->changeTimeFromMinute( $aggregateList['overtime'] );
            array_push($attendanceCtAllList, $aggregateList);
            
            // テーブルの長さを作成
            // 手当の分の長さを取得
            $manualAllowanceCnt  = count( $manualAllowanceList );
            $manualAllowanceSize = $manualAllowanceCnt * 45;
            
            // 概算の長さを取得
            $roughEstimateCnt = 0;
            if( $estimateFlg[78] === true && $estimateFlg[79] === true )
            {
                $roughEstimateCnt = 2;
            }
            else if( $estimateFlg[78] === true || $estimateFlg[79] === true )
            {
                $roughEstimateCnt = 1;
            }
            $roughEstimateSize = 45 * $roughEstimateCnt;
            
            // 表示行数を算出
            $viewCnt = count( $attendanceCtAllList );
            // ベースサイズ
            $baseSize = 1000; 
            $baseHeadSize = 100;
            $isScrollBar = false;
            $isScrollSize = 0;
            
            // div(表示サイズを設定) 18pxはスクロールバー分
            $viewSize = $baseSize + $baseHeadSize + $roughEstimateSize + $manualAllowanceSize + $isScrollSize;
            $dataTableSize = $viewSize;
            if( $viewSize > 1260 )
            {
                $viewSize = 1260;
            }

            $holidayNameList = $this->getHolidayList();
            $screenName = "勤怠確認画面";
            
            // ヘッダなし
            $isNoHeader = true;
            
            // 印刷
            if($isPrint)
            {
                require_once './View/SimpleAttendancePrintPanel.html';
            }
            // 全画面更新である
            else if($isFullScreen)
            {
                require_once './View/SimpleAttendancePanel.html';
            }
            else
            {
                // テーブルエリアの更新
                require_once './View/SimpleAttendanceTablePanel.html';
            }

            $Log->trace("END   initialDisplay");
        }
        
        /**
         * 一覧表で、同日日付の数をカウントしたリストを作成
         * @note     表示時に、同日のセルを結合する際に使用する
         * @return   日付カウント
         */
        private function getDateCountList( $attendanceCtAllList )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getDateCountList");

            $ret = array();
            // 勤怠修正一覧データ取得
            foreach( $attendanceCtAllList as $val )
            {
                if( empty($ret[$val['date']]) )
                {
                    $ret[$val['date']] = 0;
                }
                $ret[$val['date']] = $ret[$val['date']] + 1;
            }

            $Log->trace("END   getDateCountList");
            
            return $ret;
        }

        /**
         * 一覧表で、同一ユーザの数をカウントしたリストを作成
         * @note     表示時に、同日のセルを結合する際に使用する
         * @return   ユーザ数カウント
         */
        private function getUserDateCountList( $attendanceCtAllList )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getUserDateCountList");

            $ret = array();
            // 勤怠修正一覧データ取得
            foreach( $attendanceCtAllList as $val )
            {
                if( empty($ret[$val['user_id']]) )
                {
                    $ret[$val['user_id']] = 0;
                }
                $ret[$val['user_id']] = $ret[$val['user_id']] + 1;
            }

            $Log->trace("END   getUserDateCountList");
            
            return $ret;
        }

        /**
         * 出勤状況リスト取得
         * @note     休日マスタ情報+公休日+法定休日+出勤+欠勤
         * @return   出勤状況リスト
         */
        private function getHolidayList()
        {
            global $Log, $simpleAttendance;  // グローバル変数宣言
            $Log->trace("START getHolidayList");

            // 登録用休日プルダウン
            $holidayNameList = $simpleAttendance->setPulldown->getSearchHolidayNameList( 'registration' );
            array_shift($holidayNameList);
            $holiday = array( 
                                'holiday_id'    =>  SystemParameters::$PUBLIC_HOLIDAY,
                                'holiday'       =>  '公休日',
                            );
            array_unshift($holidayNameList, $holiday);
            $holiday = array( 
                                'holiday_id'    =>  SystemParameters::$STATUTORY_HOLIDAY,
                                'holiday'       =>  '法定休日',
                            );
            array_unshift($holidayNameList, $holiday);
            $holiday = array( 
                                'holiday_id'    =>  SystemParameters::$ABSENCE,
                                'holiday'       =>  '欠勤',
                            );
            array_unshift($holidayNameList, $holiday);
            $holiday = array( 
                                'holiday_id'    =>  SystemParameters::$ATTENDANCE,
                                'holiday'       =>  '出勤',
                            );
            array_unshift($holidayNameList, $holiday);
            $holiday = array( 
                                'holiday_id'    =>  0,
                                'holiday'       =>  '',
                            );
            array_unshift($holidayNameList, $holiday);

            $Log->trace("END   getHolidayList");
            
            return $holidayNameList;
        }

    }
?>
