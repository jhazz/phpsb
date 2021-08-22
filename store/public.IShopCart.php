<?php
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }

class store_IShopCart
{
  var $EditMode,
    $Request,
    $RequestState,
    $RequestID,
    $CaptionSetTotal,
    $qRequestStates,
    $qRequestDetails,
    $qProducts,
    $qRequestSets;

  function store_IShopCart()
    {
    global $cfg;
    $_=&$GLOBALS[_STRINGS][store];


    $this->Title=$_[ISHOPCART];
    $this->About=$_[ISHOPCART_ABOUT];
    $this->CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";
    }


  function OnUserLogin($args)
    {
    global $_USER;
    if ($_SESSION->RequestID)
      {
      DBExec ("UPDATE store_Requests SET UserID=$_USER->UserID WHERE RequestID=$_SESSION->RequestID");
      }
    }

  function OnSessionClose($args)
    {
    $_SESSION->RequestID=0;
    $_SESSION->RequestSetID=0;
    }

  function Load($args)
    {
    global $_USER,$cfg;
    $_=&$GLOBALS[_STRINGS][store];

    extract(param_extract(array(
      RequestID=>"int=0",
      RequestSetID=>"int",
      UpdatePrices=>'int=1',
      RecalculateTotals=>'int',
      action=>'string'
      ),$args));

    $qstates=DBQuery ("SELECT * FROM store_RequestStates","StateID");
    if (!$qstates)
      {
      return array(Error=>$_[ISHOPCART_NOSTATESDEFINED],Details=>"Table: 'store_RequestStates'",IntruderAlert=>0);
      }

    if (!$RequestID)
      {
      $RequestID=intval($_SESSION->RequestID);
      if (!$RequestID) {return; }
      }

    if ($action=='chooseset')
      {
      $_SESSION->RequestSetID=$this->RequestSetID=$RequestSetID;
      }
    else
      {
      if (!$RequestSetID)
        {
        $RequestSetID=intval($_SESSION->RequestSetID);
        }
      $this->RequestSetID=$RequestSetID;
      }

    $qreq=DBQuery ("SELECT * FROM store_Requests WHERE RequestID=$RequestID");
    if (!$qreq)
      {
      return array(Error=>$_[ISHOPCART_ERRORREQUESTID],Details=>$RequestID,IntruderAlert=>50);
      }
    if ($qreq->Top->SessionKey!=$_SESSION->SessionKey)
      {
      if ($_USER->UserID != $qreq->Top->UserID)
        {
        if (!$_USER->HasRole("backend:RequestView"))
          {
          return array(Error=>$_[ISHOPCART_ERRORACCESS],Details=>$RequestID,IntruderAlert=>50);
          }
        }
      }

    $Request=&$qreq->Top;
    $this->Request=&$qreq->Top;
    $this->RequestID=&$RequestID;

    $RequestState=$qstates->Rows[$qreq->Top->StateID];
    if (!$RequestState)
      {
      $RequestState=new object;
      $RequestState->Cancelable=1;
      $RequestState->Editable=1;
      $RequestState->PriceLocked=0;
      $RequestState->DoClose=0;
      $RequestState->Name='';
      }
    $this->RequestState=&$RequestState;
    $this->qRequestStates=&$qstates;

    $this->EditMode=0;
    if (($_USER->HasRole("backend:RequestEdit")) || $RequestState->Editable ) $this->EditMode=1;

    $this->qRequestSets=DBQuery ("SELECT * FROM store_RequestSets WHERE RequestID=$RequestID","RequestSetID");
    if (!$this->qRequestSets)
      {
      return array(Error=>"Internal error. No one RequestSet defined inside Request",Details=>"RequestID=$RequestID");
      }

    if ((!$this->RequestSetID) && ($this->qRequestSets) && ($action!='chooseset'))
      {
      $this->RequestSetID=$this->qRequestSets->Top->RequestSetID;
      }

    $s="";
    if ($this->qRequestSets) {$s=$this->qRequestSets->Rows[$this->RequestSetID]->Name; }
    if (!$s) {$s="New Empty Set"; }
    $this->CurrentRequestSetName=$s;

    $qcurr=DBQuery("SELECT * FROM store_Currencies WHERE (IsDefault=1) OR (CurrencyID=0) ORDER BY CurrencyID");
    $BCurrency=$qcurr->Rows[0];
    $VCurrency=$qcurr->Rows[1];

    $BCdivVCrate=$VCurrency->BCdivVCrate;
    $VCurrencyID=$VCurrency->CurrencyID;


    $qdet=DBQuery ("SELECT * FROM store_RequestDetails WHERE RequestID=$RequestID ORDER BY RowIndex","RowIndex");
    $this->qRequestDetails=&$qdet;
    $Prods=false;
    foreach ($qdet->Rows as $RowID=>$pdetail) $Prods[$pdetail->ProductID]=true;
    $ProdIDs=implode (",",array_keys($Prods));

    $qprod=DBQuery ("SELECT ProductID,Hidden,BCPrice,VCPrice,Weight,HotNewSale FROM store_Products WHERE ProductID IN ($ProdIDs)","ProductID");
    $this->qProducts=&$qprod;

    if (($RecalculateTotals) || (!$RequestState->PriceLocked))
      {
      $Request->FinalBCPrice=$Request->FinalVCPrice=$Request->FinalNewBCPrice=
         $Request->FinaNewVCPrice=$Request->NewWeight=$Request->ItemCount=0;
      foreach (array_keys($this->qRequestSets->Rows) as $aRequestSetID)
        {
        $reqset=&$this->qRequestSets->Rows[$aRequestSetID];
        $SetNewWeight=$SetBCTotal=$SetVCTotal=$SetNewBCTotal=$SetNewVCTotal=$ItemsInside=0;
        $SetCount=$reqset->SetCount;
        reset ($qdet->Rows);
        foreach (array_keys($qdet->Rows) as $RowIndex)
          {
          $detail=&$qdet->Rows[$RowIndex];
          if ($detail->RequestSetID!=$aRequestSetID) {continue;}
          $ic=$detail->ItemCount*$SetCount;
          $ItemsInside+=$ic;    # Temporary value simply for reporting
          $SetBCTotal+=$detail->BCPrice*$ic;
          $SetVCTotal+=$detail->VCPrice*$ic;
          $product=&$qprod->Rows[$detail->ProductID];

          if ($product->Hidden)
            {
            $detail->Canceled=1;
            }
          else
            {
            $detail->NewBCPrice=doubleval($product->BCPrice);
            $detail->NewVCPrice=doubleval($product->VCPrice);
            $detail->NewWeight=doubleval($product->Weight);

            $SetNewBCTotal+=$detail->NewBCPrice*$ic;
            $SetNewVCTotal+=$detail->NewVCPrice*$ic;
            $SetNewWeight+=$detail->NewWeight*$ic;
            }
          }
        $reqset->FinalBCPrice=$SetBCTotal;
        $reqset->FinalVCPrice=$SetVCTotal;
        $reqset->FinalNewBCPrice=$SetNewBCTotal;
        $reqset->FinalNewVCPrice=$SetNewVCTotal;
        $reqset->NewWeight=$SetNewWeight;
        $reqset->ItemsInside=$ItemsInside;
        $Request->FinalBCPrice+=$SetBCTotal;
        $Request->FinalVCPrice+=$SetVCTotal;
        $Request->FinalNewBCPrice+=$SetNewBCTotal;
        $Request->FinalNewVCPrice+=$SetNewVCTotal;
        $Request->ItemCount+=$ItemsInside;
        $Request->NewWeight+=$SetNewWeight;

#        $FinalBCPriceDelta+=$SetNewBCTotal-$SetBCTotal;
#        $FinalVCPriceDelta+=$SetNewVCTotal-$SetVCTotal;
        }

      if (!$RequestState->PriceLocked)
        {
        # For newly created requests and not locked we should make recalculation of new prices everytime
        $Request->FinalBCPrice=$Request->FinalNewBCPrice;
        $Request->FinalVCPrice=$Request->FinalNewVCPrice;
        }

      # Ok, Let's apply calculation if it needed
      if ((!$RequestState->PriceLocked) || ($UpdatePrices))
        {
        $now=time();
        $s="UPDATE store_Requests SET
            BCTotal=$Request->FinalBCPrice,
            VCTotal=$Request->FinalVCPrice,
            CurrencyID=$VCurrencyID,
            BCdivVCrate=$BCdivVCrate,
            Weight=$Request->NewWeight,
            ModifiedAt=$now
          WHERE RequestID=$Request->RequestID";
        print "<hr>$s";
        DBExec ($s);

        foreach(array_keys($qdet->Rows) as $RowIndex)
          {
          $detail=&$qdet->Rows[$RowIndex];
          if (!$detail->Canceled)
            {
            $s="UPDATE store_RequestDetails SET
              BCPrice=$detail->NewBCPrice,
              VCPrice=$detail->NewVCPrice,
              Canceled=$detail->Canceled,
              Weight=$detail->NewWeight
              WHERE RequestID=$Request->RequestID AND RowIndex=$RowIndex";
            print "<hr>$s";
            DBExec ($s);
            }
          }
        }
      }
    }



