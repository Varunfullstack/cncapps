import * as React from "react";
import moment from "moment";
import * as PropTypes from "prop-types";

export class InternalNoteItemComponent extends React.Component {
    render() {
        return <div
            className="internalNoteItem"
        >
            <div className="internalNoteItem_header">
                <div className="internalNoteItem_header_date">
                    Last
                    Updated: {moment(this.props.internalNote.updatedAt?.date).format("DD/MM/YYYY")} By: {this.props.internalNote.updatedBy}
                </div>
            </div>
            <div className="internalNoteItem_content">
                <div dangerouslySetInnerHTML={{__html: this.props.internalNote.content}}
                />
            </div>
        </div>;
    }
}

InternalNoteItemComponent.propTypes = {internalNote: PropTypes.any};

