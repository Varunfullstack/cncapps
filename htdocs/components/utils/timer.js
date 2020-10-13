class Timer extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
     
        const {value}=this.props;    
        this.state = {  
            data:{
                minutes:value?value.split(':')[1]:"",
                hours:value?value.split(':')[0]:""
            }
        }
    }
    validHours=(hours)=>{
         if(hours&&hours!=""&&parseInt(hours)<24&&hours>=0)
            return hours;
        else return "";
    }
    validMinutes=(minutes)=>{
        if(minutes&&minutes!=""&&minutes<60&&minutes>=0)
        return minutes;
        else return "";
    }
    setValue=(hours,minutes)=>
    {
        const {data}=this.state;
        data.hours=this.validHours(hours);
        data.minutes=this.validMinutes(minutes);       
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
        const {disabled}=this.props;        
        return ( el("div",{style:{display:"Flex",flexDirection:"row"}},
        el("input",{type:"number",disabled,max:23,min:0, placeholder:"HH",style:{width:35,height:18.400,marginRight:0},onChange:(event)=>this.setValue(event.target.value,data.minutes),value:data.hours}),        
        el("input",{type:"number",disabled,max:59,min:0,placeholder:"MM",style:{width:35,height:18.400,margin:0},onChange:(event)=>this.setValue(data.hours,event.target.value),value:data.minutes}),
        ) );
    }
}
 
export default Timer;