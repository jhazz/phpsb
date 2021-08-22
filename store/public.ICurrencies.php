<?
class store_ICurrencies
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Store";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function store_ICurrencies()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $this->Title=$_['PINTERFACE_CURRENCIES'];
  }

function Save($args)
  {
  extract(param_extract(array(
    Name=>'array:string',
    Caption=>'array:string',
    Prefix=>'array:string',
    Suffix=>'array:string',
    Decimals=>'array:int',
    CurrencyID=>'int',
    Keys=>'string',
    ),$args));

  $InsertMode=0;
  if (!$CurrencyID)
    {
    $CurrencyID=DBGetID("store.Currency");
    $InsertMode=1;
    }

  $Keys=explode (",",$Keys);
  foreach ($Keys as $LangID)
    {
    DBReplace(array(
      Table=>'store_Currencies',
      Values=>array(
        Caption=>$Caption[$LangID],
        Name=>$Name[$LangID],
        Prefix=>$Prefix[$LangID],
        Suffix=>$Suffix[$LangID],
        Decimals=>$Decimals[$LangID],
        ),
      Keys=>array(CurrencyID=>intval($CurrencyID),LangID=>$LangID),
      ));
    }
  return array(ModalResult=>true);
  }

function Edit($args)
  {
  extract(param_extract(array(
    CurrencyID=>'int',
    LangID=>'string',
    ),$args));
  $_=&$GLOBALS[_STRINGS][store];

  if ($CurrencyID)
    {
    $qc=DBQuery ("SELECT * FROM store_Currencies WHERE CurrencyID=$CurrencyID","LangID");
    if ($qc)
      {
      extract(param_extract(array(
        Name=>'string',
        Caption=>'string',
        Prefix=>'string',
        Suffix=>'string',
        Decimals=>'int',
        ),$qc->Top));
      $Title="$_[EDIT_CURRENCY] '$Name'";
      }
    } else {$Title="$_[ADD_CURRENCY]";}

  print "<h1>$Title</h1><table width='100%'><tr><td align='center'>";
  $Languages=&$_LANGUAGE_DISPATCHER->LoadLanguages();

  if (!$qc)
    {
    $qc=new TRecordSet();
    $qc->Fields=array(LangID=>"LangID",Name=>$_['CURRENCY_NAME'],Caption=>$_['CURRENCY_CAPTION']);
    }

  foreach ($Languages as $aLangID=>$l)
    {
    unset($row);
    if (!$l['Enabled']) continue;
    if ($qc) $row=&$qc->Rows[$aLangID];
    if (!$row)
      {
      $row=new stdclass;
      $r->Rows[$aLangID]=&$row;
      }
    $row->LangID=$aLangID;
    $LangValues[$aLangID]=$l['Caption'];
    }
  $_ENV->PrintTable($qc,array(
    Action=>ActionURL("store.ICurrencies.Save.b"),
    ModalOkOnOk=>1,
    Fields=>array(
      LangID=>"Lang",
      Name=>$_['CURRENCY_NAME'],
      Caption=>$_['CURRENCY_CAPTION'],
      Prefix=>$_['CURRENCY_PREFIX'],
      Suffix=>$_['CURRENCY_SUFFIX'],
      Decimals=>$_['CURRENCY_DECIMALS']),
    HiddenFields=>array(CurrencyID=>$CurrencyID),
    FieldTypes=>array(
      LangID=>array(Type=>"lookupvalue",Values=>$LangValues),
      Name=>"inputstring:3",
      Caption=>"inputstring:40",
      Prefix=>"inputstring:10",
      Suffix=>"inputstring:10",
      Decimals=>"inputint",
      ),
    TableStyle=>1,
    PutKeyFieldsList=>true,
    Width=>'600',
    ShowOk=>true,
    ShowCancel=>true,
    ThisObject=>&$this));
  }

function Update($args)
  {
  extract(param_extract(array(
    Name=>'array:string',
    Caption=>'array:string',
    Prefix=>'array:string',
    Suffix=>'array:string',
    Decimals=>'array:int',
    LangID=>'string',
    Keys=>'string',
    action=>'string',
    check=>'int_checkboxes',
    ),$args));

  if ($action=='delete')
    {
    $ids=implode (",",array_keys($check));
    if ($ids)
      {
      $s="DELETE FROM store_Currencies WHERE CurrencyID IN ($ids)";
      DBExec ($s);
      }
    }
  return array(ModalResult=>true);
  }

function Browse($args)
  {
  extract(param_extract(array(
    FilterLangID=>'string',
    PageNo=>'int',
    ),$args));

  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];
  
  $Languages=&$_LANGUAGE_DISPATCHER->LoadLanguages();
  $FilterLangValues=array();
  foreach ($Languages as $aLangID=>$l){
    if ($l['Enabled'])
      {
      if (!$FilterLangID) $FilterLangID=$aLangID;
      $FilterLangValues[$aLangID]=$l['Caption'];
      }
    }
  $qc=DBQuery("SELECT * FROM store_Currencies WHERE LangID='$FilterLangID' ORDER BY CurrencyID","CurrencyID");
  $_ENV->PrintTable($qc,array(
    Action=>ActionURL("store.ICurrencies.Update.b"),
    Fields=>array(
      Name=>$_['CURRENCY_NAME'],
      Caption=>$_['CURRENCY_CAPTION']),
    HiddenFields=>array(LangID=>$FilterLangID),
    ButtonEdit=>array(ModalWindowAction=>"store.ICurrencies.Edit.b",KeyName=>"CurrencyID",Width=>700,Height=>350),
    Filters=>array(
      array(Caption=>"Choose language",Variable=>'FilterLangID',Type=>"radio",Values=>$FilterLangValues,Value=>$FilterLangID),
      ),
    FiltersAutoReload=>true,
    ShowCheckers=>true,
    ShowDelete=>true,
    TableStyle=>1,
    PutKeyFieldsList=>true,
    Width=>'600',
    ButtonAdd=>array(ModalWindowURL=>ActionURL("store.ICurrencies.Edit.b",array(LangID=>$FilterLangID)),Width=>700,Height=>350),
    ThisObject=>&$this));
  }
}
?>
