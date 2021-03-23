import * as React from "react";
import * as PropTypes from "prop-types";

export class InternalNoteItemComponent extends React.Component {
    render() {
        return (
            <div className="internalNoteItem">
                <div className="internalNoteItem_header">
                    <div className="internalNoteItem_header_date">
                        Last Updated: {this.props.updatedAt} By: {this.props.updatedBy}
                    </div>
                </div>
                <div className="internalNoteItem_content">
                    <div dangerouslySetInnerHTML={{__html: this.props.content}}/>
                </div>
            </div>
        );
    }
}

InternalNoteItemComponent.propTypes = {
    updatedAt: PropTypes.string,
    updatedBy: PropTypes.string,
    content: PropTypes.string
};

