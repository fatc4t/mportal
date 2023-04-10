<?php
    /**
     * @file      コード検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/06/13
     * @version   1.00
     * @note      コード検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchCodeName-ajax">
                                        <select name="codeName" id="codeName" style="width: 100px">
                                            <?php foreach($codeList as $code) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($code['code'] == $searchArray['codeID']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                <option value="<?php hsc($code['code']); ?>" <?php hsc($selected); ?>><?php hsc($code['code']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchCodeName-ajax -->
