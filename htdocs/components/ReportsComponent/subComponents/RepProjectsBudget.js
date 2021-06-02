import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../../ProjectsComponent/services/APIProjects';
import Table from "../../shared/table/table";
import {equal} from "../../utils/utils";
import ProjectsHelper from "../../ProjectsComponent/helper/ProjectsHelper";

export default class RepProjectsBudget extends MainComponent {
    api = new APIProjects();
    helper = new ProjectsHelper();

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
        const {consID, dateFrom, dateTo} = this.props;
        this.api.getProjectsSearch(consID, dateFrom, dateTo)
            .then(projects => {
                projects = projects.map((p) => {
                    p.inHoursClass = this.helper.getRedClass(p.inHoursUsed, p.inHoursBudget);
                    p.outHoursClass = this.helper.getRedClass(p.outHoursUsed, p.outHoursBudget);
                    return p;
                });
                this.setState({projects, showModal: false, loadData: false});
            });
    }

    getProjectsTable = () => {
        const {projects} = this.state;
        const columns = [
            {
                path: "customerName",
                //label: "Customer Name",
                sortable: true,
                hdToolTip: "Customer Name",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                content: (project) => <a style={{color: 'black'}}
                                         href={`Projects.php?action=edit&&projectID=${project.projectID}`}>{project.customerName}</a>
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
                                         target="_blank">{project.description}</a>
            },
            {
                path: "engineerName",
                //label: "Description",
                sortable: true,
                hdToolTip: "Project Stage",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
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
                path: "inOutHoursBudget",
                //label: "IHB & INU ",
                sortable: true,
                hdToolTip: "In hours budget / used",
                icon: "fal fa-2x fa-house-day color-gray2 pointer",
                className: "text-center",
                content: (project) => <div className="flex-row flex-center">
                    <div>{project.inHoursBudget}&nbsp;/&nbsp;</div>
                    <div className={project.inHoursClass}>{project.inHoursUsed}</div>
                </div>
            },
            {
                path: "inHoursUsed",
                sortable: true,
                hdToolTip: "In Hours Budget Difference",
                icon: "fal fa-2x fa-arrows-h color-gray2 pointer",
                className: "text-center",
                content: (project) => {
                    if (!project.inHoursBudget) {
                        return '-';
                    }
                    const difference = ((project.inHoursBudget - project.inHoursUsed) * -1);
                    let className = '';
                    if (difference > 0) {
                        className = 'red'
                    }
                    return (
                        <div className="flex-row flex-center">
                            <div className={className}>{difference.toFixed(2)}</div>
                        </div>
                    )
                }
            },
            {
                path: "outHoursBudgetUsed",
                //label: "OOHB & OOHU ",
                hdToolTip: "Out of hours budget / used",
                icon: "fal fa-2x fa-house-night color-gray2 pointer",
                sortable: true,
                className: "text-center",
                //width:100,
                content: (project) => <div className="flex-row flex-center">
                    <div>{project.outHoursBudget}&nbsp;/&nbsp;</div>
                    <div className={project.outHoursClass}>{project.outHoursUsed}</div>
                </div>
            },
            {
                path: "outHoursUsed",
                sortable: true,
                hdToolTip: "Out Hours Budget Difference",
                icon: "fal fa-2x fa-arrows-h color-gray2 pointer",
                className: "text-center",
                content: (project) => {
                    if (!project.outHoursBudget) {
                        return '-';
                    }
                    const difference = ((project.outHoursBudget - project.outHoursUsed) * -1);
                    let className = '';
                    if (difference > 0) {
                        className = 'red'
                    }

                    return (
                        <div className="flex-row flex-center">
                            <div className={className}>{difference.toFixed(2)}</div>
                        </div>
                    )
                }
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
        return <div>
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
            <Spinner key="spinner" show={this.state.showSpinner}/>
            {this.getProjectsTable()}
        </div>
    }

}

  