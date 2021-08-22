<?
class store_IProdOptions
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Store";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $SKUProductID;

function store_IProdOptions()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $this->Title=$_['STORE_PRODUCT_OPTIONS'];
  }

function tab_OptionProductCount(&$OptionID,&$row,$fname)
  {
  $c="";
  if ($this->qc) {$c=$this->qc->Rows[$OptionID]; $c=$c->ValueCount; if ($c) {$c=" ($c)";} }
  print $c;
  }
function tab_OptionValuesCount(&$OptionID,&$row,$fname)
  {
  $__=&$GLOBALS['_STRINGS']['_'];
  $c="";
  if ($this->qvc)
    {
    $c=$this->qvc->Rows[$OptionID];
    $c=$c->ValueCount;
    if ($c) {
      $c="$__[CAPTION_EDIT]&nbsp;($c)";
      }  else $c="$__[CAPTION_EDIT]";
    $c="<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("store.IProdOptions.EditOptionValues.b",array(OptionID=>$OptionID))."\",reloadOnOk:1,w:700,h:500})'>$c</a>";
    }
  print $c;
  }

function Browse($args)
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  $qo=DBQuery ("SELECT OptionID,Name,OrderNo,IsSelectBox,Hidden FROM store_Options ORDER BY OrderNo","OptionID");
  $this->qc=DBQuery ("SELECT OptionID,COUNT(OptionValueID) AS ValueCount FROM store_ProdOptions GROUP BY OptionID","OptionID");
  $this->qvc=DBQuery ("SELECT OptionID,COUNT(OptionValueID) AS ValueCount FROM store_OptionValues GROUP BY OptionID","OptionID");

  $_ENV->PrintTable($qo,array(
    Action=>ActionURL("store.IProdOptions.OptionTableDoAction.b"),
    ReloadOnOk=>1,
    Fields=>array(
      Name=>$_['OPTION_NAME'],
      ProductCount=>$_['OPTION_USAGECOUNT'],
      ValuesCount=>$_['OPTION_VALUESCOUNT'],
      IsSelectBox=>$_['OPTION_ISPRODUCTSELECTBOX'],
      Hidden=>$_['OPTION_HIDDEN'],
      OrderNo=>$_['ORDERNO']),
    ShowCheckers=>1,
    FieldHooks=>array(ProductCount=>tab_OptionProductCount,ValuesCount=>tab_OptionValuesCount),
    FieldTypes=>array(
      Name=>array(
        Type=>'langstring',
        Action=>"store.IProdOptions.EditOption.b",
        Modal=>1,
        KeyName=>"OptionID",
        Width=>700,
        Height=>500),
      Hidden=>'checkbox',
      IsSelectBox=>'checkbox',
      OrderNo=>'int',
      ),
    ColAligns=>array(ProductCount=>'center',ValuesCount=>'center'),
    TableStyle=>1,
    Width=>'700',
    ColWidths=>$Control->ColumnWidths,
    PutKeyFieldsList=>true,
    ShowDelete=>1,
    ButtonAdd=>array(ModalWindowURL=>ActionURL("store.IProdOptions.EditOption.b"),Width=>700,Height=>500),
    ThisObject=>&$this));

  ?>
  <script>
  document.justFocusing=false;
  </script>
  <?
  }




