<?php
/**
 * Created by IntelliJ IDEA.
 * User: hpw
 * Date: 16/4/23
 * Time: 上午8:50
 */


//$host="localhost";
$host="172.19.55.239";
$db_user="root";
$db_pass="webexci2016";
$db_name="webexci";

//$db_pass="123456";
//$db_name="webex";
//$db_table_ec_job="EC_JOB";
//$db_table_ec_step_job="EC_STEPJOB";
//$db_table_ec_step_sequence="EC_STEPSEQUENCE";
//$db_table_stash_git_commit="STASH_GIT_COMMIT";
//$db_table_stash_pull_requests="STASH_PULL_REQUESTS";
//$db_table_parameters="PARAMETERS";

$mysqli = new mysqli($host, $db_user, $db_pass,$db_name);

if (mysqli_connect_errno()) {
    echo "Could not connect: " . mysqli_connect_error();
    die('Could not connect: ' . mysqli_connect_error());

}


header("Content-Type: text/html; charset=utf-8");


?>