  function EditRequestSet($args)
    {
    $_=&$GLOBALS[_STRINGS][store];
    global $cfg;

    extract(param_extract(array(
      TableStyle=>"int",
      CaptionSetTotal=>"string=$_[TSHOPCART_CAPTION_SET_TOTAL]",
      TextEmpty=>"string=$_[TSHOPCART_TEXTEMPTY]",
      ),$args));

    $this->CaptionSetTotal=&$CaptionSetTotal;
    if (!$this->RequestID)
      {
      print $TextEmpty;
      return;
      }

    global $ICurrency;
    $ICurrency=&$_ENV->LoadInterface("store.PCurrencies");
    $ICurrency->Init();

    $Fields=array(Name=>$_['COLUMN_PRODUCTNAME']);
    $Fields[VCPrice]=$_[COLUMN_VCPRICE].", ".$ICurrency->VC->ConversionName;
    $Fields[BCPrice]=$_[COLUMN_BCPRICE].", ".$ICurrency->BC->ConversionName;
    $Fields[ItemCount]=$_[COLUMN_ITEMCOUNT];
    $Fields[TotalBC]=$_[COLUMN_TOTAL].", ".$ICurrency->BC->ConversionName;

    print "<table width='100%'><tr><td colspan='10'>";

    $args=array(
      URL=>ActionURL("store.IShopCart.DoAction.f"),
      Fields=>$Fields,
      OnRowFilter=>tab_Filter,
      OnBeforeStart=>tab_CallBeforeStart,
      OnLastRow=>tab_LastRow,
      ShowCheckers=>$this->EditMode,
      ShowDelete=>$this->EditMode,
      FieldHooks=>array(ItemCount=>tab_ItemCount,BCPrice=>tab_BCPrice,VCPrice=>tab_VCPrice,TotalBC=>tab_TotalBC),
      TableStyle=>$TableStyle,
      Width=>'100%',
      ColWidths=>$Control->ColumnWidths,
      BgColor_Hovered=>'#fff0f0',
      BgColor_Checked=>'#fff0e0',
      ThisObject=>&$this);
    if ($this->EditMode)
      {
      $args+=array(
        Buttons=>array(update=>$_[BUTTON_UPDATESHOPCAR]),
        );
      }

    $GLOBALS[_CORE]->PrintTable($this->qRequestDetails,$args);

    print "</td></tr>";
    if ($this->qRequestSets->Rows[$this->RequestSetID])
      {
      print "<tr bgcolor='#e0e0e0'><td align='right'>$_[COLUMN_SETCOUNT]: ".$this->qRequestSets->Rows[$this->RequestSetID]->SetCount."</td></tr>";
      }
    print "</table>";
    }


