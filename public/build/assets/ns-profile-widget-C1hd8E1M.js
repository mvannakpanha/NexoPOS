import{n as p}from"./ns-avatar-image-CAD6xUGA.js";import{_ as b,n as h}from"./currency-lOMYG1Wf.js";import{_ as v}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{r as n,o as l,c as i,a as e,t as a,f as d,F as g,b as x}from"./runtime-core.esm-bundler-RT2b-_3S.js";import"./index.es-Br67aBEV.js";const w={name:"ns-profile-widget",components:{nsAvatarImage:p},data(){return{svg:"",user:ns.user,profileDetails:[]}},computed:{avatarUrl(){return this.url.length===0?"":this.url}},mounted(){this.loadUserProfileWidget()},methods:{__:b,nsCurrency:h,loadUserProfileWidget(o){nsHttpClient.get(`/api/reports/cashier-report${o?"?refresh=true":""}`).subscribe(s=>{this.profileDetails=s})}}},y={id:"ns-best-cashiers",class:"flex flex-auto flex-col shadow rounded-lg overflow-hidden"},k={class:"flex-auto"},C={class:"head text-center border-b w-full flex justify-between items-center p-2"},P={class:"flex -mx-1"},D={class:"px-1"},U={class:"px-1"},W={class:"body"},j={class:"h-40 flex items-center justify-center"},B={class:"rounded-full border-4 border-gray-400 bg-white shadow-lg overflow-hidden"},$={class:"border-t border-box-edge"};function F(o,s,N,V,r,c){const u=n("ns-icon-button"),_=n("ns-close-button"),f=n("ns-avatar-image");return l(),i("div",y,[e("div",k,[e("div",C,[e("h5",null,a(c.__("Profile")),1),e("div",P,[e("div",D,[d(u,{"class-name":"la-sync-alt",onClick:s[0]||(s[0]=t=>c.loadUserProfileWidget(!0))})]),e("div",U,[d(_,{onClick:s[1]||(s[1]=t=>o.$emit("onRemove"))})])])]),e("div",W,[e("div",j,[e("div",B,[d(f,{size:32,url:r.user.attributes.avatar_link,name:r.user.username},null,8,["url","name"])])]),e("div",$,[e("ul",null,[(l(!0),i(g,null,x(r.profileDetails,(t,m)=>(l(),i("li",{key:m,class:"border-b border-box-edge p-2 flex justify-between"},[e("span",null,a(t.label),1),e("span",null,a(t.value),1)]))),128))])])])])])}const L=v(w,[["render",F]]);export{L as default};
