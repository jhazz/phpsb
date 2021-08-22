<?php
class stdctrls_TDrawText
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $Subscribers="Bind";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->Propdefs=array(
    Bind=>array(Type=>"Binding",DataType=>"String",Caption=>$_['SITETEXT_BINDTOTEXT']),
    Text=>array(Type=>"String",Caption=>$_['SITETEXT_TEXT']),
    DrawingFont=>array(Type=>"DrawingFont",Caption=>$_['DRAWTEXT_FONTSTYLE'],Required=>1),
    RenderAsText=>array(Type=>"Boolean",Caption=>$_['DRAWTEXT_ASTEXT']),
    Align=>array(Type=>"Align"),
    );
  }

function Render(&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  global $cfg, $_THEME_NAME;

  $b=$Control->Properties['Bind'];
  extract ($Control->Properties);

  if (!$RenderAsText)
    {
    $RenderAsText=$cfg['Settings']['stdctrls']['TDrawTextDisable'];
    }

  if (($b!='self')&&($Text==""))
    {
    $Text=$Control->Bind;
    }

  if (!$Text)
    {
    if ($Control->DesignMode)
      {
      $Text=$_['SITETEXT_TITLESAMPLE'];
      }
    else
      {
      return;
      }
    }

  $IDrawText=&$_ENV->LoadInterface("stdctrls.PDrawText");
  $r=$IDrawText->GetDrawText(array(
    RenderAsText=>$RenderAsText,
    DrawingFont=>$DrawingFont,
    Text=>$Text
    ));
  if ($r['Error']) return $r;
  print $r['Text']."<br>";

  }

}
?>
