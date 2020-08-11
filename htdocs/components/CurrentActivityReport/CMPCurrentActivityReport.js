
import CMPInboxHelpDesk from './components/CMPInboxHelpDesk.js?v=1';
import SVCCurrentActivityService from './services/SVCCurrentActivityService.js?v=1';
class CMPCurrentActivityReport extends React.Component{
    el = React.createElement;
    apiCurrentActivityService;
    constructor(props)
    {
        super(props);
        this.state={
            activeTab:'helpdesk',
            helpDeskInbox:[],
            escalationsInbox:[],
            salesInbox:[],
            smallProjectsInbox:[],
            projectsInbox:[],
            fixedInbox:[],
            futureInbox:[],
            allocatedUsers:[],
            currentUser:null
        }
        this.apiCurrentActivityService=new SVCCurrentActivityService();
    }
    componentDidMount(){
        this.loadData();
    }
    getTabsElement=()=>
    {
        const {el,isActive,setActiveTab}=this;
       
        return el('div',{key:'tab',className:'tab-container'},[
            el('i',{key:'helpdesk', className:isActive("helpdesk")          ,onClick:()=>setActiveTab("helpdesk")},'Help Desk'),
            el('i',{key:'escalations', className:isActive("escalations")    ,onClick:()=>setActiveTab("escalations")},"Escalations"),
            el('i',{key:'smallProjects', className:isActive("smallProjects"),onClick:()=>setActiveTab("smallProjects")},"Small Projects"),
            el('i',{key:'projects', className:isActive("projects")          ,onClick:()=>setActiveTab("projects")},"Projects"),
            el('i',{key:'sales', className:isActive("sales")                ,onClick:()=>setActiveTab("sales")},"Sales"),
        ])
    }
    isActive=(tab)=>{
        const {activeTab}=this.state;
        if(activeTab===tab)
        return "active";
        else return "";
    }
    setActiveTab=(tab)=>{
        this.setState({activeTab:tab})
    }
    loadData=()=>{
        this.apiCurrentActivityService.getAllocatedUsers().then(res=>this.setState({allocatedUsers:res}));
        this.apiCurrentActivityService.getCurrentUser().then(res=>this.setState({currentUser:res}));
        this.apiCurrentActivityService.getHelpDeskInbox().then(res=>this.setState({helpDeskInbox:res}));
        this.apiCurrentActivityService.getEscalationsInbox().then(res=>this.setState({escalationsInbox:res}));
        this.apiCurrentActivityService.getSalesInbox().then(res=>this.setState({salesInbox:res}) );
        this.apiCurrentActivityService.getSmallProjectsInbox().then(res=>this.setState({smallProjectsInbox:res}));
        this.apiCurrentActivityService.getProjectsInbox().then(res=>this.setState({projectsInbox:res}));
        this.apiCurrentActivityService.getFixedInbox().then(res=>this.setState({fixedInbox:res}));
        this.apiCurrentActivityService.getFutureInbox().then(res=>this.setState({futureInbox:res}));
        

    }
    loadQueue=(code)=>{
        switch(code){
            case "H":
                this.apiCurrentActivityService.getHelpDeskInbox().then(res=>this.setState({helpDeskInbox:res}));
            break;
        }
    }
    render()
    {
        const {el,getTabsElement,isActive,loadQueue}=this;
        const {helpDeskInbox,allocatedUsers,currentUser}=this.state;
        return el('div',{style:{backgroundColor:'white'}}, [ 
            getTabsElement(),
            isActive("helpdesk")?el(CMPInboxHelpDesk,{key:'help',data:helpDeskInbox,allocatedUsers,currentUser,loadQueue:loadQueue}):null
        ]);
    }
}

export default CMPCurrentActivityReport;
const domContainer = document.querySelector("#reactMainCurrentActivity");
ReactDOM.render(React.createElement(CMPCurrentActivityReport), domContainer);
