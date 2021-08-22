<?php
class stdctrls_TPageButtons
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $Subscribers="BindToPages";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->Propdefs=array(
    BindToPages=>array(Type=>"Binding",DataType=>"Pages",Caption=>$_['TPAGEBUTTONS_BINDTOCONTROL']),
    CSS_Text=>array(Type=>"CSS_Class",BaseCSSClass=>"td"),
    CSS_Link=>array(Type=>"CSS_Class",BaseCSSClass=>"a"),
    ButtonPrev=>array(Type=>"ThemeElement",Caption=>$_['BUTTON_IMAGE'],Section=>"Buttons"),
    ButtonNext=>array(Type=>"ThemeElement",Caption=>$_['BUTTON_IMAGE'],Section=>"Buttons"),
    Style=>array(Type=>"List",Caption=>$_['TPAGEBUTTONS_STYLE'],
      DefaultValue=>'simpletext',
      Required=>true,
      Values=>array(
        'simpletext'=>$_['TPAGEBUTTONS_STYLE_SIMPLETEXT'],
        'buttons'=>$_['TPAGEBUTTONS_STYLE_BUTTONS']
        ),
    ),
    Align=>array(Type=>"Align",DefaultValue=>'right'),
    );
  }

function Init (&$Control) {
  	global $_THEME;
  	if (!$Control->Properties['RenderAsText'])
  	{
  		$ButtonStyle=$Control->Properties['ButtonPrev'];
  		if ($ButtonStyle)
  		{
  			$this->PreloadImages[$ButtonName]=$_THEME['Buttons'][$ButtonStyle];
  		}
  		$ButtonStyle=$Control->Properties['ButtonNext'];
  		if ($ButtonStyle)
  		{
  			$this->PreloadImages[$ButtonName]=$_THEME['Buttons'][$ButtonStyle];
  		}
  	}
  }

function AfterInit (&$Control) {
  	global $cfg, $_THEME;
  	if (!$this->PreloadImages) {return ;}

  	$s="";
  	foreach ($this->PreloadImages as $ButtonStyle=>$ButtonData)
  	{
  		$imghover=$ButtonData['ImgSrc_hover'];
  		if ($imghover)
  		{
  			$imgPath="$_THEME[SkinPath]/$imghover";
  			$imgURL= "$_THEME[SkinsURL]/$imghover";
  			if (file_exists($imgPath))
  			{
  				$imginfo=getimagesize($imgPath);
  			}
  			$s.="\ntmp=new Image(); tmp.src='$imgURL'; document._pi_[document._pi_.length]=tmp; ";
  		}
  	}
  	$s="\n<script>if (!document._pi_) {document._pi_=new Array();} $s\n</script>\n";
  	print $s;
  	$this->PreloadImages=false;
  }

function Render(&$Control)
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  global $cfg, $_THEME_NAME;

  $ControlPages=&$Control->BindToPages;
  extract ($Control->Properties);
  list($at,$ac)=get_css_pair($CSS_Link,'a');
  $PageNo=$ControlPages['PageNo'];
  if (!$PageNo) $PageNo=1;
  
  $PageCount=$ControlPages['PageCount'];
  $PageArray=$ControlPages['PageArray'];
  if ($PageArray) {$PageCount=count($PageArray);}

  $getargs="";
  if (is_array($_GET)) foreach ($_GET as $k=>$v) {
    $getargs.=(($getargs)?"&":"")."$k=".urlencode($v);
    }
  $getargs=".".$cfg['VirtualExtension'].(($getargs)?"?".$getargs:"");

  switch ($Style)
    {
    case 'simpletext':
      if ($PageCount>1)
        {
        $centralpagestart=$PageNo-2;
        $centralpageend=$PageNo+2;
        $startpageend=3;

        if ($centralpagestart<$startpageend)
          {
          $startpageend=0; # no startpage serie
          $centralpagestart=1;
          $centralpageend=7;
          }

        $endpagestart=$PageCount-1;
        if (($endpagestart-$centralpageend)<0)
          {
          $endpagestart=0; # No endpages serie
          $centralpageend=$PageCount;
          }
        for ($i=1;$i<=$PageCount;$i++)
          {
          if (($startpageend)&&($i>$startpageend)&&($i<$centralpagestart))
            {
            $s.= "...";
            $i=$centralpagestart;
            }
          if (($endpagestart) && ($i>$centralpageend) && ($i<$endpagestart))
            {
            $s.="...";
            $i=$endpagestart;
            }

          $caption=(is_array($PageArray)) ? $PageArray[$i] : $i;
          if ($i==$PageNo)
            {
            $s.=" <b>$caption</b> ";
            }
          else
            {
            if (strlen($caption)>10) {$caption=substr($caption,0,7).'..';}
            $s.=" <a $ac href='".$Control->JSBPageID."|".$ControlPages['JSBPageControlID']."_p=$i$getargs'>$i</a> ";

            }
          }
        if (($ButtonPrev)&&($ButtonNext))
          {
          $buttons="";
          if ($PageNo>1) {
            $buttons.=$this->_getButtonHtml($ButtonPrev,$Control->JSBPageID."|".$ControlPages['JSBPageControlID']."_p=".($PageNo-1)."$getargs",$Control->JSBPageControlID.'p',$_[TPAGEBUTTONS_CAPTION_PAGEPREV]);
            }
          if ($PageNo<$PageCount) {
            $buttons.=$this->_getButtonHtml($ButtonNext,$Control->JSBPageID."|".$ControlPages['JSBPageControlID']."_p=".($PageNo+1)."$getargs",$Control->JSBPageControlID.'n',$_[TPAGEBUTTONS_CAPTION_PAGENEXT]);
            }
          switch ($Align) {
            case 'right': $s=$s.$buttons; break;
            case 'center' : $s=$buttons."<br>".$s; break;
            default: $s=$buttons.$s; break;
            }
          }
        $s="<table width='100%'><tr><td nowrap align='$Align'>$_[TPAGEBUTTONS_CAPTION_PAGES]$s</td></tr></table>";
        print $s;
        }
      break;
    default:
      print "Please select button style";
    }
  }

function _getButtonHtml ($Button,$href,$id,$Text=false)
  {
  global $cfg;
  global $_THEME_NAME;
  $ButtonData=$GLOBALS['_THEME']['Buttons'][$Button];
  $img=$ButtonData['ImgSrc'];
  $imghover=$ButtonData['ImgSrc_hover'];
  $imgPath=$cfg['SkinsPath'].'/'.$_THEME_NAME.'/'.$img;
  $imgURL= $cfg['SkinsURL'] .'/'.$_THEME_NAME.'/'.$img;
  if ($imghover) {$imgHoverURL= $cfg[SkinsURL] .'/'.$_THEME_NAME.'/'.$imghover;}
  if (!$Text) $Text=$ButtonData['Caption'];

  $s="href='$href'";
  if (file_exists($imgPath))
    {
    $imginfo=getimagesize($imgPath);
    if ($imghover)
      {
      $s.=" onMouseOver='document.getElementById(\"$id\").src=\"$imgHoverURL\"' onMouseOut='document.getElementById(\"$id\").src=\"$imgURL\"'";
      }
    }
  $s="<a $s><img align='absmiddle' id='$id' src='$imgURL' $imginfo[3] alt='$Text' border='0'></a>";
  return $s;
  }
}
?>
