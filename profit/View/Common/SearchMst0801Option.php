<?php
    /**
     * @file      POS商品マスタ　商品区分(右)エリア
     * @author    川橋
     * @date      2018/12/13
     * @version   1.00
     * @note      POS商品マスタ　商品大分類、中分類、小分類、クラス
     */
?>
                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                    <!-- ajax差し替えエリア -->
                    <div id="jquery-replace-SearchMst0801Option-ajax">
                        <table style="margin-top:0; margin-left:0; margin-bottom:0; margin-right:0; table-layout:fixed;">
                            <tbody>
                                <tr>
                                    <th style="width:150px;">商品大分類</th>
                                    <td style="width:160px; border-width:1px 1px 1px 1px;">
                                        <select title="商品大分類コード" id="prod_t_cd1" name="prod_t_cd1" style="width:150px;" onChange="changeMst0801Options(this);" tabindex="87"><!-- mst0801 -->
                                            <option value=""></option>
                                            <?php foreach($mst0801DataList as $mst0801) { ?>
                                                <?php
                                                    if ($mst0801['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0801['prod_t_type'] !== '1') {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0801['prod_t_cd1'] == $mst0201DataList['prod_t_cd1']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0801['prod_t_cd1']); ?>" <?php hsc($selected); ?>><?php hsc($mst0801['prod_t_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;">商品中分類</th>
                                    <td style="width:160px; border-width:0px 1px 1px 1px;">
                                        <select title="商品中分類コード" id="prod_t_cd2" name="prod_t_cd2" style="width:150px;" onChange="changeMst0801Options(this);" tabindex="88"><!-- mst0801 -->
                                            <option value=""></option>
                                            <?php foreach($mst0801DataList as $mst0801) { ?>
                                                <?php
                                                    if ($mst0801['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0801['prod_t_type'] !== '2' || 
                                                        $mst0801['prod_t_cd1'] !== $mst0201DataList['prod_t_cd1']) {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0801['prod_t_cd2'] == $mst0201DataList['prod_t_cd2']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0801['prod_t_cd2']); ?>" <?php hsc($selected); ?>><?php hsc($mst0801['prod_t_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;">商品小分類</th>
                                    <td style="width:160px; border-width:0px 1px 1px 1px;">
                                        <select title="商品小分類コード" id="prod_t_cd3" name="prod_t_cd3" style="width:150px;" onChange="changeMst0801Options(this);" tabindex="89"><!-- mst0801 -->
                                            <option value=""></option>
                                            <?php foreach($mst0801DataList as $mst0801) { ?>
                                                <?php
                                                    if ($mst0801['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0801['prod_t_type'] !== '3' || 
                                                        $mst0801['prod_t_cd1'] !== $mst0201DataList['prod_t_cd1'] || 
                                                        $mst0801['prod_t_cd2'] !== $mst0201DataList['prod_t_cd2']) {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0801['prod_t_cd3'] == $mst0201DataList['prod_t_cd3']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0801['prod_t_cd3']); ?>" <?php hsc($selected); ?>><?php hsc($mst0801['prod_t_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width:150px;">商品クラス</th>
                                    <td style="width:160px; border-width:0px 1px 1px 1px;">
                                        <select title="商品クラスコード" id="prod_t_cd4" name="prod_t_cd4" style="width:150px;" tabindex="90"><!-- mst0801 -->
                                            <option value=""></option>
                                            <?php foreach($mst0801DataList as $mst0801) { ?>
                                                <?php
                                                    if ($mst0801['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0801['prod_t_type'] !== '4' || 
                                                        $mst0801['prod_t_cd1'] !== $mst0201DataList['prod_t_cd1'] || 
                                                        $mst0801['prod_t_cd2'] !== $mst0201DataList['prod_t_cd2'] || 
                                                        $mst0801['prod_t_cd3'] !== $mst0201DataList['prod_t_cd3']) {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0801['prod_t_cd4'] == $mst0201DataList['prod_t_cd4']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0801['prod_t_cd4']); ?>" <?php hsc($selected); ?>><?php hsc($mst0801['prod_t_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.jquery-replace-SearchMst0801Option-ajax -->
