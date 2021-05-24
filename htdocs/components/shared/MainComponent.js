import Alert from "./Alert.js";
import Confirm from "./Confirm.js";
import Prompt from "./Prompt.js";

import React from 'react';
import APIHeader from '../services/APIHeader';
import ToolTip from "./ToolTip.js";

export default class MainComponent extends React.Component {

    promptCallback;
    api;

    constructor(props) {
        super(props);
        this.el = React.createElement;
        this.state = {
            alert: {
                show: false,
                title: "",
                width: 500,
                message: "",
                isHTML: false,
                autoClose: true,
            },
            confirm: {
                show: false,
                title: "",
                width: 500,
                message: "",
                result: null
            },
            prompt: {
                show: false,
                title: "",
                width: 500,
                height: 20,
                message: "",
                value: null,
                defaultValue: null,
                isEditor: false
            },
        };
        this.apiHeader = new APIHeader();
    }

    isSDManager(user) {
        return user.isSDManager;
    }

    redirectPost(url, data) {
        let form = document.createElement("form");
        document.body.appendChild(form);
        form.method = "post";
        form.action = url;
        for (let name in data) {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = name;
            input.value = data[name];
            form.appendChild(input);
        }
        form.submit();
    }

    openPopup(url) {
        window.open(
            url,
            "reason",
            "scrollbars=yes,resizable=yes,height=550,width=500,copyhistory=no, menubar=0"
        );
    }

    //----------------alert
    alert = (message, width = 500, title = "Alert", isHTML = false, autoClose = true) => {
        const {alert} = this.state;
        alert.show = true;
        alert.width = width;
        alert.title = title;
        alert.message = message;
        alert.isHTML = isHTML;
        alert.autoClose = autoClose;
        this.setState({alert});
        return new Promise((resolve, reject) => {
            setInterval(() => {
                if (!this.state.alert.show)
                    resolve(true);
            }, 100);
        });
    }
    getAlert = () => {
        const {alert} = this.state;
        return <Alert
            key={"alert"}
            show={alert.show}
            width={alert.width}
            title={alert.title}
            message={alert.message}
            isHTML={alert.isHTML}
            onClose={() => this.handleAlertClose()}
            onAutoClose={this.handleAlertAutoClose}
            autoClose={alert.autoClose}
        />;
    }
    handleAlertAutoClose = () => {
        const {alert} = this.state;
        alert.show = false;
        this.setState({alert});
    }
    handleAlertClose = () => {
        const {alert} = this.state;
        alert.show = false;
        alert.message = "";
        alert.title = "";
        alert.width = 500
        this.setState({alert})
    }

    confirm(message, width = 500, title = "Confirm") {
        return new Promise(resolve => {
            this.setState(
                {
                    confirm: {
                        show: true,
                        width,
                        title,
                        message,
                        onClose: (value) => {
                            resolve(value);
                            this.clearConfirm();
                        }
                    }
                }
            );
        });
    }

    getConfirm() {
        const {confirm} = this.state;
        return (
            <Confirm
                show={confirm.show}
                width={confirm.width}
                title={confirm.title}
                message={confirm.message}
                onClose={confirm.onClose}
                key="confirmThingy"
            />
        );
    }

    clearConfirm() {
        const {confirm} = this.state;
        confirm.show = false;
        confirm.message = "";
        confirm.title = "";
        confirm.width = 500
        this.setState({confirm});
    }

    //-----------------end alert
    //----------------prompt
    prompt = (title = "Prompt", width = 500, defaultValue = null, isEditor = false, height = 20) => {
        const {prompt} = this.state;
        prompt.show = true;
        prompt.width = width;
        prompt.height = height;
        prompt.title = title;
        prompt.value = null;
        prompt.defaultValue = defaultValue;
        prompt.isEditor = isEditor;
        this.setState({prompt});
        return new Promise((resolve, reject) => {
            this.promptCallback = (value) => {
                resolve(value)
            }
        });
    }
    getPrompt = () => {
        const {prompt} = this.state;
        return this.el(Prompt, {...prompt, onClose: this.handlePromptClose, key: "prompt"});
    }
    handlePromptClose = (value) => {

        const {prompt} = this.state;
        prompt.show = false;
        prompt.title = "";
        prompt.width = 500
        prompt.value = value;
        this.promptCallback(value);
        this.setState({prompt})
    }
    //-----------------end alert
    setValue = (property, value) => {
        const {data} = this.state;
        data[property] = value;
        this.setState({data});
    }
    setFilter = (field, value, callback = null) => {
        const {filter} = this.state;
        filter[field] = value;
        this.setState({filter}, callback);
    }
    editorHasProblems = async () => {
        return this.apiHeader.getNumberOfAllowedMistaks().then(nMistakes => {
            const wscInstances = WEBSPELLCHECKER.getInstances();
            let count = wscInstances.reduce((acc, instance) => {
                const containerNode = instance.getContainerNode();
                if (containerNode.classList.contains('excludeFromErrorCount')) {
                    return acc;
                }

                if (!instance.isAllModulesReady()) {
                    return acc;
                }

                return acc + instance.getProblemsCount();
            }, 0)
            if (count > nMistakes) {
                this.alert("You have too many spelling or grammatical errors, please correct them before proceeding.");
                return true
            }
            return false;
        });
    }

    getCorrectDate(date, hasTime = false) {
        let format = "DD/MM/YYYY";
        if (hasTime)
            format += " HH:mm";
        if (date != '' && date != null)
            return moment(date).format(format);
        else return '';
    }

    isEmpty(variable) {
        if (variable == null || variable == undefined || variable == '')
            return true;
        else
            return false;
    }

    getTrueFalseElement(value) {
        return value ? <i className="fal fa-2x fa-check color-gray "></i> :
            <i className="fal fa-2x fa-times color-gray "></i>
    }

    getEditElement(obj, callBack, display = true) {
        if (!display)
            return null;
        return <i className="fal fa-2x fa-edit color-gray pointer"
                  onClick={() => callBack(obj)}
        ></i>
    }

    getSearchElement(callBack) {     
        return <ToolTip title="Search" width={30}>
        <i className="fal fa-2x fa-search color-gray pointer"
                  onClick={() => callBack()}
        ></i>
        </ToolTip>
    }

    getEditIcon() {
        return "fal fa-2x fa-edit color-gray2 pointer";
    }

    getDeleteElement(obj, callBack, display = true) {
        if (!display)
            return null;
        return <i className="fal fa-2x fa-trash-alt color-gray pointer"
                  onClick={() => callBack(obj)}
        ></i>
    }

    getDeleteIcon() {
        return "fal fa-2x fa-trash-alt color-gray2 pointer";
    }

    getTableStyle() {
        return "table table-striped";
    }
    
    isFormValid=(id)=>{
        const elements=$(`#${id} :input`);  
        //console.log(elements);
        for(let i=0;i<elements.length;i++)
        {
            if($(elements[i]).prop('required')&&elements[i].value=="")
                return false;
        }
        return true;
      }
}
