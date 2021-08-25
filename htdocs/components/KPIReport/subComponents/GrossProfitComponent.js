"use strict";
import React from "react";
import { Bar, Line } from "react-chartjs-2";
import { Colors, groupBy, sumBy } from "../../utils/utils";
import { KPIReportHelper } from "../helper/KPIReportHelper";
import moment from "moment";
import { ReportType } from "./../KPIReportComponent";
import { ColorsBig, sort } from "./../../utils/utils";
import Table, { CellType } from "../../shared/table/table";

export default class GrossProfitComponent extends React.Component {
  api;

  helper = new KPIReportHelper();

  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      data: this.props.data,
      filter: this.props.filter,
    };
  }

  static getDerivedStateFromProps(props, state) {
    return { ...state, ...props };
  }

  componentDidMount() {}

  getChartData = (data, filter) => {
    let filterData = [];
    let dataLabels = [];

    if (filter.resultType == ReportType.Monthly) {
      filterData = this.getMonthlyData(data);
      dataLabels = this.getMonthlyLabels(data);
    }
    console.log("filterData", filterData);
    return { filterData, dataLabels };
  };

  getMonthlyData(data) {
    const borderWidth = 2;
    const fill = false;
    data.map((i) => (i.profit = i.totalSale - i.totalCost));
 
    return [
      {
        label: "Internet (B)",
        data: this.getSumDataByKey(data, "profit", "B"),
        backgroundColor: "#4472c4",
        borderColor: "#4472c4",
        borderWidth,
        fill,
      },
      {
        label: "Telecom (F)",
        data: this.getSumDataByKey(data, "profit", "F"),
        backgroundColor: "#ffc000",
        borderColor: "#ffc000",
        borderWidth,
        fill,
      },
      {
        label: "Consultancy (G)",
        data: this.getSumDataByKey(data, "profit", "G"),
        backgroundColor: "#5b9bd5",
        borderColor: "#5b9bd5",
        borderWidth,
        fill,
      },
      {
        label: "Hardware (H)",
        data: this.getSumDataByKey(data, "profit", "H"),
        backgroundColor: "#70ad47",
        borderColor: "#70ad47",
        borderWidth,
        fill,
      },
      {
        label: "Managed (J)",
        data: this.getSumDataByKey(data, "profit", "J"),
        backgroundColor: "#264478",
        borderColor: "#264478",
        borderWidth,
        fill,
      },
      {
        label: "Maintenance (M)",
        data: this.getSumDataByKey(data, "profit", "M"),
        backgroundColor: "#9e480e",
        borderColor: "#9e480e",
        borderWidth,
        fill,
      },
      {
        label: "Prey-Pay (R)",
        data: this.getSumDataByKey(data, "profit", "R"),
        backgroundColor: "#636363",
        borderColor: "#636363",
        borderWidth,
        fill,
      },
      {
        label: "Software (S)",
        data: this.getSumDataByKey(data, "profit", "S"),
        backgroundColor: "#997300",
        borderColor: "#997300",
        borderWidth,
        fill,
      },
    ];
  }

  getMonthlyLabels(data) {
    return this.helper.getMonths(data, "date").map((d) => d.month);
  }

  getChart = () => {
    const { data, filter } = this.state;
    const { filterData, dataLabels } = this.getChartData(data, filter);
    const chartData = {
      labels: dataLabels,
      datasets: filterData,
    };
    const options = {
      maintainAspectRatio: false,
      scales: {
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
            },
          },
        ],
      },
    };
    return (
      <div style={{ height: "80vh", width: "85vw" }}>
        <Bar data={chartData} options={options} />
      </div>
    );
  };
  getSumDataByKey(data, key, cat) {
    const groups = groupBy(
      data.filter((i) => i.stockCat == cat),
      "date"
    );
    const final = groups.map((g) => {
      const total = sumBy(g.items, key).toFixed(2);
      return total;
    });
    return final;
  }
  getSummary = () => {
    const { data } = this.state;
    const groupByDate = groupBy(data, "date");
    sort(groupByDate,'groupName');
    const columns = [
      {
        path: "groupName",
        label: "Month",
        hdToolTip: "Month",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Text,
        footerContent:()=>"Total"

      },
      {
        path: "Internet (B)",
        label: "Internet (B)",
        hdToolTip: "Internet (B)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "B"),
        footerContent:()=>this.getTotalByCat('B')

      },
      {
        path: "Telecom (F)",
        label: "Telecom (F)",
        hdToolTip: "Telecom (F)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "F"),
        footerContent:()=>this.getTotalByCat('F')

      },
      {
        path: "Consultancy (G)",
        label: "Consultancy (G)",
        hdToolTip: "Consultancy (G)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "G"),
        footerContent:()=>this.getTotalByCat('G')

      },
      {
        path: "Hardware (H)",
        label: "Hardware (H)",
        hdToolTip: "Hardware (H)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "H"),
        footerContent:()=>this.getTotalByCat('H')

      },
      {
        path: "Managed (J)",
        label: "Managed (J)",
        hdToolTip: "Managed (J)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "S"),
        footerContent:()=>this.getTotalByCat('S')

      },
      {
        path: "Maintenance (M)",
        label: "Maintenance (M)",
        hdToolTip: "Maintenance (M)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "M"),
        footerContent:()=>this.getTotalByCat('M')

      },       
      {
        path: "Prey-Pay (R)",
        label: "Prey-Pay (R)",
        hdToolTip: "Prey-Pay (R)",
        hdClassName: "text-center",
         sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "R"),
        footerContent:()=>this.getTotalByCat('R')

      },
      {
        path: "Software (S)",
        label: "Software (S)",
        hdToolTip: "Software (S)",
        hdClassName: "text-center",
        sortable: false,
        cellType: CellType.Number,
        content: (group) => this.getMonthSummaryByCat(group.groupName, "S"),
        footerContent:()=>this.getTotalByCat('S')
      },
    ];
    return (
      <Table
        style={{ width: 900, marginTop: 10 }}
        key="summary"
        pk="groupName"
        columns={columns}
        data={groupByDate || []}
        search={false}
        hasFooter={true}
      ></Table>
    );
  };
  getTotalByCat=(cat)=>{
    const { data } = this.state;
    return <div className="text-right" >
        {data.filter(d=>d.stockCat==cat).map(d=>d.profit).reduce((a,b)=>a+b,0).toFixed(2)}
    </div>

  }
  getMonthSummaryByCat = (month, cat) => {
    const { data } = this.state;
    return data
      .filter((d) => d.stockCat == cat && d.date == month)
      .map((i) => i.totalSale - i.totalCost)
      .reduce((a, b) => a + b, 0)
      .toFixed(2);
  };
  getAverage=()=>{
    const { data } = this.state;
    const months=groupBy(data,'date').length;    
    const avgData=groupBy(data.filter(d=>d.profit>0),'stockCat').map(g=>{         
        return {'name':g.groupName,value:(sumBy(g.items,'profit')/months).toFixed(2)};
    }).filter(g=>['B','F','G','H','J','M','R','S'].indexOf(g.name)>=0);
    sort(avgData,'name');

    const columns=[
        {
           path: "name",
           label: "Stock Category",
           hdToolTip: "Stock Category",
           hdClassName: "text-center",
           sortable: true,
           cellType:CellType.Text,
           content:(g)=>this.getCatName(g.name)
         },
        {
            path: "value",
            label: "Average Profit",
            hdToolTip: "Average Profit per month",
            hdClassName: "text-center",
            sortable: true,
            cellType:CellType.Number,
            content:(g)=>g.value
         }
    ];
    return (
        <Table
          style={{ width: 600, marginTop: 10 }}
          key="monthAverage"
          pk="name"
          columns={columns}
          data={avgData || []}
          search={false}
         ></Table>
      );
  }
  getCatName=(cat)=>{
      switch(cat){
          case 'B':
              return 'Internet (B)';
              case 'F':
              return 'Telecom (F)';
              case 'G':
              return 'Consultancy (G)';
              case 'H':
              return 'Hardware (H)';
              case 'J':
              return 'Managed (J)';
              case 'M':
              return 'Maintenance (M)';
              case 'R':
              return 'Prey-Pay (R)';
              case 'S':
              return 'Software (S)';
              default :
              return cat
      }
  }
  render() {
    return (
      <div>
        {this.getChart()}
        <h3>Monthly Total  Over The Selected Period</h3>
        {this.getSummary()}
        <h3>Monthly Average Over The Selected Period</h3>
        {this.getAverage()}
      </div>
    );
  }
}
