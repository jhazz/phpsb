<?
class news_IGroup
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. News publishing cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
  NewsBackend=>"Edit,Browse,View",
  RemoveNews=>"UpdateNewsList"
  );

function Browse($args)
  {
  global $cfg;
  $ctx=$cfg['Settings']['news']['NewsGroupsContext'];
  $qp=DBQuery ("SELECT HomePageID FROM sys_Contexts WHERE SysContext='$ctx'");
  $HomePageID=0;
  if ($qp) { $HomePageID=intval($qp->Top->HomePageID); }

  return array(ForwardTo=>ActionURL("jsb.ISiteExplorer.Open.n",
    array(Path=>"/$ctx/$HomePageID",
    ContextLocked=>1,
    NoLayouts=>1)));
  }


function UpdateNewsList($args)
  {
  extract(param_extract(array(
    NewsGroupID=>'int',
    DateGroup=>'int',
    PageNo=>'int',
    action=>'string',
    check=>'int_checkboxes',
    subaction=>'string',
    ),$args));
  $_=$GLOBALS['_STRINGS']['news'];
  global $cfg;

  $chklist=implode (",",array_keys($check));
  if (($action=='delete')&&($check))
    {
    DBExec ("DELETE FROM news_Events WHERE NewsID IN ($chklist)");
    }
  if ($action=='ok')
    {
    if (substr($subaction,0,7)=="moveto_")
      {
      $TargetID=intval(substr($subaction,7));
      if ($TargetID)
        {
        DBExec ("UPDATE news_Events SET NewsGroupID=$TargetID WHERE NewsID IN ($chklist)");
        }
      }
    }
#  return array (ForwardTo=>ActionURL("news.IGroup.Browse",array(NewsGroupID=>$NewsGroupID,DateGroup=>$DateGroup,PageNo=>$PageNo)));
  return array (ModalResult=>true);
  }

function btab_ShowStatus(&$k,&$row)
  {
  $flagColor="#00ff00";
  $StatusLabel="";
  $toShow=format_date("<b>day&nbsp;mon</b><br>year",$row->DateToShow);
  if (time()<$row->DateToShow)
    {
    $toShow="<font color='red'>$toShow</font>";

    switch ($row->PubStatus)
      {
      case 10: $flagColor="#a0ffa0"; $StatusLabel="[OK]"; break; # too young and approved
      case 5:  $flagColor="#ffffa0"; $StatusLabel="[?]"; break; # too young and under review
      default: $flagColor="#ffb0b0"; $StatusLabel="[!]"; break; # too young but not reviewed
      }

    }
  $toHide=format_date("<b>day&nbsp;mon</b><br>year",$row->DateToHide);
  if (time()>$row->DateToHide)
    {
    $toHide="<font color='red'>$toHide</font>";
    $flagColor="#e0e0e0"; # too old
    $StatusLabel="_RIP_";
    }
  $row->toHide="<p align='center'>$toHide</p>";
  $row->toShow="<p align='center'>$toShow</p>";

  if (  (time()>=$row->DateToShow) && ((time()<=$row->DateToHide)))
    {
    switch ($row->PubStatus)
      {
      case 10: $flagColor="#00e000"; $StatusLabel="[OK]"; break; # modern and approved
      case 5:  $flagColor="#ffff00"; $StatusLabel="[??]"; break; # modern and under review
      default: $flagColor="#ff0000"; $StatusLabel="[!!]"; break; # modern but not reviewed
      }
    }
  print "<table width='100%' cellpadding='10'><tr valign='top'><td bgcolor='$flagColor' align='center'>$StatusLabel</td></tr></table>";
  }

 function btab_ShowIco(&$k,&$row)
  {
  global $cfg;
  if ($this->qico)
    {
    $NewsHeadImgURL=$cfg['FilesURL'].'/img/news.Event/imghead';
    $imgdoc=$this->qico->Rows["news.Event/imghead/$k"];
    if ($imgdoc)
      {
      $TnNames=explode_properties($imgdoc->Filenames);
      $TnURL=$NewsHeadImgURL."/".$TnNames[1];
      $ico="<img hspace=4 vspace=4 align='left' border='0' src='$TnURL'>";
      print $ico;
      }
    }
   }

 function btab_ShowContent(&$k,&$row)
  {
  global $cfg;
  $_=$GLOBALS['_STRINGS']['news'];

#  $UploadPath=$cfg['FilesPath']."/news.Uploads/$BindToInfo->Folder";
#  $UploadURL =$cfg['FilesURL'] ."/news.Uploads/$BindToInfo->Folder";

  $Title=langstr_get($row->Title);
  if (!$Title) {$Title=$_['EVENT_WITHOUT_TITLE'];}
  $Date=format_date ("shortdate",$row->DateOfNews);

  echo "<b><font color='#505030'><i>$Date</i></font> <a href='javascript:;' onClick='W.openModal({url:\"".
    ActionURL("news.IEvent.Edit.b",array(NewsID=>$row->NewsID))."\",reloadOnOk:1})'>$Title</a></b><br>";
  $BodyCharLimit=120;
  extract(param_extract(array(
  	"Intro"=>'nonesc_langstring'),$row));
  
  if (!$Intro) {$Intro="<i>$_[EVENT_WITHOUT_INTRO]</i>";}

  if (mb_strlen($Intro>$BodyCharLimit)) {
	  $s=mb_substr ($Intro,0,$BodyCharLimit-3);
    $pos=strrpos ($s," ");
    $Intro=substr ($s,0,$pos);
    $Intro.="...";
  }

  echo nl2br($Intro);
  if ($row->URL)
    {
    $url=$row->URL;
    echo "&nbsp; <br><a style='font-size:8pt;' href='$url' target='_blank'>$url</a> ";
    }

#  if ($row->UploadFile)
#    {
#    if (is_file($UploadPath))
#    echo "&nbsp; <a style='font-size:8pt;' href='$UploadURL/$row->UploadFile'>Attached file:".$row->UploadFile."</a> ";
#    }
  }

