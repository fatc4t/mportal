<?php
    /**
     * @file    日本の祝日を設定するクラス(Model)
     * @author  USE Y.Sakata
     * @date    2016/08/03
     * @version 1.00
     * @note    日本の祝日を設定する処理を定義
     */

    // FwBaseModelの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseModel.php';

    /**
     * 日本の祝日の取得クラス
     * @note    日本の祝日の取得処理を定義
     */
    class JapanHoliday extends FwBaseModel
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseModelのコンストラクタ
            parent::__construct();
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // FwBaseModelのデストラクタ
            parent::__destruct();
        }

        /**
         * 祝日データ更新
         * @return   SQLの実行結果
         */
        public function modJapanHoliday()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START modJapanHoliday");

            if( $DBA->beginTransaction() )
            {
                $ret = "";
                
                $japanHolidayList = $this->getJapanHoliday();
            
                $holidayDate = array_keys($japanHolidayList);
                $sdate = reset($holidayDate); 
                $edate = end($holidayDate);

                // 日本祝日マスタのデータの削除
                $ret = $this->holidayDel($sdate, $edate);
                if( $ret === false )
                {
                    // SQL実行エラー ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "更新に失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

                // 日本祝日マスタへの登録
                $ret = $this->holidayInsert( $japanHolidayList );
                if( $ret === false )
                {
                    // コミットエラー  ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "登録に失敗致しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }
                
                // コミット
                if( !$DBA->commit() )
                {
                    // コミットエラー　ロールバック対応
                    $DBA->rollBack();
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "コミットに失敗しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return "MSG_FW_DB_EXCLUSION_NG";
                }

            }
            else
            {
                // エラー処理(トランザクション開始エラー)
                $Log->fatal("MSG_FW_DB_TRANSACTION_NG");
                $errMsg = "トランザクション開始エラー";
                $Log->fatalDetail($errMsg);
                return "MSG_FW_DB_TRANSACTION_NG";
            }

            $Log->trace("END modJapanHoliday");

            return "MSG_BASE_0000";
        }

        /**
         * 祝日情報の取得
         * @note     昨年、今年、来年の3年分の日本の祝日情報を取得
         * @note     配列の key : 日付、value : 祝日名
         * @return   祝日情報
         */
        private function getJapanHoliday()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getJapanHoliday");

            // カレンダーID
            $calendar_id = urlencode('japanese__ja@holiday.calendar.google.com');
            $url = 'https://calendar.google.com/calendar/ical/'.$calendar_id.'/public/full.ics';

            $chInit = curl_init();
            curl_setopt($chInit, CURLOPT_URL, $url);
            curl_setopt($chInit, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($chInit, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chInit, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($chInit);
            curl_close($chInit);

            // googleから取得データなし
            if ( empty($result) )
            {
                $Log->trace("END getJapanHoliday");
                return array();
            }

            $items = $sort = array();
            $start = false;
            $count = 0;
            foreach(explode("\n", $result) as $row => $line) 
            {
                // 1行目が「BEGIN:VCALENDAR」でなければ終了
                if (0 === $row && false === stristr($line, 'BEGIN:VCALENDAR')) 
                {
                    break;
                }

                // 改行などを削除
                $line = trim($line);

                // 「BEGIN:VEVENT」なら日付データの開始
                if (false !== stristr($line, 'BEGIN:VEVENT')) 
                {
                    $start = true;
                }
                elseif ($start) 
                {
                    // 「END:VEVENT」なら日付データの終了
                    if (false !== stristr($line, 'END:VEVENT')) 
                    {
                        $start = false;

                        // 次のデータ用にカウントを追加
                        ++$count;
                    }
                    else
                    {
                        // 配列がなければ作成
                        if (empty($items[$count])) 
                        {
                            $items[$count] = array('date' => null, 'title' => null);
                        }

                        // 「DTSTART;～」（対象日）の処理
                        if(0 === strpos($line, 'DTSTART;VALUE')) 
                        {
                            $date = explode(':', $line);
                            $date = end($date);
                            $date = wordwrap($date, 6, "/", true);
                            $date = wordwrap($date, 4, "/", true);

                            $items[$count]['date'] = $date;

                            // ソート用の配列にセット
                            $sort[$count] = $date;
                        }
                        // 「SUMMARY:～」（名称）の処理
                        elseif(0 === strpos($line, 'SUMMARY:')) 
                        {
                            list($title) = explode('/', substr($line, 8));
                            $items[$count]['title'] = trim($title);
                        }
                    }
                }
            }

            // 日付でソート
            $items = array_combine($sort, $items);
            ksort($items);
            
            // 一次元配列に修正する
            $ret = array();
            foreach ( $items as $item )
            {
                $ret = array_merge( $ret, array( $item['date']  =>  $item['title'] ) );
            }
            
            $Log->trace("END getJapanHoliday");

            return $ret;
        }

        /**
         * 祝日情報の削除
         * @param    $sdate     削除対象開始日
         * @param    $edate     削除対象終了日
         * @return   true：成功  false：失敗
         */
        private function holidayDel( $sdate, $edate ) 
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START holidayDel");

            $sql =  ' DELETE FROM public.m_japan_holiday  WHERE :sdate <= date AND date <= :edate';
            $parameters = array( ':sdate' => $sdate, ':edate' => $edate,);

            $result = $DBA->executeSQL($sql, $parameters);
            if( $result === false )
            {
                // ロールバック対応
                $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                $errMsg = "登録に失敗致しました。";
                $Log->warnDetail($errMsg);
                $Log->trace("END addNewData");
                return false;
            }

            $Log->trace("END holidayDel");

            return true;
        }

        /**
         * 祝日情報の登録
         * @param    $holidayList  登録対象の祝日リスト
         * @return   true：成功  false：失敗
         */
        private function holidayInsert( $holidayList ) 
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START holidayInsert");
            
            $sql  = 'INSERT INTO public.m_japan_holiday( date'
                .  '                 , holiday_name'
                .  '                 ) VALUES ( '
                .  '                 :date'
                .  '               , :holiday_name) ';

            foreach ( $holidayList as $key => $val )
            {
                $parameters = array( 
                                        ':date'                   => $key, 
                                        ':holiday_name'           => $val, 
                               );

                $result = $DBA->executeSQL($sql, $parameters);

                if( $result === false )
                {
                    // ロールバック対応
                    $Log->warn("MSG_FW_DB_EXCLUSION_NG");
                    $errMsg = "登録に失敗致しました。";
                    $Log->warnDetail($errMsg);
                    $Log->trace("END addNewData");
                    return false;
                }
            }

            $Log->trace("END holidayInsert");

            return true;
        }
    }

?>
