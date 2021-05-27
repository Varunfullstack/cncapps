import React from "react";
import ToolTip from "../../shared/ToolTip";
import {InternalNoteItemComponent} from "../../shared/InternalNoteItemComponent/InternalNoteItemComponent";
import moment from "moment";
import * as PropTypes from "prop-types";
import EditTaskListModalComponent from "../../Modals/EditTaskListModalComponent";
import APIStandardText from "../../services/APIStandardText";

export class TaskListComponent extends React.Component {
    apiStandardText = new APIStandardText();

    constructor(props, context) {
        super(props, context);
        this.state = {
            taskListEdit: '',
            taskListEditModalShow: false,
            allStandardTexts: [],
            value: '',
            lastUpdatedAt: '',
            lastUpdatedBy: '',
        }
    }

    componentDidMount() {
        this.apiStandardText.getAllTypes().then(allStandardTexts => {
            allStandardTexts = allStandardTexts.filter(x => [1, 3].indexOf(x.typeId) > -1);
            this.setState({allStandardTexts})
        });

        this.fetchTaskList();
    }

    async fetchTaskList() {
        const {serviceRequestId} = this.props;
        const response = await fetch(`/SRActivity.php?action=getTaskList&serviceRequestId=${serviceRequestId}`)
        const res = await response.json();
        const {value, lastUpdatedAt, lastUpdatedBy} = res.data;
        this.setState({value, lastUpdatedAt, lastUpdatedBy});
    }

    updateTaskList = async (value) => {
        const {serviceRequestId} = this.props;
        try {
            const response = await fetch('?action=saveTaskList', {
                method: 'POST',
                body: JSON.stringify(
                    {content: value, serviceRequestId}
                )
            });
            const res = await response.json();
            if (!res.status === 'ok') {
                throw new Error('Failed to save task list');
            }

            this.fetchTaskList();
        } catch (error) {
            console.error(error);
            alert(error);
        }
        this.hideTaskListModal();
    }

    editTaskList = () => {
        const {value} = this.state;
        this.setState({
            taskListEditModalShow: true,
            taskListEdit: value,
        })
    }

    hideTaskListModal = () => {
        this.setState({
            taskListEditModalShow: false,
            taskListEdit: '',
        })
    }

    getEditTaskListModalComponent = () => {
        const {taskListEditModalShow, taskListEdit, allStandardTexts} = this.state;
        return (
            <EditTaskListModalComponent
                okTitle="Save"
                key="taskListEdit"
                value={taskListEdit}
                show={taskListEditModalShow}
                options={allStandardTexts.map(x => ({...x, template: x.content, name: x.title}))}
                title="Task List Edit"
                onChange={this.updateTaskList}
                onCancel={this.hideTaskListModal}
            />
        )
    }

    render() {
        const {lastUpdatedBy, lastUpdatedAt, value} = this.state;
        return <div className="round-container">
            {this.getEditTaskListModalComponent()}
            <div className="flex-row">
                <label className="label mt-5 mr-3 ml-1 mb-5"
                       style={{display: "block"}}
                >
                    Task List
                </label>
                <ToolTip
                    width="15"
                    title="These are the tasks associated with the Service Request. These are per Service Request."
                    content={
                        <i className="fal fa-info-circle mt-5 pointer icon"/>
                    }
                />
                <a onClick={this.editTaskList}
                   className="icon pointer ml-5"
                ><i className="fal fa-edit fa-2x"/></a>
            </div>
            <div className="internalNotesContainer">
                {
                    !lastUpdatedAt ?
                        '' :
                        <InternalNoteItemComponent
                            updatedAt={moment(lastUpdatedAt).format("DD/MM/YYYY HH:mm")}
                            updatedBy={lastUpdatedBy}
                            content={value}
                        />
                }

            </div>
        </div>;
    }
}

TaskListComponent.propTypes = {
    serviceRequestId: PropTypes.number
};