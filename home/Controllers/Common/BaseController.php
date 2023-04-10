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
         * 更新/削除ボタン権限によるロック判定
         * @note     一覧表内の修正/更新/削除ボタンの制御を、アクセス権限を見て行う
         * @param    $dataList     各マスタまたはテーブルの一覧
         * @return   一覧表内の修正/更新/削除ボタンの押下可否リスト
         */
        protected function modBtnDelBtnDisabledCheck( $dataList )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START modBtnDelBtnDisabledCheck");
            
            if( isset( $dataList[0]['organization_id'] ) )
            {
                $list = $this->setModBtnDelBtnDisabledForID( $dataList );
            }
            else
            {
                $list = $this->setModBtnDelBtnDisabled( $dataList );
            }

            $Log->trace("END modBtnDelBtnDisabledCheck");

            return $list;
        }

        /**
         * 画面遷移時指定ページ数変更
         * @note      各画面に遷移した際に一覧データの数や表示できるページ数を判定し、セッションで持っている指定ページ数に満たない場合には書き換える
         * @param     $recordCnt            一覧データのレコード数  
         * @param     $displayRecordCnt     指定表示レコード数
         * @return    $pageNo               ページ数
         */
        protected function getDuringTransitionPageNo($recordCnt, $displayRecordCnt)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getDuringTransitionPageNo");

            $pageNo = $this->Page->getDuringTransitionPageNo($recordCnt, $displayRecordCnt);

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
        protected function pageNoWhenUpdating( $dataList, $id_name, $last_id, $recordCnt, $pageCnt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START pageNoWhenUpdating");

            $this->Page->pageNoWhenUpdating( $dataList, $id_name, $last_id, $recordCnt, $pageCnt );

            $Log->trace("END pageNoWhenUpdating");
        }

        /**
         * データ削除後の指定ページの変更
         * @note      データを削除したことにより、最大ページ数が変わった場合に一つ前のページを指定
         * @param     $cnt          最大ページ数
         * @return    無
         */
        protected function setSessionParameterPageNoDel( $cnt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setSessionParameterPageNoDel");

            $this->Page->setSessionParameterPageNoDel( $cnt );

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
        protected function refineListDisplayNoSpecifiedPage( $dataList, $id_name, $recordCnt, $pageNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START refineListDisplayNoSpecifiedPage");

            $list = $this->Page->refineListDisplayNoSpecifiedPage( $dataList, $id_name, $recordCnt, $pageNo );

            $Log->trace("END refineListDisplayNoSpecifiedPage");

            return $list;
        }

        /**
         * ページング一つ前へ戻るボタンと一つ進むボタンの制御の値を取得する
         * @note     パラメータは、View側で使用
         * @param    $pageNo（指定ページ番号） $pagedCnt（総ページ数）
         * @return   「一つ前へ戻る」ボタンと「一つ進む」ボタンの指定ページ番号のリスト
         */
        protected function checkPrevBtnNextBtnPosition( $pageNo, $pagedCnt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START checkPrevBtnNextBtnPosition");

            $positionArray = $this->Page->checkPrevBtnNextBtnPosition( $pageNo, $pagedCnt );

            $Log->trace("END checkPrevBtnNextBtnPosition");

            return $positionArray;
        }

        /**
         * 選択された表示数リンクに固定
         * @note     パラメータは、View側で使用
         * @param    $pagedRecordCnt（指定表示レコード数）
         * @return   表示数へ固定するためのリンクパラメータ文のリスト
         */
        protected function recordLinkRock( $pagedRecordCnt )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START recordLinkRock");

            $recordCntRockArray = $this->Page->recordLinkRock( $pagedRecordCnt );

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
        protected function getDisplayNo( $viewListCnt, $pagedRecordCnt, $pageNo, $sortNo )
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getDisplayNo");

            $displayNo = $this->Page->getDisplayNo( $viewListCnt, $pagedRecordCnt, $pageNo, $sortNo );

            $Log->trace("END   getDisplayNo");

            return $displayNo;
        }

        /**
         * リストの中から指定のキーと比較して値を抜き出し返す
         * @note     項目一覧から指定したキーによる値の抜き出しを行う
         * @param    $key
         * @param    $itemArray
         * @return   $item
         */
        protected function getItemName($key, $itemArray)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getItemName");

            if( array_key_exists( $key, $itemArray ) )
            {
                $item = $itemArray[$key];
            }

            $Log->trace("END getItemName");

            return $item;
        }
        
        /**
         * 更新/削除ボタン権限によるロック判定（組織IDが存在する）
         * @note     一覧表内の修正/更新/削除ボタンの制御を、アクセス権限を見て行う
         * @param    $dataList     各マスタまたはテーブルの一覧
         * @return   一覧表内の修正/更新/削除ボタンの押下可否リスト
         */
        private function setModBtnDelBtnDisabledForID($dataList)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setModBtnDelBtnDisabledForID");
            
            foreach($_SESSION["REGISTRATION"] as $reg)
            {
                $regArray[] = $reg['organization_id'];
            }
            foreach($_SESSION["DELETE"] as $del)
            {
                $delArray[] = $del['organization_id'];
            }

            foreach($dataList as $data)
            {
                if(in_array($data['organization_id'], $regArray))
                {
                    $mod_disabled = "";
                }
                else
                {
                    $mod_disabled = "disabled";
                }

                if(in_array($data['organization_id'], $delArray))
                {
                    $del_disabled = "";
                }
                else
                {
                    $del_disabled = "disabled";
                }
                
                if(!empty($mod_disabled) && !empty($del_disabled))
                {
                    $cor_disabled = "disabled";
                }
                else
                {
                    $cor_disabled = "";
                }

                $disabledCheck = array('mod_disabled' => $mod_disabled, 'del_disabled' => $del_disabled, 'cor_disabled' => $cor_disabled,);

                $list[] = array_merge($data, $disabledCheck);
            }

            $Log->trace("END setModBtnDelBtnDisabledForID");
            
            return $list;
        }
        
        /**
         * 更新/削除ボタン権限によるロック判定（組織IDが存在しない）
         * @note     一覧表内の修正/更新/削除ボタンの制御を、アクセス権限を見て行う
         * @param    $dataList     各マスタまたはテーブルの一覧
         * @return   一覧表内の修正/更新/削除ボタンの押下可否リスト
         */
        private function setModBtnDelBtnDisabled($dataList)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START setModBtnDelBtnDisabled");
        
            $securityProcess = new SecurityProcess();
            // アクセス権限参照範囲を見て、組織IDを設定する
            $accessAuthorityList = $securityProcess->getAccessAuthorityList();

            $accessList = array( 1, 2, 3, 4, 5 );

            foreach($dataList as $data)
            {
                if( in_array( $accessAuthorityList['registration'], $accessList ) )
                {
                    $mod_disabled = "";
                }
                else
                {
                    $mod_disabled = "disabled";
                }

                if( in_array( $accessAuthorityList['delete'], $accessList ) )
                {
                    $del_disabled = "";
                }
                else
                {
                    $del_disabled = "disabled";
                }
                
                if(!empty($mod_disabled) && !empty($del_disabled))
                {
                    $cor_disabled = "disabled";
                }
                else
                {
                    $cor_disabled = "";
                }

                $disabledCheck = array('mod_disabled' => $mod_disabled, 'del_disabled' => $del_disabled, 'cor_disabled' => $cor_disabled,);

                $list[] = array_merge($data, $disabledCheck);
            }

            $Log->trace("END setModBtnDelBtnDisabled");

            return $list;
        }
    }
?>
