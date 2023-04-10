<?php
    /**
     * @file      日々の打刻漏れ、アラートチェック
     * @author    USE Y.Sakata
     * @date      2016/10/25
     * @version   1.00
     * @note      日々の打刻漏れ、アラートチェック(組織単位)
     */

    // BaseCheckAlertController.phpを読み込む
    require './Controllers/Common/BaseCheckAlertController.php';
    // 日々の打刻漏れ、アラートチェックモデル
    require './Model/ExeCheckAlert.php';

    /**
     * 日々の打刻漏れ、アラートチェッククラス
     * @note   勤怠修正マスタテーブルの管理を行うの初期設定を行う
     */
    class ExeCheckAlertController extends BaseCheckAlertController
    {
        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // ModelBaseのコンストラクタ
            parent::__construct();
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
         * 日々アラートチェック
         */
        public function sendCheckAlertMail()
        {
            global $DBA, $Log; // グローバル変数宣言
            
            $Log->info( "MSG_INFO_1820" );
            $Log->trace("START sendCheckAlertMail");

            $_SESSION["SCHEMA"] = 'public';

            $exeCheckAlert = new ExeCheckAlert();
            $companyList = $exeCheckAlert->getCompanyContract();

            // 昨日の日付を設定
            $selectDate = date('Y/m/d', strtotime('-1 day', time()));
            // 店舗の開始時間(現在より、1時間前)を設定
            $startTime = date('H:i', strtotime( '-1 hour', time() ) );
            // 店舗の開始時間(現在時刻)を設定
            $endTime   = date('H:i');

            foreach( $companyList as $company )
            {
                // スキーマ取得
                $_SESSION["SCHEMA"] = $company;
                // 一企業単位で、勤怠更新
                $this->companiesAlertCheck( $exeCheckAlert, $selectDate, $startTime, $endTime );
            }

            $Log->trace("END sendCheckAlertMail");
        }

        /**
         * 企業単位でアラートチェックを実行
         * @param    &$exeCheckAlert    アラートチェックモデル
         * @param    $selectDate        日付
         * @param    $startTime         開始時間
         * @param    $endTime           終了時間
         * @return   なし(バッチ処理で、ログ出力のみ)
         */
        private function companiesAlertCheck( &$exeCheckAlert, $selectDate, $startTime, $endTime )
        {
            global $DBA, $Log; // グローバル変数宣言
            $Log->trace("START companiesAlertCheck");
            
            // 全組織取得
            $allOrganizationList = $exeCheckAlert->getAllOrganization();

            foreach( $allOrganizationList as $organization )
            {
                $alertMsg = '';
                $embossingLeakageMsg = '';
                $eMail    = '';
                // 組織所属の従業員を取得
                $attendanceCorList = $exeCheckAlert->getTargetDayBulk( $organization['organization_id'], $selectDate, $startTime, $endTime );
                if( !empty($attendanceCorList) )
                {
                    foreach( $attendanceCorList as $attendanceCt )
                    {
                        // アラート設定
                        $tmpEMail = '';
                        $tmpAlertMsg = $this->setAlert( $attendanceCt, $selectDate, $exeCheckAlert, false, $tmpEMail );
                        
                        if( !empty( $tmpAlertMsg ) )
                        {
                            $name = $attendanceCt['user_name'] . "：";
                            $tmpAlertMsg = str_replace( "", $name, $tmpAlertMsg );
                            $alertMsg .= "\n　　" . $tmpAlertMsg;

                            // 就業規則で取得したE-メールアドレス
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
                        
                        // 打刻漏れチェック
                        $tmpEmbossingLeakageMsg = $this->getEmbossingLeakageMsg( $attendanceCt );
                        if( !empty( $tmpEmbossingLeakageMsg ) )
                        {
                            $embossingLeakageMsg .= $tmpEmbossingLeakageMsg;

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
                if( !empty( $alertMsg ) || !empty( $embossingLeakageMsg ) )
                {
                    // 送信日を設定
                    $mailDay = date( 'Y年m月d日', strtotime( $selectDate ) );
                    
                    // 送信先を従業員マスタで個別に承認権限があるものを取得
                    $userMailList = $exeCheckAlert->getUserMailList( $organization['organization_id'] );
                    foreach($userMailList as $val )
                    {
                        // メールアドレスが空以外かつ、既に登録されていないアドレスのみ追加
                        if( (!empty($val)) && ( false === strpos( $eMail, $val ) ) )
                        {
                            $eMail .= "," . $val;
                        }
                    }

                    // 上位組織(自組織含む)で、承認権限があるもの
                    $userMailList = $exeCheckAlert->getUserMailListForOrg( $organization['organization_id'] );
                    foreach($userMailList as $val )
                    {
                        // メールアドレスが空以外かつ、既に登録されていないアドレスのみ追加
                        if( (!empty($val)) && ( false === strpos( $eMail, $val ) ) )
                        {
                            $eMail .= "," . $val;
                        }
                    }

                    // メールアドレスの整形
                    // [,,]を削除
                    $eMail = str_replace( ",,", ",", $eMail );

                    // アラートメールの送信
                    $subject = "「" . $mailDay . " " . $organization['abbreviated_name'] . "」勤怠実績 アラート情報";
                    $alertMailMsg = str_replace( "<br>", "\n　　", $alertMsg );
                    
                    $allAlertMsg  = "「" . $mailDay . " " . $organization['abbreviated_name'] . "」の勤怠実績で、以下のアラートが発生しています。\n";
                    
                    if( !empty( $alertMailMsg ) )
                    {
                        $allAlertMsg .= "\n　■勤怠実績アラート" . $alertMailMsg . "\n";
                    }

                    if( !empty( $embossingLeakageMsg ) )
                    {
                        $allAlertMsg .= "\n　■打刻漏れ" . $embossingLeakageMsg . "\n";
                    }

                    $allAlertMsg .= "\n以上です。";
                    
                    $ret = $this->sendMail( $eMail, $subject, $allAlertMsg );
                    $Log->trace( "送信結果：" . $ret );
                }
            }

            $Log->trace("END companiesAlertCheck");
            return;
        }
    }
?>
