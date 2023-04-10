<?php
    /**
     * @file      給与データ出力コントローラ
     * @author    USE R.dendo
     * @date      2016/07/13
     * @version   1.00
     * @note      給与データ出力の内容表示/修正を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 給与データ出力処理モデル
    require './Model/PayrollDataOut.php';

    /**
     * 給与データ出力コントローラクラス
     * @note    給与データ出力の内容表示/修正を行う
     */
    class PayrollDataOutController extends BaseController
    {
        protected $isItemName    = 0;   ///< ヘッダ項目の表示有無 1：表示   2：非表示
        protected $displayFormat = 0;   ///< 時間の表示形式 1：10進数   2：時刻
        protected $noDataFormat  = 0;   ///< 時間データなし 1：数値     2：空白
        protected $countingUnit  = 0;   ///< 集計単位  1：日単位   2：期間(締日)  3：両方
        protected $outputCsv     = 0;   ///< 出力条件  1：勤怠締めのみ   2：勤怠締めていないものも含む
        
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
         * 給与データ出力一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1190" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }


        /**
         * 一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START searchAction");
            $Log->info( "MSG_INFO_1191" );

            $this->initialList();
            
            $Log->trace("END   searchAction");
        }

        /**
         * CSV出力
         * @return    なし
         */
        public function outputAction()
        {
            global $Log, $isItemName, $displayFormat, $noDataFormat, $countingUnit, $outputCsv; // グローバル変数宣言

            $Log->trace("START outputAction");
            $Log->info( "MSG_INFO_1192" );

            $searchArray = array(
                'closingDate'         => parent::escStr( $_POST['closingDate']),
                'organizationID'      => parent::escStr( $_POST['organizationName'] ),
                'isBeneath'           => parent::escStr( $_POST['isBeneath'] ) === 'on' ? 1 : 0,
                'file'                => parent::escStr( $_POST['file'] ),
                'output'              => parent::escStr( $_POST['output'] ),
                'sort'                => 0,
            );

            // 出力条件をグローバル変数に格納
            $outputCsv      = $searchArray['output'];
            $payrollDataOut = new PayrollDataOut();

            // 給与データ出力一覧データ取得
            $payOutList = $payrollDataOut->getListData($searchArray);

            // 対象従業員リスト
            $userInfoList = $payrollDataOut->getTargetUserInfoList( $searchArray );

            // 給与システム連携マスタIDリスト作成
            $payrollSysList = $payrollDataOut->getPayrollSystemIDList( $userInfoList );

            $zipFiles = array();

            foreach( $payrollSysList as $key => $val )
            {
                // 給与システム出力項目一覧
                $payrollCooperation = $payrollDataOut->getPayrollCooperationInfo( $val['payroll_system_id'] );

                // 給与システムの表示設定をグローバル変数に設定
                $isItemName     = $payrollCooperation['is_item_name'];
                $displayFormat  = $payrollCooperation['display_format'];
                $noDataFormat   = $payrollCooperation['no_data_format'];
                $countingUnit   = $payrollCooperation['counting_unit'];

                if( $countingUnit == 2 )
                {
                    // 締め済勤怠リスト
                    $attendanceList = $payrollDataOut->getAttendanceTightenInfo( $searchArray );
                    // 締め済手当リスト
                    $allowanceList = $payrollDataOut->getLoopInfoList( $searchArray, 'allowance_id' );
                    // 締め済休日リスト
                    $holidayList = $payrollDataOut->getLoopInfoList( $searchArray, 'holiday_id' );
                    // 締め済休日名称リスト
                    $holidayNameList = $payrollDataOut->getLoopInfoList( $searchArray, 'holiday_name_id' );
                    // 給与システムでの表示対象外項目リスト(期間用)
                    $hideList = array( 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 80, 87 );
                }
                else
                {
                    // 締め済勤怠リスト
                    $attendanceList = $payrollDataOut->creatAttendanceDaysInfoList( $searchArray, $countingUnit );
                    // 締め済手当リスト
                    $allowanceList = $payrollDataOut->creatLoopDaysInfoList( $searchArray, $countingUnit, 'allowance_id' );
                    // 締め済休日リスト
                    $holidayList = $payrollDataOut->creatLoopDaysInfoList( $searchArray, $countingUnit, 'is_holiday' );
                    // 締め済休日名称リスト
                    $holidayNameList = $payrollDataOut->creatLoopDaysInfoList( $searchArray, $countingUnit, 'holiday_name_id' );
                    // 給与システムでの表示対象外項目リスト(日用&期間両方)　※仮で、期間用設定を記載
                    $hideList = array( 75 );
                }

                // 給与システムの表示項目設定
                $payrollSystemList = $payrollDataOut->getPayrollSystemList( $hideList, $val['payroll_system_id'] );

                // ファイル名なし
                if( empty( $csvFileName ) )
                {
                    $csvFileName = SystemParameters::$PAYROLL_SYSTEM_PATH . time() . rand() . '.csv';
                }

                // CSV出力
                $csvFileName = $this->createCSV( $payrollSystemList, $userInfoList, $attendanceList, $allowanceList, $holidayList, $holidayNameList, $csvFileName, $val['payroll_system_id'], $payOutList );
                // ファイル分割有(給与フォーマットごと)
                if( $searchArray['file'] == 1 )
                {
                    $Log->debug($csvFileName . ' KEY：' . $key );
                    // ファイル名のリネーム
                    $csvFileNewName = SystemParameters::$PAYROLL_SYSTEM_PATH . str_replace("/", "", $searchArray['closingDate'] ) . '_' . $val['payroll_system_name'] . '_' . $_SESSION["COMPANY_ID"] . '.csv';
                    rename( $csvFileName, $csvFileNewName );
                    // 給与フォーマット名に修正
                    array_push( $zipFiles, $csvFileNewName );
                    $Log->debug($csvFileName . ' KEY：' . $key );
                }
            }

            // ファイル分割されているか
            if( $searchArray['file'] == 1 )
            {
                // ファイルの圧縮処理
                $zip = new ZipArchive();
                // ZIPファイルをオープン
                $zipName = SystemParameters::$PAYROLL_SYSTEM_PATH . str_replace("/", "", $searchArray['closingDate'] ) . '_'  . time() . rand() . '_' . $_SESSION["COMPANY_ID"] . '.zip';
                $res = $zip->open( $zipName, ZipArchive::CREATE);
                 
                // zipファイルのオープンに成功した場合
                if ($res === true)
                {
                    // 圧縮するファイルを指定する
                    foreach( $zipFiles as $file )
                    {
                        // ファイル名のみ取得
                        $setFileName = str_replace( SystemParameters::$PAYROLL_SYSTEM_PATH, "", $file );
                        // ファイル名をエンコード
                        mb_convert_variables('sjis-win', 'UTF-8', $setFileName);
                        $zip->addFile($file, $setFileName);
                    }

                    // ZIPファイルをクローズ
                    $zip->close();
                }
                $csvFileName = $zipName;
                // ここで渡されるファイルがダウンロード時のファイル名になる
                $dlFileName = "PayrollDataOut.zip";
            }
            else
            {
                // 1ファイルのみ
                // ここで渡されるファイルがダウンロード時のファイル名になる
                $dlFileName = "PayrollDataOut.csv";
            }

            // ダウンロード開始
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $dlFileName); 
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($csvFileName));
            readfile($csvFileName);

            $Log->trace("END   outputAction");
        }

        /**
         * 給与データ出力一覧画面
         * @note     給与データ出力画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            $payrollDataOut = new PayrollDataOut();

            // 前月を設定
            $searchDate = date( "Y/m", strtotime( "first day of - 1 month" ) );
            $searchArray = array(
                'closingDate'          => $searchDate,
                'organizationID'       => $_SESSION["ORGANIZATION_ID"],
                'isBeneath'            => 1,
                'sort'                 => '',
            );

            // 検索プルダウン
            $abbreviatedNameList = $payrollDataOut->setPulldown->getSearchOrganizationList( 'reference', true );          // 組織略称名リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;
            
            // 給与データ出力一覧データ取得
            $payOutAllList = $payrollDataOut->getListData($searchArray);

            // 給与データ出力一覧レコード数
            $payOutRecordCnt = count($payOutAllList);

            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];

            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($payOutRecordCnt, $pagedRecordCnt);

            // シーケンスIDName
            $idName = "organization_id";
            // 配列のKEYが、組織IDとなっている為、連番に振りなおす
            $payOutAllList = array_merge($payOutAllList);
            $payOutList = $this->refineListDisplayNoSpecifiedPage($payOutAllList, $idName, $pagedRecordCnt, $pageNo);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($payOutList) >= 11 )
            {
                $isScrollBar = true;
            }

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $payOutSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $display_no = $this->getDisplayNo( $payOutRecordCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );
            
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            $pagedCnt = ceil($payOutRecordCnt /  $pagedRecordCnt);
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            $Log->trace("END   initialDisplay");
            require_once './View/PayrollDataOutPanel.html';
        }
         /**
         * 給与データ出力一覧更新
         * @note     給与データ出力画面の一覧部分のみの更新
         * @param    $messege        
         * @return   無
         */
        private function initialList()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");
            
            $searchArray = array(
                'closingDate'         => parent::escStr( $_POST['searchClosingDate']),
                'organizationID'      => parent::escStr( $_POST['searchOrganizatioID'] ),
                'isBeneath'           => parent::escStr( $_POST['searchBeneath'] ) === 'true' ? 1 : 0,
                'sort'                => parent::escStr( $_POST['sortConditions'] ),
            );

            $payrollDataOut = new PayrollDataOut();

            // 給与データ出力一覧データ取得
            $payOutAllList = $payrollDataOut->getListData($searchArray);

            // 給与データ出力一覧レコード数
            $payOutRecordCnt = count($payOutAllList);

            // 表示レコード数
            $pagedRecordCnt = parent::escStr( $_POST['displayRecordCnt'] );

            $pagedCnt = ceil($payOutRecordCnt /  $pagedRecordCnt);
            
            // 指定ページ
            $pageNo = parent::escStr( $_POST['displayPageNo'] );

            // シーケンスIDName
            $idName = "organization_id";
            // 配列のKEYが、組織IDとなっている為、連番に振りなおす
            $payOutAllList = array_merge($payOutAllList);
            $payOutList = $this->refineListDisplayNoSpecifiedPage($payOutAllList, $idName, $pagedRecordCnt, $pageNo);

            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($payOutList) >= 11 )
            {
                $isScrollBar = true;
            }

            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $payOutSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;

            $display_no = $this->getDisplayNo( $payOutRecordCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            $pagedCnt = ceil($payOutRecordCnt /  $pagedRecordCnt);
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);

            require_once './View/PayrollDataOutTablePanel.html';
            
            $Log->trace("END   initialList");
        }

        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     パラメータは、View側で使用
         * @param    ソート条件番号
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        private function changeHeaderItemMark($sortNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'payrollDataOutNoSortMark'                   => '',
                    'payrollDataOutClosingDateSortMark'          => '',
                    'payrollDataOutOrganizationSortMark'         => '',
                    'payrollDataOutClosingConditionsSortMark'    => '',
                    'payrollDataOutLClosingConditionsSortMark'   => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "payrollDataOutNoSortMark";
                $sortList[2] = "payrollDataOutNoSortMark";
                $sortList[3] = "payrollDataOutClosingDateSortMark";
                $sortList[4] = "payrollDataOutClosingDateSortMark";
                $sortList[5] = "payrollDataOutOrganizationSortMark";
                $sortList[6] = "payrollDataOutOrganizationSortMark";
                $sortList[7] = "payrollDataOutClosingConditionsSortMark";
                $sortList[8] = "payrollDataOutClosingConditionsSortMark";
                $sortList[9] = "payrollDataOutLClosingConditionsSortMark";
                $sortList[10] = "payrollDataOutLClosingConditionsSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }
            
            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }

        /**
         * CSV作成
         * @note     CSVファイルの作成
         * @param    $payrollSystemList   給与システムの表示項目リスト
         * @param    $userInfoList        対象従業員リスト
         * @param    $attendanceList      締め済勤怠リスト
         * @param    $allowanceList       締め済手当リスト
         * @param    $holidayList         締め済休日リスト
         * @param    $holidayNameList     締め済休日名称リスト
         * @param    $csvFileName         CSVファイル名
         * @param    $payrollSystemID     給与システム連携マスタID
         * @param    $payOutList          各組織ごとの勤怠締め状況
         * @return   CSVファイル名
         */
        private function createCSV( $payrollSystemList, $userInfoList, $attendanceList, $allowanceList, $holidayList, $holidayNameList, $csvFileName, $payrollSystemID, $payOutList )
        {
            global $Log, $isItemName, $outputCsv;  // グローバル変数宣言
            $Log->trace("START createCSV");
            try
            {
                // ファイル名をエンコード
                mb_convert_variables('sjis-win', 'UTF-8', $csvFileName);
                
                $res = fopen($csvFileName, 'a+');
                if ($res === FALSE) {
                    throw new Exception('ファイルの書き込みに失敗しました。');
                }

                // ヘッダ行の出力対応
                if( $isItemName == 1 )
                {
                    $header = array();
                    foreach( $payrollSystemList as $payroll )
                    {
                        if( $payroll['output_type_id'] <= 1)
                        {
                            array_push( $header, $payroll['item_name'] );
                        }
                        else
                        {
                            if( $payroll['output_item_branch'] > 0 )
                            {
                                array_push( $header, $payroll['item_name'] );
                            }
                        }
                    }
                    
                    // 文字コード変換。エクセルで開けるようにする
//                   mb_convert_variables('SJIS', 'UTF-8', $header);
                    mb_convert_variables('sjis-win', 'UTF-8', $header);

                    // ファイルに書き出しをする
                    fputcsv($res, $header);
                }

                // 勤怠データ出力対応
                foreach( $userInfoList as $user )
                {
                    // 同一の給与フォーマットを出力する
                    if( $user['payroll_system_id'] == $payrollSystemID )
                    {
                        
                        // 勤怠の締め済組織であるか判定
                        if( $payOutList[$user['organization_id']]['approval'] == 0 && $outputCsv == 1 )
                        {
                            // 勤怠締めのみ出力の場合、勤怠〆ていない組織は出力対象外とする
                            continue;
                        }
                        
                        list( $lastName, $firstName ) = explode("　", $user['user_name']);
                        
                        foreach( $attendanceList[$user['user_id']] as $key => $attendance )
                        {
                            $line = array();
                            foreach( $payrollSystemList as $payroll )
                            {
                                if( $payroll['output_type_id'] == 1 )
                                {
                                    if( $payroll['output_item_id'] == 1 )
                                    {
                                        // 姓 
                                        array_push( $line,$lastName );
                                    } 
                                    elseif( $payroll['output_item_id'] == 2 )
                                    { 
                                        // 名 
                                        array_push( $line,$firstName );
                                    } 
                                    elseif( $payroll['output_item_id'] == 3 )
                                    {
                                        // 姓 名
                                        array_push( $line,$user['user_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 4 )
                                    {
                                        // 従業員No 
                                        array_push( $line,$user['employees_no'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 5 )
                                    {
                                        // 雇用形態名 
                                        array_push( $line,$user['employment_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 6 )
                                    {
                                        // 部門コード 
                                        array_push( $line,$user['department_code'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 7 )
                                    {
                                        // 組織名 
                                        array_push( $line,$user['organization_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 8 )
                                    {
                                        // 組織略称 
                                        array_push( $line,$user['abbreviated_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 9 )
                                    {
                                        // 役職名 
                                        array_push( $line,$user['position_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 10 )
                                    {
                                        // セクション名 
                                        array_push( $line,$user['section_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 11 )
                                    {
                                        // 打刻場所部門コード 
                                        array_push( $line,$attendance['embossing_department_code'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 12 )
                                    {
                                        // 打刻場所組織名 
                                        array_push( $line,$attendance['embossing_organization_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 13 )
                                    {
                                        // 打刻場所組織略称 
                                        array_push( $line,$attendance['embossing_abbreviated_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 14 )
                                    {
                                        // 打刻場所状況 
                                        array_push( $line,$attendance['embossing_status'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 15 )
                                    {
                                        // シフト出勤時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['shift_attendance_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 16 )
                                    {
                                        // シフト退勤時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['shift_taikin_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 17 )
                                    {
                                        // シフト休憩時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['shift_break_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 18 )
                                    {
                                        // 出勤時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['attendance_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 19 )
                                    {
                                        // 退勤時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['clock_out_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 20 )
                                    {
                                        // 出勤実時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['embossing_attendance_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 21 )
                                    {
                                        // 退勤実時刻 
                                        array_push( $line,$this->changeViewTimelimitedDays( $attendance['embossing_clock_out_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 22 )
                                    {
                                        // 欠勤回数 
                                        array_push( $line, $attendance['absence_count'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 23 )
                                    {
                                        // 遅刻回数 
                                        array_push( $line, $attendance['being_late_count'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 24 )
                                    {
                                        // 早退回数 
                                        array_push( $line, $attendance['leave_early_count'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 25 )
                                    {
                                        // 遅刻早退回数 
                                        $late_leave_count = $attendance['being_late_count'] + $attendance['leave_early_count'];
                                        array_push( $line,$late_leave_count );
                                    }
                                    elseif( $payroll['output_item_id'] == 26 )
                                    {
                                        // 所定労働日数 
                                        array_push( $line,$attendance['prescribed_working_days'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 27 )
                                    { 
                                        // 実働日数 
                                        array_push( $line,$attendance['production_dates'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 28 )
                                    {
                                        // 平日出勤日数 
                                        array_push( $line,$attendance['weekday_attendance_dates'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 29 )
                                    {
                                        // 休日出勤日数 
                                        array_push( $line,$attendance['holiday_work_days'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 30 )
                                    {
                                        // 公休日出勤日数 
                                        array_push( $line,$attendance['public_holiday_attendance_dates'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 31 )
                                    {
                                        // 法定休日出勤日数 
                                        array_push( $line,$attendance['legal_holiday_work_days'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 32 )
                                    {
                                        // 実労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['working_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 33 )
                                    {
                                        // 所定内労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['prescribed_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 34 )
                                    {
                                        // 平日労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['weekday_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 35 )
                                    {
                                        // 平日所定内労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['weekday_prescribed_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 36 )
                                    {
                                        // 休日労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 37 )
                                    {
                                        // 休日所定内労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_prescribed_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 38 )
                                    {
                                        // 公休日労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['public_holiday_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 39 )
                                    {
                                        // 公休日所定内労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['public_holiday_prescribed_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 40 )
                                    {
                                        // 法定休日労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_holiday_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 41 )
                                    {
                                        // 法定休日所定内労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_holiday_prescribed_working_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 42 )
                                    {
                                        // 実残業時間 
                                        $actualOvertime = $attendance['weekday_overtime'] + $attendance['holiday_overtime_hours'];
                                        array_push( $line,$this->changeViewTime( $actualOvertime ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 43 )
                                    { 
                                        // 平日残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['weekday_overtime'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 44 )
                                    {
                                        // 休日残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 45 )
                                    {
                                        // 公休日残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['public_holiday_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 46 )
                                    {
                                        // 法定休日残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_holiday_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 47 )
                                    {
                                        // 法定内残業時間（公休日含む） 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 48 )
                                    {
                                        // 法定外残業時間（公休日含む） 
                                        $overHoursAll = $attendance['nonstatutory_overtime_hours'] + $attendance['nonstatutory_overtime_hours_45h'] + $attendance['nonstatutory_overtime_hours_60h'];
                                        array_push( $line,$this->changeViewTime( $overHoursAll ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 49 )
                                    {
                                        // 法定外残業時間（公休日含む）（45H以下） 
                                        array_push( $line,$this->changeViewTime( $attendance['nonstatutory_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 50 )
                                    {
                                        // 法定外残業時間（公休日含む）（45H超え60H以下） 
                                        array_push( $line,$this->changeViewTime( $attendance['nonstatutory_overtime_hours_45h'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 51 )
                                    {
                                        // 法定外残業時間（公休日含む）（60H以下）
                                        $overHoursUnder60 = $attendance['nonstatutory_overtime_hours'] + $attendance['nonstatutory_overtime_hours_45h'];
                                        array_push( $line,$this->changeViewTime( $overHoursUnder60 ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 52 )
                                    {
                                        // 法定外残業時間（60H超え） 
                                        array_push( $line,$this->changeViewTime( $attendance['nonstatutory_overtime_hours_60h'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 53 )
                                    {
                                        // 法定外残業時間（公休日含まない） 
                                        $overHoursNoPubAll = $attendance['nonstatutory_overtime_hours_no_pub'] + $attendance['nonstatutory_overtime_hours_45h_no_pub'] + $attendance['nonstatutory_overtime_hours_60h_no_pub'];
                                        array_push( $line,$this->changeViewTime( $overHoursNoPubAll ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 54 )
                                    {
                                        // 法定外残業時間（公休日含まない）（45H以下） 
                                        array_push( $line,$this->changeViewTime( $attendance['nonstatutory_overtime_hours_no_pub'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 55 )
                                    {
                                        // 法定外残業時間（公休日含まない）（45H超え60H以下） 
                                        array_push( $line,$this->changeViewTime( $attendance['nonstatutory_overtime_hours_45h_no_pub'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 56 )
                                    {
                                        // 法定外残業時間（公休日含まない）（60H以下） 
                                        $overHoursNoPubU60 = $attendance['nonstatutory_overtime_hours_no_pub'] + $attendance['nonstatutory_overtime_hours_45h_no_pub'];
                                        array_push( $line,$this->changeViewTime( $overHoursNoPubU60 ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 57 )
                                    {
                                        // 法定外残業時間（公休日含まない）（60H超え）
                                        array_push( $line,$this->changeViewTime( $attendance['nonstatutory_overtime_hours_60h_no_pub'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 58 )
                                    {
                                        // 実深夜時間 
                                        array_push( $line,$this->changeViewTime( $attendance['night_working_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 59 )
                                    {
                                        // 平日深夜時間 
                                        array_push( $line,$this->changeViewTime( $attendance['weekdays_midnight_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 60 )
                                    {
                                        // 休日深夜時間 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_late_at_night_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 61 )
                                    {
                                        // 公休日深夜時間 
                                        array_push( $line,$this->changeViewTime( $attendance['public_holidays_late_at_night_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 62 )
                                    {
                                        // 法定休日深夜時間 
                                        array_push( $line,$this->changeViewTime( $attendance['legal_holiday_late_at_night_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 63 )
                                    {
                                        // 普通労働時間 
                                        array_push( $line,$this->changeViewTime( $attendance['normal_working_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 64 )
                                    {
                                        // 普通残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['normal_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 65 )
                                    {
                                        // 普通深夜時間 
                                        array_push( $line,$this->changeViewTime( $attendance['normal_night_working_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 66 )
                                    {
                                        // 深夜残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['normal_night_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 67 )
                                    {
                                        // 平日深夜残業時間
                                        array_push( $line,$this->changeViewTime( $attendance['weekday_night_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 68 )
                                    {
                                        // 休日深夜残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_night_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 69 )
                                    {
                                        // 公休日深夜残業時間 public_night_overtime_hours
                                        array_push( $line,$this->changeViewTime( $attendance['public_night_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 70 )
                                    {
                                        // 法定休日深夜残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_holiday_night_overtime_hours'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 71 )
                                    {
                                        // 休憩時間 
                                        array_push( $line,$this->changeViewTime( $attendance['break_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 72 )
                                    {
                                        // 遅刻時間 
                                        array_push( $line,$this->changeViewTime( $attendance['late_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 73 )
                                    {
                                        // 早退時間 
                                        array_push( $line,$this->changeViewTime( $attendance['leave_early_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 74 )
                                    {
                                        // 遅刻早退時間 
                                        $late_leave_time = $attendance['late_time'] + $attendance['leave_early_time'];
                                        array_push( $line,$this->changeViewTime( $late_leave_time ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 75 )
                                    {
                                        // みなし残業時間 
                                        array_push( $line,$this->changeViewTime( $attendance['considered_overtime'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 76 )
                                    {
                                        // 残業時間（みなし除く）※公休日含む 
                                        array_push( $line,$this->changeViewTime( $attendance['overtime_hours_no_considered'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 77 )
                                    {
                                        // 残業時間（みなし除く）※公休日含まない
                                        array_push( $line,$this->changeViewTime( $attendance['overtime_hours_no_considered_no_pub'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 78 )
                                    {
                                        // 概算給与（実績） 
                                        array_push( $line,$attendance['estimate_performance'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 79 )
                                    {
                                        // 概算給与（シフト） 
                                        array_push( $line,$attendance['approximate_schedule'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 80 )
                                    {
                                        // 概算給与（未来予定 + 過去実績） 
                                        array_push( $line,$attendance['rough_estimate_month'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 81 )
                                    {
                                        // 時給 
                                        array_push( $line,$user['hourly_wage'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 82 )
                                    {
                                        // 電話番号 
                                        array_push( $line,$user['tel'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 83 )
                                    {
                                        // 携帯電話 
                                        array_push( $line,$user['cellphone'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 84 )
                                    {
                                        // メールアドレス 
                                        array_push( $line,$user['mail_address'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 85 )
                                    {
                                        // 従業員コメント 
                                        array_push( $line,$user['user_comment'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 86 )
                                    {
                                        // 所属組織コメント 
                                        array_push( $line,$user['organization_comment'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 87 )
                                    {
                                        // 勤怠コメント 
                                        array_push( $line,$attendance['attendance_comment'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 88 )
                                    {
                                        // 打刻修正数 
                                        array_push( $line,$attendance['modify_count'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 89 )
                                    {
                                        // 賃金形態 
                                        array_push( $line,$user['wage_form_name'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 90 )
                                    {
                                        // 基本給 
                                        array_push( $line,$user['base_salary'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 91 )
                                    {
                                        // 法定内残業時間（公休日は含まない） 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_overtime_hours_no_pub'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 92 )
                                    {
                                        // 勤務状況 
                                        array_push( $line, $attendance['work_classification'] );
                                    }
                                    elseif( $payroll['output_item_id'] == 93 )
                                    {
                                        // 平日普通 
                                        array_push( $line,$this->changeViewTime( $attendance['weekday_normal_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 94 )
                                    {
                                        // 平日深夜 
                                        array_push( $line,$this->changeViewTime( $attendance['weekday_midnight_time_only'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 95 )
                                    {
                                        // 休日普通 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_normal_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 96 )
                                    {
                                        // 休日深夜 
                                        array_push( $line,$this->changeViewTime( $attendance['holiday_midnight_time_only'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 97 )
                                    {
                                        // 公休日普通 
                                        array_push( $line,$this->changeViewTime( $attendance['public_holiday_normal_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 98 )
                                    {
                                        // 公休日深夜 
                                        array_push( $line,$this->changeViewTime( $attendance['public_holiday_midnight_time_only'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 99 )
                                    {
                                        // 法定休日普通） 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_holiday_normal_time'] ) );
                                    }
                                    elseif( $payroll['output_item_id'] == 100 )
                                    {
                                        // 法定休日深夜 
                                        array_push( $line,$this->changeViewTime( $attendance['statutory_holiday_midnight_time_only'] ) );
                                    }
                                } 
                                else if( $payroll['output_type_id'] == 2 )
                                {
                                    // 表示項目出力種別ID（手当取得情報） 
                                    if( $payroll['output_item_branch'] > 0 )
                                    {
                                        $isInsert = true;
                                        // 手当取得リスト展開
                                        foreach( $allowanceList[$user['user_id']][$key] as $allowance )
                                        {
                                            // 出力項目IDと手当IDを比較
                                            if( $payroll['output_item_id'] == $allowance['allowance_id'] )
                                            {
                                                // 出力項目IDと手当IDを比較 
                                                if( $payroll['output_item_branch'] == 1 )
                                                {
                                                    // 出力項目枝番が1のものは取得回数 
                                                    array_push( $line, $this->setDisplay( $allowance['allowance_num'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 2 )
                                                {
                                                    // 出力項目枝番が2のものは手当金額 
                                                    array_push( $line, $this->setDisplay( $allowance['allowance_amount'] ) );
                                                    $isInsert = false;
                                                }
                                            }
                                        }
                                        if($isInsert)
	                                    {
                                            if( $payroll['output_item_branch'] == 1 || $payroll['output_item_branch'] == 2 )
                                            {
                                                array_push( $line, 0 );
                                            }
	                                    }
                                        
                                    }
                                }
                                else if($payroll['output_type_id'] == 3)
                                {
                                    // 表示項目出力種別ID（休日取得情報） 
                                    if( $payroll['output_item_branch'] > 0 )
                                    {
                                        $isInsert = true;
                                        // 表示項目出力枝番が0以上のもののみ表示対象とする 
                                        foreach( $holidayList[$user['user_id']][$key] as $holiday )
                                        {
                                            // 勤怠IDでの関連付け 
                                            if( $payroll['output_item_id'] == $holiday['holiday_id'] )
                                            { 
                                                // 出力項目IDと休日IDを比較 
                                                if( $payroll['output_item_branch'] == 1 )
                                                { 
                                                    // 出力項目枝番が1のものは取得日数 
                                                    array_push( $line, $this->setDisplay( $holiday['holiday_get_dates'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 2 )
                                                {
                                                    // 出力項目枝番が2のものは出勤日数 
                                                    array_push( $line, $this->setDisplay( $holiday['holiday_work_days'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 3 )
                                                {
                                                    // 出力項目枝番が3のものは労働時間 
                                                    array_push( $line, $this->changeViewTime( $holiday['holiday_working_hours'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 4 )
                                                {
                                                    // 出力項目枝番が4のものは休日所定内労働時間 
                                                    array_push( $line, $this->changeViewTime( $holiday['holiday_prescribed_working_hours'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 5 )
                                                {
                                                    // 出力項目枝番が5のものは残業時間 
                                                    array_push( $line, $this->changeViewTime( $holiday['holiday_overtime_hours'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 6 )
                                                {
                                                    // 出力項目枝番が6のものは深夜時間 
                                                    array_push( $line, $this->changeViewTime( $holiday['holiday_late_night_time'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 7 )
                                                {
                                                    // 出力項目枝番が7のものは深夜残業時間 
                                                    array_push( $line, $this->changeViewTime( $holiday['holiday_midnight_overtime_hours'] ) );
                                                    $isInsert = false;
                                                }
                                            }
                                        }
                                        if($isInsert)
                                        {
                                            if( $payroll['output_item_branch'] == 1 || $payroll['output_item_branch'] == 2 )
                                            {
                                                array_push( $line, 0 );
                                            }
                                            else
                                            {
                                                array_push( $line, $this->changeViewTime( '00:00' ) );
                                            }
                                        }
                                    }
                                }
                                else if( $payroll['output_type_id'] == 4 )
                                {
                                    $isInsert = true;
                                    // 表示項目出力種別ID（休日名称取得情報） 
                                    if( $payroll['output_item_branch'] > 0 )
                                    { 
                                        // 表示項目出力枝番が0以上のもののみ表示対象とする 
                                        foreach( $holidayNameList[$user['user_id']][$key] as $holidayName )
                                        {
                                            // 勤怠IDでの関連付け 
                                            if( $payroll['output_item_id'] == $holidayName['holiday_name_id'] )
                                            {
                                                // 出力項目IDと休日名称IDを比較 
                                                if( $payroll['output_item_branch'] == 1 )
                                                {
                                                    // 出力項目枝番が1のものは取得日数 
                                                    array_push( $line, $this->setDisplay( $holidayName['holiday_get_dates'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 2 )
                                                {
                                                    // 出力項目枝番が2のものは出勤日数 
                                                    array_push( $line, $this->setDisplay( $holidayName['holiday_work_days'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 3 )
                                                {
                                                    // 出力項目枝番が3のものは労働時間 
                                                    array_push( $line, $this->changeViewTime( $holidayName['holiday_working_hours'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 4 )
                                                {
                                                    // 出力項目枝番が4のものは休日所定内労働時間 
                                                    array_push( $line, $this->changeViewTime( $holidayName['holiday_prescribed_working_hours'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 5 )
                                                {
                                                    // 出力項目枝番が5のものは残業時間 
                                                    array_push( $line, $this->changeViewTime( $holidayName['holiday_overtime_hours'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 6 )
                                                {
                                                    // 出力項目枝番が6のものは深夜時間 
                                                    array_push( $line, $this->changeViewTime( $holidayName['holiday_late_night_time'] ) );
                                                    $isInsert = false;
                                                }
                                                else if( $payroll['output_item_branch'] == 7 )
                                                {
                                                    // 出力項目枝番が7のものは深夜残業時間
                                                    array_push( $line, $this->changeViewTime( $holidayName['holiday_midnight_overtime_hours'] ) );
                                                    $isInsert = false;
                                                }
                                            }
                                        }
                                        if($isInsert)
                                        {
                                            if( $payroll['output_item_branch'] == 1 || $payroll['output_item_branch'] == 2 )
                                            {
                                                array_push( $line, 0 );
                                            }
                                            else
                                            {
                                                array_push( $line, $this->changeViewTime( '00:00' ) );
                                            }
                                        }
                                    }
                                }
                            }

                            // 文字コード変換。エクセルで開けるようにする
                            mb_convert_variables('SJIS-win', 'UTF-8', $line);

                            // ファイルに書き出しをする
                            fputcsv($res, $line);
                        }
                    }
                }
                // ハンドル閉じる
                fclose($res);
            }
            catch(Exception $e)
            {
                // 例外処理をここに書きます
                echo $e->getMessage();
            }

            $Log->trace("END createCSV");
            return $csvFileName;
        }

        /**
         * 時間表示設定
         * @note     給与連携マスタの時間表示形式に合わせて、データを表示する
         * @param    $time          時間(分)
         * @return   時間の表示形式
         */
        private function changeViewTime( $time )
        {
            global $Log, $displayFormat, $noDataFormat;  // グローバル変数宣言
            $Log->trace("START changeViewTime");
            
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

            $Log->trace("END changeViewTime");

            return $ret;
        }

        /**
         * 時間表示設定(1日ずつの場合)
         * @note     給与連携マスタの時間表示形式に合わせて、データを表示する
         * @param    $time          時間(時刻形式[13:00])
         * @return   時間の表示形式
         */
        private function changeViewTimelimitedDays( $time )
        {
            global $Log, $displayFormat, $noDataFormat;  // グローバル変数宣言
            $Log->trace("START changeViewTimelimitedDays");
            
            // 時間が0 かつ 時間なしの場合が空白の場合
            if( empty( $time ) && $noDataFormat == 2 )
            {
                // ''を返す
                return '';
            }

            // 時間の表示形式が、10進数の場合
            if( !empty( $time ) && $displayFormat == 1 )
            {
                $list = explode( ":", $time );
                $minute = intval( $list[1] ) / 60;
                if( $minute == 0 )
                {
                    $time = $list[0] . ".00";
                }
                else
                {
                    $time = intval( $list[0] ) + $minute;
                }
            }
            else if( empty( $time ) && $displayFormat == 1 )
            {
                $time = '0.00';
            }

            $Log->trace("END changeViewTimelimitedDays");

            return $time;
        }

        /**
         * 手当/休日で、データがない場合、0を設定する
         * @note     表示データが空白の場合、0にする
         * @param    $display            表示データ
         * @return   表示データ
         */
        private function setDisplay( $display )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setDisplay");

            // 表示データなしの場合が0を設定
            if( empty( $display ) )
            {
                // 0を返す
                return 0;
            }

            $Log->trace("END  setDisplay");

            return $display;
        }

    }
?>
