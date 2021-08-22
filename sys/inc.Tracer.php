<?
class sys_Tracer
{
var $TraceBuf=false;
var $Inited=false;

function EnableOutput()
  {
  if ($this->Inited) return;

  global $cfg;
  if ($cfg['TraceToWindow'])
    print "<script>var siteName='$".$cfg['SiteName']."';</script><script src='$cfg[PublicURL]/sys/TraceWindow.js'></script>";
  else
    print "<style>
    .t0{background-color:#e8e8e8; font-size:9px; color:#000000; font-family:verdana,arial,helvetica;}
    .t1{background-color:#707070; font-size:9px; color:#ffffff; font-family:verdana,arial,helvetica; font-weight:bold;}
    .t2{background-color:#f0f0e8; font-size:9px; color:#a00000; font-family:verdana,arial,helvetica; font-weight:bold;}
    .t3{background-color:#f0f0e8; font-size:9px; color:#000000; font-family:arial,helvetica; }
    </style>";

  # dump buffered output
  $this->Inited=true;

  if ($this->TraceBuf)
    {
    foreach ($this->TraceBuf as $s)
      {
      print $s;
      }
    unset ($this->TraceBuf);
    }
  }

function Trace(&$v,$type=0)
  {
  global $cfg,$START_TICK,$PREV_TICK;
  if (!$PREV_TICK) $PREV_TICK=$START_TICK;
  $nowtick=getmicrotime();
  if (!$v) {$type=1; $v="-- trace start -- ".$_SERVER['REQUEST_URI']; $toffs="s.msec";}
  else
    {
    if (function_exists("getmicrotime"))
      {
      $toffs="";
      if ($cfg['TraceTime'])
        $toffs="<span class=overt>".sprintf("%.3f",$nowtick-$START_TICK)."</span>";
      if ($cfg['TraceTimeDelta'])
        {
        if ($toffs) $toffs.="<br>";
        $toffs.="&Delta;".sprintf("%.3f",$nowtick-$PREV_TICK);
        }
      }
    if (is_array($v)||is_object($v))
      {
      $s="";
      if (is_object($v)) {$v=get_object_vars($v);}
      if (is_array($v))
        {
        foreach ($v as $k=>$v1) $s.="<b>$k:</b>".str_replace (array("'","\\","\n","\r","<",">"),array('"',"/"," "," ","&lt;","&gt;"),$v1)." ";
        $v=$s;
        }
      }
    else
      {
      $v=str_replace (array("'","\\","\n","\r","<",">"),array('"',"/"," "," ","&lt;","&gt;"),$v);
      }
    }


  if ($cfg['TraceToWindow'])
    {
    $s="\n\n\n<script>traceText('$v',$type,'$toffs');</script>";
    }
  else
    {
    $c='t'.$type;
    $s="<table cellpadding=0 cellspacing=1 width='100%'><tr valign=top><td width=1  class='$c'>$toffs</td><td width=100% class='$c'>$v</td></tr></table>";
    }

  if (!$this->Inited)
    {
    $this->TraceBuf[]=$s;
    }
  else
    {
    print $s;
    }
  # pass through TraceWindow_trace time  *8)
  $PREV_TICK=getmicrotime();
  }
}
?>
