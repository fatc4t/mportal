<?php
    /**
     * @file      組織カレンダーコントローラ
     * @author    USE Y.Sugou
     * @date      2016/06/30
     * @version   1.00
     * @note      組織カレンダーの新規登録/修正/削除を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 組織カレンダー処理モデル
    require './Model/OrganizationCalendar.php';

    /**
     * 組織カレンダーマスタコントローラクラス
     * @note   組織カレンダマスタの新規登録/修正/削除を行う
     */
    class OrganizationCalendarController extends BaseController
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
         * 組織カレンダー一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1180" );
            
            $this->initialDisplay();
            $Log->trace("END   showAction");
        }

        /**
         * 組織カレンダー一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1181" );

            $this->initialList();

            $Log->trace("END   searchAction");
        }

        /**
         * 組織カレンダー一覧画面登録
         * @return    なし
         */
        public function modAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START addAction" );
            $Log->info( "MSG_INFO_1182" );

            $organizationCalendar = new OrganizationCalendar();

            $postArray = array(
                'is_sunday_h'                 => parent::escStr( $_POST['is_sunday_h'] ),
                'is_public_holiday_h'         => parent::escStr( $_POST['is_public_holiday_h'] ),
                'is_saturday_1_h'             => parent::escStr( $_POST['is_saturday_1_h'] ),
                'is_saturday_2_h'             => parent::escStr( $_POST['is_saturday_2_h'] ),
                'is_saturday_3_h'             => parent::escStr( $_POST['is_saturday_3_h'] ),
                'is_saturday_4_h'             => parent::escStr( $_POST['is_saturday_4_h'] ),
                'is_saturday_5_h'             => parent::escStr( $_POST['is_saturday_5_h'] ),
                'is_sunday_p'                 => parent::escStr( $_POST['is_sunday_p'] ),
                'is_public_holiday_p'         => parent::escStr( $_POST['is_public_holiday_p'] ),
                'is_saturday_1_p'             => parent::escStr( $_POST['is_saturday_1_p'] ),
                'is_saturday_2_p'             => parent::escStr( $_POST['is_saturday_2_p'] ),
                'is_saturday_3_p'             => parent::escStr( $_POST['is_saturday_3_p'] ),
                'is_saturday_4_p'             => parent::escStr( $_POST['is_saturday_4_p'] ),
                'is_saturday_5_p'             => parent::escStr( $_POST['is_saturday_5_p'] ),
                'allowance_id'                => parent::escStr( $_POST['allowance_id'] ),
                'updateTime'                  => parent::escStr( $_POST['updateTime'] ),
                'add_organization_id'         => parent::escStr( $_POST['add_organization_id'] ),
                'add_datetext'                => parent::escStr( $_POST['add_datetext'] ),
                'user_id'                     => $_SESSION["USER_ID"],
                'organization'                => $_SESSION["ORGANIZATION_ID"],
            );

            $isDateArrayList = array();
            $isHolidayArrayList = array();
            
            $arrayCount = count($_POST['isDateArrayList']);
            for($i = 0 ; $i < $arrayCount ; $i++ )
            {
                array_push( $isDateArrayList ,              parent::escStr( $_POST['isDateArrayList'][$i] ) );
                array_push( $isHolidayArrayList ,           parent::escStr( $_POST['isHolidayArrayList'][$i] ) );
            }

            $messege = $organizationCalendar->addNewData($postArray, $isDateArrayList, $isHolidayArrayList);

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                $this->initialList();
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }

            $Log->trace("END   addAction");
        }


        /**
         * 組織カレンダー一覧画面
         * @note     組織カレンダー画面全てを更新
         * @return   無
         */
        private function initialDisplay()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");    //終了ログ書出

            //組織カレンダマスタロジックインスタンスを生成
            $organizationCalendar = new OrganizationCalendar();
            //プルダウンリストの取得
            $abbreviatedNameList = $organizationCalendar->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;
            
            //登録用プルダウンリストの取得
            $regNameList = $organizationCalendar->setPulldown->getSearchOrganizationList( 'registration' );                 // 組織略称名リスト
            $regFlag = count( $regNameList ) -1;
            $addAllowanceList = $organizationCalendar->setPulldown->getSearchAdditionalAllowanceList( 'registration' );     // 割増手当リスト

            //当月を取得
            $toYm = date("Y")."/".date("m");
            
            //当月の1日をdate型で取得
            $ftoYm = date("Y")."/".date("m")."/".date("1");
            $timestamp = strtotime($ftoYm);
            $stoYm = date("Y-m-d H:i:s", $timestamp);

            //来月の1日をdate型で取得
            $ltoYm = date("Y/m/d", strtotime("$ftoYm +1 month" ));
            $timestamp = strtotime($ltoYm);
            $etoYm = date("Y-m-d H:i:s", $timestamp);

            // ログイン者の組織IDを設定
            $searchArray = array();
            $searchArray['organizationID'] = $_SESSION["ORGANIZATION_ID"];

            // 組織カレンダー情報を取得
            $mCalendarList = $organizationCalendar->getCalendar($_SESSION["ORGANIZATION_ID"]);
            // 組織カレンダー詳細を取得
            $mCalendarDList = $organizationCalendar->getCalendarDetail($mCalendarList['organization_calendar_id'], $stoYm, $etoYm );

            //カレンダー用HTMLタグを生成
            if( $regFlag >= 1 )
            {
                $calList = $organizationCalendar->getCalendarValues($toYm, $_SESSION["ORGANIZATION_ID"]);
            }

            $Log->trace("END   initialDisplay"); //終了ログ書出
            //ViewのInclude
            require_once './View/OrganizationCalendarPanel.html';
        }

        /**
         * 組織カレンダー一覧更新
         * @note     組織カレンダー画面の一覧部分のみの更新
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        private function initialList()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialList");

            $searchArray = array(
                'organizationID' => parent::escStr( $_POST['searchOrganizationID'] ),
                'target'         => parent::escStr( $_POST['searchTarget'] ),
            );

            $organizationCalendar = new OrganizationCalendar();
            
            // 検索月を取得
            $toYm = $searchArray['target'];
            
            //検索月の1日をdate型で取得
            $ftoYm = $searchArray['target']."/".date("1");
            $timestamp = strtotime($ftoYm);
            $stoYm = date("Y-m-d H:i:s", $timestamp);

            //来月の1日をdate型で取得
            $ltoYm = date("Y/m/d", strtotime("$ftoYm +1 month" ));
            $timestamp = strtotime($ltoYm);
            $etoYm = date("Y-m-d H:i:s", $timestamp);
            
            // 組織カレンダー情報を取得
            $mCalendarList = $organizationCalendar->getCalendar($searchArray['organizationID']);
            // 組織カレンダー詳細を取得
            $mCalendarDList = $organizationCalendar->getCalendarDetail($mCalendarList[0]['organization_calendar_id'], $stoYm, $etoYm );

            //カレンダー用HTMLタグを生成
            $calList = $organizationCalendar->getCalendarValues($toYm, $searchArray['organizationID']);

            $abbreviatedNameList = $organizationCalendar->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            // アクセス権限の設定
            $regNameList = $organizationCalendar->setPulldown->getSearchOrganizationList( 'registration' );      // 組織略称名リスト
            $regFlag = count( $regNameList ) -1;

            $Log->trace("END   initialList");
            require_once './View/OrganizationCalendarTablePanel.html';
        }
    }
?>
