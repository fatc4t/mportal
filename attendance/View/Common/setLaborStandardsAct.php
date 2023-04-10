<?php
    /**
     * @file      就業規則の労働基準法アラートチェック
     * @author    USE Y.Sakata
     * @date      2016/10/24
     * @version   1.00
     * @note      就業規則の労働基準法アラートチェック
     */
?>
        <div id="jquery-replace-ajax">
            <script type="text/javascript">
                var isAlert = false;    // アラート有無
                var allAlertMsg = '';   // 就業規則上の全アラート
                
                /**
                 * アラートチェック
                 */
                function alertCheck()
                {
                    isAlert = false;
                    allAlertMsg = '';
                    // アラートチェックを行うか
                    if( 0 != <?php echo $mAlert['is_labor_standards_act']; ?> )
                    {
                        // アラートチェックは、行わない
                        return '';
                    }
                    
                    // 現在表示中のアラートを一旦クリア
                    document.getElementById('overtime_alert_font2').innerText = "";  // 日/週の残業時間
                    document.getElementById('overtime_alert_font4').innerText = "";  // 週：日の残業時間
                    document.getElementById('overtime_alert_font6').innerText = "";  // 週：週の残業時間
                    document.getElementById('overtime_alert_font8').innerText = "";  // 曜日：月の残業時間
                    document.getElementById('overtime_alert_font10').innerText = ""; // 曜日：火の残業時間
                    document.getElementById('overtime_alert_font12').innerText = ""; // 曜日：水の残業時間
                    document.getElementById('overtime_alert_font14').innerText = ""; // 曜日：木の残業時間
                    document.getElementById('overtime_alert_font16').innerText = ""; // 曜日：金の残業時間
                    document.getElementById('overtime_alert_font18').innerText = ""; // 曜日：土の残業時間
                    document.getElementById('overtime_alert_font20').innerText = ""; // 曜日：日の残業時間
                    document.getElementById('overtime_alert_font22').innerText = ""; // 曜日：祝の残業時間
                    document.getElementById('overtime_alert_font24').innerText = ""; // 月の総日数：28日の残業時間
                    document.getElementById('overtime_alert_font26').innerText = ""; // 月の総日数：29日の残業時間
                    document.getElementById('overtime_alert_font28').innerText = ""; // 月の総日数：30日の残業時間
                    document.getElementById('overtime_alert_font30').innerText = ""; // 月の総日数：31日の残業時間
                    document.getElementById('overtime_alert_font32').innerText = ""; // 各月/年：1月の残業時間
                    document.getElementById('overtime_alert_font34').innerText = ""; // 各月/年：2月の残業時間
                    document.getElementById('overtime_alert_font36').innerText = ""; // 各月/年：3月の残業時間
                    document.getElementById('overtime_alert_font38').innerText = ""; // 各月/年：4月の残業時間
                    document.getElementById('overtime_alert_font40').innerText = ""; // 各月/年：5月の残業時間
                    document.getElementById('overtime_alert_font42').innerText = ""; // 各月/年：6月の残業時間
                    document.getElementById('overtime_alert_font44').innerText = ""; // 各月/年：7月の残業時間
                    document.getElementById('overtime_alert_font46').innerText = ""; // 各月/年：8月の残業時間
                    document.getElementById('overtime_alert_font48').innerText = ""; // 各月/年：9月の残業時間
                    document.getElementById('overtime_alert_font50').innerText = ""; // 各月/年：10月の残業時間
                    document.getElementById('overtime_alert_font52').innerText = ""; // 各月/年：11月の残業時間
                    document.getElementById('overtime_alert_font54').innerText = ""; // 各月/年：12月の残業時間
                    
                    document.getElementById('legalInLabel_font').innerText = "";            // 割増賃金(法定内)
                    document.getElementById('legalOutLabel_font').innerText = "";           // 割増賃金(法定外)
                    document.getElementById('fortyfiveHoursLabel_font').innerText = "";     // 割増賃金(法定外45)
                    document.getElementById('sixtyHoursLabel_font').innerText = "";         // 割増賃金(法定外60)
                    document.getElementById('lateAtNightLabel_font').innerText = "";        // 割増賃金(深夜)
                    document.getElementById('legalHolidayLabel_font').innerText = "";       // 割増賃金(法定休)
                    document.getElementById('prescribedHolidayLabel_font').innerText = "";  // 割増賃金(公休)

                    document.getElementById('late_at_night_font').innerText = "";           // 深夜残業時間
                    
                    var baseTime = 0;
                    var minute   = 0;
                    
                    // 残業時間アラートチェック
                    switch ( $('#overtime_setting').val() )
                    {
                        case "1":
                            // 日単位
                            // 時間を分に修正
                            minute = convertTimeToMinute( $('#overtime_reference_time').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font2', "日の残業時間：" );
                            break

                        case "2":
                            // 週単位
                            // 時間を分に修正
                            minute = convertTimeToMinute( $('#overtime_reference_time').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_week']; ?>, 'overtime_alert_font2', "週の残業時間：" );
                            break

                        case "4":
                            // 日と週の大きい単位
                            // 時間を分に修正(日)
                            minute = convertTimeToMinute( $('#overtime_reference_time_day').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font4', "日の残業時間：" );
                            
                            // 時間を分に修正(週)
                            minute = convertTimeToMinute( $('#overtime_reference_time_week').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_week']; ?>, 'overtime_alert_font6', "週の残業時間：" );
                            break

                        case "5":
                            // 曜日単位
                            // 時間を分に修正(月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_mon').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font8', "月曜の残業時間：" );
                            
                            // 時間を分に修正(火)
                            minute = convertTimeToMinute( $('#overtime_reference_time_tue').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font10', "火曜の残業時間：" );
                            
                            // 時間を分に修正(水)
                            minute = convertTimeToMinute( $('#overtime_reference_time_wed').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font12', "水曜の残業時間：" );
                            
                            // 時間を分に修正(木)
                            minute = convertTimeToMinute( $('#overtime_reference_time_thu').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font14', "木曜の残業時間：" );
                            
                            // 時間を分に修正(金)
                            minute = convertTimeToMinute( $('#overtime_reference_time_fri').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font16', "金曜の残業時間：" );
                            
                            // 時間を分に修正(土)
                            minute = convertTimeToMinute( $('#overtime_reference_time_sat').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font18', "土曜の残業時間：" );
                            
                            // 時間を分に修正(日)
                            minute = convertTimeToMinute( $('#overtime_reference_time_sun').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font20', "日曜の残業時間：" );
                            
                            // 時間を分に修正(祝)
                            minute = convertTimeToMinute( $('#overtime_reference_time_hol').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['working_hours_day']; ?>, 'overtime_alert_font22', "祝日の残業時間：" );
                            break

                        case "6":
                            // 月の総日数別単位
                            // 時間を分に修正(28日)
                            minute = convertTimeToMinute( $('#overtime_reference_time_28').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_28']; ?>, 'overtime_alert_font24', "月の総日数別(28日)の残業時間：" );
                            
                            // 時間を分に修正(29日)
                            minute = convertTimeToMinute( $('#overtime_reference_time_29').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_29']; ?>, 'overtime_alert_font26', "月の総日数別(29日)の残業時間：" );
                            
                            // 時間を分に修正(30日)
                            minute = convertTimeToMinute( $('#overtime_reference_time_30').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_30']; ?>, 'overtime_alert_font28', "月の総日数別(30日)の残業時間：" );
                            
                            // 時間を分に修正(31日)
                            minute = convertTimeToMinute( $('#overtime_reference_time_31').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font30', "月の総日数別(31日)の残業時間：" );
                            break

                        case "3":
                        case "7":
                            // 年単位
                            // 月(各月)単位
                            // 時間を分に修正(1月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_1').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font32', "1月の残業時間：" );
                            
                            // 時間を分に修正(2月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_2').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_28']; ?>, 'overtime_alert_font34', "2月の残業時間：" );
                            
                            // 時間を分に修正(3月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_3').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font36', "3月の残業時間：" );
                            
                            // 時間を分に修正(4月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_4').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_30']; ?>, 'overtime_alert_font38', "4月の残業時間：" );
                            
                            // 時間を分に修正(5月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_5').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font40', "5月の残業時間：" );
                            
                            // 時間を分に修正(6月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_6').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_30']; ?>, 'overtime_alert_font42', "6月の残業時間：" );
                            
                            // 時間を分に修正(7月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_7').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font44', "7月の残業時間：" );
                            
                            // 時間を分に修正(8月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_8').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font46', "8月の残業時間：" );
                            
                            // 時間を分に修正(9月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_9').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_30']; ?>, 'overtime_alert_font48', "9月の残業時間：" );
                            
                            // 時間を分に修正(10月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_10').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font50', "10月の残業時間：" );
                            
                            // 時間を分に修正(11月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_11').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_30']; ?>, 'overtime_alert_font52', "11月の残業時間：" );
                            
                            // 時間を分に修正(12月)
                            minute = convertTimeToMinute( $('#overtime_reference_time_12').val() );
                            checkOverTime( minute, <?php echo $laborStandardsAct['month_of_days_31']; ?>, 'overtime_alert_font54', "12月の残業時間：" );
                            
                            break
                        case "8":
                            // シフト単位
                            break
                    }
                    
                    // 割増賃金のアラートチェック
                    // 法定内残業代
                    checkExtraPay( <?php echo $laborStandardsAct['legal_in_overtime']; ?>, $('#legal_time_in_overtime').val(), <?php echo $laborStandardsAct['legal_in_overtime_set_value']; ?>, $('#legal_time_in_overtime_value').val(), "legalInLabel_font", "法定内残業代：" );
                    // 法定外残業代
                    checkExtraPay( <?php echo $laborStandardsAct['legal_out_overtime']; ?>, $('#legal_time_out_overtime').val(), <?php echo $laborStandardsAct['legal_out_overtime_set_value']; ?>, $('#legal_time_out_overtime_value').val(), "legalOutLabel_font", "法定外残業代：" );
                    // 法定外残業代(45時間以上)
                    checkExtraPay( <?php echo $laborStandardsAct['legal_out_overtime_45']; ?>, $('#legal_time_out_overtime_45').val(), <?php echo $laborStandardsAct['legal_out_overtime_set_value_45']; ?>, $('#legal_time_out_overtime_value_45').val(), "fortyfiveHoursLabel_font", "法定外残業代(45時間以上)：" );
                    // 法定外残業代(60時間以上)
                    checkExtraPay( <?php echo $laborStandardsAct['legal_out_overtime_60']; ?>, $('#legal_time_out_overtime_60').val(), <?php echo $laborStandardsAct['legal_out_overtime_set_value_60']; ?>, $('#legal_time_out_overtime_value_60').val(), "sixtyHoursLabel_font", "法定外残業代(60時間以上)：" );
                    // 深夜残業代
                    checkExtraPay( <?php echo $laborStandardsAct['late_night_work']; ?>, $('#late_at_night_out_overtime').val(), <?php echo $laborStandardsAct['late_night_work_set_value']; ?>, $('#late_at_night_out_overtime_value').val(), "lateAtNightLabel_font", "深夜残業代：" );
                    // 法定休日残業代
                    checkExtraPay( <?php echo $laborStandardsAct['statutory_holiday_overtime']; ?>, $('#legal_holiday_allowance').val(), <?php echo $laborStandardsAct['statutory_holiday_overtime_set_value']; ?>, $('#legal_holiday_allowance_value').val(), "legalHolidayLabel_font", "法定休日残業代：" );
                    // 公休残業代
                    checkExtraPay( <?php echo $laborStandardsAct['public_holiday_overtime']; ?>, $('#prescribed_holiday_allowance').val(), <?php echo $laborStandardsAct['public_holiday_overtime_set_value']; ?>, $('#prescribed_holiday_allowance_value').val(), "prescribedHolidayLabel_font", "公休残業代：" );
                    
                    // 深夜時間帯のアラートチェック
                    if( "<?php echo ( $laborStandardsAct['late_night_work_start_time1'] ); ?>" == $('#late_at_night_start').val() && "<?php echo ( $laborStandardsAct['late_night_work_end_time1'] ); ?>" == $('#late_at_night_end').val() )
                    {
                        // 正常、なにもなし
                    }
                    else if( "<?php echo ( $laborStandardsAct['late_night_work_start_time2'] ); ?>" == $('#late_at_night_start').val() && "<?php echo ( $laborStandardsAct['late_night_work_end_time2'] ); ?>" == $('#late_at_night_end').val() )
                    {
                        // 正常、なにもなし
                    }
                    else
                    {
                        var lateNightMsg = "\n※" + "<?php echo ( $laborStandardsAct['late_night_work_start_time1'] ); ?>" + "～" + "<?php echo ( $laborStandardsAct['late_night_work_end_time1'] ); ?>";
                        lateNightMsg = lateNightMsg + "又は、" + "<?php echo ( $laborStandardsAct['late_night_work_start_time2'] ); ?>" + "～" + "<?php echo ( $laborStandardsAct['late_night_work_end_time2'] ); ?>" + "以外の値が設定されています。";
                        document.getElementById("late_at_night_font").innerText = lateNightMsg;
                        allAlertMsg += "\n　　深夜残業時間：" + "<?php echo ( $laborStandardsAct['late_night_work_start_time1'] ); ?>" + "～" + "<?php echo ( $laborStandardsAct['late_night_work_end_time1'] ); ?>";
                        allAlertMsg += "又は、" + "<?php echo ( $laborStandardsAct['late_night_work_start_time2'] ); ?>" + "～" + "<?php echo ( $laborStandardsAct['late_night_work_end_time2'] ); ?>" + "以外の値が設定されています。";
                        isAlert = true;
                    }
                    
                    // 休憩時間のアラートチェック
                    // 手動取得以外
                    if( 1 != $('#break_time_acquisition').val() )
                    {
                        // 設定時間分、付与であるか
                        if( 1 == $('#automatic_break_time_acquisition').val() )
                        {
                            // 拘束時間の値を配列で保持する
                            var bindingArray = $(".binding_hour").map(function(){
                                return $(this).val();
                            }).get();
                            
                            // 付与時間の値を配列で保持する
                            var mwrbBreakArray = $(".mwrb_break_time").map(function(){
                                return $(this).val();                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
                            }).get();
                            
                            // 拘束時間のIDを配列で保持する
                            var bindingIdArray = $(".binding_hour").map(function(){
                                return $(this).attr("id"); 
                            }).get();
                            
                            var len = bindingArray.length;
                            
                            for (var i = 0 ; i < len ; i++)
                            {
                                var id = bindingIdArray[i];
                                var resultID = id.replace( /binding_hour/g , "" ) ;
                                
                                // アラート表示クリア
                                document.getElementById("mwrb_break_time_font" + resultID).innerText = '';
                                
                                // 拘束時間が8時間以上か
                                if( 480 <= convertTimeToMinute( bindingArray[i] ) )
                                {
                                    // 休憩時間が指定時間以上か
                                    if( <?php echo ( $laborStandardsAct['break_time_8_hours_work_time'] ); ?> > convertTimeToMinute( mwrbBreakArray[i] ) )
                                    {
                                        // 休憩時間不足のアラート
                                        document.getElementById("mwrb_break_time_font" + resultID).innerText = "\n※拘束時間8時間以上に対し、休憩時間を" + convertMinuteToTime( <?php echo ( $laborStandardsAct['break_time_8_hours_work_time'] ); ?> ) + "時間以下に設定しています。" ;
                                        allAlertMsg += "\n　　休憩時間：拘束時間8時間以上に対し、休憩時間を" + convertMinuteToTime( <?php echo ( $laborStandardsAct['break_time_8_hours_work_time'] ); ?> ) + "時間以下に設定しています。" ;
                                        isAlert = true;
                                    }
                                }
                                else if( 360 <= convertTimeToMinute( bindingArray[i] ) )
                                {
                                    if( <?php echo ( $laborStandardsAct['break_time_6_hours_work_time'] ); ?> > convertTimeToMinute( mwrbBreakArray[i] ) )
                                    {
                                        // 休憩時間不足のアラート
                                        document.getElementById("mwrb_break_time_font" + resultID).innerText = "\n※拘束時間6時間以上に対し、休憩時間を" + convertMinuteToTime( <?php echo ( $laborStandardsAct['break_time_6_hours_work_time'] ); ?> ) + "時間以下に設定しています。" ;
                                        allAlertMsg += "\n　　休憩時間：拘束時間6時間以上に対し、休憩時間を" + convertMinuteToTime( <?php echo ( $laborStandardsAct['break_time_6_hours_work_time'] ); ?> ) + "時間以下に設定しています。" ;
                                        isAlert = true;
                                    }
                                }
                            }
                        }
                    }
                    
                    
                    var alertMsg = "";
                    if( isAlert )
                    {
                        alertMsg = "労働基準法違反のアラートがあります。";
                    }
                    
                    return alertMsg;
                }

                /**
                 * 割増賃金のチェックメソッド
                 */
                function checkExtraPay( laborStandardsActSetValue, referenceSetValue, laborStandardsActRatePremium, ratePremium, fontID, headMsg )
                {
                    // 割増残業の設定値のチェック
                    if( laborStandardsActSetValue == referenceSetValue )
                    {
                        // 割増率が労働基準法上の値より上であるか
                        if( laborStandardsActRatePremium > ratePremium )
                        {
                            // 割増率が労働基準法以下である
                            document.getElementById(fontID).innerText = "\n※労働基準法上の割増賃金の割合( " + laborStandardsActRatePremium + "% )以下を設定しています。 " ;
                            allAlertMsg += "\n　　" + headMsg + "労働基準法上の割増賃金の割合( " + laborStandardsActRatePremium + "% )以下を設定しています。 " ;
                            isAlert = true;
                        }
                    }
                    else
                    {
                        // 割増率の設定ではない
                        document.getElementById(fontID).innerText = "\n※労働基準法の適用設定と異なります。";
                        allAlertMsg += "\n　　" + headMsg + "労働基準法の適用設定と異なります。";
                        isAlert = true;
                    }
                }

                /**
                 * 残業時間のチェックメソッド
                 */
                function checkOverTime( minute, laborStandardsActBaseTime, fontID, headMsg )
                {
                    // 労働基準法の時間より、大きくないか
                    if( minute > laborStandardsActBaseTime )
                    {
                        // 労働基準法違反
                        document.getElementById(fontID).innerText = "\n※" + getOverTimeAlertMsg( laborStandardsActBaseTime );
                        allAlertMsg += "\n　　" + headMsg + getOverTimeAlertMsg( laborStandardsActBaseTime );
                        isAlert = true;
                    }
                    else if( 0 == <?php echo $mAlert['is_labor_standards_act_warning']; ?> )
                    {
                        // アラートマスタの閾値を超えているか
                        baseTime = laborStandardsActBaseTime * <?php echo ( $mAlert['warning_value'] ); ?> / 100;
                        
                        if( minute > baseTime )
                        {
                            // アラートマスタの閾値オーバー
                            document.getElementById(fontID).innerText = "\n※" + getOverTimeThresholdAlertMsg( baseTime );
                            allAlertMsg += "\n　　" + headMsg + getOverTimeThresholdAlertMsg( baseTime );
                            isAlert = true;
                        }
                    }
                }
                
                /**
                 * 時間を分する
                 */
                function convertTimeToMinute( time )
                {
                    var timeSplit = time.split(":");
                    
                    var minute = timeSplit[0] * 60 + parseInt(timeSplit[1] );
                    
                    return minute;
                }

                /**
                 * 分を時間にする
                 */
                function convertMinuteToTime( minute )
                {
                    var time1 = Math.floor( minute / 60 );
                    if( time1 < 10 )
                    {
                        time1 = "0" + time1;
                    }
                    var time2 = Math.floor( minute % 60 );
                    if( time2 < 10 )
                    {
                        time2 = "0" + time2;
                    }
                    
                    return time1 + ":" + time2;
                }

                /**
                 * 残業時間のアラートメッセージを作成
                 */
                function getOverTimeAlertMsg( minute )
                {
                    var msg  = "労働基準法の残業時間( ";
                    var time = convertMinuteToTime( minute );
                    msg = msg + time + " )を超えています。"
                    
                    return msg;
                }

                /**
                 * 残業時間の閾値アラートメッセージを作成
                 */
                function getOverTimeThresholdAlertMsg( minute )
                {
                    var msg  = "残業時間の閾値時間( ";
                    var time = convertMinuteToTime( minute );
                    msg = msg + time + " )を超えています。"
                    
                    return msg;
                }

            </script>
        </div><!-- /.jquery-replace-ajax -->
