<?
class msg_IRateManager
{
var $CopyrightText="(c)2006 Messages control cartridge";
var $CopyrightURL="http://www.jhazz.com/msg";
var $ComponentVersion="1.0";

function DoAction($args)
  {
  extract(param_extract(array(
    check=>"int_checkboxes",
    action=>"string"
    ),$args));

  $s=implode (",",array_keys($check));
  switch ($action)
    {
    case 'delete':
      if ($s)
        {
        DBExec ("DELETE FROM msg_Rateit WHERE RateMsgID IN ($s)");
        }
      break;
    case 'approve':
      DBExec ("UPDATE msg_Rateit SET Approved=1 WHERE RateMsgID IN ($s)");
      break;
    }

  return array(ModalResult=>true);
  }
function Browse($args)
  {
  extract(param_extract(array(
    ViewMode=>"string=unapproved",
    BindTo=>'string',
    PageNo=>'int=1'
    ),$args));

  global $cfg;
  $_ =&$GLOBALS['_STRINGS']['msg'];

  $QuarantineTime=intval($cfg['Settings']['msg']['QuarantineTime'])*60*60;
  $timeago=time()-$QuarantineTime;
  $RowsPerPage=20;


  $where="";
  if ($ViewMode=='unapproved') $where="WHERE (Approved=0 AND PostTime>$timeago)";
  $qc=DBQuery ("SELECT COUNT(*) AS MsgCount FROM msg_Rateit $where");
  $RowCount=$qc->Top->MsgCount;
  $PageCount=ceil($RowCount/$RowsPerPage);

  print $s;
  $s="SELECT * FROM msg_Rateit $where ORDER BY PostTime DESC LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
  $qm=DBQuery ($s,"RateMsgID");
  
  $_ENV->PrintTable($qm,array(
    Action=>ActionURL("msg.IRateManager.DoAction",array(BindTo=>$BindTo)),
    ReloadOnOk=>1,
    FiltersAutoReload=>true,
    Filters=>array(
      array(#Caption=>$_[],
        Variable=>'ViewMode',
        Type=>"radio",
        Values=>array('unapproved'=>$_['QNA_VIEW_UNAPPROVED'],'all'=>$_['QNA_VIEW_ALL']),
        Value=>$ViewMode,
        ),
      ),
    Fields=>array(PostTime=>"Время",Rate=>'Оценка',Author=>"Сообщение",MsgText=>"Вопрос",BindTo=>"Target object"),
    ShowCheckers=>1,
    FieldHooks=>array(PostTime=>_tab_PostTime,Author=>_tab_Author,MsgText=>_tab_MsgText,BindTo=>_tab_BindTo),
    ShowDelete=>1,
    TableStyle=>1,
		HideSubmit=>1,
		OnGetCellStyle=>_tab_GetCellStyle,
    FieldHookArgs=>array(timeago=>$timeago),
    ColAligns=>array(Answered=>'center'),
    Pages=>array(RowCount=>$RowCount,RowsPerPage=>$RowsPerPage),
		ButtonEdit=>array(ModalWindowAction=>"msg.IRateit.Edit.b",KeyName=>"RateMsgID"),
    Buttons=>array(array(Kind=>'ok',FormAction=>'approve',Caption=>$_['PUBLISH'])),
    ThisObject=>&$this));
  if (!$qm) {return array(Message=>$_['NO_MESSAGES']);}
  }

function _tab_GetCellStyle(&$id,&$row,$fieldname,$args=false) 
{
	
	if ($row->Approved==1) return 'Highlight'; else return "";
}
function _tab_PostTime (&$id,&$row,$fname,$args=false)
  {
  print format_date("shortdate hh:mm",$row->$fname);
  }
function _tab_Author (&$id,&$row,$fname,$args=false)
  {
  $s=$row->$fname;
  if ($row->UserID) {print "<a  href='javascript:;' onClick='W.openModal({url:\"".ActionURL("um.IUsers.Edit.b",array(UserID=>$row->UserID))."\",w:550,h:400})'>$s</a>";}
  else print "<b>$s</b>";
  if ($row->Phone) {print "<br/><b>Тел:</b>$row->Phone";}
  if ($row->Email) {print "<br/><a href='mailto:$row->Email'>$row->Email</a><br>";}
  
  }

function _tab_MsgText (&$id,&$row,$fname,$args)
  {
  $_ =&$GLOBALS['_STRINGS']['msg'];
  $s=$row->$fname;
  if (($row->Approved==0 ) && ($args['timeago']<$row->PostTime))
    {
    $s="<span style='color:#808080'>$s<br><font color='red'>In quarantine</font></span>";
    }
  else
    {
    if (strlen($s)>200) $s=substr($s,0,200)."<br>...";
    }
  print $s;#." <a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.b",array(PostID=>$id))."\",w:400,h:400, reloadOnOk:1})'>$_[EDIT_MESSAGE]</a>";
  }

function _tab_BindTo (&$id,&$row,$fname,$args=false)
  {
  global $cfg;
  $s=$row->$fname;
  print $s;
	return;
  $document=&load_document_info($s);
  if ($document)
    {
    $s=langstr_get($document->Caption);
    $ctx=$document->_Class->SiteContext;
    if ($ctx) {$s="<a href='".$cfg['RootURL'].'/'.$cfg['OpeningDoor']."/$ctx/$document->IndexID.html' target='_blank'>$s</a>";}
    print "<b>$s</b><br><i>".$document->_Class->ClassCaption."</i>";
    }
  else
    {
    print $s.'<br>';
    }
  }
}

?>
