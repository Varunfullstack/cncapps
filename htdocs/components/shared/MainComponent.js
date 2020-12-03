import Alert from "./Alert.js";
import Confirm from "./Confirm.js";
import Prompt from "./Prompt.js";

import React from 'react';

export default class MainComponent extends React.Component {

    constructor(props) {
        super(props);
        this.el = React.createElement;
        this.state = {
            alert: {
                show: false,
                title: "",
                width: 500,
                message: "",
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
                message: "",
                value: null,
                defaultValue: null
            },
        };
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
    alert = (message, width = 500, title = "Alert") => {
        const {alert} = this.state;
        alert.show = true;
        alert.width = width;
        alert.title = title;
        alert.message = message;
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
            show={alert.show}
            width={alert.width}
            title={alert.title}
            message={alert.message}
            onClose={() => this.handleAlertClose()}
        />;
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
    prompt = (title = "Prompt", width = 500, defaultValue = null) => {
        const {prompt} = this.state;
        prompt.show = true;
        prompt.width = width;
        prompt.title = title;
        prompt.value = null;
        prompt.defaultValue = defaultValue;
        this.setState({prompt});
        let handleInterval = null;
        return new Promise((resolve, reject) => {
            handleInterval = setInterval(() => {
                if (this.state.prompt.value != null) {
                    resolve(this.state.prompt.value);
                    clearInterval(handleInterval);
                }
            }, 100);
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
        this.setState({prompt})
    }
    //-----------------end alert
    setValue = (property, value) => {
        const {data} = this.state;
        data[property] = value;
        this.setState({data});
    }
}