function Edit($args)
  {
  extract(param_extract(array(
    NewsGroupID=>'int',
    DateGroup=>'int',
    PageNo=>'int=1',
    ),$args));
  $_ =$GLOBALS['_STRINGS']['news'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_SYSSKIN_NAME;
  $NewsGroupName="";
  $EventsPerPage=20;
  $SysSkinURL="$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME";

  $qng=DBQuery("SELECT JSBPageID,Caption,Title
    FROM jsb_Pages
    WHERE State=1 AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."' ORDER BY OrderNo","JSBPageID");

  if ($qng)
    {
    $NewsGroupName=langstr_get($qng->Rows[$NewsGroupID]->Caption);
    }
  else
    {
    return array(Error=>"Unknown news group","Group ID = $NewsGroupID",IntruderAlert=>10);
    }

  $months=explode (',',','.$__['SHORT_MONTH_NAMES']);
  $s="SELECT DateOfNews FROM news_Events WHERE NewsGroupID=$NewsGroupID ORDER BY DateOfNews DESC";
  $q=DBQuery($s);
  $links2=array(Caption=>$_['CAPTION_BROWSE_GROUPS'],Items=>array());
  foreach ($qng->Rows as $aNewsGroupID=>$row)
    {
    $links2['Items'][]=array(Caption=>langstr_get($row->Caption),Icon=>"$SysSkinURL/tree_f.gif",
      URL=>ActionURL("news.IGroup.Edit",array(NewsGroupID=>$aNewsGroupID,DateGroup=>-1)));
    }
  $_ENV->PutRelatedLinks("links2",$links2);

  $links1=array(Caption=>$NewsGroupName,Items=>array());
  if ($q)
    {
    foreach ($q->Rows as $i=>$row)
      {
      $d=getdate ($row->DateOfNews);
      $d2=mktime (0,0,0,$d["mon"],1,$d["year"]);
      $dates["$d2"]++;
      }
    reset ($dates);
    while (list ($d2,$count)=each ($dates))
      {
      $d=getdate($d2);
      $dname=$months[$d['mon']].' '.$d['year'];
      $monthsago=floor((time()-$d2)/(60*60*24*30));
      $mago=($monthsago>0)?"<br>$monthsago $_[MONTHS_AGO]":"";
      $active=($d2==$DateGroup);
      $links1['Items'][]=array(Caption=>$dname.$mago,Bullet=>"($count)",Active=>$active,
        URL=>ActionURL("news.IGroup.Edit",array(NewsGroupID=>$NewsGroupID,DateGroup=>$d2)));
      }
    $links1['Items'][]=array(Caption=>$_['ALL_DATES_EVENTS'],Icon=>"$SysSkinURL/tree_f.gif",
      URL=>ActionURL("news.IGroup.Edit",array(NewsGroupID=>$NewsGroupID,DateGroup=>-1)));
    $_ENV->PutRelatedLinks("links1",$links1);
    }

  if (!$DateGroup) {$DateGroup=time();}
  if ($DateGroup)
    {
    if ($DateGroup!=-1)
      {
      $d=getdate ($DateGroup);
      $mi=$d["mon"]; $yi=$d["year"];
      $yi1=$yi; $mi1=$mi+1; if ($mi1>12) {$mi1=1; $yi1++;}
      $d2=mktime (0,0,0,$mi1 ,1,$yi1);
      $d1=mktime (0,0,0,$mi  ,1,$yi);
      $h3=format_date("mon year",$d1);
      $qc=DBQuery ("SELECT COUNT(*) AS NewsCount FROM news_Events WHERE NewsGroupID=$NewsGroupID AND DateOfNews>=$d1 AND DateOfNews<$d2");
      $qnews=DBQuery ("SELECT * FROM news_Events WHERE NewsGroupID=$NewsGroupID AND DateOfNews>=$d1 AND DateOfNews<$d2 ORDER BY DateOfNews DESC LIMIT ".(($PageNo-1)*$EventsPerPage).",$EventsPerPage","NewsID");
      }
    else
      {
      $h3=$_['ALL_DATES_EVENTS'];
      $qc=DBQuery ("SELECT COUNT(*) AS NewsCount FROM news_Events WHERE NewsGroupID=$NewsGroupID");
      $qnews=DBQuery ("SELECT * FROM news_Events WHERE NewsGroupID=$NewsGroupID ORDER BY DateOfNews DESC LIMIT ".(($PageNo-1)*$EventsPerPage).",$EventsPerPage","NewsID");
      }

    print "<h1>$NewsGroupName [$h3]</h1><table width='100%'><tr>
      <td><a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("news.IEvent.Edit.b",array(NewsGroupID=>$NewsGroupID))."\",reloadOnOk:1})'>$_[ADD_NEW_EVENT]</a>
      </td><td align='right'>";

    if ($qc)
      {
      $NewsCount=$qc->Top->NewsCount;
      if ($NewsCount)
        {
        print $__['CAPTION_PAGES'];
        $PagesCount=ceil($NewsCount/$EventsPerPage);
        for ($i=1;$i<=$PagesCount;$i++)
          {
          if ($i==$PageNo)
            print "$i ";
          else
            print "<a href='".ActionURL("news.IGroup.Browse",array(NewsGroupID=>$NewsGroupID,DateGroup=>$DateGroup,PageNo=>$i))."'>$i</a> ";
          }
        }
      }
    print "</td></tr></table>";

    if ($qnews)
      {
      $NewsIDs="";
      foreach ($qnews->Rows as $news_EventID=>$row)
        {
        if ($NewsIDs) $NewsIDs.=",";
        $NewsIDs.="'news.Event/imghead/$news_EventID'";
        }
      $this->qico=DBQuery ("SELECT BindTo,Filenames FROM img_Documents WHERE BindTo IN ($NewsIDs)","BindTo");
      $subactions=false;
      foreach ($qng->Rows as $aNewsGroupID=>$row)
        {
        $subactions["moveto_$aNewsGroupID"]=$_['MOVE_NEWS_TO'].langstr_get($row->Caption);
        }

      $_ENV->PrintTable($qnews,array(
      Action=>ActionURL("news.IGroup.UpdateNewsList"),
      ReloadOnOk=>1,
      Fields=>array(
        Status=>'Status',
        Ico=>"",
        Content=>$_['BACKEND_NEWS_BRIEF_CONTENT'],
        toShow=>$_['TNEWSEVENT_D_DATETOSHOW'],
        toHide=>$_['TNEWSEVENT_D_DATETOHIDE'],
        ),
      HiddenFields=>array(
        NewsGroupID=>$NewsGroupID,
        DateGroup=>$DateGroupID,
        PageNo=>$PageNo),
      ShowCheckers=>true,
      FieldHooks=>array(Status=>btab_ShowStatus, Content=>btab_ShowContent,Ico=>btab_ShowIco),
      ShowDelete=>true,
      CSS_TabHead=>"tabhead",
      CSS_Row=>"tab",
      SubactionList=>$subactions,
      ShowOk=>true,
      ThisObject=>&$this,
      TableStyle=>1
      ));
      }
    else
      {
      echo $_['NO_NEWS_EVENTS'];
      }
    }

  print "</td></tr></table></form>";
  }

}
?>


