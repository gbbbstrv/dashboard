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


//$starttime=$_POST['starttime'];
//$endtime=$_POST['endtime'];
//    $project = $_POST['project'];
    $repository_para = '%'.$_POST['repository'].'%';
    $branch_para = '%'.$_POST['branch'].'%';
    $author_para = '%'.$_POST['author'].'%';



    $sql="select COMMITID,STASHPROJECT,REPOSITORY,BRANCH,TYPE,AUTHOR,LOGTIME from scm_commit  where   STASHPROJECT='cctgfork'  and BRANCH like '%gate%' and TYPE =2 and  
                                   REPOSITORY like ? and BRANCH like ? and AUTHOR like ? order by LOGTIME desc limit 50";
    if ($stmt = $mysqli->prepare($sql))
    {
        $stmt->bind_param("sss", $repository_para,$branch_para,$author_para);
        $stmt->execute();
        $stmt->bind_result($COMMITID,$STASHPROJECT,$REPOSITORY,$BRANCH,$TYPE,$AUTHOR,$LOGTIME);
    }

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
    public $time ;
    public $commitId;
    public $project ;
    public $repository ;
    public $branch ;
    public $type ;
    public $author ;
    public $statue ;

}

?>