  function DisplayRequestSetLinks($args)
    {
    if (!$this->RequestID) {return;}
    print "<h4>Request set</h4>";
    $s="";
    foreach ($this->qRequestSets->Rows as $aRequestSetID=>$row)
      {
      $s2=$row->Name;
      if ($aRequestSetID!=$this->RequestSetID) $s2="<a href='javascript:rsetchooser.RequestSetID.value=$aRequestSetID; rsetchooser.submit();'>$s2</a>";
      $s.="<tr><td><input type='checkbox' name='selectedset[]' value='$aRequestSetID'></td><td>$s2</td><td>$row->FinalNewBCPrice</td><td>x $row->SetCount</td></tr>";
      }
    if (($this->RequestSetID) && ($this->EditMode))
      {
      $s.="<tr><td></td><td><a href='javascript:rsetchooser.RequestSetID.value=0; rsetchooser.submit();'>Clear</a></td></tr>";
      }
    if ($s) {$s="<table>$s</table>";}
    print "<form method='get' name='rsetchooser'>
      $s
      <input type='hidden' name='action' value='chooseset'>
      <input type='hidden' name='RequestSetID' value='0'></form>";
    }

  function DoAction($args)
    {
    extract(param_extract(array(
      DontShowReport=>"int",
      TableStyle=>"int",
      RequestID=>"int",
      RequestSetID=>"int",
      UpdatePrices=>'int=1',
      RecalculateTotals=>'int',
      ForwardURL=>'string',
      ),$args));
    global $_USER,$cfg;
    $_=&$GLOBALS[_STRINGS][store];


    var_dump($_POST);

    if ($_POST['action']=='update')
      {

      }

    exit;
    if ($ForwardURL)
      {
      Header("Location: $ForwardURL");
      }
    else
      {
      Header("Location: $_SERVER[HTTP_REFERER]");
      }


    }

