<?
class jsb_ISiteExplorer
{
var $CopyrightText="(c)2006 JhAZZ Site Builder. jsb management";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function jsb_ISiteExplorer()
  {
  $_=&$GLOBALS['_STRINGS']['jsb'];
  $this->Title=$_['TITLE_THESE_ARE_CONTEXTS'];
  $this->RoleAccess=array(
    MainDesigner=>"Open,UpdatePage,UpdateControl,EditPage,EditBackendMenu,ExplorerTree",
    Composer=>"Open,UpdatePage,UpdateControl,EditPage,ExplorerTree");
  }

function Open($args)
  {
  global $cfg;
  extract(param_extract(array(
    Path=>'string',
    EditMode=>'int',
    TargetURL=>'string',
    UseQueryMethod=>'int',
    ContextLocked=>'int=0',
    NoLayouts=>'int'
    ),$args));

  if (!$Path)
    {
    $HomeContext=$cfg['Settings']['jsb']['HomeContext'];
    $qp=DBQuery ("SELECT HomePageID FROM sys_Contexts WHERE SysContext='$HomeContext'");
    $HomePageID=intval($qp->Top->HomePageID);
    $Path="jsb/$HomeContext/$HomePageID";
    }

  $p=BindPathInfo($Path);
  if (!$p)
    {
    return array(Error=>"Unable to determine path",Details=>$Path,IntruderAlert=>10);
    }

  $args1=array(SysContext=>$p->Context
  ,JSBPageID=>$p->ID
  ,UseQueryMethod=>$UseQueryMethod
  ,ContextLocked=>$ContextLocked
  ,NoLayouts=>$NoLayouts);

  if ($TargetURL) {$args1['TargetURL']=$TargetURL;}
  $url=ActionURL("jsb.ISiteExplorer.ExplorerTree.b",$args1);

  $viewsurl="";
  if ($p->ID)
    {
    $viewsurl=" src='$cfg[EditPageURL]/$p->Context/$p->ID'";
    }

  print "<frameset cols='300,*'>
    <frameset rows='*,20'>
      <frame name='tree' src='$url'>
      <frame name='transaction' scrolling=no src='".ActionURL("jsb.ISiteExplorer.UpdatePage.n")."'>
    </frameset>
      <frame id='viewer'  name='viewer' $Path $viewsurl>
    </frameset>
    ";
  }

function ExplorerTree($args)
  {
  global $cfg;
  $args+=array(
    MyURL=>ActionURL("jsb.ISiteExplorer.ExplorerTree.b"),
    TargetURL=>$cfg['EditPageURL'],
    EditMode=>1
    );
  $this->BuildContextTree ($args);
  }


function UpdatePage($args)
  {
  global $cfg;
  extract(param_extract(array(
    JSBPageID1=>'int',
    JSBPageID2=>'int',
    SysContext=>'string',
    JSBPageID=>'int',
    param=>'string',
    NewTitle=>'string',
    NewCaption=>'string',
    NewOptions=>'string',
    Checked=>'array',
    ShowHidden=>'int',
    ExpandedItems=>'string',
    Checked=>'string',
    BrowserViewMode=>'int',
    SelectedContext=>'string',
    SelectedPageID=>'int',
    Parameter=>'string',
    action=>'string',
    ContextLocked=>'int',
    Parameter=>'string',
    ),$args));

  $result="";
  
  if (!$NewTitle) $NewTitle=$NewCaption;
  print '<body topmargin=0 leftmargin=0 widthmargin=0 heightmargin=0 bgcolor="#b0b0b0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Cache-Control" content="no-cache">
    <style>body{font-family:verdana; font-size:10px;}</style>
    <b>Transaction ('.$action.'):</b> ';

  if (!$action)
    {
    print "no actions";
    return;
    }

  $cleaner=&$_ENV->LoadInterface("jsb.IClearTmp");
  $cleaner->NavigationMenu();

  if (($action=="putbef")||($action=="putaft")||($action=="putinsd"))
    {
    $JSBPageID1=intval($JSBPageID1); $JSBPageID2=intval($JSBPageID2);
    if (!$SysContext) {print "bad request"; return;}
    # id1-dragging object, id2-target

    $q1=DBQuery ("SELECT OrderNo,ParentID,Caption,Title FROM jsb_Pages WHERE JSBPageID=$JSBPageID1 AND SysContext='$SysContext'");
    $q2=DBQuery ("SELECT OrderNo,ParentID,Caption,Title FROM jsb_Pages WHERE JSBPageID=$JSBPageID2 AND SysContext='$SysContext'");
    if ((!$q1)||(!$q2)) {print "page disappeared!"; return;}

    $OriginOrderNo=$q2->Top->OrderNo;
    $ParentID=intval($q2->Top->ParentID);

    if ($action=="putinsd")
      {
      $q=DBQuery ("SELECT OrderNo FROM jsb_Pages WHERE ParentID=$JSBPageID2 AND SysContext='$SysContext' ORDER BY OrderNo LIMIT 1");
      if ($q) {$OriginOrderNo=$q->Top->OrderNo;}
      $action="putbef";
      $ParentID=$JSBPageID2;
      }
    if ($action=="putbef")
      {
      $q=DBQuery ("SELECT OrderNo FROM jsb_Pages WHERE ParentID=$ParentID AND SysContext='$SysContext' AND OrderNo<$OriginOrderNo ORDER BY OrderNo DESC LIMIT 1");
      if ($q) {$NewOrderNo=($OriginOrderNo+$q->Top->OrderNo)/2;}
      else {$NewOrderNo=$OriginOrderNo-100000;}
      }
    if ($action=="putaft")
      {
      $q=DBQuery ("SELECT OrderNo FROM jsb_Pages WHERE ParentID=$ParentID AND SysContext='$SysContext' AND OrderNo>$OriginOrderNo ORDER BY OrderNo LIMIT 1");
      if ($q) {$NewOrderNo=($OriginOrderNo + $q->Top->OrderNo)/2;}
      else {$NewOrderNo=$OriginOrderNo+100000;}
      }
    DBExec ("UPDATE jsb_Pages SET ParentID=$ParentID,OrderNo=$NewOrderNo WHERE JSBPageID=$JSBPageID1 AND SysContext='$SysContext'");
    $result="Moving ok";
    }

  if (($action=="chlay")||($action=="hide")||($action=="unhide")||($action=="recycle"))
    {
    $a=explode ("|",$Checked);
    foreach ($a as $b)
      {
      if (!$b) {continue;}
      list($SysContext,$JSBPageIDs)=explode (":",$b);


      if ($action=="recycle")
        {
        DBExec ("UPDATE jsb_Pages SET State=3 WHERE SysContext='$SysContext' AND JSBPageID IN ($JSBPageIDs)");
        $result="Pages are put in recycle bin";
        }

      if ($action=="hide")
        {
        DBExec ("UPDATE jsb_Pages SET State=2 WHERE SysContext='$SysContext' AND JSBPageID IN ($JSBPageIDs)");
        $result="Pages are made invisible";
        }
      if ($action=="unhide")
        {
        DBExec ("UPDATE jsb_Pages SET State=1 WHERE SysContext='$SysContext' AND JSBPageID IN ($JSBPageIDs)");
        $result="Pages are made visible";
        }
      if ($action=="chlay")
        {
        DBExec ("UPDATE jsb_Pages SET JSBLayoutID=$Parameter WHERE SysContext='$SysContext' AND JSBPageID IN ($JSBPageIDs)");
        $result="Layout changed";
        }

      }
    }

  if ($action=="sethome")
    {
    $s="UPDATE sys_Contexts SET HomePageID=$JSBPageID WHERE SysContext='$SysContext'";
    DBExec($s);
    $result="Home page selected";
    }
  if ($action=="do_ren")
    {
    $s="UPDATE jsb_Pages SET Options='$NewOptions',Caption='$NewCaption',
    Title='$NewTitle',UpdatedAt=".time()." WHERE JSBPageID=$JSBPageID AND SysContext='$SysContext'";
    DBExec($s);
    if ($SysContext=='layouts') {
     	parse_str($NewOptions,$o);
      #check page loader control that should has the same class
      $q=DBQuery("SELECT JSBPageControlID,ControlClass FROM jsb_PageControls WHERE Slot='init' AND SysContext='layouts' AND JSBPageID=$JSBPageID");
      $needadd=true;
      if ($q)
        {
        $id=$q->Top->JSBPageControlID;
        if ($o['obj']!=$q->Top->ControlClass)
          {
          print "Remove old control of class '".$q->Top->ControlClass."'<br>";
          DBExec ("DELETE FROM jsb_PageControls WHERE JSBPageControlID=$id");
          }
        else $needadd=false;
        }
      if ($needadd)
        {
        $Control=$_ENV->CreateControl($o['obj'],false,true); #EditMode=true
        $Control->JSBPageControlID=DBGetID("jsb.PageControl");
        $props=$_ENV->Serialize($Control->Properties);
        $s="INSERT INTO jsb_PageControls (JSBPageControlID,ControlClass,OrderNo,SysContext,JSBPageID,Slot,PropertiesStr)
          VALUES ($Control->JSBPageControlID,'$o[obj]',0,'layouts',$JSBPageID,'init','$props')";
        DBExec ($s);
        }
      }
    $result="Page updated";
    }

  if (($action=="do_insbef")||($action=="do_insaft")||($action=="do_insinsd")||($action=="do_dup"))
    {
    if (!$SysContext) {
    	print "No SysContext used for $action";
    	return;
    }
    $NewPageID=DBGetID("jsb.Page",$SysContext);

    $q=false;
    if ($JSBPageID)
      {
      $q=DBQuery ("SELECT JSBLayoutID,OrderNo,ParentID FROM jsb_Pages
                  WHERE JSBPageID=$JSBPageID AND SysContext='$SysContext'");
      }

    if ($q)
      {$ParentID=intval($q->Top->ParentID);
      $JSBLayoutID=$q->Top->JSBLayoutID;
      $OriginOrderNo=$q->Top->OrderNo;
      $InsertIntoContext=false;
      }
    else
      {
      $ParentID=0;
      $JSBLayoutID=0;
      $qcl=DBQuery ("SELECT JSBLayoutID FROM jsb_Pages WHERE SysContext='$SysContext' AND JSBPageID=0");
      if ($qcl)
        {
        $JSBLayoutID=intval($qcl->Top->JSBLayoutID);
        }
      $InsertIntoContext=true;
      }

    if ($action=="do_insinsd")
      {
      $q1=DBQuery ("SELECT OrderNo FROM jsb_Pages
                      WHERE ParentID=$JSBPageID AND SysContext='$SysContext' ORDER BY OrderNo LIMIT 1");
      if ($q1)
        {
        $OriginOrderNo=$q1->Top->OrderNo;
        print "Taken $OriginOrderNo<br>";
        }
      $action="do_insbef";
      $ParentID=$JSBPageID;
      }

    if ($action=="do_insbef")
      {
      $q2=DBQuery ("SELECT OrderNo FROM jsb_Pages
      WHERE ParentID=$ParentID AND SysContext='$SysContext' AND OrderNo<$OriginOrderNo
      ORDER BY OrderNo DESC LIMIT 1");

      if ($q2) {$NewOrderNo=($OriginOrderNo+$q2->Top->OrderNo)/2;}
      else {$NewOrderNo=$OriginOrderNo-100000;}
      }

    if (($action=="do_insaft")||($action=="do_dup"))
      {
      if ($InsertIntoContext)
        {
        print "Inserting in context!";
        $q2=DBQuery ("SELECT OrderNo FROM jsb_Pages WHERE ParentID=$ParentID AND SysContext='$SysContext' ORDER BY OrderNo DESC LIMIT 1");
        if ($q2) {$NewOrderNo=$q2->Top->OrderNo;}
         else {$NewOrderNo=10;}
        }
      else
        {
        $q2=DBQuery ("SELECT OrderNo FROM jsb_Pages WHERE ParentID=$ParentID AND SysContext='$SysContext' AND OrderNo>$OriginOrderNo ORDER BY OrderNo LIMIT 1");
        if ($q2) {$NewOrderNo=($OriginOrderNo+$q2->Top->OrderNo)/2;}
         else {$NewOrderNo=$OriginOrderNo+100000;}
        }
      }

    DBExec("INSERT INTO jsb_Pages (OrderNo,JSBPageID,SysContext,JSBLayoutID,ParentID,Caption,Title,UpdatedAt,State,Options)
        VALUES ($NewOrderNo,$NewPageID,'$SysContext',$JSBLayoutID,$ParentID,'$NewCaption','$NewTitle',".time().",1,'$NewOptions')");
    $result="Page inserted";

    if ($action=="do_dup")
      {
      $qc=DBQuery("SELECT JSBPageControlID,ControlClass,OrderNo,Slot,PropertiesStr
          FROM jsb_PageControls
          WHERE JSBPageID=$JSBPageID AND SysContext='$SysContext' ORDER BY OrderNo","JSBPageControlID");
      if ($qc)
        {
        foreach($qc->Rows as $JSBPageControlID=>$row)
          {
          $NewJSBPageControlID=DBGetID("jsb.PageControl");
          DBExec ("INSERT INTO jsb_PageControls
            (JSBPageControlID,SysContext,JSBPageID,ControlClass,OrderNo,Slot,PropertiesStr) VALUES
            ($NewJSBPageControlID,'$SysContext',$NewPageID,'$row->ControlClass',
            $row->OrderNo,'$row->Slot','$row->PropertiesStr')");
          }
        }
      }
    }

  if (($action=="do_attpage")||($action=="do_virtpage")||($action=="de_attpage")||($action=="de_virtpage"))
    {
    $q=DBQuery ("SELECT Options FROM jsb_Pages WHERE JSBPageID='$JSBPageID' AND SysContext='$SysContext'");
    if ($q)
      {
      parse_str($q->Top->Options,$Options);
      if ($action=="do_attpage")
        {
        $Options['attach']=$Parameter;
        $result="'$Parameter' attached to '$SysContext:$JSBPageID'";
        }
      if ($action=="do_virtpage")
        {
        $Options['virtual']=$Parameter;
        $result="'$Parameter' virtualized in '$SysContext:$JSBPageID'";
        }

      if ($action=="de_attpage")
        {
        $Options['attach']=false;
        $result="Menu detached";
        }
      if ($action=="de_virtpage")
        {
        $Options['virtual']=false;
        $result="Substitution canceled";
        }

      $s="";foreach ($Options as $k=>$v) {if ($s!="") {$s.="&";} if ($v) {$s.="$k=$v";}}
      DBExec ("UPDATE jsb_Pages SET Options='$s' WHERE JSBPageID='$JSBPageID' AND SysContext='$SysContext'");
      }
    }

  if ($result)
    {
    $url=ActionURL("jsb.ISiteExplorer.ExplorerTree.b",
      array(SysContext=>$SysContext,
        BrowserViewMode=>$BrowserViewMode,
        ExpandedItems=>$ExpandedItems,
        ShowHidden=>$ShowHidden,
        ContextLocked=>$ContextLocked,
        ));
    print "<script>window.parent.frames['tree'].location.href='$url';</script>$result";
    }
  else
    {
    print "canceled";
    }
  }

function EditPage($args)
  {
  extract(param_extract(array(
    SysContext=>'string',
    JSBPageID=>'int',
    action=>'string'
    ),$args));

  global $cfg;
#  $_ENV->InitWindows();

  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if (($action=="ren")||($action=="insaft")||($action=="insbef")||($action=="insinsd")||($action=="dup"))
    {
    if (($action=="ren")||($action=="dup"))
      {
      $q=DBQuery ("SELECT Caption,Title,Options FROM jsb_Pages WHERE JSBPageID=$JSBPageID AND SysContext='$SysContext'");
      $Caption=str_replace(array('"',"'"),array('&quot;','&#039;'),$q->Top->Caption);
      $Title  =str_replace(array('"',"'"),array('&quot;','&#039;'),$q->Top->Title);
#      if ($action=="dup") {$Caption="[2]".$Caption;}
      $Options=$q->Top->Options;
      }
    else
      {
      $Caption=""; $Title="";
      }

  if ($SysContext=='layouts')
    {
    $cartridges=&$_ENV->LoadCartridgesList(true);
    $ObjectClasses=false;
    foreach ($cartridges as $c=>$IsActive)
      {
      if (!$IsActive) continue;
      $cartridge=&$_ENV->LoadCartridge($c);
      if (method_exists($cartridge,"ObjectClasses"))
        {
        $info=$cartridge->ObjectClasses();
        # check interface availability
        foreach($info as $aObjectClass=>$info)
          {
          $ObjectClasses[$aObjectClass]="$info[Caption] [$aObjectClass]";
          }
        }
      }
    $_ENV->PutValueSet(array(ValueSetName=>'objclasses', Values=>$ObjectClasses));
    }


  $_ENV->LoadTheme('b');
  parse_str($Options,$o);
  $_ENV->OpenForm(array(Align=>'center',Name=>'ModalForm',ShowCancel=>1,OnSubmit=>"doSubmit()"));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'action',Value=>$formaction));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'SysContext',Value=>$SysContext));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'JSBPageID',Value=>$JSBPageID));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'phtmpl',Value=>$o['phtmpl']));
  $_ENV->PutFormField(array(Type=>'hidden',Name=>'NewOptions',Value=>$Options,Size=>70));

