import MainComponent from "../../CMPMainComponent.js?v=1";
import { SRQueues } from "../../utils/utils.js?v=9";
import APISDManagerDashboard from "../services/APISDManagerDashboard.js?v=1";
import Spinner from './../../utils/spinner.js?v=9';

class CMPDailyStats extends MainComponent {
  el = React.createElement;
  apiSDManagerDashboard = new APISDManagerDashboard();
  intervalHandler;
  loading=true;
  constructor(props) {
    super(props);
    this.state = {
      summary: {
        prioritySummary: [],
        openSrTeamSummary: [],
        dailySourceSummary: [],
        raisedTodaySummary: { total: 0 },
        fixedTodaySummary: { total: 0 },
        nearSLASummary: { total: 0 },
        reopenTodaySummary: { total: 0 },
      },
      showSpinner: true,
    };
  }
  componentWillUnmount() {
    clearInterval(  this.intervalHandler);
  }
  componentDidMount() {
    this.loadDashBoard();    
    this.intervalHandler=setInterval(()=>this.loadDashBoard(),2*60*1000);
  }
  loadDashBoard = () => {
    this.apiSDManagerDashboard.getDailyStatsSummary().then((result) => {
      console.log(result);
      this.loading=false;
      this.setState({ showSpinner: false, summary: result });
    });
  };

