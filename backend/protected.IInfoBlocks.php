<?
class backend_IInfoBlocks
{
var $CopyrightText="(c)2007 PHP Systems builder. Backend";
var $CopyrightURL="http://www.phpsb.com/backend";
var $ComponentVersion="1.0";
var $RoleAccess=array(BackendAccess=>"Stat,Whoisonline");

function Whoisonline($args)
  {
  global $cfg;
  global $_USER,$_SYSSKIN_NAME;
  $qc=DBQuery("SELECT COUNT(*) AS cnt FROM um_Sessions WHERE Closed=0");
  $Count=$qc->Top->cnt;
  $q=DBQuery("SELECT SessionKey,IPaddr,UserID,LoginTime,UpdateTime,Language,Browser,UpdateCount FROM um_Sessions WHERE Closed=0 ORDER BY UpdateTime DESC LIMIT 0,20","SessionKey");
  $_ENV->PrintTable($q,array(
    Fields=>array(
      Language=>"Language",
      LoginTime=>"Login time",
      ActiveTime=>"Active time",
      IdleTime=>"Idle time",
      IPaddr=>"IP addr",
      Browser=>"Client browser",
      UserID=>"User",
      UpdateCount=>"Hits",
      ),
    FieldHooks=>array(
      ActiveTime=>"_tab_ActiveTime",
      IdleTime=>"_tab_IdleTime",
      LoginTime=>"_tab_Time",
      ),
    ColAligns=>array(ActiveTime=>'center'),
    OnGetCellStyle=>"_tab_Style",
    TableStyle=>1,
    ThisObject=>&$this));
  print "<p>Total: $Count sessions</p>";
  }
function _tab_Style(&$k,&$row)
  {
  global $_SESSION;
  if ($row->SessionKey==$_SESSION->SessionKey) return 'Red';
  }
function _tab_Time(&$key,$row,$field)
  {
  print format_date('numericdate hh:mm',$row->$field);
  }

function _tab_ActiveTime(&$key,$row)
  {
  $s="";
  $len=$row->UpdateTime-$row->LoginTime;
  $days=floor($len/(24*60*60));
  $len-=$days*24*60*60;
  $hours=floor($len/(60*60));
  $len-=$hours*(60*60);
  $mins=floor($len/60);
  if ($days) $s="$days"."d,&nbsp;$hours"."hrs";
  elseif ($mins) $s="$hours:$mins";
  print $s;
  }

function _tab_IdleTime(&$key,$row)
  {
  $s="";
  $len=time()-$row->UpdateTime;
  $days=floor($len/(24*60*60));
  $len-=$days*24*60*60;
  $hours=floor($len/(60*60));
  $len-=$hours*(60*60);
  $mins=floor($len/60);
  if ($days) $s="$days"."d,&nbsp;$hours"."hrs";
  elseif ($mins) $s="$hours:$mins";
  print $s;
  }

function Stat($args)
  {
  global $cfg;
  global $_USER,$_SYSSKIN_NAME;
  $_=&$GLOBALS['_STRINGS']['backend'];
  $startdate=time()-24*60*60*30;
  $enddate=time();
  $d1=getdate($startdate);
  $d2=getdate($enddate);
  print "<img src='".ActionURL("backend.IInfoBlocks.DrawStat.n",array(
    y1=>$d1['year'],m1=>$d1['mon'],d1=>$d1['mday'],
    y2=>$d2['year'],m2=>$d2['mon'],d2=>$d2['mday'],
    width=>300,
    height=>220
    ))."'/>";
  print "<br>From ".format_date("shortdate",$startdate)." to ".format_date("shortdate",$enddate);
  print "<br><font color='#0000b0'>Hits</font> <font color='#00b000'>Visits</font> <font color='#b00000'>Unique</font>";

/*  $y1=$d1['year'];$m1=$d1['mon'];$d1=>$d1['mday'],
  $y2=$d2['year'];$m2=$d2['mon'];$d2=>$d2['mday'],

  $startp=$y1*366+$m1*31+$d1;
  $endp=$y2*366+$m2*31+$d2;
  $s="SELECT Year,Month,Day,SUM(Hits) AS SHits, SUM(Visits) AS SVisits, SUM(Uniq) AS SUniq
    FROM stat_ByDate WHERE (Year*366+Month*31+Day)>$startp AND (Year*366+Month*31+Day)<$endp
    GROUP BY Year,Month,Day";
  */
  }

function DrawStat($args)
  {
  extract(param_extract(array(
    y1=>'int',
    m1=>'int',
    d1=>'int',
    y2=>'int',
    m2=>'int',
    d2=>'int',
    width=>'int=50',
    height=>'int=40',
    ),$args));

  $startp=$y1*366+$m1*31+$d1;
  $endp=$y2*366+$m2*31+$d2;
  $s="SELECT Year,Month,Day,SUM(Hits) AS SHits, SUM(Visits) AS SVisits, SUM(Uniq) AS SUniq
    FROM stat_ByDate WHERE (Year*366+Month*31+Day)>=$startp AND (Year*366+Month*31+Day)<=$endp
    GROUP BY Year,Month,Day";
  $q=DBQuery($s,array('Year','Month','Day'));
  $im=@imagecreate($width,$height);
  $bg=imagecolorallocate($im, 255, 255, 255);
  $boxbg=imagecolorallocate($im, 240,240,240);
  $border=imagecolorallocate($im, 200,200,200);
  $black=imagecolorallocate($im, 0,0,0);
  $green=imagecolorallocate($im, 0,190,0);
  $blue=imagecolorallocate($im, 0,0,190);
  $red=imagecolorallocate($im, 190,0,0);

  $box_x=2;$box_y=2;$box_w=$width-4; $box_h=$height-20;
  imagefilledrectangle ($im,$box_x,$box_y,$box_w+1,$box_h+1,$boxbg);
  imagerectangle ($im,$box_x,$box_y,$box_w+2,$box_h+2,$border);
  if (!$q)
    {
    imagestring($im,2,10,10,"No statistics data",$black);
    }
  else
    {
    $d=mktime(0,0,0,$m1,$d1,$y1);
    $end=mktime(0,0,0,$m2,$d2,$y2);
    $daycount=floor(($end-$d)/(60*60*24));
    $d0=$d;
    $maxhits=0; $maxvisits=0; $maxuniq=0;
    for ($i=0;$i<=$daycount;$i++)
      {
      $dd=getdate($d0);
      $r=&$q->Rows[$dd['year']][$dd['mon']][$dd['mday']];
      if ($r->SHits>$maxhits) $maxhits=$r->SHits;
      if ($r->SVisits>$maxvisits) $maxvisits=$r->SVisits;
      if ($r->SUniq>$maxuniq) $maxuniq=$r->SUniq;
      $d0+=60*60*24;
      }
    if ($maxhits<$box_h) $maxhits=$box_h;
    if ($maxvisits<$box_h) $maxvisits=$box_h;
    if ($maxuniq<$box_h) $maxuniq=$box_h;


    $first=true;
    $scale_x=$box_w/$daycount;
    $textx=-100;
    for ($i=0;$i<=$daycount;$i++)
      {
      $dd=getdate($d);
      $r=&$q->Rows[$dd['year']][$dd['mon']][$dd['mday']];

      $x=$box_x+$scale_x*$i;
      $shitsy  =$boxy+$box_h-($box_h/$maxhits)*$r->SHits;
      $svisitsy=$boxy+$box_h-($box_h/$maxvisits)*$r->SVisits;
      $suniqy  =$boxy+$box_h-($box_h/$maxuniq)*$r->SUniq;
      if (!$first)
        {
        imageline($im,$prevx,$prevshitsy,$x,$shitsy,$blue);
        imageline($im,$prevx,$prevsvisitsy,$x,$svisitsy,$green);
        imageline($im,$prevx,$prevsuniqy,$x,$suniqy,$red);
        }

      $prevshitsy=$shitsy;
      $prevsvisitsy=$svisitsy;
      $prevsuniqy=$suniqy;
      $prevx=$x;

      if ($x>$textx)
        {
        imageline($im,$x,$box_y+$box_h+3,$x,$box_y+$box_h-3,$black);
        imagestring($im,1,$x+1,$box_y+$box_h+2,$dd['mday'],$black);
        $textx=$x+15;
        }
      else
        {
        imageline($im,$x,$box_y+$box_h,$x,$box_y+$box_h-3,$black);
        }
      $d+=60*60*24;
      $first=false;
      }
    imagefilledrectangle ($im,$box_x+2,$box_y+2,30,10,$boxbg);
    imagestring($im,1,$box_x+2,$box_y+2,"Hits ceil: $maxhits",$blue);
    imagestring($im,1,$box_x+2,$box_y+12,"Visits ceil: $maxvisits",$green);
    imagestring($im,1,$box_x+2,$box_y+22,"Uniq ceil: $maxuniq",$red);
    }

  header('Content-type: image/png');
  imagepng($im);
  }

function Cartridges($args)
  {
  $cartlist=&$_ENV->LoadCartridgesList(false);

  $cartridges=new TRecordset;
  $cartridges->Fields=array("Cartridge","Caption","Info","Price");
  $cartridges->KeyFields=array("Cartridge");

  $total=0;
  foreach ($cartlist as $cartname=>$active)
    {
    $d=new stdclass;
    $info=$_ENV->CartridgesInfo[$cartname];
    if (is_array($info))
      {
      $d->Price=$info['price'];
      $s="";
      if ($info['till'])
        {
        if ($info['till']<time()) $active=false;
        switch($info['mode'])
          {
          case 1: # try demo till
            $s="Try till ".format_date("shortdate",$info['till']);
            break;
          case 2: # paid till
            $s="Prepaid till ".format_date("shortdate",$info['till']);
            break;
          }
        }
      else
        {
        switch($info['mode'])
          {
          case 1: $s="Demo mode"; break;
          case 2: $s="Paid"; break;
          case 3: $s="Rent"; break;
          }
        }

      switch(  $info['mode'])
        {
        case 3: $total+=$d->Price; $d->Price="<b>$d->Price</b>"; break;
        default: $d->Price="<font color='#808080'>$d->Price</font>"; break;
        }
      $d->Info=$s;
      }
    $d->Cartridge=($active)?"<font color='green'>[on]&nbsp; $cartname</font>":"<font color='#808080'>[off] $cartname</font>";
    $cartridges->Rows[$cartname]=$d;
    }


  if ($_ENV->CartridgePackage)
    {
    print "Package: <font color='green'>$_ENV->CartridgePackage</font>";
    }

  $_ENV->PrintTable($cartridges,array(
    Fields=>array(
      Cartridge=>"Cartridge",
      Info=>"Info",
      Price=>"$/mo",
    ),
    TableStyle=>1,
    NoForm=>1,
    ThisObject=>&$this)
    );
  print "<table width='100%'><tr><td align='right'>Total rent: <b>\$$total</b>/mo</td></tr></table>";

  }

function Languages($args)
  {
  }

}
