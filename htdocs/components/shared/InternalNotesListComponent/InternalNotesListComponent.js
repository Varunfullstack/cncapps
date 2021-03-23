import * as PropTypes from "prop-types";
import {InternalNoteItemComponent} from "../InternalNoteItemComponent/InternalNoteItemComponent";
import React from 'react';
import moment from "moment";

export class InternalNotesListComponent extends React.PureComponent {
    render() {
        const {internalNotes} = this.props;
        if (!internalNotes) {
            return '';
        }

        return internalNotes.map(internalNote => <InternalNoteItemComponent
            updatedAt={moment(internalNote.updatedAt?.date).format('DD/MM/YYYY HH:mm')}
            updatedBy={internalNote.updatedBy}
            content={internalNote.content}
            key={internalNote.id}
        />);
    }
}

InternalNotesListComponent.propTypes = {internalNotes: PropTypes.array};