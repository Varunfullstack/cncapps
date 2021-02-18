import CurrentActivityService from "../services/CurrentActivityService";
import React, { Fragment } from "react";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import APIStandardText from "../../services/APIStandardText";
import MainComponent from "../../shared/MainComponent";
import APICustomers from "../../services/APICustomers";
import Table from "../../shared/table/table";
class CallBackComponent extends MainComponent {
  apiCurrentActivityService = new CurrentActivityService();
  constructor(props) {
    super(props);
    this.state={         
        callbacks:[]
    }
   }
  componentDidMount() {
  this.apiCurrentActivityService.getMyCallback().then(callbacks=>{
    console.log('callbacks',callbacks);
    this.setState({callbacks});
  });
  }
   
  getContent=()=>{
      const { callbacks} = this.state;
      const columns=[
          {
             path: "problemID",
             label: "",
             hdToolTip: "Service Request",
             hdClassName: "text-center",
             icon: "fal fa-2x fa-hashtag color-gray2 pointer",
             sortable: true,
             className: "text-center",         
             content:(problem)=><a href={`SRActivity.php?action=displayActivity&serviceRequestId=${problem.problemID}`} target="_blank">{problem.problemID}</a>
          },
          {
            path: "contactName",
            label: "",
            hdToolTip: "Contact",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-phone color-gray2 pointer",
            sortable: true,
            //className: "text-center",         
          },
          {
            path: "customerName",
            label: "",
            hdToolTip: "Customer",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-building color-gray2 pointer",
            sortable: true,
            //className: "text-center",         
          },
          {
            path: "DESCRIPTION",
            label: "",
            hdToolTip: "Call back date time",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-file-alt color-gray2 pointer",
            sortable: true,
            className: "text-center",         
          },
          {
            path: "callback_datetime",
            label: "",
            hdToolTip: "Call back date time",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-hourglass color-gray2 pointer",
            sortable: true,
            content:(problem)=><div>{this.getCorrectDate(problem.callback_datetime,true)}</div>,
            className: "text-center",         
          },
          {
            path: "timeRemainIcon",
            label: "",
            hdToolTip: "Time Remain",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
            sortable: false,
            width:30,
            content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>
            <ToolTip title="Time Remain" width={30}>
                <i className="fal fa-2x fa-hourglass color-gray2 pointer"></i>               
            </ToolTip>            
            </div>,
            className: "text-center",         
          },
          {
            path: "timeRemain",
            label: "",
            hdToolTip: "Time Remain",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
            sortable: false,             
            //className: "text-center",         
          },
          {
            path: "",
            label: "",
            hdToolTip: " ",
            hdClassName: "text-center",
             
            sortable: false,
            content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>
                {problem.timeRemain<0?
            <ToolTip title="Call back time expired" width={30}>
                <i className="fal fa-2x fa-exclamation-triangle color-gray2 pointer"></i>               
            </ToolTip>:null}            
            </div>,
            className: "text-center",         
          },
      ];

      return <div style={{width:800}}>
          <h3>Call back</h3>
          <Table
      
      key="callback"
      data={callbacks||[]}
      pk="id"
      columns={columns}
      search={false}
      >
      </Table>
          </div>
  }
   
  render() {    
    return this.getContent();    
  }
}

export default CallBackComponent;