  $_ENV->PutFormField(array(Type=>'langstring',Required=>1,Caption=>$_['CAPTION_PAGE_MENUCAPTION'],Name=>'NewCaption',MaxLength=>30,Value=>$Caption,Size=>30));
  $_ENV->PutFormField(array(Type=>'langstring',Caption=>$_['CAPTION_PAGE_TITLE'],Name=>'NewTitle',MaxLength=>100,Size=>50,Value=>$Title));
  if ($SysContext=='layouts')
    {
    $_ENV->PutFormField(array(Type=>'droplist',Required=>1,DefaultValue=>'jsb.Page',Caption=>$_['LAYOUT_OBJECTCLASS'],Size=>40,ValueSetName=>'objclasses', Name=>"obj",Value=>$o['obj']));
    $_ENV->PutFormField(array(Type=>'identifier',Name=>'lit',MaxLength=>2,Value=>$o['lit'],Caption=>"Layout symbol"));
    }
  print "</table>";
  if ($error) {print $error;}
  print "<a class='jsb_tiny' href='javascript:;' onClick='P$.find(\"more\").style.display=\"block\";'>More details</a>";
  print "<table id='more' style='display:none'  width='100%'>";
  $_ENV->PutFormField(array(Type=>'inputmodal',Name=>'i',Value=>$o['i'],Editable=>1,ModalCall=>ActionURL('jsb.IThemeReader.SelectSkinImage'),Caption=>"Menu icon"));
  $_ENV->PutFormField(array(Type=>'inputmodal',Name=>'hi',Value=>$o['hi'],Editable=>1,ModalCall=>ActionURL('jsb.IThemeReader.SelectSkinImage'),Caption=>"Menu icon on hover"));
  $_ENV->PutFormField(array(Type=>'inputmodal',
    Size=>40,
    Name=>'attach',
    Value=>$o['attach'],
    InitCall=>"jsb.IPage.GetPageNameByValue",
    ModalCall=>"jsb.IPage.Select",
    ModalArgs=>array(SysContext=>$SysContext,ContextSelectable=>1),
    Caption=>"Attached submenu"));
  $_ENV->PutFormField(array(Type=>'inputmodal',
    SysContext=>$SysContext,
    Size=>40,
    Name=>'virtual',Value=>$o['virtual'],
    InitCall=>"jsb.IPage.GetPageNameByValue",
    ModalCall=>"jsb.IPage.Select",
    ModalArgs=>array(SysContext=>$SysContext),
    Caption=>"Substituting page"));

