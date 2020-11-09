import MainComponent from "../CMPMainComponent.js?v=1";
import SVCCurrentActivityService from "../CurrentActivityReport/services/SVCCurrentActivityService.js?v=1";
import Table from "../utils/table/table.js?v=1";
import Toggle from "../utils/toggle.js?v=1";
import ToolTip from "../utils/ToolTip.js?v=1";

import { SRQueues,sort } from "../utils/utils.js?v=1";
import APISDManagerDashboard from "./services/APISDManagerDashboard.js?v=1";
 
class CMPSDManagerDashboard extends MainComponent {
  el = React.createElement;
  tabs = [];
  api = new APISDManagerDashboard();
  apiCurrentActivityService = new SVCCurrentActivityService();
  constructor(props) {
    super(props);
    this.state = {
      filter: {
        hd: false,
        es: false,
        sp: false,
        p: false,
        p5: false,
        activeTab: 1,
        limit:10
      },
      queueData:[],
      allocatedUsers:[]
    };
    this.tabs = [
      { id: 1, title: "Shortest SLA Remaining", showP5: true, icon: null },
      { id: 2, title: "Current Open P1 Requests", showP5: false, icon: null },
      { id: 3, title: "Shortest SLA Fix Remaining", showP5: false, icon: null },
      { id: 4, title: "Critical Service Requests", showP5: false, icon: null },
      { id: 5, title: "Current Open SRs", showP5: true, icon: null },
      { id: 6, title: "Oldest Updated SRs", showP5: true, icon: null },
      { id: 7, title: "Longest Open SR", showP5: true, icon: null },
      { id: 8, title: "Most Hours Logged", showP5: true, icon: null },
      { id: 9, title: "Customer", showP5: false, icon: null },
    ];
  }
  componentDidMount() {
    this.loadFilterFromStorage();
    this.apiCurrentActivityService
      .getAllocatedUsers()
      .then((res) => {
        console.log(res);
        this.setState({ allocatedUsers: res })
      });
  }
  isActive = (code) => {
    const { filter } = this.state;
    if (filter.activeTab === code) return "active";
    else return "";
  };
  setActiveTab = (code) => {
    console.log("tab change");
    const { filter } = this.state;
    filter.activeTab = code;
    this.saveFilter(filter);
    //this.saveFilterToLocalStorage(filter);
    this.setState({ filter,queueData:[] });
  };

