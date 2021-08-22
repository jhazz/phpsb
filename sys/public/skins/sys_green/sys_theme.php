<?
$GLOBALS['_THEME']=array(
Title=>"Green mint system skin",
Description=>"Aqua and mint freshness",

FormStyles=>array(
'clear'=>array(Caption=>"Clear form without table"),
'normal'=>array(Caption=>"Normal form inside a table",
  FormPanel=>array(Padding=>'20',MainPanelCSS=>'td.bgup'),
  TableStyle=>1),
),

TableStyles=>array(
  array(  Caption=>'Table with head',
    Top=>'th',
    HiEven=>'td.bgtop',
    HiOdd=>'td.bgtop',
    Even=>'td.ce',
    Odd =>'td.co',
    Warning=>'td.red',
    BgColor_Hovered=>'#c0ffff',
    BgColor_Checked=>'#ffffe0',
  ),
  array(  Caption=>'Simple Table',
    Top=>'th',
    HiEven=>'td.bgtop',
    HiOdd=>'td.bgtop',
    Warning=>'td.red',
    Even=>'td.ce', 
    Odd =>'td.co',
    BgColor_Hovered=>'#e8f8f8',
    BgColor_Checked=>'#ffffe0',
    )
  ),

Topmenu=>"{Defaults:{Font:'Arial',FontSize:'14px'},
Level0:{nFontColor:'#202020',hFontColor:'#FFFFFF',nBgColor:false,hBgColor:'#75c0c8'},
Level1:{nBgColor:'#75c0c8',hBgColor:'#3c8d95',nFontColor:'#000000',hFontColor:'#ffffff'}
}",

Buttons=>array(
  'default'=>array(
    TwoPart=>"btn",
    Width=>"50",
    ),
  'standard'=>array(CSS=>"input.button"),
  'plus'=>array(ImgSrc=>"jsb_add.gif"),
  'clear'=>array(ImgSrc=>"jsb_trash.gif"),
  ),

Spacer=>"sp.gif",

WindowStyle=>array(
  ItemBgColorHover=>'#ff6600',
  ItemFontColorHover=>'#ffffff',
  ),
);
?>

