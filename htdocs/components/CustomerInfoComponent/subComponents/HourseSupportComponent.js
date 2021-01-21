import MainComponent from "../../shared/MainComponent";
import React from 'react';

class HourseSupportComponent extends MainComponent {
    el = React.createElement;
   

    constructor(props) {
        super(props);
        this.state = {                        
        };
    }

    componentWillUnmount() {
     }

    componentDidMount() {
      
    }
     
    render() {
        const {el} = this;
        return <div>Welcome to support hours</div>;
    }
}

export default HourseSupportComponent;