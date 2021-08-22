class phpsbFlash.utils {
	static var version:String = "1.0.0.1";
	static var symbolName:String = "phpsbFlash.utils";
	static var symbolOwner:Object = phpsbFlash.utils;
	var className:String = "utils";

static function str2hex(s:String)
	{
		if (s.charAt(0)=="#")
		{
			s=s.substr(1);
		}
		return Number("0x"+s);
	}

static function var_dump(obj:Object,noCR:Boolean)
{
	var nl:String=(noCR)?" ":"\n";
	var i;
	var v;
	var s:String="";

	for (i in obj)
	{
		v=obj[i];
		if (v instanceof Object)
		{
			s=s + i + ":@Object" + nl;
			continue;
		}
		if (v instanceof Array)
		{
			s=s + i + ":@Array" + nl;
			continue;
		}
		s=s +i +":" + typeof (v) + "=" + v.toString() + nl;
	}
	return s;
}
/*
static function dump_xml(obj:Object,asTree:Boolean,treeField:String,depth:Number)	
{
	var i;
	var v;
	var j:Number;
	var s:String="";
	var nl:String=" ";
	var indent:String="";

	if (depth==undefined)
	{
		depth=0;
	}
	if (depth>3)
	{
		return "Too deep";
	}
	
	if (asTree==undefined)
	{
		asTree=true;
	}
	if (asTree)
	{
		for (i=0;i<depth;i++)
		{
			indent=indent + "   ";
		}
		nl="\n";
	}
	 
	
	for (i in obj)
	{
		v=obj[i];
		if (v instanceof Array)
		{
			s=s + indent + i + ":array" + nl + indent + "[" + nl;
			for (var j=0;j<v.length;j++)
			{
				s=s + dump_tree(v[j],asTree,treeField,depth+1);
				if (j!=(v.length-1)) {s=s + indent + "--------" + nl;}
			}
			s=s + indent + "]" + nl;
		}
		else
		{
		if (typeof(v)=="object")
		{
			if (i==treeField)
			{
				s=s + indent + i + ":" + nl + indent + "{" + nl + dump_tree(v,asTree,treeField,depth+1) + indent + "}" + nl;
			}
			else
			{
				if (v.nodeName!=undefined)
				{
					s=s + indent + i + "<" + v.nodeName + " " + dump_tree (v.style,false,undefined,depth+1) + ">" + nl;
				}
				else
				{
					s=s + indent + i + ":@Object" + nl;
				}
					
			}
		}
		else
		{
			s=s + indent + i + ":" + v + nl;
		}
		}
	}
	return s;
}
*/
static function str_replace(from:String, to:String, s:String)
{
	var p:Number;
	do
	{
		p = s.indexOf(from);
		if (p != -1)
		{
			s = ((p>0) ? (s.substr(0, p)) : "") + to + ((p<s.length-from.length) ? (s.substr(p+from.length, s.length-p-from.length)) : "");
		}
	} while (p != -1);
	return s;
}
static function assign_fields(to:Object,from:Object)
{
	if (from)
	{
		var p;
		for (p in from)
		{
			to[p] = from[p];
		}
	}
};	

static function append_fields(to:Object,from:Object)
{
	var p;
	for (p in from)
	{
		if (to[p] == undefined)
		{
			to[p] = from[p];
		}
	}
};
static function convertEntitiesToStr(s:String)
{
	s = str_replace("\t", " ", s);
	s = str_replace("\r", "", s);
	s = str_replace("  ", " ", s);
	s = str_replace("&quote;", '\"', s);
	s = str_replace("&apos;", "'", s);
	s = str_replace("&amp", "&", s);
	return s;
}
static function toNum(any)
{
	return (any==undefined)?0:Number(any);
}
}

