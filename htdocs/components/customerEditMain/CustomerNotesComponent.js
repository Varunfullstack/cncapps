import moment from "moment";
import React from "react";
import APICustomers from "../services/APICustomers";
import APIUser from "../services/APIUser";
import MainComponent from "../shared/MainComponent";
import Modal from "../shared/Modal/modal";
import Spinner from "../shared/Spinner/Spinner";
import ToolTip from "../shared/ToolTip";
import Table from "./../shared/table/table";

class CustomerNotesComponent extends MainComponent {
    el = React.createElement;
    api = new APICustomers();
    apiUsers = new APIUser();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            loaded: false,
            showModal: false,
            customerNotes: [],
            enginners: [],
            customerId: props.customerId,
            currentNote: null,
            currentNoteIdx: null,
            isAddingNote: false,
            reviewData: {
                toBeReviewedOnAction: "",
                toBeReviewedOnByEngineerId: "",
                toBeReviewedOnDate: "",
                toBeReviewedOnTime: "",
            },
        };

    }

    componentDidMount() {
        this.api.getCustomerReviewData(this.state.customerId).then(
            (res) => {
                this.setState({reviewData: res.data});
            },
            (error) => {
                this.alert("Error in loading data");
            }
        );
        this.apiUsers.getActiveUsers().then((enginners) => {
            this.setState({enginners});
        });
        this.getData();
    }

    getData = () => {
        this.api.getCustomerNotes(this.props.customerId).then((response) => {
            this.setState({customerNotes: response.data, loaded: true, showModal: false});
        });
    };


    handleEdit = (note) => {
        this.setState({currentNote: {...note}, showModal: true});
    };

    getHistoryElement() {
        const {customerNotes} = this.state;
        const columns = [
            {
                path: "modifiedAt",
                label: "",
                hdToolTip: "Modefied At",
                icon: "fal fa-2x fa-calendar color-gray2 pointer",
                sortable: true,
                width: 150,
                content: (note) => this.getCorrectDate(note.modifiedAt, true),
            },
            {
                path: "modifiedByName",
                label: "",
                hdToolTip: "Modefied By",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: true,
                width: 120,
            },
            {
                path: "note",
                label: "",
                hdToolTip: "Note",
                icon: "fal fa-2x fa-file color-gray2 pointer",
                sortable: true,
            },
            {
                path: "",
                label: "",
                hdToolTip: "Edit",
                icon: this.getEditIcon(),
                sortable: true,
                content: (note) => this.getEditElement(note, this.handleEdit),
            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Delete",
                icon: this.getDeleteElement(),
                sortable: true,
                content: (note) =>
                    this.getDeleteElement(note, this.handleDeleteNote),
            },
        ];
        return (
            <Table
                pk="id"
                data={customerNotes}
                columns={columns}
                search={true}
            ></Table>
        );
    }

    handleDeleteNote = async (note) => {
        if (await this.confirm("Are you sure you want to detele this note?")) {
            this.api.deleteCustomerNote(note.id).then((res) => this.getData());
        }
    };


    handleSaveNote = () => {
        const {currentNote, customerId} = this.state;
        if (this.state.currentNote.id >= 0)
            currentNote.lastUpdatedDateTime = moment().format("YYYY-MM-DD HH:mm:ss");
        currentNote.customerId = customerId;
        this.api
            .saveCustomerNote(currentNote)
            .then((response) => {
                this.getData();
            })
            .catch((error) => {
                this.alert("Failed to save note");
            });
    };


    setReviewData = ($event) => {
        const {reviewData} = this.state;
        reviewData[$event.target.name] = $event.target.value;
        this.setState({reviewData});
    };
    handleSaveREviewData = () => {
        const {reviewData, customerId} = this.state;
        reviewData.customerId = customerId;
        this.api.updateCustomerReviewData(reviewData).then(
            (res) => {
                this.alert("saved");
            },
            (error) => {
                this.alert("Error in save data");
            }
        );
    };
    getReviewElement = () => {
        const {reviewData} = this.state;
        return (
            <div className="flex-row" style={{alignItems: "center"}}>
                <span>To be reviewed on</span>
                <input
                    style={{width: 140}}
                    className="form-control"
                    type="date"
                    value={reviewData.toBeReviewedOnDate}
                    name="toBeReviewedOnDate"
                    onChange={($event) => this.setReviewData($event)}
                ></input>
                <span>Time</span>
                <input
                    style={{width: 80}}
                    className="form-control"
                    type="time"
                    value={reviewData.toBeReviewedOnTime}
                    name="toBeReviewedOnTime"
                    onChange={($event) => this.setReviewData($event)}
                ></input>
                <span>By</span>
                <select
                    style={{width: 150}}
                    className="form-control"
                    value={reviewData.toBeReviewedOnByEngineerId}
                    name="toBeReviewedOnByEngineerId"
                    onChange={($event) => this.setReviewData($event)}
                >
                    {this.state.enginners.map((e) => (
                        <option key={e.id} value={e.id}>
                            {e.name}
                        </option>
                    ))}
                </select>
                <ToolTip title="Save reviewed data">
                    <i
                        onClick={this.handleSaveREviewData}
                        className="fal fa-save fa-2x icon pointer "
                    ></i>
                </ToolTip>
            </div>
        );
    };
    setNote = (value) => {
        const {currentNote} = this.state;
        currentNote.note = value;
        this.setState({currentNote})
    }
    getModal = () => {
        const {showModal, currentNote} = this.state;
        if (!showModal)
            return null;
        return (
            <Modal
                width={600}
                show={showModal}
                title="Update Customer Note"
                footer={
                    <div key="footerActions">
                        <button onClick={this.handleSaveNote}>Save</button>
                        <button onClick={() => this.setState({showModal: false})}>
                            Cancel
                        </button>
                    </div>
                }
            >
                <div>
                    {
                        currentNote.id >= 0 ?
                            <div>
                                <span>Updated by : </span>
                                <span>{currentNote.modifiedByName}</span>
                                <span>   At </span>
                                <span>{this.getCorrectDate(currentNote.modifiedAt, true)}</span>
                            </div> : null
                    }
                    <textarea
                        value={currentNote.note}
                        onChange={($event) => this.setNote($event.target.value)}
                        className="form-control"
                        style={{height: 200}}
                    ></textarea>
                </div>
            </Modal>
        );
    }

    handleNewNote = () => {
        this.setState({
            currentNote: {
                id: -1,
                customerId: this.props.customerId,
                createdAt: "",
                modifiedAt: "",
                modifiedById: "",
                note: "",
                createdById: "",
                modifiedByName: "",
            },
            showModal: true
        });
    };
    getAddNewNoteElement = () => {
        return <div className="m-5"><ToolTip width={30} title="Add New Note">
            <i className="fal fa-plus fa-2x pointer" onClick={this.handleNewNote}></i>
        </ToolTip>
        </div>
    }

    render() {
        if (!this.state.loaded) {
            return <Spinner show={this.state.loaded}></Spinner>;
        }

        return (
            <div>
                {this.getConfirm()}
                {this.getAlert()}
                {this.getModal()}
                {this.getReviewElement()}
                {this.getAddNewNoteElement()}
                {this.getHistoryElement()}

            </div>
        );
    }
}

export default CustomerNotesComponent;