function EditOptionValues($args)
  {
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  extract(param_extract(array(
    OptionID=>'int',
    ),$args));

  if ($OptionID)
    {
    $qo=DBQuery ("SELECT * FROM store_Options WHERE OptionID=$OptionID");
    $qv=DBQuery ("SELECT OptionValueID,Name FROM store_OptionValues WHERE OptionID=$OptionID","OptionValueID");
    $Name=$qo->Top->Name;
    $s=langstr_get($Name);
    }
  else
    {
    $s=$_['ADD_NEW_OPTION'];
    }
  print "<h1>$s</h1><table width='100%'><tr><td align='center'>";

  ?>
  <script>
  document.justFocusing=false;
  function doFocusBox(fieldname) {
    var f=P$.find(fieldname);
    if (f) {f.focusing=true; f.focus();}
    }
  function checkAddBox(event,focusingField)
    {
    var s=""; var input=false,pinput=false,ppinput=false;
    var result=true;
    var f=P$.find(focusingField);
    if (f && f.focusing) {f.focusing=false; if (event) {event.returnValue=true; return true;}}
    var i;
    for (i=0;i<100;i++) {
      ppinput=pinput;
      pinput=input;
      input=P$.find("new["+i+"]");
      if (input) {s+="<tr><td></td><td><input type='text' onFocus='return checkAddBox(event,\"new["+i+"]\");' class='inputarea' id='new["+i+"]' name='new["+i+"]' value='"+input.value+"'></td></tr>";}
       else break;
      }
    if ( (!pinput) || (!ppinput) || (pinput.value!="") || (  (pinput.value=="")&&(ppinput.value!="") ) ) {
      s+="<tr><td>[+]</td><td><input type='text' onFocus='return checkAddBox(event,\"new["+i+"]\");'  class='inputarea'  id='new["+i+"]' name='new["+i+"]' value=''></td></tr>";
      }

    P$.find("addValuesBox").innerHTML="<table>"+s+"</table>";
    if (focusingField) {
      window.setTimeout("doFocusBox('"+focusingField+"');",20);
      result=false;
    } else {
      result=true;
    }
    if (event) {event.returnValue=result;}  return result;
    }
  </script>
  <?
  if ($qo)
    {
    print "<h2>$_[TEXT_OPTION_VALUES]</h2>";

    $_ENV->OpenForm(array(
      Name=>"VForm",
      ShowCancel=>1,
      Modal=>1,
      Action=>ActionURL("store.IProdOptions.UpdateOptionValues.b"),
      Align=>"center"));


    if ($qv)
      {
      $ColCount=4;
      $ColNo=0;
      print "<table cellpadding='5' cellspacing='2' border='0'>";
      if (!$ColNo) print "<tr>";
      foreach ($qv->Rows as $OptionValueID=>$row)
        {
        print "<td bgcolor='#e8e8e8'>";
#        <input class='inputarea' name='old[$OptionValueID]' type='text' size='10' value='$row->Name'>
        $_ENV->PutFormField(array(
          Type=>'langstring',
          Name=>"old[$OptionValueID]",
#          Caption=>$_['OPTION_NAME'],
          Value=>$row->Name,
          Style=>"vertical",
          MaxLength=>100));
        print "</td>";
        $ColNo++; if ($ColNo>=$ColCount) {$ColNo=0; print "</tr>";}
        }
      if ($ColNo) print "</tr>";
      print "</table><br><a href='".ActionURL("store.IProdOptions.ShowValuesUsage.b",array(OptionID=>$OptionID))."' ";
#      onClick='W.openModal({url:\"".ActionURL("store.IProdOptions.ShowValuesUsage.b",array(OptionID=>$OptionID))."\",w:500,h:400,reloadOnOk:1});'>";
      print ">$_[REMOVING_UNUSABLE_VALUES]</a><hr>";
      }
    print "$_[ADD_OPTION_VALUES]<br><div id='addValuesBox'></div>
         <input type='hidden' name='OptionID' value='$OptionID'>";
    $_ENV->CloseForm();
    print "</div>";
    print "<script>checkAddBox()</script></td></tr></table>";
    }
  }

function UpdateOptionValues ($args)
  {
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  extract(param_extract(array(
    OptionID=>'int',
    ),$args));
  if ($args['old'])
    {
    foreach ($args['old'] as $OptionValueID=>$Value)
      {
      $Value=trim($Value);
      DBExec ("UPDATE store_OptionValues SET Name='$Value' WHERE OptionID=$OptionID AND OptionValueID=$OptionValueID");
      }
    }
  if ($args['new'])
    {
    foreach ($args['new'] as $i=>$Value)
      {
      $Value=trim($Value);
      if ($Value==='') {continue;}
      $OptionValueID=DBGetID("store.OptionValue");
      DBExec ("INSERT INTO store_OptionValues (OptionID,OptionValueID,Name) VALUES ($OptionID,$OptionValueID,'$Value')");
      }
    }
  return array(ModalResult=>true);
  }

