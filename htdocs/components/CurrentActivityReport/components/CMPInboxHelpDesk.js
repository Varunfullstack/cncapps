import Table from "./../../utils/table/table.js?v=1";
import {Colors} from "./../../utils/utils.js?v=1";

import SVCCurrentActivityService from "./../services/SVCCurrentActivityService.js?v=1";

class CMPInboxHelpDesk extends React.Component {
  code = "H";
  el = React.createElement;
  apiCurrentActivityService;
  constructor(props) {
    super(props);
    this.apiCurrentActivityService = new SVCCurrentActivityService();
  }
  addToolTip=(element,title)=>
  {
    return this.el('div',{className:'tooltip'},element,this.el('div',{className:"tooltiptext tooltip-bottom"},title))
  }
  getTableElement = () => {
    const { el,addToolTip } = this;
    const {
      getMoveElement,
      srDescription,
      allocateAdditionalTime,
      requestAdditionalTime,
      startWork,
      getAllocatedElement,
    } = this.props;
    let columns = [
      {
        hide:false,
        order:1,
        path: null,
        label: "",
        key: "work",
        sortable: false,
        className: "text-center",
        hdClassName:"text-center",
        className:"text-center",                
        //backgroundColorColumn: "workBgColor",
        content: (problem) =>
        addToolTip(
          el('div',{ key: "img1",onClick: () => startWork(problem, this.code),},
          el("i", {
            className:(problem.workBtnColor==="#C6C6C6"? "fal fa-play":"fad fa-play ")+" fa-2x  pointer inbox-icon" + problem.workHidden||'',
            //onClick: () => startWork(problem, this.code),
            //title: problem.workBtnTitle,
            style: {
              color: problem.workBtnColor,
              "--fa-primary-color":
                problem.workBtnColor == "#FFF5B3" ? "gold" : "#32a852",
              "--fa-secondary-color":
                problem.workBtnColor == "#FFF5B3" ? "gray" : "gray",
            },
          })
          )
          ,problem.workBtnTitle)
      },
      {
        hide:false,
        order:2,
        path: null,
        key:"custsomerIcon",
        label: "",
        sortable: false,       
        //classNameColumn: "customerNameDisplayClass",
        toolTip:"Special Attention customer / contact",
        content: (problem) =>          
          problem.customerNameDisplayClass != null
            ? el("i", {
                className: "fal fa-2x fa-star color-gray pointer float-right inbox-icon",
                //title: "Special Attention customer / contact",
                key: "starIcon",
              })
            : null,       
      },
      {
        hide:false,
        order:4.1,
        path: null,
        key:"Future Icon",
        label: "",
        sortable: false,       
        //classNameColumn: "customerNameDisplayClass",
         content: (problem) =>          
          moment(problem.alarmDateTime)>moment()
            ? 
            addToolTip(
            el("i", {
                className: "fal fa-2x fa-alarm-snooze color-gray pointer float-right inbox-icon",
                //title: "Special Attention customer / contact",
                key: "starIcon",
              }),
              `This Service Request is scheduled for the future date of ${moment(problem.alarmDateTime).format("DD/MM/YYYY HH:mm")}`
            )
            : null,       
      },
      {
        hide:false,
        order:10,
        path: null,
        label: "",
        key: "moreTime",
        hdToolTip: "Amount of time left on the Service Request",
        icon: "fal fa-2x fa-stopwatch color-gray2 ",
        width: "40",
        sortable: false,
        hdClassName:"text-center",
        className:"text-center",
        toolTip:"Request more time",
        content: (problem) => [
          el('div',{ key: "img1",onClick: () => requestAdditionalTime(problem),},
          el(
            "i",
            {             
              className: "fal fa-2x fa-hourglass-end color-gray inbox-icon",
              //title: "Request more time",
              style: { cursor: "pointer", width: 20 },
              //onClick: () => requestAdditionalTime(problem),
            },
            ""
          ),
          el(
            "span",
            {
              key: "span1",
              style: {
                // color: problem.hdColor != "green" ? problem.hdColor : "",
              },
            },
            `${problem.hdRemaining}`
          ),) ,
          
          // el(
          //   "span",
          //   { key: "span2", style: { color: problem.esColor } },
          //   ` E:${problem.esRemaining}`
          // ),
          // el(
          //   "span",
          //   { key: "span3", style: { color: problem.smallProjectsTeamColor } },
          //   ` SP:${problem.smallProjectsTeamRemaining}`
          // ),
          // el(
          //   "span",
          //   { key: "span4", style: { color: problem.projectTeamColor } },
          //   ` P:${problem.projectTeamRemaining}`
          // ),
        ],
      },
      {
        hide:false,
        order:3,
        path: null,
        key:"hoursRemainingIcon",
        label: "",
        sortable: false,
        toolTip:"On Hold",
        className:"text-center",
        //backgroundColorColumn: "hoursRemainingBgColor",
        content: (problem) =>
                  problem.hoursRemainingBgColor === "#BDF8BA"
                  ? el("i", {
                      className: "fal  fa-user-clock color-gray pointer inbox-icon",
                      //title: "On Hold",
                      key: "icon",
                      style: { float: "right" },
                    })
                  : null,
        
      },
      
      {
        hide:false,
        order:4,
        path: null,
        key:"problemIdIcon",
        label: "",
        sortable: false,
        className:"text-center",
        toolTip:"SLA Failed for this Service Request",
        //backgroundColorColumn:"bgColour",
        content: (problem) =>          
          problem.bgColour == "#F8A5B6"
            ? el("i", {
                className: "fal fa-2x fa-bell-slash color-gray pointer inbox-icon",
                title: "",
                key: "icon",               
              })
            : null,       
      },
      {
        hide:false,
        order:8,
        path: "hoursRemaining",
        key:"hoursRemainingLabel",
        label: "",
        hdToolTip: "Hours the Service Request has been open",
        icon: "fal fa-2x  fa-clock color-gray2 ",
        sortable: false,
        width: "55",
        hdClassName:"text-center",
        className:"text-center",
        //backgroundColorColumn: "hoursRemainingBgColor",
        content: (problem) => [
          el(
            "label",
            { key: "label", style: { verticalAlign: "middle" } ,
            },
            problem.hoursRemaining
          )          
        ],
      },
      {
        hide:false,
        order:5,
        path: null,
        label: "",
        key: "moverequest",
        hdToolTip: "Move Service Request to another queue",
        icon: "fal fa-2x fa-person-carry color-gray2 ",
        sortable: false,
        hdClassName:"text-center",
        className:"text-center",
        content: (problem) => getMoveElement(this.code, problem),
      },
      {
        hide:false,
        order:6,
        path: "problemID",
        label: "",
        hdToolTip: "Service Request number",
        icon: "fal fa-2x fa-hashtag color-gray2 ",
        sortable: false,
        hdClassName:"text-center",
        className:"text-center",
        //backgroundColorColumn:"bgColour",
        content: (problem) => 
            el(
            "a",
            {
              href: `Activity.php?action=displayLastActivity&problemID=${problem.problemID}`,
              target: "_blank",
              key: "link",
            },
            problem.problemID
          )
        
      },
      {
        hide:false,
        order:7,
        path: "customerName",
        label: "",
        hdToolTip: "Customer",
        icon: "fal fa-2x fa-building color-gray2 ",
        sortable: false,
        width: "220",
        hdClassName:"text-center",
        //classNameColumn: "customerNameDisplayClass",
        content: (problem) => 
          el(
            "a",
            {
              href: `Customer.php?action=dispEdit&customerID=${problem.customerID}`,
              target: "_blank",
              key: "link",
            },
            problem.customerName
          )
        
      },
      {
        hide:false,
        order:11,
        path: "priority",
        label: "",
        hdToolTip: "Service Request Priority",
        icon: "fal fa-2x fa-signal color-gray2 ",
        sortable: false,
        hdClassName:"text-center",
        className:"text-center",
      },

      // {
      //   path: "totalActivityDurationHours",
      //   label: "Time Spent",
      //   sortable: false,
      //   classNameColumn: "timeSpentColorClass",
      // },

      {
        hide:false,
        order:12,
        path: "reason",
        label: "",
        hdToolTip: "Description of the Service Request",
        icon: "fal fa-2x fa-file-alt color-gray2 ",
        sortable: false,
        hdClassName:"text-center",
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
        hide:false,
        order:13,
        path: null,
        label: "",
        key: "assignedUser",
        hdToolTip: "Service Request is assigned to this person",
        icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
        sortable: false,
        hdClassName:"text-center",
         
        content: (problem) => getAllocatedElement(problem, this.code),
      },
      // {
      //   path: "updated",
      //   label: "Updated",
      //   sortable: false,
      //   width:"100",
      //   backgroundColorColumn:"updatedBgColor",
      //   content: (problem) =>
      //     moment(problem.updated).format("DD/MM/YYYY HH:mm"),
      // },
    ];
    if (this.props?.currentUser?.isSDManger)
      columns.push({
        hide:false,
        order:9,
        path: null,
        label: "",
        key: "additionalTime",
        hdToolTip: "Allocate additional time",
        icon: "fal fa-2x fa-alarm-plus color-gray2 ",
        sortable: false,
        hdClassName:"text-center",
        className:"text-center",
        toolTip:"Allocate more time",
        content: (problem) =>
          el('div',{onClick: () => allocateAdditionalTime(problem)},
          el("i", {
            className: "fal fa-2x fa-hourglass-start color-gray inbox-icon",
            //title: "Allocate more time",
            style: { cursor: "pointer" },
            
          })),
      });
    columns=columns.filter(c=>c.hide==false).sort((a,b)=>a.order>b.order?1:-1);
    const { data } = this.props;

    return el(Table, {
      key: "helpDesk",
      data: data || [],
      columns: columns,
      pk: "problemID",
      search: true,
    });
  };

