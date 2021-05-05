import MainComponent from "../shared/MainComponent.js";
import APICallactType from "../services/APICallacttype.js";
import APIUser from "../services/APIUser.js";
import Modal from "../shared/Modal/modal";

import React from 'react';
import Toggle from "../shared/Toggle.js";

/**
 * onCancel -> call when cancel button click
 *
 */
class ActivityFollowOn extends MainComponent {
    el = React.createElement;
    apiCallactType = new APICallactType();
    apiUser = new APIUser();

    constructor(props) {
        super(props);
        this.state = {...this.state, types: [], callActTypeID: "", isInbound: null};
    }

    componentDidMount() {
        Promise.all([
            this.apiCallactType.getAll(),
            this.apiUser.getCurrentUser(),
        ]).then((result) => {
            const currentUser = result[1];
            let types = result[0];

            if (!currentUser.isSDManager) {
                types = types.filter(c => c.visibleInSRFlag == 'Y')
            }
            this.setState({types});
        });
    }

    getModal = () => {
        const {el} = this;
        const {types, isInbound} = this.state;
        const Inbound = isInbound == null ? false : isInbound;
        const Outbound = isInbound == null ? false : !isInbound;
        const isCustomerContactActivity = this.state.callActTypeID == 11;
        return el(Modal, {
            key: "followOnModal",
            show: true,
            width: 400,
            title: "Create a follow on activity",
            onClose: this.handleCancel,
            content: (
                <div key="divContainer">
                    <label>Activity Type</label>
                    <select
                        required={true}
                        value={this.state.callActTypeID}
                        onChange={(event) =>
                            this.setState({callActTypeID: event.target.value})
                        }
                        style={{width: "100%", marginBottom: 20}}
                        autoFocus={true}
                    >
                        <option key="empty" value="">
                            Please select
                        </option>
                        {types?.map((t) => (
                            <option key={t.id} value={t.id}>
                                {t.description}
                            </option>
                        ))}
                    </select>
                    {isCustomerContactActivity ? (
                        <div
                            style={{
                                display: "flex",
                                flexDirection: "row",
                                justifyContent: "space-between",
                            }}
                        >
                            <div>
                                <label className="mr-2">Inbound</label>
                                <Toggle
                                    checked={Inbound}
                                    onChange={() => this.setState({isInbound: true})}
                                />
                            </div>
                            <div>
                                <label className="mr-2">Outbound</label>
                                <Toggle
                                    checked={Outbound}
                                    onChange={() => this.setState({isInbound: false})}
                                />
                            </div>
                        </div>
                    ) : null}
                </div>
            ),
            footer:
                <div key="divFooter">
                    <button onClick={this.handleCreate}
                            disabled={isCustomerContactActivity && isInbound === null}>Create
                    </button>
                    <button onClick={this.handleCancel}>Cancel</button>
                </div>
        });
    };
    handleCreate = async () => {
        const {callActivityID} = this.props;
        let {callActTypeID, isInbound} = this.state;
        if (callActTypeID == "") {
            await this.alert("Please select Activity Type");
            return;
        }
        let append = `&&inbound=${isInbound}`
        if (callActTypeID != 11) {
            append = "";
        }

        // if (startWork) {
        //     // if (
        //     //   confirm(
        //     //     "You are about to commence work and an email will be sent to the customer?"
        //     //   )
        //     // )
        //     window.location = `Activity.php?action=createFollowOnActivity&callActivityID=${callActivityID}&callActivityTypeID=${callActTypeID}`;
        // } else

        window.location = `Activity.php?action=createFollowOnActivity&callActivityID=${callActivityID}&callActivityTypeID=${callActTypeID}${append}`;
    };
    handleCancel = () => {
        if (this.props.onCancel) this.props.onCancel();
    };

    render() {
        return this.el('div', null,
            this.getAlert(),
            this.getConfirm(),
            this.getModal()
        );
    }
}

export default ActivityFollowOn;
