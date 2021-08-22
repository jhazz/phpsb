<?php
class stdctrls_TYouTube
{
var $CopyrightText="Youtube module";
var $Subscribers="BindTo";

function InitComponent()
  {
#  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $this->About="Показ видео с ютюба";
  $this->Propdefs=array(
    BindTo=>array(Type=>"Binding",DataType=>"Mediafiles",Caption=>'Media source bind'),
  	YouTubes=>array(Type=>"String",Caption=>"Коды фильма с YouTube (текст после http://youtube.com/watch?v=....) можно указывать через запятую"),
    );
  }

/*
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
    $s.="\ntmp=new Image(); tmp.src='$imgURL'; document._pi_[document._pi_.length]=tmp; ";
    }
  $s="\n<script>if (!document._pi_) {document._pi_=new Array();} $s\n</script>\n";
  print $s;
  $this->PreloadImages=false;
  }
*/
function Render(&$Control) {
	extract($Control->Properties);
	
	if ($BindTo) {
		if ($Control->BindTo) $MediaFiles=$Control->BindTo;
		if (substr($MediaFiles,0,2)=='y:') {$YouTubes=explode (",",substr($MediaFiles,2));}
	}
	
	if ($YouTubes) {
		foreach ($YouTubes as $YouTubeV) {
			print "<iframe FRAMEBORDER=0 SCROLLING=NO width='483' height='388' src='".ActionURL("stdctrls.PYouTube.ShowV.f",array(z=>4,v=>$YouTubeV))."'/>";
		} 
	} else {
#		print "Укажите идентификатор видео с YouTube";
		return;
	}


}

}

/*
<object width="425" height="344">
<param name="movie" value="http://www.youtube.com/v/rP6wzhUMwQ4&hl=ru&fs=1"></param>
<param name="allowFullScreen" value="true"></param>
<embed src="http://www.youtube.com/v/rP6wzhUMwQ4&hl=ru&fs=1" 
type="application/x-shockwave-flash" allowfullscreen="true" width="425" height="344">
</embed></object>


http://ru.youtube.com/watch?v=rP6wzhUMwQ4
*/

?>
