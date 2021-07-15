import MainComponent from "../../shared/MainComponent.js";
import React from "react";
import Toggle from "../../shared/Toggle.js";
import APIStarterLeaverManagement from "../services/APIStarterLeaverManagement.js";
import Table from "../../shared/table/table.js";
import QuestionDetailsComponent from "./QuestionDetailsComponent.js";
import {equal} from "../../utils/utils.js";
import ToolTip from "../../shared/ToolTip.js";

export default class QuestionsComponent extends MainComponent {
    api = new APIStarterLeaverManagement();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            data: {
                customerID: "",
                type: "y/n",
                formType: "starter",
                required: "",
                multi: "",
                options: [],
                name: "",
                label: "",
            },
            optionValue: "",
            showModal: false,
            customer: null,
            questions: []
        };
    }

    componentDidMount() {
        this.getCustomerQuestions()


    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        if (!equal(prevProps.customer, this.state.customer)) {
            this.getCustomerQuestions();
        }
    }

    static getDerivedStateFromProps(props, state) {
        state.customer = props.customer;
        return state;
    }

    handleCancel = () => {
        if (this.props.onClose) this.props.onClose();

    };
    handleDeleteQuestion = async (question) => {
        if (await this.confirm("Are you sure to delete this question?"))
            this.api.deleteQuestion(question).then(res => {
                this.getCustomerQuestions();
            }, error => {
                this.alert("Error in delete question")
            })
    };


    hanldeSave = () => {
        const {data} = this.state;
        if (!this.isFormValid("form")) {
            this.alert("Please add all required inputs");

        }
    };
    handleAddOptions = () => {
        const {optionValue, data} = this.state;
        if (this.state.optionValue != "") {
            const indx = data.options.indexOf(optionValue);
            if (indx >= 0)
                this.alert("Option exist")
            else
                data.options.push(optionValue);
            this.setState({data, optionValue: ""});
        } else
            this.alert("Please enter the option text")

    }
    getContent = () => {
        const {questions} = this.state;
        const columns = [
            {
                path: "name",
                label: "Name",
                hdToolTip: "Name",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                //className: "text-center",
            },
            {
                path: "formType",
                label: "Type",
                hdToolTip: "Type",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                className: "text-center",
            },
            {
                path: "label",
                label: "Label",
                hdToolTip: "Label",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                //className: "text-center",
            },
            {
                path: "required",
                label: "Required",
                hdToolTip: "Required",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                className: "text-center",
                content: (question) => <Toggle disabled={true} checked={question.required}></Toggle>
            },
            {
                path: "type",
                label: "Type",
                hdToolTip: "Type",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                className: "text-center",
            },
            {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,
                className: "text-center",
                content: (question) => this.getEditElement(question, this.handleEdit)
            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Delete",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-trash color-gray2 pointer",
                sortable: false,
                className: "text-center",
                content: (question) => this.getDeleteElement(question, this.handleDeleteQuestion)
            },

        ]
        // if(questions.length==0)
        // return null;
        return <Table
            pk="questionID"
            columns={columns}
            search={true}
            data={questions}
            onOrderChange={this.handleOrderChange}
            allowRowOrder={true}
        >
        </Table>


    };
    handleOrderChange = async (current, next) => {
        const {questions} = this.state;
        if (next) {
            current.sortOrder = next.sortOrder;
            next.sortOrder = current.sortOrder + 0.001;
            await this.api.updateQuestion(next);
        }
        if (!next) {
            current.sortOrder = Math.max(...questions.map(i => i.sortOrder)) + 0.001;
        }

        await this.api.updateQuestion(current);
        this.getCustomerQuestions();
    }
    handleEdit = (question) => {
        this.setState({data: question, showModal: true})
    }
    handleCloseModal = (load = false) => {
        this.setState({showModal: false});
        if (load)
            this.getCustomerQuestions();
    }
    getCustomerQuestions = () => {
        const {customer} = this.state;
        if (customer)
            this.api.getCustomerQuestions(customer.id).then(result => {
                this.setState({questions: result.data});
            })
    }
    handleNewQuestion = () => {
        if (!this.props.customer) {
            this.alert("Please select customer");
            return;
        }
        const data = {
            customerID: this.props.customer.id,
            formType: "starter",
            label: "",
            multi: 0,
            name: "",
            options: null,
            questionID: null,
            required: 0,
            type: "free"
        }
        this.setState({data, showModal: true})
    }

    render() {
        return (
            <div className="ml-3">
                {this.getAlert()}
                {this.getConfirm()}
                <div className="flex-row" style={{alignItems: "center"}}>
                    <h3>Questions</h3>
                    <ToolTip width={30} title="Add New Question">
                        <i
                            className="fal fa-2x fa-plus color-gray2 pointer mb-5"
                            onClick={this.handleNewQuestion}
                        ></i>
                    </ToolTip>
                </div>
                {this.getContent()}
                <QuestionDetailsComponent
                    data={this.state.data}
                    customer={this.props.customer}
                    onClose={this.handleCloseModal}
                    show={this.state.showModal}
                ></QuestionDetailsComponent>
            </div>
        );
    }

}
