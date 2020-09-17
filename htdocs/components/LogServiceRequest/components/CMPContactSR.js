
import Table from "./../../utils/table/table.js?v=1";

class CMPContactSR extends React.Component {
    el=React.createElement;
    constructor(props) {
        super(props);
        this.state = {  }
    }
    getTableElement=(items)=>{
        const { el, addToolTip } = this;
        const {openProblemHistory,newSrActivity } = this.props;
        let columns = [
            {
                hide: false,
                order: 1,
                path: null,
                key: "newSr",
                label: "",
                sortable: false,
                //classNameColumn: "customerNameDisplayClass",
                toolTip: "Log Activity",
                content: (problem) =>
                   el("i", {
                        className:
                          "fal fa-2x fa-play color-gray pointer float-right inbox-icon",
                        //title: "Special Attention customer / contact",
                        key: "starIcon",
                        onClick:()=>newSrActivity(problem.contactProblemID,problem.contactActivityID)
                      })             
              },
            {
                hide: false,
                order: 1.1,
                path: null,
                key: "custsomerIcon",
                label: "",
                sortable: false,
                //classNameColumn: "customerNameDisplayClass",
                toolTip: "Special Attention contact",
                content: (problem) =>
                  problem.contactPriority===1
                    ? el("i", {
                        className:
                          "fal fa-2x fa-exclamation-triangle color-gray pointer float-right inbox-icon",
                        //title: "Special Attention customer / contact",
                        key: "starIcon",
                      })
                    : null,
              },
          {
            hide: false,
            order:1.2,
            path: "contactProblemID",
            label: "",
            key: "problemId",
            hdToolTip: "Problem Id",
            icon: "fal fa-2x fa-hashtag  color-gray2 ",             
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",            
        },
          {
            hide: false,
            order: 2,
            path: "contactDateRaised",
            label: "",
            key: "contactDateRaised",
            hdToolTip: "Raised Date",
            icon: "fal fa-2x fa-calendar-alt color-gray2 ",             
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",      
              },
          {
            hide: false,
            order: 3,
            path: "contactReason",
            label: "",
            key: "activityId",
            hdToolTip: "Reason",
            icon: "fal fa-2x fa-file-alt color-gray2 ",             
            sortable: false,
            hdClassName: "text-center",
            content:(problem)=>el('div',{className:"pointer",style:{color:'blue'},onClick:()=>openProblemHistory(problem.contactProblemID), dangerouslySetInnerHTML:{__html:problem.contactReason}}),
        },
          {
            hide: false,
            order: 4,
            path: "contactPriority",
            label: "",
            hdToolTip: "Service Request Priority",
            icon: "fal fa-2x fa-signal color-gray2 ",
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",     
        },
          {
            hide: false,
            order: 5,
            path: "contactEngineerName",
            label: "",
            hdToolTip: "Allocated To",
            icon: "fal fa-2x fa-user-hard-hat color-gray2 ",
            sortable: false,
            hdClassName: "text-center",
            className: "text-center",     
        }
        ];        
        columns = columns
          .filter((c) => c.hide == false)
          .sort((a, b) => (a.order > b.order ? 1 : -1));
     
        return el(Table, {
          key: "Sr",
          data: items || [],
          columns: columns,
          pk: "contactActivityID",
          search: true,
        });
    }
    render() { 
        const {items}=this.props;
        const {getTableElement,el}=this;
        console.log(items.length);
        return (  
            el('div',null,getTableElement(items))
        );
    }
}
 
export default CMPContactSR;