<?
class jsb_Page
{
function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  $this->Datadefs=array(
    PageCaption=>array(DataType=>"String",Caption=>$_['TPAGE_PAGECAPTION']),
    PageTitle  =>array(DataType=>"String",Caption=>$_['TPAGE_PAGETITLE']),
    PageObject =>array(DataType=>"Object",Caption=>'Loaded page object'),
    );
 }

function Init(&$Control)
  {
  global $JSBPageID;
  global $JSB_PageData;
  $_=&$GLOBALS['_STRINGS']['jsb'];
  
  if ($JSB_PageData->PageNotRegistered){
    return array(PageNotFound=>1);
  }

  $t=$Control->Data['PageTitle']=langstr_get($JSB_PageData->Title);
  $c=$Control->Data['PageCaption']=langstr_get($JSB_PageData->Caption);
  $Control->Data['PageObject']="jsb.Page/$JSBPageID";

  $GLOBALS['_TITLE']=($t)?$t:$c;

  # Default text
  if (($Control->SysContext=='layouts')&&($Control->DesignMode))
    {
    $Control->Data['LayoutCaption']=langstr_get($JSB_PageData->Title);
    $Control->Data['PageTitle']=langstr_get($_['TPAGE_PAGETITLE']);
    $Control->Data['PageCaption']=langstr_get($_['TPAGE_PAGECAPTION']);
    return;
    }
  $Control->Data['LayoutCaption'] =$JSB_PageData->LayoutCaption;
  }

function Select($args)
  {
  extract(param_extract(array(
    SysContext=>'string',
    JSBPageID=>'int',
    action=>'string',
    ContextSelectable=>'int',
    ),$args));

  $_ENV->SetWindowOptions(array(Width=>300,Height=>500));
  if ($SysContext)
    {
    $qc=DBQuery ("SELECT SysContext,ContextInterface FROM sys_Contexts WHERE SysContext='$SysContext'");
    if (!$qc)
      {
      return array (Error=>'Context not found',Details=>$SelectedContext);
      }

    $ContextInterface=$qc->Top->ContextInterface;
    if ($ContextInterface)
      {
      $intf=&$_ENV->LoadInterface($ContextInterface);
      if ($intf &&(method_exists($intf,"Select")))
        {
        $intf->Select(array(SysContext=>$SysContext));
        return;
        }
      }
    }

  $args['MyURL']=ActionURL("jsb.CPages.Select.b");
  $args['EditMode']=0;
  $args['ByInterface']="";
  print "<table height='100%' width='100%'><tr valign='top'><td bgcolor='#d0d0d0'>";
  $ISiteExplorer=&$_ENV->LoadInterface("jsb.ISiteExplorer");
  $ISiteExplorer->BuildContextTree ($args);
  print "</td></tr></table>";
  }

function SelectPageOrURL($args)
  {
  extract(param_extract(array(
    SysContext=>"string",
    Path=>"string",
    ByInterface=>"string=jsb.CPages",
    ),$args));

#  $_ENV->InitWindows();
  $_ENV->SetWindowOptions(array(Width=>550,Height=>200,Title=>$_[MSG_ENTER_URL]));

  print "<style>body{background-color:#e8e8e8;}</style>";
  global $cfg;
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];

  $Clause="";

  if ($Path)
    {
    $BindToInfo=BindPathInfo($Path);
    if ($BindToInfo) $SysContext=$BindToInfo->Context;
    }

  if ($SysContext) $Clause="SysContext='$SysContext'";
  if ($ByInterfase)
    {
    if ($Clause) $Clause.=" OR ";
    $Clause.=" (ContextInterface IS NOT NULL AND ContextInterface='$ByInterface')";
    } else {
    if ($Clause) $Clause.=" OR ";
    $Clause.=" (Hidden=0)";
    }
  if ($Clause) $Clause=" WHERE $Clause ";
  $qc=DBQuery ("SELECT SysContext,Caption FROM sys_Contexts $Clause ORDER BY OrderNo","SysContext");

  print "<table cellpadding='10' ><tr><td>";
  $script="W.openModal({url:'".ActionURL("jsb.CPages.Select.b")."'
    +'?ContextSelectable=1&SysContext='+form1.SelectedContext.value,w:300,h:530,callback:'callback_PageSelected'});";


#  $s="<option value=''>$_[SELECT_CONTEXT]</option><option value=''>--------------</option>";
  foreach($qc->Rows as $aSysContext=>$row)
    {
    $sel=($aSysContext==$SysContext)?"selected":"";
    $s.="<option value='$aSysContext' $sel>".langstr_get($row->Caption)."</option>";
    }
  if ($s)
    {
    $s="<select name='SelectedContext'>$s</select>";
    }
  print "<form name='form1'>
  <p>$_[MSG_ENTER_URL]</p>
  <input type='text' class='inputarea' name='addr' value='$Path' maxlength='250' size='50' onChange='check_addr(this)'>";
  $_ENV->PutButton(array(Kind=>'ok',OnClick=>'W.modalResult(form1.addr.value);'));
  print "</p><p>$_[MSG_SELECT_SITEPAGE]</p>$s";
  $_ENV->PutButton(array(Caption=>$_['CAPTION_OPENCONTEXT'],OnClick=>$script));
  print "</form>";
  ?>
  <script>
  function check_addr(f) {if (f.value.substr(0,4)=='www.') {f.value="http://"+f.value;} }
  function callback_PageSelected(result) {form1.addr.value="../"+result['SelectedContext']+"/"+result['SelectedPageID']+".html";}
  </script>
  <?
  }
}
?>
