<?php
class store_TShopCart
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  $this->About=$_[PRICELIST];
  $this->Propdefs=array(
    TableStyle=>array(Type=>"String",Caption=>$_[TPRICELIST_P_TABLESTYLE],GetValueFrom=>"jsb.IThemeReader.SelectTableStyle",Required=>true),
    TextEmptyCart=>array(Type=>"Caption",Caption=>$_[TSHOPCART_TEXTEMPTY],DefaultValue=>$_[TSHOPCART_TEXTEMPTY]),
    );
  $this->Datadefs=array(
    RequestSets=>array(DataType=>"store.RequestSets",Caption=>$_[TSHOPCART_REQUESTSETS])
    );
  }

function Init (&$Control)
  {
  global $cfg;
  global $_USER;
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  if (!$Control->Properties['TableStyle'])
    {
    return (array(Warning=>"Property TableStyle not declared"));
    }

  $this->IShopCart=&$_ENV->LoadInterface("store.IShopCart");
  $this->IShopCart->Load($_GET);


/*
  if ($_SESSION->RequestID)
    {
    $qreq=DBQuery ("SELECT * FROM store_Requests WHERE RequestID=$RequestID");
    if ($qreq)
      {
      if (($qreq->Top->SessionKey!=$_SESSION->SessionKey) && ($qreq->Top->UserID!=$_USER->UserID))
        {
        $_SESSION->RequestID=0;
        $_SESSION->RequestSetID=0;
        $qreq=false;
        }
      }
    $qsets=DBQuery("SELECT * FROM store_RequestSets WHERE RequestID=$RequestID");
    $Control->Data[Request]=&$qreq;
    $Control->Data[RequestSets]=&$qsets;
    }
  */
  }

function Render(&$Control)
  {

  $this->IShopCart->EditRequestSet($Control->Properties);
  $this->IShopCart->DisplayRequestSetLinks($Control->Properties);
  }


}
?>
