
import React from 'react';
import MainComponent from "../../shared/MainComponent";
import Modal from '../../shared/Modal/modal';
import Spinner from "../../shared/Spinner/Spinner";
import Table from '../../shared/table/table';
import ToolTip from '../../shared/ToolTip';
import { params } from '../../utils/utils';
import ProjectsHelper from '../helper/ProjectsHelper';
import APIProjects from '../services/APIProjects';
import ProjectDetailsComponent from './ProjectDetailsComponent';
export default class CurrentProjectsComponent extends MainComponent {
  api = new APIProjects();
  helper=new ProjectsHelper();
  constructor(props) {
    super(props);
    this.state = {
      projects: [],
      showHistory: false,
      currentProject: null,
      history: [],
      mode: "list", // list, add , edit
      projectID:null,
      projectsSummary:this.props.projectsSummary
    };
  }

  componentDidMount() {
   let mode="list";
    const action=params.get("action");
    const projectID=params.get("projectID");
    if(action)
      mode=action;
    this.setState({ showSpinner: true ,projectID,mode});
    if(mode=='list')
    this.api.getProjects().then((projects) => {
      projects.map((p) => {
        p.inHoursClass =this.helper.getRedClass(p.inHoursUsed , p.inHoursBudget);
        p.outHoursClass =this.helper.getRedClass(p.outHoursUsed , p.outHoursBudget);
      }); 
      this.setState({ projects, showSpinner: false });
    });
  }

  getProjectsElement = () => {
    let { projects } = this.state;
    const {projectsSummary}=this.props;
    //apply Filter
    if(projectsSummary){
      projects= projects.filter(p=>{
        if(p.projectStageName==null)
        return true;
        const indx=projectsSummary.map(s=>s.name).indexOf(p.projectStageName);
        if(indx>=0&&!projectsSummary[indx].filter)
        {
          return false;
        }
        else 
        return true;
        
      })
    }
    //console.log(projects.length);
    const columns = [
        {
            path: "customerName",
            //label: "Customer Name",
            sortable: true,
            hdToolTip: "Customer Name",
            icon: "fal fa-2x fa-building color-gray2 pointer",
            //content:(project)=><a style={{color:'black'}} href={`Projects.php?action=edit&&projectID=${project.projectID}`}>{project.customerName}</a>
            //className: "text-center",
          },
      {
        path: "description",
        //label: "Description",
        sortable: true,
        hdToolTip: "Description",
        icon:"fal fa-2x fa-file color-gray2 pointer",
        //className: "text-center",
        // content:(project)=><a href={`/Project.php?action=edit&projectID=${project.projectID}`} target="_blank">{project.description}</a>
        content:(project)=><a style={{color:'black'}} href={`Projects.php?action=edit&&projectID=${project.projectID}`}>{project.description}</a>

      },    
      {
        path: "projectStageName",
        //label: "Description",
        sortable: true,
        hdToolTip: "Project Stage",
        icon:"fal fa-2x fa-step-forward color-gray2 pointer",
        //className: "text-center",
        // content:(project)=><a href={`/Project.php?action=edit&projectID=${project.projectID}`} target="_blank">{project.description}</a>
      },  
      {
        path: "projectTypeName",
        //label: "Description",
        sortable: true,
        hdToolTip: "Project Type",
        icon:"fal fa-2x fa-text color-gray2 pointer",
        //className: "text-center",
        // content:(project)=><a href={`/Project.php?action=edit&projectID=${project.projectID}`} target="_blank">{project.description}</a>
      },
      {
        path: "assignedEngineer",
        //label: "Engineer",
        sortable: true,
        hdToolTip: "Issue raised by",
        icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
        //className: "text-center",
      },
      {
        path: "commenceDate",
        //label: "Commencement",
        sortable: true,
        className: "text-center",
        hdToolTip: "Commencement",
        icon: "fal fa-2x fa-calendar-day color-gray2 pointer",
        content: (project) => (
          <span>{this.getCorrectDate(project.commenceDate)}</span>
        ),
      },
      {
        path: "hasProjectPlan",
        //label: "Project Plan",
        sortable: true,
        className: "text-center",
        hdToolTip: "Project Plan",
        icon: "fal fa-2x fa-analytics color-gray2 pointer",
        content: (project) =>
          project.hasProjectPlan ? (
            <a
              href={`/Projects.php?action=projectFiles&projectID=${project.projectID}`}
              target="_blank"
            >
              <i className="fal fa-download pointer icon"></i>
            </a>
          ) : null,
      },
      {
        path: "inOutHoursBudget",
        //label: "IHB & INU ",
        sortable: true,
        hdToolTip:"In hours budget / used",
        icon: "fal fa-2x fa-house-day color-gray2 pointer",

        className: "text-center",
        content:(project)=><div className="flex-row">
          <div>{project.inHoursBudget}&nbsp;/&nbsp;</div>
          <div className={project.inHoursClass}>{ project.inHoursUsed}</div>
        </div>
      },
      {
        path: "outHoursBudgetUsed",
        //label: "OOHB & OOHU ",
        hdToolTip:"Out of hours budget / used",
        icon: "fal fa-2x fa-house-night color-gray2 pointer",

        sortable: true,
        className: "text-center",
        //width:100,
        content:(project)=><div className="flex-row">
          <div>{project.outHoursBudget}&nbsp;/&nbsp;</div>
          <div className={project.outHoursClass}>{ project.outHoursUsed}</div>
        </div>
      },
      
      {
        path: "latestUpdate",
        label: "",
        sortable: true,
        icon: "fal fa-2x fa-comment-alt-edit color-gray2 pointer",
        hdToolTip: "Latest Update",
        //className: "text-center",
        content: (project) => this.helper.getLatestUpdate(project),
      },
      {
        path: "2",
        label: "",
        sortable: false,
        //className: "text-center",
        toolTip: "Edit",
        hdToolTip: "Edit",
        icon: "fal fa-2x fa-edit color-gray2 pointer",
        content: (project) => (          
           <a href={`Projects.php?action=edit&projectID=${project.projectID}`}> <i className="fal fa-edit icon pointer"></i></a>
        ),
      },
      {
        path: "1",
        label: "",
        sortable: false,
        hdToolTip: "Histpry",
        icon: "fal fa-2x fa-history color-gray2 pointer",
        //className: "text-center",
        content: (project) => (
          <i
            className="fal fa-history icon pointer"
            onClick={() => this.handleHistoryClick(project)}
          ></i>
        ),
      },
    ];
    return (
      <Table
        id="projects"
        data={projects || []}
        columns={columns}
        pk="projectID"
        search={true}
      ></Table>
    );
  };
  
