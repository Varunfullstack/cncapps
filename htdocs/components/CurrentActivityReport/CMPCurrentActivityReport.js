
import CMPInboxHelpDesk from './components/CMPInboxHelpDesk.js?v=1';
import SVCCurrentActivityService from './services/SVCCurrentActivityService.js?v=1';
import Spinner from './../utils/spinner.js?v=9';
import AutoComplete from "./../utils/autoComplete.js?v=1";
import MainComponent from './../CMPMainComponent.js?v=1';
class CMPCurrentActivityReport extends MainComponent{
    el = React.createElement;
    apiCurrentActivityService;
    constructor(props)
    {
        super(props);
        this.state={
            activeTab:'helpdesk',
            helpDeskInbox:[],
            helpDeskInboxFiltered:[],
            escalationsInbox:[],
            salesInbox:[],
            smallProjectsInbox:[],
            projectsInbox:[],
            fixedInbox:[],
            futureInbox:[],
            allocatedUsers:[],
            currentUser:null,
            _showSpinner:false,
            userFilter:'',
        }
        this.apiCurrentActivityService=new SVCCurrentActivityService();
    }
    componentDidMount(){
        this.loadData();
    }
    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };
    getTabsElement=()=>
    {
        const {el,isActive,setActiveTab}=this;
       
        return el('div',{key:'tab',className:'tab-container'},[
            el('i',{key:'helpdesk', className:isActive("helpdesk")          ,onClick:()=>setActiveTab("helpdesk")},'Helpdesk'),
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
        this.loadQueue('H');
        //this.loadQueue('E');
        //this.loadQueue('S');
        //this.loadQueue('SP');
        //this.loadQueue('P');
        //this.apiCurrentActivityService.getFixedInbox().then(res=>this.setState({fixedInbox:res}));
        //this.apiCurrentActivityService.getFutureInbox().then(res=>this.setState({futureInbox:res}));
        

    }
    loadQueue=(code)=>{
      //console.log('load',code);
      if(code)
      {
      this.showSpinner();
        switch(code){
            case "H":
                this.apiCurrentActivityService.getHelpDeskInbox().then(res=>{
                  const helpDeskInbox=this.prepareResult(res);
                  const helpDeskInboxFiltered=[...helpDeskInbox];
                  console.log(helpDeskInbox);
                  this.setState({_showSpinner: false,helpDeskInbox,helpDeskInboxFiltered})
                });
            break;
            case 'E':
                this.apiCurrentActivityService.getEscalationsInbox().then(res=>this.setState({_showSpinner: false,escalationsInbox:res}));
            break;
            case 'S':
                this.apiCurrentActivityService.getSalesInbox().then(res=>this.setState({_showSpinner: false,salesInbox:res}) );
            break;
            case 'SP':
                this.apiCurrentActivityService.getSmallProjectsInbox().then(res=>this.setState({_showSpinner: false,smallProjectsInbox:res}));
                break;
            case 'P':
                this.apiCurrentActivityService.getProjectsInbox().then(res=>this.setState({_showSpinner: false,projectsInbox:res}));
                break;

        }
      }
    }
    // Shared methods
    moveToAnotherTeam = ({ target }, problem,code) => {
        //console.log(target.value, problem, problem.problemStatus);
        let answer = null;
        if (problem.problemStatus === "P") {
          answer = prompt(
            "Please provide a reason for moving this SR into a different queue"
          );
          if (!answer) {
            return;
          }
        }
    
        this.apiCurrentActivityService
          .changeQueue(problem.problemID, target.value, answer)
          .then((res) => {
            //console.log(res);
            if (res && res.status) {
              this.loadQueue(code);
            }
          });
      };
    getMoveElement = (code, problem) => {
        const { el, moveToAnotherTeam } = this;
        let options = [
          { id: 2, title: "E" },
          { id: 3, title: "SP" },
          { id: 5, title: "P" },
          { id: 4, title: "S" },
          { id: 1, title: "H" },
        ];
        options = options.filter((e) => e.title != code);
        return el(
          "select",
          {
            key: "movItem" + problem.callActivityID,
            onChange: (event) => moveToAnotherTeam(event, problem,code),
          },
          [
            el("option", { value: "", key: "null" }),
            options.map((e) => el("option", { value: e.id, key: e.id }, e.title)),
          ]
        );
      };
      srDescription = (problem) => {
        window.open(
          `Activity.php?action=problemHistoryPopup&problemID=${problem.problemID}&htmlFmt=popup`,
          "reason",
          "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
        );
      };
      allocateAdditionalTime = (problem) => {
        //console.log("aalocate");
        window.location = `Activity.php?action=allocateAdditionalTime&problemID=${problem.problemID}`;
      };
      requestAdditionalTime = (problem) => {
        var reason = prompt(
          "Please provide your reason for requesting additional time.(Required)"
        );
        if (!reason) {
          return;
        }
        this.apiCurrentActivityService
          .requestAdditionalTime(problem.problemID, reason)
          .then((res) => {
            if (res.ok) alert("Additional time has been requested");
          });
      };
      startWork = (problem,code) => {
        if (problem.lastCallActTypeID != null) {
          const message =
            "Are you sure you want to start work on this SR? It will be automatically allocated to you UNLESS it is already allocated";
          if (confirm(message)) {
            this.apiCurrentActivityService
              .startActivityWork(problem.callActivityID)
              .then((res) => {            
                  ////console.log(res);
                  //reload
                  this.loadQueue(code);
                
              });
            ////console.log(problem);
          }
        } else {
          alert("Another user is currently working on this SR");
        }
      }; 
      handleUserOnSelect = (event,problem,code) => {
        //console.log(event.target.value,problem);
        const engineerId=event.target.value!=""?event.target.value:0;
        problem.engineerId=engineerId;
        this.apiCurrentActivityService.allocateUser(problem.problemID,engineerId).then(res=>{
          if(res.status)
          {
            this.loadQueue(code)
          }
        })
      };
      getAllocatedElement = (problem,code) => {
        const { el, handleUserOnSelect } = this;
        const { allocatedUsers} = this.state;      
        return el("select", {
          key: "allocatedUser",
          value: problem.engineerId ||'',
          width: 120,
          onChange:(event)=> handleUserOnSelect(event,problem,code),
        },[el('option',{value:'',key:"allOptions"},""),...allocatedUsers.map(p=>el('option',{value:p.userID,key:"option"+p.userID},p.fullName))]);
      }; 
    // end of shared methods
    getProblemWorkTitle(problem){
        let title="";
        if(problem.workBgColor==null)
          title= "Work on this request";
        if(problem.hoursRemainingBgColor=="#FFF5B3")
          title= "Request not started yet";
        if(problem.workBgColor=="#BDF8BA")
          title= "Request being worked on by somebody else";
        return title;
      }
      getProblemWorkColor(problem){
        let color="#C6C6C6";
        if(problem.workBgColor==null)
            color="#C6C6C6";
        if(problem.hoursRemainingBgColor=="#FFF5B3")
            color= "#FFF5B3";
        if(problem.workBgColor=="#BDF8BA")
          color= "#BDF8BA";
        return color;
      }
    prepareResult=(result)=>{
  
        result.map(problem=>{
            problem.workBtnTitle=this.getProblemWorkTitle(problem);
            problem.workBtnColor=this.getProblemWorkColor(problem);            
            problem.alarmDateTime=problem.alarmDateTime?.trim(' ');
            if(moment(problem.alarmDateTime)>moment())
            console.log('Future',problem.problemID);
            //delete problem.alarmDateTime;
            delete problem.date;
            delete problem.engineerDropDown;
            delete problem.esColor;
            delete problem.esRemaining;
            delete problem.linkAllocateAdditionalTime;
            delete problem.projectTeamColor;
            delete problem.projectTeamRemaining;
            delete problem.queueOptions;
            delete problem.slaResponseHours;
            delete problem.smallProjectsTeamColor;
            delete problem.smallProjectsTeamRemaining;
            delete problem.smallProjectsTeamRemaining;
            delete problem.smallProjectsTeamRemaining;
            delete problem.smallProjectsTeamRemaining;
            delete problem.smallProjectsTeamRemaining;
            delete problem.smallProjectsTeamRemaining;
            delete problem.time;
            delete problem.timeSpentColorClass;
            delete problem.totalActivityDurationHours;
            delete problem.updated;
            delete problem.updatedBgColor;
            delete problem.urlCustomer;
            delete problem.urlProblemHistoryPopup;
            delete problem.urlViewActivity;
            delete problem.urlCustomer;
            delete problem.urlCustomer;
            delete problem.workOnClick;
        });
        const old=result.filter(p=> moment(p.alarmDateTime)<=moment());
        const feature=result.filter(p=> moment(p.alarmDateTime)>moment()).sort((a,b)=>moment(a.alarmDateTime)>moment(b.alarmDateTime)?1:-1);

        return [...old,...feature];
    }
    handleUserFilterOnSelect=(event)=>{
      const userFilter=event.target.value;      
      let {helpDeskInbox}=this.state;      
      const helpDeskInboxFiltered=this.filterData(userFilter,helpDeskInbox);
      this.setState({userFilter,helpDeskInboxFiltered});
    }
    filterData=(engineerId,data)=>{
      // //console.log(engineerId,data.map(p=>{
      //   return {"problemID":p.problemID,"engineerId":p.engineerId};
      // }));
      const result=data.filter(p=>p.engineerId===null||p.engineerId==engineerId||engineerId==='' )
      //console.log(result);
      return result;
    }
    getEngineersFilterElement=()=>
    {
      const { el, handleUserFilterOnSelect } = this;
      const { allocatedUsers,userFilter} = this.state;      
      return el("select", {
        style:{marginTop:"20px"},
        className:"float-right",
        key: "userFilter",
        value: userFilter,
        width: 120,
        onChange:(event)=> handleUserFilterOnSelect(event),
      },[el('option',{value:'',key:""},"All engineers"),...allocatedUsers.map(p=>el('option',{value:p.userID,key:"option"+p.userID},p.fullName))]);

    }
    render()
    {
        const {el,getTabsElement,isActive,loadQueue,
        //events
        getMoveElement,
        srDescription,
        allocateAdditionalTime,
        requestAdditionalTime,
        startWork,
        getAllocatedElement,
        getEngineersFilterElement
        }=this;
        const {helpDeskInboxFiltered,allocatedUsers,currentUser,_showSpinner}=this.state;
        //console.log(currentUser);
        return el('div',{style:{backgroundColor:'white'}}, [ 
            el(Spinner, {key: "spinner", show: _showSpinner}),
            getTabsElement(),
            getEngineersFilterElement(),
            isActive("helpdesk")?el(CMPInboxHelpDesk,{key:'help',data:helpDeskInboxFiltered,allocatedUsers,currentUser,
            loadQueue:loadQueue,
            getMoveElement,
            srDescription,
            startWork,
            allocateAdditionalTime,
            requestAdditionalTime,           
            getAllocatedElement}):null,
            
        ]);
    }
}

export default CMPCurrentActivityReport;
const domContainer = document.querySelector("#reactMainCurrentActivity");
ReactDOM.render(React.createElement(CMPCurrentActivityReport), domContainer);
