<?php
    /**
     * @file      ベース就業規則コントローラ
     * @author    USE S.Kasai
     * @date      2016/07/27
     * @version   1.00
     * @note      就業規則の新規登録/修正/削除を行う
     */

    // LaborRegulationsController.phpを読み込む
    require './Controllers/Common/BaseController.php';
    // 就業規則処理モデル
    require './Model/LaborRegulations.php';

    /**
     * 就業規則コントローラクラス
     * @note   就業規則の新規登録/修正/削除を行う
     */
    class BaseLaborRegulationsController extends BaseController
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
         * 就業規則一覧画面
         * @note     就業規則画面全てを更新
         * @return   無
         */
        protected function initialDisplay($startFlag)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialDisplay");
            // 就業規則モデルインスタンス化
            $laborRegulations = new laborRegulations();
            // 検索プルダウン
            $abbreviatedNameList  = $laborRegulations->setPulldown->getSearchOrganizationList( 'reference', true );         // 組織略称名リスト
            $laborRegNameList     = $laborRegulations->setPulldown->getSearchLaborRegulationsList( 'reference' );           // 就業規則リスト
            $overtimeSetNameList  = $laborRegulations->setPulldown->getSearchOvertimeSettingList( 'reference' );            // 残業設定リスト
            $legalInNameList      = $laborRegulations->setPulldown->getSearchLegalTimeInOvertimeList( 'reference' );        // 法定内残業代リスト
            $legalOutNameList     = $laborRegulations->setPulldown->getSearchLegalTimeOutOvertimeList( 'reference' );       // 法定外残業代リスト
            $legHolidayNameList   = $laborRegulations->setPulldown->getSearchLegalHolidayAllowanceList( 'reference' );      // 法定休日残業代リスト
            $prescribedNameList   = $laborRegulations->setPulldown->getSearchPrescribedHolidayList( 'reference' );          // 公休日残業代リスト
            $lateAtNightNameList  = $laborRegulations->setPulldown->getSearchLateAtNightOutOvertimeList( 'reference' );     // 深夜残業代リスト
            // 検索用プルダウンサイズ指定(組織プルダウン専用)
            $selectOrgSize = 200;
            // 検索バー用チッェクボックスの状況を配列に入れる
            if(!empty($startFlag))
            {
                $effArray = array(1,0,0,0);
            }
            else
            {
                $effArray = array();
                $this->booleanTypeChangeIntger( $_POST['searchApplyCheck'], $effArray );
                $this->booleanTypeChangeIntger( $_POST['searchApplySchCheck'], $effArray );
                $this->booleanTypeChangeIntger( $_POST['searchNonApplyCheck'], $effArray );
                $this->booleanTypeChangeIntger( $_POST['searchDelCheck'], $effArray );
            }
            // チェックボックス状況の配列を2進数に直したうえで10進数にして返す
            $effFlag = $this->getStateFlag($effArray);
            // 一覧表用検索項目
            $searchArray = array(
                    'is_del'                     => 0,
                    'organizationID'             => '',
                    'laborRegulationsID'         => '',
                    'overtimeSetting'            => '',
                    'legalTimeInOvertime'        => '',
                    'legalTimeOutOvertime'       => '',
                    'fixedOvertime'              => '',
                    'legalHolidayAllowance'      => '',
                    'prescribedHolidayAllowance' => '',
                    'lateAtNightOutOvertime'     => '',
                    'breakTimeAcquisition'       => '',
                    'lateAtNightStart'           => '',
                    'lateAtNightEnd'             => '',
                    'comment'                    => '',
                    'sort'                       => 2,
                );
            if( !empty($_POST['searchOrganizationID']) )
            {
                $searchArray = array(
                    'is_del'                     => 0,
                    'organizationID'             => parent::escStr( $_POST['searchOrganizationID'] ),
                    'laborRegulationsID'         => parent::escStr( $_POST['searchLaborRegulationsName'] ),
                    'overtimeSetting'            => parent::escStr( $_POST['searchOvertimeSetting'] ),
                    'legalTimeInOvertime'        => parent::escStr( $_POST['searchLegalTimeInOvertime'] ),
                    'legalTimeOutOvertime'       => parent::escStr( $_POST['searchLegalTimeOutOvertime'] ),
                    'fixedOvertime'              => parent::escStr( $_POST['searchFixedOvertime'] ),
                    'legalHolidayAllowance'      => parent::escStr( $_POST['searchLegalHolidayAllowance'] ),
                    'prescribedHolidayAllowance' => parent::escStr( $_POST['searchPrescribedHolidayAllowance'] ),
                    'lateAtNightOutOvertime'     => parent::escStr( $_POST['searchLateAtNightOutOvertime'] ),
                    'breakTimeAcquisition'       => parent::escStr( $_POST['searchBreakTimeAcquisition'] ),
                    'lateAtNightStart'           => parent::escStr( $_POST['searchLateAtNightStart'] ),
                    'lateAtNightEnd'             => parent::escStr( $_POST['searchLateAtNightEnd'] ),
                    'comment'                    => parent::escStr( $_POST['searchComment'] ),
                    'sort'                       => 2,
                );
            }
            
            // 就業規則一覧データ取得
            $laborRegAllList = $laborRegulations->getListData($searchArray, $effFlag );
            // 就業規則一覧レコード数
            $laborRegRecordCnt = count($laborRegAllList);
            // 表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 指定ページ
            $pageNo = $this->getDuringTransitionPageNo($laborRegRecordCnt, $pagedRecordCnt);
            // シーケンスIDName
            $idName = "labor_regulations_id";
            // 一覧表
            $laborRegList = $this->refineListDisplayNoSpecifiedPage($laborRegAllList, $idName, $pagedRecordCnt, $pageNo);
            $laborRegList = $this->modBtnDelBtnDisabledCheck($laborRegList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($laborRegList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ページ数
            $pagedCnt = ceil($laborRegRecordCnt /  $pagedRecordCnt);

            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // ソートマーク初期化
            $headerArray = $this->changeHeaderItemMark(0);
            // ソートがを選択されたかどうかのチェックフラグ（laborRegulationsTablePanel.htmlにて使用）
            $laborRegNoSortFlag = false;
            // NO表示用
            $display_no =0;

            $Log->trace("END   initialDisplay");
            require_once './View/LaborRegulationsPanel.html';
        }

        /**
         * 就業規則テーブル画面
         * @note     パラメータは、View側で使用
         * @param    $addFlag           新規登録フラグ true：新規登録  false：新規登録以外
         * @param    $messege           DBの更新結果(データ指定がない場合、デフォルト値[MSG_BASE_0000]を設定)
         * @return   無
         */
        protected function initialList( $addFlag, $messege = "MSG_BASE_0000")
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START initialList");
            
            $laborRegulations = new laborRegulations();

            // 深夜時間の設定時間の修正
            $searchLateNightStart = $this->setLateAtNighTime( parent::escStr( $_POST['searchLateAtNightStart'] ) );
            $searchLateAtNightEnd = $this->setLateAtNighTime( parent::escStr( $_POST['searchLateAtNightEnd'] ) );

            $searchArray = array(
                'is_del'                      => 0,
                'organizationID'              => parent::escStr( $_POST['searchOrganizationID'] ),
                'laborRegulationsID'          => parent::escStr( $_POST['searchLaborRegulationsName'] ),
                'overtimeSetting'             => parent::escStr( $_POST['searchOvertimeSetting'] ),
                'legalTimeInOvertime'         => parent::escStr( $_POST['searchLegalTimeInOvertime'] ),
                'legalTimeOutOvertime'        => parent::escStr( $_POST['searchLegalTimeOutOvertime'] ),
                'fixedOvertime'               => parent::escStr( $_POST['searchFixedOvertime'] ),
                'legalHolidayAllowance'       => parent::escStr( $_POST['searchLegalHolidayAllowance'] ),
                'prescribedHolidayAllowance'  => parent::escStr( $_POST['searchPrescribedHolidayAllowance'] ),
                'lateAtNightOutOvertime'      => parent::escStr( $_POST['searchLateAtNightOutOvertime'] ),
                'breakTimeAcquisition'        => parent::escStr( $_POST['searchBreakTimeAcquisition'] ),
                'lateAtNightStart'            => $searchLateNightStart,
                'lateAtNightEnd'              => $searchLateAtNightEnd,
                'comment'                     => parent::escStr( $_POST['searchComment'] ),
                'sort'                        => parent::escStr( $_POST['sortConditions'] ),
            );

            // シーケンスIDName
            $idName = "labor_regulations_id";
            // 適用状態の表示フラグ
            $effArray = array();
            $this->booleanTypeChangeIntger( $_POST['searchApplyCheck'], $effArray );
            $this->booleanTypeChangeIntger( $_POST['searchApplySchCheck'], $effArray );
            $this->booleanTypeChangeIntger( $_POST['searchNonApplyCheck'], $effArray );
            $this->booleanTypeChangeIntger( $_POST['searchDelCheck'], $effArray );
            $effFlag = $this->getStateFlag($effArray);

            // 就業規則一覧データ取得
            $laborRegSearchList = $laborRegulations->getListData($searchArray, $effFlag);
            // 就業規則一覧検索後のレコード数
            $laborRegSrcListCnt = count($laborRegSearchList);
            // 就業規則一覧表示レコード数
            $pagedRecordCnt = $_SESSION["DISPLAY_RECORD_CNT"];
            // 検索結果後の就業規則レコードに対するページ数
            $pagedCnt = ceil($laborRegSrcListCnt /  $pagedRecordCnt);

            if($addFlag)
            {
                // 新規追加後の最新データ就業規則IDを取得
                $laborRegLastId = $laborRegulations->getLastid();
                $this->pageNoWhenUpdating($laborRegSearchList, $idName, $laborRegLastId, $pagedRecordCnt, $pagedCnt);
            }

            // ページ数が変わった場合の対応
            $this->setSessionParameterPageNoDel($pagedCnt);
            
            // 指定ページ
            $pageNo = $_SESSION["PAGE_NO"];

            // ページ部分に対応させたリスト
            $laborRegList = $this->refineListDisplayNoSpecifiedPage($laborRegSearchList, $idName, $pagedRecordCnt, $pageNo);
            $laborRegList = $this->modBtnDelBtnDisabledCheck($laborRegList);

            //-- 2016/09/19 Y.Sugou ------------------------------------->
            // 表示レコード数が11以上の場合、スクロールバーを表示
            $isScrollBar = false;
            if( $_SESSION["DISPLAY_RECORD_CNT"] >= 11 && count($laborRegList) >= 11)
            {
                $isScrollBar = true;
            }
            //--------------------------------------------------------------->

            // ページング部分「一つ前に戻る」ボタンと「一つ進む」ボタンの位置をセット
            $positionArray = $this->checkPrevBtnNextBtnPosition($pageNo, $pagedCnt);
            // 表示数リンクのロック処理
            $recordCntRockArray = $this->recordLinkRock($pagedRecordCnt);
            // ソート時のマーク変更メソッド
            $headerArray = $this->changeHeaderItemMark( $searchArray['sort'] );

            $laborRegNoSortFlag = $searchArray['sort'] % 2 == 1 ? true : false;
            
            $display_no = $this->getDisplayNo( $laborRegSrcListCnt, $pagedRecordCnt, $pageNo, $searchArray['sort'] );

            // SQLの実行結果に合わせて、出力内容を変更
            if( $messege === "MSG_BASE_0000" )
            {
                // 成功一覧表更新
                require_once './View/LaborRegulationsTablePanel.html';
            }
            else
            {
                // エラーメッセージを表示
                echo( $Log->getMsgLog($messege) );
            }
            $Log->trace("END   initialList");
        }

        /**
         * ヘッダー部分ソート時のマーク変更
         * @note     ソート番号により、ソートマークを設定する
         * @param    $sortNo ソート条件番号
         * @return   $headerArray (各ヘッダー部分のソート（△/▽）マーク)
         */
        protected function changeHeaderItemMark( $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START changeHeaderItemMark");
            
            // 初期化
            $headerArray = array(
                    'laborRegulationsNoSortMark'                          => '',
                    'laborRegulationsStateSortMark'                       => '',
                    'laborRegulationsOrganizationSortMark'                => '',
                    'laborRegulationsNameSortMark'                        => '',
                    'laborRegulationsApplicationDateStartSortMark'        => '',
                    'laborRegulationsOvertimeSettingSortMark'             => '',
                    'laborRegulationsLegalTimeInOvertimeSortMark'         => '',
                    'laborRegulationsLegalTimeOutOvertimeSortMark'        => '',
                    'laborRegulationsFixedOvertimeSortMark'               => '',
                    'laborRegulationsLegalHolidayAllowanceSortMark'       => '',
                    'laborRegulationsPrescribedHolidayAllowanceSortMark'  => '',
                    'laborRegulationsLateAtNightTimeSortMark'             => '',
                    'laborRegulationsLateAtNightOutOvertimeSortMark'      => '',
                    'laborRegulationsBreakTimeAcquisitionSortMark'        => '',
                    'laborRegulationsCommentSortMark'                     => '',
                    'laborRegulationsDispOrderSortMark'                   => '',
            );
            if(!empty($sortNo))
            {
                $sortList[1] = "laborRegulationsNoSortMark";
                $sortList[2] = "laborRegulationsNoSortMark";
                $sortList[3] = "laborRegulationsStateSortMark";
                $sortList[4] = "laborRegulationsStateSortMark";
                $sortList[5] = "laborRegulationsOrganizationSortMark";
                $sortList[6] = "laborRegulationsOrganizationSortMark";
                $sortList[7] = "laborRegulationsNameSortMark";
                $sortList[8] = "laborRegulationsNameSortMark";
                $sortList[9] = "laborRegulationsApplicationDateStartSortMark";
                $sortList[10] = "laborRegulationsApplicationDateStartSortMark";
                $sortList[11] = "laborRegulationsOvertimeSettingSortMark";
                $sortList[12] = "laborRegulationsOvertimeSettingSortMark";
                $sortList[13] = "laborRegulationsLegalTimeInOvertimeSortMark";
                $sortList[14] = "laborRegulationsLegalTimeInOvertimeSortMark";
                $sortList[15] = "laborRegulationsLegalTimeOutOvertimeSortMark";
                $sortList[16] = "laborRegulationsLegalTimeOutOvertimeSortMark";
                $sortList[17] = "laborRegulationsFixedOvertimeSortMark";
                $sortList[18] = "laborRegulationsFixedOvertimeSortMark";
                $sortList[19] = "laborRegulationsLegalHolidayAllowanceSortMark";
                $sortList[20] = "laborRegulationsLegalHolidayAllowanceSortMark";
                $sortList[21] = "laborRegulationsPrescribedHolidayAllowanceSortMark";
                $sortList[22] = "laborRegulationsPrescribedHolidayAllowanceSortMark";
                $sortList[23] = "laborRegulationsLateAtNightTimeSortMark";
                $sortList[24] = "laborRegulationsLateAtNightTimeSortMark";
                $sortList[25] = "laborRegulationsLateAtNightOutOvertimeSortMark";
                $sortList[26] = "laborRegulationsLateAtNightOutOvertimeSortMark";
                $sortList[27] = "laborRegulationsBreakTimeAcquisitionSortMark";
                $sortList[28] = "laborRegulationsBreakTimeAcquisitionSortMark";
                $sortList[29] = "laborRegulationsCommentSortMark";
                $sortList[30] = "laborRegulationsCommentSortMark";
                $sortList[31] = "laborRegulationsDispOrderSortMark";
                $sortList[32] = "laborRegulationsDispOrderSortMark";
                $headerArray = $this->Sort->sortMarkInsert($headerArray, $sortNo, $sortList);
            }

            $Log->trace("END changeHeaderItemMark");

            return $headerArray;
        }
    }
?>
