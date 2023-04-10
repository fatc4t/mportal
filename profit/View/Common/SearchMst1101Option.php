<?php
    /**
     * @file      POS商品マスタ　仕入先エリア
     * @author    川橋
     * @date      2018/11/28
     * @version   1.00
     * @note      POS商品マスタ　仕入先エリア
     */
?>
                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                    <!-- ajax差し替えエリア -->
                    <div id="jquery-replace-SearchMst1101Option-ajax">
                        <table style="margin-top:0px; margin-bottom:0px; margin-left:0px; margin-right:auto; table-layout:fixed;">
                            <tbody>
                                <tr>
                                    <th style="width:150px;">仕入先</th>
                                    <td style="width:350px; border-width:0px 1px 1px 1px;">
                                        <select title="仕入先" id="supp_cd" name="supp_cd" style="width:340px;" tabindex="9">
                                            <option value=""></option>
                                            <?php foreach($mst1101DataList as $mst1101) { ?>
                                                <?php
                                                    $selected = '';
                                                    if ($mst1101['organization_id'] !== $mst0201DataList['organization_id']) {
                                                        continue;
                                                    }
                                                    if ($mst1101['supp_cd'] == $mst0201DataList['head_supp_cd']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst1101['supp_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst1101['supp_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.jquery-replace-SearchMst1101Option-ajax -->
