import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../services/APIProjects';
import Table from "../../shared/table/table";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";
 
export default class ProjectStagesHistoryComponent extends MainComponent { 
  api = new APIProjects(); 
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal:false,
      stages:[],            
    };     
  }

  componentDidMount() {
    this.getData();
  }
  getData(){
    this.api.getProjectStagesHistory(this.props.projectID).then(stages=>{
      console.log('stages',stages);
      this.setState({stages,showModal:false});
    })
  }
  getStagesTable=()=>{
    const {stages} = this.state;
     
    const columns=[
        {
            path: "stageName",
            label: "",
            hdToolTip: "Project Stage",
            hdClassName: "text-center",
            icon: "fal fa-2x fa-text color-gray2 pointer",
            sortable: true,
            className: "text-center",
            width:150
         },
      {
         path: "stageTimeHours",
         label: "",
         hdToolTip: "Spent Stage time hours",
         hdClassName: "text-center",
         icon: "fal fa-2x fa-hourglass-half color-gray2 pointer",
         sortable: true,
         className: "text-center",
         width:150,
         content:(stage)=>this.checkStageTime(stage)
      },
      {
        path: "engineerName",
        label: "",
        hdToolTip: "Engineer",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
        sortable: true,
        className: "text-center",
        width:150
     },
      {
        path: "createAt",
        label: "",
        hdToolTip: "Create At",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-calendar color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(stage)=><p>{this.getCorrectDate(stage.createAt,true)}</p>,
        width:100
     },
      
    ]
    return<div style={{maxWidth:1000}}> <Table
    columns={columns}
    pk={"id"}
    data={stages}
    search={true}
    >

    </Table>
    </div>
  }
  checkStageTime(stage){
      if(stage.stageTimeHours==null)
      {
          const createAt=moment(stage.createAt); 
          const duration=moment.duration( moment().diff(createAt));          
          return duration.asHours().toFixed(2);
      }
      else 
      return stage.stageTimeHours;
  }
  render() {      
    return <div>
        <Spinner  key="spinner" show= {this.state.showSpinner}></Spinner>
        {this.getStagesTable()}
    </div>
  }

}

  