<?
class mail_IInfoBlocks
{
var $CopyrightText="(c)2007 PHP Systems builder. Mail";
var $CopyrightURL="http://www.phpsb.com/mail";
var $RoleAccess=array(QueueManager=>"QueuesStatus");

function QueuesStatus($args)
  {
  global $cfg;
  global $_USER;
  $_=&$GLOBALS['_STRINGS']['mail'];
  $qc=DBQuery("SELECT COUNT(*) AS TotalQueuesCount,SUM(CountWait) AS WaitCount,
    SUM(CountErr) AS ErrorsCount,
    SUM(CountOk) OkCount,
    SUM(Complete) AS CompleteQueuesCount FROM mail_Queues");
  if (!$qc) return array(Error=>"Mail queues not supported!"); 
  
  extract (param_extract(array(
  	TotalQueuesCount=>'int',
  	ErrorsCount=>'int',
  	WaitCount=>'int',
  	OkCount=>'int',
  	CompleteQueuesCount=>'int',
  	),$qc->Top));
  
	$IncompleteQueuesCount=$TotalQueuesCount-$CompleteCount;
  if ($ErrorsCount) $s.="<tr><td>$_[INFO_ERRORSCOUNT]</td><td><a href='".ActionURL("mail.IMailQueues.Browse.bm",array(FilterComplete=>0))."'>$ErrorsCount</a></td></tr>";
  if ($WaitCount) $s.="<tr><td>$_[INFO_WAITCOUNT]</td><td>$WaitCount</td></tr>";
  if ($OkCount) $s.="<tr><td>$_[INFO_OKCOUNT]</td><td>$OkCount</td></tr>";
  if ($s) $s.="<tr><td colspan='2'><hr></td></tr>";
  if ($IncompleteQueuesCount) $s.="<tr><td>$_[INFO_INCOMPLETE_QUEUES_COUNT]</td><td><a href='".ActionURL("mail.IMailQueues.Browse.bm",array(FilterComplete=>0))."'>$TotalQueuesCount</a></td></tr>";
  if ($CompleteCount) $s.="<tr><td>$_[INFO_COMPLETE_QUEUES_COUNT]</td><td><a href='".ActionURL("mail.IMailQueues.Browse.bm",array(FilterComplete=>1))."'>$OkCount</a></td></tr>";
  
  
  if ($s) print "<table>$s</table>";
  }

}
