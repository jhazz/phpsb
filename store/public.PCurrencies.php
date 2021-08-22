<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }

class store_PCurrencies
  {
  var $BC,$VC, $Values;

  function store_PCurrencies()
    {
    $_=&$GLOBALS[_STRINGS][store];
    $this->Title=$_[PINTERFACE_CURRENCIES];
    $this->CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";

    }

  function Init()
    {
    $this->BC=false;
    $this->BC->ConversionName="USD";
    $this->BC->Prefix="$";
    $this->BC->Suffix="";
    $this->BC->Decimals=2;
    $this->BC->BCdivVCrate=1;
    $this->BC->DecPoint=".";
    $this->BC->ThousandsSep=",";
    $this->VC=$this->BC;

    $qcurr=DBQuery ("SELECT * FROM store_Currencies ORDER BY CurrencyID","CurrencyID");

    if ($qcurr)
      {
      $this->Values=&$qcurr->Rows;
      foreach ($this->Values as $CurrencyID=>$row)
        {
        if (!$row->ThousandsSep) {$this->Values[$CurrencyID]->ThousandsSep=" ";}
        if ($row->IsBase)  $this->BC=$row;
        if ($row->IsValue) $this->VC=$row;
        }
      }
    }

  function Format($value,$currency)
    {
    $currency=$this->Values[$currency];
    return $currency->Prefix.str_replace (' ','&nbsp;',number_format($value,$currency->Decimals,$currency->DecPoint,$currency->ThousandsSep)).$currency->Suffix;
    }
  function BCFormat($value)
    {
    return $this->BC->Prefix.str_replace (' ','&nbsp;',number_format($value,$this->BC->Decimals,$this->BC->DecPoint,$this->BC->ThousandsSep)).$this->BC->Suffix;
    }
  function VCFormat($value)
    {
    return $this->VC->Prefix.str_replace (' ','&nbsp;',number_format($value,$this->VC->Decimals,$this->VC->DecPoint,$this->VC->ThousandsSep)).$this->VC->Suffix;
    }

  }


?>
