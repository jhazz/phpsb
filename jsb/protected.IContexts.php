<?
class jsb_IContexts
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(
      MainDesigner=>"Browse,Update,Remove,Edit,UpdateContext",
      Composer    =>"Browse"
      );

function jsb_IContexts()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  $this->Title=$_['TITLE_THESE_ARE_CONTEXTS'];
  }

function Remove($args)
  {
  $_=&$GLOBALS[_STRINGS][jsb];
  extract(param_extract(array(
    rems=>"string"
    ),$args));

  if ($rems)
    {
    $r=explode (",",$rems);
    $ctxs="";
    foreach ($r as $ContextID)
      {
      if ($ctxs) {$ctxs.=",";}
      $ctxs.="'$ContextID'";
      }

    $s="DELETE FROM sys_Contexts WHERE SysContext IN ($ctxs)";
    DBExec ($s);
    $s="DELETE FROM jsb_PageControls WHERE SysContext IN ($ctxs)";
    DBExec ($s);
    $s="DELETE FROM jsb_Pages WHERE SysContext IN ($ctxs)";
    DBExec ($s);
    }
  return array(ModalResult=>true);
  }

function Update($args)
  {
  $_=&$GLOBALS[_STRINGS][jsb];
  $__=&$GLOBALS[_STRINGS][_];
  extract(param_extract(array(
    action=>'string',
    SysContext=>'string',
    SysContexts=>'array',
    ContextCaption=>'array',
    DefaultLayout=>'array',
    check=>'array'
    ),$args));

  if (($action=='delete')&&($check))
    {
    $rems=implode (",",array_keys($check));
    $_ENV->UnlockTwicePost();
    print "<form method='post' action='".ActionURL("jsb.IContexts.Remove.b")."'>
      <table><tr><td colspan='2' align='center'>$_[ICONTEXT_REMOVE_CONFIRMATION]<br><br><b>$rems</b><br/><br/></td>
      <tr><td align='right'>";
    $_ENV->PutButton('cancel');
    $_ENV->PutButton('submit');
    print "</table><input type='hidden' name='rems' value='$rems'></form>";
    }
  }

