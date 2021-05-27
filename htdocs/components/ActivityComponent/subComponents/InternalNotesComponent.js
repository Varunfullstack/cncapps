import React from "react";
import ToolTip from "../../shared/ToolTip";
import {InternalNotesListComponent} from "../../shared/InternalNotesListComponent/InternalNotesListComponent";
import * as PropTypes from "prop-types";
import AddInternalNoteModalComponent from "../../Modals/AddInternalNoteModalComponent";

export class InternalNotes extends React.Component {


    constructor(props, context) {
        super(props, context);
        this.state = {
            addInternalNoteModalShow: false,
            internalNoteEdit: '',
            internalNotes: []
        }

    }

    componentDidMount() {
        this.fetchInternalNotes();
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (prevProps.serviceRequestId !== this.props.serviceRequestId) {
            this.fetchInternalNotes();
        }
    }

    async fetchInternalNotes() {
        const {serviceRequestId} = this.props;
        const response = await fetch(`/SRActivity.php?action=getInternalNotes&serviceRequestId=${serviceRequestId}`)
        const res = response.json();
        this.setState({internalNotes: res.data});
    }

    addInternalNote = () => {
        this.setState({
            addInternalNoteModalShow: true,
        });
    }


    render() {
        const {addInternalNoteModalShow, internalNoteEdit, internalNotes} = this.state;
        return (
            <div className="round-container">
                <AddInternalNoteModalComponent
                    value={internalNoteEdit}
                    show={addInternalNoteModalShow}
                    onChange={this.saveInternalNote}
                    onCancel={this.hideNewInternalNoteModal}
                />
                <div className="flex-row">
                    <label className="label mt-5 mr-3 ml-1 mb-5"
                           style={{display: "block"}}
                    >
                        Internal Notes
                    </label>
                    <ToolTip
                        width="15"
                        title="These are internal notes only and not visible to the customer. These are per Service Request."
                        content={
                            <i className="fal fa-info-circle mt-5 pointer icon"/>
                        }
                    />
                    <a onClick={this.addInternalNote}
                       className="icon pointer ml-5"
                    ><i className="fal fa-plus fa-2x"/></a>
                </div>
                <div className="internalNotesContainer">
                    <InternalNotesListComponent internalNotes={internalNotes}/>
                </div>
            </div>
        );

    }

    hideNewInternalNoteModal = () => {
        this.setState({addInternalNoteModalShow: false, internalNoteEdit: ''});
    }

    async saveNewInternalNote(value) {
        const {serviceRequestId} = this.props;

        const response = await fetch('/SRActivity.php?action=addInternalNote', {
            method: 'POST',
            body: JSON.stringify(
                {content: value, serviceRequestId}
            )
        });
        const res = await response.json();
        if (!res.status === 'ok') {
            throw new Error('Failed to save internal note');
        }
    }

    saveInternalNote = async (value) => {
        return this.saveNewInternalNote(value).then(result => {
            if (this.props.onNoteAdded) {
                this.props.onNoteAdded();
            }
        }).catch()
            .then(() => {
                this.hideNewInternalNoteModal();
            })
    }
}

InternalNotes.propTypes = {
    serviceRequestId: PropTypes.number
};