  $_ENV->PutFormField(array(Type=>'string',Name=>'u',Value=>$o['u'],Caption=>"Open URL"));
  $_ENV->PutFormField(array(Type=>'string',Name=>'rk',Value=>$o['rk'],Caption=>"Menu access key"));
  $_ENV->CloseForm();?>
  <script>
  var opvars=["i","hi","rk","attach","virtual","obj","phtmpl","lit","u"];
  function updateOptions(){
		var v,s="";
		for (var i in opvars){
			try{
				v=ModalForm[opvars[i]].value;
				if (v) {if (s!="") s+="&"; s+=opvars[i]+"="+v;}
			}catch(e){}
		}
		ModalForm.NewOptions.value=s;
	}
  function setfocus() {document.getElementById('lfi_ModalFormNewCaption').focus();}
  window.setTimeout("setfocus();",500);
  function doSubmit(){
  	updateOptions();
		if (ModalForm.NewCaption.value!=""){
			W.modalResult({NewCaption:ModalForm.NewCaption.value,NewTitle:ModalForm.NewTitle.value,NewOptions:ModalForm.NewOptions.value});
		}
    return false;
	}
	</script>
    <?
    return;
    }
  return;
  }

function BuildContextTree ($args)
  {
  extract(param_extract(array(
    SysContext=>"string",
    JSBPageID=>"int",
    EditMode=>'int',
    MyURL=>'string',
    TargetURL=>'string',
    UseQueryMethod=>'int',
    Title=>'string',
    ShowHidden=>"int",
    NoLayouts=>"int",
    BrowserViewMode=>"int",
    ExpandedItems=>"string",    # Comma-separated list of ID that should be open
    ContextSelectable=>'int',   # Puts button [Select this context]
    ContextLocked=>'int',       # User will not change context to other one
    ByObjectClass=>'string',# In pulldown list will be placed only context having this method: OnSelect,OnPageLoad
    ),$args));

  global $cfg,$_THEME;
#  $_ENV->InitWindows();
  $_ENV->LoadTheme('b');
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if (!$SysContext)
    {
    print_error("Context not selected for BuildContextTree");
    return;
    }

  $Clause="";
  if ($ByObjectClass)
    {
    $Clause=" AND SysContext='$SysContext' OR (ObjectClass IS NOT NULL AND ObjectClass='$ByObjectClass')";
    }
#  if ($SysContext=='layouts') {$EditMode=0;}
  $qcontexts=DBQuery("SELECT SysContext,Caption,HomePageID FROM sys_Contexts WHERE Hidden<>1 $Clause ORDER BY OrderNo","SysContext");
  if (!$qcontexts)
    {
    print "No contexts found in the system. Browsing disabled";
    return;
    }

  $qlayouts=false;
  if ($SysContext!="layouts")
    {
    $qlayouts =DBQuery("SELECT JSBPageID,Caption,Options FROM jsb_Pages WHERE SysContext='layouts' AND State=1","JSBPageID");
    }

  $s1=""; $s2="";

  $HomePageID=0;
  foreach ($qcontexts->Rows as $aJSBContext=>$row)
    {
    $Caption=langstr_get($row->Caption);
    if (!$Caption) $Caption="(---)";
    if ($aJSBContext==$SysContext) $HomePageID=intval($row->HomePageID);
    if ($s1) {$s1.=',';} $s1.=$aJSBContext.':"'.$Caption.'"';
    }

  if ((!$JSBPageID)&& $HomePageID) $JSBPageID=$HomePageID;

  if (($qlayouts)&&(!$NoLayouts))
    {
    foreach ($qlayouts->Rows as $aJSBPageID=>$row)
      {
      parse_str($row->Options,$opt);
      $lit=$opt['lit'];
      $Caption=langstr_get($row->Caption);
      if (!$Caption) $Caption="(---)";
      if ($s2) {$s2.=",";} $s2.="$aJSBPageID:'$lit-".langstr_get($Caption)."'";
      }
    $s2='{'.$s2.'}';
    }
    else {$s2="false";}

  $ContextName=langstr_get($qcontexts->Rows[$SysContext]->Caption);

  if ($Title)
    {
    $_ENV->SetWindowOptions(array(Title=>$Title));
    }

  print '
<script src="'.$cfg[PublicURL].'/jsb/ExplorerTree.js"></script>
<script>
TActsC={attpage:"'.$_['CAPTION_ATTACH_PAGE'].'",
dup:"'.$_[CAPTION_DUPLICATE].'",
virtpage:"'.$_[CAPTION_VIRTUAL_PAGE].'",
ren:"'.$_[CAPTION_RENAME_PAGE].'",
insbef:"'.$_[CAPTION_INSERT_PAGE_BEFORE].'",
insaft:"'.$_[CAPTION_INSERT_PAGE_AFTER].'",
insinsd:"'.$_[CAPTION_INSERT_PAGE_INSIDE].'",
recycle:"'.$_[CAPTION_RECYCLE].'",
hide:"'.$_[CAPTION_HIDE].'",
unhide:"'.$_[CAPTION_UNHIDE].'",
putbef:"'.$_[CAPTION_PUTBEFORE].'",
putaft:"'.$_[CAPTION_PUTAFTER].'",
putinsd:"'.$_[CAPTION_PUTINSIDE].'",
showhid:"'.$_[CAPTION_SHOWHIDDEN].'",
hidehid:"'.$_[CAPTION_HIDEHIDDEN].'",
view0:"'.$_[CAPTION_DATE].'",
view1:"'.$_[CAPTION_LAYOUT].'",
view2:"'.$_[CAPTION_VIEWCOUNT].'",
sethome:"'.$_[CAPTION_SETHOMEPAGE].'",
de_attpage:"'.$_[CAPTION_DETACH_PAGE].'",
de_virtpage:"'.$_[CAPTION_DEVIRT_PAGE].'"};


STRINGS={CAPTION_CHANGE_LAYOUT_TO:"'.$_[CAPTION_CHANGE_LAYOUT_TO].'",MSG_NO_SELECTED_PAGES:"'.$_[MSG_NO_SELECTED_PAGES].'"};
Contexts={'.$s1.'};
Layouts='.$s2.';
ImagesURL=SkinURL+"/";
MyURL="'.$MyURL.'";
TransactURL="'.ActionURL("jsb.ISiteExplorer.UpdatePage.n").'";
EditPageURL="'.ActionURL("jsb.ISiteExplorer.EditPage.b").'";
SelectPageURL="'.ActionURL("jsb.IPage.Select.b").'";
JSBPageID="'.$JSBPageID.'";
ContextSelectable='.intval($ContextSelectable).';
ContextName="'.$ContextName.'";
ContextLocked='.intval($ContextLocked).';
NoLayouts='.intval($NoLayouts).';
SysContext="'.$SysContext.'";
EditMode='.intval($EditMode).';
ShowHidden='.intval($ShowHidden).';
';

  if ($BrowserViewMode) {print 'BrowserViewMode="'.$BrowserViewMode.'";';}
  print '</script>';

  global $_SYSSKIN_NAME;
  $dropimg="<img src='$_THEME[SkinURL]/drop.gif' border=0 align='absmiddle'>";
  print "<table width='98%' border=0 cellpadding=5 cellspacing=0><tr><td align='right' class='topmenu_bg'>";
  print " <a href='#' class='jsb_menuh' onClick='location.reload();'>$_[CAPTION_REFRESH]</a> | ";
  if ($EditMode)
    {
    print "<a href='#' class='jsb_menuh' onClick='PopupBrowserEdit(this)'>".$_['CAPTION_MODIFY'].$dropimg."</a> |";
    }
  print " <a href='#' class='jsb_menuh' onClick='PopupBrowserView(this)'>".$_['CAPTION_VIEWMODE'].$dropimg."</a></td></tr></table>";

  $LoadingContexts[$SysContext]=1;
  $res="\nnavtree=new Array();\n"; $rowno=0;

  $months=explode (',',$__['SHORT_MONTH_NAMES']);
  for ($MAX_CONTEXTS=10;$MAX_CONTEXTS>0;$MAX_CONTEXTS--)
    {
    $NextContext=false;
    # look for nonloaded context
    foreach ($LoadingContexts as $k=>$v)
      {
      if ($v==1)
        {
        $NextContext=$k;
        $LoadingContexts[$k]=2;
        break;
        }
      }

    if (!$NextContext) {break;}
    $wc=" WHERE SysContext='$NextContext'";
    if (!$ShowHidden) {$wc.=" AND (State=1 OR State=2)";}
    $s="SELECT CONCAT(SysContext,'~',JSBPageID) AS ContextPageID,
        Caption,Title,ParentID,JSBLayoutID,Options,UpdatedAt,State,Viewed
        FROM jsb_Pages $wc ORDER BY SysContext,OrderNo";
    $qpages=DBQuery ($s,"ContextPageID");

    $s=false;
    $PrevContext="";

    if ($qpages)
      {
      foreach ($qpages->Rows as $Context_PageID=>$pagedata)
        {
        $Caption=langstr_get($pagedata->Caption);
        if (!$Caption) $Caption="(???)";
        if (!$s) {$s='navtree['.$rowno.']="'; $rowno++;}
        list ($aJSBContext,$aJSBPageID)=split ("~",$Context_PageID);
        if ($aJSBContext!=$PrevContext) {$s.=$aJSBContext;}
        $s.=":".intval($pagedata->ParentID).":".intval($aJSBPageID).":";
        $s.=str_replace(array('"',"'",'|','@'),array('&quot;','&#039;',' ',' '),$Caption);
        $PrevContext=$aJSBContext;
        if (($HomePageID)&&($HomePageID==$aJSBPageID)) $s.="@home=1";
        if ($pagedata->State!=1) {$s.='@s='.intval($pagedata->State);}
        parse_str ($pagedata->Options,$Options);

        $s.='@vc='.$pagedata->Viewed;

        if ($Options['attach'])
          {
          $s.='@at='.$Options['attach'];
          list ($ctx,$pg)=explode ('/',$Options['attach']);

          $qt=DBQuery ("SELECT Caption FROM jsb_Pages WHERE SysContext='$ctx' AND JSBPageID='$pg'");
          $s.='@atinf=('.$ctx.')'.langstr_get($qt->Top->Caption);

          if (!$LoadingContexts[$ctx])
            {$LoadingContexts[$ctx]=1;}     # markup other context to load
          }
        if ($Options['virtual'])
          {
          $s.='@v='.$Options['virtual'];
          list ($ctx,$pg)=explode ('/',$Options['virtual']);

          $qt=DBQuery ("SELECT Caption FROM jsb_Pages WHERE SysContext='$ctx' AND JSBPageID='$pg'");
          $s.='@virtinf='.$ctx.'/'.str_replace(array('"',"'",'|','@'),array('&quot;','&#039;',' ',' '),langstr_get($qt->Top->Caption));
          }
        $d=$pagedata->UpdatedAt;
        if ($d)
          {
          $d=getdate($d);
          $s.='@date='.$d['mday'].' '.$months[$d['mon']-1].' '.$d['year'];
          } else {$s.='@date='.$_['CAPTION_UNKNOWN_UP_DATE'];}
        if (($aJSBContext!="layouts")&&($pagedata->JSBLayoutID)) {$s.='@lay='.$pagedata->JSBLayoutID;}

        if ($aJSBContext=="layouts")
          {
          $phtmpl=$Options['phtmpl'];
          $lit=$Options['lit'];
          if ($phtmpl)
            {
            $s.="@tmpl=$lit-$phtmpl";
            }
          }
        if (strlen($s)>16384) {$res.=$s."\";\n"; $s=false;} else {$s.="|";}
        }

      if ($NextContext==$SysContext)
        {
        # EXPAND ALL FOLDERS CONTAINS EDITING PAGE
        $exitems="";
        if (($JSBPageID)&&($SysContext))
          {
          $id=$JSBPageID;
          for ($i=0;$i<16;$i++)
            {
            $pagedata=&$qpages->Rows[$SysContext.'~'.$id];
            if ($pagedata)
              {
              $ParentID=$pagedata->ParentID;
              }
            if ($ParentID)
              {
              if ($exitems) $exitems.=",";
              $exitems.=$ParentID;
              }
            $id=$ParentID;
            }
          }
        if ($exitems)
          {
          if ($ExpandedItems) $ExpandedItems.="|";
          $ExpandedItems.="$SysContext:$exitems";
          }
        }
      }
    if ($s) {$res.=$s."\";\n";}
    }

  if ($ExpandedItems) {$res.='ExpandedItems="'.$ExpandedItems.'";';}
  $ReadOnly=intval(!$EditMode);
  print "<script>$res InitEditor($EditMode);</script>";

  # Put Select button
  if ($ContextSelectable)
    {
    $_ENV->PutButton(array(
      Caption=>$_['SELECT_THIS_CONTEXT'],
      OnClick=>"W.modalResult('$SysContext/0\\n[$ContextName]');"
      ));
    print "<br>";
    }

  print "<a href='javascript:;' onClick='PopupBrowserContexts(this)' class='jsbh'><img
    src='$_THEME[SkinURL]/tree_f.gif' border=0
     align='absmiddle'>$ContextName$dropimg</a>
    <br>";

  print "<script>
    m=new TNavTree ('tab_1',navtree,'$SysContext',0,'$TargetURL','viewer','$UseQueryMethod');
    m.Build('',0,false,$ReadOnly);
    </script>";
  
  $l=strlen ($res);
  $info="Tree data:";
  if ($l<4096) {$info.="$l bytes";} else {$info.=ceil($l/1024)." Kbytes";}
  print "<br><div id='treestatus' style='font-size:9px; font-family:arial,sans;color:#808080'>$info</div>";


  }

}
?>
