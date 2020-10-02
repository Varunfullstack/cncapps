class Timer extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
     
      
        this.state = {  
            data:{
                minutes:'',
                hours:''
            }
        }
    }
    setValue=(hours,minutes)=>
    {
        const {data}=this.state;
        data.hours=hours;
        data.minutes=minutes;
        console.log(data);
        this.setState({data});
        if(this.props.onChange)
        {
            const val=(data.hours!=""?data.hours:"00")+":"+(data.minutes!=""?data.minutes:"00")
            this.props.onChange(val);
        }
    }
    render() { 
        const {el}=this;        
        let {data}=this.state;
        const {value,disabled}=this.props;
        if(value)
        {            
            data.hours=value?value.split(':')[0]:"",
            data.minutes=value?value.split(':')[1]:"";
        }
        return ( el("div",{style:{display:"Flex",flexDirection:"row"}},
        el("input",{type:"number",disabled,max:23,min:0, placeholder:"HH",style:{width:35,height:18.400,margin:0},onChange:(event)=>this.setValue(event.target.value,data.minutes),defaultValue:data.hours}),        
        el("input",{type:"number",disabled,max:59,min:0,placeholder:"MM",style:{width:35,height:18.400,margin:0},onChange:(event)=>this.setValue(data.hours,event.target.value),defaultValue:data.minutes}),
        ) );
    }
}
 
export default Timer;