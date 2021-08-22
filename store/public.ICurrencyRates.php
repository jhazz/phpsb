<?
class store_ICurrencyRates
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Store";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function store_ICurrencyRates()
  {
  $_=&$GLOBALS[_STRINGS][store];
  $this->Title=$_[INTERFACE_CURRENCYRATES];
  }

function Save($args)
  {
  extract(param_extract(array(
    CurrencyIDs=>'array:int',
    Rate=>'array:float',
    ),$args));

  $InsertMode=0;
  if (!$CurrencyID)
    {
    $CurrencyID=DBGetID("store.Currency");
    $InsertMode=1;
    }

#  $Keys=explode (",",$Keys);
  foreach ($CurrencyIDs as $i=>$CurrencyID)
    {
    print "$CurrencyID=".$Rate[$CurrencyID]."<br>";
    /*
    DBReplace(array(
      Table=>'store_CurrencyRates',
      Values=>array(
        $CurrencyID
        Caption=>$Caption[$LangID],
        Name=>$Name[$LangID],
        Prefix=>$Prefix[$LangID],
        Suffix=>$Suffix[$LangID],
        Decimals=>$Decimals[$LangID],
        ),
      Keys=>array(CurrencyID=>intval($CurrencyID),LangID=>$LangID),
      ));
      */
    }
#  return array(ModalResult=>true);
  }

function Edit($args)
  {
  extract(param_extract(array(
    CurrencyID=>'int',
    ),$args));
  $_=&$GLOBALS['_STRINGS']['store'];
  global $_LANGUAGE;

  if (!$CurrencyID)
    {
    return array(ModalResult=>'cancel');
    }

  $qc=DBQuery ("SELECT Name,Caption FROM store_Currencies WHERE LangID='$_LANGUAGE' AND CurrencyID=$CurrencyID");
  $qcr=DBQuery ("SELECT Rate FROM store_CurrencyRates WHERE CurrencyID=$CurrencyID");
  $Rate=0;
  if ($qcr) $Rate=$qcr->Top->Rate;
  $Caption=$qc->Top->Caption;
  $Name=$qc->Top->Name;

  print "<h1>$Caption</h1>";
  $_ENV->OpenForm(array(
      Name=>"VForm",
      ShowCancel=>1,
      Action=>ActionURL("store.ICurrencyRates.Update.b"),
      Align=>"center"));
  $_ENV->PutFormField(array(Type=>"hidden",Name=>'CurrencyID',Value=>$CurrencyID));
  $_ENV->PutFormField(array(Type=>"float",Decimals=>8,Size=>20,MaxLength=>20,Caption=>"$Name rate",Name=>'Rate',Value=>$Rate));
  $_ENV->CloseForm();
  }

function Update($args)
  {
  extract(param_extract(array(
    CurrencyID=>'int',
    Rate=>'string',
    ),$args));

    DBReplace(array(
      Table=>'store_CurrencyRates',
      Values=>array(
        Rate=>str_to_float($Rate),
        DateSet=>time()
        ),
      Keys=>array(CurrencyID=>$CurrencyID)));
  return array(ModalResult=>true);
  }

function Browse($args)
  {
  extract(param_extract(array(
    PageNo=>'int',
    ),$args));

  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $_LANGUAGE;

  $qc=DBQuery ("SELECT * FROM store_Currencies WHERE LangID='$_LANGUAGE'","CurrencyID");
  $qcr=DBQuery ("SELECT * FROM store_CurrencyRates ORDER BY CurrencyID","CurrencyID");
  $qtab=new TRecordSet();
  if ($qc)
    {
    foreach ($qc->Rows as $CurrencyID=>$row)
      {
      $Caption=$row->Caption;
      if (!$Caption) $Caption=$Name;
      $newrow=new stdclass;
      $newrow->Caption=$Caption;
      $r=$qcr->Rows[$CurrencyID]->Rate;
      if (!$r) $r="--";
      $newrow->Rate=$r;
      $newrow->DateSet=$qcr->Rows[$CurrencyID]->DateSet;
      $qtab->Rows[$CurrencyID]=$newrow;
      }
    }
  else
    {
    print "No currencies defined";
    }

  $qc=DBQuery("SELECT * FROM store_Currencies WHERE LangID='$_LANGUAGE' ORDER BY CurrencyID","CurrencyID");
  $_ENV->PrintTable($qtab,array(
    Fields=>array(
      Caption=>$_['CURRENCY_CAPTION'],
      DateSet=>"Time",
      Rate=>"Rate"),
    ButtonEdit=>array(ModalWindowAction=>"store.ICurrencyRates.Edit.b",KeyName=>"CurrencyID",Width=>700,Height=>350),
    TableStyle=>1,
    FieldTypes=>array(
      DateSet=>'time',
      Rate=>'float:8'
      ),

    PutKeyFieldsList=>true,
    Width=>'600',
    ThisObject=>&$this));
  }
}

# Виктор Петрович
# 206-04-61
# 299-02-11
# 350р/кв.м.
# от 18 кв.м.

?>
