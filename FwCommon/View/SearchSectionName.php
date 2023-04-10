<?php
    /**
     * @file      セクション検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/06/13
     * @version   1.00
     * @note      セクション検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-SearchSectionName-ajax">
                                        <select name="sectionName" id="sectionName" style="width: 100px">
                                            <?php foreach($sectionNameList as $section) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($section['section_name'] == $searchArray['sectionName']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($section['section_name']); ?>" <?php hsc($selected); ?>><?php hsc($section['section_name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-SearchSectionName-ajax -->
