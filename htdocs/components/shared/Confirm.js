import Modal from "../shared/Modal/modal";

import React, {Fragment} from 'react';

class Confirm extends React.Component {
    constructor(props) {
        super(props);

    }

    close(value) {
        this.props.onClose(value);
    }

    render() {
        const {title, width, message, show} = this.props;
        return (
            <Modal
                key="confirmModal"
                title={title || "Alert"}
                show={show}
                width={width || 300}
                onClose={() => this.close()}
                footer={
                    <Fragment key="confirmFooter">
                        <button onClick={() => this.close(true)}
                                autoFocus={true}
                        >Yes
                        </button>
                        <button onClick={() => this.close(false)}>No</button>
                    </Fragment>
                }
                content={<label key="message">{message}</label>}
            />
        );
    }
}

export default Confirm;