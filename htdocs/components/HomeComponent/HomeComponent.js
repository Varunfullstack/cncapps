"use strict";
import {params, sort} from "../utils/utils";
import ReactDOM from 'react-dom';
import React from 'react';
import '../style.css';
import './HomeComponent.css';
import '../shared/table/table.css';
import APIHome from "./services/APIHome";
import Spinner from "../shared/Spinner/Spinner";
import MainComponent from "../shared/MainComponent";
class HomeComponent extends MainComponent {
    api=new APIHome();
    cards=[];
    CARD_UPCOMING_VISITS=1;
    CARD_SALES_FIGURES=2;
    CARD_FIXED_REOPEN=3;
    CARD_FIRST_TIME_FIXED=4;
    CARD_TEAM_PERFORMANCE=5;
    CARD_USER_PERFORMANCE=6;
    CARD_DAILT_STATS=7;
    CARD_CHARTS=8;
    constructor(props) {
        super(props);
        this.state={
            cards:this.getCards(true),
            showSpinner:false,
            upcomingVisit:[],
            salesFigures:{},
            fixedReopen:[],
            firstTimeFixed:{},
            teamPerformance:[],
            allUserPerformance:[]
        }
    }
    componentDidMount() {
        this.initDragResize();    
        this.getData();
    }
    initDragResize=()=>{
        setTimeout(() => {
            $(".card").draggable({              
            stop: this.updateIndex
            }).disableSelection();
            $(".card").resizable({
                stop: this.updateIndex,
                resize: this.updateIndex,
            });
        }, 100);
    }
    getData=()=>
    {
        const requests=[
            this.api.getUpcomingVisits(),
            this.api.getSalesFigures(),
            this.api.getFixedAndReopenData(),
            this.api.getFirstTimeFixData(),
            this.api.getTeamPerformance(),
            this.api.getAllUserPerformance()
        ]
        this.setState({showSpinner:true});
        Promise.all(requests).then(([upcomingVisit,salesFigures,fixedReopen,firstTimeFixed,teamPerformance,allUserPerformance])=>{
            console.log(upcomingVisit,salesFigures,fixedReopen,firstTimeFixed,teamPerformance,allUserPerformance);
            this.setState({showSpinner:false,upcomingVisit,salesFigures,fixedReopen,firstTimeFixed,teamPerformance,allUserPerformance});
        })
    }
    /**
     *
     * @param {place element} e
     * @param {drag element} ui
     */
    updateIndex = (e, ui) => {     
        
       const cardsElements=document.getElementsByClassName("card");
       const {cards}=this.state;              
       for(let i=0;i<cardsElements.length;i++)
       {                     
            cards[cardsElements[i].id-1].order=i+1;
            cards[cardsElements[i].id-1].top=this.getCorrectValue(cardsElements[i].style.top);
            cards[cardsElements[i].id-1].left=this.getCorrectValue(cardsElements[i].style.left);
            cards[cardsElements[i].id-1].width=this.getCorrectValue(cardsElements[i].style.width);
            cards[cardsElements[i].id-1].height=this.getCorrectValue(cardsElements[i].style.height);
       }
        this.saveOrder();   
        console.log(cards);
        this.setState({cards});
    };
    saveOrder=()=>{
        const {cards}=this.state; 
        localStorage.setItem("homeCards",JSON.stringify(cards));
    }
    getCorrectValue(value){
        if(value)
        {
            const v= parseFloat(value.replace("px",""));
            if(v!=NaN)
            return v;
            else return null;
        }
        else return null;
    }
    getCards=(isOrigin=false)=>{
        let cards=localStorage.getItem("homeCards");
        let origin= [
            {
                id:this.CARD_UPCOMING_VISITS,
                order:1,
                title:"Upcoming Visits",
                minimize:false,
                position: "relative", 
                height: "", 
                width: "", 
                left: "", 
                top: "",
                scroll:true,
            },
            {
                id:this.CARD_SALES_FIGURES,
                order:2,
                title:"Sales Figures",
                minimize:false,
                position: "relative", 
                height: "", 
                width: "", 
                left: "", 
                top: "",
                scroll:true,
            },
            {
                id:this.CARD_FIXED_REOPEN,
                order:3,
                title:"Daily Fixed & Reopened Stats",
                minimize:false,
                position: "relative", 
                height: "", 
                width: "", 
                left: "", 
                top: "",
                scroll:true,
            },
            {
                id:this.CARD_FIRST_TIME_FIXED,
                order:4,
                title:"HD First Time Fixes",
                minimize:false,
                position: "relative", 
                height: "", 
                width: "", 
                left: "", 
                top: "",
                scroll:true,
            },
            {
                id:this.CARD_TEAM_PERFORMANCE,
                order:5,
                title:"Team Performance",
                minimize:false,
                position: "relative", 
                height: "", 
                width: "", 
                left: "", 
                top: "",
                scroll:true,
            },
            {
                id:this.CARD_USER_PERFORMANCE,
                order:6,
                title:"User Performance",
                minimize:false,
                position: "relative", 
                height: "", 
                width: "", 
                left: "", 
                top: "",
                scroll:true,
            },
            {
                id:this.CARD_DAILT_STATS,
                order:7,
                title:"Daily Stats",
                minimize:false,
                position: "relative", 
                height: 473, 
                width: 1166, 
                left: "", 
                top: "",
                scroll:false,
            },
            {
                id:this.CARD_CHARTS,
                order:8,
                title:"User Charts",
                minimize:false,
                position: "relative", 
                height: 473, 
                width: 1166, 
                left: "", 
                top: "",
                scroll:false,
            },
            
        ];
        if(isOrigin)
            return origin;
        if(cards)
            return JSON.parse(cards);
        else 
            return origin;        
    }
    getCardsElement=()=>{
        let {cards}=this.state;
        cards=sort(cards,"order");
       // console.log(cards);
        return (
          <div className="drag-card " style={{display:"flex",flexDirection:"row",flexWrap:"wrap" }}>
              {
                 cards.filter(c=>!c.minimize).map(c=>
                <div key={c.id} id={c.id}  className={"card text-left "+(c.minimize?"card-colapse":"") } 
                style={{height:c.height, width:c.width, top:c.top, left:c.left}}>
                    <div className="card-header" style={{display:"flex",flexDirection:"row", justifyContent:"space-between",alignItems:"center"}}>
                        <h4 className="card-title">{c.title}</h4>  
                        <i className={!c.minimize?"fa fa-minus pointer ml-4":"fa fa-plus"} onClick={()=>this.handleMinimizeCard(c)}></i>
                    </div>
                    <div className="card-body" style={{height:c.height-90,overflowY:c.scroll?"auto":""}}>                        
                        {
                            this.getCardBody(c)  
                        }
                    </div>
                </div>) 
              }            
          </div>
        );
    }
    handleMinimizeCard=(c)=>{
        console.log(c);
        const {cards}=this.state;
        const indx=cards.map(c=>c.id).indexOf(c.id);
        cards[indx].minimize=!cards[indx].minimize;
        this.setState({cards},()=>{
            this.initDragResize();
            this.saveOrder();
        });
        
    }
    getCardBody=(c)=>{
        switch(c.id)
        {
            case this.CARD_UPCOMING_VISITS:
                return this.getUpcomingVisitsElement();
            case this.CARD_SALES_FIGURES:
                return this.getSalesFigures();
            case this.CARD_FIXED_REOPEN:
                return this.getDailyFixed();
            case this.CARD_FIRST_TIME_FIXED:
                return this.getFirstTimeFix();
            case this.CARD_TEAM_PERFORMANCE:
                return this.getTeamPerformance();
            case this.CARD_USER_PERFORMANCE:
                return this.getUserPerformance();
            case this.CARD_DAILT_STATS:
                return this.getDailyStats();
            case this.CARD_CHARTS:
                return this.getTeamCharts();
        }
    }
    getUpcomingVisitsElement=()=>{
        const {upcomingVisit}=this.state;
        
        return <table className="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Customer</th>
                    <th>Engineer</th>
                    <th>Reason</th>
                    <th>SR</th>
                </tr>
            </thead>
            <tbody>
                {upcomingVisit.map((c,i)=><tr key={i} style={{backgroundColor: moment(c.date, 'YYYY-MM-DD').isSame(moment(), 'day') ? "#e6ffe6" : moment(c.date, 'YYYY-MM-DD').isSame(moment().add(1, 'day'), 'day') ?
                        '#ffffe6' : ''}}> 
                    <td> 
                        {moment(c.date, 'YYYY-MM-DD').format('DD/MM/YYYY')}
                    </td>
                    <td>        
                    { moment(c.time, 'HH:mm').format('A')}
                    </td>
                    <td>    
                    {c.customerName}                    
                    </td>
                    <td>
                        {c.engineerName}
                    </td>
                    <td>               
                        {c.reason.replace(/\n/g, " ")}         
                    </td>
                    <td>  
                    <a href= {'/SRActivity.php?callActivityID='+c.serviceRequestID+ "&action=displayActivity"}  > {c.serviceRequestID} </a>                                              
                    </td>
                </tr>)}
                
            </tbody>
        </table>
    }
    getSalesFigures=()=>{
        const { salesFigures } = this.state;
        return (
          <table className="table table-striped">
            <thead>
              <tr>
                <th></th>
                <th>Invoiced This Month</th>
                <th>Invoices Waiting</th>
                <th>Sales Orders </th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Cost</strong> </td>
                    <td>{salesFigures.invPrintedCost}</td>
                    <td>{salesFigures.invUnprintedCost}</td>
                    <td>{salesFigures.soCost}</td>
                    <td>{salesFigures.costTotal}</td>
                </tr>
                <tr>
                    <td><strong>Sale</strong> </td>
                    <td>{salesFigures.invPrintedSale}</td>
                    <td>{salesFigures.invUnprintedSale}</td>
                    <td>{salesFigures.soSale}</td>
                    <td>{salesFigures.saleTotal}</td>
                </tr>
                <tr>
                    <td><strong>Profit</strong> </td>
                    <td>{salesFigures.invPrintedProfit}</td>
                    <td>{salesFigures.invUnprintedProfit}</td>
                    <td>{salesFigures.soProfit}</td>
                    <td>{salesFigures.profitTotal}</td>
                </tr>
            </tbody>
          </table>
        );
    }
    getDailyFixed=()=>{
        const {fixedReopen}=this.state;
        return (
          <table className="table table-striped">
            <thead>
                <tr>
                    <th>Team</th>
                    <th>HD</th>
                    <th>ES</th>
                    <th>SP</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Daily Fixed</strong></td>
                    <td>{fixedReopen.dailyHdFixed}</td>
                    <td>{fixedReopen.dailyEscFixed}</td>
                    <td>{fixedReopen.dailyImtFixed}</td>
                    <td>{fixedReopen.dailyTotalFixed}</td>
                </tr>
                <tr>
                    <td><strong>Daily Reopened</strong></td>
                    <td>{fixedReopen.dailyHdReopened}</td>
                    <td>{fixedReopen.dailyEscReopened}</td>
                    <td>{fixedReopen.dailyImtReopened}</td>
                    <td>{fixedReopen.dailyTotalReopened}</td>
                </tr>
                <tr>
                    <td><strong>Weekly Fixed</strong></td>
                    <td>{fixedReopen.weeklyHdFixed}</td>
                    <td>{fixedReopen.weeklyEscFixed}</td>
                    <td>{fixedReopen.weeklyImtFixed}</td>
                    <td>{fixedReopen.weeklyTotalFixed}</td>
                </tr>
                <tr>
                    <td><strong>Weekly Reopened</strong></td>
                    <td>{fixedReopen.weeklyHdReopened}</td>
                    <td>{fixedReopen.weeklyEscReopened}</td>
                    <td>{fixedReopen.weeklyImtReopened}</td>
                    <td>{fixedReopen.weeklyTotalReopened}</td>
                </tr>
            </tbody>
          </table>
        );
    }
    getFirstTimeFix=()=>{
        const {firstTimeFixed}=this.state;
        if(!firstTimeFixed.engineers)
        return null;
        return <table className="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Raised</th>
                    <th>Attempted</th>
                    <th>Achieved</th>
                </tr>
            </thead>
            <tbody>
                {
                    firstTimeFixed.engineers.map(e=><tr key={e.name}>
                        <td>{e.name}</td>
                        <td>{e.totalRaised}</td>
                        <td>{e.attemptedFirstTimeFix}</td>
                        <td>{e.firstTimeFix}</td>
                    </tr>)
                }
            </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td>{firstTimeFixed.phonedThroughRequests}</td>
                    <td>{firstTimeFixed.firstTimeFixAttemptedPct}%</td>
                    <td>{firstTimeFixed.firstTimeFixAchievedPct}%</td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>{firstTimeFixed.monthlyPhonedThroughRequests}</td>
                    <td>{firstTimeFixed.monthlyFirstTimeFixAttemptedPct}%</td>
                    <td>{firstTimeFixed.monthlyFirstTimeFixAchievedPct}%</td>
                </tr>
            </tfoot>
        </table>
    }
    getTeamPerformance=()=>{
        const {teamPerformance}=this.state;
        return <table className="table table-striped">
            <thead>
                <tr>
                <th></th>
                <th>Target</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>HD SLA %	</td>
                    <td >{teamPerformance.hdTeamTargetSlaPercentage}</td>
                    <td className={teamPerformance.hdTeamActualSlaPercentage1Class}>{teamPerformance.hdTeamActualSlaPercentage1}</td>
                    <td className={teamPerformance.hdTeamActualSlaPercentage2Class}>{teamPerformance.hdTeamActualSlaPercentage2}</td>
                    <td className={teamPerformance.hdTeamActualSlaPercentage3Class}>{teamPerformance.hdTeamActualSlaPercentage3}</td>
                    <td className={teamPerformance.hdTeamActualSlaPercentage4Class}>{teamPerformance.hdTeamActualSlaPercentage4}</td>
                </tr>
                <tr>
                    <td>HD Fix Hours	</td>
                    <td>{teamPerformance.hdTeamTargetFixHours}</td>
                    <td className={teamPerformance.hdTeamActualFixHours1Class}>{teamPerformance.hdTeamActualFixHours1}</td>
                    <td className={teamPerformance.hdTeamActualFixHours2Class}>{teamPerformance.hdTeamActualFixHours2}</td>
                    <td className={teamPerformance.hdTeamActualFixHours3Class}>{teamPerformance.hdTeamActualFixHours3}</td>
                    <td className={teamPerformance.hdTeamActualFixHours4Class}>{teamPerformance.hdTeamActualFixHours4}</td>                    
                </tr>
                <tr >
                    <td>HD Qty	</td>
                    <td>{teamPerformance.hdTeamTargetFixQty}</td>
                    <td className={teamPerformance.hdTeamActualFixQty1Class}>{teamPerformance.hdTeamActualFixQty1}</td>
                    <td className={teamPerformance.hdTeamActualFixQty2Class}>{teamPerformance.hdTeamActualFixQty2}</td>
                    <td className={teamPerformance.hdTeamActualFixQty3Class}>{teamPerformance.hdTeamActualFixQty3}</td>
                    <td className={teamPerformance.hdTeamActualFixQty4Class}>{teamPerformance.hdTeamActualFixQty4}</td>
                </tr>                
                <tr><td colSpan={6} style={{height:10}}></td></tr>
                <tr>
                    <td>Esc SLA %	</td>
                    <td>{teamPerformance.esTeamTargetSlaPercentage}</td>
                    <td className={teamPerformance.esTeamActualSlaPercentage1Class}>{teamPerformance.esTeamActualSlaPercentage1}</td>
                    <td className={teamPerformance.esTeamActualSlaPercentage2Class}>{teamPerformance.esTeamActualSlaPercentage2}</td>
                    <td className={teamPerformance.esTeamActualSlaPercentage3Class}>{teamPerformance.esTeamActualSlaPercentage3}</td>
                    <td className={teamPerformance.esTeamActualSlaPercentage4Class}>{teamPerformance.esTeamActualSlaPercentage4}</td>
                </tr>
                <tr>
                    <td>Esc Fix Hours	</td>
                    <td>{teamPerformance.esTeamTargetFixHours}</td>
                    <td className={teamPerformance.esTeamActualFixHours1Class}>{teamPerformance.esTeamActualFixHours1}</td>
                    <td className={teamPerformance.esTeamActualFixHours2Class}>{teamPerformance.esTeamActualFixHours2}</td>
                    <td className={teamPerformance.esTeamActualFixHours3Class}>{teamPerformance.esTeamActualFixHours3}</td>
                    <td className={teamPerformance.esTeamActualFixHours4Class}>{teamPerformance.esTeamActualFixHours4}</td>
                </tr>
                <tr>
                    <td>Esc Qty	</td>
                    <td>{teamPerformance.esTeamTargetFixQty}</td>
                    <td className={teamPerformance.esTeamActualFixQty1Class}>{teamPerformance.esTeamActualFixQty1}</td>
                    <td className={teamPerformance.esTeamActualFixQty2Class}>{teamPerformance.esTeamActualFixQty2}</td>
                    <td className={teamPerformance.esTeamActualFixQty3Class}>{teamPerformance.esTeamActualFixQty3}</td>
                    <td className={teamPerformance.esTeamActualFixQty4Class}>{teamPerformance.esTeamActualFixQty4}</td>
                </tr>
                <tr><td colSpan={6} style={{height:10}}></td></tr>
                <tr>
                    <td>SP SLA %	</td>
                    <td>{teamPerformance.smallProjectsTeamTargetSlaPercentage}</td>
                    <td className={teamPerformance.smallProjectsTeamActualSlaPercentage1Class}>{teamPerformance.smallProjectsTeamActualSlaPercentage1}</td>
                    <td className={teamPerformance.smallProjectsTeamActualSlaPercentage2Class}>{teamPerformance.smallProjectsTeamActualSlaPercentage2}</td>
                    <td className={teamPerformance.smallProjectsTeamActualSlaPercentage3Class}>{teamPerformance.smallProjectsTeamActualSlaPercentage3}</td>
                    <td className={teamPerformance.smallProjectsTeamActualSlaPercentage4Class}>{teamPerformance.smallProjectsTeamActualSlaPercentage4}</td>                    
                </tr>
                <tr>
                    <td>SP Fix Hours	</td>
                    <td>{teamPerformance.smallProjectsTeamTargetFixHours}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixHours1Class}>{teamPerformance.smallProjectsTeamActualFixHours1}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixHours2Class}>{teamPerformance.smallProjectsTeamActualFixHours2}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixHours3Class}>{teamPerformance.smallProjectsTeamActualFixHours3}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixHours4Class}>{teamPerformance.smallProjectsTeamActualFixHours4}</td>
                </tr>
                <tr>
                    <td>SP Qty	</td>
                    <td>{teamPerformance.smallProjectsTeamTargetFixQty}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixQty1Class}>{teamPerformance.smallProjectsTeamActualFixQty1}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixQty2Class}>{teamPerformance.smallProjectsTeamActualFixQty2}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixQty3Class}>{teamPerformance.smallProjectsTeamActualFixQty3}</td>
                    <td className={teamPerformance.smallProjectsTeamActualFixQty4Class}>{teamPerformance.smallProjectsTeamActualFixQty4}</td>
                </tr>
                <tr><td colSpan={6} style={{height:10}}></td></tr>
                <tr>
                    <td>Project SLA %	</td>
                    <td>{teamPerformance.projectTeamTargetSlaPercentage}</td>
                    <td className={teamPerformance.projectTeamActualSlaPercentage1Class}>{teamPerformance.projectTeamActualSlaPercentage1}</td>
                    <td className={teamPerformance.projectTeamActualSlaPercentage2Class}>{teamPerformance.projectTeamActualSlaPercentage2}</td>
                    <td className={teamPerformance.projectTeamActualSlaPercentage3Class}>{teamPerformance.projectTeamActualSlaPercentage3}</td>
                    <td className={teamPerformance.projectTeamActualSlaPercentage4Class}>{teamPerformance.projectTeamActualSlaPercentage4}</td>
                </tr>
                <tr>
                    <td>Projects Fix Hours	</td>
                    <td>{teamPerformance.projectTeamTargetFixHours}</td>
                    <td className={teamPerformance.projectTeamActualFixHours1Class}>{teamPerformance.projectTeamActualFixHours1}</td>
                    <td className={teamPerformance.projectTeamActualFixHours2Class}>{teamPerformance.projectTeamActualFixHours2}</td>
                    <td className={teamPerformance.projectTeamActualFixHours3Class}>{teamPerformance.projectTeamActualFixHours3}</td>
                    <td className={teamPerformance.projectTeamActualFixHours4Class}>{teamPerformance.projectTeamActualFixHours4}</td>
                </tr>
                <tr>
                    <td>Projects Qty	</td>
                    <td>{teamPerformance.projectTeamTargetFixQty}</td>
                    <td className={teamPerformance.projectTeamActualFixQty1Class}>{teamPerformance.projectTeamActualFixQty1}</td>
                    <td className={teamPerformance.projectTeamActualFixQty2Class}>{teamPerformance.projectTeamActualFixQty2}</td>
                    <td className={teamPerformance.projectTeamActualFixQty3Class}>{teamPerformance.projectTeamActualFixQty3}</td>
                    <td className={teamPerformance.projectTeamActualFixQty4Class}>{teamPerformance.projectTeamActualFixQty4}</td>
                </tr>
            </tbody>
        </table>
    }
    getUserPerformance=()=>{
        const {allUserPerformance}=this.state;
        return <div style={{display:"flex" ,flexDirection:"row" , flexWrap:"wrap"}}>
            {this.getUserTeamPerformance("Help Desk",allUserPerformance.filter(u=>u.team=='hd'))}
            {this.getUserTeamPerformance("Escalations",allUserPerformance.filter(u=>u.team=='es'))}
            {this.getUserTeamPerformance("Small Projects",allUserPerformance.filter(u=>u.team=='sp'))}
            {this.getUserTeamPerformance("Projects",allUserPerformance.filter(u=>u.team=='p'))}
        </div>
    }
    getUserTeamPerformance(title,data){
        return <div style={{marginRight:25}}>
            <h3>{title}</h3>
            <table className="table table-striped ">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Target %</th>
                    <th colSpan={2} style={{textAlign:"center"}}>Week</th>
                    <th colSpan={2} style={{textAlign:"center"}}>Month</th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th>%</th>
                    <th>Hours</th>
                    <th>%</th>
                    <th>Hours</th>
                </tr>
            </thead>
            <tbody>
                {data.map((u,i)=><tr key={i}>
                    <td className="bold">{u.initials}</td>
                    <td>{u.targetPercentage}</td>
                    <td className={u.weeklyPercentageClass}>{u.weeklyPercentage}</td>
                    <td>{u.weeklyHours}</td>
                    <td className={u.monthlyPercentageClass}>{u.monthlyPercentage}</td>
                    <td>{u.monthlyHours}</td>
                </tr>)}
            </tbody>
        </table>
        </div>
        
    }
    getActionBar=()=>{
        const { cards } = this.state;
        //return <div style={{position:"absolute", bottom:0}}>Test</div>
        return (
          <div style={{ position: "fixed", bottom: 0, display: "flex" }}>
            <div className="action-bar-item" onClick={this.handleReset}>
              Default Layout
            </div>
            {cards
              .filter((c) => c.minimize == true)
              .map((c,i) => (
                <div
                  key={i}
                  className="action-bar-item"
                  onClick={() => this.handleMinimizeCard(c)}
                >
                  {c.title}
                  <i className="fa fa-plus ml-3"></i>
                </div>
              ))}
          </div>
        );
    }
    handleReset=()=>{
        let {cards}=this.state;        
        for(let i=0; i<cards.length;i++)
        {
            cards[i].order=cards[i].id;
            cards[i].top="";
            cards[i].left="";
            cards[i].width="";
            cards[i].height="";
        }
        
        this.setState({cards},()=>this.saveOrder());
    }
    getDailyStats=()=>{
        return <iframe style={{border:0,overflow:"hidden",overflowX:"hidden",overflowY:"hidden",height:"80%",minWidth:"200",position:"absolute",top:70,left:0,right:0,bottom:0}}  width="100%" height="100%" src="https://cncdev2.cnc-ltd.co.uk/popup.php?action=dailyStats"></iframe>
    }
    getTeamCharts(){
        return (
          <div>
            <select
              name="team"              
              style={{display: "inline-block"}}
            >
              <option value="1" >
                Help Desk Team
              </option>
              <option value="2">Escalations Team</option>
              <option value="3">Small Projects Team</option>
              <option value="5">Projects Team</option>
            </select>
          </div>
        );
    }
    render() {        
        return (
            <div style={{minHeight:"97vh",marginBottom:30}}>
                <Spinner show={this.state.showSpinner}></Spinner>                
                {
                    this.getCardsElement()
                    
                }
                {this.getActionBar()}
            </div>
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactHome");
    ReactDOM.render(React.createElement(HomeComponent), domContainer);
});
