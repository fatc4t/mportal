<?php
    /**
     * @file    ページコントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/06/15
     * @version 1.00
     * @note    各画面で共通使用するページングの処理を定義
     */

    // FwBaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseClass.php';

    /**
     * ページコントローラ
     * @note    各画面で共通使用するページングの処理を定義
     */
    class PageController extends FwBaseClass
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
         * 画面遷移時指定ページ数変更
         * @note      各画面に遷移した際に一覧データの数や表示できるページ数を判定し、セッションで持っている指定ページ数に満たない場合には書き換える
         * @param     $recordCnt            一覧データのレコード数  
         * @param     $displayRecordCnt     指定表示レコード数
         * @return    $pageNo               ページ数
         */
        public function getDuringTransitionPageNo($recordCnt, $displayRecordCnt)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getDuringTransitionPageNo");

            if($_SESSION['PAGE_NO'] <= 1)
            {
                $pageNo = 1;
            }
            else if($_SESSION['PAGE_NO'] > 1 && $recordCnt < (($_SESSION['PAGE_NO'] - 1) * $displayRecordCnt))
            {
                $pageNo = 1;
            }
            else
            {
                $pageNo = $_SESSION['PAGE_NO'];
            }

            $Log->trace("END getDuringTransitionPageNo");

            return $pageNo;
        }

        /**
         * データ登録後の指定ページの変更
         * @note      データを登録したことにより、新たに追加した情報が表示されているページを指定
         * @param     $dataList     各マスタまたはテーブルの一覧
         * @param     $last_id      新規追加後のラストID
         * @param     $recordCnt    レコード表示数
         * @param     $pageCnt      総ページ数
         * @return    無
         */
        public function pageNoWhenUpdating( $dataList, $id_name, $last_id, $recordCnt, $pageCnt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START pageNoWhenUpdating");

            $newDataPosition = $this->getNewDataPosition( $dataList, $id_name, $last_id );

            $this->setSessionParameterPageNoAdd($newDataPosition, $recordCnt, $pageCnt);
            
            $Log->trace("END pageNoWhenUpdating");
        }

        /**
         * データ削除後の指定ページの変更
         * @note      データを削除したことにより、最大ページ数が変わった場合に一つ前のページを指定
         * @param     $cnt          最大ページ数
         * @return    無
         */
        public function setSessionParameterPageNoDel($cnt)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setSessionParameterPageNoDel");

            if($cnt < $_SESSION["PAGE_NO"])
            {
                $_SESSION["PAGE_NO"] = $cnt;
            }
            
            $Log->trace("END setSessionParameterPageNoDel");
        }

        /**
         * 表示レコード数と指定ページに対応させたリストの作成
         * @note     パラメータは、View側で使用
         * @param    $dataList      組織構造にて並びかえられたリスト
         * @param    $id_name       シーケンスID
         * @param    $recordCnt     レコード表示数
         * @param    $pageNo        指定ページ番号
         * @return   表示レコード数と指定ページに対応させたリスト 
         */
        public function refineListDisplayNoSpecifiedPage($dataList, $id_name, $recordCnt, $pageNo)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START refineListDisplayNoSpecifiedPage");

            // 一覧表の表示数とページに該当するデータのスタート値を求める
            if($pageNo != 1)
            {
                $displayInitialNo = ($recordCnt * ($pageNo - 1));
            }
            else
            {
                $displayInitialNo = 0;
            }
            
            $recordCnt = ($recordCnt + $displayInitialNo);

            $list = array();
            // 表示用の配列作成
            for($i = $displayInitialNo; $i < $recordCnt; $i++)
            {
                if( isset( $dataList[$i][$id_name] ) )
                {
                    if($dataList[$i][$id_name] != null)
                    {
                        $list[$i] = $dataList[$i];
                    }
                }
            }

            $Log->trace("END refineListDisplayNoSpecifiedPage");

            return $list;
        }

        /**
         * ページング一つ前へ戻るボタンと一つ進むボタンの制御の値を取得する
         * @note     パラメータは、View側で使用
         * @param    $pageNo        指定ページ番号
         * @param    $pagedCnt      総ページ数
         * @return   「一つ前へ戻る」ボタンと「一つ進む」ボタンの指定ページ番号のリスト
         */
        public function checkPrevBtnNextBtnPosition( $pageNo, $pagedCnt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkPrevBtnNextBtnPosition");

            $prevBtn = 1;
            $nextBtn = 1;

            if($pageNo <= 1)
            {
                $prevBtn = 1;
                if($pagedCnt > 1)
                {
                    $nextBtn = 2;
                }
                else
                {
                    $nextBtn = 1;
                }
            }
            else
            {
                $prevBtn = $pageNo - 1;
                if($pageNo >= $pagedCnt)
                {
                    $nextBtn = $pagedCnt;
                }
                else
                {
                    $nextBtn = $pageNo + 1;
                }
            }

            $positionArray = array(
                'prevBtn' => $prevBtn,
                'nextBtn' => $nextBtn,
            );

            $Log->trace("END checkPrevBtnNextBtnPosition");

            return $positionArray;
        }

        /**
         * 選択された表示数リンクに固定
         * @note     パラメータは、View側で使用
         * @param    $pagedRecordCnt    指定表示レコード数
         * @return   表示数へ固定するためのリンクパラメータ文のリスト
         */
        public function recordLinkRock($pagedRecordCnt)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START recordLinkRock");

            // 初期化
            $recordCntRockArray = array(
                    'recordCurrentTen'           => '',
                    'recordCurrentThirty'        => '',
                    'recordCurrentFifity'        => '',
                    'recordCurrentHundred'       => '',
            );

            $recordCntList = array(
                '10'  => "recordCurrentTen",
                '30'  => "recordCurrentThirty",
                '50'  => "recordCurrentFifity",
                '100' => "recordCurrentHundred",
            );

            $temp = "";

            if(array_key_exists($pagedRecordCnt, $recordCntList))
            {
                $temp = $recordCntList[$pagedRecordCnt];
            }

            $currentVal = "current";

            $recordCntRockArray[$temp] = $currentVal;

            $Log->trace("END recordLinkRock");

            return $recordCntRockArray;
        }
        
        /**
         * 一覧表の表示Noを取得
         * @note     一覧表のNo列の設定
         * @param    $viewListCnt      一覧表全体の件数
         * @param    $pagedRecordCnt   1ページあたりの表示件数
         * @param    $pageNo           現在の表示ページ
         * @param    $sortNo           ソートNo
         * @return   一覧表の表示No(表示ページの先頭No)
         */
        public function getDisplayNo( $viewListCnt, $pagedRecordCnt, $pageNo, $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getDisplayNo");

            $displayNo = 0;
            if( $sortNo % 2 == 1)
            {
                if($pageNo <= 1)
                {
                    $displayNo = $viewListCnt;
                }
                else
                {
                    $displayNo = ($viewListCnt - ($pagedRecordCnt * ($pageNo - 1)));
                }
            }
            else
            {
                if($pageNo <= 1)
                {
                    $displayNo = 0;
                }
                else
                {
                    $displayNo = ($pagedRecordCnt * ($pageNo - 1));
                }
            }

            $Log->trace("END   getDisplayNo");
            
            return $displayNo;
        }
        
        /**
         * データ登録後の指定ページの変更
         * @note     データを登録したことにより、新たに追加した情報が表示されているページを指定
         * @param    $newDataPosition   新規データの位置
         * @param    $recordCnt         レコード表示数
         * @param    $pageCnt           総ページ数
         * @return   無
         */
        private function setSessionParameterPageNoAdd($newDataPosition, $recordCnt, $pageCnt)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setSessionParameterPageNoAdd");

            if($newDataPosition <= $recordCnt)
            {
                $_SESSION["PAGE_NO"] = 1;
            }
            else
            {
                $punctuation = 0;
                for($i = 1; $i <= $pageCnt; $i++)
                {
                    $punctuation = ($recordCnt * $i);
                    if($newDataPosition <= $punctuation)
                    {
                        $_SESSION["PAGE_NO"] = $i;
                        break;
                    }
                }
            }
            
            $Log->trace("END setSessionParameterPageNoAdd");
        }

        /**
         * 新規追加されたデータのリスト内の位置を取得
         * @note     パラメータは新規追加情報を表示させる位置を特定させるために使用
         * @param    $array         一覧リスト 
         * @param    $id_name       シーケンスID名
         * @param    $last_id       新規追加後のラストID
         * @return   リスト内の位置を示す数字
         */
        private function getNewDataPosition($array, $id_name, $last_id)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getNewDataPosition");

            $count = 1;
            foreach($array as $data)
            {
                if($data[$id_name] == $last_id)
                {
                    $Log->trace("END getNewDataPosition");
                    return $count;
                }
                $count++;
            }
            
            $Log->trace("END getNewDataPosition");
        }

    }
?>
