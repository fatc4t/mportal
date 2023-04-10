<?php
    /**
     * @file      ページングエリア
     * @author    USE Y.Sakata
     * @date      2016/06/15
     * @version   1.00
     * @note      共通のページングエリア
     */
?>
                <!-- PageMoveArea -->
                <div class="PageMoveArea">
                    <table>
                        <tr>
                            <td>
                                <a href="#" onClick="displayPage(1)" ><<</a>
                            </td>
                            <td>
                                <a href="#" onClick="displayPage(<?php hsc($positionArray['prevBtn']); ?>)" ><</a>
                            </td>
                            <?php
                                // 表示するページの開始と終了を確定する
                                $pageStart = 1;
                                $pageEnd   = $pagedCnt;

                                if( $pagedCnt > SystemParameters::$PAGE_AREA_MAX )
                                {
                                    // ページングの開始位置をカレントページから設定
                                    $halfPage = round( SystemParameters::$PAGE_AREA_MAX / 2 );
                                    $pageStart = ( $pageNo - $halfPage + 1 ) > 0 ? ( $pageNo - $halfPage + 1 ) : 1;
                                    $pageEnd   = ( $pageNo + $halfPage ) > $pagedCnt ? $pagedCnt : ( $pageNo + $halfPage );

                                    // 終了位置から開始位置までで、表示するページ数のMAXまであるか
                                    if( ( ( $pageEnd - $pageStart + 1 ) < SystemParameters::$PAGE_AREA_MAX ) )
                                    {
                                        if( $pageEnd == $pagedCnt )
                                        {
                                            $pageStart = $pageEnd - SystemParameters::$PAGE_AREA_MAX + 1;
                                        }
                                        else
                                        {
                                            $pageEnd = $pageStart + SystemParameters::$PAGE_AREA_MAX - 1;
                                        }
                                    }
                                    else
                                    {
                                        $pageEnd = $pageStart + SystemParameters::$PAGE_AREA_MAX - 1;
                                    }
                                }

                                for($i=$pageStart; $i <= $pageEnd; $i++) 
                                {
                                    $current = '';
                                    if( $i == $pageNo )
                                    {
                                        $current = "current";
                                    } 
                            ?>
                                    <td>
                                        <a href="#" onClick="displayPage(<?php hsc($i); ?>)" class="<?php hsc($current); ?>"><?php hsc($i); ?></a>
                                    </td>
                            <?php 
                                }
                                
                                // 最終ページが表示されていない
                                if( $pageEnd !== $pagedCnt )
                                {
                                    // ...を表示
                            ?>
                                    <td>
                                        ...
                                    </td>
                                    <td>
                                        <a href="#" onClick="displayPage(<?php hsc($pagedCnt); ?>)" class=""><?php hsc($pagedCnt); ?></a>
                                    </td>
                            <?php
                                }
                            ?>
                            <td>
                                <a href="#" onClick="displayPage(<?php hsc($positionArray['nextBtn']); ?>)" >></a>
                            </td>
                            <td>
                                <a href="#" onClick="displayPage(<?php hsc($pagedCnt); ?>)" >>></a>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2" rowspan="2">　表示数：</th>
                            <td>
                                <a href="#" onClick="displayRecord(10)" class="<?php hsc($recordCntRockArray['recordCurrentTen']); ?>">10</a>
                            </td>
                            <td>
                                <a href="#" onClick="displayRecord(30)" class="<?php hsc($recordCntRockArray['recordCurrentThirty']); ?>">30</a>
                            </td>
                            <td>
                                <a href="#" onClick="displayRecord(50)" class="<?php hsc($recordCntRockArray['recordCurrentFifity']); ?>">50</a>
                            </td>
                            <td>
                                <a href="#" onClick="displayRecord(100)" class="<?php hsc($recordCntRockArray['recordCurrentHundred']); ?>">100</a>
                            </td>
                        </tr>
                    </table>
                </div><!-- /.PageMoveArea -->
