<?php
    /**
     * @file      パンくずリスト(View)
     * @author    USE Y.Sakata
     * @date      2016/06/03
     * @version   1.00
     * @note      パンくずリスト
     */
?>

            <!-- menuNameArea -->
            <div class="menuNameArea">
                <h2>
                    <a href="#"><span class="menu"></span></a>
                    <?php 
                        // 現在のパスを設定
                        $path = $_SERVER['REQUEST_URI'];
                        
                        // 機能基準パスを設定
                        // パラメータ取得
                        $param = isset( $_GET['param'] ) ? parent::escStr( $_GET['param'] ) : null;

                        // パラメータ分割
                        $params = array();
                        if ('' != $param)
                        {
                            // パラメーターを / で分割
                            $params = explode('/', $param);
                        }

                        // 一覧のパスを取得
                        $pathShow = SystemParameters::$V_A_BASE_PASH . $params[0] . '/show';
                        
                        // 勤怠管理のトップパスを設定
                        $breadcrumns[SystemParameters::$V_A_TOP] = $_SESSION["A_TOP_MENU"][SystemParameters::$V_A_TOP];
                        
                        if($path !== $_SESSION["A_TOP_MENU"][SystemParameters::$V_A_TOP])
                        {
                            if( array_key_exists( "$pathShow", $_SESSION["A_MANAGEMENT_MENU"] ) )
                            {
                                $breadcrumns["#"] = "勤怠メンテナンス";
                                $breadcrumns[$pathShow] = $_SESSION["A_MANAGEMENT_MENU"][$pathShow];
                            }
                            if( array_key_exists( "$pathShow", $_SESSION["A_TIME_MENU"] ) )
                            {
                                $breadcrumns["#"] = "勤怠機能";
                                // 勤怠一覧画面以外の場合、勤怠一覧を間に追加する
                                if( SystemParameters::$V_A_TIME_LIST != $pathShow )
                                {
                                    $breadcrumns[SystemParameters::$V_A_TIME_LIST] = $_SESSION["A_TIME_MENU"][SystemParameters::$V_A_TIME_LIST];
                                }
                                $breadcrumns[$pathShow] = $_SESSION["A_TIME_MENU"][$pathShow];
                            }
                            if( array_key_exists( "$pathShow", $_SESSION["A_SHIFT_MENU"] ) )
                            {
                                $breadcrumns["#"] = "シフト作成機能";
                                $breadcrumns[$pathShow] = $_SESSION["A_SHIFT_MENU"][$pathShow];
                            }

                            // 入力画面用のパスを作成
                            $inputPath = explode('&', $path);

                            if( array_key_exists( $inputPath[0], $_SESSION["A_INPUT_MENU"] ) )
                            {
                                $breadcrumns[$path] = $_SESSION["A_INPUT_MENU"][$inputPath[0]];
                            }
                        }
                        
                        $screenName = '';
                        $first = true;
                        foreach($breadcrumns as $key => $val)
                        {
                            if( $first )
                            {
                                // 最初は、「>」を表示しない
                                $first = false;
                            }
                            else
                            {
                                echo('<span style="color:#ffffff;"> > </span>');
                            }
                            
                            if( $key != '#' )
                            {
                                echo("<a href=\"{$key}&home=1\">{$val}</a>");
                            }
                            else
                            {
                                echo('<span style="color:#ffffff;">' . $val . '</span>');
                            }

                            // 画面の最後のみ保存する（ダイアログ表示用）
                            $screenName = $val;
                        }

                    ?>
                </h2>
            </div><!-- /.menuNameArea -->

