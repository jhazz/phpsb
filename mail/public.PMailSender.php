<?
class mail_PMailSender {

var $MailTemplates; # Mail templates cache

function EnqueueMessage($args) {
	extract(param_extract(array(
		TemplateID=>'int', # (TemplateID) OR (TemplateName+Cartridge+Language)

		TemplateName=>'string',
		Cartridge=>'string',
		Language=>'string=en',
		
		MailFrom=>'string',
		MailTo=>'string',
		
		QueueID=>'int',
		QueueName=>'string',
		
	  FieldValues=>'array',
	  Suspended=>'int'
	  ),$args));
	
	global $_USER;
	if (!$TemplateID) {
		if ($TemplateName && $Cartridge && $Language) {
			$q=DBQuery("SELECT TemplateID FROM mail_Templates WHERE Name='$TemplateName' AND Cartridge='$Cartridge' AND Lang='$Language'");
			if (!$q) {
				print_developer_warning ("Mail template not found","Name='$TemplateName', Cartridge='$Cartridge', Language='$Language'");
				return false;
			}
			$TemplateID=$q->Top->TemplateID;
		}
	}

	if (!$QueueID) {
		$now=time();
		$QueueID=DBInsert(array(
			Table=>'mail_Queues',
			GetAutoInc=>1,
			Values=>array(
				DateStart=>$now, DateEnd=>$now+60*60*24*3, 
				CreateByUserID=>$_USER->UserID,
				Name=>$QueueName,
				Priority=>1)
			));
		if (!$QueueID) return false;
	}

	DBInsert(array(
		Table=>'mail_QMessages',
		Values=>array(
			QueueID=>$QueueID,
			TemplateID=>$TemplateID,
			FieldValues=>serialize($FieldValues),
			MailFrom=>$MailFrom,
			MailTo=>$MailTo
		),
	));
	
	if (!$Suspended) {
		$this->Bulk(array(QueueID=>$QueueID));
	}
	return 1;
}

function Bulk($args) {
	extract(param_extract(array(
		QueueID=>'int',
		QueueIDs=>'&array',
		ForceSending=>'int',
	  ),$args));
	global $cfg;
	
	if ($ForceSending) {
		unset($args['ForceSending']);
		DBExec ("UPDATE mail_Queues SET Suspended=0");
		DBExec ("UPDATE mail_QMessages SET Suspended=0");
	}
	
	$MaxMailErrorCount=100;
	if ($QueueID) {
		$q=DBQuery("SELECT QueueID,Name FROM mail_Queues WHERE QueueID=$QueueID","QueueID");
		if ($q) {
			if ($q->Top->Stopped) {
				return;
			}
		}
	} elseif (is_array($QueueIDs)){
		$q=DBQuery("SELECT QueueID,Name FROM mail_Queues WHERE QueueIDs IN (".implode(",",$QueueIDs)." ORDER BY Priority DESC LIMIT 0,500","QueueID");
	} else {
		$q=DBQuery("SELECT QueueID,Name FROM mail_Queues WHERE Suspended=0 AND Stopped=0 AND Complete=0 ORDER BY Priority DESC LIMIT 0,100","QueueID");
	}
	
	if (!$q) return array(Message=>"No queues to send",ButtonClose=>1);

	$MaxTime=ini_get("max_execution_time");
	$now=time();
#	$EndingTime=$now+2;
	$EndingTime=$now+$MaxTime-5;
	$TimeBreak=false;

	$LifeTime=intval($cfg['Settings']['mail']['LifetimeSentMessages']);
	if (!$LifeTime) $LifeTime=3;
	$qold=DBQuery("SELECT QueueID FROM mail_Queues WHERE Complete=1 AND DateEnd<".(time()-$LifeTime*24*60*60),"QueueID");
	if ($qold) {
		$qids=implode (",",array_keys($qold->Rows));
		DBExec ("DELETE FROM mail_Queues WHERE QueueID IN ($qids)");
		DBExec ("DELETE FROM mail_QMessages WHERE QueueID IN ($qids)");
	}

	$LifeTime=intval($cfg['Settings']['mail']['LifetimeUnsentMessages']);
	if (!$LifeTime) $LifeTime=10;
	$qold=DBQuery("SELECT QueueID FROM mail_Queues WHERE Complete=0 AND DateEnd<".(time()-$LifeTime*24*60*60),"QueueID");
	if ($qold) {
		$qids=implode (",",array_keys($qold->Rows));
		DBExec ("DELETE FROM mail_Queues WHERE QueueID IN ($qids)");
		DBExec ("DELETE FROM mail_QMessages WHERE QueueID IN ($qids)");
	}

	$ErrorCount=0;
	foreach ($q->Rows as $QueueID=>$tmp) {
		if (time()>=$EndingTime) {$TimeBreak=true; break;}
		do {
			$s="SELECT * FROM mail_QMessages WHERE QueueID=$QueueID AND (Status=0 OR ((Status=1 OR Status=2) AND Suspended=0)) LIMIT 0,1000";
			$q2=DBQuery($s,"QMessageID");
			if (!$q2) break;
			
			foreach ($q2->Rows as $QMessageID=>$msg) {
				DBExec ("UPDATE mail_QMessages SET Status=1,Suspended=1,DateSent=".time()." WHERE QMessageID=$QMessageID");
				if ($msg->FieldValues) $FieldValues=unserialize($msg->FieldValues); else $FieldValues=array();
				if (!$this->SendMail($msg->TemplateID,$msg->MailFrom,$msg->MailTo,$FieldValues)) {
					DBExec ("UPDATE mail_QMessages SET Status=2,DateSent=".time()." WHERE QMessageID=$QMessageID");
					$ErrorCount++;
					continue;
				}
				DBExec ("UPDATE mail_QMessages SET Status=3,DateSent=".time()." WHERE QMessageID=$QMessageID");
			}
		if (time()>=$EndingTime) {$TimeBreak=true; break;}
		if ($ErrorCount>$MaxMailErrorCount) break;
		} while (true);
		
		$qtc=DBQuery("SELECT Status,COUNT(*) AS Counter FROM mail_QMessages WHERE QueueID=$QueueID GROUP BY Status","Status");
		$CountWait=intval($qtc->Rows['0']->Counter);
		$CountSending=intval($qtc->Rows['1']->Counter);
		$CountErr=intval($qtc->Rows['2']->Counter);
		$CountOk=intval($qtc->Rows['3']->Counter);
#		print "CountErr=$CountErr,CountWait=$CountWait,CountSending=$CountSending,CountOk=$CountOk<br>";
		if (!$CountSending) {
			if (!($CountWait+$CountErr)) {
				# Queue successfully complete
				if ($cfg['Settings']['mail']['ArchiveCompleteQueues']) {
					DBExec ("UPDATE mail_Queues SET Complete=1,DateEnd=$now WHERE QueueID=$QueueID");
					if (!$cfg['Settings']['mail']['ArchiveQueueMessages']) {
						DBExec ("DELETE FROM mail_QMessages WHERE QueueID=$QueueID");
					} 
				} else {
					DBExec ("DELETE FROM mail_Queues WHERE QueueID=$QueueID");
					DBExec ("DELETE FROM mail_QMessages WHERE QueueID=$QueueID");
				}
			} else {
				DBExec ("UPDATE mail_Queues SET Suspended=1,CountErr=$CountErr,CountWait=$CountWait,CountSending=$CountSending,CountOk=$CountOk WHERE QueueID=$QueueID");
			}
		} else {
			# still sending
			DBExec ("UPDATE mail_Queues SET Suspended=1,CountErr=$CountErr,CountWait=$CountWait,CountSending=$CountSending,CountOk=$CountOk WHERE QueueID=$QueueID");
		}
	}
	if ($TimeBreak) {
		return array(
			Message=>"Time overflow. Let's bulk again",
			TimeoutForwardTo=>ActionURL("mail.PMailSender.Bulk.b",$args)
			);
	}
	if ($ErrorCount>$MaxMailErrorCount) {
		return array(Message=>"Too much errors");
	}
	return array(Message=>"Sending complete",ButtonClose=>1);
}


function SendMail($TemplateID,&$MailFrom,&$MailTo,&$FieldValues) {
#	$name="$Cartridge.$TemplateName($Language)";
global $cfg;
if (isset($this->MailTemplates[$TemplateID])) $Template=&$this->MailTemplates[$TemplateID];
else {
	$s="SELECT Subject,PlainBody,HtmlBody,Encoding,Lang FROM mail_Templates WHERE TemplateID=$TemplateID";
	$q=DBQuery($s);
	if (!$q) {
		$q=DBQuery("SELECT Subject,PlainBody,HtmlBody,Encoding,Lang FROM mail_Templates WHERE TemplateID=$TemplateID");
	}
	if ($q) {
		$Template=$q->Top;
		$this->MailTemplates[$TemplateID]=&$Template;
	} else {
		print_developer_warning("Mail template is absent",$Template);
		return false;
	}
}
$crlf="\r\n";

$FieldValues['MAIL_FROM']=$MailFrom;
$FieldValues['MAIL_TO']=$MailTo;
$FieldValues['SITE_NAME']=$cfg['SiteName'];
$FieldValues['SITE_TITLE']=langstr_get($cfg['Settings']['jsb']['WebSiteTitle']);
$FieldValues['SITE_URL']=$cfg['SiteURL'];
$FieldValues['HELP_EMAIL']=$cfg['HelpEmail'];
$FieldValues['FOOTER']=langstr_get($cfg['Settings']['mail']['EmailFooter']);

$Encoding=$Template->Encoding;
$search=array(); $replace=array();
foreach ($FieldValues as $k=>$v) {$search[]="|{"."$k}|i"; $replace[]=$v;}
$Subject=preg_replace($search,$replace,$Template->Subject);

$ResSubject="";
while (mb_strlen($Subject)>0) {
	$s=mb_substr($Subject,0,30);
	$Subject=mb_substr($Subject,30);
	$ResSubject.="=?utf-8?B?".base64_encode($s)."=?=".$crlf;
}
$PlainBody=preg_replace($search,$replace,$Template->PlainBody);
$HtmlBody="";
if (!empty($Template->HtmlBody)) {
	$HtmlBody=preg_replace($search,$replace,$Template->HtmlBody);
}

$BOUNDARY="----=".uniqid("NextPart");
$Headers="From: $MailFrom$crlf"
  ."MIME-Version: 1.0$crlf"
  ."Content-Type: multipart/mixed; boundary=\"$BOUNDARY\"$crlf";
  
$Body="This is a multipart message!$crlf"
	."--$BOUNDARY$crlf"
	."Content-Type: text/plain;".(($Encoding)?"charset=\"$Encoding\"":"").$crlf
	."Content-Transfer-Encoding: base64".$crlf.$crlf
	.chunk_split(base64_encode($PlainBody)).$crlf;

	if (!empty($HtmlBody)) {
	$Body.="--$BOUNDARY$crlf"
	."Content-Type: text/html;".(($Encoding)?"charset=\"$Encoding\"":"").$crlf
	."Content-Transfer-Encoding: base64".$crlf.$crlf
	.chunk_split(base64_encode($HtmlBody)).$crlf;
	
}
#print "<hr><h1>Отправляю письмо</h1><pre>";
#print "$EmailTo<hr>Subject: $ResSubject<hr><b><pre>$Headers</pre></b><hr><pre>$Body</pre><hr>";
return mail ($MailTo,$ResSubject,$Body,$Headers);
}
}
?>

