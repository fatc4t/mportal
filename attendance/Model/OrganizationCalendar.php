<?php
    /**
     * @file      組織カレンダマスタ
     * @author    USE Y.Sugou
     * @date      2016/06/30
     * @version   1.00
     * @note      組織カレンダマスタテーブル・カレンダ詳細マスタテーブルの管理を行う
     */

    // BaseModel.phpを読み込む
    require './Model/Common/BaseModel.php';

    /**
     * 組織カレンダマスタクラス
     * @note   組織カレンダマスタテーブル・カレンダ詳細マスタテーブルの管理を行う
     */
    class OrganizationCalendar extends BaseModel
    {
        protected $allowanceList;  ///< 手当マスタ情報
    
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
            
            $addAllowanceList = $this->setPulldown->getSearchAdditionalAllowanceList( 'reference' );
            $this->allowanceList = array();
            foreach( $addAllowanceList as $val )
            {
                if( $val['allowance_id'] != "" )
                {
                    $this->allowanceList[$val['allowance_id']] = $val['allowance_name'];
                }
            }
            //$this->allowanceList = array_column($addAllowanceList,'allowance_name', 'allowance_id');
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
          * カレンダー用配列の生成を行う
          * @author 2016/07/01 Y.Sugou
          */
        public function getCalendarValues($toym, $organizationID)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getCalendarValues");
            //リターン用配列のインスタンスを作成
            $rtnAry = array();
            list($Yer, $Mth) = split('[/.-]', $toym);
            //各必要な変数の定義
            if ($Yer != "") 
            {
                $year = $Yer;
            }
            else
            {
                $year = date('Y'); //年
            }
            if ($Mth != "")
            {
                $month = $Mth;
            }
            else
            {
                $month = date('n'); //月
            }

            //組織カレンダーマスタから休日情報の取得
            $mCalendarList = $this->getCalendar( $organizationID );
            if($mCalendarList['is_sunday'] !== 0)
            {
                $isSunday = $mCalendarList['is_sunday'];
            }
            if($mCalendarList['is_public_holiday'] !== 0)
            {
                $isPublicHoliday = $mCalendarList['is_public_holiday'];
            }
            if($mCalendarList['is_saturday_1'] !== 0)
            {
                $isSaturday1 = $mCalendarList['is_saturday_1'];
            }
            if($mCalendarList['is_saturday_2'] !== 0)
            {
                $isSaturday2 = $mCalendarList['is_saturday_2'];
            }
            if($mCalendarList['is_saturday_3'] !== 0)
            {
                $isSaturday3 = $mCalendarList['is_saturday_3'];
            }
            if($mCalendarList['is_saturday_4'] !== 0)
            {
                $isSaturday4 = $mCalendarList['is_saturday_4'];
            }
            if($mCalendarList['is_saturday_5'] !== 0)
            {
                $isSaturday5 = $mCalendarList['is_saturday_5'];
            }

            //当月の1日をdate型で取得
            $ftoYm = $year."/".$month."/".date("1");
            $timestamp = strtotime($ftoYm);
            $stoYm = date("Y-m-d H:i:s", $timestamp);

            //来月の1日をdate型で取得
            $ltoYm = date("Y/m/d", strtotime("$ftoYm +1 month" ));
            $timestamp = strtotime($ltoYm);
            $etoYm = date("Y-m-d H:i:s", $timestamp);

            //カレンダー詳細マスタから休日情報の取得
            $mCalendarDList = $this->getCalendarDetail($mCalendarList['organization_calendar_id'], $stoYm, $etoYm );

            $date = 1; //日
            $wd1 = date("w", mktime(0,0,0,$month, 1, $year)); //先頭ブランクセル数
            $cnt = 0; //カウント
            $row = 1;
            //=== 1.先頭のブランクセル部作成 ==================================>
            array_push($rtnAry, "<tr>");
            for ($i = 0; $i < $wd1; $i++) {
                array_push($rtnAry, "<td class=\"calDetailHeaderCell\"></td>");
            }

            //=== 2.各対象月の日セル部作成 ===================================>
            $cnt = $wd1;
            $dayList = array();
            for($a = 0; $a < $wd1; $a++)
            {
                array_push( $dayList, '');
            }
            while(checkdate($month, $date, $year)) {
                array_push($rtnAry, "<td class=\"calDetailHeaderCell\">".$date."</td>");
                array_push( $dayList, $date);


                //カウントが5(土曜)まで回ったらコンテンツ側の作成を行う
                if ($cnt > 5)
                {
                    array_push($rtnAry, "</tr>");
                    array_push($rtnAry, "<tr>");
                    //日曜～土曜までのコンテンツ表示セルを作成
                    for ($i = 0; $i < 7; $i++)
                    {
                        $tmpId = $row . "-" . $i;
                        $days = $year . '-' . $month . '-' . str_pad($dayList[$i], 2, 0, STR_PAD_LEFT);

                        //休日情報をリセット
                        $holiday = "";
                        
                        //ラベルをリセット
                        $holidayText = "";
                        
                        //色をリセット
                        $color = "";
                        
                        // 祝日情報取得
                        $publicHolidayName = $this->getPublicHolidayName( $days );
                        if( $publicHolidayName != "" )
                        {
                            $holiday = $isPublicHoliday;
                            $color = $this->getHolidayColor($isPublicHoliday);
                            $holidayText = $publicHolidayName;
                        }
                        
                        //カレンダー詳細マスタ、組織カレンダーマスタに休日情報がある場合、値を挿入
                        foreach ( $mCalendarDList as $key => $val )
                        {
                            if($val['date'] == $days)
                            {
                                if($val['is_holiday'] != 0)
                                {
                                    $holiday = $val['is_holiday'];
                                    $color = $this->getHolidayColor($val['is_holiday']);
                                    $holidayText = $this->getHolidayText($val['is_holiday']);
                                }
                                elseif($val['allowance_id'] != 0)
                                {
                                    $holiday = $val['allowance_id'];
                                    $color = $this->getHolidayColor($val['allowance_id']);
                                    $holidayText = $this->getHolidayText($val['allowance_id']);
                                }
                            }
                        }
                        
                        if(empty($holiday))
                        {
                            if( $i == 0 && substr( $days, 8, 2 ) != "00" )
                            {
                                $holiday =  $isSunday;
                                $color = $this->getHolidayColor($isSunday);
                                $holidayText = $this->getHolidayText($isSunday);
                            }
                            if($tmpId === "1-6")
                            {
                                $holiday =  $isSaturday1;
                                $color = $this->getHolidayColor($isSaturday1);
                                $holidayText = $this->getHolidayText($isSaturday1);
                            }
                            if($tmpId === "2-6")
                            {
                                $holiday =  $isSaturday2;
                                $color = $this->getHolidayColor($isSaturday2);
                                $holidayText = $this->getHolidayText($isSaturday2);
                            }
                            if($tmpId === "3-6")
                            {
                                $holiday =  $isSaturday3;
                                $color = $this->getHolidayColor($isSaturday3);
                                $holidayText = $this->getHolidayText($isSaturday3);
                            }
                            if($tmpId === "4-6")
                            {
                                $holiday =  $isSaturday4;
                                $color = $this->getHolidayColor($isSaturday4);
                                $holidayText = $this->getHolidayText($isSaturday4);
                            }

                        }

                        //array_push($rtnAry, "<td id=\"$tmpId\" class=\"calendarTblCell\" oncontextmenu=\"changeCell('$tmpId')\"></td>");
                        array_push($rtnAry, "<td id=\"$tmpId\" class=\"calendarTblCell\" style=\"$color\" oncontextmenu=\"changeCell('$tmpId')\">");
                        array_push($rtnAry, "<input type=\"hidden\" id=\"$tmpId-val\" name=\"holiday\" value=\"$holiday\" class=\"is_holiday\">");
                        array_push($rtnAry, "<input type=\"hidden\" id=\"$tmpId-day\" name=\"holi-day\" value=\"$days\" class=\"is_date\">");
                        array_push($rtnAry, "<label id=\"$tmpId-view\">$holidayText</label>");
                        array_push($rtnAry, "</td>");
                    }

                    //コンテンツセルが作成されたらTRを閉じる
                    array_push($rtnAry, "</tr>");
                    array_push($rtnAry, "<tr>");
                    //カウントリセット
                    $cnt = 0;
                    $row++;
                    $dayList = array();
                }
                else
                {
                    //カウントインクリメントのみ行う
                    $cnt++;
                }
                $date++;
            }
            //=== 3.月最終週の空白ブランク部生成 ===============================>
            //最終週の空白セル生成する個数を指定
            for ($i = 0; $i < 7 - $cnt; $i++)
            {
                array_push($rtnAry, "<td class=\"calDetailHeaderCell\"></td>");
            }
            array_push($rtnAry, "</tr>");
            array_push($rtnAry, "<tr>");
            //最後に最終週用のコンテンツセルを生成する
                for ($i = 0; $i < 7; $i++)
                {
                    $tmpId = $row . "-" . $i;
                    $days = $year . '-' . $month . '-' . str_pad($dayList[$i], 2, 0, STR_PAD_LEFT);

                    //休日情報をリセット
                    $holiday = "";
  
                    //ラベルをリセット
                    $holidayText = "";

                    //色をリセット
                    $color = "";
                    
                    //カレンダー詳細マスタ、組織カレンダーマスタに休日情報がある場合、値を挿入
                    //※祝日分を入れる必要あり
                    foreach ( $mCalendarDList as $key => $val )
                    {
                        if($val['date'] == $days)
                        {
                            if($val['is_holiday'] != 0)
                            {
                                $holiday = $val['is_holiday'];
                                $color = $this->getHolidayColor($val['is_holiday']);
                                $holidayText = $this->getHolidayText($val['is_holiday']);
                            }
                            elseif($val['allowance_id'] != 0)
                            {
                                $holiday = $val['allowance_id'];
                                $color = $this->getHolidayColor($val['allowance_id']);
                                $holidayText = $this->getHolidayText($val['allowance_id']);
                            }
                        }
                    }

                        if(empty($holiday))
                        {
                            if( $i == 0 && substr( $days, 8, 2 ) != "00" )
                            {
                                $holiday =  $isSunday;
                                $color = $this->getHolidayColor($isSunday);
                                $holidayText = $this->getHolidayText($isSunday);
                            }
                            if($tmpId === "5-6" && substr( $days, 8, 2 ) != "00")
                            {
                                $holiday =  $isSaturday5;
                                $color = $this->getHolidayColor($isSaturday5);
                                $holidayText = $this->getHolidayText($isSaturday5);
                            }
                        }

                    array_push($rtnAry, "<td id=\"$tmpId\" class=\"calendarTblCell\" style=\"$color\" oncontextmenu=\"changeCell('$tmpId')\">");
                    array_push($rtnAry, "<input type=\"hidden\" id=\"$tmpId-val\" name=\"holiday\" class=\"is_holiday\" value=\"$holiday\">");
                    array_push($rtnAry, "<input type=\"hidden\" id=\"$tmpId-day\" name=\"holi-day\" class=\"is_date\" value=\"$days\">");
                    array_push($rtnAry, "<label id=\"$tmpId-view\">$holidayText</label>");
                    array_push($rtnAry, "</td>");
                }

            array_push($rtnAry, "</tr>");
            $Log->trace("END getCalendarValues");

            //カレンダー用配列が生成されたらリターン
            return $rtnAry;
        }
        
        /**
         * 組織カレンダー情報を取得
         * @param    $organizationID    組織ID
         * @return   組織カレンダー情報
         */
        public function getCalendar( $organizationID )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCalendar");
            
            // 組織のTOPIDを取得する
            $topID = $this->securityProcess->getTopOrganizationID( $organizationID, 'm_organization_calendar');

            // 指定組織のカレンダー情報を取得
            $sql = " SELECT "
                . "        organization_calendar_id "
                . "      , is_sunday "
                . "      , is_public_holiday "
                . "      , is_saturday_1 "
                . "      , is_saturday_2 "
                . "      , is_saturday_3 "
                . "      , is_saturday_4 "
                . "      , is_saturday_5 "
                . "      , update_time "
                . " FROM  m_organization_calendar "
                . " WHERE organization_id = :organization_id ";

            $parameters = array( 
                                ':organization_id' => $topID,
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'organization_calendar_id'  =>  0,
                            'is_sunday'                 =>  0,
                            'is_public_holiday'         =>  0,
                            'is_saturday_1'             =>  0,
                            'is_saturday_2'             =>  0,
                            'is_saturday_3'             =>  0,
                            'is_saturday_4'             =>  0,
                            'is_saturday_5'             =>  0,
                            'update_time'               =>  null,
                        );

            if( $result === false )
            {
                $Log->trace("END getCalendar");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data;
            }

            $Log->trace("END getCalendar");

            return $ret;
        }
        
        /**
         * 組織カレンダー情報を取得
         * @param    $calendarID    組織カレンダーID
         * @return   組織カレンダー詳細情報
         */
        public function getCalendarDetail( $calendarID, $stoYm, $etoYm )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getCalendarDetail");

            // 指定組織のカレンダー情報を取得
            $sql = " SELECT "
                . "     organization_calendar_id "
                . "   , allowance_id "
                . "   , date "
                . "   , is_holiday "
                . " FROM "
                . "   m_calendar_detail "
                . " WHERE organization_calendar_id = :organization_calendar_id "
                . " AND :sdate <= date AND date < :edate"
                . " ORDER BY date ";

            $parameters = array( 
                                ':organization_calendar_id' => $calendarID,
                                ':sdate' => $stoYm,
                                ':edate' => $etoYm,
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            $ret = array(
                            'organization_calendar_id'  =>  0,
                            'allowance_id'              =>  0,
                            'date'                      =>  0,
                            'is_holiday'                =>  0,
                        );

            if( $result === false )
            {
                $Log->trace("END getCalendarDetail");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                array_push( $ret, $data );
            }

            $Log->trace("END getCalendarDetail");

            return $ret;
        }

        /**
         * 組織カレンダーマスタ新規データ登録
         * @param    $postArray(組織カレンダーマスタ、またはカレンダー詳細マスタへの登録情報)
         * @return   SQLの実行結果
         */
        public function addNewData($postArray, $isDateArrayList, $isHolidayArrayList)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addNewData");

            if($DBA->beginTransaction())
            {
                $ret = "";
                
                // 組織カレンダーマスタ追加/更新振り分け後、対象の組織カレンダーID取得
                $organCalendarID = $this->organizationCalendarProcessingDistribution($postArray);

                // 組織カレンダーマスタの追加/更新の結果がfalseの場合ロールバック
                if( $organCalendarID == 0 )
                {
                    $DBA->rollBack();
                    $errMsg = "組織カレンダーマスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // カレンダー詳細マスタ追加/更新/削除処理
                $ret = $this->calendarDetailProcessingDistribution($postArray, $isDateArrayList, $isHolidayArrayList, $organCalendarID);

                if($ret !== "MSG_BASE_0000")
                {
                    $DBA->rollBack();
                    $errMsg = "組織カレンダー詳細マスタへの登録更新処理にエラーが生じました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return $ret;
                }
                
                
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "組織カレンダーマスタ登録処理のコミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "組織カレンダーID：" . $postarray['organization_calendar_id']. "組織ID：" . $postarray['add_organization_id'] . "サーバエラー";
                $Log->fatalDetail($errMsg);
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END addNewData");
            return $ret;
        }
        
        /**
         * 組織カレンダーマスタ追加/更新処理振り分け
         * @param    $postArray(組織カレンダーマスタ登録情報)
         * @return   SQL実行結果（定型文）
         */
        private function organizationCalendarProcessingDistribution($postArray)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START organizationCalendarProcessingDistribution");
            
            $organizationCalID = $this->isAttendance($postArray);
            
            // 新規登録か既存組織の適用予定作成かの判定
            if(!empty($organizationCalID))
            {
                // 組織カレンダーマスタ更新
                $ret = $this->modOrganizationCalendarUpdateData($postArray);
            }
            else
            {
                // 新規登録の場合組織カレンダーマスタへ登録
                $ret = $this->addOrganizationCalendarNewData($postArray);
            }

            $Log->trace("END organizationCalendarProcessingDistribution");
            return $ret;
        }
        
        /**
         * カレンダー詳細マスタ追加/更新処理振り分け
         * @param    $applicationDateId
         * @param    $laborRegId
         * @param    $postArray
         * @return   SQL実行結果（定型文）
         */
        private function calendarDetailProcessingDistribution($postArray, $isDateArrayList, $isHolidayArrayList, $organCalendarID)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START calendarDetailProcessingDistribution");
            
            // POSTで送られてきた配列に登録済みの組織カレンダーIDを入れ込む
            $arrayCount = count( $isDateArrayList );
            $ret = "";

            // 組織カレンダー詳細を削除
            $this->delOrganizationCalendarDetail( $organCalendarID, $isDateArrayList[0] );

            // カレンダー詳細の更新
            for( $i = 0; $i < $arrayCount; $i++ )
            {
                if( substr( $isDateArrayList[$i], 8, 2 ) != "00" )
                {
                    $ret = $this->addOrganizationCalendarDetailNewData($postArray, $isDateArrayList[$i], $isHolidayArrayList[$i], $organCalendarID);
                    
                    // 更新結果の確認
                    if($ret !== "MSG_BASE_0000")
                    {
                        $errMsg = "組織カレンダー詳細マスタの追加/更新/削除処理に失敗しました。";
                        $Log->warnDetail($errMsg);
                        $Log->trace("END calendarDetailProcessingDistribution");
                        return $ret;
                    }
                    
                }
            }

            $Log->trace("END calendarDetailProcessingDistribution");
            return $ret;
        }
        
        /**
         * 組織カレンダーマスタ新規データ登録
         * @param    $postArray            
         * @return   SQLの実行結果
         */
        private function addOrganizationCalendarNewData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addOrganizationCalendarNewData");

            $sql = 'INSERT INTO m_organization_calendar( organization_id'
                . '                        , is_sunday'
                . '                        , is_public_holiday'
                . '                        , is_saturday_1'
                . '                        , is_saturday_2'
                . '                        , is_saturday_3'
                . '                        , is_saturday_4'
                . '                        , is_saturday_5'
                . '                        , registration_time'
                . '                        , registration_user_id'
                . '                        , registration_organization'
                . '                        , update_time'
                . '                        , update_user_id'
                . '                        , update_organization'
                . '                        ) VALUES ('
                . '                         :organization_id'
                . '                       , :is_sunday'
                . '                       , :is_public_holiday'
                . '                       , :is_saturday_1'
                . '                       , :is_saturday_2'
                . '                       , :is_saturday_3'
                . '                       , :is_saturday_4'
                . '                       , :is_saturday_5'
                . '                       , current_timestamp'
                . '                       , :registration_user_id'
                . '                       , :registration_organization'
                . '                       , current_timestamp'
                . '                       , :update_user_id'
                . '                       , :update_organization)';

            $isSunday = $this->HolidayParameters($postArray['is_sunday_h'],$postArray['is_sunday_p']);
            $isPublicHoliday = $this->HolidayParameters($postArray['is_public_holiday_h'],$postArray['is_public_holiday_p']);
            $isSaturday1 = $this->HolidayParameters($postArray['is_saturday_1_h'],$postArray['is_saturday_1_p']);
            $isSaturday2 = $this->HolidayParameters($postArray['is_saturday_2_h'],$postArray['is_saturday_2_p']);
            $isSaturday3 = $this->HolidayParameters($postArray['is_saturday_3_h'],$postArray['is_saturday_3_p']);
            $isSaturday4 = $this->HolidayParameters($postArray['is_saturday_4_h'],$postArray['is_saturday_4_p']);
            $isSaturday5 = $this->HolidayParameters($postArray['is_saturday_5_h'],$postArray['is_saturday_5_p']);

            $parameters = array(
                    ':organization_id'             => $postArray['add_organization_id'],
                    ':is_sunday'                   => $isSunday,
                    ':is_public_holiday'           => $isPublicHoliday,
                    ':is_saturday_1'               => $isSaturday1,
                    ':is_saturday_2'               => $isSaturday2,
                    ':is_saturday_3'               => $isSaturday3,
                    ':is_saturday_4'               => $isSaturday4,
                    ':is_saturday_5'               => $isSaturday5,
                    ':registration_user_id'        => $postArray['user_id'],
                    ':registration_organization'   => $postArray['organization'],
                    ':update_user_id'              => $postArray['user_id'],
                    ':update_organization'         => $postArray['organization'],
                    );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_ERR_3111");
                $errMsg = "組織カレンダーマスタの新規登録処理に失敗しました。";
                $Log->warnDetail($errMsg);
                return 0;
            }
   
            // Lastidの更新
            $Lastid = $DBA->lastInsertId( "m_organization_calendar" );

            
            $Log->trace("END addOrganizationCalendarNewData");
            
            return $Lastid;
        }

        /**
         * 組織カレンダーマスタ詳細マスタ新規データ登録
         * @param    $postArray            入力パラメータ
         * @param    $date                 登録日
         * @param    $isHoliday            休日設定
         * @param    $organCalendarID      組織カレンダーID
         * @return   SQLの実行結果
         */
        private function addOrganizationCalendarDetailNewData($postArray, $date, $isHoliday, $organCalendarID)
        {

            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START addOrganizationCalendarDetailNewData");

            $sql = 'INSERT INTO m_calendar_detail( organization_calendar_id'
                . '                        , allowance_id'
                . '                        , date'
                . '                        , is_holiday'
                . '                        ) VALUES ('
                . '                         :organization_calendar_id'
                . '                       , :allowance_id'
                . '                       , :date'
                . '                       , :is_holiday)';

            // 休日設定がない場合
            if(empty($isHoliday))
            {
                $isHoliday = 0;
            }
            
            // 公休日or法定休暇以外
            $allowanceID = 0;
            if( $isHoliday != SystemParameters::$PUBLIC_HOLIDAY && SystemParameters::$STATUTORY_HOLIDAY != $isHoliday )
            {
                $allowanceID = $isHoliday;
                $isHoliday = 0;
            }

            // 休日/割増手当の設定がない場合、DBの追加を行わない
            if( $isHoliday == 0 && $allowanceID == 0 )
            {
                $Log->trace("END addOrganizationCalendarDetailNewData");
                return "MSG_BASE_0000";
            }

            // チェックボックスによる登録の場合、詳細にはデータ登録を行わない
            if( $this->isCheckBox( $postArray, $date, $isHoliday ) )
            {
                $Log->trace("END addOrganizationCalendarDetailNewData");
                return "MSG_BASE_0000";
            }

            $parameters = array(
                    ':organization_calendar_id'        => $organCalendarID,
                    ':allowance_id'                    => $allowanceID,
                    ':date'                            => $date,
                    ':is_holiday'                      => $isHoliday,
                    );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "登録に失敗しました。";
                $Log->warnDetail($errMsg);
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            

            $Log->trace("END addOrganizationCalendarDetailNewData");
            return "MSG_BASE_0000";
        }

        /**
         * チェックボックスによる、データ更新か
         * @param    $postArray            入力パラメータ
         * @param    $date                 登録日
         * @param    $isHoliday            休日設定
         * @return   公休か法定休か
         */
        private function isCheckBox( $postArray, $date, $isHoliday )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START isCheckBox");
            
            // 調査日の曜日を算出
            $datetime = new DateTime($date);
            $weekNo = (int)$datetime->format('w');
            
            // 土曜日(6)の場合
            if( $weekNo == 6 )
            {
                // 調査日付から、年間の週数を取得
                $searchDayW = intval(date('W',strtotime($date)));
                // 調査日付の月初を取得する
                $firstDate = date('Y-m-d', strtotime('first day of ' . $date));
                // 調査日付の月初の年間の週数を取得
                $firstDateW = intval(date('W',strtotime($firstDate)));
                // 調査月の第何週かを求める
                $weekEyes = $searchDayW - $firstDateW + 1;
                // 調査対象日が、法定休日であるか確認
                if( $postArray['is_saturday_'.$weekEyes.'_h'] == 'true' AND $isHoliday == SystemParameters::$STATUTORY_HOLIDAY )
                {
                    // チェックボックスでついた情報である
                    $Log->trace("END isCheckBox");
                    return true;
                }
                
                // 調査対象日が、公休日であるか確認
                if( $postArray['is_saturday_'.$weekEyes.'_p'] == 'true' AND $isHoliday == SystemParameters::$PUBLIC_HOLIDAY )
                {
                    // チェックボックスでついた情報である
                    $Log->trace("END isCheckBox");
                    return true;
                }
            }
            
            // 日曜日(0)の場合
            if( $weekNo == 0 )
            {
                // 調査対象日が、法定休日であるか確認
                if( $postArray['is_sunday_h'] == 'true' AND $isHoliday == SystemParameters::$STATUTORY_HOLIDAY )
                {
                    // チェックボックスでついた情報である
                    $Log->trace("END isCheckBox");
                    return true;
                }
                
                // 調査対象日が、公休日であるか確認
                if( $postArray['is_sunday_p'] == 'true' AND $isHoliday == SystemParameters::$PUBLIC_HOLIDAY )
                {
                    // チェックボックスでついた情報である
                    $Log->trace("END isCheckBox");
                    return true;
                }
            }
            
            // 調査日付が祝日である
            if( "" != $this->getPublicHolidayName( $date ) )
            {
                // 調査対象日が、法定休日であるか確認
                if( $postArray['is_public_holiday_h'] == 'true' AND $isHoliday == SystemParameters::$STATUTORY_HOLIDAY )
                {
                    // チェックボックスでついた情報である
                    $Log->trace("END isCheckBox");
                    return true;
                }
                
                // 調査対象日が、公休日であるか確認
                if( $postArray['is_public_holiday_p'] == 'true' AND $isHoliday == SystemParameters::$PUBLIC_HOLIDAY )
                {
                    // チェックボックスでついた情報である
                    $Log->trace("END isCheckBox");
                    return true;
                }
            }
            
            $Log->trace("END isCheckBox");
            return false;
        }

        /**
         * 法定休暇設定、公休日設定による休日設定
         * @return   公休か法定休か
         */
        private function HolidayParameters( $postDataH, $postDataP )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START HolidayParameters");
           
            if( $postDataH === "true" )
            {
                $isHoliday = SystemParameters::$STATUTORY_HOLIDAY;

            }
            elseif( $postDataP === "true" )
            {
                $isHoliday = SystemParameters::$PUBLIC_HOLIDAY;
            }
            else
            {
                $isHoliday = 0;
            }
            
            $Log->trace("END HolidayParameters");
            return $isHoliday;
        }

        /**
         * 組織カレンダーマスタデータ更新
         * @param    $postArray            
         * @return   組織カレンダーマスタ更新SQL文
         */
        private function modOrganizationCalendarUpdateData($postArray)
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modOrganizationCalendarUpdateData");

            $sql  = 'UPDATE m_organization_calendar SET'
                . '       is_sunday = :is_sunday'
                . '     , is_public_holiday = :is_public_holiday'
                . '     , is_saturday_1 = :is_saturday_1'
                . '     , is_saturday_2 = :is_saturday_2'
                . '     , is_saturday_3 = :is_saturday_3'
                . '     , is_saturday_4 = :is_saturday_4'
                . '     , is_saturday_5 = :is_saturday_5'
                . '     , update_time = current_timestamp'
                . '     , update_user_id = :update_user_id'
                . '     , update_organization = :update_organization '
                . '       WHERE organization_id = :organization_id AND update_time = :update_time ';

            $isSunday = $this->HolidayParameters($postArray['is_sunday_h'],$postArray['is_sunday_p']);
            $isPublicHoliday = $this->HolidayParameters($postArray['is_public_holiday_h'],$postArray['is_public_holiday_p']);
            $isSaturday1 = $this->HolidayParameters($postArray['is_saturday_1_h'],$postArray['is_saturday_1_p']);
            $isSaturday2 = $this->HolidayParameters($postArray['is_saturday_2_h'],$postArray['is_saturday_2_p']);
            $isSaturday3 = $this->HolidayParameters($postArray['is_saturday_3_h'],$postArray['is_saturday_3_p']);
            $isSaturday4 = $this->HolidayParameters($postArray['is_saturday_4_h'],$postArray['is_saturday_4_p']);
            $isSaturday5 = $this->HolidayParameters($postArray['is_saturday_5_h'],$postArray['is_saturday_5_p']);

            $parameters = array( 
                    ':organization_id'             => $postArray['add_organization_id'],
                    ':is_sunday'                   => $isSunday,
                    ':is_public_holiday'           => $isPublicHoliday,
                    ':is_saturday_1'               => $isSaturday1,
                    ':is_saturday_2'               => $isSaturday2,
                    ':is_saturday_3'               => $isSaturday3,
                    ':is_saturday_4'               => $isSaturday4,
                    ':is_saturday_5'               => $isSaturday5,
                    ':update_user_id'              => $postArray['user_id'],
                    ':update_organization'         => $postArray['organization'],
                    ':update_time'                 => $postArray['updateTime'],
                    );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters, true) )
            {
                // SQL実行エラー 
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "更新失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END modOrganizationCalendarUpdateData");
                return 0;
            }
            
            // 指定組織のカレンダーIDを取得
            $sql = " SELECT "
                . "     organization_calendar_id "
                . " FROM "
                . "   m_organization_calendar "
                . " WHERE organization_id = :organization_id ";

            $parameters = array( ':organization_id' => $postArray['add_organization_id'], );

            $result = $DBA->executeSQL($sql, $parameters);
            $ret = 0;
            if( $result === false )
            {
                $Log->trace("END modOrganizationCalendarUpdateData");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = $data['organization_calendar_id'];
            }
            
            $Log->trace("END modOrganizationCalendarUpdateData");
            
            return $ret;
        }

        /**
         * カレンダー詳細マスタデータ更新
         * @param    $postArray            
         * @return   カレンダー詳細マスタ更新SQL文
         */
        private function delOrganizationCalendarDetail( $organCalendarID, $date )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START delOrganizationCalendarDetail");
            
            // 指定月の1日をdate型で取得
            $ftoYm = substr( $date, 0, 4 ) . "/" . substr( $date, 5, 2 ) ."/".date("1");
            $timestamp = strtotime($ftoYm);
            $stoYm = date("Y-m-d H:i:s", $timestamp);

            // 来月の1日をdate型で取得
            $ltoYm = date("Y/m/d", strtotime("$ftoYm +1 month" ));
            $timestamp = strtotime($ltoYm);
            $etoYm = date("Y-m-d H:i:s", $timestamp);

            $sql  = 'DELETE FROM m_calendar_detail '
                . '        WHERE organization_calendar_id = :organization_calendar_id AND date >= :stoYm AND date < :etoYm ';

            $parameters = array( 
                    ':organization_calendar_id' => $organCalendarID,
                    ':stoYm'                    => $stoYm,
                    ':etoYm'                    => $etoYm,
                    );

            // SQL実行
            if( !$DBA->executeSQL($sql, $parameters) )
            {
                // SQL実行エラー 
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "カレンダー詳細マスタの更新失敗";
                $Log->warnDetail($errMsg);
                $Log->trace("END modOrganizationCalendarDetailUpdateData");
                return "MSG_FW_DB_EXCLUSION_NG";
            }
            $Log->trace("END delOrganizationCalendarDetail");

            return "MSG_BASE_0000";
        }

        /**
         * データが存在しているか確認
         * @param    $postArray
         * @return   有：true   無：false
         */
        private function isAttendance( $postArray )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START isAttendance");
            
            // 同じ組織IDが登録されているか
            $sql  = " SELECT organization_calendar_id FROM m_organization_calendar "
                . " WHERE  organization_id = :organization_id ";

            $parameters = array( 
                                ':organization_id'             => $postArray['add_organization_id'],
                                );

            $result = $DBA->executeSQL($sql, $parameters);

            if( $result === false )
            {
                $Log->trace("END isAttendance");
                return $organCalendarID;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $Log->trace("END isAttendance");
                $organCalendarID = $data['organization_calendar_id'];

                return $organCalendarID;
            }

            $Log->trace("END isAttendance");

            return $organCalendarID;
        }


        /**
          * カレンダーへのテキスト出力
          * @note 公休もしくは法定休の場合、テキストを出力
          * @return $holidayText
          */
        private function getHolidayText($holiday)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayText");

            if($holiday == SystemParameters::$STATUTORY_HOLIDAY)
            {
                $holidayText = "法定休日";
            }
            elseif($holiday == SystemParameters::$PUBLIC_HOLIDAY)
            {
                $holidayText = "公休日";
            }
            elseif(array_key_exists($holiday, $this->allowanceList))
            {
                $holidayText = $this->allowanceList[$holiday];
            }
            $Log->trace("END getHolidayText");
            return $holidayText;
        }

        /**
          * カレンダーへの色入力
          * @note 公休、法定休、割増の場合、色を出力
          * @return $color
          */
        private function getHolidayColor($holiday)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getHolidayColor");
            if($holiday == SystemParameters::$STATUTORY_HOLIDAY)
            {
                $color = "background-color: rgb(255, 204, 255)";
            }
            elseif($holiday == SystemParameters::$PUBLIC_HOLIDAY)
            {
                $color = "background-color: rgb(204, 255, 255)";
            }
            elseif(array_key_exists($holiday, $this->allowanceList))
            {
                $color = "background-color: rgb(255, 255, 204)";
            }

            $Log->trace("END getHolidayColor");
            return $color;
        }
    }
?>
