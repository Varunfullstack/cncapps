import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../services/APIProjects';
import Table from "../../shared/table/table";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import CNCCKEditor from "../../shared/CNCCKEditor";

export default class ProjectIssuesComponent extends MainComponent {
    api = new APIProjects();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            showModal: false,
            issues: [],
            newIssue: true,
            currentIssue: {issuesRaised: ''},
            data: {
                issuesRaised: '',
            },

        };
    }

    componentDidMount() {
        this.getData();
    }

    getData() {
        this.api.getProjectIssues(this.props.projectID).then(issues => {
            console.log('issues', issues);
            this.setState({issues, showModal: false});
        })
    }

    getIssuesTable = () => {
        const {issues} = this.state;
        if (issues.length > 0)
            console.log(this.props.currentUser.id, issues[0].consID);
        const columns = [
            {
                path: "engineerName",
                label: "",
                hdToolTip: "Issue raised by",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: true,
                className: "text-center",
                width: 150
            },
            {
                path: "createAt",
                label: "",
                hdToolTip: "Issue raised at",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-calendar color-gray2 pointer",
                sortable: true,
                className: "text-center",
                content: (issue) => this.getCorrectDate(issue.createAt, true),
                width: 100
            },
            {
                path: "issuesRaised",
                label: "",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                hdToolTip: "Issues Raised",
                hdStyle: {width: 20},
            },
            {
                path: "",
                label: "",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                hdToolTip: "Edit",
                className: "text-center",
                content: (issue) => {
                    return issue.consID == this.props.currentUser.id ?
                        <i className="fal  fa-edit color-gray2 pointer"
                           onClick={() => this.setState({showModal: true, newIssue: false, currentIssue: issue})}
                        ></i>
                        : null
                }
            },
            {
                path: "2",
                label: "",
                icon: "fal fa-2x fa-trash color-gray2 pointer",
                hdToolTip: "Delete",
                className: "text-center",
                content: (issue) => {
                    return issue.consID == this.props.currentUser.id ?
                        <i className="fal  fa-trash color-gray2 pointer"
                           onClick={() => this.handleDeleteIssue(issue.id)}
                        ></i>
                        : null
                }
            },
        ]
        return <div style={{maxWidth: 1000}}><Table
            columns={columns}
            pk={"id"}
            data={issues}
            search={true}
        >

        </Table>
        </div>
    }
    handleDeleteIssue = (issueID) => {
        this.api.deleteProjectIssue(issueID).then(result => {
            if (result.status)
                this.getData();
            else
                this.alert("Issue can't be delete");
        })
    }
    getIssueDetailsModal = () => {
        const {showModal} = this.state;
        return (
            <Modal
                title="New Issue"
                show={showModal}
                onClose={() => this.setState({showModal: false})}
                width={750}
                content={this.getModalConetnt()}
                footer={
                    <div key="footer">
                        <button onClick={this.handleIssueDetails}>Save</button>
                        <button onClick={() => this.setState({showModal: false})}>
                            Cancel
                        </button>
                    </div>
                }
            ></Modal>
        );
    }
    getModalConetnt = () => {
        const {currentIssue} = this.state;
        return (
            <div key="content">
                <div className="form-group">
                    <label>Issues Raised</label>
                    <CNCCKEditor
                        autoFocus={true}
                        type="inline"
                        value={currentIssue?.issuesRaised || ''}
                        style={{width: 700, minHeight: 60}}
                        onChange={(data) => {
                            currentIssue.issuesRaised = data;
                            this.setState({currentIssue})
                        }
                        }
                    ></CNCCKEditor>
                </div>
            </div>
        );
    }
    handleIssueDetails = () => {
        const {currentIssue, newIssue} = this.state;
        console.log(currentIssue);
        if (currentIssue.issuesRaised == "") {
            this.alert("Please enter issue raised");
            return;
        }
        console.log("newIssue", newIssue);
        if (newIssue)
            this.api
                .addProjectIssues(this.props.projectID, currentIssue)
                .then((result) => {
                    if (result.status) {
                        this.getData();
                    }
                });
        else
            this.api
                .updateProjectIssue(this.props.projectID, currentIssue)
                .then((result) => {
                    if (result.status) {
                        this.getData();
                    }
                });
    }
    handleNewIssueModal = () => {
        const {currentIssue} = this.state;
        currentIssue.issuesRaised = '';
        this.setState({showModal: true, newIssue: true, currentIssue})
    }

    render() {
        const {mode} = this.state;
        return <div>
            <Spinner key="spinner"
                     show={this.state.showSpinner}
            ></Spinner>
            {this.getAlert()}
            <div className="m-5">
                <ToolTip title="New Issue"
                         width={30}
                >
                    <i className="fal fa-plus fa-2x icon pointer"
                       onClick={this.handleNewIssueModal}
                    ></i>
                </ToolTip>
            </div>
            {this.getIssueDetailsModal()}
            {this.getIssuesTable()}
        </div>
    }

}

  