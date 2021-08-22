<?php
class stdctrls_TButton
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Subscribers="BindToLink";


function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->About=$_['SITETEXT_ABOUT'];
  $this->Propdefs=array(
    Text=>array(Type=>"String",Caption=>$_['SITETEXT_TEXT']),
    Style=>array(Type=>"ThemeElement",Caption=>$_['BUTTON_IMAGE'],DefaultValue=>'default',Section=>"Buttons"),

#    Style=>array(Type=>"List",Caption=>$_['BUTTON_IMAGE'],GetListValuesFrom=>"stdctrls.PThemeStyles.GetButtonStyles"),
    TargetURL=>array(Type=>"InputModal",Caption=>$_['BUTTON_LINKTO'],Editable=>1,InitCall=>"jsb.IPage.GetPageNameByURLValue",ModalCall=>"jsb.IPage.SelectPageOrURL"),
    Action=>array(Type=>"List",Caption=>"Action",
      DefaultValue=>'url',
      Values=>array(
        'url'=>'Jump to url',
        'back'=>'Back to opening page',
        'home'=>'Jump to home page',
        'link'=>'Jump to BindToLink',
      )),
    GlyphKind=>array(Type=>"String"),
    BindToLink=>array(Caption=>"Bind to control publishing the link",Type=>"Binding",DataType=>"Link"),
    Align=>array(Type=>"Align"),
    NewWindow=>array(Type=>"boolean",Caption=>"Open in a new window"),
    PutBR=>array(Type=>"boolean",Caption=>"Next control will be put on the new line")
    );
  }

function Init (&$Control)
  {
  $Style=$Control->Properties['Style'];
  if ($Style)
    {
    $ImgSrc_hover=$GLOBALS['_THEME']['Buttons'][$Style]['ImgSrc_hover'];
    if ($ImgSrc_hover) $this->PreloadImages[$Style]=$ImgSrc_hover;
    }
  }

function AfterInit (&$Control)
  {
  global $cfg, $_THEME_NAME;
  if (!$this->PreloadImages) {return ;}

  $s="";
  foreach ($this->PreloadImages as $Style=>$imghover)
    {
    $imgPath="$cfg[SkinsPath]/$_THEME_NAME/$imghover";
    $imgURL= "$cfg[SkinsURL]/$_THEME_NAME/$imghover";
    if (file_exists($imgPath)) $imginfo=getimagesize($imgPath);
    $s.=(($s)?",":"")."'$imgURL'";
    }
  $s="\n<script>if(!document._pi_){document._pi_=new Array();}var tmp,j=[$s];for(var i in j){tmp=new Image(); tmp.src=j[i];document._pi_[document._pi_.length]=tmp;}\n</script>\n";
  print $s;
  $this->PreloadImages=false;
  }

function Render(&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  global $cfg, $_THEME_NAME;

  extract ($Control->Properties);
  if (substr($TargetURL,0,4)=='jsb/')
    {
    $TargetURL="../".substr($TargetURL,4);
    }

  $ButtonArgs['Style']=$Style;
  if ($GlyphKind) $ButtonArgs['Kind']=$GlyphKind;
  if ($Text) $ButtonArgs['Caption']=$Text;

  $ButtonArgs['Action']=$Action;
  $ButtonArgs['NewWindow']=$NewWindow;
  switch ($Action)
    {
    case 'home':
      $ButtonArgs['Href']="../".$cfg['Settings']['jsb']['HomeContext'].".$cfg[VirtualExtension]";
      break;
    case 'url':
      $ButtonArgs['Href']=$TargetURL;
      break;
    case 'link':
      $url=$Control->BindToLink;
      if (is_array($url)) {$url=$url['URL']; $ButtonArgs['Caption']=$url['Text'];}
      $ButtonArgs['Href']=$Control->BindToLink['URL'];
      break;

    }

  $_ENV->PutButton($ButtonArgs);
  if ($PutBR)print"<br/>";
  }
}
?>
