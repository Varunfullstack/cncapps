"use strict";
import React from 'react'; 
import APIHeader from '../../services/APIHeader';
import MainComponent from '../../shared/MainComponent';
export default class PortalDocumentComponent extends MainComponent { 
    api = new APIHeader();
    constructor(props) {
        super(props);
        this.state = {
          ...this.state,        
          data: {          
          },         
        };        
      }
    componentDidMount() {
        this.getData();
    } 
    getData=()=>{
        this.api.getHeaderData
    }
    getDocuments=()=>{

    }
    render() {
        return (
            <div>
                {this.getChart()}
                {this.getSummaryElement()}
            </div>
        );
    }
}
 