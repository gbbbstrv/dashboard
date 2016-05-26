<?php
/**
 * Created by IntelliJ IDEA.
 * User: hpw
 * Date: 16/5/10
 * Time: 下午1:01
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once("dbconfig.php");
    include_once("utils.php");

    $commitId = $_POST['commitId'];
    $project = $_POST['project'];
    $repository = $_POST['repository'];
    $branch = $_POST['branch'];


//$commitId='aaaaa';
//$project='cctgfork';
//$repository='web';
//$branch='refs/heads/gate/penghu';

    $jobindex = array();
    $stepindex = array();
    $prindex = array();
    $chindex = array();
    $meindex = array();

    $chlogindex = array();
    $prlogindex = array();
    $steplogindex = array();
    $melogindex = array();

    $result = $mysqli->query("

select com.COMMITID,com.STASHPROJECT,com.REPOSITORY,com.BRANCH,com.LOGTIME as 'TIMESTAMP' ,com.TYPE,com.AUTHOR,com.EMAIL,
  pr.PRSTATUS,pr.PULLREQUESTID,pr.PRLOGTIME,pr.APPROVER,pr.PRLOG,pr.REVIEWERS,pr.PRSTATE,
  ecjob.JOBID,ecjob.JOBNAME,ecjob.COMPONENTNAME,ecjob.BUILDNUMBER,ecjob.VERSION,ecjob.BUILDOPTION,ecjob.JOBLOGTIME,ecjob.JOBWAITTIME,ecjob.JOBSTATUS,
  st.STEPID,st.STEPNAME,st.STEPLOGTIME,st.STEPWAITTIME,st.STEPSTATUS,st.STEPLOG,
  ma.MERGESTASHPROJECT, ma.MERGETIMESTAMP,ma.MERGETYPE
from
  (select * from scm_commit com
  where  com.BRANCH like '%gate%' and com.COMMITID='".$commitId."' and com.STASHPROJECT='".$project."' and com.REPOSITORY='".$repository."' and com.BRANCH='".$branch."'
  ) com
  left join
  (
    select * from
      (select spr.COMMITID,spr.STASHPROJECT,spr.REPOSITORY,spr.SOURCEBRANCH,spr.TARGETBRANCH,spr.PULLREQUESTID,status as 'PRSTATUS',para.PARAMVALUE as 'PRSTATE',REVIEWERS,LOGTIME as 'PRLOGTIME',APPROVER,log as 'PRLOG' from scm_pullrequest spr,scm_pullrequest_log sprlog ,parameters para
      where spr.PULLREQUESTID=sprlog.PULLREQUESTID and spr.REPOSITORY=sprlog.REPOSITORY and spr.STASHPROJECT=sprlog.STASHPROJECT and spr.STATUS=para.PARAMKEY and para.PARAMGROUP='SCMPullRequestStatus'
       order by spr.PULLREQUESTID,LOGTIME desc
      ) req


  ) pr  on  com.COMMITID=pr.COMMITID and com.STASHPROJECT=pr.STASHPROJECT and com.REPOSITORY=pr.REPOSITORY and substring(com.BRANCH,12,length(com.BRANCH))=pr.SOURCEBRANCH

  left join
  (
    select * from
      (select j.COMMITID,j.STASHPROJECT,j.REPOSITORY,j.BRANCH,j.JOBID,JOBNAME,COMPONENTNAME,BUILDNUMBER,VERSION, BUILDOPTION ,LOGTIME as 'JOBLOGTIME',WAITTIME as 'JOBWAITTIME',STATUS as 'JOBSTATUS',log  as 'JOBLOG',para.PARAMVALUE as 'JOBSTATE',para2.PARAMVALUE as 'BUILDNAME'
       from ec_job j,ec_job_log log,parameters para ,parameters para2  where j.JOBID=log.JOBID and para.PARAMKEY=j.STATUS and para.PARAMGROUP='ECJobStatus' and para2.PARAMKEY=j.BUILDOPTION and para2.PARAMGROUP='ECJobBuildOption' ORDER BY j.jobid,log.LOGTIME
      ) job
  ) ecjob  on ecjob.CommitId=com.CommitId and ecjob.STASHPROJECT=com.STASHPROJECT and ecjob.REPOSITORY=com.REPOSITORY and ecjob.BRANCH=substring(com.BRANCH,12,length(com.BRANCH)) 

  left join
  (
    select * from
      (select ecs.JOBID,ecs.STEPID,STEPNAME,LOGTIME as 'STEPLOGTIME',WAITTIME as 'STEPWAITTIME',PARAMVALUE,status as 'STEPSTATUS',log.LOG as 'STEPLOG',RESOURCENAME from ec_jobstep ecs,parameters param,ec_stepsequence seq,ec_jobstep_log log
      where ecs.status=param.paramkey and param.paramgroup='ECJobStepStatus' and ecs.STEPSEQUENCEID=seq.STEPINDEX and ecs.JOBID=log.JOBID and ecs.STEPSEQUENCEID=log.STEPSEQUENCEID order by log.STEPSEQUENCEID,LOGTIME desc
      ) step
  ) st on st.jobid=ecjob.jobid
  left join
  (select  COMMITID,REPOSITORY,BRANCH,STASHPROJECT as 'MERGESTASHPROJECT',LOGTIME as 'MERGETIMESTAMP',TYPE as 'MERGETYPE' from scm_commit me
  where me.branch like '%master%' and STASHPROJECT='cctg'
  ) ma on com.COMMITID=ma.COMMITID  and com.REPOSITORY=ma.REPOSITORY


"
    );
    if (!$mysqli->errno) {
        $mysqli->commit();

    } else {

        $mysqli->rollback();
        echo "Error: " . $mysqli->error;
        die('Error: ' . $mysqli->error);
    }

    $checkin = new Checkin();
    $scmlog = new Log();

    $property = new Property();
    $pr = new Pullrequest();
    $merge = new Merge();


    $stage = array();
    $buildjob = array();

    $stepbuildjob = array();

    $logs = array();

    while ($row = $result->fetch_assoc()) {

        if ($row["COMMITID"] != null) {
            $property->commitid = $row["COMMITID"];
            $property->stashproject = $row["STASHPROJECT"];
            $property->repository = $row["REPOSITORY"];
            $property->branch = $row["BRANCH"];
            $property->buildoption = 'gate';
            $property->author = $row["AUTHOR"];
            $property->email = $row["EMAIL"];
        }
        if ($row["TIMESTAMP"] != null) {
            if (in_array($row["COMMITID"], $prindex, true)) {
            } else {
                $checkin->name = "checkin";
                $checkin->starttime = gmttogmt8($row["TIMESTAMP"]);
                $checkin->status = $row["TYPE"];
//            $checkin->url = "https://bitbucket-eng-chn-sjc1.cisco.com/bitbucket/projects/" . $row["STASHPROJECT"] . "/repos/" . $row["REPOSITORY"] . "/commits/" . $row["COMMITID"];

            }
        }
        if ($row["PRLOGTIME"] != null) {
            $pr->createtime = gmttogmt8($row["PRLOGTIME"]);
            if (in_array($row["PULLREQUESTID"], $prindex, true)) {
                //starttime
                $pr->createtime = gmttogmt8($row["PRLOGTIME"]);
            } else {
                $pr->name = "pr";
                //updatetime
                $pr->updatetime = gmttogmt8($row["PRLOGTIME"]);
                $pr->author = $row["APPROVER"];
                $pr->status = $row["PRSTATUS"];
                $pr->statusname = $row["PRSTATE"];
//           $pr->url = "https://bitbucket-eng-chn-sjc1.cisco.com/bitbucket/projects/" . $row["STASHPROJECT"] . "/repos/" . $row["REPOSITORY"] . "/pull-requests/" . $row["PULLREQUESTID"] . "/overview";

            }
        }
        if ($row["MERGETIMESTAMP"] != null) {
            if (in_array($row["COMMITID"], $prindex, true)) {
            } else {
                $merge->name = "merge";
                $merge->endtime = gmttogmt8($row["MERGETIMESTAMP"]);
                $merge->status = $row["MERGETYPE"];
//            $merge->url = "https://bitbucket-eng-chn-sjc1.cisco.com/bitbucket/projects/" . $row["MERGESTASHPROJECT"] . "/repos/" . $row["REPOSITORY"] . "/commits/" . $row["COMMITID"];

            }
        }
        if ($row["JOBID"] != null) {
            if (in_array($row["JOBID"], $jobindex, true)) {
            } else {
                $ecjob = new ECjob();
                $ecjob->jobid = $row["JOBID"];
//            $ecjob->jobname = $row["JOBNAME"];
                $ecjob->componentname = $row["COMPONENTNAME"];
                $ecjob->buildnumber = $row["BUILDNUMBER"];
                $ecjob->version = $row["VERSION"];
                $ecjob->buildoption = $row["BUILDOPTION"];
//            $ecjob->waittime = $row["JOBWAITTIME"];
                $ecjob->timestamp = gmttogmt8($row["JOBLOGTIME"]);
                $ecjob->status = $row["JOBSTATUS"];
//            $ecjob->url = "http://cadada.com";

                array_push($buildjob, $ecjob);
            }

        }

        if ($row["STEPID"] != null) {
            if (in_array($row["STEPID"], $stepindex, true)) {

                foreach ($stepbuildjob as $key => $value) {
                    if ($value->stepjob == $row["STEPID"]) {

                        $value->createtime = gmttogmt8($row["STEPLOGTIME"]);
                    }

                }

            } else {

                $ecstepjob = new ECstepjob();
                $ecstepjob->stepjob = $row["STEPID"];
                $ecstepjob->jobid = $row["JOBID"];
                $ecstepjob->stepname = $row["STEPNAME"];
//            $ecstepjob->waittime = $row["STEPWAITTIME"];
//            $ecstepjob->createtime = $row["STEPLOGTIME"];
                $ecstepjob->updatetime = gmttogmt8($row["STEPLOGTIME"]);
                $ecstepjob->status = $row["STEPSTATUS"];
//            $ecstepjob->url = "http://cadada.com";

                array_push($stepbuildjob, $ecstepjob);

            }
        }


        if ($row["TIMESTAMP"] != null) {
            if (in_array($row["TIMESTAMP"], $chlogindex, true)) {
            } else {
                $log = new Log();
                $log->status = $row["TYPE"];
                $log->timestamp = gmttogmt8($row["TIMESTAMP"]);
                $log->log = "Checkin Success! ";
                $log->url = "https://bitbucket-eng-chn-sjc1.cisco.com/bitbucket/projects/" . $row["STASHPROJECT"] . "/repos/" . $row["REPOSITORY"] . "/commits/" . $row["COMMITID"];
                array_push($logs, $log);
            }
        }
        if ($row["PRLOGTIME"] != null) {
            if (in_array($row["PRLOGTIME"], $prlogindex, true)) {
            } else {
                $log = new Log();
                $log->status = $row["PRSTATUS"];
                $log->timestamp = gmttogmt8($row["PRLOGTIME"]);
                $log->log = "PR " . $row["PRLOG"] . "! Reviewers :[ " . $row["REVIEWERS"] . " ] ";
                $log->url = "https://bitbucket-eng-chn-sjc1.cisco.com/bitbucket/projects/" . $row["STASHPROJECT"] . "/repos/" . $row["REPOSITORY"] . "/pull-requests/" . $row["PULLREQUESTID"] . "/overview";
                array_push($logs, $log);
            }
        }
        if ($row["MERGETIMESTAMP"] != null) {
            if (in_array($row["MERGETIMESTAMP"], $melogindex, true)) {
            } else {
                $log = new Log();
                $log->status = $row["MERGETYPE"];
                $log->timestamp = gmttogmt8($row["MERGETIMESTAMP"]);
                $log->log = "Merge Success! ";
                $log->url = "https://bitbucket-eng-chn-sjc1.cisco.com/bitbucket/projects/" . $row["MERGESTASHPROJECT"] . "/repos/" . $row["REPOSITORY"] . "/commits/" . $row["COMMITID"];
                array_push($logs, $log);
            }
        }
        if ($row["STEPID"] != null) {
            if (in_array($row["STEPLOGTIME"], $steplogindex, true)) {
            } else {
                $log = new Log();
                $log->status = $row["STEPSTATUS"];
                $log->timestamp = gmttogmt8($row["STEPLOGTIME"]);
                $log->log = $row["STEPNAME"] . ":" . $row["STEPLOG"] . " ";
                $log->url = "https://cctg-ec2.cisco/commander/link/jobStepDetails/jobSteps/".$row["MERGETYPE"];
                array_push($logs, $log);
            }
        }


        array_push($chindex, $row["COMMITID"]);
        array_push($prindex, $row["PULLREQUESTID"]);
        array_push($meindex, $row["COMMITID"]);
        array_push($jobindex, $row["JOBID"]);
        array_push($stepindex, $row["STEPID"]);

        array_push($prlogindex, $row["PRLOGTIME"]);
        array_push($steplogindex, $row["STEPLOGTIME"]);
        array_push($chlogindex, $row["TIMESTAMP"]);
        array_push($melogindex, $row["MERGETIMESTAMP"]);
    }

    $logs1 = array();
    $logs2 = array();
    $time1 = array();
    $time2 = array();
    foreach ($logs as $key => $value) {
//        $id[$key] = $value->timestamp;
//        $time[$key] = $value->log;

        if ($value->status == 1 || $value->status == 2) {
            $id1[$key] = $value->timestamp;
            $time1[$key] = $value->log;
            array_push($logs1, $value);

        } else {
            $id2[$key] = $value->timestamp;
            $time2[$key] = $value->log;
            array_push($logs2, $value);

        }

    }
//    array_multisort($time, SORT_NUMERIC, SORT_ASC, $id, SORT_STRING, SORT_DESC, $logs);
    if (is_array($time1)&&count($time1)!=0) {
        array_multisort($time1, SORT_NUMERIC, SORT_ASC, $id1, SORT_STRING, SORT_DESC, $logs1);
    }
    if (is_array($time2)&&count($time2)!=0) {
        array_multisort($time2, SORT_NUMERIC, SORT_ASC, $id2, SORT_STRING, SORT_DESC, $logs2);
    }
$sort_logs=array_merge($logs2,$logs1);

    if ($checkin->name != null) {
        array_push($stage, $checkin);
    }
    if ($pr->name != null) {
        array_push($stage, $pr);
    }
    if ($merge->name != null) {
        array_push($stage, $merge);
    }
    $items = array();
//Packet
    foreach ($stepbuildjob as $item) {
        $jobid = $item->jobid;
        unset($item->jobid);
        if (!isset($items[$jobid])) {
            $items[$jobid] = array('jobid' => $jobid, 'items' => array());
        }
        $items[$jobid]['items'][] = $item;
    }

    foreach ($buildjob as $value) {
        $value->stepjob = $items[$value->jobid]["items"];
    }

    $stage_json = json_encode($stage);

    $pipelineData = array(
        "property" => $property,
        "stage" => $stage,
        "build" => $buildjob,
        "logs" => $sort_logs
    );

    $pipelineData_json = json_encode($pipelineData);
    $json = json_decode($pipelineData_json);

//echo $json->{"build"}[0]->{"stepjob"};
    echo $pipelineData_json;

//echo "{".'"property"'.":".$property_json.",".'"stage"'.":".$stage_json.",".'"file"'.":[]"."}";
    $mysqli->close();
}
    class Pullrequest
    {
        public $name;
        public $createtime;
        public $updatetime;
        public $author;
        public $status;
        public $statusname;
//    public $url ;
//    public $logs ;

    }

    class Merge
    {
        public $name;
        public $endtime;
        public $status;
//    public $url ;
//    public $logs ;

    }

    class Checkin
    {
        public $name;
        public $starttime;
        public $status;
//    public $url ;
//    public $logs ;


    }

    class Log
    {
        public $stagename;
        public $timestamp;
        public $status;
        public $url;
        public $log;
    }

    class ECjob
    {
        public $jobid;
//    public $jobname ;
        public $componentname;
        public $buildnumber;
        public $version;
        public $buildoption;
//    public $waittime ;
        public $timestamp;
        public $status;
//    public $url;
        public $stepjob;

    }

    class ECstepjob
    {
        public $stepjob;
        public $jobid;
        public $stepname;
//    public $waittime ;
        public $createtime;
        public $updatetime;
        public $status;
//    public $url;
//    public $logs;
    }

    class Property
    {

        public $commitid;
        public $stashproject;
        public $repository;
        public $branch;
        public $buildoption;
        public $author;
        public $email;
    }


