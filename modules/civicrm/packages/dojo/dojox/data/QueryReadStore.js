/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.data.QueryReadStore"]){
dojo._hasResource["dojox.data.QueryReadStore"]=true;
dojo.provide("dojox.data.QueryReadStore");
dojo.require("dojo.string");
dojo.declare("dojox.data.QueryReadStore",null,{url:"",requestMethod:"get",_className:"dojox.data.QueryReadStore",_items:[],_lastServerQuery:null,lastRequestHash:null,doClientPaging:false,doClientSorting:false,_itemsByIdentity:null,_identifier:null,_features:{"dojo.data.api.Read":true,"dojo.data.api.Identity":true},_labelAttr:"label",constructor:function(_1){
dojo.mixin(this,_1);
},getValue:function(_2,_3,_4){
this._assertIsItem(_2);
if(!dojo.isString(_3)){
throw new Error(this._className+".getValue(): Invalid attribute, string expected!");
}
if(!this.hasAttribute(_2,_3)){
if(_4){
return _4;
}
console.log(this._className+".getValue(): Item does not have the attribute '"+_3+"'.");
}
return _2.i[_3];
},getValues:function(_5,_6){
this._assertIsItem(_5);
var _7=[];
if(this.hasAttribute(_5,_6)){
_7.push(_5.i[_6]);
}
return _7;
},getAttributes:function(_8){
this._assertIsItem(_8);
var _9=[];
for(var i in _8.i){
_9.push(i);
}
return _9;
},hasAttribute:function(_b,_c){
return this.isItem(_b)&&typeof _b.i[_c]!="undefined";
},containsValue:function(_d,_e,_f){
var _10=this.getValues(_d,_e);
var len=_10.length;
for(var i=0;i<len;i++){
if(_10[i]==_f){
return true;
}
}
return false;
},isItem:function(_13){
if(_13){
return typeof _13.r!="undefined"&&_13.r==this;
}
return false;
},isItemLoaded:function(_14){
return this.isItem(_14);
},loadItem:function(_15){
if(this.isItemLoaded(_15.item)){
return;
}
},fetch:function(_16){
_16=_16||{};
if(!_16.store){
_16.store=this;
}
var _17=this;
var _18=function(_19,_1a){
if(_1a.onError){
var _1b=_1a.scope||dojo.global;
_1a.onError.call(_1b,_19,_1a);
}
};
var _1c=function(_1d,_1e,_1f){
var _20=_1e.abort||null;
var _21=false;
var _22=_1e.start?_1e.start:0;
if(_17.doClientPaging==false){
_22=0;
}
var _23=_1e.count?(_22+_1e.count):_1d.length;
_1e.abort=function(){
_21=true;
if(_20){
_20.call(_1e);
}
};
var _24=_1e.scope||dojo.global;
if(!_1e.store){
_1e.store=_17;
}
if(_1e.onBegin){
_1e.onBegin.call(_24,_1f,_1e);
}
if(_1e.sort&&this.doClientSorting){
_1d.sort(dojo.data.util.sorter.createSortFunction(_1e.sort,_17));
}
if(_1e.onItem){
for(var i=_22;(i<_1d.length)&&(i<_23);++i){
var _26=_1d[i];
if(!_21){
_1e.onItem.call(_24,_26,_1e);
}
}
}
if(_1e.onComplete&&!_21){
var _27=null;
if(!_1e.onItem){
_27=_1d.slice(_22,_23);
}
_1e.onComplete.call(_24,_27,_1e);
}
};
this._fetchItems(_16,_1c,_18);
return _16;
},getFeatures:function(){
return this._features;
},close:function(_28){
},getLabel:function(_29){
if(this._labelAttr&&this.isItem(_29)){
return this.getValue(_29,this._labelAttr);
}
return undefined;
},getLabelAttributes:function(_2a){
if(this._labelAttr){
return [this._labelAttr];
}
return null;
},_fetchItems:function(_2b,_2c,_2d){
var _2e=_2b.serverQuery||_2b.query||{};
if(!this.doClientPaging){
_2e.start=_2b.start||0;
if(_2b.count){
_2e.count=_2b.count;
}
}
if(!this.doClientSorting){
if(_2b.sort){
var _2f=_2b.sort[0];
if(_2f&&_2f.attribute){
var _30=_2f.attribute;
if(_2f.descending){
_30="-"+_30;
}
_2e.sort=_30;
}
}
}
if(this.doClientPaging&&this._lastServerQuery!==null&&dojo.toJson(_2e)==dojo.toJson(this._lastServerQuery)){
_2c(this._items,_2b);
}else{
var _31=this.requestMethod.toLowerCase()=="post"?dojo.xhrPost:dojo.xhrGet;
var _32=_31({url:this.url,handleAs:"json-comment-optional",content:_2e});
_32.addCallback(dojo.hitch(this,function(_33){
_33=this._filterResponse(_33);
if(_33.label){
this._labelAttr=_33.label;
}
var _34=_33.numRows||-1;
this._items=[];
dojo.forEach(_33.items,function(e){
this._items.push({i:e,r:this});
},this);
var _36=_33.identifier;
this._itemsByIdentity={};
if(_36){
this._identifier=_36;
for(i=0;i<this._items.length;++i){
var _37=this._items[i].i;
var _38=_37[_36];
if(!this._itemsByIdentity[_38]){
this._itemsByIdentity[_38]=_37;
}else{
throw new Error(this._className+":  The json data as specified by: ["+this.url+"] is malformed.  Items within the list have identifier: ["+_36+"].  Value collided: ["+_38+"]");
}
}
}else{
this._identifier=Number;
for(i=0;i<this._items.length;++i){
this._items[i].n=i;
}
}
_34=(_34===-1)?this._items.length:_34;
_2c(this._items,_2b,_34);
}));
_32.addErrback(function(_39){
_2d(_39,_2b);
});
this.lastRequestHash=new Date().getTime()+"-"+String(Math.random()).substring(2);
this._lastServerQuery=dojo.mixin({},_2e);
}
},_filterResponse:function(_3a){
return _3a;
},_assertIsItem:function(_3b){
if(!this.isItem(_3b)){
throw new Error(this._className+": Invalid item argument.");
}
},_assertIsAttribute:function(_3c){
if(typeof _3c!=="string"){
throw new Error(this._className+": Invalid attribute argument ('"+_3c+"').");
}
},fetchItemByIdentity:function(_3d){
if(this._itemsByIdentity){
var _3e=this._itemsByIdentity[_3d.identity];
if(!(_3e===undefined)){
if(_3d.onItem){
var _3f=_3d.scope?_3d.scope:dojo.global;
_3d.onItem.call(_3f,{i:_3e,r:this});
}
return;
}
}
var _40=function(_41,_42){
var _43=_3d.scope?_3d.scope:dojo.global;
if(_3d.onError){
_3d.onError.call(_43,error);
}
};
var _44=function(_45,_46){
var _47=_3d.scope?_3d.scope:dojo.global;
try{
var _48=null;
if(_45&&_45.length==1){
_48=_45[0];
}
if(_3d.onItem){
_3d.onItem.call(_47,_48);
}
}
catch(error){
if(_3d.onError){
_3d.onError.call(_47,error);
}
}
};
var _49={serverQuery:{id:_3d.identity}};
this._fetchItems(_49,_44,_40);
},getIdentity:function(_4a){
var _4b=null;
if(this._identifier===Number){
_4b=_4a.n;
}else{
_4b=_4a.i[this._identifier];
}
return _4b;
},getIdentityAttributes:function(_4c){
return [this._identifier];
}});
}
