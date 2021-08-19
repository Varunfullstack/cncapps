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

    handleEdit = () => {
        var notes = {};
        const customerID = params.get("customerID");


        const {genNotes, customerId} = this.state;
        notes.genNotes = this.state.genNotes;
        var genNotesOld = this.state.originData.data.genNotes;
        notes.customerID = customerID;
        if(notes.genNotes == null){
            alert("Please enter required inputs");
            return false;
        }

        this.api.saveGenNote({genNotes:notes.genNotes,customerID:notes.customerID}).then((res) => {
            if (res.status == 200) {
                this.logData({genNotes} ,{genNotesOld},notes.customerID, null, Pages.Customer);
                this.getData()
            }
            return true;
        });



    }


    render() {
        return (
            <div>
                <div class="card-header"><h3>Genral Notes</h3></div>
                <div>
                    <textarea rows={15}  name="genNotes" value={this.state.genNotes}  className="text_textarea"
                              onChange={($event) => this.setNotesData($event)}>{this.state.genNotes} </textarea>
                </div>
                <button onClick={(e) => {
                    e.currentTarget.blur();
                    this.handleEdit();
                }
                } className="ml-5">Save</button>

            </div>
        );
    }
}
