import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../../ProjectsComponent/services/APIProjects';
import Table from "../../shared/table/table";
import {equal} from "../../utils/utils";

export default class RepProjects extends MainComponent {
    api = new APIProjects();

    constructor(props) {
        super(props);
        this.state = {
            showSpinner: false,
            projects: [],
            params: {},

        };
    }

    componentDidMount() {
        // this.getData();
    }

    componentDidUpdate(prevProps, prevState) {
        if (!equal(prevProps, this.props))
            this.getData();
    }

    getData() {
        this.setState({showModal: true});
        const {dateFrom, dateTo, projectStageID, projectTypeID} = this.props;
        this.api.getProjectsSearch('', dateFrom, dateTo, projectStageID, projectTypeID)
            .then(projects => {
                console.log('projects', projects);
                this.setState({projects, showModal: false, loadData: false});
            });
    }

    getProjectsTable = () => {
        //console.log('props',this.props);
        const {projects} = this.state;
        const columns = [
            {
                path: "customerName",
                //label: "Customer Name",
                sortable: true,
                hdToolTip: "Customer Name",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                content: (project) => project.customerName
                //className: "text-center",
            },
            {
                path: "description",
                //label: "Description",
                sortable: true,
                hdToolTip: "Description",
                icon: "fal fa-2x fa-file color-gray2 pointer",
                //className: "text-center",
                content: (project) => <a style={{color: 'black'}}
                                         href={`/Projects.php?action=edit&projectID=${project.projectID}`}
                                         target="_blank"
                >{project.description}</a>
            },
            {
                path: "projectStageName",
                //label: "Description",
                sortable: true,
                hdToolTip: "Project Stage",
                icon: "fal fa-2x fa-step-forward color-gray2 pointer",
                //className: "text-center",
                // content:(project)=><a href={`/Project.php?action=edit&projectID=${project.projectID}`} target="_blank">{project.description}</a>
            },
            {
                path: "projectTypeName",
                //label: "Description",
                sortable: true,
                hdToolTip: "Project Type",
                icon: "fal fa-2x fa-text color-gray2 pointer",
                //className: "text-center",
                // content:(project)=><a href={`/Project.php?action=edit&projectID=${project.projectID}`} target="_blank">{project.description}</a>
            },
            {
                path: "startDate",
                //label: "Description",
                sortable: true,
                hdToolTip: "Start Date",
                icon: "fal fa-2x fa-calendar color-gray2 pointer",
                //className: "text-center",
                //content:(project)=><div>{this.getCorrectDate(project.startDate)}</div>
            },
        ]
        return <div style={{maxWidth: 1000}}>
            <Table
                columns={columns}
                pk={"projectID"}
                data={projects}
                search={true}
            >
            </Table>
        </div>;
    }

    render() {

        return <div>
            <Spinner key="spinner"
                     show={this.state.showSpinner}
            ></Spinner>
            {this.getProjectsTable()}
        </div>
    }

}

  