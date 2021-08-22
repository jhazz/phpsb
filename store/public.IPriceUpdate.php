<?
class store_IPriceUpdate
  {
  var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
  var $CopyrightURL="http://www.jhazz.com/jsb";
  var $ComponentVersion="1.0";
  var $SKUProductID;
#  var $RoleAccess=array(
#      StoreManager=>"UpdatePriceFromFile,GetPriceFileNames",
#  );
  function checktreefordeep(&$tree,$startgroup,$deep,$enddeep,$list)
    {
    if ($deep>$enddeep)
      {
      print "<h2>Большая вложенность</h2>Вложенность групп для ветки <br>[$list] превышает $enddeep<br>";
      return -1;
      }
    $count=0;
    reset ($tree);
    while (list($groupid,$v)=each ($tree))
      {
      if ($v[0]==$startgroup)
        {
        $count++;
        $docheck[]=$groupid;
        }
      }
      if ($docheck)
      {while (list ($i,$groupid)=each ($docheck))
        {
        print "<table width='100%' border='1' cellpadding='4' cellspacing='0'><tr valign='top'><td width='50' bgcolor='#f0f0ff'>$groupid</td><td bgcolor='#ffffff'>".$tree[$groupid][1];
        $res=$this->checktreefordeep ($tree,$groupid,$deep+1,$enddeep,$list."->$groupid");
        $tree[$groupid][2]=$res;
        print "</td><td bgcolor='#f0f0f0' width='50'><nobr>".$tree[$groupid][2]."</nobr></td></tr></table>";
        if ($res==-1) {return false;}
        }
      }
    return $count;
    }


  function UpdatePriceFromFile($filename)
    {
    global $cfg;
    $ProductGroupsContext=$cfg['Settings']['store']['ProductGroupsContext'];

    if (!$ProductGroupsContext)
      {
      print "WARNING! Please set config variable \$cfg['Settings']['store']['ProductGroupsContext'] to store context";
      $ProductGroupsContext="store";
      }


    $s="";
    $pricelines=file($filename);
    # Сначала ищем ключевое слово @BEGIN GROUPS
    $ok=no;
    for ($i=0;$i<count($pricelines);$i++)
      {
      if (strpos ($pricelines[$i],"@BEGIN GROUPS")!==false ) {$ok=yes; break;}
      }
    if (!$ok)
      {
      print "\n::error::В файле не найден заголовок @BEGIN GROUPS\n";
      return;
      }

    $i++;
    $ii=$i;
    # Читаем номера групп в $GroupsTree для проверки
    print "<h2>1. Загрузка групп</h2>";
    for (;$i<count($pricelines);$i++)
     {
     $s=$pricelines[$i];
     if (substr ($s,0,1)=="@") {break;}
     if (trim($s)=="") {coninue;}
     list ($ProductGroupID,$ParentGroupID,$GroupName)=split ("\t",$s);
     $ProductGroupID=intval($ProductGroupID);
     if ($ProductGroupID==0) {print "\n::error::Ошибка в описании группы: строка №".($i+1);return;}
     if ($GroupName=="") {print "\n::error::Ошибка в названии группы: строка №".($i+1);return;}
     $ParentGroupID=intval ($ParentGroupID);
     if ($ParentGroupID==0) {continue;}
    //       $GroupName = ereg_replace("(['])","`",trim($GroupName));
     if ($ProductGroupID>0) {$GroupsTree[$ProductGroupID]=array($ParentGroupID,$GroupName,false);}
     }
    print "<hr>";

    # ПРОВЕРКА ГРУППЫ '-1' - компьютеры
    print "<table border='1'><tr><td bgcolor='#f0f0f0'>Проверка группы 'КОМПЬЮТЕРЫ [-1]'";
    $lookresult=$this->checktreefordeep ($GroupsTree,-1,0,5,"Компьютеры"); # Проверяем дерево на глубину не более 5, начиная с 0
    if ($lookresult==-1)
     {
     print "\n::error::Проверьте группы товаров на ошибки. Обновление базы не производится!";
     return;
     }
    print "</td></tr></table>";


    $i=$ii;
    # Делаем загрузку проверенного файла

    $StoreLayoutID=1;

    $q=DBQuery ("SELECT DefaultLayout FROM sys_Contexts WHERE SysContext='$ProductGroupsContext'");
    if ($q)
     {
     $StoreLayoutID=intval($q->Top->DefaultLayout);
     }
    else
     {
     DBExec("INSERT INTO sys_Contexts (SysContext,Caption,DefaultLayout) VALUES ('$ProductGroupsContext','Webstore context',$StoreLayoutID) ");
     }
    # Прячем все страницы групп в структуре дерева сайта
    DBExec ("UPDATE jsb_Pages Set State=0 WHERE SysContext='$ProductGroupsContext'");

    $l="";
    $rowno=0;
    print "<hr>";
    for (;$i<count($pricelines);$i++)
     {
     $s=$pricelines[$i];
     if (substr ($s,0,1)=="@") {break;}
     list ($ProductGroupID,$ParentGroupID,$GroupName)=split ("\t",$s);
     $ParentGroupID=intval ($ParentGroupID);
     if ($ParentGroupID==0) {continue;}
     $GroupName=preg_replace(array("/\'/",'/\"/','/\|/'),array("`","``",' '),trim($GroupName));
     if ($ParentGroupID==-1) {$ParentGroupID=0;}
     $q=DBQuery("SELECT SysContext FROM jsb_Pages WHERE SysContext='$ProductGroupsContext' AND JSBPageID=$ProductGroupID");

     $now=time();
     if ($q)
       {
       if (!DBExec ("UPDATE jsb_Pages
         SET OrderNo=$rowno, ParentID=$ParentGroupID,
           Title='$GroupName',Caption='$GroupName',
           UpdatedAt=$now, State=1, JSBLayoutID=$StoreLayoutID
         WHERE SysContext='$ProductGroupsContext' AND JSBPageID=$ProductGroupID"));
       }
     else
       {
       DBExec ("INSERT INTO jsb_Pages
         (SysContext,JSBPageID,ParentID,Caption,Title,OrderNo,State,JSBLayoutID,Viewed)
         VALUES ('$ProductGroupsContext',$ProductGroupID,$ParentGroupID,'$GroupName','$GroupName',$i,1,$StoreLayoutID,0)");
       }
     }
    print"</table>";

    # Ищем ключевое слово @BEGIN PRICE
    $ok=no;
    for (;$i<count($pricelines);$i++)
     {
     if (strpos ($pricelines[$i],"@BEGIN PRICE")!==false) {$ok=yes; break;}
     }
    if (!$ok)
     {
     print "\n::error::Ошибка файла. Не найден заголовок начала описания товаров @BEGIN PRICE";
     return;
     }

    print "<h2>2. Загрузка товаров из прайса</h2>";
    DBExec ("UPDATE store_Products SET Hidden=1");
    $i++;
    for (;$i<count($pricelines);$i++)
     {
     $s=$pricelines[$i];
     if (substr ($s,0,1)=="@") {break;}
     list ($GroupID,$SKU,$ProductName,$BCPrice,$VCPrice,$Guarantee)=split ("\t",trim($s));
     $GroupID=intval($GroupID);
     $SKU=DBEscape($SKU);
     $ProductName=DBEscape($ProductName);
     $Guarantee=DBEscape($Guarantee);
     $BCPrice=floatval($BCPrice);
     $VCPrice=floatval($VCPrice);

     $q=DBQuery("SELECT ProductID FROM store_Products WHERE SKU='$SKU'");
     if ($q)
       {
       $ProductID=$q->Top->ProductID;
       DBExec("UPDATE LOW_PRIORITY store_Products SET
         Hidden=0,GroupID=$GroupID,SKU='$SKU',Name='$ProductName',Guarantee='$Guarantee',BCPrice=$BCPrice,VCPrice=$VCPrice
         WHERE ProductID=$ProductID");
       }
     else
       {
       $ProductID=DBGetID('store.Product');
       DBExec("INSERT LOW_PRIORITY INTO store_Products (Hidden,ProductID,GroupID,SKU,Name,BCPrice,VCPrice,Guarantee)
       VALUES (0,$ProductID,$GroupID,'$SKU','$ProductName',$BCPrice,$VCPrice,'$Guarantee')");
       }
     $this->SKUProductID[$SKU]=$ProductID;
     }

    $i++;
    if (trim($s)=='@BEGIN ATTRIBUTES')
      {
      print "<h2>3. Собираю параметры товаров</h2>";
      $SKU="";
      for (;$i<count($pricelines);$i++)
        {
        $s=DBEscape(trim($pricelines[$i]));
        if (!$s) {continue;}
        if (substr ($s,0,1)=="@") {break;}
        if (substr ($s,0,2)=='- ')
          {
          $s=substr ($s,2,1000);
          list($attr,$val)=explode (':',$s,2);
          if (!$SKU)
            {
            print "\n::error::Ошибка блока параметров товаров";
            return;
            }
          $s2="INSERT INTO store_ProdAttrValues (ProductID,Attr,Value) VALUES($ProductID,'$attr','$val')";
          DBExec($s2);
          }
        else
          {
          $SKU=$s;
          $ProductID=$this->SKUProductID[$SKU];
          DBExec ("DELETE FROM store_ProdAttrValues WHERE ProductID=$ProductID");
          }
        }
      }

    $cleaner=&$_ENV->LoadInterface("jsb.IClearTmp");
    $cleaner->NavigationMenu();
    print "<h2>Структура прайса обновлена</h2>";
    return true;
    }

  function GetPriceFileNames($args)
    {
    global $cfg;
    extract(param_extract(array(
      Login=>'string',
      Pass=>'string',
      ),$args));

    if (($Login=='admin') && ($Pass=='jo13'))
      {
      $q=DBQuery("SELECT PriceFileID,PriceFileTitle,PriceFilePath,DatePosted FROM store_PriceFiles ORDER BY PriceFileID");
      print "PriceNamesStarts\n";
      foreach ($q->Rows as $PriceFileID=>$row)
        {
        print "$row->PriceFileID\t$row->PriceFileTitle\t$row->DatePosted\n";
        }
      }
    else
      {
      print "Login error";
      }
    }

  function PostPriceObject($args)
    {
    global $cfg;
    $TargetPath=$cfg['FilesPath'].'/store';
    if (!is_dir($TargetPath)) {mkdir($TargetPath);}
    $TargetPath.='/pricelists';
    if (!is_dir($TargetPath)) {mkdir($TargetPath);}
    extract(param_extract(array(
      Login=>'string',
      Pass=>'string',
      DatePosted=>'string',
      PriceFilePrefix=>'string',
      PriceFileID=>'integer',
      ),$args));

    if (($Login=='admin') && ($Pass=='jo13'))
      {
      $err=$_FILES['DataObject']['error'];
      if ($err!=0) {print "Error $err"; return;}

      if ($PriceFileID==0)
        {
        $this->UpdatePriceFromFile($_FILES['DataObject']['tmp_name']);
        }
      else
        {
        $q=DBQuery ("SELECT PriceFilePath FROM store_PriceFiles WHERE PriceFileID=$PriceFileID");

        if ($q->Top->PriceFilePath) {if ($q) {$oldfile=$TargetPath.'/'.$q->Top->PriceFilePath; if (file_exists($oldfile)) {unlink($oldfile); }}}
        $fi=pathinfo($_FILES['DataObject']['name']);
        $fname=$PriceFilePrefix.'_'.$DatePosted.'_'.substr(md5(uniqid(rand(), true)),1,4).'.'.$fi['extension'];
        move_uploaded_file($_FILES['DataObject']['tmp_name'],$TargetPath.'/'.$fname );
        DBExec ("UPDATE store_PriceFiles SET PriceFilePath='$fname',DatePosted='$DatePosted' WHERE PriceFileID=$PriceFileID");
        print "<h2>Файл ".$_FILES['DataObject']['name']." сохранен успешно</h2>";
        print "Прайс сохранен в файле ".$TargetPath.'/'.$fname."<br>";
        }
        print "<br>Работа окончена";
        $ip=$_SERVER["REMOTE_ADDR"];
        DBExec ("DELETE FROM sys_Posts WHERE IPaddr='$ip'");
      }
    else
      {
      print "Login error (login:[$Login], pass:[$Pass])";
      }

    }
  }
