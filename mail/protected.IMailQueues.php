<?
class mail_IMailQueues {
var $CopyrightText="(c)2007 PHP Systems builder. Mail";
var $CopyrightURL="http://www.phpsb.com/mail";

function _tab_User (&$QueueID,&$row,$fname,$args) {
	print "<a href='javascript:;' onClick='W.openModal({url:\"".
  ActionURL("um.IUsers.Edit.b",array(UserID=>$row->$fname))."\",w:550,h:450,reloadOnOk:1});'>$row->Login</a>";
}
function _tab_Priority(&$QueueID,&$row,$fname,$args) {
	switch ($row->$fname) {
		case 1: $s=""; break;
		case 2: $s="!"; break;
		case 3: $s="!!"; break;
		default: $s=$row->$fname;
	}
	if ($s) print "<font color='red'>$s</font>";
}

function _tab_Name (&$QueueID,&$row,$fname,$args) {
	$s=$row->$fname;
	if (!$s) $s="(--)";
	print "<a href='javascript:;' onClick='W.openModal({url:\"".
  ActionURL("mail.IMailQueues.Edit.b",array(QueueID=>$QueueID))."\",w:850,h:450,reloadOnOk:1});'>$s</a>";
}
function _tab_Status (&$QueueID,&$row,$fname,$args) {
	$s="";
	if ($row->CountWait) $s.="$row->CountWait";
	if ($row->CountSending) $s.=" <font color='#ff6600'>sending:$row->CountSending</font>";
	if ($row->CountErr) $s.=" <font color='red'>errors:$row->CountErr</font>";
	if ($row->CountOk) {if ($s) $s="($s)"; else $s=" done"; $s="<b>$row->CountOk</b>$s";}
	if ($row->Stopped) $s="<b>[STOP]</b> ".$s;
	print $s;
}



function Browse($args) {
	extract(param_extract(array(
		PageNo=>'int=1', 
		RowsPerPage=>'int=20',
		FilterComplete=>'int=0',
	  ),$args));
  $_=&$GLOBALS['_STRINGS']['mail'];
	global $_USER;
  
  $IncompleteCount=$CompleteCount=0;
	$qc=DBQuery ("SELECT Complete,COUNT(*) AS RowCount FROM mail_Queues GROUP BY Complete","Complete");
		if ($qc) {
  	$IncompleteCount=$qc->Rows['0']->RowCount;
 		$CompleteCount=$qc->Rows['1']->RowCount;
 	}
  $where="WHERE Complete=$FilterComplete";
  $s="SELECT q.*,u.Login FROM mail_Queues q LEFT OUTER JOIN um_Users u ON (q.CreateByUserID=u.UserID)
  $where ORDER BY QueueID LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
  $q=DBQuery($s,"QueueID");
  $RowCount=($FilterComplete)?$CompleteCount:$IncompleteCount;

  $args=array(
    Action=>ActionURL("mail.IMailQueues.BrowseAction.b"),
    Modal=>1,
    Fields=>array(
      Priority=>"!",
      DateStart=>$_['MAILQUEUE_DATESTART'],
      DateEnd=>$_['MAILQUEUE_DATEEND'],
      Name=>$_['MAILQUEUE_NAME'],
      Status=>$_['MAILQUEUE_STATUS'],
      CreateByUserID=>$_['MAILQUEUE_USER']
      ),
    FieldHooks=>array(
      CreateByUserID=>_tab_User,
      Status=>_tab_Status,
      Priority=>_tab_Priority,
      Name=>_tab_Name,
      ),
    FieldTypes=>array(DateStart=>"datetime",DateEnd=>"datetime"),
    FiltersAutoReload=>true,
    Filters=>array(
      array(#Caption=>$_[],
        Variable=>'FilterComplete',
        Type=>"radio",
        Values=>array(0=>$_['MAILQUEUE_INCOMPLETE_QUEUES'],1=>$_['MAILQUEUE_COMPLETE_QUEUES']),
        Value=>$FilterComplete
        ),
      ),
    ShowCheckers=>1,
    ShowDelete=>1,
    ShowOk=>1,
    TableStyle=>1,
    Width=>'100%',
    Pages=>array(RowCount=>$RowCount,RowsPerPage=>$RowsPerPage),
    SubactionList=>array(
    	stop_selected=>$_['MAILQUEUE_STOP_SELECTED'],
    	resume_selected=>$_['MAILQUEUE_RESUME_SELECTED'],
    	priority_high=>$_['MAILQUEUE_PRIORITY_HIGH'],
    	priority_low=>$_['MAILQUEUE_PRIORITY_LOW']),
#    SubactionList=>$SubactionList,
#    HiddenFields=>array(CatalogID=>$CatalogID,MemberIDs=>$MemberIDs),
#    ColAligns=>array(Edit=>'center',ShowIt=>'center',Removed=>'center'),
    ThisObject=>&$this);
    
  $_ENV->PrintTable($q,$args);
	print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("mail.PMailSender.Bulk.b",array(ForceSending=>1))."\",w:750,h:600});'>$_[START_BULK_PROCESS]</a>";
	
}

function Edit ($args) {
	extract(param_extract(array(
		QueueID=>'*int', 
		PageNo=>'int=1', 
		RowsPerPage=>'int=20',
	  ),$args));
  $_=&$GLOBALS['_STRINGS']['mail'];
	global $_USER;

	$qq=DBQuery("SELECT * FROM mail_Queues WHERE QueueID=$QueueID");
	if (!$qq) return array(Error=>"Mail queue is absent",Details=>$QueueID);
	
  $qc=DBQuery("SELECT COUNT(*) AS RowCount FROM mail_QMessages WHERE QueueID=$QueueID");
  $RowCount=$qc->Top->RowCount;
  if (!$RowCount)
  {
  	return array(Message=>$_['MAILQUEUE_EMPTY']);
  }

  $qm=DBQuery("SELECT  m.*, CONCAT(t.Cartridge,\".\",t.Name) AS TemplateName FROM mail_QMessages m LEFT OUTER JOIN mail_Templates t ON (m.TemplateID=t.TemplateID) 
    WHERE QueueID=$QueueID LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage","QMessageID");
  
  $args=array(
    Action=>ActionURL("mail.IMailQueues.QueueEditAction.b"),
    Modal=>1,
    Fields=>array(
      FieldValues=>$_['MAILQUEUEMSG_DATA'],
      DateSent=>$_['MAILQUEUEMSG_DATESENT'],
      MailFrom=>$_['MAILQUEUEMSG_MAILFROM'],
      MailTo=>$_['MAILQUEUEMSG_MAILTO'],
      Status=>$_['MAILQUEUEMSG_STATUS'],
      TemplateName=>$_['MAILQUEUEMSG_TEMPLATE']
      ),
    FieldHooks=>array(
			FieldValues=>_tab_MsgFieldValues,
      Status=>_tab_MsgStatus,
      TemplateName=>_tab_MsgTemplate,
      ),
    FieldTypes=>array(DateSent=>"datetime"),
    ShowCheckers=>1,
    ShowDelete=>1,
    ShowCancel=>1,
    TableStyle=>1,
    Width=>'100%',
    Pages=>array(RowCount=>$RowCount,RowsPerPage=>$RowsPerPage),
    ThisObject=>&$this);	
  $_ENV->PrintTable($qm,$args);
	
}
function _tab_MsgFieldValues(&$QMessageID,&$row,$fname,$args) {
	$fv=unserialize($row->FieldValues);
	foreach ($fv as $k=>$v) {$s.="<tr><td align='right' class='tiny'><b>$k:</b></td><td class='tiny'>$v</td></tr>";}
	if ($s) print "<table>$s</table>";
	
}
function _tab_MsgStatus(&$QMessageID,&$row,$fname,$args) {
  $_=&$GLOBALS['_STRINGS']['mail'];
	$s="??";
	switch ($row->Status) {
		case 0: $s=$_['MAILQUEUEMSG_STATUS_WAITING']; break;
		case 1: $s="<font color='blue'>".$_['MAILQUEUEMSG_STATUS_SENDING']."</font>"; break;
		case 2: $s="<font color='red'><b>".$_['MAILQUEUEMSG_STATUS_ERROR']."</font></b>"; break;
		case 3: $s="<font color='#808080'>".$_['MAILQUEUEMSG_STATUS_OK']."</font>"; break;
	}
	print $s;
}
function _tab_MsgTemplate(&$QMessageID,&$row,$fname,$args) {
	print "<a href='javascript:;' onClick='W.openModal({url:\"".
  ActionURL("mail.IMailTemplates.Edit.b",array(TemplateID=>$row->TemplateID))."\",w:550,h:450,reloadOnOk:1});'>$row->TemplateName</a>";
  
}

function BrowseAction($args) {
	extract(param_extract(array(
		subaction=>'string', 
		action=>'string', 
		check=>'int_checks', 
	  ),$args));
  $_=&$GLOBALS['_STRINGS']['mail'];
	global $_USER;

	
	$QueueIDs=implode(",",array_keys($check));
	if ($subaction=='stop_selected') {
		$r=DBExec ("UPDATE mail_Queues SET Stopped=1 WHERE QueueID IN ($QueueIDs)");
	} elseif ($subaction=='resume_selected') {
		$r=DBExec ("UPDATE mail_Queues SET Stopped=0 WHERE QueueID IN ($QueueIDs)");
	} elseif ($action=='delete') {
		$r=DBExec ("DELETE FROM mail_Queues WHERE QueueID IN ($QueueIDs)");
		$r&=DBExec ("DELETE FROM mail_QMessages WHERE QueueID IN ($QueueIDs)");
	} elseif ($subaction=='priority_high') {
		$r=DBExec ("UPDATE mail_Queues SET Priority=Priority+1 WHERE QueueID IN ($QueueIDs)");
	}	elseif ($subaction=='priority_low') {
		$r=DBExec ("UPDATE mail_Queues SET Priority=Priority-1 WHERE QueueID IN ($QueueIDs)");
	} else {
		return array(Error=>"Unknown action");
	}
	if ($r) return array(ModalResult=>true);
}

function QueueEditAction($args) {
	extract(param_extract(array(
		subaction=>'string', 
		action=>'string', 
		check=>'int_checks', 
	  ),$args));
  $_=&$GLOBALS['_STRINGS']['mail'];
	global $_USER;
	
	if (($action=='delete')&& $check) {
		$ids=implode(",",array_keys($check));
		$r=DBExec ("DELETE FROM mail_QMessages WHERE QMessageID IN ($ids)");
		if (!$r) return;
	}
	return array(ModalResult=>true);
}
}


?>