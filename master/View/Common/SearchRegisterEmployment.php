<?php
    /**
     * @file      従業員登録画面雇用形態プルダウンメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/06/17
     * @version   1.00
     * @note      従業員登録画面雇用形態プルダウンメニュー(共通)
     */
?>
                                <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                <!-- ajax差し替えエリア -->
                                <div id="jquery-replace-SearchRegisterEmployment-ajax">
                                    <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" id="employment" name="employment" onChange="changeEmploymentName(this)" style="width: 130px" required>
                                        <?php foreach($employmentList as $employment) { ?>
                                            <?php $selected =""; ?>
                                            <?php if($employment['employment_id'] == $userDataList['employment_id'] ) { ?>
                                                <?php $selected ="selected"; ?>
                                            <?php } ?>
                                            <option value="<?php hsc($employment['employment_id']); ?>" <?php hsc($selected); ?>><?php hsc($employment['employment_name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div><!-- /.jquery-replace-SearchRegisterEmployment-ajax -->
