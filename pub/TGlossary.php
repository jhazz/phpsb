<?php
class pub_TGlossary
{
var $CopyrightText="(c)2006 PHPSB. Users publications";
var $CopyrightURL="http://www.phpsb.com/pub";
var $ComponentVersion="1.0";

var $Data=false;

function InitComponent()
  {
  $_ =&$GLOBALS['_STRINGS']['pub'];
  $this->Propdefs=array(
    CaptionAddTerm=>array(Type=>"Caption",Required=>true,DefaultValue=>$_['ADD_TERM']),
    CaptionEditTerm=>array(Type=>"Caption",Required=>true,DefaultValue=>$_['EDIT_TERM']),
    Charsets=>array(Type=>"String",Required=>true,DefaultValue=>"A-Z,А-Я",Caption=>$_['GLOSSARY_CHARSETS']),
    CanChangeSubject=>array(Type=>"Boolean"),
    );
  $this->Datadefs=array(
    SubjectName=>array(DataType=>"String",Caption=>"Название выбранной темы"),
    SubjectImage=>array(DataType=>"img.Image",Caption=>"Титульное изображение темы"),
    );
  }


function Init (&$Control)
  {
  global $cfg,$_USER,$_SESSION;
  $_ =&$GLOBALS['_STRINGS']['pub'];
  $__=&$GLOBALS['_STRINGS']['_'];

  $Control->SubjectID=$Control->Arguments['s'];
  $Control->FirstChar=$Control->Arguments['c'];
  if ($Control->DesignMode)
    {
    $Control->Data['SubjectImage']="schedule/design/";
    }


  $DesignID=1;
  $Control->Data['SubjectImage']="schedule/design/$DesignID";
  if ($Control->SubjectID)
    {
    $s="SELECT ImgID FROM schedule_Images WHERE DesignID=$DesignID AND SubjectID=$Control->SubjectID";
    $qimg=DBQuery ($s);
    if ($qimg) $Control->Data['SubjectImage'].="!".$qimg->Top->ImgID;
    }

  $this->qsubj=DBQuery ("SELECT JSBPageID AS SubjectID,Caption FROM jsb_Pages
    WHERE State=1 AND SysContext='".$cfg['Settings']['pub']['SubjectsContext']."' ORDER BY OrderNo","SubjectID");
  $this->qtc=DBQuery("SELECT SubjectID,COUNT(*) as TCount FROM pub_Glossary GROUP BY SubjectID","SubjectID");

  if ($this->qsubj)
    {
    if (!$Control->SubjectID) $Control->SubjectID=intval($this->qsubj->Top->SubjectID);

    $s=$this->qsubj->Rows[$Control->SubjectID];
    if ($s) $Control->Data['SubjectName']=$s->Caption;
    }
  return array(DisableCache=>true);
  }

function Render (&$Control)
  {
#  $_ENV->InitWindows();
  global $cfg,$_USER,$_SESSION;
  $_ =&$GLOBALS['_STRINGS']['pub'];
  $__=&$GLOBALS['_STRINGS']['_'];
  extract ($Control->Properties);

  if ($CanChangeSubject)
    {
    if ($this->qsubj)
      {
      $subjselect="";
      foreach ($this->qsubj->Rows as $aSubjectID=>$subject)
        {
        $add="";
        if ($this->qtc) {$TCount=$this->qtc->Rows[$aSubjectID]; if ($TCount)$add=" ($TCount->TCount)"; }
        $subjselect.="<option value='$aSubjectID' ".(($Control->SubjectID==$aSubjectID)?"selected":"").">$subject->Caption$add</option>";
        }
      if ($subjselect) $subjselect="Тема словаря: <select onChange='location.href=\"$Control->JSBPageID|$Control->JSBPageControlID"."_s=\"+this.options[this.selectedIndex].value+\".$cfg[VirtualExtension]\"' name='SubjectID'>$subjselect</select>";
      }
    print $subjselect;
    }
  print "<table cellpadding='5' width='100%'><tr><td align='right'><font color='#808080'>";
  if ($Control->SubjectID) $subjq=" WHERE SubjectID=$Control->SubjectID";
  $qc=DBQuery ("SELECT UPPER(LEFT(Term,1)) fchar, COUNT(*) as ccount FROM pub_Glossary $subjq GROUP BY fchar ORDER BY fchar","fchar");

#  for ($char=0x41;$char<=0x5A;$char++)
  $carr=explode (",",$Charsets);
  foreach ($carr as $cset)
    {
    print "<br/>";
    list($from,$to)=explode ("-",$cset);
    if (!$to) $to=$from;
    for ($char=ord($from);$char<=ord($to);$char++)
      {
      $chr=chr($char);
      $row=$qc->Rows[$chr];
      if ($char==$Control->FirstChar)
        {
        $chr="<font color='#ff8000'><b>[$chr]</b></font>";
        }
      else
        {
        if ($row)
          {
          $chr="<a href='$Control->JSBPageID|$Control->JSBPageControlID"."_s=$Control->SubjectID"."_c=$char.$cfg[VirtualExtension]' title='Определений: $row->ccount'>$chr</a>";
          }
        }
      print $chr." ";
      }
    }

  print "</font></td></tr></table>";
  $chr="";
  if ($Control->FirstChar)
    {
    $chr=strtoupper(chr($Control->FirstChar));
    if ($chr=="'") $chr="";
    }
  if ($chr)
    {
    $subjq="";
    if ($Control->SubjectID) $subjq=" AND SubjectID=$Control->SubjectID";
    $s="SELECT * FROM pub_Glossary WHERE UPPER(LEFT(Term,1))='$chr' $subjq ORDER By Term";
    $qt=DBQuery ($s,"TermID");
    if ($qt)
      {
      $binds="";
      foreach ($qt->Rows as $aTermID=>$term)
        {
        $binds.=(($binds)?",":"")."'pub.Term/$Control->SubjectID/$aTermID'";
        }

      $qc=DBQuery ("SELECT BindTo,DocID,Content FROM stdctrls_Richtexts WHERE BindTo IN ($binds)","BindTo");

      foreach ($qt->Rows as $aTermID=>$term)
        {
        $content=&$qc->Rows["pub.Term/$Control->SubjectID/$aTermID"];
        if ($content)
          {
          preg_replace_callback("|\[term\](.*?)\[/term\]|",create_function('$matches','global $TERMS; $TERMS[strtoupper(trim($matches[1]))]=1;'),langstr_get($content->Content));
          }
        }
      global $TERMS,$qtm;
      if ($TERMS)
        {
        $s="";
        foreach ($TERMS as $i=>$t) $s.=(($s)?" OR ":"")."(Term LIKE '%$i%')";
#        print $s;
        $qtm=DBQuery("SELECT SubjectID,TermID,Term FROM pub_Glossary WHERE $s","Term");
        }
      foreach ($qt->Rows as $aTermID=>$term)
        {
        print "<a name='term$aTermID'></a><h4>".langstr_get($term->Term)."</h4>";
        $content=&$qc->Rows["pub.Term/$Control->SubjectID/$aTermID"];
        if ($content)
          {
          $s=nl2br(langstr_get($content->Content));
          $s=preg_replace_callback("|\[term\](.*?)\[/term\]|",create_function('$matches',
            'global $qtm; $st=strtoupper(trim($matches[1]));
            $t=$qtm->Rows[$st];
            $char=substr($st,0,1);
            if ($t) return "<a href=\''
            .$Control->JSBPageID.'|'.$Control->JSBPageControlID.'_s='.$Control->SubjectID.'_c=".ord($char).".'.$cfg['VirtualExtension'].'#term$t->TermID\'>$t->Term</a>";
            else  return "<b>$matches[1]</b>";'),$s);
          print "<p>".$s;
          }
        else
          {
          print "<p><i>(текст определения отсутствует)</i>";
          }
        if (($term->AuthorUserID==$_USER->UserID)||($_USER->HasRole("pub:Moderator")))
          {
          print "<table width='100%'><tr><td align='right'><a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("pub.IGlossary.Edit",array(TermID=>$aTermID))."\",w:600,h:500,reloadOnOk:1});'>Редактировать</a> |
           <a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("pub.IGlossary.Delete",array(TermID=>$aTermID))."\",reloadOnOk:1});'>Удалить</a></td></tr></table><hr/>";
          }
        print "</p>";
        }
      }
    }
  else
    {
    print "<h4>Выберите раздел и первую букву</h4><p>Нажмите одну из букв-ссылок справа</p>";
    }

  if ($_USER->UserID)
    {
    if (($Control->SubjectID)&&($_USER->HasRole("pub:CanAddTerms")))
      {
  print "<br/>";
  print "
  <a href='javascript:;' onClick='document.getElementById(\"addterm\").style.display=\"block\";'>Добавить новый термин</a>
  <div id='addterm' style='display:none'>
  <form method='post' action='".ActionURL("pub.IGlossary.Post")
    ."' onSubmit='this.target=W.openModal({reloadOnOk:1});'>
  <br/>Термин от <i>$_USER->Login</i>:<br/>
  <input type='text' name='Term' class='inputarea' size='80' maxlength='200'>
  <br/><br/>Описание термина:<br/>
  <textarea name='Content' class='inputarea' cols='80' rows='20'></textarea><br/>
  <input type='hidden' name='SubjectID' value='$Control->SubjectID'
  <p class='notice'>
  [b]выделить жирным[/b] &nbsp; [i]наклонный[/i] &nbsp; [quote]примечание[/quote]<br/>
  [li]элемент списка[/li] &nbsp; [term]другой термин из словаря[/term]
  </p>".$_ENV->PutButton(array(Kind=>'add',Action=>'submit',ToString=>1))."</td></tr>
  </table></form>
  </div>";
      }
    }
/*  else
    {
    print "<h3>Добавьте свой термин</h3><p>Вы можете добавить свой термин в словарь. Только для начала Вам нужно <a href='1309.html'>Зарегистрироваться</a></p>";
    }
*/
  }
function registerTerms($t)
  {
  print "{".$t."} ";

  }
} # end of class


?>
