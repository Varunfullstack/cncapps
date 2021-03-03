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
        }
    }

    componentDidMount() {
        this.apiStandardText.getAllTypes().then(allStandardTexts => {
            this.setState({allStandardTexts})
        });
    }

    updateTaskList = async (value) => {
        const {problemId, onUpdatedTaskList} = this.props;
        try {
            const response = await fetch('?action=saveTaskList', {
                method: 'POST',
                body: JSON.stringify(
                    {content: value, serviceRequestId: problemId}
                )
            });
            const res = await response.json();
            if (!res.status === 'ok') {
                throw new Error('Failed to save task list');
            }
            if (onUpdatedTaskList) {
                onUpdatedTaskList();
            }
        } catch (error) {
            console.error(error);
            alert(error);
        }
        this.hideTaskListModal();
    }

    editTaskList = () => {
        const {taskList} = this.props;
        this.setState({
            taskListEditModalShow: true,
            taskListEdit: taskList,
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
                <InternalNoteItemComponent updatedAt={moment(this.props.taskListUpdatedAt).format("DD/MM/YYYY HH:mm")}
                                           updatedBy={this.props.taskListUpdatedBy}
                                           content={this.props.taskList}
                />

            </div>
        </div>;
    }
}

TaskListComponent.propTypes = {
    taskListUpdatedAt: PropTypes.string,
    taskListUpdatedBy: PropTypes.string,
    taskList: PropTypes.string,
    problemId: PropTypes.number,
    onUpdatedTaskList: PropTypes.func
};