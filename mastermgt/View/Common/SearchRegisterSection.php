<?php
    /**
     * @file      従業員登録画面セクションプルダウンメニュー(共通)
     * @author    USE M.Higashihara
     * @date      2016/06/17
     * @version   1.00
     * @note      従業員登録画面セクションプルダウンメニュー(共通)
     */
?>
                                <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                <!-- ajax差し替えエリア -->
                                <div id="jquery-replace-SearchRegisterSection-ajax">
                                    <select title="<?php hsc($Log->getMsgLog('MSG_BASE_0303')); ?>" id="section" name="section" style="width: 130px">
                                        <?php foreach($sectionList as $section) { ?>
                                            <?php $selected =""; ?>
                                            <?php if($section['section_id'] == $userDataList['section_id'] ) { ?>
                                                <?php $selected ="selected"; ?>
                                            <?php } ?>
                                            <option value="<?php hsc($section['section_id']); ?>" <?php hsc($selected); ?>><?php hsc($section['section_name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div><!-- /.jquery-replace-SearchRegisterSection-ajax -->
