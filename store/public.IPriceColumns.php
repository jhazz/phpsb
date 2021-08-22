<?
class store_IPriceColumns
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Store";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function store_IPriceColumns()
  {
  $_=&$GLOBALS[_STRINGS][store];
  $this->Title="Price columns";
  }

function Add($args)
  {
  $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrder FROM store_PriceColumns");
  if ($q) $MaxOrderNo=$q->Top->MaxOrder;
  $MaxOrderNo=intval($MaxOrderNo)+10;
  $MaxOrderNo=ceil($MaxOrderNo/10)*10;

  $ColumnID=DBGetID("store.PriceColumn");
  DBExec ("INSERT INTO store_PriceColumns (ColumnID,OrderNo,Caption) VALUES ($ColumnID,$MaxOrderNo,'New column')");
  return array(ModalResult=>true);
  }

function Update($args)
  {
  extract(param_extract(array(
    Caption       =>'array:string',
    AutoCalcBase  =>'array:string',
    AutoCalcType  =>'array:string',
    AutoCalcAmount=>'array:float',
    CurrencyID    =>'array:int',
    UserGroupID   =>'array:int',
    OrderNo       =>'array:int',
    Editable      =>'array:int',
    VisitorType   =>'array:string',
    Keys=>'string',
    action=>'string',
    check=>'int_checkboxes',
    ),$args));

  if ($action=='delete')
    {
    if ($check)
      {
      $ids=implode (",",array_keys($check));
      $s="DELETE FROM store_PriceColumns WHERE ColumnID IN ($ids)";
      DBExec ($s);
      $s="DELETE FROM store_Prices WHERE ColumnID IN ($ids)";
      DBExec ($s);
      }
    }
  else
    {
    if ($Keys)
      {
      $Keys=explode (",",$Keys);
      foreach ($Keys as $ColumnID)
        {
        $ColumnID=intval($ColumnID);
        $s="UPDATE store_PriceColumns SET
         Caption='".$Caption[$ColumnID]."',
         AutoCalcBase=".$AutoCalcBase[$ColumnID].",
         VisitorType='".$VisitorType[$ColumnID]."',
         AutoCalcAmount=".$AutoCalcAmount[$ColumnID].",
         CurrencyID=".$CurrencyID[$ColumnID].",
         UserGroupID=".$UserGroupID[$ColumnID].",
         OrderNo=".$OrderNo[$ColumnID].",
         Editable=".intval($Editable[$ColumnID])."
         WHERE ColumnID=$ColumnID";
        DBExec ($s);
        }
      }
    }
  return array(ModalResult=>true);
  }

function Browse($args)
  {
  extract(param_extract(array(
    PageNo=>'int',
    ),$args));

  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  global $_LANGUAGE;

  $qcurr=DBQuery("SELECT * FROM store_Currencies WHERE LangID='$_LANGUAGE'","CurrencyID");
  $qug  =DBQuery("SELECT * FROM um_UserGroups","GroupID");
  $qpc  =DBQuery("SELECT * FROM store_PriceColumns ORDER BY OrderNo","ColumnID");

  $_ENV->PrintTable($qpc,array(
    ModalWindowURL=>ActionURL("store.IPriceColumns.Update.b"),
    Fields=>array(
      Caption=>"Column name",
      OrderNo=>"Sort order",
      CurrencyID=>"Currency",
      UserGroupID=>"User group allowed to view",
      VisitorType=>"Visitor type that can see column",
      AutoCalcBase=>"Depend on",
      AutoCalcAmount=>"Amount",
      Editable=>"Editable",
      ),
    HiddenFields=>array(LangID=>$FilterLangID),
    FieldTypes=>array(
      Caption=>"inputstring:20",
      CurrencyID  =>array(Type=>'droplist',Recordset=>&$qcurr,CaptionField=>"Caption"),
      UserGroupID =>array(Type=>'droplist',Recordset=>&$qug,CaptionField=>"Caption",NullCaption=>"All users"),
      AutoCalcBase=>array(Type=>'droplist',Recordset=>&$qpc,CaptionField=>"Caption",NullCaption=>"No dependecies"),
      AutoCalcAmount=>"inputfloat:4",
      VisitorType=>"inputstring:10",
      OrderNo=>"inputint",
      Editable=>"checkbox"
      ),
    TableStyle=>1,
    PutKeyFieldsList=>true,
    Width=>'100%',
    ShowCheckers=>true,
    ShowDelete=>true,
    ButtonAdd=>array(ModalWindowURL=>ActionURL("store.IPriceColumns.Add.b")),
    ShowOk=>true,
    ThisObject=>&$this));
  }
}


?>
