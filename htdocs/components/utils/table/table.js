import TableHeader from "./tableHeader.js?v=1";
import TableBody from "./tableBody.js?v=1";
import TableFooter from "./tableFooter.js?v=1";
/**
 * -- main properties
 * key: "documents",
 * data: data?.documents || [],
 * columns: columns,
 * pk: "id",
 * search: false,
 * hasFooter:false
 * -- columns properties
 * classNameColumn
 * className
 * backgroundColorColumn
 * path:''
 * label:''
 * sortable:false
 * footerContent :(c)=>
 * footerColSpan :1
 * toolTip
 * textColorColumn -> td text color
 * allowRowOrder Boolean allo rows drag and drops using jqueryUI
 * onOrderChange Event fire on row order changed and return current and next element
 */
class Table extends React.Component {
  delayTimer;
  constructor(props) {
    super(props);
    this.state = {
      sortColumn: {
        path: this.props.defaultSortPath,
        order: this.props.defaultSortOrder
          ? this.props.defaultSortOrder
          : "asc",
        searchFilter: "",
      },
    };
  }
  componentDidMount() {
   if(this.props.allowRowOrder)
   {
    setTimeout(()=>{
    $("#table"+this.props.key+" tbody").sortable({
      helper: this.fixHelperModified,
      stop: this.updateIndex
    }).disableSelection()
    },2000);
  }
  }
  fixHelperModified = (e, tr)=> {
    var $originals = tr.children();
    var $helper = tr.clone();
    $helper.children().each(function(index) {
        $(this).width($originals.eq(index).width())
    });
    return $helper;
  }
  /**
   * 
   * @param {place element} e 
   * @param {drag element} ui 
   */
  updateIndex = (e, ui)=> {    
    const currentItemId= $(ui.item[0]).attr('id');
    const nextItemId=$(ui.item[0]).next().attr('id');
    const currentItem=this.props.data.filter(i=>i[this.props.pk]==currentItemId)[0];
    const nextItem=this.props.data.filter(i=>i[this.props.pk]==nextItemId)[0];    
    if(this.props.onOrderChange)
     this.props.onOrderChange(currentItem,nextItem);    
  };

  handleSort = (sortColumn) => {
    for (let i = 0; i < this.props.columns.length; i++) {
      if (this.props.columns[i].path === sortColumn.path) {
        //check if column is sortable
        const sortable = this.props.columns[i].sortable
          ? this.props.columns[i].sortable
          : false;
        if (sortable) this.setState({ sortColumn });
      }
    }
  };
  get = (o, p) => p.split(".").reduce((a, v) => a[v], o);
  sort = (array, path, order = "asc") => {
    return array.sort((a, b) => {
      if (
        this.get(a, path) > this.get(b, path) ||
        this.get(a, path) == null ||
        this.get(a, path) == undefined
      )
        return order == "asc" ? 1 : -1;
      if (
        this.get(a, path) < this.get(b, path) ||
        this.get(b, path) == null ||
        this.get(a, path) == undefined
      )
        return order == "asc" ? -1 : 1;
      else return 0;
    });
  };
  handleSearch = (event) => {
    clearTimeout(this.delayTimer);
    event.persist();
    this.delayTimer = setTimeout(()=> {
      console.log(event.target.value);
      this.setState({ searchFilter: event.target.value });
    }, 1000); // Will do the ajax stuff after 1000 ms, or 1 s
  };
  filterData(data, columns) {
    const { searchFilter } = this.state;
    let filterdData = [];
    if (searchFilter && searchFilter.length > 0) {
      for (let i = 0; i < data.length; i++) {
        for (let j = 0; j < columns.length; j++) {
          if (columns[j].path != null && columns[j].path != "") {
            if (
              data[i][columns[j].path] &&
              data[i][columns[j].path]
                .toString()
                .toLowerCase()
                .indexOf(searchFilter.toLowerCase()) >= 0
            ) {
              filterdData.push(data[i]);
              break;
            }
          }
        }
      }
      return filterdData;
    } else return [...data];
  }
  render() {
    const props = this.props;
    const {
      data,
      columns,
      pk,
      selected,
      selectedKey,
      search,
      searchLabelStyle,
      hasFooter
    } = props;
    const { sortColumn } = this.state;
    const { handleSearch } = this;
    const el = React.createElement;
    const filterData = search ? this.filterData(data, columns) : data;
    if (this.state.sortColumn.path != null && data.length > 0) {
      this.sort(
        filterData,
        this.state.sortColumn.path,
        this.state.sortColumn.order
      );
    }
    return [
      search
        ? el("div", { key: "tableSearch", style: { marginBottom: 5 } }, [
            el(
              "label",
              { key: "lbLabel", style: searchLabelStyle || null },
              "Search"
            ),
            el("input", { key: "inpSearch", onChange: handleSearch }),
          ])
        : null,
      el("table", { key: "table"+this.props.key,id: "table"+this.props.key, className: "table table-striped" }, [
        el(TableHeader, {
          key: "tableHeader",
          columns: columns,
          sortColumn: sortColumn,
          onSort: this.handleSort,
        }),
        filterData.length > 0
          ? el(TableBody, {
              key: "tableBody",
              data: filterData,
              columns,
              pk,
              selected,
              selectedKey,
            })
          : null,
          hasFooter?el(TableFooter,{ key: "tableFooter",columns}):null
      ]),
    ];
  }
}
export default Table;

