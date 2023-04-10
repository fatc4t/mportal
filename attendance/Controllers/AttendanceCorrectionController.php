<?php
    /**
     * @file      勤怠修正画面(View)
     * @author    USE R.dendo
     * @date      2016/07/22
     * @version   1.00
     * @note      勤怠修正画面画面
     */

    // BaseCheckAlertController.phpを読み込む
    require './Controllers/Common/BaseCheckAlertController.php';
    // 勤怠修正処理モデル
    require './Model/AttendanceCorrection.php';

    /**
     * 勤怠修正コントローラクラス
     * @note    勤怠修正の内容表示/修正を行う
     */
    class AttendanceCorrectionController extends BaseCheckAlertController
    {
        protected $attendanceCorrection = null;     ///< 勤怠修正モデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseCheckAlertControllerのコンストラクタ
            parent::__construct();
            global $attendanceCorrection;
            $attendanceCorrection = new attendanceCorrection();
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
         * 勤怠修正画面 初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1420" );

            $selectUserID = '';
            $selectDate = '';

            if( !empty( $_POST['selectUserID'] ) )
            {
                $selectUserID = parent::escStr( $_POST['selectUserID']);
                $selectDate = parent::escStr( $_POST['selectDate']);
                if( $_POST['dateTimeUnit'] == "月" )
                {
                    if( strlen( $selectDate ) == 7 )
                    {
                        $selectDate .= "/01";
                    }
                }
            }
            else
            {
                // ユーザ指定がない場合、ログインユーザのIDと当日を設定
                $selectUserID = $_SESSION["ORGANIZATION_ID"];
                $selectDate = date('Y/m/d');
            }

            $this->initialDisplay( true, $selectUserID ,$selectDate, 3);

            $Log->trace("END   showAction");
        }

        /**
         * 勤怠一覧画面からの初期表示
         * @return    なし
         */
        public function showAttendanceViewAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAttendanceViewAction");
            $Log->info( "MSG_INFO_1428" );

            $selectUserID = parent::escStr( $_POST['selectUserID']);
            $selectDate = parent::escStr( $_POST['selectDate']);
            if( strlen( $selectDate ) == 7 )
            {
                $selectDate .= "/01";
            }

            $dateTimeUnit = 1;
            if( $_POST['dateTimeUnit'] == "月" )
            {
                $dateTimeUnit = 2;
            }

            $this->initialDisplay( true, $selectUserID ,$selectDate, $dateTimeUnit, false, true);

            $Log->trace("END   showAttendanceViewAction");
        }

        /**
         * 勤怠修正画面 検索処理
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            $Log->info( "MSG_INFO_1421" );

            $selectUserID = '';
            $selectDate = '';

            if( !empty( $_POST['userId'] ) )
            {
                $selectUserID = parent::escStr( $_POST['userId']);
                $selectDate = parent::escStr( $_POST['date']);
                if( $_POST['dateTimeUnit'] == "月" )
                {
                    if( strlen( $selectDate ) == 7 )
                    {
                        $selectDate .= "/01";
                    }
                }
            }
            else
            {
                // ユーザ指定がない場合、ログインユーザのIDと当日を設定
                $selectUserID = $_SESSION["ORGANIZATION_ID"];
                $selectDate = date('Y/m/d');
            }

            $this->initialDisplay( false, $selectUserID ,$selectDate);
            $Log->trace("END   searchAction");
        }

        /**
         * 勤怠修正画面新規登録
         * @return    なし
         */
        public function addAction()
        {
            global $Log, $attendanceCorrection; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1422" );

            $postArray = $this->creatPostArray();
            
            // 時間入力にエラーがないか
            if( $postArray['errMsg'] != '' )
            {
                // エラーメッセージを表示
                $userName = $attendanceCorrection->getUserName( $postArray['userId'], $postArray['date'] );
                $msg = substr( $postArray['date'], 0, 4 ) . '年' . substr( $postArray['date'], 5, 2 ) . "月" . substr( $postArray['date'], 8, 2 ) . "日";
                $msg = $msg . " " . $userName . "：";
                echo( $msg . $Log->getMsgLog( $postArray['errMsg'] ) );
                $Log->trace("END   addAction");
                return;
            }
            
            $messege = $attendanceCorrection->addNewData($postArray);

            $selectDate = parent::escStr( $_POST['searchdate']);
            if( $_POST['dateTimeUnit'] == "月" ){
                if( strlen( $selectDate ) == 7 ){
                    $selectDate .= "/01";
                }
            }

            if( $messege === "MSG_BASE_0000" )
            {
                $this->initialDisplay( false, $postArray['userId'] , $selectDate, parent::escStr( $_POST['isScreen'] ) );
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }

            $Log->trace("END   addAction");
        }

        /**
         * 個人日別勤怠修正画面修正
         * @return    なし
         */
        public function modAction()
        {
            global $Log, $attendanceCorrection; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1423" );

            // 勤怠IDがNULLの場合、新規追加を行う
            if( $_POST['attendanceId'] == null )
            {
                return $this->addAction();
            }

            $postArray = $this->creatPostArray();
            
            // 時間入力にエラーがないか
            if( $postArray['errMsg'] != '' )
            {
                // エラーメッセージを表示
                $userName = $attendanceCorrection->getUserName( $postArray['userId'], $postArray['date'] );
                $msg = substr( $postArray['date'], 0, 4 ) . '年' . substr( $postArray['date'], 5, 2 ) . "月" . substr( $postArray['date'], 8, 2 ) . "日";
                $msg = $msg . " " . $userName . "：";
                echo( $msg . $Log->getMsgLog( $postArray['errMsg'] ) );
                $Log->trace("END   modAction");
                return;
            }

            $selectDate = parent::escStr( $_POST['searchdate']);
            if( $_POST['dateTimeUnit'] == "月" ){
                if( strlen( $selectDate ) == 7 ){
                    $selectDate .= "/01";
                }
            }

            $messege = $attendanceCorrection->addNewData($postArray);
            if( $messege === "MSG_BASE_0000" )
            {
                $this->initialDisplay( false, $postArray['userId'] , $selectDate, parent::escStr( $_POST['isScreen'] ) );
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   modAction");
        }

        /**
         *  個人日別勤怠修正画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log, $attendanceCorrection; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1424" );

            $postArray = $this->creatPostArray();

            $selectDate = parent::escStr( $_POST['searchdate']);
            if( $_POST['dateTimeUnit'] == "月" ){
                if( strlen( $selectDate ) == 7 ){
                    $selectDate .= "/01";
                }
            }

            $messege = $attendanceCorrection->delUpdateData($postArray);
            
            if( $messege === "MSG_BASE_0000" )
            {
                $this->initialDisplay( false, $postArray['userId'] , $selectDate, parent::escStr( $_POST['isScreen'] ) );
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   delAction");
        }

        /**
         * 個人日別勤怠一括登録
         * @return    なし
         */
        public function addBulkAction()
        {
            global $Log, $attendanceCorrection; // グローバル変数宣言
            $Log->trace( "START addBulkAction" );
            $Log->info( "MSG_INFO_1425" );

            $messege = "MSG_BASE_0000";
            $max = count( $_POST['attendanceId'] );
            for( $i = 0; $i < $max; $i++)
            {
                $postArray = $this->creatPostArrayForBulk( $i );

                // 組織IDがない場合、又は承認済みデータ場合、次のデータへ
                if( $postArray['organizationID'] == "" || $postArray['approval'] == 1 )
                {
                    continue;
                }

                // 勤怠打刻がない場合又は、勤務状況も設定されていない場合は、登録しない
                if( $postArray['attendance_time'] == "" && $postArray['clock_out_time'] == "" &&
                    $postArray['s_break_time_1']  == "" && $postArray['e_break_time_1'] == "" &&
                    $postArray['s_break_time_2']  == "" && $postArray['e_break_time_2'] == "" &&
                    $postArray['s_break_time_3']  == "" && $postArray['e_break_time_3'] == "" &&
                    $postArray['isholiday'] == 0 )
                {
                    continue;
                }

                // 時間入力にエラーがないか
                if( $postArray['errMsg'] != '' )
                {
                    // エラーメッセージを表示
                    $userName = $attendanceCorrection->getUserName( $postArray['userId'], $postArray['date'] );
                    $msg = substr( $postArray['date'], 0, 4 ) . '年' . substr( $postArray['date'], 5, 2 ) . "月" . substr( $postArray['date'], 8, 2 ) . "日";
                    $msg = $msg . " " . $userName . "：";
                    echo( $msg . $Log->getMsgLog( $postArray['errMsg'] ) );
                    $Log->trace("END   addBulkAction");
                    return;
                }

                $messege = $attendanceCorrection->addNewData($postArray);
                if( $messege !== "MSG_BASE_0000" )
                {
                    // 更新エラー
                    break;
                }
            }
            
            // 先頭データ取得
            $postArray = $this->creatPostArrayForBulk( 0 );
            if( $messege === "MSG_BASE_0000" )
            {
                $this->initialDisplay( false, $postArray['userId'], $postArray['date'], parent::escStr( $_POST['isScreen'] ) );
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
            global $Log, $attendanceCorrection; // グローバル変数宣言
            $Log->trace("START userListUpAction");
            $Log->info( "MSG_INFO_1426" );

            // 検索用ユーザリスト
            $searchUserNameList = $attendanceCorrection->setPulldown->getSearchUserList( parent::escStr( $_POST['searchOrgID']) );      // ユーザリスト

            require_once '../FwCommon/View/SearchUserName.php';

            $Log->trace("END   userListUpAction");
        }

        /**
         * 新規入力エリアの更新処理
         * @return    なし
         */
        public function inputAreaAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START inputAreaAction");
            $Log->info( "MSG_INFO_1427" );

            $postArray = $this->creatPostArray();

            $selectDate = '';
            if( !empty( $_POST['userId'] ) )
            {
                $selectDate = parent::escStr( $_POST['date']);
                if( $_POST['dateTimeUnit'] == "月" )
                {
                    $selectDate .= "/01";
                }
            }

            $this->initialDisplay( false, $postArray['userId'] , $selectDate, 0, true );

            $Log->trace("END   inputAreaAction");
        }

        /**
         * POSTされた値のエスケープ処理を行う
         * @note     追加 / 修正 /削除のPOST情報をエスケープ
         * @return   画面からの入力データ
         */
        private function creatPostArray()
        {
            global $Log, $attendanceCorrection;  // グローバル変数宣言
            $Log->trace("START creatPostArray");

            $date = parent::escStr( $_POST['date'] );
            $isholiday = parent::escStr( $_POST['isholiday'] );

            $postArray = array(
                'attendanceId'      => parent::escStr( $_POST['attendanceId'] ),
                'date'              => $date,
                'userId'            => parent::escStr( $_POST['userId'] ),
                'approval'          => parent::escStr( $_POST['approval'] ),
                'organizationID'    => parent::escStr( $_POST['organizationId'] ),
                'isholiday'         => $isholiday,
                'attendance_time'   => $this->escapeDate( $date, $_POST['attendance_time'] ),
                'clock_out_time'    => $this->escapeDate( $date, $_POST['clock_out_time'] ),
                's_break_time_1'    => $this->escapeDate( $date, $_POST['s_break_time_1'] ),
                'e_break_time_1'    => $this->escapeDate( $date, $_POST['e_break_time_1'] ),
                's_break_time_2'    => $this->escapeDate( $date, $_POST['s_break_time_2'] ),
                'e_break_time_2'    => $this->escapeDate( $date, $_POST['e_break_time_2'] ),
                's_break_time_3'    => $this->escapeDate( $date, $_POST['s_break_time_3'] ),
                'e_break_time_3'    => $this->escapeDate( $date, $_POST['e_break_time_3'] ),
                'updateTime'        => parent::escStr( $_POST['updateTime'] ),
                'user_id'           => $_SESSION["USER_ID"],
                'organization'      => $_SESSION["ORGANIZATION_ID"],
            );
            
            // 手当分のリストを作成する
            $allowanceList = array();
            $manualList = $attendanceCorrection->setPulldown->getSearchManualAllowanceList( 'registration' );
            foreach( $manualList as $manual )
            {
                $key = "allowanceId_" . $manual['allowance_id'];
                if( !empty( $_POST[$key] ) )
                {
                    $allowance = array( $manual['allowance_id'] => $this->escStr( $_POST[$key] ) );
                    array_push( $allowanceList, $allowance );
                }
            }
            
            $postArray['allowanceList'] = $allowanceList;
            
            // 時間チェック
            $postArray['errMsg'] = $attendanceCorrection->isTimeConsistency( $postArray );
            
            // 出勤/欠勤のステータス時、isholidayは設定しない
            if( $isholiday == SystemParameters::$ATTENDANCE || $isholiday == SystemParameters::$ABSENCE )
            {
                $postArray['isholiday'] = 0;
            }
            
            $Log->trace("END   creatPostArray");
            
            return $postArray;
        }
        
        /**
         * POSTされた値のエスケープ処理を行う
         * @note     追加 / 修正 /削除のPOST情報をエスケープ
         * @return   画面からの入力データ
         */
        private function creatPostArrayForBulk( $index )
        {
            global $Log, $attendanceCorrection;  // グローバル変数宣言
            $Log->trace("START creatPostArrayForBulk");

            $date = parent::escStr( $_POST['date'][$index] );
            $isholiday = parent::escStr( $_POST['isholiday'][$index] );
        
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
            $manualList = $attendanceCorrection->setPulldown->getSearchManualAllowanceList( 'registration' );
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

            // 時間チェック
            $postArray['errMsg'] = $attendanceCorrection->isTimeConsistency( $postArray );

            // 出勤/欠勤のステータス時、isholidayは設定しない
            $isholiday = parent::escStr( $_POST['isholiday'][$index] );
            if( $isholiday == SystemParameters::$ATTENDANCE || $isholiday == SystemParameters::$ABSENCE )
            {
                $postArray['isholiday'] = 0;
            }

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
         * 打刻時間のエスケープ処理
         * @note     打刻時間のPOST情報をエスケープ
         * @return   エスケープ後の打刻データ
         */
        private function escapeDate( $date, $time )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START escapeDate");

            // 時間をエスケープする
            $eTime = parent::escStr( $time );
            
            // 時間が空白の場合、nullを返す
            if( $eTime == "" )
            {
                return null;
            }
            
            // 返り値(日付)を設定
            $ret = $date . " " . $eTime;
            
            // 24時間以上指定されている場合、日付と時間を修正する
            $hour = $this->changeMinuteFromTime($eTime);
            if( 1440 <= $hour )
            {
                $cDate = date('Y/m/d', strtotime( "+1 day", strtotime($date) ) );
                $cTime = $this->changeTimeFromMinute( ($hour - 1440) );
                $ret = $cDate . " " . $cTime;
            }
            
            $Log->trace("END   escapeDate");
            
            return $ret;
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
         * 個人日別勤怠修正一覧画面
         * @note     勤怠修正画面全てを更新
         * @return   なし
         */
        private function initialDisplay( $isFullScreen, $selectUserID, $selectDate, $isScreen = 0, $isNew = false, $isShowAttendance = false )
        {
            global $Log, $TokenID, $attendanceCorrection;  // グローバル変数宣言
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

            // 勤怠修正一覧データ取得
            $attendanceCtAllList = $attendanceCorrection->getAttendanceData($sendingUserID, $sendingDate, $isScreen, $isEnrollment);

            $allowanceAllList = array();
            // 手当テーブル一覧データ取得
            foreach( $attendanceCtAllList as $attendanceCtAll )
            {
                // 勤怠IDがある場合、手当情報を取得する
                if( !empty( $attendanceCtAll['attendance_id'] ) )
                {
                    $userID = $sendingUserID;
                    if( $targetDayBulk == 3 )
                    {
                        // 組織表示時は、勤怠に登録している、userIDを使用する
                        $userID = $attendanceCtAll['user_id'];
                    }
                    $allowanceAllList[$attendanceCtAll['attendance_id']] = $attendanceCorrection->getAllowanceTableList( $userID, $attendanceCtAll['attendance_id'] );
                }
            }

            // 勤怠一覧にデータがあった場合に対象従業員名をセットする
            $sendingName = $attendanceCtAllList[0]['user_name'];
            
            // 検索用組織プルダウン
            $abbreviatedNameList = $attendanceCorrection->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト
            
            $searchUserID = $selectUserID;
            if( $isScreen == 3 )
            {
                $searchUserID = $_SESSION["USER_ID"];
            }
            $organizationID = $attendanceCorrection->getParentOrganization( $searchUserID );
            // 検索用ユーザリスト
            $searchUserNameList = $attendanceCorrection->setPulldown->getSearchUserList( $organizationID );      // ユーザリスト
            $searchArray = array(
                                    'organizationID'  =>  $organizationID,
                                    'userID'          =>  $selectUserID,
                                );

            // 登録用組織プルダウン
            $abbreviatedList = $attendanceCorrection->setPulldown->getSearchOrganizationList( 'registration', true );
            // 登録用休日プルダウン
            $holidayNameList = $this->getHolidayList();

            // 手動手当リスト
            $manualAllowanceList = $attendanceCorrection->setPulldown->getSearchManualAllowanceList( 'registration' );
            // 概算の表示非表示対応
            $estimateFlg = $attendanceCorrection->getProposedBudgetList( $_SESSION["USER_ID"] );
            
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
                                        'user_name'                 => '-',
                                        'alert'                     => '',
                                    );

            if( !$isNew )
            {
                foreach($attendanceCtAllList as &$attendanceCt)
                {
                    // 時間のカンマ表示を削除
                    $attendanceCt['attendance_time'] = str_replace(":", "", $attendanceCt['attendance_time']);
                    $attendanceCt['clock_out_time']  = str_replace(":", "", $attendanceCt['clock_out_time']);
                    $attendanceCt['s_break_time_1']  = str_replace(":", "", $attendanceCt['s_break_time_1']);
                    $attendanceCt['e_break_time_1']  = str_replace(":", "", $attendanceCt['e_break_time_1']);
                    $attendanceCt['s_break_time_2']  = str_replace(":", "", $attendanceCt['s_break_time_2']);
                    $attendanceCt['e_break_time_2']  = str_replace(":", "", $attendanceCt['e_break_time_2']);
                    $attendanceCt['s_break_time_3']  = str_replace(":", "", $attendanceCt['s_break_time_3']);
                    $attendanceCt['e_break_time_3']  = str_replace(":", "", $attendanceCt['e_break_time_3']);
                    
                    // 対象日を設定
                    $targetDate = $attendanceCt['date'];
                    if( empty( $targetDate ) )
                    {
                        $targetDate = $sendingDate;
                    }
                    $eMail = '';
                    // アラート設定
                    $attendanceCt['alert'] = $this->setAlert( $attendanceCt, $targetDate, $attendanceCorrection, false, $eMail );

                    // 休日情報設定
                    if( $attendanceCt['is_holiday'] === null )
                    {
                        // 勤怠情報が未入力の場合のみ、勤務状況を取得する
                        $shiftInfo = array();
                        $shiftInfo['shift_id']   = empty( $attendanceCt['shift_id'] ) ? 0 : $attendanceCt['shift_id'];
                        $shiftInfo['is_holiday'] = empty( $attendanceCt['shift_is_holiday'] ) ? 0 : $attendanceCt['shift_is_holiday'];
                        $attendanceCt['is_holiday'] = $attendanceCorrection->getIsHoliday( $attendanceCt['user_id'], $targetDate, $shiftInfo );
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
                }
                unset($attendanceCt);
            }
            
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
            $heightSize   = '';
            if( $viewCnt > 18 )
            {
                $isScrollSize = 18;
                $isScrollBar = true;
                $heightSize  = 'height:500px;';
            }
            
            // div(表示サイズを設定) 18pxはスクロールバー分(+2は、セル結合の不足サイズの対応)
            $viewSize = $baseSize + $baseHeadSize + $roughEstimateSize + $manualAllowanceSize + $isScrollSize + 2;
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

            // 全画面更新である
            if($isFullScreen)
            {
                $dateTimeUnitDay = 'checked="checked"';
                $dateTimeUndateTimeUnitMonthitDay = '';
                $targetUnitOrg = 'checked="checked"';
                $targetUnitInd = '';
                // 検索値の初期値設定
                if( $isShowAttendance )
                {
                    $dateTimeUnitDay = 'checked="checked"';
                    $dateTimeUndateTimeUnitMonthitDay = '';
                    $targetUnitOrg = '';
                    $targetUnitInd = 'checked="checked"';
                }
                
                require_once './View/AttendanceCorrectionPanel.html';
            }
            else
            {
                // 新規入力エリアの更新
                if( $isNew == true )
                {
                    $isEnrollment = true;
                    // ユーザリスト作成
                    $userNameList = array();
                    $userNameList[''] = '';
                    foreach($attendanceCtAllList as $attendanceCt)
                    {
                        // 集計行の'-'は含めない
                        if( $attendanceCt['user_name'] != '-' )
                        {
                            $userNameList[$attendanceCt['user_id']] = $attendanceCt['user_name'];
                        }
                    }
                    require_once './View/AttendanceCorrectionInputPanel.html';
                }
                else
                {
                    require_once './View/AttendanceCorrectionTablePanel.html';
                }
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
            // 勤怠修正一覧データ取得
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
            global $Log, $attendanceCorrection;  // グローバル変数宣言
            $Log->trace("START getHolidayList");

            // 登録用休日プルダウン
            $holidayNameList = $attendanceCorrection->setPulldown->getSearchHolidayNameList( 'registration' );
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
