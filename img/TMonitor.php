<?
class img_TMonitor
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Inited;

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS]['img'];
  $this->Propdefs=array(
    Caption_Inside=>array(Type=>"String",Caption=>$_[IMGMONITOR_CAPTIONINSIDE],DefaultValue=>$_[IMGMONITOR_CAPTIONINSIDE_SAMPLE]),
    Size=>array(Type=>"size",DefaultValue=>"500x600",Caption=>$_[IMGMONITOR_SIZE],Required=>true),
    FitInside=>array(Type=>"Boolean",DefaultValue=>true,Caption=>$_[IMGMONITOR_FITINSIDE]),
    ShowFormat=>array(Type=>"List", DefaultValue=>0, Caption=>$_[IMGMONITOR_SHOWFORMAT],
      Values=>array(0=>$_[ORIGINAL],
        1=>$_[FORMAT_A],
        2=>$_[FORMAT_B],
        3=>$_[FORMAT_C])),
    );
  $this->Datadefs=array(
    Monitor=>array(DataType=>"img.Monitor",Caption=>$_[IMGCONTAINER_SOCKET])
    );
  }


function Init(&$Control)
  {
  $Control->Data['Monitor']=$Control->PageControlPath;
  }

function Render(&$Control)
  {
  global $_STRINGS;
  $_=&$_STRINGS['img'];
  global $cfg;

  extract(param_extract(array(
      Size=>"size=400x600",
      Caption_Inside=>"string",
      FitInside=>'int'
      ),
    $Control->Properties));

  list($mw,$mh)=explode ('x',$Size);
  $mw2=intval($mw)-2; $mh2=intval($mh)-2;

  $MonitorID=$Control->JSBPageControlID;
  print "<script>function img_monitor_show_$MonitorID (imgSrc,imgWidth,imgHeight)
    {
    var m=P$.find('TMonitor_$MonitorID');
    var maxWidth=$mw;
    var maxHeight=$mh;
    if (m)
      {
      ";
  if ($FitInside) print "
      var aspect=imgWidth/imgHeight;
      if (imgWidth>$mw2){imgWidth=$mw2;imgHeight=imgWidth/aspect;}
      if (imgHeight>$mh2){imgHeight=$mh2;imgWidth=imgHeight*aspect;}
      ";
   print "
      m.innerHTML=\"<img src='\"+imgSrc+\"' width='\"+imgWidth+\"' height='\"+imgHeight+\"'/>\";

      }
    }
    </script>
  <div id='TMonitor_$MonitorID' style='width:$mw; height:$mh; border:solid 1;' align='center' valign='middle'>$Caption_Inside</div>";
  }
}

?>

