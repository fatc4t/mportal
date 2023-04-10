<?php
    /**
     * @file      シフトフォーマットダウンロード/インポート画面
     * @author    USE K.Narita
     * @date      2016/07/29
     * @version   1.00
     * @note      シフトフォーマットの管理を行う
     */

    // CheckAlert.phpを読み込む
    require './Model/CheckAlert.php';
    // Excel読み込み用ファイル
    require_once SystemParameters::$FW_COMMON_PATH . 'PHPExcel_1-8-0/Classes/PHPExcel/IOFactory.php';
    // ShiftTimeRegistration.phpを読み込む
    require './Model/ShiftTimeRegistration.php';

    /**
     * ログインクラス
     * @note   ログイン時の認証処理及び、セッションの初期設定を行う
     */
    class shiftDlUp extends CheckAlert
    {
        public $objPExcel = null;                   ///< PHP Excelのクラスオブジェクト
        protected $workTimeReg = null;              ///< 労働時間計算用モデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
            global $workTimeReg;
            $workTimeReg = new ShiftTimeRegistration();
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
         * Excelファイル読み込み
         * @param    $fileName   Excelファイル名
         * @return   成功時：true  失敗：false
         */
        public function setExcel( $fileName )
        {
            global $Log; // ログクラス
            $Log->trace("START setExcel");

            // パスから拡張子を取得
            $path_parts = pathinfo( $fileName );
            // 添付ファイルがExcelである
            if( $path_parts['extension'] === 'xlsx' || $path_parts['extension'] === 'xls' || $path_parts['extension'] === 'xlsm' )
            {
                // xlsxをPHPExcelに食わせる
                $this->objPExcel = PHPExcel_IOFactory::load( $fileName );
                
                // 読み込み成功
                return true;
            }

            $Log->trace("END setExcel");

            return false;
        }

        /**
         * シフト勤怠情報を取得する
         * @param    $organizationID  組織ID
         * @param    $sDay            開始日
         * @param    $eDay            終了日
         * @return   成功時：true  失敗：false
         */
        public function getShiftAttendance( $organizationID, $sDay, $eDay )
        {
            global $Log, $DBA; // ログクラス
            $Log->trace("START getShiftAttendance");

            // シフト勤怠情報を取得
            $sql  = " SELECT ts.shift_id, ts.user_id, ts.day, ts.attendance as attendance_time, ts.break_time, ts.total_working_time, vu.user_name "
                  . " FROM t_shift ts INNER JOIN v_user vu ON ts.user_id = vu.user_id AND vu.is_del = 0 AND vu.eff_code = '適用中' "
                  . " WHERE ts.organization_id = :organization_id AND :sDay <= ts.day AND ts.day < :eDay "
                  . " ORDER BY ts.day, ts.shift_id ";

            $parameters = array( 
                                    ':organization_id' => $organizationID, 
                                    ':sDay'            => $sDay, 
                                    ':eDay'            => $eDay, 
                               );
            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array();
            if( $result === false )
            {
                $Log->trace("END  getShiftAttendance");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data );
            }

            $Log->trace("END  getShiftAttendance");

            return $ret;
        }

        /**
         * 所定残業時間を取得する
         * @param    $userID               ユーザID
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   所定残業時間
         */
        public function getPrescribedOvertimeHours( $userID, $laborRegulationsID, $targetDate )
        {
            global $Log, $DBA, $workTimeReg; // グローバル変数宣言
            $Log->trace("START getPrescribedOvertimeHours");

            // 就業規則を取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $laborRegulationsID, $targetDate );
            $monthDay = 0;
            $prescribedOvertimeHours = $workTimeReg->getPrescribedOvertimeHours( $userID, $lrAllInfo, $targetDate, 't_shift', $monthDay );

            $Log->trace("END getPrescribedOvertimeHours");

            return $prescribedOvertimeHours;
        }
        
        /**
         * 当日の所定残業時間の残りを取得する
         * @param    $userID               ユーザID
         * @param    $laborRegulationsID   就業規則ID
         * @param    $targetDate           対象日
         * @return   当日の所定残業時間
         */
        public function getPredeterminedTime( $userID, $laborRegulationsID, $targetDate )
        {
            global $Log, $DBA, $workTimeReg; // グローバル変数宣言
            $Log->trace("START getPredeterminedTime");

            // 就業規則を取得
            $lrAllInfo = $this->getLaborRegulationsAllInfo( $laborRegulationsID, $targetDate );
            $monthDay = 0;
            $prescribedOvertimeHours = $workTimeReg->getPredeterminedTime( $userID, $lrAllInfo, $targetDate, 't_shift' );

            $Log->trace("END getPredeterminedTime");

            return $prescribedOvertimeHours;
        }
        
        /**
         * 指定日の1ヶ月の日数を取得する
         * @param    $workingDays       労働日
         * @param    $cutoffMonthDay    月の締日
         * @return   1ヵ月分の日付リスト
         */
        public function getMonthDayList( $workingDays, $cutoffMonthDay )
        {
            global $Log, $DBA, $workTimeReg; // グローバル変数宣言
            $Log->trace("START getMonthDayList");

            $dayList = $workTimeReg->getMonthDayList( $workingDays, $cutoffMonthDay );

            $Log->trace("END getMonthDayList");

            return $dayList;
        }

        /**
         * テンプレのエクセルに必要な書き込みを行う
         * @param    $postArray   入力パラメータ(年月/組織ID)
         * @return   なし
         */
        public function excelSave($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START excelSave");

            $userList = $this->getUserData( $postArray, false );     // 従業員の取得
            $sectionList = $this->getSectionList( $postArray['organizationID'] );         // セクション取得
            $holidayList = $this->getHolidayList( $postArray['organizationID'] );         // 休日名称の取得
            
            // テンプレートを読み込む
            $book = PHPExcel_IOFactory::load( SystemParameters::$SHIFT_SAVE_PATH . 'WorksheetTemp.xlsm');
            
            // 配列数取得
            $userCnt    = count($userList);
            $sectionCnt = count($sectionList);
            $holidayCnt = count($holidayList);
            
            // 2番目のシートをアクティブにする（シートは左から順に、0、1，2・・・）
            $book->setActiveSheetIndex(2);
            // アクティブにしたシートの情報を取得
            $objSheet = $book->getActiveSheet();
            
            for($k = 0 ; $k < $userCnt ; $k++)
            {
                $objSheet->getCell('B' . (6+$k))->setValue($k+1);
                $objSheet->setCellValueExplicit( 'D' . (6+$k), $userList[$k]['employees_no'], PHPExcel_Cell_DataType::TYPE_STRING );
                $objSheet->getCell('C' . (6+$k))->setValue($userList[$k]['employment_name']);
                $objSheet->getCell('E' . (6+$k))->setValue($userList[$k]['user_name']);
                $objSheet->getCell('J' . (6+$k))->setValue($userList[$k]['abbreviated_name']);
                $objSheet->getCell('H' . (6+$k))->setValue($userList[$k]['hourly_wage']);
                $objSheet->getCell('K' . (6+$k))->setValue($userList[$k]['wage_form_id']);
                $objSheet->getCell('L' . (6+$k))->setValue($userList[$k]['trial_period_type_id']);
                $objSheet->getCell('M' . (6+$k))->setValue($userList[$k]['trial_period_criteria_value']);
                $objSheet->getCell('N' . (6+$k))->setValue($userList[$k]['trial_period_write_down_criteria']);
                $objSheet->getCell('O' . (6+$k))->setValue($userList[$k]['trial_period_wages_value']);
                $objSheet->getCell('CQ' . (6+$k))->setValue($userList[$k]['section_name']);
                
                if($userList[$k]['wage_form_id'] == 3)
                {
                    $objSheet->getCell('F' . (6+$k))->setValue($userList[$k]['base_salary']);
                }
                if($userList[$k]['wage_form_id'] == 2)
                {
                    $objSheet->getCell('G' . (6+$k))->setValue($userList[$k]['base_salary']);
                }
                
                $userInfo = array(
                    'user_id'           => $userList[$k]['user_id'],
                    'employment_id'     => $userList[$k]['employment_id'],
                    'position_id'       => $userList[$k]['position_id'],
                    'organization_id'   => $userList[$k]['organization_id'],
                );
                
                $laborList = $this->getUserEmploymentInfo($userInfo);
                
                $userLaborList = $this->getLaborRegulationList($laborList);
                
                $objSheet->getCell('P' . (6+$k))->setValue($userLaborList[0]['shift_time_unit']);
                $objSheet->getCell('Q' . (6+$k))->setValue($userLaborList[0]['overtime_setting_name']);
                
                if($userLaborList[0]['overtime_setting_name'] == "日" || $userLaborList[0]['overtime_setting_name'] == "年")
                {
                    $objSheet->getCell('S' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('AT' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                }
                
                if($userLaborList[0]['overtime_setting_name'] == "週")
                {
                    $objSheet->getCell('S' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('AT' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                    
                    if($userLaborList[0]['closing_date_set_id'] == 1)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を日数分でわる");
                    }
                    if($userLaborList[0]['closing_date_set_id'] == 2)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を締日前に寄せる");
                    }
                    if($userLaborList[0]['closing_date_set_id'] == 3)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を締日後に寄せる");
                    }
                    if($userLaborList[0]['closing_date_set_id'] == 4)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を2等分する");
                    }
                }
                
                if($userLaborList[0]['overtime_setting_name'] == "日と週の大きい方")
                {
                    $objSheet->getCell('T' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('U' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                    $objSheet->getCell('AU' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('AV' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                    
                    if($userLaborList[0]['closing_date_set_id'] == 1)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を日数分でわる");
                    }
                    if($userLaborList[0]['closing_date_set_id'] == 2)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を締日前に寄せる");
                    }
                    if($userLaborList[0]['closing_date_set_id'] == 3)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を締日後に寄せる");
                    }
                    if($userLaborList[0]['closing_date_set_id'] == 4)
                    {
                        $objSheet->getCell('R' . (6+$k))->setValue("残業時間を2等分する");
                    }
                }
                
                if($userLaborList[0]['overtime_setting_name'] == "曜日")
                {
                    $objSheet->getCell('V' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('W' . (6+$k))->setValue($userLaborList[1]['regular_working_hours_time']);
                    $objSheet->getCell('X' . (6+$k))->setValue($userLaborList[2]['regular_working_hours_time']);
                    $objSheet->getCell('Y' . (6+$k))->setValue($userLaborList[3]['regular_working_hours_time']);
                    $objSheet->getCell('Z' . (6+$k))->setValue($userLaborList[4]['regular_working_hours_time']);
                    $objSheet->getCell('AA' . (6+$k))->setValue($userLaborList[5]['regular_working_hours_time']);
                    $objSheet->getCell('AB' . (6+$k))->setValue($userLaborList[6]['regular_working_hours_time']);
                    $objSheet->getCell('AC' . (6+$k))->setValue($userLaborList[7]['regular_working_hours_time']);
                    
                    $objSheet->getCell('AW' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                    $objSheet->getCell('AX' . (6+$k))->setValue($userLaborList[1]['overtime_reference_time']);
                    $objSheet->getCell('AY' . (6+$k))->setValue($userLaborList[2]['overtime_reference_time']);
                    $objSheet->getCell('AZ' . (6+$k))->setValue($userLaborList[3]['overtime_reference_time']);
                    $objSheet->getCell('BA' . (6+$k))->setValue($userLaborList[4]['overtime_reference_time']);
                    $objSheet->getCell('BB' . (6+$k))->setValue($userLaborList[5]['overtime_reference_time']);
                    $objSheet->getCell('BC' . (6+$k))->setValue($userLaborList[6]['overtime_reference_time']);
                    $objSheet->getCell('BD' . (6+$k))->setValue($userLaborList[7]['overtime_reference_time']);
                }
                
                if($userLaborList[0]['overtime_setting_name'] == "月の総日数別")
                {
                    $objSheet->getCell('AD' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('AE' . (6+$k))->setValue($userLaborList[1]['regular_working_hours_time']);
                    $objSheet->getCell('AF' . (6+$k))->setValue($userLaborList[2]['regular_working_hours_time']);
                    $objSheet->getCell('AG' . (6+$k))->setValue($userLaborList[3]['regular_working_hours_time']);
                    
                    $objSheet->getCell('BE' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                    $objSheet->getCell('BF' . (6+$k))->setValue($userLaborList[1]['overtime_reference_time']);
                    $objSheet->getCell('BG' . (6+$k))->setValue($userLaborList[2]['overtime_reference_time']);
                    $objSheet->getCell('BH' . (6+$k))->setValue($userLaborList[3]['overtime_reference_time']);
                }
                
                if($userLaborList[0]['overtime_setting_name'] == "月（各月）")
                {
                    $objSheet->getCell('AH' . (6+$k))->setValue($userLaborList[0]['regular_working_hours_time']);
                    $objSheet->getCell('AI' . (6+$k))->setValue($userLaborList[1]['regular_working_hours_time']);
                    $objSheet->getCell('AJ' . (6+$k))->setValue($userLaborList[2]['regular_working_hours_time']);
                    $objSheet->getCell('AK' . (6+$k))->setValue($userLaborList[3]['regular_working_hours_time']);
                    $objSheet->getCell('AL' . (6+$k))->setValue($userLaborList[4]['regular_working_hours_time']);
                    $objSheet->getCell('AM' . (6+$k))->setValue($userLaborList[5]['regular_working_hours_time']);
                    $objSheet->getCell('AN' . (6+$k))->setValue($userLaborList[6]['regular_working_hours_time']);
                    $objSheet->getCell('AO' . (6+$k))->setValue($userLaborList[7]['regular_working_hours_time']);
                    $objSheet->getCell('AP' . (6+$k))->setValue($userLaborList[8]['regular_working_hours_time']);
                    $objSheet->getCell('AQ' . (6+$k))->setValue($userLaborList[9]['regular_working_hours_time']);
                    $objSheet->getCell('AR' . (6+$k))->setValue($userLaborList[10]['regular_working_hours_time']);
                    $objSheet->getCell('AS' . (6+$k))->setValue($userLaborList[11]['regular_working_hours_time']);
                    
                    $objSheet->getCell('BI' . (6+$k))->setValue($userLaborList[0]['overtime_reference_time']);
                    $objSheet->getCell('BJ' . (6+$k))->setValue($userLaborList[1]['overtime_reference_time']);
                    $objSheet->getCell('BK' . (6+$k))->setValue($userLaborList[2]['overtime_reference_time']);
                    $objSheet->getCell('BL' . (6+$k))->setValue($userLaborList[3]['overtime_reference_time']);
                    $objSheet->getCell('BM' . (6+$k))->setValue($userLaborList[4]['overtime_reference_time']);
                    $objSheet->getCell('BN' . (6+$k))->setValue($userLaborList[5]['overtime_reference_time']);
                    $objSheet->getCell('BO' . (6+$k))->setValue($userLaborList[6]['overtime_reference_time']);
                    $objSheet->getCell('BP' . (6+$k))->setValue($userLaborList[7]['overtime_reference_time']);
                    $objSheet->getCell('BQ' . (6+$k))->setValue($userLaborList[8]['overtime_reference_time']);
                    $objSheet->getCell('BR' . (6+$k))->setValue($userLaborList[9]['overtime_reference_time']);
                    $objSheet->getCell('BS' . (6+$k))->setValue($userLaborList[10]['overtime_reference_time']);
                    $objSheet->getCell('BT' . (6+$k))->setValue($userLaborList[11]['overtime_reference_time']);
                }
                
                $objSheet->getCell('BU' . (6+$k))->setValue($userLaborList[0]['legal_time_in_overtime']);
                $objSheet->getCell('BV' . (6+$k))->setValue($userLaborList[0]['legal_time_in_overtime_value']);
                $objSheet->getCell('BW' . (6+$k))->setValue($userLaborList[0]['legal_time_out_overtime']);
                $objSheet->getCell('BX' . (6+$k))->setValue($userLaborList[0]['legal_time_out_overtime_value']);
                $objSheet->getCell('BY' . (6+$k))->setValue($userLaborList[0]['legal_time_out_overtime_45']);
                $objSheet->getCell('BZ' . (6+$k))->setValue($userLaborList[0]['legal_time_out_overtime_value_45']);
                $objSheet->getCell('CA' . (6+$k))->setValue($userLaborList[0]['legal_time_out_overtime_60']);
                $objSheet->getCell('CB' . (6+$k))->setValue($userLaborList[0]['legal_time_out_overtime_value_60']);
                $objSheet->getCell('CC' . (6+$k))->setValue($userLaborList[0]['late_at_night_start']);
                $objSheet->getCell('CD' . (6+$k))->setValue($userLaborList[0]['late_at_night_end']);
                $objSheet->getCell('CE' . (6+$k))->setValue($userLaborList[0]['late_at_night_out_overtime']);
                $objSheet->getCell('CF' . (6+$k))->setValue($userLaborList[0]['late_at_night_out_overtime_value']);
                $objSheet->getCell('CG' . (6+$k))->setValue($userLaborList[0]['legal_holiday_allowance']);
                $objSheet->getCell('CH' . (6+$k))->setValue($userLaborList[0]['legal_holiday_allowance_value']);
                $objSheet->getCell('CI' . (6+$k))->setValue($userLaborList[0]['prescribed_holiday_allowance']);
                $objSheet->getCell('CJ' . (6+$k))->setValue($userLaborList[0]['prescribed_holiday_allowance_value']);
                $objSheet->getCell('CK' . (6+$k))->setValue($userLaborList[0]['fixed_overtime_type']);
                $objSheet->getCell('CL' . (6+$k))->setValue($userLaborList[0]['fixed_overtime_time']);
                $objSheet->getCell('CM' . (6+$k))->setValue($userLaborList[0]['hourly_wage_start_time']);
                $objSheet->getCell('CN' . (6+$k))->setValue($userLaborList[0]['hourly_wage_end_time']);
                $objSheet->getCell('CO' . (6+$k))->setValue($userLaborList[0]['hourly_wage']);
                $objSheet->getCell('CP' . (6+$k))->setValue($userLaborList[0]['hourly_wage_value']);
            }
            
            $objSheet->getCell('B' . (6+$userCnt))->setValue('N');
            
            for($i = 0 ; $i < $sectionCnt ; $i++ )
            {
                $objSheet->getCell('DB' . (6+$i))->setValue($sectionList[$i]['section_name']);
            }
            
            $objSheet->getCell('DC6')->setValue("公休");
            $objSheet->getCell('DC7')->setValue("法定休");
            for($i = 0 ; $i < $holidayCnt ; $i++ )
            {
                $objSheet->getCell('DC' . (8+$i))->setValue($holidayList[$i]['holiday']);
            }
            
            // 4番目のシートをアクティブにする（シートは左から順に、0、1，2・・・）
            $book->setActiveSheetIndex(4);
            // アクティブにしたシートの情報を取得
            $objSheet = $book->getActiveSheet();
            
            $maxLine = $userCnt + 10;   // 空行10行を設定
            if( $userCnt == 0 )
            {
                // 誰もいない場合、1行既に空行があるので、maxLineから1行引く
                $maxLine = $maxLine - 1;
            }
            
            for($i = 0 ; $i < $maxLine ; $i++)
            {
                $predictionCellIndex  =  9 + $i * 3;   // 予測行のIndex
                $shiftCellIndex       = 10 + $i * 3;   // シフト行のIndex
                $performanceCellIndex = 11 + $i * 3;   // 実績行のIndex
                
                if( $i > 0 || ( $userCnt == 0 && $i > 0 ) )
                {
                    // 2人以上要る場合、行を追加する
                    
                    // 3行挿入(結合セルを含む行は、PHPExcelの機能では、コピー&ペーストできない為、新規に3行追加する)
                    $objSheet->insertNewRowBefore( $predictionCellIndex, 3);
                    
                    // セル結合（S/種別/所属/社員コード/社員名/セクション(勤怠の1-2-3)/勤怠）
                    $objSheet->mergeCells('B' . $predictionCellIndex . ':B' . $performanceCellIndex);
                    $objSheet->mergeCells('C' . $predictionCellIndex . ':C' . $performanceCellIndex);
                    $objSheet->mergeCells('D' . $predictionCellIndex . ':D' . $performanceCellIndex);
                    $objSheet->mergeCells('E' . $predictionCellIndex . ':E' . $performanceCellIndex);
                    $objSheet->mergeCells('F' . $predictionCellIndex . ':F' . $performanceCellIndex);
                    $objSheet->mergeCells('H' . $predictionCellIndex . ':H' . $performanceCellIndex);
                    $objSheet->mergeCells('I' . $predictionCellIndex . ':I' . $performanceCellIndex);
                    $objSheet->mergeCells('N' . $predictionCellIndex . ':N' . $performanceCellIndex);
                    $objSheet->mergeCells('S' . $predictionCellIndex . ':S' . $performanceCellIndex);
                    
                    // 予測/シフト/実績のヘッダ項目の設定
                    $objSheet->getCell('G' . $predictionCellIndex)->setValue($objSheet->getCell('G9')->getValue());
                    $objSheet->getCell('G' . $shiftCellIndex)->setValue($objSheet->getCell('G10')->getValue());
                    $objSheet->getCell('G' . $performanceCellIndex)->setValue($objSheet->getCell('G11')->getValue());
                    $objSheet->getCell('X' . $predictionCellIndex)->setValue($objSheet->getCell('X9')->getValue());
                    $objSheet->getCell('X' . $shiftCellIndex)->setValue($objSheet->getCell('X10')->getValue());
                    $objSheet->getCell('X' . $performanceCellIndex)->setValue($objSheet->getCell('X11')->getValue());
                    
                    // デフォルトの書式設定のコピー
                    $objSheet->duplicateStyle($objSheet->getStyle('B9:BN11'), 'B' . $predictionCellIndex);
                    $objSheet->duplicateStyle($objSheet->getStyle('J10'), 'J' . $shiftCellIndex);
                    $objSheet->duplicateStyle($objSheet->getStyle('K10'), 'K' . $shiftCellIndex);
                    $objSheet->duplicateStyle($objSheet->getStyle('O10'), 'O' . $shiftCellIndex);
                    $objSheet->duplicateStyle($objSheet->getStyle('P10'), 'P' . $shiftCellIndex);
                    $objSheet->duplicateStyle($objSheet->getStyle('T10'), 'T' . $shiftCellIndex);
                    $objSheet->duplicateStyle($objSheet->getStyle('U10'), 'U' . $shiftCellIndex);
                    $objSheet->getStyle('Y' . $predictionCellIndex . ':BL' . $predictionCellIndex)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    
                    // 勤怠時間の計算式（終了時間-開始時間）
                    $timeRowList = array( $predictionCellIndex, $shiftCellIndex, $performanceCellIndex );
                    foreach ( $timeRowList as $timeRow )
                    {
                        $objSheet->getCell('L' . $timeRow)->setValue( '=IF(AND(J' . $timeRow . '<>"",K' . $timeRow . '<>""), K' . $timeRow . '-J' . $timeRow .',"")' );
                        $objSheet->getCell('Q' . $timeRow)->setValue( '=IF(AND(P' . $timeRow . '<>"",O' . $timeRow . '<>""), P' . $timeRow . '-O' . $timeRow .',"")' );
                        $objSheet->getCell('V' . $timeRow)->setValue( '=IF(AND(U' . $timeRow . '<>"",T' . $timeRow . '<>""), U' . $timeRow . '-T' . $timeRow .',"")' );
                    }
                }

                // 従業員データが存在するか
                if( $i < $userCnt )
                {
                    $userIndex = 6 + $i;    // ユーザ情報のIndex
                    
                    // 従業員情報
                    $objSheet->getCell('B' . $predictionCellIndex)->setValue('=人件費マスタ!B' . $userIndex);
                    $objSheet->getCell('C' . $predictionCellIndex)->setValue('=人件費マスタ!C' . $userIndex);
                    $objSheet->getCell('D' . $predictionCellIndex)->setValue('=人件費マスタ!J' . $userIndex);
                    $objSheet->getCell('E' . $predictionCellIndex)->setValue('=人件費マスタ!D' . $userIndex);
                    $objSheet->getCell('F' . $predictionCellIndex)->setValue('=人件費マスタ!E' . $userIndex);
                    
                    // 勤怠時間1のセクション情報
                    $objSheet->getCell('H' . $predictionCellIndex )->setValue('=人件費マスタ!CQ' . $userIndex);
                    // 勤怠時間2のセクション情報
                    $objSheet->getCell('N' . $predictionCellIndex )->setValue('=人件費マスタ!CQ' . $userIndex);
                    // 勤怠時間3のセクション情報
                    $objSheet->getCell('S' . $predictionCellIndex )->setValue('=人件費マスタ!CQ' . $userIndex);
                    
                }
            }
            
            // 合計行の計算式設定
            $inputLast = $maxLine * 3 + 8;
            $predictionCellIndex  =  9 + $maxLine * 3;   // 予測行のIndex
            $shiftCellIndex       = 10 + $maxLine * 3;   // シフト行のIndex
            $performanceCellIndex = 11 + $maxLine * 3;   // 実績行のIndex
            
            // 合計列リスト
            $totalRowList = array( 'L', 'M', 'Q', 'R', 'V', 'W' );
            foreach ( $totalRowList as $totalRow )
            {
                // 勤怠時間/休憩時間の集計関数設定
                $objSheet->getCell($totalRow . $predictionCellIndex  )->setValue( '=SUMIFS(' . $totalRow .'$9:' . $totalRow . $inputLast . ',G$9:G' . $inputLast . ',$G' . $predictionCellIndex . ')' );
                $objSheet->getCell($totalRow . $shiftCellIndex       )->setValue( '=SUMIFS(' . $totalRow .'$9:' . $totalRow . $inputLast . ',G$9:G' . $inputLast . ',$G' . $shiftCellIndex . ')' );
                $objSheet->getCell($totalRow . $performanceCellIndex )->setValue( '=SUMIFS(' . $totalRow .'$9:' . $totalRow . $inputLast . ',G$9:G' . $inputLast . ',$G' . $performanceCellIndex . ')' );
            }
            
            // シミュレーション列リスト
            $simulationRowList = array( 
                                         'Y' =>  'Z', 
                                        'AA' => 'AB', 
                                        'AC' => 'AD', 
                                        'AE' => 'AF', 
                                        'AG' => 'AH', 
                                        'AI' => 'AJ', 
                                        'AK' => 'AL', 
                                        'AM' => 'AN', 
                                        'AO' => 'AP', 
                                        'AQ' => 'AR', 
                                        'AS' => 'AT', 
                                        'AU' => 'AV', 
                                        'AW' => 'AX', 
                                        'AY' => 'AZ', 
                                        'BA' => 'BB', 
                                        'BC' => 'BD', 
                                        'BE' => 'BF', 
                                        'BG' => 'BH', 
                                        'BI' => 'BJ', 
                                        'BK' => 'BL', 
                                      );
            $simulationIndex = $predictionCellIndex + 16;   // シミュレーションの人数設定行を設定
            foreach ( $simulationRowList as $key => $value )
            {
                // 人数シミュレーション用、計算関数の設定
                $strMethod = '=SUMIFS(' . $key .'$9:' . $key . $inputLast . ',G$9:G' . $inputLast . ',"シフト" ) + SUMIFS(' . $value .'$9:' . $value . $inputLast . ',G$9:G' . $inputLast . ',"シフト" )';
                $objSheet->getCell( $key . $simulationIndex )->setValue( $strMethod );
            }
            
            $temp = $this->objPExcel = PHPExcel_IOFactory::createWriter( $book, 'Excel2007' );
            $temp->save( SystemParameters::$SHIFT_SAVE_PATH . session_id() . '.xlsm');
            
            $book->disconnectWorksheets();
            unset($book);
            
            $Log->trace("END excelSave");
        }
        
        /**
         * 31日分のシートを追加する
         * @param    $postArray   入力パラメータ(年月/組織ID)
         * @param    $fileName    エクセル作成時に付ける名前(例：201506-12 YYYYMM-組織ID)
         * @param    $date        フォーマット作成年月
         * @return   なし
         */
        public function excelCopy($postArray, $fileName, $date)
        {
            global  $Log; // グローバル変数宣言
            $Log->trace("START excelCopy");
            
            $newBook = PHPExcel_IOFactory::load( SystemParameters::$SHIFT_SAVE_PATH . session_id() . '.xlsm');
            // 4番目のシートをアクティブにする（シートは左から順に、0、1，2・・・）
            $newBook->setActiveSheetIndex(4);
            // アクティブにしたシートの情報を取得
            $newObjSheet = $newBook->getActiveSheet();
            
            $sheet_copy = clone $newObjSheet;
            
            $newSheets = array();
            
            $lastDate = date('Y-m-d', strtotime('last day of ' . $date));
            $max = mb_substr($lastDate, -2);

            // 末日分配列にクローンを格納
            for ($i = 2; $i <= $max; $i++)
            {
                $index = strval($i);
                
                $sheet_copy->setTitle($index);
                
                $newSheets[] = clone $sheet_copy;
            }
            
            // 格納されたクローンの数分シートを追加する
            foreach ($newSheets as $newSheet)
            {
                $newBook->addSheet($newSheet,null);
            }
            
            $write = $this->objPExcel = PHPExcel_IOFactory::createWriter( $newBook, 'Excel2007' );
            $write->save($fileName);
            
            $Log->trace("END excelCopy");
        }
        
        /**
         * 取得したExcelの配列をINSERT用の配列に入れなおす
         *
         */
        public function loopList( $filePath, $fileName, $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            
            //モデル内で、該当で、参照可能な組織IDを取得する
            $securityProcess = new SecurityProcess();

            $organizationList = $this->getOrganizationList();
            $sectionList = $this->getSectionList( $postArray['organizationID'] );
            $userList = $this->getUserData( $postArray, true );
            $holidayList = $this->getHolidayList( $postArray['organizationID'] );

            //シート数取得
            $sheetsCount = $this->objPExcel->getSheetCount();

            // 勤務場所の組織IDを取得する
            $organizationID = explode( "-", $fileName );
            $formatList = array();

            // 1ヵ月分のシフトを取得
            for($j = 4; $j < $sheetsCount; $j++)
            {
                //シート取得
                $this->objPExcel->setActiveSheetIndex($j);
                $sheet = $this->objPExcel->getActiveSheet();

                //シート名取得
                $sheetName = $sheet->getTitle();
                $fileList = $this->objPExcel->getActiveSheet()->toArray(null,true,true,true);

                $listCount = count($fileList);
                // シフトの日付作成
                $dateName = substr($fileName,0,4) . '-' . substr($fileName,4,2) . '-' . $sheetName;

                // 1シート(1日)分のシフト情報を取得
                for( $i = 0; ($i*3+9) < $listCount; $i++ )
                {
                    $user_id = 0;
                    // 従業員Noから、ユーザIDを取得
                    foreach($userList as $user)
                    {
                        if( $fileList[9+$i*3]['E'] == $user['employees_no'])
                        {
                            $user_id = $user['user_id'];
                            break;
                        }
                    }

                    // 従業員が入力されている場合、シフト登録用データを作成する
                    if( $user_id != 0 )
                    {
                        // 勤怠時間1回目
                        $inPara = array(
                                         'inUserID'         =>   $user_id,
                                         'inOrganizationID' =>   $organizationID[1],
                                         'inAttendance'     =>   $fileList[10+$i*3]['J'],
                                         'inTaikin'         =>   $fileList[10+$i*3]['K'],
                                         'inBreakTime'      =>   $fileList[10+$i*3]['M'],
                                         'inWorkingTime'    =>   $fileList[10+$i*3]['L'],
                                         'inHoliday'        =>   $fileList[9+$i*3]['I'],
                                         'inHolidayList'    =>   $holidayList,
                                         'shiftDate'        =>   $dateName,
                                         'sectionName'      =>   $fileList[9+$i*3]['H'],
                                         'sectionList'      =>   $sectionList,
                                        );
                        
                        $format1 = $this->getShiftData( $inPara );
                        if( !is_null( $format1['attendance'] ) && !is_null( $format1['taikin'] ) && empty( $format1['is_holiday'] ) )
                        {
                            // 出勤-退勤時間が設定されている場合のみ登録する
                            array_push( $formatList, $format1 );
                        }
                        
                        // 勤怠時間2回目
                        $inPara = array(
                                         'inUserID'         =>   $user_id,
                                         'inOrganizationID' =>   $organizationID[1],
                                         'inAttendance'     =>   $fileList[10+$i*3]['O'],
                                         'inTaikin'         =>   $fileList[10+$i*3]['P'],
                                         'inBreakTime'      =>   $fileList[10+$i*3]['R'],
                                         'inWorkingTime'    =>   $fileList[10+$i*3]['Q'],
                                         'inHoliday'        =>   $fileList[9+$i*3]['I'],
                                         'inHolidayList'    =>   $holidayList,
                                         'shiftDate'        =>   $dateName,
                                         'sectionName'      =>   $fileList[9+$i*3]['N'],
                                         'sectionList'      =>   $sectionList,
                                        );
                        
                        $format2 = $this->getShiftData( $inPara );
                        if( !is_null( $format2['attendance'] ) && !is_null( $format2['taikin'] ) && empty( $format2['is_holiday'] ) )
                        {
                            // 出勤-退勤時間が設定されている場合のみ登録する
                            array_push( $formatList, $format2 );
                        }
                        
                        // 勤怠時間3回目
                        $inPara = array(
                                         'inUserID'         =>   $user_id,
                                         'inOrganizationID' =>   $organizationID[1],
                                         'inAttendance'     =>   $fileList[10+$i*3]['T'],
                                         'inTaikin'         =>   $fileList[10+$i*3]['U'],
                                         'inBreakTime'      =>   $fileList[10+$i*3]['W'],
                                         'inWorkingTime'    =>   $fileList[10+$i*3]['V'],
                                         'inHoliday'        =>   $fileList[9+$i*3]['I'],
                                         'inHolidayList'    =>   $holidayList,
                                         'shiftDate'        =>   $dateName,
                                         'sectionName'      =>   $fileList[9+$i*3]['S'],
                                         'sectionList'      =>   $sectionList,
                                        );
                        
                        $format3 = $this->getShiftData( $inPara );
                        if( !is_null( $format3['attendance'] ) && !is_null( $format3['taikin'] ) && empty( $format3['is_holiday'] ) )
                        {
                            // 出勤-退勤時間が設定されている場合のみ登録する
                            array_push( $formatList, $format3 );
                        }
                        
                        // 休日設定である場合、重複登録しないようにリストへデータを追加
                        if( !empty( $inPara['inHoliday'] ) )
                        {
                            // 1回目は、データがあれば登録する
                            if( !is_null( $format1['attendance'] ) && !is_null( $format1['taikin'] ) )
                            {
                                // 出勤-退勤時間が設定されている場合のみ登録する
                                array_push( $formatList, $format1 );
                            }
                            
                            // 2回目は、勤怠として入力がある場合、登録する
                            if( $format2['attendance'] !== $format2['taikin'] )
                            {
                                // 出勤-退勤時間が設定されている場合のみ登録する
                                array_push( $formatList, $format2 );
                            }
                            
                            // 3回目は、勤怠として入力がある場合、登録する
                            if( $format3['attendance'] !== $format3['taikin'] )
                            {
                                // 出勤-退勤時間が設定されている場合のみ登録する
                                array_push( $formatList, $format3 );
                            }
                        }
                    }else{
                        // 従業員Noが不一致の場合は、メッセージへ積む
                    }
                }
            }
            
            // 1ヶ月分のシフトデータを登録
            return $this->formatArray($formatList);
        }
        
        /**
         * 1シフトのデータ取得
         * @param    $inPara      シフト入力データ
         * @return   DB登録用パラメータ
         */
        private function getShiftData( $inPara )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getShiftData");

            // 値の初期化
            $attendanceTime = '2016-04-01 00:00:00';
            $taikinTime     = '2016-04-01 00:00:00';
            $breakTime      = '00:00:00';
            $workingTime    = '00:00:00';
            
            // 勤怠状況から、休日IDを取得
            $isHoliday = $this->getHolidayID( $inPara['inHoliday'], $inPara['inHolidayList'] );
            // Excelに入力されている、出勤/退勤/休憩時間を取得
            $attendanceTime  = $this->getTimeFormat( $inPara['inAttendance'], false );
            $taikinTime      = $this->getTimeFormat( $inPara['inTaikin'], false );
            $breakTime       = $this->getTimeFormat( $inPara['inBreakTime'], true );
            $workingTime     = $this->getTimeFormat( $inPara['inWorkingTime'], true );
            // セクションIDを取得
            $sectionID       = $this->getSectionID( $inPara['sectionName'], $inPara['sectionList'] );
            
            // 休日で、出勤退勤時間がNULLの場合、初期値を設定する
            if( is_null( $attendanceTime ) && is_null( $taikinTime ) && $isHoliday != 0 )
            {
                // 休日は、初期値を代入
                $attendanceTime = '2016-04-01 00:00:00';
                $taikinTime     = '2016-04-01 00:00:00';
            }
            
            $format = array(
                'user_id'           => $inPara['inUserID'],
                'organization_id'   => $inPara['inOrganizationID'],
                'section_id'        => $sectionID,
                'color'             => 0,
                'day'               => $inPara['shiftDate'],
                'is_holiday'        => $isHoliday,
                'attendance'        => $attendanceTime,
                'taikin'            => $taikinTime,
                'break_time'        => $breakTime,
                'working_time'      => $workingTime,
            );

            $Log->trace("END getShiftData");

            return $format;
        }
        
        /**
         * 時間フォーマットを取得
         * @param    $time       Excelから入力された時間(hh.mm)
         * @param    $isTime     Time型である(true)
         * @return   DB登録用時間フォーマット
         */
        private function getTimeFormat( $time, $isTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getTimeFormat");

            // 入力値が空白
            if( $time == "" )
            {
                return null;
            }

            // 時間分割(小数点で時分)を分割
            $timeSplit = explode( ".", $time );

            // 日付(固定)
            $ret    = "2016-04-01 ";
            $hour   = $timeSplit[0];
            $minute = $timeSplit[1];

            // 時間が24時間を超えているか
            if( $hour >= 24 )
            {
                // 日付をプラスし、時間を24時間マイナスする
                $ret  = "2016-04-02 ";
                $hour = $hour -24;
            }

            // 分が60以上である場合、0に修正する
            if( $minute >= 60 )
            {
                // 分を0に修正
                $minute = 0;
            }

            // 時間型である
            if( $isTime )
            {
                $ret = "";
            }

            // DB登録用時間を生成
            $ret = $ret . str_pad( $hour, 2, 0, STR_PAD_LEFT) . ":" . str_pad( $minute, 2, 0, STR_PAD_LEFT) . ":00";

            $Log->trace("END getTimeFormat");

            return $ret;
        }
        
        /**
         * セッションIDの取得
         * @param    $sectionName       セッション名
         * @param    $sectionList       セッションマスタリスト
         * @return   セッションID
         */
        private function getSectionID( $sectionName, $sectionList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getSectionID");

            $sectionID = 0;
            foreach( $sectionList as $section )
            {
                if( $sectionName === $section['section_name'] )
                {
                    $sectionID = $section['section_id'];
                    break;
                }
            }

            $Log->trace("END getSectionID");

            return $sectionID;
        }
        
        /**
         * 休日IDの取得
         * @param    $attendanceName    勤怠名(休日マスタ名称)
         * @param    $holidayList       休日マスタリスト
         * @return   休日ID
         */
        private function getHolidayID( $attendanceName, $holidayList )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayID");

            $isHoliday = 0;
            foreach($holidayList as $holiday)
            {
                if( $attendanceName == $holiday['holiday'] )
                {
                    $isHoliday = $holiday['holiday_id'];
                    break;
                }
                if( $attendanceName == '公休')
                {
                    $isHoliday = SystemParameters::$PUBLIC_HOLIDAY;
                    break;
                }
                if( $attendanceName == '法定休')
                {
                    $isHoliday = SystemParameters::$STATUTORY_HOLIDAY;
                    break;
                }
            }

            $Log->trace("END getHolidayID");

            return $isHoliday;
        }
        
        /**
         * 配列分ループし、呼び出す
         *
         */
        public function formatArray( $formatList )
        {
            global $DBA, $Log, $workTimeReg;  // グローバル変数宣言
            $Log->trace("START formatArray");
            
            if( $DBA->beginTransaction() )
            {
            
                foreach($formatList as $data)
                {
                    $postArray = array(
                        'userID'            => $data['user_id'],
                        'organizationID'    => $data['organization_id'],
                        'sectionID'         => $data['section_id'],
                        'color'             => $data['color'],
                        'day'               => $data['day'],
                        'isHoliday'         => $data['is_holiday'],
                        'attendance'        => $data['attendance'],
                        'taikin'            => $data['taikin'],
                        'breakTime'         => $data['break_time'],
                        'workingTime'       => $data['working_time'],
                        'user_id'           => $_SESSION["USER_ID"],
                        'organization_id'   => $_SESSION["ORGANIZATION_ID"],
                    );
                    
                    // シフトIDを取得
                    $shiftID = $this->getShift( $postArray );
                    if( $shiftID == 0 )
                    {
                        $ret = $this->addNewData( $postArray );
                    }
                    else
                    {
                        $ret = $this->modData( $postArray, $shiftID );
                    }
                    if($ret !== "MSG_BASE_0000")
                    {
                        // 下位でログ出力している為、省略
                        $DBA->rollBack();
                        $Log->trace("END formatArray");
                        return $ret;
                    }
                    
                    // シフト時間を元に、労働時間を再計算
                    $shiftID = $this->getShift( $postArray );
                    $ret = $workTimeReg->setShiftInfo( $shiftID );
                    if($ret !== "MSG_BASE_0000")
                    {
                        // 下位でログ出力している為、省略
                        $DBA->rollBack();
                        $Log->trace("END formatArray");
                        return $ret;
                    }
                    
                    // 勤怠テーブルのレコードの更新
                    $ret = $workTimeReg->setAttendanceRecord( $shiftID );
                    if($ret !== "MSG_BASE_0000")
                    {
                        // 下位でログ出力している為、省略
                        $DBA->rollBack();
                        $Log->trace("END formatArray");
                        return $ret;
                    }
                }
                
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "シフト登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END formatArray");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END formatArray");
                return "MSG_FW_DB_TRANSACTION_NG";
            }
            
            $Log->trace("END formatArray");
            return "MSG_BASE_0002";
        }

        /**
         * シフト勤務の概算金額を登録する
         * @param    $shiftID        基本給
         * @return   OK：MSG_BASE_0000  NG：その他
         */
        public function setShiftInfo( $shiftID )
        {
            global $DBA, $Log, $workTimeReg;  // グローバル変数宣言
            $Log->trace("START setShiftInfo");
            
            if( $DBA->beginTransaction() )
            {
                // シフト時間を元に、労働時間を再計算
                $ret = $workTimeReg->setShiftInfo( $shiftID );
                if($ret !== "MSG_BASE_0000")
                {
                    // 下位でログ出力している為、省略
                    $DBA->rollBack();
                    $Log->trace("END setShiftInfo");
                    return $ret;
                }
                    
                
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "シフト登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END setShiftInfo");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                $Log->trace("END setShiftInfo");
                return "MSG_FW_DB_TRANSACTION_NG";
            }
            
            $Log->trace("END setShiftInfo");
            
            return "MSG_BASE_0000";
        }

        /**
         * 従業員情報画面一覧表
         * @param    $postArray   入力パラメータ
         * @param    $isAllUser   給与システム内全ユーザ
         * @return   ユーザリスト
         */
        private function getUserData( $postArray, $isAllUser )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getUserData");

            $searchArray = array();
            $sql = $this->creatSQL($postArray,$searchArray, $isAllUser);

            $result = $DBA->executeSQL($sql, $searchArray);
            $userDataList = array();
            
            if( $result === false )
            {
                $Log->trace("END getUserData");
                return $userDataList;
            }
            
            $userIdList = array();
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                if( false == in_array( $data['user_id'], $userIdList ) )
                {
                    array_push( $userDataList, $data);
                    array_push( $userIdList, $data['user_id']);
                }
            }

            $Log->trace("END getUserData");

            return $userDataList;
        }

        /**
         * 従業員情報取得SQL文作成
         * @param    $postArray    入力パラメータ
         * @param    &$searchArray 検索パラメータ
         * @param    $isAllUser    給与システム内全ユーザ
         * @return   従業員情報取得SQL文
         */
        private function creatSQL( $postArray, &$searchArray, $isAllUser )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START creatSQL");
            
            $sql = ' SELECT  vu.eff_code,vu.user_id ,vu.user_name, vu.organization_name,vu.abbreviated_name,vu.position_name '
                .  '        ,vu.employment_name,vu.section_name,vu.wage_form_id,vu.hourly_wage,vu.base_salary, vu.application_date_start '
                .  '        ,mu.trial_period_type_id,mu.trial_period_criteria_value,mu.trial_period_write_down_criteria,mu.trial_period_wages_value'
                .  '        ,vu.userallowancelist,vu.userallowanceamountlist,vu.employees_no,vu.employment_id,vu.position_id,vu.organization_id '
                .  " FROM v_user vu INNER JOIN m_user mu ON mu.user_id = vu.user_id AND vu.eff_code <> '適用外' "
                .  "                INNER JOIN v_organization vo ON vo.organization_id = vu.organization_id AND vo.eff_code <> '適用外' ";

            if( !empty( $postArray['organizationID'] ) )
            {
                if( $isAllUser )
                {
                    // 給与システムの全ユーザ取得(シフトインポート時に使用)
                    $sql .= ' WHERE vo.payroll_system_id = :payroll_system_id ';
                    // 給与システムIDを取得する
                    $payrollSystemID = $this->getPayrollSystemID( $postArray['organizationID'] );
                    $codeIDArray = array(':payroll_system_id' => $payrollSystemID,);
                    $searchArray = array_merge($searchArray, $codeIDArray);
                }
                else
                {
                    // 該当組織のみ出力(シフトダウンロード時に使用)
                    $sql .= ' WHERE vu.organization_id = :organization_id ';
                    $codeIDArray = array(':organization_id' => $postArray['organizationID'],);
                    $searchArray = array_merge($searchArray, $codeIDArray);
                }
            }
            
            $sql .= 'ORDER BY vu.user_id, vu.application_date_start ,vu.eff_code ';
            
            $Log->trace("END creatSQL");
            return $sql;
        }

        /**
         * 給与システムIDを取得
         * @param    $organizationID
         * @return   給与システムID
         */
        private function getPayrollSystemID( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPayrollSystemID");

            $sql = "SELECT payroll_system_id FROM v_organization WHERE organization_id = :organization_id AND eff_code = '適用中' ";
            $parameters = array(':organization_id' => $organizationID,);

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END getPayrollSystemID");
                return $ret;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['payroll_system_id'];
            }

            $Log->trace("END getPayrollSystemID");

            return $ret;
        }

        /**
         * 組織一覧を取得
         * @return   組織一覧表
         */
        private function getOrganizationList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getOrganizationList");
            
            $searchArray = array();
            $sql = "SELECT organization_id , abbreviated_name "
                .  "FROM v_organization "
                .  "WHERE eff_code = '適用中' "
                .  "ORDER BY organization_id ";

            $result = $DBA->executeSQL( $sql, $searchArray);
            
            $list = array();
            if( $result === false )
            {
                $Log->trace("END getOrganizationList");
                return $list;
            }
            
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $key =  '"' . strval( $data['organization_id'] ) . '"';

                $input = array( $key  => $data['abbreviated_name'] , );
                $list = array_merge( $list, $input);

            }

            $Log->trace("END getOrganizationList");
            return $list;
        }
        
        /**
         * セクション一覧を取得
         * @param    $organizationID   組織ID
         * @return   セクション一覧
         */
        private function getSectionList( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getSectionList");
            
            // 入力された組織で、表示するマスタの上位IDを設定
            $topID = $this->securityProcess->getTopOrganizationID( $organizationID, 'm_section' );

            $sql = " SELECT section_id , organization_id , section_name "
                .  " FROM m_section "
                .  " WHERE organization_id = :organization_id AND is_del = 0 ";

            $searchArray = array( ':organization_id'    =>  $topID, );
            $result = $DBA->executeSQL( $sql, $searchArray);

            $list = array();
            if( $result === false )
            {
                $Log->trace("END getSectionList");
                return $list;
            }
            // 取得したデータを配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($list,$data);
            }

            $Log->trace("END getSectionList");
            return $list;
        }

        /**
         * 休日一覧を取得
         * @param    $organizationID   組織ID
         * @return   休日一覧
         */
        private function getHolidayList( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getHolidayList");
            
            // 入力された組織で、表示するマスタの上限を設定
            $topID = $this->securityProcess->getTopOrganizationID( $organizationID, 'm_holiday' );
            
            $searchArray = array();
            $sql = "SELECT holiday_id, holiday, organization_id "
                .  "FROM m_holiday "
                .  "WHERE is_del = 0 AND organization_id = :organization_id ";

            $searchArray = array( ':organization_id'    =>  $topID, );
            $result = $DBA->executeSQL( $sql, $searchArray);
            
            $list = array();
            if( $result === false )
            {
                $Log->trace("END getHolidayList");
                return $list;
            }
            // 取得したデータを配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($list,$data);
            }
            
            $Log->trace("END getHolidayList");
            return $list;
        }

        /**
         * 就業規則一覧表
         * @param    $postArray(organizationID/sectionName/positionName/employmentName/minDay/maxDay)
         * @return   成功時：$shiftList(section_name/day/count)  失敗：無
         */
        private function getLaborRegulationList($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShiftList");
            
            $searchArray = array();
            $sql = "SELECT mwrt.shift_time_unit , vlr.overtime_setting_name , mwrt.application_date_id,mwrt.labor_regulations_id,"
                .  "mo.regular_working_hours_time,mo.overtime_reference_time,mo.overtime_id,vlr.legal_time_in_overtime,"
                .  "mwra.legal_time_in_overtime_value,vlr.legal_time_out_overtime,mwra.legal_time_out_overtime_value,"
                .  "mwra.legal_time_out_overtime_45,mwra.legal_time_out_overtime_value_45,mwra.legal_time_out_overtime_60,"
                .  "mwra.legal_time_out_overtime_value_60,vlr.late_at_night_start,vlr.late_at_night_end,vlr.late_at_night_out_overtime,"
                .  "mwra.late_at_night_out_overtime_value,vlr.legal_holiday_allowance,mwra.legal_holiday_allowance_value,"
                .  "vlr.prescribed_holiday_allowance,mwra.prescribed_holiday_allowance_value,vlr.fixed_overtime,mwra.fixed_overtime_time,"
                .  "mhwc.hourly_wage_start_time,mhwc.hourly_wage_end_time,mhwc.hourly_wage,mhwc.hourly_wage_value,mo.closing_date_set_id,mwra.fixed_overtime_type "
                .  "FROM m_work_rules_time mwrt "
                .  "INNER JOIN v_labor_regulations vlr ON mwrt.application_date_id = vlr.application_date_id AND mwrt.labor_regulations_id = vlr.labor_regulations_id "
                .  "INNER JOIN m_overtime mo ON mo.application_date_id = mwrt.application_date_id AND mo.labor_regulations_id = mwrt.labor_regulations_id "
                .  "INNER JOIN m_work_rules_allowance mwra ON mwra.application_date_id = mwrt.application_date_id AND mwra.labor_regulations_id = mwrt.labor_regulations_id "
                .  "LEFT OUTER JOIN m_hourly_wage_change mhwc ON mhwc.application_date_id = mwrt.application_date_id AND mhwc.labor_regulations_id = mwrt.labor_regulations_id ";
            
            if( !empty( $postArray['labor_regulations_id'] ) )
            {
                $sql .= ' WHERE mwrt.labor_regulations_id = :labor_regulations_id ';
                $codeIDArray = array(':labor_regulations_id' => $postArray['labor_regulations_id'],);
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            if( !empty( $postArray['application_date_id'] ) )
            {
                $sql .= ' AND mwrt.application_date_id = :application_date_id ';
                $codeIDArray = array(':application_date_id' => $postArray['application_date_id'],);
                $searchArray = array_merge($searchArray, $codeIDArray);
            }
            
            $sql .= "ORDER BY mo.overtime_id ";
            
            $result = $DBA->executeSQL( $sql, $searchArray);
            
            $shiftList = array();
            if( $result === false )
            {
                $Log->trace("END getShiftList");
                return $shiftList;
            }
            // 取得したデータを配列に格納
            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push($shiftList,$data);
            }

            $Log->trace("END getShiftList");
            return $shiftList;
        }

        /**
         * シフトテーブル新規データ登録
         * @param    $postArray   入力パラメータ(従業員ID/組織ID/セクションID/色/日付/休日/出勤時間/退勤時間/休憩時間/ユーザID/更新組織)
         * @return   SQLの実行結果
         */
        private function addNewData( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            $sql = 'INSERT INTO t_shift(   user_id'
                . '                      , organization_id'
                . '                      , section_id'
                . '                      , color'
                . '                      , day'
                . '                      , is_holiday'
                . '                      , attendance'
                . '                      , taikin'
                . '                      , break_time'
                . '                      , total_working_time'
                . '                      , registration_time'
                . '                      , registration_user_id'
                . '                      , registration_organization'
                . '                      , update_time'
                . '                      , update_user_id'
                . '                      , update_organization'
                . '                      ) VALUES ('
                . '                      :user_id'
                . '                      , :organization_id'
                . '                      , :section_id'
                . '                      , :color'
                . '                      , :day'
                . '                      , :is_holiday'
                . '                      , :attendance'
                . '                      , :taikin'
                . '                      , :break_time'
                . '                      , :working_time'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization'
                . '                      , current_timestamp'
                . '                      , :registration_user_id'
                . '                      , :registration_organization)';

            $parameters = array(
                ':user_id'                   => $postArray['userID'],
                ':organization_id'           => $postArray['organizationID'],
                ':section_id'                => $postArray['sectionID'],
                ':color'                     => $postArray['color'],
                ':day'                       => $postArray['day'],
                ':is_holiday'                => $postArray['isHoliday'],
                ':attendance'                => $postArray['attendance'],
                ':taikin'                    => $postArray['taikin'],
                ':break_time'                => $postArray['breakTime'],
                ':working_time'              => $postArray['workingTime'],
                ':registration_user_id'      => $postArray['user_id'],
                ':registration_organization' => $postArray['organization_id'],
            );

            // 新規登録SQLの実行
            $ret = $this->executeOneTableSQL( $sql, $parameters, "t_shift" );
            
            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3120");
                $errMsg = "ユーザID：" . $postArray['userID'] . "日付：" . $postArray['day'] . "更新ユーザID：" . $postarray['user_id'] . "の失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3120";
            }
            
            $Log->trace("END addNewData");

            return "MSG_BASE_0000";
        }

        /**
         * シフトテーブル更新
         * @param    $postArray   入力パラメータ
         * @param    $shiftID     シフトID
         * @return   SQLの実行結果
         */
        private function modData( $postArray, $shiftID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modData");

            $sql = ' UPDATE t_shift SET '
                 . '            attendance  = :attendance '
                 . '          , taikin      = :taikin '
                 . '          , break_time  = :break_time '
                 . '          , total_working_time  = :total_working_time '
                 . '          , update_time = current_timestamp '
                 . '          , update_user_id = :update_user_id '
                 . '          , update_organization = :update_organization '
                 . ' WHERE shift_id = :shift_id ';

            $parameters = array(
                ':shift_id'                  => $shiftID,
                ':attendance'                => $postArray['attendance'],
                ':taikin'                    => $postArray['taikin'],
                ':break_time'                => $postArray['breakTime'],
                ':total_working_time'        => $postArray['workingTime'],
                ':update_user_id'            => $postArray['user_id'],
                ':update_organization'       => $postArray['organization_id'],
            );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3119");
                $errMsg = "シフトID：" . $shiftID . "ユーザID：" . $postarray['user_id'] . "の失敗";
                $Log->warnDetail($errMsg);
                return "MSG_ERR_3119";
            }
            
            $Log->trace("END modData");

            return "MSG_BASE_0000";
        }

        /**
         * シフトテーブルIDを取得する
         * @param    $postArray   入力パラメータ
         * @return   SQLの実行結果
         */
        private function getShift( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getShift");
           
            $sql = ' SELECT shift_id FROM t_shift '
                 . ' WHERE user_id = :user_id AND organization_id = :organization_id AND section_id = :section_id '
                 . '   AND day = :day AND is_holiday = :is_holiday AND attendance = :attendance ';

            $parameters = array(
                ':user_id'          => $postArray['userID'],
                ':organization_id'  => $postArray['organizationID'],
                ':section_id'       => $postArray['sectionID'],
                ':day'              => $postArray['day'],
                ':is_holiday'       => $postArray['isHoliday'],
                ':attendance'       => $postArray['attendance'],
            );

            $result  = $DBA->executeSQL( $sql, $parameters );
            $shiftID = 0;
            if( $result === false )
            {
                $Log->trace("END getShift");
                return $shiftID;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $shiftID = $data['shift_id'];
            }

            $Log->trace("END getShift");

            return $shiftID;
        }
    }
?>