function UpdateContext($args)
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  extract(param_extract(array(
    SysContext=>'string',
    Caption=>'string',
    Hidden=>'int',
    NewLayoutID=>'int',
    NewContextName=>'string',
    ObjectClass=>'string',
    OrderNo=>'int',
    ),$args));

  if (!$NewContextName)
    {
    return array(Error=>"ContextID does not entered during creating new context");
    }

  if ($SysContext && $NewContextName)
    {
    DBExec("UPDATE sys_Contexts SET SysContext='$NewContextName',Caption='$Caption',Hidden=$Hidden,OrderNo=$OrderNo,
        ObjectClass='$ObjectClass' WHERE SysContext='$SysContext'");
    DBExec("UPDATE jsb_Pages SET SysContext='$NewContextName' WHERE SysContext='$SysContext'");
    }

  if (!$SysContext)
    {
    DBExec ("INSERT INTO sys_Contexts (SysContext,Caption,ObjectClass,Hidden,OrderNo)
    VALUES ('$NewContextName','$Caption','$ObjectClass',$Hidden,$OrderNo)");
    }

  $q=DBQuery ("SELECT SysContext FROM jsb_Pages WHERE SysContext='$NewContextName' AND JSBPageID=0");
  if ($q)
    {
    DBExec ("UPDATE jsb_Pages SET JSBLayoutID=$NewLayoutID,Caption='$Caption'
    WHERE SysContext='$NewContextName' AND JSBPageID=0");
    }
  else
    {
    DBExec ("INSERT INTO jsb_Pages (SysContext,ParentID,JSBPageID,JSBLayoutID,Caption)
    VALUES ('$NewContextName',-1,0,$NewLayoutID,'$Caption')");
    }
  return array(ModalResult=>true);
  }

function tab_DefaultLayout($SysContext,$row)
  {
  $_= &$GLOBALS['_STRINGS']['jsb'];
  $dl=$this->qd->Rows[$SysContext];
  if ($dl) $LayoutPageID=$dl->JSBLayoutID;
  $s="<font color='red'>$_[CONTEXT_HAS_NO_LAYOUT]</font>";
  if ($LayoutPageID)
    {
    $l=$this->ql->Rows[$LayoutPageID];
    if ($l)
      {
      parse_str($l->Options,$opt);
      $s="[".$opt['lit']."] ".langstr_get($l->Caption);
      if (!$s) $s="<font color='red'>$[MISSING_LAYOUT]</font><br/>(PageID:$LayoutPageID)";
      }
    }
  print "<nobr>$s</nobr>";
  }

function tab_ContextCaption($SysContext,$row)
  {
  $_= &$GLOBALS[_STRINGS][jsb];
  $s=langstr_get($row->Caption); if (!$s) $s=$SysContext;
  $s="<a href='javascript:;' onClick='W.openModal({url:\""
    .ActionURL("jsb.IContexts.Edit.b",array(SysContext=>$SysContext))
    ."\",w:400,h:400,Title:\"Edit $SysContext\",reloadOnOk:1})'>$s</a>";
  print $s;
  }

function tab_BrowseContext($SysContext,$row)
  {
  $s="<a href='".ActionURL("jsb.ISiteExplorer.Open.n",array(Path=>"/$SysContext/"))."'>/$SysContext/</a>";
  print $s;
  }
function tab_ContextMethods ($SysContext,$row)
  {
  $s="";
  if ($row->ContextInterface)
    {
    $intf=&$_ENV->LoadInterface($row->ContextInterface);
    $s="";
    if (is_object($intf))
      {
      $SupportedMethods=array("OnPageLoad","Select","Browse");
      foreach ($SupportedMethods as $m)
        {
        if (method_exists($intf,$m)) {
          if ($s) $s.=",&nbsp;";
          $s.="$m";
          }
        }
      if ($s) $s="Methods: $s"; else $s="<font color='red'>No context specific methods found!</font>";
      $s="<b>$row->ContextInterface</b><br>$s";
      } else {$s="<font color='red'>Error loading '$row->ContextInterface'</font>";}
    }
  print $s;
  }

function tab_ObjectClass ($SysContext,$row)
  {
  $ObjectClass=$row->ObjectClass;
  $s=$this->ObjectClasses[$ObjectClass]['Caption'];
  print "<b>$s</b><br><span class='notice']>[$ObjectClass]</span> ";
  }

function Browse($args)
  {
  global $cfg;
  $_= &$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
  $s1="SELECT * FROM sys_Contexts ORDER BY OrderNo";
  $qc=DBQuery ($s1,"SysContext");

  # Reading default pages for context where JSBPageID=0
  $s2="SELECT SysContext,JSBLayoutID,Options FROM jsb_Pages WHERE JSBPageID=0";
  $this->qd=DBQuery ($s2,"SysContext");
  $this->ql=DBQuery ("SELECT JSBPageID,Caption,Options FROM jsb_Pages WHERE SysContext='layouts'","JSBPageID");
  $this->cartridges=&$_ENV->LoadCartridgesList(true);
  $this->ObjectClasses=array();
  foreach ($this->cartridges as $c=>$IsActive)
    {
    if (!$IsActive) continue;
    $cartridge=&$_ENV->LoadCartridge($c);
    if (method_exists($cartridge,"ObjectClasses"))
      {
      $ObjectClasses=$cartridge->ObjectClasses();
      if ($ObjectClasses)
        {
        $this->ObjectClasses+=$ObjectClasses;
        }
      }
    }
  if ($qc)
    {
    $candelete=$GLOBALS['_USER']->IsActionAllowed("jsb.IContexts.Remove");
    $_ENV->PrintTable($qc,array(
      Action=>ActionURL("jsb.IContexts.Update.b"),
      ReloadOnOk=>1,
      Fields=>array(Browse=>"Browse section",ContextCaption=>$_['CAPTION_CONTEXTS'],
        DefaultLayout=>$_['CAPTION_LAYOUT'],ObjectClass=>$_['CONTEXT_OBJECTCLASS'],OrderNo=>'##',Hidden=>'Hidden'),
      ShowCheckers=>true,
      FieldHooks=>array(ContextCaption=>tab_ContextCaption,
        ObjectClass=>tab_ObjectClass,
        DefaultLayout=>tab_DefaultLayout,Browse=>tab_BrowseContext),
      TableStyle=>1,
      ShowDelete=>$candelete,
      CSS_TabHead=>"tabhead",
      CSS_Row=>"tab",
      SubactionList=>$SubactionList,
      ColWidths=>array(Edit=>'10%',DefaultLayout=>'10%',ContextMethods),
      ColAligns=>array(Edit=>'center',ContextMethods=>'center'),
      ThisObject=>&$this
      ));
    print "<a href='javascript:' onClick='W.openModal({url:\"".ActionURL("jsb.IContexts.Edit.b")."\",w:400,h:300,Title:\"$_[CAPTION_ADD_CONTEXT]\",reloadOnOk:1});'>$_[CAPTION_ADD_CONTEXT]</a>";
    }
  }

function Edit($args)
  {
  extract(param_extract(array(
    SysContext=>'string',
    ),$args));

  global $cfg;
  $_= &$GLOBALS[_STRINGS][jsb];
  $__=&$GLOBALS[_STRINGS][_];

  $_ENV->SetWindowOptions(array(Width=>600,Height=>340,Title=>"Edit context"));
  if ($SysContext)
    {
    $q=DBQuery ("SELECT * FROM sys_Contexts WHERE SysContext='$SysContext'");
    }

  if ($q)
    {
    extract(param_extract(array(
      OrderNo=>'int',
      Hidden=>'int',
      Caption=>'string',
      ObjectClass=>'string',
      ),$q->Top));
    $aCaption=langstr_get($Caption);
    print "<h2>$aCaption</h2>";
    }
  else
    {
    print "<h1>$_[CAPTION_ADD_CONTEXT]</h1>";
    }

  $ql=DBQuery ("SELECT JSBPageID,Caption,Options FROM jsb_Pages WHERE JSBPageID<>0 AND SysContext='layouts'","JSBPageID");
  $qd=DBQuery ("SELECT SysContext,JSBLayoutID FROM jsb_Pages WHERE JSBPageID=0 AND SysContext='$SysContext'");

  $cartridges=&$_ENV->LoadCartridgesList(true);
  $ObjectClasses=false;
  foreach ($cartridges as $c=>$IsActive)
    {
    if (!$IsActive) continue;
    $cartridge=&$_ENV->LoadCartridge($c);
    if (method_exists($cartridge,"ObjectClasses"))
      {
      $classesinfo=$cartridge->ObjectClasses();
      # check interface availability
      foreach($classesinfo as $aObjectClass=>$info)
        {
        $ObjectClasses[$aObjectClass]="$info[Caption] [$aObjectClass]";
        }
      }
    }

  $layouts=false;
  if ($ql)
    {
    foreach ($ql->Rows as $aLayoutID=>$layout)
      {
      parse_str($layout->Options,$opt);
      $layouts[$aLayoutID]="[$opt[lit]] ".langstr_get($layout->Caption);
      }
    }

  $_ENV->PutValueSet(array(ValueSetName=>'layouts', Values=>$layouts));
  $_ENV->PutValueSet(array(ValueSetName=>'objclasses', Values=>$ObjectClasses));

  $_ENV->OpenForm(array(Name=>"Form1",ShowCancel=>1,Action=>ActionURL("jsb.IContexts.UpdateContext.b"),Align=>"center"));
  $_ENV->PutFormField(array(Type=>'hidden', Name=>"SysContext",Value=>$SysContext));
  $_ENV->PutFormField(array(Type=>'identifier', Name=>"NewContextName",Value=>$SysContext,Caption=>$_['CONTEXT_IDENTIFIER'],Notice=>$_["CONTEXT_IDENTIFIER_NOTICE"],Required=>1,MaxLength=>20,Size=>20));
  $_ENV->PutFormField(array(Type=>'langstring', Name=>"Caption",Value=>$Caption,Caption=>$_['CONTEXT_CAPTION'],Required=>1,MaxLength=>200,Size=>40));
  $args=array(Type=>'droplist',Caption=>$_['CONTEXT_LAYOUT'],Size=>40,ValueSetName=>'layouts', Name=>"NewLayoutID");
  if ($qd && $qd->Top->JSBLayoutID) $args['Value']=$qd->Top->JSBLayoutID;
  $_ENV->PutFormField($args);
  $_ENV->PutFormField(array(Type=>'droplist',Caption=>$_['CONTEXT_OBJECTCLASS'],Size=>40,ValueSetName=>'objclasses', Name=>"ObjectClass",Value=>$ObjectClass));

  $_ENV->PutFormField(array(Type=>'checkbox', Name=>"Hidden",Value=>$Hidden,Caption=>'Hidden'));
  $_ENV->PutFormField(array(Type=>'int', Name=>"OrderNo",Value=>$OrderNo,Caption=>'OrderNo ##'));
  $_ENV->CloseForm();
  }
}

?>
