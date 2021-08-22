  var monthNames;
  if (monthNames)
    {
    monthNames=","+monthNames;
    monthNames=monthNames.split (",");
    }
  else
    {
    monthNames=new Array("","Янв","Фев","Мар","Апр","Май","Июн","Июл","Авг","Сен","Окт","Ноя","Дек");
    }

  function InputDate(aForm,aVariable,aDay,aMonth,aYear,nullable)
    {
    var s;
    var i;
    var today=new Date();
    var aDay; var aMonth; var aYear;
    var dateok=0;

    if (!aDay)
      {
      if (nullable) {aDay=0; aMonth=0; aYear=0;}
      else
        {
        aDay=  today.getDate();
        aMonth=today.getMonth()+1;
        aYear= today.getYear();
        datestr=today.toGMTString();
        }
      }
    else
      {
      var dd=new Date(aYear,aMonth,aDay);
      datestr=dd.toGMTString();
      }

    s="";
    datecheck=" onChange='CheckDate(\""+aForm+"."+aVariable+"\")'";

    s+="<select name='"+aVariable+"_day'"+datecheck+">";
    if (nullable) {s+="<option value='0'>--";}
    for (i=1;i<=31;i++)
      {
      if (aDay==i) {sel=" SELECTED";} else {sel="";}
      s+="<option value='"+i+"'"+sel+">"+i;
      }
    s+="</select>";

    s+="<select name='"+aVariable+"_month'"+datecheck+">";
    if (nullable) {s+="<option value='0'>--";}
    for (i=1;i<monthNames.length;i++)
      {
      if (aMonth==i) {sel=" SELECTED";} else {sel="";}
      s+="<option value='"+i+"'"+sel+">"+monthNames[i];
      }
    s+="</select>";

    thisYear=today.getYear();
    s+="<select name='"+aVariable+"_year'"+datecheck+">";
    if (nullable) {s+="<option value='0'>--";}
    for (i=(thisYear-3);i<(thisYear+4);i++)
      {
      if (aYear==i) {sel=" SELECTED";} else {sel="";}
      s+="<option value='"+i+"'"+sel+">"+i;
      }
    s+="</select>";
    s+="<input type='hidden' name='"+aVariable+"' value='"+datestr+"'>";
    document.write (s);
    }
  function CheckDate(aVarName)
    {
    var d=eval (aVarName+"_day.value");
    var m=eval (aVarName+"_month.value");
    var y=eval (aVarName+"_year.value");
    var t;

    eval (aVarName+"_day.style.color='black'");
    eval (aVarName+"_month.style.color='black'");
    eval (aVarName+"_year.style.color='black'");

    if ( (!d) && (!m) && (!y) )
      {
      eval (aVarName+".value=''");
      }
    else
      {
      if (d && m && y)
        {
        var dd=new Date(y,m-1,d);
        var ddt=dd.getTime();
        if (dd.getDate() != d)
          {eval (aVarName+"_day.style.color='red'");}
        eval (aVarName+".value='"+dd.toGMTString()+"'");
        }
      else
        {
        if (!d) {eval (aVarName+"_day.style.color='red'");}
        if (!m) {eval (aVarName+"_month.style.color='red'");}
        if (!y) {eval (aVarName+"_year.style.color='red'");}
        }
      }
    }