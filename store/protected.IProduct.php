<?
class store_IProduct {
var $CopyrightText="(c)2005 JhAZZ Site Builder. Web store";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(PriceUploader=>"Open,UpdatePage,UpdateControl,EditPage,EditBackendMenu,ExplorerTree,GetOptionValues");

var $SKUProductID;

function store_IProduct()
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $this->Title=$_['STORE_CARTIDGE_TITLE'];
  }

function Browse($args)
  {
  global $cfg;
  extract(param_extract(array(
    ProductGroupID=>'int',
    ),$args));
  print "<frameset cols='*'>
  <frame name='bigframe' width='100%' height='500' src='".ActionURL("jsb.ISiteExplorer.Open.n",
    array(Path=>"jsb/".$cfg['Settings']['store']['ProductGroupsContext']."/",
    ContextLocked=>1,NoLayouts=>1,
    TargetURL=>ActionURL("store.IProduct.EditGroup.b"),
    UseQueryMethod=>1,
    ))."'/></frameset>";
  }

function DoAction ($args)
  {
  global $cfg;
  extract(param_extract(array(
    action=>'string',
    check=>'int_checkboxes',
    subaction=>'string',
    GroupID=>'int',
    orderno=>'array',
    BaseProductID=>'int',
    ),$args));

  if ($action=="delete")
    {
    $this->DeleteProducts(implode (",",array_keys($check)));
    }

  if (($action=='ok')&&(substr($subaction,0,7)=='moveto_'))
    {
    $TargetGroupID=intval(substr($subaction,7));
    $ids=implode (",",array_keys($check));
    if ($ids && $TargetGroupID)
      {
      DBExec ("UPDATE store_Products SET GroupID=$TargetGroupID WHERE ProductID IN ($ids)");
      }

    }

  if (($orderno) && ($action=='ok'))
    {
    foreach($orderno as $k=>$v)
      {
      DBExec ("UPDATE store_Products SET OrderNo=$v WHERE ProductID=$k");
      }
    }

  if ($BaseProductID) {
    return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",
      array(EditProductID=>$BaseProductID,PageTab=>'subproducts')
      ));
    }
  return array(ModalResult=>true);
  }

function DeleteProducts ($DeleteList)
  {
  if (!$DeleteList) {return;}

  $Products=explode (",",$DeleteList);

  $q=DBQuery ("SELECT ProductID FROM store_Products WHERE BaseProductID IN ($DeleteList)","ProductID");
  if ($q)
    {
    $x=array_keys($q->Rows);
    $Products=array_merge($Products,$x);
    }
  $imgintf =&$_ENV->LoadInterface("img.IImage");
  $textintf=&$_ENV->LoadInterface("stdctrls.IRichtext");

  foreach ($Products as $ProductID)
    {
    $textintf->Remove_BoundToObject(array(BindTo=>"store.Product/desc/$ProductID"));
    $imgintf->Remove_BoundToObject(array(BindTo=>"store.Product/image/$ProductID"));
    $imgintf->Remove_BoundToObject(array(BindTo=>"store.Product/album/$ProductID"));
    }
  DBExec("DELETE FROM store_Products WHERE ProductID IN ($DeleteList)");
  DBExec("DELETE FROM store_ProdOptions WHERE ProductID IN ($DeleteList)");
  }

function tab_OrderNo(&$id,&$row,$fname)
  {
  print "<input type='text' name='orderno[$id]' value='$row->OrderNo' maxlength=4' size='4' class='inputarea'>";
  }
function tab_BCPrice (&$ProductID,&$row,$fname)
  {
  global $ICurrency;
  $v=$row->$fname;
  print $ICurrency->BCFormat($v);
  }
function tab_Image(&$ProductID,&$row,$fname,$args) {
 	global $cfg;
  if ($this->qimgs) {
  	$img=&$this->qimgs->Rows["store.Product/image/$ProductID"];
    if ($img)
      {
      $TnNames=$_ENV->Unserialize($img->Filenames);
      $TnName=$TnNames[1];
      $Filepath=$cfg['FilesPath'].'/img/store.Product/image/'.$TnName;
      $Fileurl=$cfg['FilesURL']  .'/img/store.Product/image/'.$TnName;
      if (is_file($Filepath)) {
        $size=@getimagesize($Filepath);
        if ($size) $tn="<img hspace='5' border='0' alt='$Caption' $size[3] src='$Fileurl'>";
				print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$ProductID))."\",reloadOnOk:1})'>$tn</a>";
        }
  	}
  }
}
  
function tab_ProductName(&$ProductID,&$row,$fname,$args)
  {
  
  $t=langstr_get($row->Teaser);
  $s=strlen($t);
  if ($s>200) $t=substr($t,0,200)."(...)";
  print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$ProductID))."\",reloadOnOk:1})'>".langstr_get($row->$fname)."</a><br>$t";

  }
function tab_SubName (&$ProductID,&$row,$fname,$args)
  {
  if ($ProductID != $args[EditProductID])
    {
    print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("store.IProduct.EditProduct.b",
      array(EditProductID=>$ProductID,PageTab=>'options'))."\",reloadOnOk:1});'>".$row->$fname."</a>";
    }
  else print "<i>".$row->$fname."</i>";
  }

function tab_Options (&$ProductID,&$row,$fname)
  {
  if ($this->QOptions)
    {
    $options=$this->QOptions->Rows[$ProductID];
    if (!$options) return;
    $s="";
    foreach($options as $OptionID=>$row)
      {
      if ($s) $s.="; ";
      $s.=langstr_get($row->OptionName).":<b>".langstr_get($row->ValueName)."</b>";
      }
    print $s;
    }
  }
function tab_HotNewSale (&$ProductID,&$row,$fname)
  {
  $_=&$GLOBALS['_STRINGS']['store'];
  $priceflags=explode (",",$_['PRODUCT_HOTNEWSALE']);
  $priceflag=$row->$fname;
  print $priceflags[$priceflag];
  }

