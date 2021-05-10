import MainComponent from "../../shared/MainComponent";
import APIActivity from "../../services/APIActivity";
import React from 'react';

export default class MassDeletionComponent extends MainComponent {
    api = new APIActivity()

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            search: ''
        }
    }

    handleDelete = async () => {
        const response = await this.confirm('Are you sure?')
        if (!response) {
            return;
        }
        const {search} = this.state;
        let message = "";
        try {
            const res = await this.api.deleteUnstartedServiceRequests(search);
            message = res.result;
        } catch (error) {
            message = "Failed to delete service requests";
            if (typeof error === 'string') {
                message = error;
            }
            if ("message" in error) {
                message = error.message;
            }
        }
        this.alert(message);
    };

    render() {
        const {search} = this.state;
        return (
            <div>
                {this.getAlert()}
                {this.getConfirm()}
                <h3>
                    This will remove all unstarted Service Requests where the text below is found in the Initial
                    Activity
                </h3>
                <input value={search} onChange={this.updateValue}/>
                <button onClick={this.handleDelete} disabled={!Boolean(search)}>Delete</button>
            </div>
        );
    }

    updateValue = ($event) => {
        this.setState({search: $event.target.value});
    };
}