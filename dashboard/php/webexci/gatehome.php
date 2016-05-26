<?php
/**
 * Created by IntelliJ IDEA.
 * User: hpw
 * Date: 16/4/23
 * Time: 上午8:50
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    include_once("dbconfig.php");
    include_once("utils.php");
    ini_set('date.timezone', 'PRC');




    $sql="select COMMITID,STASHPROJECT,REPOSITORY,BRANCH,TYPE,AUTHOR,LOGTIME from scm_commit where  STASHPROJECT='cctgfork'  and BRANCH like '%gate%' and TYPE ='2' order by LOGTIME DESC limit 50";
    if ($stmt = $mysqli->prepare($sql))
    {
        $stmt->execute();
        $stmt->bind_result($COMMITID,$STASHPROJECT,$REPOSITORY,$BRANCH,$TYPE,$AUTHOR,$LOGTIME);
    }
//    $data = array();
    while($stmt->fetch()) {
        $commit = new Commit();
        $commit->time = gmttogmt8($LOGTIME);
        $commit->commitId = $COMMITID;
        $commit->project = $STASHPROJECT;
        $commit->repository = $REPOSITORY;
        $commit->branch = $BRANCH;
        $commit->type = $TYPE;
        $commit->author = $AUTHOR;
        $data[] = $commit;
    }


    $json = json_encode($data);
    echo "{" . '"data"' . ":" . $json . "," . '"options"' . ":[]" . "," . '"file"' . ":[]" . "}";
    
    $stmt->close();
    $mysqli->close();



}
class Commit
{
    public $time;
    public $commitId;
    public $project;
    public $repository;
    public $branch;
    public $type;
    public $author;
    public $statue;


}

?>