function UpdateGroup($args)
  {
  global $cfg;
  extract(param_extract(array(
    GroupID=>'int',
    GroupCaption=>'string',
    GroupTitle=>'string',
    ),$args));

  if ($GroupCaption && $GroupID) {
    DBExec ("UPDATE jsb_Pages
     SET Caption='$GroupCaption', Title='$GroupTitle'
    WHERE JSBPageID=$GroupID AND SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."'");
    }
  return array(ModalResult=>true,);
  }

function EditGroup($args) {
  global $cfg;
  global $ICurrency;
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];

  extract(param_extract(array(
    JSBPageID=>'int',
    GroupID=>'int',
    ),$args));
  if ($JSBPageID) {$GroupID=$JSBPageID;}



  $qg=DBQuery ("SELECT Caption,Title,MetaData FROM jsb_Pages WHERE JSBPageID=$GroupID AND SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."'");
  if (!$qg) return;
  $ICurrency=&$_ENV->LoadInterface("store.PCurrencies");
  $ICurrency->Init();

#  print "<table width='100%' border='1'><tr valign='top'><td>";
  $imgintf=&$_ENV->LoadInterface("img.IImage");
  print "<table width='100%' border='0' cellpadding='10'><tr valign='top'><td>";
  $_ENV->OpenForm(array(Name=>"form1",
    Modal=>1,
    HideSubmit=>1,
    Action=>ActionURL("store.IProduct.UpdateGroup.b",array(GroupID=>$GroupID)),
    Style=>"clear"));
  print "$_[PRODUCTS_GROUPNAME]:<br/>";
  $_ENV->PutFormField(array(Type=>'langstring', Style=>'clear',Name=>"GroupCaption",Value=>$qg->Top->Caption,Required=>1,MaxLength=>200,Size=>40));
  print "<br/>$_[PRODUCTS_GROUPTITLE]:<br/>";
  $_ENV->PutFormField(array(Type=>'langstring', Style=>'clear',Name=>"GroupTitle",Value=>$qg->Top->Title,Required=>1,MaxLength=>200,Size=>40));
  $_ENV->PutButton(array(Caption=>$__['CAPTION_SAVE'],Action=>'submit',Name=>'submitbtn'));
  $_ENV->CloseForm();

#  <td><input type='text' name='GroupName' size='30' class='inputarea' value='".$qg->Top->Caption."'>";
  print "</td><td align='right'>";
  $imgintf->View(array(BindTo=>"store.ProdGroup/image/$GroupID",TnFormatNo=>1,ShowCaption=>0,EditMode=>1));
  print "</td></tr></table>";

  $qgs=DBQuery ("SELECT JSBPageID,Caption,Title
    FROM jsb_Pages
    WHERE State=1 AND SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."'","JSBPageID");

  $qprod=DBQuery ("SELECT ProductID,SKU,Name,OrderNo,HotNewSale,Hidden,Teaser
    FROM store_Products WHERE BaseProductID=0 AND GroupID=$GroupID ORDER BY OrderNo","ProductID");
  if ($qprod) {
  	$s="";
  	foreach ($qprod->Rows as $ProductID=>$row) {
  		$s.=($s?",":"")."'store.Product/image/$ProductID'";
  	}
  	$this->qimgs=DBQuery("SELECT BindTo,Filenames FROM img_Documents WHERE BindTo IN ($s)","BindTo");
  }
#store.Product/image/
  
  $SubactionList=array(save=>$_['CAPTION_RESORTPRODUCTS']);
  foreach($qgs->Rows as $JSBPageID=>$row)
    {
    $SubactionList["moveto_$JSBPageID"]=$_['MOVE_TO']." '".langstr_get($row->Caption)."'";
    }
  if ($qprod) {
    $BCName=$ICurrency->BC->ConversionName;
    $args=array(
      ModalWindowURL=>ActionURL("store.IProduct.DoAction.b",array(GroupID=>$GroupID)),
      Fields=>array(
        Image=>'Image',
      	SKU=>$_['PRODUCT_SKU'],
        Name=>$_['PRODUCT_NAME'],
        HotNewSale=>"!",
        OrderNo=>$_['ORDERNO']),
      FieldHooks=>array(Image=>tab_Image,Name=>tab_ProductName,OrderNo=>tab_OrderNo,HotNewSale=>tab_HotNewSale,BCPrice=>tab_BCPrice),
      ShowCheckers=>1,
      ShowDelete=>1,
      ShowOk=>1,
      TableStyle=>1,
      Width=>'100%',
      BgColor_Hovered=>'#fff0f0',
      BgColor_Checked=>'#fff0e0',
      SubactionList=>$SubactionList,
      ColWidths=>array(SKU=>'15%',OrderNo=>'10%',HotNewSale=>'1%'),
      ColAligns=>array(Image=>'center',OrderNo=>'center',HotNewSale=>'center',BCPrice=>'right'),
      ThisObject=>&$this);
    $_ENV->PrintTable($qprod,$args);
  }

  print "<form method='post' onSubmit='this.target=W.openModal({reloadOnOk:1});' action='".ActionURL("store.IProduct.AddProduct.b",array(GroupID=>$GroupID))."'>
    <table><tr><td align='right'>$_[PRODUCTS_ADDNEW]:</td><td><input type='text' size='30' name='NewProductName' class='inputarea'>
    $_[PRODUCT_SKU]:<input type='text' maxlength='50' size='8' name='NewProductSKU' class='inputarea'>"
    .$_ENV->PutButton(array(ToString=>1,Action=>'submit', Kind=>'add'))."</td></tr></form></table>
    </td></tr></table>";


}

function AddProduct ($args)
  {
  extract(param_extract(array(
    GroupID=>'int',
    NewProductName=>'string',
    NewProductSKU=>'string'
    ),$args));
  if (!$NewProductName) {return array(Message=>"Please enter product name");}
  $ProductID=DBGetID("store.Product");
  $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM store_Products WHERE GroupID=$GroupID");
  $OrderNo=($q)?intval($q->Top->MaxOrderNo)+3:3;
  DBExec ("INSERT INTO store_Products (ProductID,Name,GroupID,OrderNo,SKU) VALUES ($ProductID,'$NewProductName',$GroupID,$OrderNo,'$NewProductSKU')");
  return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$ProductID)));
