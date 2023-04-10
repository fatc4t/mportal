<?php
    /**
     * @file    FW共通コントローラ(Controller)
     * @author  USE Y.Sakata
     * @date    2016/06/22
     * @version 1.00
     * @note    FW共通コントローラの処理を定義
     */

    // BaseClassの設定
    require_once SystemParameters::$FW_COMMON_PATH . 'FwBaseClass.php';
    // Tokenの設定
    require_once '../FwCommon/Controllers/Token.php';
    // ページ処理の設定
    require_once '../FwCommon/Controllers/PageController.php';
    // ソート処理の設定
    require_once '../FwCommon/Controllers/SortController.php';

    require_once '../FwCommon/Model/FwSecurityProcess.php';
   
    /**
     * 各コントローラの基本クラス
     * @note    共通で使用するコントローラの処理を定義
     */
    class FwBaseController extends FwBaseClass
    {
        protected $TokenID = "";    ///< トークンID
        protected $Page = null;     ///< ページコントローラ
        protected $Sort = null;     ///< ソートコントローラ

        /**
         * コンストラクタ
         */
        public function __construct()
        {
            // FwBaseClassのコンストラクタ
            parent::__construct();
            
            // 初期化(セッション開始)
            session_start();
            // クリックジャッキング対策
            header('X-FRAME-OPTIONS:DENY');
            
            // セッションチェック
            if( !$this->isSessionOK() )
            {
                header('Location:' . SystemParameters::$LOGOUT_PATH);
                exit;
            }
            
            // トークンチェック
            if( !$this->isTokenOK() )
            {
                header('Location:' . SystemParameters::$LOGOUT_PATH);
                exit;
            }
            
            // アクセス権限チェック
            if( !$this->isPermissions() )
            {
                $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_ACCESS_FRAUD";
                header('Location:' . SystemParameters::$LOGOUT_PATH);
                exit;
            }
            
            $this->Page = new PageController();
            $this->Sort = new SortController();
        }
        
        /**
         * デストラクタ
         */
        public function __destruct()
        {
            // ログクラス開放処理
            global $Log;  // グローバル変数宣言
            if( !is_null( $Log ) )
            {
                $Log->clearLog();
                $Log = null;
            }
            
            $this->Page = null;
            $this->Sort = null;
            
            // FwBaseClassのデストラクタ
            parent::__destruct();
        }

        /**
         * エスケープ処理 (セキュリティ対策)
         * @note      コントローラからモデルへ値を渡す際に使用する
         * @param     $str   エスケープ対象文字列
         * @return    エスケープ終了後の文字列
         */
        public function escStr( $str )
        {
            // NULLバイト攻撃&SQLインジェクション対策
            $ret = $this->delNullStr( $str );
            return $this->pgEscapeStr( $ret );
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
         * 適用状態フラグ作成
         * @note     一覧表絞り込みに使用
         * @param    $stateCheckArray(チェックボックスのチェック有無リスト(チェック無：0/チェック有：1))
         * @return   $stateFlag
         */
        protected function getStateFlag($stateCheckArray)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START getStateFlag");

            $binaryNumber = "";
            // チェックボックスのチェック有無（0/1を結合させて2進数表記にする）
            foreach($stateCheckArray as $state)
            {
                $binaryNumber .= $state;
            }

            // 作成された2進数を10進数の直してフラグとする
            $stateFlag = bindec($binaryNumber);

            $Log->trace("END getStateFlag");

            return $stateFlag;
        }

        /**
         * 入力画面からPOSTされた配列を整理
         * @note     登録時に使用
         * @param    $undisposedArray
         * @return   $escapeArray
         */
        protected function toOrganizeAnArray($undisposedArray, $columnName, $flag = true)
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START toOrganizeAnArray");

            $unique = array();
            if(!empty($flag))
            {
                // 配列で重複している物を削除する
                $unique = array_unique($undisposedArray);
            }
            else
            {
                $unique = $undisposedArray;
            }
            // 配列の空要素を削除する
            $unStrlenArray = array_filter($unique, "strlen");
            //キーが飛び飛びになっているので、キーを振り直す
            $liquidationArray = array_values($unStrlenArray);

            $escapeArray = array();
            foreach($liquidationArray as $liquidation)
            {
                $escape[$columnName] = $this->escStr($liquidation);
                array_push($escapeArray, $escape);
            }

            $Log->trace("END toOrganizeAnArray");

            return $escapeArray;
        }

        /**
         * 更新対象に登録されている所属組織が削除権限対象か編亭する
         * @param    $organization_id
         * @return   $intarray
         */
        protected function checkDeleteTarget($organization_id)
        {
            global $Log; // グローバル変数宣言
            $Log->trace("START checkDeleteTarget");

            $del_disabled = "disabled";
            foreach($_SESSION["DELETE"] as $delete)
            {
                if($delete['organization_id'] === $organization_id)
                {
                    $del_disabled = "";
                }
            }

            $Log->trace("END checkDeleteTarget");

            return $del_disabled;
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
            $list = array();
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

        /**
         * NULLバイト攻撃対策
         * @note      NULL文字を空白へ変換
         * @param     $str   エスケープ対象文字列
         * @return    変換後の文字列
         */
        private function delNullStr( $str )
        {
            return str_replace( "\0", "", $str );
        }
        
        /**
         * SQLインジェクション対策
         * @note      DBのエスケープ処理
         * @param     $str   エスケープ対象文字列
         * @return    エスケープ終了後の文字列
         */
        private function pgEscapeStr( $str )
        {
            return pg_escape_string($str);
        }
        
        /**
         * セッションの有効期限チェック
         * @note      セッション有効期限のチェック
         * @return    セッションの有効の有無
         */
        private function isSessionOK()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START isSessionOK");
            
            $fwSecurityProcess = new FwSecurityProcess();
            
            // ログイン状態である
            if( isset( $_SESSION["SESSION_TIME"] ) && !isset( $_SESSION["SECURITY_LOGOUT"] ) )
            {
                // ログイン状態のチェック
                if ( !isset( $_SESSION["USER_ID"] ) ) 
                {
                    $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_LOGIN_FRAUD";
                    $Log->error( $_SESSION["SECURITY_LOGOUT"] );
                    $Log->trace("END isSessionOK");
                    return false;
                }
                
                $sessionTimeOut = 0;

                // 企業情報を取得する
//                $companyContract = $fwSecurityProcess->getCompanyContract();

                if($companyContract)
                {
                    $sessionTimeOut = $companyContract['session_time_out'];
                }
                
                // セッションがタイムアウトしていないかチェック
                if( $_SESSION["SESSION_TIME"] < ( time() - SystemParameters::$SESSION_TIME_OUT ) )
                {
                    $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_SESSION_OUT";
                    $Log->warn( $_SESSION["SECURITY_LOGOUT"] );
                    $Log->trace("END isSessionOK");
                    return false;
                }
                else
                {
                    // セッション時間を更新
                    $_SESSION["SESSION_TIME"] = time();
                    $Log->trace("END isSessionOK");
                    return true;
                }
            }
            else if( !empty($_SERVER["REQUEST_URI"]) && strstr($_SERVER["REQUEST_URI"], 'Login/' ) )
            {
                // ログインページとエラーページアクセスされた場合
                $Log->trace("END isSessionOK");
                return true;
            }

            $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_SESSION_OUT";

            $Log->trace("END isSessionOK");
            return false;
        }
        
        /**
         * トークンの有効期限チェック
         * @note      トークン有効期限のチェック
         * @return    トークンの有効の有無
         */
        private function isTokenOK()
        {
            global $Log, $TokenID;  // グローバル変数宣言
            $Log->trace("START isTokenOK");

            $token = new Token( SystemParameters::$SESSION_TIME_OUT );
            // CSRF をチェックする必要のある処理の場合
            if ( isset( $_POST["token"] ) )
            {
                if ( empty( $_POST["token"] ) )
                {
                    // CSRF の検出(トークン未設定)
                    $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_TOKEN_NO_SET";
                    $Log->fatal( $_SESSION["SECURITY_LOGOUT"] );
                    $Log->trace("END isTokenOK");
                    return false;
                }

                if ( $token->isCSRF( $_POST["token"] ) ) 
                {
                    // CSRF の検出(トークン異常)
                    $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_TOKEN_FRAUD";
                    $Log->error( $_SESSION["SECURITY_LOGOUT"] );
                    $Log->trace("END isTokenOK");
                    return false;
                }
                
                if ( $token->isTimeOut( $_POST["token"] ) ) 
                {
                    // 期限切れの場合の処理
                    $_SESSION["SECURITY_LOGOUT"] = "MSG_FW_TOKEN_OUT";
                    $Log->warn( $_SESSION["SECURITY_LOGOUT"] );
                    $Log->trace("END isTokenOK");
                    return false;
                }
            }
            
            $TokenID = $token->createToken();

            $Log->trace("END isTokenOK");

            return true;
        }

        /**
         * アクセス制限のチェック
         * @note     URL(コントローラ名)にて、アクセス制限を行う
         * @return   URLへのアクセス許可
         */
        private function isPermissions()
        {
            global $Log;  // グローバル変数宣言
            $Log->trace("START isPermissions");
            
            // ログイン前は、アクセス権限チェックは行わない
            if( !isset( $_SESSION["ACCESS_MENU_LIST"] ) )
            {
                $Log->trace("END isPermissions");
                return true;
            }
            
            // パラメータ取得
            $param = isset( $_GET['param'] ) ? $this->escStr( $_GET['param'] ) : null;
            
            // パラメータなしは、Login画面への遷移である為、OKとする
            if( is_null( $param ) )
            {
                $Log->trace("END isPermissions");
                return true;
            }

            // パラメーターを / で分割
            $params = explode('/', $param);

            // ログイン/ログアウトは対象外
            if( $params[0] === 'Login' )
            {
                $Log->trace("END isPermissions");
                return true;
            }

            $path = explode( '?', $_SERVER["REQUEST_URI"] );
            $key = $path[0] . '?param=' . $params[0] . '/show';
            $Log->trace("END isPermissions");

            return array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] );
        }

    }
?>
