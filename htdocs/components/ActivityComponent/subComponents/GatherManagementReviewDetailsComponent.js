import MainComponent from "../../shared/MainComponent";
import APIActivity from "../../services/APIActivity";
import ToolTip from "../../shared/ToolTip";
import {params} from "../../utils/utils";
import React from 'react';
import EditorFieldComponent from "../../shared/EditorField/EditorFieldComponent";

class GatherManagementReviewDetailsComponent extends MainComponent {
    el = React.createElement;
    apiActivity = new APIActivity();

    constructor(props) {
        super(props);
        this.state = {...this.state, description: ""};
    }

    getHistoryLink() {
        return <a
            className="fal fa-history fa-2x icon pointer m-4"
            href={`Activity.php?problemID=${params.get("problemID")}&action=problemHistoryPopup&htmlFmt=popup`}
            target="_blank"
        />
    }

    getElements = () => {
        const {description} = this.state;

        return <div style={{flex: 1, width: 850, justifyContent: "flext-start"}}>
            <ToolTip width="50"
                     title="History"
                     content={this.getHistoryLink()}
            />
            <label className="m-5"
                   style={{fontSize: 18, display: "block"}}
            >
                Why does this SR require review by management?
            </label>
            <EditorFieldComponent name="managementReviewDetails"
                                  value={description}
                                  onChange={(value) => this.setState({description: value})}
            />

            <button onClick={this.handleOnSave}>Save</button>
        </div>
    };
    handleOnSave = () => {
        const {description} = this.state;
        if (description == "") {
            this.alert("Please enter reason for management review");
            return;
        }
        const payload = {
            problemID: params.get("problemID"),
            description
        }
        this.apiActivity.saveManagementReviewDetails(payload).then(res => {
            if (res.status)
                window.location = `CurrentActivityReport.php`
        })
    }

    render() {
        return this.el('div', null,
            this.getAlert(),
            this.getElements()
        );
    }
}

export default GatherManagementReviewDetailsComponent;