#  return array(ModalResult=>$ProductID);
  }


function UpdateProduct($args)
  {
  extract(param_extract(array(
    EditProductID=>'int',
    ProductName=>'string',
    Hidden=>'int',
    HotNewSale=>'int',
    Teaser=>'string',
    ProductSKU=>'string',
    mweight=>'string', # measure_weight
    Weight=>'string',
    msize =>'string',
    Width =>'string',
    Height=>'string',
    Length=>'string',
    ),$args));

  global $cfg;
  if (($EditProductID>0)&&($ProductName))
    {
    $PackageWS="";
    if ($cfg['Settings']['store']['AskPackageWS'])
      {
      $Weight=str_to_float($Weight);
      $Width =str_to_float($Width);
      $Height=str_to_float($Height);
      $Length=str_to_float($Length);
      if ($cfg['Settings']['store']['MeasurementSystemSI'])
        {
        # base measurements: g, cm
        $mws=array('lbs'=>453.592,'oz'=>28.35,'kgs'=>1000,'g'=>1);
        $mss=array('ft'=>12*2.54,'in'=>2.54,'m'=>100,'cm'=>1);
        }
      else
        {
        # base measurements: oz, in(ch)
        $mws=array('lbs'=>16,'oz'=>1,'kgs'=>0.035274*1000,'g'=> 0.035274);
        $mss=array('ft'=>12,'in'=>1,'m'=>39.37,'cm'=>0.3937);
        }
      $Weight*=$mws[$mweight];
      $Width *=$mss[$msize];
      $Height*=$mss[$msize];
      $Length*=$mss[$msize];
      $PackageWS=",PackageWS='".$Weight.';'.$Width.';'.$Height.';'.$Length."' ";

      }

    if (DBExec("UPDATE store_Products
      SET Name='$ProductName', Hidden=$Hidden, HotNewSale=$HotNewSale,Teaser='$Teaser', SKU='$ProductSKU'
      $PackageWS WHERE ProductID=$EditProductID"))

      {
      return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$EditProductID,PageTab=>'descs')));
#        return array(ModalResult=>true);
      }
    }
  }

function UpdateProductPrices($args)
  {

  extract(param_extract(array(
    EditProductID=>'int',
    Prices=>'array:string',
    ),$args));
  global $cfg,$_LANGUAGE;
  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];

  $qc    =DBQuery ("SELECT * FROM store_PriceColumns ORDER BY OrderNo","ColumnID");
  $qcur  =DBQuery ("SELECT * FROM store_Currencies WHERE LangID='$_LANGUAGE'","CurrencyID");
  $qrates=DBQuery ("SELECT * FROM store_CurrencyRates","CurrencyID");

  foreach($Prices as $ColumnID=>$s)
    {
    $ColumnValues[$ColumnID]=str_to_float($s);
    }

  DBExec ("DELETE FROM store_Prices WHERE ProductID=$EditProductID");

  foreach($qc->Rows as $ColumnID=>$Column)
    {
    if ($Column->Editable)
      {
      $v=$ColumnValues[$ColumnID];
      DBExec ("INSERT INTO store_Prices (ProductID,ColumnID,Value) VALUES ($EditProductID,$ColumnID,$v)");
      }
    }
  return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$EditProductID,PageTab=>'subproducts')));
  }

