<?php
    /**
     * @file      ページ共通ヘッダ(View)
     * @author    USE Y.Sakata
     * @date      2016/07/02
     * @version   1.00
     * @note      ページ共通ヘッダ
     */
?>

    <!-- Navbar -->
    <nav class="navbar navbar-default navbar-fixed-top sb-slide" role="navigation">
        <!-- Left Control -->
        <div class="sb-toggle-left navbar-left">
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
        </div><!-- /.sb-control-left -->
        
        <!-- Right Control -->
        <div class="sb-toggle-right navbar-right">
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
            <div class="navicon-line"></div>
        </div><!-- /.sb-control-right -->

        <div class="container">
            <!-- Logo -->
            <div id="logo" class="navbar-left">
                <a href="#"><img src="../img/top-menu-logo.png" alt="HOME" height="40"></a>
            </div><!-- /.logo -->
            
            <!-- Menu -->
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#">ﾌﾞｯｸﾏｰｸ登録</a></li>
                <li><a href="../home/index.php?param=Home/show&home=1">ホーム</a></li>
                <li><a href="#"><?php $_SESSION["SECURITY_NAME"] ?></a></li>
                <li><a href="#"><?php $_SESSION["USER_NAME"] ?></a></li>
                <li><a href="<?php hsc( SystemParameters::$LOGOUT_PATH ); ?>">ログアウト</a></li>
                <li><a id="top-arrow" href="#top">＾</a></li>
            </ul><!-- /.Menu -->
        </div>
    </nav>

    <!-- Slidebars -->
    <!-- sb-left -->
    <div class="sb-slidebar sb-left">
        <nav>
            <ul class="sb-menu">
                <li><img src="../img/right-menu-logo.png" alt="Slidebars" width="118" height="40"></li>
                <li class="sb-close"><a href="<?php hsc(SystemParameters::$V_M_HOME); ?>&home=1">トップページ</a></li>

                        <div onclick="drop_menu();">
                    <li class="sb-close_menu"><a>売上管理</a></li>
                </div><!-- /.drop_menu() -->
                        <div id="drop_menu">
                                                    <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;帳票</a></li>
                </div><!-- /.drop_menu1 -->
                                    
                <div onclick="drop_menu1();">
                    <li class="sb-close_menu"><a>勤怠管理</a></li>
                </div><!-- /.drop_menu1() -->
                <div id="drop_menu1">
                                                    <li class="sb-close"><a href="/attendance/index.php?param=TimeCorrection/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;勤怠修正画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceApproval/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;勤怠承認画面</a></li>
                </div><!-- /.drop_menu1 -->
                                    
                <div onclick="drop_menu2();">
                    <li class="sb-close_menu"><a>営業管理</a></li>
                </div><!-- /.drop_menu2() -->
                <div id="drop_menu2">
                                                    <!--<li class="sb-close"><a href="http://122.249.156.186:8080/mportal/sales/report/sales_report.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;日報</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/sales/customer/customer.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;顧客リスト</a></li>-->
                                                    <li class="sb-close"><a href="#">&nbsp;&nbsp;&nbsp;&nbsp;日報</a></li>
                                                    <li class="sb-close"><a href="#">&nbsp;&nbsp;&nbsp;&nbsp;顧客リスト</a></li>
                </div><!-- /.drop_menu2 -->

                <div onclick="drop_menu3();">
                    <li class="sb-close_menu"><a>ワークフロー</a></li>
                </div><!-- /.drop_menu3() -->
                <div id="drop_menu3">
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/workflow/apply.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー申請</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/workflow/apply_agree.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー承認・参照</a></li>
                </div><!-- /.drop_menu3 -->
                <div onclick="drop_menu4();">
                    <li class="sb-close_menu"><a>スケジュール</a></li>
                </div><!-- /.drop_menu4() -->
                <div id="drop_menu4">
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/schedule/mon.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;月</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/schedule/week.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;週</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/schedule/day.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;日</a></li>
                </div><!-- /.drop_menu3 -->

                <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/notice/notice_list.php?pw=demo&home=1">通知</a></li>
                <div onclick="drop_menu5();">
                    <li class="sb-close_menu"><a>メンテナンス</a></li>
                </div><!-- /.drop_menu() -->
                <div id="drop_menu5">
                            <?php 
                                foreach( $_SESSION["M_PORTAL_SYS_MENU"] as $key => $val )
                                {
                                    if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                                    {
                            ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                            <?php 
                                    }
                                }
                            ?>
                <?php 
                        foreach( $_SESSION["M_MANAGEMENT_MENU"] as $key => $val )
                        {
                            if( false !== array_key_exists ( $key, $_SESSION["ACCESS_MENU_LIST"] ) )
                            {
                    ?>
                                <li class="sb-close"><a href="<?php hsc($key); ?>&home=1">&nbsp;&nbsp;&nbsp;&nbsp;<?php hsc($val); ?></a></li>
                    <?php 
                            }
                        }
                    ?>
                </div><!-- /.drop_menu -->
            </ul>
        </nav>
    </div><!-- /.sb-left -->
    <?php include("../FwCommon/FwRightMenuList.php"); ?>
