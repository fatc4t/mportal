<?php
    /**
     * @file      従業員登録画面セキュリティプルダウンメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/06/17
     * @version   1.00
     * @note      従業員登録画面セキュリティプルダウンメニュー(共通)
     */
?>
                                <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                <!-- ajax差し替えエリア -->
                                <div id="jquery-replace-SearchRegisterSecurity-ajax">
                                    <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" id="security" name="security" style="width: 130px" required>
                                        <?php foreach($securityList as $security) { ?>
                                            <?php $selected =""; ?>
                                            <?php if($security['security_id'] == $userDataList['security_id'] ) { ?>
                                                <?php $selected ="selected"; ?>
                                            <?php } ?>
                                            <option value="<?php hsc($security['security_id']); ?>" <?php hsc($selected); ?>><?php hsc($security['security_name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div><!-- /.jquery-replace-SearchRegisterSecurity-ajax -->
