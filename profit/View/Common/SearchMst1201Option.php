<?php
    /**
     * @file      POS商品マスタ　商品部門エリア
     * @author    川橋
     * @date      2018/11/28
     * @version   1.00
     * @note      POS商品マスタ　商品部門エリア
     */
?>
                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                    <!-- ajax差し替えエリア -->
                    <?php
                        $lbl_sect_tax = "";
                        $sect_tax_type = "";
                        $sect_tax = "";
                    ?>
                    <div id="jquery-replace-SearchMst1201Option-ajax">
                        <table style="margin-top:5px; margin-bottom:0px; margin-left:0px; margin-right:auto; table-layout:fixed;">
                            <tbody>
                                <tr>
                                    <th style="width:150px;">部門&nbsp;<span style="color:#ff0000;">＊</span></th>
                                    <td style="width:350px; border-width:1px 1px 1px 1px;">
                                        <select title="部門" id="sect_cd" name="sect_cd" style="width:340px;" required  tabindex="8" onchange="sect_cd_onChange(this);">
                                            <option value=""></option>
                                        <?php foreach($mst1201DataList as $mst1201) { ?>
                                            <?php
                                                $selected = '';
                                                if ($mst1201['organization_id'] !== $mst0201DataList['organization_id']) {
                                                    continue;
                                                }
                                                if ($mst1201['sect_cd'] == $mst0201DataList['sect_cd']) {
                                                    $selected = 'selected=""';

                                                    $sect_tax_type  = $mst1201['sect_tax_type'];
                                                    $sect_tax       = $mst1201['sect_tax'];
                                                    switch ($sect_tax_type) {
                                                        case '1':
                                                            $lbl_sect_tax = '外税（'.number_format($sect_tax).'％）';
                                                            break;
                                                        case '2':
                                                            $lbl_sect_tax = '内税（'.number_format($sect_tax).'％）';
                                                            break;
                                                        case '9':
                                                            $lbl_sect_tax = '非課税';
                                                            break;
                                                        default:
                                                            $lbl_sect_tax = '';
                                                            break;
                                                    }
                                                }
                                            ?>
                                            <option value="<?php hsc($mst1201['sect_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst1201['sect_nm']); ?></option>
                                        <?php } ?>
                                        </select>
                                    </td>
                                    <td style="width:100px; text-align:center; border-width:1px 1px 1px 1px; color:#FFFFFF; font-weight:bold; background-color:#0D0D70;">
                                        <div id="lbl_sect_tax" name="lbl_sect_tax"><?php hsc($lbl_sect_tax); ?></div>
                                    </td>
                                    <input type="hidden" id="sect_tax_type" name="sect_tax_type" value="<?php hsc($sect_tax_type); ?>">
                                    <input type="hidden" id="sect_tax" name="sect_tax" value="<?php hsc($sect_tax); ?>">
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.jquery-replace-SearchMst1201Option-ajax -->
