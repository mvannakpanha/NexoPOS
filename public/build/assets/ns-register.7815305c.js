import{F as x,f as F,b as n,c as m,n as d}from"./bootstrap.673b86ff.js";import{_ as h}from"./lang.2d0006f0.js";import{_ as T}from"./plugin-vue_export-helper.21dcd24c.js";import{b1 as t,aA as r,aB as s,L as V,b6 as X,az as p,y as l,ao as c,br as b,b8 as f,ay as w,aF as g}from"./runtime-core.esm-bundler.aa7a54f6.js";import"./runtime-dom.esm-bundler.68a12c3b.js";const B={name:"ns-register",data(){return{fields:[],xXsrfToken:null,validation:new x}},mounted(){F([n.get("/api/fields/ns.register"),n.get("/sanctum/csrf-cookie")]).subscribe(o=>{this.fields=this.validation.createFields(o[0]),this.xXsrfToken=n.response.config.headers["X-XSRF-TOKEN"],setTimeout(()=>m.doAction("ns-register-mounted",this))})},methods:{__:h,register(){if(!this.validation.validateFields(this.fields))return d.error(h("Unable to proceed the form is not valid.")).subscribe();this.validation.disableFields(this.fields),m.applyFilters("ns-register-submit",!0)&&n.post("/auth/sign-up",this.validation.getValue(this.fields),{headers:{"X-XSRF-TOKEN":this.xXsrfToken}}).subscribe(e=>{d.success(e.message).subscribe(),setTimeout(()=>{document.location=e.data.redirectTo},1500)},e=>{this.validation.triggerFieldsErrors(this.fields,e),this.validation.enableFields(this.fields),d.error(e.message).subscribe()})}}},N={class:"ns-box rounded shadow overflow-hidden transition-all duration-100"},C={class:"ns-box-body"},S={class:"p-3 -my-2"},E={key:0,class:"py-2 fade-in-entrance anim-duration-300"},R={key:0,class:"flex items-center justify-center"},j={class:"flex w-full items-center justify-center py-4"},A={href:"/sign-in",class:"link hover:underline text-sm"},H={class:"flex ns-box-footer border-t justify-between items-center p-3"};function K(o,e,L,O,a,i){const v=f("ns-field"),y=f("ns-spinner"),u=f("ns-button");return t(),r("div",N,[s("div",C,[s("div",S,[a.fields.length>0?(t(),r("div",E,[(t(!0),r(V,null,X(a.fields,(_,k)=>(t(),w(v,{key:k,field:_},null,8,["field"]))),128))])):p("",!0)]),a.fields.length===0?(t(),r("div",R,[l(y)])):p("",!0),s("div",j,[s("a",A,c(i.__("Already registered ?")),1)])]),s("div",H,[s("div",null,[l(u,{onClick:e[0]||(e[0]=_=>i.register()),type:"info"},{default:b(()=>[g(c(i.__("Register")),1)]),_:1})]),s("div",null,[l(u,{link:!0,href:"/sign-in",type:"success"},{default:b(()=>[g(c(i.__("Sign In")),1)]),_:1})])])])}var q=T(B,[["render",K]]);export{q as default};