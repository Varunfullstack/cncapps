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
        }

    }

    addInternalNote = () => {
        this.setState({
            addInternalNoteModalShow: true,
        });
    }


    render() {
        const {addInternalNoteModalShow, internalNoteEdit} = this.state;
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
                    <InternalNotesListComponent internalNotes={this.props.data?.internalNotes}/>
                </div>
            </div>
        );

    }

    hideNewInternalNoteModal = () => {
        this.setState({addInternalNoteModalShow: false, internalNoteEdit: ''});
    }

    async saveNewInternalNote(value) {
        const {data} = this.props;

        const response = await fetch('?action=addInternalNote', {
            method: 'POST',
            body: JSON.stringify(
                {content: value, serviceRequestId: data.problemID}
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
    data: PropTypes.shape({
        date: PropTypes.string,
        reason: PropTypes.string,
        projects: PropTypes.any,
        documents: PropTypes.any,
        alarmTime: PropTypes.string,
        cncNextAction: PropTypes.string,
        customerNotesTemplate: PropTypes.string,
        reasonTemplate: PropTypes.string,
        customerNotes: PropTypes.string,
        internalNotes: PropTypes.any,
        alarmDate: PropTypes.string,
        cncNextActionTemplate: PropTypes.string,
        completeDate: PropTypes.string,
        emptyAssetReasonNotify: PropTypes.bool,
        techNotes: PropTypes.string,
        contactNotes: PropTypes.string,
        submitAsOvertime: PropTypes.number,
        priorityChangeReason: PropTypes.string,
        emptyAssetReason: PropTypes.string
    })
};
