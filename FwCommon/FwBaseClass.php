<?php
    /**
     * @file    アプリケーション共通クラス
     * @author  USE Y.Sakata
     * @date    2016/06/23
     * @version 1.00
     * @note    アプリケーション共通処理を定義
     */

    // Log4phpの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'Log.php';

    /**
     * アプリケーション共通クラス
     * @note    Controller / Modelともに、継承する基本クラス
     */
    class FwBaseClass
    {
        // Log4PHPアクセスクラス
        protected $Log = null;  ///< ログ出力クラス

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            global $Log;    // グローバル変数宣言
            
            // Log4phpの初期化
            if( is_null( $Log ) )
            {
                $Log = new Log();
            }
        }

        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // オブジェクト破棄
            // Logクラスは、ベースコントローラで開放する
        }
        
        /**
         * 時間から分へ変換
         * @note     「00:00」形式を分(整数)に変換
         * @param    $time    「00:00」形式の時間
         * @return   分(整数)
         */
        protected function changeMinuteFromTime( $time )
        {

            global $Log; // グローバル変数宣言
            $Log->trace("START changeMinuteFromTime");

            // 空白の場合、0を返却する
            if( empty( $time ) )
            {
                return 0;
            }

            // マイナスの場合、0を返却する
            if( $time < 0 )
            {
                return 0;
            }

            $tArry=explode(":",$time);

            // 時間→分
            $hour=$tArry[0]*60;

            // 分を足す
            $mins=$hour+$tArry[1];

            $Log->trace("END changeMinuteFromTime");
            return $mins; 
        }

        /**
         * 分から時間へ変換
         * @note     分(整数)を「xx00:00」形式の時間に変換
         * @param    $time    分(整数)
         * @return   「xx00:00」形式の時間
         */
        protected function changeTimeFromMinute( $time )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeTimeFromMinute");

            // 空白の場合、00:00を返却する
            if( empty( $time ) )
            {
                return "00:00";
            }

            // マイナス値の場合、00:00で返却する
            if( $time <= 0 )
            {
                return "00:00";
            }

            //時間
            $hour = floor($time / 60);
            $strHour = '';
            
            $strHour = sprintf("%d",$hour);
            if( $hour < 100 )
            {
                $strHour = sprintf("%02d",$hour);
            }

            //余りを分に
            $min = floor($time % 60);

            $Log->trace("END changeTimeFromMinute");
            return sprintf("%02d", $strHour) . ':' . sprintf("%02d", $min);
        }
        
        /**
         * 祝日情報の取得
         * @note     指定日が、祝日かどうか判定し、祝日名を取得する
         * @param    $day    祝日か判定する日付(yyyy-mm-dd)
         * @return   祝日名
         */
        protected function getPublicHolidayName( $day )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START getPublicHolidayName");

            $holidayList = $_SESSION["A_PUBLIC_HOLIDAY_LIST"];
            if( empty($_SESSION["A_PUBLIC_HOLIDAY_LIST"]) )
            {
                $holidayList = $this->getPublicHolidayInfoList();
            }

            $ret = "";
            // 祝日情報のセッションテーブルが存在するか
            if( array_key_exists( $day, $holidayList ) )
            {
                $ret = $holidayList[$day];
            }

            $Log->trace("END getPublicHolidayName");
            
            return $ret;
        }

        /**
         * 日本の祝日リストを取得
         * @note     日本の祝日リストを取得する
         * @return   祝日リスト
         */
        protected function getPublicHolidayInfoList()
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START getPublicHolidayName");

            // 指定組織のカレンダー情報を取得
            $sql  = " SELECT date, holiday_name FROM public.m_japan_holiday ORDER BY date ";
            $parameters = array();

            $result = $DBA->executeSQL($sql, $parameters);
            $ret = array();
            if( $result === false )
            {
                $Log->trace("END getPublicHolidayName");
                return $ret;
            }

            while ( $data = $result->fetch(PDO::FETCH_ASSOC) )
            {
                $ret = array_merge( $ret, array( $data['date'] => $data['holiday_name'] ) );
            }

            $Log->trace("END getPublicHolidayName");

            return $ret;
        }

        /**
         * 2次元配列の2次元目の項目でのソート
         * @param    &$array        ソート対象の2次元配列
         * @param    $sortKey       ソート対象のキー名
         * @param    $sortType      ソートタイプ( SORT_ASC / SORT_DESC )
         * @return   なし
         */
        protected function sortArrayByKeyForTwoDimensions( &$array, $sortKey, $sortType = SORT_ASC )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START sortArrayByKeyForTwoDimensions");
            
            $tmpArray = array();
            foreach ( $array as $key => $row )
            {
                $tmpArray[$key] = $row[$sortKey];
            }
            array_multisort( $tmpArray, $sortType, $array );
            unset( $tmpArray );
            
            $Log->trace("END sortArrayByKeyForTwoDimensions");
        }

        /**
         * メール送信
         * @param    $to        宛先(,区切りで複数可)
         * @param    $subject   件名
         * @param    $body      本文
         * @param    $from      送信元
         * @return   true：送信コマンド実行  false：送信コマンド失敗
         */
        protected function sendMail( $to, $subject, $body, $from = "" )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START sendMail");
            
            // 宛先設定
            if( empty($from) )
            {
                $from = SystemParameters::$MAIL_SENDER;
            }
            
            // ヘッダの設定
            $header = "Content-Type: text/plain; charset=\"ISO-2022-JP\";\n" . "From:" . $from;
            
            // エンコードの設定
            mb_internal_encoding("UTF-8");
            // 件名のエンコード
            $enSubject = mb_encode_mimeheader( $subject, 'ISO-2022-JP-MS' );
            // 本文のエンコード
            $enBody    = mb_convert_encoding(  $body, 'ISO-2022-JP-MS');

            $ret = true;
            if( !mail( $to, $enSubject, $enBody, $header) )
            {
                // 送信コマンドに失敗
                $ret = false;
            }
            
            $Log->trace("END sendMail");
            
            return $ret;
        }
    }
?>