function ShowValuesUsage($args)
  {
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  extract(param_extract(array(
    OptionID=>'int',
    ),$args));
  $qo=DBQuery ("SELECT Name FROM store_Options WHERE OptionID=$OptionID");
  if (!$qo) {return array(Error=>"Option not found");}

  $qc=DBQuery("SELECT OptionValueID, COUNT(OptionValueID) AS ValueCount FROM store_ProdOptions WHERE OptionID=$OptionID GROUP BY OptionValueID","OptionValueID");
  print "<h1>$_[REMOVING_UNUSABLE_VALUES]</h1><h2>".langstr_get($qo->Top->Name)."</h2><table width='100%'><tr><td align='center'>";
  $qv=DBQuery ("SELECT OptionValueID,Name FROM store_OptionValues WHERE OptionID=$OptionID","OptionValueID");
  if ($qv)
    {
    $ColCount=5;
    $ColNo=0;
    print "<form method='post' onSubmit='this.target=W.openModal({w:300,h:300,reloadOnOk:1});'
      action='".ActionURL("store.IProdOptions.RemoveOptionValues.b")."' name='editoptionvaluesform'>
      <table cellpadding='5' cellspacing='2' border='0'>";
    if (!$ColNo) print "<tr>";
    foreach ($qv->Rows as $OptionValueID=>$row)
      {
      $c=""; $c=$qc->Rows[$OptionValueID]; if ($c) $c=$c->ValueCount;
      $color='#e0ffe0';
      if ($c) {$c="<a href='javascript:' onClick='W.openModal({url:\"".
        ActionURL("store.IProdOptions.ShowProductsOfValue.b",
        array(OptionID=>$OptionID, OptionValueID=>$OptionValueID))."\",w:500,h:450,reloadOnOk:1})'>($c)</a>";
        $color='#e8e8e8';
        }
      $Name=langstr_get($row->Name);
      print "<td bgcolor='$color'><input type='checkbox' name='check[$OptionValueID]' value='1'>$Name $c</td>";
      $ColNo++; if ($ColNo>=$ColCount) {$ColNo=0; print "</tr>";}
      }
    if ($ColNo) print "</tr>";
    print "</table><hr>";
    $s="";
    foreach ($qv->Rows as $OptionValueID=>$row)
      {
      $Name=langstr_get($row->Name);
      $s.="<option value='$OptionValueID'>$Name</option>";
      }
    $s.="<option>----------------</option><option value='-1'>$_[JUST_REMOVE_VALUES]</option>";
    $s="$_[REMOVE_VALUES_AND_SET_OPTION]<br/><select name='changetovalue' class='inputarea'>$s</select><br/><br/>
    <input type='hidden' name='OptionID' value='$OptionID'>"
    .$_ENV->PutButton(array(Action=>'submit',ToString=>1))
    .$_ENV->PutButton(array(Action=>'ok',Kind=>'cancel',ToString=>1))."</form>";
    print $s;
    }
  }

function RemoveOptionValues($args)
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  extract(param_extract(array(
    OptionID=>'int',
    check=>'int_checkboxes',
    changetovalue=>'int',
    ),$args));


  if ((!$OptionID)||(!$check)) {return array(ModalResult=>'cancel');}

  if (array_key_exists($changetovalue,$check))
    {
    return array(Message=>$_[SELECTED_SAME_VALUE_LIKE_REMOVING]);
    }

  $remlist=implode (",",array_keys($check));
  if ($changetovalue==-1)
    {
    $s="DELETE FROM store_ProdOptions WHERE OptionID=$OptionID AND OptionValueID IN ($remlist)";
    }
  else
    {
    $s="UPDATE store_ProdOptions SET OptionValueID=$changetovalue WHERE OptionID=$OptionID AND OptionValueID IN ($remlist)";
    }
  DBExec ($s);
  DBExec ("DELETE FROM store_OptionValues WHERE OptionID=$OptionID AND OptionValueID IN ($remlist)");
  return array(ModalResult=>true);
  }

