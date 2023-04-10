<?php
    /**
     * @file    アラートチェック共通コントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/10/20
     * @version 1.00
     * @note    共通で使用するアラートチェック用コントローラの処理を定義
     */

    // BaseController.phpを読み込む
    require_once './Controllers/Common/BaseController.php';

    /**
     * 各コントローラの基本クラス
     * @note    共通で使用するコントローラの処理を定義
     */
    class BaseCheckAlertController extends BaseController
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
         * アラート情報の設定
         * @note     アラート情報の設定
         * @param    $attendanceCt    勤怠レコード
         * @param    $targetDate      対象日
         * @param    &$model          モデル
         * @param    $isShift         trur：シフトである  false：シフトではない
         * @return   アラート内容
         */
        protected function setAlert( $attendanceCt, $targetDate, &$model, $isShift, &$eMail )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setAlert");

            $alertMsg = '';
            if( !$isShift )
            {
                // 打刻不正のアラートチェック
                $alertMsg = $this->checkEmbossingFraud( $attendanceCt, $targetDate );
            }

            // アラート情報
            $alertInfo = $model->getLaborRegulationsAlertInfo( $attendanceCt['user_id'], $attendanceCt['labor_regulations_id'], $targetDate );
            $eMail = '';

            // アラートを出力するか
            if( $alertInfo['alert_info']['is_overtime_alert'] == 1 )
            {
                // 就業規則設定のメールアドレス設定
                $eMail = $alertInfo['alert_info']['e_mail'];

                // 休憩時間に問題ないか
                $breakTimeMsg = $this->checkBreakTimeFraud( $attendanceCt, $targetDate, $alertInfo );
                if( $breakTimeMsg != '' )
                {
                    // 休憩時間のアラート
                    if( $alertMsg != '' )
                    {
                        $alertMsg .= '<br>';
                    }
                    
                    $headMsg = '※';
                    if( $isShift )
                    {
                        $headMsg = date( 'm月d日', strtotime( $targetDate ) );
                        $headMsg = $headMsg . '　' . $attendanceCt['user_name'] . "：";
                    }
                    $alertMsg .= $headMsg . $breakTimeMsg;
                }

                // 残業時間に問題ないか
                $overTimeMsg = $this->checkOverTimeFraud( $attendanceCt, $targetDate, $alertInfo, $model );
                if( $overTimeMsg != "" )
                {
                    // 残業時間のアラート
                    if( $alertMsg != '' )
                    {
                        $alertMsg .= '<br>';
                    }
                    
                    $headMsg = '※';
                    if( $isShift )
                    {
                        $headMsg = date( 'm月d日', strtotime( $targetDate ) );
                        $headMsg = $headMsg . '　' . $attendanceCt['user_name'] . "：";
                    }
                    $alertMsg .= $headMsg . $overTimeMsg;
                }

                // 休日日数の確認
                // 休日の判定方法を取得
                $holidayMsg = $this->checkHolidayFraud( $attendanceCt, $targetDate, $alertInfo, $model, $isShift );
                
                if( $holidayMsg != "" )
                {
                    // 休暇日数のアラート
                    if( $alertMsg != '' )
                    {
                        $alertMsg .= '<br>';
                    }
                    
                    $headMsg = '※';
                    if( $isShift )
                    {
                        $headMsg = date( 'm月d日', strtotime( $targetDate ) );
                        $headMsg = $headMsg . '　' . $attendanceCt['user_name'] . "：";
                    }
                    $alertMsg .= $headMsg . $holidayMsg;
                }
            }
            

            $Log->trace("END   setAlert");
            
            return $alertMsg;
        }
        
        /**
         * 1勤怠にて、打刻漏れがあるかチェックする
         * @param    $attendanceCt            勤怠情報
         * @return   打刻漏れアラート
         */
        protected function getEmbossingLeakageMsg( $attendanceCt )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getEmbossingLeakageMsg");

            $embossingLeakageMsg = '';
            if( !empty( $attendanceCt['attendance_time'] ) && empty( $attendanceCt['clock_out_time'] ) )
            {
                $embossingLeakageMsg .= "\n　　" . $attendanceCt['user_name'] . "：退勤時間の打刻がありません。";
            }
            
            if( !empty( $attendanceCt['s_break_time_1'] ) && empty( $attendanceCt['e_break_time_1'] ) )
            {
                $embossingLeakageMsg .= "\n　　" . $attendanceCt['user_name'] . "：休憩終了時間1の打刻がありません。";
            }
            
            if( !empty( $attendanceCt['s_break_time_2'] ) && empty( $attendanceCt['e_break_time_2'] ) )
            {
                $embossingLeakageMsg .= "\n　　" . $attendanceCt['user_name'] . "：休憩終了時間2の打刻がありません。";
            }
            
            if( !empty( $attendanceCt['s_break_time_3'] ) && empty( $attendanceCt['e_break_time_3'] ) )
            {
                $embossingLeakageMsg .= "\n　　" . $attendanceCt['user_name'] . "：休憩終了時間3の打刻がありません。";
            }

            $Log->trace("END getEmbossingLeakageMsg");

            return $embossingLeakageMsg;
        }
        
        /**
         * 打刻不正のアラートチェック
         * @note     打刻不正のアラートチェック
         * @param    $attendanceCt    勤怠レコード
         * @param    $targetDate      対象日
         * @return   打刻不正のアラート内容
         */
        private function checkEmbossingFraud( $attendanceCt, $targetDate )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkEmbossingFraud");

            $isLocalTime  = false;
            $localTimeMsg = '';
            // ローカル時間打刻の場合、メッセージを表示
            if( $attendanceCt['is_embossing_attendance_time'] == 1 )
            {
                $isLocalTime = true;
                // 出勤打刻が、ローカル時間
                $localTimeMsg = "出勤、";
            }
            
            if( $attendanceCt['is_embossing_clock_out_time'] == 1 )
            {
                $isLocalTime = true;
                // 退勤打刻が、ローカル時間
                $localTimeMsg = $localTimeMsg . "退勤、";
            }
            
            if( $attendanceCt['is_embossing_s_break_time_1'] == 1 )
            {
                $isLocalTime = true;
                // 休憩開始打刻1が、ローカル時間
                $localTimeMsg = $localTimeMsg . "休憩開始1、";
            }
            
            if( $attendanceCt['is_embossing_e_break_time_1'] == 1 )
            {
                $isLocalTime = true;
                // 休憩終了打刻1が、ローカル時間
                $localTimeMsg = $localTimeMsg . "休憩終了1、";
            }
            
            if( $attendanceCt['is_embossing_s_break_time_2'] == 1 )
            {
                $isLocalTime = true;
                // 休憩開始打刻2が、ローカル時間
                $localTimeMsg = $localTimeMsg . "休憩開始2、";
            }
            
            if( $attendanceCt['is_embossing_e_break_time_2'] == 1 )
            {
                $isLocalTime = true;
                // 休憩終了打刻2が、ローカル時間
                $localTimeMsg = $localTimeMsg . "休憩終了2、";
            }
            
            if( $attendanceCt['is_embossing_s_break_time_3'] == 1 )
            {
                $isLocalTime = true;
                // 休憩開始打刻3が、ローカル時間
                $localTimeMsg = $localTimeMsg . "休憩開始3、";
            }
            
            if( $attendanceCt['is_embossing_e_break_time_3'] == 1 )
            {
                $isLocalTime = true;
                // 休憩終了打刻3が、ローカル時間
                $localTimeMsg = $localTimeMsg . "休憩終了3、";
            }
            
            $alertMsg = '';
            if($isLocalTime)
            {
                $localTimeMsg = rtrim( $localTimeMsg, '、' );
                $alertMsg = "※ローカルの時間で、" . $localTimeMsg . "の打刻されています。";
            }

            $Log->trace("END   checkEmbossingFraud");
            
            return $alertMsg;
        }

        /**
         * 休憩時間アラート情報
         * @note     休憩時間アラート情報の設定
         * @param    $attendanceCt    勤怠レコード
         * @param    $targetDate      対象日
         * @param    $alertInfo       アラート情報
         * @return   休憩時間不正のアラート内容
         */
        private function checkBreakTimeFraud( $attendanceCt, $targetDate, $alertInfo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkBreakTimeFraud");

            // 出勤時間がない場合、休憩時間のチェックを行わない
            if( empty( $attendanceCt['attendance_time'] ) )
            {
                $Log->trace("END   checkBreakTimeFraud");
                return "";
            }

            // 休憩時間の確認
            $totalWorkingTime = $this->changeMinuteFromTime( $attendanceCt['total_working_time'] );
            $basicBreak = 0;
            $breakTimeMsg = '拘束時間';
            // 拘束時間8時間以上か
            if( $totalWorkingTime >= 480 )
            {
                $basicBreak = $alertInfo['labor_standards_act']['break_time_8_hours_work_time'];
                $breakTimeMsg .= '8時間に対し、';
            } 
            else if( $totalWorkingTime >= 360 )
            {
                $basicBreak = $alertInfo['labor_standards_act']['break_time_6_hours_work_time'];
                $breakTimeMsg .= '6時間に対し、';
            }
            
            $alertMsg = '';
            // 休憩時間に問題ないか
            $breakTime = $this->changeMinuteFromTime( $attendanceCt['break_time'] );
            if( $basicBreak > 0 && $basicBreak > $breakTime )
            {
                // 休憩時間のアラート
                $alertMsg .= $breakTimeMsg . $basicBreak . '分の休憩時間が必要です。';
            }

            $Log->trace("END   checkBreakTimeFraud");
            
            return $alertMsg;
        }
        
        /**
         * 残業時間アラート情報
         * @note     残業時間アラート情報の設定
         * @param    $attendanceCt   勤怠レコード
         * @param    $targetDate     対象日
         * @param    $alertInfo      アラート情報
         * @param    &$model         モデル
         * @return   残業時間不正のアラート内容
         */
        private function checkOverTimeFraud( $attendanceCt, $targetDate, $alertInfo, &$model )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkOverTimeFraud");
            
            // 残業時間の確認
            // 就業規則ID取得
            $laborRegulationsID      = $model->getLaborRegulationsID( $attendanceCt['user_id'], $attendanceCt['labor_regulations_id'], $targetDate );
            // 所定残業時間の取得
            $prescribedOvertimeHours = $model->getprescribedOvertimeHours( $attendanceCt['user_id'], $laborRegulationsID, $targetDate );
            $predeterminedTime       = $model->getPredeterminedTime( $attendanceCt['user_id'], $laborRegulationsID, $targetDate );
            
            // 所定残業時間から、アラートの閾値を算出する
            $alertThreshold = $prescribedOvertimeHours['workingOverHoursTime'] * $alertInfo['alert_info']['overtime_alert_value'] / 100;
            
            // 比較対象の労働時間を取得する
            $workingTime = $this->changeMinuteFromTime( $attendanceCt['total_working_time'] );  // 日単位
            if( $prescribedOvertimeHours['workingOverHoursTime'] != $predeterminedTime['workingOverHoursTime'] )
            {
                // 期間単位
                $workingTime = $prescribedOvertimeHours['workingOverHoursTime'] - $predeterminedTime['workingOverHoursTime'] + $workingTime;
            }

            // 残業時間のアラート対象か
            $overTimeMsg = "";
            if( $prescribedOvertimeHours['workingOverHoursTime'] < $workingTime )
            {
                $overTimeMsg = "労働時間が残業時間の設定時間" . $this->changeTimeFromMinute( $prescribedOvertimeHours['workingOverHoursTime'] ) . "を超えています。";
            }
            else if( $alertThreshold < $workingTime )
            {
                $overTimeMsg = "労働時間が残業発生までの閾値(" . $alertInfo['alert_info']['overtime_alert_value'] . "%)" . $this->changeTimeFromMinute( $alertThreshold ) . "を超えています。";
            }
            
            $alertMsg = '';
            if( $overTimeMsg != "" && $attendanceCt['attendance_time'] != "" )
            {
                // 残業時間のアラート
                $alertMsg .= $overTimeMsg;
            }

            $Log->trace("END   checkOverTimeFraud");
            
            return $alertMsg;
        }

        /**
         * 休日日数アラート情報
         * @note     休日日数アラート情報の設定
         * @param    $attendanceCt    勤怠レコード
         * @param    $targetDate      対象日
         * @param    $alertInfo       アラート情報
         * @param    &$model          モデル
         * @param    $isShift         trur：シフトである  false：シフトではない
         * @return   休日日数不正のアラート内容
         */
        private function checkHolidayFraud( $attendanceCt, $targetDate, $alertInfo, &$model, $isShift )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkHolidayFraud");

            // 休日日数の確認
            // 休日の判定方法を取得
            $holidayMsg = '';
            if( $alertInfo['alert_info']['holiday_settings'] == 1 )
            {
                // 1週単位で休暇チェック
                // 対象日の曜日を取得
                $datetime = new DateTime($targetDate);
                $weekNo = (int)$datetime->format('w');
                if( $weekNo == 0)
                {
                    // 本システム上、日曜日は7番と設定
                    $weekNo = 7;
                }
                
                // チェック対象日である
                if( $alertInfo['alert_info']['balance_payments'] == $weekNo )
                {
                    $sDate = date( 'Y/m/d', strtotime( $targetDate . '-1 week' ) );
                    $eDate = date( 'Y/m/d', strtotime( $targetDate . '+1 day' ) );
                    if($isShift)
                    {
                        $numberAttendances = $model->getShiftNumberAttendances( $attendanceCt['user_id'], $sDate, $eDate );
                    }
                    else
                    {
                        $numberAttendances = $model->getNumberAttendances( $attendanceCt['user_id'], $sDate, $eDate );
                    }
                    
                    // 休暇日数を算出
                    $vacationDays = 7 - $numberAttendances;

                    // 1週間の内、必要日数の休暇があったか
                    if( $vacationDays < $alertInfo['labor_standards_act']['holiday_dates_1week'] )
                    {
                        $holidayMsg = '1週間の内、休日が' . $alertInfo['labor_standards_act']['holiday_dates_1week'] . '日必要です。';
                    }
                }
            }
            else if( $alertInfo['alert_info']['holiday_settings'] == 2 )
            {
                // 1ヵ月単位で休暇をチェック
                // 締日を設定する
                $endDay = $alertInfo['alert_info']['month_tightening'];
                if( $endDay == 31 )
                {
                    // 月末を設定する
                    $endDay = date('t'); 
                }
                
                // 対象日の日付を取得する
                $day = date( 'd', strtotime( $targetDate ) );
                
                // チェック対象日である
                if( $endDay == $day )
                {
                    $dayList = $model->getMonthDayList( $targetDate, $alertInfo['alert_info']['month_tightening'] );
                    
                    $sDate = date( 'Y/m/d', strtotime( $dayList[0] ) );
                    $eDate = date( 'Y/m/d', strtotime( end($dayList) . '+1 day' ) );
                    if($isShift)
                    {
                        $numberAttendances = $model->getShiftNumberAttendances( $attendanceCt['user_id'], $sDate, $eDate );
                    }
                    else
                    {
                        $numberAttendances = $model->getNumberAttendances( $attendanceCt['user_id'], $sDate, $eDate );
                    }
                    // 労働期間中の日数
                    $maxDays = count( $dayList );
                    
                    // 休暇日数を算出
                    $vacationDays = $maxDays - $numberAttendances;
                    // 4週間の内、必要日数の休暇があったか
                    if( $vacationDays < $alertInfo['labor_standards_act']['holiday_dates_4week'] )
                    {
                        $holidayMsg = '4週間の内、休日が' . $alertInfo['labor_standards_act']['holiday_dates_4week'] . '日必要です。';
                    }
                }
                
            }
            
            $alertMsg = '';
            if( $holidayMsg != "" )
            {
                // 休暇日数のアラート
                $alertMsg .= $holidayMsg;
            }
            
            $Log->trace("END   checkHolidayFraud");
            
            return $alertMsg;
        }

    }
?>