    function tab_Filter(&$this,&$row)
      {
      return ($row->RequestSetID==$this->RequestSetID);
      }

    function tab_ItemCount(&$RowIndex,&$row,$FieldName,&$this)
      {
      if ($this->EditMode)
        {
        print "<input name='row[\"$RowIndex\"]' type='text' size='3' maxlength='5' value='$row->ItemCount'>";
        }
      else
        {
        print $row->ItemCount;
        }
      }
    function tab_TotalBC(&$RowIndex,&$row)
      {
      global $ICurrency;
      if ($row->Canceled)
        {
        print "---";
        return;
        }
      $s=$ICurrency->BCFormat($row->BCPrice*$row->ItemCount);
      if ($row->BCPrice != $row->NewBCPrice)
        {
        $s="<font color='#808080' style='text-decoration: line-through;'>$s</font><br><font color='red'>"
         .$ICurrency->BCFormat($row->NewBCPrice*$row->ItemCount)."</font>";
        }
      print $s;
      }
    function tab_BCPrice(&$RowIndex,&$row)
      {
      global $ICurrency;
      $s=$ICurrency->BCFormat($row->BCPrice);
      if ($row->Canceled)
        {
        $s="<font color='#808080' style='text-decoration: line-through;'>$s</font>";
        print $s;
        return;
        }

      if ($row->BCPrice != $row->NewBCPrice)
        {
        $s="<font color='#808080' style='text-decoration: line-through;'>$s</font><br><font color='red'>"
         .$ICurrency->BCFormat($row->NewBCPrice)."</font>";
        }
      print "<div align='right'>$s</div>";
      }

    function tab_VCPrice(&$RowIndex,&$row)
      {
      global $ICurrency;
      $s=$ICurrency->VCFormat($row->VCPrice);
      if ($row->Canceled)
        {
        $s="<font color='#808080' style='text-decoration: line-through;'>$s</font>";
        print $s;
        return;
        }

      if ($row->VCPrice != $row->NewVCPrice)
        {
        $s="<font color='#808080' style='text-decoration: line-through;'>$s</font><br><font color='red'>"
         .$ICurrency->VCFormat($row->NewVCPrice)."</font>";
        }
      print "<div align='right'>$s</div>";
      }

    function tab_CallBeforeStart(&$this)
      {
      print "<h2>[".$this->RequestSetID."] ".$this->Request->Name."</h2>";

      if ($this->EditMode)
        {
        print "<input type='hidden' name='RequestID' value='$this->RequestID'>
        <input type='text' name='RequestSetName' value='$this->CurrentRequestSetName'>
        <input type='hidden' name='RequestSetID' value='$this->RequestSetID'>
        ";
        }
      else
        {
        print "<p>$this->CurrentRequestSetName</p>";
        }
      }
    function tab_LastRow(&$this,$tag,$cssclass)
      {
      global $ICurrency;
      $reqset=&$this->qRequestSets->Rows[$this->RequestSetID];
      $s=$ICurrency->BCFormat($reqset->FinalBCPrice);
      if ($reqset->FinalBCPrice != $reqset->FinalNewBCPrice)
        {
        $s="<font color='#808080' style='text-decoration: line-through;'>$s</font><br><font color='red'>"
         .$ICurrency->BCFormat($reqset->FinalNewBCPrice)."</font>";
        }

      print "<tr><td></td><$tag $cssclass colspan='4'>$this->CaptionSetTotal</$tag><$tag $cssclass>$s</$tag></tr>";
      }


}
?>
