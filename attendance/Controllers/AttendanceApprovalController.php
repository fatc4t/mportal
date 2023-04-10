<?php
    /**
     * @file      勤怠承認コントローラ
     * @author    USE R.dendo
     * @date      2016/08/24
     * @version   1.00
     * @note      勤怠承認の内容表示/修正を行う
     */

    // BaseCheckAlertController.phpを読み込む
    require './Controllers/Common/BaseCheckAlertController.php';
    // 勤怠承認処理モデル
    require './Model/AttendanceApproval.php';

    /**
     * 勤怠承認コントローラクラス
     * @note    勤怠承認の内容表示/修正を行う
     */
    class AttendanceApprovalController extends BaseCheckAlertController
    {
        protected $attendanceApproval = null;     ///< 勤怠承認モデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseCheckAlertControllerのコンストラクタ
            parent::__construct();
            global $attendanceApproval;
            $attendanceApproval = new AttendanceApproval();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // BaseCheckAlertControllerのデストラクタ
            parent::__destruct();
        }

        /**
         * 勤怠承認画面 初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1430" );

            $selectUserID = '';
            $selectDate = '';

            if( !empty( $_POST['selectUserID'] ) )
            {
                $selectUserID = parent::escStr( $_POST['selectUserID']);
                $selectDate = parent::escStr( $_POST['selectDate']);
                if( $_POST['dateTimeUnit'] == "月" )
                {
                    $selectDate .= "/01";
                }
            }
            else
            {
                // ユーザ指定がない場合、ログインユーザのIDと当日を設定
                $selectUserID = $_SESSION["USER_ID"];
                $selectDate = date('Y/m/d');
            }

            $this->initialDisplay( true, $selectUserID ,$selectDate, 3);

            $Log->trace("END   showAction");
        }

        /**
         * 勤怠承認画面 印刷画面からの戻り処理
         * @return    なし
         */
        public function printReturnAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START printReturnAction");
            $Log->info( "MSG_INFO_1435" );

            $selectUserID = '';
            $selectDate = '';

            $selectUserID = parent::escStr( $_POST['userId']);
            $selectDate = parent::escStr( $_POST['date']);
            $isScreen = parent::escStr( $_POST['isScreen']);

            $this->initialDisplay( true, $selectUserID ,$selectDate, $isScreen );
            $Log->trace("END   printReturnAction");
        }

        /**
         * 勤怠承認画面 検索処理
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            $Log->info( "MSG_INFO_1431" );

            $selectUserID = '';
            $selectDate = '';

            if( !empty( $_POST['userId'] ) )
            {
                $selectUserID = parent::escStr( $_POST['userId']);
                $selectDate = parent::escStr( $_POST['date']);
                if( $_POST['dateTimeUnit'] == "月" )
                {
                    $selectDate .= "/01";
                }
            }
            else
            {
                // ユーザ指定がない場合、ログインユーザのIDと当日を設定
                $selectUserID = $_SESSION["USER_ID"];
                $selectDate = date('Y/m/d');
            }

            $this->initialDisplay( false, $selectUserID ,$selectDate);
            $Log->trace("END   searchAction");
        }

        /**
         * 勤怠承認画面 印刷表示
         * @return    なし
         */
        public function printAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START printAction");
            $Log->info( "MSG_INFO_1433" );

            $isPrint = true;

            $selectUserID = '';
            $selectDate = '';

            if( !empty( $_POST['userId'] ) )
            {
                $selectUserID = parent::escStr( $_POST['userId']);
                $selectDate = parent::escStr( $_POST['date']);
                if( $_POST['dateTimeUnit'] == "月" )
                {
                    $selectDate .= "/01";
                }
            }
            else
            {
                // ユーザ指定がない場合、ログインユーザのIDと当日を設定
                $selectUserID = $_SESSION["USER_ID"];
                $selectDate = date('Y/m/d');
            }

            $this->initialDisplay( false, $selectUserID ,$selectDate, 0, $isPrint);
            $Log->trace("END   printAction");
        }

        /**
         * 個人日別勤怠一括登録
         * @return    なし
         */
        public function addBulkAction()
        {
            global $Log, $attendanceApproval; // グローバル変数宣言
            $Log->trace( "START addBulkAction" );
            $Log->info( "MSG_INFO_1432" );

            $userIDList = array();
            $messege = "MSG_BASE_0000";
            $max = count( $_POST['attendanceId'] );
            for( $i = 0; $i < $max; $i++)
            {
                $postArray = $this->creatPostArrayForBulk( $i );
                
                $messege = $attendanceApproval->addNewData($postArray);
                if( $messege !== "MSG_BASE_0000" )
                {
                    // 更新エラー
                    break;
                }
                
                // ユーザIDを保持
                array_push( $userIDList, $postArray['userId'] );
            }

            // 勤怠〆対象のユーザリスト
            $attDeadlineList = array_unique( $userIDList );

            // 先頭データ取得
            $postArray = $this->creatPostArrayForBulk( 0 );

            // 勤怠〆テーブルの更新
            foreach( $attDeadlineList as $userID )
            {
                $messege = $attendanceApproval->attendanceDeadline( $userID, $postArray['date'] );
                if( $messege !== "MSG_BASE_0000" )
                {
                    // 更新エラー
                    break;
                }
            }

            // 情報の登録が全て成功したか？
            if( $messege === "MSG_BASE_0000" )
            {
                $this->initialDisplay( false, $postArray['userId'], $postArray['date'] );
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }

            $Log->trace("END   addBulkAction");
        }

        /**
         * 従業員リスト更新処理
         * @return    なし
         */
        public function searchUserListAction()
        {
            global $Log, $attendanceApproval; // グローバル変数宣言
            $Log->trace("START userListUpAction");
            $Log->info( "MSG_INFO_1434" );

            // 検索用ユーザリスト
            $searchUserNameList = $attendanceApproval->setPulldown->getSearchUserList( parent::escStr( $_POST['searchOrgID']) );      // ユーザリスト

            require_once '../FwCommon/View/SearchUserName.php';

            $Log->trace("END   userListUpAction");
        }
        
        /**
         * POSTされた値のエスケープ処理を行う
         * @note     追加 / 修正 /削除のPOST情報をエスケープ
         * @return   画面からの入力データ
         */
        private function creatPostArrayForBulk( $index )
        {
            global $Log, $attendanceApproval;  // グローバル変数宣言
            $Log->trace("START creatPostArrayForBulk");

            $date = parent::escStr( $_POST['date'][$index] );
            $approval = parent::escStr( $_POST['approval'][$index] ) == 'true' ? 1 : 0; 

            $postArray = array(
                'attendanceId'      => parent::escStr( $_POST['attendanceId'][$index] ),
                'date'              => $date,
                'userId'            => parent::escStr( $_POST['userId'][$index] ),
                'approval'          => $approval,
                'organizationID'    => parent::escStr( $_POST['organizationId'][$index] ),
                'updateTime'        => parent::escStr( $_POST['updateTime'][$index] ),
                'user_id'           => $_SESSION["USER_ID"],
                'organization'      => $_SESSION["ORGANIZATION_ID"],
            );

            $Log->trace("END   creatPostArrayForBulk");
            
            return $postArray;
        }
        
        /**
         * POSTされた手当のチェックボックスの値のエスケープ処理を行う
         * @note     追加 / 修正 /削除のPOST情報をエスケープ
         * @return   画面からの入力データ
         */
        private function creatManualAllowanceArray()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START creatManualAllowanceArray");
            
            $manualAllowanceArray = array(
                'organizationID'    => parent::escStr( $_POST['organizationId'] ),
            );
            
            $Log->trace("END   creatManualAllowanceArray");
            
            return $manualAllowanceArray;
        }

        /**
         * 表示画面の設定を行う
         * @note     表示画面のパラメータを設定
         * @return   なし
         */
        private function isScreen()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START isScreen");

            // 対象画面の設定が存在する
            if( !empty( $_POST['isScreen'] ) )
            {
                return $_POST['isScreen'];
            }

            $isScreen = 1;
            if( !empty( $_POST['dateTimeUnit'] ) && $_POST['dateTimeUnit'] === "月")
            {
                $isScreen = 2;
            }

            // 対象日一括修正である
            if( !empty( $_POST['targetDayBulk'] ) && $_POST['targetDayBulk'] == 3 )
            {
                $isScreen = 3;
            }

            $Log->trace("END   isScreen");
            
            return $isScreen;
        }

        /**
         * 対象日一括の場合、組織IDのを行う
         * @note     対象日一括の場合、組織IDを取得する
         * @return   なし
         */
        private function getOrganizatioID()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getOrganizatioID");

            $ret = 0;
            // 組織IDが未指定
            if( empty( $_POST['searchOrganizatioID'] ) )
            {
                // ログインユーザの組織IDを設定
                $ret = $_SESSION["ORGANIZATION_ID"];
            }
            else
            {
                // 指定の組織IDを設定
                $ret = $_POST['searchOrganizatioID'];
            }

            $Log->trace("END   getOrganizatioID");
            
            return $ret;
        }

        /**
         * 個人日別勤怠承認一覧画面
         * @note     勤怠承認画面全てを更新
         * @return   なし
         */
        private function initialDisplay( $isFullScreen, $selectUserID, $selectDate, $isScreen = 0, $isPrint = false )
        {
            global $Log, $TokenID, $attendanceApproval;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            // 勤怠一覧対象ユーザからの遷移時、ユーザのIDを代入
            $sendingUserID = $selectUserID;
            // 勤怠一覧対象ユーザからの遷移時、対象日を代入
            $sendingDate = $selectDate;

            // 表示画面を選択
            if( $isScreen == 0 )
            {
                $isScreen = $this->isScreen();
            }
            $searchOrganizatioID = 0;
            $targetDayBulk = 0;
            if( $isScreen == 3 )
            {
                // 対象日一括修正の場合、「$sendingUserID」へ組織IDを設定する
                $sendingUserID = $this->getOrganizatioID();
                $searchOrganizatioID = $sendingUserID;
                $targetDayBulk = 3;
            }

            // 勤怠承認一覧データ取得
            $attendanceCtAllList  = $attendanceApproval->getAttendanceData($sendingUserID, $sendingDate, $isScreen, $isEnrollment);
            // 割増手当期間データ取得
            $allowanceOrganID = $this->getOrganizatioID();
            $premiumAllowanceList = $attendanceApproval->getPremiumAllowanceList($allowanceOrganID, $sendingDate, $isScreen );

            // 勤怠一覧にデータがあった場合に対象従業員名をセットする
            $sendingName = $attendanceCtAllList[0]['user_name'];

            // 検索用組織プルダウン
            $abbreviatedNameList = $attendanceApproval->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト
            $organizationID = $attendanceApproval->getParentOrganization( $selectUserID );
            // 検索用ユーザリスト
            $searchUserNameList = $attendanceApproval->setPulldown->getSearchUserList( $organizationID );      // ユーザリスト
            $searchArray = array(
                                    'organizationID'  =>  $organizationID,
                                    'userID'          =>  $selectUserID,
                                );

            // 概算の表示非表示対応
            $estimateFlg = $attendanceApproval->getProposedBudgetList( $_SESSION["USER_ID"] );
            
            $securityProcess = new SecurityProcess();
            $accessAuthorityList = $securityProcess->getAccessAuthorityList();
            
            // アクセス権限によるボタン制御
            $isBtApproval = "disabled"; // 承認権限のボタン制御
            $viewOrgID = $this->getOrganizatioID(); // 検索(表示)した組織ID
            // 承認可能組織リスト
            $approvalList = $attendanceApproval->setPulldown->getAccessIDList( SystemParameters::$V_A_ATTENDANCE_APPROVAL, 'approval' );
            foreach( $approvalList as $val )
            {
                if( $viewOrgID == $val['organization_id'] )
                {
                    $isBtApproval = "";
                    break;
                }
            }
            
            // 従業員マスタで登録されている、承認組織リストを取得する
            $userApprovalList = $attendanceApproval->getUserApprovalList( $_SESSION["USER_ID"] );
            foreach( $userApprovalList as $val )
            {
                if( $viewOrgID == $val['organization_id'] )
                {
                    $isBtApproval = "";
                    break;
                }
            }
            
            $securityProcess = new SecurityProcess();
            $accessAuthorityList = $securityProcess->getAccessAuthorityList();
            
            // アクセス権限によるボタン制御
            $isBtMod = "";
            if( ( $accessAuthorityList['registration'] >= 5 ) ) 
            {
                $isBtMod = "disabled";
            }
            
            $isBtDel = "";
            if( ( $accessAuthorityList['delete'] >= 5 ) ) 
            {
                $isBtDel = "disabled";
            }

            // アクセス不可データを削除する
            if( $isBtMod == "disabled" && $isBtDel == "disabled" )
            {
                $maxCnt = count( $attendanceCtAllList );
                for( $i = $maxCnt; $i > 0; $i-- )
                {
                    // データ削除
                    if( $_SESSION["USER_ID"] != $attendanceCtAllList[$i-1]['user_id'] )
                    {
                        unset($attendanceCtAllList[$i-1]);
                    }
                }
            }
            
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
                                        'hourly_wage'               => '-',
                                        'user_name'                 => '-',
                                        'absence_count'             => 0,
                                        'late_time'                 => 0,
                                        'leave_early_time'          => 0,
                                    );

            // 集計する為、表示する行を全てループ
            foreach($attendanceCtAllList as &$attendanceCt)
            {
                // 対象日を設定
                $targetDate = $attendanceCt['date'];
                if( empty( $targetDate ) )
                {
                    $targetDate = $sendingDate;
                }
                // アラート設定
                $eMail = '';
                $attendanceCt['alert'] = $this->setAlert( $attendanceCt, $targetDate, $attendanceApproval, false, $eMail );

                // 割増期間の表示対応
                foreach( $premiumAllowanceList as $premiumAllowance )
                {
                    $targetData = $attendanceCt['date'];
                    if( is_null( $attendanceCt['date'] ) )
                    {
                        $targetData = $sendingDate;
                        $targetData = str_replace( "/", "-", $targetData );
                    }
                    // 同じ日付である
                    if( $targetData == $premiumAllowance['date'] )
                    {
                        // 既にアラートが存在するか
                        if( !empty( $attendanceCt['alert'] ) )
                        {
                            $attendanceCt['alert'] = $attendanceCt['alert'] . "<br>";
                        }
                        $attendanceCt['alert'] = $attendanceCt['alert'] . "※割増期間「" . $premiumAllowance['allowance_name'] . "」です。";
                        break;
                    }
                }
                
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
                // 欠勤
                $aggregateList['absence_count'] += $attendanceCt['absence_count'];
                // 遅刻
                $aggregateList['late_time'] += $attendanceCt['late_time'];
                // 早退
                $aggregateList['leave_early_time'] += $attendanceCt['leave_early_time'];
            }
            unset($attendanceCt);
            
            // 対象日一括修正である
            if( $isScreen == 3 )
            {
                // 同一ユーザの勤怠データ数のカウント
                $dateCountList = $this->getUserDateCountList($attendanceCtAllList);
            }
            else
            {
                // 同日の勤怠データ数のカウント
                $dateCountList = $this->getDateCountList($attendanceCtAllList);
            }
            
            // 集計行の時間(分)を時間に戻す
            $aggregateList['shift_working_time'] = $this->changeTimeFromMinute( $aggregateList['shift_working_time'] );
            $aggregateList['shift_night_working_time'] = $this->changeTimeFromMinute( $aggregateList['shift_night_working_time'] );
            $aggregateList['shift_break_time'] = $this->changeTimeFromMinute( $aggregateList['shift_break_time'] );
            $aggregateList['shift_overtime'] = $this->changeTimeFromMinute( $aggregateList['shift_overtime'] );
            $aggregateList['total_working_time'] = $this->changeTimeFromMinute( $aggregateList['total_working_time'] );
            $aggregateList['night_working_time'] = $this->changeTimeFromMinute( $aggregateList['night_working_time'] );
            $aggregateList['break_time'] = $this->changeTimeFromMinute( $aggregateList['break_time'] );
            $aggregateList['overtime'] = $this->changeTimeFromMinute( $aggregateList['overtime'] );
            $aggregateList['night_overtime'] = $this->changeTimeFromMinute( $aggregateList['night_overtime'] );
            array_push($attendanceCtAllList, $aggregateList);
            
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
            $roughEstimateSize = 50 * $roughEstimateCnt;
            
            // 表示行数を算出
            $viewCnt = count( $attendanceCtAllList );
            // ベースサイズ
            $baseSize = 1065;
            $baseHeadSize = 50;
            $isScrollBar = false;
            $isScrollSize = 0;

            if( $viewCnt > 18 )
            {
                $isScrollSize = 18;
                $isScrollBar = true;
            }

            // div(表示サイズを設定) 18pxはスクロールバー分
            $viewSize = $baseSize + $roughEstimateSize + $baseHeadSize + $isScrollSize;
            $dataTableSize = $viewSize;

            $viewTable = $dataTableSize;
            if( $isScrollBar )
            {
                $viewTable = $dataTableSize - 18;
            }

            // 印刷ではない
            if( !$isPrint )
            {
                if( $viewSize > 1260 )
                {
                    $viewSize = 1260;
                }
            }
            else
            {
                $viewSize = $viewSize - $isScrollSize;
                $isScrollBar = false;
                require_once './View/AttendanceApprovalPrintPanel.html';
                $Log->trace("END   initialDisplay");
                return;
            }

            // 登録用休日プルダウン
            $holidayNameList = $this->getHolidayList();
            
            // 全画面更新である
            if($isFullScreen)
            {
                require_once './View/AttendanceApprovalPanel.html';
            }
            else
            {
                require_once './View/AttendanceApprovalTablePanel.html';
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
            // 勤怠承認一覧データ取得
            foreach( $attendanceCtAllList as $val )
            {
                if( empty($ret[$val['date']]) )
                {
                    $ret[$val['date']] = 0;
                }
                $ret[$val['date']] = $ret[$val['date']] + 1;
                // アラートがある場合、さらに+1する
                if( $val['alert'] != '' )
                {
                    $ret[$val['date']] = $ret[$val['date']] + 1;
                }
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
            // 勤怠承認一覧データ取得
            foreach( $attendanceCtAllList as $val )
            {
                if( empty($ret[$val['user_id']]) )
                {
                    $ret[$val['user_id']] = 0;
                }
                $ret[$val['user_id']] = $ret[$val['user_id']] + 1;
                // アラートがある場合、さらに+1する
                if( $val['alert'] != '' )
                {
                    $ret[$val['user_id']] = $ret[$val['user_id']] + 1;
                }
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
            global $Log, $attendanceApproval;  // グローバル変数宣言
            $Log->trace("START getHolidayList");

            // 登録用休日プルダウン
            $holidayNameList = $attendanceApproval->setPulldown->getSearchHolidayNameList( 'registration' );
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
