import MainComponent from "../../shared/MainComponent.js";
import * as React from "react";
import APIActivity from "../../services/APIActivity.js";
import { groupBy, params } from "../../utils/utils.js";
 
class TimeBreakdownComponent extends MainComponent {
  el = React.createElement;
  api = new APIActivity();
  constructor(props) {
    super(props);
    this.state = { data: [] };
  }

  componentDidMount() {
    if (params.get("problemID"))
      this.api.getTimeBreakdown(params.get("problemID")).then((data) => {
        data=data.map(d=>{
          return {...d,sname:d.firstName[0]+d.lastName[0]}
        })
        this.setState({ data });
        console.log('data',data);
      });
  }

  getTimeBreakdownElement = () => {
    const { el } = this;
    const { data } = this.state;
    const dataGroup = groupBy(data, "cat_desc");
    const users=data.map(d=>d.sname).reduce((prev,cur)=>{      
        if(prev.indexOf(cur)==-1)
        prev.push(cur);
        return prev;
    },[]);
    console.log("users",users,dataGroup);

    return (
      <div style={{display: "flex",justifyContent:"center",alignItems:"center"}}>
      <table className="table table-striped" style={{width:500}}>
        <thead>
          <tr>
              <th>Activity</th>
              {users.map(u=><th key={u}>{u}</th>)}
              <th>Total</th>
           </tr>
        </thead>
        <tbody>          
            {dataGroup.map(g=>
            <tr key={g.groupName}>
            <td>{g.groupName}</td>
            {users.map(u=><td key={u} style={{color:this.getColor(g.items,u)}}>{this.getUserTime(g.items,u)}</td>)}
            <td>{this.getActivityTypeTime(g.groupName)}</td>
            </tr>
            )}          
        </tbody>
        <tfoot>
          <tr>
            <th  style={{textAlign:"left"}}>Total</th>
            {users.map(u=><th key={u} style={{textAlign:"left"}}>{this.getUserTotal(u)}</th>)}
            <th style={{textAlign:"left"}}>{this.getTotalTime()}</th>
          </tr>
        </tfoot>
      </table>
      </div>
    );
  };
  getActivityTypeTime(activityType)
  {
    const {data}=this.state;
    const type=data.filter(u=>u.cat_desc==activityType);        
    if(type.length>0)
    {
      return type.reduce((prev,curr)=>
      {
        if(curr)
        prev +=parseFloat(curr.inHours)+parseFloat(curr.outHours);
        return prev;
      },0).toFixed(2);
         
    }
    else 
      return "0.00";
  }
  getUserTime(users,userName)
  {
    const user=users.filter(u=>u.sname==userName);        
    if(user.length>0)
      return (parseFloat(user[0].inHours)+parseFloat(user[0].outHours)).toFixed(2);
    else return "0.00"
  }
  getColor(users,userName)
  {
    const user=users.filter(u=>u.sname==userName);        
    let value=0;
    if(user.length>0)
      value= (parseFloat(user[0].inHours)+parseFloat(user[0].outHours)).toFixed(2);
    if(value<=0)
    return '#cccccc';
    else return '000';
  }
  getUserTotal=(sname)=>
  {
    const {data}=this.state;
    const items=data.filter(u=>u.sname==sname);
    console.log(items);
    const sum=items.reduce((prev,cur)=>{
      if(cur)
        prev +=parseFloat(cur.inHours)+parseFloat(cur.outHours);
      return prev ;
    },0);
    console.log(sum);
    return sum.toFixed(2);
  }
  getTotalTime()
  {
    const {data}=this.state;
    return data.reduce((prev,cur)=>{
      if(cur)
      prev +=parseFloat(cur.inHours)+parseFloat(cur.outHours);
      return prev;
    },0).toFixed(2);   
  }
  render() {
    return this.getTimeBreakdownElement();
  }
}
export default TimeBreakdownComponent;