  getTabsElement = () => {
    const { el, tabs } = this;
    const { filter } = this.state;
    let tabsFilter = tabs;
    if (filter.p5) tabsFilter = tabs.filter((t) => t.showP5 == true);
    return el(
      "div",
      { key: "tab", className: "tab-container" ,style:{flexWrap:"wrap",justifyContent:"space-between",maxWidth:1200}},
      tabsFilter.map((t) => {
        return el(
          "i",
          {
            key: t.id,
            className: this.isActive(t.id) + " nowrap",
            onClick: () => this.setActiveTab(t.id),
            style:{flex:"3 3 160px",flexBasis:200}
          },
          t.title,
          t.icon
            ? el("span", {
                className: t.icon,
                style: {
                  fontSize: "12px",
                  marginTop: "-12px",
                  marginLeft: "-5px",
                  position: "absolute",
                  color: "#000",
                },
              })
            : null
        );
      })
    );
  };
  loadFilterFromStorage = () => {
    let filter = localStorage.getItem("SDManagerDashboardFilter");
    if (filter) filter = JSON.parse(filter);
    else filter = this.state.filter;    
    this.setState({ filter },()=>this.loadTab(filter.activeTab));
  };
  setFilterValue = (property, value) => {
    const { filter } = this.state;
    filter[property] = value;    
    this.setState({ filter },()=>this.saveFilter(filter));
  };
  saveFilter(filter) {
    localStorage.setItem("SDManagerDashboardFilter", JSON.stringify(filter));
    this.loadTab(filter.activeTab);
  }
  getFilterElement = () => {
    const { el } = this;
    const { filter } = this.state;
    return el(
      "div",
      { className: "m-5" },
      el("label", { className: "mr-3 ml-5" }, "HD"),
      el(Toggle, {
        disabled: false,
        checked: filter.hd,
        onChange: (value) => this.setFilterValue("hd", !filter.hd),
      }),

      el("label", { className: "mr-3 ml-5" }, "ES"),
      el(Toggle, {
        disabled: false,
        checked: filter.es,
        onChange: (value) => this.setFilterValue("es", !filter.es),
      }),

      el("label", { className: "mr-3 ml-5" }, "SP"),
      el(Toggle, {
        disabled: false,
        checked: filter.sp,
        onChange: (value) => this.setFilterValue("sp", !filter.sp),
      }),

      el("label", { className: "mr-3 ml-5" }, "P"),
      el(Toggle, {
        disabled: false,
        checked: filter.p,
        onChange: (value) => this.setFilterValue("p", !filter.p),
      }),

      el("label", { className: "mr-3 ml-5" }, "P5"),
      el(Toggle, {
        disabled: false,
        checked: filter.p5,
        onChange: (value) => this.setFilterValue("p5", !filter.p5),
      }),

      el("label", { className: "mr-3 ml-5" }, "Limit"),
      el('select', {
        value: filter.limit,        
        onChange: (event) => this.setFilterValue("limit", event.target.value),
      },
      el("option",{value:5},5),
      el("option",{value:10},10),
      el("option",{value:15},15),
      el("option",{value:20},20),
      el("option",{value:25},25),
      el("option",{value:30},30),
      )
    );
  };
  loadTab = (id) => {
    const { filter } = this.state;
    this.api.getQueue(id,filter).then((queueData) => {
      console.log(queueData);
      this.setState({queueData})
    });
  };
  getQueueElement=()=>{
      const {filter,queueData}=this.state;
      const {el}=this;
      if(filter.activeTab<9)
      {
      const columns=[
          {
             path: "problemID",
             label: "",
             hdToolTip: "Service Request Number",
             hdClassName: "text-center",
             icon: "fal fa-2x fa-hashtag color-gray2 pointer",
             sortable: false,
             className: "text-center",
             backgroundColorColumn:"bgColour",
             classNameColumn:"",
             content:(problem)=>el('a',{href:`Activity.php?action=displayLastActivity&problemID=${problem.problemID}`,target:'_blank'},problem.problemID)
          },
          {
            path: "customerName",
            label: "",
            hdToolTip: "Customer",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-building color-gray2 pointer",
            sortable: false,
            //className: "text-center",
            classNameColumn:"customerNameDisplayClass",
            //backgroundColorColumn:"bgColour",            
            content:(problem)=>el('a',{href:`SalesOrder.php?action=search&customerID=${problem.customerID}`,target:'_blank'},problem.customerName)
         },
         {
            path: "priority",
            label: "",
            hdToolTip: "Service Request Priority",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-signal color-gray2 pointer",
            sortable: false,
            className: "text-center",
            classNameColumn:"priorityBgColor",
            //backgroundColorColumn:"bgColour",
            
          },
          {
            path: "",
            label: "",
            hdToolTip: "Allocate additional time",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-alarm-plus color-gray2 pointer",
            sortable: false,
            className: "text-center",
            //classNameColumn:"customerNameDisplayClass",
            //backgroundColorColumn:"bgColour",   
            content:(problem)=>el(ToolTip,{title:"Allocate more time",
            content:el('a',{className:"fal fa-2x fa-hourglass-start color-gray inbox-icon", href:`Activity.php?action=allocateAdditionalTime&problemID=${problem.problemID}`,target:'_blank'})
                    }),            
          },
          {
            path: "hoursRemaining",
            label: "",
            hdToolTip: "Open Hours: Green = Awaiting Customer Blue = CNC Yellow = Not Started",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-clock  color-gray2 pointer",
            sortable: false,
            className: "text-center",
            backgroundColorColumn:"hoursRemainingBgColor"
            //backgroundColorColumn:"bgColour",
          },
          {
            path: "totalActivityDurationHours",
            label: "",
            hdToolTip: "Time spent",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-stopwatch color-gray2 pointer",
            sortable: false,
            className: "text-center",            
            //backgroundColorColumn:"bgColour",
          },
          {
            path: "activityCount",
            label: "",
            hdToolTip: "Number Of Activities",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-sigma color-gray2 pointer",
            sortable: false,
            className: "text-center",            
            //backgroundColorColumn:"bgColour",
          },
          {
                
            path: "reason",
            label: "",
            hdToolTip: "Description of the Service Request",
            icon: "fal fa-2x fa-file-alt color-gray2 ",
            sortable: false,
            hdClassName: "text-center",
            content: (problem) =>
            el(
                "a",
                {
                className: "pointer",
                onClick: () => this.srDescription(problem),
                dangerouslySetInnerHTML:{ __html:problem.reason}
                },
                
            ),
         },
         {   
          path: "teamID",
          label: "",
          key: "team",
          hdToolTip: "Team",
          icon: "fal fa-2x fa-users color-gray2 ",
          sortable: false,
          hdClassName: "text-center",
          content: (problem) => el('label',null,this.getTeamCode(problem.teamID)),
        },
         {   
            path: "engineerName",
            label: "",
            key: "assignedUser",
            hdToolTip: "Service Request is assigned to this person",
            icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
            sortable: false,
            hdClassName: "text-center",
            content: (problem) => this.getAllocatedElement(problem, problem.teamID),
          },
          {   
            path: "dateTime",
            label: "",
            key: "dateTime",
            hdToolTip: "Purple = Updated by another user OR has an alarm date in past",
            icon: "fal fa-2x fa-calendar color-gray2 ",
            sortable: false,
            hdClassName: "text-center",
           // content: (problem) => getAllocatedElement(problem, this.code),
           backgroundColorColumn:"updatedBgColor",
          },        
      ]
      return el(Table, {
        key: "queueData",
        data: queueData || [],
        columns: columns,
        pk: "problemID",
        search: true,
      });
    }
    else{
        const columns=[            
            {
              path: "customerName",
              label: "",
              hdToolTip: "Customer",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-building color-gray2 pointer",
              sortable: false,
              //className: "text-center",
              //classNameColumn:"customerNameDisplayClass",
              //backgroundColorColumn:"bgColour",            
              content:(problem)=>el('a',{href:`SalesOrder.php?action=search&customerID=${problem.customerID}`,target:'_blank'},problem.customerName)
           },                
            {
              path: "srCount",
              label: "",
              hdToolTip: "Number Of Activities",
              hdClassName: "text-center",
              icon: "fal fa-2x fa-sigma color-gray2 pointer",
              sortable: false,
              className: "text-center",            
              //backgroundColorColumn:"bgColour",
              content:(problem)=>el('a',{href:`CurrentActivityReport.php?action=setFilter&selectedCustomerID=${problem.customerID}`,target:'_blank'},problem.srCount)

            } 
        ]
        return el('div',{style:{width:500}},
            el(Table, {
            key: "queueData",
            data: queueData || [],
            columns: columns,
            pk: "customerID",
            search: true,
            
            })
        );
    }
  }
  srDescription = (problem) => {
    window.open(
      `Activity.php?action=problemHistoryPopup&problemID=${problem.problemID}&htmlFmt=popup`,
      "reason",
      "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
    );
  };
  getAllocatedElement = (problem, teamId) => {
    const { el } = this;
    const { allocatedUsers } = this.state;    
    const currentTeam = allocatedUsers.filter((u) => u.teamID === teamId);
    const otherTeams = allocatedUsers.filter((u) => u.teamID !== teamId);
    return el(
      "select",
      {
        key: "allocatedUser",
        value: problem.engineerId || "",
        width: 120,
        onChange: (event) => this.handleUserOnSelect(event, problem, teamId),
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
  
  handleUserOnSelect = (event, problem, code) => {
    //console.log(event.target.value,problem);
    const engineerId = event.target.value != "" ? event.target.value : 0;
    problem.engineerId = engineerId;
    this.apiCurrentActivityService
      .allocateUser(problem.problemID, engineerId)
      .then((res) => {
        if (res.status) {
          this.loadTab(this.state.filter.activeTab);
        }
      });
  };
  getTeamCode=(teamID)=>{    
    const queues=SRQueues.filter(q=>q.teamID==teamID);    
    if(queues.length>0)
    return queues[0].code;
    else return ""
  }
  render() {
    const { el } = this;
    return el("div", null, 
    this.getFilterElement(), 
    this.getTabsElement(),
    this.getQueueElement()
    );
  }
}
export default CMPSDManagerDashboard;
 
const domContainer = document.querySelector("#reactMainSDManagerDashboard");
ReactDOM.render(React.createElement(CMPSDManagerDashboard), domContainer);