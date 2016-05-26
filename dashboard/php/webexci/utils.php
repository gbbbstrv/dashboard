<?php
/**
 * Created by PhpStorm.
 * User: hpw
 * Date: 16/5/23
 * Time: 上午9:54
 */
function gmttogmt8($datetime){
    return date("Y-m-d H:i:s", strtotime($datetime)+8*3600);

}
?>