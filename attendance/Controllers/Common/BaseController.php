<?php
    /**
     * @file    共通コントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/04/27
     * @version 1.00
     * @note    共通で使用するコントローラの処理を定義
     */

    // FwBaseControllerの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseController.php';

    /**
     * 各コントローラの基本クラス
     * @note    共通で使用するコントローラの処理を定義
     */
    class BaseController extends FwBaseController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
        }
        
        /**
         * デストラクタ
         */
        public function __destruct()
        {
            
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }
        
        /**
         * 配列の値をstring型からint型に変換して配列に再度入れ直す
         * @param    $stringArray
         * @param    $flag
         * @return   $intarray
         */
        protected function changeStringArrayIntArray($stringCommaArray, $flag)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeStringArrayIntArray");

            if(!empty($flag))
            {
                $stringComma = $stringCommaArray[0];
            }
            else
            {
                $stringComma = $stringCommaArray[1];
            }

            $stringArray = explode(",", $stringComma);

            foreach($stringArray as $string)
            {
                list($hour, $minute) = explode(":", $string);
                if(!empty($string))
                {
                    $intHourArray[] = intval($hour);
                    $intMinuteArray[] = intval($minute);
                }
            }
            // 時間部分のリストと分部分のリストを順番に配列に入れて、配列でまとめてリターンする
            $hourMinuteArray = array($intHourArray, $intMinuteArray);

            $Log->trace("END changeStringArrayIntArray");

            return $hourMinuteArray;
        }

        /**
         * 配列の値をCSV形式に修正
         * @param    $stringArray
         * @param    $flag
         * @return   $intarray
         */
        protected function changeCsvList( $stringCommaArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START changeCsvList");
            $ret = '';
            foreach($stringCommaArray as $string)
            {
                $ret .= $string . ',';
            }

            $ret = substr($ret, 0, -1);

            $Log->trace("END changeCsvList");

            return $ret;
        }
        
        /**
         * リストの値取得
         * @param    $orgArray   入力値
         * @param    $allArray   入力データをマージし返す配列
         * @return    なし
         */
        protected function setTimeArray( $orgArray,&$allArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START setTimeArray" );
            foreach( $orgArray as $key => $val )
            {
                if(!empty($val))
                {
                    $allArray[$key] = $this->changeMinuteFromTime($val);
                }
            }

            $Log->trace("END setTimeArray");
        }
        
        /**
         * 入力した配列を指定配列にマージ
         * @param    $orgArray   入力値
         * @param    $allArray   入力データをマージし返す配列
         * @return    なし
         */
        protected function setMergeList( $orgArray,&$allArray )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START setMergeList" );

            $index = 0;
            foreach( $orgArray as $org )
            {
                foreach( $org as $key => $val )
                {
                    $allArray[$key.$index] = $val;
                }
                $index++;
            }

            $Log->trace("END setMergeList");
        }
        
        /**
         * 深夜時間の設定
         * @param    $lateAtNighTime   深夜時間(00:00)
         * @return   日付の下一桁 + 時間
         */
        protected function setLateAtNighTime( $lateAtNighTime )
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START setLateAtNighTime" );

            $ret = null;
            if( $lateAtNighTime  != '' )
            {
                $time = explode( ':', $lateAtNighTime );
                
                $ret = '1 ' . $lateAtNighTime;
                if( 24 <= $time[0] )
                {
                    $ret = '2 ' . ( $time[0] - 24 ) . ':' . $time[1];
                }
            }

            $Log->trace("END setLateAtNighTime");

            return $ret;
        }
        
        /**
         * POSTで帰ってきた検索チェックボックスの値をboolean型から0/1へ変換する
         * @param    $checkFlag   チェックボックスの内容
         * @param    $checkArray  チェックリストの結果配列
         * @return   なし
         */
        protected function booleanTypeChangeIntger( $checkFlag, &$checkArray )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START booleanTypeChangeIntger");

            array_push($checkArray, $checkFlag === 'true' ? 1 : 0);

            $Log->trace("END booleanTypeChangeIntger");
        }

        /**
         * 表示用に数値をカンマ区切りにする
         * @param    $num   数値
         * @return   カンマ区切りの数値
         */
        protected function isNumberFormat( $num )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START isNumberFormat");

            // 変換対象の数値が存在するか
            if( empty( $num ) )
            {
                return 0;
            }

            $Log->trace("END isNumberFormat");
            
            return number_format( $num );
        }

        /**
         * 検索時間の分換算
         * @note     検索する時間帯を分に換算する
         * @param    $hour
         * @param    $minute
         * @return   $searchMinute
         */
        protected function conversionSearchTime( $hour, $minute )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START conversionSearchTime");

            // 文字が含まれていないか判定（文字があれば空文字を返す）
            $hour = $this->checkIntText( $hour );
            $minute = $this->checkIntText( $minute );

            // 文字列となっているものを数字へ変換する（空文字であれば0を返す）
            $hourInt = $this->changeTextIntval( $hour );
            $minuteInt = $this->changeTextIntval( $minute );

            $searchMinute = "";

            // 時間と分のどちらか一つでも0でない場合分換算したものをセットする
            if( $hourInt != 0 || $minuteInt != 0 )
            {
                $searchMinute = ( $hourInt * 60 ) + $minuteInt;
            }

            $Log->trace("END conversionSearchTime");

            return $searchMinute;
        }

        /**
         * 数字判定
         * @note     変数に入っている値が全て数字かどうか判定する
         * @param    $text
         * @return   $int
         */
        private function checkIntText( $text )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkIntText");

            if( !ctype_digit( $text ) )
            {
                $text = "";
            }

            $int = $text;

            $Log->trace("END checkIntText");

            return $int;
        }

        /**
         * 数字変換
         * @note     
         * @param    $checkText
         * @return   $checkInt
         */
        private function changeTextIntval( $checkText )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeTextIntval");

            if( !empty( $checkText ) )
            {
                $checkInt = intval($checkText);
            }
            else
            {
                $checkInt = 0;
            }

            $Log->trace("END changeTextIntval");

            return $checkInt;
        }

    }
?>