function EditProduct ($args)
  {
  extract(param_extract(array(
    EditProductID=>'int',
    PageTab=>'string=main',
    new_options=>'int_checkboxes',
    ),$args));

  global $cfg,$_LANGUAGE,$_THEME;
  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if (!$EditProductID)
    {
    return array(Error=>"EditProductID not specified");
    }

  $qp=DBQuery ("SELECT * FROM store_Products WHERE ProductID=$EditProductID");
  if (!$qp)
    {
    return array(Error=>"Product not found in database",Details=>$EditProductID);
    }

  $ProductName=$qp->Top->Name;
  $ProductGroupID=$qp->Top->GroupID;
  $ProductSKU=$qp->Top->SKU;
  $BaseProductID=$qp->Top->BaseProductID;
  $SubProductCount=0;

#  $_ENV->InitWindows();
  $_ENV->SetWindowOptions(array(Title=>langstr_get($ProductName),Width=>750,Height=>500));

  if ($BaseProductID)
    {
    $qb=DBQuery ("SELECT SKU,Name FROM store_Products WHERE ProductID=$BaseProductID");
    if (!$qb) {return array(Error=>"Base product was removed!");}
    $BaseSKU=$qb->Top->SKU;
    $BaseProductName=$qb->Top->Name;
    $qsc=DBQuery ("SELECT COUNT(*) AS SubProductCount FROM store_Products WHERE BaseProductID=$BaseProductID");
    if ($qsc) {$SubProductCount=$qsc->Top->SubProductCount;}
    }
  else {
    $qsc=DBQuery ("SELECT COUNT(*) AS SubProductCount FROM store_Products WHERE BaseProductID=$EditProductID");
    if ($qsc) {$SubProductCount=$qsc->Top->SubProductCount;}
    }

  $qg=DBQuery ("SELECT Caption FROM jsb_Pages WHERE JSBPageID=$ProductGroupID AND SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."'");

  print "<center><table width='100%' border='0' cellpadding='5' cellspacing='0'><tr><td colspan='20'>".langstr_get($qg->Top->Caption)." / ";
  if ($BaseProductID)
    {
    print langstr_get($BaseProductName)." / ";
    }
  print "<b>".langstr_get($ProductName)."</b><br><br></td></tr>";

  $PageTabs=array(
    main=>$_[PRODUCT_MAINDATA],
    descs=>$_[PRODUCT_DESCRIPTIONS],
    options=>$_[PRODUCT_ATTRIBUTES],
    prices=>$_[PRODUCT_PRICES],
    subproducts=>$_[PRODUCT_SUBPRODUCTS],
    photos=>$_[PRODUCT_PHOTOALBUM]);

  $s="";
  foreach ($PageTabs as $p=>$linkcaption) {
    $color="bgcolor='#e0e0e0'";
    if (($BaseProductID)&&($p=='subproducts')) continue;
    if (($p=='subproducts')&&($SubProductCount)) {$linkcaption.="&nbsp;<b>($SubProductCount)</b>";}
    if ($p!=$PageTab) {
      $linkcaption="<a href='".ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$EditProductID,PageTab=>$p))."'>$linkcaption</a>";
      $color="bgcolor='#f8f8f8'";;
      }
    $s.="<td> </td><td align='right' $color>$linkcaption</td>";
  }
  print "<tr>$s</tr><tr><td colspan='20' bgcolor='#e0e0e0' align='center'>";

  switch($PageTab) {
    case 'main':
      print "<table cellspacing='10' width='100%'><tr valign='top'><td>";
      $imgintf=&$_ENV->LoadInterface("img.IImage");
      $imgintf->View(array(BindTo=>"store.Product/image/$EditProductID",TnFormatNo=>1,ShowCaption=>0,EditMode=>1));
      print "</td><td>";
      $_ENV->OpenForm(array(Name=>"Form1",Action=>ActionURL("store.IProduct.UpdateProduct.b"),Align=>"left"));
      print "<tr valign='top'><td>";
      $_ENV->PutFormField(array(Name=>"ProductName",Value=>$ProductName,Caption=>$_['PRODUCT_NAME'], Required=>1,Type=>"langstring",MaxLength=>100,Size=>60,Style=>'vertical'));
      print "<input type='hidden' name='EditProductID' value='$EditProductID'></td><td><b>$_[PRODUCT_SKU]</b><br><input
      type='text' name='ProductSKU' class='inputarea'  size='20' maxlength='50' value='$ProductSKU'></td></tr></table>
      <table width='100%' border=0 cellpadding='3'><tr>";
      $states=explode (",",$_['PRODUCT_HOTNEWSALE']);
      for ($i=0;$i<count($states);$i++)
        {
        $c=(intval($qp->Top->HotNewSale)==$i)?"checked":"";
        print "<td class='bgdown'><input type='radio' name='HotNewSale' value='$i' $c >$states[$i]</td>";
        }
      print "</tr></table>";

      if ($qp->Top->Hidden) {$c="checked";}

      $PackageWS=explode (";",$qp->Top->PackageWS);
      $Weight=float_to_str(floatval($PackageWS[0]),1);
      $Width =float_to_str(floatval($PackageWS[1]),1);
      $Height=float_to_str(floatval($PackageWS[2]),1);
      $Length=float_to_str(floatval($PackageWS[3]),1);
      print "<input type='checkbox' name='Hidden' value='1' $c>$_[PRODUCT_HIDDEN]
      <br/><br/>$_[PRODUCT_TEASER]<br/>";

      $_ENV->PutFormField(array(Type=>'langtext',
        Style=>'clear',Name=>"Teaser",Value=>$qp->Top->Teaser,Size=>'95', Rows=>5));
      if ($cfg['Settings']['store']['AskPackageWS'])
        {
        $_ENV->PutValueSet(array(ValueSetName=>'mweight', Values=>array(
          'lbs'=>$_['WEIGHT_LBS'],'oz'=>$_['WEIGHT_OZ'],'kgs'=>$_['WEIGHT_KGS'],'g'=>$_['WEIGHT_G'])));
        $_ENV->PutValueSet(array(ValueSetName=>'msize', Values=>array(
          'ft'=>$_['LENGTH_FT'],'in'=>$_['LENGTH_INCH'],'m'=>$_['LENGTH_M'],'cm'=>$_['LENGTH_CM'])));
        print "<table width='100%'><tr><td><b>$_[PACKAGE_WEIGHT]:</b></td><td colspan='5' class='bgdown'><table cellpadding=0 cellspacing=0><tr><td><input type='text' name='Weight' class='inputarea' value='$Weight' size='14'></td><td>";
        $_ENV->PutDropDown(array(
          ValueSetName=>'mweight',
          Value=>(($cfg['Settings']['store']['MeasurementSystemSI'])?'g':'oz'),
          Name=>"mweight"));
        print "</td></tr></table></td></tr>
        <tr><td><b>$_[PACKAGE_SIZE]:</b></td><td class='bgdown'>$_[WIDTH]:<input name='Width' type='text' class='inputarea' size='10' value='$Width'/></td>
        <td class='bgdown'>$_[HEIGHT]:<input name='Height' type='text' class='inputarea' size='10' value='$Height'/></td>
        <td class='bgdown'>$_[LENGTH]:<input name='Length' type='text' class='inputarea' size='10' value='$Length'/></td><td>";
        $_ENV->PutDropDown(array(
          ValueSetName=>'msize',
          Value=>(($cfg['Settings']['store']['MeasurementSystemSI'])?'cm':'in'),
          Name=>"msize"));
        print "</td></tr><tr><td></td><td class='notice' colspan='5'>$_[WIDTH_LENGTH_NOTICE]</td></tr></table>";
        }
      $_ENV->CloseForm();
      print "</td></tr></table>";
      break;

      case 'prices':
        ?>
        <script>
        var timeout;
        function RecalculateColumns()
          {
          var i,a,b,ColumnID,Editable,Base,Amount,v,BaseCurrency,Rate,s;
          s="";
          for (i in Columns)
            {
            a=Columns[i].split (":");
            ColumnID=Int(i);
            Editable=Int(a[0]);
            Base    =Int(a[1]);
            Amount  =parseFloat(a[2]);
            CurrencyID=Int(a[3]);
            Decimals=CurrencyDecimals[CurrencyID];
            Rate=1;
            if (Base)
              {
              b=Columns[Base].split (":");
              BaseCurrency=Int(b[3]);
              Rate=Rates[BaseCurrency]/Rates[CurrencyID];
              v=document.getElementById("Price_"+Base).value;
              v=str_to_float(v);
              }

            if (Editable)
              {
              if (Base)
                {
                if (document.getElementById("Auto_"+ColumnID).checked)
                  {
                  document.getElementById("Price_"+ColumnID).value=float_to_str(v*Amount*Rate,Decimals);
                  }
                }
              }
            else
              {
              if (Base)
                {
                v=float_to_str(v*Amount*Rate,Decimals);
                document.getElementById("Price_"+ColumnID).value=v;
                document.getElementById("PriceText_"+ColumnID).innerHTML=v;
                }
              }
            }
          window.status=s;

          }

        function ColChanged()
          {
          if (timeout) window.clearTimeout(timeout);
          timeout=window.setTimeout(RecalculateColumns,200);
          }

        function float_to_str(f,decimals)
          {
          if (!decimals) decimals=2;
          var d=Math.round(Math.exp(decimals*Math.log(10)));
          f=Math.round(f*d)/d;
          var s=String(f);
          var p=s.lastIndexOf('.');
          if (p==-1) p=s.length;
          var s2="",i=0,r=p-Math.floor(p/3)*3,c,l=s.length;
          while (i<l)
            {
            c=s.charAt(i);
            if (c=='.') {s2+=s.substring(i,l);break; }
            r--;
            if (r<0) {r=2; if (i>0) s2+=ThousandsSep;}
            s2+=c;
            i++;
            }
          var p=s2.lastIndexOf('.');
          var n=s2.length-p-1;
          if (p==-1) {p=s2.length; n=decimals; s2+=DecimalSep;} else {n=decimals-n;}
          while (n>0) {s2+="0";n--;}
          s2=s2.replace(/\./gi,DecimalSep);
          return s2;
          }

        function str_to_float(s)
          {
          var re=new RegExp("\\"+DecimalSep, "g");
          s=s.replace(re,".");
          var re=new RegExp("\\"+ThousandsSep, "g");
          s=s.replace(re,"");
          return parseFloat(s);
          }
        </script>
        <?
        $qcur=DBQuery ("SELECT * FROM store_Currencies WHERE LangID='$_LANGUAGE'","CurrencyID");
        $qrates=DBQuery ("SELECT * FROM store_CurrencyRates","CurrencyID");
        $VarRates=$VarDecimals="";
        foreach ($qcur->Rows as $CurrencyID=>$Currency)
          {
          $VarDecimals.=(($VarDecimals)?",":"")."$CurrencyID:$Currency->Decimals";
          }

        foreach ($qrates->Rows as $CurrencyID=>$CRates)
          {
          $VarRates.=(($VarRates)?",":"")."$CurrencyID:$CRates->Rate";
          }
        $qcols=DBQuery ("SELECT * FROM store_PriceColumns ORDER BY OrderNo","ColumnID");
        $qprices=DBQuery("SELECT * FROM store_Prices WHERE ProductID=$EditProductID","ColumnID");
        print "<br/><form
         method='post' action='".ActionURL("store.IProduct.UpdateProductPrices.f")."'>
         <input type='hidden' name='EditProductID' value='$EditProductID'>
         <input type='hidden' name='PageTab' value='prices'>
         <table border='0'>";
        $VarColumns="";
        foreach ($qcols->Rows as $ColumnID=>$Column)
          {
          $Currency=$qcur->Rows[$Column->CurrencyID];
          print "<tr valign='botton' class='bgup'><td align='right' class='small'><b>$Column->Caption:</b></td><td>$Currency->Prefix</td><td align='right'>";
          $VarColumns.=(($VarColumns)?",":"")."$ColumnID:'$Column->Editable:$Column->AutoCalcBase:$Column->AutoCalcAmount:$Column->CurrencyID'";
          if ($Column->Editable)
            {
            $PriceValue=0;
            if ($qprices)
              {
              $PriceValue=$qprices->Rows[$ColumnID]->Value;
              }
            $PriceValueStr=float_to_str($PriceValue,$Currency->Decimals);
#            $PriceValueStr=number_format(floatval($PriceValue),$Currency->Decimals,$__[DECIMAL_SEPARATOR],$__[THOUSAND_SEPARATOR]);
            print "<input onChange='document.getElementById(\"btnok\").disabled=false; ColChanged()' onKeyUp='document.getElementById(\"btnok\").disabled=false; ColChanged()' style='text-align:right' type='text' class='inputarea' size='14' maxlength='14' id='Price_$ColumnID' name='Prices[$ColumnID]' value='$PriceValueStr'/>";
            if ($Column->AutoCalcBase) print "<br><font color='red'>".$PriceValueStr."</font>";
            print "</td><td>$Currency->Suffix</td><td>";
            if ($Column->AutoCalcBase) print "<input onClick='ColChanged()' type='checkbox' id='Auto_$ColumnID' value='1' checked>Autocalc.";
            }
          else
            {
            print "<table><tr><td id='PriceText_$ColumnID' class='bgup'>----</td></tr></table>";
            print "</td><td>";
            print "$Currency->Suffix<input type='hidden' name='Prices[$ColumnID]'  id='Price_$ColumnID' value='$PriceValueStr'></td><td>";
            }
          print "</td>";
          print "<td><i>($Currency->Caption)</i></td>";
          print "</tr>";
          }
        print "</table><table width='100%'><tr><td align='right'>";
        $_ENV->PutButton(array(ID=>'btnok',Disabled=>1,Action=>'submit'));
        print "</form><script>var Columns={"."$VarColumns};";
        print "var ThousandsSep='$__[THOUSAND_SEPARATOR]';";
        print "var DecimalSep='$__[DECIMAL_SEPARATOR]'; ";
        if ($VarDecimals) print "var CurrencyDecimals={"."$VarDecimals}; ";
        if ($VarRates) print "var Rates={"."$VarRates}; ";
        print "ColChanged();</script></table>";
      break;


      case 'photos':
        if ($_ENV->IsCartridgeActive("img"))
          {
          $view_imgindex=ActionURL("img.IImgIndex.View.f",
          array(
            EditMode=>1,
            BindTo=>"store.Product/album/$EditProductID",
            ColumnCount=>5,
            Selectable=>1,
            ShowCaptions=>1,
            Insertable=>1,
            TnFormats=>$cfg['Settings']['store']['ProductAlbumTn'],
            TnFormat=>1,
            MaxImgSize=>$cfg['Settings']['store']['ProductAlbumMaxImg'],
            ),3);
          $w+=50; $h+=50;
          print "<iframe width='100%' name='prodfotos' height='300' src='$view_imgindex'></iframe>";
          }
      break;

      case 'descs':
        print "<iframe name='prddesc' src='".ActionURL("stdctrls.IRichtext.View.f",
        array(EditMode=>1,BindTo=>"store.Product/desc/$EditProductID")).
        "' width='100%' height='300'></iframe>";
      break;


      case 'options':
      print "<table width='100%' border='0'><tr><td align='center'>";
      ?><script>

    var Timer1=false,updateurl=false;
    function newOptionChanged()
      {
      var drop=P$.find("drop_newoptionvalue");
      drop.style.visibility='hidden';
      updateurl="<? print ActionURL("store.IProduct.GetOptionValues") ?>?Name="+dropFieldValue;
      if (Timer1) {window.clearTimeout(Timer1);Timer1=false;}
      Timer1=window.setTimeout(onTimer1,300);
      }
    function onTimer1()
      {
      var fr=P$.find("ifnewoptionvalues");
      fr.src=updateurl;
      }
    function setOptionValues(vs)
     {
     var o,v,drop,vfield;
     drop=P$.find("drop_newoptionvalue");
     vfield=P$.find("lfv_newoptionvalue");
     vfield.value="";

     var f1=P$.find("lfv_VFormnewoptionunit");
     var f2=P$.find("lfv_VFormnewoptioninfo");

     if (vs!=undefined)
       {
       ValueSet_newoptionvalue=vs;
       drop.style.visibility='visible';
       f1.style.visibility='hidden';
       f2.style.visibility='hidden';
       }
     else
       {
       drop.style.visibility='hidden';
       f1.style.visibility='visible';
       f2.style.visibility='visible';
       }
     }
    </script><?

      # used options in the product group (look at store_ProdOptions)
      $quo=DBQuery ("SELECT po.OptionID
        FROM store_ProdOptions po INNER JOIN store_Products p ON po.ProductID=p.ProductID
        WHERE p.GroupID=$ProductGroupID GROUP BY po.OptionID","OptionID");
      $qv=DBQuery ("SELECT OptionValueID,OptionID FROM store_ProdOptions WHERE ProductID=$EditProductID","OptionID");

      $_ENV->OpenForm(array(
        Name=>"VForm",
        ShowCancel=>0,
        Action=>ActionURL("store.IProduct.UpdateProductOptions.b"),
        Align=>"center"));
      $_ENV->PutFormField(array(Type=>'hidden',Name=>'gonext',Value=>'yes'));
      $_ENV->PutFormField(array(Type=>'hidden',Name=>'PageTab',Value=>$PageTab));
      $_ENV->PutFormField(array(Type=>'hidden',Name=>'EditProductID',Value=>$EditProductID));

      print "<table border=0><tr><td>$_[OPTION_NAME]</td><td>$_[OPTION_VALUE]</td><td>$_[OPTION_UNIT]</td><td>$_[OPTION_INFO]</td></tr>";
      if ($quo)
        {
        $uo_list="";
        if ($quo) {$uo_list=implode (",",array_keys($quo->Rows));}

        # information about these options
        $qo=DBQuery ("SELECT OptionID,Name,Unit,Info FROM store_Options WHERE OptionID IN ($uo_list) ORDER BY OrderNo","OptionID");

        # read values for used options
        $qvv=DBQuery ("SELECT OptionID,OptionValueID,Name
        FROM store_OptionValues WHERE OptionID IN ($uo_list) ORDER BY Name",array("OptionID","OptionValueID"));

        if ($qo) foreach ($qo->Rows as $OptionID=>$row)
          {
          $v='';
          if ($qvv)
            {
            $ov=$qvv->Rows[$OptionID];
            if ($qv && $ov) {
              $vid=$qv->Rows[$OptionID]->OptionValueID;
              if ($vid)
                {
                $v=langstr_get($ov[$vid]->Name);
                }
              }
            if ($ov)
              {
              $vls=false;
              foreach ($ov as $OptionValueID=>$vrow)
                {
                $vls[$OptionValueID]=langstr_get($vrow->Name);
                }
              $_ENV->PutValueSet(array(ValueSetName=>"op_$OptionID",Values=>$vls));
              }
            }
          print "<input type='hidden' name='OptionIDs[]' value='$OptionID'/><tr><td class='co'>"
            .langstr_get($row->Name)."</td><td class='ce'>";
          $_ENV->PutDropDown(array(Size=>45,Editable=>1,Name=>"opv_$OptionID",Value=>langstr_get($v),ValueSetName=>"op_$OptionID"));
          print "</td><td class='ce'>".langstr_get($row->Unit)."</td><td class='ce'>".langstr_get($row->Info)."</td></tr>";
          }
        }

      $alloptions=false;
      $qo=DBQuery ("SELECT OptionID,Name FROM store_Options ORDER BY Name","OptionID");
      if ($qo)
        {
        foreach ($qo->Rows as $OptionID=>$row)
          {
          if ($quo) if (isset($quo->Rows[$OptionID])) {continue;}
          $alloptions[$OptionID]=langstr_get($row->Name);
          }
      $_ENV->PutValueSet(array(ValueSetName=>"newoptionname",Values=>$alloptions));
      print "<tr><td>";
      $_ENV->PutDropDown(array(Size=>15,Editable=>1,Name=>"newoptionname",OnChange=>"newOptionChanged()",ValueSetName=>"newoptionname"));
      print "</td><td>";
      $_ENV->PutDropDown(array(Size=>45,Editable=>1,Name=>"newoptionvalue",ValueSetName=>"newoptionvalue"));
      print "</td><td>";
      $_ENV->PutFormField(array(Type=>"string",Style=>"clear",Name=>"newoptionunit",Size=>10));
      print "</td><td>";
      $_ENV->PutFormField(array(Type=>"string",Style=>"clear",Name=>"newoptioninfo",Size=>10));
      print "</td></tr>";
      print "<tr><td>";
      $_ENV->PutButton(array(Kind=>'add',OnClick=>"document.getElementById('lfv_VFormgonext').value='no'; document.getElementById('VForm').submit();"));
      print "</td></tr>";
      print "</table>";
      $_ENV->CloseForm();
      print "<iframe id='ifnewoptionvalues' style='display:none'/></iframe>";
      }
      print "</td></tr></table>";
      break;

    case 'subproducts':
      if (!$BaseProductID)
        {
        $BaseProductID=$EditProductID;
        $BaseProductName=$ProductName;
        $BaseSKU=$ProductSKU;
        }

      $qsp=DBQuery ("SELECT ProductID,SKU,Name FROM store_Products WHERE BaseProductID=$BaseProductID","ProductID");
      if ($qsp)
        {
        $this->QOptions=DBQuery ("SELECT po.ProductID, opv.OptionID, o.Name AS OptionName, opv.Name AS ValueName
FROM store_Options AS o
INNER JOIN ((store_OptionValues AS opv
INNER JOIN store_ProdOptions AS po ON opv.OptionValueID = po.OptionValueID)
INNER JOIN store_Products AS p ON po.ProductID = p.ProductID) ON o.OptionID = po.OptionID
WHERE p.BaseProductID=$BaseProductID",array("ProductID","OptionID"));
        $BCName=$ICurrency->BC->ConversionName;
        $args=array(
          URL=>ActionURL("store.IProduct.DoAction.b",array(GroupID=>$GroupID,BaseProductID=>$BaseProductID)),
          Fields=>array(SKU=>$_[PRODUCT_SKU],Name=>$_[PRODUCT_NAME],Options=>"OPTIONS"),
          ShowCheckers=>1,
          FieldHooks=>array(BCPrice=>tab_BCPrice,Name=>tab_SubName,Options=>tab_Options),
          FieldHookArgs=>array(EditProductID=>$EditProductID),
          ShowDelete=>1,
          TableStyle=>1,
          Width=>'100%',
          ColWidths=>array(SKU=>'10%'),
          BgColor_Hovered=>'#fff0f0',
          BgColor_Checked=>'#fff0e0',
          ColAligns=>array(BCPrice=>'right'),
          ThisObject=>&$this);
        $_ENV->PrintTable($qsp,$args);
        }
      print "<h2>$_[ADD_SUBPRODUCT]</h2>";
      print "$_[HINT_ADD_SUBPRODUCT]<table cellpadding='5'><form method='post' onSubmit='this.target=W.openModal();' action='"
      .ActionURL("store.IProduct.AddSubProduct.bm",array(BaseProductID=>$BaseProductID))."'>
      <tr><td bgcolor='#d8d8d8'>$_[PRODUCT_SKU]:<br>
      <i>$BaseSKU</i><br>
      <input type='text' maxlength='50' size='8' name='NewProductSKU' class='inputarea' value='+'>
      </td>
      <td bgcolor='#d8d8d8'>$_[PRODUCTS_ADDNEW]:<br>
      <i>".langstr_get($BaseProductName)."</i><br>
      <input type='text' name='NewProductName' class='inputarea' size='80' maxlength='250' value='+' onKeyUp='document.getElementById(\"btnadd\").disabled=false;'><br>
      </td></tr><tr><td></td><td>";
      $_ENV->PutButton(array(ID=>'btnadd',Kind=>'add',Action=>'submit',Disabled=>1));
      print "</td></tr></form></table>";

      $qo=DBQuery ("SELECT OptionID,Name FROM store_Options ORDER BY Name","OptionID");
      if ($qo) {
        $s="";
        foreach ($qo->Rows as $OptionID=>$row)
          {
          if ($quo) if (isset($quo->Rows[$OptionID])) {continue;}
          $s.="<option value='$OptionID'>$row->Name</option>";
          }
        if ($s) {
          $s="<br><select class='inputarea' size='5' id='new_options[]' name='new_options[]' multiple>$s</select><br>";
          }
        }
    break;
    } ## switch ##
  print "</td></tr></table><table width='100%'><tr><td align='right'>";
  $_ENV->PutButton(array(Action=>'ok',Kind=>'cancel',Caption=>$__['CAPTION_CLOSE']));
  print "</td></tr></table>";
  }


function AddNewOption($args)
  {
  extract(param_extract(array(
    EditProductID=>'int',
    new_options=>'int_checkboxes',
    newoptionname=>'string',
    newoptionvalue=>'string',
    newoptionunit=>'string',
    newoptioninfo=>'string'
    ),$args));

    if ($newoptionname && $newoptionvalue) {
      $OptionID=DBGetID("store.Option");
      $OptionValueID=DBGetID("store.OptionValue");
      $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM store_Options");
      $OrderNo=($q)?intval($q->Top->MaxOrderNo)+10:10;
      DBExec ("INSERT INTO store_Options (OptionID,Name,Unit,Info,OrderNo) VALUES ($OptionID,'$newoptionname','$newoptionunit','$newoptioninfo',$OrderNo)");
      DBExec ("INSERT INTO store_OptionValues (OptionID,OptionValueID,Name) VALUES ($OptionID,$OptionValueID,'$newoptionvalue')");
      DBExec ("INSERT INTO store_ProdOptions (ProductID,OptionID,OptionValueID) VALUES ($EditProductID,$OptionID,$OptionValueID)");
    }

  return array(ModalResult=>true);
  }


function AddSubProduct($args)
  {
  extract(param_extract(array(
    BaseProductID=>'int',
    NewProductName=>'string',
    NewProductSKU=>'string'
    ),$args));
  if (!$NewProductName) {return array(Message=>"Please enter product name");}
  $ProductID=DBGetID("store.Product");
  $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM store_Products");
  $OrderNo=($q)?intval($q->Top->MaxOrderNo)+10:10;
  $q=DBQuery ("SELECT GroupID FROM store_Products WHERE ProductID=$BaseProductID");
  if (!$q) {return array(Error=>"Product not found to set as Base product",Details=>$BaseProductID);}
  $GroupID=$q->Top->GroupID;
  DBExec ("INSERT INTO store_Products (ProductID,BaseProductID,Name,GroupID,OrderNo,SKU) VALUES ($ProductID,$BaseProductID,'$NewProductName',$GroupID,$OrderNo,'$NewProductSKU')");
  return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$ProductID,PageTab=>'options')));
  }


