"use strict";  
class AutoComplete extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      // The active selection's index
      activeSuggestion: 0,
      // The suggestions that match the user's input
      filteredSuggestions: [],
      // Whether or not the suggestion list is shown
      showSuggestions: false,
      // What the user has entered
      userInput: "",
    };
    
  }
  // Event fired when the input value is changed
  onChange = (e) => {
    let { items, displayLength ,displayColumn} = this.props;
    if (!displayLength) displayLength = 10;
    const userInput = e.currentTarget.value;

    // Filter our suggestions that don't contain the user's input
    const filteredSuggestions = items
      .filter(
        (suggestion) =>
          (displayColumn?suggestion[displayColumn]:suggestion).toLowerCase().indexOf(userInput.toLowerCase()) > -1
      )
      .slice(0, displayLength);

    // Update the user input and filtered suggestions, reset the active
    // suggestion and make sure the suggestions are shown
    this.setState({
      activeSuggestion: 0,
      filteredSuggestions,
      showSuggestions: true,
      userInput: e.currentTarget.value,
    });
  };
  // Event fired when the user clicks on a suggestion
  onClick = (item) => {
    const {displayColumn,onSelect}=this.props;
    // Update the user input and reset the rest of the state
    //console.log('click',item);
    this.setState({
      activeSuggestion: 0,
       showSuggestions: false,
      userInput:displayColumn?item[displayColumn]:item,
    });
    if(onSelect)
      onSelect(item);
  };
  // Event fired when the user presses a key down
  onKeyDown = (e) => {
    const { activeSuggestion, filteredSuggestions } = this.state; 
    const {displayColumn,onSelect}   =this.props;
    if(e.keyCode===27) //esc
    {
        this.setState({
            activeSuggestion: 0,
            showSuggestions: false,
            userInput: '',
            filteredSuggestions:[]
          });
          return;
    }
    // User pressed the enter key, update the input and close the
    // suggestions
    if (e.keyCode === 13) {
      this.setState({
        activeSuggestion: 0,
        showSuggestions: false,
        userInput: displayColumn?filteredSuggestions[activeSuggestion][displayColumn]:filteredSuggestions[activeSuggestion],
      });
      if(onSelect)
      onSelect(filteredSuggestions[activeSuggestion]);
    }
    // User pressed the up arrow, decrement the index
    else if (e.keyCode === 38) {
      if (activeSuggestion === 0) {
        return;
      }

      this.setState({ activeSuggestion: activeSuggestion - 1 });
    }
    // User pressed the down arrow, increment the index
    else if (e.keyCode === 40) {
      if (activeSuggestion - 1 === filteredSuggestions.length) {
        return;
      }

      this.setState({ activeSuggestion: activeSuggestion + 1 });
    }
  };
  handleOnClick = e=>{
     let {items,displayLength}=this.props;
     const {userInput}=this.state;
     displayLength=displayLength??10;
     let {filteredSuggestions}=this.state;
     if(filteredSuggestions.length===0&&userInput==='') // display first n of items
     {
        filteredSuggestions=items.slice(0,displayLength);
        this.setState({filteredSuggestions,
            showSuggestions:true
        })
     }
     else
     {
        this.setState({ 
            showSuggestions:true
        })
     }
  }
  handleOnBlur=e=>{
      setTimeout(()=>{
        this.setState({showSuggestions:false})
      },200);
  }
  componentDidUpdate(prevProps) {
    
  }
  render() {
    const {displayColumn,pk,errorMessage}=this.props;
   
    const {
      onChange,
      onClick,
      onKeyDown,
      state: {
        activeSuggestion,
        filteredSuggestions,
        showSuggestions,
        userInput,
      },
      handleOnClick,
      handleOnBlur
    } = this;
    let suggestionsListComponent;

    if (showSuggestions) {
      if (filteredSuggestions.length) {
        suggestionsListComponent = React.createElement("ul", {
          className: "suggestions"
        }, filteredSuggestions.map((suggestion, index) => {
          let className; // Flag the active suggestion with a class
        
          if (index === activeSuggestion) {
            className = "suggestion-active";
          }
         
          return React.createElement("li", {
            className: className,
            key: pk ? suggestion[pk] : suggestion,
            onClick: () => onClick(suggestion)
          }, displayColumn ? suggestion[displayColumn] : suggestion);
        }));
      } else {
        if(userInput!="")
        suggestionsListComponent =React.createElement("div", {
          className: "no-suggestions"
        }, React.createElement("em", null, errorMessage ? errorMessage : "No items "));
      }
    }
    let defaultValue=this.props.value?this.props.value:"";
    return React.createElement("div", null, React.createElement("input", {
      type: "text",
      onChange: onChange,
      onKeyDown: onKeyDown,
      value: userInput||defaultValue,
      onClick: handleOnClick,
      onBlur: handleOnBlur,
      style:{width:'100%'}
    }), suggestionsListComponent);
  }
}
export default AutoComplete;
