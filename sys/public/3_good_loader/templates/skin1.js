P$.$.regModule('sys.templates.skin1',{
onUsing:function(params,ldr,p){
	P$.trace('Вызван sys.templates.skin1.onUsing()','Источник: '+ldr.name);
	
	},
	// вызывается после того как все модули, от которых зависит данный модуль, загружены
initModule:function(){
	P$.trace('Вызван sys.templates.skin1.initModule()');
	}

},'');