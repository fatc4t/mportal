<?php
    /**
     * @file      セキュリティ名検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/06/27
     * @version   1.00
     * @note      セキュリティ名検索用ドロップメニュー(共通)
     */
?>
                               <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
                               <!-- ajax差し替えエリア -->
                               <div id="jquery-replace-SearchSecurityName-ajax">
                                  <select name="security" id="security" style="width: 100px">
                                      <?php foreach($securityList as $security) { ?>
                                          <?php $selected = ""; ?>
                                          <?php if($security['security_name'] == $searchArray['security']) { ?>
                                              <?php $selected = "selected"; ?>
                                          <?php }?>
                                          <option value="<?php hsc($security['security_name']); ?>" <?php hsc($selected); ?>><?php hsc($security['security_name']); ?></option>
                                      <?php } ?>
                                  </select>
                               </div><!-- /.jquery-replace-SearchSecurityName-ajax -->
