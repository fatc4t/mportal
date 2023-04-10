<?php
    /**
     * @file      シフト一覧画面コントローラ
     * @author    USE K.narita
     * @date      2016/07/22
     * @version   1.00
     * @note      シフト一覧画面の内容表示を行う
     */

    // BaseController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // シフト一覧処理モデル
    require './Model/ShiftList.php';

    /**
     * シフト一覧画面コントローラクラス
     * @note    シフト一覧画面の内容表示を行う
     */
    class ShiftListController extends BaseController
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
         * シフト一覧画面初期表示
         * @return    なし
         */
        public function showAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START showAction");
            $Log->info( "MSG_INFO_1700" );
            
            $printFlag = false;
            $this->initialDisplay( true, $printFlag );
            $Log->trace("END   showAction");
        }

        /**
         * シフト一覧画面印刷からの戻り表示
         * @return    なし
         */
        public function printReturnAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START printReturnAction");
            $Log->info( "MSG_INFO_1703" );
            
            $printFlag = false;
            $this->initialDisplay( false, $printFlag );
            $Log->trace("END   printReturnAction");
        }

        /**
         * シフト一覧画面検索
         * @return    なし
         */
        public function searchAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START searchAction" );
            $Log->info( "MSG_INFO_1701" );

            $this->initialList();
            $Log->trace("END   searchAction");
        }

        /**
         * 印刷用シフト一覧印刷画面表示
         * @return    なし
         */
        public function printAction()
        {
            global $Log; // グローバル変数宣言
            $Log->trace( "START printAction" );
            $Log->info( "MSG_INFO_1702" );

            $printFlag = true;
            $this->initialDisplay( false, $printFlag );
            $Log->trace("END printAction");
        }

        /**
         * シフト一覧画面表示
         * @note     シフト一覧画面全てを更新
         * @param    $isInit       検索値の初期値設定の有無 true：初期設定  false：設定なし
         * @param    $printFlag    初期表示か印刷ボタン押下を判別するためのフラグ
         * @return   無
         */
        private function initialDisplay( $isInit, $printFlag )
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialDisplay");

            if( $isInit )
            {
                $minDate = date("Y-m-d");
                $maxDate = date("Y-m-d",strtotime("+2 week"));
                $date = explode("-",$maxDate);
                $minDateTwoWeek = mktime(0,0,0,$date[1],$date[2] - 1,$date[0]);
                $maxDate = date("Y-m-d",$minDateTwoWeek);
                
                $_POST['searchMinDay'] = $minDate;
                $_POST['searchMaxDay'] = $maxDate;
                $_POST['searchOrganizatioID'] = $_SESSION["ORGANIZATION_ID"];
            }
            
            $searchArray = array(
                'organizationID'    => parent::escStr( $_POST['searchOrganizatioID'] ),
                'sectionName'       => parent::escStr( $_POST['searchSectionName'] ),
                'positionName'      => parent::escStr( $_POST['searchPositionName'] ),
                'employmentName'    => parent::escStr( $_POST['searchEmploymentname'] ),
                'minDay'            => parent::escStr( $_POST['searchMinDay'] ),
                'maxDay'            => parent::escStr( $_POST['searchMaxDay'] ),
            );

            $shiftList = new shiftList();
            
            // シフト一覧データ取得
            $shiftListAllList = $shiftList->getListData($searchArray);
            // 概要用データ取得
            $overviewList = $shiftList->getShiftList($searchArray);
            
            if(!$printFlag)
            {
                $abbreviatedNameList = $shiftList->setPulldown->getSearchOrganizationList( 'reference', true );      // 組織略称名リスト
                // 検索用プルダウンサイズ指定(組織プルダウン専用)
                $selectOrgSize = 200;
            }
            else
            {
                $abbreviatedNameList = $shiftList->setPulldown->getSearchOrganizationList( 'reference' );      // 組織略称名リスト
            }
            $sectionNameList     = $shiftList->setPulldown->getSearchSectionList( 'reference' );           // セクションリスト
            $positionNameList    = $shiftList->setPulldown->getSearchPositionList( 'reference' );          // 役職名リスト
            $employmentNameList  = $shiftList->setPulldown->getSearchEmploymentList( 'reference' );        // 雇用形態名リスト

            $dayList = array();
            $dateList = array();
            
            $this->setDateList( $searchArray, $dayList, $dateList);
            
            // 休日データ一覧を取得
            $holidayList = $shiftList->getHolidayList();

            $Log->trace("END   initialDisplay");
            require_once './View/ShiftListPanel.html';
        }

        /**
         * シフト一覧更新
         * @note     シフト一覧画面の概要と各セクション部分のみの更新
         * @return   無
         */
        private function initialList()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START initialList");
            
            $searchArray = array(
                'organizationID'    => parent::escStr( $_POST['searchOrganizatioID'] ),
                'sectionName'       => parent::escStr( $_POST['searchSectionName'] ),
                'positionName'      => parent::escStr( $_POST['searchPositionName'] ),
                'employmentName'    => parent::escStr( $_POST['searchEmploymentname'] ),
                'minDay'            => parent::escStr( $_POST['searchMinDay'] ),
                'maxDay'            => parent::escStr( $_POST['searchMaxDay'] ),
            );
            
            $shiftList = new shiftList();
            
            // シフト一覧データ取得
            $shiftListAllList = $shiftList->getListData($searchArray);
            // 概要用データ取得
            $overviewList = $shiftList->getShiftList($searchArray);
            
            $dayList = array();
            $dateList = array();
            
            $this->setDateList($searchArray,$dayList,$dateList);
            
            // 休日データ一覧を取得
            $holidayList = $shiftList->getHolidayList();
            
            $Log->trace("END   initialList");
            require_once './View/ShiftListTablePanel.html';
        }
        
        /**
         * 日付配列の作成
         * @param   $searchArray(開始日/終了日)
         * @param   $dayList(ヘッダ表示用の日付の空配列);
         * @param   $dateList(データ表示時に使用する日付の空配列)
         * @return  無
         */
        private function setDateList($searchArray,&$dayList,&$dateList)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setDateList");
            
            // 文字列分解
            $minDay = explode("-",$searchArray['minDay']);
            $maxDay = explode("-",$searchArray['maxDay']);
            
            $min = mktime(0,0,0,$minDay[1],$minDay[2],$minDay[0]);
            $max = mktime(0,0,0,$maxDay[1],$maxDay[2],$maxDay[0]);
            
            // 曜日用配列
            $weekArray = array(
                '日',
                '月',
                '火',
                '水',
                '木',
                '金',
                '土',
            );
            
            for($i = 0 ; $min + (86400 * $i) <= $max ; $i++)
            {
                // 曜日変換に使用するコード
                $weekDay = mktime(0,0,0,$minDay[1],$minDay[2]+$i,$minDay[0]);
                
                // 日付(m/d)に変換
                $today = date("n/j",$weekDay);
                // その日付の曜日を数値に変換
                $week = date("w",$weekDay);
                
                // 日付に曜日を連結
                $today .= "(" . $weekArray[$week] . ")" ; 
                
                array_push($dateList,date("Y-m-d",$weekDay));
                array_push($dayList,$today);
            }
            
            $Log->trace("END   setDateList");
        }
        
        /**
         * 時間表示設定
         * @param   $date    シフトの出退勤時間
         * @return  シフトの時間表示
         */
        private function setTimeDisplay( $date )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setTimeDisplay");
            
            // 日時から、時間(HH)を抜き出す
            $dateList = explode( " ",$date );
            $outputTime = substr( $dateList[1], 0, 2 );
            if($dateList[0] == "2016-04-02")
            {
                $outputTime = substr( $dateList[1], 0, 2 ) + 24; 
            }
            
            $Log->trace("END   setTimeDisplay");
            
            return $outputTime;
        }
    }
?>
