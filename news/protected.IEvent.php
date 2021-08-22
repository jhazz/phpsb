<?
class news_IEvent
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. News publishing cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  NewsBackend=>"Edit,Post,GetAlbumFormats",

  );

function news_IEvent()
  {
  $_=$GLOBALS['_STRINGS']['news'];
  $this->Title=$_['NEWS_CARTRIDGETITLE'];
  }


function UpdateMedia($args) {
	#print_r($args);
	$s="UPDATE news_Events SET UploadFile='y:$args[YouTube]' WHERE NewsID=$args[NewsID]";
	DBexec($s);
	return array(ModalResult=>true);
}
function Post($args)
  {
  extract(param_extract(array(
    NewsID=>'int',
    NewsGroupID=>'int=0',
    Title=>'string',
    Body=>'string',
    Author=>'string',
    Intro=>'string',
    Keywords=>'string',
    DateOfNews_month=>'int',
    DateOfNews_day=>'int',
    DateOfNews_year=>'int',
    DateToShow_month=>'int',
    DateToShow_day=>'int',
    DateToShow_year=>'int',
    DateToHide_month=>'int',
    DateToHide_day=>'int',
    DateToHide_year=>'int',
    PubStatus=>'int',
    SubjectID=>'int',
    AuthorHTTP=>'string',
    URL=>'string',
    ),$args));

  $_=$GLOBALS['_STRINGS']['news'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_USER;

  
  if ($NewsID)
    {
    $qnews=DBQuery ("SELECT * FROM news_Events WHERE NewsID=$NewsID");
    if (!$qnews)
      {
      return array(Error=>"Unknown news page",Details=>$NewsID);
      }
    $NewsGroupID=$qnews->Top->NewsGroupID;
    }

  $DateOfNews=mktime(0,0,0,$DateOfNews_month,$DateOfNews_day,$DateOfNews_year);
  $DateToShow=mktime(0,0,0,$DateToShow_month,$DateToShow_day,$DateToShow_year);
  $DateToHide=mktime(0,0,0,$DateToHide_month,$DateToHide_day,$DateToHide_year);

  if ($NewsID)
    {
    $s="UPDATE news_Events SET Title='$Title',Intro='$Intro',
       DateOfNews=$DateOfNews,
       DateToShow=$DateToShow,
       DateToHide=$DateToHide,
       URL='$URL',Author='$Author',
       AuthorHTTP='$AuthorHTTP',
       Keywords='$Keywords',
       ModifyByUserID=$_USER->UserID,
       PubStatus=$PubStatus,
       SubjectID=$SubjectID,
       DateModified=".time()." WHERE NewsID=$NewsID";
    if (DBExec ($s))
      return array(ForwardTo=>ActionURL("news.IEvent.Edit.b",array(PageTab=>'text',NewsGroupID=>$NewsGroupID,NewsID=>$NewsID)));
    }
  else
    {
    $NewsID=DBGetID("news.Event");
    DBExec ("INSERT INTO news_Events (NewsID,NewsGroupID,DateOfNews,Title,URL,Author,Keywords,
     Intro,CreateByUserID,ModifyByUserID,DateModified,DateToShow,DateToHide,PubStatus,SubjectID)
            VALUES ($NewsID,$NewsGroupID,$DateOfNews,'$Title','$URL','$Author','$Keywords',
            '$Intro',$_USER->UserID,$_USER->UserID,".time().",$DateToShow,$DateToHide,$PubStatus,SubjectID
            )");

    return array(ForwardTo=>ActionURL("news.IEvent.Edit.b",array(PageTab=>'text',NewsGroupID=>$NewsGroupID,NewsID=>$NewsID)));
    }
  }

function Edit($args)
  {
  extract(param_extract(array(
    NewsID=>'int',
    NewsGroupID=>'int=0',
    PageTab=>'string=main'
    ),$args));
  $_ =&$GLOBALS['_STRINGS']['news'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  $NewsGroupName="";
  global $MachineKey;
  if ($NewsID)
    {
    $qnews=DBQuery ("SELECT * FROM news_Events WHERE NewsID=$NewsID");
    if (!$qnews)
      {
      return array(Error=>"Unknown news page",Details=>$NewsID);
      }
    $NewsGroupID=$qnews->Top->NewsGroupID;
    }

  $qg=DBQuery ("SELECT Caption FROM jsb_Pages WHERE SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."' AND JSBPageID=$NewsGroupID");
  if (!$qg)
    {
    return array(Error=>"Unknown news group",Details=>$NewsGroupID);
    }
  $NewsGroupName=langstr_get($qg->Top->Caption);

  echo "<h1>$NewsGroupName</h1><table width='100%'><tr><td align='center'>";
  $PageTabs=array(
    main    =>$_['TITLE_EDITNEWS'],
    text    =>$_['TITLE_RICHTEXT'],
    headimg =>$_['HEADIMAGE'],
    album   =>$_['TITLE_IMAGEINDEX'],
    media   =>'Media'
    );

  if (!$NewsID)
    {
    print "<h2>$_[TITLE_INSERTNEWS]</h2>";
    $_ENV->SetWindowOptions(array(Width=>800, Height=>560, Title=>$_['TITLE_INSERTNEWS']));

    $DateOfNews=getdate(time());
    $DateToShow=getdate(time());
    $DateToHide=getdate(time()+intval($cfg['Settings']['news']['MonthsToShow'])*60*60*24*30);
    $CreateByUserID=$GLOBALS['_USER']->UserID;
    $ModifyByUserID=0;
    }
  else
    {
    $s="";

    $DateOfNews=getdate($qnews->Top->DateOfNews);
    $DateToShow=getdate($qnews->Top->DateToShow);
    $DateToHide=getdate($qnews->Top->DateToHide);

    $ChangesHTML="";
    $l=$CreateByUserID;

    extract(param_extract(array(
      URL=>'string',
      Author=>'string',
      Icon=>'string',
      UploadFile=>'string',
      Title=>'nonesc_string',
      Intro=>'nonesc_string',
      Keywords=>'string',
      AuthorHTTP=>'string',
      DateModified=>'int',
      CreateByUserID=>'int',
      ModifyByUserID=>'int',
      SubjectID=>'int',
      ),$qnews->Top));

    print "<h2>".langstr_get($Title)."</h2>";
    $_ENV->SetWindowOptions(array(Width=>800, Height=>570, Title=>langstr_get($Title)));
    foreach ($PageTabs as $p=>$linkcaption) {
      $cl="class='bgup'";
      if ($p!=$PageTab) {
        $linkcaption="<a href='".ActionURL("news.IEvent.Edit.b",
          array(NewsID=>$NewsID,NewsGroupID=>$NewsGroupID,PageTab=>$p))."'>$linkcaption</a>";
        $cl="class='bgdown'";;
        }
      $s.="<td > </td><td nowrap $cl>$linkcaption</td>";
    }
    print "<table width='100%' cellpadding='10' cellspacing='0'  border='0'><tr>$s<td></td></tr>
    <tr><td colspan='20' height='350' class='bgup' align='center'>";

    if ($l)
      {
      if ($ModifyByUserID) {$l.=",".$ModifyByUserID;}
      $q=DBQuery ("SELECT UserID,Login FROM um_Users WHERE UserID IN ($l)","UserID");
      if ($q)
        {
        $ChangesHTML="<tr><td  align='right'>Author:</td><td>".$q->Rows[$CreateByUserID]->Login."</td></tr>";
        if ($ModifyByUserID)
          {
          $ChangesHTML.="<tr><td  align='right' nowrap>Modified by:</td><td>".$q->Rows[$ModifyByUserID]->Login."</td></tr>";
          }
        }
      }
    if ($DateModified)
      {
      $ChangesHTML.="<tr><td nowrap align='right'>Last edit:</td><td nowrap>".format_date("day mon year",$DateModified)."</td></tr>";
      }
    if ($ChangesHTML)
      {
      $ChangesHTML="<table width='100%' border=0>$ChangesHTML</table>";
      }
    }

  switch ($PageTab)
    {
    case 'main':
    $s="SELECT JSBPageID AS SubjectID,Caption FROM jsb_Pages
      WHERE State=1 AND SysContext='".$cfg['Settings']['news']['SubjectsContext']."' ORDER BY OrderNo";
    $qsubj=DBQuery ($s,"SubjectID");
    if ((!$SubjectID)&&($qsubj)) $SubjectID=intval($qsubj->Top->SubjectID);
    $_ENV->PutValueSet(array(ValueSetName=>'subjects', Recordset=>$qsubj, CaptionField=>"Caption" ));
    $_ENV->OpenForm(array(Name=>"form1",ShowCancel=>1,Action=>ActionURL("news.IEvent.Post.b"),Style=>"clear"));
    print "<script>var monthNames='$__[SHORT_MONTH_NAMES]';</script>
    <script src='".$cfg['PublicURL']."/sys/TDateField.js'></script>";
    $statuses[0] =$_['PUBSTATUS_0'];
    $statuses[5] =$_['PUBSTATUS_5'];
    $statuses[10]=$_['PUBSTATUS_10'];
    $StatusHTML="";
    foreach ($statuses as $PubStatusID=>$StatusCaption)
      {
      $sel=""; if ($PubStatusID==$qnews->Top->PubStatus) {$sel=" CHECKED";}
      $StatusHTML.="<tr valign='top'><td class='bgdown'><input type='radio' name='PubStatus' $sel value='$PubStatusID'></td><td class='bgdown'>$StatusCaption</td></tr>";
      }
    $StatusHTML="<table width='100%'><tr><td></td><td><b>$_[PUBSTATUS]</b></td></tr>$StatusHTML</table>";

    print "<table width='500' border=0 cellspacing='2' cellpadding='0'>
    <tr><td width='150' align='right'><b>$_[TNEWSEVENT_D_DATE]</b></td><td><script>InputDate('form1','DateOfNews',$DateOfNews[mday],$DateOfNews[mon],$DateOfNews[year],0);</script></td><td>&nbsp;&nbsp;$_[TNEWSEVENT_D_SUBJECT]</td><td>";
    $_ENV->PutFormField(array(Type=>'droplist',Style=>'clear',Size=>30,ValueSetName=>'subjects', Name=>"SubjectID",Value=>$SubjectID));
    print "</td><td rowspan='10' bgcolor='#c0c0c0'>&nbsp;</td><td width='150' rowspan='8' align='right' valign='top' nowrap >$_[TNEWSEVENT_D_DATETOSHOW]
    <br><script>InputDate('form1','DateToShow',$DateToShow[mday],$DateToShow[mon],$DateToShow[year],0);</script>
    <br>$_[TNEWSEVENT_D_DATETOHIDE]
    <br><script>InputDate('form1','DateToHide',$DateToHide[mday],$DateToHide[mon],$DateToHide[year],0);</script>
    <br><br>$ChangesHTML<br>$StatusHTML</td></tr>
    <tr><td width='100' align='right'><b>$_[TITLE]</b></td><td colspan='3'>";
    $_ENV->PutFormField(array(Type=>'langstring', Style=>'clear',Name=>"Title",Value=>$Title,Required=>1,MaxLength=>200,Size=>70));
    print "</td></tr>
    <tr valign='top'><td align='right'><b>$_[INTRO]</b></td><td colspan='3'>";
    $_ENV->PutFormField(array(Type=>'langtext', Style=>'clear',Name=>"Intro",Value=>$Intro,Size=>'70', Rows=>3));
    print "</td></tr><tr><td width='150' align='right'>$_[AUTHOR]</td><td colspan='3'>
    <table width='100%' cellpadding='0' cellspacing='0'><tr><td>";
    $_ENV->PutFormField(array(Type=>'langstring', Style=>'clear',Name=>"Author",Value=>$Author,MaxLength=>100,Size=>15));
    print "</td><td nowrap>&nbsp;$_[AUTHOR_HTTP]</td><td width='100%'><input class='inputarea' type='text' name='AuthorHTTP' style='width:100%' value='$AuthorHTTP'></td></tr>
    </table></td></tr><tr><td></td><td colspan='3'>$_[KEYWORDS]<br><input class='inputarea' maxlength='100' style='width:100%' type='text' name='Keywords' value='$Keywords'></td></tr>
  <tr valign='bottom'><td></td><td colspan='3'>$_[WILL_BE_HREF_TO]
    <input class='inputarea' size='40' style='width:100%' maxlength='250' type='text' name='URL' value='$URL'></td></tr>
  </table>
  <input type='hidden' name='NewsID' value='$NewsID'>
  <input type='hidden' name='NewsGroupID' value='$NewsGroupID'>";
  break;
    case 'text':
      $view_richtext=ActionURL("stdctrls.IRichtext.View.f",array(
        EditMode=>1,
        BindTo=>"news.Event/$NewsID"));
      print "<iframe id='framerichtext' name='framerichtext' width='100%' height='300' src='$view_richtext'></iframe>";
      break;

    case 'album':
      if ($_ENV->IsCartridgeActive("img"))
        {
        $view_imgindex=ActionURL("img.IImgIndex.View.f",
        array(
          EditMode=>1,
          BindTo=>"news.Event/imgindex/$NewsID",
          Insertable=>1,
          Selectable=>1,
          ShowCaptions=>true,
          CSS_Caption=>"p.mini",
          ));
        print "<iframe name='album' width='100%' height='300' src='$view_imgindex'></iframe>";
        }
      break;

      
    case 'headimg':
      $IImage=&$_ENV->LoadInterface("img.IImage");
      if ($IImage)
        {
        $IImage->View(array(BindTo=>"news.Event/imghead/$NewsID",
          TnFormatNo=>1,ShowCaption=>0,EditMode=>1));
        }

/*
      if ($_ENV->IsCartridgeActive("img"))
        {
        $view_headimg=ActionURL("img.IImgIndex.View.f",
          array(
            Insertable=>1,
            EditMode=>1,
            BindTo=>"news.Event/imghead/$NewsID",
          ));
        }
      print "<iframe name='headimg' src='$view_headimg' width='100%' height='300'></iframe>";
      */
  	
      break;
    case 'media':
    	$YouTube="";
    	if (substr($UploadFile,0,2)=='y:') $YouTube=substr($UploadFile,2);
      $_ENV->OpenForm(array(Name=>"form1",ShowCancel=>1,Action=>ActionURL("news.IEvent.UpdateMedia.b"),Style=>"clear"));
      print "Код фильма с YouTube (текст после http://youtube.com/watch?v=....)<br>";
      $_ENV->PutFormField(array(Type=>'string', Style=>'clear',Name=>"YouTube",Value=>$YouTube,Size=>'70', Rows=>3));
#      $_ENV->CloseForm();
    	print "<input type='hidden' name='NewsID' value='$NewsID'>";
      break;
    	
    } # end of switch by PageTab
  print "</td></tr></table><br><table width='100%'><tr><td align='right'>";
  if (($PageTab=='main')||($PageTab=='media'))
    {
    $_ENV->CloseForm();
#    $_ENV->PutButton(array(Action=>"submit")); else $_ENV->PutButton(array(Action=>"ok"));
    }
  $_ENV->PutButton(array(Action=>"ok",Caption=>$__['CAPTION_CLOSE']));
  print "</td></tr></table>";
  #</form>";
  } # end of funcion

function GetAlbumFormats()
  {
  $IImage=&$_ENV->LoadInterface("img.IImage");
  $r=$IImage->_getFormatInfo(array(BindTo=>"news.Event/imghead/"));
  if (!$r['Error'])
    {
    $qf=&$r[qf];
    $result=false;
    if (!$qf) {return false;}
    foreach ($qf->Rows as $TnFormatNo=>$row)
      {
      $s="$TnFormatNo:";
      if (!$TnFormatNo) $s="(original):";
      $result[$TnFormatNo]=$s.$row->Caption;
      }
    return array(ListValues=>$result);
    }

  }

}



?>
