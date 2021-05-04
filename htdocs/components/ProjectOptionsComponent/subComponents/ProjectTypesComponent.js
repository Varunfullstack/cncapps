import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import {dateFormatExcludeNull, exportCSV, poundFormat} from "../../utils/utils";
import APIProjectOptions from "../services/APIProjectOptions";
import ToolTip from "../../shared/ToolTip";
import Modal from "../../shared/Modal/modal";
import Toggle from "../../shared/Toggle";

export class ProjectTypesComponent extends MainComponent {
    api = new APIProjectOptions();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data: {
                id: '',
                name: '',
                includeInWeeklyReport: false,
                notes: ''
            },
            items: [],
            showModal: false
        };
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        this.api.getProjectTypes().then(items => {
            this.setState({items, showModal: false});
        })
    }
    getDataTableElement = () => {
        const {items} = this.state;
        const columns = [
            {
                path: "id",
                label: "",
                hdToolTip: "ID",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                className: "text-center",
            },
            {
                path: "name",
                label: "Type",
                hdToolTip: "Stage Name",
                hdClassName: "text-center",
                sortable: true,

            },
            {
                path: "includeInWeeklyReport",
                label: "Include In Weekly Report",
                hdToolTip: "Include In Weekly Report",
                hdClassName: "text-center",
                className: "text-center",
                sortable: true,
                content: (type) => <Toggle checked={type.includeInWeeklyReport} disabled={true}/>

            },
            {
                path: "notes",
                label: "Notes",
                hdToolTip: "Notes",
                hdClassName: "text-center",
                sortable: true,

            },
            {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                className: "text-center",
                content: (type) => <ToolTip title="edit">
                    <i className="fal fa-edit color-gray2 fa-2x pointer icon" onClick={() => this.handleEdit(type)}/>
                </ToolTip>

            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
                className: "text-center",
                content: (type) => <ToolTip title="edit">
                    <i className="fal fa-trash-alt  color-gray2 fa-2x pointer icon"
                       onClick={() => this.handleDelete(type)}/>
                </ToolTip>
            },
        ];
        return <Table
            key="types"
            pk="id"
            columns={columns}
            data={items || []}
            search={true}
        >

        </Table>
    }
    handleEdit = (type) => {
        this.setState({data: {...type}, showModal: true});
    }
    handleDelete = async (type) => {
        const confirm = await this.confirm("Are you sure to delete it")
        if (confirm)
            this.api.deleteProjectType(type.id).then(result => {
                this.getData();
            }).catch(ex => {
                this.alert("Type can't be delete");
            })
    }
    getModal = () => {
        const {data, showModal} = this.state;
        if (!data)
            return null;
        return <Modal title="Project Type Name" show={showModal} width={500}
                      onClose={() => this.setState({showModal: false})}
                      content={<div key="content">
                          <div className="form-group">
                              <label>Name</label>
                              <input required value={data.name}
                                     onChange={(event) => this.setValue("name", event.target.value)} type="text"/>
                          </div>

                          <div className="form-group">
                              <label>Notes</label>
                              <textarea required value={data.notes}
                                        onChange={(event) => this.setValue("notes", event.target.value)}/>
                          </div>
                          <div className="form-group">
                              <label>Include In Weekly Report</label>
                              <Toggle checked={data.includeInWeeklyReport}
                                      onChange={() => this.setValue("includeInWeeklyReport", !data.includeInWeeklyReport)}/>
                          </div>
                      </div>
                      }
                      footer={<div key="footer">
                          <button onClick={this.handleSave}>Save</button>
                          <button onClick={() => this.setState({showModal: false})}>Cancel</button>
                      </div>}
        >

        </Modal>
    }
    handleSave = () => {
        const {data} = this.state;
        data.includeInWeeklyReport = data.includeInWeeklyReport ? 1 : 0;
        if (data.name == '') {
            this.alert("Please enter name");
            return;
        }
        if (data.id != '') {
            this.api.updateProjectType(data.id, data).then(result => {
                this.getData();
            });
        } else //new
        {
            this.api.addProjectType(data).then(result => {
                this.getData();
            });
        }
    }
    handleNew = () => {
        this.setState({showModal: true, data: {id: '', name: '', includeInWeeklyReport: false, notes: ''}});
    }

    render() {
        return <div style={{width: 500}}>
            <ToolTip width={30} title="New Stage">
                <i className="fal fa-plus fa-2x m-5 pointer" onClick={this.handleNew}/>
            </ToolTip>
            {this.getAlert()}
            {this.getConfirm()}
            {this.getModal()}
            {this.getDataTableElement()}
        </div>
    }
}