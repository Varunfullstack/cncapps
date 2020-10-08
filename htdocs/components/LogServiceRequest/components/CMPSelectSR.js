import RadioButtons, { RadioButtonsType } from "../../utils/radioButtons.js";
import Table from "./../../utils/table/table.js?v=1";
 import Spinner from "../../utils/spinner.js?v=1";
import CMPContactSR from "./CMPContactSR.js?v=1";
import CMPCustomerSR from "./CMPCustomerSR.js";
import SVCCustomers from "../../services/APICutsomer.js";

class CMPSelectSR extends React.Component {
    el=React.createElement;
    apiCutsomer=new SVCCustomers();

    tabs=[];
    constructor(props)
    {
        super(props);
        this.state = { 
            srType:1,
            activeTab:"COSR" ,
            contactSR:[],
            contactFixedSR:[],
            customerSR:[],
            _showSpinner:true
        };
        this.initTaps();
    }
    componentDidMount() {
        //if(this.props.customerId&&this.props.contactId)
        console.log(this.props);
        this.getExistingSR(this.props.customerId,this.props.contactId);
    }
    showSpinner=()=>{      
        this.setState({_showSpinner:true});
    }
    hideSpinner=()=>{
      this.setState({_showSpinner:false});
      }
    initTaps=()=>{
        this.tabs=[
            {id:1,title:'Contact SR',code:'COSR',order:1,display:true,icon:null},
            {id:2,title:'Customer SR',code:'CUSR',order:2,display:true,icon:null},
            {id:3,title:'Contact Fixed SR',code:'CFSR',order:3,display:true,icon:null},            
          ];
    }
    getTabsElement = () => {
        
        const { el, isActive, setActiveTab ,tabs} = this;    
        return el("div", { key: "tab", className: "tab-container" }, 
        tabs.sort((a,b)=>a.order>b.order?1:-1).map(t=>{
          if(t.display)
          return  el(
            "i",
            {
              key: t.code,
              className: isActive(t.code)+" nowrap",
              onClick: () => setActiveTab(t.code),
            },
            t.title,
            t.icon?el("span",{className:t.icon,style:{
              fontSize: "12px",
              marginTop: "-12px",
              marginLeft:"-5px",
              position: "absolute",
              color:"#000"
            }}):null
          );
          else return null;
        }));
      };
      isActive = (code) => {
        const { activeTab } = this.state;
        if (activeTab === code) return "active";
        else return "";
      };
      setActiveTab = (code) => {               
        this.setState({ activeTab:code });
      };
   
    handleSrTypeChange=(srType)=>{
        //console.log(srType);
        if(srType==2)
          this.props.updateSRData({nextStep:3})
        this.setState({srType});
    }
    getSRTypeElement=()=>{
        const {el,handleSrTypeChange}=this;
        const items=[{id:1,name:'Existing Request'},{id:2,name:'New Service Request'}];
        return el("div",null,el(RadioButtons,{items,mode:RadioButtonsType.horizontal,center:true,value:1,onChange:handleSrTypeChange}));
    }
    getExistingSR=(customerId,contactId)=>{
        this.showSpinner();
        this.apiCutsomer.getCustomerSR(customerId,contactId).then(res=>{
            
            const customerSR=res.customerSR;
            const contactSR=res.contactSR.filter(s=>s.status!=="F");
            const contactFixedSR=res.contactSR.filter(s=>s.status==="F");
            //console.log(res.contactSRs,res.customerSRs);
            //console.log(customerSR,contactSR,contactFixedSR);
            this.setState({customerSR,contactSR,contactFixedSR,_showSpinner:false});
        });
    }
    openProblemHistory=(problemId)=>{
        window.open(
            'Activity.php?action=problemHistoryPopup&problemID='+problemId+'&htmlFmt=popup',
            'reason',
            'scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0')
    }
    newSrActivity=(problemId,activityId)=>{
        console.log(problemId,activityId);
      //window.location=`Activity.php?action=editActivity&callActivityID=${activityId}`;      
      const url="Activity.php?action=createFollowOnActivity&callActivityID="+activityId;                
      //console.log(url);
      window.location=url;

    }
    render() { 
        const {el,getSRTypeElement,getTabsElement,openProblemHistory,newSrActivity}=this;
        const {contactSR,contactFixedSR,customerSR, activeTab,_showSpinner}=this.state;
         return (  
            el(Spinner,{show:_showSpinner}),
            el('div',null,getSRTypeElement(),
            getTabsElement(),
            activeTab==='COSR'?el(CMPContactSR,{items:contactSR,openProblemHistory,newSrActivity}):null,
            activeTab==='CFSR'?el(CMPContactSR,{items:contactFixedSR,openProblemHistory,newSrActivity}):null,
            activeTab==='CUSR'?el(CMPCustomerSR,{items:customerSR,openProblemHistory,newSrActivity}):null,
            )
        );
    }
}
 
export default CMPSelectSR;