  getSummaryElemen = () => {
    const { el } = this;
    const { summary } = this.state;
    if(this.loading)
    return null;
    return el(
      'div',{style:{display:"flex",justifyContent:"center",maxWidth:1200}},
    el(
      "div",
      { className: "flex-row",style:{flexWrap:"wrap",justifyContent:"left",maxWidth:1140} },
      this.getOpenSrCard(summary.prioritySummary),
      this.getTeamSrCard(summary.openSrTeamSummary,"#00628B","#E6E6E6"),
      this.getDailySourceCard(summary.dailySourceSummary),
      this.getTotalCard( "Unique Customers",summary.uniqueCustomerTodaySummary.total,"#00628B","#E6E6E6"),
      this.getTotalCard( "Near SLA",summary.nearSLASummary.total),
      this.getTotalCard( "Raised Today",summary.raisedTodaySummary.total,"#00628B","#E6E6E6"),      
      this.getTotalCard( "Today's Started",summary.reopenTodaySummary.total),
      this.getTotalCard( "Fixed Today",summary.fixedTodaySummary.total,"#00628B","#E6E6E6"),
      this.getTotalCard( "Reopened Today",summary.raisedStartTodaySummary.total),
      this.getTotalCard( "Breached SLA",summary.breachedSLATodaySummary.total,"#00628B","#E6E6E6"),

    ));
  };
  getOpenSrCard = (data,backgroundColor="#C6C6C6",textColor="#3C3C3C") => {
    if (data.length > 0) {
      const { el } = this;
      const getPriorityData = (id) => {
        const obj = data.filter((d) => d.priority == id);
        if (obj.length > 0) return obj[0].total;
        else return 0;
        s;
      };
      const totalSR = data.reduce((prev, curr) => {
        prev = prev + parseInt(curr.total);
        return prev;
      }, 0);
      return el(
        "div",
        { className: "sd-card "  ,style:{backgroundColor:backgroundColor,color:textColor}},
        el("label", { className: "sd-card-title" }, "Open SRs"),
        el(
          "table",
          null,
          el(
            "tbody",
            null,
            el(
              "tr",
              null,
              el("td", null, `P1  `),
              el("td", null, getPriorityData(1))
            ),
            el(
              "tr",
              null,
              el("td", null, `P2  `),
              el("td", null, getPriorityData(2))
            ),
            el(
              "tr",
              null,
              el("td", null, `P3  `),
              el("td", null, getPriorityData(3))
            ),
            el(
              "tr",
              null,
              el("td", null, `P4  `),
              el("td", null, getPriorityData(4))
            ),
            el("tr", null, el("td", null, `Total  `), el("td", null, totalSR))
          )
        )
      );
    } else return null;
  };
  getTeamSrCard = (data,backgroundColor="#C6C6C6",textColor="#3C3C3C") => {
    if (data.length > 0) {
      const { el } = this;
      const getTeamTitle = (id) => {
        const team = SRQueues.filter((t) => t.teamID == id);
        if (team.length > 0) return team[0].name;
      };
      const getTeamTotal = (id) => {
        const team = data.filter((t) => t.teamID == id);
        if (team.length > 0) return team[0].total;
      };
      const totalSR = data.reduce((prev, curr) => {
        prev = prev + parseInt(curr.total);
        return prev;
      }, 0);
      return el(
        "div",
        { className: "sd-card " ,style:{backgroundColor:backgroundColor,color:textColor} },
        el("label", { className: "sd-card-title" }, "Team SRs"),
        el(
          "table",
          {style:{color:textColor}},
          el(
            "tbody",
            null,
            el(
              "tr",
              null,
              el("td", null, getTeamTitle(1)),
              el("td", null, getTeamTotal(1))
            ),
            el(
              "tr",
              null,
              el("td", null, getTeamTitle(2)),
              el("td", null, getTeamTotal(2))
            ),
            el(
              "tr",
              null,
              el("td", null, getTeamTitle(4)),
              el("td", null, getTeamTotal(4))
            ),
            el(
              "tr",
              null,
              el("td", null, getTeamTitle(5)),
              el("td", null, getTeamTotal(5))
            ),
            el("tr", null, el("td", null, `Total  `), el("td", null, totalSR))
          )
        )
      );
    } else return null;
  };
  getDailySourceCard = (data,backgroundColor="#C6C6C6",textColor="#3C3C3C") => {
    if(data.length==0)
    {
      data=[
        {description: "Email", total: "0"},
        {description: "Alert", total: "0"},
        {description: "Manual", total: "0"},
        {description: "Phone", total: "0"},
        {description: "On site", total: "0"},
        {description: "Sales", total: "0"}
    ]
    }
    if (data.length > 0) {
      const { el } = this;
      const dataDisplay = data.filter(
        (d) =>
          d.description == "Phone" ||
          d.description == "Email" ||
          d.description == "Alert" ||
          d.description == "Portal"
      );
      const dataOthers = data.filter(
        (d) =>
          d.description != "Phone" &&
          d.description != "Email" &&
          d.description != "Alert" &&
          d.description != "Portal"
      );
      console.log(dataOthers);
      const dataOthersTotal = dataOthers.reduce((prev, curr) => {
        prev = prev + parseInt(curr.total);
        return prev;
      }, 0);

      return el(
        "div",
        { className: "sd-card "  ,style:{backgroundColor:backgroundColor,color:textColor}},
        el("label", { className: "sd-card-title" }, "Daily SR Source"),
        el(
          "table",
          null,
          el(
            "tbody",
            null,
            dataDisplay.map((d) =>
              el(
                "tr",
                { key: d.description },
                el("td", null, d.description),
                el("td", null, d.total)
              )
            ),
            el(
              "tr",
              {},
              el("td", null, "Others"),
              el("td", null, dataOthersTotal)
            )
          )
        )
      );
    } else return null;
  };
  getTotalCard = (label,total,backgroundColor="#C6C6C6",textColor="#3C3C3C") => {
    const {el}=this;
    return el(
      "div",
      { className: "sd-card " ,style:{backgroundColor:backgroundColor,color:textColor}},
      el("label", { className: "sd-card-title" },label),
      el("label", { style:{fontSize:40,marginTop:30} }, total)
    );
  };
 
  getDailyStatsLink=()=>{
    const {el}=this;
    return el('i',{className:"fal fa-expand-arrows fa-2x pointer",onClick:()=>window.open('Popup.php?action=dailyStats','popup','width=920,height=300')})
  }
  render() {
    const { el } = this;
    return el(
      "div",
      null,
      el(Spinner, { key: "spinner", show: this.state.showSpinner }),
      this.getSummaryElemen(),
      this.getDailyStatsLink()
    );
  }
}
export default CMPDailyStats;