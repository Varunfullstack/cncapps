import MainComponent from "../../shared/MainComponent.js";
import APICallactType from "../../services/APICallacttype.js";
import Icon from "../../shared/Icon.js";
import Table from "../../shared/table/table";
import {sort} from "../../utils/utils.js";
import React from 'react';

class ActivityListComponent extends MainComponent {
    el = React.createElement;
    apiCallactType = new APICallactType();

    constructor(props) {
        super(props);
        this.state = {
            types: [],
            filterColumn: 'all'
        }
    }

    async getItems() {
        const types = await this.apiCallactType.getAllWithDetails();
        sort(types, "order");
        this.setState({types})
    }

    componentDidMount = async () => {
        this.getItems();
    }
    getColumnsFilter = () => {
        const {el} = this;
        const {filterColumn} = this.state;
        const columns = [

            {
                path: "all",
                label: "All",
                sortable: true,
            },
            {
                label: "Visible in SR",
                path: "visibleInSRFlag",
                sortable: true,
            },
            {
                label: "Active",
                path: "activeFlag",
                sortable: true,
            },
            {
                label: "Value",
                path: "curValueFlag",
                sortable: true,
            },
            {
                label: "Multiplier",
                path: "oohMultiplier",
                sortable: true,
            },
            {
                label: "Min Hours",
                path: "minHours",
                sortable: true,
            },
            {
                label: "Max Hours",
                path: "maxHours",
                sortable: true,
            },
            {
                label: "Send Email",
                path: "customerEmailFlag",
                sortable: true,
            },
            {
                label: "Portal",
                path: "portalDisplayFlag",
                sortable: true,
            },
            {
                label: "Allow SCR Printing",
                path: "allowSCRFlag",
                sortable: true,
            },
            {
                label: "Require Checking",
                path: "requireCheckFlag",
                sortable: true,
            },
            {
                label: "Travel",
                path: "travelFlag",
                sortable: true,
            },
            {
                label: "Engineer Over Time",
                path: "engineerOvertimeFlag",
                sortable: true,
            },
            {
                label: "On-site",
                path: "onSiteFlag",
                sortable: true,
            },
            {
                label: "Activity Notes Required",
                path: "activityNotesRequired",
                sortable: true,
            },
            {
                label: "Require CNC Next Action, CNC Action",
                path: "catRequireCNCNextActionCNCAction",
                sortable: true,
            },
            {
                label: "Require CNC Next Action On Hold",
                path: "catRequireCNCNextActionOnHold",
                sortable: true,
            },
            {
                label: "Require Customer Note CNC Action",
                path: "catRequireCustomerNoteCNCAction",
                sortable: true,
            },
            {
                label: "Require Customer Note On Hold",
                path: "catRequireCustomerNoteOnHold",
                sortable: true,
            },
            {
                label: "Min Minutes Allowed",
                path: "minMinutesAllowed",
                sortable: true,
            },

        ];
        return el('div', {className: "flex-row mb-5"},
            el('label', {style: {display: "block", width: 38}}, "Filter"),
            el('select', {
                    style: {width: 158},
                    value: filterColumn,
                    onChange: (event) => this.setState({filterColumn: event.target.value})
                },
                columns.map(c => el('option', {key: c.path, value: c.path}, c.label))
            ));
    }
    getListElement = () => {
        const {types, filterColumn} = this.state;
        const {el} = this;
        const columns = [
            {
                label: "#",
                path: "order",
                sortable: true,
                content: (type) =>
                    this.el(Icon, {
                        title: "Move Down",
                        name: "fal fa-sort",
                        size: 4,
                        onClick: () => this.handleEdit(type)
                    })
            },
            {
                path: "description",
                label: "Activity Type",
                sortable: true,
                content: (item) => el('div', {
                    dangerouslySetInnerHTML: {__html: item.description}
                })
            },
            {
                label: "Visible in SR",
                path: "visibleInSRFlag",
                sortable: true,
            },
            {
                label: "Active",
                path: "activeFlag",
                sortable: true,
            },
            {
                label: "Value",
                path: "curValueFlag",
                sortable: true,
            },
            {
                label: "Multiplier",
                path: "oohMultiplier",
                sortable: true,
            },
            {
                label: "Min Hours",
                path: "minHours",
                sortable: true,
            },
            {
                label: "Max Hours",
                path: "maxHours",
                sortable: true,
            },
            {
                label: "Send Email",
                path: "customerEmailFlag",
                sortable: true,
            },
            {
                label: "Portal",
                path: "portalDisplayFlag",
                sortable: true,
            },
            {
                label: "Allow SCR Printing",
                path: "allowSCRFlag",
                sortable: true,
            },
            {
                label: "Require Checking",
                path: "requireCheckFlag",
                sortable: true,
            },
            {
                label: "Travel",
                path: "travelFlag",
                sortable: true,
            },
            {
                label: "Engineer Over Time",
                path: "engineerOvertimeFlag",
                sortable: true,
            },
            {
                label: "On-site",
                path: "onSiteFlag",
                sortable: true,
            },
            {
                label: "Require CNC Next Action, CNC Action",
                path: "catRequireCNCNextActionCNCAction",
                sortable: true,
            },
            {
                label: "Require CNC Next Action On Hold",
                path: "catRequireCNCNextActionOnHold",
                sortable: true,
            },
            {
                label: "Require Customer Note CNC Action",
                path: "catRequireCustomerNoteCNCAction",
                sortable: true,
            },
            {
                label: "Require Customer Note On Hold",
                path: "catRequireCustomerNoteOnHold",
                sortable: true,
            },
            {
                label: "Min Minutes Allowed",
                path: "minMinutesAllowed",
                sortable: true,
            },
            {
                label: "",
                path: "",
                content: (type) => this.el(Icon, {
                    title: "Edit",
                    name: "fal fa-edit",
                    size: 3,
                    onClick: () => this.handleEdit(type)
                })
            },
        ];
        let columnsFilter;
        if (filterColumn !== 'all')
            columnsFilter = columns.filter(c => c.path == filterColumn || c.path == "" || c.path == "description");
        else
            columnsFilter = [...columns];

        return this.el(
            "div",
            {style: {width: filterColumn !== 'all' ? 500 : "100%"}},
            this.getColumnsFilter(),
            this.el(Table, {
                id: "activityList",
                data: types || [],
                columns: columnsFilter,
                pk: "callActTypeID",
                search: true,
                allowRowOrder: true,
                onOrderChange: this.handleOrderChange,
                defaultSortPath: 'order',
                defaultSortOrder: 'asc'
            })
        );
    }
    handleOrderChange = (current, next) => {
        this.apiCallactType.updateActivityTypeOrder(current, next)
            .then(res => {
                return this.getItems();
            });
    }
    handleEdit = (type) => {
        window.location = `ActivityType.php?action=editActivityType&callActTypeID=${type.callActTypeID}`
    }

    render() {
        return this.getListElement();
    }
}

export default ActivityListComponent;