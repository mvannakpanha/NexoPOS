"use strict";(self.webpackChunkNexoPOS_4x=self.webpackChunkNexoPOS_4x||[]).push([[8104],{8104:(e,t,i)=>{i.r(t),i.d(t,{default:()=>d});var s=i(7266),o=i(6386),l=i(8603),n=i(9671),a=i(7389);const r={name:"ns-pos-note-popup",data:function(){return{validation:new s.Z,fields:[{label:(0,a.__)("Note"),name:"note",value:"",description:(0,a.__)("More details about this order"),type:"textarea"},{label:(0,a.__)("Display On Receipt"),name:"note_visibility",value:"",options:[{label:(0,a.__)("Yes"),value:"visible"},{label:(0,a.__)("No"),value:"hidden"}],description:(0,a.__)("Will display the note on the receipt"),type:"switch"}]}},mounted:function(){var e=this;this.popupCloser(),this.fields.forEach((function(t){"note"===t.name?t.value=e.$popupParams.note:"note_visibility"===t.name&&(t.value=e.$popupParams.note_visibility)}))},methods:{__:a.__,popupResolver:o.Z,popupCloser:l.Z,closePopup:function(){this.popupResolver(!1)},saveNote:function(){if(!this.validation.validateFields(this.fields)){var e=this.validation.validateFieldsErrors(this.fields);return this.validation.triggerFieldsErrors(this.fields,e),this.$forceUpdate(),console.log(this.fields),n.kX.error((0,a.__)("Unable to proceed the form is not valid.")).subscribe()}return this.popupResolver(this.validation.extractFields(this.fields))}}};const d=(0,i(1900).Z)(r,(function(){var e=this,t=e.$createElement,i=e._self._c||t;return i("div",{staticClass:"shadow-lg bg-white w-95vw md:w-3/5-screen lg:w-2/5-screen"},[i("div",{staticClass:"p-2 flex justify-between items-center border-b border-gray-200"},[i("h3",{staticClass:"font-bold"},[e._v(e._s(e.__("Order Note")))]),e._v(" "),i("div",[i("ns-close-button",{on:{click:function(t){return e.closePopup()}}})],1)]),e._v(" "),i("div",{staticClass:"p-2 border-b border-gray-200"},e._l(e.fields,(function(e,t){return i("ns-field",{key:t,attrs:{field:e}})})),1),e._v(" "),i("div",{staticClass:"p-2 flex justify-end"},[i("ns-button",{attrs:{type:"info"},on:{click:function(t){return e.saveNote()}}},[e._v("Save")])],1)])}),[],!1,null,null,null).exports}}]);