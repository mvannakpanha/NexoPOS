import{_ as i,n as d,a as h}from"./currency-lOMYG1Wf.js";import{_ as x}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{r as _,o as f,c as u,a as s,t as a,f as p}from"./runtime-core.esm-bundler-RT2b-_3S.js";const m={name:"ns-orders-chart",data(){return{totalWeeklySales:0,totalWeekTaxes:0,totalWeekExpenses:0,totalWeekIncome:0,chartOptions:{theme:{mode:window.ns.theme},chart:{id:"vuechart-example",width:"100%",height:"100%"},stroke:{curve:"smooth",dashArray:[0,8]},xaxis:{categories:[]},colors:["#5f83f3","#AAA"]},series:[{name:i("Current Week"),data:[]},{name:i("Previous Week"),data:[]}],reportSubscription:null,report:null}},components:{},methods:{__:i,nsCurrency:d,nsRawCurrency:h},mounted(){this.reportSubscription=Dashboard.weeksSummary.subscribe(n=>{n.result!==void 0&&(this.chartOptions.xaxis.categories=n.result.map(t=>t.label),this.report=n,this.totalWeeklySales=0,this.totalWeekIncome=0,this.totalWeekExpenses=0,this.totalWeekTaxes=0,this.report.result.forEach((t,c)=>{if(t.current!==void 0){const r=t.current.entries.map(e=>e.day_paid_orders);let o=0;r.length>0&&(o=r.reduce((e,l)=>e+l)),this.totalWeekExpenses+=t.current.entries.map(e=>parseFloat(e.day_expenses)).reduce((e,l)=>e+l),this.totalWeekTaxes+=t.current.entries.map(e=>parseFloat(e.day_taxes)).reduce((e,l)=>e+l),this.totalWeekIncome+=t.current.entries.map(e=>parseFloat(e.day_income)).reduce((e,l)=>e+l),this.series[0].data.push(o)}else this.series[0].data.push(0);if(t.previous!==void 0){const r=t.previous.entries.map(e=>e.day_paid_orders);let o=0;r.length>0&&(o=r.reduce((e,l)=>e+l)),this.series[1].data.push(o)}else this.series[1].data.push(0)}),this.totalWeeklySales=this.series[0].data.reduce((t,c)=>t+c))})}},b={id:"ns-orders-chart",class:"flex flex-auto flex-col shadow ns-box rounded-lg overflow-hidden"},k={class:"p-2 flex ns-box-header items-center justify-between border-b"},y={class:"font-semibold"},v=s("div",{class:"head flex-auto flex h-56"},[s("div",{class:"w-full h-full pt-2"})],-1),w={class:"foot p-2 -mx-4 flex flex-wrap"},W={class:"flex w-full lg:w-full border-b lg:border-t lg:py-1 lg:my-1"},g={class:"px-4 w-1/2 lg:w-1/2 flex flex-col items-center justify-center"},C={class:"text-xs"},S={class:"text-lg xl:text-xl font-bold"},E={class:"px-4 w-1/2 lg:w-1/2 flex flex-col items-center justify-center"},j={class:"text-xs"},I={class:"text-lg xl:text-xl font-bold"},T={class:"flex w-full lg:w-full"},A={class:"px-4 w-full lg:w-1/2 flex flex-col items-center justify-center"},O={class:"text-xs"},B={class:"text-lg xl:text-xl font-bold"},F={class:"px-4 w-full lg:w-1/2 flex flex-col items-center justify-center"},N={class:"text-xs"},R={class:"text-lg xl:text-xl font-bold"};function D(n,t,c,r,o,e){const l=_("ns-close-button");return f(),u("div",b,[s("div",k,[s("h3",y,a(e.__("Recents Orders")),1),s("div",null,[p(l,{onClick:t[0]||(t[0]=V=>n.$emit("onRemove"))})])]),v,s("div",w,[s("div",W,[s("div",g,[s("span",C,a(e.__("Weekly Sales")),1),s("h2",S,a(e.nsCurrency(o.totalWeeklySales,"abbreviate")),1)]),s("div",E,[s("span",j,a(e.__("Week Taxes")),1),s("h2",I,a(e.nsCurrency(o.totalWeekTaxes,"abbreviate")),1)])]),s("div",T,[s("div",A,[s("span",O,a(e.__("Net Income")),1),s("h2",B,a(e.nsCurrency(o.totalWeekIncome,"abbreviate")),1)]),s("div",F,[s("span",N,a(e.__("Week Expenses")),1),s("h2",R,a(e.nsCurrency(o.totalWeekExpenses,"abbreviate")),1)])])])])}const G=x(m,[["render",D]]);export{G as default};