function UpdateProductOptions($args)
  {
   extract(param_extract(array(
    gonext=>'string',
    EditProductID=>'int',
    OptionIDs=>'array:int',
    EditProductID=>'int',
    newoptionname=>'string',
    newoptionvalue=>'string',
    newoptionunit=>'string',
    newoptioninfo=>'string'

    ),$args));

  $s="";
  foreach ($OptionIDs as $OptionID) {
    $Value=DBEscape($args["opv_$OptionID"]);
    if (!$Value) continue;
    $Value=DBEscape($Value);
    if ($s) $s.=" OR ";
    $s.="(OptionID=$OptionID AND Name='$Value')";
    }

  if ($s) {
    $qv=DBQuery ("SELECT OptionID,OptionValueID FROM store_OptionValues WHERE $s","OptionID");
    DBExec ("DELETE FROM store_ProdOptions WHERE ProductID=$EditProductID");
    }

  foreach ($OptionIDs as $OptionID)
    {
    $Value=DBEscape($args["opv_$OptionID"]);
    if (!$Value) continue;

    $OptionValueID=0;
    if ($qv)
      {
      $row=$qv->Rows[$OptionID];
      if ($row) $OptionValueID=$row->OptionValueID;
      }
    if (!$OptionValueID)
      {
      $OptionValueID=DBGetID("store.OptionValue");
      $s="INSERT INTO store_OptionValues (OptionID,OptionValueID,Name) VALUES ($OptionID,$OptionValueID,'$Value')";
      DBExec ($s);
      }
    $s="INSERT INTO store_ProdOptions (ProductID,OptionID,OptionValueID) VALUES ($EditProductID,$OptionID,$OptionValueID)";
    DBExec ($s);
    }

  if ($newoptionname && $newoptionvalue)
    {
    $OptionID=0;
    $OptionValueID=0;

    $q1=DBQuery ("SELECT OptionID,Name FROM store_Options WHERE Name LIKE '%�$newoptionname"."�%' OR Name='$newoptionname'","OptionID");
    if ($q1)
      {
      $OptionID=$q1->Top->OptionID;
      $q2=DBQuery ("SELECT OptionValueID,Name FROM store_OptionValues WHERE OptionID=$OptionID AND (Name LIKE '%�$$newoptionvalue"."�%' OR Name='$newoptionvalue')");
      if ($q2)
        {
        $OptionValueID=$q2->Top->OptionValueID;
        }
      }
    if (!$OptionID)
      {
      $OptionID=DBGetID("store.Option");
      $q=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM store_Options");
      $OrderNo=($q)?intval($q->Top->MaxOrderNo)+10:10;
      DBExec ("INSERT INTO store_Options (OptionID,Name,Unit,Info,OrderNo) VALUES ($OptionID,'$newoptionname','$newoptionunit','$newoptioninfo',$OrderNo)");
      }
    if (!$OptionValueID)
      {
      $OptionValueID=DBGetID("store.OptionValue");
      DBExec ("INSERT INTO store_OptionValues (OptionID,OptionValueID,Name) VALUES ($OptionID,$OptionValueID,'$newoptionvalue')");
      }
    DBExec ("INSERT INTO store_ProdOptions (ProductID,OptionID,OptionValueID) VALUES ($EditProductID,$OptionID,$OptionValueID)");
    }

  if ($gonext=='yes')
    return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$EditProductID,PageTab=>'prices')));
  else
    return array(ForwardTo=>ActionURL("store.IProduct.EditProduct.b",array(EditProductID=>$EditProductID,R=>rand(),PageTab=>'options')));
  }

