<?
class pub_IGlossary
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. News publishing cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  CanAddTerms=>"Add,Edit,Post,Delete"
  );

function Delete($args)
  {
  extract(param_extract(array(
    TermID=>'int',
    ),$args));
  global $_USER,$cfg;
  if (!$TermID) return array(Error=>"No termid");

  if (!$_USER->HasRole("pub:Moderator"))
    {
    $qt=DBQuery("SELECT AuthorUserID FROM pub_Glossary WHERE TermID=$TermID");
    if ($qt)
      {
      if ($qt->Top->AuthorUserID != $_USER->UserID)
        {
        print "<center><br/><font color='red'>Этот термин был опубликован не вами</font><br/><br/>";
        $_ENV->PutButton('cancel');
        return;
        }
      }
    }

  $BindTo="pub.Term/$SubjectID/$TermID";
  DBExec ("DELETE FROM pub_Glossary WHERE TermID=$TermID");
  DBExec ("DELETE FROM stdctrls_Richtexts WHERE BindTo='$BindTo'");
  return array(ModalResult=>true);

  }
function Edit($args)
  {
  extract(param_extract(array(
    SubjectID=>'int',
    TermID=>'int',
    ),$args));

  global $_USER,$cfg;

  print "<table cellpadding='10'><tr><td>";
  if ($TermID)
    {
    $qt=DBQuery("SELECT * FROM pub_Glossary WHERE TermID=$TermID");
    if ($qt)
      {
      if (!$_USER->HasRole("pub:Moderator"))
        {
        if ($qt->Top->AuthorUserID != $_USER->UserID)
          {
          print "<center><br/><font color='red'>Этот термин был опубликован не вами</font><br/><br/>";
          $_ENV->PutButton('cancel');
          return;
          }
        }

      extract(param_extract(array(Term=>'string',SubjectID=>'int',AuthorUserID=>'int'),$qt->Top));
      $ql=DBQuery ("SELECT Login FROM um_Users WHERE UserID=$AuthorUserID");
      $Login=$ql->Top->Login;
      $BindTo="pub.Term/$SubjectID/$TermID";
      $q=DBQuery ("SELECT DocID,Content FROM stdctrls_Richtexts WHERE BindTo='$BindTo'","DocID");
      if ($q)
        {
        $Content=preg_replace(
          array("'<i>'i","'</i>'i","'<b>'i","'</b>'i","'<p class=\"notice\">'i","'</p>'i","'<li>'i","'</li>'i"),
          array("'[i]","[/i]","[b]","[/b]","[quote]","[/quote]","[li]" ,"[/li]" ),
          $q->Top->Content);

        }
      }
    }
  $qsubj=DBQuery ("SELECT JSBPageID AS SubjectID,Caption FROM jsb_Pages
    WHERE State=1 AND SysContext='".$cfg['Settings']['pub']['SubjectsContext']."' ORDER BY OrderNo","SubjectID");
  $subjselect="";
  if ($qsubj)
    {
    foreach ($qsubj->Rows as $aSubjectID=>$subject)
      {
      $subjselect.="<option value='$aSubjectID' ".(($SubjectID==$aSubjectID)?"selected":"").">$subject->Caption</option>";
      }
    if ($subjselect) $subjselect="Тема словаря: <select name='SubjectID'>$subjselect</select>";
    }


  print "<form method='post' action='".ActionURL("pub.IGlossary.Post")."' onSubmit='this.target=W.openModal({modalOkOnOk:1});'>
  $subjselect  <br/>
  <br/>Термин от <i>$Login</i>:<br/>
  <input type='text' name='Term' class='inputarea' size='80' maxlength='200' value='$Term'>
  <br/>Описание термина:<br/>
  <textarea name='Content' class='inputarea' cols='80' rows='20'>$Content</textarea><br/>
  <input type='hidden' name='TermID' value='$TermID'
  <p class='notice'>
  [b]выделить жирным[/b] &nbsp; [i]наклонный[/i] &nbsp; [quote]примечание[/quote]<br/>
  [li]элемент списка[/li] &nbsp; [term]другой термин из словаря[/term]
  </p>".$_ENV->PutButton(array(Kind=>'ok',Action=>'submit',ToString=>1))."</td></tr>
  </table></form></td></tr></table>";

  }

function Post($args)
  {
  extract(param_extract(array(
    SubjectID=>'int',
    TermID=>'int',
    Term=>'string',
    Content=>'string',
    PubStatus=>'int=0',  #1 - published
    Title=>'string', #page title
    ),$args));

  $_=$GLOBALS['_STRINGS']['pub'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_USER;


  if (!$Term)
    {
    print "<center><br/><font color='red'>Вы не ввели название самого термина</font><br/><br/>";
    $_ENV->PutButton('cancel');
    return;
    }

  if (!$TermID)
    {
    if (!$SubjectID){return array(Error=>"Subject not selected for new term!");}
    if (!$Term){return array(Error=>"Term has no description!");}
    $TermID=DBGetID("pub.Term",$SubjectID);
    $q=DBQuery ("SELECT TermID,Term FROM pub_Glossary WHERE Term LIKE '$Term%'");
    if ($q)
      {
      print "<center><br/><font color='red'>Такой термин уже есть '".$q->Top->Term."'</font><br/><br/>";
      $_ENV->PutButton('cancel');
      return;
      }

    $BindTo="pub.Term/$SubjectID/$TermID";
    $ok=DBExec ("INSERT INTO pub_Glossary (TermID,SubjectID,PubStatus,Term,AuthorUserID) VALUES ($TermID,$SubjectID,$PubStatus,'$Term',$_USER->UserID)");
    $DocID=DBGetID("stdctrls.Richtext");
    }
  else
    {
    if (!$_USER->HasRole("pub:Moderator"))
      {
      $qa=DBQuery("SELECT AuthorUserID FROM pub_Glossary WHERE TermID=$TermID");
      if ($qa)
        {
        if ($qa->Top->AuthorUserID != $_USER->UserID)
          {
          print "<center><br/><font color='red'>Этот термин не был опубликован вами</font><br/><br/>";
          $_ENV->PutButton('cancel');
          return;
          }
        }
      }

    $BindTo="pub.Term/$SubjectID/$TermID";
    $ok=DBExec("UPDATE pub_Glossary SET Term='$Term',SubjectID=$SubjectID WHERE TermID=$TermID");
    $q=DBQuery ("SELECT DocID FROM stdctrls_Richtexts WHERE BindTo='$BindTo'","DocID");
    $DocID=$q->Top->DocID;
    }

  if ($ok)
    {
    $Content=preg_replace(
    array("'\[i\]'i","'\[/i\]'i","'\[b\]'i","'\[/b\]'i","'\[quote\]'i","'\[/quote\]'i","'\[li\]'i" ,"'\[/li\]'i"),
    array('<i>','</i>','<b>','</b>','<p class="notice">','</p>','<li>','</li>'),$Content);
    DBReplace(array(Table=>'stdctrls_Richtexts',Values=>array(BindTo=>$BindTo,Title=>$Term,Content=>$Content),
    Keys=>array(DocID=>$DocID)));
    return array(ModalResult=>true);
    }
  } # end of funcion


}



?>
