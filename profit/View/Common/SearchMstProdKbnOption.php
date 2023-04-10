<?php
    /**
     * @file      POS商品マスタ　商品区分(左)エリア
     * @author    川橋
     * @date      2018/12/13
     * @version   1.00
     * @note      POS商品マスタ　自社分類コード、メーカーコード、JICFS分類コード、商品区分1～5
     */
?>
                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                    <!-- ajax差し替えエリア -->
                    <div id="jquery-replace-SearchMstProdKbnOption-ajax">
                        <table style="margin-top:0; margin-left:0; margin-bottom:0; margin-right:0; table-layout:fixed;">
                            <tbody>
                                <tr>
                                    <td style="width:150px; height:0px; border-style:none;"></td>
                                    <td style="width:160px; height:0px; border-style:none;"></td>
                                </tr>
                                <tr>
                                    <th>自社分類</th>
                                    <td>
                                        <select title="自社分類コード" id="priv_class_cd" name="priv_class_cd" style="width:250px;" tabindex="79">
                                            <option value=""></option>
                                            <?php foreach($mst5501DataList as $mst5501) { ?>
                                                <?php
                                                    $selected = '';
                                                    if ($mst5501['organization_id'] !== $mst0201DataList['organization_id']) {
                                                        continue;
                                                    }
                                                    if (mst5501['priv_class_cd'] == $mst0201DataList['priv_class_cd']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst5501['priv_class_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst5501['priv_class_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>メーカー</th>
                                    <td>
                                        <select title="メーカーコード" id="maker_cd" name="maker_cd" style="width:250px;" tabindex="80">
                                            <option value=""></option>
                                            <?php foreach($mst1001DataList as $mst1001) { ?>
                                                <?php
                                                    $selected = '';
                                                    if ($mst1001['organization_id'] !== $mst0201DataList['organization_id']) {
                                                        continue;
                                                    }
                                                    if ($mst1001['maker_cd'] == $mst0201DataList['maker_cd']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst1001['maker_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst1001['maker_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>JICFS分類</th>
                                    <td>
                                        <select title="JICFS分類コード" id="jicfs_class_cd" name="jicfs_class_cd" style="width:250px;" tabindex="81">
                                            <option value=""></option>
                                            <?php foreach($mst5401DataList as $mst5401) { ?>
                                                <?php
                                                    $selected = '';
                                                    if ($mst5401['organization_id'] !== $mst0201DataList['organization_id']) {
                                                        continue;
                                                    }
                                                    if ($mst5401['jicfs_class_cd'] == $mst0201DataList['jicfs_class_cd']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst5401['jicfs_class_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst5401['jicfs_class_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品区分1</th>
                                    <td>
                                        <select title="商品区分1" id="prod_k_cd1" name="prod_k_cd1" style="width:250px;" tabindex="82">
                                            <option value=""></option>
                                            <?php foreach($mst0901DataList as $mst0901) { ?>
                                                <?php
                                                    if ($mst0901['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0901['prod_k_type'] !== '01') {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0901['prod_k_cd'] == $mst0201DataList['prod_k_cd1']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0901['prod_k_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst0901['prod_k_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品区分2</th>
                                    <td>
                                        <select title="商品区分2" id="prod_k_cd2" name="prod_k_cd2" style="width:250px;" tabindex="83">
                                            <option value=""></option>
                                            <?php foreach($mst0901DataList as $mst0901) { ?>
                                                <?php
                                                    if ($mst0901['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0901['prod_k_type'] !== '02') {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0901['prod_k_cd'] == $mst0201DataList['prod_k_cd2']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0901['prod_k_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst0901['prod_k_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品区分3</th>
                                    <td>
                                        <select title="商品区分3" id="prod_k_cd3" name="prod_k_cd3" style="width:250px;" tabindex="84">
                                            <option value=""></option>
                                            <?php foreach($mst0901DataList as $mst0901) { ?>
                                                <?php
                                                    if ($mst0901['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0901['prod_k_type'] !== '03') {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0901['prod_k_cd'] == $mst0201DataList['prod_k_cd3']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0901['prod_k_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst0901['prod_k_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品区分4</th>
                                    <td>
                                        <select title="商品区分4" id="prod_k_cd4" name="prod_k_cd4" style="width:250px;" tabindex="85">
                                            <option value=""></option>
                                            <?php foreach($mst0901DataList as $mst0901) { ?>
                                                <?php
                                                    if ($mst0901['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0901['prod_k_type'] !== '04') {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0901['prod_k_cd'] == $mst0201DataList['prod_k_cd4']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0901['prod_k_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst0901['prod_k_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th>商品区分5</th>
                                    <td>
                                        <select title="商品区分5" id="prod_k_cd5" name="prod_k_cd5" style="width:250px;" tabindex="86">
                                            <option value=""></option>
                                            <?php foreach($mst0901DataList as $mst0901) { ?>
                                                <?php
                                                    if ($mst0901['organization_id'] !== $mst0201DataList['organization_id'] ||
                                                        $mst0901['prod_k_type'] !== '05') {
                                                        continue;
                                                    }
                                                    $selected = '';
                                                    if ($mst0901['prod_k_cd'] == $mst0201DataList['prod_k_cd5']) {
                                                        $selected = 'selected=""';
                                                    }
                                                ?>
                                                <option value="<?php hsc($mst0901['prod_k_cd']); ?>" <?php hsc($selected); ?>><?php hsc($mst0901['prod_k_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.jquery-replace-SearchMstProdKbnOption-ajax -->
