import React from 'react';
import MainComponent from '../../shared/MainComponent';
import APIDailyReport from '../services/APIDailyReport';

class SummaryComponent extends MainComponent {    
    api = new APIDailyReport(); 

    constructor(props) {
        super(props);
        this.state = {         
            selectedYear:moment().year(),
            yearData:[], 
            years:[]           
        };
    }
    // static getderivedstatefromprops(props,state)
    // {
    //     if(this.props.years)
    // }
    componentDidMount=async()=> {        
        const years=await this.api.getYears();
        this.setState({years});
        this.getYearData(moment().year());
        console.log('years',years);
    }

   getYearsElement=()=>{
       const {years}=this.state;
       const {selectedYear}=this.state;
       let y=years.length>0?years[0].YEAR:null;       
       if(!years)
        return null;
       return <select value={selectedYear||y} onChange={(event)=>this.handleYearChange(event.target.value)}>
           {
               years.map(y=><option key={y.YEAR} value={y.YEAR}>{y.YEAR}</option>)
           }           
       </select>
   }
   handleYearChange=(year)=>{
    this.setState({selectedYear:year});
    this.getYearData(year);

   }
   getYearData=(year)=>{
    this.api.getOutStandingPerYear(year).then(yearData=>{
        console.log(yearData);  
        this.setState({yearData})      
    })
   }
   getMonthsElement=()=>{
       const {yearData}=this.state;
       return <table className="table table-striped" style={{maxWidth:200+yearData.length*40}}>
           <thead>
               <tr>
                   <th></th>
                   {yearData.map((y,i)=><th key={i}>{moment(y.month,'MM').format("MMM")}</th>)}
               </tr>
           </thead>
           <tbody>
               <tr>
                   <td>Average Number of 7 Dayers	</td>
                   {yearData.map((y,i)=><td key={i} style={{color:this.getColor(y.olderThan7DaysAvg,y.targetAvg)}}>{y.olderThan7DaysAvg.toFixed(1)}</td>)}
               </tr>
               <tr>
                   <td>Target		</td>
                   {yearData.map((y,i)=><td key={i} style={{color:this.getColor(y.olderThan7DaysAvg,y.targetAvg)}}>{y.targetAvg}</td>)}
               </tr>
           </tbody>
       </table>
   }
   getColor(avg,target){
    if(avg>target)
    return 'red';
    if(avg<target)
    return 'green';
    
   }
    render() {        
        return <div>
            {
                this.getYearsElement()
            }
            {
                this.getMonthsElement()
            }
        </div> 
    }
}

export default SummaryComponent;