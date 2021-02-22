import * as PropTypes from "prop-types";
import {InternalNoteItemComponent} from "../InternalNoteItemComponent/InternalNoteItemComponent";
import React from 'react';

export class InternalNotesListComponent extends React.PureComponent {
    render() {
        const {internalNotes} = this.props;
        if (!internalNotes) {
            return '';
        }
        return internalNotes.map(internalNote => <InternalNoteItemComponent internalNote={internalNote}
                                                                            key={internalNote.id}
        />);
    }
}

InternalNotesListComponent.propTypes = {internalNotes: PropTypes.array};