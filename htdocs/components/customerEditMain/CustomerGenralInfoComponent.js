import React from 'react';
import {Pages, params} from "../utils/utils";
import APICustomers from '../services/APICustomers';
import MainComponent from '../shared/MainComponent';
import Table from '../shared/table/table';
import APIProjects from '../ProjectsComponent/services/APIProjects';
import ToolTip from '../shared/ToolTip';
import './../style.css';

export default class CustomerGenralInfoComponent extends MainComponent {
    api = new APICustomers();

    // apiProjects = new APIProjects();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            customerId: null,
            genNotes: "",
            originData: null
        }
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        const customerId = params.get("customerID");
        this.api.getCustomerGenNotes(customerId).then(notes => {
            var genNotes = notes.data.genNotes;
            this.setState({genNotes, customerId,originData:notes})


        })
    }

    setNotesData = ($event) => {
        this.setState({genNotes: $event.target.value});
    };

    handleEdit = (notes) => {

        const customerID = params.get("customerID");


        const {genNotes, customerId} = this.state;
        notes.genNotes = this.state.genNotes;
        notes.customerID = customerID;
        this.api.saveGenNote({genNotes:notes.genNotes,customerID:notes.customerID}).then((res) => {
            if (res.status == 200) {

                this.logData({genNotes, customerId} ,this.state.originData.data,notes.customerID, null, Pages.Customer);
                this.getData()


            }
        });



    }


    render() {
        return (
            <div>
                <div>
                    <textarea rows={15}  name="genNotes" value={this.state.genNotes}  className="text_textarea"
                              onChange={($event) => this.setNotesData($event)}>{this.state.genNotes} </textarea>
                </div>
                <ToolTip title="Save Notes">
                    <i
                        onClick={this.handleEdit}
                        className="fal fa-save fa-2x icon pointer "
                    ></i>
                </ToolTip>
            </div>
        );
    }
}