  getSrByUsersSummaryElement = () => {
    const { el } = this;
    const { data } = this.props;
    const future=data.filter(p=>moment(p.alarmDateTime)>moment()).length;
    if (data.length > 0) {
      const items = data
        .reduce((prev, current) => {
          //check index
          const index = prev.findIndex((p) => p.name === current.engineerName);
          if (index == -1) prev.push({ name: current.engineerName, total: 1 });
          else prev[index].total += 1;
          return prev;
        }, [])
        .map((p) => {
          if (p.name != null && p.name != "") {
            p.name = p.name.replace("  ", " ");
            const arr = p.name.split(" ");
            p.name = arr[0][0] + arr[1][0];
          }
          return p;
        })
        .sort((a, b) => (a.name > b.name ? 1 : -1))
        .map((item) => {
            return [
                el("dt", { key: "name" ,style:{paddingLeft:10}},  (item.name||'Unassigned') + ":"),
                el("dd", { key: "total" }, item.total),
            ]
             
        }).concat([
          el("dt", { key: "name" ,style:{paddingLeft:10}},  ('Future') + ":"),
          el("dd", { key: "total" }, future),
      ]);
      //console.log(items);
      return [...[
        el("dt", { key: "nameFuture" ,style:{paddingLeft:10}},  'Total' + ":"),
        el("dt", { key: "totalFuture" }, data.length),
    ],...items];
    }
    return null;
  };

  render() {
    const { el, getTableElement, getSrByUsersSummaryElement } = this;
    const { data } = this.props;
    //console.log(data);

    return [
      el("div", { key: "summary",style:{display:'flex',flexDirection:'row'} }, getSrByUsersSummaryElement()),
      getTableElement(),
    ];
  }
}
export default CMPInboxHelpDesk;
