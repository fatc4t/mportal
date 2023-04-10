<?php
    /**
     * @file      シフトフォーマットダウンロード/インポートコントローラ
     * @author    USE Y.Sakata
     * @date      2016/07/30
     * @version   1.00
     * @note      シフトフォーマットダウンロード/インポートを行う
     */

    // BaseCheckAlertController.phpを読み込む
    require './Controllers/Common/BaseCheckAlertController.php';
    // シフトフォーマットダウンロード/インポート処理モデル
    require './Model/ShiftDlUp.php';

    /**
     * シフトフォーマットダウンロード/インポートコントローラクラス
     * @note   シフトフォーマットダウンロード/インポートを処理を行う
     */
    class ShiftDlUpController extends BaseCheckAlertController
    {
        protected $shiftDlUp = null;              ///< シフトアップロード/ダウンロードモデル
        
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // BaseControllerのコンストラクタ
            parent::__construct();
            global $shiftDlUp;
            $shiftDlUp = new ShiftDlUp();
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
         * シフトフォーマットダウンロード/インポート画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1710" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * シフト登録(インポート)
         * @return    なし
         */
        public function addAction()
        {
            global $Log, $shiftDlUp; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1712" );
            
            // 実行最大時間の設定
            set_time_limit(300);
            
            $current_id = session_id();
            
            $filePath = system('find ' . SystemParameters::$SHIFT_SAVE_PATH . ' -type f -name ' . $current_id .'*');
            $fileName = explode("_",$filePath);

            // ファイル名から、日付と組織IDを取得する
            $list = explode( "-", $fileName[2] );
            $date = substr( $list[0], 0, 4 ) . '/' . substr( $list[0], 4, 2 );
            $organizationID = $list[1];
            
            $postArray = array(
                'organizationID' => $organizationID,
                'date'           => $date,
            );

            $alertMsg = '';
            if( !$shiftDlUp->setExcel( $filePath ) )
            {
                // Excelファイルの読み込みに失敗
                $messege = "MSG_ERR_3121";
            }
            else
            {
                // インポート処理開始
                $messege = $shiftDlUp->loopList( $filePath, $fileName[2], $postArray );
                // セッションIDを削除したファイル名へ修正(ダウンロード時に使用するファイル名)
                rename( $filePath, SystemParameters::$SHIFT_SAVE_PATH . $fileName[2] );
                
                // 登録したシフト勤怠情報を取得する
                $sDay = date( 'Y/m/01', strtotime( $date . '/01') );
                $eDay = date( 'Y/m/01', strtotime( $date . '/01' . '+1 month' ) );
                $shiftAttendanceList = $shiftDlUp->getShiftAttendance( $organizationID, $sDay, $eDay );
                $eMail = '';
                // シフト勤怠分にエラーがないかチェックする
                foreach($shiftAttendanceList as $shiftAttendance )
                {
                    // シフト概算計算にて、所定日数の場合、全てのシフト登録後でないと日数が確定できない為、
                    // ここでもう一度、再計算を行う
                    $shiftDlUp->setShiftInfo( $shiftAttendance['shift_id'] );
                    $tmpEMail = '';
                    $tmpMsg = $this->setAlert( $shiftAttendance, $shiftAttendance['day'], $shiftDlUp, true, $tmpEMail );
                    if( $tmpMsg != '' )
                    {
                        if( $alertMsg != '' )
                        {
                            $alertMsg .= '<br>';
                        }
                        $alertMsg .= $tmpMsg;
                        
                        // 同じメールアドレスは、登録しない
                        $eMailList = explode( ',', $tmpEMail );
                        foreach($eMailList as $val )
                        {
                            if( false === strpos( $eMail, $val ) )
                            {
                                $eMail .= $val . ",";
                            }
                        }
                    }
                }
            }

            // アラートがあるか
            if( !empty( $alertMsg ) )
            {
                // 組織名を取得する
                $targetDate = $date . "/01";
                $organizationName = $shiftDlUp->getOrganizationName( $organizationID, $targetDate );
                $mailDay = date( 'Y年m月', strtotime( $date . '/01') );
                
                // 送信先を従業員で承認権限があるものを取得
                $userMailList = $shiftDlUp->getUserMailList( $organizationID );
                foreach($userMailList as $val )
                {
                    // メールアドレスが空以外かつ、既に登録されていないアドレスのみ追加
                    if( (!empty($val)) && ( false === strpos( $eMail, $val ) ) )
                    {
                        $eMail .= $val . ",";
                    }
                }
                
                // アラートメールの送信
                $subject = "「" . $mailDay . " " . $organizationName . "」シフト アラート情報";
                $alertMailMsg = str_replace( "<br>", "\n　　", $alertMsg );
                
                $allAlertMsg  = $_SESSION["USER_NAME"] . "様が、「" . $mailDay . " " . $organizationName . "」のシフトを更新致しました。\n" 
                              . "以下のアラートが発生しています。\n\n　　" . $alertMailMsg;
                $allAlertMsg .= "\n\n以上です。";
                
                $ret = $this->sendMail( $eMail, $subject, $allAlertMsg );
                $Log->trace( "送信結果：" . $ret );
            }

            $this->initialDisplay( $messege, $alertMsg );

            $Log->trace("END   addAction");
        }

        /**
         * シフトフォーマットのダウンロード
         * @return    なし
         */
        public function downloadAction()
        {
            global $Log, $shiftDlUp; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1713" );
            
            // 実行最大時間の設定
            set_time_limit(300);
            
            $postArray = array(
                'organizationID' => parent::escStr( $_POST['organizationId'] ),
                'date'           => parent::escStr( $_POST['date'] ),
            );
            
            $date = explode("/",$postArray['date']);
            
            // ダウンロードするシフト名を作成
            $fileName = $date[0] . $date[1] . "-" . $postArray['organizationID'] . "-" . $_SESSION["COMPANY_ID"] . ".xlsm"; 

            // シフトファイルが存在する
            if( !file_exists( SystemParameters::$SHIFT_SAVE_PATH . $fileName ) )
            {
                $shiftDlUp->excelSave( $postArray );
                $date = substr( $fileName, 0, 4 ) . "-" . substr( $fileName, 4, 2 ) ;
                $shiftDlUp->excelCopy( $postArray, SystemParameters::$SHIFT_SAVE_PATH . $fileName, $date );
            }

            // シフト表をダウンロード可能フォルダへコピー
            if( !copy( SystemParameters::$SHIFT_SAVE_PATH . $fileName, 'Temp/' .$fileName ) )
            {
                $this->initialDisplay("MSG_ERR_3118");
                exit;
            }

            // テンプレートファイル削除
            unlink( SystemParameters::$SHIFT_SAVE_PATH . session_id() . '.xlsm' );

            // シフトファイルのダウンロード
            $setLocation = "LOCATION: " . 'Temp/' .$fileName;
            header($setLocation);

            $Log->trace("END   downloadAction");
        }
        
        /**
         * シフトフォーマットダウンロード/インポート画面
         * @note     シフトフォーマットダウンロード/インポート画面全てを更新
         * @return   無
         */
        private function initialDisplay( $messege = "MSG_BASE_0000", $alertMsg = '' )
        {
            global $Log, $TokenID, $shiftDlUp;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            
            // 検索プルダウン
            $abbreviatedNameList = $shiftDlUp->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;

            // アクセス権限を制御するプルダウン
            $btDisabledList = $shiftDlUp->setPulldown->getSearchOrganizationList( 'registration' );   // 組織略称名リスト

            // ダウンロードボタン押下判定フラグ
            $btDisabled = "";
            if( ( count( $btDisabledList ) -1 ) == 0 ) 
            {
                $btDisabled = "disabled";
            }
            
            // ダウンロードの日付設定
            $toYm = date('Y/m');
            // 検索組織の初期設定
            $searchArray = array( 'organizationID'  => $_SESSION["ORGANIZATION_ID"] );

            // 画面描画
            require_once './View/ShiftDlUpPanel.html';

            $Log->trace("END   initialDisplay");
        }
    }
?>