  getHistoryModal = () => {
    const { showHistory, currentProject } = this.state;
    return (
      <Modal
        width={800}
        show={showHistory}
        title={"Project Update History"}
        onClose={() => this.setState({ showHistory: false })}
        content={this.getHistoryElement()}
      ></Modal>
    );
  };
  handleHistoryClick = async (currentProject) => {
    const history = await this.api.getProjectHistory(currentProject.projectID);
    //console.log("history", history);
    this.setState({ currentProject, showHistory: true, history });
  };
  getHistoryElement = () => {
    const { history } = this.state;
    const columns = [
      {
        path: "createdAt",
        label: "",
        hdToolTip: "Created At",
        hdClassName: "text-center",
        icon: "fal  fa-calendar   pointer white",
        sortable: true,
        className: "text-center",
      },
      {
        path: "createdBy",
        label: "",
        hdToolTip: "Created By",
        hdClassName: "text-center",
        icon: "fal  fa-user-hard-hat   pointer white",
        sortable: true,
        //className: "text-center",
      },
      {
        path: "comment",
        label: "",
        hdToolTip: "Comment",
        hdClassName: "text-center",
        icon: "fal  fa-file   pointer white",
        sortable: true,
        //className: "text-center",
      },
    ];
    return (
      <Table
        id="history"
        key="history"
        pk="id"
        data={history || []}
        sortable={true}
        columns={columns}
      ></Table>
    );
  };
  setMode = (mode,projectID=null) => {
    this.setState({ mode,projectID });
  };
  render() {
    const { showSpinner } = this.state; 
      return (
        <div>
          <Spinner key="spinner" show={showSpinner}></Spinner>
          <div style={{ marginTop: -10, marginBottom: 5 }}>
            <ToolTip title="New Project" width={30}>
              <a href={`Projects.php?action=add`}>
                <i
                  className="fal fa-plus fa-2x pointer"                  
                ></i>
              </a>              
            </ToolTip>
          </div>
          {this.getHistoryModal()}
          {this.getProjectsElement()}
        </div>
      );
   
  }
}
 