function tab_ProdName(&$ProductID,&$row,$fname)
  {
  print "<a href='javascript:;' onClick=\"W.openModal({url:'".ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$ProductID,Page=>'options'))."',w:750,h:550})\">".langstr_get($row->$fname)."</a>";
  }
function ShowProductsOfValue($args)
  {
  global $cfg;
  $_=&$GLOBALS['_STRINGS']['store'];

  extract(param_extract(array(
    OptionID=>'int',
    OptionValueID=>'int',
    ),$args));

  $s="SELECT o.ProductID,p.Name FROM store_ProdOptions o INNER JOIN store_Products p ON o.ProductID=p.ProductID
    WHERE o.OptionID=$OptionID AND o.OptionValueID=$OptionValueID";
  $qo=DBQuery ("SELECT Name FROM store_Options WHERE OptionID=$OptionID");
  $qp=DBQuery ("SELECT o.ProductID,p.Name FROM store_ProdOptions o INNER JOIN store_Products p ON o.ProductID=p.ProductID
    WHERE o.OptionID=$OptionID AND o.OptionValueID=$OptionValueID","ProductID");

  print "<h1>$_[PRODUCTS_OF_VALUE]</h1><h2>".langstr_get($qo->Top->Name)."</h2>";
  if (!$qp) return;
  $_ENV->PrintTable($qp,array(
    Action=>ActionURL("store.IProdOptions.DoAction.bm",array(GroupID=>$GroupID)),
    Fields=>array(Name=>$_['PRODUCT_NAME']),
    ShowCheckers=>0,
    FieldHooks=>array(Name=>tab_ProdName),
    ShowDelete=>0,
    TableStyle=>1,
    Width=>'100%',
    ColWidths=>$Control->ColumnWidths,
    BgColor_Hovered=>'#fff0f0',
    BgColor_Checked=>'#fff0e0',
    ThisObject=>&$this));

  }

function OptionTableDoAction($args)
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];

  extract(param_extract(array(
    action=>'string',
    check=>'int_checkboxes',
    subaction=>'string',
    Hidden=>'array:int',
    IsSelectBox=>'array:int',
    OrderNo=>'array:int',
    Keys=>'string',
    ),$args));

  $Keys=explode (",",$Keys);
  if ($check) {$checklist=implode (",",array_keys($check));}

  if (($Keys) && ($action=='ok'))
    {
    foreach($Keys as $OptionID)
      {
      $vIsSelectBox=intval($IsSelectBox[$OptionID]);
      $vHidden=intval($Hidden[$OptionID]);
      $vOrderNo=intval($OrderNo[$OptionID]);
      $s="UPDATE store_Options SET
        IsSelectBox=$vIsSelectBox,
        OrderNo=$vOrderNo,
        Hidden=$vHidden
        WHERE OptionID=$OptionID";
      DBExec ($s);
      }
    return array(ModalResult=>true);
    }

  if (($checklist)&&($action=='delete'))
    {
    DBExec ("DELETE FROM store_OptionValues WHERE OptionID IN ($checklist)");
    DBExec ("DELETE FROM store_Options WHERE OptionID IN ($checklist)");
    DBExec ("DELETE FROM store_ProdOptions WHERE OptionID IN ($checklist)");
    return array(ModalResult=>true);
    }
  }

