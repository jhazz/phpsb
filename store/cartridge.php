<?
class store
{
function store()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $this->Title=$_['STORE_CARTIDGE_TITLE'];
  $this->Roles=array(
    PriceUploader=>$_['ROLE_PRICE_UPLOADER'],
    StoreDescriptioner=>$_['ROLE_DESCRIPTIONER'],
    RequestView=>$_['ROLE_REQUESTVIEW'],
    RequestEdit=>$_['ROLE_REQUESTEDIT'],
    );
  }

function Controls()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  return array(
#    TPriceAttrSelector=>array(Caption=>$_['TPRICEATTRSELECTOR_CAPTION'],Description=>$_['TPRICEATTRSELECTOR_DESCRIPTION'],Icon=>''),
#    TPriceList=>array(Caption=>$_['TPRICELIST_CAPTION'],Description=>$_['TPRICELIST_DESCRIPTION'],Icon=>''),
#    TProduct=>array(Caption=>$_['TPRODUCT_CAPTION'],Description=>$_['TPRODUCT_DESCRIPTION'],Icon=>''),
    TCatalogPage=>array(Caption=>$_['TPRODUCTSPAGE_CAPTION'],Description=>$_['TPRODUCTSPAGE_DESCRIPTION'],Icon=>''),
    TShopCart=>array(Caption=>$_['TSHOPCART_CAPTION'],Description=>$_['TSHOPCART_DESCRIPTION'],Icon=>''),
    TCatalogIndex=>array(Caption=>$_['TCATALOGINDEX_CAPTION'],Description=>$_['TCATALOGINDEX_DESCRIPTION'],Icon=>''),
    );
  }

function Menu()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  return array (
    array
      (
      PutToCategory=>"",
      CreateCategory=>"store",
      Caption=>$_['STORE_CARTIDGE_TITLE'],
      Items=>array(
        array(Caption=>$_['CAPTION_PRODUCTSCATALOGUE'],Call=>"store.IProdGroup.Browse.n"),
        array(Caption=>$_['STORE_PRODUCT_OPTIONS'],Call=>"store.IProdOptions.Browse.bm"),
        array(Caption=>$_['EDIT_CURRENCIES'],Call=>"store.ICurrencies.Browse.bm"),
        array(Caption=>$_['EDIT_PRICECOLUMNS'],Call=>"store.IPriceColumns.Browse.bm"),
        array(Caption=>$_['EDIT_CURRENCYRATES'],Call=>"store.ICurrencyRates.Browse.bm"),
        )
      ),
    );
  }

function Settings()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  return array
    (
    HidePriceFromNonpermitted=>array(Caption=>"Hide price from ordinal users or visitors who has not permitted to view price column",Type=>'boolean',DefaultValue=>'0'),
    ShowPrice=>array(Caption=>"Show price",Type=>'boolean',DefaultValue=>'1'),
    MeasurementSystemSI=>array(Caption=>$_['USE_MEASUREMENT_SYSTEM_SI'],Type=>'boolean',DefaultValue=>'0'),
    AskPackageWS=>array(Caption=>$_['SETTINGS_ASK_PACKAGEWS'],Type=>'boolean',DefaultValue=>'0'),
    ProductGroupsContext=>array(Caption=>$_['SETTINGS_GROUPCONTEXT'],Type=>'syscontext',DefaultValue=>"store_groups"),
    ProductInfoContext=>array(Caption=>$_['SETTINGS_PRODUCTCONTEXT'],Type=>'syscontext',DefaultValue=>"store_product"),
    ProductOptionGroupsContext=>array(Caption=>$_['SETTINGS_OPTGRPCONTEXT'],Type=>'syscontext',DefaultValue=>"store_optgrp"),
    );
  }

function ObjectClasses()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  return array
    (
    "store.ProdGroup"=>array(Caption=>"Store product group",UseSettingsContext=>"ProductGroupsContext",Interface=>"store.IProdGroup"),
    "store.Product"=>array(Caption=>"Store product",UseSettingsContext=>"ProductInfoContext",Interface=>"store.IProduct"),
    );
  }

}


?>
