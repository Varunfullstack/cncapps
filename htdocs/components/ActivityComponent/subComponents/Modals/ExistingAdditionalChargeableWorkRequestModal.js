import * as React from "react";
import * as PropTypes from "prop-types";
import Modal from "../../../shared/Modal/modal";
import APIActivity from "../../../services/APIActivity";
import moment from "moment";

export default class ExistingAdditionalChargeableWorkRequestModal extends React.Component {
    api = new APIActivity()

    constructor(props) {
        super(props);

        /**
         * @typedef AdditionalChargeableWorkRequest
         * @type {object}
         * @property {string} tokenId
         * @property {number} serviceRequestId
         * @property {string} serviceRequestEmailSummarySubject
         * @property {string} contactName
         * @property {number} additionalTimeRequested
         * @property {string} reason
         * @property {string} requesterFullName
         * @property {string} requestedAt
         */

        /**
         * @typedef ExistingAdditionalChargeableWorkRequestModalState
         * @type {object}
         * @property {AdditionalChargeableWorkRequest} chargeableWorkRequest
         */

        /**
         * @type {ExistingAdditionalChargeableWorkRequestModalState}
         */
        this.state = {
            chargeableWorkRequest: null,
        }
    }

    async componentDidMount() {
        const {chargeableWorkRequestId} = this.props;
        const chargeableWorkRequest = await this.api.getAdditionalChargeableWorkRequestInfo(chargeableWorkRequestId);
        this.setState({chargeableWorkRequest})
    }

    resendEmail = async () => {
        const {chargeableWorkRequestId} = this.props;
        await this.api.resendChargeableRequestEmail(chargeableWorkRequestId);
        this.props.onClose(true);
    }

    cancelRequest = async () => {
        const {chargeableWorkRequestId} = this.props;
        await this.api.cancelChargeableRequest(chargeableWorkRequestId);
        this.props.onClose(true);
    };

    signalClose = (closingValue) => {
        this.props.onClose(closingValue);
    }

    render() {
        const {show} = this.props;
        const {chargeableWorkRequest} = this.state;
        if (!chargeableWorkRequest) {
            return '';
        }

        return (
            <Modal
                width="900"
                onClose={this.signalClose}
                title="Additional Chargeable Work Request"
                show={show}
                className="standardTextModal"
                content={(
                    <React.Fragment key="stuff">
                        <div>
                            Requested Hours: {chargeableWorkRequest.additionalTimeRequested}
                        </div>
                        <div>
                            Requested At: {moment(chargeableWorkRequest.requestedAt).format('HH:mm DD/MM/YYYY')}
                        </div>
                        <div>
                            Requested By: {chargeableWorkRequest.requesterFullName}
                        </div>
                    </React.Fragment>
                )}
                footer={
                    <div key="footer">
                        <button key="resendEmailButton"
                                onClick={this.resendEmail}
                        >
                            Resend Email
                        </button>
                        <button key="cancelRequestButton"
                                onClick={() => this.cancelRequest()}
                        >
                            Cancel Request
                        </button>
                    </div>
                }
            />
        )
    }
}

ExistingAdditionalChargeableWorkRequestModal.propTypes = {
    onClose: PropTypes.func,
    show: PropTypes.bool,
    chargeableWorkRequestId: PropTypes.string
};