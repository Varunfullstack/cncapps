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
            cancelReason: ''
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
        const {cancelReason} = this.state;
        await this.api.cancelChargeableRequest(chargeableWorkRequestId, cancelReason);
        this.props.onClose(true);
    };

    signalClose = (closingValue) => {
        this.props.onClose(closingValue);
    }

    updateCancelReason = ($event) => {
        this.setState({cancelReason: $event.target.value});
    }

    render() {
        const {show} = this.props;
        const {chargeableWorkRequest, cancelReason} = this.state;
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
                        <div>
                            Reason: <div dangerouslySetInnerHTML={{__html: chargeableWorkRequest.reason}}/>
                        </div>
                        <div style={{borderTop: "0.1em solid #e9ecef", paddingTop: "1em"}}>
                            <label>
                                Cancel Reason
                            </label>
                            <br/>
                            <input style={{width: "50%"}} value={cancelReason} onChange={this.updateCancelReason}/>
                            <button key="cancelRequestButton"
                                    disabled={!cancelReason}
                                    onClick={() => this.cancelRequest()}
                            >
                                Cancel Request
                            </button>
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