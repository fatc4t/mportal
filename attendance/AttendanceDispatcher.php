<?php
    /**
     * @file      勤怠管理振り分けクラス
     * @author    USE Y.Sakata
     * @date      2016/06/22
     * @version   1.00
     * @note      リクエストを各コントローラへ振り分ける
     */

    // システムパラメータファイル
    require_once '../../local_security/mportal/attendance/SystemParameters.php';
    // メインコントローラ(index.phpからのみ参照)
    require_once SystemParameters::$FW_COMMON_PATH . 'Dispatcher.php';

    // 勤怠システムの振分け処理クラス
    class AttendanceDispatcher extends Dispatcher
    {
        /**
         * リクエストの振り分け処理
         */
        public function directAccessDispatch()
        {
            // パラメーター取得（末尾の / は削除）
            $param = isset($_GET['param']) ? htmlspecialchars($_GET['param']) : null;

            // パラメータなしの場合、エラーページ
            if( $param == "" )
            {
                header("HTTP/1.0 404 Not Found");
            }
            
            $params = array();
            if ('' != $param)
            {
                // パラメーターを / で分割
                $params = explode('/', $param);
            }

            // １番目のパラメーターをコントローラーとして取得
            $controller = 'index';
            if (0 < count($params))
            {
                $controller = $params[0];
            }

            // パラメータより取得したコントローラー名によりクラス振分け
            $className = $controller . 'Controller';
            
            // ファイル名作成
            $classFileName = './Controllers/' . $className . '.php';

            // コントローラが存在しているか
            if( !file_exists($classFileName) ) {
                header("HTTP/1.0 404 Not Found");
            }
            else
            {
                // クラスファイル読込
                require_once $classFileName;
                
                // クラスインスタンス生成
                $controllerInstance = new $className();
                
                // 2番目のパラメーターをメソッドとして取得
                $action= 'index';
                if (1 < count($params)) {
                        $action= $params[1];
                }

                // アクションメソッドを実行
                $actionMethod = $action . 'Action';
                $controllerInstance->$actionMethod();
            }
        }
    }
?>
