<?
class msg_TRateitImg
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Rateit ImgIndex";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $iimg=&$_ENV->LoadInterface("img.IImgIndex");
  $_=&$GLOBALS['_STRINGS']['img'];
  $R=&$GLOBALS['_STRINGS']['msg'];

  $this->About=$_['TRATEITIMG_ABOUT'];
  $this->Propdefs=array(
    BindTo=>array(Type=>"Binding",DataType=>"Socket",Caption=>$_[P_BINDTO]),
    TnFormat=>array(Type=>"List",Required=>true,DefaultValue=>"2",Caption=>$_[TN_FORMAT],
           Values=>array (1=>$_[FORMAT_A],2=>$_[FORMAT_B],3=>$_[FORMAT_C])),
    OnShow=>array (Type=>"List",Caption=>$_[IMAGE_P_ONSHOW],
            Values=>array(
                    w=>$_['IMAGE_V_ONSHOW_WINDOW'],
                    m=>$_['IMAGE_V_ONSHOW_IMGMONITOR'],
                    c=>$_['IMAGE_V_ONSHOW_OPENCONTEXT'],
                    n=>$_['IMAGE_V_ONSHOW_NONE'],
                    ),
            DefaultValue=>'w'),
    Monitor=>array (Type=>"binding",DataType=>"img.monitor",Caption=>$_[P_BINDTOMONITOR]),
    ColumnCount=>array(Type=>"Number",DefaultValue=>3,Caption=>$_[TIMGINDEX_P_COLUMNCOUNT]),
    ShowCaptions=>array (Type=>"Boolean",Caption=>$_[P_SHOWCAPTIONS],DefaultValue=>true),
    CSS_Caption=>array(Type=>"CSS_Class",Caption=>$_[P_CSS_CAPTION],BaseCSSClass=>"p"),
    CSS_Border =>array(Type=>"CSS_Class",Caption=>$_[P_CSS_BORDER],BaseCSSClass=>"td"),
    CSS_Href   =>array(Type=>"CSS_Class",Caption=>$_[P_CSS_HREF],BaseCSSClass=>"a"),
    ForceTnSize=>array(Type=>"size",Caption=> $_[FORCED_TNSIZE]),
    ImgViewContext=>array(Type=>"SysContext",Caption=>$_[IMGVIEW_CONTEXT]),
    ClickToRate=>array(Type=>"Caption",Caption=>$R[CLICK_TO_RATE],DefaultValue=>$R[CLICK_TO_RATE]),
    );
  }


function Render(&$Control)
  {
  global $_STRINGS;
  $_=&$_STRINGS['img'];

#  $_ENV->InitWindows();

  global $cfg;

  $imgPath=$cfg['FilesPath']."/img";
  $imgURL =$cfg['FilesURL']."/img";
  $TImageBindings=false;

  extract ($Control->Properties);

  $BindTo=$Control->Bindings['BindTo'];
  if ($BindTo)
    {
    if ($Control->EditableContent)
      {
      $Selectable=true;
      }
    $BindToDetails=BindPathInfo($BindTo);
    if (!$BindToDetails)
      {
      return array(Error=>"Binding reference invalid");
      }
    }



/*    $qlimit="BindTo='$BindTo'";
    if (!$Moderate)
      {
      $qlimit.=" AND (Approved=1 OR PostTime<$timeago OR IPaddr='$IPaddr')";
      }

    $ratecounter=0;
    $rateavg=0;

    # acquire rate of object (Rate>0)
    $qc=DBQuery ("SELECT AVG(Rate) as avg,COUNT(*) as counter FROM msg_Rateit WHERE $qlimit AND Rate>0");
    if ($qc)
      {
      $rateavg=$qc->Top->avg;
      $ratecounter=$qc->Top->counter;
      }
  */

  $qimgs=DBQuery ("SELECT ImgID FROM img_Documents WHERE BindTo='$BindTo'","ImgID");
  if ($qimgs)
    {
    $keys=implode (",",array_keys($qimgs->Rows));

    $IPaddr=$_SERVER[REMOTE_ADDR];
    $timeago=time()-60*60*24*2; // two days ago
    $qlimit="BindTo='$BindTo' AND Rate>0";
    if (!$Moderate)
      {
      $qlimit.=" AND (Approved=1 OR PostTime<$timeago OR IPaddr='$IPaddr')";
      }
    $GLOBALS['ImgRates']=DBQuery ("SELECT BindTo,AVG(Rate) as avg,COUNT(*) as counter FROM msg_Rateit WHERE SUBSTRING(BindTo,5) IN ($keys) GROUP BY BindTo","BindTo");
    }

  $iimg=&$_ENV->LoadInterface("img.IImgIndex");
  $iimg->View (array(
    TnFormat     =>$TnFormat,
    TnFormats    =>$TnFormats,
    ShowCaptions =>$ShowCaptions,
    CSS_Caption  =>$CSS_Caption,
    CSS_Border   =>$CSS_Border,
    CSS_Href     =>$CSS_Href,
    ColumnCount  =>$ColumnCount,
    BindTo       =>$BindTo,
    DummyMode    =>$Control->EditMode,
    ForceTnSize  =>$ForceTnSize,
    OnShow       =>$OnShow,
    Monitor      =>$Monitor,
    ImgViewContext=>$ImgViewContext,
    CaptionCallback=>"RateIt_DrawRate",
    RText=>$ClickToRate
    ));
  }
}


# img.ImgIndex.interface callback function
function RateIt_DrawRate($ImgID,$ClickToRateText)
  {
  $_=&$GLOBALS['_STRINGS']['msg'];

  $qrates=&$GLOBALS['ImgRates'];
#  $qrates->Dump();



#  print "img/$ImgID";
  $rate=$qrates->Rows["img/$ImgID"];
#  var_dump($qrates->Rows["img/$ImgID"]);
#  foreach ($rates as $RateID=>$row)
#    {
#    print $row=>Authod
#    }

  $Text=$ClickToRateText;
  if (!$Text) $Text=$_['CLICK_TO_RATE'];
  if ($rate->counter)
    {
    $Text.="($rate->counter)";
    }

  $Rate=$rate->avg;
  if (($Rate)&&($rate->counter>5))
    {
    $sr="";
    for ($i=0;$i<$Rate;$i++) {$sr.="«";}
    $sr="<font color='red' face='wingdings'>$sr</font>";
    if ($i<5) {
      $sr.="<font color='#8f8f8f' face='wingdings'>";
      for (;$i<5;$i++) {$sr.="«";}
      $sr.="</font>";
      }
    $Text.="<br>".$sr;
    }
  return  $Text;
  }
?>
