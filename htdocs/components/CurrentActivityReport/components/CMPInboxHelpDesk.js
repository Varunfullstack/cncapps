import Table from "./../../utils/table/table.js?v=1";
import SVCCurrentActivityService from "./../services/SVCCurrentActivityService.js?v=1";
import AutoComplete from "./../../utils/autoComplete.js?v=1";

class CMPInboxHelpDesk extends React.Component {
  code = "H";
  el = React.createElement;
  apiCurrentActivityService;
  constructor(props) {
    super(props);
    this.apiCurrentActivityService = new SVCCurrentActivityService();
  }
  moveToAnotherTeam = () => {
    const reason = prompt(
      "Please provide a reason for moving this SR into a different queue"
    );
    console.log(reason);
  };
  startWork = (problem) => {
    if (problem.lastCallActTypeID != null) {
      const message =
        "Are you sure you want to start work on this SR? It will be automatically allocated to you UNLESS it is already allocated";
      if (confirm(message)) {
        this.apiCurrentActivityService
          .startActivityWork(problem.callActivityID)
          .then((res) => {
            if (res) {
              console.log(res);
              //reload
            }
          });
        console.log(problem);
      }
    } else {
      alert("Another user is currently working on this SR");
    }
  };
  moveToAnotherTeam = ({ target }, problem) => {
    console.log(target.value, problem, problem.problemStatus);
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
        console.log(res);
        if (res && res.status) {
          this.props.loadQueue(this.code);
        }
      });
  };
  allocateAdditionalTime = (problem) => {
    console.log("aalocate");
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
  srDescription = (problem) => {
    window.open(
      `Activity.php?action=problemHistoryPopup&problemID=${problem.problemID}&htmlFmt=popup`,
      "reason",
      "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
    );
  };
  getTableElement = () => {
    const {
      el,
      startWork,
      getMoveElement,
      allocateAdditionalTime,
      requestAdditionalTime,
      srDescription,
      getAllocatedElement,
    } = this;
    const columns = [
      {
        path: null,
        label: "Work",
        sortable: false,
        backgroundColorColumn: "workBgColor",
        content: (problem) =>
          el("button", { onClick: () => startWork(problem) }, "Work"),
      },
      {
        path: null,
        label: "Move",
        sortable: false,
        content: (problem) => getMoveElement(this.code, problem),
      },
      {
        path: "problemID",
        label: "Id",
        sortable: false,
        backgroundColorColumn:"bgColour",        
        content: (problem) =>
          el(
            "a",
            {
              href: `Activity.php?action=displayLastActivity&problemID=${problem.problemID}`,
              target: "_blank",
            },
            problem.problemID
          ),
      },
      {
        path: "customerName",
        label: "Customer",
        sortable: false,
        classNameColumn:"customerNameDisplayClass",
        content: (problem) =>
          el(
            "a",
            {
              href: `Customer.php?action=dispEdit&customerID=${problem.customerID}`,
              target: "_blank",
            },
            problem.customerName
          ),
      },
      { path: "priority", label: "Priority", sortable: false },
      {
        path: null,
        label: "Aditional Time",
        sortable: false,
        content: (problem) =>
          el("img", {
            src: "/images/clock.png",
            title: "Allocate Aditional Time",
            style: { width: 20, cursor: "pointer" },
            onClick: () => allocateAdditionalTime(problem),
          }),
      },
      {
        path: "hoursRemaining",
        label: "Open Hours",
        sortable: false,
        backgroundColorColumn: "hoursRemainingBgColor",
      },
      {
        path: "totalActivityDurationHours",
        label: "Time Spent",
        sortable: false,
        classNameColumn: "timeSpentColorClass",
      },
      {
        path: null,
        label: "Time Budget",
        sortable: false,
        content: (problem) => [
          el("img", {
            src: "/images/clock.png",
            title: "Allocate Aditional Time",
            style: { width: 20, cursor: "pointer" },
            onClick: () => requestAdditionalTime(problem),
          }),
          el(
            "span",
            { key: "span1", style: { color: problem.hdColor } },
            `H:${problem.hdRemaining}`
          ),
          el(
            "span",
            { key: "span2", style: { color: problem.esColor } },
            ` E:${problem.esRemaining}`
          ),
          el(
            "span",
            { key: "span3", style: { color: problem.smallProjectsTeamColor } },
            ` SP:${problem.smallProjectsTeamRemaining}`
          ),
          el(
            "span",
            { key: "span4", style: { color: problem.projectTeamColor } },
            ` P:${problem.projectTeamRemaining}`
          ),
        ],
      },
      {
        path: "reason",
        label: "Description",
        sortable: false,
        width:"350",
        content: (problem) =>
          el(
            "a",
            {
              className: "pointer",
              onClick: () => srDescription(problem),
            },
            problem.reason
          ),
      },
      {
        path: null,
        label: "Assigned To",
        sortable: false,
        content: (problem) => getAllocatedElement(problem),
      },
      {
        path: "updated",
        label: "Updated",
        sortable: false,
        width:"100",
        backgroundColorColumn:"updatedBgColor",
        content: (problem) =>
          moment(problem.updated).format("DD/MM/YYYY HH:mm"),
      },
    ];
    const { data } = this.props;

    return el(Table, {
      key: "helpDesk",
      data: data || [],
      columns: columns,      
      pk: "problemID",
      search: true,
    });
  };
  getAllocatedElement = (problem) => {
    const { el, handleUserOnSelect } = this;
    const { allocatedUsers, currentUser } = this.props;
    return el(AutoComplete, {
      key: "allocatedUser",
      errorMessage: "No User Found",
      items: allocatedUsers,
      displayColumn: "fullName",
      pk: "userID",
      value: problem.engineerName || null,
      width: 100,
      onSelect:(event)=> handleUserOnSelect(event,problem),
    });
  };
  handleUserOnSelect = (user,problem) => {
    console.log(user,problem);
    this.apiCurrentActivityService.allocateUser(problem.problemID,user?.userID||0).then(res=>{
      if(res.status)
      {
        this.props.loadQueue(this.code)
      }
    })
  };
  getMoveElement = (current, problem) => {
    const { el, moveToAnotherTeam } = this;
    let options = [
      { id: 2, title: "E" },
      { id: 3, title: "SP" },
      { id: 5, title: "P" },
      { id: 4, title: "S" },
      { id: 1, title: "H" },
    ];
    options = options.filter((e) => e.title != current);
    return el(
      "select",
      {
        key: "movItem" + problem.callActivityID,
        onChange: (event) => moveToAnotherTeam(event, problem),
      },
      [
        el("option", { value: "", key: "null" }),
        options.map((e) => el("option", { value: e.id, key: e.id }, e.title)),
      ]
    );
  };
  render() {
    const { el, getTableElement } = this;
    const { data } = this.props;
    console.log(data);
    return getTableElement();
  }
}
export default CMPInboxHelpDesk;
