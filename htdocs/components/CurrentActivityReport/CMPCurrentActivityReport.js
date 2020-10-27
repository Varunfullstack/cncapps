
// tabs components
import CMPInboxHelpDesk from './components/CMPInboxHelpDesk.js?v=1';
import CMPInboxEscalations from './components/CMPInboxEscalations.js?v=1';
import CMPInboxSmallProjects from './components/CMPInboxSmallProjects.js?v=1';
import CMPInboxSales from './components/CMPInboxSales.js?v=1';
import CMPInboxProjects from './components/CMPInboxProjects.js?v=1';
import CMPInboxToBeLogged from './components/CMPInboxToBeLogged.js?v=1';
import CMPInboxPendingReopened from './components/CMPInboxPendingReopened.js?v=1';
import SVCCurrentActivityService from './services/SVCCurrentActivityService.js?v=1';
import Spinner from './../utils/spinner.js?v=9';
import MainComponent from './../CMPMainComponent.js?v=1';
class CMPCurrentActivityReport extends MainComponent {
  el = React.createElement;
  apiCurrentActivityService;
  teams;
  constructor(props) {
    super(props);
    const filter= this.getLocalStorageFilter();
    this.state = {
      ...this.state,
      helpDeskInbox: [],
      helpDeskInboxFiltered: [],
      escalationInbox: [],
      escalationInboxFiltered: [],
      salesInbox: [],
      salesInboxFiltered: [],
      smallProjectsInbox: [],
      smallProjectsInboxFiltered: [],
      projectsInbox: [],
      projectsInboxFiltered: [],
      toBeLoggedInbox:[],
      toBeLoggedInboxFiltered:[],
      pendingReopenedInbox:[],
      pendingReopenedInboxFiltered:[],
      fixedInbox: [],
      futureInbox: [],
      allocatedUsers: [],
      currentUser: null,
      _showSpinner: false,
      userFilter: "",
      filter,
    };
    this.apiCurrentActivityService = new SVCCurrentActivityService();
    this.teams=[
      {id:1,title:'Helpdesk',code:'H',queueNumber:1,order:1,display:true,icon:null,canMove:true},
      {id:2,title:'Escalations',code:'E',queueNumber:2,order:2,display:true,icon:null,canMove:true},
      {id:4,title:'Small Projects',code:'SP',queueNumber:3,order:3,display:true,icon:null,canMove:true},
      {id:5,title:'Projects',code:'P',queueNumber:5,order:4,display:true,icon:null,canMove:true},
      {id:7,title:'Sales',code:'S',queueNumber:4,order:5,display:true,icon:null,canMove:true},
      {id:10,title:'To Be Logged',code:'TBL',queueNumber:10,order:6,display:true,icon:null,canMove:false},
      {id:11,title:'Pending Reopen',code:'PR',queueNumber:11,order:7,display:true,icon:null,canMove:false},      
    ]
  }
  componentDidMount() {
    this.loadData();
  }
  showSpinner = () => {
    this.setState({ _showSpinner: true });
  };
  hideSpinner = () => {
    this.setState({ _showSpinner: false });
  };
  getTabsElement = () => {
    const { el, isActive, setActiveTab ,teams} = this;    
    return el("div", { key: "tab", className: "tab-container" }, teams.sort((a,b)=>a.order>b.order?1:-1).map(t=>{
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
    const { filter } = this.state;
    if (filter.activeTab === code) return "active";
    else return "";
  };
  setActiveTab = (code) => {
    console.log("tab change");
    const { filter } = this.state;
    filter.activeTab = code;
    this.loadQueue(code);
    this.saveFilterToLocalStorage(filter);
    this.setState({ filter });
  };
  loadData = () => {
    const {filter}=this.state;
    this.apiCurrentActivityService
      .getAllocatedUsers()
      .then((res) => this.setState({ allocatedUsers: res }));
    this.apiCurrentActivityService
      .getCurrentUser()
      .then((res) => {
        if(res.isSDManger)
        this.teams.filter(t=>t.id==11)[0].display=true;
        this.setState({ currentUser: res })
      });    
    this.loadQueue(filter.activeTab);
    this.loadQueue('TBL');
    setInterval(()=> {
      this.loadQueue('TBL');
    }, 4*60*1000);
    this.loadQueue('PR');
    setInterval(()=> {
      this.loadQueue('PR');
    }, 4*60*1000);    
  
  };
  
  getLocalStorageFilter=()=>{
    let filter=localStorage.getItem("inboxFilter");
    if(filter)
      filter=JSON.parse(filter);
    else
      filter={ activeTab: "H", userFilter: "" };
    console.log('filter',filter);
    //this.setState({filter});
    return filter;
  }
  saveFilterToLocalStorage=(filter)=>{
    localStorage.setItem("inboxFilter",JSON.stringify(filter));
  }
  loadQueue = (code) => {
    //console.log('load',code);
    const {handleUserFilterOnSelect}=this;
    const {filter}=this.state;
    if (code) {
      this.showSpinner();
      switch (code) {
        case "H":
          this.apiCurrentActivityService.getHelpDeskInbox().then((res) => {
            const helpDeskInbox = this.prepareResult(res);
            const helpDeskInboxFiltered = [...helpDeskInbox];
            //console.log("helpDeskInbox", helpDeskInbox.length,res.length);
            this.setState({
              _showSpinner: false,
              helpDeskInbox,              
            },()=>handleUserFilterOnSelect(filter.userFilter));
          });
          break;
        case "E":
          this.apiCurrentActivityService.getEscalationsInbox().then((res) => {
            const escalationInbox = this.prepareResult(res);
            const escalationInboxFiltered = [...escalationInbox];
            console.log("escalationInboxFiltered", escalationInboxFiltered);
            this.setState({
              _showSpinner: false,
              escalationInbox,             
            },()=>handleUserFilterOnSelect(filter.userFilter));
          });
          break;
        case "S":
          this.apiCurrentActivityService.getSalesInbox().then((res) => {
            const salesInbox = this.prepareResult(res);
            const salesInboxFiltered = [...salesInbox];
            console.log("salesInboxFiltered", salesInboxFiltered);
            
            this.setState({
              _showSpinner: false,
              salesInbox,              
            },()=>handleUserFilterOnSelect(filter.userFilter));
          });
          break;
        case "SP":
          this.apiCurrentActivityService.getSmallProjectsInbox().then((res) => {
            const smallProjectsInbox = this.prepareResult(res);
            const smallProjectsInboxFiltered = [...smallProjectsInbox];
            // console.log(
            //   "smallProjectsInboxFiltered",
            //   smallProjectsInboxFiltered
            // );
            this.setState({
              _showSpinner: false,
              smallProjectsInbox,          
            },()=>handleUserFilterOnSelect(filter.userFilter));
          });
          break;
        case "P":
          this.apiCurrentActivityService.getProjectsInbox().then((res) => {
            const projectsInbox = this.prepareResult(res);
            const projectsInboxFiltered = [...projectsInbox];
            //console.log("projectsInboxFiltered", projectsInboxFiltered);
            this.setState({
              _showSpinner: false,
              projectsInbox,            
            },()=>handleUserFilterOnSelect(filter.userFilter));
          });
          break;
          case "TBL":
            this.apiCurrentActivityService.getToBeLoggedInbox().then((res) => {
              const toBeLoggedInbox = this.prepareResult(res);
              const toBeLoggedInboxFiltered = [...toBeLoggedInbox];
              //console.log("toBeLoggedInboxFiltered", toBeLoggedInboxFiltered);
              console.log(toBeLoggedInboxFiltered.length);
              if(toBeLoggedInboxFiltered.length>0)
                this.teams.filter(t=>t.code==='TBL')[0].icon="fal fa-asterisk";
              else
                this.teams.filter(t=>t.code==='TBL')[0].icon=null;

              this.setState({
                _showSpinner: false,
                toBeLoggedInbox,            
              },()=>handleUserFilterOnSelect(filter.userFilter));
            });
            break;
          case "PR":
            this.apiCurrentActivityService.getPendingReopenedInbox().then((res) => {
              const pendingReopenedInbox = this.prepareResult(res);
              const pendingReopenedInboxFiltered = [...pendingReopenedInbox];
              //console.log("pendingReopenedInboxFiltered", pendingReopenedInboxFiltered);
              if(pendingReopenedInboxFiltered.length>0)
                this.teams.filter(t=>t.code==='PR')[0].icon="fal fa-asterisk";
              else
                this.teams.filter(t=>t.code==='PR')[0].icon=null;
              this.setState({
                _showSpinner: false,
                pendingReopenedInbox,            
              },()=>handleUserFilterOnSelect(filter.userFilter));
            });
            break;
      }
    }
  };
  // Shared methods
  moveToAnotherTeam = async({ target }, problem, code) => {
    //console.log(target.value, problem, problem.problemStatus);
    let answer = null;
    if (problem.problemStatus === "P") {
      answer =await this.prompt(
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
  /**
   * Move to another queue
   */
  getMoveElement = (code, problem) => {    
    const { el, moveToAnotherTeam,teams } = this;
    let options = teams.map(t=>{return { id: t.queueNumber, title: t.code ,canMove:t.canMove}})
                        .filter((e) => e.title != code&&e.canMove==true);    
    return el(
      "select",
      {
        key: "movItem" + problem.callActivityID,
        onChange: (event) => moveToAnotherTeam(event, problem, code),
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
  srCustomerDescription=(problem)=>{
    window.open(
      `Activity.php?action=customerProblemPopup&customerProblemID=${problem.cpCustomerProblemID}&htmlFmt=popup`,
      "reason",
      "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
    ); 
  }
  allocateAdditionalTime = (problem) => {
    //console.log("aalocate");
    window.location = `Activity.php?action=allocateAdditionalTime&problemID=${problem.problemID}`;
  };
  requestAdditionalTime = async(problem) => {
    var reason = await this.prompt(
      "Please provide your reason for requesting additional time.(Required)"
    );
    if (!reason) {
      return;
    }
    this.apiCurrentActivityService
      .requestAdditionalTime(problem.problemID, reason)
      .then((res) => {
        if (res.ok) this.alert("Additional time has been requested");
      });
  };
  startWork = async(problem, code) => {
    if (problem.lastCallActTypeID != null) {
      const message =
        "Are you sure you want to start work on this SR? It will be automatically allocated to you UNLESS it is already allocated";
      if (await this.confirm(message)) {
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
      this.alert("Another user is currently working on this SR");
    }
  };
  handleUserOnSelect = (event, problem, code) => {
    //console.log(event.target.value,problem);
    const engineerId = event.target.value != "" ? event.target.value : 0;
    problem.engineerId = engineerId;
    this.apiCurrentActivityService
      .allocateUser(problem.problemID, engineerId)
      .then((res) => {
        if (res.status) {
          this.loadQueue(code);
        }
      });
  };
  getAllocatedElement = (problem, code) => {
    const { el, handleUserOnSelect } = this;
    const { allocatedUsers } = this.state;
    //console.log(allocatedUsers);
    // order allocated users team then alpha
    //"technical,sales,accountManagement,reports,renewals,accounts,maintenance,seniorManagement,supervisor"
    const teamId = this.getTeamId(code);
    const currentTeam = allocatedUsers.filter((u) => u.teamID === teamId);
    const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);
    return el(
      "select",
      {
        key: "allocatedUser",
        value: problem.engineerId || "",
        width: 120,
        onChange: (event) => handleUserOnSelect(event, problem, code),
      },
      [
        el("option", { value: "", key: "allOptions" }, ""),
        ...[...currentTeam, ...otherTeams].map((p) =>
          el(
            "option",
            {
              value: p.userID,
              key: "option" + p.userID,
              className: teamId === p.teamID ? "in-team" : "",
            },
            p.fullName
          )
        ),
      ]
    );
  };
  getTeamId(code) {
    //console.log(code);
    return this.teams.filter(t=>t.code===code)[0].id;    
  }
  // end of shared methods
  getProblemWorkTitle(problem) {
    let title = "";
    if (problem.workBgColor == null) title = "Work on this request";
    if (problem.hoursRemainingBgColor == "#FFF5B3")
      title = "Request not started yet";
    if (problem.workBgColor == "#BDF8BA")
      title = "Request being worked on by somebody else";
    return title;
  }
  getProblemWorkColor(problem) {
    let color = "#C6C6C6";
    if (problem.workBgColor == null) color = "#C6C6C6";
    if (problem.hoursRemainingBgColor == "#FFF5B3") color = "#FFF5B3";
    if (problem.workBgColor == "#BDF8BA") color = "#BDF8BA";
    return color;
  }
  prepareResult = (result) => {
    result.map((problem) => {
      problem.workBtnTitle = this.getProblemWorkTitle(problem);
      problem.workBtnColor = this.getProblemWorkColor(problem);
      problem.alarmDateTime = problem.alarmDateTime?.trim(" ");
      if (moment(problem.alarmDateTime) > moment())
        console.log("Future", problem.problemID);
      //delete problem.alarmDateTime;
      delete problem.date;
      delete problem.engineerDropDown;
      //delete problem.esColor;
      //delete problem.esRemaining;
      delete problem.linkAllocateAdditionalTime;
      //delete problem.projectTeamColor;
      //delete problem.projectTeamRemaining;
      delete problem.queueOptions;
      delete problem.slaResponseHours;
      //delete problem.smallProjectsTeamColor;
      //delete problem.smallProjectsTeamRemaining;
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
    const emptyAlarm = result.filter((p) =>p.alarmDateTime ==null||p.alarmDateTime =='');
    const old = result.filter((p) => moment(p.alarmDateTime) <= moment());
    const feature = result
      .filter((p) => moment(p.alarmDateTime) > moment())
      .sort((a, b) =>
        moment(a.alarmDateTime) > moment(b.alarmDateTime) ? 1 : -1
      );
    //console.log(result.length,old.length,emptyAlarm.length,feature.length);
    return [...old, ...emptyAlarm, ...feature];
  };
  handleUserFilterOnSelect = (userId) => {
    //console.log("save User",userId);
    const userFilter = userId;
    let {
      helpDeskInbox,
      smallProjectsInbox,
      projectsInbox,
      salesInbox,
      escalationInbox,
      toBeLoggedInbox,
      pendingReopenedInbox,
      filter,
    } = this.state;
    filter.userFilter = userFilter;
    const helpDeskInboxFiltered = this.filterData(userFilter, helpDeskInbox);
    const smallProjectsInboxFiltered = this.filterData(
      userFilter,
      smallProjectsInbox
    );
    const projectsInboxFiltered = this.filterData(userFilter, projectsInbox);
    const salesInboxFiltered = this.filterData(userFilter, salesInbox);
    const escalationInboxFiltered = this.filterData(
      userFilter,
      escalationInbox
    );
    const toBeLoggedInboxFiltered= this.filterData(
      userFilter,
      toBeLoggedInbox
    );
    const pendingReopenedInboxFiltered=this.filterData(
      userFilter,
      pendingReopenedInbox
    );
    this.saveFilterToLocalStorage(filter);
    this.setState({
      filter,
      helpDeskInboxFiltered,
      smallProjectsInboxFiltered,
      projectsInboxFiltered,
      salesInboxFiltered,
      escalationInboxFiltered,
      toBeLoggedInboxFiltered,
      pendingReopenedInboxFiltered
    });
  };
  filterData = (engineerId, data) => {
    // //console.log(engineerId,data.map(p=>{
    //   return {"problemID":p.problemID,"engineerId":p.engineerId};
    // }));
    const result = data.filter(
      (p) =>
        p.engineerId === null || p.engineerId == engineerId || engineerId === ""
    );
    //console.log(result);
    return result;
  };
  getEngineersFilterElement = () => {
    const { el, handleUserFilterOnSelect, } = this;
    const { allocatedUsers, filter } = this.state;

    let code =filter.activeTab;   
    const teamId = this.getTeamId(code);
    const currentTeam = allocatedUsers.filter((u) => u.teamID === teamId);
    const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);
    
    return el(
      "select",
      {
        style: { marginTop: "20px", marginRight: "8px" },
        className: "float-right",
        key: "userFilter",
        value: filter.userFilter,
        width: 120,
        onChange: (event) => handleUserFilterOnSelect(event.target.value),
      },
      [
        el("option", { value: "", key: "" }, "All engineers"),
        ...[...currentTeam, ...otherTeams].map((p) =>
          el(
            "option",
            {
              value: p.userID,
              key: "option" + p.userID,
              className: teamId === p.teamID ? "in-team" : "",
            },
            p.fullName
          )
        ),
      ]
    );
  };
  deleteSR=(problem,code)=>{
    console.log('delete',problem);
    this.apiCurrentActivityService.deleteSR(problem.cpCustomerProblemID).then(res=>{
      this.loadQueue(code);
      console.log(res);
    },error=>{
      console.log(error);
      this.alert("You don't have permission to delete this SR or somthing wrong")})
  }
  createNewSR=(problem,code)=>{
    //console.log('create new ',problem);
    window.location=`LogServiceRequest.php?customerproblemno=${problem.cpCustomerProblemID}`    
    //window.location=`Activity.php?action=createRequestFromCustomerRequest&cpr_customerproblemno=${problem.cpCustomerProblemID}`    

  }
  render() {
    const {
      el,
      getTabsElement,
      isActive,
      loadQueue,
      //events
      getMoveElement,
      srDescription,
      allocateAdditionalTime,
      requestAdditionalTime,
      startWork,
      getAllocatedElement,
      getEngineersFilterElement,
      deleteSR,
      createNewSR,
      srCustomerDescription,
      
    } = this;
    const {
      helpDeskInboxFiltered,
      escalationInboxFiltered,
      smallProjectsInboxFiltered,
      salesInboxFiltered,
      projectsInboxFiltered,
      toBeLoggedInboxFiltered,
      pendingReopenedInboxFiltered,
      allocatedUsers,
      currentUser,
      _showSpinner,
      filter,
      
    } = this.state;
    //console.log(currentUser);
    return el("div", { style: { backgroundColor: "white" } }, [
      this.getConfirm(),
      this.getAlert(),
      this.getPrompt(),
      el(Spinner, { key: "spinner", show: _showSpinner }),
      getTabsElement(),
      filter.activeTab!=='TBL'&&filter.activeTab!=="PR"?getEngineersFilterElement():null,
      isActive("H")
        ? el(CMPInboxHelpDesk, {
            key: "help",
            data: helpDeskInboxFiltered,
            allocatedUsers,
            currentUser,
            loadQueue: loadQueue,
            getMoveElement,
            srDescription,
            startWork,
            allocateAdditionalTime,
            requestAdditionalTime,
            getAllocatedElement,
          })
        : null,

      isActive("E")
        ? el(CMPInboxEscalations, {
            key: "escalation",
            data: escalationInboxFiltered,
            allocatedUsers,
            currentUser,
            loadQueue: loadQueue,
            getMoveElement,
            srDescription,
            startWork,
            allocateAdditionalTime,
            requestAdditionalTime,
            getAllocatedElement,
          })
        : null,

      isActive("SP")
        ? el(CMPInboxSmallProjects, {
            key: "smallProjects",
            data: smallProjectsInboxFiltered,
            allocatedUsers,
            currentUser,
            loadQueue: loadQueue,
            getMoveElement,
            srDescription,
            startWork,
            allocateAdditionalTime,
            requestAdditionalTime,
            getAllocatedElement,
          })
        : null,

      isActive("S")
        ? el(CMPInboxSales, {
            key: "salesInbox",
            data: salesInboxFiltered,
            allocatedUsers,
            currentUser,
            loadQueue: loadQueue,
            getMoveElement,
            srDescription,
            startWork,
            allocateAdditionalTime,
            requestAdditionalTime,
            getAllocatedElement,
          })
        : null,

      isActive("P")
        ? el(CMPInboxProjects, {
            key: "projects",
            data: projectsInboxFiltered,
            allocatedUsers,
            currentUser,
            loadQueue: loadQueue,
            getMoveElement,
            srDescription,
            startWork,
            allocateAdditionalTime,
            requestAdditionalTime,
            getAllocatedElement,
          })
        : null,

        isActive("TBL")
        ? el(CMPInboxToBeLogged, {
            key: "toBeLogged",
            data: toBeLoggedInboxFiltered,
            deleteSR,
            createNewSR,
            srCustomerDescription,
          })
        : null,
        
        isActive("PR")
        ? el(CMPInboxPendingReopened, {
            key: "pendingReopend",
            data: pendingReopenedInboxFiltered,
            deleteSR,
            createNewSR,
            srCustomerDescription,
            loadQueue
          })
        : null,
    ]);
  }
}

export default CMPCurrentActivityReport;
const domContainer = document.querySelector("#reactMainCurrentActivity");
ReactDOM.render(React.createElement(CMPCurrentActivityReport), domContainer);
