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
  dataInterval;
  constructor(props) {
    super(props);
    this.state={         
      ...this.state,
        callbacks:[]
    }
   }
  componentDidMount() {
    this.getData()
    this.dataInterval=setInterval(()=> this.getData(),1000*30)
  }
  componentWillUnmount() {
    if( this.dataInterval)
    clearTimeout( this.dataInterval);
  }
  getData=()=>{
    this.apiCurrentActivityService.getMyCallback()
    .then(callbacks=>{
      console.log('callbacks',callbacks);
      this.setState({callbacks});
    });
  }
  getContent=()=>{
      const { callbacks} = this.state;
      const columns=[
        {
          path: "customerContact",
          label: "",
          hdToolTip: " ",
          hdClassName: "text-center",
           
          sortable: false,
          content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>             
          <ToolTip title="Create customer contact" width={30}>
              <i className="fal fa-2x fa-phone color-gray2 pointer" onClick={()=>this.createCustomerContact(problem)}></i>               
          </ToolTip>             
          </div>,
          className: "text-center",         
        },
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
            path: "customerName",
            label: "",
            hdToolTip: "Customer",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-building color-gray2 pointer",
            sortable: true,
            //className: "text-center",         
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
          // {
          //   path: "timeRemainIcon",
          //   label: "",
          //   hdToolTip: "Time Remain",
          //   hdClassName: "text-center",
          //   icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
          //   sortable: false,
          //   width:30,
          //   content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>
          //   <ToolTip title="Time Remain" width={30}>
          //       <i className="fal fa-2x fa-hourglass color-gray2 pointer"></i>               
          //   </ToolTip>            
          //   </div>,
          //   className: "text-center",         
          // },
          // {
          //   path: "timeRemain",
          //   label: "",
          //   hdToolTip: "Time Remain",
          //   hdClassName: "text-center",
          //   icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
          //   sortable: false,             
          //   //className: "text-center",         
          // },
          {
            path: "",
            label: "",
            hdToolTip: " ",
            hdClassName: "text-center",
             
            sortable: false,
            content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>
                {problem.timeRemain<0?
            <ToolTip title="Call back time expired" width={30}>
                <i className="fal fa-2x fa-alarm-exclamation color-gray2 pointer"></i>               
            </ToolTip>:null}            
            </div>,
            className: "text-center",         
          },
          {
            path: "CancelCallback",
            label: "",
            hdToolTip: " ",
            hdClassName: "text-center",
             
            sortable: false,
            content:(problem)=><div className="flex-row" style={{justifyContent:"center"}}>              
            <ToolTip title="Cancel call back" width={30}>
                <i className="fal fa-2x fa-phone-slash color-gray2 pointer" onClick={()=>this.cancelCallBack(problem)}></i>               
            </ToolTip>            
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
  createCustomerContact=(callback)=>{
    console.log(callback);
    this.apiCurrentActivityService.updateCallBackStatus(callback.id,'contacted').then(result=>{
      console.log(result);
      if(result.status)
      {
        this.getData();
      }

    })
  }
  cancelCallBack=async (callback)=>{
    console.log(callback);
    const reason=await this.prompt("Reason for not calling back");
    if(reason!==false && reason!=''&&reason!=null){      
      this.apiCurrentActivityService.cancelCallBack(callback.id,reason)
      .then(result=>{
        console.log(result);
        if(result.status)
        {
          this.getData();
        }
      })
    }
    
  }
  render() {    
    return <div>
            {this.getPrompt()}
            {this.getContent()}
           </div>
        
  }
}

export default CallBackComponent;
