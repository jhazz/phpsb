<?php
class news_TNewsPath
{
var $CopyrightText="(c)2003 JhAZZ Site Builder.News cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][news];
  $__=&$GLOBALS[_STRINGS][_];

  $this->About=$_[TNEWSPATH_ABOUT];
  $this->Propdefs=array(
    CSS_Text=>array(Type=>"CSS_Class",Caption=>$_[SITEHEADLINE_P_CSSTEXT],BaseCSSClass=>"p"),
    ShowRoot=>array(Type=>"Boolean",DefaultValue=>"1"),
    Delimiter=>array(Type=>"String",DefaultValue=>" : "),
    AttachRoot=>array(Type=>"LocalURL",Caption=>$_[TNEWSPATH_ATTACHROOT]),
    NewsEvent=>array(Type=>"Binding",DataType=>"news.NewsEvent"),
    Align=>array(Type=>"Align"),
    );
  }


function Render(&$Control)
  {
  global $cfg,$_TITLE;
  $ext=$cfg['VirtualExtension'];
  extract ($Control->Properties);

  $NewsEvent=&$Control->Bindings['NewsEvent'];
  $JSBPageID=$NewsEvent->Data['NewsGroupID'];
  $Text=$NewsEvent->Data['Title'];
  $SysContext=$cfg['Settings']['news']['NewsGroupsContext'];

  for ($i=0;$i<10;$i++)
    {
    $q2=DBQuery ("SELECT ParentID,Caption FROM jsb_Pages WHERE SysContext='$SysContext' AND JSBPageID='$JSBPageID'");
    if (!$q2) {break;}
    $ParentID=intval($q2->Top->ParentID);
    $Caption=$q2->Top->Caption;
    $Text=$Delimiter.$Text;
    $Text="<a href='../$SysContext/$JSBPageID.$ext'>$Caption</a>".$Text;
    if ($ParentID)
      {
      $JSBPageID=$ParentID;
      }
    else
      {
      break;
      }
    }

  if ($AttachRoot)
    {
    list ($tmp,$SysContext,$ParentID)=explode ("/",$AttachRoot);
    $AttachRoot=false;
    for ($i=0;$i<10;$i++)
      {
      $vJSBContext=$vJSBPageID=false;
      $q=DBQuery ("SELECT ParentID,Caption,Options FROM jsb_Pages WHERE SysContext='$SysContext' AND JSBPageID='$JSBPageID'");
      if ($q)
        {
        parse_str($q->Top->Options,$opt);
        $virtual=$opt['virtual'];
        $ParentID=$q->Top->ParentID;

        if ($virtual)
          {
          list($vJSBContext,$vJSBPageID)=explode (":",$virtual);
          $q=DBQuery ("SELECT ParentID,Caption,Options FROM jsb_Pages WHERE SysContext='$vJSBContext' AND JSBPageID='$vJSBPageID'");
          }


        if (($i!=0) || (!$_TITLE))
          {
          $Caption=$q->Top->Caption;
          if ($Text) {$Text=$Delimiter.$Text;}

          if ($Control->JSBPageID==$JSBPageID)
            {
            $Text=$Caption.$Text;
            }
          else
            {
            if ($virtual)
              {
              $Text="<a href='../$vJSBContext/$vJSBPageID.$ext'>$Caption</a>".$Text;
              }
            else
              {
              $Text="<a href='../$SysContext/$JSBPageID.$ext'>$Caption</a>".$Text;
              }
            }
          }
        }
      if (!$ParentID)
        {
        break;
        }
      $JSBPageID=$ParentID;
      }
    }

  if ($Align) {$Align=" align='$Align'";}
  list($t,$c)=get_css_pair($CSS_Text,'p');
  print "<$t$c$Align>$Text</$t>";
  }
}

?>
