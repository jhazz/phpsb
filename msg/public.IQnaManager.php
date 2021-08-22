<?
class msg_IQnaManager
{
var $CopyrightText="(c)2006 Messages control cartridge";
var $CopyrightURL="http://www.jhazz.com/msg";
var $ComponentVersion="1.0";

function Messages_DoAction($args)
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
        DBExec ("DELETE FROM msg_Posts WHERE PostID IN ($s)");
        }
      break;
    case 'approve':
      DBExec ("UPDATE msg_Posts SET Approved=1 WHERE PostID IN ($s)");
      break;
    }

  return array(ModalResult=>true);
  }
function BrowseThreads($args)
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


  switch ($ViewMode)
    {
    case 'all':
      $where="WHERE ParentID=0 ";
      break;
    case 'unanswered':
      $where="WHERE Answered=0 AND ParentID=0 ";
      break;
    case 'unapproved':
    default:
      $where="WHERE (Approved=0 AND PostTime>$timeago) AND ParentID=0 ";
    }

  $qc=DBQuery ("SELECT COUNT(*) AS MsgCount FROM msg_Posts $where");
  $RowCount=$qc->Top->MsgCount;
  $PageCount=ceil($RowCount/$RowsPerPage);

  $s="SELECT * FROM msg_Posts $where ORDER BY PostTime DESC LIMIT ".(($PageNo-1)*$RowsPerPage).",$RowsPerPage";
  $qm=DBQuery ($s,"PostID");
  if ($qm) $this->qa=DBQuery("SELECT * FROM msg_Posts WHERE ParentID IN (".implode (",",array_keys($qm->Rows)).")","ParentID");
 
  $_ENV->PrintTable($qm,array(
    Action=>ActionURL("msg.IQnaManager.Messages_DoAction.b",array(BindTo=>$BindTo)),
    ReloadOnOk=>1,
    FiltersAutoReload=>true,
    Filters=>array(
      array(#Caption=>$_[],
        Variable=>'ViewMode',
        Type=>"radio",
        Values=>array('unanswered'=>$_['QNA_VIEW_UNANSWERED'],'unapproved'=>$_['QNA_VIEW_UNAPPROVED'],'all'=>$_['QNA_VIEW_ALL']),
        Value=>$ViewMode,
        ),
      ),
    Fields=>array(PostTime=>"Время",Author=>"Автор",MsgText=>"Вопрос",Answer=>"Ответ",BindTo=>"Target object"),
    ShowCheckers=>1,
    FieldHooks=>array(PostTime=>_tab_PostTime,Author=>_tab_Author,MsgText=>_tab_MsgText,Answer=>_tab_Answer,BindTo=>_tab_BindTo),
    ShowDelete=>1,
    TableStyle=>1,
		HideSubmit=>1,
		OnGetCellStyle=>_tab_GetCellStyle,
    FieldHookArgs=>array(timeago=>$timeago),
    ColAligns=>array(Answered=>'center'),
    Pages=>array(RowCount=>$RowCount,RowsPerPage=>$RowsPerPage),
    Buttons=>array(array(Kind=>'ok',FormAction=>'approve',Caption=>$_['PUBLISH'])),
    ThisObject=>&$this));
  if (!$qm) {return array(Message=>$_['NO_MESSAGES']);}
  }

function _tab_GetCellStyle(&$id,&$row,$fieldname,$args=false) 
{
	if (!isset($this->qa->Rows[$id])) return 'Highlight'; else return "";
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
function _tab_Answer (&$id,&$row,$fname,$args) 
{
  $_ =&$GLOBALS['_STRINGS']['msg'];
	$a=$this->qa->Rows[$id];
	if ($a) {
		$AnswerAuthor=$a->Author;
		if ($a->UserID) {$AnswerAuthor="<a  href='javascript:;' onClick='W.openModal({url:\"".ActionURL("um.IUsers.Edit.b",array(UserID=>$a->UserID))."\",w:550,h:400})'>$AnswerAuthor</a>";}
		$s=$a->MsgText;
    if (mb_strlen($s)>200) $s=mb_substr($s,0,200)."<br>...";
		print "<b>$AnswerAuthor ответил: </b>$s <a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.b",array(PostID=>$a->PostID))."\",w:400,h:400, reloadOnOk:1})'>[$_[EDIT_MESSAGE]]</a>";
	} else {
		print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.b",array(AnswerTo=>$id))."\",w:400,h:400,reloadOnOk:1})'>[$_[WRITE_ANSWER]]</a>";		
	}
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
  print $s." <a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.b",array(PostID=>$id))."\",w:400,h:400, reloadOnOk:1})'>$_[EDIT_MESSAGE]</a>";
  }

function _tab_BindTo (&$id,&$row,$fname,$args=false)
  {
  global $cfg;
  $s=$row->$fname;
  $document=&load_document_info($s);
  if ($document)
    {
#      print_r ($document);
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
  
  /*
function _tab_Answered (&$id,&$row,$fname,$args)
  {
  if (!$row->$fname)
    {
    print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("msg.IPost.Edit.b",array(AnswerTo=>$id))."\",w:400,h:400,reloadOnOk:1})'>[write&nbsp;answer]</a>";
    }
  else
    {
    print "Answered";
    }
  }
  */
}

?>
