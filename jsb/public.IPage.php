<?
class jsb_IPage
{
function Select($args)
  {
  global $cfg;
  if (!$args['SysContext']) $args['SysContext']=$cfg['Settings']['jsb']['HomeContext'];
  extract(param_extract(array(
    Value=>'string', # call via inputmodal
    SysContext=>'string',
    JSBPageID=>'int',
    action=>'string',
    ContextSelectable=>'int',
    ),$args));

  if ($Value)
    {
    list($SysContext,$JSBPageID)=explode ("/",$Value,3);
    $args['SysContext']=$SysContext;
    $args['JSBPageID']=$JSBPageID;
    }

  $_ENV->SetWindowOptions(array(Width=>300,Height=>500));
  if ($SysContext)
    {
    $qc=DBQuery ("SELECT SysContext,ObjectClass FROM sys_Contexts WHERE SysContext='$SysContext'");
    if (!$qc)
      {
      $SysContext=$JSBPageID=false;
#      return array (Error=>'Context not found',Details=>$SysContext);
      }

    $ObjectClass=$qc->Top->ObjectClass;
    if ($ObjectClass)
      {
      list ($cartname,$iname)=explode('.',$ObjectClass);
      $cart=&$_ENV->LoadCartridge($cart);
      if (method_exists($cart,'ObjectClasses'))
        {
        $ObjectClasses=$cart->ObjectClasses();
        $ContextInterface=$ObjectClasses[$ObjectClass]['Interface'];
        $intf=&$_ENV->LoadInterface($ContextInterface);
        if ($intf &&(method_exists($intf,"Select")))
          {
          $intf->Select(array(SysContext=>$SysContext));
          return;
          }
        }
      }
    }

  $args['MyURL']=ActionURL("jsb.IPage.Select.b");
  $args['EditMode']=0;
  $args['ByObjectClass']="";
  print "<table height='100%' width='100%'><tr valign='top'><td bgcolor='#d0d0d0'>";
  $ISiteExplorer=&$_ENV->LoadInterface("jsb.ISiteExplorer");
  $ISiteExplorer->BuildContextTree ($args);
  print "</td></tr></table>";
  }

function GetPageNameByValue($args)
  {
  extract ($args);
  if ($Value)
    {
    list($SysContext,$JSBPageID)=explode ('/',$Value,3);
    $JSBPageID=intval($JSBPageID);
    if ($JSBPageID)
      {
      $q=DBQuery("SELECT s.Caption as ContextCaption, p.Caption as PageCaption FROM jsb_Pages p, sys_Contexts s
        WHERE s.SysContext='$SysContext' AND p.SysContext=s.SysContext AND JSBPageID=$JSBPageID");
      if ($q)
        {
        $Caption="[".langstr_get($q->Top->ContextCaption)."] ".langstr_get($q->Top->PageCaption);
        }
      }
    else
      {
      $q=DBQuery("SELECT Caption FROM sys_Contexts WHERE SysContext='$SysContext'");
      if ($q)
        {
        $Caption="[".langstr_get($q->Top->Caption)."]";
        }

      }
    }

  return array(Caption=>$Caption);
  }

function GetPageNameByURLValue($args)
  {
  extract ($args);
  if ($Value && substr($Value ,0,3)=='../')
    {
    $BindToInfo=BindPathInfo($Value);
    if ($BindToInfo)
      {
      $SysContext=$BindToInfo->Context;
      $JSBPageID=intval($BindToInfo->ID);
      if ($JSBPageID)
        {
        $q=DBQuery("SELECT s.Caption as ContextCaption, p.Caption as PageCaption FROM jsb_Pages p, sys_Contexts s
          WHERE s.SysContext='$SysContext' AND p.SysContext=s.SysContext AND JSBPageID=$JSBPageID");
        if ($q)
          {
          $Caption="[".langstr_get($q->Top->ContextCaption)."] ".langstr_get($q->Top->PageCaption);
          }
        }
      }
    }

  return array(Caption=>$Caption);
  }

function SelectPageOrURL($args)
  {
  extract(param_extract(array(
    SysContext=>"string",
    Path=>"string",
    Value=>"string",
    ByObjectClass=>"string",
    ),$args));

#  $_ENV->InitWindows();
  $_ENV->SetWindowOptions(array(Width=>550,Height=>200,Title=>$_[MSG_ENTER_URL]));

  print "<style>body{background-color:#e8e8e8;}</style>";
  global $cfg;
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];

  $Clause="";
  if ($Value)$Path=$Value;
  if ($Path && substr($Path,0,3)=='../')
    {
    $Caption="";
    $BindToInfo=BindPathInfo($Path);
    if ($BindToInfo)
      {$SysContext=$BindToInfo->Context;
      $JSBPageID=intval($BindToInfo->ID);
      if ($JSBPageID)
        {
        $q=DBQuery("SELECT Caption FROM jsb_Pages WHERE SysContext='$SysContext' AND JSBPageID=$JSBPageID");
        if ($q)
          {
          $Caption=langstr_get($q->Top->Caption);
          }
        }
      }

    }

  if ($SysContext) $Clause="SysContext='$SysContext'";
  if ($ByObjectClass)
    {
    if ($Clause) $Clause.=" OR ";
    $Clause.=" (ObjectClass IS NOT NULL AND ObjectClass='$ByObjectClass')";
    } else {
    if ($Clause) $Clause.=" OR ";
    $Clause.=" (Hidden=0)";
    }
  if ($Clause) $Clause=" WHERE $Clause ";
  $qc=DBQuery ("SELECT SysContext,Caption FROM sys_Contexts $Clause ORDER BY OrderNo","SysContext");
  if (!$qc) return;

  print "<table cellpadding='10' ><tr><td>";
  $script="W.openModal({url:'".ActionURL("jsb.IPage.Select.b")."'
    +'?ContextSelectable=1&SysContext='+form1.SelectedContext.value,w:300,h:530,callback:'callback_PageSelected'});";


#  $s="<option value=''>$_[SELECT_CONTEXT]</option><option value=''>--------------</option>";
  $dSysContext=$SysContext;
  if (!$dSysContext) $dSysContext=$cfg['Settings']['jsb']['HomeContext'];
  foreach($qc->Rows as $aSysContext=>$row)
    {
    $sel=($aSysContext==$dSysContext)?"selected":"";
    $s.="<option value='$aSysContext' $sel>".langstr_get($row->Caption)."</option>";
    }
  if ($s)
    {
    $s="<select name='SelectedContext'>$s</select>";
    }
  print "<form name='form1'>
  <p>$_[MSG_ENTER_URL]</p>
  <input type='text' class='inputarea' name='addr' value='$Path' maxlength='250' size='50' onChange='check_addr(this)'>
  <input type='hidden' name='caption' value='$Caption'>";
  $_ENV->PutButton(array(Kind=>'ok',OnClick=>"W.modalResult(form1.addr.value+'\\n'+form1.caption.value);"));
  print "<div id='divcaption' class='notice'>$Caption</div>";
  print "</p><p>$_[MSG_SELECT_SITEPAGE]</p>$s";
  $_ENV->PutButton(array(Caption=>$_['CAPTION_OPENCONTEXT'],OnClick=>$script));
  print "</form>";
  ?>
  <script>
  function check_addr(f) {if (f.value.substr(0,4)=='www.') {f.value="http://"+f.value;} }
  function callback_PageSelected(result) {
    if (!result) return;
    var r=result.split('\n');
    var s=r[0].split('/');
    form1.addr.value="../"+s[0]+"/"+s[1]+".html";
    document.getElementById('divcaption').innerHTML=form1.caption.value=r[1];
    }
  </script>
  <?
  }
}
?>
