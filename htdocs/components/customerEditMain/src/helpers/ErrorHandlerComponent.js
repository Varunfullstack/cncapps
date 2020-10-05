import {connect} from "react-redux";
import {Alert} from "react-bootstrap";
import {dismissError} from "../actions";
import React from "react";

class ErrorHandler extends React.Component {

    render() {
        const {errors, onDismissError} = this.props;

        return errors.map((error, idx) =>
            (
                <Alert variant={error.variant}
                       onClose={() => onDismissError(idx)}
                       dismissible
                       key={idx}
                >
                    {error.message}
                </Alert>
            )
        )
    }

}

function mapStateToProps(state) {
    return {
        errors: state.error.items,
    }
}

function mapDispatchToProps(dispatch) {
    return {
        onDismissError: (errorIndex) => {
            dispatch(dismissError(errorIndex))
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ErrorHandler)