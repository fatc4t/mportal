<?php
    /**
     * @file      就業規則コントローラ
     * @author    USE S.Kasai
     * @date      2016/06/29
     * @version   1.00
     * @note      就業規則の新規登録/修正/削除を行う
     */

    // LaborRegulationsAddController.phpを読み込む
    require './Controllers/BaseLaborRegulationsController.php';

    /**
     * 就業規則コントローラクラス
     * @note   就業規則の新規登録/修正/削除を行う
     */
    class LaborRegulationsController extends BaseLaborRegulationsController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
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
         * 就業規則一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1100" );

            $startFlag = true;
            if(isset($_POST['back']))
            {
                $startFlag = false;
            }

            $this->initialDisplay($startFlag);
            $Log->trace("END   showAction");
        }
        
        /**
         * 就業規則一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1101" );

            if(isset($_POST['displayPageNo']))
            {
                $_SESSION['PAGE_NO'] = parent::escStr( $_POST['displayPageNo'] );
            }

            if(isset($_POST['displayRecordCnt']))
            {
                $_SESSION['DISPLAY_RECORD_CNT'] = parent::escStr( $_POST['displayRecordCnt'] );
            }

            $this->initialList(false);
            
            $Log->trace("END   searchAction");
        }
        
        /**
         * 就業規則情報入力画面遷移
         * @return    なし
         */
        public function addInputAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addInputAction" );
            $Log->info( "MSG_INFO_1102" );
            
            $isInit = false;
            if(isset($_POST['reset']))
            {
                $isInit = true;
            }
            
            $this->laborRegulationsInputPanelDisplay($isInit);

            $Log->trace("END addInputAction");
            
        }
        
        /**
         * 就業規則新規登録処理
         * @return    なし
         */
        public function addAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1103" );

            // 新規登録
            $this->updateProcess(true);

            $Log->trace("END addAction");
        }
        
        /**
         * 就業規則更新処理
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START modAction" );
            $Log->info( "MSG_INFO_1104" );

            // 更新処理
            $this->updateProcess(false);

            $Log->trace("END modAction");
        }
        
        /**
         * 就業規則一覧画面削除
         * @return    なし
         */
        public function delAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START delAction" );
            $Log->info( "MSG_INFO_1105" );

            $laborRegulations = new laborRegulations();
            $startFlag = false;

            $postArray = array(
                'laborRegulationsID'      => parent::escStr( $_POST['laborRegulationsID'] ),
                'applicationDateId'       => parent::escStr( $_POST['applicationDateId'] ),
                'applicationDateStart'    => parent::escStr( $_POST['applicationDateStart'] ),
                'updateTime'              => parent::escStr( $_POST['updateTime'] ),
                'is_del'                  => 1,
                'user_id'                 => $_SESSION["USER_ID"],
                'organization'            => $_SESSION["ORGANIZATION_ID"],
            );

            $message = $laborRegulations->delUpdateData($postArray);

            if($message === "MSG_BASE_0000")
            {
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END delAction");
        }
        
        /**
         * 就業規則チェック情報更新
         * @return    なし
         */
        public function checkLaborStandardsActInfoAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START checkLaborStandardsActInfoAction" );
            $Log->info( "MSG_INFO_1107" );

            $laborRegulations = new laborRegulations();
            $startFlag = false;

            // 労働時間マスタ情報を取得する
            $laborStandardsAct = $laborRegulations->getLaborStandardsAct( parent::escStr( $_POST['applicationDateStart'] ) );
            $mAlert = $laborRegulations->getAlertM();

            // アラートチェックJSの更新
            require_once './View/Common/setLaborStandardsAct.php';

            $Log->trace("END checkLaborStandardsActInfoAction");
        }

        /**
         * 就業規則入力画面
         * @note     パラメータは、View側で使用
         * @return   無
         */
        protected function laborRegulationsInputPanelDisplay( $isInit = false )
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START laborRegulationsInputPanelDisplay" );

            $laborRegulations = new laborRegulations();
            $CorrectionFlag = false;      // 修正時フラグ（false）
            
            $laborRegDataList = $this->initialization();    // データの初期化
            $breakTimeList = $laborRegulations->getBreakTimeList( 0,0 );
            $breakTimeCsvList = $this->changeCsvList($breakTimeList);
            $shiftBreakList = $laborRegulations->getShiftBreakTimeList( 0,0 );
            $shiftBreakCsvList = $this->changeCsvList($shiftBreakList);
            $breakZoneList = $laborRegulations->getBreakTimeZone( 0,0 );
            $breakZoneCsvList = $this->changeCsvList($breakZoneList);
            $overTimeList = $laborRegulations->getOvertime( 0,0 );
            $overTimeCsvList = $this->changeCsvList($overTimeList);
            $closingDateSetId = '';
            $hourlyChangeList = $laborRegulations->getHourlyWageChange( 0,0 );
            $hourlyChangeCsvList = $this->changeCsvList($hourlyChangeList);

            // 登録データ更新時
            if(!empty($_POST['laborRegulationsID']))
            {
                $laborRegDataList = $laborRegulations->getLaborRegulationsData( parent::escStr($_POST['laborRegulationsID']), parent::escStr($_POST['applicationDateId']) );
                $breakTimeList = $laborRegulations->getBreakTimeList( parent::escStr( $_POST['laborRegulationsID'] ), parent::escStr($_POST['applicationDateId']) ); // 拘束時間と付与時間をそれぞれを1元配列に入れ直しjavascriptへ受け渡すため、CSV形式へ変換する
                $breakTimeCsvList = $this->changeCsvList($breakTimeList);
                $shiftBreakList = $laborRegulations->getShiftBreakTimeList( parent::escStr( $_POST['laborRegulationsID'] ), parent::escStr($_POST['applicationDateId']) ); // 出勤時間と付与時間をそれぞれを1元配列に入れ直しjavascriptへ受け渡すため、CSV形式へ変換する
                $shiftBreakCsvList = $this->changeCsvList($shiftBreakList);
                $breakZoneList = $laborRegulations->getBreakTimeZone( parent::escStr( $_POST['laborRegulationsID'] ), parent::escStr($_POST['applicationDateId']) ); // 休憩開始時間と休憩終了時間をそれぞれを1元配列に入れ直しjavascriptへ受け渡すため、CSV形式へ変換する
                $breakZoneCsvList = $this->changeCsvList($breakZoneList);
                $overTimeList = $laborRegulations->getOvertime( parent::escStr( $_POST['laborRegulationsID'] ), parent::escStr($_POST['applicationDateId']) ); //所定時間と残業時間
                $overTimeCsvList = $this->changeCsvList($overTimeList);
                $closingDateSetId = $laborRegulations->getClosingDateSetId( parent::escStr( $_POST['laborRegulationsID'] ), parent::escStr($_POST['applicationDateId']) ); // 残業設定の〆時間
                $hourlyChangeList = $laborRegulations->getHourlyWageChange( parent::escStr($_POST['laborRegulationsID']), parent::escStr($_POST['applicationDateId']) ); //時給変更時間帯と設定時間帯手当代
                $hourlyChangeCsvList = $this->changeCsvList($hourlyChangeList);
                $del_disabled = $this->checkDeleteTarget($laborRegDataList['organization_id']); // 対象就業規則を作成した組織に対して削除権限があるか判定
                $CorrectionFlag = true;
            }
            $abbreviatedList = $laborRegulations->setPulldown->getSearchOrganizationList( 'registration', true );          // 登録用組織設定プルダウン
            $timeTypeNameList = $laborRegulations->setPulldown->getSearchTimeTypeList( 'registration' );                   // 登録用時間設定プルダウン
            $overtimeSetNameList = $laborRegulations->setPulldown->getSearchOvertimeSettingList( 'registration' );         // 登録用残業設定プルダウン
            $legalInNameList = $laborRegulations->setPulldown->getSearchLegalTimeInOvertimeList( 'registration' );         // 登録用法定内残業代プルダウン
            $legalOutNameList = $laborRegulations->setPulldown->getSearchLegalTimeOutOvertimeList( 'registration' );       // 登録用法定外残業代プルダウン
            $legHolidayNameList = $laborRegulations->setPulldown->getSearchLegalHolidayAllowanceList( 'registration' );    // 登録用法定休日残業代プルダウン
            $prescribedNameList = $laborRegulations->setPulldown->getSearchPrescribedHolidayList( 'registration' );        // 登録用公休日残業代プルダウン
            $lateAtNightNameList = $laborRegulations->setPulldown->getSearchLateAtNightOutOvertimeList( 'registration' );  // 登録用深夜残業代プルダウン
            $earlyShiftNameList = $laborRegulations->setPulldown->getSearchEarlyShiftOvertimeList( 'registration' );       // 登録用早出残業認定種別プルダウン
            $weekCutDateNameList = $laborRegulations->setPulldown->getSearchWeekCutoffDateList( 'registration' );          // 登録用週締日種別プルダウン
            $trialPeriodList = $laborRegulations->setPulldown->getSearchTrialPeriodCrieriaList( 'registration' );          // 登録用試用期間プルダウン
            $settingTimeNameList = $laborRegulations->setPulldown->getSearchSettingTimeAllowanceList( 'registration' );    // 登録用設定時間帯手当代プルダウン
            
            // 労働時間マスタ情報を取得する
            $laborStandardsAct = $laborRegulations->getLaborStandardsAct( $laborRegDataList['application_date_start'] );
            $mAlert = $laborRegulations->getAlertM();

            // 初期化処理である
            if( $isInit )
            {
                // 残業時間の設定を法律の基準値に合わせて設定
                $laborRegDataList['legal_time_in_overtime'] = $laborStandardsAct['legal_in_overtime'];                              // 法定内残業時間代の初期値
                $laborRegDataList['legal_time_in_overtime_value'] = $laborStandardsAct['legal_in_overtime_set_value' ];             // 法定内残業時間の値の初期値
                $laborRegDataList['legal_time_out_overtime'] = $laborStandardsAct['legal_out_overtime'];                            // 法定外残業時間代の初期値
                $laborRegDataList['legal_time_out_overtime_value'] = $laborStandardsAct['legal_out_overtime_set_value'];            // 法定外残業時間の値の初期値
                $laborRegDataList['legal_time_out_overtime_45'] = $laborStandardsAct['legal_out_overtime_45'];                      // 法定外残業時間代(45H)の初期値
                $laborRegDataList['legal_time_out_overtime_value_45'] = $laborStandardsAct['legal_out_overtime_set_value_45' ];     // 法定外残業時間(45H)の値の初期値
                $laborRegDataList['legal_time_out_overtime_60'] = $laborStandardsAct['legal_out_overtime_60'];                      // 法定外残業時間代(60H)の初期値
                $laborRegDataList['legal_time_out_overtime_value_60'] = $laborStandardsAct['legal_out_overtime_set_value_60' ];     // 法定外残業時間(60H)の値の初期値
                $laborRegDataList['late_at_night_start'] = $laborStandardsAct['late_night_work_start_time1'];                       // 深夜労働開始時間
                $laborRegDataList['late_at_night_end'] = $laborStandardsAct['late_night_work_end_time1'];                           // 深夜労働終了時間
                $laborRegDataList['late_at_night_out_overtime'] = $laborStandardsAct['late_night_work'];                            // 深夜労働時間の初期値
                $laborRegDataList['late_at_night_out_overtime_value'] = $laborStandardsAct['late_night_work_set_value'];            // 深夜労働時間の値の初期値
                $laborRegDataList['legal_holiday_allowance'] = $laborStandardsAct['statutory_holiday_overtime'];                    // 法定休日残業時間代の初期値
                $laborRegDataList['legal_holiday_allowance_value'] = $laborStandardsAct['statutory_holiday_overtime_set_value'];    // 法定休日残業時間の値の初期値
                $laborRegDataList['prescribed_holiday_allowance'] = $laborStandardsAct['public_holiday_overtime'];                  // 公休日残業時間代の初期値
                $laborRegDataList['prescribed_holiday_allowance_value'] = $laborStandardsAct['public_holiday_overtime_set_value'];  // 公休日残業時間の値の初期値
            }
            $viewOverTimeList = $this->creatOverTimeSettingList();   // 所定時間/残業時間の作成用リスト
            $Log->trace("END laborRegulationsInputPanelDisplay");
            require_once './View/LaborRegulationsInputPanel.html';
        }

        /**
         * 検索用就業規則リストの更新
         * @note     就業規則マスタを更新した場合、検索用のリストも更新する
         * @return   検索用就業規則リスト
         */
        public function searchLaborRegulationsNameAction()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START searchLaborRegulationsNameAction");
            $Log->info( "MSG_INFO_1106" );

            $laborRegulations = new laborRegulations();

            $laborRegNameList = $laborRegulations->setPulldown->getSearchLaborRegulationsList( 'reference' );               // 就業規則リスト

            $searchArray['laborRegulationsName'] = parent::escStr( $_POST['searchLaborRegulationsName'] );

            $Log->trace("END   searchLaborRegulationsNameAction");
            require_once './FwCommon/View/SearchLaborRegulationsName.php';
        }

        /**
         * 就業規則DB更新
         * @param    $addFlag (新規：true  更新：false)
         * @return   なし
         */
        protected function updateProcess( $addFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START updateProcess" );

            $laborRegulations = new laborRegulations();

            $startFlag = false;
            
            $allOverTimeArray = $this->creatAllOverTimeArray();
            
            // 登録用拘束時間リスト（メソッド内でエスケープ処理済）
            $bindingArray = $this->toOrganizeAnArray( $_POST['bindingList'], "binding_hour", false );
            // 登録用付与時間リスト（メソッド内でエスケープ処理済）
            $mwrbBreakArray = $this->toOrganizeAnArray( $_POST['mwrbBreakList'], "break_time", false );
            $allTimeArray = array();
            // 拘束時間リスト
            $this->setMergeList( $bindingArray, $allTimeArray );
            // 付与時間リスト
            $this->setMergeList( $mwrbBreakArray, $allTimeArray );
            
            // 登録用休憩開始時間リスト（メソッド内でエスケープ処理済）
            $wageStartArray = $this->toOrganizeAnArray( $_POST['wageStartList'], "hourly_wage_start_time", false );
            // 登録用休憩終了時間リスト（メソッド内でエスケープ処理済）
            $wageEndArray = $this->toOrganizeAnArray( $_POST['wageEndList'], "hourly_wage_end_time", false );
            $allBreakZoneArray = array();
            // 休憩開始時間リスト
            $this->setMergeList( $wageStartArray, $allBreakZoneArray );
            // 休憩終了時間リスト
            $this->setMergeList( $wageEndArray, $allBreakZoneArray );

            // 登録用経過時間リスト（メソッド内でエスケープ処理済）
            $elapsedArray = $this->toOrganizeAnArray( $_POST['elapsedList'], "elapsed_time", false );
            // 登録用休憩付与リスト（メソッド内でエスケープ処理済）
            $mwrsbBreakArray = $this->toOrganizeAnArray( $_POST['mwrsbBreakList'], "break_time", false );
            $allBreakArray = array();
            // 経過時間リスト
            $this->setMergeList( $elapsedArray, $allBreakArray );
            // 休憩付与リスト
            $this->setMergeList( $mwrsbBreakArray, $allBreakArray );
            
            // 登録用時給変更時間帯リスト（メソッド内でエスケープ処理済）
            $hourlyWage_startTime = $this->toOrganizeAnArray( $_POST['hourlyWageStartTimeList'], "hourly_wage_start_time", false );
            // 登録用時給変更時間帯リスト（メソッド内でエスケープ処理済）
            $hourly_wage_end_time = $this->toOrganizeAnArray( $_POST['hourlyWageEndTime'], "hourly_wage_end_time", false );
            // 登録用設定時間帯手当リスト（メソッド内でエスケープ処理済）
            $hourly_wage = $this->toOrganizeAnArray( $_POST['hourlyWageList'], "hourly_wage", false );
            // 登録用設定時間帯手当リスト（メソッド内でエスケープ処理済）
            $hourly_wage_value = $this->toOrganizeAnArray( $_POST['hourlyWageValueList'], "hourly_wage_value", false );
            $allHourlyChange = array();
            
            // 時給変更時間帯リスト
            $this->setMergeList( $hourlyWage_startTime, $allHourlyChange );
            // 時給変更時間帯リスト
            $this->setMergeList( $hourly_wage_end_time, $allHourlyChange );
            // 時給設定時間帯手当リスト
            $this->setMergeList( $hourly_wage, $allHourlyChange );
            // 時給設定時間帯手当リスト
            $this->setMergeList( $hourly_wage_value, $allHourlyChange );

            $postArray = $this->creatPostArray( $addFlag );
            
            if( $postArray['overtime_alert_value'] === '' )
            {
                $postArray['overtime_alert_value'] = 0 ;
            }
            if( $postArray['labor_regulations_alert_value'] === '' )
            {
                $postArray['labor_regulations_alert_value'] = 0 ;
            }
            if( $postArray['fixed_overtime'] === '' )
            {
                $postArray['fixed_overtime'] = 1 ;
            }

            // 休日設定
            $postArray['holiday_settings'] =  parent::escStr( $_POST['holiday_settings'] );
            
            // メールアドレス
            $postArray['e_mail'] =  parent::escStr( $_POST['e_mail'] );

            // DB更新処理
            $message = $laborRegulations->addNewData($postArray, $allOverTimeArray, $allTimeArray, $allBreakArray, $allBreakZoneArray, $allHourlyChange, $addFlag);

            if($message === "MSG_BASE_0000")
            {
                // アラートがあるか
                $allAlertMsg = parent::escStr( $_POST['allAlertMsg'] );
                if( !empty( $allAlertMsg ) )
                {
                    // アラートメールの送信
                    $subject = "「" . $postArray['labor_regulations_name'] . "」就業規則 アラート情報";
                    
                    $allAlertMsg  = $_SESSION["USER_NAME"] . "様が、「" . $postArray['labor_regulations_name'] . "」の就業規則を更新致しました。\n" 
                                  . "以下のアラートが発生しています。\n" . $allAlertMsg;
                    $allAlertMsg .= "\n\n以上です。";
                    
                    $ret = $this->sendMail( $postArray['e_mail'], $subject, $allAlertMsg );
                    $Log->trace( "送信結果：" . $ret );
                }
                // 登録成功→一覧画面へ遷移
                $this->initialDisplay($startFlag);
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($message) );
            }

            $Log->trace("END updateProcess");
        }
        
        /**
         * 所定時間/残業時間のテーブル作成用リスト
         * @return    なし
         */
        protected function creatOverTimeSettingList()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START creatOverTimeSettingList" );
            $viewOverTimeList = array();
            $laborRegDataList['regular_working_hours_time'] = '';
            $laborRegDataList['overtime_reference_time'] = '';
            $laborRegDataList['regular_working_hours_time_day'] = '';
            $laborRegDataList['overtime_reference_time_day'] = '';
            $laborRegDataList['regular_working_hours_time_week'] = '';
            $laborRegDataList['overtime_reference_time_week'] = '';
            $laborRegDataList['regular_working_hours_time_mon'] = '';
            $laborRegDataList['overtime_reference_time_mon'] = '';
            $laborRegDataList['regular_working_hours_time_tue'] = '';
            $laborRegDataList['overtime_reference_time_tue'] = '';
            $laborRegDataList['regular_working_hours_time_wed'] = '';
            $laborRegDataList['overtime_reference_time_wed'] = '';
            $laborRegDataList['regular_working_hours_time_thu'] = '';
            $laborRegDataList['overtime_reference_time_thu'] = '';
            $laborRegDataList['regular_working_hours_time_fri'] = '';
            $laborRegDataList['overtime_reference_time_fri'] = '';
            $laborRegDataList['regular_working_hours_time_sat'] = '';
            $laborRegDataList['overtime_reference_time_sat'] = '';
            $laborRegDataList['regular_working_hours_time_sun'] = '';
            $laborRegDataList['overtime_reference_time_sun'] = '';
            $laborRegDataList['regular_working_hours_time_hol'] = '';
            $laborRegDataList['overtime_reference_time_hol'] = '';
            $laborRegDataList['regular_working_hours_time_28'] = '';
            $laborRegDataList['overtime_reference_time_28'] = '';
            $laborRegDataList['regular_working_hours_time_29'] = '';
            $laborRegDataList['overtime_reference_time_29'] = '';
            $laborRegDataList['regular_working_hours_time_30'] = '';
            $laborRegDataList['overtime_reference_time_30'] = '';
            $laborRegDataList['regular_working_hours_time_31'] = '';
            $laborRegDataList['overtime_reference_time_31'] = '';
            $viewOverTimeList['overtimeDayWeekYear'] = array();
            array_push( $viewOverTimeList['overtimeDayWeekYear'], array( '所定時間', 'regular_working_hours_time', '', $laborRegDataList['regular_working_hours_time'], '時間 ' ) );
            array_push( $viewOverTimeList['overtimeDayWeekYear'], array( '残業時間', 'overtime_reference_time', '', $laborRegDataList['overtime_reference_time'], '時間' ) );
            
            $viewOverTimeList['overtimeBiggerDayOrWeek1'] = array();
            array_push( $viewOverTimeList['overtimeBiggerDayOrWeek1'], array( '所定時間', 'regular_working_hours_time_day', '日', $laborRegDataList['regular_working_hours_time_day'], '時間 × 所定労働日数' ) );
            array_push( $viewOverTimeList['overtimeBiggerDayOrWeek1'], array( '残業時間', 'overtime_reference_time_day', '日', $laborRegDataList['overtime_reference_time_day'], '時間 × 所定労働日数' ) );
            
            $viewOverTimeList['overtimeBiggerDayOrWeek2'] = array();
            array_push( $viewOverTimeList['overtimeBiggerDayOrWeek2'], array( '', 'regular_working_hours_time_week', '週', $laborRegDataList['regular_working_hours_time_week'], '時間' ) );
            array_push( $viewOverTimeList['overtimeBiggerDayOrWeek2'], array( '', 'overtime_reference_time_week', '週', $laborRegDataList['overtime_reference_time_week'], '時間' ) );

            $viewOverTimeList['overtimeDay1'] = array();
            array_push( $viewOverTimeList['overtimeDay1'], array( '所定時間', 'regular_working_hours_time_mon', '月', $laborRegDataList['regular_working_hours_time_mon'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay1'], array( '残業時間', 'overtime_reference_time_mon', '月', $laborRegDataList['overtime_reference_time_mon'], '時間' ) );

            $viewOverTimeList['overtimeDay2'] = array();
            array_push( $viewOverTimeList['overtimeDay2'], array( '', 'regular_working_hours_time_tue', '火', $laborRegDataList['regular_working_hours_time_tue'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay2'], array( '', 'overtime_reference_time_tue', '火', $laborRegDataList['overtime_reference_time_tue'], '時間' ) );

            $viewOverTimeList['overtimeDay3'] = array();
            array_push( $viewOverTimeList['overtimeDay3'], array( '', 'regular_working_hours_time_wed', '水', $laborRegDataList['regular_working_hours_time_wed'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay3'], array( '', 'overtime_reference_time_wed', '水', $laborRegDataList['overtime_reference_time_wed'], '時間' ) );

            $viewOverTimeList['overtimeDay4'] = array();
            array_push( $viewOverTimeList['overtimeDay4'], array( '', 'regular_working_hours_time_thu', '木', $laborRegDataList['regular_working_hours_time_thu'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay4'], array( '', 'overtime_reference_time_thu', '木', $laborRegDataList['overtime_reference_time_thu'], '時間' ) );

            $viewOverTimeList['overtimeDay5'] = array();
            array_push( $viewOverTimeList['overtimeDay5'], array( '', 'regular_working_hours_time_fri', '金', $laborRegDataList['regular_working_hours_time_fri'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay5'], array( '', 'overtime_reference_time_fri', '金', $laborRegDataList['overtime_reference_time_fri'], '時間' ) );

            $viewOverTimeList['overtimeDay6'] = array();
            array_push( $viewOverTimeList['overtimeDay6'], array( '', 'regular_working_hours_time_sat', '土', $laborRegDataList['regular_working_hours_time_sat'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay6'], array( '', 'overtime_reference_time_sat', '土', $laborRegDataList['overtime_reference_time_sat'], '時間' ) );

            $viewOverTimeList['overtimeDay7'] = array();
            array_push( $viewOverTimeList['overtimeDay7'], array( '', 'regular_working_hours_time_sun', '日', $laborRegDataList['regular_working_hours_time_sun'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay7'], array( '', 'overtime_reference_time_sun', '日', $laborRegDataList['overtime_reference_time_sun'], '時間' ) );

            $viewOverTimeList['overtimeDay8'] = array();
            array_push( $viewOverTimeList['overtimeDay8'], array( '', 'regular_working_hours_time_hol', '祝', $laborRegDataList['regular_working_hours_time_hol'], '時間' ) );
            array_push( $viewOverTimeList['overtimeDay8'], array( '', 'overtime_reference_time_hol', '祝', $laborRegDataList['overtime_reference_time_hol'], '時間' ) );

            $viewOverTimeList['overtimeTotalMonth1'] = array();
            array_push( $viewOverTimeList['overtimeTotalMonth1'], array( '所定時間', 'regular_working_hours_time_28', '28日', $laborRegDataList['regular_working_hours_time_28'], '時間' ) );
            array_push( $viewOverTimeList['overtimeTotalMonth1'], array( '残業時間', 'overtime_reference_time_28', '28日', $laborRegDataList['overtime_reference_time_28'], '時間' ) );

            $viewOverTimeList['overtimeTotalMonth2'] = array();
            array_push( $viewOverTimeList['overtimeTotalMonth2'], array( '', 'regular_working_hours_time_29', '29日', $laborRegDataList['regular_working_hours_time_29'], '時間' ) );
            array_push( $viewOverTimeList['overtimeTotalMonth2'], array( '', 'overtime_reference_time_29', '29日', $laborRegDataList['overtime_reference_time_29'], '時間' ) );

            $viewOverTimeList['overtimeTotalMonth3'] = array();
            array_push( $viewOverTimeList['overtimeTotalMonth3'], array( '', 'regular_working_hours_time_30', '30日', $laborRegDataList['regular_working_hours_time_30'], '時間' ) );
            array_push( $viewOverTimeList['overtimeTotalMonth3'], array( '', 'overtime_reference_time_30', '30日', $laborRegDataList['overtime_reference_time_30'], '時間' ) );

            $viewOverTimeList['overtimeTotalMonth4'] = array();
            array_push( $viewOverTimeList['overtimeTotalMonth4'], array( '', 'regular_working_hours_time_31', '31日', $laborRegDataList['regular_working_hours_time_31'], '時間' ) );
            array_push( $viewOverTimeList['overtimeTotalMonth4'], array( '', 'overtime_reference_time_31', '31日', $laborRegDataList['overtime_reference_time_31'], '時間' ) );

            $this->creatOverTimeSettingListMonth( $viewOverTimeList );
            $Log->trace("END creatOverTimeSettingList");
            return $viewOverTimeList;
        }
        
        /**
         * 所定時間/残業時間のテーブル作成用リスト(月及び、シフト指定)
         * @return    なし
         */
        protected function creatOverTimeSettingListMonth( &$viewOverTimeList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START creatOverTimeSettingListMonth" );
            
            $laborRegDataList['regular_working_hours_time_1'] = '';
            $laborRegDataList['overtime_reference_time_1'] = '';
            $laborRegDataList['regular_working_hours_time_2'] = '';
            $laborRegDataList['overtime_reference_time_2'] = '';
            $laborRegDataList['regular_working_hours_time_3'] = '';
            $laborRegDataList['overtime_reference_time_3'] = '';
            $laborRegDataList['regular_working_hours_time_4'] = '';
            $laborRegDataList['overtime_reference_time_4'] = '';
            $laborRegDataList['regular_working_hours_time_5'] = '';
            $laborRegDataList['overtime_reference_time_5'] = '';
            $laborRegDataList['regular_working_hours_time_6'] = '';
            $laborRegDataList['overtime_reference_time_6'] = '';
            $laborRegDataList['regular_working_hours_time_7'] = '';
            $laborRegDataList['overtime_reference_time_7'] = '';
            $laborRegDataList['regular_working_hours_time_8'] = '';
            $laborRegDataList['overtime_reference_time_8'] = '';
            $laborRegDataList['regular_working_hours_time_9'] = '';
            $laborRegDataList['overtime_reference_time_9'] = '';
            $laborRegDataList['regular_working_hours_time_10'] = '';
            $laborRegDataList['overtime_reference_time_10'] = '';
            $laborRegDataList['regular_working_hours_time_11'] = '';
            $laborRegDataList['overtime_reference_time_11'] = '';
            $laborRegDataList['regular_working_hours_time_12'] = '';
            $laborRegDataList['overtime_reference_time_12'] = '';
            
            $viewOverTimeList['overtimeAllMonth1'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth1'], array( '所定時間', 'regular_working_hours_time_1', ' 1月', $laborRegDataList['regular_working_hours_time_1'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth1'], array( '残業時間', 'overtime_reference_time_1', ' 1月', $laborRegDataList['overtime_reference_time_1'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth2'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth2'], array( '', 'regular_working_hours_time_2', ' 2月', $laborRegDataList['regular_working_hours_time_2'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth2'], array( '', 'overtime_reference_time_2', ' 2月', $laborRegDataList['overtime_reference_time_2'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth3'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth3'], array( '', 'regular_working_hours_time_3', ' 3月', $laborRegDataList['regular_working_hours_time_3'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth3'], array( '', 'overtime_reference_time_3', ' 3月', $laborRegDataList['overtime_reference_time_3'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth4'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth4'], array( '', 'regular_working_hours_time_4', ' 4月', $laborRegDataList['regular_working_hours_time_4'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth4'], array( '', 'overtime_reference_time_4', ' 4月', $laborRegDataList['overtime_reference_time_4'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth5'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth5'], array( '', 'regular_working_hours_time_5', ' 5月', $laborRegDataList['regular_working_hours_time_5'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth5'], array( '', 'overtime_reference_time_5', ' 5月', $laborRegDataList['overtime_reference_time_5'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth6'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth6'], array( '', 'regular_working_hours_time_6', ' 6月', $laborRegDataList['regular_working_hours_time_6'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth6'], array( '', 'overtime_reference_time_6', ' 6月', $laborRegDataList['overtime_reference_time_6'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth7'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth7'], array( '', 'regular_working_hours_time_7', ' 7月', $laborRegDataList['regular_working_hours_time_7'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth7'], array( '', 'overtime_reference_time_7', ' 7月', $laborRegDataList['overtime_reference_time_7'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth8'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth8'], array( '', 'regular_working_hours_time_8', ' 8月', $laborRegDataList['regular_working_hours_time_8'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth8'], array( '', 'overtime_reference_time_8', ' 8月', $laborRegDataList['overtime_reference_time_8'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth9'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth9'], array( '', 'regular_working_hours_time_9', ' 9月', $laborRegDataList['regular_working_hours_time_9'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth9'], array( '', 'overtime_reference_time_9', ' 9月', $laborRegDataList['overtime_reference_time_9'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth10'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth10'], array( '', 'regular_working_hours_time_10', '10月', $laborRegDataList['regular_working_hours_time_10'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth10'], array( '', 'overtime_reference_time_10', '10月', $laborRegDataList['overtime_reference_time_10'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth11'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth11'], array( '', 'regular_working_hours_time_11', '11月', $laborRegDataList['regular_working_hours_time_11'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth11'], array( '', 'overtime_reference_time_11', '11月', $laborRegDataList['overtime_reference_time_11'], '時間' ) );

            $viewOverTimeList['overtimeAllMonth12'] = array();
            array_push( $viewOverTimeList['overtimeAllMonth12'], array( '', 'regular_working_hours_time_12', '12月', $laborRegDataList['regular_working_hours_time_12'], '時間' ) );
            array_push( $viewOverTimeList['overtimeAllMonth12'], array( '', 'overtime_reference_time_12', '12月', $laborRegDataList['overtime_reference_time_12'], '時間' ) );

            $viewOverTimeList['overtimeShift'] = array();
            array_push( $viewOverTimeList['overtimeShift'], array( '', '', '残業時間はシフトの設定時間を超えた場合とする', '', '' ) );

            $Log->trace("END creatOverTimeSettingListMonth");
        }
        
        /**
         * モデルに渡す画面入力値のリスト作成
         * @param    $addFlag (新規：true  更新：false)
         * @return   画面入力値のリスト
         */
        protected function creatPostArray( $addFlag )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START creatPostArray" );
            
            $upLaborRegID = '';  $upAppDateID = ''; $upAppDateStart = ''; $updateTime = '';
            if( !$addFlag )
            {
                // 更新時は、POSTデータを設定する。
                $upLaborRegID    = parent::escStr( $_POST['laborRegulationsID'] );
                $upAppDateID     = parent::escStr( $_POST['applicationDateId'] );
                $upAppDateStart  = parent::escStr( $_POST['applicationDateStart'] );
                $updateTime      = parent::escStr( $_POST['updateTime'] );
            }

            $fixedOverTime = $this->changeMinuteFromTime( parent::escStr( $_POST['fixed_overtime_time'] ) );

            $postArray = array(
                'organization_id'                      => parent::escStr( $_POST['organization_id'] ),
                'labor_regulations_name'               => parent::escStr( $_POST['labor_regulations_name'] ),
                'disp_order'                           => parent::escStr( $_POST['disp_order'] ),
                'app_comment'                          => parent::escStr( $_POST['app_comment'] ),
                'application_date_start'               => parent::escStr( $_POST['application_date_start'] ),
                'shift_time_unit'                      => parent::escStr( $_POST['shift_time_unit'] ),
                'start_working_hours'                  => parent::escStr( $_POST['start_working_hours'] ),
                'end_working_hours'                    => parent::escStr( $_POST['end_working_hours'] ),
                'is_shift_working_hours_use'           => parent::escStr( $_POST['is_shift_working_hours_use'] ),
                'is_work_rules_working_hours_use'      => parent::escStr( $_POST['is_work_rules_working_hours_use'] ),
                'attendance_rounding_time'             => parent::escStr( $_POST['attendance_rounding_time'] ),
                'attendance_rounding'                  => parent::escStr( $_POST['attendance_rounding'] ),
                'break_rounding_start_time'            => parent::escStr( $_POST['break_rounding_start_time'] ),
                'break_rounding_start'                 => parent::escStr( $_POST['break_rounding_start'] ),
                'break_rounding_end_time'              => parent::escStr( $_POST['break_rounding_end_time'] ),
                'break_rounding_end'                   => parent::escStr( $_POST['break_rounding_end'] ),
                'leave_work_rounding_time'             => parent::escStr( $_POST['leave_work_rounding_time'] ),
                'leave_work_rounding'                  => parent::escStr( $_POST['leave_work_rounding'] ),
                'early_shift_approval'                 => parent::escStr( $_POST['early_shift_approval'] ),
                'early_shift_approval_time'            => parent::escStr( $_POST['early_shift_approval_time'] ),
                'overtime_approval'                    => parent::escStr( $_POST['overtime_approval'] ),
                'overtime_approval_time'               => parent::escStr( $_POST['overtime_approval_time'] ),
                'work_handling_travel'                 => parent::escStr( $_POST['work_handling_travel'] ),
                'work_handling_travel_time'            => parent::escStr( $_POST['work_handling_travel_time'] ),
                'recorded_travel_time'                 => parent::escStr( $_POST['recorded_travel_time'] ),
                'total_working_day_rounding_time'      => parent::escStr( $_POST['total_working_day_rounding_time'] ),
                'total_working_day_rounding'           => parent::escStr( $_POST['total_working_day_rounding'] ),
                'total_working_month_rounding_time'    => parent::escStr( $_POST['total_working_month_rounding_time'] ),
                'total_working_month_rounding'         => parent::escStr( $_POST['total_working_month_rounding'] ),
                'balance_payments'                     => parent::escStr( $_POST['balance_payments'] ),
                'month_tightening'                     => parent::escStr( $_POST['month_tightening'] ),
                'year_tighten'                         => parent::escStr( $_POST['year_tighten'] ),
                'max_break_time'                       => parent::escStr( $_POST['max_break_time'] ),
                'break_time_acquisition'               => parent::escStr( $_POST['break_time_acquisition'] ),
                'automatic_break_time_acquisition'     => parent::escStr( $_POST['automatic_break_time_acquisition'] ),
                'mod_break_time'                       => parent::escStr( $_POST['mod_break_time'] ),
                'overtime_setting'                     => parent::escStr( $_POST['overtime_setting'] ),
                'closing_date_set_id'                  => parent::escStr( $_POST['closing_date_set_id'] ),
                'legal_time_in_overtime'               => parent::escStr( $_POST['legal_time_in_overtime'] ),
                'legal_time_in_overtime_value'         => parent::escStr( $_POST['legal_time_in_overtime_value'] ),
                'legal_time_out_overtime'              => parent::escStr( $_POST['legal_time_out_overtime'] ),
                'legal_time_out_overtime_value'        => parent::escStr( $_POST['legal_time_out_overtime_value'] ),
                'fixed_overtime'                       => parent::escStr( $_POST['fixed_overtime'] ),
                'fixed_overtime_type'                  => parent::escStr( $_POST['fixed_overtime_type'] ),
                'fixed_overtime_time'                  => $fixedOverTime,
                'legal_time_out_overtime_45'           => parent::escStr( $_POST['legal_time_out_overtime_45'] ),
                'legal_time_out_overtime_value_45'     => parent::escStr( $_POST['legal_time_out_overtime_value_45'] ),
                'legal_time_out_overtime_60'           => parent::escStr( $_POST['legal_time_out_overtime_60'] ),
                'legal_time_out_overtime_value_60'     => parent::escStr( $_POST['legal_time_out_overtime_value_60'] ),
                'late_at_night_start'                  => parent::escStr( $_POST['late_at_night_start'] ),
                'late_at_night_end'                    => parent::escStr( $_POST['late_at_night_end'] ),
                'late_at_night_out_overtime'           => parent::escStr( $_POST['late_at_night_out_overtime'] ),
                'late_at_night_out_overtime_value'     => parent::escStr( $_POST['late_at_night_out_overtime_value'] ),
                'is_shift_holiday_use'                 => parent::escStr( $_POST['is_shift_holiday_use'] ),
                'is_organization_calendar_holiday_use' => parent::escStr( $_POST['is_organization_calendar_holiday_use'] ),
                'legal_holiday_allowance'              => parent::escStr( $_POST['legal_holiday_allowance'] ),
                'legal_holiday_allowance_value'        => parent::escStr( $_POST['legal_holiday_allowance_value'] ),
                'prescribed_holiday_allowance'         => parent::escStr( $_POST['prescribed_holiday_allowance'] ),
                'prescribed_holiday_allowance_value'   => parent::escStr( $_POST['prescribed_holiday_allowance_value'] ),
                'trial_period_type_id'                 => parent::escStr( $_POST['trial_period_type_id'] ),
                'trial_period_criteria_value'          => parent::escStr( $_POST['trial_period_criteria_value'] ),
                'labor_cost_calculation'               => parent::escStr( $_POST['labor_cost_calculation'] ),
                'is_overtime_alert'                    => parent::escStr( $_POST['is_overtime_alert'] ),
                'overtime_alert_value'                 => parent::escStr( $_POST['overtime_alert_value'] ),
                'is_labor_regulations_alert'           => 1,
                'labor_regulations_alert_value'        => parent::escStr( $_POST['labor_regulations_alert_value'] ),
                'is_del'                               => 0,
                'user_id'                              => $_SESSION["USER_ID"],
                'organization'                         => $_SESSION["ORGANIZATION_ID"],
                'up_labor_regulations_id'              => $upLaborRegID,
                'up_application_date_id'               => $upAppDateID,
                'up_application_date_start'            => $upAppDateStart,
                'updateTime'                           => $updateTime,
            );
            $Log->trace("END creatPostArray");

            return $postArray;
        }
        
        /**
         * モデルに渡す画面入力値(時間リスト)のリスト作成
         * @return   画面入力値(時間リスト)のリスト
         */
        protected function creatAllOverTimeArray()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START creatAllOverTimeArray" );
            
            $allArray = array();

            // 登録用所定時間リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['regularWorkingList'], $allArray );
            // 登録用残業時間リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['overtimeList'], $allArray ); 
            
            // 登録用所定・残業時間(日)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['dayList'], $allArray );
            // 登録用所定・残業時間(週)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['weekList'], $allArray );
            
            // 登録用所定・残業時間(月曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['mondayList'], $allArray );
            // 登録用所定・残業時間(火曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['tuesdayList'], $allArray );
            // 登録用所定・残業時間(水曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['wednesdayList'], $allArray );
            // 登録用所定・残業時間(木曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['thursdayList'], $allArray );
            // 登録用所定・残業時間(金曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['fridayList'], $allArray );
            // 登録用所定・残業時間(土曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['saturdayList'], $allArray );
            // 登録用所定・残業時間(日曜)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['sundayList'], $allArray );
            // 登録用所定・残業時間(祝日)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['holidayList'], $allArray );
            
            // 登録用所定・残業時間(28日)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['twentyEighthList'], $allArray );
            // 登録用所定・残業時間(29日)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['twentyNinethList'], $allArray );
            // 登録用所定・残業時間(30日)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['thirtiethList'], $allArray );
            // 登録用所定・残業時間(31日)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['thirtyFirstList'], $allArray );
            
            // 登録用所定・残業時間(1月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['januaryList'], $allArray );
            // 登録用所定・残業時間(2月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['februaryList'], $allArray );
            // 登録用所定・残業時間(3月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['marchList'], $allArray );
            // 登録用所定・残業時間(4月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['aprilList'], $allArray );
            // 登録用所定・残業時間(5月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['mayList'], $allArray );
            // 登録用所定・残業時間(6月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['juneList'], $allArray );
            // 登録用所定・残業時間(7月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['julyList'], $allArray );
            // 登録用所定・残業時間(8月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['augastList'], $allArray );
            // 登録用所定・残業時間(9月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['septemberList'], $allArray );
            // 登録用所定・残業時間(10月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['octorberList'], $allArray );
            // 登録用所定・残業時間(11月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['novemberList'], $allArray );
            // 登録用所定・残業時間(12月)リスト（メソッド内でエスケープ処理済）
            $this->setTimeArray( $_POST['decemberList'], $allArray );

            $Log->trace("END creatAllOverTimeArray");

            return $allArray;
        }
        
        /**
         * 就業規則入力パラメータ初期化
         * @note     パラメータは、View側で使用
         * @return   初期化済み、パラメータリスト
         */
        private function initialization()
        {
            global $Log, $TokenID; // グローバル変数宣言
            $Log->trace( "START initialization" );
            
            $laborRegDataList = array();
            $laborRegDataList['organization_id'] = '';
            $laborRegDataList['labor_regulations_name'] = '';
            $laborRegDataList['comment'] = '';
            $laborRegDataList['disp_order'] = '';
            $laborRegDataList['application_date_start'] = '';
            $laborRegDataList['shift_time_unit'] = 30;
            $laborRegDataList['start_working_hours'] = '';
            $laborRegDataList['end_working_hours'] = '';
            $laborRegDataList['is_shift_working_hours_use'] = 1;
            $laborRegDataList['is_work_rules_working_hours_use'] = 1;
            $laborRegDataList['attendance_rounding_time'] = 30;
            $laborRegDataList['attendance_rounding'] = 1;
            $laborRegDataList['break_rounding_start_time'] = 30;
            $laborRegDataList['break_rounding_start'] = 1;
            $laborRegDataList['break_rounding_end_time'] = 30;
            $laborRegDataList['break_rounding_end'] = 2;
            $laborRegDataList['leave_work_rounding_time'] = 30;
            $laborRegDataList['leave_work_rounding'] = 2;
            $laborRegDataList['early_shift_approval'] = '';
            $laborRegDataList['early_shift_approval_time'] = '';
            $laborRegDataList['overtime_approval'] = '';
            $laborRegDataList['overtime_approval_time'] = '';
            $laborRegDataList['work_handling_travel'] = 1;
            $laborRegDataList['work_handling_travel_time'] = '';
            $laborRegDataList['recorded_travel_time'] = 1;
            $laborRegDataList['total_working_day_rounding_time'] = 30;
            $laborRegDataList['total_working_day_rounding'] = 2;
            $laborRegDataList['total_working_month_rounding_time'] = 30;
            $laborRegDataList['total_working_month_rounding'] = 2;
            $laborRegDataList['balance_payments'] = '';
            $laborRegDataList['month_tightening'] = '';
            $laborRegDataList['year_tighten'] = '';
            $laborRegDataList['max_break_time'] = '';
            $laborRegDataList['break_time_acquisition'] = '';
            $laborRegDataList['automatic_break_time_acquisition'] = '';
            $laborRegDataList['binding_hour'] = '';
            $laborRegDataList['mwrb_break_time'] = '';
            $laborRegDataList['elapsed_time'] = '';
            $laborRegDataList['mwrsb_break_time'] = '';
            $laborRegDataList['mbtz_hourly_wage_start_time'] = '';
            $laborRegDataList['mbtz_hourly_wage_end_time'] = '';
            $laborRegDataList['mod_break_time'] = 1;
            $laborRegDataList['overtime_setting'] = '';
            $laborRegDataList['regular_working_hours_time_day'] = '';
            $laborRegDataList['overtime_reference_time_day'] = '';
            $laborRegDataList['legal_time_in_overtime'] = '';
            $laborRegDataList['legal_time_in_overtime_value'] = '';
            $laborRegDataList['legal_time_out_overtime'] = '';
            $laborRegDataList['legal_time_out_overtime_value'] = '';
            $laborRegDataList['fixed_overtime'] = '';
            $laborRegDataList['fixed_overtime_type'] = '';
            $laborRegDataList['fixed_overtime_time'] = '';
            $laborRegDataList['legal_time_out_overtime_45'] = '';
            $laborRegDataList['legal_time_out_overtime_value_45'] = '';
            $laborRegDataList['legal_time_out_overtime_60'] = '';
            $laborRegDataList['legal_time_out_overtime_value_60'] = '';
            $laborRegDataList['late_at_night_start'] = '';
            $laborRegDataList['late_at_night_end'] = '';
            $laborRegDataList['late_at_night_out_overtime'] = '';
            $laborRegDataList['late_at_night_out_overtime_value'] = '';
            $laborRegDataList['hourly_wage_start_time'] = '';
            $laborRegDataList['hourly_wage_end_time'] = '';
            $laborRegDataList['hourly_wage'] = '';
            $laborRegDataList['hourly_wage_value'] = '';
            $laborRegDataList['is_shift_holiday_use'] = 1;
            $laborRegDataList['is_organization_calendar_holiday_use'] = 1;
            $laborRegDataList['legal_holiday_allowance'] = '';
            $laborRegDataList['legal_holiday_allowance_value'] = '';
            $laborRegDataList['prescribed_holiday_allowance'] = '';
            $laborRegDataList['prescribed_holiday_allowance_value'] = '';
            $laborRegDataList['trial_period_type_id'] = '';
            $laborRegDataList['trial_period_criteria_value'] = '';
            $laborRegDataList['labor_cost_calculation'] = 1;
            $laborRegDataList['is_overtime_alert'] = 1;
            $laborRegDataList['overtime_alert_value'] = '';
            $laborRegDataList['is_labor_regulations_alert'] = 1;
            $laborRegDataList['labor_regulations_alert_value'] = '';
            $laborRegDataList['update_time'] = '';
            $laborRegDataList['holiday_settings'] = 1;
            
            $Log->trace( "END   initialization" );
            
            return $laborRegDataList;
        }

    }
?>
