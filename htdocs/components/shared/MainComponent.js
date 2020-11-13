import Alert from "./Alert.js";
import Confirm from "./Confirm.js";
import Prompt from "./Prompt.js";

export default class MainComponent extends React.Component {
    constructor(props) {
        super(props);
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

    isSDManger(user) {
        return user.isSDManger;
    }

    redirectPost(url, data) {
        var form = document.createElement("form");
        document.body.appendChild(form);
        form.method = "post";
        form.action = url;
        for (var name in data) {
            var input = document.createElement("input");
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
        return this.el(Alert, {...alert, onClose: this.handleAlertClose, key: "alert"});
    }
    handleAlertClose = () => {
        const {alert} = this.state;
        alert.show = false;
        alert.message = "";
        alert.title = "";
        alert.width = 500
        this.setState({alert})
    }
    //-----------------end alert
    //----------------confirm
    confirm = (message, width = 500, title = "Confirm") => {
        const {confirm} = this.state;
        confirm.show = true;
        confirm.width = width;
        confirm.title = title;
        confirm.message = message;
        confirm.result = null;
        this.setState({confirm});
        return new Promise((resolve, reject) => {
            setInterval(() => {
                if (this.state.confirm.result != null)
                    resolve(this.state.confirm.result);
            }, 100);
        });


    }
    getConfirm = () => {
        const {confirm} = this.state;
        return this.el(Confirm, {...confirm, onClose: this.handleConfirmClose, key: 'confirm'});
    }
    handleConfirmClose = (value) => {
        //  console.log(value);
        const {confirm} = this.state;
        confirm.show = false;
        confirm.message = "";
        confirm.title = "";
        confirm.width = 500
        confirm.result = value;
        this.setState({confirm})
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
        //console.log(value);
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
