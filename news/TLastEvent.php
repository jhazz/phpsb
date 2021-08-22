<?php
class news_TLastEvent
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. News system cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][news];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;

  $q=DBQuery ("SELECT JSBPageID,Caption FROM jsb_Pages WHERE State=1 AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."'","JSBPageID");
#    $BindToGroupList=array(''=>$_[NEWSBROWSER_BIND_TO_THIS_PAGE]);
  if ($q)
    {
    foreach ($q->Rows as $JSBPageID=>$row)
      {
      $BindToGroupList["$JSBPageID"]=$row->Caption;
      }
    }
  $this->About=$_[TLASTEVENT_ABOUT];
  $this->Propdefs=array(
      NewsGroupID=>array(Type=>"List",Values=>$BindToGroupList,Caption=>$_[NEWSBROWSER_P_NEWSGROUP]),
      Caption_Details        =>array(Type=>"String",Caption=>$_[NEWSBROWSER_C_DETAILS],DefaultValue=>$_[NEWSBROWSER_C_DETAILS]),
      IntroLength        =>array(Type=>"Integer",Caption=>$_[NEWSBROWSER_P_INTROLENGTH],DefaultValue=>150),
      NewsEventsContext=>array(Type=>"SysContext",Caption=>$_[NEWSGROUP_TARGET_NEWSEVENTS_CONTEXT]),
      CSS_NewsHref      =>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"a",BaseCSSClass=>"a"),
      CSS_NewsTitle     =>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"p.news-tit"),
      CSS_NewsIntro     =>array(Type=>"CSS_Class",Required=>true,DefaultValue=>"p.news-text"),
      TnFormat          =>array(Type=>"List",Required=>true,DefaultValue=>"1",Caption=>$_[NEWS_TN_FORMAT],Values=>array(0=>"Original",1=>"A",2=>"B",3=>"C")),
      DateFormat        =>array(Type=>"Dateformat",Required=>true,DefaultValue=>"day mon year",Caption=>$_[NEWSDATEFORMAT]),
      Align             =>array(Type=>"Align"),
      );
  $this->Datadefs=array(
    NewsGroupCaption=>array(DataType=>"String",Caption=>$_[NEWSGROUP_CAPTION]),
    );
  }

function AfterInit (&$Control)
  {
  $_=&$GLOBALS[_STRINGS][news];
  global $cfg;
  extract ($Control->Properties);


  if (!$NewsGroupID)
    {
    if ($Control->SysContext=='layouts')
      {
      $Control->Data[NewsGroupName]=$_[NEWSBROWSER_GROUP_NAME_SAMPLE];
      return true;
      }

    $NewsGroupID=intval($Control->JSBPageID);
    }
  else
    {
    $NewsGroupID=intval($NewsGroupID);
    }


  if (!$NewsGroupID)
    {
    $Control->Error=$_[ERROR_GROUPID_NOT_SELECTED];
    return;
    }


  $now=time();
  $Control->$NewsCount=0;

  $qevent=DBQuery ("SELECT * FROM news_Events
     WHERE NewsGroupID=$NewsGroupID AND PubStatus=10 AND DateToShow<$now AND DateToHide>$now
     ORDER BY DateOfNews DESC
     LIMIT 0,1","NewsID");

  if (!$qevent) {return;}
  $Control->NewsEvent=$qevent->Top;

  $qg=DBQuery ("SELECT Caption FROM jsb_Pages WHERE JSBPageID=$NewsGroupID AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."'");
  if (!$qg)
    {
    $Control->Error="GroupID $NewsGroupID does not exists";
    return;
    }

  $Control->Data['NewsGroupCaption']=$qg->Top->Caption;
  }


function Render(&$Control)
  {
  $_=&$GLOBALS[_STRINGS][news];
  global $_HOMEURL,$cfg;

  extract ($Control->Properties);
  $NewsEvent=$Control->NewsEvent;


  if ($Control->Error)
    {
    print $Control->Error;
    return;
    }


#  $NewsGroupsURL=$_HOMEURL.'/'.$cfg['Settings']['news']['NewsGroupsContext'];
  $NewsEventURL=$_HOMEURL.'/'.$NewsEventsContext;
#  $NewsUploadURL =$cfg['FilesURL'] .'/'.$cfg['Settings']['news']['UploadFolder'];
#  $NewsUploadPath=$cfg['FilesPath'].'/'.$cfg['Settings']['news']['UploadFolder'];

  $NewsHeadImgURL =$cfg['FilesURL'] .'/img/news.headimg';
  $NewsHeadImgPath=$cfg['FilesPath'].'/img/news.headimg';



/*  if (!$NewsGroupID)
    {
    if ($Control->SysContext=='layouts')
      {
      print $_[NEWSBROWSER_SAMPLELIST];
      }
    return true;
    }
  */
  $DateOfNews=format_date($DateFormat,$NewsEvent->DateOfNews);
  $hrefopen=$NewsEventURL.'/'.$NewsEvent->NewsID.".html";
  if ($NewsEvent->URL) {$hrefopen=$NewsEvent->URL;}
  $Intro=substr ($NewsEvent->Intro,0,$IntroLength);
  if (strlen($Intro)!=strlen($NewsEvent->Intro))
    {
    $pos=strrpos ($Intro," ");
    $Intro=substr ($Intro,0,$pos);
    $Intro.="...";
    }

  $qico=DBQuery ("SELECT BindTo,ImgName,TnNames FROM img_Documents WHERE BindTo='news.headimg/$NewsEvent->NewsID'");

  $ico="";
#  if (!$Hide_Icon)
#    {
    if ($qico)
      {
      $imgdoc=$qico->Top;
      if ($imgdoc)
        {
        $TnFormat=intval($TnFormat);
        if ($TnFormat>0)
          {
          $TnNames=explode("|",$imgdoc->TnNames);
          $ImgName=$TnNames[$TnFormat-1];
          }
        else
          {
          $ImgName=$imgdoc->ImgName;
          }
        $ico="<a href='$hrefopen'><img hspace='10' vspace='10' border='0' src='$NewsHeadImgURL/$ImgName'></a>";
        }
      }
#    }

  $Text="";
  if ($ico) {$Text.=$ico."<br>";}
  list($t,$c)=get_css_pair($CSS_NewsTitle,'span');
  $Text.="<$t $c>$NewsEvent->Title</$t>";
  list($t,$c)=get_css_pair($CSS_NewsIntro,'span');
  $Text.="<$t $c>$Intro</$t>";
  list($t,$c)=get_css_pair($CSS_NewsHref,'a');
  $Text.="<a $c href='$hrefopen'>$Caption_Details</a>";


  print $Text;
  }
}


?>
