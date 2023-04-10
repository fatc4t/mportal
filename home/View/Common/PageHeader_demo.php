<?php
    /**
     * @file      ページ共通ヘッダ(View)
     * @author    USE Y.Sakata
     * @date      2016/04/27
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
                <li><a href="#"><?php hsc( $_SESSION["SECURITY_NAME"] ); ?></a></li>
                <li><a href="#"><?php hsc( $_SESSION["USER_NAME"] ); ?></a></li>
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
                <li class="sb-close"><a href="/home/index.php?param=Home/show&home=1">トップページ</a></li>

                <div onclick="drop_menu();">
                    <li class="sb-close_menu"><a>売上管理</a></li>
                </div>
                <div id="drop_menu">
                    <div onclick="drop_menu1();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;売上報告書</a></li>
                    </div>
                    <div id="drop_menu1">
                                                        <li class="sb-close"><a href="/profit/index.php?param=SalesReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次報告書 - 売上報告書</a></li>
<!--                                                        <li class="sb-close"><a href="/profit/index.php?param=CostReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次報告書 - コスト</a></li> -->
                                                        <li class="sb-close"><a href="/profit/index.php?param=OrderReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次報告書 - 仕入れ</a></li>
<!--                                                        <li class="sb-close"><a href="/profit/index.php?param=LaborReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次報告書 - 人件費</a></li> -->
                                                        <li class="sb-close"><a href="/profit/index.php?param=CostReportListMonth/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;月次報告書 - コスト</a></li>
                                                        <li class="sb-close"><a href="/profit/index.php?param=TergetReportListDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;目標       - 日次</a></li>
                                                        <li class="sb-close"><a href="/profit/index.php?param=TergetReportListMonth/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;目標       - 月次</a></li>
                    </div>
                    <div onclick="drop_menu2();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;売上帳票</a></li>
                    </div>
                    <div id="drop_menu2">
                                                        <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetDay/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;帳票</a></li>
                    </div>
                    <div onclick="drop_menu3();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;売上メンテナンス</a></li>
                    </div>
                    <div id="drop_menu3">
                                                    <li class="sb-close"><a href="/profit/index.php?param=PosBrand/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;POS種別名</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=PosKeyFile/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;POSキーファイル</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=MappingName/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;マッピング名</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=PosMapping/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;日次マッピング設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=PosMappingItem/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品別マッピング設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=PosMappingTime/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;時間別マッピング設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=ReportForm/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;報告書設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=LedgerSheetForm/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;帳票設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=Void/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;VOID設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=TaxRate/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;税率設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=TimeZone/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;時間帯設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=Department/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;部門設定</a></li>
                                                    <li class="sb-close"><a href="/profit/index.php?param=Menu/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;商品設定</a></li>
                    </div>
                </div>
                                    
                <div onclick="drop_menu4();">
                    <li class="sb-close_menu"><a>勤怠管理</a></li>
                </div>
                <div id="drop_menu4">
                    <div onclick="drop_menu5();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;勤怠機能</a></li>
                    </div>
                    <div id="drop_menu5">
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceRecord/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;勤怠一覧画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceCorrection/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;勤怠修正画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=AttendanceApproval/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;勤怠承認画面</a></li>

                    </div>
                    <div onclick="drop_menu6();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;シフト作成機能</a></li>
                    </div>
                    <div id="drop_menu6">

                                                    <li class="sb-close"><a href="/attendance/index.php?param=ShiftList/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;シフト一覧画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=ShiftDlUp/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;シフトインポート画面</a></li>

                    </div>
                    <div onclick="drop_menu7();">
                        <li class="sb-close_menu"><a>&nbsp;&nbsp;&nbsp;&nbsp;勤怠メンテナンス</a></li>
                    </div>
                    <div id="drop_menu7">

                                                    <li class="sb-close"><a href="/attendance/index.php?param=Section/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;セクションマスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=HolidayName/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;休日名称マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=Holiday/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;休日マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=Allowance/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;手当マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=LaborRegulations/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;就業規則マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=OrganizationCalendar/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;組織カレンダーマスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=Alert/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;アラートマスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=DisplayItem/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;表示項目設定マスタ画面</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=PayrollSystem/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;給与システム連携マスタ</a></li>
                                                    <li class="sb-close"><a href="/attendance/index.php?param=PayrollDataOut/show&home=1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;給与データ出力画面</a></li>

                    </div>
                </div>

                <div onclick="drop_menu8();">
                    <li class="sb-close_menu"><a>ワークフロー</a></li>
                </div><!-- /.drop_menu9() -->
                <div id="drop_menu8">
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/workflow/apply.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー申請</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/workflow/apply_agree.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;ワークフロー承認・参照</a></li>
                </div><!-- /.drop_menu8 -->
                <div onclick="drop_menu9();">
                    <li class="sb-close_menu"><a>スケジュール</a></li>
                </div><!-- /.drop_menu9() -->
                <div id="drop_menu9">
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/schedule/mon.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;月</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/schedule/week.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;週</a></li>
                                                    <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/schedule/day.php?pw=demo&home=1">&nbsp;&nbsp;&nbsp;&nbsp;日</a></li>
                </div><!-- /.drop_menu -->

                <li class="sb-close"><a href="http://122.249.156.186:8080/mportal/group/notice/notice_list.php?pw=demo&home=1">通知</a></li>
                                    
                <div onclick="drop_menu10();">
                    <li class="sb-close_menu"><a>管理者メンテナンス</a></li>
                </div><!-- /.drop_menu() -->
                <div id="drop_menu10">
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
    </div>
    <!-- /.sb-left -->

    <!-- 右メニュー封印 -->
    <?php // include("../FwCommon/FwRightMenuList.php"); ?>
