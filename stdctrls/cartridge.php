<?php
class stdctrls
{
function stdctrls()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->Title=$_['TITLE'];
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  return array(
    TRichtext   =>array(Caption=>&$_['TRICHTEXT_CAPTION'],   Description=>&$_['TRICHTEXT_DESCRIPTION'],Icon=>"ico_TRichText.gif"),
    TColumns    =>array(Caption=>&$_['TCOLUMNS_CAPTION'],    Description=>&$_['TCOLUMNS_DESCRIPTION'],Icon=>""),
    TText       =>array(Caption=>&$_['TTEXT_CAPTION'],       Description=>&$_['TTEXT_DESCRIPTION'],Icon=>"ico_TText.gif"),
    TJSMenu     =>array(Caption=>&$_['TJSMENU_CAPTION'],     Description=>&$_['TJSMENU_DESCRIPTION'],Icon=>"ico_TJSMenu.gif"),
    TVerticalMenu=>array(Caption=>&$_['TVERTICALMENU_CAPTION'],Description=>&$_['TVERTICALMENU_CAPTION']),
    TButton     =>array(Caption=>&$_['TBUTTON_CAPTION'],     Description=>&$_['TBUTTON_DESCRIPTION'],Icon=>""),
    THTMLLibrary=>array(Caption=>&$_['THTMLLIBRARY_CAPTION'],Description=>&$_['THTMLLIBRARY_DESCRIPTION'],Icon=>""),
    TLinkList   =>array(Caption=>&$_['TLINKLIST_CAPTION'],   Description=>&$_['TLINKLIST_DESCRIPTION'],Icon=>""),
    TPageButtons=>array(Caption=>&$_['TPAGEBUTTONS_CAPTION'],Description=>&$_['TPAGEBUTTONS_DESCRIPTION'],Icon=>""),
    TPath       =>array(Caption=>&$_['TPATH_CAPTION'],       Description=>&$_['TPATH_DESCRIPTION'],Icon=>"ico_TPath.gif"),
    THorizontalRule=>array(Caption=>"Horizontal rule"),
    TYouTube=>array(Caption=>"You tube video"),
    );
  }


function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  return array
    (
    RichtextImagesLimitSize=>array(Caption=>$_['SETTING_RICHTEXTIMGLIMITS'],Type=>'dim',DefaultValue=>'500x500'),
    RichtextImagesQuality=>array(Caption=>$_['SETTING_RICHTEXTIMAGESQUALITY'],Type=>'int',DefaultValue=>'90'),
    DrawTextDisable=>array(Caption=>$_['TDRAWTEXT_RENDERASTEXT'],Type=>'boolean',DefaultValue=>'0'),
    );
  }
  
function ObjectClasses()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  return array
    (
    "stdctrls.Richtext"=>array (Caption=>"Richtext",Table=>"stdctrls_RichText",TableKey=>"DocID",Bindable=>1),
    );
  }
  
}
?>