function EditOption($args)
  {
  extract(param_extract(array(
    OptionID=>'int',
    ),$args));
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;

  if ($OptionID)
    {
    $qo=DBQuery ("SELECT * FROM store_Options WHERE OptionID=$OptionID");
    extract(param_extract(array(
      Name=>'string',
      Unit=>'string',
      Info=>'string',
      IsSelectBox=>'int',
      Colour=>'string',
      HasImage=>'int',
      OptionGroupID=>'int',
      HasDescription=>'int',
      Hidden=>'int',
      ),$qo->Top));
    $Title=langstr_get($Name);
    }
  else {$Title=$_['ADD_NEW_OPTION'];}
  print "<table width='100%' height='100%'><tr><td align='center'>";
  $_ENV->OpenForm(array(Align=>'center',Title=>$Title,Name=>"Form1",ShowCancel=>1,Action=>ActionURL("store.IProdOptions.UpdateOption.b")));
  $_ENV->PutFormField(array(Type=>'inputmodal',
    Size=>40,
    Name=>'OptionGroup',
    Value=>$cfg['Settings']['store']['ProductOptionGroupsContext'].'/'.$OptionGroupID,
    InitCall=>"jsb.IPage.GetPageNameByValue",
    ModalCall=>"jsb.IPage.Select",
    ModalArgs=>array(SysContext=>$cfg['Settings']['store']['ProductOptionGroupsContext'],ContextLocked=>1,ContextSelectable=>1),
    Caption=>"Attribute group"));
  
  $_ENV->PutFormField(array(Type=>'langstring',Name=>'Name',Caption=>$_['OPTION_NAME'],Value=>$Name,MaxLength=>100,Required=>True));
  $_ENV->PutFormField(array(Type=>'langstring',Name=>'Unit',Caption=>$_['OPTION_UNIT'],Value=>$Unit,MaxLength=>20));
  $_ENV->PutFormField(array(Type=>'langstring',Name=>'Info',Caption=>$_['OPTION_INFO'],Value=>$Info,MaxLength=>100));
  $_ENV->PutFormField(array(Type=>'checkbox',Name=>'IsSelectBox',Caption=>$_['OPTION_ISPRODUCTSELECTBOX'],Value=>$IsSelectBox));
  $_ENV->PutFormField(array(Type=>'checkbox',Name=>'Hidden',Caption=>$_['OPTION_HIDDEN'],Value=>$Hidden));
  $_ENV->PutFormField(array(Type=>'color',Name=>'Colour',Caption=>$_['OPTION_COLOUR'],Value=>$Colour));
  $_ENV->PutFormField(array(Type=>'checkbox',Name=>'HasImage',Caption=>$_['OPTION_HASIMAGE'],Value=>$HasImage));
  $_ENV->PutFormField(array(Type=>'checkbox',Name=>'HasDescription',Caption=>$_['OPTION_HASDESCRIPTION'],Value=>$HasDescription));
  print "<input type='hidden' name='OptionID' value='$OptionID'>";
  $_ENV->CloseForm();
  }

function UpdateOption ($args)
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg;
  extract(param_extract(array(
    OptionID=>'int',
    OptionGroup=>'string',
    Name=>'string',
    Info=>'string',
    Unit=>'string',
    IsSelectBox=>'int',
    Hidden=>'int',
    HasImage=>'int',
    HasDescription=>'int',
    Colour=>'string',
    ),$args));

  $OptionGroupID=0;
  if ($OptionGroup)
    {
    list($x,$OptionGroupID)=explode ("/",$OptionGroup);
    $OptionGroupID=intval($OptionGroupID);
    }
  if (!$OptionID) {
    $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM store_Options");
    $OrderNo=($q)?intval($q->Top->MaxOrderNo)+10:10;
    $OptionID=DBGetID("store.Option");
    if (DBExec ("INSERT INTO store_Options (OptionID,OptionGroupID,Name,Unit,Info,OrderNo,IsSelectBox,Hidden,HasImage,HasDescription,Colour)
      VALUES ($OptionID,$OptionGroupID,'$Name','$Unit','$Info',$OrderNo,$IsSelectBox,$Hidden,$HasImage,$HasDescription,'$Colour')"))
    return array(ModalResult=>true);
  } else {
  	if (DBExec ("UPDATE store_Options SET
    Name='$Name', OptionGroupID=$OptionGroupID, Unit='$Unit', Info='$Info', IsSelectBox=$IsSelectBox, Hidden=$Hidden, HasImage=$HasImage,
    HasDescription=$HasDescription, Colour='$Colour' WHERE OptionID=$OptionID"))
    return array(ModalResult=>true);
    }
  }



} #class
