<?php
    /**
     * @file      従業員検索用ドロップメニュー(共通)
     * @author    USE Y.Sakata
     * @date      2016/08/19
     * @version   1.00
     * @note      従業員検索用ドロップメニュー(共通)
     */
?>
        <?php require_once( SystemParameters::$SANITIZE_SETTING_FILE ) ?>
        <!-- ajax差し替えエリア -->
    <div id="jquery-replace-SearchUserName-ajax">
    <table align=left>
        <tr>
            <td style="border-style:none;"><p id="p1" style="<?php hsc($unit_disabled); ?>">選択一覧</p>
                <select name="searchUser" id="searchUser" size="10" style="width: 200px;<?php hsc($unit_disabled); ?>" multiple>
                    <?php
                        foreach($searchUserNameList as $userName)
                        {
                    ?>
                            <option value="<?php hsc($userName['id']); ?>"><?php hsc($userName['name']); ?></option>
                    <?php } ?>
                </select>
            </td>
            <td style="border-style:none;">
                <input type="button" name="right" id="right" value="≫" onclick="moveList();" style="<?php hsc($unit_disabled); ?>"/><br /><br />
                <input type="button" name="left" id="left" value="≪" onclick="delList();" style="<?php hsc($unit_disabled); ?>"/>
            </td>
            <td style="border-style:none;"><p id="p2" style="<?php hsc($unit_disabled); ?>">通知一覧</p>
                <select name="selectUser" id="selectUser" size="10" style="width: 200px;<?php hsc($unit_disabled); ?>" multiple>
                    <?php
                        foreach($selectUserNameList as $sUserName)
                        {
                    ?>
                            <option value="<?php hsc($sUserName['id']); ?>"><?php hsc($sUserName['name']); ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
    </table>
    </div><!-- /.jquery-replace-SearchUserName-ajax -->
