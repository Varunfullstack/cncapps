import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Spinner from "../../shared/Spinner/Spinner";
import APIProjects from '../services/APIProjects';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';


export default class ProjectsCalendarComponent extends MainComponent {
    api = new APIProjects();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            projects: []

        };
    }

    componentDidMount() {
        this.getData();
    }

    getData() {
        this.setState({showSpinner: true});
        this.api.getProjects(this.props.projectID).then(projects => {
            this.setState({projects, showSpinner: false});
        })
    }

    getCalendar = () => {
        const {projects} = this.state;
        if (projects.length == 0)
            return null;
        const events = projects.filter(p => p.commenceDate != null).map(p => {
            return {
                title: p.description,
                //date:p.commenceDate,
                start: p.commenceDate,
                end: p.expectedHandoverQADate,
                id: p.projectID,
                eventContent: "test"
            }
        })
        return <FullCalendar
            plugins={[dayGridPlugin]}
            initialView="dayGridMonth"
            weekends={true}
            events={events}
            eventContent={this.handleEventRender}
            eventClick={this.handleEventClick}
        />
    }
    handleEventClick = (event) => {
        window.open(`Projects.php?action=edit&&projectID=${event.event._def.publicId}`, '_blank');
    }
    handleEventRender = (arg) => {
        const {projects} = this.state;
        const project = projects.find(p => p.projectID == arg.event.id);
        let eventEl = document.createElement('div')
        eventEl.innerHTML = `
      <p>${project.customerName}</p>
      <p>${project.description}</p>
      <p>${project.assignedEngineer}</p>            
      `;
        let arrayOfDomNodes = [eventEl]

        return {domNodes: arrayOfDomNodes}

    }

    render() {
        return <div>
            <Spinner key="spinner"
                     show={this.state.showSpinner}
            />
            {this.getCalendar()}
        </div>
    }

}

  