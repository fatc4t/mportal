<?php
    /**
     * @file      従業員登録画面役職プルダウンメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/06/17
     * @version   1.00
     * @note      従業員登録画面役職プルダウンメニュー(共通)
     */
?>
                                <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                <!-- ajax差し替えエリア -->
                                <div id="jquery-replace-SearchRegisterPosition-ajax">
                                    <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" id="position" name="position" onChange="changePositionName(this)" style="width: 130px" required>
                                        <?php foreach($positionList as $position) { ?>
                                            <?php $selected =""; ?>
                                            <?php if($position['position_id'] == $userDataList['position_id'] ) { ?>
                                                <?php $selected ="selected"; ?>
                                            <?php } ?>
                                            <option value="<?php hsc($position['position_id']); ?>" <?php hsc($selected); ?>><?php hsc($position['position_name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div><!-- /.jquery-replace-SearchRegisterPosition-ajax -->
