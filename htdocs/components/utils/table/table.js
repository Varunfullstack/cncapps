import TableHeader from "./tableHeader.js?v=1";
import TableBody from "./tableBody.js?v=1";
class Table extends React.Component {
  constructor(props){
    super(props);
    this.state={
      sortColumn:{path:this.props.defaultSortPath,
        order:this.props.defaultSortOrder?this.props.defaultSortOrder:'asc'}
    }
    
  }
  handleSort=(sortColumn)=>{
 
    for(let i=0;i<this.props.columns.length;i++)
    {
      if(this.props.columns[i].path===sortColumn.path)
      {
           //check if column is sortable
        const sortable=this.props.columns[i].sortable?this.props.columns[i].sortable:false;
        if(sortable)
        this.setState({ sortColumn });
       }
    }
    
  }
  get=(o, p) => p.split('.').reduce((a, v) => a[v], o);
  sort=(array,path,order='asc')=>{
     return array.sort((a,b)=>{
          if(this.get(a,path)>this.get(b,path) || this.get(a,path)==null || this.get(a,path)==undefined)
              return order=='asc'?1:-1;
          if(this.get(a,path)<this.get(b,path) || this.get(b,path)==null || this.get(a,path)==undefined)
              return order=='asc'?-1:1;
          else return 0;
      })
  }
  render() {
    const props=this.props;   
      const { data,  columns,pk } = props;
      const {sortColumn}=this.state;
      const el = React.createElement;
      if(this.state.sortColumn.path!=null)
      {
        this.sort(data,this.state.sortColumn.path,this.state.sortColumn.order);
      }
      return el("table", { key: "table", className: "table table-striped" }, [
        el(TableHeader, {
          key: "tableHeader",
          columns: columns,
          sortColumn: sortColumn,
          onSort: this.handleSort,
        }),
        el(TableBody, { key: "tableBody", data: data, columns: columns,pk }),
      ]);   
  }
}

export default Table;
1;