function GetOptionValues($args)
  {
   extract(param_extract(array(
    Name=>'string',
    ),$args));
  $q=DBQuery ("SELECT OptionID,Name FROM store_Options WHERE Name LIKE '%�$Name"."�%' OR Name='$Name'","OptionID");
#  $q->Dump();

  if ($q)
    {
    $OptionID=intval($q->Top->OptionID);
    $q2=DBQuery("SELECT OptionValueID,Name FROM store_OptionValues WHERE OptionID=$OptionID","OptionValueID");
#    $q2->Dump();
    $r="";
    foreach ($q2->Rows as $OptionValueID=>$row)
      {
      $r.=(($r)?",":"")."$OptionValueID:'".addslashes(langstr_get($row->Name))."'";
      }
    if ($r) $r="{".$r."}";
    }
  print "<script>window.parent.setOptionValues($r)</script>$r";
  }
}

#http://localhost/kosmetik2/scripts/do/img.IImage.Edit.f?ArgsStr=~H:store~FProdGroup~Dimage~D1331|&call=modal
#http://localhost/kosmetik2/scripts/do/img.IImage.Edit.f?ArgsStr=~H:store~FProdGroup~Dimage~D1331|&call=modal
#http://localhost/kosmetik2/scripts/do/img.IImage.Edit.f?ArgsStr=~H:store~FProdGroup~Dimage~D1333|&call=modal