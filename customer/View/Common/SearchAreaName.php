<?php
    /**
     * @file      地区検索用ドロップメニュー(共通)
     * @author    K.Sakamoto
     * @date      2017/8/31
     * @version   1.00
     * @note      地区検索用ドロップメニュー(共通)
     */
?>
                                    <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                                    <!-- ajax差し替えエリア -->
                                    <div id="jquery-replace-AreaName-ajax" style='display: inline-block; _display: inline;'>
                                        <select name="areaCd" id="areaCd" style="width: 100px">
                                            <?php foreach($areaList as $area) { ?>
                                                <?php $selected = ""; ?>
                                                <?php if($area['area_cd'] == $searchArray['areaCd']) { ?>
                                                    <?php $selected = "selected"; ?>
                                                <?php } ?>
                                                    <option value="<?php hsc($area['area_cd']); ?>" <?php hsc($selected); ?>><?php hsc($area['area_nm']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div><!-- /.jquery-replace-AreaName-ajax -->
